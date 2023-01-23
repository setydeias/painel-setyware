<?php

	include_once '../class/FirebirdCRUD.class.php';

	$data = json_decode(file_get_contents('php://input'));
	$sigla = $data->customer;

	if ( strlen($sigla) != 3 ) {
		echo json_encode(array('error' => 'Informe uma sigla válida'));
		return false;
	}

	$crud = new FirebirdCRUD(array(
		'driver' => 'firebird',
		'dbname' => '179.188.38.39:E:\\ServidorWeb\\fluxo-remessas\\FLUXO-REMESSAS.fdb',
		'charset' => 'WIN1252',
		'user' => 'SYSDBA',
		'password' => 'masterkey'
		));

	$SelectShippings = array(
		'table' => 'FLUXO f',
		'distinct' => true,
		'params' => 'f.SIGLA, f.NUM_REMESSA, s.DESC_STATUS',
		'inner_join' => array(
			'table' => 'STATUS s',
			'on' => 'f.ID_STATUS_REMESSA, s.ID_STATUS_REMESSA'
			),
		'where' => array(
			'f.SIGLA' => $sigla
			),
		'order' => array(
			'param_order' => 'f.DATA_RECEBIMENTO',
			'order_by' => 'DESC'
			)
		);

	$data = $crud->Select($SelectShippings);

	if ( count($data) === 0 ) {
		echo json_encode(array('error' => 'Nenhuma remessa foi encontrada para o cliente informado'));
		return false;
	}

	echo json_encode($data);