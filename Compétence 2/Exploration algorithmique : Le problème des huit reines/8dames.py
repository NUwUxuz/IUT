##############################################################################################################
## REALISE PAR: NEVOT Pierre, CHAPLAIS Ethan, GOGDET Mael, LE VERGE Lou ##
## DATE: 22/03/2024 ##
## SUJET: Projet 8 Reines ##
## VERSION: 1.0 ##
##############################################################################################################

##############################################################################################################
## IMPORTS ##
##############################################################################################################

import time
import random
from typing import List

##############################################################################################################
## CLASSE GRAPHE ##
##############################################################################################################

class Noeud:
    def __init__(self) -> None:
        self.aretes: List[tuple[int, int]] = []

# Annotations de type
Sommet = tuple[int, int]
ListeSommet = list[Sommet]
GrapheReine = dict[Sommet, Noeud]

class Graphe:
    def __init__(self, graphe_dict: GrapheReine = None) -> None:
        self.graphe_dict = graphe_dict if graphe_dict is not None else {}

    def aretes(self, sommet: Sommet) -> ListeSommet:
        return self.graphe_dict[sommet].aretes

    def all_sommets(self) -> ListeSommet:
        return list(self.graphe_dict.keys())

    def add_sommet(self, sommet: Sommet) -> None:
        if sommet not in self.graphe_dict:
            self.graphe_dict[sommet] = Noeud()

    def add_arete(self, arete: ListeSommet) -> None:
        arete1, arete2 = tuple(arete)
        for x, y in [(arete1, arete2), (arete2, arete1)]:
            if x in self.graphe_dict:
                self.graphe_dict[x].aretes.append(y)

    def remove_arete(self, arete: Sommet) -> None:
        for sommet in self.graphe_dict.keys():
            if sommet == arete:
                self.graphe_dict[sommet].aretes = []
            elif arete in self.graphe_dict[sommet].aretes:
                self.graphe_dict[sommet].aretes.remove(arete)

    def __str__(self) -> str:
        res = "sommets: " + " ".join(str(k) for k in self.graphe_dict.keys()) + "\naretes: "
        res += " ".join(str(arete) for arete in self.__list_aretes())
        return res

    def __list_aretes(self) -> List[set[Sommet]]:
        aretes = []
        for sommet in self.graphe_dict:
            for voisin in self.graphe_dict[sommet].aretes:
                if {voisin, sommet} not in aretes:
                    aretes.append({sommet, voisin})
        return aretes

##############################################################################################################
## HUIT REINES ##
##############################################################################################################

class HuitReines:
    def __init__(self, n: int) -> None:
        self.n = n
        self.liste_solutions = []

    def backtracking(self) -> None:
        self.resoudre_backtracking([], 0)

    def resoudre_backtracking(self, solution_partielle: List[Sommet], ranger: int) -> None:
        if ranger == self.n:  # Si on a placé toutes les reines sur le plateau
            self.liste_solutions.append(solution_partielle[:])  # Ajouter la solution trouvée
            return

        for col in range(self.n):  # Pour chaque colonne dans la rangée actuelle
            if self.est_bon(solution_partielle, ranger, col):  # Si la position est sûre pour une reine
                solution_partielle.append((ranger, col))  # Placer une reine
                self.resoudre_backtracking(solution_partielle, ranger + 1)  # Résoudre le reste du plateau
                solution_partielle.pop()  # Retirer la reine pour essayer d'autres positions

    def est_bon(self, solution: List[Sommet], ranger: int, col: int) -> bool:
        for (r, c) in solution:
            if c == col or r + c == ranger + col or r - c == ranger - col:
                return False
        return True

    def bfs(self) -> None:
        self.resoudre_bfs([])

    def resoudre_bfs(self, queue: List[List[Sommet]]) -> None:
        queue.append([])  # Ajouter une sentinelle pour marquer les niveaux
        while queue:
            solution_partielle = queue.pop(0)  # Récupérer la prochaine solution partielle à explorer
            if len(solution_partielle) == self.n:  # Si une solution complète est trouvée
                self.liste_solutions.append(solution_partielle)  # Ajouter la solution trouvée
                continue
            for col in range(self.n):  # Pour chaque colonne dans la nouvelle rangée
                if self.est_bon(solution_partielle, len(solution_partielle), col):  # Si la position est sûre
                    queue.append(solution_partielle + [(len(solution_partielle), col)])  # Ajouter la position à explorer

    def dfs(self) -> None:
        self.resoudre_dfs([])

    def resoudre_dfs(self, solution_partielle: List[Sommet]) -> None:
        if len(solution_partielle) == self.n:
            self.liste_solutions.append(solution_partielle)
            return
        for col in range(self.n):
            if self.est_bon(solution_partielle, len(solution_partielle), col):
                self.resoudre_dfs(solution_partielle + [(len(solution_partielle), col)])

    def aleatoire(self) -> None:
        while len(self.liste_solutions) < 1:  # Tant qu'aucune solution n'est trouvée
            solution_partielle = []
            for ranger in range(self.n):  # Pour chaque rangée
                col = random.randint(0, self.n - 1)  # Choisir une colonne aléatoire
                solution_partielle.append((ranger, col))  # Placer une reine dans la position aléatoire
            if self.est_solution(solution_partielle):  # Si la configuration est une solution valide
                self.liste_solutions.append(solution_partielle)  # Ajouter la solution trouvée

    def est_solution(self, solution: List[Sommet]) -> bool:
        for i, (r1, c1) in enumerate(solution):
            for j, (r2, c2) in enumerate(solution):
                if i != j and (r1 == r2 or c1 == c2 or r1 + c1 == r2 + c2 or r1 - c1 == r2 - c2):
                    return False
        return True


