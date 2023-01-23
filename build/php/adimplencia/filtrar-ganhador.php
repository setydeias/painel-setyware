<?php
	try {
		error_reporting(E_ALL);
		include_once '../class/STYComBr.class.php';


		$STYComBr = new STYComBr();

        $numero_sorteio = $_POST['NUMERO_SORTEIO'];

        //Listar os Sorteios realizados pela Loteria Federal
		$data = $STYComBr->filtrarGanhador(array(
            'numero_sorteio' => "$numero_sorteio"
        ));

		echo json_encode($data);
    
    } catch ( Exception $e ) {
		echo $e->getMessage();
		http_response_code(400);
	}