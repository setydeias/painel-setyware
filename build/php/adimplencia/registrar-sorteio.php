<?php
	try {
		error_reporting(0);
		include_once '../class/STYComBr.class.php';


		$STYComBr = new STYComBr();

		$concurso = $_POST['CONCURSO'];
		$data = $_POST['DATA'];
		$bilhete = $_POST['BILHETE'];

        //Inserindo o Sorteio no site de clientes
		$site_insert = $STYComBr->InsertSorteio(array(
			'concurso' => "$concurso",
			'data' => $data,
			'bilhete' => $bilhete
		));

		echo json_encode($site_insert);
    
    } catch ( Exception $e ) {
		echo $e->getMessage();
		http_response_code(400);
	}