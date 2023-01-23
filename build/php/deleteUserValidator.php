<?php

	include_once 'connectLocawebHoster.php';
	include_once 'functions.php';

	$id = $_GET['id'];

	if ( $conn->query('DELETE FROM validador_clientes WHERE id_cliente = "'.$id.'"') ) :
		createAlert('success', 'Cliente excluído com sucesso!');
	else :
		createAlert('danger', 'Cliente não foi excluído!');
	endif;

	header('Location: ../managerValidator.php');

