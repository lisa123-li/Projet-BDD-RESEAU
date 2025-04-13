#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <winsock2.h>
#include <ctype.h>

#pragma comment(lib, "ws2_32.lib") // Lien avec la bibliotheque Winsock

#define SERVER_IP "127.0.0.1"
#define SERVER_PORT 8081
#define BUFFER_SIZE 256
#define TIMEOUT 120  // Timeout en secondes

// Fonction pour nettoyer les entrees utilisateur
void trim(char* str) {
    char* end;
    while (isspace((unsigned char)*str)) str++;  // Supprimer les espaces en debut
    if (*str == 0) return;                       // Chaine vide
    end = str + strlen(str) - 1;
    while (end > str && isspace((unsigned char)*end)) end--; // Supprimer les espaces en fin
    *(end + 1) = '\0';
}

int main() {
    WSADATA wsa;
    int sock;
    struct sockaddr_in server_addr;
    char buffer[BUFFER_SIZE];
    int bytes_received;
    int has_articles = 0;  // Variable pour verifier si des articles ont ete ajoutes

    // Initialisation de Winsock
    printf("Initialisation de Winsock...\n");
    if (WSAStartup(MAKEWORD(2, 2), &wsa) != 0) {
        printf("Echec de l'initialisation de Winsock. Code d'erreur: %d\n", WSAGetLastError());
        return 1;
    }

    // Creation de la socket
    sock = socket(AF_INET, SOCK_STREAM, 0);
    if (sock == INVALID_SOCKET) {
        printf("Erreur de creation de la socket. Code d'erreur: %d\n", WSAGetLastError());
        WSACleanup();
        return 1;
    }

    // Configuration de l'adresse du serveur
    server_addr.sin_family = AF_INET;
    server_addr.sin_port = htons(SERVER_PORT);
    server_addr.sin_addr.s_addr = inet_addr(SERVER_IP);

    // Connexion au serveur
    if (connect(sock, (struct sockaddr*)&server_addr, sizeof(server_addr)) == SOCKET_ERROR) {
        printf("Erreur de connexion au serveur. Code d'erreur: %d\n", WSAGetLastError());
        closesocket(sock);
        WSACleanup();
        return 1;
    }
    printf("Connecte au serveur.\n");

    // Configuration du timeout pour les operations de reception
    int timeout = TIMEOUT * 1000;  // Timeout en millisecondes
    if (setsockopt(sock, SOL_SOCKET, SO_RCVTIMEO, (char*)&timeout, sizeof(timeout)) == SOCKET_ERROR) {
        printf("Erreur lors de la configuration du timeout. Code d'erreur: %d\n", WSAGetLastError());
        closesocket(sock);
        WSACleanup();
        return 1;
    }

    // Interaction avec le serveur - envoi et validation du numero de MSA
    while (1) {
        printf("Entrez le numero de MSA: ");
        fgets(buffer, sizeof(buffer), stdin);
        trim(buffer);
        send(sock, buffer, strlen(buffer), 0);

        bytes_received = recv(sock, buffer, sizeof(buffer) - 1, 0);
        if (bytes_received == SOCKET_ERROR) {
            printf("Erreur de reception ou timeout. Le serveur peut etre deconnecte.\n");
            break;
        }
        buffer[bytes_received] = '\0';
        printf("Serveur: %s\n", buffer);

        if (strncmp(buffer, "MSA valide", 10) == 0) {
            break;
        }
    }

    // Validation du numero de carte
    while (1) {
        printf("Entrez le numero de carte: ");
        fgets(buffer, sizeof(buffer), stdin);
        trim(buffer);
        send(sock, buffer, strlen(buffer), 0);

        bytes_received = recv(sock, buffer, sizeof(buffer) - 1, 0);
        if (bytes_received == SOCKET_ERROR) {
            printf("Erreur de reception ou timeout. Le serveur peut etre deconnecte.\n");
            break;
        }
        buffer[bytes_received] = '\0';
        printf("Serveur: %s\n", buffer);

        if (strncmp(buffer, "Carte valide", 12) == 0) {
            break;
        }
    }

    // Ajout des articles
    while (1) {
        printf("Entrez la reference de l'article (ou 'FIN' pour terminer): ");
        fgets(buffer, sizeof(buffer), stdin);
        trim(buffer);
        send(sock, buffer, strlen(buffer), 0);

        if (strncmp(buffer, "FIN", 3) == 0) {
            break;
        }

        bytes_received = recv(sock, buffer, sizeof(buffer) - 1, 0);
        if (bytes_received == SOCKET_ERROR) {
            printf("Erreur de reception ou timeout. Le serveur peut etre deconnecte.\n");
            break;
        }
        buffer[bytes_received] = '\0';
        printf("Serveur: %s\n", buffer);

        // Si un article a ete ajoute, mettre a jour la variable has_articles
        if (strncmp(buffer, "Article existe", 14) == 0) {
            has_articles = 1;
        }
    }

    // Si aucun article n'a ete ajoute, afficher le message et ne pas continuer a l'etape suivante
    if (!has_articles) {
        printf("Serveur: Aucun article ajoute, connexion terminee.\n");
        closesocket(sock);
        WSACleanup();
        return 0;
    }

    // Reception du montant total
    bytes_received = recv(sock, buffer, sizeof(buffer) - 1, 0);
    if (bytes_received == SOCKET_ERROR) {
        printf("Erreur de reception ou timeout. Le serveur peut etre deconnecte.\n");
        closesocket(sock);
        WSACleanup();
        return 1;
    }
    buffer[bytes_received] = '\0';
    printf("Serveur: %s\n", buffer);  // Affiche le montant total des achats

    // Validation ou annulation de la transaction
    while (1) {
        printf("Entrez 'VALIDER' pour confirmer: ");
        fgets(buffer, sizeof(buffer), stdin);
        trim(buffer);
        send(sock, buffer, strlen(buffer), 0);

        bytes_received = recv(sock, buffer, sizeof(buffer) - 1, 0);
        if (bytes_received == SOCKET_ERROR) {
            printf("Erreur de reception ou timeout. Le serveur peut etre deconnecte.\n");
            break;
        }
        buffer[bytes_received] = '\0';
        printf("Serveur: %s\n", buffer);

        if (strncmp(buffer, "Facture validee", 14) == 0 || 
            strncmp(buffer, "Connexion terminee", 18) == 0) {
            break;
        }
    }
     // Appliquer la reduction, si applicable
    bytes_received = recv(sock, buffer, sizeof(buffer) - 1, 0);
    buffer[bytes_received] = '\0';
    printf("Serveur: %s\n", buffer);  // Affiche la reduction si elle est appliquee
    
    // Fermeture de la connexion
    closesocket(sock);
    WSACleanup();
    printf("Connexion fermee.\n");

    return 0;
}
