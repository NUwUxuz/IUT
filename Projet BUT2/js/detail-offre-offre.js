document.addEventListener('DOMContentLoaded', function () { (function () {
    "use strict"; // Correction de la faute de frappe
    const slideTimeout = 3500; // Temps d'affichage de chaque slide
    const prev = document.querySelector('#prev');
    const next = document.querySelector('#next');
    const $slides = document.querySelectorAll('.slide');
    let $dots;
    let intervalId;
    let currentSlide = 0; // Index de la première slide (commence à 0)
    const totalSlides = $slides.length; // Nombre total de slides
    // Fonction pour afficher un slide spécifique
    function slideTo(index) {
        currentSlide = (index + totalSlides) % totalSlides; // Gestion des dépassements
        $slides.forEach(($elt, idx) => {
            $elt.style.transform = `translateX(-${currentSlide * 100}%)`;
        });
        $dots.forEach(($dot, idx) => {
            $dot.classList.toggle('active', idx === currentSlide);
            $dot.classList.toggle('inactive', idx !== currentSlide);
        });
    }
    // Fonction pour passer au slide suivant
    function showSlide() {
        slideTo(currentSlide + 1);
    }
    // Création des "dots" (indicateurs de pagination) en fonction du nombre de slides
    for (let i = 0; i < totalSlides; i++) {
        let dotClass = i === currentSlide ? 'active' : 'inactive';
        let $dot = document.createElement('span');
        $dot.dataset.slideId = i;
        $dot.className = `dot ${dotClass}`;
        document.querySelector('.carousel-dots').appendChild($dot);
    }
    $dots = document.querySelectorAll('.dot');

    // Ajout d'événements "click" sur les dots pour naviguer
    $dots.forEach(($dot, idx) => {
        $dot.addEventListener('click', () => {
            clearInterval(intervalId); // Stop l'intervalle temporairement
            slideTo(idx);
            intervalId = setInterval(showSlide, slideTimeout); // Redémarre l'intervalle
        });
    });
    // Ajout d'événements sur les boutons "prev" et "next"
    prev.addEventListener('click', () => {
        clearInterval(intervalId);
        slideTo(currentSlide - 1);
        intervalId = setInterval(showSlide, slideTimeout);
    });
    next.addEventListener('click', () => {
        clearInterval(intervalId);
        slideTo(currentSlide + 1);
        intervalId = setInterval(showSlide, slideTimeout);
    });
    // Initialisation de l'intervalle de défilement automatique
    intervalId = setInterval(showSlide, slideTimeout);
    // Ajout de gestion des interactions par la souris et le toucher (swipe)
    $slides.forEach($slide => {
        let startX, endX;
        // Stop le défilement auto lors d'un survol
        $slide.addEventListener('mouseover', () => clearInterval(intervalId), false);
        $slide.addEventListener('mouseout', () => intervalId = setInterval(showSlide, slideTimeout), false);
        // Gestion du swipe
        $slide.addEventListener('touchstart', (event) => {
            startX = event.touches[0].clientX;
        });
        $slide.addEventListener('touchend', (event) => {
            endX = event.changedTouches[0].clientX;
            if (startX > endX) {
                slideTo(currentSlide + 1);
            } else if (startX < endX) {
                slideTo(currentSlide - 1);
            }
            clearInterval(intervalId);
            intervalId = setInterval(showSlide, slideTimeout); // Redémarre après swipe
        });
    });
})();


    // Code pour changer les images en fonction de la taille de l'écran
    function updateImageSources() {
        const etoiles = document.querySelectorAll('img[alt="Etoile"], img[alt="Etoile Vide"]');
        const body = document.querySelector('body');
        console.log(etoiles);
        etoiles.forEach(etoile => {
            if (body.classList.contains('BO')) {
                etoile.src = etoile.src.replace("etoile_bleue", "etoile_rouge");
            } else {
                etoile.src = etoile.src.replace("etoile_rouge", "etoile_bleue");
            }
        });
    }

    updateImageSources();
});




