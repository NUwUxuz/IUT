<?php

include ('../sql/connect_params.php');

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    
    $id_compte = $data['id_compte'];
    $id_avis = $data['id_avis'];
        try {
            $pdo = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            
            $stmt = $pdo->prepare('INSERT INTO pact._signal_reponse (idA, idSignaleur) VALUES (:id_avis, :id_compte)');
            $stmt->execute(['id_avis' => $id_avis, 'id_compte' => $id_compte]);

            
            header('Content-Type: application/json');
            
            exit;

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            exit;
        }
?>
