<?php
	error_reporting(E_WARNING);
	include_once '../class/FirebirdCRUD.class.php';
	$data = json_decode(file_get_contents('php://input'));
	$customer = utf8_decode($data->cli);

	$crud = new FirebirdCRUD();

	$dataToSelect = array(
		'table' => 'SACADOS s',
		'params' => 's.CODSAC, s.NOMSAC, s.STATUS',
		'like' => array(
			'field' => 's.NOMSAC',
			'param' => utf8_encode($customer),
			'format' => 'lowercase'
			),
		'order' => array(
			'param_order' => 's.CODSAC',
			'order_by' => 'ASC'
			)
		);

	$data = $crud->Select($dataToSelect);

	echo json_encode($data);