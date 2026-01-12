#include <errno.h>
#include <netdb.h>
#include <stdio.h>
#include <string.h>
#include <sys/socket.h>
#include <sys/types.h>
#include <unistd.h>
#include <stdlib.h>
#include <postgresql/libpq-fe.h>
#include <stdbool.h>
#include <sys/select.h>  // Pour select()


#define PORT 4242
#define BUFSIZE 4096

static char user_type[BUFSIZE] = "";

void set_user_type(char *type) {
    strncpy(user_type, type, BUFSIZE);
    user_type[strcspn(user_type, "\n")] = '\0';
}

void send_message(int socket_fd, char *api_key) {
    char message[BUFSIZE];
    char buffer[BUFSIZE];  // Tampon pour le message

    printf("Your message: ");
    fgets(message, BUFSIZE, stdin);
    message[strcspn(message, "\n")] = '\0';

    // Copier le message dans le buffer
    snprintf(buffer, sizeof(buffer), "%s\n", message);

    // Envoyer toutes les données en une seule fois
    send(socket_fd, "SEND", 4, 0);  // Envoi du préfixe "SEND"  
    send(socket_fd, buffer, strlen(buffer), 0);

}



int receive_messages(int socket_fd, char *api_key) {
    char username[BUFSIZE], buffer[BUFSIZE];
    int bytes_read;

    strncpy(username, api_key, BUFSIZE);
    username[strcspn(username, "\n")] = '\0';


    send(socket_fd, "RECEIVE", 7, 0);
    // send(socket_fd, username, strlen(username), 0);

    system("clear");

    while (1) {
        // Réception des messages
        printf("\n\n");
        printf("Messages for you:\n\n");
        while ((bytes_read = recv(socket_fd, buffer, BUFSIZE - 1, 0)) > 0) {
            buffer[bytes_read] = '\0';

            // Vérifier la fin du bloc
            if (strcmp(buffer, "END_OF_BLOCK") == 0) {
                break; // Fin des messages, on passe aux interactions utilisateur
            }

            printf("%s", buffer);
        }
        printf("\n");

        // Interaction utilisateur
        char choice[BUFSIZE];
        printf("\n\n\n============================= MENU ==============================\n∥\t\t\t\t\t\t\t\t∥\n");
        printf("∥          - '+'\t TO SEE THE PREVIOUS MESSAGES\t\t∥\n∥          - 'CHANGE'\t TO MODIFIY A PREVIOUS MESSAGE\t\t∥\n∥          - 'DELETE'\t TO DELETE A PREVIOUS MESSAGE\t\t∥\n∥          - '-'\t TO RETURN TO THE HOME MENU\t\t∥\n");
        printf("∥\t\t\t\t\t\t\t\t∥\n=================================================================\n");
        printf("\n>> ");
        fgets(choice, BUFSIZE, stdin);
        choice[strcspn(choice, "\n")] = '\0';

        if (choice[0] == '+') {
            int message_id;
            printf("Enter message ID: ");
            scanf("%d", &message_id);
            getchar(); // Consommer le caractère '\n' laissé par scanf

            send(socket_fd, "+", 1, 0);
            char message_id_str[BUFSIZE];
            snprintf(message_id_str, BUFSIZE, "%d", message_id);
            send(socket_fd, message_id_str, strlen(message_id_str), 0);
            system("clear");
        } else if (choice[0] == '-') {
            send(socket_fd, "-", 1, 0);
            break; // Quitter la boucle
        } else if (strcmp(choice, "CHANGE") == 0) {
            printf("Enter the message ID you want to change: ");
            int message_id;
            scanf("%d", &message_id);
            getchar(); // Consommer le caractère '\n'

            // Préparer la commande CHANGE avec l'ID
            char command[BUFSIZE];
            snprintf(command, BUFSIZE, "CHANGE %d", message_id);
            send(socket_fd, command, strlen(command), 0);

            printf("Enter the new message: ");
            char new_message[BUFSIZE];
            fgets(new_message, BUFSIZE, stdin);
            new_message[strcspn(new_message, "\n")] = '\0';

            // Envoyer le nouveau message
            send(socket_fd, new_message, strlen(new_message), 0);

            // Recevoir la réponse
            char response[BUFSIZE];
            int response_bytes_read = recv(socket_fd, response, BUFSIZE - 1, 0);
            response[response_bytes_read] = '\0';
            printf("\n%s\n", response);
            

        } else if(strcmp(choice, "DELETE") == 0) {
            printf("Enter the message ID you want to delete: ");
            int message_id;
            scanf("%d", &message_id);
            getchar(); // Consommer le caractère '\n' laissé par scanf

            // Préparer la commande SUPPR avec l'ID
            char command[BUFSIZE];
            snprintf(command, BUFSIZE, "DELETE %d", message_id);
            send(socket_fd, command, strlen(command), 0);

            // Recevoir la réponse
            char response[BUFSIZE];
            int response_bytes_read = recv(socket_fd, response, BUFSIZE - 1, 0);
            response[response_bytes_read] = '\0';
            printf("\n%s\n", response);

            system("clear");
            
        } else {
            printf("Invalid command.\n");
            return -1;
        }
    }
    return 0;
}



