document.addEventListener("DOMContentLoaded", function() {
    const currentPage = window.location.pathname.split("/").pop();
    const navItems = document.querySelectorAll('.nav-item');
    const sidebar = document.getElementById("sidebar");
    const pactItem = document.querySelector("#pact-item");
    const bouton = document.querySelector(".sphere");
    const itemToHideFO = ['tableau-de-bord', 'facturation', 'creation', 'notification-avec-notif'];
    const itemEnConstruction = ['favoris'];
    const accueilItem = document.querySelector('[data-icon="accueil"]');
    const tableauDeBordItem = document.querySelector('[data-icon="tableau-de-bord"]');
    const creationOffreItem = document.querySelector('[data-icon="creation"]');
    const notificationAvecItem = document.querySelector('[data-icon="notification-avec-notif"]');
    const notificationSansItem = document.querySelector('[data-icon="notification-sans-notif"]');
    const ParametreItem = document.querySelector('[data-icon="parametre"]');

    function changerApparenceIcon(p, it, im, ic){
        if (p === currentPage || (it === accueilItem && currentPage === 'detail-offre.php') || (it === tableauDeBordItem && currentPage === 'modifier_offre.php') || (it === creationOffreItem && currentPage === 'previsualisation_offre.php') || (it === notificationAvecItem && currentPage === 'avis_non_rep.php') || (it === notificationSansItem && currentPage === 'avis_non_rep.php') || (it === ParametreItem && currentPage == 'modifInfo.php')) {
            it.classList.add('actuel');
            if(sidebar.classList.contains("FO")){
                im.src = `../icons/icons-bleu/icon-${ic}-bleu.svg`;  // Chemin pour l'icône actif
            } else {
                im.src = `../icons/icons-rouge/icon-${ic}-rouge.svg`;  // Chemin pour l'icône actif
            }
        } else {
            im.src = `../icons/icons-gris/icon-${ic}-gris.svg`;   // Chemin pour l'icône inactif
        }
    };

    changerApparenceIcon('accueil.php', accueilItem, accueilItem.querySelector('.logo-nav'), 'accueil');

    //logo pact en fonction BO ou FO
    if (sidebar) {
        const imageDefault = pactItem.querySelector(".image-default"); // Première image
        const imageHover = pactItem.querySelector(".image-hover");     // Deuxième image
    
        if (sidebar.classList.contains("FO")) {
            // Si la classe est "BO"
            imageDefault.src = "../logo/logo_reduit_vert.png"; // Définir l'image 1
            imageHover.src = "../logo/logo_vert.png"; // Définir l'image 1-hover
        } else if (sidebar.classList.contains("BO")) {
            // Si la classe est "FO"
            imageDefault.src = "../logo/logo_reduit_rouge.png"; // Définir l'image 2
            imageHover.src = "../logo/logo_rouge.png"; // Définir l'image 2-hover
        }
    }

    //icons nav
    navItems.forEach(item => {
        // Récupérer l'image associée à l'élément de navigation
        const img = item.querySelector('.logo-nav');
        

        // Vérifier si c'est l'élément avec l'image spécifique à ne pas changer
        if (img && img.id === 'img-profile') {
            // Ne rien faire pour cet élément
            item.style.display= 'flex';
            return;  // Sortir de la boucle pour cet élément
        }

        // Récupérer la valeur de data-icon pour les autres éléments
        const iconName = item.dataset.icon;
        const page = item.getAttribute('href');
        if (sidebar.classList.contains('FO')){
            if (!itemToHideFO.includes(iconName) && !itemEnConstruction.includes(iconName)){
                item.style.display= 'flex';
                changerApparenceIcon(page, item, img, iconName);
            }
        } else {
            if (!itemEnConstruction.includes(iconName)){
                item.style.display= 'flex';
                changerApparenceIcon(page, item, img, iconName);
            }
        }
    });

    bouton.addEventListener('click', function () {
        sidebar.classList.toggle('active'); // Ajouter/retirer la classe active
        bouton.classList.toggle('active');
    });
});
