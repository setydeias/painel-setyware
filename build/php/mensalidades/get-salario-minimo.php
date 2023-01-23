<?php

	include_once '../class/FirebirdCRUD.class.php';
	$data = json_decode(file_get_contents('php://input'));

	$crud = new FirebirdCRUD();

	$dataToSelect = array(
		'table' => 'SALARIO_MINIMO sm',
		'params' => 'sm.SALARIO_MINIMO'
		);

	$data = $crud->Select($dataToSelect);

	echo json_encode($data);