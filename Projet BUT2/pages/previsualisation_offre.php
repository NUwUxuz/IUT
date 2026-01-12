<?php
include('../sql/connect_params.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// echo '<pre>';
// print_r($_POST);
// echo '</pre>';

$idC = $_COOKIE['user'];
$categorie = $_POST['categorie'];


$temp = explode(' ', trim(($_POST["adresse"])));
$numero_voie = $temp[0]; // Premier élément : numéro de la voie
array_shift($temp); // Retirer le numéro de la voie pour ne garder que la voie
$voie = ucfirst(htmlspecialchars(implode(' ', $temp))); // Voie avec majuscule et échappement HTML
$titre = ucfirst(htmlspecialchars($_POST['titre']));
$resume = ucfirst(htmlspecialchars($_POST['resume']));
$description = ucfirst(htmlspecialchars($_POST['description']));
$prix_min = $_POST['prix'];
$accessibilite = ucfirst(htmlspecialchars($_POST['accessibilite']));
$ville = ucfirst(htmlspecialchars($_POST['ville']));
$code_postal= $_POST['codeP'];

if(isset($_POST['complement'])){
    if($_POST['complement'] !== ''){
        $complement = $_POST['complement'];
    } else {
        $complement = null;
    }
}

if(isset($_POST['site'])){
    if ($_POST['site'] !== ''){
        $site = $_POST['site'];
    }else {
        $site = 'Non disponible';
    }
} else {
    $site = 'Non disponible';
}

if (isset($_POST['validation'])){
    function type_offre($nom_type){
        $resultat = 1; //type offre gratuite par défaut
    
        if ($nom_type == "standard"){
            $resultat = 2;
        }else if ($nom_type == "premium"){
            $resultat = 3;
        }
        return $resultat;
    }
    
    function option_offre($arr){
        $res = null;
    
        if (isset($arr['une']) && $arr['une'] != ''){
            $res = 2;
        }else if (isset($arr['relief']) && $arr['relief'] != ''){
            $res = 1;
        }
        return $res;
    }
    
    function tag_offre($arr){
        $resultat = []; //aucun tag par défaut
    
        if (isset($_POST['culturel'])){
            $resultat[] = $arr['culturel'];
        } 
        if (isset($_POST['patrimoine'])){
            $resultat[] = $arr['patrimoine'];
        } 
        if (isset($_POST['histoire'])){
            $resultat[] = $arr['histoire'];
        } 
        if (isset($_POST['urbain'])){
            $resultat[] = $arr['urbain'];
        } 
        if (isset($_POST['nature'])){
            $resultat[] = $arr['nature'];
        } 
        if (isset($_POST['plein-air'])){
            $resultat[] = $arr['plein-air'];
        } 
        if (isset($_POST['sport'])){
            $resultat[] = $arr['sport'];
        } 
        if (isset($_POST['nautique'])){
            $resultat[] = $arr['nautique'];
        } 
        if (isset($_POST['gastronomie'])){
            $resultat[] = $arr['gastronomie'];
        } 
        if (isset($_POST['musee'])){
            $resultat[] = $arr['musee'];
        } 
        if (isset($_POST['atelier'])){
            $resultat[] = $arr['atelier'];
        } 
        if (isset($_POST['musique'])){
            $resultat[] = $arr['musique'];
        } 
        if (isset($_POST['famille'])){
            $resultat[] = $arr['famille'];
        } 
        if (isset($_POST['cinema'])){
            $resultat[] = $arr['cinema'];
        } 
        if (isset($_POST['cirque'])){
            $resultat[] = $arr['cirque'];
        } 
        if (isset($_POST['son-et-lumiere'])){
            $resultat[] = $arr['son-et-lumiere'];
        } 
        if (isset($_POST['humour'])){
            $resultat[] = $arr['humour'];
        } 
        if (isset($_POST['francais'])){
            $resultat[] = $arr['francais'];
        } 
        if (isset($_POST['fruit-de-mer'])){
            $resultat[] = $arr['fruit-de-mer'];
        } 
        if (isset($_POST['asiatique'])){
            $resultat[] = $arr['asiatique'];
        } 
        if (isset($_POST['indienne'])){
            $resultat[] = $arr['indienne'];
        } 
        if (isset($_POST['italienne'])){
            $resultat[] = $arr['italienne'];
        } 
        if (isset($_POST['gastronomique'])){
            $resultat[] = $arr['gastronomique'];
        } 
        if (isset($_POST['restauration-rapide'])){
            $resultat[] = $arr['restauration-rapide'];
        } 
        if (isset($_POST['creperie'])){
            $resultat[] = $arr['creperie'];
        } 
        if (isset($_POST['vegetarienne'])){
            $resultat[] = $arr['vegetarienne'];
        } 
        if (isset($_POST['vegetalienne'])){
            $resultat[] = $arr['vegetalienne'];
        } 
        if (isset($_POST['kebab'])){
            $resultat[] = $arr['kebab'];
        }
        return $resultat;
    } 
}

try{
    $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname",
        $user, $pass); 
        
    // pour telephone
    $Stelephone = $dbh->prepare(
        "select telephone from pact.professionnel where idc = :user;"
    );
    $Stelephone->execute([
        'user' => $idC
    ]);
    $telephone = $Stelephone->fetchColumn();

    if ($telephone === null){
        $telephone = 'Non disponible';
    }



        //pour l'insertion des 3 images
        $Sid = $dbh->prepare(
            "select max(ido)
            from pact.offre;"
        );
        $Sid->execute();
        $idOffre = $Sid->fetchColumn() + 1; //id de l'offre qui a été crée avant 


    if (isset($_POST['validation'])){
        $type = type_offre($_POST['offre']);
        switch ($categorie) {
            case 'activite':
                $duree = $_POST['duree'];
                $age_min = $_POST['age'];
        
                $prestation_incluse = isset($_POST['pinclus']) ? $_POST['pinclus'] : '';
                $prestation_excluse = isset($_POST['pexclus']) ? $_POST['pexclus'] : '';
        
                $Ioffre = $dbh->prepare(
                    "INSERT INTO pact.activite 
                    (titre, resume, \"type\", description, site_web, prix_min, accessibilite, date_creation, date_publication, duree, age_min, prestation_incluse, prestation_excluse, ville, numero_voie, voie, code_postal, complement, idC) 
                    VALUES 
                    (:titre, :resume, :type, :description, :site_web, :prix_min, :accessibilite, NOW(), NOW(), :duree::INTERVAL, :age_min, :prestation_incluse, :prestation_excluse, :ville, :numero_voie, :voie, :code_postal, :complement, :idC)"
                );
        
                $Ioffre->bindParam(':titre', $titre);
                $Ioffre->bindParam(':resume', $resume);
                $Ioffre->bindParam(':type', $type);
                $Ioffre->bindParam(':description', $description);
                $Ioffre->bindParam(':site_web', $site);
                $Ioffre->bindParam(':prix_min', $prix_min);
                $Ioffre->bindParam(':accessibilite', $accessibilite);
                $Ioffre->bindParam(':duree', $duree);
                $Ioffre->bindParam(':age_min', $age_min);
                $Ioffre->bindParam(':prestation_incluse', $prestation_incluse);
                $Ioffre->bindParam(':prestation_excluse', $prestation_excluse);
                $Ioffre->bindParam(':ville', $ville);
                $Ioffre->bindParam(':numero_voie', $numero_voie);
                $Ioffre->bindParam(':voie', $voie);
                $Ioffre->bindParam(':code_postal', $code_postal);
                $Ioffre->bindParam(':complement', $complement);
                $Ioffre->bindParam(':idC', $idC);
        
                break; 

            case 'spectacle':
                $duree = $_POST['duree'];
                $capacite = $_POST['capacite'];
            
                $Ioffre = $dbh->prepare(
                    "INSERT INTO pact.spectacle 
                    (titre, resume, \"type\", description, site_web, prix_min, accessibilite, date_creation, date_publication, duree, capacite, ville, numero_voie, voie, code_postal, complement, idC) 
                    VALUES 
                    (:titre, :resume, :type, :description, :site_web, :prix_min, :accessibilite, NOW(), NOW(), :duree::INTERVAL, :capacite, :ville, :numero_voie, :voie, :code_postal, :complement, :idC)"
                );
            
                $Ioffre->bindParam(':titre', $titre);
                $Ioffre->bindParam(':resume', $resume);
                $Ioffre->bindParam(':type', $type);
                $Ioffre->bindParam(':description', $description);
                $Ioffre->bindParam(':site_web', $site);
                $Ioffre->bindParam(':prix_min', $prix_min);
                $Ioffre->bindParam(':accessibilite', $accessibilite);
                $Ioffre->bindParam(':duree', $duree);
                $Ioffre->bindParam(':capacite', $capacite);
                $Ioffre->bindParam(':ville', $ville);
                $Ioffre->bindParam(':numero_voie', $numero_voie);
                $Ioffre->bindParam(':voie', $voie);
                $Ioffre->bindParam(':code_postal', $code_postal);
                $Ioffre->bindParam(':complement', $complement);
                $Ioffre->bindParam(':idC', $idC);

                break;
                
                
            case 'visite':
                $duree = $_POST['duree'];
                $langues = isset($_POST['langue']) ? $_POST['langue'] : '';
            
                $Ioffre = $dbh->prepare(
                    "INSERT INTO pact.visite 
                    (titre, resume, \"type\", description, site_web, prix_min, accessibilite, date_creation, date_publication, duree, langues, ville, numero_voie, voie, code_postal, complement, idC) 
                    VALUES 
                    (:titre, :resume, :type, :description, :site_web, :prix_min, :accessibilite, NOW(), NOW(), :duree::INTERVAL, :langues, :ville, :numero_voie, :voie, :code_postal, :complement, :idC)"
                );
            
                $Ioffre->bindParam(':titre', $titre);
                $Ioffre->bindParam(':resume', $resume);
                $Ioffre->bindParam(':type', $type);
                $Ioffre->bindParam(':description', $description);
                $Ioffre->bindParam(':site_web', $site);
                $Ioffre->bindParam(':prix_min', $prix_min);
                $Ioffre->bindParam(':accessibilite', $accessibilite);
                $Ioffre->bindParam(':duree', $duree);
                $Ioffre->bindParam(':langues', $langues);
                $Ioffre->bindParam(':ville', $ville);
                $Ioffre->bindParam(':numero_voie', $numero_voie);
                $Ioffre->bindParam(':voie', $voie);
                $Ioffre->bindParam(':code_postal', $code_postal);
                $Ioffre->bindParam(':complement', $complement);
                $Ioffre->bindParam(':idC', $idC);

                break;
                
    
                case 'restauration':
                    $src_image = time();
                    $gamme_prix = $_POST['categorie-prix'];
                    $petit_dejeuner = isset($_POST['petit-dejeuner']) ? 'true' : 'false';
                    $brunch = isset($_POST['brunch']) ? 'true' : 'false';
                    $dejeuner = isset($_POST['dejeuner']) ? 'true' : 'false';
                    $diner = isset($_POST['diner']) ? 'true' : 'false';
                    $boissons = isset($_POST['boissons']) ? 'true' : 'false';
                
                    $Ioffre = $dbh->prepare(
                        "INSERT INTO pact.restauration 
                        (titre, resume, \"type\", description, site_web, prix_min, accessibilite, date_creation, date_publication, src_image, gamme_prix, ville, petit_dejeuner, brunch, dejeuner, diner, boissons, numero_voie, voie, code_postal, complement, idC) 
                        VALUES 
                        (:titre, :resume, :type, :description, :site_web, :prix_min, :accessibilite, NOW(), NOW(), :src_image, :gamme_prix, :ville, :petit_dejeuner, :brunch, :dejeuner, :diner, :boissons, :numero_voie, :voie, :code_postal, :complement, :idC)"
                    );
                
                    $Ioffre->bindParam(':titre', $titre);
                    $Ioffre->bindParam(':resume', $resume);
                    $Ioffre->bindParam(':type', $type);
                    $Ioffre->bindParam(':description', $description);
                    $Ioffre->bindParam(':site_web', $site);
                    $Ioffre->bindParam(':prix_min', $prix_min);
                    $Ioffre->bindParam(':accessibilite', $accessibilite);
                    $Ioffre->bindParam(':src_image', $src_image);
                    $Ioffre->bindParam(':gamme_prix', $gamme_prix);
                    $Ioffre->bindParam(':ville', $ville);
                    $Ioffre->bindParam(':petit_dejeuner', $petit_dejeuner);
                    $Ioffre->bindParam(':brunch', $brunch);
                    $Ioffre->bindParam(':dejeuner', $dejeuner);
                    $Ioffre->bindParam(':diner', $diner);
                    $Ioffre->bindParam(':boissons', $boissons);
                    $Ioffre->bindParam(':numero_voie', $numero_voie);
                    $Ioffre->bindParam(':voie', $voie);
                    $Ioffre->bindParam(':code_postal', $code_postal);
                    $Ioffre->bindParam(':complement', $complement);
                    $Ioffre->bindParam(':idC', $idC);

                    break;                
    
                case 'parcAttraction':
                    $src_image = time();
                    $nbr_attractions = $_POST['nbattraction'];
                    $age_min = $_POST['age'];
                
                    $Ioffre = $dbh->prepare(
                        "INSERT INTO pact.parc_d_attraction 
                        (titre, resume, \"type\", description, site_web, prix_min, accessibilite, date_creation, date_publication, src_image, nbr_attractions, age_min, ville, numero_voie, voie, code_postal, complement, idC) 
                        VALUES 
                        (:titre, :resume, :type, :description, :site_web, :prix_min, :accessibilite, NOW(), NOW(), :src_image, :nbr_attractions, :age_min, :ville, :numero_voie, :voie, :code_postal, :complement, :idC)"
                    );
                
                    $Ioffre->bindParam(':titre', $titre);
                    $Ioffre->bindParam(':resume', $resume);
                    $Ioffre->bindParam(':type', $type);
                    $Ioffre->bindParam(':description', $description);
                    $Ioffre->bindParam(':site_web', $site);
                    $Ioffre->bindParam(':prix_min', $prix_min);
                    $Ioffre->bindParam(':accessibilite', $accessibilite);
                    $Ioffre->bindParam(':src_image', $src_image);
                    $Ioffre->bindParam(':nbr_attractions', $nbr_attractions);
                    $Ioffre->bindParam(':age_min', $age_min);
                    $Ioffre->bindParam(':ville', $ville);
                    $Ioffre->bindParam(':numero_voie', $numero_voie);
                    $Ioffre->bindParam(':voie', $voie);
                    $Ioffre->bindParam(':code_postal', $code_postal);
                    $Ioffre->bindParam(':complement', $complement);
                    $Ioffre->bindParam(':idC', $idC);

                    break;
                    
    
            default:
                # code...
                break;
        } 
        $Ioffre->execute();


        $idOffre;
        for ($i = 0; $i < 3; $i++) { //pour chaque images
            if(isset($_POST['image'.$i]) && $_POST['image'.$i] !== 'false'){
                $image = $_POST['image'.$i];
                $Iimage = $dbh->prepare(
                    "insert into pact.photo_offre 
                    (src_image, idO) values 
                    ('$image', '$idOffre');"
                );   
                $Iimage->execute();
            }
        }

        // insertion de la map
        if (isset($_POST['mapPhoto'])){
            $image = $_POST['mapPhoto'];
            if ($categorie === 'parcAttraction'){
                $Umap = $dbh->prepare(
                    "UPDATE pact.parc_d_attraction SET src_image = '$image'
                    WHERE ido = $idOffre;"
                    
                );
                $Umap->execute();
            } else if ($categorie === 'restauration'){
                $Umap = $dbh->prepare(
                    "UPDATE pact.restauration SET src_image = '$image'
                    WHERE ido = $idOffre;"
                );
                $Umap->execute();
            }
            
            
        }


        //ajout des tags 

        $tags = tag_offre($_POST);
        for ($i=0; $i < sizeof($tags) ; $i++) { 
            $t = $tags[$i];
            $Stag = $dbh->prepare(
                //récupération de l'idTag
                "select idtag from pact._tag where libelle = '$t';"
            );
            $Stag->execute();
            $idtag = $Stag->fetchColumn();


            if ($categorie === 'parcAttraction'){
                //vérification que le tag n'a pas déjà été inséré
                $checkStmt = $dbh->prepare("
                    SELECT COUNT(*) FROM pact._tags_parc_d_attraction
                    WHERE idO = :idOffre AND idtag = :idtag;
                ");
                $checkStmt->execute([
                    'idOffre' => $idOffre,
                    'idtag' => $idtag
                ]);
                if ($checkStmt->fetchColumn() == 0) {
                    $Itag = $dbh->prepare(
                        //insertion    
                        "insert into pact._tags_parc_d_attraction(idO, idtag) values ($idOffre,$idtag);"
                    );

                }
            } else {
                //vérification que le tag n'a pas déjà été inséré
                $checkStmt = $dbh->prepare("
                    SELECT COUNT(*) FROM pact._tags_$categorie
                    WHERE idO = :idOffre AND idtag = :idtag;
                ");
                $checkStmt->execute([
                    'idOffre' => $idOffre,
                    'idtag' => $idtag
                ]);
                if ($checkStmt->fetchColumn() == 0) {
                    $Itag = $dbh->prepare(
                        //insertion
                        "insert into pact._tags_$categorie(idO, idtag) values ($idOffre,$idtag);"
                    );
                }
            }
            
            $Itag->execute();
        }


        //insertion de l'option :
        $option = option_offre($_POST);
        $Ioption = $dbh->prepare(
            "insert into pact._option_offre 
                (idOption, idO) values (:option, :idOffre);"
        );
        $Ioption->execute([
            'idOffre' => $idOffre,
            'option' => $option
        ]);;

        // Redirection vers /accueil.php
        header('Location: tableau_de_bord.php');
        exit(); // Important : Terminer le script pour éviter des exécutions non voulues


    } // fin de vérif si clic sur bouton


} catch (PDOException $e) {
    echo "<pre>";
    print "Erreur !: " . $e->getMessage() . "<br/>";
    echo "</pre>";
    die();
}


$photos = [];
for ($i = 1; $i < 4; $i++) { //pour chaque images
    if (!empty($_FILES['file-upload'.$i]['name'])){
        if ($_POST['image'.$i-1] !== 'false'){
            unlink((glob("../images/image_offre/$idOffre-$i.*"))[0]);
        }
        $extention = pathinfo($_FILES['file-upload'.$i]['name'])['extension'];
        move_uploaded_file($_FILES['file-upload'.$i]['tmp_name'], '../images/image_offre/'.$idOffre.'-'.$i.'.'.$extention);
        $photos[] = 'image_offre/'.$idOffre.'-'.$i.'.'.$extention;
    } else {
        $img = glob("../images/image_offre/$idOffre-$i.*");
        if (($_POST['image'.$i-1]== 'false')){
            if(!empty($img)){
                unlink($img[0]);
                $photos[] = 'null';
            }
        } else {
            $photos[] = $_POST['image'.$i-1];
        }
        
    }
}

// insertion de la map
$mapPhoto = '';

$menu = glob("../images/image_offre/$idOffre-menu.*");
$parc = glob("../images/image_offre/$idOffre-plan.*");
if (!empty($_FILES['map-file-upload']['name'])){
    if ($_POST['mapPhoto'] === 'false'){ // image remplacé, il faut supprimer l'ancienne
        if(!empty($menu)){
            unlink($menu[0]);
        } 
        if(!empty($parc)) {
            unlink($parc[0]);
        }
        
    }
    $fileInfo = pathinfo($_FILES['map-file-upload']['name']); // création de la nouvelle image
    if (isset($fileInfo['extension'])){
        $extention = $fileInfo['extension'];
        if ($categorie === 'parcAttraction'){
            $mapPhoto = 'image_offre/'.$idOffre.'-plan.'.$extention;
            move_uploaded_file($_FILES['map-file-upload']['tmp_name'], '../images/image_offre/'.$idOffre.'-plan.'.$extention);   
        } else if ($categorie === 'restauration'){
            $mapPhoto = 'image_offre/'.$idOffre.'-menu.'.$extention;
            move_uploaded_file($_FILES['map-file-upload']['tmp_name'], '../images/image_offre/'.$idOffre.'-menu.'.$extention); 
        }
    }

} else {
    
    if(isset($_POST['mapPhoto'])){
        if($_POST['mapPhoto'] === 'false'){
            if(!empty($menu)){
                unlink($menu[0]);
            }
            if (!empty($parc)){
                unlink($parc[0]);
            }
            $mapPhoto = '';
        }else if(!empty($menu) && $categorie === 'parcAttraction'){
            $extension = pathinfo($menu[0], PATHINFO_EXTENSION);
            rename($menu[0], '../images/image_offre/'.$idOffre.'-plan.'.$extension); 
            $mapPhoto = 'image_offre/'.$idOffre.'-plan.'.$extension;
              
        } else if (!empty($parc) && $categorie === 'restauration') {
            $extension = pathinfo($parc[0], PATHINFO_EXTENSION);
            rename($parc[0], '../images/image_offre/'.$idOffre.'-menu.'.$extension); 
            $mapPhoto = 'image_offre/'.$idOffre.'-menu.'.$extension;
            
        } else {
            $mapPhoto = $_POST['mapPhoto'];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prévisualisation</title>
    
    <link rel="stylesheet" href="../css/styleGeneral.css">
    <link rel="stylesheet" href="../css/samStyles.css">
    <link rel="stylesheet" href="../css/styleLisa.css">
    <script src="../js/previsualisation.js"></script>
    <script src="../detail-offre.js" async></script>
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
<body class="body main-content <?php echo ($_COOKIE['type_compte'] ?? '') === 'pro_public' || ($_COOKIE['type_compte'] ?? '') === 'pro_prive' ? 'BO' : 'FO'; ?>" >
<?php include_once 'nav.php';?>
<section class="section-page part-annonce">
    <header> 
        <h1 class="titre-rouge">
            <?php echo htmlspecialchars($_POST['titre']); ?>
        </h1>
        <h2 class="sous-titre">
            <?php echo htmlspecialchars($_POST['ville']); ?> |
            <?php echo $telephone ?>
            <?php if ($categorie === 'spectacle' || $categorie === 'activite' || $categorie === 'visite') echo " | Durée : " . htmlspecialchars($_POST['duree']); ?>

            <?php if ($categorie === 'parcAttraction' || $categorie === 'activite') echo " | Âge : " . $_POST['age'] . " ans"; ?>

            <?php   if ($categorie === 'restauration') {echo " | Prix : " . $_POST['categorie-prix'];}
                    elseif (!empty($_POST['prix'])) {echo " | Prix : " . htmlspecialchars($_POST['prix']) . " €";} 
            ?>
        </h2>

        

        <!-- Carrousel -->
        <div class="container NoSelect">
            <div class="carousel">
                <div class="carousel-inner">    
                    <?php
                    if (!empty($photos)){
                        foreach ($photos as $index => $photo): 
                        if ($photo !== 'null'){ ?>
                            <div class="slide">
                                <img src="<?php echo "../images/" . htmlspecialchars($photo); ?>"
                                    alt="Image <?php echo $index + 1; ?>">
                            </div>
                        <?php } else {?>
                            <div class="slide">
                                <img src="../images/imagesReferences/placeholder.jpg" alt="Image">
                            </div>
                        <?php } endforeach; 
                    } else { ?>
                        <div class="slide">
                            <img src="../images/imagesReferences/placeholder.jpg" alt="Image">
                        </div>
                    <?php } ?>
                    
                </div>
                
                <div class="carousel-controls NoSelect">
                    <?php if (!empty($photos) && sizeof($photos) >= 2){ ?>
                        <button id="prev"><img src="../images/fleche.png" alt="Précédent"></button>
                        <button id="next"><img src="../images/fleche.png" alt="Suivant"></button>
                    <?php } ?>  
                    
                </div>
                <?php if (!empty($photos) && sizeof($photos) >= 2){ ?>
                    <div class="carousel-dots NoSelect"></div>
                <?php } ?>
                
            </div>
        </div>

        <!-- Informations -->
        <div class="infos-offre">
            <div class="etoiles">
                <?php 
                $nbrAvis = 0;
                $moyNote = 0;
                $fullStars = (int) $moyNote;
                $emptyStars = 5 - $fullStars;

                for ($i = 0; $i < $fullStars; $i++): ?>
                <img src="../icons/<?php echo ($_COOKIE['type_compte'] ?? '') === 'pro_public' || ($_COOKIE['type_compte'] ?? '') === 'pro_prive' ? 'etoile_rouge' : 'etoile_bleue'; ?>.png"
                    alt="Étoile">
                <?php endfor; ?>
                <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                <img src="../icons/<?php echo ($_COOKIE['type_compte'] ?? '') === 'pro_public' || ($_COOKIE['type_compte'] ?? '') === 'pro_prive' ? 'etoile_rouge_vide' : 'etoile_bleue_vide'; ?>.png"
                    alt="Étoile Vide">
                <?php endfor; ?>
            </div>
            <p class="nb-avis-info-offre">
                <?php echo $nbrAvis . " Avis |"; ?>
            </p>
            <img src="../images/Wheelchair.png" alt="Accessibilité">
            <p>
                <?php echo htmlspecialchars($_POST['accessibilite']); ?>
            </p>
        </div>
    </header>

    <!-- Description -->
    <article class="description-offre">
        <h3>Description :</h3>
        <p>
            <?php echo nl2br(htmlspecialchars($_POST['description'])); ?>
        </p>
    </article>

    <article class="part-contact">
        <div class="side-contact">
            <div>
                <br>
                <h3>Contact :</h3>
                <div>
                    <?php 
                    $address = $numero_voie . $voie . $_POST['ville'] . $_POST['codeP'];
                    ?>
                    <a href="https://www.google.com/maps/dir// <?php echo $address ?>">
                        <img src="../images/maison.png" alt="icon maison (localisation)">
                        <p>
                            <?php
                            echo $numero_voie;
                            echo " ";
                            echo $voie;
                            echo " ";
                            echo $_POST['ville'];
                            echo " ";
                            echo $_POST['codeP'];

                        ?>
                        </p>
                    </a>
                </div>
                <div>
                   <?php if ($telephone !== 'Non disponible'){ ?>
                        <a href="tel:<?php 
                            echo $telephone;
                                ?> ">
                            <img src="../images/telephone.png" alt="icon telephone">
                            <p>
                                <?php 
                                        echo $telephone;
                                    ?>
                            </p>
                        </a>
                   <?php } else { ?>
                        <img src="../images/telephone.png" alt="icon telephone">
                        <p>
                            <?php 
                                    echo $telephone;
                                ?>
                        </p>
                   <?php } ?>
                    
                </div>

                
                <div>
                    <?php if ($site !== 'Non disponible'){ ?>
                        <a href=" <?php echo $site; ?>" target="_blank">
                            <img src="../images/pc.png" alt="icon pc (lien vers le site)">
                            <p>
                                <?php echo $site; ?>
                            </p>
                        </a>
                    <?php } else { ?>
                        <img src="../images/pc.png" alt="icon pc (lien vers le site)">
                            <p>
                                <?php echo $site; ?>
                            </p>
                    <?php } ?>
                    
                </div>
            </div>
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
        </div>
        
        <?php 
            if ($categorie === 'parcAttraction' && $mapPhoto !== '') { ?>
        <div>
            <br><br>
            <h3>Plan du parc : </h3>
            <img class="carte" src="<?php echo "../images/" . htmlspecialchars($mapPhoto); ?>" alt="plan">
        </div>
        <?php
            } else if ($categorie === 'parcAttraction'){ ?>
                <div>
                    <br><br>
                    <h3>Plan du parc : </h3>
                    <img class = carte src="../images/imagesReferences/placeholder.jpg" alt="plan">
                </div> <?php

            } elseif ($categorie === 'restauration' && $mapPhoto !== '') { ?>
            
        <div>
            <br><br>
            <h3>Menu : </h3>
            <img class="carte" src="<?php echo "../images/" . htmlspecialchars($mapPhoto); ?>" alt="menu">
        </div>
        <?php }else if ($categorie === 'restauration'){ ?>
            <div>
                <br><br>
                <h3>Menu : </h3>
                <img class = carte src="../images/imagesReferences/placeholder.jpg" alt="plan">
            </div> <?php } ?>
        


    </article>
    <form method="post" action="previsualisation_offre.php">
    <?php 
        foreach ($_POST as $key => $value) {
            // Si la valeur est un tableau (cas des champs checkbox ou similaires)
            if ($value !== ''){
                if (($key !== 'image0' && $key !== 'image1' && $key !== 'image2' && $key !== 'mapPhoto')){
                    ?><input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>"><?php
                }
                
            }
            
        }
        
        foreach ($photos as $key => $value) {
            // Si la valeur est un tableau (cas des champs checkbox ou similaires)
            if ($value !== ''){
                ?><input type="hidden" name="<?php echo"image". $key; ?>" value="<?php echo $value; ?>"><?php
            }
            
        }?>
        <?php if ($mapPhoto !== ''){?>
            <input type="hidden" name="mapPhoto" value="<?php echo $mapPhoto; ?>">
        <?php } else { ?>
            <input type="hidden" name="mapPhoto" value="<?php echo time(); ?>">
        <?php } ?>
        <input type="hidden" name="validation" value="true">

        



    <div class="boutonsPrevisualisation">
            <button class="FS bouton-valider" type="submit">
                Valider
            </button>
    </form>
    <form method="post" action="creation_offre.php">
    <?php 
        foreach ($_POST as $key => $value) {
            // Si la valeur est un tableau (cas des champs checkbox ou similaires)
            if ($value !== ''){
                if (($key !== 'image0' && $key !== 'image1' && $key !== 'image2' && $key !== 'mapPhoto')){
                    ?><input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>"><?php
                }
                
            }
            
        }
        
        foreach ($photos as $key => $value) {
            // Si la valeur est un tableau (cas des champs checkbox ou similaires)
            if ($value !== ''){
                ?><input type="hidden" name="<?php echo"image". $key; ?>" value="<?php echo $value; ?>"><?php
            }
            
        }
        
        if (isset($mapPhoto)){?>
            <input type="hidden" name="mapPhoto" value="<?php echo $mapPhoto; ?>">
        <?php } 
        ?>

        <button class="bouton-retour-prev NoSelect FS" type="submit">
                <img src="../icons/arrow-left-o.svg" alt="Retour">
                Retour
        </button>
    </form>
            
    </div>
    
    
</section>

<!-- Avis -->
<section class="section-page part-avis">
    <header class="header-avis">
        <h3>Avis :
            <?php echo $nbrAvis; ?>
        </h3>
    </header>
    
</section>
</body>
</html>

