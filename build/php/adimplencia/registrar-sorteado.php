<?php
	try {
		error_reporting(0);
		include_once '../class/STYComBr.class.php';


		$STYComBr = new STYComBr();

		$concurso 	= $_POST['CONCURSO'];
		$data 		= $_POST['DATA'];
		$bilhete 	= $_POST['BILHETE'];
		$unidade 	= $_POST['UNIDADE'];
		$cliente 	= $_POST['CLIENTE'];
		$status  	= $_POST['STATUS'];
		$ganhador 	= $_POST['GANHADOR'];
		$contato  	= $_POST['CONTATO'];
		$email    	= $_POST['EMAIL'];

        //Inserindo o Sorteado no site de clientes
		$site_insert = $STYComBr->InsertSorteado(array(
			'concurso'	=> "$concurso",
			'data' 		=> $data,
			'bilhete' 	=> $bilhete,
			'cliente' 	=> $cliente,
			'unidade' 	=> $unidade,
			'status' 	=> $status,
			'ganhador' 	=> $ganhador,
			'contato' 	=> $contato,
			'email' 	=> $email
		));

		echo json_encode($site_insert);
    
    } catch ( Exception $e ) {
		echo $e->getMessage();
		http_response_code(400);
	}