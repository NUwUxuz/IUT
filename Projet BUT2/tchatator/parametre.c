#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <signal.h>
#include <unistd.h>

#define CONFIG_FILE "config.txt"

// Variables globales
int port, backlog, bufsize, len_msg_history, ban_duration;
int max_messages_per_min, max_msg_size, buffer_size, size_key;

// Fonction pour charger la config depuis le fichier
void load_config() {
    FILE *file = fopen(CONFIG_FILE, "r");
    if (!file) {
        perror("Erreur d'ouverture du fichier de configuration");
        return;
    }

    char line[256];

    while (fgets(line, sizeof(line), file)) {
        // Supprimer les espaces et sauts de ligne en fin de ligne
        line[strcspn(line, "\r\n")] = 0;

        // Ignorer les lignes vides
        if (strlen(line) == 0) continue;

        // Ignorer les commentaires
        if (line[0] == '#') continue;

        char key[50];
        int value;

        // Extraction de la clé et de la valeur
        if (sscanf(line, "%49[^=]=%d", key, &value) == 2) {
            if (strcmp(key, "PORT") == 0) port = value;
            else if (strcmp(key, "BACKLOG") == 0) backlog = value;
            else if (strcmp(key, "BUFSIZE") == 0) bufsize = value;
            else if (strcmp(key, "LEN_MSG_HISTORY") == 0) len_msg_history = value;
            else if (strcmp(key, "BAN_DURATION") == 0) ban_duration = value;
            else if (strcmp(key, "MAX_MESSAGES_PER_MIN") == 0) max_messages_per_min = value;
            else if (strcmp(key, "MAX_MSG_SIZE") == 0) max_msg_size = value;
            else if (strcmp(key, "BUFFER_SIZE") == 0) buffer_size = value;
            else if (strcmp(key, "SIZE_KEY") == 0) size_key = value;
        }
    }

    fclose(file);
    printf("Configuration rechargée avec succès !\n");
}

// Fonction pour recharger la config
void signal_handler(int signum) {
    if (signum == SIGHUP) {
        printf("Signal SIGHUP reçu, rechargement de la configuration...\n");
        load_config();
    }
}

int main() {
    // Charger la config au démarrage
    load_config();

    // Capturer le signal SIGHUP
    signal(SIGHUP, signal_handler);

    // Boucle de test
    while (1) {
        printf("Serveur en écoute sur le port %d...\n", port);
        sleep(10);
    }

    return 0;
}

// Compilation : gcc parametre.c -o parametre
// Exécution : ./parametre

// Pour recharger la configuration, il suffit d'envoyer le signal SIGHUP au processus
// ps -a
// kill -SIGHUP <pid du processus>