import socket
import psycopg2
from decimal import Decimal
import logging
import select

# Configuration des logs
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(message)s')

# Informations de connexion à la base de données
DB_CONFIG = {
    'host': 'localhost',
    'port': '5432',
    'database': 'projetf',
    'user': 'dyhia',
    'password': 'dyhia*db*2003'
}

# Paramètres du serveur
HOST = '127.0.0.1'
PORT = 8081

# Connexion à la base de données
def bdd_conn():
    """
    Établit une connexion à la base de données PostgreSQL.

    Returns:
        psycopg2.extensions.connection: Objet de connexion si la connexion réussit.
        None: Si la connexion échoue.
    """
    try:
        connection = psycopg2.connect(
            host=DB_CONFIG['host'],
            port=DB_CONFIG['port'],
            database=DB_CONFIG['database'],
            user=DB_CONFIG['user'],
            password=DB_CONFIG['password']
        )
        logging.info("Connexion à la base de données réussie.")
        return connection
    except Exception as e:
        logging.error(f"Erreur de connexion à la base de données : {e}")
        return None

# Fonction pour sécuriser les entrées utilisateur
def sanitize_input(user_input):
    """
    Nettoie les données utilisateur pour éviter les injections SQL.

    Args:
        user_input (str): Entrée utilisateur brute.

    Returns:
        str: Entrée utilisateur nettoyée (sans guillemets simples ni doubles).
    """
    return user_input.replace("'", "").replace('"', "")

# Fonction pour lire les données avec timeout
def read_with_timeout(socket, timeout=120):
    """
    Lit les données à partir d'une socket avec un délai d'attente.

    Args:
        socket (socket.socket): La socket à partir de laquelle lire.
        timeout (int): Temps limite en secondes pour la lecture.

    Returns:
        str: Données reçues de la socket.
        None: Si le délai d'attente est dépassé sans réception de données.
    """
    ready = select.select([socket], [], [], timeout)
    if ready[0]:
        return socket.recv(256).decode().strip()  # S'il y a des données à lire
    else:
        return None  # Timeout atteint, aucune donnée reçue

