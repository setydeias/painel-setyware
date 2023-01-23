<?php
	try {
		error_reporting(0);
		include_once '../class/CloudServer.class.php';
		include_once '../class/STYComBr.class.php';


		$STYComBr = new STYComBr();

		$codigo_cliente = $_POST['CODSAC'];
		//$sigla = strtoupper($_POST['CLI_SIGLA']);
		$unidade = $_POST['UNIDADE'];
   
        //Ativa a unidade no site de clientes
		$site_insert = $STYComBr->desativarUnidadeTemp(array(
			'codigo_cliente' => "$codigo_cliente",
			'unidade' => $unidade
		));
    
    } catch ( Exception $e ) {
		echo $e->getMessage();
		http_response_code(400);
	}