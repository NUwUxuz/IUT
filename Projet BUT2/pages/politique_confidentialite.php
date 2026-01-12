<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styleGeneral.css">
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
    <title>Politique de Confidentialité - TripEnArvor</title>
    <style>
        ul{
            padding-left: 20px;
        }
    </style>
</head>
<body class="FO">

<header>
    <h1 class="FS-bold">Politique de Confidentialité</h1>
    
</header>

<main>
    <div class="container-page-footer">
        <p class="p-page-footer">Chez TripEnArvor, votre vie privée est essentielle. Voici comment nous protégeons vos données personnelles.</p>
        <section>
            <h2 class="h2-page-footer">1. Collecte des Informations</h2>
            <p class="p-page-footer">
                Nous collectons des informations personnelles lorsque vous :
            </p>
            <ul class="ul-page-footer">
                <li>Effectuez une réservation sur notre site.</li>
                <li>Nous contactez via nos formulaires ou par email.</li>
                <li>Vous inscrivez à notre newsletter ou créez un compte utilisateur.</li>
            </ul>
            <p class="p-page-footer">
                Ces informations peuvent inclure : votre nom, adresse email, numéro de téléphone, adresse postale et détails de paiement.
            </p>
        </section>

        <section>
            <h2 class="h2-page-footer">2. Utilisation des Informations</h2>
            <p class="p-page-footer">
                Les informations que nous collectons sont utilisées pour :
            </p>
            <ul class="ul-page-footer">
                <li>Traiter vos réservations et transactions.</li>
                <li>Améliorer nos services et personnaliser votre expérience.</li>
                <li>Vous envoyer des mises à jour, des offres promotionnelles et des informations importantes.</li>
            </ul>
            <p class="p-page-footer">Nous nous engageons à limiter l’utilisation de vos données à ces finalités uniquement.</p>
        </section>

        <section>
            <h2 class="h2-page-footer">3. Partage des Informations</h2>
            <p class="p-page-footer">
                Vos données personnelles ne seront jamais vendues, échangées ou partagées avec des tiers à des fins commerciales. 
                Cependant, nous pouvons les partager avec des partenaires de confiance pour :
            </p>
            <ul class="ul-page-footer">
                <li>Traiter les paiements sécurisés.</li>
                <li>Fournir des services essentiels liés à vos voyages.</li>
            </ul>
        </section>

        <section>
            <h2 class="h2-page-footer">4. Sécurité des Données</h2>
            <p class="p-page-footer">
                Nous utilisons des technologies avancées pour protéger vos informations personnelles, notamment :
            </p>
            <ul class="ul-page-footer">
                <li>Cryptage SSL pour les transactions en ligne.</li>
                <li>Stockage sécurisé des données sur nos serveurs.</li>
                <li>Restrictions strictes d'accès aux données par nos employés.</li>
            </ul>
        </section>

        <section>
            <h2 class="h2-page-footer">5. Vos Droits</h2>
            <p class="p-page-footer">
                Conformément à la réglementation applicable, vous avez le droit de :
            </p>
            <ul class="ul-page-footer">
                <li>Accéder à vos données personnelles et demander une copie.</li>
                <li>Corriger ou mettre à jour vos informations.</li>
                <li>Demander la suppression de vos données personnelles.</li>
                <li>Vous opposer à l’utilisation de vos données pour des raisons légitimes.</li>
            </ul>
            <p class="p-page-footer">Pour exercer ces droits, contactez-nous à <a href="mailto:privacy@tripenarvor.com">privacy@tripenarvor.com</a>.</p>
        </section>

        <section>
            <h2 class="h2-page-footer">6. Cookies</h2>
            <p class="p-page-footer">
                Nous utilisons des cookies pour améliorer votre expérience sur notre site, notamment pour :
            </p>
            <ul class="ul-page-footer">
                <li>Analyser le trafic et les performances de notre site.</li>
                <li>Personnaliser le contenu en fonction de vos préférences.</li>
            </ul>
            <p class="p-page-footer">Vous pouvez gérer vos préférences en matière de cookies via les paramètres de votre navigateur.</p>
        </section>

        <section>
            <h2 class="h2-page-footer">7. Modifications de la Politique</h2>
            <p class="p-page-footer">
                Cette politique de confidentialité peut être mise à jour de temps en temps. Toute modification sera publiée sur cette page, 
                avec une date de mise à jour claire.
            </p>
        </section>
        <button class="bouton-retour-page-footer" onclick="history.back();">
            <img src="../icons/arrow-left-w.svg" alt="Retour"> Retour
        </button>
    </div>
</main>
</body>
</html>
