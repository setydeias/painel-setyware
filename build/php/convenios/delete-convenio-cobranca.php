<?php
	error_reporting(0);
	include_once '../class/Convenio.class.php';
	$Convenio = new Convenio();
	$data = json_decode(file_get_contents('php://input'));
	$convenio = $data->convenio;

	try {
		$Convenio->delete($convenio) ? http_response_code(200) : http_response_code(400);
	} catch ( Exception $e ) {
		http_response_code(500);
		echo json_encode(array('error' => $e->getMessage()));
	}
