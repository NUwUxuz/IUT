<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styleGeneral.css">
    <title>À propos de nous - TripEnArvor</title>
    <style>
    </style>
    <?php 
    if(isset($_COOKIE['type_compte'])){
        if ($_COOKIE['type_compte'] == "pro_public" || $_COOKIE['type_compte'] == "pro_prive") { ?>
            <link id="dynamic-favicon" rel="icon" type="image/x-icon" href="../logo/rouge.ico"> <?php   
        }  else { ?>
            <link id="dynamic-favicon" rel="icon" type="image/x-icon" href="../logo/bleu.ico"> <?php
        }
    } else { ?>
        <link id="dynamic-favicon" rel="icon" type="image/x-icon" href="../logo/bleu.ico"> <?php
    }?>
</head>
<body class="FO">

<header>
    <h1 class="FS-bold">À propos de nous</h1>
</header>

<main>
    <div class="container-page-footer">
            <p class="p-page-footer">Découvrez notre mission et notre engagement pour valoriser le territoire costarmoricain.</p>
        <section>
            <h2 class="h2-page-footer">Notre Mission</h2>
            <p class="p-page-footer">
                TripEnArvor est une association à but non lucratif régie par la <strong>Loi 1901</strong>. 
                Notre objectif principal est de promouvoir le territoire Costarmoricain, en mettant en lumière ses activités, 
                parcs d’attractions, visites, spectacles et restaurants.
            </p>
            <p class="p-page-footer">
                Nous œuvrons à la valorisation du patrimoine culturel et social des Côtes d’Armor, en collaboration avec 
                les acteurs locaux et les institutions régionales.
            </p>
        </section>

        <section>
            <h2 class="h2-page-footer">Nos Partenaires et Financements</h2>
            <p class="p-page-footer">
                Grâce au soutien financier de la <strong>Région Bretagne</strong> et du <strong>Conseil Général des Côtes d’Armor</strong>, 
                TripEnArvor est en mesure de développer des initiatives qui répondent aux besoins de valorisation du territoire. 
                Nous travaillons main dans la main avec des partenaires locaux pour renforcer l’attractivité de notre région.
            </p>
        </section>

        <section>
            <h2 class="h2-page-footer">Notre Ambition pour 2025</h2>
            <p class="p-page-footer">
                L’enjeu majeur de TripEnArvor est le lancement de la <strong>Plateforme d’Avis et Conseils Touristiques (PACT)</strong>, 
                une solution innovante destinée à rapprocher les professionnels du tourisme (établissements privés, associations, secteur public) et la population locale ainsi que les visiteurs. 
                Cette plateforme a pour vocation de :
            </p>
            <ul class="ul-page-footer">
                <li>Améliorer la visibilité des acteurs touristiques costarmoricains.</li>
                <li>Créer un lien fort entre les habitants et les visiteurs.</li>
                <li>Favoriser des échanges constructifs et renforcer l’attractivité régionale.</li>
            </ul>
        </section>

        <section>
            <h2 class="h2-page-footer">Nos Valeurs</h2>
            <p class="p-page-footer">
                Chez TripEnArvor, nous croyons fermement à :
            </p>
            <ul class="ul-page-footer">
                <li><strong>L’engagement local :</strong> soutenir les initiatives régionales et les acteurs locaux.</li>
                <li><strong>La valorisation du patrimoine :</strong> promouvoir les richesses culturelles, naturelles et sociales de notre territoire.</li>
                <li><strong>L’innovation :</strong> développer des outils modernes pour répondre aux attentes des voyageurs et des résidents.</li>
                <li><strong>La collaboration :</strong> travailler ensemble avec le public, le privé et les institutions.</li>
            </ul>
        </section>
        <button class="bouton-retour-page-footer" onclick="history.back();">
            <img src="../icons/arrow-left-w.svg" alt="Retour"> Retour
        </button>
    </div>
</main>
</body>
</html>
