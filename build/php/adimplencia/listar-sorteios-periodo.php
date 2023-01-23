<?php
	try {
		error_reporting(E_ALL);
		include_once '../class/STYComBr.class.php';


		$STYComBr = new STYComBr();

		$dataInicial = $_POST['DATAINICIAL'];
		$dataFinal = $_POST['DATAFINAL'];

        //Listar os Sorteios por concurso
		$data = $STYComBr->listarSorteioPorPeriodo(array(
			'dataInicial' => $dataInicial,
			'dataFinal' => $dataFinal
		));

		echo json_encode($data);
    
    } catch ( Exception $e ) {
		echo $e->getMessage();
		http_response_code(400);
	}