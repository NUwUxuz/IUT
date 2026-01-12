#include <errno.h>
#include <arpa/inet.h>
#include <stdio.h>
#include <string.h>
#include <sys/socket.h>
#include <sys/types.h>
#include <unistd.h>
#include <stdlib.h>
#include <postgresql/libpq-fe.h>
#include <iconv.h>
#include <netdb.h>
#include <netinet/tcp.h>
#include <netinet/in.h>
#include <stdbool.h>
#include <time.h>
#include <stdarg.h>
#include <getopt.h>
#include <signal.h>



#define LOGFILE_PATH "./SERVICE.log"
#define CONFIG_FILE "PARAMETRES.txt"

static const int LIST_ADMIN_KEY[] = {13};

// Variables globales pour les paramètres
char *PORT;
int BACKLOG, BUFSIZE, LEN_MSG_HISTORY, BAN_DURATION;
int MAX_MESSAGES, MAX_MSG_SIZE, BUFFER_SIZE, SIZE_KEY;


// ============================
// Prototypes de fonctions
// ============================
char *convert_to_utf8(const char *input, const char *from_encoding);
int find_discussion(PGconn *conn, int idClient, int idPro, int idEmmeteur, int idReceveur);
void fetch_all_data(PGconn *conn);
void clear_tables(PGconn *conn);
void save_message(PGconn *conn, int idClient, int idPro, int idEmmeteur, int idReceveur, const char *message_text, int client_fd);
void send_user_messages(PGconn *conn, int user_id, int client_fd, int max_messages, int message_id, int id_conv);
int modifier_message(PGconn *conn, int user_id, int idMessage, const char *nouveau_texte);
int supprimer_message(PGconn *conn, int user_id, int idMessage);
const char *verifier_type_compte(PGconn *conn, int id_compte);
int verif_connexion_apikey(PGconn *conn, char *api_key);
void send_conversation_list(PGconn *conn, int user_id, int client_fd);
bool validConversation(PGconn *conn, int user_id, int client_fd, char *conversation_id);
int recup_receiver(PGconn *conn, int id_user, int idConv);
void write_log(const char *logfile, int client_id, const char *client_ip, const char *format, ...);
char *verifier_type_compte_MENU(PGconn *conn, int static_api_key);
int block_user(PGconn *conn, int idPro, int idClient, const char *type_compte);
int ban_user(PGconn *conn, char *pseudo);
void create_discussion(PGconn *conn, int idClient, int idPro);
int chercheIDMembre(PGconn *conn,char *pseudo, int client_fd);
int chercheIDPro(PGconn *conn, int codePro, int client_fd);
bool estBloque(PGconn *conn, int id);
bool estBan(PGconn *conn, int id);
void load_config();
void signal_handler(int signum);
int unblock_user(PGconn *conn, int idPro, int idClient, const char *type_compte);
int unban_user(PGconn *conn, char *pseudo);


// Variable `static` pour garder l'état de --verbose
static int verbose_mode = 0;
static char ip_client[INET_ADDRSTRLEN] = "";
static int static_api_key = -1;

// Fonction pour activer/désactiver le mode verbose
void set_verbose_mode(int value) {
    verbose_mode = value;
}

void set_ip_client(char *ip) {
    strncpy(ip_client, ip, INET_ADDRSTRLEN);
    ip_client[INET_ADDRSTRLEN - 1] = '\0'; // Ensure null termination
}

void set_api_key(int key) {
    static_api_key = key;
}


