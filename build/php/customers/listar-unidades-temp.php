<?php
	try {
		error_reporting(0);
		include_once '../class/CloudServer.class.php';
		include_once '../class/STYComBr.class.php';


		$STYComBr = new STYComBr();

		$codigo_cliente = $_POST['CODSAC'];
  
        //Listar as unidades no site de clientes
		$data = $STYComBr->listarUnidadeTemp(array(
			'codigo_cliente' => "$codigo_cliente"
		));

		echo json_encode($data);
    
    } catch ( Exception $e ) {
		echo $e->getMessage();
		http_response_code(400);
	}