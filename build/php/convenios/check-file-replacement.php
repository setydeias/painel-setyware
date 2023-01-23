<?php
    error_reporting(E_ALL);

    try {
        include_once '../class/Convenio.class.php';
        $data = json_decode(file_get_contents('php://input'))->convenio;
        $convenio = $data->convenio;
        $check_file = $data->checkFile ? 'S' : 'N';
        $Convenio = new Convenio();
        
        if ( $Convenio->exists($convenio) ) {
            return $Convenio->checkFileReplacement($convenio, $check_file) ? http_response_code(200) : http_response_code(400);
        } else {
            http_response_code(400);
            echo json_encode(array('error' => 'Convênio não encontrado na base de dados'));
        }
    } catch ( Exception $e ) {
        http_response_code(500);
        echo json_encode(array('error' => $e->getMessage()));
    }