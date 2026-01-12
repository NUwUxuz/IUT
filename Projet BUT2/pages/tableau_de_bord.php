<!DOCTYPE html>
<html lang="fr">
    <?php
    

use function PHPSTORM_META\type;

    include('../sql/connect_params.php');

    $dbh = new PDO("$driver:host=$server; port=$port; dbname=$dbname", $user, $pass);

    
    try {

        // met à jour les avis blacklistés
        $stmt = $dbh->prepare("SELECT pact.geererBlacklist();");
        $stmt->execute();
        
        //Récupère tous les avis
        $stmt = $dbh->prepare("SELECT * FROM pact.experience");
        $stmt->execute();
        $avis = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    
    try {

        // Récupère le nombre d'avis blacklistés
        $stmt = $dbh->prepare("SELECT ido, nbravisblacklistes FROM pact.offre");
        $stmt->execute();
        $nbr_avis_blacklistes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }


    //Récupère toutes les réponses
    try {
    
        $stmt = $dbh->prepare("SELECT * FROM pact._reponse");
        $stmt->execute();
        $reponse = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        if (isset($_POST['offre_id']) && isset($_POST['status'])) {
            
            try{
    
            
            $offre_id = $_POST['offre_id'];
            $status = $_POST['status'] === 'checked' ? 'false' : 'true';

            if($status === 'true'){
                $stmt = $dbh->prepare("UPDATE pact._offre SET en_ligne = $status, date_publication = now()  WHERE idO = $offre_id");
                $stmt->execute();
            }else{
                $stmt = $dbh->prepare("UPDATE pact._offre SET en_ligne = $status, date_hors_ligne = now() WHERE idO = $offre_id");
                $stmt->execute();
            }

            
            } catch (PDOException $e) {
                print "Erreur !: " . $e->getMessage() . "<br/>";
                die();
            }
    
            
             
        } else {
            echo "<p>Erreur : les données du formulaire ne sont pas valides.</p>";
        }
    }

    $user_cookie = $_COOKIE['user'];

    try{

    $stmt=$dbh->prepare("SELECT o.idO, o.titre, o.resume, o.idC, o.en_ligne, o.type, MIN(p.src_image) AS src_image, 
        pact.prix_previsionnel_offre_TTC(o.idO) as ttc, pact.prix_previsionnel_offre_HT(o.idO) as ht,
        op.cout_ht as opHT, op.cout_ttc as opTTC, op.nom_option,
        o.cout_ht as mHT, o.cout_ttc as mTTC FROM pact.offre o
        LEFT OUTER JOIN pact.photo_offre p ON o.idO = p.idO
        LEFT OUTER JOIN pact._option op on o.idoption = op.idoption
        WHERE o.idC = $user_cookie
        GROUP BY o.idO, o.titre, o.resume, o.idC, o.en_ligne, o.type, op.cout_ht, op.cout_ttc, op.nom_option, o.cout_ht, o.cout_ttc"
    );
    
    $stmt->execute();

    $iban_bic_incomplets = false;
    $data_previsionel = "mHT-mTTC-HT-TTC-opHT-opTTC-nomOP";
    
    // Vérification si le type de compte est 'pro_prive'
    if (isset($_COOKIE['type_compte']) && $_COOKIE['type_compte'] === 'pro_prive') {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!isset($_COOKIE['user'])) {
                die("Erreur : ID du compte introuvable dans les cookies.");
            }
        }
        $idc = $_COOKIE['user'];
            
    
        // Requête pour vérifier si l'IBAN et le BIC existent pour l'idC donné et ne sont pas null
        $query = "SELECT iban, bic FROM pact._secteur_prive WHERE idC = :idC AND iban IS NOT NULL AND bic IS NOT NULL";
        $stmt2 = $dbh->prepare($query);
        $stmt2->bindParam(':idC', $idc, PDO::PARAM_INT);
        $stmt2->execute();

        $result = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        if (empty($result) || $result[0]['iban'] == null || $result[0]['bic'] == null) {
            $iban_bic_incomplets = true;
        }
    }

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }
    $dbh = null;
    ?>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tableau de Bord</title>
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
    </head>
    
    <body class="BO">
        <div class="main-content">
            <header>
                <h1>Vos offres</h1>
                <h2>Gérez vos offres ici</h2>
                <div>
                    <?php include_once'nav.php'; ?>
                </div>
            </header>
            <main>
                <?php
                foreach($result as $key => $valeur){
                ?>
                <section class="section_offre-tableau-de-bord">
                    <img src="../images/<?php echo !is_null($valeur["src_image"]) ?  $valeur["src_image"] : '../images/rien.png'?>" alt="<?php echo $valeur["titre"]; ?>" title="<?php echo $valeur["titre"]; ?>" placeholder="../images/imagesReferences/placeholder.php">
                    <div class="infos_offre">
                        <div class="titre_switch-tableau-de-bord">
                            <p class="titre_offre-tableau-de-bord"><strong><?php echo $valeur["titre"]; ?></strong></p>
                        </div>
                        <p class="description-tableau-de-bord"><?php echo $valeur["resume"]; ?></p>
                        <div class="detail_modif-tableau-de-bord">
                            <a href="detail-offre.php?value=<?php echo $valeur["ido"];?>">Voir les détails</a>
                            <a href="modifier_offre.php?value=<?php echo $valeur["ido"];?>">Modifier les informations</a>
                        </div>
                        <div class="hors_ligne-tableau-de-bord">
                            <form method="POST" action="tableau_de_bord.php" onsubmit="validateAndSubmitForm(this)">
                            <input type="hidden" name="status" value="checked" iban-bic-data=<?=$iban_bic_incomplets ?>>
                            <input type="hidden" id="previsionnel" name="previsionnel" value="checked" mHT-mTTC-HT-TTC-opHT-opTTC-nomOP="<?php echo $valeur["mht"]."_".$valeur["mttc"]."_". $valeur["ht"]."_".$valeur["ttc"]."_".$valeur["opht"]."_".$valeur["opttc"]."_".$valeur["nom_option"]?>">
                                <label class="switch-tableau-de-bord">
                                    <input id="toggle_<?php echo $valeur["ido"]?>_<?php echo $valeur["en_ligne"]?>"type="checkbox" name="status" value="unchecked" <?php if ($valeur["en_ligne"] == 'false') echo 'checked'; ?> idOffre="<?php echo $valeur["ido"];?>">
                                    <span class="slider-tableau-de-bord round"></span>
                                </label>
                                <input type="hidden" name="offre_id" value="<?php echo $valeur["ido"]; ?>">
                            </form>
                        </div>
                        <div class="avis_non_rep">
                            <?php
                            $nbr_avis_non_rep = 0;
                            foreach($avis as $av) {
                                if ($av['ido'] == $valeur['ido']) {
                                    $check = true;
                                    foreach($reponse as $rep) {
                                        if ($av['ida'] == $rep['ida']) {
                                            $check = false;
                                        }
                                    }
                                    if ($check) {
                                        $nbr_avis_non_rep++;
                                    }
                                }
                            }
                            if ($nbr_avis_non_rep > 0) { ?>
                                <p class="FS">Vous avez <?php echo (string)$nbr_avis_non_rep ?> avis non répondu</p>
                            <?php } 
                            if($valeur['type'] == 3) {
                                foreach($nbr_avis_blacklistes as $blacklist) {
                                    if ($blacklist['ido'] == $valeur['ido']) {
                                        ?>
                                        <p class="FS">Avis Blacklistés : <?php echo $blacklist['nbravisblacklistes']; ?> / 3</p>
                                        <?php
                                        break;
                                    }
                                }
                            }
                            ?>
                        </div>
                    </div>
                </section>
                <?php
                }
                ?>
                <form action="creation_offre.php">
                    <input type="submit" value="Créer une nouvelle offre" class="creer_offre-tableau-de-bord"/>
                </form>
                
                <?php

                include_once'footer.php';

                ?>  
            </main>
        </div>

        <div class="modal" id="modal" style="display: none;">
        <form action="" method="post">
            <div id='tarif-modal'>
                <input type="hidden" name="status" value="" id="modal-id">
                <h2 class="titre-modal">Tarifs de votre offre.</h2>
                <div id="tarif-modal">
                    <p>Tarif mensuel : X/HT X/TTC</p>
                    <p>Tarif prévisonnel : X</p>
                </div>
            </div>
            <div id='option-modal' style="display: block;">
                <h2 class="titre-modal">Lancement Option</h2>
                <p class="texte-modal">Vous avez choisit l'option X, avec un tarif hebdomadaire de x€</p>
                <div>
                    <label for="date_lancement">Date de lancement (choisir un lundi): </label>
                    <input type="date" name="date_lancement" id="date_lancement" value="">
                    <div>
                        <span id="error"></span>
                    </div>
                </div>

                <div>
                    <label for="semaine">Nombre de semaine(s) : </label>
                    <select name="semaine" id="semaine">
                        <option value="null" disabled selected value hidden></option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                    </select>
                </div>
            </div>
            <div id="modal-footer" class="texte-modal">
                <div>
                    <a href="conditions_legales.php" >Nos conditions légales</a>
                </div>
                <div>
                    <input type="checkbox" id="condition1" name="condition1" value="condition1">
                    <label for="conditions"> Vous avez lu et accepté les conditions légales.*</label><br>
                    <input type="checkbox" id="condition2" name="condition2" value="condition2">
                    <label for="conditions"> Vous êtes concient que la validation est un acte financièrement engageant.*</label><br>
                </div>
                <div id="boutons-modal">
                    <input type="button" class="btn-notif" id="modal-submit-button" value="Valider"/>
                    <input type="button" class="btn-notif" id="modal-close" value="Annuler">
                </div>
            </div>
        </form>
        </div>

        <script src="../js/tableau_de_bord.js"></script>
        <script src="../js/popup.js"></script>
    </body>
</html>
