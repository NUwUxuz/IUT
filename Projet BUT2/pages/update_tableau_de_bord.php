<?php

include ('../sql/connect_params.php');

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $id_offre = $data['id_offre'];
    $status_offre = $data['status_offre'];

            try{
            $pdo = new PDO("$driver:host=$server; port=$port; dbname=$dbname", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            if($status_offre === '1'){
                $stmt = $pdo->prepare("UPDATE pact._offre SET en_ligne = :status_offre, date_publication = now()  WHERE idO = :id_offre");
                $stmt->execute(['status_offre' => $status_offre, 'id_offre' => $id_offre]);
            }else{
                $stmt = $pdo->prepare("UPDATE pact._offre SET en_ligne = :status_offre, date_hors_ligne = now() WHERE idO = :id_offre");
                $stmt->execute(['status_offre' => $status_offre, 'id_offre' => $id_offre]);
            }

            header('Content-Type: application/json');
            exit;

            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
                exit;
            }

?>