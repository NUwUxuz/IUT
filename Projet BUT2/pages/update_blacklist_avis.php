<?php

include ('../sql/connect_params.php');

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    
    $id_avis = $data['id_avis'];
    if (isset($data["duree"])) {
        $duree = $data['duree'];
    }else{
        $duree = "";
    }
    $blacklist = $data['blacklist'];
    try {
        $pdo = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($duree === "") {
            $stmt = $pdo->prepare("UPDATE pact._avis SET blackliste = :blacklist, date_blackliste = now() WHERE ida = :id_avis");
            $stmt->execute(['blacklist' => $blacklist, 'id_avis' => $id_avis]);
        }else{
            $duree = $duree . " weeks";
            $stmt = $pdo->prepare("UPDATE pact._avis SET blackliste = :blacklist, date_blackliste = now(), duree_blackliste = cast(:duree as interval) WHERE ida = :id_avis");
            $stmt->execute(['blacklist' => $blacklist, 'duree' => $duree, 'id_avis' => $id_avis]);
        }
        

        
        header('Content-Type: application/json');
        exit;

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
?>
