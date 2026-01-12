<?php
// Démarrer la session pour gérer les messages d'erreurs ou succès si besoin
session_start();

// Vérification de la méthode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Récupérer les données du formulaire en toute sécurité
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $pseudo = htmlspecialchars($_POST['pseudo']);
    $codepostal = htmlspecialchars($_POST['codepostal']);
    $ville = htmlspecialchars($_POST['ville']);
    $adresse = htmlspecialchars($_POST['adresse']);
    $email = htmlspecialchars($_POST['email']);
    $telephone = htmlspecialchars($_POST['telephone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation des mots de passe
    if ($password !== $confirm_password) {
        echo "Les mots de passe ne correspondent pas.";
        exit;
    }

    // Hacher le mot de passe avant de l'insérer dans la base de données
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Connexion à la base de données
    $host = 'localhost'; // L'adresse du serveur MySQL
    $db = 'mon_site_web'; // Le nom de la base de données
    $user = 'root'; // Le nom d'utilisateur MySQL
    $pass = ''; // Le mot de passe MySQL

    try {
        // Création d'une nouvelle connexion PDO
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Préparer la requête SQL d'insertion
        $sql = "INSERT INTO utilisateurs (nom, prenom, pseudo, codepostal, ville, adresse, email, telephone, password)
                VALUES (:nom, :prenom, :pseudo, :codepostal, :ville, :adresse, :email, :telephone, :password)";

        // Préparer la requête avec les valeurs associées
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':pseudo', $pseudo);
        $stmt->bindParam(':codepostal', $codepostal);
        $stmt->bindParam(':ville', $ville);
        $stmt->bindParam(':adresse', $adresse);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telephone', $telephone);
        $stmt->bindParam(':password', $hashed_password);

        // Exécuter la requête
        $stmt->execute();

        // Rediriger l'utilisateur vers une page de succès
        echo "Inscription réussie !";
        
    } catch (PDOException $e) {
        // En cas d'erreur, afficher un message
        echo "Erreur : " . $e->getMessage();
    }
}
?>
