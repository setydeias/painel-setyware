<?php
	/*
	* @author Bruno Pontes
	* @description Processa os arquivos de remessa que vem dos clientes
	* e converte o mesmo de acordo com o layout do banco para enviar as remessas
	* ETAPAS DO PROCESSAMENTO
	* I) BAIXAR E LISTAR OS ARQUIVOS NO SERVIDOR NAS NUVENS
	* II) PROCESSAR OS ARQUIVOS E CATEGORIZÁ-LOS DE ACORDO COM O BANCO
	* III) MONTAR LAYOUT DE ACORDO COM CADA BANCO (BB, CEF, BRD)
	*/
	try {
		set_time_limit(0);
		error_reporting(0);
		ini_set('max_execution_time', -1);
		date_default_timezone_set('America/Sao_Paulo');
		$hoje = date('y').date('m').date('d');
		$data = json_decode(file_get_contents('php://input'), true);
		$baixarTitulosMTV = $data['baixarTitulosMTV'];
		$availableShipping = $data['remessas'];
		$pathReplacementFiles = $data['pathReplacementFiles'];
		$processedRecords = array();
		//Carregamento de dependencias
		include_once '../class/CloudServer.class.php';
		include_once '../class/DirManager.class.php';
		include_once '../class/FirebirdCRUD.class.php';
		include_once '../class/Convenio.class.php';
		include_once '../class/Util.class.php';
		include_once '../class/Customer.class.php';
		include_once '../class/RemessaRegistrada.class.php';
		include_once '../class/FilesHandler.class.php';
		include_once '../class/AnalysisRecord.class.php';
		include_once '../class/strategy_class/StrategyConvenio.class.php';
		$customer = new Customer();
		$FileHandler = new FilesHandler();
		$CloudServer = new CloudServer();
		$Convenio = new Convenio();
		$AnalysisRecord = new AnalysisRecord();
		//Convênios não configurados
		$convenios_nao_configurados = array();
		//Extensões permitidas
		$allowed_extensions = array('txt', 'TXT', 'rem', 'REM');
		//Capturando a numeração das remessas
		$crud = new FirebirdCRUD();
		$pathsToSelect = array('table' => 'REMESSAS_REGISTRADAS rr', 'params' => 'rr.REMESSA_BB, rr.REMESSA_BRD, rr.REMESSA_CEF, rr.REMESSA_SICOOB, rr.REMESSA_CONVERTIDA');
		$num_remessa = $crud->Select($pathsToSelect);
		$num_remessa_bb = $num_remessa['REMESSA_BB'][0];
		$num_remessa_brd = $num_remessa['REMESSA_BRD'][0];
		$num_remessa_cef = $num_remessa['REMESSA_CEF'][0];
		$num_remessa_sicoob = $num_remessa['REMESSA_SICOOB'][0];
		//Capturando as pastas para processamento
		$dir = new DirManager();
		$diretorio = $dir->getDirs(['PROCESSAMENTO_REMESSA_BANCO', 'REMESSA_PROCESSADA_BANCO', 'REMESSA_ORIGINAL_BANCO']);
		$path = $diretorio['PROCESSAMENTO_REMESSA_BANCO'][0];
		$pathProcessadas = $diretorio['REMESSA_PROCESSADA_BANCO'][0];
		$pathOriginais = $diretorio['REMESSA_ORIGINAL_BANCO'][0];
		$pathClientes = "C:\\Setydeias\\Setyware\\ADM77777\\Adm\\Clientes\\";
		//Remove os arquivos que já existem na pasta de processamento
		$dir->deleteFiles($path, $allowed_extensions);
		//Remove os arquivos que já foram processados anteriormente
		$dir->deleteFiles($pathProcessadas, $allowed_extensions);
		//Array que recebe o status do processamento
		$info = array();
		$info['TITULOS_TO_PRESCRIBE'] = array();
		//Recebe o pathname do cliente (BVP => BVP00221)
		$pathname = "";
		//Bancos que possuem registros no processamento
		$bancos = array();
		//Capturando a quantidade de arquivos do servidor
		//Se possuir pelo menos 1 arquivo, pode prosseguir o processamento
		$CloudServer->connect();
		if ( $CloudServer->countDirFiles('./clientes/remessa-registrada', $allowed_extensions) > 0 ) {
			//Baixa os arquivos do servidor para a pasta local
			$CloudServer->get('./clientes/remessa-registrada/', $path, $allowed_extensions);
			//Array que recebe todos os títulos
			$titulos = array();
			//Faz um loop nos arquivos baixados
			//Para capturar as informações da remessa
			$filesToRead = $dir->getFiles($path, $allowed_extensions);
			foreach ( $filesToRead as $file ) {
				if ( !in_array(basename($file), $availableShipping) ) continue;
				//Copia arquivo para pasta do cliente
				$pathname = substr(basename($file), 0, 3) != substr($pathname, 0, 3) 
					? $customer->GetPathNameBySigla(substr(basename($file), 0, 3))
					: $pathname;
				/* Faz uma cópia do arquivo para a pasta do cliente
				$dir->copyFiles($file, $pathClientes.$pathname.'\\remessas-registradas\\'.basename($file)); */
				//Arquivo convertido em array
				$file = file($file);
				//Array que recebe o título atual
				foreach ( $file as $record ) {
					//Segmento
					$segmento = substr($record, 0, 1);
					switch ( $segmento ) {
						//HEADER
						case '1': 
							$titulo['sigla_cliente'] = substr($record, 1, 3); //Sigla do cliente
							$titulo['matricula_cliente'] = substr($record, 4, 5); //Matrícula do cliente
							$titulo['razao_social'] = substr($record, 9, 40); //Razão social
							$titulo['doc_constituicao'] = substr($record, 49, 15); //Documento de Constituição (CPF/CNPJ)
							$titulo['convenio'] = substr($record, 64, 8); //Convênio de cobrança
							$titulo['carteira'] = substr($record, 72, 2); //Carteira de cobrança
							$titulo['carteira_variacao'] = substr($record, 74, 3); //Variação da carteira de cobrança
							$titulo['data_envio'] = substr($record, 77, 8); //Data de envio
							$titulo['hora_envio'] = substr($record, 85, 6); //Hora do envio
							$titulo['numero_remessa'] = substr($record, 91, 5); //Número da remessa
							break;
						//DADOS DO TÍTULO
						case 'P':
							$titulo['bank'] = substr($record, 1, 3); //Banco do tótulo
							$titulo['agreement'] = substr($record, 4, 7); //Convênio de cobrança
							$titulo['our_number'] = substr($record, 11, 20); //Nosso número
							$titulo['dt_vcto'] = substr($record, 31, 8); //Data de vencimento
							$titulo['value'] = substr($record, 39, 15); //Valor nominal do título
							$titulo['kind'] = substr($record, 54, 2); //Espécie do título
							$titulo['aceite'] = substr($record, 56, 1); //Aceite
							$titulo['dt_emiss'] = substr($record, 57, 8); //Data de emissão
							$titulo['cod_juros'] = substr($record, 65, 1); //Código de tipo de juros
							$titulo['dt_juros'] = substr($record, 66, 8); //Data de juros
							$titulo['value_juros'] = substr($record, 74, 15); //Valor a cobrar de juros
							$titulo['cod_desc'] = substr($record, 89, 1); //Código do tipo de desconto
							$titulo['dt_desc'] = substr($record, 90, 8); //Data limite para conceder o desconto
							$titulo['value_desc'] = substr($record, 98, 15); //Valor a conceder de desconto
							$titulo['discount'] = substr($record, 113, 15); //Abatimento
							$titulo['cod_protest'] = substr($record, 128, 1); //Código do tipo de protesto
							$titulo['days_to_protest'] = substr($record, 129, 2); //Quatidade de dias para protestar
							$titulo['coin'] = substr($record, 131, 2); //Moeda
							$titulo['agencia'] = substr($record, 133, 4); //Agência
							$titulo['agencia_dv'] = substr($record, 137, 1); //Dígito verificador da agência
							$titulo['conta_corrente'] = substr($record, 138, 12); //Conta corrente
							$titulo['conta_corrente_dv'] = substr($record, 150, 1); //Dígito verificador da conta corrente
							$titulo['cod_mov'] = substr($record, 151, 2); //Código de movimentação
							//Checando se a data de emissão é maior que a data de vencimento
							if ( (Util::FmtDate($titulo['dt_vcto'], '4') < Util::FmtDate($titulo['dt_emiss'], '4')) && $titulo['cod_mov'] == '01' ) $titulo['dt_vcto'] = $titulo['dt_emiss'];
							//Checando se a data de vencimento é menor que a data atual
							if ( (Util::FmtDate($titulo['dt_vcto'], '4') < Util::FmtDate($hoje, '14')) && $titulo['cod_mov'] == '01' ) $titulo['dt_vcto'] = Util::FmtDate($hoje, '15');
							break;
						//DADOS DO SACADO
						case 'Q':
							$titulo['pagador_kind'] = substr($record, 1, 1); //Tipo de inscrição do pagador
							$tipo_pagador = $titulo['pagador_kind'];
							/*
							* CAMPO DOCUMENTO NÃO PODE SER VAZIO
							* Caso o documento do sacado vier "0" significa que o mesmo
							* não possui nenhum documento vinculado a ele.
							* Caso o documento do sacado vier "3" significa que o mesmo
							* está com a opção OUTROS vinculado como tipo de documento.
							* Nestes casos o documento que será vinculado ao pagador será o documento do Beneficiário
							*/
							if ($tipo_pagador == '0' || $tipo_pagador == '3') :
								//Obtendo os dados do condomínio
								$dadosCliente = $customer->GetDocSacBySigla($titulo['sigla_cliente']);
								//Se a consulta retornar algum resultado
								if (count($dadosCliente) > 0) :
									$titulo['pagador_kind'] = $dadosCliente['TPDOC']; //Tipo do documento do beneficiário "1 -> PF ou 2 -> PJ"
									$titulo['pagador_num_doc'] = str_pad($dadosCliente['DOC'], 15, '0', STR_PAD_LEFT); //Documento do beneficiário
								else :
									$not_found[] = trim($titulo['our_number']);
									$titulo['pagador_num_doc'] = str_pad('0', 15, '0', STR_PAD_LEFT);
								endif;
							else :
								/* 
								* Se for CPF/CNPJ
								* Verifica se o documento é válido
								* Se for, mantém
								* Se não for, recupera os dados do Beneficiário
								*/
								$titulo['pagador_num_doc'] = substr($record, 2, 15); //Número de inscrição do pagador
								if (!Util::ValidarDocumento($tipo_pagador, $titulo['pagador_num_doc'])) :
									$dadosCliente = $customer->GetDocSacBySigla($titulo['sigla_cliente']);
									//Se a consulta retornar algum resultado
									if (count($dadosCliente) > 0) :
										$titulo['pagador_kind'] = $dadosCliente['TPDOC']; //Tipo do documento do beneficiário "1 -> PF ou 2 -> PJ"
										$titulo['pagador_num_doc'] = str_pad($dadosCliente['DOC'], 15, '0', STR_PAD_LEFT); //Documento do beneficiário
									else :
										$not_found[] = trim($titulo['our_number']);
										$titulo['pagador_num_doc'] = str_pad('0', 15, '0', STR_PAD_LEFT);
									endif;
								endif;
							endif;
							$titulo['pagador_nome'] = substr($record, 17, 40); //Nome do pagador
							$titulo['pagador_endereco'] = utf8_encode(ltrim(substr($record, 57, 40))); //Endereço do pagador
							$titulo['pagador_bairro'] = substr($record, 97, 15); //Bairro do pagador
							$titulo['pagador_cep'] = substr($record, 112, 8); //CEP do pagador
							$titulo['pagador_cidade'] = substr($record, 120, 15); //Cidade do pagador
							$titulo['pagador_uf'] = substr($record, 135, 2); //Unidade de Federação do pagador
							break;
						//DADOS PERSONALIZADOS DO TÍTULO
						case 'R':
							$titulo['cod_desc_2'] = substr($record, 1, 1); //Código do desconto 2
							$titulo['dt_desc_2'] = substr($record, 2, 8); //Data do desconto 2
							$titulo['value_desc_2'] = substr($record, 10, 15); //Valor do desconto 2
							$titulo['cod_desc_3'] = substr($record, 25, 1); //Código do desconto 3
							$titulo['dt_desc_3'] = substr($record, 26, 8); //Data do desconto 3
							$titulo['value_desc_3'] = substr($record, 34, 15); //Valor do desconro 3
							$titulo['cod_multa'] = substr($record, 49, 1); //Código da multa
							$titulo['dt_multa'] = substr($record, 50, 8); //Data da multa
							$titulo['value_multa'] = substr($record, 58, 15); //Valor da multa
							$titulo['instru1'] = substr($record, 73, 40); //Mensagem ao pagador
							$titulo['instru2'] = substr($record, 113, 40); //Mensagem ao pagador
							$titulo['pagador_email'] = substr($record, 153, 50); //Email do pagador
							//Vinculando o título a matriz do banco que o mesmo pertence
							$titulos[$titulo['bank']][$titulo['agreement']][] = $titulo;
						//TRAILER
						case '9':
							$qtde_titulos = substr($record, 1, 5); //Quantidade de títulos na remessa
							break;
					}
				}
			}
			//Insere os dados de relatório do Banco do Brasil no banco para verificar se o nosso número existe
			$dataFileReplacement = $Convenio->getConvenioFileReplacement();
			function returnConveniosFromData($data)  {
				return $data['CONVENIO'];
			}
			$convenios_aptos_checagem = array_map('returnConveniosFromData', $dataFileReplacement);
			$filesToCheck = $dir->getFiles($pathReplacementFiles, array('bbt'));
			
			for ( $c = 0; $c < count($dataFileReplacement); $c++ ) {
				$banco_convenio = $dataFileReplacement[$c]['BANCO'];
				$convenio_to_check = $dataFileReplacement[$c]['CONVENIO'];
				$mantenedor = $dataFileReplacement[$c]['MANTENEDOR'];
				//Caso o convênio seja apto a verificação
				if ( isset($titulos[$banco_convenio][$convenio_to_check]) ) {
					$find = false;
					//Loop nos arquivos de verificação
					for ( $f = 0; $f < count($filesToCheck); $f++ ) {
						$line_to_check = file($filesToCheck[$f])[0];
						$file_conv = substr($line_to_check, 54, 7);
						$find = $convenio_to_check === $file_conv;
						if ( $find ) break;
					}
					//Se não encontrar o convênio nos arquivos de verificação
					if ( !$find ) {
						echo json_encode(array('error' => "O arquivo de reposição para o convênio <b>$convenio_to_check</b> não foi encontrado"));
						return false;
					}
				}
			}

			if ( !$FileHandler->InsertDataReport($filesToCheck) ) {
				echo json_encode(array('error' => "Erro ao inserir títulos no banco, tente novamente"));
				return false;
			}
			/*
			* Gerando os títulos de acordo com o banco e convênio
			*/
			foreach ( $titulos as $banco => $convenios ) {
				if ( !in_array($banco, $bancos) ) $bancos[] = $banco;
				foreach ( $convenios as $convenio => $titulo ) {
					//Contadores
					$qtde_titulos_per_conv = 0;
					$qtde_titulos_to_entry = 0;
					$qtde_titulos_to_change = 0;
					$qtde_titulos_to_drop = 0;
					$qtde_titulos_codmov_undefined = 0;
					//Parâmetros do convênio da remessa
					$strategy = new StrategyConvenio($convenio);
					$params = $strategy->getParams();
					if ( is_null($params) ) {
						$convenios_nao_configurados[] = $convenio;
						continue;
					}
					//Nome do arquivo
					switch ( $banco ) {
						case '001': $numero_remessa = $num_remessa_bb; break;
						case '104': $numero_remessa = $num_remessa_cef; break;
						case '237': $numero_remessa = $num_remessa_brd; break;
						case '756': $numero_remessa = $num_remessa_sicoob; break;
					}
					$params = !is_null($params) ? array_merge($params, array("file" => "{$pathProcessadas}IEDCBR_{$hoje}_{$params['sigla']}_{$banco}_{$convenio}_{$numero_remessa}.REM")) : null;
					//Criado o arquivo através do método construtor
					$RemessaRegistrada = new RemessaRegistrada($params);
					//Contador de registros
					$num_registro = 0;
					//Matrícula inicial do cliente
					$matricula_cliente = "";
					//Matriz que recebe o "nosso número"
					//Checa a existencia do nosso número no arquivo de retorno
					$nosso_numero_check = array();
					//Escrevendo os segmentos
					foreach ( $titulos[$banco][$convenio] as $titulo ) {
						//Valores que podem ser alterados/checados no processamento
						//devido aos dados inicialmente informados no banco
						$dtvcto = $titulo['dt_vcto'];
						$valor_titulo = $titulo['value'];
						$nosso_numero = trim($titulo['our_number']);
						$cod_mov = $titulo['cod_mov'];
						//Checa se o título já foi inserido na remessa
						//Checa se o código de movimentação é o mesmo, pois se for diferente
						//significa que há outra operação para o título
						//Se já houver sido inserido, não processa
						$keyCodMov = array_search($nosso_numero, array_column($nosso_numero_check, 'nosso_numero'));
						if ( $keyCodMov !== false && $nosso_numero_check[$keyCodMov]['cod_mov'] == $cod_mov) {
							continue;
						} else {
							//Insere o nosso número no array para verificar nas próximas ocorrências se o mesmo
							//já foi processado
							$nosso_numero_check[] = array('nosso_numero' => $nosso_numero, 'cod_mov' => $cod_mov);
							//Incrementando os contadores
							if ( $cod_mov == '01' ) $qtde_titulos_to_entry++;
							if ( $cod_mov == '02' ) $qtde_titulos_to_drop++;
							if ( $cod_mov == '06' ) $qtde_titulos_to_change++;
							if ( $cod_mov != '01' && $cod_mov != '02' && $cod_mov != '06' ) $qtde_titulos_codmov_undefined++;
							//VERIFICANDO SE DATA DE VENCIMENTO E VALORES ESTÃO COMPATÍVEIS COM O BANCO
							if ( in_array($convenio, $convenios_aptos_checagem) && ($cod_mov == "06" || $cod_mov == "02") ) {
								$DadosVerificados = $FileHandler->GetDataByOurNumber($nosso_numero, $cod_mov);
								if ( count($DadosVerificados) > 0 && $DadosVerificados['STATUS'][0] = "Normal") {
									isset( $DadosVerificados['DATA_VENCIMENTO'][0] ) ? $dtvcto = Util::FmtDate($DadosVerificados['DATA_VENCIMENTO'][0], '5') : '';
									$valor_titulo = $DadosVerificados['VALOR'][0];
								}
							}
							//Segmento P
							$segmento_p_args = array(
								'registro' => '3', 'num_registro' => ++$num_registro, 'segmento' => 'P', 'cod_mov' => $cod_mov, 'agencia' => $RemessaRegistrada->agencia,
								'conta' => $RemessaRegistrada->conta, 'nosso_numero' => $nosso_numero, 'cod_carteira' => '7', 'forma_cadastramento' => '1', 'tipo_documento' => '0',
								'id_emissao' => '2', 'id_distribuicao' => '2', 'num_documento' => str_pad(substr($nosso_numero, 7), 11, ' ', STR_PAD_RIGHT), 'data_vencimento' => $dtvcto,
								'valor_titulo' => str_pad($valor_titulo, 15, '0', STR_PAD_LEFT), 'especie' => '17', 'aceite' => 'N', 'data_emissao' => $titulo['dt_emiss'], 'cod_juros' => $titulo['cod_juros'],
								'data_juros' => $titulo['dt_juros'], 'juros' => $titulo['value_juros'], 'cod_desc_1' => $titulo['cod_desc'], 'data_desc_1' => $titulo['dt_desc'], 'valor_desc_1' => $titulo['value_desc'],
								'iof' => '0', 'valor_abatimento' => $titulo['discount'], 'seu_numero' => str_pad(substr($nosso_numero, 7), 11, ' ', STR_PAD_RIGHT), 'cod_protesto' => '3',
								'num_dias_protesto' => '0', 'cod_baixa' => '0', 'num_dias_baixa' => '0', 'moeda' => '09'
							);
							//Checando os parâmetros do cliente
							//Tipo do beneficiário (1 -> PF / 2 -> PJ)
							if ( $matricula_cliente != $titulo['matricula_cliente'] ) $matricula_cliente = $titulo['matricula_cliente'];

							$SelectTPDOCSAC = array('table' => 'SACADOS s', 'params' => 's.TPDOCSAC', 'where' => array('s.REPASSE_VARIACAO' => (int) $titulo['matricula_cliente']));
							$tpDocSac = $crud->Select($SelectTPDOCSAC);
							$tpDocSac = count($tpDocSac) > 0 ? $tpDocSac['TPDOCSAC'][0] : '2';
							//Segmento Q
							$segmento_q_args = array(
								'registro' => '3', 'num_registro' => ++$num_registro, 'segmento' => 'Q', 'cod_mov' => $cod_mov, 'tipo_inscricao' => $titulo['pagador_kind'],
								'numero_inscricao' => $titulo['pagador_num_doc'], 'nome' => $titulo['pagador_nome'], 'endereco' => $titulo['pagador_endereco'], 'bairro' => $titulo['pagador_bairro'],
								'cep' => $titulo['pagador_cep'], 'cidade' => $titulo['pagador_cidade'], 'uf' => $titulo['pagador_uf'], 'tp_inscricao_avalista' => $tpDocSac,
								'documento_avalista' => $titulo['doc_constituicao'], 'nome_avalista' => $titulo['razao_social']
							);
							//Segmento R
							$segmento_r_args = array(
								'registro' => '3', 'num_registro' => ++$num_registro, 'segmento' => 'R', 'cod_mov' => $cod_mov, 'cod_desc_2' => $titulo['cod_desc_2'],
								'data_desc_2' => $titulo['dt_desc_2'], 'valor_desc_2' => $titulo['value_desc_2'], 'cod_desc_3' => $titulo['cod_desc_3'], 'data_desc_3' => $titulo['dt_desc_3'],
								'valor_desc_3' => $titulo['value_desc_3'], 'cod_multa' => $titulo['cod_multa'], 'data_multa' => $titulo['dt_multa'], 'valor_multa' => $titulo['value_multa']
							);
							//Formata os segmentos
							$segmento_p = $RemessaRegistrada->SegmentoP($RemessaRegistrada->allParams, $segmento_p_args);
							$segmento_q = $RemessaRegistrada->SegmentoQ($RemessaRegistrada->allParams, $segmento_q_args);
							$segmento_r = $RemessaRegistrada->SegmentoR($RemessaRegistrada->allParams, $segmento_r_args);
							//Insere os dados do registro no array que será inserido no banco de registros processados
							$processedRecords[] = array(
								'BANCO' => $banco,
								'CONVENIO' => $convenio,
								'SIGLA_CLIENTE' => $titulo['sigla_cliente'],
								'COD_MOVIMENTO' => $titulo['cod_mov'],
								'NOSSO_NUMERO' => trim($nosso_numero),
								'SEU_NUMERO' => trim($segmento_p_args['num_documento']),
								'MOEDA' => '09',
								'AGENCIA' => substr($segmento_p_args['agencia'], 0, -1),
								'AGENCIA_DV' => substr($segmento_p_args['agencia'], -1),
								'CONTA_CORRENTE' => substr($segmento_p_args['conta'], 0, -1),
								'CONTA_CORRENTE_DV' => substr($segmento_p_args['conta'], -1),
								'VALOR' => $titulo['value'] / 100,
								'VALOR_TARIFA' => 0,
								'VALOR_ENCARGOS' => 0,
								'VALOR_DESCONTO_CONCEDIDO' => $titulo['value_desc'] / 100,
								'VALOR_ABATIMENTO' => $titulo['discount'] / 100,
								'VALOR_PAGO' => 0,
								'VALOR_CREDITADO' => 0,
								'DATA_VCTO' => Util::FmtDate($titulo['dt_vcto'], '25'),
								'DATA_ARQUIVO' => Util::FmtDate($titulo['data_envio'], '25'),
								'MOTIVO_REJEICAO' => NULL,
								'VALOR_TITULO' => $titulo['value'] / 100,
								'TIPO_REGISTRO' => 'REMESSA'
							);
							//Adiciona os segmentos no arquivo
							$RemessaRegistrada->AddSegment(array($segmento_p, $segmento_q, $segmento_r));
							//Incrementa a quantidade de títulos processados
							$qtde_titulos_per_conv++;
						}
					}
					//Informações do arquivo gerado
					$info['FILES'][] = array(
						'file' => basename($params['file']),
						'titulos_to_entry' => $qtde_titulos_to_entry,
						'titulos_to_drop' => $qtde_titulos_to_drop,
						'titulos_to_change' => $qtde_titulos_to_change,
						'titulos_codmov_undefined' => $qtde_titulos_codmov_undefined
					);
					//Informa a quantidade de títulos para determinado convênio
					//$info['BANCOS'][$banco][$convenio]['qtde'] = $qtde_titulos_per_conv;
					//Ao incluir todos os registros
					//Verificar se existem títulos a prescrever
					if ( $baixarTitulosMTV && in_array($convenio, $convenios_aptos_checagem) ) {
						$dateRef = Util::GetQntMonthsDays();

						$dataToSelect = array(
							'table' => 'PROCESSAMENTO_REMESSAS pr',
							'params' => 'pr.NOSSO_NUMERO, pr.DATA_VENCIMENTO, pr.VALOR, pr.AGENCIA, pr.CONTA, pr.PAGADOR, pr.SEU_NUMERO, pr.TPDOC, pr.DOCSAC, pr.SIGLA_CLIENTE',
							'where' => "pr.DATA_VENCIMENTO < '$dateRef' AND pr.CONVENIO = '$convenio'"
						);
						
						$DataToDown = $crud->Select($dataToSelect); //Retorna todos os títulos que deverão ser baixados

						if ( isset($DataToDown['NOSSO_NUMERO']) ) {
							$info['TITULOS_TO_PRESCRIBE'][$convenio] = count($DataToDown['NOSSO_NUMERO']);
							for ( $i = 0; $i < count($DataToDown['NOSSO_NUMERO']); $i++ ) {
								$segmento_p_args = array(
									'registro' => '3', 'num_registro' => ++$num_registro, 'segmento' => 'P', 'cod_mov' => '02', 'agencia' => $params['agencia'], 'conta' => $params['conta'],
									'nosso_numero' => $DataToDown['NOSSO_NUMERO'][$i], 'cod_carteira' => '7', 'forma_cadastramento' => '1', 'tipo_documento' => '0', 'id_emissao' => '2',
									'id_distribuicao' => '2', 'num_documento' => $DataToDown['SEU_NUMERO'][$i], 'data_vencimento' => Util::FmtDate($DataToDown['DATA_VENCIMENTO'][$i], '5'),
									'valor_titulo' => $DataToDown['VALOR'][$i], 'especie' => '17', 'aceite' => 'N', 'data_emissao' => Util::FmtDate($DataToDown['DATA_VENCIMENTO'][$i], '5'),
									'cod_juros' => '0', 'data_juros' => '0', 'juros' => '0', 'cod_desc_1' => '0', 'data_desc_1' => '0', 'valor_desc_1' => '0', 'iof' => '0', 'valor_abatimento' => '0',
									'seu_numero' => $DataToDown['SEU_NUMERO'][$i], 'cod_protesto' => '3', 'num_dias_protesto' => '0', 'cod_baixa' => '0', 'num_dias_baixa' => '0', 'moeda' => '09'
								);

								//Capturando o endereço do cliente de acordo com a matrícula do mesmo
								$EnderecoAvalista = array(
									'table' => 'SACADOS s',
									'params' => 's.TPDOCSAC, s.DOCSAC, s.NOMSAC, s.ENDSAC, s.CIDSAC, s.UFSAC, s.CEP',
									'where' => array( 's.REPASSE_VARIACAO' => substr($DataToDown['NOSSO_NUMERO'][$i], 7, 3) )
								);
								
								//Obtendo o endereço do pagador
								$EnderecoData = $crud->Select($EnderecoAvalista);
								$endereco = explode(' - ', trim($EnderecoData['ENDSAC'][0]));
								$logradouro = Util::RemoverAcentos($endereco[0]);
								$bairro = Util::RemoverAcentos($endereco[1]);
								//Obtendo o tipo de pessoa do pagador (1 -> PF | 2 -> PJ)
								$tpDocSac = count($EnderecoData['TPDOCSAC']) > 0 ? $EnderecoData['TPDOCSAC'][0] : '2';

								$segmento_q_args = array(
									'registro' => '3', 'num_registro' => ++$num_registro, 'segmento' => 'Q', 'cod_mov' => '02', 'tipo_inscricao' => $DataToDown['TPDOC'][$i],
									'numero_inscricao' => $DataToDown['DOCSAC'][$i], 'nome' => $DataToDown['PAGADOR'][$i], 'endereco' => $logradouro, 'bairro' => $bairro,
									'cep' => trim($EnderecoData['CEP'][0]), 'cidade' => trim(strtoupper($EnderecoData['CIDSAC'][0])), 'uf' => trim(strtoupper($EnderecoData['UFSAC'][0])),
									'tp_inscricao_avalista' => $tpDocSac, 'documento_avalista' => $EnderecoData['DOCSAC'][0], 'nome_avalista' =>  Util::RemoverAcentos(strtoupper($EnderecoData['NOMSAC'][0]))
								);

								$segmento_r_args = array(
									'registro' => '3', 'num_registro' => ++$num_registro, 'segmento' => 'R', 'cod_mov' => '02', 'cod_desc_2' => '0', 'data_desc_2' => '0', 'valor_desc_2' => '0',
									'cod_desc_3' => '0', 'data_desc_3' => '0', 'valor_desc_3' => '0', 'cod_multa' => '0', 'data_multa' => '0', 'valor_multa' => '0'
								);

								//Formata os segmentos
								$segmento_p = $RemessaRegistrada->SegmentoP($RemessaRegistrada->allParams, $segmento_p_args);
								$segmento_q = $RemessaRegistrada->SegmentoQ($RemessaRegistrada->allParams, $segmento_q_args);
								$segmento_r = $RemessaRegistrada->SegmentoR($RemessaRegistrada->allParams, $segmento_r_args);
								//Insere os dados do registro no array que será inserido no banco de registros processados
								$processedRecords[] = array(
									'BANCO' => $RemessaRegistrada->cod_banco,
									'CONVENIO' => $convenio,
									'SIGLA_CLIENTE' => $DataToDown['SIGLA_CLIENTE'][$i],
									'COD_MOVIMENTO' => $segmento_p_args['cod_mov'],
									'NOSSO_NUMERO' => trim($segmento_p_args['nosso_numero']),
									'SEU_NUMERO' => trim($segmento_p_args['num_documento']),
									'MOEDA' => '09',
									'AGENCIA' => $titulo['agencia'],
									'AGENCIA_DV' => $titulo['agencia_dv'],
									'CONTA_CORRENTE' => $titulo['conta_corrente'],
									'CONTA_CORRENTE_DV' => $titulo['conta_corrente_dv'],
									'VALOR' => $segmento_p_args['valor_titulo'] / 100,
									'VALOR_TARIFA' => 0,
									'VALOR_ENCARGOS' => 0,
									'VALOR_DESCONTO_CONCEDIDO' => $segmento_p_args['valor_desc_1'] / 100,
									'VALOR_ABATIMENTO' => $segmento_p_args['valor_abatimento'] / 100,
									'VALOR_PAGO' => 0,
									'VALOR_CREDITADO' => 0,
									'DATA_VCTO' => Util::FmtDate($segmento_p_args['data_vencimento'], '25'),
									'DATA_ARQUIVO' => date('Y.m.d'),
									'MOTIVO_REJEICAO' => NULL,
									'VALOR_TITULO' => $segmento_p_args['valor_titulo'] / 100,
									'TIPO_REGISTRO' => 'REMESSA'
								);
								//Adiciona os segmentos no arquivo
								$RemessaRegistrada->AddSegment(array($segmento_p, $segmento_q, $segmento_r));
							}
						}
					}
					$RemessaRegistrada->AddTrailer($RemessaRegistrada->allParams);
					$dir->copyFiles($RemessaRegistrada->file, $pathOriginais.basename($RemessaRegistrada->file));
				}
			}
			//Caso haja algum título processado o mesmo será inserido na tabela de títulos processados para futuras consultas
			if ( count($processedRecords) > 0 ) {
				$AnalysisRecord->InsertRecordData($processedRecords);
			}
			//Remove todos os títulos do relatório do Banco do Brasil do banco de dados
			$FileHandler->RemoveDataReport();
			//Atualiza a numeração da remessa dos bancos que tiveram registro
			if ( isset($RemessaRegistrada) ) $RemessaRegistrada->UpdateSeqShipping($bancos);
			//Exclui os arquivos processados do servidor
			$CloudServer->delete('./clientes/remessa-registrada/', array('REM'));
		} else {
			$info['error'] = array('NoShippings' => 'Não existem arquivos pendentes de processamento');
		}
		//Adiciona os convênios não configurados no JSON para mostrar ao usuário
		if ( count($convenios_nao_configurados) > 0 ) $info['CONVENIOS_NOT_SETTED'] = array_unique($convenios_nao_configurados);
		//Envia os dados para a tela via JSON
		echo json_encode($info);
	} catch (Exception $e) {
		echo json_encode(array("RuntimeError" => "Erro no processamento: $e"));
	}