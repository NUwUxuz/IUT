<!DOCTYPE html>
<html lang="fr">
    <?php


    include('../sql/connect_params.php');


try{
    $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname",
        $user, $pass);
        
        $stmt = $dbh->prepare(
            "SELECT DISTINCT ON (o.ido)
    o.en_ligne, 
    o.titre, 
    o.prix_min, 
    o.moy_note, 
    o.nbravis, 
    o.resume, 
    a.ville, 
    p.src_image, 
    o.ido, 
    o.categorie, 
    o.date_publication, 
    o.option, 
    o.description, 
    h.heureOuverture, 
    h.heureFermeture,
    h.ouvertWeekend,
    o.numero_voie,
    o.voie,
    o.ville,
    o.code_postal,
    pact.toStringDate(o.ido) AS adresse
    FROM 
        pact.offre o
    JOIN 
        pact._adresse a ON o.idAdresse = a.idAdresse
    LEFT JOIN 
        pact.photo_offre p ON o.idO = p.idO
    LEFT JOIN 
        pact._horaire h ON o.idO = h.idO;
    "
    );
    
    $stmt->execute();

    
    $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    function toInteger($value) {
        return intval($value);
    }

    if (isset($_GET['trie'])) {
        $resultat = trie($_GET['trie'], $resultat);
    }
    $sorted_resultat = $resultat;
    usort($sorted_resultat, function($a, $b) {
        return $a["prix_min"] - $b["prix_min"];
    });

    $prix_min = $sorted_resultat[0]["prix_min"];
    $prix_max = $sorted_resultat[count($sorted_resultat)-1]["prix_min"];
    $step = ($prix_max > 1000) ? 10 : 1;

} catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
}

