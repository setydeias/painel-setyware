<?php

	include_once '../class/FirebirdCRUD.class.php';

	$data = json_decode(file_get_contents('php://input'));
	$sigla = $data->customer;
	$remessa = $data->remessa;

	if ( strlen($sigla) != 3 ) {
		echo json_encode(array('error' => 'Informe uma sigla válida'));
		return false;
	} else if ( strlen($remessa) != 5 ) {
		echo json_encode(array('error' => 'Informe um número de remessa válido'));
		return false;
	}

	$crud = new FirebirdCRUD(array(
		'driver' => 'firebird',
		'dbname' => '179.188.38.39:E:\\ServidorWeb\\fluxo-remessas\\FLUXO-REMESSAS.FDB',
		'charset' => 'WIN1252',
		'user' => 'SYSDBA',
		'password' => 'masterkey'
		));

	$SelectShippings = array(
		'table' => 'FLUXO f',
		'distinct' => true,
		'params' => 'f.SIGLA, f.NUM_REMESSA, f.DATA_RECEBIMENTO, f.DATA_RECEPCAO_FORMULARIO, f.DATA_ENVIO_GRAFICA, f.DATA_CONFIRMACAO_GRAFICA, f.DATA_INICIO_IMPRESSAO, f.DATA_FIM_IMPRESSAO,
					f.DATA_INICIO_ENVELOPAMENTO, f.DATA_FIM_ENVELOPAMENTO, f.DATA_FINALIZACAO, f.DATA_POSTAGEM, f.DATA_SAIDA_REMESSA, f.DATA_ENTREGA, s.DESC_STATUS, ag.DESC_AGENTE_ENTREGADOR,
					p.TIPO_PACOTE , p.NOME_PACOTE, p.DATA_VENC_INICIAL, p.DATA_VENC_FINAL, p.VALOR_TOTAL_PACOTE, p.QTDE_TITULOS, p.CUSTO_UNITARIO_IMPRESSAO, p.CUSTO_UNITARIO_ENTREGA',
		'inner_join' => array(
			'table' => 'STATUS s',
			'on' => 'f.ID_STATUS_REMESSA, s.ID_STATUS_REMESSA'
			),
		'inner_join2' => array(
			'table' => 'AGENTE_ENTREGADOR ag',
			'on' => 'f.ID_AG_ENTREGADOR, ag.ID_AGENTE_ENTREGADOR'
			),
		'inner_join3' => array(
			'table' => 'PACOTES p',
			'on' => 'f.COD_PACOTE, p.COD_PACOTE'
			),
		'where' => array(
			'f.SIGLA' => $sigla,
			'f.NUM_REMESSA' => $remessa
			),
		'order' => array(
			'param_order' => 'f.DATA_RECEBIMENTO',
			'order_by' => 'DESC'
			)
		);

	$data = $crud->Select($SelectShippings);

	if ( count($data) === 0 ) {
		echo json_encode(array('error' => 'Nenhuma remessa foi encontrada para o cliente informado'));
		return false;
	}

	echo json_encode($data);