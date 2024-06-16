#include <math.h>
#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <stdbool.h>


#define N 3
#define TAILLE (N*N)

typedef struct {
int valeur;
bool candidats[TAILLE+1]; // la case d’indice 0 est neutralisée
int nbCandidats;
} tCase2;
typedef tCase2 tGrille[TAILLE][TAILLE];

void chargerGrille(tGrille g);
void afficherGrille(tGrille g);
bool verifierValeur(tGrille g, int ligne, int colonne, int valeur);
void initCase(tGrille g, int ligne, int colonne);
void ajouteCandidats(tGrille g, int ligne, int colonne);
void singletonNu(tGrille g, int ligne, int colonne);
void enleveCandidats(tGrille g, int ligne, int colonne);


int main(){
    tGrille grilleSudoku;
    int caseVide = 0;
    int caseRempli = 0;
    float tauxCaseRempli = 0;
    chargerGrille(grilleSudoku);
    clock_t begin = clock();
    afficherGrille(grilleSudoku);
    for (int i = 0 ; i < TAILLE ; i++) {
        for (int j = 0 ; j < TAILLE ; j++) {
            if (grilleSudoku[i][j].valeur == 0) {
                caseVide++;
            } 
            initCase(grilleSudoku, i, j);
            ajouteCandidats(grilleSudoku, i, j);
            singletonNu(grilleSudoku, i, j);
        }
    }
    for (int i = 0 ; i < TAILLE ; i++) {
        for (int j = 0 ; j < TAILLE ; j++) {
            if (grilleSudoku[i][j].valeur == 0) {
                caseRempli++;
            }
            enleveCandidats(grilleSudoku, i, j);
        }
    }
    caseRempli = caseVide - caseRempli;
    tauxCaseRempli = (float)caseVide / caseRempli;
    afficherGrille(grilleSudoku);
    printf("******    RESULTATS DU REMPLISSAGE PAR SINGLETON    ******\nNombre de case remplies = %d sur %d          Taux de remplissage = %3f\n",caseRempli, caseVide, tauxCaseRempli);
    clock_t end = clock();
    double tmpsCPU = (end - begin)*1.0 / CLOCKS_PER_SEC;
    printf( "Temps CPU = %.3f secondes\n",tmpsCPU);
}

void chargerGrille(tGrille g){
    char nomFichier[30];
    FILE * f;
    //Demande du nom de fichier à l'utilisateur
    printf("Nom du fichier ? ");
    scanf("%s", nomFichier);
    //Tentative d'ouverture du fichier dont le nom a été donné par l'utilisateur
    f = fopen(nomFichier, "rb");
    if (f==NULL){
        //Le fichier donné n'existe pas :(
        printf("\n ERREUR sur le fichier %s\n", nomFichier);
    } else {
        //Le fichier existe, mettre ses valeurs dans un type tGrille.
        for (int i = 0 ; i < TAILLE ; i++) {
            for (int j = 0 ; j < TAILLE ; j++) {
                fread(&g[i][j].valeur, sizeof(int), 1, f);
            }
        }
        //Fermeture du fichier.
        fclose(f);
    }
    
}

void afficherGrille(tGrille g) {
    //affichage des deux premières lignes de construction de la grille
    printf("    1  2  3    4  5  6    7  8  9\n");
    printf("  +----------+----------+----------+\n");

    //Ajouter une ligne intermédiaire pour séparer toutes les 3 lignes
    for (int ligne = 0; ligne < 9; ligne++) {
        if (ligne % 3 == 0 && ligne != 0) {
            printf("  +----------+----------+----------+\n");
        }
        printf("%d | ", ligne + 1);
        //Ajout d'une barre séparatrice toutes les 3 valeurs
        for (int colonne = 0; colonne < 9; colonne++) {
            if (colonne % 3 == 0 && colonne != 0) {
                printf("| ");
            }
            //Print un . suivi de deux espaces si le caractères est un 0 (la case est vide)
            if (g[ligne][colonne].valeur == 0) {
                printf(".  ");
            //Print le caractère de la grille correspondant à la ligne et la colonne en cours dans la boucle si ce n'est pas un 0.
            } else {
                printf("%d  ", g[ligne][colonne].valeur);
            }
        }
        printf("|\n");
    }
    //Affiche la ligne de fin de grille
    printf("  +----------+----------+----------+\n");
}

bool verifierValeur(tGrille g, int ligne, int colonne, int val){
    for (int i = 0; i < TAILLE; i++) {
        if (g[ligne][i].valeur == val) {
            return false;
        }
    }

    for (int j = 0; j < TAILLE; j++) {
        if (g[j][colonne].valeur == val) {
            return false;
        }
    }

    int debutBlocLigne = (ligne / 3) * 3;
    int debutBlocColonne = (colonne / 3) * 3;
    for (int k = debutBlocLigne; k < debutBlocLigne + 3; k++) {
        for (int l = debutBlocColonne; l < debutBlocColonne + 3; l++) {
            if (g[k][l].valeur == val) {
                return false;
            }
        }
    }
    return true;
}

void initCase(tGrille g, int ligne, int colonne) {
    for (int i = 1 ; i <= TAILLE ; i++) {
        g[ligne][colonne].candidats[i] = false;
    }
    g[ligne][colonne].nbCandidats = 0;
}

void ajouteCandidats(tGrille g, int ligne, int colonne) {
    if (g[ligne][colonne].valeur == 0) {
        for (int i = 1 ; i <= TAILLE ; i++) {
            if (verifierValeur(g, ligne, colonne, i)) {
                g[ligne][colonne].candidats[i] = true;
                g[ligne][colonne].nbCandidats++;
            }
        }
    }
}

void singletonNu(tGrille g, int ligne, int colonne) {
    int i = 1;
    if (g[ligne][colonne].nbCandidats == 1) {
        while (g[ligne][colonne].candidats[i] != true) {
            i++;
        }
        g[ligne][colonne].valeur = i;
        for (int j = 1 ; j <= TAILLE ; j++) {
            g[ligne][colonne].candidats[j] = false;
        }
    }
}

void enleveCandidats(tGrille g, int ligne, int colonne) {
    if (g[ligne][colonne].valeur == 0) {
        for (int i = 1 ; i <= TAILLE ; i++) {
            if (verifierValeur(g, ligne, colonne, i == false)) {
                g[ligne][colonne].candidats[i] = false;
            }
        }
    }
}