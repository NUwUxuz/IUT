<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../css/styleGeneral.css">
    </head>
    <body>
        <header class = "header">
            <div class="logo_recherche">
                <img src="../images/logo_vert.png" alt="logo_vert" title="logo_vert" width="122px" height="80px" id="logo">
                <?php if (str_contains($_SERVER['REQUEST_URI'], 'accueil.php')) { ?>
                <div>
                    <img src="../icons/search_icon.png" id="rechercheOffreIMGmobile">    
                    <input type="text" name="search" id="rechercheOffreMobile" placeholder="Recherche" class="FS">
                </div>
                <?php } ?>
            </div>
            <?php
            if (str_contains($_SERVER['REQUEST_URI'], 'accueil.php')) { ?>
            <div class="boutons-navigation">
                <div class="boutonALaUne NoSelect">
                    <p>À la Une</p>
                </div>
                <div class="boutonNouveau NoSelect">
                    <p>Nouveau</p>
                </div>
            </div>
            <?php } ?>
            <hr>
        </header>
        <?php
        include 'footerNav.php';
        ?>
    </body>
</html>