/**
*
* \brief Programme de sudoku
* \author CHAPLAIS ETHAN
* \version 1.0
* \date 2 décembre 2023
*
* Ce programme permet à l'utilisateur d'importer
* une grille de sudoku (d'extension .sud) et d'y
* jouer
*/


#include <math.h>
#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <stdbool.h>



/**
*
* \def N
* \brief Dimension de la grille de sudoku
*
*/
#define N 3



/**
*
* \def TAILLE
* \brief Taille maximale d’une dimension d'un tableau
*
* Taille maximale d'une ligne ou d'une colonne ou valeur
* maximale d'une grille de sudoku de NxN
*
*/
#define TAILLE 9



/**
*
* \struct tGrille
* \brief Structure d'un tableau grille de sudoku
*
* Tableau à deux dimensions qui contiendra toutes les
* valeurs de la grille de sudoku
*
*
*/
typedef int tGrille[TAILLE][TAILLE];

void chargerGrille(tGrille g);
void afficherGrille(tGrille g);
void afficherLigneSeparation();
void saisir(int *chiffre);
bool possible(tGrille g, int numLigne, int numColonne, int valeur);
bool grillePleine(tGrille g);



/**
*
* \fn int main()
* \brief Programme principal
* \return Code de sortie du programme (0 : sortie normale)
* 
* Le programme principal créer un tableau ainsi
* que 3 variables pour les coordonnées et la valeur à
* insérer. La grille de sudoku sera chargé dans le
* tableau et sera mis à jour jusqu'a son remplissage
*
*/
int main() {
  tGrille grilleSudoku;
  int indice_ligne;     /*valeur de la ligne*/
  int indice_colonne;     /*valeur de la colonne*/
  int valeur_chiffre;
  chargerGrille(grilleSudoku);
  while (grillePleine(grilleSudoku)) {
    afficherGrille(grilleSudoku);
    printf("Indice de la case ?\n");
    printf("ligne : ");
    saisir(&indice_ligne);
    printf("colonne : ");
    saisir(&indice_colonne);
    indice_ligne--;     /*on diminue de 1 car un tableau commence par l'indice 0 et non 1*/
    indice_colonne--;     /*de même*/
    if (grilleSudoku[indice_ligne][indice_colonne] != 0) {
      printf("Impossible d'inserer une valeur ici, un chiffre y est déjà présent.\n");
    }
    else {
      printf("Valeur à inserer : ");
      saisir(&valeur_chiffre);
      if (possible(grilleSudoku, indice_ligne, indice_colonne, valeur_chiffre)) {
        grilleSudoku[indice_ligne][indice_colonne] = valeur_chiffre;
      }
      else {
        printf("Impossible d'insérer ce chiffre ici, il est présent soit dans la ligne, soit dans la colonne, soit dans le carré.\n");
      }
    }
  }
}



/**
*
* \fn void chargerGrille(tGrille g)
* \brief Procédure qui charge une grille de sudoku
* \param g : paramètre de sortie où la grille de 
*            sudoku sera chargé
* \return le tableau avec la grille de sudoku chargé si valide
*         un message d'erreur si le fichier n'est pas valide
*
* Demande à l'utilisateur un fichier d'extnesion
* .sud et charge la grille de sudoku, si le fichier
* entrer est valide, dans le tableau en paramètre
*
*/
void chargerGrille(tGrille g) {
  char nomFichier[30];
  FILE *f;
  printf("Nom du fichier ? ");
  scanf("%s", nomFichier);
  f = fopen(nomFichier, "rb");
  if (f == NULL) {
    printf("\n ERREUR sur le fichier %s\n", nomFichier);
  } else {
    fread(g, sizeof(int), TAILLE * TAILLE, f);
  }
  fclose(f);
}



