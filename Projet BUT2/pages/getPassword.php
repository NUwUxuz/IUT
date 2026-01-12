<?php
include ('../sql/connect_params.php');

$data = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Connexion à la base de données échouée']);
    exit;
}

$sql = "SELECT * FROM pact.membre WHERE idC = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $_COOKIE['user']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if($data['password1'] === $data['password2']){
    if($user['password'] === $data['password1']){
        header('Content-Type: application/json');
        echo json_encode([TRUE]);
        exit;
    }else{
        http_response_code(500);
        echo json_encode([FALSE]);
        exit;
    }
}else{
    http_response_code(500);
    echo json_encode([FALSE]);
    exit;
}