<?php
	
	include_once '../class/FirebirdCRUD.class.php';

	$data = json_decode(file_get_contents('php://input'), true);
	$upd = $data['stmt'];

	$crud = new FirebirdCRUD();

	$dataToUpdate = array(
		'table' => 'SERVIDOR_NUVENS',
		'set' => array(
			'CONVERTER_ARQUIVOS' => $upd
			),
		'where' => array(
			'1' => '1'
			),
		'messageInSuccess' => 'Status da conversão de arquivos de retorno foi alterado'
		);

	$updatedInfo = $crud->Update($dataToUpdate);

	echo json_encode($updatedInfo);