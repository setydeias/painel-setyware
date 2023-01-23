<?php
	error_reporting(0);
	include_once 'class/FirebirdCRUD.class.php';
	include_once 'class/MailParams.class.php';
	$MailParams = new MailParams();
	$crud = new FirebirdCRUD(); //CRUD

	//Tarifas
	$dataToSelect = array(
		'table' => 'TARIFAS t',
		'params' => 't.BB18, t.BB17_04, t.BB17_05, t.BB17_11, t.BB_LQR, t.CEF_AUTOAT, t.CEF_AGENCIA, t.CEF_LOTERIAS, t.CEF_COMPENSACAO, t.CEF_CT, t.BRD, t.DEBITO_CONTA, t.IMPRESSAO_GRAFICA, t.IMPRESSAO, t.ENTREGA_INDIVIDUAL, t.ENTREGA_UNICA'
		);

	$Tarifas = $crud->Select($dataToSelect);

	//Envio para o servidor nas nuvens
	$dataToSelect = array(
		'table' => 'SERVIDOR_NUVENS svn',
		'params' => 'svn.ENVIAR_SERVIDOR'
		);

	$ServerSendStatus = $crud->Select($dataToSelect);

	//Conversão de arquivo
	$dataToSelect = array(
		'table' => 'SERVIDOR_NUVENS svn',
		'params' => 'svn.CONVERTER_ARQUIVOS'
		);

	$ConvertFileStatus = $crud->Select($dataToSelect);

	//Diretórios de processamento
	$dataToSelect = array(
		'table' => 'DIRETORIOS d',
		'params' => 'd.PROCESSAMENTO_RETORNOS, d.RETORNOS_PROCESSADOS, d.RETORNOS_ORIGINAIS,
			d.PAGAMENTOS_EM_CHEQUE,
			d.PROCESSAMENTO_REMESSA_GRAFICA, d.REMESSA_PROCESSADA_GRAFICA, d.REMESSA_ORIGINAL_GRAFICA,
			d.PROCESSAMENTO_REMESSA_BANCO, d.REMESSA_PROCESSADA_BANCO, d.REMESSA_ORIGINAL_BANCO,
			d.PASTA_BACKUP_REMESSA_BANCO, d.PASTA_ARQUIVOS_REPOSICAO_BASE, d.LABORATORIO, d.CLIENTES, d.CONTA_TRANSITORIA, d.BANCO_ADM77777'
		);

	$Dir = $crud->Select($dataToSelect);

	//Salário mínimo
	$dataToSelect = array(
		'table' => 'SALARIO_MINIMO sm',
		'params' => 'sm.SALARIO_MINIMO'
		);

	$SalarioMinimo = $crud->Select($dataToSelect);

	//Parâmetros de conexão com o SVN
	$dataToSelect = array(
		'table' => 'SERVIDOR_NUVENS_PARAMS snp',
		'params' => 'snp.IP_SERVER, snp.FTP_LOGIN'
	);

	$SVNParams = $crud->Select($dataToSelect);

	//Parâmetros de configuração de email

	//Retornando os dados
	$info = array(
		'tarifas' => $Tarifas,
		'sendStatus' => $ServerSendStatus,
		'convertStatus' => $ConvertFileStatus,
		'dir' => $Dir,
		'salario_minimo' => $SalarioMinimo,
		'svn_params' => $SVNParams,
		'mail_params' => $MailParams::get()
		);

	echo json_encode($info);