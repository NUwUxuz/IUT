document.addEventListener('DOMContentLoaded', function () {
    const SCREEN_MOBILE = 801; // Taille de l'écran pour le changement d'images
    
    // Variables existantes
    const boutons = document.querySelectorAll('.NouveauALaUne > div');
    const dropdownToggle = document.querySelector('.dropdown-toggle');
    const dropdown = document.querySelector('.dropdown');
    const fleche = document.querySelector('.dropdown-toggle > img');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    const menuItems = dropdownMenu.querySelectorAll('li');
    const bouton_filtre = document.querySelector('.filtrer-bouton');
    const filtres = document.querySelector('.all-filter.desktop');
    const items_filters = document.querySelectorAll('.filter.desktop');
    const fleche_filtre = document.querySelector('.filtrer-bouton > img');
    const main = document.querySelector('main');
    const carte = document.querySelector('#carte');

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    function closeDropdown() {
        if (dropdown.classList.contains('active')) {
            dropdown.classList.remove('active');
            dropdownMenu.classList.remove('show');
            menuItems.forEach((item) => {
                item.classList.remove('show');
            });
            fleche.classList.remove('rotate-180');
        }
    }

    window.closeDropdown = closeDropdown;

    function closeFilterMenu() {
        if (filtres.classList.contains('active')) {
            filtres.classList.remove('active');
            items_filters.forEach((item) => {
                item.classList.remove('show');
            });
            main.classList.remove('active_filter');
            carte.classList.remove('active_filter');
        }
    }

    // Gestion des boutons "Nouveau" et "À la une"
    // boutons.forEach(bouton => {
    //     bouton.addEventListener('click', function () {

    //         if (this.classList.contains('active')) {
    //             this.style.backgroundColor = '#2C2C2C';
    //             this.querySelector('p').style.color = '#F5F5F5';
                
    //         } else {
    //             this.style.backgroundColor = '#F5F5F5';
    //             this.querySelector('p').style.color = '#757575';
    //         }
    //     });
    // });

    // Gestion du menu Filtres
    bouton_filtre.addEventListener('click', async function (event) {
        event.preventDefault();
        closeDropdown(); // Ferme le menu "Trier par"

        if (filtres.classList.contains('active')) {
            main.classList.toggle('active_filter');
            carte.classList.toggle('active_filter');
        } else {
            main.classList.toggle('active_filter');
            carte.classList.toggle('active_filter');
            await sleep(250);
        }

        filtres.classList.toggle('active');

        if (filtres.classList.contains('active')) {
            main.classList.toggle('active_filter');
            carte.classList.toggle('active_filter');
            items_filters.forEach((item) => {
                item.classList.add('show');
            });
        } else {
            items_filters.forEach((item) => {
                item.classList.remove('show');
            });
            main.classList.toggle('active_filter');
            carte.classList.toggle('active_filter');
        }
    });

    // Gestion du menu déroulant "Trier par"
    dropdownToggle.addEventListener('click', function (event) {
        event.preventDefault();
        closeFilterMenu(); // Ferme le menu "Filtrer par"

        dropdown.classList.toggle('active');
        fleche.classList.toggle('rotate-180');

        if (dropdown.classList.contains('active')) {
            dropdownMenu.classList.add('show');
            menuItems.forEach((item, index) => {
                setTimeout(() => {
                    item.classList.add('show');
                }, index * 100);
            });
        } else {
            menuItems.forEach((item) => {
                item.classList.remove('show');
            });
            if (window.innerWidth > SCREEN_MOBILE) {
                console.log('Écran desktop');
            } else {
                console.log('Écran mobile');
            }
            dropdownMenu.classList.remove('show');
        }
    });

    // Ferme le menu si on clique en dehors
    document.addEventListener('click', function (event) {
        if (!dropdown.contains(event.target) && event.target !== dropdownToggle) {
            closeDropdown();
        }
    });

    // Code pour changer les images en fonction de la taille de l'écran
    function updateImageSources() {

        const etoiles = document.querySelectorAll('img[alt="Etoile"], img[alt="Etoile Vide"], img[alt="Demi Etoile"]');
        const body = document.querySelector('body');
        etoiles.forEach(etoile => {
            if (body.classList.contains('BO')) {
                if (window.innerWidth <= SCREEN_MOBILE) {
                    if (etoile.src.includes('etoile_rouge.png') || etoile.src.includes('etoile_rouge_vide.png') || etoile.src.includes('demi_etoile_rouge.png')) {
                        etoile.src = etoile.src.replace("etoile_rouge", "etoile_bleue");
                    }
                } else {
                    if (etoile.src.includes('etoile_bleue.png') || etoile.src.includes('etoile_bleue_vide.png') || etoile.src.includes('demi_etoile_bleu.png')) {
                        etoile.src = etoile.src.replace("etoile_bleue", "etoile_rouge");
                    }
                }
            } else {
                etoile.src = etoile.src.replace("etoile_rouge", "etoile_bleue");
            }
        });
    }

    const ecran = window.innerWidth > SCREEN_MOBILE ? 'desktop' : 'mobile';

    // Appelle la fonction au chargement et au redimensionnement de la fenêtre
    window.addEventListener('resize', updateImageSources);
    updateImageSources();

    // Code du slider double
    const sliderLeft = document.querySelector(`.${ecran} #slider-left`);
    const sliderRight = document.querySelector(`.${ecran} #slider-right`);
    const valueLeft = document.querySelector(`.${ecran} #value-left`);
    const valueRight = document.querySelector(`.${ecran} #value-right`);
    const rangeActive = document.createElement('div'); // Crée un élément pour la plage active
    rangeActive.classList.add('range-active');
    document.querySelector(`.${ecran} .slider-container`).appendChild(rangeActive);

    // const minGap = 5; // Espace minimum entre les deux sliders
    const maxSliderValue = Math.max(parseInt(sliderLeft.max), parseInt(sliderRight.max));
    const minGap = maxSliderValue > 1000 ? 50 : 5;

    // Fonction pour mettre à jour la plage active et les positions des valeurs
    function updateSliderValues() {
        const leftValue = parseInt(sliderLeft.value);
        const rightValue = parseInt(sliderRight.value);

        // Mise à jour des positions des curseurs
        valueLeft.textContent = leftValue + "€";
        valueRight.textContent = rightValue + "€";

        // Calcul des positions de la plage active
        const percentLeft = (leftValue / sliderLeft.max) * 100;
        const percentRight = (rightValue / sliderRight.max) * 100;

        // Met à jour la largeur et la position de la plage active
        rangeActive.style.left = percentLeft + '%';
        rangeActive.style.width = (percentRight - percentLeft) + '%';

        // Met à jour dynamiquement la position des valeurs sous les curseurs
        valueLeft.style.left = `calc(${percentLeft}% - 20px)`;  // Centrer sous le curseur
        valueRight.style.left = `calc(${percentRight}% - 20px)`;  // Centrer sous le curseur
    }

    // Empêcher les sliders de se chevaucher
    sliderLeft.addEventListener('input', function () {
        if (parseInt(sliderLeft.value) > parseInt(sliderRight.value) - minGap) {
            sliderLeft.value = parseInt(sliderRight.value) - minGap;
        }
        updateSliderValues();
    });

    sliderRight.addEventListener('input', function () {
        if (parseInt(sliderRight.value) < parseInt(sliderLeft.value) + minGap) {
            sliderRight.value = parseInt(sliderLeft.value) + minGap;
        }
        updateSliderValues();
    });

    // Initialisation
    updateSliderValues();

    

    function showPopup() {
        const popup = document.getElementById('popup');
        const croix = document.querySelector('.close-popup');
    
        // Afficher la pop-up
        popup.classList.add('show');
    
        // Ajouter un événement pour fermer la pop-up
        croix.addEventListener('click', function closePopup() {
            popup.classList.remove('show');
            
            // Supprimer l'écouteur pour éviter les doublons
            croix.removeEventListener('click', closePopup);
        });
    }
    
    // Afficher la pop-up après 30 secondes (une seule fois)
    if (document.body.classList.contains('FO')) {
        setTimeout(showPopup, 30000);
    }
    
});
