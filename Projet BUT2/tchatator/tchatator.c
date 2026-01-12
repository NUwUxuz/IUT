#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <arpa/inet.h>
#include <stdbool.h>
#include <postgresql/libpq-fe.h>


#define PORT 8888
#define BUFFER_SIZE 1024
#define SIZE_KEY 64

// Structure pour stocker les clés d'API
typedef struct {
    char key[SIZE_KEY];
    char role[16]; // "client", "professional", "admin"
} APIKey;

// Exemple de clés d'API stockées en mémoire (pour le test)
APIKey api_keys[] = {
    {"client_api_key", "client"},
    {"professional_api_key", "professional"},
    {"admin_api_key", "admin"}
};

// Fonction pour valider une clé d'API
const char* validate_api_key(const char* api_key) {
    for (int i = 0; i < sizeof(api_keys) / sizeof(APIKey); i++) {
        if (strcmp(api_keys[i].key, api_key) == 0) {
            return api_keys[i].role;
        }
    }
    return NULL;
}

// Fonction pour traiter une requête client
void handle_client_request(int client_socket) {
    char buffer[BUFFER_SIZE];
    int bytes_read = read(client_socket, buffer, BUFFER_SIZE);
    if (bytes_read <= 0) {
        printf("Erreur lors de la lecture de la requête\n");
        close(client_socket);
        return;
    }

    buffer[bytes_read] = '\0';
    printf("Requête reçue : %s\n", buffer);

    // Analyse de la clé d'API
    char api_key[64];
    sscanf(buffer, "LOGIN:%63s", api_key);

    const char* role = validate_api_key(api_key);
    if (role) {
        char response[BUFFER_SIZE];
        snprintf(response, BUFFER_SIZE, "200/OK: Bienvenue, rôle : %s\n", role);
        write(client_socket, response, strlen(response));
    } else {
        const char* response = "403/DENIED: Clé API invalide\n";
        write(client_socket, response, strlen(response));
    }

    close(client_socket);
}

int main() {
    // Chaîne de connexion : adapter avec vos informations
    const char *conninfo = "host=lppdt.ventsdouest.dev port=5432 dbname=sae user=sae password=brute-quint3-jOUrnaux";
    
    // Créer la connexion
    PGconn *conn = PQconnectdb(conninfo);

    // Vérifier la connexion
    if (PQstatus(conn) != CONNECTION_OK) {
        fprintf(stderr, "Erreur de connexion à la base de données : %s", PQerrorMessage(conn));
        PQfinish(conn);
        return 1;
    }

    printf("Connexion réussie !\n");

    // Exemple : Exécuter une requête
    PGresult *res = PQexec(conn, "SELECT version()");
    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        fprintf(stderr, "Erreur lors de l'exécution de la requête : %s", PQerrorMessage(conn));
        PQclear(res);
        PQfinish(conn);
        return 1;
    }

    // Récupérer les clés d'API dans la relation pact.compte
    res = PQexec(conn, "SELECT clefApi, role FROM pact.compte");
    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        fprintf(stderr, "Erreur lors de l'exécution de la requête : %s", PQerrorMessage(conn));
        PQclear(res);
        PQfinish(conn);
        return 1;
    }

    // Stocker les clés d'API récupérées dans la structure api_keys
    int nrows = PQntuples(res);
    for (int i = 0; i < nrows; i++) {
        strncpy(api_keys[i].key, PQgetvalue(res, i, 0), SIZE_KEY - 1);
        strncpy(api_keys[i].role, PQgetvalue(res, i, 1), sizeof(api_keys[i].role) - 1);
    }

    // Afficher le résultat
    printf("Version de PostgreSQL : %s\n", PQgetvalue(res, 0, 0));

    int server_socket, client_socket;
    struct sockaddr_in server_addr, client_addr;
    socklen_t addr_len = sizeof(client_addr);

    // Création du socket
    if ((server_socket = socket(AF_INET, SOCK_STREAM, 0)) == 0) {
        perror("Erreur lors de la création du socket");
        exit(EXIT_FAILURE);
    }

    server_addr.sin_family = AF_INET;
    server_addr.sin_addr.s_addr = INADDR_ANY;
    server_addr.sin_port = htons(PORT);

    // Liaison du socket à l'adresse et au port
    if (bind(server_socket, (struct sockaddr*)&server_addr, sizeof(server_addr)) < 0) {
        perror("Erreur lors du bind");
        close(server_socket);
        exit(EXIT_FAILURE);
    }

    // Mise en écoute
    if (listen(server_socket, 3) < 0) {
        perror("Erreur lors de l'écoute");
        close(server_socket);
        exit(EXIT_FAILURE);
    }

    printf("Serveur en écoute sur le port %d\n", PORT);

    while (true) {
        // Acceptation des connexions entrantes
        if ((client_socket = accept(server_socket, (struct sockaddr*)&client_addr, &addr_len)) < 0) {
            perror("Erreur lors de l'acceptation");
            continue;
        }

        // Afficher les clés d'API avec le nom du compte correspondant
        for (int i = 0; i < nrows; i++) {
            printf("Clé API : %s, Rôle : %s\n", api_keys[i].key, api_keys[i].role);
        }

        printf("Connexion acceptée\n");
        handle_client_request(client_socket);
    }

    // Libérer les ressources
    PQclear(res);
    PQfinish(conn);
    close(server_socket);
    return 0;
}