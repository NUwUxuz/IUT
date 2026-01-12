<?php
include('../sql/connect_params.php');

if($_SERVER['REQUEST_METHOD']==='POST'){

    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // connexion

    $identifiant = trim($_POST['identifiant']);
    $message_erreur = '';
    $trouve = false;
    try{
        $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname",
            $user, $pass);

        //verifie si membre

        $stmt = $dbh->prepare(
            "SELECT idC, pseudo, mdp FROM pact.membre WHERE pseudo = :pseudo;"
        );
        $stmt->bindParam(':pseudo', $identifiant, PDO::PARAM_STR);
        
            
        $stmt->execute();
        $stmt = $stmt->fetchAll(PDO::FETCH_ASSOC);

        
        if ($stmt!=[]) {
            if ($stmt[0]['mdp'] == $_POST['password']) {
                $idC = strval($stmt[0]['idc']);
                setcookie("user", $idC, time() + 3600, '/');
                setcookie("type_compte", 'membre', time() + 3600, '/');

                $_COOKIE['user'] = $idC;
                $_COOKIE['type_compte'] = 'membre';
                $trouve = true;
            }
        }
        
        //verifie si professionnel

        $identifiant = (int)$identifiant;

        $stmt = $dbh->prepare(
            "SELECT idC, codePro, mdp FROM pact.professionnel WHERE codePro = :codePro;"
        );
        $stmt->bindParam(':codePro', $identifiant, PDO::PARAM_INT);

        $stmt->execute();
        $stmt = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($stmt!=[]) {
            if ($stmt[0]['mdp'] == $_POST['password']) {
                $idC = strval($stmt[0]['idc']);
                setcookie("user", $idC, time() + 3600, '/');

                $_COOKIE['user'] = $idC;

                $stmt = $dbh->prepare(
                    "select * from pact.professionnel where idC in (select idC from pact.secteur_prive where idC = $idC);"
                );
        
                $stmt->execute();
                $stmt = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($stmt!=[]) {
                    setcookie("type_compte", 'pro_prive', time() + 3600, '/');

                    $_COOKIE['type_compte'] = 'pro_prive';
                }else{
                    setcookie("type_compte", 'pro_public', time() + 3600, '/');

                    $_COOKIE['type_compte'] = 'pro_public';
                }
                $trouve = true;
            }
        }

        if (!$trouve){
            $message_erreur = "Nom d'utilisateur ou mot de passe incorrect, veuillez réessayer";
        }

    } catch (PDOException $e) {
        print "Erreur !: " . $e->getMessage() . "<br/>";
        die();
    }

    if($_POST['type_form'] == 'supp-avis'){
        try{

            $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);
            $stmt = $dbh->prepare("DELETE FROM pact.avis WHERE idA = :ida");        
            $stmt->bindParam(':ida', $_POST['id_avis']);
            $stmt->execute();

            $dbh=null;

            header("Location: detail-offre.php?value=$_GET[value]");
            exit;

        }catch(PDOException $e){

            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();

        }

    }else if($_POST['type_form'] == 'poste-avis'){

        try{

            $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);
            $note = (int)$_POST['nbr_etoile'];
            $titre = $_POST['titre_avis'];
            $com = $_POST['zone_avis'];
            $ido = (int)$_GET['value'];
            $idc = (int)$_COOKIE['user'];
            $contexte = $_POST['options'];


            $stmt = $dbh->prepare("INSERT INTO pact.experience(idC, idO, titre_avis, note, corps, date_visite, contexte)
            VALUES (:idc, :ido, :titre, :note, :corps, NOW(), :contexte);
            ");
            $stmt->execute([
                ':idc' => $idc,
                ':ido' => $ido,
                ':titre' => $titre,
                ':note' => $note,
                ':corps' => $com,
                ':contexte' => $contexte
            ]);

            $dbh=null;

            header("Location: detail-offre.php?value=$ido");
            exit;

        }catch(PDOException $e){
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    } else if($_POST['type_form'] == 'poste-reponse') {
        try {
            $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);
            $ida = (int)$_POST['avis'];
            $idc = (int)$_COOKIE['user'];
            $corps = $_POST['zone_reponse'];

            $stmt = $dbh->prepare("INSERT INTO pact._reponse(idA, idC, corps) VALUES (:ida, :idc, :corps)");
            $stmt->execute([
                ':ida' => $ida,
                ':idc' => $idc,
                ':corps' => $corps
            ]);

            $dbh=null;
            $refresh = "Location: detail-offre.php?value=". $_POST['ido'];
            header($refresh);
            exit;

        }catch(PDOException $e){
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }

}


// Sécurisation de l'ID d'annonce
$idAnnonce = isset($_GET['value']) ? (int)$_GET['value'] : 0;
if ($idAnnonce === 0) {
    die("ID d'annonce invalide.");
}


