<?php

include ('../sql/connect_params.php');

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    
    $id_compte = $data['id_compte'];
    $id_avis = $data['id_avis'];
        try {
            $pdo = new PDO("$driver:host=$server;port=$port;dbname=$dbname", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare('SELECT * FROM pact._recommande_pas WHERE idA = :id_avis AND idC = :id_compte');
            $stmt->execute(['id_compte' => $id_compte, 'id_avis' => $id_avis]);
            $dislike_compte = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!empty($dislike_compte)) {    
                $stmt = $pdo->prepare('DELETE FROM pact._recommande_pas WHERE idC = :id_compte AND idA = :id_avis');
                $stmt->execute(['id_compte' => $id_compte, 'id_avis' => $id_avis]);

            }else{
                $stmt = $pdo->prepare('INSERT INTO pact._recommande_pas (idC, idA) VALUES (:id_compte, :id_avis)');
                $stmt->execute(['id_compte' => $id_compte, 'id_avis' => $id_avis]);

                $stmt = $pdo->prepare('SELECT * FROM pact._recommande WHERE idA = :id_avis AND idC = :id_compte');
                $stmt->execute(['id_compte' => $id_compte, 'id_avis' => $id_avis]);
                $like_compte = $stmt->fetch(PDO::FETCH_ASSOC);
                if(!empty($like_compte)){
                    $stmt = $pdo->prepare('DELETE FROM pact._recommande WHERE idC = :id_compte AND idA = :id_avis');
                    $stmt->execute(['id_compte' => $id_compte, 'id_avis' => $id_avis]);
                }
            }
            
            

            
            $stmt = $pdo->prepare('SELECT COUNT(*) as nbr_dislikes FROM pact._recommande_pas WHERE idA = :id_avis');
            $stmt->execute(['id_avis' => $id_avis]);
            $result_dislike = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare('SELECT COUNT(*) as nbr_likes FROM pact._recommande WHERE idA = :id_avis');
            $stmt->execute(['id_avis' => $id_avis]);
            $result_like = $stmt->fetch(PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode(['nbr_dislikes' => $result_dislike['nbr_dislikes'], 'nbr_likes' => $result_like['nbr_likes']]);
            exit;

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            exit;
        }
?>
