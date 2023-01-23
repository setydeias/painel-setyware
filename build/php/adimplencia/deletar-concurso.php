<?php
	try {
		error_reporting(0);
		include_once '../class/CloudServer.class.php';
		include_once '../class/STYComBr.class.php';


		$STYComBr = new STYComBr();

		$concurso = $_POST['CONCURSO'];	
   
        //Deletando o concurso no site de clientes
		$site_delet = $STYComBr->deleteConcurso(array(
			'concurso' => "$concurso"
		));
    
    } catch ( Exception $e ) {
		echo $e->getMessage();
		http_response_code(400);
	}