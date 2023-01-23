<?php
	error_reporting(E_WARNING);
	include_once '../class/FirebirdCRUD.class.php';

	$crud = new FirebirdCRUD();

	$dataToSelect = array(
		'table' => 'SACADOS s',
		'params' => 's.CODSAC, s.NOMSAC, s.STATUS, s.RETORNO_POR_EMAIL, s.CLI_SIGLA',
		'order' => array( 'param_order' => 's.CODSAC', 'order_by' => 'ASC' )
	);

	$data = $crud->Select($dataToSelect);
	$customers = array();

	for ( $i = 0; $i < count($data['CODSAC']); $i++ ) {
		$customers[] = array(
			'CODSAC' => $data['CODSAC'][$i],
			'NOMSAC' => $data['NOMSAC'][$i],
			'STATUS' => $data['STATUS'][$i],
			'RETORNO_POR_EMAIL' => $data['RETORNO_POR_EMAIL'][$i],
			'CLI_SIGLA' => $data['CLI_SIGLA'][$i]
		);
	}

	echo json_encode($customers);