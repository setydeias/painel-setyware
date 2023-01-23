<?php
	try {
		error_reporting(0);
		include_once '../class/STYComBr.class.php';


		$STYComBr = new STYComBr();

		$id_concurso = $_POST['ID-CONCURSO'];
		$concurso = $_POST['CONCURSO'];
		$data = $_POST['DATA'];
		$bilhete = $_POST['BILHETE'];

        //Edita o Concurso no site de clientes
		$site_edit = $STYComBr->EditConcurso(array(
			'id' => $id_concurso,
			'concurso' => "$concurso",
			'data' => "$data",
			'bilhete' => "$bilhete"
		));

		echo json_encode($site_edit);
    
    } catch ( Exception $e ) {
		echo $e->getMessage();
		http_response_code(400);
	}