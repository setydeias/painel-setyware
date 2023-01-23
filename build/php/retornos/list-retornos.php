<?php
	error_reporting(E_ALL);
	include_once '../class/FirebirdCRUD.class.php';

	$crud = new FirebirdCRUD();
	$dataToSelect = array( 'table' => 'DIRETORIOS d', 'params' => 'd.PROCESSAMENTO_RETORNOS' );

	$allowed_extensions = array('ret', 'RET', 'Ret', 'srq', 'srt');
	$paths = $crud->Select($dataToSelect);
	$retornos = $paths['PROCESSAMENTO_RETORNOS'][0];

	$path = dir($retornos);
	$processing_files = array('file' => array(), 'size' => array());

	while ( $file = $path->read() ) {
		if ( in_array(pathinfo($file, PATHINFO_EXTENSION), $allowed_extensions) ) {
			$size = filesize($retornos.$file) < 1000 ? false : filesize($retornos.$file);

			$processing_files['file'][] = $file;
			$processing_files['size'][] = $size;
		}
	}

	echo json_encode($processing_files);