// ============================
// Fonction principale: main
// Gère la boucle principale du serveur et la communication client
// ============================
int main(int argc, char *argv[]) {
    load_config();
    int opt;
    static struct option long_options[] = {
        {"verbose", no_argument, NULL, 'v'}, 
        {"help", no_argument, NULL, 'h'}, 
        {0, 0, 0, 0}
    };

    while ((opt = getopt_long(argc, argv, "vh", long_options, NULL)) != -1) {
        if (opt == 'v') {
            set_verbose_mode(1);  // Active le mode verbose
        } else if (opt == 'h') {
            printf("Usage: %s [OPTIONS]\n", argv[0]);
            printf("Options:\n");
            printf("  -v, --verbose   Enable verbose mode\n");
            printf("  -h, --help      Display this help message\n");
            return 0;
        } else {
            fprintf(stderr, "Try '%s --help' for more information.\n", argv[0]);
            return 1;
        }
    }

    struct addrinfo hints, *res;
    int socket_fd, client_fd;
    struct sockaddr_storage client_addr;
    socklen_t addr_size;
    char buffer[BUFSIZE];
    int bytes_read;
    int ret;


    write_log(LOGFILE_PATH, static_api_key, ip_client, "Server started.");

    const char *conninfo = "host=lppdt.ventsdouest.dev port=5432 dbname=sae user=sae password=brute-quint3-jOUrnaux";
    PGconn *conn = PQconnectdb(conninfo);

    if (PQstatus(conn) != CONNECTION_OK) {
        fprintf(stderr, "Connection to database failed: %s\n", PQerrorMessage(conn));
        PQfinish(conn);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Connection to the database failed.");
        return 1;
    }

    write_log(LOGFILE_PATH, static_api_key, ip_client, "Connected to the database successfully.");

    // printf("Connected to the database successfully.\n");

    memset(&hints, 0, sizeof hints);
    hints.ai_family = AF_UNSPEC;
    hints.ai_socktype = SOCK_STREAM;
    hints.ai_flags = AI_PASSIVE;

    if (getaddrinfo(NULL, PORT, &hints, &res) != 0) {
        perror("getaddrinfo");
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error getting address info.");
        return 1;
    }

    socket_fd = socket(res->ai_family, res->ai_socktype, res->ai_protocol);
    if (socket_fd == -1) {
        perror("socket");
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error creating socket.");
        return 2;
    }

    if (bind(socket_fd, res->ai_addr, res->ai_addrlen) != 0) {
        perror("bind");
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error binding socket.");
        return 3;
    }


    listen(socket_fd, BACKLOG);

    write_log(LOGFILE_PATH, static_api_key, ip_client, "Server is listening on port %s", PORT);
    // printf("Server is listening on port %s\n", PORT);

    while (1) {
        addr_size = sizeof client_addr;
        client_fd = accept(socket_fd, (struct sockaddr *)&client_addr, &addr_size);
        if (client_fd == -1) {
            perror("accept");
            write_log(LOGFILE_PATH, static_api_key, ip_client, "Error accepting client connection.");
            continue;
        }

        struct sockaddr_in *client_in = (struct sockaddr_in *)&client_addr;
        inet_ntop(AF_INET, &client_in->sin_addr, ip_client, INET_ADDRSTRLEN);
        set_ip_client(ip_client);
        printf("New client connected.\n");

        write_log(LOGFILE_PATH, static_api_key, ip_client, "New client connected: %s", ip_client);

        fetch_all_data(conn);

        printf("WAITING FOR CLIENT CONNEXION\n");
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Waiting for client connection.");
        bytes_read = recv(client_fd, buffer, BUFSIZE - 1, 0);
        if (bytes_read <= 0) {
            perror("recv");
            close(client_fd);
            write_log(LOGFILE_PATH, static_api_key, ip_client, "Error receiving data from client.");
            continue;
        }
        buffer[bytes_read] = '\0';

        write_log(LOGFILE_PATH, static_api_key, ip_client, "Received api_key from client: %s", buffer);
        // printf("API KEY : %s\n", buffer);

        ret = verif_connexion_apikey(conn, buffer);
        
        if (ret == -1) {
            send(client_fd, "Erreur lors de la vérification de la clé API.\n", 45, 0);
            close(client_fd);
            write_log(LOGFILE_PATH, static_api_key, ip_client, "Error verifying API key.");
            continue;
        } else if (ret == 0) {
            send(client_fd, "API_KEY_INVALID", 15, 0);
            // printf("API key invalid.\n");
            write_log(LOGFILE_PATH, static_api_key, ip_client, "API key invalid.");
            close(client_fd);
            continue;
        } else {
            send(client_fd, "API_KEY_VALID", 13, 0);
            write_log(LOGFILE_PATH, static_api_key, ip_client, "API key valid.");
            // printf("API key valid.\n");


            set_api_key(atoi(buffer));

            char query[BUFSIZE];
            write_log(LOGFILE_PATH, static_api_key, ip_client, "Unlocking the account if 24h passed.");
            snprintf(query, BUFSIZE, "SELECT pact.gestionBlocage(%d);", static_api_key);
            PGresult *res = PQexec(conn, query);
            if (PQresultStatus(res) != PGRES_TUPLES_OK) {
                fprintf(stderr, "Error executing gestionBlocage: %s\n", PQerrorMessage(conn));
                write_log(LOGFILE_PATH, static_api_key, ip_client, "Error executing gestionBlocage.");
                PQclear(res);
                close(client_fd);
                continue;
            }
            PQclear(res);

            const char *account_type = verifier_type_compte_MENU(conn, static_api_key);
            write_log(LOGFILE_PATH, static_api_key, ip_client, "Account type: %s", account_type);
            send(client_fd, account_type, strlen(account_type), 0);

            if (estBloque(conn, static_api_key) || estBan(conn, static_api_key)) {
                if (estBan(conn, static_api_key)) {
                    send(client_fd, "You have been banned by an administrator.\n", 40, 0);
                    write_log(LOGFILE_PATH, static_api_key, ip_client, "User Banned.");
                } else {
                    send(client_fd, "You have been blocked by an admin for 24h.\n", 43, 0);
                    write_log(LOGFILE_PATH, static_api_key, ip_client, "User blocked for 24h by ADMIN.");
                }
                close(client_fd);
                continue;
            } else {
                send(client_fd, "You are connected.\n", 19, 0);
                while (1) {
                    write_log(LOGFILE_PATH, static_api_key, ip_client, "BEGINNING OF MENU");
                    memset(buffer, 0, BUFSIZE);
                    send_conversation_list(conn, static_api_key, client_fd);
                    write_log(LOGFILE_PATH, static_api_key, ip_client, "Conversation list sent.");
                    send(client_fd, "END_OF_BLOCK", strlen("END_OF_BLOCK"), 0);

                    bytes_read = recv(client_fd, buffer, BUFSIZE - 1, 0);
                    if (bytes_read <= 0) {
                        perror("recv");
                        close(client_fd);
                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error receiving id of the conversation selected from client.");
                        break;
                    }
                    // buffer[bytes_read] = '\0';
                    // printf("buffer : %s\n", buffer);
                    // bytes_read = recv(client_fd, buffer, BUFSIZE - 1, 0);

                    buffer[bytes_read] = '\0';
                    write_log(LOGFILE_PATH, static_api_key, ip_client, "Received conversation ID from client: %s", buffer);

                    if (strcmp("QUIT", buffer) == 0) {
                        // printf("Client disconnected.\n");
                        close(client_fd);
                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Client disconnected.");
                        break;
                    } else if (validConversation(conn, static_api_key, client_fd, buffer)) {


                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Conversation selected: %s", buffer);
                        int id_conv = atoi(buffer);

                        while (1) {
                            bytes_read = recv(client_fd, buffer, BUFSIZE - 1, 0);
                            if (bytes_read <= 0) {
                                if (bytes_read == 0) {
                                    // printf("Client disconnected.\n");
                                } else {
                                    perror("recv");
                                }
                                close(client_fd);
                                write_log(LOGFILE_PATH, static_api_key, ip_client, "Error receiving command from client.");
                                break;
                            }
                            buffer[bytes_read] = '\0';
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Received command from client: %s", buffer);

                            if (strcmp(buffer, "SEND") == 0) {
                                write_log(LOGFILE_PATH, static_api_key, ip_client, "Received SEND command from client.");
                                int sender_id, receiver_id;
                                char message[BUFSIZE];

                                bytes_read = recv(client_fd, buffer, BUFSIZE - 1, 0);
                                if (bytes_read <= 0) {
                                    perror("recv");
                                    close(client_fd);
                                    write_log(LOGFILE_PATH, static_api_key, ip_client, "Error receiving message from client.");
                                    break;
                                }
                                buffer[bytes_read] = '\0';
                                write_log(LOGFILE_PATH, static_api_key, ip_client, "Received message from client: %s", buffer);

                                strcpy(message, buffer);
                                receiver_id = recup_receiver(conn, static_api_key, id_conv);
                                write_log(LOGFILE_PATH, static_api_key, ip_client, "Receiver ID: %d", receiver_id);
                                sender_id = static_api_key;
                                write_log(LOGFILE_PATH, static_api_key, ip_client, "Sender ID: %d", sender_id);
                                // printf("Message from %d to %d: %s\n", sender_id, receiver_id, message);

                                int idPro = -1;
                                int idClient = -1;

                                const char *sender_type = verifier_type_compte(conn, sender_id);
                                write_log(LOGFILE_PATH, static_api_key, ip_client, "Sender type: %s", sender_type);
                                const char *receiver_type = verifier_type_compte(conn, receiver_id);
                                write_log(LOGFILE_PATH, static_api_key, ip_client, "Receiver type: %s", receiver_type);

                                if (!sender_type || !receiver_type || strcmp(sender_type, "Inconnu") == 0 || strcmp(receiver_type, "Inconnu") == 0) {
                                    send(client_fd, "Erreur : ID de compte invalide.\n", 30, 0);
                                    write_log(LOGFILE_PATH, static_api_key, ip_client, "ID de compte receveur invalide.");
                                    continue;
                                } else if (strcmp(sender_type, "Professionnel") == 0) {
                                    write_log(LOGFILE_PATH, static_api_key, ip_client, "Sender is a professional.");
                                    idPro = sender_id;
                                    idClient = receiver_id;
                                } else {
                                    write_log(LOGFILE_PATH, static_api_key, ip_client, "Sender is a client.");
                                    idClient = sender_id;
                                    idPro = receiver_id;
                                }

                                save_message(conn, idClient, idPro, sender_id, receiver_id, message, client_fd);
                                send(client_fd, "Message saved.\n", 16, 0);
                                write_log(LOGFILE_PATH, static_api_key, ip_client, "Message saved.");
                                
                                
                            } else if (strcmp(buffer, "RECEIVE") == 0) {
                                // int user_id;
                                // recv(client_fd, buffer, BUFSIZE - 1, 0);
                                // sscanf(buffer, "%d", &user_id);

                                send_user_messages(conn, static_api_key, client_fd, LEN_MSG_HISTORY, 0, id_conv);
                                write_log(LOGFILE_PATH, static_api_key, ip_client, "Messages sent to client.");
                                // Indicateur de fin d'envoi
                                send(client_fd, "END_OF_BLOCK", strlen("END_OF_BLOCK"), 0);

                                while (1) {
                                    recv(client_fd, buffer, BUFSIZE - 1, 0);
                                    write_log(LOGFILE_PATH, static_api_key, ip_client, "Received command from client for RECEIVE MENU: %s", buffer);
                                    if (buffer[0] == '+') {
                                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Received '+' command from client.");
                                        int message_id;
                                        char message_id_str[BUFSIZE];
                                        recv(client_fd, message_id_str, BUFSIZE - 1, 0);
                                        message_id = atoi(message_id_str);
                                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Message ID: %d", message_id);

                                        // printf("Fetching messages before message ID %d\n", message_id);
                                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Fetching messages before message ID %d", message_id);
                                        send_user_messages(conn, static_api_key, client_fd, LEN_MSG_HISTORY, message_id, id_conv);
                                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Messages sent to client.");
                                        send(client_fd, "END_OF_BLOCK", strlen("END_OF_BLOCK"), 0);

                                    } else if (strncmp(buffer, "CHANGE", 6) == 0) {
                                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Received CHANGE command from client.");
                                        int message_id;
                                        sscanf(buffer + 7, "%d", &message_id); // Extraire l'ID après CHANGE
                                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Message ID to change: %d", message_id);

                                        // printf("Message ID to change: %d\n", message_id);

                                        // Recevoir le nouveau message
                                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Waiting for new message from client.");
                                        char new_message[BUFSIZE];
                                        bytes_read = recv(client_fd, new_message, BUFSIZE - 1, 0);
                                        if (bytes_read <= 0) {
                                            perror("recv");
                                            close(client_fd);
                                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Error receiving new message from client.");
                                            break;  
                                        }
                                        new_message[bytes_read] = '\0';
                                        write_log(LOGFILE_PATH, static_api_key, ip_client, "New message: %s", new_message);

                                        // printf("Changing message ID %d to: %s\n", message_id, new_message);
                                        int ret = modifier_message(conn, static_api_key, message_id, new_message);
                                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Change message result: %d", ret);
                                        if (ret == 1) {
                                            const char *msg = "Message updated.\n";
                                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Message updated.");
                                            send(client_fd, msg, strlen(msg), 0);
                                        } else if (ret == 0) {
                                            const char *msg = "You are not the sender of this message.\n";
                                            write_log(LOGFILE_PATH, static_api_key, ip_client, "You are not the sender of this message.");
                                            send(client_fd, msg, strlen(msg), 0);
                                        } else {
                                            const char *msg = "Error updating message.\n";
                                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Error updating message.");
                                            send(client_fd, msg, strlen(msg), 0);
                                        }
                                        send_user_messages(conn, static_api_key, client_fd, LEN_MSG_HISTORY, 0, id_conv);
                                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Messages sent to client.");
                                        send(client_fd, "END_OF_BLOCK", strlen("END_OF_BLOCK"), 0);

                                    } else if (strncmp(buffer, "DELETE", 6) == 0) {
                                        int message_id;
                                        sscanf(buffer + 6, "%d", &message_id);
                                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Message ID to delete: %d", message_id);


                                        // printf("Deleting message ID %d\n", message_id);
                                        int ret = supprimer_message(conn, static_api_key, message_id);
                                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Delete message result: %d", ret);
                                        if (ret == 1) {
                                            const char *msg = "Message deleted.\n";
                                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Message deleted.");
                                            send(client_fd, msg, strlen(msg), 0);
                                        } else if (ret == 0) {
                                            const char *msg = "You are not the sender of this message.\n";
                                            write_log(LOGFILE_PATH, static_api_key, ip_client, "You are not the sender of this message.");
                                            send(client_fd, msg, strlen(msg), 0);
                                        } else {
                                            const char *msg = "Error deleting message.\n";
                                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Error deleting message.");
                                            send(client_fd, msg, strlen(msg), 0);
                                        }
                                        send_user_messages(conn, static_api_key, client_fd, LEN_MSG_HISTORY, 0, id_conv);
                                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Messages sent to client.");
                                        send(client_fd, "END_OF_BLOCK", strlen("END_OF_BLOCK"), 0);

                                    } else if (buffer[0] == '-') {
                                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Received '-' command from client.");
                                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Leaving the RECEIVE menu.");
                                        break;
                                    } else {
                                        // printf("Invalid command: %s\n", buffer);
                                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Invalid command: %s", buffer);
                                        send_user_messages(conn, static_api_key, client_fd, LEN_MSG_HISTORY, 0, id_conv);
                                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Messages sent to client.");
                                    }
                                }

                            // } else if (strcmp(buffer, "CLEAR") == 0) {
                            //     clear_tables(conn);
                            //     send(client_fd, "Tables cleared.\n", 16, 0);

                            // } else if (strcmp(buffer, "AFFICHER") == 0) {
                            //     fetch_all_data(conn);

                        } else if (strcmp(buffer, "BLOCK") == 0 && (strcmp(account_type, "PRO") == 0)) {
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Received BLOCK command from client.");
                            int idPro = static_api_key;
                            int idClient = recup_receiver(conn, static_api_key, id_conv);
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Receiver ID: %d", idClient);

                            ret = block_user(conn, idPro, idClient, account_type);
                            if (ret == 1) {
                                write_log(LOGFILE_PATH, static_api_key, ip_client, "Discussion blocked.");
                            } else {
                                write_log(LOGFILE_PATH, static_api_key, ip_client, "Error blocking discussion.");
                            }

                            send_conversation_list(conn, static_api_key, client_fd);
                            send(client_fd, "END_OF_BLOCK", strlen("END_OF_BLOCK"), 0);
                            break;

                        } else if (strcmp(buffer, "UNBLOCK") == 0 && (strcmp(account_type, "PRO") == 0)) {
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Received UNBLOCK command from client.");
                            int idPro = static_api_key;
                            int idClient = recup_receiver(conn, static_api_key, id_conv);
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Receiver ID: %d", idClient);

                            ret = unblock_user(conn, idPro, idClient, account_type);
                            if (ret == 1) {
                                write_log(LOGFILE_PATH, static_api_key, ip_client, "Discussion unblocked.");
                            } else {
                                write_log(LOGFILE_PATH, static_api_key, ip_client, "Error unblocking discussion.");
                            }

                            send_conversation_list(conn, static_api_key, client_fd);
                            send(client_fd, "END_OF_BLOCK", strlen("END_OF_BLOCK"), 0);
                            break;
                        } else if (strcmp(buffer, "QUIT") == 0) {
                                memset(buffer, 0, BUFSIZE);
                                write_log(LOGFILE_PATH, static_api_key, ip_client, "Received QUIT command from client.");
                                send_conversation_list(conn, static_api_key, client_fd);
                                send(client_fd, "END_OF_BLOCK", strlen("END_OF_BLOCK"), 0);
                                break;

                            } else {
                                // printf("Invalid command: %s\n", buffer);
                                send(client_fd, "Invalid command.\n", 18, 0);
                                write_log(LOGFILE_PATH, static_api_key, ip_client, "Invalid command: %s", buffer);
                            }
                            // printf("buffer : %s\n", buffer);
                        }

                    } else if (strcmp(buffer, "NEW") == 0) {
                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Received NEW command from client.");
                        int idPro = -1;
                        int idMembre = -1;

                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Waiting for conversation informations from client.");
                        bytes_read = recv(client_fd, buffer, BUFSIZE - 1, 0);
                        if (bytes_read <= 0) {
                            perror("recv");
                            close(client_fd);
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Error receiving conversation informations for sending message from client.");
                            break;
                        }
                        buffer[bytes_read] = '\0';
                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Received conversation informations from client: %s", buffer);
                        if (strcmp(account_type, "PRO") == 0) {
                            char pseudo[BUFSIZE];
                            strncpy(pseudo, buffer, BUFSIZE - 1);
                            pseudo[BUFSIZE - 1] = '\0';
                            idMembre = chercheIDMembre(conn, pseudo, client_fd);
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Member ID: %d", idMembre);

                        } else {
                            int codePro;
                            sscanf(buffer, "%d", &codePro);
                            idPro = chercheIDPro(conn, codePro, client_fd);
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Professional ID: %d", idPro);

                        }

                        if (idPro == -1 && idMembre == -1) {
                            send(client_fd, "Invalid conversation informations.\n", 34, 0);
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Invalid conversation informations.");
                        } else {
                            send(client_fd, "Valid conversation informations.\n", 36, 0);
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Message for creating a new conversation...");
                            char message[BUFSIZE];

                            bytes_read = recv(client_fd, buffer, BUFSIZE - 1, 0);
                            if (bytes_read <= 0) {
                                perror("recv");
                                close(client_fd);
                                write_log(LOGFILE_PATH, static_api_key, ip_client, "Error receiving message from client.");
                                send(client_fd, "Error receiving message.\n", 24, 0);
                                break;
                            }
                            buffer[bytes_read] = '\0';
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Received message from client: %s", buffer);

                            strcpy(message, buffer);

                            if (strcmp(account_type, "PRO") == 0) {
                                write_log(LOGFILE_PATH, static_api_key, ip_client, "Sender is a professional.");
                                save_message(conn, idMembre, static_api_key, static_api_key, idMembre, message, client_fd);
                                
                            } else {
                                write_log(LOGFILE_PATH, static_api_key, ip_client, "Sender is a client.");  
                                save_message(conn, static_api_key, idPro, static_api_key, idPro, message, client_fd);
                            }
                        }
                        
                    } else if (strcmp(buffer, "BLOCK") == 0 && (strcmp(account_type, "ADMIN") == 0)) {
                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Received BLOCK command from client ADMIN.");
                        int idPro = static_api_key;
                        
                        char pseudo[BUFSIZE];
                        recv(client_fd, pseudo, BUFSIZE - 1, 0);
                        pseudo[strcspn(pseudo, "\n")] = '\0';
                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Pseudo to block: %s", pseudo);

                        char query[BUFSIZE];
                        snprintf(query, BUFSIZE, "SELECT idC FROM pact._membre WHERE pseudo = '%s';", pseudo);
                        PGresult *res = PQexec(conn, query);
                        if (PQresultStatus(res) != PGRES_TUPLES_OK) {
                            fprintf(stderr, "Erreur lors de la recherche de l'ID du membre : %s\n", PQerrorMessage(conn));
                            PQclear(res);
                            return -1;
                        }
                        if (PQntuples(res) == 0) {
                            PQclear(res);
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Membre not found.");
                            return -1;
                        }
                        int idMembre = atoi(PQgetvalue(res, 0, 0));
                        PQclear(res);

                        ret = block_user(conn, idPro, idMembre, account_type);
                        if (ret == 1) {
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Discussion blocked.");
                        } else {
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Error blocking discussion.");
                        }

                    } else if (strcmp(buffer, "BAN") == 0 && strcmp(account_type, "ADMIN") == 0) {
                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Received BAN command from client.");
                        char pseudo[BUFSIZE];
                        recv(client_fd, pseudo, BUFSIZE - 1, 0);
                        pseudo[strcspn(pseudo, "\n")] = '\0';
                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Pseudo to ban: %s", pseudo);

                        ret = ban_user(conn, pseudo);
                        if (ret == 1) {
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "User banned.");
                        } else {
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Error banning user.");
                        }
                    } else if(strcmp(buffer, "UNBLOCK") == 0 && strcmp(account_type, "ADMIN") == 0) {
                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Received UNBLOCK command from client.");
                        int idPro = static_api_key;
                        char pseudo[BUFSIZE];
                        recv(client_fd, pseudo, BUFSIZE - 1, 0);
                        pseudo[strcspn(pseudo, "\n")] = '\0';
                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Pseudo to unblock: %s", pseudo);

                        char query[BUFSIZE];
                        snprintf(query, BUFSIZE, "SELECT idC FROM pact._membre WHERE pseudo = '%s';", pseudo);
                        PGresult *res = PQexec(conn, query);
                        if (PQresultStatus(res) != PGRES_TUPLES_OK) {
                            fprintf(stderr, "Erreur lors de la recherche de l'ID du membre : %s\n", PQerrorMessage(conn));
                            PQclear(res);
                            return -1;
                        }
                        if (PQntuples(res) == 0) {
                            PQclear(res);
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Membre not found.");
                            return -1;
                        }
                        int idClient = atoi(PQgetvalue(res, 0, 0));
                        PQclear(res);
                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Receiver ID: %d", idClient);

                        ret = unblock_user(conn, idPro, idClient, account_type);
                        if (ret == 1) {
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Discussion unblocked.");
                        } else {
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Error unblocking discussion.");
                        }
                    } else if (strcmp(buffer, "UNBAN") == 0 && strcmp(account_type, "ADMIN") == 0) {
                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Received UNBAN command from client.");
                        char pseudo[BUFSIZE];
                        recv(client_fd, pseudo, BUFSIZE - 1, 0);
                        pseudo[strcspn(pseudo, "\n")] = '\0';
                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Pseudo to unban: %s", pseudo);

                        ret = unban_user(conn, pseudo);
                        if (ret == 1) {
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "User unbanned.");
                        } else {
                            write_log(LOGFILE_PATH, static_api_key, ip_client, "Error unbanning user.");
                        }
                    } else {
                        // printf("buffer : %s\n", buffer);
                        send(client_fd, "Invalid conversation ID.\n", 25, 0);
                        write_log(LOGFILE_PATH, static_api_key, ip_client, "Invalid conversation ID.");
                        // printf("Invalid conversation ID.\n");
                        continue;
                    }

                    // Capture du signal SIGHUP pour recharger les paramètres
                    signal(SIGHUP, signal_handler);
                }
                write_log(LOGFILE_PATH, static_api_key, ip_client, "END OF MENU");
            }

            
        }
    }
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Server stopped.");
    close(socket_fd);
    PQfinish(conn);
    return 0;
}



