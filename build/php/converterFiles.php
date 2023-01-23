<?php
	include_once 'class/StrategyFileConverter.php';
	include_once 'class/FileConverter.class.php';
	ini_set('memory_limit', '-1');
	
	$data = json_decode(file_get_contents('php://input'), true);
	$pathTo = $data['path']; 
	$file_content = $data['file'];
	//Cria um arquivo temporário para o processamento
	$temp_file = tempnam(sys_get_temp_dir(), 'tmp');
	$fp_handler = fopen($temp_file, 'w+');
	fwrite($fp_handler, $file_content);
	$tipoConvenio = $data['tipoconvenio'];
	$cnab400 = new StrategyFileConverter($tipoConvenio, $temp_file, 'Remessa');
	fclose($fp_handler);
	$fileConverter = new FileConverter($cnab400, $pathTo);
	$status = $alert = "";

	if ( $fileConverter->Generate() ) {
		$status = "Arquivo convertido com sucesso";
		$alert = "success";
	} else {
		$status = "Houve algum problema ao converter o arquivo, verifique o erro ou tente novamente";
		$alert = "danger";
	}

	$json = array(
		'status' => $status,
		'alert' => $alert
	);

	echo json_encode($json);