<?php
	
	include_once '../class/FirebirdCRUD.class.php';

	$data = json_decode(file_get_contents('php://input'), true);
	$upd = $data['update'];

	$crud = new FirebirdCRUD();

	$dataToUpdate = array(
		'table' => 'SERVIDOR_NUVENS',
		'set' => array(
			'ENVIAR_SERVIDOR' => $upd
			),
		'where' => array(
			'1' => '1'
			),
		'messageInSuccess' => 'Status do envio de arquivos para o servidor foi alterado'
		);

	$updatedInfo = $crud->Update($dataToUpdate);

	echo json_encode($updatedInfo);