##############################################################################################################
## GRILLE 8x8 ## EXECUTION DES ALGORITHMES
##############################################################################################################

# Algorithme 1 BACKTRACKING
start_time = time.time()
huitreines = HuitReines(8)
huitreines.backtracking()
nbsolutionsbacktracking8x8 = len(huitreines.liste_solutions)
tempsexecbacktracking8x8 = time.time() - start_time

# Algorithme 2 BFS
start_time = time.time()
huitreines = HuitReines(8)
huitreines.bfs()
nbsolutionsbfs8x8 = len(huitreines.liste_solutions)
tempsexecbfs8x8 = time.time() - start_time

# Algorithme 3 DFS
start_time = time.time()
huitreines = HuitReines(8)
huitreines.dfs()
nbsolutionsdfs8x8 = len(huitreines.liste_solutions)
tempsexecdfs8x8 = time.time() - start_time

# Algorithme 4 ALEATOIRE
start_time = time.time()
huitreines = HuitReines(8)
huitreines.aleatoire()
nbsolutionsaleatoire8x8 = len(huitreines.liste_solutions)
tempsexecaleatoire8x8 = time.time() - start_time

##############################################################################################################
## GRILLE 6x6 ## EXECUTION DES ALGORITHMES ##
##############################################################################################################

# Algorithme 1 BACKTRACKING
start_time = time.time()
huitreines = HuitReines(6)
huitreines.backtracking()
nbsolutionsbacktracking6x6 = len(huitreines.liste_solutions)
tempsexecbacktracking6x6 = time.time() - start_time

# Algorithme 2 BFS
start_time = time.time()
huitreines = HuitReines(6)
huitreines.bfs()
nbsolutionsbfs6x6 = len(huitreines.liste_solutions)
tempsexecbfs6x6 = time.time() - start_time

# Algorithme 3 DFS
start_time = time.time()
huitreines = HuitReines(6)
huitreines.dfs()
nbsolutionsdfs6x6 = len(huitreines.liste_solutions)
tempsexecdfs6x6 = time.time() - start_time

# Algorithme 4 ALEATOIRE
start_time = time.time()
huitreines = HuitReines(6)
huitreines.aleatoire()
nbsolutionsaleatoire6x6 = len(huitreines.liste_solutions)
tempsexecaleatoire6x6 = time.time() - start_time

##############################################################################################################
## RESULTAT ##
##############################################################################################################
print("________________________________________________________________________________________________")
print("Algorithme 1 BACKTRACKING (GRILLE 8x8) : ")
print("Nombre de solutions trouvées :", nbsolutionsbacktracking8x8)
print("Temps d'exécution pour trouver toutes les solutions :", tempsexecbacktracking8x8, "secondes")
print("________________________________________________________________________________________________")

print("Algorithme 2 BFS (GRILLE 8x8) : ")
print("Nombre de solutions trouvées :", nbsolutionsbfs8x8)
print("Temps d'exécution pour trouver toutes les solutions :", tempsexecbfs8x8, "secondes")
print("________________________________________________________________________________________________")

print("Algorithme 3 DFS (GRILLE 8x8) : ")
print("Nombre de solutions trouvées :", nbsolutionsdfs8x8)
print("Temps d'exécution pour trouver toutes les solutions :", tempsexecdfs8x8, "secondes")
print("________________________________________________________________________________________________")

print("Algorithme 4 ALEATOIRE (GRILLE 8x8) : ")
print("Nombre de solutions trouvées :", nbsolutionsaleatoire8x8)
print("Temps d'exécution pour trouver toutes les solutions :", tempsexecaleatoire8x8, "secondes")
print("________________________________________________________________________________________________")


print("Algorithme 1 BACKTRACKING (GRILLE 6x6) : ")
print("Nombre de solutions trouvées :", nbsolutionsbacktracking6x6)
print("Temps d'exécution pour trouver toutes les solutions :", tempsexecbacktracking6x6, "secondes")
print("________________________________________________________________________________________________")

print("Algorithme 2 BFS (GRILLE 6x6) : ")
print("Nombre de solutions trouvées :", nbsolutionsbfs6x6)
print("Temps d'exécution pour trouver toutes les solutions :", tempsexecbfs6x6, "secondes")
print("________________________________________________________________________________________________")

print("Algorithme 3 DFS (GRILLE 6x6) : ")
print("Nombre de solutions trouvées :", nbsolutionsdfs6x6)
print("Temps d'exécution pour trouver toutes les solutions :", tempsexecdfs6x6, "secondes")
print("________________________________________________________________________________________________")

print("Algorithme 4 ALEATOIRE (GRILLE 6x6) : ")
print("Nombre de solutions trouvées :", nbsolutionsaleatoire6x6)
print("Temps d'exécution pour trouver toutes les solutions :", tempsexecaleatoire6x6, "secondes")
print("________________________________________________________________________________________________")