void print_menu_message() {
    system("clear");
    printf("\n\n");
    printf("========================= MENU ==========================\n∥\t\t\t\t\t\t\t∥\n");
    printf("∥          - 'SEND'\t TO SEND A MESSAGE      \t∥\n∥          - 'RECEIVE'\t TO FETCH MESSAGES      \t∥\n∥          - 'QUIT'\t TO RETURN TO THE CONV MENU\t∥\n");
    printf("∥\t\t\t\t\t\t\t∥\n=========================================================\n");
    printf("\n>> ");
}

void print_menu_message_pro() {
    system("clear");
    printf("\n\n");
    printf("========================= MENU ==========================\n∥\t\t\t\t\t\t\t∥\n");
    printf("∥          - 'SEND'\t   TO SEND A MESSAGE      \t∥\n∥          - 'RECEIVE'\t   TO FETCH MESSAGES      \t∥\n∥          - 'QUIT'\t   TO RETURN TO THE CONV MENU\t∥\n∥          - TAP 'BLOCK'   TO BLOCK THE USER FOR 24H\t∥\n∥          - TAP 'UNBLOCK' TO UNBLOCK THE USER\t\t∥\n");
    printf("∥\t\t\t\t\t\t\t∥\n=========================================================\n");
    printf("\n>> ");
}

void menu_message(char choice[BUFSIZE], int socket_fd, char api_key[BUFSIZE], bool blocked) {
    int result;
    
    while (1) {
        system("clear");
        // Recevoir le type d'utilisateur (MEMBRE, PRO, ADMIN)

        if (strcmp(user_type, "MEMBRE") == 0) {
            print_menu_message();
        } else if (strcmp(user_type, "PRO") == 0) {
            print_menu_message_pro();
        } else if (strcmp(user_type, "ADMIN") == 0) {
            print_menu_message();
        } else {
            printf("Unknown user type.\n");
            return;
        }

        if (blocked && strcmp(user_type, "PRO") == 0) {
            printf("This conversation is blocked ! You can only UNBLOCK the conversation or QUIT to change the conversation.\n");

            printf("\n>> ");
        } else if (blocked && strcmp(user_type, "MEMBRE") == 0) {
            printf("This conversation is blocked ! You can only QUIT to change the conversation.\n");

            printf("\n>> ");
        }
        // printf("User type: %s\n", user_type);
        fgets(choice, BUFSIZE, stdin);
        choice[strcspn(choice, "\n")] = '\0';

        if (strcmp(choice, "SEND") == 0 && !blocked) {
            send_message(socket_fd, api_key);
        } else if (strcmp(choice, "RECEIVE") == 0 && !blocked) {
            result = receive_messages(socket_fd, api_key);
            while (result == -1) {
                print_menu_message();
                result = receive_messages(socket_fd, api_key);
            }
        } else if(strcmp(choice, "QUIT") == 0) {
            send(socket_fd, "QUIT", 4, 0);
            system("clear");
            break;
        } else if (strcmp(choice, "BLOCK") == 0 && (strcmp(user_type, "PRO") == 0) && !blocked) {
            send(socket_fd, "BLOCK", 5, 0);
            printf("User blocked for 24h.\n");
            break;
        } else if (strcmp(choice, "UNBLOCK") == 0 && (strcmp(user_type, "PRO") == 0) && blocked) {
            send(socket_fd, "UNBLOCK", 7, 0);
            printf("User unblocked.\n");
            break;

        } else {
            printf("Invalid choice.\n");
        }
    }
}

void print_menu_conv() {
    system("clear");
    printf("\n\n");
    printf("========================= MENU ==========================\n∥\t\t\t\t\t\t\t∥\n");
    printf("∥          - TAP THE ID\t TO CHOOSE THE CONV      \t∥\n∥          - TAP 'NEW'\t TO CREATE A NEW CONV      \t∥\n∥          - TAP 'QUIT'\t TO QUIT THE SERVICE     \t∥\n");
    printf("∥\t\t\t\t\t\t\t∥\n=========================================================\n");
}

