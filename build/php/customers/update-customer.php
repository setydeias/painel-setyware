<?php
	include_once '../class/FirebirdCRUD.class.php';
	include_once '../class/Validador.class.php';
	include_once '../class/STYComBr.class.php';
	include_once '../class/CloudServer.class.php';
	$CloudServer = new CloudServer();
	$CloudServer->connect();
	$STYComBr = new STYComBr();

	$tpdocsac = $_POST['TPDOCSAC'];
	$codigo_sacado = $_POST['CODSAC'];
	$documento = mb_convert_encoding($_POST['DOCSAC'], "UTF-8", "ASCII");
	$sigla = $_POST['CLI_SIGLA'];
	$nome_sacado = $_POST['NOMSAC'];
	$responsavel = $_POST['RESPONSAVEL'];
	$area_atuacao = $_POST['AREA_ATUACAO'];
	//Data de Nascimento
	$dt_nasc = $_POST['DTNASC'];
	$dt_nasc = explode('/', $dt_nasc);
	//Dia
	$dia_nasc = $dt_nasc[0];
	//Mês
	$mes_nasc = $dt_nasc[1];
	//Ano
	$ano_nasc = $dt_nasc[2];
	
	$site = $_POST['SITE'];
	$data_associacao = $_POST['DATA_ASSOCIACAO'];
	$cep = $_POST['CEP'];
	$endereco = utf8_decode(substr($_POST['ENDSAC'], 0, 60));
	$cidade = utf8_decode($_POST['CIDSAC']);
	$uf = $_POST['UFSAC'];
	$dicaend = utf8_decode(substr($_POST['DICAEND'], 0, 40));
	$banco = $_POST['BANCO'];
	$agencia = $_POST['AGENCIA'];
	$operacao = isset($_POST['OPERACAO']) ? $_POST['OPERACAO'] : null;
	$conta = $_POST['CONTA'];
	$tipo_mensalidade = $_POST['TIPO_MENSALIDADE'];
	$mensalidade = $_POST['MENSALIDADE'];
	$isento_mensalidade = $_POST['ISENTO_MENSALIDADE'];
	$isento_debito_automatico = $_POST['ISENTO_DEBITO_AUTOMATICO'];
	$isento_substituto_tributario = $_POST['ISENTO_SUBSTITUTO_TRIBUTARIO'];
	$tipo_tarifa = $_POST['TIPO_TARIFA'];
	$bb_1704 = $_POST['BANCO_BRASIL_1704'];
	$bb_1705 = $_POST['BANCO_BRASIL_1705'];
	$bb_1711 = $_POST['BANCO_BRASIL_1711'];
	$bb_18 = $_POST['BANCO_BRASIL_18'];
	$bb_lqr = $_POST['BB_LQR'];
	$cef_autoat = $_POST['CEF_AUTO_AT'];
	$cef_ag = $_POST['CEF_AGENCIA'];
	$cef_comp = $_POST['CEF_COMPENSACAO'];
	$cef_lot = $_POST['CEF_LOTERIAS'];
	$cef_ct = $_POST['CEF_CT'];
	$brd = $_POST['BRD'];
	$telefones = json_decode($_POST['TELEFONES']);
	if ( count($telefones) === 0 ) {
		echo json_encode(array('error' => 'Telefone é obrigatório'));
		return http_response_code(400);
	}
	$emails = json_decode($_POST['EMAILS']);
	if ( count($emails) === 0 ) {
		echo json_encode(array('error' => 'Email é obrigatório'));
		return http_response_code(400);
	}
	$retorno_por_email = $_POST['RETORNO_POR_EMAIL'];
	$cnab240 = $_POST['CNAB240'];
	$pessoa_entrega = substr($_POST['PESSOA_ENTREGA'], 0, 45);
	$nome_sacado_pesquisa = substr($_POST['NOMSAC_PESQUISA'], 0, 45);
	$repasse_variacao = $_POST['REPASSE_VARIACAO'];
	$usuario = $_POST['USUARIO'];
	$senha = $_POST['SENHA'];
	$cobranca = $_POST['COBRANCA'];
	$tpdesc = $_POST['TPDESC'];
	$vldesc = $_POST['VLDESC'];
	$diadesc = $_POST['DIADESC'];
	$endereco_cob = $_POST['ENDCOB'];
	$endereco_cedente = $_POST['END_CED'];
	$endereco_cedente_2 = $_POST['END_CED2'];
	$codfor = $_POST['CODFOR'];
	$entrega = $_POST['ENTREGA'];
	$pais = $_POST['PAIS'];
	$pais2 = $_POST['PAIS_2'];
	$codigo_convenio = $_POST['CODCONV'];
	$status = $_POST['STATUS'];
	$repasse = $_POST['REPASSE'];
	$repasse_tarifa = $_POST['REPASSE_TARIFA'];
	$logweb = $_POST['LOGWEB'];
	
	//Para ativar a opção 'Receber retorno por email'
	//É necessário haver ao menos 1 email cadastrado
	if ( $retorno_por_email == '1' && count($emails) < 1 ) {
		echo json_encode(array('error' => 'Para ativar a opção \'Receber retorno por email\' informe pelo menos 1 email'));
		return false;
	}

	//ADICIONAR NA TABELA SACADOS
	$columns = array(
		'CODSAC' => $codigo_sacado * 1,
		'TPDOCSAC' => $tpdocsac,
		'DOCSAC' => $documento,
		'NOMSAC' => utf8_decode($nome_sacado),
		'NOMUSUSAC' => utf8_decode($nome_sacado),
		'NOMTITSAC' => utf8_decode($nome_sacado),
		'RESPONSAVEL' => utf8_decode($responsavel),
		'AREA_ATUACAO' => $area_atuacao,
		'DTNASCSAC_DIA' => $dia_nasc,
		'DTNASCSAC_MES' => $mes_nasc,
		'DTNASCSAC_ANO' => $ano_nasc,
		'ENDSAC' => $endereco,
		'CIDSAC' => $cidade,
		'UFSAC' => $uf,
		'CEP' => $cep,
		'CIDSAC2' => $cidade,
		'UFSAC2' => $uf,
		'DICAEND' => $dicaend,
		'PESSOA_ENTREGA' => $pessoa_entrega,
		'NOMSAC_PESQUISA' => $nome_sacado_pesquisa,
		'STATUS' => $status,
		'DATA_ASSOCIACAO' => str_replace('/', '.', $data_associacao),
		'REPASSE' => $repasse,
		'CLI_SIGLA' => $sigla,
		'SITE' => $site,
		'TIPO_MENSALIDADE' => $tipo_mensalidade,
		'MENSALIDADE' => $mensalidade,
		'SUBSTITUTO_TRIBUTARIO' => $isento_substituto_tributario,
		'ISENTO_MENSALIDADE' => $isento_mensalidade,
		'ISENTO_DEBITO_AUTOMATICO' => $isento_debito_automatico,
		'BANCO' => $banco,
		'AGENCIA' => $agencia,
		'CONTA_CORRENTE' => $conta,
		'TIPO_TARIFA' => $tipo_tarifa,
		'BB_1704' => $bb_1704,
		'BB_1705' => $bb_1705,
		'BB_1711' => $bb_1711,
		'BB_18' => $bb_18,
		'BB_LQR' => $bb_lqr,
		'CEF_AUTOAT' => $cef_autoat,
		'CEF_AGENCIA' => $cef_ag,
		'CEF_COMPENSACAO' => $cef_comp,
		'CEF_LOTERIAS' => $cef_lot,
		'CEF_CT' => $cef_ct,
		'BRADESCO' => $brd,
		'OPERACAO' => $operacao,
		'RETORNO_POR_EMAIL' => $retorno_por_email,
		'CNAB240' => $cnab240
		);
	
	//Object
	$crud = new FirebirdCRUD();
	$dataToUpdate = array(
		'table' => 'SACADOS',
		'set' => $columns,
		'where' => array( 'CODSAC' => $codigo_sacado * 1 ),
		'messageInSuccess' => 'Cadastro atualizado com sucesso'
	);

	//Envia a imagem do cliente para as imagens do app 2º via
	if ( isset($_FILES['fileToUpload']) ) {
		$svn_image_path = "/xampp/htdocs/app/2via/sistema/imagens/cedentes/";
		$image_name = str_pad($codigo_sacado, 5, '0', STR_PAD_LEFT).".gif";
		if ( !$CloudServer->send($_FILES['fileToUpload']['tmp_name'], "/$svn_image_path/$image_name") ) {
			$data['IMAGE_2VIA_ERROR'] = "Erro ao alterar imagem na 2º via";
		}
	}
	
	//ATUALIZA OS DADOS NA TABELA SACADOS
	$updatedData = $crud->Update($dataToUpdate);
	
	if ( $updatedData['success'] ) {
		//EXCLUI TELEFONES/EMAILS VINCULADOS AO CLIENTE ATUALIZADO
		$dataToDelete = array(
			'table' => 'CONTATOS c',
			'columns' => array( 'c.CODSAC' => $codigo_sacado * 1 )
		);

		$deleteContactData = $crud->Delete($dataToDelete);
		
		if ( $deleteContactData['success'] ) {
			//TELEFONES
			for ( $i = 0 ; $i < count($telefones) ; $i++ ) {
				$dataToInsert = array(
					'table' => 'CONTATOS',
					'columns' => array(
						'CODCON' => $crud->GetGenId('CONTATOS') + 1,
						'CODSAC' => $codigo_sacado * 1,
						'NOMCON' => $telefones[$i]->descricao,
						'FONECON' => $telefones[$i]->numero
						)
					);

				$crud->Insert($dataToInsert);
			}
			//EMAILS
			for ( $i = 0 ; $i < count($emails) ; $i++ ) {
				if ( !is_null($emails[$i]) ) {
					$dataToInsert = array(
						'table' => 'CONTATOS',
						'columns' => array(
							'CODCON' => $crud->GetGenId('CONTATOS') + 1,
							'CODSAC' => $codigo_sacado * 1,
							'NOMCON' => $emails[$i]->descricao,
							'EMAIL' => $emails[$i]->email
							)
						);

					$crud->Insert($dataToInsert);
				}
			}
		} else {
			$updatedData = array(
				'success' => false,
				'status' => 'Erro ao deletar os registros de contato do cliente'
				);
		}
		
		/*
		* ATUALIZAÇÃO NO CADASTRO DO CLIENTE NA INTERNET
		*/
		$STYComBr->Update(array(
			'pathname' => "$sigla$codigo_sacado.jpg",
			'sigla' => $sigla,
			'cliente' => utf8_decode($nome_sacado),
			'cliente_desde' => $data_associacao,
			'responsavel' => $responsavel,
			'endereco' => "$endereco - $cidade ($uf)",
			'telefone1' => $telefones[0]->numero,
			'telefone2' => isset($telefones[1]) ? $telefones[1]->numero : NULL,
			'email' => $emails[0]->email,
			'site' => $site,
			'area_atuacao' => $area_atuacao,
			'imagem_cliente' => isset($_FILES['fileToUpload']) ? $_FILES['fileToUpload'] : NULL,
			'status' => $status
		));

		/*
		* ATUALIZAÇÃO DOS DADOS NO VALIDADOR
		*/
		//Dados para atualizar
		$cedente_update = array(
			'codigo_cedente' => substr($codigo_sacado, -3),
		    'nome_cedente' => strtoupper($nome_sacado),
			'sigla' => strtoupper($sigla),
			'site' => $site
		);

		//Atualização dos dados no validador
		$validador = new Validador();
		$update = $validador->Update($cedente_update);

		if ( !$update['success'] ) $updatedData['validador_status'] = $update['message'];	
	}
	
	echo json_encode(array_merge($updatedData));	