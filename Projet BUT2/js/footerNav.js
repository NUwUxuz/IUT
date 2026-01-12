function activateIcon(element, iconId, redirectUrl = null) {
    // Réinitialise toutes les icônes à leur état normal
    document.querySelectorAll('.menu-item img').forEach(img => {
        img.src = img.src.replace('Active.png', '.png'); // Remet l'icône inactive
    });
    
    // Change l'icône active
    let icon = document.getElementById(iconId + 'Icon');
    icon.src = icon.src.replace('.png', 'Active.png'); // Met à jour avec l'icône active

    // Supprime la classe active de tous les boutons
    document.querySelectorAll('.menu-item').forEach(item => {
        item.classList.remove('active');
    });

    // Ajoute la classe active à l'élément cliqué
    element.classList.add('active');

    // Si une URL de redirection est spécifiée, redirige l'utilisateur
    if (redirectUrl) {
        window.location.href = redirectUrl;
    }
}
