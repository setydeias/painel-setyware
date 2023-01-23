<?php

	include_once '../class/FirebirdCRUD.class.php';

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
		'order' => array(
			'param_order' => 'f.DATA_RECEBIMENTO',
			'order_by' => 'DESC'
			)
		);

	$data = $crud->Select($SelectShippings);

	echo json_encode($data);