$lieux = array_unique(array_column($resultat, 'ville'), SORT_REGULAR);

    ?>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Historique</title>
        <link rel="stylesheet" href="../css/styleGeneral.css">
    </head>
    
    <?php
    if(isset($_COOKIE['type_compte'])){
        if ($_COOKIE['type_compte'] == "pro_public" || $_COOKIE['type_compte'] == "pro_prive") { ?>
            <body class="BO"> <?php
        }  else { ?>
            <body class="FO"> <?php
        }
    } else { ?>
        <body class="FO"> <?php

    }?>
        <div class="main-content historique">
            <header class="historiqueHeader">
                <nav class="desktop-element">
                    <?php include_once'nav.php'; ?>
                </nav>
                <div class="historiqueHeaderBis">
                    <h1>Historique</h1>
                    <div class="buttonsHistory">
                        <button class="noselect" id="deleteHistory"><span class="text">Effacer l'historique</span><span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z"></path></svg></span></button>
                    </div>
                </div>
                
                <header class="phone-element historique">
                    <?php
                    include 'header.php';
                    ?>
                </header>
            </header>
            <main id="historique">

                <div id="historique_container"></div>

                <?php
                include_once'footer.php';
                ?>  

            </main>
        </div>
        <footer>
        <?php include_once 'footerNav.php'; ?>
        </footer>
    </body>

    <script type="module">
    import { ajouterHistorique } from '../js/filtres.js'
        
    document.addEventListener("DOMContentLoaded", function() {
        let historique = JSON.parse(localStorage.getItem("historique_offres")) || [];
        console.log(historique);
        let container = document.getElementById("historique_container");

        if (historique.length === 0) {
            container.innerHTML = "<p>Aucune offre consultée récemment.</p>";
            return;
        }

        historique.forEach(offre => {
            let offreLink = document.createElement("a");
            offreLink.href = `detail-offre.php?value=${offre.id}`; // Remplace par le bon lien
            offreLink.style.textDecoration = "none"; // Supprime le soulignement par défaut

            let offreDiv = document.createElement("div");
            offreDiv.classList.add("offre_historique_container");

            if (window.innerWidth < 800 && offre.desc.length > 60) {
                offre.desc = offre.desc.substring(0, 60) + "...";
            }

            offreDiv.innerHTML = `
                <div class="infoText">
                    <h2>${offre.titre}</h2>
                    <p>${offre.desc}</p>
                    <p>${offre.adresse} - ${offre.prix}€</p>
                </div>
                <div class="infoIMG">
                    <div class="etoiles">
                        ${genererEtoiles(offre.note)}
                        <p class="textAvis">(${offre.nbrAvis})</p>
                    </div>
                    <img src="../images/${offre.image || 'imagesReferences/placeholder.jpg'}" alt="Photo offre">
                </div>
                <button class="deleteButton">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 50 59"
                    class="bin"
                >
                    <path
                    fill="#B5BAC1"
                    d="M0 7.5C0 5.01472 2.01472 3 4.5 3H45.5C47.9853 3 50 5.01472 50 7.5V7.5C50 8.32843 49.3284 9 48.5 9H1.5C0.671571 9 0 8.32843 0 7.5V7.5Z"
                    ></path>
                    <path
                    fill="#B5BAC1"
                    d="M17 3C17 1.34315 18.3431 0 20 0H29.3125C30.9694 0 32.3125 1.34315 32.3125 3V3H17V3Z"
                    ></path>
                    <path
                    fill="#B5BAC1"
                    d="M2.18565 18.0974C2.08466 15.821 3.903 13.9202 6.18172 13.9202H43.8189C46.0976 13.9202 47.916 15.821 47.815 18.0975L46.1699 55.1775C46.0751 57.3155 44.314 59.0002 42.1739 59.0002H7.8268C5.68661 59.0002 3.92559 57.3155 3.83073 55.1775L2.18565 18.0974ZM18.0003 49.5402C16.6196 49.5402 15.5003 48.4209 15.5003 47.0402V24.9602C15.5003 23.5795 16.6196 22.4602 18.0003 22.4602C19.381 22.4602 20.5003 23.5795 20.5003 24.9602V47.0402C20.5003 48.4209 19.381 49.5402 18.0003 49.5402ZM29.5003 47.0402C29.5003 48.4209 30.6196 49.5402 32.0003 49.5402C33.381 49.5402 34.5003 48.4209 34.5003 47.0402V24.9602C34.5003 23.5795 33.381 22.4602 32.0003 22.4602C30.6196 22.4602 29.5003 23.5795 29.5003 24.9602V47.0402Z"
                    clip-rule="evenodd"
                    fill-rule="evenodd"
                    ></path>
                    <path fill="#B5BAC1" d="M2 13H48L47.6742 21.28H2.32031L2 13Z"></path>
                </svg>

                <span class="tooltip">Supprimer</span>
                </button>
            `;

            offreLink.appendChild(offreDiv);

            // Ajoute le lien <a> dans le conteneur
            container.appendChild(offreLink);
          offreLink.addEventListener("click", () => {
                ajouterHistorique(offre);
            });

            const deleteButton = offreDiv.querySelector(".deleteButton");
            deleteButton.addEventListener("click", (event) => {
                event.preventDefault(); // Empêche le <a> de naviguer
                event.stopPropagation(); // Empêche le clic de remonter au <a>
                
                offreDiv.style.transition = "transform 0.2s ease-in-out, opacity 0.5s ease-in-out !important";
                offreDiv.style.transform = "scale(1.06)"; // Légère augmentation de la taille

                setTimeout(() => {
                    offreDiv.style.transform = "scale(0)"; // Réduction rapide de la taille
                    offreDiv.style.opacity = "0"; // Disparition en fondu
                }, 150);

                setTimeout(() => {
                    historique = historique.filter(offreFiltree => offreFiltree.id !== offre.id);
                    localStorage.setItem("historique_offres", JSON.stringify(historique));
                    offreDiv.remove();
                    if (historique.length === 0) {
                        container.innerHTML = "<p>Aucune offre consultée récemment.</p>";
                    }
                }, 500);
            });

        });

        const deleteHistoryButton = document.getElementById("deleteHistory");
        deleteHistoryButton.addEventListener("click", () => {
            let offres = container.querySelectorAll(".offre_historique_container");
            let transitionPromises = [];

            offres.forEach(offreDiv => {
            offreDiv.style.transition = "transform 0.2s ease-in-out, opacity 0.5s ease-in-out !important";
            offreDiv.style.transform = "scale(1.06)";

            let promise = new Promise(resolve => {
                setTimeout(() => {
                offreDiv.style.transform = "scale(0)";
                offreDiv.style.opacity = "0";
                }, 150);

                setTimeout(() => {
                resolve();
                }, 650); // Wait for the transition to complete
            });

            transitionPromises.push(promise);
            });

            Promise.all(transitionPromises).then(() => {
            offres.forEach(offreDiv => offreDiv.remove());
            historique = [];
            localStorage.setItem("historique_offres", JSON.stringify(historique));
            container.innerHTML = "<p>Aucune offre consultée récemment.</p>";
            });
        });

    });

    // Générer les étoiles en fonction de la note
    function genererEtoiles(note) {
        if (!note) return "";

        let body = document.querySelector("body");

        let etoiles = "";
        if (body.classList.contains("BO")) {
            etoiles += "rouge"
        } else {
            etoiles += "bleue";
        }
        
        let html = "";
        let noteInt = Math.floor(note);
        
        for (let i = 0; i < noteInt; i++) {
            html += `<img src="../icons/etoile_${etoiles}.png" alt="Etoile">`;
        }
        if (note - noteInt >= 0.5) {
            html += `<img src="../icons/demi_etoile_${etoiles}.png" alt="Demi Etoile">`;
        }
        while (html.split("img").length - 1 < 5) {
            html += `<img src="../icons/etoile_${etoiles}_vide.png" alt="Etoile Vide">`;
        }
        return html;
    }
    </script>

</html>
