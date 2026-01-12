#include <stdio.h>
#include <postgresql/libpq-fe.h>

int main() {
    const char *conninfo = "dbname=postgres user=postgres password=yourpassword";
    PGconn *conn = PQconnectdb(conninfo);

    if (PQstatus(conn) != CONNECTION_OK) {
        fprintf(stderr, "Connection to database failed: %s", PQerrorMessage(conn));
        PQfinish(conn);
        return 1;
    }

    printf("Connection successful!\n");
    PQfinish(conn);
    return 0;
}