void print_menu_conv_admin() {
    system("clear");
    printf("\n\n");
    printf("========================= MENU ==========================\n∥\t\t\t\t\t\t\t∥\n");
    printf("∥          - TAP THE ID\t   TO CHOOSE THE CONV      \t∥\n∥          - TAP 'NEW'\t   TO CREATE A NEW CONV      \t∥\n∥          - TAP 'QUIT'\t   TO QUIT THE SERVICE     \t∥\n∥          - TAP 'BLOCK'   TO BLOCK THE USER FOR 24H\t∥\n∥          - TAP 'BAN'     TO BAN THE USER   \t\t∥\n∥          - TAP 'UNBLOCK' TO UNBLOCK THE USER   \t∥\n∥          - TAP 'UNBAN'   TO UNBAN THE USER   \t\t∥\n");
    printf("∥\t\t\t\t\t\t\t∥\n=========================================================\n");
}



int menu_conv(char choice[BUFSIZE], int socket_fd, char api_key[BUFSIZE]) {
    char buffer[BUFSIZE];
    int bytes_read;
    fd_set read_fds;
    struct timeval timeout;

    while (1) {
        // Afficher le menu en fonction du type d'utilisateur
        if (strcmp(user_type, "MEMBRE") == 0) {
            print_menu_conv();
        } else if (strcmp(user_type, "PRO") == 0) {
            print_menu_conv();
        } else if (strcmp(user_type, "ADMIN") == 0) {
            print_menu_conv_admin();
        } else {
            printf("Unknown user type.\n");
            return -1;
        }
        printf("\n\n");

        // Initialisation du set de descripteurs
        FD_ZERO(&read_fds);
        FD_SET(socket_fd, &read_fds);

        // Délai d'attente de 5 secondes max
        timeout.tv_sec = 5;
        timeout.tv_usec = 0;

        // Vérifier si des données sont disponibles
        int ready = select(socket_fd + 1, &read_fds, NULL, NULL, &timeout);
        if (ready == -1) {
            perror("select");
            return -1;
        } else if (ready == 0) {
            printf("Aucune donnée reçue (timeout).\n");
        } else {
            // Des données sont disponibles, on peut appeler recv()
            while ((bytes_read = recv(socket_fd, buffer, BUFSIZE - 1, 0)) > 0) {
                buffer[bytes_read] = '\0';

                // Vérifier la fin du bloc
                if (strcmp(buffer, "END_OF_BLOCK") == 0) {
                    break;
                }

                printf("%s", buffer);
            }

            if (bytes_read == 0) {
                printf("Connexion fermée par le serveur.\n");
                close(socket_fd);
                return -1;
            } else if (bytes_read < 0) {
                perror("recv");
            }
        }

        printf("\n");
        printf("CHOOSE YOUR CONVERSATION WITH THE ID OF THE CONVERSATION\n");
        printf("OR CREATE A NEW CONVERSATION WITH 'NEW'\n");
        printf("\n>> ");
        fgets(choice, BUFSIZE, stdin);
        choice[strcspn(choice, "\n")] = '\0'; // Supprimer le \n

        if (strcmp(choice, "QUIT") == 0) {
            send(socket_fd, "QUIT", 4, 0);
            close(socket_fd);
            return -1;
        }  else if (strcmp(choice, "NEW") == 0) {
            send(socket_fd, "NEW", 3, 0);
            printf("Creating a new conversation...\n");
            printf("Enter the pseudo of the member or the codePro of the professional: \n");
            char pseudo[BUFSIZE];
            fgets(pseudo, BUFSIZE, stdin);
            pseudo[strcspn(pseudo, "\n")] = '\0';
            send(socket_fd, pseudo, strlen(pseudo), 0);

            bytes_read = recv(socket_fd, buffer, BUFSIZE - 1, 0);
            if (bytes_read > 0) {
                buffer[bytes_read] = '\0';
                printf("Server response: %s\n", buffer);
            } else {
                perror("recv");
            }

            if (strcmp(buffer, "Invalid conversation informations.") == 0) {
                printf("Invalid conversation informations.\n");
            } else {
                char message[BUFSIZE];
                char buffer[BUFSIZE];  // Tampon pour le message

                printf("Your message: ");
                fgets(message, BUFSIZE, stdin);
                message[strcspn(message, "\n")] = '\0';

                // Copier le message dans le buffer
                snprintf(buffer, sizeof(buffer), "%s\n", message);

                // Envoyer toutes les données en une seule fois
                send(socket_fd, buffer, strlen(buffer), 0);
            }

        } else if (strcmp(choice, "BAN") == 0 && strcmp(user_type, "ADMIN") == 0) {
            send(socket_fd, "BAN", 3, 0);
            printf("Enter the pseudo of the user you want to ban: ");
            char pseudo[BUFSIZE];
            fgets(pseudo, BUFSIZE, stdin);
            pseudo[strcspn(pseudo, "\n")] = '\0';
            send(socket_fd, pseudo, strlen(pseudo), 0);

            printf("User banned.\n");
        } else if (strcmp(choice, "BLOCK") == 0 && strcmp(user_type, "ADMIN") == 0) {
            send(socket_fd, "BLOCK", 5, 0);
            printf("Enter the pseudo of the user you want to block: ");
            char pseudo[BUFSIZE];
            fgets(pseudo, BUFSIZE, stdin);
            pseudo[strcspn(pseudo, "\n")] = '\0';
            send(socket_fd, pseudo, strlen(pseudo), 0);
            
            printf("User blocked for 24 hours.\n");
        } else if (strcmp(choice, "UNBLOCK") == 0 && strcmp(user_type, "ADMIN") == 0) {
            send(socket_fd, "UNBLOCK", 7, 0);
            printf("Enter the pseudo of the user you want to unblock: ");
            char pseudo[BUFSIZE];
            fgets(pseudo, BUFSIZE, stdin);
            pseudo[strcspn(pseudo, "\n")] = '\0';
            send(socket_fd, pseudo, strlen(pseudo), 0);

            printf("User unblocked.\n");

        } else if (strcmp(choice, "UNBAN") == 0 && strcmp(user_type, "ADMIN") == 0) {
            send(socket_fd, "UNBAN", 5, 0);
            printf("Enter the pseudo of the user you want to unban: ");
            char pseudo[BUFSIZE];
            fgets(pseudo, BUFSIZE, stdin);
            pseudo[strcspn(pseudo, "\n")] = '\0';
            send(socket_fd, pseudo, strlen(pseudo), 0);

            printf("User unbanned.\n");

        } else {
            send(socket_fd, choice, strlen(choice), 0);
            recv(socket_fd, buffer, BUFSIZE - 1, 0);
            if (strstr(buffer, "blocked") != NULL) {
                if (strstr(buffer, "not") != NULL) {
                    menu_message(choice, socket_fd, api_key, false);
                    continue;
                } else {
                    menu_message(choice, socket_fd, api_key, true);
                }
            } else {
                printf("buffer: %s aaaa \n", buffer);
                printf("Invalid choice.\n");
                break;
            }
        }
    }
}




