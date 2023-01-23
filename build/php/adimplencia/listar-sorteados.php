<?php
	try {
		error_reporting(E_ALL);
		include_once '../class/STYComBr.class.php';

		$STYComBr = new STYComBr();

        //Listar os Sorteados
		$data = $STYComBr->listarSorteados();
		echo json_encode($data);
    
    } catch ( Exception $e ) {
		echo $e->getMessage();
		http_response_code(400);
	}