// ============================
// Fonction: convert_to_utf8
// Convertit une chaîne de caractères en UTF-8
// ============================
char* convert_to_utf8(const char* input, const char* from_encoding) {
    iconv_t cd = iconv_open("UTF-8", from_encoding);
    if (cd == (iconv_t)-1) {
        perror("iconv_open failed");
        return NULL;
    }

    size_t in_len = strlen(input);
    size_t out_len = in_len * 2; // Prévoir un buffer plus grand pour le texte converti
    char* output = malloc(out_len);
    if (!output) {
        perror("malloc failed");
        iconv_close(cd);
        return NULL;
    }

    char* in_buf = (char*)input;
    char* out_buf = output;
    size_t out_bytes_left = out_len;

    if (iconv(cd, &in_buf, &in_len, &out_buf, &out_bytes_left) == (size_t)-1) {
        perror("iconv conversion failed");
        free(output);
        iconv_close(cd);
        return NULL;
    }

    *out_buf = '\0'; // Terminer la chaîne convertie
    iconv_close(cd);
    return output;
}






// ============================
// Fonction: find_discussion
// Trouve une discussion entre un client et un professionnel
// ============================
int find_discussion(PGconn *conn, int idClient, int idPro, int idEmmeteur, int idReceveur) {
    char query[BUFSIZE];

    // printf("SELECT DISCUSSION\n");
    write_log(LOGFILE_PATH, static_api_key, ip_client, "SELECT DISCUSSION");
    snprintf(query, BUFSIZE,
             "SELECT idDiscussion FROM pact._discussion "
             "WHERE (idClient = %d AND idPro = %d) "
             "OR (idClient = %d AND idPro = %d);",
             idClient, idPro, idPro, idClient);

    PGresult *res = PQexec(conn, query);
    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        fprintf(stderr, "Error fetching discussion: %s\n", PQerrorMessage(conn));
        PQclear(res);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error fetching discussion.");
        return -1;
    }

    int discussion_id = -1;
    if (PQntuples(res) > 0) {
        discussion_id = atoi(PQgetvalue(res, 0, 0));
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Discussion found: %d", discussion_id);
    }
    PQclear(res);

    // printf("DISCUSSION ID : %d\n", discussion_id);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Discussion ID: %d", discussion_id);
    // printf("FIN DE SELECT DISCUSSION\n");
    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of SELECT DISCUSSION");

    return discussion_id;
}





