<?php
	try {
		error_reporting(0);
		include_once '../class/CloudServer.class.php';
		include_once '../class/STYComBr.class.php';


		$STYComBr = new STYComBr();

		$codigo_cliente = $_POST['CODSAC'];
		$unidade = $_POST['UNIDADE'];
		$numero_sorteio = $_POST['NUMERO_SORTEIO'];
		
        //Atualizando o número de sorteio da unidade no site de clientes
		$site_insert = $STYComBr->updateUnidadeTemp(array(
			'codigo_cliente' => "$codigo_cliente",
			'unidade' => $unidade,
			'numero_sorteio' => $numero_sorteio
		));		

		echo json_encode($site_insert);

    } catch ( Exception $e ) {
		echo $e->getMessage();
		http_response_code(400);
	}