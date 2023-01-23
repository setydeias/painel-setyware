<?php
	try {
		error_reporting(E_ALL);
		include_once '../class/STYComBr.class.php';


		$STYComBr = new STYComBr();

		$bilhete = $_POST['BILHETE'];

        //Listar os Sorteios por concurso
		$data = $STYComBr->listarSorteioPorBilhete(array(
			'bilhete' => $bilhete
		));

		echo json_encode($data);
    
    } catch ( Exception $e ) {
		echo $e->getMessage();
		http_response_code(400);
	}