// ============================
// Fonction: save_message
// Sauvegarde un message et crée une discussion si nécessaire
// ============================
void save_message(PGconn *conn, int idClient, int idPro, int idEmmeteur, int idReceveur, const char *message_text, int client_fd) {
    // Trouver ou créer la discussion
    int discussion_id = find_discussion(conn, idClient, idPro, idEmmeteur, idReceveur); 

    // printf("INSERTION DISCUSSION\n");
    write_log(LOGFILE_PATH, static_api_key, ip_client, "INSERTION DISCUSSION");

    // printf("idClient : %d\n", idClient);
    // printf("idPro : %d\n", idPro);
    // printf("idEmmeteur : %d\n", idEmmeteur);
    // printf("idReceveur : %d\n", idReceveur);

    if (discussion_id == -1) {
        char create_discussion_query[BUFSIZE];
        snprintf(create_discussion_query, BUFSIZE,
                 "INSERT INTO pact._discussion (idClient, idPro, estBloque) "
                 "VALUES (%d, %d, false) RETURNING idDiscussion;",
                 idClient, idPro);

        PGresult *create_res = PQexec(conn, create_discussion_query);
        if (PQresultStatus(create_res) != PGRES_TUPLES_OK) {
            fprintf(stderr, "Error creating discussion: %s\n", PQerrorMessage(conn));
            PQclear(create_res);
            write_log(LOGFILE_PATH, static_api_key, ip_client, "Error creating discussion.");
            return;
        }

        // Récupérer l'idDiscussion généré
        discussion_id = atoi(PQgetvalue(create_res, 0, 0));
        PQclear(create_res);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Discussion created: %d", discussion_id);
        send(client_fd, "New discussion created.\n", 24, 0);
    }

    // Convertir le texte du message en UTF-8
    char *utf8_text = convert_to_utf8(message_text, "ISO-8859-1"); // Adapter l'encodage source si nécessaire
    if (!utf8_text) {
        fprintf(stderr, "Error converting message text to UTF-8\n");
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error converting message text to UTF-8");
        return;
    }

    // Échapper le texte pour PostgreSQL
    char escaped_text[BUFSIZE * 2]; // Buffer pour contenir le texte échappé
    int error = 0;
    PQescapeStringConn(conn, escaped_text, utf8_text, strlen(utf8_text), &error);
    free(utf8_text); // Libérer la mémoire du texte converti

    if (error) {
        fprintf(stderr, "Error escaping message text: %s\n", PQerrorMessage(conn));
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error escaping message text.");
        return;
    }


    write_log(LOGFILE_PATH, static_api_key, ip_client, "INSERTION MESSAGE");

    // Insérer le message
    char query[BUFSIZE];
    snprintf(query, BUFSIZE,
             "INSERT INTO pact._message (idDiscussion, idEmmeteur, idReceveur, texte, dateMessage, recu, supprime) "
             "VALUES (%d, %d, %d, '%s', NOW(), false, false);",
             discussion_id, idEmmeteur, idReceveur, escaped_text);

    PGresult *res = PQexec(conn, query);
    if (PQresultStatus(res) != PGRES_COMMAND_OK) {
        fprintf(stderr, "Error inserting message: %s\n", PQerrorMessage(conn));
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error inserting message.");
    }

    write_log(LOGFILE_PATH, static_api_key, ip_client, "Message inserted.");

    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of INSERTION MESSAGE");
    PQclear(res);
}





// ============================
// Fonction: send_user_messages
// Envoie tous les messages reçu à l'utilisateur
// ============================

