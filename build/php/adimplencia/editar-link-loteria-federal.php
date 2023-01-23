<?php
	try {
		error_reporting(0);
		include_once '../class/STYComBr.class.php';


		$STYComBr = new STYComBr();

		$link = $_POST['LINK'];

        //Edita o Link da Loteria Federal parametrizada no Painel e exibido nos Sorteio no site de clientes
		$site_edit = $STYComBr->EditLinkLoteriaFederal(array(
			'link' => "$link"
		));

		echo json_encode($site_edit);
    
    } catch ( Exception $e ) {
		echo $e->getMessage();
		http_response_code(400);
	}