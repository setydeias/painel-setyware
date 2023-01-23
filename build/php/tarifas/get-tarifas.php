<?php

	include_once '../class/FirebirdCRUD.class.php';
	$data = json_decode(file_get_contents('php://input'));

	$crud = new FirebirdCRUD();

	$dataToSelect = array(
		'table' => 'SACADOS s',
		'params' => 's.BB_1704, s.BB_1711, s.BB_1705, s.BB_18, s.BB_LQR, s.CEF_AUTOAT, s.CEF_AGENCIA, s.CEF_COMPENSACAO, s.CEF_LOTERIAS, s.CEF_CT, s.BRADESCO'
		);

	$data = $crud->Select($dataToSelect);

	echo json_encode($data);