# Définition du serveur
def start_server():
    """
    Démarre le serveur et gère les interactions client-serveur.

    Le serveur :
    - Valide le numéro de MSA et le numéro de carte.
    - Permet l'ajout d'articles et calcule le total des achats.
    - Applique des réductions en fonction des points accumulés.
    - Génère une facture si la commande est validée.

    Toute erreur ou timeout est gérée et enregistrée dans les logs.
    """
    conn_db = bdd_conn()
    if not conn_db:
        logging.error("Impossible de démarrer la connexion à la base de données.")
        return

    cursor = conn_db.cursor()

    # Création de la socket
    server_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    server_socket.settimeout(120)

    try:
        server_socket.bind((HOST, PORT))
        server_socket.listen(1)
        logging.info(f"Le serveur est prêt et en écoute sur {HOST}:{PORT}")

        # Attente d'une connexion
        conn, addr = server_socket.accept()
        logging.info(f"Connexion établie avec {addr}")

        try:
            # Validation du numéro de MSA
            while True:
                msa_number = read_with_timeout(conn)
                if msa_number is None:
                    logging.info("Timeout atteint sans saisie MSA.")
                    conn.sendall("Timeout de saisie atteint.\n".encode('utf-8'))
                    break

                msa_number = sanitize_input(msa_number)
                if not msa_number:
                    continue
                logging.info(f"Numéro de MSA reçu : {msa_number}")

                cursor.execute("SELECT id_msa FROM msa WHERE id_msa = %s", (msa_number,))
                result = cursor.fetchone()
                if result:
                    conn.sendall("MSA valide.\n".encode('utf-8'))
                    break
                else:
                    conn.sendall("MSA invalide.\n".encode('utf-8'))

            # Validation du numéro de carte
            while True:
                card_number = read_with_timeout(conn)
                if card_number is None:
                    logging.info("Timeout atteint sans saisie de carte.")
                    conn.sendall("Timeout de saisie atteint.\n".encode('utf-8'))
                    break

                card_number = sanitize_input(card_number)
                if not card_number:
                    continue
                logging.info(f"Numéro de carte reçu : {card_number}")

                cursor.execute("SELECT id_carte FROM client WHERE id_carte = %s", (card_number,))
                result = cursor.fetchone()
                if result:
                    conn.sendall("Carte valide.\n".encode('utf-8'))
                    break
                else:
                    conn.sendall("Carte invalide.\n".encode('utf-8'))

            # Réception des articles
            articles = {}
            total = Decimal(0)
            pts = 0

            while True:
                article_reference = read_with_timeout(conn)
                if article_reference is None:
                    logging.info("Timeout atteint sans saisie d'article.")
                    conn.sendall("Timeout de saisie atteint.\n".encode('utf-8'))
                    break

                if article_reference == "FIN":
                    logging.info("Saisie des articles terminée.")
                    break

                article_reference = sanitize_input(article_reference)
                logging.info(f"Référence de l'article reçu : {article_reference}")

                cursor.execute(
                    "SELECT reference_produit, prix, point FROM article WHERE reference_produit = %s",
                    (article_reference,)
                )
                result = cursor.fetchone()

                if result:
                    ref, prix, article_points = result
                    if ref in articles:
                        articles[ref]['quantite'] += 1
                    else:
                        articles[ref] = {'prix': prix, 'points': article_points, 'quantite': 1}
                    conn.sendall("Article existe.\n".encode('utf-8'))
                else:
                    conn.sendall("Article invalide.\n".encode('utf-8'))

            # Calcul du total et des points
            for ref, data in articles.items():
                prix = Decimal(data['prix'])
                quantite = data['quantite']

                cursor.execute(
                    """
                    INSERT INTO achete (id_article, id_client, quantite_achetee, date_achat, heure_achat)
                    VALUES ((SELECT reference_produit FROM article WHERE reference_produit = %s), (SELECT id_client FROM client WHERE id_carte = %s), %s, CURRENT_DATE, CURRENT_TIME);
                    """,
                    (ref, card_number, quantite)
                )

                total += prix * Decimal(quantite)
                pts += data['points'] * quantite

            conn.sendall(f"Le montant total de vos achats est de : {total:.2f} euros.\n".encode('utf-8'))

            cursor.execute("SELECT points FROM client WHERE id_carte = %s", (card_number,))
            client_points = cursor.fetchone()[0]

            # Application des réductions en fonction des points
            if client_points >= 25 and total > 50:
                if client_points < 50:
                    total *= Decimal(0.9)
                    cursor.execute("UPDATE client SET points = 0 WHERE id_carte = %s", (card_number,))
                    conn.sendall(f"Reduction de 10%. Montant apres reduction : {total:.2f} euros.\n".encode('utf-8'))
                elif client_points < 100:
                    total *= Decimal(0.8)
                    cursor.execute("UPDATE client SET points = 0 WHERE id_carte = %s", (card_number,))
                    conn.sendall(f"Reduction de 20%. Montant apres reduction : {total:.2f} euros.\n".encode('utf-8'))
                else:
                    total *= Decimal(0.7)
                    cursor.execute("UPDATE client SET points = 0 WHERE id_carte = %s", (card_number,))
                    conn.sendall(f"Reduction de 30%. Montant apres reduction : {total:.2f} euros.\n".encode('utf-8'))
            else:
                cursor.execute("UPDATE client SET points = points + %s WHERE id_carte = %s", (pts, card_number))
                conn.sendall(f"Pas de reduction. Points supplementaires : {pts}. Montant total : {total:.2f} euros.\n".encode('utf-8'))

            # Validation
            while True:
                action = read_with_timeout(conn)
                if action is None:
                    logging.info("Timeout atteint sans saisie de commande.")
                    break

                action = action.upper()

                if action == 'VALIDER':
                    cursor.execute(
                        """
                        INSERT INTO facture (points_cumules, montant_facture, date_facture, heure_facture, id_client, id_magasin)
                        VALUES (%s, %s, CURRENT_DATE, CURRENT_TIME, (SELECT id_client FROM client WHERE id_carte = %s), (SELECT id_magasin FROM msa WHERE id_msa = %s));
                        """,
                        (pts, total, card_number, msa_number)
                    )
                    conn_db.commit()
                    conn.sendall(f"Facture validee. Montant final : {total:.2f} euros.\n".encode('utf-8'))
                    conn.sendall("Connexion terminee. Merci de votre visite.\n".encode('utf-8'))
                    break
                else:
                    conn.sendall("Commande non reconnue. Veuillez taper 'VALIDER'.\n".encode('utf-8'))

        except Exception as e:
            logging.error(f"Erreur lors de l'exécution du serveur : {e}")
        finally:
            conn.close()
            cursor.close()
            conn_db.close()

    except Exception as e:
        logging.error(f"Erreur du serveur : {e}")
    finally:
        server_socket.close()

if __name__ == "__main__":
    start_server()
