<?php
use function PHPSTORM_META\type;

include('../sql/connect_params.php');


ini_set('display_errors', 1);
error_reporting(E_ALL);

/*
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<pre>";
print_r($_FILES);
echo "</pre>";
*/

function type_offre($str){
    $res = 1;

    if ($str == "standard"){
        $res = 2;
    }else if ($str == "premium"){
        $res = 3;
    }
    return $res;
}

function hasTag($val, $arr){
    $res = false;
    foreach ($arr as $key => $value) {
        if(in_array($val, $value)){
            $res = true;
            break; // for optimisation
        }
    }
    return $res;
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

$idO = $_GET['value'];
$isResto = false; // to see what tags to set
$tags_location =''; // to check activated tags

try{    
    $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname",
        $user, $pass);
        
    $stmt = $dbh->prepare(
        "SELECT *
            FROM pact.offre
            WHERE ido = :idO;"
    );
    $stmt->bindParam(':idO', $idO, PDO::PARAM_STR);

    $stmt->execute();

    $dataOffre = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $categorie = trim($dataOffre[0]["categorie"]);

    switch ($categorie) {
        case 'Activite':
            $stmt = $dbh->prepare(
                "SELECT *
                    FROM pact.activite
                    WHERE ido = :idO;"
            );
            $tags_location = '_tags_activite';
            break;
        case 'Visite':
            $stmt = $dbh->prepare(
                "SELECT *
                    FROM pact.visite
                    WHERE ido = :idO;"
            );
            $tags_location = '_tags_visite';
            break;
        case 'Spectacle':
            $stmt = $dbh->prepare(
                "SELECT *
                    FROM pact.spectacle
                    WHERE ido = :idO;"
            );
            $tags_location = '_tags_spectacle';
            break;
        case "Parc d'attraction":
            $stmt = $dbh->prepare(
                "SELECT *
                    FROM pact.parc_d_attraction
                    WHERE ido = :idO;"
            );
            $tags_location = '_tags_parc_d_attraction';
            break;
        case "Restauration":
            $stmt = $dbh->prepare(
                "SELECT *
                    FROM pact.restauration
                    WHERE ido = :idO;" 
            );
            $tags_location = '_tags_restauration';
            $isResto = true;
            break;
    }

    $stmt->bindParam(':idO', $idO, PDO::PARAM_STR);
    $stmt->execute();
    $dataOffre = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $dbh->prepare(
        "SELECT *
         FROM pact.$tags_location natural join pact._tag where idO = :idO ;" 
    );
    $stmt->bindParam(':idO', $idO, PDO::PARAM_STR);

    $stmt->execute();

    $tags_offre = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($_POST != []){
        switch ($categorie) {
            case 'Activite':
                $titre = $_POST['titre'];
                $resume = $_POST['resume'];
                $type = type_offre($_POST['offre']);
                $description = $_POST['description'];
                $site = trim($_POST['site']);
                $prix_min = $_POST['prix'];
                $accessibilite = $_POST['accessibilite'];
                $duree = $_POST['duree'];
                $age_min = $_POST['age'];
                $prestation_incluse = $_POST['pinclus'];
                $prestation_excluse = $_POST['pexclus'];
                $ville = $_POST['ville'];
                $temp = explode(' ',trim($_POST["adresse"]));
                $numero_voie = $temp[0];
                array_shift($temp);
                $voie = implode(' ', $temp);
                $code_postal= $_POST['codeP'];

                //TODO : check if date_publication = now() id usefull
                $stmt = $dbh->prepare(
                    "update pact.activite set 
                        titre = :titre, 
                        resume = :resume, 
                        \"type\" = :type, 
                        description = :description,
                        site_web = :site,
                        prix_min = :prix_min, 
                        accessibilite = :accessibilite, 
                        date_publication = now(),
                        duree = cast(:duree as interval), 
                        age_min = :age_min,
                        prestation_incluse = :prestation_incluse, 
                        prestation_excluse = :prestation_excluse, 
                        ville = :ville, 
                        numero_voie = :numero_voie , 
                        voie = :voie, 
                        code_postal = :code_postal, 
                        complement =''
                    where idO = :idO;"
                );
                $stmt->bindParam(':titre', $titre, PDO::PARAM_STR);
                $stmt->bindParam(':resume', $resume, PDO::PARAM_STR);
                $stmt->bindParam(':type', $type, PDO::PARAM_INT);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->bindParam(':site', $site, PDO::PARAM_STR);
                $stmt->bindParam(':prix_min', $prix_min, PDO::PARAM_STR);
                $stmt->bindParam(':accessibilite', $accessibilite, PDO::PARAM_STR);

                $stmt->bindParam(':duree', $duree, PDO::PARAM_STR);
                $stmt->bindParam(':age_min', $age_min, PDO::PARAM_STR);
                $stmt->bindParam(':prestation_incluse', $prestation_incluse, PDO::PARAM_STR);
                $stmt->bindParam(':prestation_excluse', $prestation_excluse, PDO::PARAM_STR);

                $stmt->bindParam(':ville', $ville, PDO::PARAM_STR);
                $stmt->bindParam(':numero_voie', $numero_voie, PDO::PARAM_STR);
                $stmt->bindParam(':voie', $voie, PDO::PARAM_STR);
                $stmt->bindParam(':code_postal', $code_postal, PDO::PARAM_STR);
                $stmt->bindParam(':idO', $idO, PDO::PARAM_STR);
                break;
            case 'Spectacle':
                $titre = $_POST['titre'];
                $resume = $_POST['resume'];
                $type = type_offre($_POST['offre']);
                $description = $_POST['description'];
                $site = trim($_POST['site']);
                $prix_min = $_POST['prix'];
                $accessibilite = $_POST['accessibilite'];
                $duree = $_POST['duree'];
                $capacite = $_POST['capacite'];

                $ville = $_POST['ville'];

                $temp = explode(' ',trim($_POST["adresse"]));
                $numero_voie = $temp[0];
                array_shift($temp);
                $voie = implode(' ', $temp);

                $code_postal= $_POST['codeP'];

                $stmt = $dbh->prepare(
                    "update pact.spectacle set 
                        titre = :titre, 
                        resume = :resume, 
                        \"type\" = :type, 
                        description = :description,
                        site_web = :site,
                        prix_min = :prix_min, 
                        accessibilite = :accessibilite, 
                        date_publication = now(), 
                        duree = cast(:duree as interval), 
                        capacite = :capacite,
                        ville = :ville, 
                        numero_voie = :numero_voie , 
                        voie = :voie, 
                        code_postal = :code_postal, 
                        complement =''
                    where idO = :idO;"
                );
                $stmt->bindParam(':titre', $titre, PDO::PARAM_STR);
                $stmt->bindParam(':resume', $resume, PDO::PARAM_STR);
                $stmt->bindParam(':type', $type, PDO::PARAM_INT);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->bindParam(':site', $site, PDO::PARAM_STR);
                $stmt->bindParam(':prix_min', $prix_min, PDO::PARAM_STR);
                $stmt->bindParam(':accessibilite', $accessibilite, PDO::PARAM_STR);

                $stmt->bindParam(':duree', $duree, PDO::PARAM_STR);
                $stmt->bindParam(':capacite', $capacite, PDO::PARAM_STR);

                $stmt->bindParam(':ville', $ville, PDO::PARAM_STR);
                $stmt->bindParam(':numero_voie', $numero_voie, PDO::PARAM_STR);
                $stmt->bindParam(':voie', $voie, PDO::PARAM_STR);
                $stmt->bindParam(':code_postal', $code_postal, PDO::PARAM_STR);
                $stmt->bindParam(':idO', $idO, PDO::PARAM_STR);
                break;

            case 'Visite':
                $titre = $_POST['titre'];
                $resume = $_POST['resume'];
                $type = type_offre($_POST['offre']);
                $description = $_POST['description'];
                $site = trim($_POST['site']);
                $prix_min = $_POST['prix'];
                $accessibilite = $_POST['accessibilite'];
                $duree = $_POST['duree'];
                $langues = $_POST['langue'];

                $ville = $_POST['ville'];

                $temp = explode(' ',trim($_POST["adresse"]));
                $numero_voie = $temp[0];
                array_shift($temp);
                $voie = implode(' ', $temp);

                $code_postal= $_POST['codeP'];
                $stmt = $dbh->prepare(
                    "update pact.visite set 
                        titre = :titre, 
                        resume = :resume, 
                        \"type\" = :type, 
                        description = :description,
                        site_web = :site,
                        prix_min = :prix_min, 
                        accessibilite = :accessibilite, 
                        date_publication = now(), 
                        duree = cast(:duree as interval), 
                        langues = :langues,
                        ville = :ville, 
                        numero_voie = :numero_voie,
                        voie = :voie, 
                        code_postal = :code_postal, 
                        complement =''
                    where idO = :idO;"
                );
                $stmt->bindParam(':titre', $titre, PDO::PARAM_STR);
                $stmt->bindParam(':resume', $resume, PDO::PARAM_STR);
                $stmt->bindParam(':type', $type, PDO::PARAM_INT);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->bindParam(':site', $site, PDO::PARAM_STR);
                $stmt->bindParam(':prix_min', $prix_min, PDO::PARAM_STR);
                $stmt->bindParam(':accessibilite', $accessibilite, PDO::PARAM_STR);

                $stmt->bindParam(':duree', $duree, PDO::PARAM_STR);
                $stmt->bindParam(':langues', $langues, PDO::PARAM_STR);

                $stmt->bindParam(':ville', $ville, PDO::PARAM_STR);
                $stmt->bindParam(':numero_voie', $numero_voie, PDO::PARAM_STR);
                $stmt->bindParam(':voie', $voie, PDO::PARAM_STR);
                $stmt->bindParam(':code_postal', $code_postal, PDO::PARAM_STR);
                $stmt->bindParam(':idO', $idO, PDO::PARAM_STR);
                break;

            case 'Restauration':
                $titre = $_POST['titre'];
                $resume = $_POST['resume'];
                $type = type_offre($_POST['offre']);
                $description = $_POST['description'];
                $site = trim($_POST['site']);
                $prix_min = $_POST['prix'];
                $accessibilite = $_POST['accessibilite'];

                $src_image = random_int(1, 1000); // need image source
                $gamme_prix = $_POST['categorie-prix'];
                $petit_dejeuner = (isset($_POST['petit-dejeuner']) && $_POST['petit-dejeuner'] == 'petit-dejeuner') ? 'true' : 'false';
                $brunch = (isset($_POST['brunch']) && $_POST['brunch'] == 'brunch') ? 'true' : 'false';
                $dejeuner = (isset($_POST['brunch']) && $_POST['dejeuner'] == 'dejeuner') ? 'true' : 'false';
                $diner = (isset($_POST['diner']) && $_POST['diner'] == 'diner') ? 'true' : 'false';
                $boissons = (isset($_POST['boissons']) && $_POST['boissons'] == 'boissons') ? 'true' : 'false';

                $ville = $_POST['ville'];

                $temp = explode(' ',trim($_POST["adresse"]));
                $numero_voie = $temp[0];
                array_shift($temp);
                $voie = implode(' ', $temp);

                $code_postal= $_POST['codeP'];
                $stmt = $dbh->prepare(
                    "update pact.restauration set 
                        titre = :titre, 
                        resume = :resume, 
                        \"type\" = :type, 
                        description = :description,
                        site_web = :site,
                        prix_min = :prix_min, 
                        accessibilite = :accessibilite, 
                        date_publication = now(),
                        src_image = :src_image,
                        gamme_prix = :gamme_prix,
                        petit_dejeuner = :petit_dejeuner,
                        brunch = :brunch, 
                        dejeuner = :dejeuner, 
                        diner = :diner, 
                        boissons = :boissons,
                        ville = :ville, 
                        numero_voie = :numero_voie , 
                        voie = :voie, 
                        code_postal = :code_postal, 
                        complement =''
                    where idO = :idO;"
                );
                $stmt->bindParam(':titre', $titre, PDO::PARAM_STR);
                $stmt->bindParam(':resume', $resume, PDO::PARAM_STR);
                $stmt->bindParam(':type', $type, PDO::PARAM_INT);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->bindParam(':site', $site, PDO::PARAM_STR);
                $stmt->bindParam(':prix_min', $prix_min, PDO::PARAM_STR);
                $stmt->bindParam(':accessibilite', $accessibilite, PDO::PARAM_STR);

                $stmt->bindParam(':src_image', $src_image, PDO::PARAM_STR);
                $stmt->bindParam(':gamme_prix', $gamme_prix, PDO::PARAM_STR);
                $stmt->bindParam(':petit_dejeuner', $petit_dejeuner, PDO::PARAM_STR);
                $stmt->bindParam(':brunch', $brunch, PDO::PARAM_STR);
                $stmt->bindParam(':dejeuner', $dejeuner, PDO::PARAM_STR);
                $stmt->bindParam(':diner', $diner, PDO::PARAM_STR);
                $stmt->bindParam(':boissons', $boissons, PDO::PARAM_STR);

                $stmt->bindParam(':ville', $ville, PDO::PARAM_STR);
                $stmt->bindParam(':numero_voie', $numero_voie, PDO::PARAM_STR);
                $stmt->bindParam(':voie', $voie, PDO::PARAM_STR);
                $stmt->bindParam(':code_postal', $code_postal, PDO::PARAM_STR);
                $stmt->bindParam(':idO', $idO, PDO::PARAM_STR);
                break;
            case "Parc d'attraction":
                $titre = $_POST['titre'];
                $resume = $_POST['resume'];
                $type = type_offre($_POST['offre']);
                $description = $_POST['description'];
                $site = trim($_POST['site']);
                $prix_min = $_POST['prix'];
                $accessibilite = $_POST['accessibilite'];

                $src_image = random_int(1, 1000); // need image source
                $nbr_attractions = $_POST['nbattraction'];
                $age_min = $_POST['age'];

                $ville = $_POST['ville'];

                $temp = explode(' ',trim($_POST["adresse"]));
                $numero_voie = $temp[0];
                array_shift($temp);
                $voie = implode(' ', $temp);

                $code_postal= $_POST['codeP'];
                $stmt = $dbh->prepare(
                    "update pact.parc_d_attraction set 
                        titre = :titre, 
                        resume = :resume, 
                        \"type\" = :type, 
                        description = :description,
                        site_web = :site,
                        prix_min = :prix_min, 
                        accessibilite = :accessibilite, 
                        date_publication = now(), 
                        src_image = :src_image, 
                        nbr_attractions = :nbr_attractions,
                        age_min = :age_min,
                        ville = :ville, 
                        numero_voie = :numero_voie , 
                        voie = :voie,     
                        code_postal = :code_postal, 
                        complement =''
                    where idO = :idO;"
                );
                $stmt->bindParam(':titre', $titre, PDO::PARAM_STR);
                $stmt->bindParam(':resume', $resume, PDO::PARAM_STR);
                $stmt->bindParam(':type', $type, PDO::PARAM_INT);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->bindParam(':site', $site, PDO::PARAM_STR);
                $stmt->bindParam(':prix_min', $prix_min, PDO::PARAM_STR);
                $stmt->bindParam(':accessibilite', $accessibilite, PDO::PARAM_STR);

                $stmt->bindParam(':src_image', $src_image, PDO::PARAM_STR);
                $stmt->bindParam(':nbr_attractions', $nbr_attractions, PDO::PARAM_STR);
                $stmt->bindParam(':age_min', $age_min, PDO::PARAM_STR);

                $stmt->bindParam(':ville', $ville, PDO::PARAM_STR);
                $stmt->bindParam(':numero_voie', $numero_voie, PDO::PARAM_STR);
                $stmt->bindParam(':voie', $voie, PDO::PARAM_STR);
                $stmt->bindParam(':code_postal', $code_postal, PDO::PARAM_STR);
                $stmt->bindParam(':idO', $idO, PDO::PARAM_STR);
                break;

            default:
                # code...
                break;
        }
        $stmt->execute();

        // gestion options
        $option = option_offre($_POST);
        
        // retire option pour la modifier
        // ou si l'option à été retitrée par l'utilisateur
        $stmt = $dbh->prepare(
            "delete from pact._option_offre where idO = :idO;"
        );
        $stmt->bindParam(':idO', $idO, PDO::PARAM_STR);
        $stmt->execute();

        // quand option est non nulle donc on ajoute l'option à l'offre
        if ($option != null) {
            $stmt = $dbh->prepare(
                "insert into pact._option_offre 
                    (idOption, idO) values (:option, :idO);"
            );
            $stmt->bindParam(':idO', $idO, PDO::PARAM_STR);
            $stmt->bindParam(':option', $option, PDO::PARAM_STR);
            $stmt->execute();
        }
       

        // $idO 
        //pour l'insertion des 3 images
        $dir = '../images/image_offre';
        $images = scandir($dir);
        for ($i = 1; $i < 4; $i++) { //pour chaque images
            $pattern = "/$idO-$i/";
            $image = array_values(preg_grep($pattern, $images));
            if (!empty($_FILES['file-upload'.$i]['name'])){
                $extention = pathinfo($_FILES['file-upload'.$i]['name'])['extension'];
                //trouver ancienne image si elle existe pour la supprimer
                if (!empty($image)) {
                    //supprime image pour la remplacer
                    $image = $image[0];
                    $src = "image_offre/$image";
                    $stmt = $dbh->prepare(
                        "delete from pact.photo_offre 
                        where src_image = :src;"
                    );
                    $stmt->bindParam(':src', $src, PDO::PARAM_STR);
                    $stmt->execute();
                    unlink("$dir/$image");
                }
                move_uploaded_file($_FILES['file-upload'.$i]['tmp_name'], '../images/image_offre/'.$idO.'-'.$i.'.'.$extention);
                //TODO test
                $src = "image_offre/$idO-$i.$extention";
                $stmt = $dbh->prepare(
                    "insert into pact.photo_offre 
                    (src_image, idO) values 
                    (:src, :idO);"
                );
                $stmt->bindParam(':idO', $idO, PDO::PARAM_STR);
                $stmt->bindParam(':src', $src, PDO::PARAM_STR);
                $stmt->execute();
            } else if ($_POST['statusImage'.$i] === 'supprime') {
                // supprimer images
                $image = $image[0];
                $src = "image_offre/$image";
                $stmt = $dbh->prepare(
                    "delete from pact.photo_offre 
                    where src_image = :src;"
                );
                $stmt->bindParam(':src', $src, PDO::PARAM_STR);
                $stmt->execute();
                unlink("$dir/$image");
            } 
        }

        //images plan et menu
        $fileInfo = pathinfo($_FILES['map-file-upload']['name']);
        if (isset($fileInfo['extension'])){
            $extention = $fileInfo['extension'];
            if ($categorie === "Parc d'attraction"){
                $pattern = "/$idO-plan/";
                $image = array_values(preg_grep($pattern, $images));
                if (!empty($image)) {
                    //supprime image pour la remplacer
                    $image = $image[0];
                    unlink("$dir/$image");
                }
                $src = "image_offre/'.$idO.-plan.$extention";
                move_uploaded_file($_FILES['map-file-upload']['tmp_name'], '../images/'.$src);
                $stmt = $dbh->prepare(
                    "UPDATE pact.parc_d_attraction SET src_image = :src
                    WHERE ido = :idO;"
                );
                $stmt->bindParam(':src', $src, PDO::PARAM_STR);
            } else if ($categorie === 'Restauration'){
                $pattern = "/$idO-menu/";
                $image = array_values(preg_grep($pattern, $images));
                if (!empty($image)) {
                    //supprime image pour la remplacer
                    $image = $image[0];
                    unlink("$dir/$image");
                }
                $src = "image_offre/'.$idO.-menu.$extention";
                move_uploaded_file($_FILES['map-file-upload']['tmp_name'], '../images/'.$src);
                $stmt = $dbh->prepare(
                    "UPDATE pact.restauration SET src_image = :src
                    WHERE ido = :idO;"
                );
                $stmt->bindParam(':src', $src, PDO::PARAM_STR);
            }
            $stmt->execute();
        }

        if ($_POST['status-map'] === 'true') {
            if ($categorie === "Parc d'attraction"){
                $pattern = "/$idO-plan/";
                $image = array_values(preg_grep($pattern, $images));
            } else if ($categorie === 'Restauration'){
                $pattern = "/$idO-menu/";
                $image = array_values(preg_grep($pattern, $images));
            }
            // supprimer images
            $image = $image[0];
            $src = "image_offre/$image";
            $stmt = $dbh->prepare(
                "delete from pact.photo_offre 
                where src_image = :src;"
            );
            $stmt->bindParam(':src', $src, PDO::PARAM_STR);
            $stmt->execute();
            unlink("$dir/$image");
        }

        //ajout des tags
        $tags = tag_offre($_POST);
        
        $stmt = $dbh->prepare(
            //récupération de l'idTag
            "delete from pact.$tags_location where idO = :idO;"
        );
        $stmt->bindParam(':idO', $idO, PDO::PARAM_STR);
        $stmt->execute();
        

        for ($i=0; $i < sizeof($tags) ; $i++) { 
            $t = $tags[$i];
            $stmt = $dbh->prepare(
                //récupération de l'idTag
                "select idtag from pact._tag where libelle = :t;"
            );
            $stmt->bindParam(':t', $t, PDO::PARAM_STR);
            $stmt->execute();
            $idtag = $stmt->fetchColumn();
            
            //vérification que le tag n'a pas déjà été inséré
            $checkStmt = $dbh->prepare("
                SELECT COUNT(*) FROM pact.$tags_location
                WHERE idO = :idO AND idtag = :idtag;
            ");
            $checkStmt->execute([
                'idO' => $idO,
                'idtag' => $idtag
            ]);
            if ($checkStmt->fetchColumn() == 0) {
                $stmt = $dbh->prepare(
                    //insertion  
                    "insert into pact.$tags_location(idO, idtag) values (:idO,:idtag);"
                );
                $stmt->bindParam(':idO', $idO, PDO::PARAM_STR);
                $stmt->bindParam(':idtag', $idtag, PDO::PARAM_STR);
                $stmt->execute();
            }
                        
        }   
        
        header('Location:tableau_de_bord.php');
        exit();
    }
    $dbh = null;
} catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
}


