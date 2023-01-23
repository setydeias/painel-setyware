<?php

	include_once 'class/FirebirdCRUD.class.php';
	$data = json_decode(file_get_contents('php://input'));
	$codsac = $data->codsac;

	$crud = new FirebirdCRUD();

	$dataToSelect = array(
		'table' => 'SACADOS s',
		'distinct' => true,
		'params' => 's.TPDOCSAC, s.STATUS, s.CODSAC, s.DOCSAC, s.CLI_SIGLA, s.NOMSAC, s.RESPONSAVEL, s.AREA_ATUACAO, s.DTNASCSAC_DIA, s.DTNASCSAC_MES, s.DTNASCSAC_ANO, s.SITE, s.DATA_ASSOCIACAO, s.REPASSE,
					s.CEP, s.ENDSAC, s.CIDSAC, s.UFSAC, s.DICAEND, s.RETORNO_POR_EMAIL, s.CNAB240,
					s.BANCO, s.AGENCIA, s.OPERACAO, s.CONTA_CORRENTE, s.TIPO_MENSALIDADE, s.MENSALIDADE, s.SUBSTITUTO_TRIBUTARIO, s.ISENTO_MENSALIDADE, s.ISENTO_DEBITO_AUTOMATICO, s.TIPO_TARIFA, s.BB_1704, s.BB_1705, s.BB_1711, s.BB_18, s.CEF_AUTOAT, s.CEF_AGENCIA, s.CEF_COMPENSACAO, s.CEF_LOTERIAS, s.CEF_CT, s.BRADESCO,
					c.NOMCON, c.FONECON, c.EMAIL',
		'left_join' => array(
			'table' => 'CONTATOS c',
			'on' => 's.CODSAC, c.CODSAC'
			),
		'where' => array(
			's.CODSAC' => $codsac
			)
		);

	$data = $crud->Select($dataToSelect);

	echo json_encode($data);