void send_user_messages(PGconn *conn, int user_id, int client_fd, int max_messages, int message_id, int id_conv) {
    char query[BUFSIZE];
    
    // Si un message_id est fourni, récupérer les messages précédents ce message_id
    write_log(LOGFILE_PATH, static_api_key, ip_client, "SEND USER MESSAGES");
    if (message_id > 0) {
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Message ID: %d", message_id);
        snprintf(query, BUFSIZE,
            "SELECT msg.idMessage, msg.dateMessage, msg.texte, "
            "emmeteur.nom AS nom_emmeteur, emmeteur.prenom AS prenom_emmeteur, "
            "receveur.nom AS nom_receveur, receveur.prenom AS prenom_receveur, "
            "emmeteur.idC AS id_emmeteur "
            "FROM pact._message msg "
            "JOIN pact._compte emmeteur ON msg.idEmmeteur = emmeteur.idC "
            "JOIN pact._compte receveur ON msg.idReceveur = receveur.idC "
            "WHERE msg.idDiscussion = %d AND msg.idMessage < %d AND msg.supprime = false "
            "ORDER BY msg.idMessage DESC "
            "LIMIT %d;",
            id_conv, message_id, max_messages);
    } else {
        write_log(LOGFILE_PATH, static_api_key, ip_client, "No message ID provided.");
        // Sinon, récupérer les messages les plus récents
        snprintf(query, BUFSIZE,
            "SELECT msg.idMessage, msg.dateMessage, msg.texte, "
            "emmeteur.nom AS nom_emmeteur, emmeteur.prenom AS prenom_emmeteur, "
            "receveur.nom AS nom_receveur, receveur.prenom AS prenom_receveur, "
            "emmeteur.idC AS id_emmeteur "
            "FROM pact._message msg "
            "JOIN pact._compte emmeteur ON msg.idEmmeteur = emmeteur.idC "
            "JOIN pact._compte receveur ON msg.idReceveur = receveur.idC "
            "WHERE msg.idDiscussion = %d AND msg.supprime = false "
            "ORDER BY msg.idMessage DESC "
            "LIMIT %d;",
            id_conv, max_messages);
    }

    PGresult *res = PQexec(conn, query);
    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        fprintf(stderr, "Error fetching messages: %s\n", PQerrorMessage(conn));
        send(client_fd, "Error retrieving messages.\n", 27, 0);
        PQclear(res);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error fetching messages.");
        return;
    }

    int rows = PQntuples(res);
    if (rows == 0) {
        send(client_fd, "No messages for you.\n", 22, 0);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "No messages for user.");
    } else {
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Messages for user:");
        for (int i = rows - 1; i >= 0; i--) { // Les messages sont récupérés en ordre descendant, les renvoyer en ordre ascendant
            const char *idMessage = PQgetvalue(res, i, 0);
            char dateMessage[11];
            strncpy(dateMessage, PQgetvalue(res, i, 1), 10);
            dateMessage[10] = '\0';
            const char *message = PQgetvalue(res, i, 2);
            const char *NomEmmeteurMessage = PQgetvalue(res, i, 3);
            const char *PrenomEmmeteurMessage = PQgetvalue(res, i, 4);
            const char *idEmmeteurMessage = PQgetvalue(res, i, 7);

            // printf("message: %s\n", message);

            char formatted_nom[BUFSIZE];
            if (atoi(idEmmeteurMessage) == user_id) {
                snprintf(formatted_nom, BUFSIZE, "Moi");
            } else {
                snprintf(formatted_nom, BUFSIZE, "%s %s", NomEmmeteurMessage, PrenomEmmeteurMessage);
            }
            
            char composed_message[BUFSIZE];

            write_log(LOGFILE_PATH, static_api_key, ip_client, "%-3s | [%s] - %-20s: %s", idMessage, dateMessage, formatted_nom, message);
            
            // Vérifier si le message a été modifié
            char modification_info[BUFSIZE];
            strncpy(modification_info, "", BUFSIZE);
            write_log(LOGFILE_PATH, static_api_key, ip_client, "Checking if message has been modified.");
            snprintf(query, BUFSIZE,
                     "SELECT EXTRACT(EPOCH FROM (NOW() - dateModif)) FROM pact._message WHERE idMessage = %s AND dateModif IS NOT NULL;", idMessage);
            PGresult *mod_res = PQexec(conn, query);
            if (PQresultStatus(mod_res) == PGRES_TUPLES_OK && PQntuples(mod_res) > 0) {
                write_log(LOGFILE_PATH, static_api_key, ip_client, "Message has been modified.");
                double seconds = atof(PQgetvalue(mod_res, 0, 0));
                if (seconds < 60) {
                    write_log(LOGFILE_PATH, static_api_key, ip_client, "Message modified a few seconds ago.");
                    snprintf(modification_info, BUFSIZE, " (modifié il y a quelques secondes)");
                } else if (seconds < 900) {
                    write_log(LOGFILE_PATH, static_api_key, ip_client, "Message modified a few minutes ago.");
                    snprintf(modification_info, BUFSIZE, " (modifié il y a quelques minutes)");
                } else if (seconds < 3600) {
                    write_log(LOGFILE_PATH, static_api_key, ip_client, "Message modified a few minutes ago.");
                    int minutes = (int)(seconds / 60);
                    int rounded_minutes = ((minutes + 7) / 15) * 15;
                    snprintf(modification_info, BUFSIZE, " (modifié il y a %d min)", rounded_minutes);
                } else if (seconds < 86400) {
                    write_log(LOGFILE_PATH, static_api_key, ip_client, "Message modified a few hours ago.");
                    int hours = (int)(seconds / 3600);
                    snprintf(modification_info, BUFSIZE, " (modifié il y a %d heures)", hours);
                } else {
                    write_log(LOGFILE_PATH, static_api_key, ip_client, "Message modified a few days ago.");
                    int days = (int)(seconds / 86400);
                    snprintf(modification_info, BUFSIZE, " (modifié il y a %d jours)", days);
                }
            }
            PQclear(mod_res);
            write_log(LOGFILE_PATH, static_api_key, ip_client, "Message modification info: %s", modification_info);

            snprintf(composed_message, BUFSIZE, "%-3s | [%s] - %-20s: %s%s", idMessage, dateMessage, formatted_nom, message, modification_info);
            write_log(LOGFILE_PATH, static_api_key, ip_client, "Final message: %s", composed_message);

            send(client_fd, composed_message, strlen(composed_message), 0);
            send(client_fd, "\n", 1, 0);
            write_log(LOGFILE_PATH, static_api_key, ip_client, "Message sent to client.");
        }
    }
    PQclear(res);
    
    // Optionnel : Marquer les messages récupérés comme lus
    snprintf(query, BUFSIZE,
             "UPDATE pact._message SET recu = true "
             "WHERE idReceveur = %d AND recu = false;", user_id);
    res = PQexec(conn, query);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Messages marked as read.");
    if (PQresultStatus(res) != PGRES_COMMAND_OK) {
        fprintf(stderr, "Error updating message status: %s\n", PQerrorMessage(conn));
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error updating message status.");
    }
    PQclear(res);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of SEND USER MESSAGES");
}


// ============================
// Fonction: modifier_message
// Modifie un message existant
// ============================
int modifier_message(PGconn *conn, int user_id, int idMessage, const char *nouveau_texte) {
    write_log(LOGFILE_PATH, static_api_key, ip_client, "MODIFIER MESSAGE");

    // Vérifier si l'utilisateur est l'émetteur du message
    char query_temp[BUFSIZE];
    snprintf(query_temp, BUFSIZE,
             "SELECT 1 FROM pact._message WHERE idMessage = %d AND idEmmeteur = %d;",
             idMessage, user_id);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Checking if user is the sender of the message.");
    if (PQntuples(PQexec(conn, query_temp)) == 0) {
        fprintf(stderr, "Error: user is not the sender of the message.\n");
        write_log(LOGFILE_PATH, static_api_key, ip_client, "User is not the sender of the message.");
        return 0;
    }

    write_log(LOGFILE_PATH, static_api_key, ip_client, "User is the sender of the message.");
    // Convertir le texte du message en UTF-8
    char *utf8_text = convert_to_utf8(nouveau_texte, "ISO-8859-1"); // Adapter l'encodage source si nécessaire
    if (!utf8_text) {
        fprintf(stderr, "Error converting message text to UTF-8\n");
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error converting message text to UTF-8");
        return -1;
    }

    // Échapper le texte pour PostgreSQL
    char escaped_text[BUFSIZE * 2]; // Buffer pour contenir le texte échappé
    int error = 0;
    PQescapeStringConn(conn, escaped_text, utf8_text, strlen(utf8_text), &error);
    free(utf8_text); // Libérer la mémoire du texte converti
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Text escaped for PostgreSQL.");
    if (error) {
        fprintf(stderr, "Error escaping message text: %s\n", PQerrorMessage(conn));
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error escaping message text.");
        return -1;
    }

    // Insérer le message
    char query[BUFSIZE];
    snprintf(query, BUFSIZE,
             "UPDATE pact._message SET texte = '%s' WHERE idMessage = %d;",
             escaped_text, idMessage);

    PGresult *res = PQexec(conn, query);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Message updated.");
    if (PQresultStatus(res) != PGRES_COMMAND_OK) {
        fprintf(stderr, "Error updating message: %s\n", PQerrorMessage(conn));
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error updating message.");
    }
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Message updated.");
    // printf("Message updated.\n");
    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of MODIFIER MESSAGE");
    PQclear(res);
    return 1;
}




// ============================
// Fonction: supprimer_message
// Supprime un message existant
// ============================
int supprimer_message(PGconn *conn, int user_id, int idMessage) {
    write_log(LOGFILE_PATH, static_api_key, ip_client, "SUPPRIMER MESSAGE");

    // Vérifier si l'utilisateur est l'émetteur du message
    char query[BUFSIZE];
    snprintf(query, BUFSIZE,
             "SELECT 1 FROM pact._message WHERE idMessage = %d AND idEmmeteur = %d;",
             idMessage, user_id);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Checking if user is the sender of the message.");
    if (PQntuples(PQexec(conn, query)) == 0) {
        fprintf(stderr, "Error: user is not the sender of the message.\n");
        write_log(LOGFILE_PATH, static_api_key, ip_client, "User is not the sender of the message.");
        return 0;
    }
    write_log(LOGFILE_PATH, static_api_key, ip_client, "User is the sender of the message.");
    // Supprimer le message
    snprintf(query, BUFSIZE,
             "update pact._message set supprime = true where idMessage = %d;", idMessage);
    PGresult *res = PQexec(conn, query);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Try to delete message.");
    if (PQresultStatus(res) != PGRES_COMMAND_OK) {
        fprintf(stderr, "Error deleting message: %s\n", PQerrorMessage(conn));
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error deleting message.");
    }
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Message deleted.");
    // printf("Message deleted.\n");
    PQclear(res);

    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of SUPPRIMER MESSAGE");
    return 1;
}




// ============================
// Fonction: verifier_type_compte
// Vérifie si un identifiant correspond à un professionnel ou un client
// ============================
const char* verifier_type_compte(PGconn *conn, int id_compte) {
    write_log(LOGFILE_PATH, static_api_key, ip_client, "VERIFIER TYPE COMPTE");
    
    char query[BUFSIZE];
    PGresult *res;

    // Vérifier dans la table _professionnel
    snprintf(query, BUFSIZE, "SELECT 1 FROM pact._professionnel WHERE idC = %d;", id_compte);
    res = PQexec(conn, query);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Checking in _professionnel.");
    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        fprintf(stderr, "Erreur lors de la vérification dans _professionnel : %s\n", PQerrorMessage(conn));
        PQclear(res);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error checking in _professionnel.");
        return "Erreur";
    }
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Checked in _professionnel.");

    if (PQntuples(res) > 0) {
        PQclear(res);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Professional found.");
        return "Professionnel";
    }
    PQclear(res);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Professional not found.");

    // Vérifier dans la table _membre
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Checking in _membre.");
    snprintf(query, BUFSIZE, "SELECT 1 FROM pact._membre WHERE idC = %d;", id_compte);
    res = PQexec(conn, query);

    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        fprintf(stderr, "Erreur lors de la vérification dans _membre : %s\n", PQerrorMessage(conn));
        PQclear(res);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error checking in _membre.");
        return "Erreur";
    }

    if (PQntuples(res) > 0) {
        PQclear(res);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Client found.");
        return "Client";
    }
    PQclear(res);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Client not found.");
    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of VERIFIER TYPE COMPTE");
    // Si aucune correspondance trouvée
    return "Inconnu";
}




