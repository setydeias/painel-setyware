<?php
	
	include_once '../class/FirebirdCRUD.class.php';

	$data = json_decode(file_get_contents('php://input'), true);
	$salario = str_replace('.', '', $data['salario']);
	$salario = str_replace(',', '.', $salario);

	$crud = new FirebirdCRUD();

	$dataToUpdate = array(
		'table' => 'SALARIO_MINIMO',
		'set' => array(
			'SALARIO_MINIMO' => $salario
			),
		'where' => array(
			'1' => 1
			),
		'messageInSuccess' => 'Salário mínimo foi atualizado com sucesso'
		);

	$update = $crud->Update($dataToUpdate);
	
	echo json_encode($update);