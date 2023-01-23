<?php
	try {
		error_reporting(0);
		include_once '../class/FirebirdCRUD.class.php';
		include_once '../class/ContaTransitoria.class.php';
		include_once '../class/CloudServer.class.php';
		include_once '../class/DirManager.class.php';
		include_once '../class/STYComBr.class.php';
		include_once '../class/Validador.class.php';
		$CloudServer = new CloudServer();
		$DirManager = new DirManager();
		$STYComBr = new STYComBr();
		$tpdocsac = $_POST['TPDOCSAC'];
		$codigo_sacado = $_POST['CODSAC'];
		$documento = $_POST['DOCSAC'];
		$sigla = strtoupper($_POST['CLI_SIGLA']);
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
		$endereco = substr(utf8_decode($_POST['ENDSAC']), 0, 60);
		$cidade = $_POST['CIDSAC'];
		$uf = $_POST['UFSAC'];
		$dicaend = $_POST['DICAEND'];
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
		$pessoa_entrega = $_POST['PESSOA_ENTREGA'];
		$nome_sacado_pesquisa = $_POST['NOMSAC_PESQUISA'];
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
			'CODSAC' => $codigo_sacado,
			'COBRANCA' => $cobranca,
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
			'TPDESC' => $tpdesc,
			'VLDESC' => $vldesc,
			'DIADESC' => $diadesc,
			'ENDCOB' => $endereco_cob,
			'END_CED' => $endereco_cedente,
			'DICAEND' => $dicaend,
			'END_CED2' => $endereco_cedente_2,
			'CODFOR' => $codfor,
			'PESSOA_ENTREGA' => $pessoa_entrega,
			'ENTREGA' => $entrega,
			'PAIS' => $pais,
			'PAIS_2' => $pais2,
			'NOMSAC_PESQUISA' => $nome_sacado_pesquisa,
			'CODCONV' => $codigo_convenio,
			'STATUS' => $status,
			'DATA_ASSOCIACAO' => str_replace('/', '.', $data_associacao),
			'CLI_SIGLA' => $sigla,
			'SITE' => $site,
			'REPASSE' => $repasse,
			'REPASSE_VARIACAO' => $repasse_variacao,
			'REPASSE_TARIFA' => $repasse_tarifa,
			'USUARIO' => $usuario,
			'SENHA' => $senha,
			'LOGWEB' => $logweb,
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
		//Data
		$dataToInsert = array(
			'table' => 'SACADOS',
			'columns' => $columns,
			'messageInSuccess' => 'Cliente cadastrado com sucesso',
			'jsonResponse' => true,
			'lastId' => true
			);
		//Insert
		$data = $crud->Insert($dataToInsert);

		//ADICIONA OS TELEFONES DO CLIENTE
		if ( count($telefones) > 0 ) {
			for ( $i = 0; $i < count($telefones); $i++ ) {
				//Columns
				$columns = array(
					'CODCON' => $crud->GetGenId('CONTATOS') + 1,
					'CODSAC' => $crud->GetGenId('SACADOS'),
					'NOMCON' => $telefones[$i]->descricao,
					'FONECON' => $telefones[$i]->numero
					);
				//Data
				$dataToInsert = array(
					'table' => 'CONTATOS',
					'columns' => $columns
					);
				//Insert
				$crud->Insert($dataToInsert);
			}
		}
		
		//ADICIONA OS EMAILS DO CLIENTE
		if ( count($emails) > 0 ) {
			for ( $i = 0; $i < count($emails); $i++ ) {
				//Columns
				$columns = array(
					'CODCON' => $crud->GetGenId('CONTATOS') + 1,
					'CODSAC' => $crud->GetGenId('SACADOS'),
					'NOMCON' => $emails[$i]->descricao,
					'EMAIL' => $emails[$i]->email
					);
				//Data
				$dataToInsert = array(
					'table' => 'CONTATOS',
					'columns' => $columns
					);
				//Insert
				$crud->Insert($dataToInsert);
			}
		}
		
		/*
		* CRIA UMA CONTA TRANSITORIA
		*/
		$path_errors = array();
		$ContaTransitoria = new ContaTransitoria($sigla);
		if ( !ContaTransitoria::create() ) $path_errors['CT_CREATE_ERROR'] = 'Não foi possível criar a conta transitória';

		/*
		* CRIA A ESTRUTURA DE PASTAS DO CLIENTE LOCAL
		*/
		$cliente_path = $DirManager->getDirs(array('CLIENTES'))['CLIENTES'][0];
		$pasta_modelo_customer = "$cliente_path\\Modelo";
		is_dir($pasta_modelo_customer) 
			? $DirManager->copyr($pasta_modelo_customer, "$cliente_path\\$sigla$codigo_sacado")
			: $path_errors['CUSTOMER_PATH_ERROR'] = "Pasta \"Modelo\" para criar pasta do cliente não foi encontrada em $cliente_path";

		//Cria a pasta Laboratório
		$rootLabPath = $DirManager->getDirs(array('LABORATORIO'))['LABORATORIO'][0];
		$auxLabPaths = array('backups', 'Remessa', 'Retorno', 'Atualizacoes');

		if ( !is_dir("$rootLabPath\\$sigla$codigo_sacado") ) {
			//Caso crie o diretório com sucesso
			//Adiciona as pastas auxiliares dentro da pasta criada
			if ( mkdir("$rootLabPath\\$sigla$codigo_sacado") ) {
				for ( $i = 0; $i < count($auxLabPaths); $i++ ) {
					$aux_path = "$rootLabPath$sigla$codigo_sacado\\$auxLabPaths[$i]";

					if ( !mkdir($aux_path) ) {
						$path_errors['LAB_MKDIR_AUXPATHS_ERROR'][] = $aux_path;
					}
				}
				//Cria config.ini do cliente
				$DirManager->createConfigIni(array(
					'path' => $rootLabPath,
					'name' => $nome_sacado,
					'sigla' => $sigla,
					'codigo_sacado' => $codigo_sacado 
				));
			} else {
				$path_errors['LAB_MKDIR_ERROR'] = "Não foi possível criar a pasta $rootLabPath";
			}
		} else {
			$path_errors['LAB_ORIGINAL_PATH'] = "Laboratório já existente";
		}

		/*
		* CRIA A ESTRUTURA DE PASTAS DO CLIENTE NO SERVIDOR NAS NUVENS
		*/
		$svn_aux_paths = array('retorno');
		$svn_customer_path = './clientes/'.strtolower($sigla).$codigo_sacado;
		$CloudServer->connect();
		if ( !$CloudServer->createDir($svn_customer_path, true) ) {
			$path_errors['SVN_CREATE_DIR'][] = "Não foi possível criar a pasta $svn_customer_path no Servidor nas Nuvens";
		} else {
			for ( $i = 0; $i < count($svn_aux_paths); $i++ ) {
				$svn_aux_path = "$svn_customer_path/$svn_aux_paths[$i]";

				if ( !$CloudServer->createDir($svn_aux_path) ) {
					$path_errors['SVN_CREATE_AUX_DIR'][] = $svn_aux_path;
				}
			}
		}
		//Envia a imagem do cliente para as imagens do app 2º via
		if ( isset($_FILES['fileToUpload']) ) {
			$svn_image_path = "/xampp/htdocs/app/2via/sistema/imagens/cedentes/";
			$image_name = str_pad($codigo_sacado, 5, '0', STR_PAD_LEFT).".gif";
			if ( !$CloudServer->send($_FILES['fileToUpload']['tmp_name'], "/$svn_image_path/$image_name") ) {
				$data['IMAGE_2VIA_ERROR'] = "Erro ao inserir imagem na 2º via";
			}
		}

		if ( $data['success'] ) {
			//Preparando os dados para a inserção
			$cedente_data = array(
				'codigo_cedente' => substr($codigo_sacado, -3),
				'nome_cedente' => strtoupper($nome_sacado),
				'sigla' => strtoupper($sigla),
				'site' => $site
			);
			//Inserção
			$validador = new Validador();
			$insert = $validador->Insert($cedente_data);
			
			if ( !$insert['success'] ) $data['validador_status'] = $insert['message'];

			//Inserindo o cliente no site de clientes
			$site_insert = $STYComBr->Insert(array(
				'pathname' => "$sigla$codigo_sacado.jpg",
				'sigla' => $sigla,
				'cliente' => $nome_sacado,
				'cliente_desde' => $data_associacao,
				'responsavel' => $responsavel,
				'endereco' => utf8_encode("$endereco - $cidade ($uf)"),
				'telefone1' => $telefones[0]->numero,
				'telefone2' => isset($telefones[1]) ? $telefones[1]->numero : NULL,
				'email' => $emails[0]->email,
				'site' => $site,
				'foto' => "$sigla$codigo_sacado.jpg",
				'area_atuacao' => $area_atuacao,
				'imagem_cliente' => isset($_FILES['fileToUpload']) ? $_FILES['fileToUpload'] : null,
				'status' => $status,
				'password' => md5("sty$codigo_sacado")
			));

			if ( !$site_insert['success'] ) $data['stycombr_status'] = $site_insert['error'];
		}

		echo json_encode(array_merge($data, $path_errors));
	} catch ( Exception $e ) {
		echo $e->getMessage();
		http_response_code(400);
	}