// ============================
// Fonction: fetch_all_data
// Affiche toutes les données de la table _message
// ============================
void fetch_all_data(PGconn *conn) {    
    const char *query = "SELECT * FROM pact._message;";


    PGresult *res = PQexec(conn, query);

    // Vérifiez si la requête a réussi
    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        fprintf(stderr, "Erreur lors de l'exécution de la requête : %s\n", PQerrorMessage(conn));
        PQclear(res);
        return;
    }

    int nFields = PQnfields(res); // Nombre de colonnes
    int nRows = PQntuples(res);  // Nombre de lignes

    // Si la table est vide
    if (nRows == 0) {
        // printf("La table est vide.\n");
        PQclear(res);
        return;
    }

    // printf("\n========== Données de la table pact._message =========\n");
    fflush(stdout);

    // Affichage des noms des colonnes
    // printf("| ");
    for (int i = 0; i < nFields; i++) {
        // printf("%-20s | ", PQfname(res, i));
    }
    // printf("\n");
    fflush(stdout);

    // Affichage des lignes
    for (int i = 0; i < nRows; i++) {
        // printf("| ");
        for (int j = 0; j < nFields; j++) {
            if (PQgetisnull(res, i, j)) {
                // printf("%-20s | ", "NULL");
            } else {
                // printf("%-20s | ", PQgetvalue(res, i, j));
            }
        }
        // printf("\n");
    }

    // printf("=====================================================\n");
    const char *query_discussion = "SELECT * FROM pact._discussion;";

    PGresult *res_discussion = PQexec(conn, query_discussion);

    if (PQresultStatus(res_discussion) != PGRES_TUPLES_OK) {
        fprintf(stderr, "Erreur lors de l'exécution de la requête : %s\n", PQerrorMessage(conn));
        PQclear(res_discussion);
        return;
    }

    int nFields_discussion = PQnfields(res_discussion); // Nombre de colonnes
    int nRows_discussion = PQntuples(res_discussion);  // Nombre de lignes

    // Si la table est vide
    if (nRows_discussion == 0) {
        // printf("La table _discussion est vide.\n");
        PQclear(res_discussion);
        return;
    }

    // printf("\n========== Données de la table pact._discussion =========\n");
    fflush(stdout);

    // Affichage des noms des colonnes
    // printf("| ");
    for (int i = 0; i < nFields_discussion; i++) {
        // printf("%-20s | ", PQfname(res_discussion, i));
    }
    // printf("\n");
    fflush(stdout);

    // Affichage des lignes
    for (int i = 0; i < nRows_discussion; i++) {
        // printf("| ");
        for (int j = 0; j < nFields_discussion; j++) {
            if (PQgetisnull(res_discussion, i, j)) {
                // printf("%-20s | ", "NULL");
            } else {
                // printf("%-20s | ", PQgetvalue(res_discussion, i, j));
            }
        }
        // printf("\n");
    }

    // printf("=====================================================\n");

    PQclear(res_discussion);

    // printf("=====================================================\n");

    const char *query_compte = "SELECT * FROM pact._compte WHERE estBan = true;";

    PGresult *res_compte = PQexec(conn, query_compte);

    if (PQresultStatus(res_compte) != PGRES_TUPLES_OK) {
        fprintf(stderr, "Erreur lors de l'exécution de la requête : %s\n", PQerrorMessage(conn));
        PQclear(res_compte);
        return;
    }

    int nFields_compte = PQnfields(res_compte); // Nombre de colonnes
    int nRows_compte = PQntuples(res_compte);  // Nombre de lignes

    // Si la table est vide
    if (nRows_compte == 0) {
        // printf("La table _compte est vide.\n");
        PQclear(res_compte);
        return;
    }

    // printf("\n========== Données de la table pact._compte =========\n");
    fflush(stdout);

    // Affichage des noms des colonnes
    // printf("| ");
    for (int i = 0; i < nFields_compte; i++) {
        // printf("%-20s | ", PQfname(res_compte, i));
    }
    // printf("\n");
    fflush(stdout);

    // Affichage des lignes
    for (int i = 0; i < nRows_compte; i++) {
        // printf("| ");
        for (int j = 0; j < nFields_compte; j++) {
            if (PQgetisnull(res_compte, i, j)) {
                // printf("%-20s | ", "NULL");
            } else {
                // printf("%-20s | ", PQgetvalue(res_compte, i, j));
            }
        }
        // printf("\n");
    }

    // printf("=====================================================\n");

    PQclear(res_compte);

    PQclear(res);
}




// ============================
// Fonction: clear_tables
// Vide les tables _message et _discussion
// ============================
void clear_tables(PGconn *conn) {
    const char *clear_message_query = "DELETE FROM pact._message;";
    PGresult *res = PQexec(conn, clear_message_query);
    if (PQresultStatus(res) != PGRES_COMMAND_OK) {
        fprintf(stderr, "Error clearing _message table: %s\n", PQerrorMessage(conn));
        PQclear(res);
        return;
    }
    PQclear(res);

    const char *clear_discussion_query = "DELETE FROM pact._discussion;";
    res = PQexec(conn, clear_discussion_query);
    if (PQresultStatus(res) != PGRES_COMMAND_OK) {
        fprintf(stderr, "Error clearing _discussion table: %s\n", PQerrorMessage(conn));
        PQclear(res);
        return;
    }
    PQclear(res);

    // printf("Tables _message and _discussion cleared successfully.\n");
}




int verif_connexion_apikey(PGconn *conn, char *api_key) {
    write_log(LOGFILE_PATH, static_api_key, ip_client, "VERIF CONNEXION APIKEY");
    char query[BUFSIZE];
    int temp = atoi(api_key);
    
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Vérification API key: %d", temp);
    snprintf(query, BUFSIZE, "SELECT 1 FROM pact._compte WHERE idC = '%d';", temp);
    PGresult *res = PQexec(conn, query);

    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error fetching API key: %s", PQerrorMessage(conn));
        fprintf(stderr, "Error fetching API key: %s\n", PQerrorMessage(conn));
        PQclear(res);
        return -1;
    }


    write_log(LOGFILE_PATH, static_api_key, ip_client, "API key found.");
    int rows = PQntuples(res);
    PQclear(res);

    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of VERIF CONNEXION APIKEY");
    return rows;
}



void send_conversation_list(PGconn *conn, int user_id, int client_fd) {
    write_log(LOGFILE_PATH, static_api_key, ip_client, "SEND CONVERSATION LIST");

    char query[BUFSIZE];
    // printf("Fetching discussions for user %d\n", user_id);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Fetching discussions for user %d", user_id);
    snprintf(query, BUFSIZE,
             "SELECT idDiscussion, idClient, idPro FROM pact._discussion "
             "WHERE idClient = %d OR idPro = %d;", user_id, user_id);

    PGresult *res = PQexec(conn, query);
    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        fprintf(stderr, "Error fetching discussions: %s\n", PQerrorMessage(conn));
        send(client_fd, "Error retrieving discussions.\n", 28, 0);
        PQclear(res);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error fetching discussions.");
        return;
    }
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Discussions fetched.");

    int rows = PQntuples(res);
    if (rows == 0) {
        write_log(LOGFILE_PATH, static_api_key, ip_client, "No discussions for user.");
        send(client_fd, "No discussions for you.\n", 24, 0);
    } else {
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Discussions for user:");
        for (int i = 0; i < rows; i++) {
            const char *idDiscussion = PQgetvalue(res, i, 0);
            const char *idClient = PQgetvalue(res, i, 1);
            const char *idPro = PQgetvalue(res, i, 2);

            char composed_message[BUFSIZE];
            snprintf(composed_message, BUFSIZE, "Discussion ID: %-3s | Client ID: %s | Pro ID: %s", idDiscussion, idClient, idPro);
            write_log(LOGFILE_PATH, static_api_key, ip_client, "Sending conversation: %s", composed_message);
            char nom_prenom[BUFSIZE];
            int other_user_id = (atoi(idClient) == user_id) ? atoi(idPro) : atoi(idClient);
            write_log(LOGFILE_PATH, static_api_key, ip_client, "Fetching user name for user %d", other_user_id);

            snprintf(query, BUFSIZE, "SELECT nom, prenom, pseudo FROM pact._compte LEFT JOIN pact._membre ON pact._compte.idC = pact._membre.idC WHERE pact._compte.idC = %d;", other_user_id);
            PGresult *name_res = PQexec(conn, query);
            if (PQresultStatus(name_res) != PGRES_TUPLES_OK) {
                fprintf(stderr, "Error fetching user name: %s\n", PQerrorMessage(conn));
                PQclear(name_res);
                write_log(LOGFILE_PATH, static_api_key, ip_client, "Error fetching user name.");
                continue;
            }
            write_log(LOGFILE_PATH, static_api_key, ip_client, "User name fetched.");

            if (PQntuples(name_res) > 0) {
                write_log(LOGFILE_PATH, static_api_key, ip_client, "User name found.");
                snprintf(query, BUFSIZE, "SELECT 1 FROM pact._professionnel WHERE idC = %d;", other_user_id);
                PGresult *pro_res = PQexec(conn, query);
                if (PQresultStatus(pro_res) != PGRES_TUPLES_OK) {
                    fprintf(stderr, "Error checking professional status: %s\n", PQerrorMessage(conn));
                    PQclear(pro_res);
                    snprintf(nom_prenom, BUFSIZE, "Unknown");
                } else if (PQntuples(pro_res) > 0) {
                    snprintf(nom_prenom, BUFSIZE, "%s %s", PQgetvalue(name_res, 0, 0), PQgetvalue(name_res, 0, 1));
                } else {
                    snprintf(nom_prenom, BUFSIZE, "%s", PQgetvalue(name_res, 0, 2));
                }
                PQclear(pro_res);
            } else {
                write_log(LOGFILE_PATH, static_api_key, ip_client, "User name not found.");
                snprintf(nom_prenom, BUFSIZE, "Unknown");
            }
            PQclear(name_res);
            
            write_log(LOGFILE_PATH, static_api_key, ip_client, "User name: %s", nom_prenom);


            write_log(LOGFILE_PATH, static_api_key, ip_client, "Checking for new messages.");
            snprintf(query, BUFSIZE, "SELECT COUNT(*) FROM pact._message WHERE idDiscussion = %s AND idReceveur = %d AND recu = false;", idDiscussion, user_id);
            PGresult *msg_res = PQexec(conn, query);
            int new_messages = 0;
            if (PQresultStatus(msg_res) == PGRES_TUPLES_OK && PQntuples(msg_res) > 0) {
                new_messages = atoi(PQgetvalue(msg_res, 0, 0));
                write_log(LOGFILE_PATH, static_api_key, ip_client, "New messages found: %d", new_messages);
            }

            PQclear(msg_res);
            write_log(LOGFILE_PATH, static_api_key, ip_client, "End of new messages check.");

            write_log(LOGFILE_PATH, static_api_key, ip_client, "Composing conversation message.");
            if (new_messages > 0) {
                if (new_messages == 1) {
                    write_log(LOGFILE_PATH, static_api_key, ip_client, "1 new message found.");
                    snprintf(composed_message, BUFSIZE, "Discussion ID: %-3s | %s (%d new message)", idDiscussion, nom_prenom, new_messages);
                } else {
                    write_log(LOGFILE_PATH, static_api_key, ip_client, "%d new messages found.", new_messages);
                    snprintf(composed_message, BUFSIZE, "Discussion ID: %-3s | %s (%d new messages)", idDiscussion, nom_prenom, new_messages);    
                }
            } else {
                write_log(LOGFILE_PATH, static_api_key, ip_client, "No new messages found.");
                snprintf(composed_message, BUFSIZE, "Discussion ID: %-3s | %s", idDiscussion, nom_prenom);
            }
            write_log(LOGFILE_PATH, static_api_key, ip_client, "Sending conversation: %s", composed_message);
            // printf("Sending conversation: %s\n", composed_message);
            write_log(LOGFILE_PATH, static_api_key, ip_client, "Sending conversation to client.");
            send(client_fd, composed_message, strlen(composed_message), 0);
            send(client_fd, "\n", 1, 0);
            
        }
    }
    PQclear(res);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of SEND CONVERSATION LIST");
}


