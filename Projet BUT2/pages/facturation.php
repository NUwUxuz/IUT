<?php
include('../sql/connect_params.php');
try {
    $bdh = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);
    $userID = filter_var($_COOKIE['user'], FILTER_VALIDATE_INT);

    if (!$userID) {
        die("Invalid user ID.");
    }

    $stmt = $bdh->prepare("SELECT * FROM pact.facturation WHERE idC = :userId");
    $stmt->execute([':userId' => $userID]);
    $factures = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur : " . htmlspecialchars($e->getMessage());
    die();
}

function offre($numFacture, $bdh){
    $stmt = $bdh->prepare("SELECT o.*, f.* from pact.offre o inner join pact.facturation f on f.idO = o.idO where f.numFacture = :numFacture");
    $stmt->execute([':numFacture' => $numFacture]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function image($idOffre, $bdh) {
    $stmtPhotos = $bdh->prepare("SELECT src_image FROM pact.photo_offre WHERE idO = :idOffre");
    $stmtPhotos->execute([':idOffre' => $idOffre]);
    $photos = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);
    return $photos[0]['src_image'] ?? ''; // Assurez-vous que la clé existe
}

function PrixOptionTTC($idOffre, $bdh) {
    try {
        // Préparez et exécutez la requête
        $stmtPrixOption = $bdh->prepare("SELECT pact.prix_option_offre_TTC(:idOffre) AS prix_option_ttc");
        $stmtPrixOption->execute([':idOffre' => $idOffre]);

        // Récupérez le résultat
        $Prix = $stmtPrixOption->fetch(PDO::FETCH_ASSOC);

        // Retournez la valeur ou une valeur par défaut si rien n'est trouvé
        return $Prix['prix_option_ttc'] ?? 0;
    } catch (PDOException $e) {
        // Gérez les erreurs
        echo "Erreur lors de la récupération du prix des options : " . htmlspecialchars($e->getMessage());
        return 0;
    }
}

//executer la fonction BDD creerFactures(idOffre)
function creerFactures($idOffre, $bdh) {
    try {
        // Préparez et exécutez la requête
        $stmtCreerFactures = $bdh->prepare("SELECT pact.creerFactures(:idOffre)");
        $stmtCreerFactures->execute([':idOffre' => $idOffre]);

        // Récupérez le résultat
        $result = $stmtCreerFactures->fetch(PDO::FETCH_ASSOC);

        // Retournez la valeur ou une valeur par défaut si rien n'est trouvé
        return $result["creerFactures"] ?? 0;
    } catch (PDOException $e) {
        // Gérez les erreurs
        echo "Erreur lors de la création des factures : " . htmlspecialchars($e->getMessage());
        return 0;
    }
}

//boucle qui parcours les offres et qui appelle la fonction creerFactures pour chaque offre
function creerFacturesPourToutesLesOffres($bdh) {
    try {
        // Préparez et exécutez la requête
        $stmtOffres = $bdh->prepare("SELECT idO FROM pact.offre");
        $stmtOffres->execute();
        $offres = $stmtOffres->fetchAll(PDO::FETCH_ASSOC);

        // Parcourez les offres et créez des factures pour chacune
        foreach ($offres as $offre) {
            creerFactures($offre['ido'], $bdh);
        }
    } catch (PDOException $e) {
        // Gérez les erreurs
        echo "Erreur lors de la création des factures pour toutes les offres : " . htmlspecialchars($e->getMessage());
    }
}


?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/samStyles.css">
    <link rel="stylesheet" href="../css/styleGeneral.css">
    <title>Facturation</title>
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


<body class="BO">
    
    <div class="main-content facture_titre">
    <header>
        <h1>Facturation</h1>
        <div> <?php include_once 'nav.php' ?> </div>
    </header>
    <div>
        <?php
        creerFacturesPourToutesLesOffres($bdh);
        if ($factures) { foreach ($factures as $facture) { ?>
        <article class="facture-composant-liste">
            
                <div class="facture-liste-facture">
                
                    <div>
                        <p>Date :</p>
                        <p><?php echo htmlspecialchars($facture['date_facture']); ?></p>
                    </div>
                    <div>
                        <p>Numéro de facture :</p>
                        <p><?php echo htmlspecialchars($facture['numfacture']); ?></p>
                    </div>
                    <div>
                        <p class="facture_total">Total à payer :</p>
                        <p class="facture_total"><?php echo htmlspecialchars(number_format($facture['prix_offre_ttc'] + $facture['prix_option_ttc'],2)); ?>€/TTC</p>
                    </div>
                        <?php $offre = offre($facture['numfacture'], $bdh);?>
                    <a href="facture_telechargeable.php?numFacture=<?php echo htmlspecialchars($facture['numfacture']); ?>&userID=<?php echo $userID; ?>" class="telecharger facture-button" target="_blank">Télécharger</a>
                    <button class="voir-plus" aria-expanded="true">Voir plus +</button>
                </div>
                <div class="facture-details">
                    <hr>
                    <div class="facture-offre">
                    <div class="facture_tableau_image">
                            <p class="facture_tableau_presentation"></p>
                        </div>
                        <div class="facture_tableau_titre">
                            <p>Titre</p>
                        </div>
                        <div class="facture_tableau_option">
                            <p>Option</p>
                        </div>
                        <div class="facture_tableau_Montant">
                            <p class="montant_facture facture_total">Montant</p>
                        </div>
                        <div class="facture-detail-element">
                            <a href="detail-offre.php?value=<?php echo $offre[0]['ido']?>"><img src="../images/<?php echo image($offre[0]['ido'], $bdh) ?>" alt=""></a>
                        </div>
                        
                        <div class="facture-detail-titre facture-detail-element">
                        <a href="detail-offre.php?value=<?php echo $offre[0]['ido']?>"><p ><?php echo htmlspecialchars($offre[0]['titre']) ?></p></a>
                        </div>
                        <?php
                        $idO = $offre[0]['ido'];

                        $stmtOption = $bdh->prepare("SELECT * FROM pact._option_offre WHERE date_lancement < now() and idO = :idO");
                        $stmtOption->execute([':idO' => $idO]);
                        $options = $stmtOption->fetchAll(PDO::FETCH_ASSOC);
                        
                        ?>
                        <div class="facture-detail-option facture-detail-element">
                            <p ><?php if (!empty($options[0])) {
                                                                    echo htmlspecialchars($offre[0]['option']);
                                                                } else {
                                                            echo "Pas d'option";
                                                                }
                                                    ?></p>
                        </div>
                            <div class="facture-total facture-detail-element">
                        <p class="montant_facture">Prix offre HT : <?php echo number_format($offre[0]['prix_offre_ht'],2) ?>€</p>
                        <p class="montant_facture">Prix option HT : <?php echo number_format($offre[0]['prix_option_ht'],2) ?>€</p>
                        <br>
                        <p class="montant_facture">Total HT : <?php echo number_format($offre[0]['prix_offre_ht'] + $offre[0]['prix_option_ht'],2) ?>€</p>
                    <p class="montant_facture">TVA : <?php echo number_format($offre[0]['prix_offre_ttc'] + $offre[0]['prix_option_ttc'] - $offre[0]['prix_offre_ht'] - $offre[0]['prix_option_ht'],2) ?>€</p>
                    <p class="montant_facture facture_total">Total TTC : <?php echo number_format($offre[0]['prix_offre_ttc'] + $offre[0]['prix_option_ttc'],2) ?>€</p>   
                    </div>
                    
                    </div>
                </div>
        </article> 
        <?php } }
        elseif (!$factures) {?>
            <h2>Vous n'avez pas de factures</h2>
        <?php } ?>
    </div><?php include_once 'footer.php'; ?>
</div>

    <script>
    // Sélectionner tous les boutons "Voir plus"
    const buttons = document.querySelectorAll('.voir-plus');
    const detailFirst = document.getElementsByClassName('facture-details');
    detailFirst[0].classList.toggle('active');

    // Ajouter un gestionnaire d'événement pour chaque bouton
    buttons.forEach(button => {
        button.addEventListener('click', () => {
            // Trouver la section des détails correspondante
            const details = button.parentElement.nextElementSibling;

            // Basculer la classe active
            details.classList.toggle('active');

            // Mettre à jour le texte du bouton en fonction de l'état actif
            const isActive = details.classList.contains('active');
            button.textContent = isActive ? 'Voir moins -' : 'Voir plus +';

            // Mettre à jour l'attribut ARIA pour l'accessibilité
            button.setAttribute('aria-expanded', isActive);
        });
    });
    </script>
</body>

</html>