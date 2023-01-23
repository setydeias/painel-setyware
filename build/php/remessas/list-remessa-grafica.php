<?php
	error_reporting(0);
	include_once '../functions.php';
	include_once '../class/CloudServer.class.php';
	include_once '../class/FirebirdCRUD.class.php';
	$CloudServer = new CloudServer();
	$CloudServer->connect();
	$files = $CloudServer->listFiles('./clientes/remessas', array('txt'));
	$info = $data = array();
	
	if ( count($files) > 0 ) {
		foreach ( $files as $file ) {
			$data['customer'][] = basename($file['filename']);
		}
		$info['success'] = true;
		$info['status'] = $data;
	} else {
		$info['success'] = false;
		$info['status'] = 'Nenhuma remessa encontrada';
	}

	echo json_encode($info);