bool validConversation(PGconn *conn, int user_id, int client_fd, char *conversation_id) {
    write_log(LOGFILE_PATH, static_api_key, ip_client, "VALID CONVERSATION");

    char query[BUFSIZE];
    int idChoose = atoi(conversation_id);
    snprintf(query, BUFSIZE,
             "SELECT idDiscussion FROM pact._discussion "
             "WHERE idDiscussion = %d AND (idClient = %d OR idPro = %d);",
             idChoose, user_id, user_id);

    write_log(LOGFILE_PATH, static_api_key, ip_client, "Checking conversation ID: %d", idChoose);
    PGresult *res = PQexec(conn, query);
    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error fetching discussion: %s", PQerrorMessage(conn));
        fprintf(stderr, "Error fetching discussion: %s\n", PQerrorMessage(conn));
        send(client_fd, "Error retrieving discussion.\n", 28, 0);
        PQclear(res);
        return false;
    }
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Discussion fetched.");

    int rows = PQntuples(res);
    // printf("rows : %d\n", rows);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Rows: %d", rows);
    PQclear(res);

    write_log(LOGFILE_PATH, static_api_key, ip_client, "Checking if conversation is blocked.");
    snprintf(query, BUFSIZE,
            "SELECT estBloque FROM pact._discussion "
            "WHERE idDiscussion = %d;", idChoose);
    res = PQexec(conn, query);
    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        fprintf(stderr, "Error fetching blocked status: %s\n", PQerrorMessage(conn));
        PQclear(res);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error fetching blocked status.");
        return false;
    }

    if (PQntuples(res) > 0 && strcmp(PQgetvalue(res, 0, 0), "t") == 0) {
        
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Blocked conversation.");
        send(client_fd, "blocked.\n", 9, 0);

    } else if (PQntuples(res) > 0 && strcmp(PQgetvalue(res, 0, 0), "f") == 0) {
        
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Conversation not blocked.");
        send(client_fd, "not blocked.\n", 13, 0);
    }
    PQclear(res);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of discussion check.");
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Returning result: %s", rows > 0 ? "true" : "false");
    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of VALID CONVERSATION");
    return rows > 0;
}

int recup_receiver(PGconn *conn, int id_user, int idConv) {
    write_log(LOGFILE_PATH, static_api_key, ip_client, "RECUP RECEIVER");

    // printf("Fetching receiver for conversation %d\n", idConv);
    char query[BUFSIZE];
    snprintf(query, BUFSIZE,
    "SELECT CASE "
    "WHEN idClient = %d THEN idPro "
    "WHEN idPro = %d THEN idClient "
    "END AS idReceveur "
    "FROM pact._discussion "
    "WHERE idDiscussion = %d "
    "AND (%d = idClient OR %d = idPro);", id_user, id_user, idConv, id_user, id_user);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Fetching receiver for conversation %d", idConv);
    PGresult *res = PQexec(conn, query);
    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error fetching receiver: %s", PQerrorMessage(conn));
        fprintf(stderr, "Error fetching receiver: %s\n", PQerrorMessage(conn));
        PQclear(res);
        return -1;
    }
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Receiver fetched.");

    int rows = PQntuples(res);
    if (rows == 0) {
        write_log(LOGFILE_PATH, static_api_key, ip_client, "No receiver found.");
        PQclear(res);
        return -1;
    }

    int idReceiver = atoi(PQgetvalue(res, 0, 0));
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Receiver ID: %d", idReceiver);
    PQclear(res);
    // printf("Receiver ID: %d\n", idReceiver);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of RECUP RECEIVER");
    return idReceiver;
}


// Fonction pour écrire un message dans le fichier de log
void write_log(const char *logfile, int client_id, const char *client_ip, const char *format, ...) {
    FILE *log_file = fopen(logfile, "a");
    if (!log_file) {
        perror("Erreur ouverture log file");
        return;
    }

    // Récupérer l'heure actuelle
    time_t t = time(NULL);
    struct tm *tm_info = localtime(&t);
    char time_buffer[20];
    strftime(time_buffer, sizeof(time_buffer), "%Y-%m-%d %H:%M:%S", tm_info);

    // Si `client_id` est 0, afficher une valeur vide
    char client_id_text[10];

    if (client_id > 0) {
        snprintf(client_id_text, 10, "%d", client_id);
    } else {
        snprintf(client_id_text, 10, "");
    } 
    fprintf(log_file, "[%s] [%s] [%s] ", time_buffer, client_id_text, client_ip ? client_ip : "");
    
    // Écrire le message
    va_list args;
    va_start(args, format);
    vfprintf(log_file, format, args);
    va_end(args);

    fprintf(log_file, "\n");
    fclose(log_file);

     // Si le mode verbose est activé, afficher aussi dans la console
    if (verbose_mode) {
        printf("[%s] [%s] [%s] ", time_buffer, client_id_text, client_ip ? client_ip : "");
        va_list args;
        va_start(args, format);
        vprintf(format, args);
        va_end(args);
        printf("\n");
    }
}


char *verifier_type_compte_MENU(PGconn *conn, int api_key) {
    write_log(LOGFILE_PATH, static_api_key, ip_client, "VERIFIER TYPE COMPTE FOR MENU");
    // Vérifier si la clé API est dans la liste des clés administratives
    
    int is_admin = 0;
    for (size_t i = 0; i < sizeof(LIST_ADMIN_KEY) / sizeof(LIST_ADMIN_KEY[0]); i++) {
        if (api_key == LIST_ADMIN_KEY[i]) {
            is_admin = 1;
            break;
        }
    }
    if (is_admin) {
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Admin key found.");
        return "ADMIN";
    }
    
    char query[BUFSIZE];
    PGresult *res;

    // Vérifier dans la table _professionnel
    snprintf(query, BUFSIZE, "SELECT 1 FROM pact._professionnel WHERE idC = %d;", api_key);
    res = PQexec(conn, query);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Checking in _professionnel.");
    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        fprintf(stderr, "Erreur lors de la vérification dans _professionnel : %s\n", PQerrorMessage(conn));
        PQclear(res);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error checking in _professionnel.");
        return "Erreur";
    }
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Checked in _professionnel.");

    if (PQntuples(res) > 0) {
        PQclear(res);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Professional found.");
        return "PRO";
    }
    PQclear(res);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Professional not found.");

    // Vérifier dans la table _membre
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Checking in _membre.");
    snprintf(query, BUFSIZE, "SELECT 1 FROM pact._membre WHERE idC = %d;", api_key);
    res = PQexec(conn, query);

    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        fprintf(stderr, "Erreur lors de la vérification dans _membre : %s\n", PQerrorMessage(conn));
        PQclear(res);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error checking in _membre.");
        return "Erreur";
    }

    if (PQntuples(res) > 0) {
        PQclear(res);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Client found.");
        return "MEMBRE";
    }
    PQclear(res);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Client not found.");
    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of VERIFIER TYPE COMPTE FOR MENU");
    // Si aucune correspondance trouvée
    return "Inconnu";    
}


