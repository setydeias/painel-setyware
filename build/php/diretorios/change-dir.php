<?php

	include_once '../class/FirebirdCRUD.class.php';
	$data = json_decode(file_get_contents('php://input'));
	
	//Formatando para barras invertidas e adicionando barras no final
	foreach ( $data as $key => $path ) {
		$data->$key = str_replace('/', '\\', $path);
		if ( !(substr($data->$key, strlen($data->$key) - 1) == '\\') ) $data->$key = $data->$key.'\\';
	}

	$processamento = $data->processamento;
	$retornos_processados = $data->retornos_processados;
	$retornos_originais = $data->retornos_originais;
	$pagamentos_cheque = $data->pagamentos_cheque;
	$processamento_remessa = $data->processamento_remessa;
	$remessas_processadas = $data->remessas_processadas;
	$remessas_originais = $data->remessas_originais;
	$remessa_banco = $data->remessa_banco;
	$remessa_banco_processadas = $data->remessa_banco_processadas;
	$remessa_banco_originais = $data->remessa_banco_originais;
	$pasta_backup_remessa_banco = $data->pasta_backup_remessa_banco;
	$pasta_reposicao_base = $data->pasta_reposicao_base;
	$conta_transitoria = $data->conta_transitoria;
	$laboratorio = $data->laboratorio;
	$clientes = $data->clientes;
	$banco_adm = $data->banco_adm;

	$crud = new FirebirdCRUD();

	$dataToUpdate = array(
		'table' => 'DIRETORIOS',
		'set' => array(
			'PROCESSAMENTO_RETORNOS' => $processamento,
			'RETORNOS_PROCESSADOS' => $retornos_processados,
			'RETORNOS_ORIGINAIS' => $retornos_originais,
			'PAGAMENTOS_EM_CHEQUE' => $pagamentos_cheque,
			'PROCESSAMENTO_REMESSA_GRAFICA' => $processamento_remessa,
			'REMESSA_PROCESSADA_GRAFICA' => $remessas_processadas,
			'REMESSA_ORIGINAL_GRAFICA' => $remessas_originais,
			'PROCESSAMENTO_REMESSA_BANCO' => $remessa_banco,
			'REMESSA_PROCESSADA_BANCO' => $remessa_banco_processadas,
			'REMESSA_ORIGINAL_BANCO' => $remessa_banco_originais,
			'PASTA_BACKUP_REMESSA_BANCO' => $pasta_backup_remessa_banco,
			'PASTA_ARQUIVOS_REPOSICAO_BASE' => $pasta_reposicao_base,
			'CONTA_TRANSITORIA' => $conta_transitoria,
			'LABORATORIO' => $laboratorio,
			'CLIENTES' => $clientes,
			'BANCO_ADM77777' => $banco_adm
			),
		'where' => array(
			'1' => 1
			),
		'messageInSuccess' => 'Diretórios atualizados com sucesso'
		);

	$update = $crud->Update($dataToUpdate);

	echo json_encode($update);