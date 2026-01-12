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
    <title>Nos Politiques - TripEnArvor</title>
</head>
<body class="FO">
<header>
    <h1 class="FS-bold">Nos Politiques</h1>
</header>

<main>
    <div class="container-page-footer">
        <p class="p-page-footer">Chez TripEnArmor, nous mettons tout en œuvre pour garantir une expérience de voyage transparente et agréable.</p>
        <section>
            <h2 class="h2-page-footer">1. Politique de Réservation</h2>
            <p class="p-page-footer">
                Les réservations peuvent être effectuées directement sur notre site ou via notre service client. 
                Un acompte de 30 % est requis pour confirmer votre réservation. Le solde doit être réglé au plus tard 7 jours avant le départ.
            </p>
        </section>
        <section>
            <h2 class="h2-page-footer">2. Politique d'Annulation</h2>
            <p class="p-page-footer">
                Les annulations effectuées au moins 14 jours avant la date prévue du départ seront remboursées à 100 %.
                Passé ce délai, des frais d'annulation peuvent être appliqués selon nos conditions générales.
            </p>
        </section>
        <section>
            <h2 class="h2-page-footer">3. Politique de Confidentialité</h2>
            <p class="p-page-footer">
                Nous respectons la confidentialité de vos informations personnelles. 
                Toutes les données collectées sont utilisées uniquement pour traiter vos demandes et améliorer nos services. 
                Nous ne partageons jamais vos informations avec des tiers sans votre consentement.
            </p>
        </section>
        <section>
            <h2 class="h2-page-footer">4. Politique de Sécurité</h2>
            <p class="p-page-footer">
                Notre site utilise des technologies de cryptage pour protéger vos transactions et vos informations personnelles.
                Votre sécurité est notre priorité.
            </p>
        </section>
        <section>
            <h2 class="h2-page-footer">5. Politique de Satisfaction</h2>
            <p class="p-page-footer">
                Si vous rencontrez un problème ou si vous n’êtes pas satisfait de nos services, veuillez nous contacter. 
                Nous ferons tout notre possible pour résoudre votre problème dans les plus brefs délais.
            </p>
        </section>
        <button class="bouton-retour-page-footer" onclick="history.back();">
            <img src="../icons/arrow-left-w.svg" alt="Retour"> Retour
        </button>
    </div>
</main>
</body>
</html>
