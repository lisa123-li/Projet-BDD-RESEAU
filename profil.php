<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    // Connexion à la base de données
    include_once 'connexionbdd.php';
    $conn = get_db_connection();

    // Récupérer les données de l'utilisateur
    $query = "SELECT * FROM client WHERE email = $1";
    $result = pg_query_params($conn, $query, array($email));

    if ($result && pg_num_rows($result) > 0) {
        $user = pg_fetch_assoc($result);
        $nom = $user['nom'];
        $prenom = $user['prenom'];
        $telephone = $user['telephone'];
        $adresse = $user['adresse'];
        $id_carte = $user['id_carte'];
        $points = $user['points'];
    } else {
        // Rediriger ou afficher un message si l'utilisateur n'est pas trouvé
        die("Utilisateur non trouvé.");
    }

    // Récupérer les factures de l'utilisateur
    $query = "SELECT date_facture, montant_facture FROM facture WHERE id_client = $1";
    $result = pg_query_params($conn, $query, array($user['id_client']));
    $factures = [];
    if ($result) {
        $factures = pg_fetch_all($result);
    }
} else {
    // Rediriger vers la page de connexion si non connecté
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Page Utilisateur</title>
    <style>
        

    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #FFFF;
        
    }

    header {
        background-color: #4CAF50;
        color: white;
        padding: 20px;
        text-align: center;
    }

    h1 {
        margin: 0;
    }

    main {
        margin: 20px auto;
        max-width: 800px;
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
    }

    section {
        margin-bottom: 30px;
    }

    

    ul {
        list-style-type: none;
        padding: 0;
    }

    li {
        margin: 10px 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table th, table td {
        padding: 10px;
        border: 1px solid #ccc;
        text-align: left;
    }

    table th {
        background-color: #B18E7E;
        color: white;
    }

    a {
        color: #4CAF50;
        text-decoration: none;
        font-weight: bold;
    }

    a:hover {
        text-decoration: underline;
    }


    </style>
  
</head>
<body>
      <!-- Top bar -->
    <div class="top-bar">
        <div class="social-icons">
            <img src="photo/instagram.png" alt="Instagram">
            <img src="photo/facebook.png" alt="Facebook">
            <img src="photo/twitter.png" alt="Twitter">
        </div>
    </div>
    <?php include 'headerConnect.php'; ?>
    <main>
        <section id="user-info">
            <h2>Informations personnelles</h2>
            <ul>
                <li><strong>Nom :</strong> <?= htmlspecialchars($nom) ?></li>
                <li><strong>Prénom :</strong> <?= htmlspecialchars($prenom) ?></li>
                <li><strong>Téléphone :</strong> <?= htmlspecialchars($telephone) ?></li>
                <li><strong>Adresse :</strong> <?= htmlspecialchars($adresse) ?></li>
                <li><strong>Numéro de carte :</strong> <?= htmlspecialchars($id_carte) ?></li>
                <li><strong>Points cumulés :</strong> <?= htmlspecialchars($points) ?></li>
            </ul>
            <a href="#update-info">Modifier mes informations</a>
        </section>

        <section id="factures">
            <h2>Mes Factures</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($factures)): ?>
                        <?php foreach ($factures as $facture): ?>
                            <tr>
                                <td><?= htmlspecialchars($facture['date_facture']) ?></td>
                                <td><?= htmlspecialchars($facture['montant_facture']) ?> €</td>
                                <td><a href="voir_facture.php?id_facture=<?= urlencode($facture['id_facture']) ?>">Voir</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">Aucune facture trouvée.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

     <!-- Footer -->
     <footer>
        <p>&copy; 2024 Fidélité Shine & Silk. Tous droits réservés.</p>
    </footer>

</body>
</html>