/**
* \fn afficherGrille(tGrille g)
* \brief Procédure qui affiche la grille de sudoku
* \param g : paramètre d'entrée qui représente le tableau des
*            valeurs de la grille de sudoku
*
* Affiche les valeurs de la grille de sudoku grâce au tableau
* en paramètre ainsi que la grille elle même et les numéros
* des lignes et colonnes pour bien visualiser la grille
*
*/
void afficherGrille(tGrille g) {
  int chiffre_hor = 1;     /*affichera les chiffres horizontales sur la première ligne pour la QoL (voir ligne 59 pour plus d'information)*/
  int chiffre_ver = 1;     /*affichera les chiffres verticales au début de chaque ligne pour la QoL*/
  int repere_espace = 0;     /*repère pour détecter quand ajouter un | (ou un espace lors de l'affichage des chiffres horizontale pour la QoL) pour délimiter les carré du sudoku*/
  int repere_ligne_separe = 0;     /*repère pour détecter quand ajouter une ligne pour délimiter les carrés du sudoku*/
  printf("%5s","");
  for (chiffre_hor ; chiffre_hor < TAILLE + 1 ; chiffre_hor++) {     /*on parcourt les chiffres de 1 à 9 pour les afficher à la première ligne*/
    if (repere_espace == N) {     /*détecte si on doit ajouter un espace pour délimiter les carrés de sudoku(carré de taille 3)*/
      printf(" ");
      repere_espace = 0;     /*on remet la valeur a 0 pour la prochaine délimitation*/
      chiffre_hor--;     /*on enlève 1 car nous n'avons pas afficher de chiffre puisque nous avons délimité le carré*/
    }
    else {
      printf("%-3d",chiffre_hor);
      repere_espace++;     /*on ajoute pour détecter quand la délimitation du carré doit être fait*/
    }
  }
  repere_espace = 0;     /*la ligne a entièrement été affiché, donc on remet la valeur à 0*/
  printf("\n");
  afficherLigneSeparation();     /*voir ligne 13 pour plus d'info*/
  for (int i = 0 ; i < TAILLE ; i++) {     /*on parcourt la première partie du tableau, soit les lignes du sudoku*/
    if (repere_ligne_separe == N) {     /*détecte si on doit ajouter une ligne de séparation pour délimiter les carrés de sudoku*/
      afficherLigneSeparation();
      repere_ligne_separe = 0;      /*on remet la valeur a 0 pour la prochaine délimitation*/
      i--;     /*nous n'aurons pas afficher de valeur puisque nous avons délimiter le carré, donc on enlève 1*/
    }
    else {
      printf("%d",chiffre_ver);
      printf("%3s","|");
      chiffre_ver++;
      for (int j = 0 ; j < TAILLE ; j++) {     /*on parcourt la deuxième partie du tableau, soit les colonnes du sudoku*/
        if (repere_espace == N) {     /*détecte si ou doit ajouter un pipe pour délimiter les carrés du sudoku*/
          printf("|");
          repere_espace = 0;     /*on remet la valeur a 0 pour la prochaine délimitation*/
          j--;     /*nous n'aurons pas afficher de valeur puisque nous avons délimiter le carré, donc on enlève 1*/
        }
        else {
          if (g[i][j] == 0) {
            printf(" . ");     /*on remplace tous les zérps par des points*/
            repere_espace++;
          }
          else {
            printf(" %d ",g[i][j]);
            repere_espace++;
            }
        }
      }
      repere_ligne_separe++;
      repere_espace = 0;     /*on remet la valeur à zéro pour ne pas causer de problème pour la prochaine ligne*/
      printf("|\n");     /*fin d'une ligne*/
    }
  }
  afficherLigneSeparation();
}



/**
*
* \fn afficherLigneSeparation()
* \brief Procédure qui affiche une ligne de séparation de sudoku
*
* Affiche une ligne qui sépare les carrés entre eux grâce à
* des + et des -
*
*/
void afficherLigneSeparation(){
  printf("%4s","+");
  for (int i = 0 ; i < N ; i++) {
    for (int j = 0 ; j < TAILLE ; j++) {
        printf("-");
    }
    printf("+");
  }
  printf("\n");
}



