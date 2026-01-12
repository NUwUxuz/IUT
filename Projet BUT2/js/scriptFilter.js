// Boutons et menus
const filterButton = document.getElementById('filterButton');
const menu = document.getElementById('menu');
const closeButton = document.getElementById('closeButton');
const sortButton = document.getElementById('sortButton');
const sortMenu = document.getElementById('sortMenu');
const sortMenuItems = document.querySelectorAll('#sortMenu .menu-item');
const overlayFiltre = document.getElementById('overlay-filtre');

overlayFiltre.style.display = 'none'; // Cache l'overlay au chargement de la page

// Ouvrir le menu "Filtrer"
filterButton.addEventListener('click', () => {
    menu.style.left = '0';
    overlayFiltre.style.display = 'block';
    document.body.classList.add('no-scroll'); // Empêche le scroll sur la page principale
});

// Fermer le menu "Filtrer"
closeButton.addEventListener('click', () => {
    menu.style.left = '-273px';
    overlayFiltre.style.display = 'none';
    document.body.classList.remove('no-scroll'); // Réactive le scroll sur la page principale
});

// Ouvrir ou fermer le menu "Trier"
sortButton.addEventListener('click', (event) => {
    event.stopPropagation(); // Empêche la fermeture immédiate
    if (sortMenu.style.display === 'block') {
        sortMenu.style.display = 'none';
    } else {
        // Positionner le menu sous le bouton
        const rect = sortButton.getBoundingClientRect();
        sortMenu.style.top = `${rect.bottom + window.scrollY}px`;
        sortMenu.style.left = `${rect.left}px`;
        sortMenu.style.display = 'block';
    }
});
// Fonction pour fermer le menu "Trier" depuis un autre fichier
function closeSortMenu() {
    sortMenu.style.display = 'none';
}

// Exporter la fonction pour qu'elle soit accessible depuis un autre fichier
// export { closeSortMenu };

// Fermer le menu "Filtrer" en cliquant en dehors
document.addEventListener('click', (event) => {
    if (!menu.contains(event.target) && !filterButton.contains(event.target)) {
        menu.style.left = '-273px';
        overlayFiltre.style.display = 'none';
        document.body.classList.remove('no-scroll'); // Réactive le scroll sur la page principale*
    }
});

// Fermer le menu "Trier" en cliquant ailleurs
document.addEventListener('click', (event) => {
    if (!sortMenu.contains(event.target) && !sortButton.contains(event.target)) {
        sortMenu.style.display = 'none';
    }
});

// Ajouter un écouteur d'événements sur chaque élément
sortMenuItems.forEach(item => {
    item.addEventListener('click', () => {
        // Supprimer la classe active de tous les éléments
        sortMenuItems.forEach(menuItem => menuItem.classList.remove('active'));

        // Ajouter la classe active à l'élément cliqué
        item.classList.add('active');
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const filterTitles = document.querySelectorAll(".titleFilter"); // Sélectionner tous les éléments avec la classe titleFilter

    filterTitles.forEach((title) => {
        const nextDiv = title.nextElementSibling; // Récupère la div suivante par rapport à l'élément avec la classe titleFilter
        const arrow = title.querySelector("#fleche-filtre"); // Récupère l'image de la flèche

        if (nextDiv) {
            // Stocker l'état initial dans un attribut data
            nextDiv.dataset.initialDisplay = window.getComputedStyle(nextDiv).display;

            // Initialiser l'état de la flèche
            arrow.style.transition = "transform 0.3s ease"; // Ajouter une transition fluide pour la rotation

            title.addEventListener("click", function () {
                // Bascule entre "none" et "flex"
                if (nextDiv.style.display === "flex") {
                    nextDiv.style.display = "none";
                    arrow.style.transform = "rotate(0deg)"; // Retour à la position initiale
                } else {
                    nextDiv.style.display = "flex";
                    arrow.style.transform = "rotate(180deg)"; // Rotation de 180 degrés
                }
            });
        }
    });
});

