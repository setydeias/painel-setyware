<?php
	try {
		error_reporting(0);
		include_once '../class/CloudServer.class.php';
		include_once '../class/STYComBr.class.php';


		$STYComBr = new STYComBr();

		$numero_sorteio = $_POST['NUMEROSORTEIO'];
		$condomino = $_POST['CONDOMINO'];
		$email = $_POST['EMAIL'];
		$contato = $_POST['CONTATO'];
   
        //Atualiza o contato na unidade referente ao prêmio adimplência no site de clientes
		$site_insert = $STYComBr->confirmarContatoUnidadeTemp(array(
			'numero_sorteio' => "$numero_sorteio",
			'condomino' => $condomino,
			'email' => $email,
			'contato' => $contato
		));
    
    } catch ( Exception $e ) {
		echo $e->getMessage();
		http_response_code(400);
	}