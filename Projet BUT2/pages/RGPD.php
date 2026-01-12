<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styleGeneral.css">
    <link rel="stylesheet" href="../css/cssFooter.css">
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
    <title>Conformité RGPD - TripEnArmor</title>
    <style>
        ul {
            padding-left: 20px;
        }
    </style>
</head>
<body class="FO">

<header>
    <h1>Conformité RGPD</h1>
</header>

<main>
    <div class="container-page-footer">
            <p class="p-page-footer">TripEnArmor respecte vos droits en matière de confidentialité et s'engage à protéger vos données personnelles conformément au RGPD.</p>
        <section>
            <h2 class="h2-page-footer">1. Transparence et Responsabilité</h2>
            <p class="p-page-footer">
                Nous sommes transparents sur la manière dont nous collectons, utilisons et stockons vos données. 
                Nous sommes responsables de leur sécurité et conformité aux lois en vigueur.
            </p>
        </section>

        <section>
            <h2 class="h2-page-footer">2. Vos Droits en vertu du RGPD</h2>
            <p class="p-page-footer">
                Conformément au RGPD, vous avez les droits suivants :
            </p>
            <ul>
                <li><strong>Droit d'accès :</strong> Obtenir une copie des données personnelles que nous détenons sur vous.</li>
                <li><strong>Droit de rectification :</strong> Corriger toute information inexacte ou incomplète.</li>
                <li><strong>Droit à l'effacement :</strong> Demander la suppression de vos données personnelles, sauf obligation légale contraire.</li>
                <li><strong>Droit à la limitation :</strong> Restreindre temporairement ou définitivement le traitement de vos données.</li>
                <li><strong>Droit à la portabilité :</strong> Recevoir vos données dans un format lisible ou les transférer à un autre responsable de traitement.</li>
                <li><strong>Droit d'opposition :</strong> Refuser le traitement de vos données pour des motifs légitimes.</li>
                <li><strong>Droit de retirer votre consentement :</strong> Retirer votre consentement à tout moment pour des traitements basés sur celui-ci.</li>
            </ul>
            <p class="p-page-footer">Pour exercer ces droits, contactez-nous à <a href="PACT-TripEnArmor@proton.me">rgpd@tripenarmor.com</a>.</p>
        </section>

        <section>
            <h2 class="h2-page-footer">3. Base Légale du Traitement</h2>
            <p class="p-page-footer">
                Nous collectons et utilisons vos données personnelles uniquement lorsque nous avons une base légale pour le faire, notamment :
            </p>
            <ul>
                <li>Votre consentement explicite.</li>
                <li>L'exécution d'un contrat (par exemple, pour traiter une réservation).</li>
                <li>Le respect d'une obligation légale.</li>
                <li>Notre intérêt légitime, sous réserve qu'il ne porte pas atteinte à vos droits fondamentaux.</li>
            </ul>
        </section>

        <section>
            <h2 class="h2-page-footer">4. Durée de Conservation des Données</h2>
            <p class="p-page-footer">
                Vos données personnelles sont conservées uniquement pendant la durée nécessaire aux finalités pour lesquelles elles ont été collectées, sauf obligation légale de les conserver plus longtemps.
            </p>
        </section>

        <section>
            <h2 class="h2-page-footer">5. Sécurité des Données</h2>
            <p class="p-page-footer">
                Nous mettons en place des mesures techniques et organisationnelles pour protéger vos données contre tout accès non autorisé, perte ou altération. Cela inclut :
            </p>
            <ul>
                <li>Le cryptage des données sensibles.</li>
                <li>Des contrôles stricts d'accès pour nos employés.</li>
                <li>Des audits réguliers de nos systèmes de sécurité.</li>
            </ul>
        </section>

        <section>
            <h2 class="h2-page-footer">6. Utilisation des Cookies</h2>
            <p class="p-page-footer">
                Notre site utilise des cookies pour analyser le trafic, personnaliser le contenu et améliorer votre expérience utilisateur. Vous pouvez gérer vos préférences via les paramètres de votre navigateur ou notre gestionnaire de cookies.
            </p>
        </section>

        <section>
            <h2 class="h2-page-footer">7. Contact et Réclamations</h2>
            <p class="p-page-footer">
                Si vous avez des questions ou des préoccupations concernant le traitement de vos données personnelles, veuillez nous contacter :
            </p>
            <p class="p-page-footer">Email : <a href="PACT-TripEnArmor@proton.me">PACT-TripEnArmor@proton.me</a></p>
            <p class="p-page-footer">Si vous estimez que vos droits ne sont pas respectés, vous pouvez introduire une réclamation auprès de l'autorité de contrôle compétente (en France : <a href="https://www.cnil.fr">CNIL</a>).</p>
        </section>
        <button class="bouton-retour-page-footer" onclick="history.back();">
            <img src="../icons/arrow-left-w.svg" alt="Retour"> Retour
        </button>
    </div>
</main>
</body>
</html>
