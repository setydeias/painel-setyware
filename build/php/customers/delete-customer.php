<?php
	error_reporting(0);
	include_once '../class/FirebirdCRUD.class.php';
	include_once '../class/Validador.class.php';
	include_once '../class/Customer.class.php';
	include_once '../class/STYComBr.class.php';

	$data = json_decode(file_get_contents('php://input'));
	$pathname = $data->codsac;
	$crud = new FirebirdCRUD();

	$dataToDelete = array(
		'table' => 'SACADOS s',
		'columns' => array( 's.CLI_SIGLA' => substr($pathname, 0, 3) ),
		'messageInSuccess' => 'Cliente excluido com sucesso'
	);

	$data = $crud->Delete($dataToDelete);

	if ( $data['success'] ) {
		$customer = new Customer();
		$sigla = substr($pathname, 0, 3);
		/*
		* REMOVE DO VALIDADOR
		*/
		$validador = new Validador();
		$delete = $validador->Delete(substr($pathname, 5));

		if ( !$delete['success'] ) $data['validador_status'] = $delete['message'];	

		/*
		* REMOVE DO SITE 
		*/
		$STYComBr = new STYComBr();
		$delete_site = $STYComBr->Delete($pathname);

		if ( isset($delete_site['error']) ) {
			$data['remover_site'] = $delete_site['error'];
		}

		if ( isset($delete_site['image_site_error']) ) {
			$data['image_site_error'] = $delete_site['image_site_error'];
		}

		if ( isset($delete_site['image_2via_error']) ) {
			$data['image_2via_error'] = $delete_site['image_2via_error'];
		}
	}

	echo json_encode($data);