$id_compte = 0;
$id_avis = 0;
try {
    $bdh = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);

    // Requête pour récupérer les informations de l'offre
    $stmt = $bdh->prepare("SELECT * FROM pact.offre WHERE ido = :idAnnonce");
    $stmt->execute([':idAnnonce' => $idAnnonce]);
    $offre = $stmt->fetch(PDO::FETCH_ASSOC);

    // met à jour les avis blacklistés
    $stmt = $bdh->prepare("SELECT pact.geererBlacklist()");
    $stmt->execute();

    if (!$offre) {
        die("Aucune offre trouvée.");
    }

    // Requête pour récupérer les infos optionnel des activités
    $stmtActivite = $bdh->prepare("SELECT * FROM pact.activite WHERE ido = :idAnnonce");
    $stmtActivite->execute([':idAnnonce' => $idAnnonce]);
    $activite = $stmtActivite->fetchAll(PDO::FETCH_ASSOC);

    $stmtParc = $bdh->prepare("SELECT * FROM pact.parc_d_attraction WHERE ido = :idAnnonce");
    $stmtParc->execute([':idAnnonce' => $idAnnonce]);
    $parc_actraction = $stmtParc->fetchAll(PDO::FETCH_ASSOC);

    $stmtSpectacle = $bdh->prepare("SELECT * FROM pact.spectacle WHERE ido = :idAnnonce");
    $stmtSpectacle->execute([':idAnnonce' => $idAnnonce]);
    $spectacle = $stmtSpectacle->fetchAll(PDO::FETCH_ASSOC);

    $stmtRestaurant = $bdh->prepare("SELECT * FROM pact.restauration WHERE ido = :idAnnonce");
    $stmtRestaurant->execute([':idAnnonce' => $idAnnonce]);
    $restaurant = $stmtRestaurant->fetchAll(PDO::FETCH_ASSOC);

    $stmtVisite = $bdh->prepare("SELECT * FROM pact.visite WHERE ido = :idAnnonce");
    $stmtVisite->execute([':idAnnonce' => $idAnnonce]);
    $visite = $stmtVisite->fetchAll(PDO::FETCH_ASSOC);

    // Requête pour récupérer les images associées
    $stmtPhotos = $bdh->prepare("SELECT src_image FROM pact.photo_offre WHERE idO = :idAnnonce");
    $stmtPhotos->execute([':idAnnonce' => $idAnnonce]);
    $photos = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);

    $pdo = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare('SELECT * FROM pact._recommande WHERE idA = :id_avis AND idC = :id_compte');
    $stmt->execute(['id_compte' => $id_compte, 'id_avis' => $id_avis]);
    $like_compte = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare('SELECT * FROM pact._recommande_pas WHERE idA = :id_avis AND idC = :id_compte');
    $stmt->execute(['id_compte' => $id_compte, 'id_avis' => $id_avis]);
    $like_compte = $stmt->fetch(PDO::FETCH_ASSOC);

    // Requête pour récupérer les avis associés à l'offre
    if(isset($_COOKIE['user'])){
        $stmtAvis = $bdh->prepare("SELECT * FROM pact.experience WHERE idO = :idAnnonce and idC = :idCompte");
        $stmtAvis->execute([':idAnnonce' => $idAnnonce, ':idCompte' => $_COOKIE['user']]);
        $avis = $stmtAvis->fetchAll(PDO::FETCH_ASSOC);
        $stmtAvis = $bdh->prepare("SELECT * FROM pact.experience WHERE idO = :idAnnonce and idC != :idCompte");
        $stmtAvis->execute([':idAnnonce' => $idAnnonce, ':idCompte' => $_COOKIE['user']]);
        $tab = $stmtAvis->fetchall(PDO::FETCH_ASSOC);
        foreach($tab as $val):
        array_push($avis, $val);
        endforeach;
    }else{
        $stmtAvis = $bdh->prepare("SELECT * FROM pact.experience WHERE idO = :idAnnonce");
        $stmtAvis->execute([':idAnnonce' => $idAnnonce]);
        $avis = $stmtAvis->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer le blacklistage des avis
    /*
    foreach ($avis as &$avisIndividuel) {
        
        $stmtBlackliste = $bdh->prepare("SELECT * FROM pact.avis WHERE idA = :idA");
        $stmtBlackliste->execute([':idA' => $avisIndividuel['ida']]);
        $blacklisteData = $stmtBlackliste->fetch(PDO::FETCH_ASSOC);


    
        var_dump($blacklisteData); // Debugging si besoin
    }
    */
    
    /*
    // Requête pour griser tout les avis qui ont déjà été signalé par l'utilisateur
    $idAvis = $avisIndividuel['ida'];
    $idSignaleur = (int)$_COOKIE['user'];

    $bdh = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);
    $stmt = $bdh->prepare("
        SELECT COUNT(*) FROM pact._signal_avis 
        WHERE idA = :idA AND idSignaleur = :idSignaleur
    ");
    $stmt->execute([
        ':idA' => $idAvis,
        ':idSignaleur' => $idSignaleur
    ]);
    $alreadySignaled = $stmt->fetchColumn() > 0;
    */

    // Récupération du téléphone du professionnel
    $stmtTelephone = $bdh->prepare("
        SELECT telephone 
        FROM pact.professionnel p 
        INNER JOIN pact.offre o ON p.idC = o.idC 
        WHERE o.idC = :idC
    ");
    $stmtTelephone->execute([':idC' => $offre['idc']]);
    $telephone = $stmtTelephone->fetch(PDO::FETCH_ASSOC)['telephone'] ?? 'Non disponible';

} catch (PDOException $e) {
    echo "Erreur : " . htmlspecialchars($e->getMessage());
    die();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo htmlspecialchars($offre['categorie'] ?? 'Détails de l\'offre'); ?>
    </title>
    <link rel="stylesheet" href="../css/samStyles.css">
    <link rel="stylesheet" href="../css/styleGeneral.css">
    <script src="../js/detail-offre-offre.js"></script>
    <script src="../js/detail_offre.js"></script>
    
    <!-- Je ne sais pas pourquoi, mais cette fonction refuse de fonctionner si je ne l'inclut pas dans le script, même si il est inclut dans detail-offre.js -->
    <script>
    function toggle_reponse(id) {
        var form = document.getElementById("form_reponse_" + id);
        if (form.style.display === "none" || form.style.display === "") {
            form.style.display = "inline";
        } else {
            form.style.display = "none";
        }
    }
    </script>
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

<div class="desktop-element">
    <?php include_once 'nav.php';?>
</div>

<body
    class="main-content <?php echo ($_COOKIE['type_compte'] ?? '') === 'pro_public' || ($_COOKIE['type_compte'] ?? '') === 'pro_prive' ? 'BO' : 'FO'; ?>">

    <div class="body">
        <section class="section-page part-annonce">
            <header>
                <h1 class="titre-rouge">
                    <?php echo htmlspecialchars($offre['titre']); ?>
                </h1>
                <h2 class="sous-titre">
                    <?php echo htmlspecialchars($offre['ville']); ?> |
                    <?php echo htmlspecialchars($telephone); ?>
                    <?php if (!empty($spectacle[0]['duree'])) echo " | Durée : " . htmlspecialchars(substr($spectacle[0]['duree'], 0, 5)); ?>
                    <?php if (!empty($activite[0]['duree'])) echo " | Durée : " . htmlspecialchars(substr($activite[0]['duree'], 0, 5)); ?>
                    <?php if (!empty($visite[0]['duree'])) echo " | Durée : " . htmlspecialchars(substr($visite[0]['duree'], 0, 5)); ?>

                    <?php if (!empty($activite[0]['age_min'])) echo " | Âge : " . $activite[0]['age_min'] . " ans"; ?>
                    <?php if (!empty($parc_actraction[0]['age_min'])) echo " | Âge : " . $parc_actraction[0]['age_min'] . " ans"; ?>

                    <?php   if (!empty($restaurant[0]['gamme_prix'])) {echo " | Prix : " . htmlspecialchars($restaurant[0]['gamme_prix']);}
                        elseif (!empty($offre['prix_min'])) {echo " | Prix : " . htmlspecialchars($offre['prix_min']) . " €";} 
                ?>

                    <?php echo htmlspecialchars($offre['gamme_prix'] ?? ''); ?>
                </h2>

                <!-- Carrousel -->
                <div class="container NoSelect">
                    <div class="carousel">
                        <div class="carousel-inner">
                            <?php foreach ($photos as $index => $photo): ?>
                            <div class="slide">
                                <img src="../images/<?php echo htmlspecialchars($photo['src_image']); ?>"
                                    alt="Image <?php echo $index + 1; ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="carousel-controls NoSelect">
                            <button id="prev"><img src="../images/fleche.png" alt="Précédent"></button>
                            <button id="next"><img src="../images/fleche.png" alt="Suivant"></button>
                        </div>
                        <div class="carousel-dots NoSelect"></div>
                    </div>
                </div>

                <!-- Informations -->
                <div class="infos-offre">
                    <div class="infos-avis">
                        <div class="etoiles">
                            <?php
                            $nbrAvis = $offre['nbravis'];
                            $nbrAvisBlacklist = $offre['nbravisblacklistes'];
                            $fullStars = floor($offre["moy_note"]);
                            $halfStar = ($offre["moy_note"] - $fullStars >= 0.5) ? 1 : 0;
                            $emptyStars = 5 - $fullStars - $halfStar;
                        ?>

                            <?php for ($i = 0; $i < $fullStars; $i++): ?>
                            <img src="../icons/<?php echo ($_COOKIE['type_compte'] ?? '') === 'pro_public' || ($_COOKIE['type_compte'] ?? '') === 'pro_prive' ? 'etoile_rouge' : 'etoile_bleue'; ?>.png"
                                alt="Étoile">
                            <?php endfor; ?>

                            <?php if ($halfStar): ?>
                            <img src="../icons/<?php echo ($_COOKIE['type_compte'] ?? '') === 'pro_public' || ($_COOKIE['type_compte'] ?? '') === 'pro_prive' ? 'demi_etoile_rouge' : 'demi_etoile_bleue'; ?>.png"
                                alt="Demi Étoile">
                            <?php endif; ?>

                            <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                            <img src="../icons/<?php echo ($_COOKIE['type_compte'] ?? '') === 'pro_public' || ($_COOKIE['type_compte'] ?? '') === 'pro_prive' ? 'etoile_rouge_vide' : 'etoile_bleue_vide'; ?>.png"
                                alt="Étoile Vide">
                            <?php endfor; ?>

                        </div>
                        <p class="nb-avis-info-offre">
                            <?php echo $nbrAvis?>&#xA0;Avis
                        </p>
                    </div>
                    <div class="infos-accessibilite">

                        <img src="../images/Wheelchair.png" alt="Accessibilité">
                        <p>
                            <?php echo htmlspecialchars($offre['accessibilite']); ?>
                        </p>
                    </div>
                </div>
            </header>

            <!-- Description -->
            <article class="description-offre">
                <h3>Description :</h3>
                <p>
                    <?php echo nl2br(htmlspecialchars($offre['description'])); ?>
                </p>
            </article>

            <article class="part-contact">
                <div class="side-contact">
                    <div>
                        <h3>Contact :</h3>
                        <div class="element-contact">
                            <?php 
                        $address = $offre['numero_voie'] . $offre['voie'] . $offre['ville'] . $offre['code_postal'];
                        ?>
                            <a href="https://www.google.com/maps/dir// <?php echo $address ?>">
                                <img src="../images/maison.png" alt="icon maison (localisation)">
                                <p class="lien">
                                    <?php
                                echo $offre['numero_voie'];
                                echo " ";
                                echo $offre['voie'];
                                echo " ";
                                echo $offre['ville'];
                                echo " ";
                                echo $offre['code_postal'];

                            ?>
                                </p>
                            </a>
                        </div>
                        <div class="element-contact">
                            <a href="tel:<?php 
                            echo htmlspecialchars($telephone);
                                ?> ">
                                <img src="../images/telephone.png" alt="icon telephone">
                                <p class="lien">
                                    <?php 
                                        echo htmlspecialchars($telephone);
                                    ?>
                                </p>
                            </a>
                        </div>

                        <?php
                        if (!empty($offre['site_web'])) { ?>
                        <div class="element-contact">
                            <a href=" <?php echo $offre['site_web']; ?>">
                                <img src="../images/pc.png" alt="icon pc (lien vers le site)">
                                <p class="lien">
                                    <?php
                            echo $offre['site_web']; ?>
                                </p>
                            </a>
                        </div>
                    </div>
                  <?php

                        } ?>
                </div>
                <!--<div class="horaires">
                        <h3>Horaires et tarifs :</h3>
                        <div>
                            <img src="../images/Delivery Time.png" alt="icon heure">
                            <p>8h - 13h et 14h30 - 18h du lundi au vendredi</p>
                        </div>
                        <div>
                            <img src="../images/Stack of Coins.png" alt="icon argent">
                            <p>12 € par adulte, gratuit pour les enfants (-12 ans)</p>
                        </div>
                    </div>-->

                <!--
            </div>
            <?php 
                    if (!empty($parc_actraction[0]['src_image'])) { ?>
            <div>
                <img class="carte" src="../images/<?php echo $parc_actraction[0]['carte'] ?>" alt="plan">
            </div>
            <?php
                    } elseif (!empty($restaurant[0]['src_image'])) { ?>
            <div>
                <img class="carte" src="../images/<?php echo $restaurant[0]['src_image'] ?>" alt="carte">
            </div>
            <?php
                    }
                ?>
            </div>
            -->
 </article>
        <button class="desktop-element bouton-retour NoSelect">
            <a href="./accueil.php">
                <img src="../icons/arrow-left-o.svg" alt="Retour">
                Retour
            </a>
        </button>
    </section>

    <!-- Avis -->
    <section class="section-page part-avis">
        <header class="header-avis">
            <h3>Avis :
                <?php
                    echo $nbrAvis;
                ?>
            </h3>
            <?php if (isset($_COOKIE['type_compte']) && $_COOKIE['type_compte'] == "pro_prive" && $offre['type'] == 3): ?>
                <?php if ($nbrAvisBlacklist > 0 && $nbrAvisBlacklist <= 3): ?>
                    <button id="btn-blacklist" data-nbr-avis-blacklist="<?php echo $nbrAvisBlacklist; ?>" onclick="toggleBlacklistButton()">Afficher avis blacklistés (<?php echo $nbrAvisBlacklist; ?> / 3)</button>
                <?php elseif ($nbrAvisBlacklist == 0): ?>
                    <button id="btn-blacklist" style="cursor: not-allowed;" disabled>Aucun avis blacklistés (<?php echo $nbrAvisBlacklist; ?>)</button>
                <?php endif; ?>
            <?php endif; ?>
        </header>
        <article class="article-avis">
            <?php if(!isset($_COOKIE['type_compte']) || $_COOKIE['type_compte'] == "membre"){
                    if (isset($_COOKIE['user'])){
                    $avis_publie = false;
                    if ($nbrAvis > 0){ foreach ($avis as $avisIndividuel):
                        if($_COOKIE['user'] == $avisIndividuel['idc']){
                            $avis_publie = true;
                        }
                    endforeach;}
                    if(!$avis_publie){ ?>
                <article class="poster_avis">
                    <h3>Poster un avis :</h3>
                    <div class="notes_avis">
                        <?php for($i = 1; $i<6; $i++):?>
                        <img src="../icons/etoile_bleue_vide.png" width="35px" height="35px"
                            id="etoile_note<?php echo $i?>">
                        <?php endfor;?>
                    </div>
                    <form method="POST" action="detail-offre.php?value=<?php echo $offre['ido']?>"
                        class="zone-btn-publier">
                        <input type="hidden" name="type_form" value="poste-avis">
                        <input type="hidden" id="nbr_etoile" name="nbr_etoile"
                            value="<?php echo isset($_POST['etoile']) ? $_POST['etoile'] : ''; ?>">
                        <textarea maxlength="30" type="text" name="titre_avis" id="titre_avis"
                            placeholder="Titre de l'avis"
                            required="required"><?php echo isset($_POST['titreAvis']) ? $_POST['titreAvis'] : ''; ?></textarea>
                        <textarea minlength="30" maxlength="255" type="text" name="zone_avis" id="ecrire_avis"
                            placeholder="Veuillez écrire dans cette zone pour pouvoir déposer un avis sur cette offre."><?php echo isset($_POST['contenuAvis']) ? $_POST['contenuAvis'] : ''; ?></textarea>
                        <!--<input type="file" accept=".png, .jpeg, .svg, .webp" id="image_avis" name="image_avis">-->
                        <label for="options" id="titre-contexte">Contexte de la visite :</label>
                        <select id="options-contexte" name="options">
                            <option value="Affaires" <?=isset($_POST['contextAvis']) &&
                                $_POST['contextAvis']==='Affaires' ? 'selected' : '' ?>>Affaires</option>
                            <option value="Couple" <?=isset($_POST['contextAvis']) && $_POST['contextAvis']==='Couple'
                                ? 'selected' : '' ?>>Couple</option>
                            <option value="Famille" <?=isset($_POST['contextAvis']) && $_POST['contextAvis']==='Famille'
                                ? 'selected' : '' ?>>Famille</option>
                            <option value="Amis" <?=isset($_POST['contextAvis']) && $_POST['contextAvis']==='Amis'
                                ? 'selected' : '' ?>>Amis</option>
                            <option value="Seul" <?=isset($_POST['contextAvis']) && $_POST['contextAvis']==='Seul'
                                ? 'selected' : '' ?>>Seul</option>
                        </select>
                        <input type="submit" id="bouton_publier" value="Publier">
                    </form>
                    <?php
                }
                }else{ ?>
                    <h3>Poster un avis :</h3>
                    <div class="notes_avis">
                        <?php for($i = 1; $i<6; $i++):?>
                        <img src="../icons/etoile_bleue_vide.png" width="35px" height="35px"
                            id="etoile_note<?php echo $i; ?>">
                        <?php endfor;?>
                    </div>
                    <input type="hidden" id="nbr_etoile" name="nbr_etoile"
                        value="<?php echo isset($_POST['etoile']) ? $_POST['etoile'] : ''; ?>">
                    <textarea maxlength="30" type="text" name="titre_avis" id="titre_avis" placeholder="Titre de l'avis"
                        required="required"><?php echo isset($_POST['titreAvis']) ? $_POST['titreAvis'] : ''; ?></textarea>
                    <textarea minlength="30" maxlength="255" type="text" name="zone_avis" id="ecrire_avis"
                        placeholder="Veuillez écrire dans cette zone pour pouvoir déposer un avis sur cette offre."><?php echo isset($_POST['contenuAvis']) ? $_POST['contenuAvis'] : ''; ?></textarea>
                    <!--<input type="file" accept=".png, .jpeg, .svg, .webp" id="image_avis" name="image_avis">-->
                    <label for="options" id="titre-contexte">Contexte de la visite :</label>
                    <select id="options-contexte" name="options">
                        <option value="Affaires" <?=isset($_POST['contextAvis']) && $_POST['contextAvis']==='Affaires'
                            ? 'selected' : '' ?>>Affaires</option>
                        <option value="Couple" <?=isset($_POST['contextAvis']) && $_POST['contextAvis']==='Couple'
                            ? 'selected' : '' ?>>Couple</option>
                        <option value="Famille" <?=isset($_POST['contextAvis']) && $_POST['contextAvis']==='Famille'
                            ? 'selected' : '' ?>>Famille</option>
                        <option value="Amis" <?=isset($_POST['contextAvis']) && $_POST['contextAvis']==='Amis'
                            ? 'selected' : '' ?>>Amis</option>
                        <option value="Seul" <?=isset($_POST['contextAvis']) && $_POST['contextAvis']==='Seul'
                            ? 'selected' : '' ?>>Seul</option>
                    </select>

                    <button id="bouton_publier" type="button">
                        Valider
                    </button>
                    <?php } ?>
                </article>
                <?php   } ?>

                <?php 
            $id_avis = 0;
            if ($nbrAvis > 0 || $nbrAvisBlacklist > 0) {
                foreach ($avis as $avisIndividuel): 
                
                $stmtCompte = $bdh->prepare("SELECT * FROM pact.membre WHERE idC = :idC");
                $stmtCompte->execute([':idC' => $avisIndividuel['idc']]);
                $compte = $stmtCompte->fetch(PDO::FETCH_ASSOC);      
                
                $stmtReponse = $bdh->prepare("SELECT * FROM pact._reponse WHERE idA = :idA");
                $stmtReponse->execute([':idA' => $avisIndividuel['ida']]);
                $reponse = $stmtReponse->fetch(PDO::FETCH_ASSOC);

                $stmtBlackliste = $bdh->prepare("SELECT * FROM pact.avis WHERE idA = :idA");
                $stmtBlackliste->execute([':idA' => $avisIndividuel['ida']]);
                $blacklisteData = $stmtBlackliste->fetch(PDO::FETCH_ASSOC);
                
                // var_dump($blacklisteData);
                $classBlacklist = isset($blacklisteData['blackliste']) && $blacklisteData['blackliste'] ? 'blacklisted' : '';
            
          //vérifie si avis est blacklisté
                $stmtCompte = $bdh->prepare("SELECT blackliste FROM pact._avis WHERE idA = :idA");
                $stmtCompte->execute([':idA' => $avisIndividuel['ida']]);
                $estBlackliste = $stmtCompte->fetch(PDO::FETCH_ASSOC)["blackliste"];

                // Vérification avis deja signalé 

                // Vérifier si l'avis a déjà été signalé par l'utilisateur connecté
                /*$idAvis = $avisIndividuel['ida'];
                $idSignaleur = isset($_COOKIE['user']) ? (int)$_COOKIE['user'] : 0; // Récupérer l'utilisateur depuis le cookie

                $stmtSignal = $bdh->prepare("
                    SELECT COUNT(*) FROM pact._signal_avis 
                    WHERE idA = :idA AND idSignaleur = :idSignaleur
                ");
                $stmtSignal->execute([
                    ':idA' => $idAvis,
                    ':idSignaleur' => $idSignaleur
                ]);
                $alreadySignaled = $stmtSignal->fetchColumn() > 0;

                // Vérifier si une réponse  a déjà été signalé par l'utilisateur connecté
                $idAvis = $avisIndividuel['ida'];
                $idSignaleur = isset($_COOKIE['user']) ? (int)$_COOKIE['user'] : 0; // Récupérer l'utilisateur depuis le cookie

                $stmtSignal = $bdh->prepare("
                    SELECT COUNT(*) FROM pact._signal_reponse 
                    WHERE idA = :idA AND idSignaleur = :idSignaleur
                ");
                $stmtSignal->execute([
                    ':idA' => $idAvis,
                    ':idSignaleur' => $idSignaleur
                ]);
                $alreadySignaled = $stmtSignal->fetchColumn() > 0;
                */
                /*
                // Récupérer l'utilisateur depuis le cookie
                $idSignaleur = isset($_COOKIE['user']) ? (int)$_COOKIE['user'] : 0;

                // Vérification si l'avis a déjà été signalé
                $idAvis = $avisIndividuel['ida'];

                $stmtSignalAvis = $bdh->prepare("
                    SELECT COUNT(*) FROM pact._signal_avis 
                    WHERE idA = :idA AND idSignaleur = :idSignaleur
                ");
                $stmtSignalAvis->execute([
                    ':idA' => $idAvis,
                    ':idSignaleur' => $idSignaleur
                ]);
                $alreadySignaledAvis = $stmtSignalAvis->fetchColumn() > 0;

                // Vérification si une réponse liée à l'avis a déjà été signalée
                $stmtSignalReponse = $bdh->prepare("
                    SELECT COUNT(*) FROM pact._signal_reponse 
                    WHERE idA = :idA AND idSignaleur = :idSignaleur
                ");
                $stmtSignalReponse->execute([
                    ':idA' => $idAvis,
                    ':idSignaleur' => $idSignaleur
                ]);
                $alreadySignaledReponse = $stmtSignalReponse->fetchColumn() > 0;
                */
                /*
                // Récupération de l'ID de l'utilisateur (depuis les cookies)
                $idSignaleur = isset($_COOKIE['user']) ? (int)$_COOKIE['user'] : 0;

                // Récupération de l'ID de l'avis
                $idAvis = $avisIndividuel['ida'];

                // Vérification si l'avis a déjà été signalé
                $stmtSignalAvis = $bdh->prepare("
                    SELECT COUNT(*) FROM pact._signal_avis 
                    WHERE idA = :idA AND idSignaleur = :idSignaleur
                ");
                $stmtSignalAvis->execute([
                    ':idA' => $idAvis,
                    ':idSignaleur' => $idSignaleur
                ]);
                $alreadySignaledAvis = $stmtSignalAvis->fetchColumn() > 0;

                // Vérification si une réponse liée à l'avis a déjà été signalée
                $stmtSignalReponse = $bdh->prepare("
                    SELECT COUNT(*) FROM pact._signal_reponse 
                    WHERE idA = :idA AND idSignaleur = :idSignaleur
                ");
                $stmtSignalReponse->execute([
                    ':idA' => $idAvis,
                    ':idSignaleur' => $idSignaleur
                ]);
                $alreadySignaledReponse = $stmtSignalReponse->fetchColumn() > 0;

                // Déterminer l'état du bouton en fonction des résultats
                $buttonDisabled = $alreadySignaledAvis || $alreadySignaledReponse;
                $buttonStyle = $buttonDisabled ? 'cursor: not-allowed; opacity: 0.5;' : '';
                $buttonText = $alreadySignaledAvis ? 'Déjà signalé' : "Signaler l'avis";
                // Vérifier si l'utilisateur a signalé cet avis ou une de ses réponses
                */
                /*
                if ($alreadySignaledAvis) {
                    echo "L'avis a déjà été signalé par cet utilisateur.";
                } elseif ($alreadySignaledReponse) {
                    echo "Une réponse à cet avis a déjà été signalée par cet utilisateur.";
                } else {
                    echo "Cet utilisateur n'a pas encore signalé cet avis ou ses réponses.";
                }
                */
?>
            <div class="alignement-conteneur-avis <?= $classBlacklist ?>">
                <div class="header-avis-profil">
                    <div class="profil">
                        <div class="container-profil">
                            <img src="../images/1 2.png" alt="Photo de profil" width="40px" height="40px">
                            <div class="titre-contexte">
                                <div class="titre-date">
                                    <h4>
                                        <?php echo htmlspecialchars($avisIndividuel['titre_avis']); ?>
                                    </h4>
                                    <p><?php echo htmlspecialchars($avisIndividuel['date_visite']); ?></p>
                                </div>
                                <div class="pseudo-contexte">
                                    <?php if($avisIndividuel['pseudo'] == 'Anonyme') { ?>
                                        <p style="font-style: italic" class="ligne_sepa_contexte">
                                            <?php echo htmlspecialchars($avisIndividuel['pseudo']); ?>
                                        </p>
                                    <?php } else { ?>
                                        <p class="ligne_sepa_contexte">
                                            <?php echo htmlspecialchars($avisIndividuel['pseudo']); ?>
                                        </p>
                                    <?php } ?>
                                    <p><?php echo htmlspecialchars($avisIndividuel['contexte']); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="etoile-avis-detail-offre">
                            <?php
                        $full = (int) $avisIndividuel['note'];
                        $empty = 5 - $full;
                        for ($i = 0; $i < $full; $i++): ?>
                            <img src="../icons/<?php echo ($_COOKIE['type_compte'] ?? '') === 'pro_public' || ($_COOKIE['type_compte'] ?? '') === 'pro_prive' ? 'etoile_rouge' : 'etoile_bleue'; ?>.png"
                                width="20px" height="20px" alt="Étoile">
                            <?php endfor; ?>
                            <?php for ($i = 0; $i < $empty; $i++): ?>
                            <img src="../icons/<?php echo ($_COOKIE['type_compte'] ?? '') === 'pro_public' || ($_COOKIE['type_compte'] ?? '') === 'pro_prive' ? 'etoile_rouge_vide' : 'etoile_bleue_vide'; ?>.png"
                                width="20px" height="20px" alt="Étoile Vide">
                            <?php endfor;
                        ?>
                        </div>
                    </div>
                    </div>
                    <?php if (isset($_COOKIE['type_compte']) && ($_COOKIE['type_compte'] == "pro_public" || $_COOKIE['type_compte'] == "pro_prive") && $offre['idc'] == $_COOKIE['user']) { 
                    if (!isset($reponse['ida'])) { ?>
                    <div class="commentaire-consulte exergue">
                        <p>
                            <?php echo nl2br(htmlspecialchars($avisIndividuel['corps'])); ?>
                        </p>
                    </div>
                    <?php } else { ?>
                    <div class="commentaire-consulte">
                        <p>
                            <?php echo nl2br(htmlspecialchars($avisIndividuel['corps'])); ?>
                        </p>
                    </div>
                    <?php } 
                }else{
                    ?>
                    <div class="commentaire-consulte">
                        <p>
                            <?php echo nl2br(htmlspecialchars($avisIndividuel['corps'])); ?>
                        </p>
                    </div>
                    <?php
                } ?>

                    <?php
                if(isset($_COOKIE['user']) && $avisIndividuel['idc'] == $_COOKIE['user']){
                    ?>
                    <form method="POST" action="detail-offre.php?value=<?php echo $idAnnonce; ?>" class="form-supp">
                        <input type="hidden" name="type_form" value="supp-avis">
                        <input type="hidden" name="id_avis" value=<?php echo $avisIndividuel['ida']; ?>>
                        <input type="button" name="bouton_supp" class="bouton-supp" value="Supprimer l'avis">
                    </form>
                    <hr>
                    <?php
                }

                ?>
                    <div class="signaler-like">
                        <?php
                
                if (isset($_COOKIE['type_compte']) && ($_COOKIE['type_compte'] == "membre" || $_COOKIE['type_compte'] == "pro_public" || $_COOKIE['type_compte'] == "pro_prive") && $avisIndividuel['idc'] != $_COOKIE['user']) : ?>
                        <div class="signaler">
                            <button type="button" class="btn-signaler"
                                id="btn_signaler_avis_<?php echo $_COOKIE['user']; ?>_<?php echo $avisIndividuel['ida']; ?>">
                                  <img src="../icons/warningred.png" alt="Icône signaler" id="icone-signaler">
                                <p>Signaler l'avis</p>
                            </button>
                        </div>
                        <?php

if ($_COOKIE['type_compte'] == "pro_prive" && $offre['type'] == 3 && $offre['idc'] == $_COOKIE['user']) : ?>
    <?php if ($estBlackliste) : ?>
        <!-- Avis déjà blacklisté -->
        <div class="blacklist">
            <button type="button" class="btn-blacklist" style="cursor: not-allowed; opacity: 0.5;" disabled>
                <img src="../icons/blacklist.png" alt="Icône Blacklist" id="icone-blacklist">
                <p>Avis déjà blacklisté</p>
            </button>
        </div>
    <?php elseif ($nbrAvisBlacklist >= 3) : ?>
        <!-- Plus de jetons de blacklist -->
        <div class="blacklist">
            <button type="button" class="btn-blacklist" style="cursor: not-allowed; opacity: 0.5;" disabled>
                <img src="../icons/blacklist.png" alt="Icône Blacklist" id="icone-blacklist">
                <p>Plus de jetons de blacklist</p>
            </button>
        </div>
    <?php else : ?>
        <!-- Option pour blacklister l'avis -->
        <div class="blacklist">
            <button type="button" class="btn-blacklist">
                <img src="../icons/blacklist.png" alt="Icône Blacklist" id="icone-blacklist">
                <p>Blacklister l'avis</p>
            </button>
            <div class="blacklist-content">
                <button type="button" class="btn-blacklist-irr"
                    id="btn_blacklist_<?php echo $_COOKIE['user'] ?>_<?php echo $avisIndividuel['ida'] ?>">
                    Blacklister
                </button>
                <button type="button"
                    id="btn_blacklist_<?php echo $_COOKIE['user'] ?>_<?php echo $avisIndividuel['ida'] ?>"
                    class="btn-blacklist-rev">Blacklister temporairement</button>
            </div>
        </div>
    <?php endif; ?>
<?php endif; 
endif; 

                if(isset($_COOKIE['type_compte']) && $_COOKIE['type_compte'] == "membre" && $avisIndividuel['idc'] != $_COOKIE['user']){
                ?>
                        <div class="like-dislike">
                            <?php 
                        $stmt = $dbh->prepare("SELECT * FROM pact.avis WHERE idA = :idAvis");
                        $stmt->execute([':idAvis'=>$avisIndividuel['ida']]);
                        $like_dislike = $stmt->fetch(PDO::FETCH_ASSOC);
                        ?>

                            <!-- From Uiverse.io by catraco -->
                            <label class="container_like LikeButton">
                                <input checked="checked" type="checkbox"
                                    id="likeButton_<?php echo $_COOKIE['user']?>_<?php echo $avisIndividuel['ida']?>">
                                <svg viewBox="0 0 512 512" height="1em" xmlns="http://www.w3.org/2000/svg"
                                    class="thumbs-up-regular">
                                    <path
                                        d="M323.8 34.8c-38.2-10.9-78.1 11.2-89 49.4l-5.7 20c-3.7 13-10.4 25-19.5 35l-51.3 56.4c-8.9 9.8-8.2 25 1.6 33.9s25 8.2 33.9-1.6l51.3-56.4c14.1-15.5 24.4-34 30.1-54.1l5.7-20c3.6-12.7 16.9-20.1 29.7-16.5s20.1 16.9 16.5 29.7l-5.7 20c-5.7 19.9-14.7 38.7-26.6 55.5c-5.2 7.3-5.8 16.9-1.7 24.9s12.3 13 21.3 13L448 224c8.8 0 16 7.2 16 16c0 6.8-4.3 12.7-10.4 15c-7.4 2.8-13 9-14.9 16.7s.1 15.8 5.3 21.7c2.5 2.8 4 6.5 4 10.6c0 7.8-5.6 14.3-13 15.7c-8.2 1.6-15.1 7.3-18 15.1s-1.6 16.7 3.6 23.3c2.1 2.7 3.4 6.1 3.4 9.9c0 6.7-4.2 12.6-10.2 14.9c-11.5 4.5-17.7 16.9-14.4 28.8c.4 1.3 .6 2.8 .6 4.3c0 8.8-7.2 16-16 16H286.5c-12.6 0-25-3.7-35.5-10.7l-61.7-41.1c-11-7.4-25.9-4.4-33.3 6.7s-4.4 25.9 6.7 33.3l61.7 41.1c18.4 12.3 40 18.8 62.1 18.8H384c34.7 0 62.9-27.6 64-62c14.6-11.7 24-29.7 24-50c0-4.5-.5-8.8-1.3-13c15.4-11.7 25.3-30.2 25.3-51c0-6.5-1-12.8-2.8-18.7C504.8 273.7 512 257.7 512 240c0-35.3-28.6-64-64-64l-92.3 0c4.7-10.4 8.7-21.2 11.8-32.2l5.7-20c10.9-38.2-11.2-78.1-49.4-89zM32 192c-17.7 0-32 14.3-32 32V448c0 17.7 14.3 32 32 32H96c17.7 0 32-14.3 32-32V224c0-17.7-14.3-32-32-32H32z">
                                    </path>
                                </svg>
                                <svg viewBox="0 0 512 512" height="1em" xmlns="http://www.w3.org/2000/svg"
                                    class="thumbs-up-solid">
                                    <path
                                        d="M313.4 32.9c26 5.2 42.9 30.5 37.7 56.5l-2.3 11.4c-5.3 26.7-15.1 52.1-28.8 75.2H464c26.5 0 48 21.5 48 48c0 18.5-10.5 34.6-25.9 42.6C497 275.4 504 288.9 504 304c0 23.4-16.8 42.9-38.9 47.1c4.4 7.3 6.9 15.8 6.9 24.9c0 21.3-13.9 39.4-33.1 45.6c.7 3.3 1.1 6.8 1.1 10.4c0 26.5-21.5 48-48 48H294.5c-19 0-37.5-5.6-53.3-16.1l-38.5-25.7C176 420.4 160 390.4 160 358.3V320 272 247.1c0-29.2 13.3-56.7 36-75l7.4-5.9c26.5-21.2 44.6-51 51.2-84.2l2.3-11.4c5.2-26 30.5-42.9 56.5-37.7zM32 192H96c17.7 0 32 14.3 32 32V448c0 17.7-14.3 32-32 32H32c-17.7 0-32-14.3-32-32V224c0-17.7 14.3-32 32-32z">
                                    </path>
                                </svg>
                            </label>
                            <style>
                                /*------ Settings ------*/
                                .container_like {
                                    --color: rgb(138, 170, 219);
                                    --size: 30px;
                                    display: flex;
                                    justify-content: center;
                                    align-items: center;
                                    cursor: pointer;
                                    font-size: var(--size);
                                    user-select: none;
                                    fill: var(--color);
                                    margin-right: 10px;
                                }

                                .container_like .thumbs-up-solid {
                                    animation: keyframes-fill .5s;
                                }

                                .container_like .thumbs-up-regular {
                                    display: none;
                                    animation: keyframes-fill .5s;
                                }

                                /* ------ On check event ------ */
                                .container_like input:checked~.thumbs-up-regular {
                                    display: block;
                                }

                                .container_like input:checked~.thumbs-up-solid {
                                    display: none;
                                }

                                /* ------ Hide the default checkbox ------ */
                                .container_like input {
                                    opacity: 0;
                                    cursor: pointer;
                                    height: 0;
                                    width: 0;
                                }

                                /* ------ Animation ------ */
                                @keyframes keyframes-fill {
                                    0% {
                                        transform: scale(0);
                                        opacity: 0;
                                    }

                                    50% {
                                        transform: scale(1.2) rotate(-10deg);
                                    }
                                }
                            </style>

                            <span id="likesCount_<?php echo $avisIndividuel['ida']?>">
                                <?php
                        echo $like_dislike['nbr_likes'];



                        ?>
                            </span>
                            <!-- From Uiverse.io by catraco -->
                            <label class="container_dislike DislikeButton">
                                <input checked="checked" type="checkbox"
                                    id="dislikeButton_<?php echo $_COOKIE['user']?>_<?php echo $avisIndividuel['ida']?>">
                                <svg viewBox="0 0 512 512" height="1em" xmlns="http://www.w3.org/2000/svg"
                                    class="thumbs-up-regular">
                                    <path
                                        d="M323.8 34.8c-38.2-10.9-78.1 11.2-89 49.4l-5.7 20c-3.7 13-10.4 25-19.5 35l-51.3 56.4c-8.9 9.8-8.2 25 1.6 33.9s25 8.2 33.9-1.6l51.3-56.4c14.1-15.5 24.4-34 30.1-54.1l5.7-20c3.6-12.7 16.9-20.1 29.7-16.5s20.1 16.9 16.5 29.7l-5.7 20c-5.7 19.9-14.7 38.7-26.6 55.5c-5.2 7.3-5.8 16.9-1.7 24.9s12.3 13 21.3 13L448 224c8.8 0 16 7.2 16 16c0 6.8-4.3 12.7-10.4 15c-7.4 2.8-13 9-14.9 16.7s.1 15.8 5.3 21.7c2.5 2.8 4 6.5 4 10.6c0 7.8-5.6 14.3-13 15.7c-8.2 1.6-15.1 7.3-18 15.1s-1.6 16.7 3.6 23.3c2.1 2.7 3.4 6.1 3.4 9.9c0 6.7-4.2 12.6-10.2 14.9c-11.5 4.5-17.7 16.9-14.4 28.8c.4 1.3 .6 2.8 .6 4.3c0 8.8-7.2 16-16 16H286.5c-12.6 0-25-3.7-35.5-10.7l-61.7-41.1c-11-7.4-25.9-4.4-33.3 6.7s-4.4 25.9 6.7 33.3l61.7 41.1c18.4 12.3 40 18.8 62.1 18.8H384c34.7 0 62.9-27.6 64-62c14.6-11.7 24-29.7 24-50c0-4.5-.5-8.8-1.3-13c15.4-11.7 25.3-30.2 25.3-51c0-6.5-1-12.8-2.8-18.7C504.8 273.7 512 257.7 512 240c0-35.3-28.6-64-64-64l-92.3 0c4.7-10.4 8.7-21.2 11.8-32.2l5.7-20c10.9-38.2-11.2-78.1-49.4-89zM32 192c-17.7 0-32 14.3-32 32V448c0 17.7 14.3 32 32 32H96c17.7 0 32-14.3 32-32V224c0-17.7-14.3-32-32-32H32z">
                                    </path>
                                </svg>
                                <svg viewBox="0 0 512 512" height="1em" xmlns="http://www.w3.org/2000/svg"
                                    class="thumbs-up-solid">
                                    <path
                                        d="M313.4 32.9c26 5.2 42.9 30.5 37.7 56.5l-2.3 11.4c-5.3 26.7-15.1 52.1-28.8 75.2H464c26.5 0 48 21.5 48 48c0 18.5-10.5 34.6-25.9 42.6C497 275.4 504 288.9 504 304c0 23.4-16.8 42.9-38.9 47.1c4.4 7.3 6.9 15.8 6.9 24.9c0 21.3-13.9 39.4-33.1 45.6c.7 3.3 1.1 6.8 1.1 10.4c0 26.5-21.5 48-48 48H294.5c-19 0-37.5-5.6-53.3-16.1l-38.5-25.7C176 420.4 160 390.4 160 358.3V320 272 247.1c0-29.2 13.3-56.7 36-75l7.4-5.9c26.5-21.2 44.6-51 51.2-84.2l2.3-11.4c5.2-26 30.5-42.9 56.5-37.7zM32 192H96c17.7 0 32 14.3 32 32V448c0 17.7-14.3 32-32 32H32c-17.7 0-32-14.3-32-32V224c0-17.7 14.3-32 32-32z">
                                    </path>
                                </svg>
                            </label>
                            <style>
                                /*------ Settings ------*/
                                .container_dislike {
                                    --color: rgb(250, 127, 127);
                                    --size: 30px;
                                    display: flex;
                                    justify-content: center;
                                    align-items: center;
                                    cursor: pointer;
                                    font-size: var(--size);
                                    user-select: none;
                                    fill: var(--color);
                                    margin-right: 10px;
                                    margin-left: 20px;
                                    transform: scale(-1);
                                }
                                .container_dislike .thumbs-up-solid {
                                    animation: keyframes-fill .5s;
                                }

                                .container_dislike .thumbs-up-regular {
                                    display: none;
                                    animation: keyframes-fill .5s;
                                }

                                /* ------ On check event ------ */
                                .container_dislike input:checked~.thumbs-up-regular {
                                    display: block;
                                }

                                .container_dislike input:checked~.thumbs-up-solid {
                                    display: none;
                                }
                                                                

                                /* ------ Hide the default checkbox ------ */
                                .container_dislike input {
                                    opacity: 0;
                                    cursor: pointer;
                                    height: 0;
                                    width: 0;
                                }

                                /* ------ Animation ------ */
                                @keyframes keyframes-fill {
                                    0% {
                                        transform: scale(0);
                                        opacity: 0;
                                    }

                                    50% {
                                        transform: scale(1.2) rotate(-10deg);
                                    }
                                }
                            </style>


                            <span id="dislikesCount_<?php echo $avisIndividuel['ida']?>">
                                <?php
                        echo $like_dislike['nbr_dislikes'];
                        ?>
                            </span>
                        </div>
                        <?php }?>
                    </div>
                    <?php

                if (isset($_COOKIE['type_compte'], $_COOKIE['user'])) {
                    if (($_COOKIE['type_compte'] == "pro_public" || $_COOKIE['type_compte'] == "pro_prive" || $_COOKIE['type_compte'] == "membre")) {
                        if (isset($reponse['ida'])) { ?>
                    <div class="commentaire-reponse">
                        <p>
                            <?php
                                echo nl2br(htmlspecialchars($reponse['corps'])) ?>
                        </p>
                    </div>
                    <?php 
                        if (isset($_COOKIE['type_compte']) && ($_COOKIE['type_compte'] == "membre" || $_COOKIE['type_compte'] == "pro_public" || $_COOKIE['type_compte'] == "pro_prive") && $avisIndividuel['idc'] != $_COOKIE['user']) : ?>
                    <button type="button" class="btn-signaler"
                        id="btn_signaler_reponse_<?php echo $_COOKIE['user'] ?>_<?php echo $avisIndividuel['ida'] ?>"
                        style="margin-left : 30px;">
                        <img src="../icons/warningred.png" alt="Icône signaler" id="icone-signaler">
                        <p>Signaler réponse</p>
                    </button>

                    <?php endif;
                        
                        } else if ($_COOKIE['type_compte'] != "membre" || $offre['idc'] == $_COOKIE['user']) { ?>
                            <button type="button" onclick="toggle_reponse(<?php echo $id_avis ?>)" id="btn-reponse">Ajouter une réponse</button>
                            <form method="POST" action="detail-offre.php?value=<?php $offre['ido']?>" class="zone-btn-publier form_reponse" id="form_reponse_<?php echo $id_avis; ?>" style="display: none">
                                <input type="hidden" name="type_form" value="poste-reponse">
                                <input type="hidden" name="avis" value=<?php echo $avisIndividuel['ida']?>>
                                <input type="hidden" name="ido" value=<?php echo $avisIndividuel['ido']?>>
                                <textarea minlength="30" maxlength="255" type="text" name="zone_reponse" id="ecrire_reponse" placeholder="Veuillez écrire dans cette zone pour pouvoir déposer une réponse sur cette avis."></textarea>
                                <input type="submit" id="bouton_publier" value="Publier">
                            </form>
                            <?php
                        }
                    }
                }
                ?>
            </div>
                <?php
            $id_avis++;
            endforeach; }?>
                <!-- Ensure this is properly placed -->
            </article>
        </section>
        <div id="popup">
            <p>Votre signalement a bien été pris en compte.</p>
            <div class="progress-bar">
                <div></div>
            </div>
            <button class="close-btn">Fermer</button>
        </div>

        <?php if(!isset($_COOKIE['user'])){?>
        <div id="overlay"></div>
        <div id="popup_avis">

            <img id="logo" src="../logo/logo_reduit_vert.png">
            <div id="group">
                <div id="text">
                    <p id="debut">Vous souhaitez faire part de votre avis ?</p>
                    <p id="fin"> Connectez-vous</p>

                </div>
                <form id="formConnexion" action="detail-offre.php?value=<?php echo $offre['ido']?>" method="post">

                    <div class="form-group">
                        <!--Identifiant correspond au pseudo ou codePro, pas à l'idC-->
                        <label for="identifiant">Identifiant compte</label>
                        <input id="identifiant" name="identifiant" placeholder="Identifiant" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                    </div>

                    <input type="hidden" id="etoile" name="etoile">
                    <input type="hidden" id="titreAvis" name="titreAvis">
                    <input type="hidden" id="contenuAvis" name="contenuAvis">
                    <input type="hidden" id="contextAvis" name="contextAvis">

                    <button type="submit" id="connexion_popup" class="submit-button">Se connecter</button>

                    <div class="links">
                        <!--<a href="#" class="mdpOublie">Mot de passe oublié ?</a>-->
                        <a href="creation_compte_formulaire.php" class="inscription">S'inscrire</a>
                    </div>

                </form>
            </div>
            <img id="croix" src="../images/croix.png" alt="" class="close-popup">
        </div>
<?php } ?>

    <?php if(isset($_COOKIE['user']) && $_COOKIE['type_compte'] == "pro_prive" && $offre['type']==3 && $offre['idc'] == $_COOKIE['user'] ){?>
        <div id="overlay"></div>
        <div class="modal" id="modal" style="display: none;">
            <input type="hidden" name="status" value="" id="modal-id">
            <div id='option-modal'>
                <h2 class="titre-modal">Blacklist Options</h2>
                <div>
                    <label for="semaine">Nombre de semaine(s) : </label>
                    <input id="semaine" type="number" value="1" min="1"/>
                    <br>
                    <p id="warning-semaine" name="warning-semaine" style="color: red;">Saisir un nombre valide</p>
                </div>
                <div id="boutons-modal">
                    <input type="button" class="btn-notif" id="modal-submit-button" value="Valider"/>
                    <input type="button" class="btn-notif" id="modal-close" value="Annuler">
                </div>
            </div>
        </div>

        <div class="modal" id="modal-warning" style="display: none;">
            <h2 class="titre-modal">Êtes vous sûr?</h2>
            <div id="boutons-modal">
                <input type="button" class="btn-notif" id="modal-warning-submit-button" value="Valider"/>
                <input type="button" class="btn-notif" id="modal-warning-close" value="Annuler">
            </div>
        </div>
    <?php } ?>  
    <footer class="phone-element">
        <?php include_once 'footerNav.php'; ?>
    </footer>
    </div>
    <footer>
        <?php include_once 'footer.php'; ?>
    </footer>

</body>

</html>