int block_user(PGconn *conn, int idPro, int idClient, const char *type_compte) {
    write_log(LOGFILE_PATH, static_api_key, ip_client, "BLOCK USER");


    char block_query[BUFSIZE];
    if (strcmp(type_compte, "PRO") == 0) {
        int discussion_id = find_discussion(conn, idClient, idPro, static_api_key, idClient);
        
        if (discussion_id == -1) {
            write_log(LOGFILE_PATH, static_api_key, ip_client, "Error fetching discussion.");
            return -1;
        }

        snprintf(block_query, BUFSIZE, "UPDATE pact._discussion SET estBloque = true, dateBlocage = now() WHERE idDiscussion = %d;", discussion_id);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Discussion blocked %d.", discussion_id);

    } else {
        snprintf(block_query, BUFSIZE, "UPDATE pact._compte SET estBloque = true, dateBlocage = now() WHERE idC = %d;", idClient);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "User blocked %d.", idClient);
    }
    
    PGresult *block_res = PQexec(conn, block_query);
    if (PQresultStatus(block_res) != PGRES_COMMAND_OK) {
        fprintf(stderr, "Error blocking discussion: %s\n", PQerrorMessage(conn));
        PQclear(block_res);
        return -1;
    }

    PQclear(block_res);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of BLOCK USER");
    return 1;
}

int ban_user(PGconn *conn, char *pseudo) {
    write_log(LOGFILE_PATH, static_api_key, ip_client, "BAN USER");

    char query[BUFSIZE];
    snprintf(query, BUFSIZE, "SELECT idC FROM pact._membre WHERE pseudo = '%s';", pseudo);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Fetching member ID.");
    PGresult *res = PQexec(conn, query);
    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        fprintf(stderr, "Erreur lors de la recherche de l'ID du membre : %s\n", PQerrorMessage(conn));
        PQclear(res);
        return -1;
    }
    if (PQntuples(res) == 0) {
        PQclear(res);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Membre not found.");
        return -1;
    }
    int idMembre = atoi(PQgetvalue(res, 0, 0));
    PQclear(res);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Member ID: %d", idMembre);
    snprintf(query, BUFSIZE, "UPDATE pact._compte SET estBan = true WHERE idC = %d;", idMembre);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Banning user.");
    PGresult *block_res = PQexec(conn, query);
    if (PQresultStatus(block_res) != PGRES_COMMAND_OK) {
        fprintf(stderr, "Error blocking discussion: %s\n", PQerrorMessage(conn));
        PQclear(block_res);
        return -1;
    }

    PQclear(block_res);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of BLOCK USER");
    return 1;
}

int unblock_user(PGconn *conn, int idPro, int idClient, const char *type_compte) {
    write_log(LOGFILE_PATH, static_api_key, ip_client, "UNBLOCK USER");

    char block_query[BUFSIZE];
    if (strcmp(type_compte, "PRO") == 0) {
        int discussion_id = find_discussion(conn, idClient, idPro, static_api_key, idClient);
        
        if (discussion_id == -1) {
            write_log(LOGFILE_PATH, static_api_key, ip_client, "Error fetching discussion.");
            return -1;
        }

        snprintf(block_query, BUFSIZE, "UPDATE pact._discussion SET estBloque = false, dateBlocage = null WHERE idDiscussion = %d;", discussion_id);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Discussion unblocked.");

    } else {
        snprintf(block_query, BUFSIZE, "UPDATE pact._compte SET estBloque = false, dateBlocage = null WHERE idC = %d;", idClient);   
        write_log(LOGFILE_PATH, static_api_key, ip_client, "User unblocked.");
    }
    
    PGresult *block_res = PQexec(conn, block_query);
    if (PQresultStatus(block_res) != PGRES_COMMAND_OK) {
        fprintf(stderr, "Error blocking discussion: %s\n", PQerrorMessage(conn));
        PQclear(block_res);
        return -1;
    }

    PQclear(block_res);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of UNBLOCK USER");
    return 1;
}

int unban_user(PGconn *conn, char *pseudo) {
    write_log(LOGFILE_PATH, static_api_key, ip_client, "UNBAN USER");

    char query[BUFSIZE];
    snprintf(query, BUFSIZE, "SELECT idC FROM pact._membre WHERE pseudo = '%s';", pseudo);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Fetching member ID.");
    PGresult *res = PQexec(conn, query);
    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        fprintf(stderr, "Erreur lors de la recherche de l'ID du membre : %s\n", PQerrorMessage(conn));
        PQclear(res);
        return -1;
    }
    if (PQntuples(res) == 0) {
        PQclear(res);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Membre not found.");
        return -1;
    }
    int idMembre = atoi(PQgetvalue(res, 0, 0));
    PQclear(res);

    write_log(LOGFILE_PATH, static_api_key, ip_client, "Member ID: %d", idMembre);

    snprintf(query, BUFSIZE, "UPDATE pact._compte SET estBan = false WHERE idC = %d;", idMembre);

    write_log(LOGFILE_PATH, static_api_key, ip_client, "Unbanning user.");

    PGresult *block_res = PQexec(conn, query);
    if (PQresultStatus(block_res) != PGRES_COMMAND_OK) {
        fprintf(stderr, "Error blocking discussion: %s\n", PQerrorMessage(conn));
        PQclear(block_res);
        return -1;
    }

    PQclear(block_res);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of UNBAN USER");
    return 1;
}

void create_discussion(PGconn *conn, int idClient, int idPro) {
    write_log(LOGFILE_PATH, static_api_key, ip_client, "CREATE DISCUSSION");

    char query[BUFSIZE];
    snprintf(query, BUFSIZE,
             "INSERT INTO pact._discussion (idClient, idPro) VALUES (%d, %d);",
             idClient, idPro);

    PGresult *res = PQexec(conn, query);
    if (PQresultStatus(res) != PGRES_COMMAND_OK) {
        fprintf(stderr, "Error creating discussion: %s\n", PQerrorMessage(conn));
        PQclear(res);
        return;
    }
    PQclear(res);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "Discussion created.");
    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of CREATE DISCUSSION");
}

int chercheIDMembre(PGconn *conn,char *pseudo,int client_fd) {
    char query[BUFSIZE];
    snprintf(query, BUFSIZE, "SELECT idC FROM pact._membre WHERE pseudo = '%s';", pseudo);
    PGresult *res = PQexec(conn, query);
    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        fprintf(stderr, "Erreur lors de la recherche de l'ID du membre : %s\n", PQerrorMessage(conn));
        PQclear(res);
        return -1;
    }
    if (PQntuples(res) == 0) {
        PQclear(res);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Membre not found.");
        send(client_fd, "Member/Pro not found.\n", 18, 0);
        return -1;
    }
    int idMembre = atoi(PQgetvalue(res, 0, 0));
    PQclear(res);
    return idMembre;
}

int chercheIDPro(PGconn *conn,int codePro, int client_fd) {
    char query[BUFSIZE];
    snprintf(query, BUFSIZE, "SELECT idC FROM pact._professionnel WHERE codePro = '%d';", codePro);
    PGresult *res = PQexec(conn, query);
    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        fprintf(stderr, "Erreur lors de la recherche de l'ID du professionnel : %s\n", PQerrorMessage(conn));
        PQclear(res);
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Professional not found.");
        send(client_fd, "Member/Pro not found.\n", 18, 0);
        return -1;
    }
    if (PQntuples(res) == 0) {
        PQclear(res);
        return -1;
    }
    int idPro = atoi(PQgetvalue(res, 0, 0));
    PQclear(res);
    return idPro;
}


bool estBloque(PGconn *conn, int id) {
    write_log(LOGFILE_PATH, static_api_key, ip_client, "EST BLOQUE");

    char query[BUFSIZE];
    snprintf(query, BUFSIZE, "SELECT estBloque FROM pact._compte WHERE idC = %d;", id);
    PGresult *res = PQexec(conn, query);
    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error fetching blocked status: %s", PQerrorMessage(conn));
        fprintf(stderr, "Error fetching blocked status: %s\n", PQerrorMessage(conn));
        PQclear(res);
        return false;
    }

    if (PQntuples(res) == 0) {
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Account not found.");
        PQclear(res);
        return false;
    }

    bool blocked = strcmp(PQgetvalue(res, 0, 0), "t") == 0;
    PQclear(res);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of EST BLOQUE");
    return blocked;
}


bool estBan(PGconn *conn, int id) {
    write_log(LOGFILE_PATH, static_api_key, ip_client, "EST BAN");

    char query[BUFSIZE];
    snprintf(query, BUFSIZE, "SELECT estBan FROM pact._compte WHERE idC = %d;", id);
    PGresult *res = PQexec(conn, query);
    if (PQresultStatus(res) != PGRES_TUPLES_OK) {
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Error fetching banned status: %s", PQerrorMessage(conn));
        fprintf(stderr, "Error fetching banned status: %s\n", PQerrorMessage(conn));
        PQclear(res);
        return false;
    }

    if (PQntuples(res) == 0) {
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Account not found.");
        PQclear(res);
        return false;
    }

    bool banned = strcmp(PQgetvalue(res, 0, 0), "t") == 0;
    PQclear(res);
    write_log(LOGFILE_PATH, static_api_key, ip_client, "End of EST BAN");
    return banned;
}

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
}

// Fonction pour recharger la configuration à reception du signal SIGHUP
void signal_handler(int signum) {
    if (signum == SIGHUP) {
        printf("Signal SIGHUP reçu, rechargement de la configuration...\n");
        write_log(LOGFILE_PATH, static_api_key, ip_client, "Signal SIGHUP reçu, rechargement de la configuration...");
        load_config();
    }
}