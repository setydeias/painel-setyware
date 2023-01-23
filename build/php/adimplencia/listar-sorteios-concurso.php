<?php
	try {
		error_reporting(E_ALL);
		include_once '../class/STYComBr.class.php';


		$STYComBr = new STYComBr();

		$concurso = $_POST['CONCURSO'];

        //Listar os Sorteios por concurso
		$data = $STYComBr->listarSorteioPorConcurso(array(
			'concurso' => $concurso
		));

		echo json_encode($data);
    
    } catch ( Exception $e ) {
		echo $e->getMessage();
		http_response_code(400);
	}