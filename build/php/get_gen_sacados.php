<?php
	
	/*
	* NO SCRIPT DE CADASTRO DO CLIENTE EXISTE UM CAMPO CHAMADO CODSAC
	* ESTE SCRIPT JS FAZ UMA SOLICITAÇÃO ASSÍNCRONA A ESTE SCRIPT "GET_GEN_SACADOS.PHP"
	* RECUPERANDO O ATUAL GENERATOR DA TABELA SACADOS
	*/


	include_once 'class/FirebirdConnection.class.php';
	include_once 'class/FirebirdCRUD.class.php';

	$data = json_decode(file_get_contents('php://input'));
	$gen_name = $data->gen_name;

	$crud = new FirebirdCRUD();
	$cod_sac = $crud->GetGenId($gen_name);

	$info = array(
		'CODSAC' => str_pad($cod_sac + 1, 5, "0", STR_PAD_LEFT)
		);

	echo json_encode($info);
