<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <footer class="mobile-footer">
        <nav class="menu-footer">
            <a href="../pages/accueil.php" class="menu-item-footer">
                <img src="../icons/home.png" alt="Home Icon" id="homeIcon">
                <p class="FS-bold">Accueil</p>
            </a>
            
            <a href="../pages/historique.php" class="menu-item-footer">
                <img src="../icons/recent.png" alt="Clock Icon" id="clockIcon">
                <p class="FS-bold">Historique</p>
            </a>
            <!--
            <a href="../pages/en-construction.php" class="menu-item-footer">
                <img src="../icons/notification.png" alt="Bell Icon" id="bellIcon">
            </a>
            -->
            <?php if (isset($_COOKIE['type_compte'])) { ?>
            <a href="../pages/infoCompte.php" class="menu-item-footer">
                <img src="../icons/profil.png" alt="Profile Icon" id="profileIcon">
                <p class="FS-bold">Compte</p>
            </a>
            <?php }
            else { ?>
            <a href="../pages/connexion.php" class="menu-item-footer">
                <img src="../icons/profil.png" alt="Profile Icon" id="profileIcon">
                <p class="FS-bold">Compte</p>
            </a>
            <?php } ?> 
        </nav>
    </footer>

    <script>
        // Fonction pour définir l'icône active en fonction de l'ID
        function setActiveIcon(activeIconId) {
            // Réinitialise toutes les icônes à leur image normale et supprime la classe activeIcon
            document.querySelectorAll('.menu-item-footer').forEach(item => {
            const img = item.querySelector('img');
            img.src = img.src.replace('Active', '');  // Enlève 'Active' si présent
            item.classList.remove('activeIcon');  // Supprime la classe activeIcon
            });
            
            // Définit l'icône active et ajoute la classe activeIcon
            const activeIcon = document.getElementById(activeIconId);
            if (activeIcon) {
            activeIcon.src = activeIcon.src.replace('.png', 'Active.png');
            activeIcon.closest('.menu-item-footer').classList.add('activeIcon');  // Ajoute la classe activeIcon
            }
        }

        // Vérifie si une icône active est enregistrée dans localStorage
        const savedIconId = localStorage.getItem('activeIconId');
        if (savedIconId) {
            setActiveIcon(savedIconId);
        }

        // Ajoute des événements de clic à chaque icône
        document.querySelectorAll('.menu-item-footer img').forEach(icon => {
            icon.addEventListener('click', function() {
            // Change l'icône cliquée en iconeActive.png
            const iconId = this.id;
            localStorage.setItem('activeIconId', iconId);  // Sauvegarde l'ID dans localStorage
            setActiveIcon(iconId);
            });
        });
    </script>
</body>
</html>