/**
*
* \fn saisir(int *chiffre)
* \brief Procédure qui demande à l'utilisateur de saisir la valeur d'une coordonnée
* \param chiffre : paramètre de sortie qui représente la coordonnée
* \return la valeur à insérer souhaiter par l'utilisateur
*
* Demande à l'utilisateur une valeur pour la coordonnée d'une ligne
* ou d'une colonne à répétition jusqu'a ce que la valeur soit valide
*
*/
void saisir(int *chiffre){
  char valeur[5];
  int check = 0;     /*variable pour vérifier quand arrêter la boucle*/
  do {
    scanf("%s", valeur);
    if (sscanf(valeur, "%d", &(*chiffre)) != 0) {     /*on vérifie que la valeur donné est un integer*/
      if (*chiffre < 1 || *chiffre > TAILLE) {     /*puis on vérifie que la valeur se situe dans le bon intervalle*/
        printf("Valeur non accepté, veuillez resaisir une valeur.\n");
      }
      else {
        printf("Valeur accepté.\n");
        check++;     /*la boucle peut s'arrêter après que la valeur est accepté*/
      }
    }
    else {
      printf("Valeur non accepté, veuillez resaisir une valeur.\n");
    }
  } while (check == 0);
}



/**
*
* \fn possible(tGrille g, int numLigne, int numColonne, int valeur)
* \brief Fonction qui vérifie si la valeur que l'on souhaite insérer est valide
* \param g : paramètre d'entrée qui représente le tableau de la grille de sudoku
* \param numLigne : paramètre d'entrée qui représente l'indice de la ligne
* \param numColonne: paramètre d'entrée qui représente l'indice de la colonne
* \param valeur : paramètre d'entrée qui représente la valeur à insérer
* \return true si la valeur peut être insérer
*         false sinon
*
* Vérifie si la valeur que l'utilisateur souhaite insérer est présent
* dans la ligne, la colonne ou le carré.
*
*/
bool possible(tGrille g, int numLigne, int numColonne, int valeur){
  bool check = true;      /*booléen renvoyé, true si possible, false si impossible*/
  int carré_x;
  int carré_y;
  for (int i = 0 ; i < TAILLE ; i++) {
    if (g[numLigne][i] == valeur) {     /*on parcourt tous les chiffres de la ligne*/
      check = false;     /*deviens false si on trouve la valeur insérer dans la ligne*/
    }
  }
  for (int j = 0 ; j < TAILLE ; j++) {
    if (g[j][numColonne] == valeur) {     /*on parcourt tous les chiffres de la colonne*/
      check = false;     /*deviens false si on trouve la valeur insérer dans la colonne*/
    }
  }
  if (numLigne <= 2) {     /*on vérifie quelle carré on parcourra*/
    carré_x = 0;     /*carré de la ligne 1*/
  }
  else if (numLigne >= 3 && numLigne <= 5) {
    carré_x = 3;     /*carré de la ligne 2*/
  }
  else if (numLigne >= 6) {
    carré_x = 6;     /*carré de la ligne 3*/
  }
  if (numColonne <= 2) {
    carré_y = 0;     /*carré de la colonne 1*/
  }
  else if (numColonne >= 3 && numColonne <= 5) {
    carré_y = 3;     /*carré de la colonne 2*/
  }
  else if (numColonne >= 6) {
    carré_y = 6;     /*carré de la colonne 3*/
  }
  for (int i = 0 ; i < N ; i++) {
    for (int j = 0 ; j < N ; j++) {
      if (g[i + carré_x][j + carré_y] == valeur) {
        check = false;
      }
    }
  }
  return check;
}



/**
*
* \fn grillePleine(tGrille g)
* \brief Procédure qui s'arrête lorsque la grille est fini
* \param g : paramètre d'entrée qui représente le tableau la grille de sudoku
* \return true si la grille n'est pas fini
*         false sinon
*
* Recherche toute les valeurs du tableau pour voir si au moins une
* valeur est vide et répète le programme jusqu'à ce que la grille
* soit entièrement remplie
*
*/
bool grillePleine(tGrille g) {
  bool chiffre_manquant = false;
  for (int i = 0 ; i < TAILLE ; i++) {
    for (int j = 0 ; j < TAILLE ; j++) {
      if (g[i][j] == 0) {
        chiffre_manquant = true;
      }
    }
  }
  return chiffre_manquant;
}