?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Modification d'offre</title>
        <link rel="stylesheet" href="../css/styleGeneral.css">
        <link rel="icon" type="image/png" href="../logo/logo_reduit_rouge.png">
    </head>
    <nav>
        <?php include_once 'nav.php';?>
    </nav>
    <body class="BO">
        <div class="main-content-crea-modif-offre"> 
            <h1 class="TS-bold">Modifiez votre offre</h1>            
            <form action="" method="post" enctype="multipart/form-data">
                <div id="form-content" class="FS">
                    <div id="left-part">
                        <div id="left-part-content">
                            <div id="title-categorie">
                                <!-- Champ "Titre" -->
                                <div class="container-crea-modif-offre" id="titre-container">
                                    <label for="titre">Titre de l'offre*</label>
                                    <p id="error-message-titre" class="error-message"></p>
                                    <input type="text" id="titre" class="FS" name="titre" placeholder="Roméo et Juliette" required value="<?php echo $dataOffre[0]["titre"];?>"/>
                                    
                                </div>
                                <!-- Champ "Catégorie" -->
                                <div class="container-crea-modif-offre categorie-container">
                                    <label for="categorie-offre">Catégorie de l'offre</label>
                                    <select id="categorie-offre" name="categorie" required class="FS" disabled>
                                    <?php switch ($categorie) {
                                        case 'Activite':
                                            ?>
                                            <option value="activite" <?php echo 'selected id="first-option"'?>>Activité</option>
                                            <?php
                                            break;
                                        case 'Visite':
                                             ?>
                                            <option value="visite" <?php echo 'selected id="first-option"'?>>Visite</option>
                                            <?php
                                            break;
                                        case 'Spectacle':
                                            ?>
                                            <option value="spectacle" <?php echo 'selected id="first-option"'?>>Spectacle</option>
                                            <?php
                                            break;
                                        case "Parc d'attraction":
                                                ?>
                                                <option value="parcAttraction" <?php echo 'selected id="first-option"'?>>Parc d'attraction</option>
                                                <?php
                                                break;
                                        case "Restauration":
                                            ?>
                                            <option value="restauration" <?php echo 'selected id="first-option"'?>>Restauration</option>
                                            <?php
                                            break;
                                        default:
                                            # code...
                                            break;
                                    }?>
                                    </select>
                                </div>
                            </div>
                            <!-- Champ "Résumé" -->
                            <div class="container-crea-modif-offre" id="resume-container">
                                <label for="resume">Résumé*</label>
                                <p id="resume-error-message" class="error-message"></p>
                                <textarea id="resume" class="FS textarea-modif-crea-offre" name="resume" placeholder="L'histoire d'amour et tragique de..." required><?php echo $dataOffre[0]["resume"];?></textarea>
                            </div>
                            <!-- Champ "Description" -->
                            <div class="container-crea-modif-offre" id="description-container">
                                <label for="description">Description*</label>
                                <p id="description-error-message" class="error-message"></p>
                                <textarea id="description" class="FS textarea-modif-crea-offre" name="description" placeholder="Spectacle se déroulant au théatre du..." required><?php echo $dataOffre[0]["description"];?></textarea>
                            </div>

                            <!-- Champ "Site web" -->
                            <div class="container-crea-modif-offre" id="site-container">
                                <label for="site">Site web</label>
                                <p id="site-error-message" class="error-message"></p>
                                <input type="text" id="site" class="FS" name="site" placeholder="https://exemple.com" value = "<?php echo $dataOffre[0]["site_web"];?>">
                            </div>

                            <!-- Champ "Tags" -->
                            <div id="tag-container">
                                <label>Tags</label>
                                <div id="tags-items-container">
                                <input type="checkbox" class="hidden" name="culturel" id="culturel" value="Culturel" <?php echo  (hasTag("Culturel", $tags_offre)) ?  'checked': '';?>>
                                    <label for="culturel">Culturel</label>

                                    <input type="checkbox" class="hidden" name="patrimoine" id="patrimoine" value="Patrimoine" <?php echo  (hasTag("Patrimoine", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="patrimoine">Patrimoine</label>

                                    <input type="checkbox" class="hidden" name="histoire" id="histoire" value="Histoire" <?php echo  (hasTag("Histoire", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="histoire">Histoire</label>

                                    <input type="checkbox" class="hidden" name="urbain" id="urbain" value="Urbain" <?php echo  (hasTag("Urbain", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="urbain">Urbain</label>

                                    <input type="checkbox" class="hidden" name="nature" id="nature" value="Nature" <?php echo  (hasTag("Nature", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="nature">Nature</label>

                                    <input type="checkbox" class="hidden" name="plein-air" id="plein-air" value="Pleine air" <?php echo  (hasTag("Pleine air", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="plein-air">Pleine air</label>
                                    
                                    <input type="checkbox" class="hidden" name="sport" id="sport" value="Sport" <?php echo  (hasTag("Sport", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="sport">Sport</label>

                                    <input type="checkbox" class="hidden" name="nautique" id="nautique" value="Nautique" <?php echo  (hasTag("Nautique", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="nautique">Nautique</label>

                                    <input type="checkbox" class="hidden" name="gastronomie" id="gastronomie" value="Gastronomie" <?php echo  (hasTag("Gastronomie", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="gastronomie">Gastronomie</label>

                                    <input type="checkbox" class="hidden" name="musee" id="musee" value="Musée" <?php echo  (hasTag("Musée", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="musee">Musée</label>

                                    <input type="checkbox" class="hidden" name="atelier" id="atelier" value="Atelier" <?php echo  (hasTag("Atelier", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="atelier">Atelier</label>

                                    <input type="checkbox" class="hidden" name="musique" id="musique" value="Musique" <?php echo  (hasTag("Musique", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="musique">Musique</label>

                                    <input type="checkbox" class="hidden" name="famille" id="famille" value="Famille" <?php echo  (hasTag("Famille", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="famille">Famille</label>

                                    <input type="checkbox" class="hidden" name="cinema" id="cinema" value="Cinéma" <?php echo  (hasTag("Cinéma", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="cinema">Cinéma</label>

                                    <input type="checkbox" class="hidden" name="cirque" id="cirque" value="Cirque" <?php echo  (hasTag("Cirque", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="cirque">Cirque</label>

                                    <input type="checkbox" class="hidden" name="son-et-lumiere" id="son-et-lumiere" value="Son et lumière" <?php echo  (hasTag("Son et lumière", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="son-et-lumiere">Son et lumière</label>

                                    <input type="checkbox" class="hidden" name="humour" id="humour" value="Humour" <?php echo  (hasTag("Humour", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="humour">Humour</label>

                                    <input type="checkbox" class="hidden" name="francais" id="francais" value="Français" <?php echo  (hasTag("Français", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="francais">Français</label>

                                    <input type="checkbox" class="hidden" name="fruit-de-mer" id="fruit-de-mer" value="Fruit de mer" <?php echo  (hasTag("Fruit de mer", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="fruit-de-mer">Fruit de mer</label>

                                    <input type="checkbox" class="hidden" name="asiatique" id="asiatique" value="Asiatique" <?php echo  (hasTag("Asiatique", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="asiatique">Asiatique</label>

                                    <input type="checkbox" class="hidden" name="indienne" id="indienne" value="Indienne" <?php echo  (hasTag("Indienne", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="indienne">Indienne</label>

                                    <input type="checkbox" class="hidden" name="italienne" id="italienne" value="Italienne" <?php echo  (hasTag("Italienne", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="italienne">Italienne</label>

                                    <input type="checkbox" class="hidden" name="gastronomique" id="gastronomique" value="Gastronomique" <?php echo  (hasTag("Gastronomique", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="gastronomique">Gastronomique</label>

                                    <input type="checkbox" class="hidden" name="restauration-rapide" id="restauration-rapide" value="Restauration rapide" <?php echo  (hasTag("Restauration rapide", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="restauration-rapide">Restauration rapide</label>

                                    <input type="checkbox" class="hidden" name="creperie" id="creperie" value="Crêperie" <?php echo  (hasTag("Crêperie", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="creperie">Crêperie</label>

                                    <input type="checkbox" class="hidden" name="vegetarienne" id="vegetarienne" value="Végétarienne" <?php echo  (hasTag("Végétarienne", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="vegetarienne">Végétarienne</label>

                                    <input type="checkbox" class="hidden" name="vegetalienne" id="vegetalienne" value="Végétalienne" <?php echo  (hasTag("Végétalienne", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="vegetalienne">Végétalienne</label>

                                    <input type="checkbox" class="hidden" name="kebab" id="kebab" value="Kebab" <?php echo  (hasTag("Kebab", $tags_offre)) ?  'checked' : '';?>>
                                    <label for="kebab">Kebab</label>
                                </div>
                            </div>
                            <!-- Bouton de soumission -->
                            <div id="publier-retour">
                                <input id="submit-button" type="submit" class="FS" value="Modifier"/>
                                <button class="FS" onclick="confirmer(event)">
                                    <img src="../icons/arrow-left-o.svg" alt="Bouton Image">
                                    Retour
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="right-part">
                        <div id="right-part-content">
                            <div id="top-right-part-content">
                                <!-- Champ "Adresse" -->
                                <div class="container-crea-modif-offre" id="adresse-container">
                                    <label for="adresse">Adresse*</label>
                                    <p id="adresse-error-message" class="error-message"></p>
                                    <input type="text" id="adresse" class="FS" name="adresse" placeholder="5 rue de l'exemple" required value="<?php echo $dataOffre[0]["numero_voie"] . " " . $dataOffre[0]["voie"];?>"/>
                                </div>
                                <div class="container-crea-modif-offre" id="complementAdresse-container">
                                    <label for="complement">Complément d'adresse</label>
                                    <p id="complement-error-message" class="error-message"></p>
                                    <input type="text" id="complement" class="FS" name="complement" placeholder="batiment B, salle 105" value="<?php echo isset($_POST['complement']) ? $_POST['complement'] : ''; ?>"/>
                                </div>
                                <div id="codeP-ville">
                                    <!-- Champ Code postal -->
                                    <div class="container-crea-modif-offre" id="codeP-container">
                                        <label for="codeP">Code Postal*</label>
                                        <p id="postal-code-error-message" class="error-message"></p>
                                        <input type="text" id="codeP" class="FS" name="codeP" placeholder="22100" required value="<?php echo $dataOffre[0]["code_postal"] ?>"/>
                                    </div>
                                    <!-- Champ "ville" -->
                                    <div class="container-crea-modif-offre" id="ville-container">
                                        <label for="ville">Ville*</label>
                                        <p id="city-error-message" class="error-message"></p>
                                        <input type="text" id="ville" class="FS" name="ville" placeholder="Lannion" required value="<?php echo $dataOffre[0]["ville"] ?>" />
                                    </div>
                                </div>
                                
                                <!-- Champ "Accesibilité" -->
                                <div class="container-crea-modif-offre" id="accessibilite-container">
                                    <label for="accessibilite">Accessibilité*</label>
                                    <p id="accessibility-error-message" class="error-message"></p>
                                    <textarea id="accessibilite" class="FS textarea-modif-crea-offre" name="accessibilite" placeholder="Entrée du théatre accessible en fauteuil roulant et amménagé pour.." required><?php echo  (isset($dataOffre[0]['accessibilite'])) ?  $dataOffre[0]["accessibilite"] : '';?></textarea>
                                </div>
                                <!-- Champ "prix minimum" -->
                                <div id="duree-prix-age">
                                    <div class="container-crea-modif-offre" id="duree-container">
                                        <label for="duree">Durée*</label>
                                        <p id="duree-error-message" class="error-message"></p>
                                        <input type="text" id="duree" class="FS" name="duree" placeholder="01:10" required value="<?php echo  (isset($dataOffre[0]['duree'])) ?  substr($dataOffre[0]["duree"], 0, 5) : '';?>"/>
                                    </div>
                                    <div class="container-crea-modif-offre" id="nbattraction-container">
                                        <label for="nbattraction">Nombre d'attractions*</label>
                                        <p id="nbr_attractions-error-message" class="error-message"></p>
                                        <input type="text" id="nbattraction" class="FS" name="nbattraction" placeholder="25" required value="<?php echo  (isset($dataOffre[0]['nbr_attractions'])) ?  $dataOffre[0]["nbr_attractions"] : '';?>"/>
                                    </div>
                                    <div class="container-crea-modif-offre" id="prix-container">
                                        <label for="prix">Prix minimum (€)*</label>
                                        <p id="prix-error-message" class="error-message"></p>
                                        <input type="text" id="prix" class="FS" name="prix" placeholder="25" required value="<?php echo  (isset($dataOffre[0]['prix_min'])) ?  $dataOffre[0]["prix_min"] : '';?>"/>
                                    </div>
                                    <div class="container-crea-modif-offre" id="age-container">
                                        <label for="age">Âge minimum*</label>
                                        <p id="age_min-error-message" class="error-message"></p>
                                        <input type="text" id="age" class="FS" name="age" placeholder="8" required value="<?php echo  (isset($dataOffre[0]['age_min'])) ?  $dataOffre[0]["age_min"] : '';?>"/>
                                    </div>
                                    <div class="container-crea-modif-offre" id="capacite-container">
                                        <label for="capacite">Nombre de places*</label>
                                        <p id="capacite-error-message" class="error-message"></p>
                                        <input type="text" id="capacite" class="FS" name="capacite" placeholder="230" required value="<?php echo  (isset($dataOffre[0]['capacite'])) ?  $dataOffre[0]["capacite"] : '';?>"/>
                                    </div>
                                    <div class="container-crea-modif-offre categorie-container" id="categorie-prix-container">
                                        <label for="categorie-prix">Gamme de prix*</label>
                                        <select id="categorie-prix" name="categorie-prix" class="FS">
                                            <option value="" disabled <?php echo  (isset($dataOffre[0]['gamme_prix']) && (string)$dataOffre[0]['gamme_prix'] == '') ?  'selected' : '';?> hidden id="first-option">Gamme de prix</option>
                                            <option value="€" <?php echo  (isset($dataOffre[0]['gamme_prix']) && (string)$dataOffre[0]['gamme_prix'] == '€') ?  'selected' : '';?> >€</option>
                                            <option value="€€" <?php echo  ( isset($dataOffre[0]['gamme_prix']) && (string)$dataOffre[0]['gamme_prix'] == '€€') ?  'selected' : '';?>>€€</option>
                                            <option value="€€€" <?php echo  ( isset($dataOffre[0]['gamme_prix']) && (string)$dataOffre[0]['gamme_prix'] == '€€€') ?  'selected' : '';?>>€€€</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="container-crea-modif-offre" id="pinclus-container">
                                    <label for="pinclus">Prestation incluse</label>
                                    <p id="pinclus-error-message" class="error-message"></p>
                                    <textarea id="pinclus" class="FS textarea-modif-crea-offre" name="pinclus" placeholder="Kayak, Pagaie, Gilet de sauvetage.."><?php echo  (isset($dataOffre[0]['prestation_incluse'])) ?  $dataOffre[0]["prestation_incluse"] : '';?></textarea>
                                </div>
                                <div class="container-crea-modif-offre" id="pexclus-container">
                                    <label for="pexclus">Prestation excluse</label>
                                    <p id="pexclus-error-message" class="error-message"></p>
                                    <textarea id="pexclus" class="FS textarea-modif-crea-offre" name="pexclus" placeholder="Casque,.."><?php echo  (isset($dataOffre[0]['prestation_excluse'])) ?  $dataOffre[0]["prestation_excluse"] : '';?></textarea>
                                </div>
                                <div class="container-crea-modif-offre" id="langue-container">
                                    <label for="langue">Langue</label>
                                    <p id="langue-error-message" class="error-message"></p>
                                    <input type="text" id="langue" class="FS" name="langue" placeholder="Saisir" value="<?php echo  (isset($dataOffre[0]['langues'])) ?  $dataOffre[0]["langues"] : '';?>"/>
                                </div>

                                <div id="repas-map">
                                    <div id="repas-container">
                                        <label>Repas servis</label>
                                        <div id="repas-items-container">
                                            <div id="petit-dejeuner">
                                                <input type="checkbox" id="petit-dejeuner" name="petit-dejeuner" value="petit-dejeuner" <?php echo  (isset($dataOffre[0]['petit_dejeuner']) && $dataOffre[0]['petit_dejeuner']) ?  'checked' : '';?>/>
                                                <label for="petit-dejeuner">Petit-déjeuner</label>
                                            </div>
                                            <div id="brunch">
                                                <input type="checkbox" id="brunch" name="brunch" value="brunch" <?php echo  (isset($dataOffre[0]['brunch']) && $dataOffre[0]['brunch']) ?  'checked' : '';?>/>
                                                <label for="brunch">Brunch</label>
                                            </div>
                                            <div id="dejeuner">
                                                <input type="checkbox" id="dejeuner" name="dejeuner" value="dejeuner" <?php echo  (isset($dataOffre[0]['dejeuner']) && $dataOffre[0]['dejeuner']) ?  'checked' : '';?>/>
                                                <label for="dejeuner">Déjeuner</label>
                                            </div>
                                            <div id="dinner">
                                                <input type="checkbox" id="diner" name="diner" value="diner" <?php echo  (isset($dataOffre[0]['diner']) && $dataOffre[0]['diner']) ?  'checked' : '';?>/>
                                                <label for="diner">Dîner</label>
                                            </div>
                                            <div id="boissons">
                                                <input type="checkbox" id="boissons" name="boissons" value="boissons" <?php echo  (isset($dataOffre[0]['boissons']) && $dataOffre[0]['boissons']) ?  'checked' : '';?>/>
                                                <label for="boissons">Boissons</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!---- Champs ajout carte --->
                                    <div id="map-image">
                                        <label>Plan du parc</label>
                                        <!-- Éléments pour afficher les images importées -->
                                        <div id="map-image-container">
                                            <img id="selected-image" src="../images/rien.png" alt="Image sélectionnée" />
                                        </div>
                                        <div id="map-file-container">
                                            <label for="map-file-upload" id="map-custom-file-upload">
                                                <img src="../icons/plus.svg" alt="Bouton Image">
                                                <p id="map-upload-text">Ajouter</p>
                                            </label>
                                            <input type="file" id="map-file-upload" name="map-file-upload" accept=".png, .jpg, .jpeg, .gif"/>
                                            <input type="hidden" id="status-map" name="status-map" value="false">
                                            <button id="map-file-delete" type="button">
                                                <img src="../icons/moins.svg" alt="Bouton Image">
                                                Supprimer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div id="type-option"  <?php echo (isset($_COOKIE['type_compte']) && $_COOKIE['type_compte'] === 'pro_public') ? 'style="display: none"' : '';?>>
                                <div id="type-option"  <?php echo  ((string)$_COOKIE['type_compte'] == 'pro_prive') ?  '' : 'style="display: none;"';?>>
                                    <!-- Champ "Type" -->
                                    <div id="type-container">
                                        <label>Type d'offre</label>
                                        <!-- bulle info -->
                                        <div id="bulle1" class="info-container" style="display:none">
                                            <div class="info-icon">?</div>
                                            <div class="info-tooltip">
                                            - Standard : Type par défaut. <br> <br>
                                            - Premium : permet en plus de « blacklister » un maximum de 3 Avis sur une Offre. <br> <br>
                                            Attention : Un fois choisi, le type d'offre ne peut pas être changé.                                           </div>
                                        </div>
                                        <!---------------->
                                        <div id="standard-premium" style="color: grey;">
                                            <div id="standard">                                                
                                                <input type="radio" id="standard" name="offre" value="standard" style="cursor : not-allowed; pointer-events: none;" 
                                                    <?php 
                                                    echo isset($_POST['offre']) 
                                                        ? ($_POST['offre'] == 'standard' ? 'checked' : '') 
                                                        : (((string)$_COOKIE['type_compte'] == 'pro_prive') ? 'checked' : '');
                                                    ?> 
                                                />
                                                <label for="standard">Standard (2€/jour)</label>
                                            </div>
                                            <div id="premium">
                                                <!-- <input type="radio" id="premium" name="offre" value="premium" /> -->
                                                <input type="radio" id="premium" name="offre" value="premium" style="cursor: not-allowed; pointer-events: none;"
                                                    <?php 
                                                    echo isset($_POST['offre']) 
                                                        ? ($_POST['offre'] == 'premium' ? 'checked' : '') 
                                                        : '';
                                                    ?> 
                                                />
                                                <label for="premium">Premium (4€/jour)</label>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Champ "Options" -->
                                    <div id="option-container">
                                        <label>Options</label>
                                        <!-- bulle info -->
                                        <div id="bulle2" class="info-container">
                                            <div class="info-icon">?</div>
                                            <div class="info-tooltip">
                                            - À la une : Offre mis en avant sur l'accueil (comprend aussi l'option en relief). <br> <br>
                                            - En relief : Offre mis en exergue à l'affichage dans la liste d'offres.                                
                                            </div>
                                        </div>
                                        <!---------------->
                                        <div id="une-relief">
                                            <div id="une">
                                                <input type="checkbox" id="une" name="une" value="À la Une" <?php echo isset($dataOffre[0]['option']) && $dataOffre[0]['option'] == 'À la Une' ? 'checked' : ''; ?> onclick="checkOnlyOne(this)"/>
                                                <label for="une">À la une (20€/semaine)</label>
                                            </div>
                                            <div id="relief">
                                                <input type="checkbox" id="relief" name="relief" value="En relief" <?php echo isset($dataOffre[0]['option']) && $dataOffre[0]['option'] == 'En relief' ? 'checked' : ''; ?> onclick="checkOnlyOne(this)"/>
                                                <label for="relief">En relief (10€/semaine)</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                            <div id="bottom-right-part-content">
                            <!---- Champs ajout 3 images --->
                            <div id="file-container">
                                <!---- Bouton 1 --->
                                <label for="file-upload1" class="custom-file-upload">
                                    <img src="../icons/plus.svg" alt="Bouton Image">
                                    <p class="upload-text">Ajouter une image 1</p>
                                </label>
                                <input type="file" id="file-upload1" class="file-upload" name="file-upload1" accept=".png, .jpg, .jpeg, .gif"/>

                                <!---- Bouton 2 --->
                                <label for="file-upload2" class="custom-file-upload">
                                    <img src="../icons/plus.svg" alt="Bouton Image">
                                    <p class="upload-text">Ajouter une image 2</p>
                                </label>
                                <input type="file" id="file-upload2" class="file-upload" name="file-upload2" accept=".png, .jpg, .jpeg, .gif"/>
                                <!---- Bouton 3 --->
                                <label for="file-upload3" class="custom-file-upload">
                                    <img src="../icons/plus.svg" alt="Bouton Image">
                                    <p class="upload-text">Ajouter une image 3</p>
                                </label>
                                <input type="file" id="file-upload3" class="file-upload" name="file-upload3" accept=".png, .jpg, .jpeg, .gif"/>

                                <button id="file-delete" type="button">
                                    <img src="../icons/moins.svg" alt="Bouton Image">
                                    Supprimer une image
                                </button>
                            </div>
                            <p id="error-message-supprimer" style="color: red; display: none;">Vous ne pouvez pas supprimer une image inexistante !</p>
                            <p id="message-prevention"></p>
                            <!-- Éléments pour afficher les images importées -->
                            <div id="image-container">
                                <img class="selected-image" src="../images/rien.png" alt="Image sélectionnée1" />
                                <input class="status-image" type="hidden" id="statusImage1" name="statusImage1" value="false">
                                <img class="selected-image" src="../images/rien.png" alt="Image sélectionnée2" />
                                <input class="status-image" type="hidden" id="statusImage2" name="statusImage2" value="false">
                                <img class="selected-image" src="../images/rien.png" alt="Image sélectionnée3" />
                                <input class="status-image" type="hidden" id="statusImage3" name="statusImage3" value="false">
                            </div>
                        </div>
                    </div>
                </div>      
            </form>
        </div>

        <?php
            // Trouve les images de l'offre
            $dir = '../images/image_offre';
            $files = array_slice(scandir($dir),2);
            $pattern = "/$idO-[0-9]/";
            $res = preg_grep($pattern, $files);
            //met le bon index pour chaque image de l'offre
            // c'est important pour bien remplacer le fichier et modifier la bdd
            foreach ($res as $key => $value) {
                $id = (int)substr(explode("-", $value)[1],0,1);
                $id--;
                $matches[$id] = $value;
            }

            $pattern = "/$idO-[a-z]/i";
            $special = array_values(preg_grep($pattern, $files));
        ?>

        <script src="../js/creation_offre.js"></script>
        <script>
            //status-image : si false = existe pas
            const statusMap = document.getElementById('status-map');
            const statusImg1 = document.getElementById('statusImage1');
            const statusImg2 = document.getElementById('statusImage1');
            const statusImg3 = document.getElementById('statusImage1');

            handleCategorieChange();
            // Affiche image de carte/menu si elle existe
            if (<?php echo empty($special) ?  'false' : 'true'?>) {
                imageCarte = document.getElementById('selected-image');
                imageCarte.src = '../images/image_offre/<?php echo isset($special[0]) ?  $special[0] : ''?>';
                statusMap.value = 'true';
                
                //affiche boutons modifier et supprimer
                mapFileDelete.style.display = 'flex';
                
                mapTextBouton.textContent = 'Modifier';
                mapIconBouton.src = '../icons/changer.svg';
            }


            // affiche les images de l'offre
            <?php for ($i=0; $i < 3; $i++) { 
                if (isset($matches[$i])){
            ?>
            selectedImages[<?php echo $i?>].src = '../images/image_offre/<?php echo $matches[$i]?>';
            statusImg<?php echo $i + 1?>.value = 'true';
            currentImageCount++;
            <?php }}?>

            //affiche le bouton supression s'il y a des images
            if (currentImageCount > 0) {
                nextIndex = rechercheImageVide();                           
                boutonSuppression.style.display = 'flex';
            }
            toggleFileUpload(); // affiche/cache bouton ajouter
        </script>
    </body>
</html>
