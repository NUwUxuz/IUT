#include <stdlib.h>
#include <stdio.h>
#include <unistd.h>
#include <stdbool.h>
#include <time.h>

#define TAILLE 16
#define N 4
typedef int tGrille[TAILLE][TAILLE];

void chargerGrille(tGrille g);
void afficherGrille(tGrille grille);
void saisir(int *valeurFinale, int n);
bool verifierValeur(tGrille grille, int ligne, int colonne, int valeur);
int verifierPleine(tGrille grille);
bool backtracking (tGrille grille, int numeroCase);


int main() {
    // Déclaration des variables et types
    tGrille grilleSudoku;
    // Initialisation de la grille
    chargerGrille(grilleSudoku);
    afficherGrille(grilleSudoku);
    // Début du chronomètre
    clock_t begin = clock();
    if (backtracking(grilleSudoku, 0)) {
        printf("La grille a été résolu\n");
    }
    else {
        printf("La grille n'a pas été résolu\n");
    }
    afficherGrille(grilleSudoku);
    // Arrêt du chronomètre
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
        fread(g, sizeof(int), TAILLE*TAILLE, f);
        //Fermeture du fichier.
        fclose(f);
    }
    
}

// Procédure pour afficher la grille dans le terminal
void afficherGrille(tGrille grille) {
    //affichage de la première lignes de construction de la grille
    printf(" +-------------+-------------+-------------+-------------+\n");
    //Ajouter une ligne intermédiaire pour séparer toutes les 3 lignes
    for (int ligne = 0; ligne < TAILLE; ligne++) {
        if (ligne % N == 0 && ligne != 0) {
            printf(" +-------------+-------------+-------------+-------------+\n");
        }
        printf(" | ");
        //Ajout d'une barre séparatrice toutes les 3 valeurs
        for (int colonne = 0; colonne < TAILLE; colonne++) {
            if (colonne % N == 0 && colonne != 0) {
                printf("| ");
            }
            //Print un . suivi de deux espaces si le caractères est un 0 (la case est vide)
            if (grille[ligne][colonne] == 0) {
                printf(".  ");
            //Print le caractère de la grille correspondant à la ligne et la colonne en cours dans la boucle si ce n'est pas un 0.
            } else {
                printf("%-3d", grille[ligne][colonne]);
            }
        }
        printf("|\n");
    }
    //Affiche la ligne de fin de grille
    printf(" +-------------+-------------+-------------+-------------+\n\n");
}

void saisir(int *valeurFinale, int n){
    //Initialisation de valeur qui sera la valeur finale et de temporaire, qui servira de variable temporaire pour stocker l'entrée de l'utilisateur.
    int valeur = 0;
    char temporaire[100];

    do {
        //Mettre l'entére dans l'utilisateur dans la variable temporaire sans avoir beosin de la formater avec un scanf
        fgets(temporaire, sizeof(temporaire), stdin);
        //Vérification de la faisabilité de la conversion de temporaire en un entier.
        if (sscanf(temporaire, "%d", &valeur) != 0){
            if (valeur >= 1 && valeur <= n) {
                //La valeur est valide, et comprise entre 1 et n², on assigne donc cette valeur à valeurFinale
                *valeurFinale = valeur;
                break;
            } else {
                //La valeur est bien un entier mais n'est pas comprise entre 1 et n²
                printf("La valeur doit être comprise entre 1 et %d.\n", TAILLE);
            }
        } else {
            //La valeur n'est pas un entier.
            printf("Veuillez entrer un entier valide.\n");
        }
    } while (1);
}

//Fonction booléenne qui vérifie si une valeur est déjà dans une ligne, colonne ou bloc.
bool verifierValeur(tGrille grille, int ligne, int colonne, int valeur) {
    //vérification de la ligne en parcourant chaque élément de celle ci à la recherche de la valeur souhaitée par l'utilisateur.
    for (int i = 0; i < TAILLE; i++) {
        if (grille[ligne][i] == valeur) {
            return false;
        }
    }

    //vérification de la colonne en parcourant chaque élément de celle ci à la recherche de la valeur souhaitée par l'utilisateur.
    for (int j = 0; j < TAILLE; j++) {
        if (grille[j][colonne] == valeur) {
            return false;
        }
    }
    
    //vérification du bloc en parcourant chaque élément de celui ci à la recherche de la valeur souhaitée par l'utilisateur.
    int debutBlocLigne = (ligne) / N * N;
    int debutBlocColonne = (colonne) / N * N;

    for (int k = debutBlocLigne ; k < debutBlocLigne + N ; k++) {
        for (int l = debutBlocColonne ; l < debutBlocColonne + N ; l++) {
            if (grille[k][l] == valeur) {
                return false;
            }
        }
    }
    return true;
}

//Fonction qui vérifie si la grille est remplie (retourne 0 si elle trouve ne serait-ce qu'un seul 0 et retourne 1 si elle n'en trouve aucun après avoir tout parcouru)
int verifierPleine(tGrille grille) {
    for (int i = 0; i < TAILLE; i++) {
        for (int j = 0; j < TAILLE; j++) {
            if (grille[i][j] == 0) {
                return 0;
            }
        }
    }
    return 1;
}

bool backtracking (tGrille grille, int numeroCase) {
    int ligne;
    int colonne;
    bool resultat;
    resultat = false;
    if (numeroCase == TAILLE * TAILLE) {
        resultat = true;
    }
    else {
        ligne = numeroCase / TAILLE;
        colonne = numeroCase % TAILLE;
        if (grille[ligne][colonne] != 0) {
            resultat = backtracking(grille, numeroCase + 1);
        }
        else {
            for (int valeur = 1 ; valeur <= TAILLE ; valeur++) {
                if (verifierValeur(grille, ligne, colonne, valeur)) {
                    grille[ligne][colonne] = valeur;
                    if (backtracking(grille, numeroCase + 1)) {
                        resultat = true;
                    }
                    else {
                        grille[ligne][colonne] = 0;
                    }
                }
            }
        }
    }
    return resultat;
}