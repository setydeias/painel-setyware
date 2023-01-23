<?php
    error_reporting(0);

    try {
        include_once '../class/Convenio.class.php';
        $data = json_decode(file_get_contents('php://input'), true);
        $Convenio = new Convenio();
        $add = $Convenio->add($data);
        
        if ( $add['success'] ) {
            http_response_code(201);
        } else {
            http_response_code(400);
            echo json_encode(array('error' => $add['error']));
        }
    } catch ( Exception $e ) {
        http_response_code(500);
        echo json_encode(array('error' => $e->getMessage()));
    }

