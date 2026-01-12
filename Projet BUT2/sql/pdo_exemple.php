<?php
include('connect_params_lppdt.php');
try{
    $dbh = new PDO("$driver:host=$server;port=$port;dbname=$dbname",
        $user, $pass);

    $stmt = $dbh->prepare(
        "INSERT INTO pact.secteur_public (codePro, denomination_sociale, nom, prenom, email, telephone, mdp, ville, numero_voie, voie, code_postal, complement) 
        VALUES(:codePro, :denomination_sociale,  :nom, :prenom, :email, :telephone, :mdp, :ville, :numero_voie, :voie, :code_postal, :complement)"
    );

    $stmt->bindParam(":codePro", $codePro);
    $stmt->bindParam(":denomination_sociale", $denomination_sociale);
    $stmt->bindParam(":nom", $nom);
    $stmt->bindParam(":prenom", $prenom);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":telephone", $telephone);
    $stmt->bindParam(":mdp", $mdp);
    $stmt->bindParam(":ville", $ville);
    $stmt->bindParam(":numero_voie", $numero_voie);
    $stmt->bindParam(":voie", $voie);
    $stmt->bindParam(":code_postal", $code_postal);
    $stmt->bindParam(":complement", $complement);


    $codePro = '728';
    $denomination_sociale = "MyBuisiness";
    $nom = "Prigent";
    $prenom = "Richard";
    $email = "richard.prigent@wanadoo.fr";
    $telephone = "0758124278";
    $mdp = "N5W7mOliROdlXYtRep"; 
    $ville = "Saint-Brieuc";
    $numero_voie = '11';
    $voie = "rue de la gare";
    $code_postal = '22000';
    $complement = "";
    
    $stmt->execute();

    $dbh = null;
} catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
}
?>