int main(void) {
    struct sockaddr_in sa;
    int socket_fd;
    int status;
    char choice[BUFSIZE];



    printf("---- CLIENT ----\n\n");

    memset(&sa, 0, sizeof sa);
    sa.sin_family = AF_INET;
    sa.sin_addr.s_addr = htonl(INADDR_LOOPBACK); // 127.0.0.1
    sa.sin_port = htons(PORT);

    socket_fd = socket(sa.sin_family, SOCK_STREAM, 0);
    if (socket_fd == -1) {
        perror("socket");
        return 1;
    }

    status = connect(socket_fd, (struct sockaddr *)&sa, sizeof sa);
    if (status != 0) {
        perror("connect");
        return 2;
    }
    printf("Connected to server.\n");

    printf("LOGIN YOUSELF WITH YOUR API KEY\n");
    char api_key[BUFSIZE];
    printf("API KEY: ");
    fgets(api_key, BUFSIZE, stdin);
    send(socket_fd, api_key, strlen(api_key), 0);

    int bytes_received = recv(socket_fd, choice, BUFSIZE - 1, 0);
    if (bytes_received < 0) {   
        perror("recv");
        close(socket_fd);
        return 3;
    }
    choice[bytes_received] = '\0';
    printf("Received: %s\n", choice);

    if (strcmp(choice, "API_KEY_INVALID") == 0) {
        printf("API key invalid.\n");
        close(socket_fd);
        return 3;
    } else if (strcmp(choice, "API_KEY_VALID") == 0) {
        printf("API key valid.\n");

        if (strcmp(user_type, "") == 0) {
        char temp[BUFSIZE];
        int bytes_received = recv(socket_fd, temp, BUFSIZE - 1, 0);
        if (bytes_received < 0) {
            perror("recv");
            close(socket_fd);
            return -1;
        }
        temp[bytes_received] = '\0';
        set_user_type(temp);
    }

        recv(socket_fd, choice, BUFSIZE - 1, 0);
        printf("%s\n", choice);
        if (strcmp(choice, "You are connected.\n") == 0) {
            while (1) {
                if (menu_conv(choice, socket_fd, api_key) == -1) {
                    break;
                }
            }
        } else {
            close(socket_fd);
            return 0;
        }
        
    } else {
        printf("Unknown response from server.\n");
        close(socket_fd);
        return 4;
    }


    close(socket_fd);
    return 0;
}