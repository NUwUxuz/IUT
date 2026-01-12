<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conditions d'Utilisation</title>
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
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: white;
            color: #333;
        }

        .container-page-creation-compte {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1, h2 {
            color: #006B8F;
            margin-bottom: 15px;
        }

        h1 {
            text-align: center;
            font-size: 28px;
            margin-bottom: 30px;
        }

        h2 {
            font-size: 22px;
        }

        p {
            margin-bottom: 15px;
            line-height: 1.6;
        }

        ul {
            list-style-type: disc;
            margin-left: 20px;
            margin-bottom: 15px;
        }

        a {
            color: #006B8F;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .bouton-retour-page-création button {
            margin-bottom: 20px;
            position: fixed;
            top: 30px;
            left: 30px;
            background-color: #48c9b0;
            border: none;
            color: black;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }

        .spacer {
            height: 30px;
        }
    </style>
</head>
<body class="FO">

    <div class="container-page-creation-compte">
        <header>
            <h1 class="FS-bold">Conditions Générales d'Utilisation</h1>
        </header>

        <main>
            <section>
                <h2>1. Objet des Conditions Générales d'Utilisation</h2>
                <p>
                    Les présentes Conditions Générales d'Utilisation (CGU) régissent l'accès et l'utilisation de notre site,
                    une plateforme permettant aux utilisateurs de rechercher, consulter, et publier des avis sur des lieux, 
                    services ou activités.
                </p>
            </section>

            <section>
                <h2>2. Acceptation des Conditions</h2>
                <p>
                    En accédant à notre site et en l'utilisant, vous acceptez pleinement et sans réserve les présentes CGU.
                    Si vous n'acceptez pas ces conditions, vous êtes prié de ne pas utiliser notre plateforme.
                </p>
            </section>

            <section>
                <h2>3. Inscription et Compte</h2>
                <p>
                    L'inscription sur notre site est gratuite. Les utilisateurs doivent fournir des informations exactes et 
                    complètes lors de la création de leur compte. Les utilisateurs sont responsables de maintenir la 
                    confidentialité de leurs identifiants.
                </p>
            </section>

            <section>
                <h2>4. Publication des Avis</h2>
                <p>
                    Les utilisateurs peuvent publier des avis à condition qu'ils respectent les règles suivantes :
                </p>
                <ul>
                    <li>Les avis doivent refléter une expérience réelle et honnête.</li>
                    <li>Aucun contenu offensant, diffamatoire ou illégal n'est autorisé.</li>
                    <li>Les utilisateurs ne doivent pas poster de contenu promotionnel ou publicitaire.</li>
                </ul>
            </section>

            <section>
                <h2>5. Droits et Responsabilités</h2>
                <p>
                    Notre site se réserve le droit de modérer, modifier ou supprimer tout contenu publié par un utilisateur 
                    qui ne respecterait pas les présentes CGU.
                </p>
                <p>
                    Nous ne garantissons pas l'exactitude ou l'exhaustivité des informations publiées par les utilisateurs et 
                    déclinons toute responsabilité à cet égard.
                </p>
            </section>

            <section>
                <h2>6. Propriété Intellectuelle</h2>
                <p>
                    Tout le contenu publié sur notre site (textes, images, logos, etc.) est protégé par les lois sur la 
                    propriété intellectuelle. Toute reproduction ou distribution non autorisée est interdite.
                </p>
            </section>

            <section>
                <h2>7. Protection des Données Personnelles</h2>
                <p>
                    Nous collectons et utilisons les données personnelles des utilisateurs conformément à notre politique de 
                    confidentialité. Ces données sont utilisées pour fournir et améliorer nos services.
                </p>
            </section>

            <section>
                <h2>8. Modification des CGU</h2>
                <p>
                    Nous nous réservons le droit de modifier les présentes CGU à tout moment. Les modifications seront 
                    publiées sur cette page et prendront effet immédiatement après leur mise en ligne.
                </p>
            </section>

            <section>
                <h2>9. Contact</h2>
                <p>
                    Pour toute question relative aux présentes CGU, vous pouvez nous contacter via l'email suivant : 
                    <a href="mailto:PACT-TripEnArvor@proton.me">PACT-TripEnArmor@proton.me</a>
                </p>
            </section>
        </main>
    </div>

</body>
</html>
