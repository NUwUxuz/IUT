<?php

include ('../sql/connect_params.php');

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $idOffre = intval($data[0]);
    $date = $data[1];
    $nbrJours= intval($data[2])*7;
    $nbrJours = $nbrJours . " days";

            try{
            $pdo = new PDO("$driver:host=$server; port=$port; dbname=$dbname", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->prepare("UPDATE pact._option_offre SET date_lancement = :date_lancement, duree_option = cast(:duree as interval) where ido = :idOffre");
            $stmt->bindParam(':date_lancement', $date, PDO::PARAM_STR);
            $stmt->bindParam(':duree', $nbrJours, PDO::PARAM_STR);
            $stmt->bindParam(':idOffre', $idOffre, PDO::PARAM_INT);
            $stmt->execute();

            header('Content-Type: application/json');
            exit;

            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
                exit;
            }
?>