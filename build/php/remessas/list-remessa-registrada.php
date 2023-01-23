<?php
	error_reporting(E_ALL);
	set_time_limit(0);
	include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/CloudServer.class.php';
	//Conecta ao servidor via FTP
	$CloudServer = new CloudServer();
	$CloudServer->connect();
	$files = $CloudServer->listFiles('./clientes/remessa-registrada');
	
	$info = $data = array();
	
	if ( count($files) > 0 ) {
		foreach ( $files as $file ) {
			$data['fileName'][] = basename($file['filename']);
		}
		$info['success'] = true;
		$info['status'] = $data;
	} else {
		$info['success'] = false;
		$info['status'] = 'Nenhuma remessa encontrada';
	}

	echo json_encode($info);