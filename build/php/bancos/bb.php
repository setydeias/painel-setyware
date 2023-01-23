<?php
	//Showing the bank's image
	echo '<img src="/painel/build/images/bb.png" width="200" height="35" /> <br/><br/>';
	//Path to processed files
	$pathaux = $processed;
	$diraux = dir($pathaux);
	$lqrTax = $customer->getLqrTax();
	//Arrays auxiliares
	$arquivos_com_duplicidade = $repasseAtrasado = $LQRpayments = $duplicidades = $entrada_confirmada = $entrada_rejeitada = $vencimento_alterado = $titulos_baixados = $intrucoes_rejeitadas = $debito_tarifas = $outras_ocorrencias = array();
	/*
	* (1º ETAPA)
	* Lista os arquivos que contém pagamentos
	* Processa linha a linha e destina a informação do pagamento
	* ao devido cliente de acordo com o NOSSO NÚMERO do REGISTRO DETALHE
	*/
	for ( $i = 0; $i < count($bb); $i++ ) {
		if (filesize($bb[$i]) > 1000) {
			/*
			* 1.1) CAPTURAÇÃO DAS INFORMAÇÕES GERAIS DO ARQUIVO (HEADER, TRAILER)
			*/
			//Capturing the file's header
	 		$header = file($bb[$i])[0].file($bb[$i])[1];
	 		//Capturing the file's trailer
	 		$trailer = file($bb[$i])[count(file($bb[$i]))-2].file($bb[$i])[count(file($bb[$i]))-1];
	 		//Capturing the file's date
	 		$data = fmtDatePattern(substr($header, 143, 8), '1');
	 		$dataBase = fmtDatePattern(substr($header, 143, 8), '1');
	 		//Sequencial
	 		$sequence = substr($header, 157, 6);
	 		//Convenio
		 	$conv = substr($header, 34, 7);
			//Verificação de duplicidades
		 	if ( $checarDuplicidade == '1' ) {
	 			$dataReferencia = date('Y-m-d', strtotime("-$diasDuplicidades days", strtotime($data))); //Data de referência para a comparação nas duplicidades
				$duplicidades[] = verificarDuplicidade($crud, $conv, $data, $dataBase, $dataReferencia, $orig, 'bb');
			}
	 		//Capturing the string where there's the file
	 		$file = explode('\\', $bb[$i]);
	 		//Aux var
	 		$sigla = null;
			/*
			* 1.2) VARREDURA DOS REGISTROS DETALHE, LINHA A LINHA
			* VERIFICA QUAL É O CLIENTE QUE POSSUI AQUELE PAGAMENTO
			* E VINCULA A INFORMAÇÃO DO PGTO AO SEU ARQUIVO DE RETORNO
			*/
 			for ($k = 2; $k < (count(file($bb[$i]))-2); $k++) {
	 			if ( in_array($conv, $setydeiasConvs) ) {
	 				$cod = substr(file($bb[$i])[$k], 44, 3); //Matrícula do cliente
	 				$customerSigla = $customer->GetSiglaByCodSac($cod);
		 			$cli = strlen($customerSigla) <= 0 ? 'INDEFINIDO' : $customerSigla;
				} else {
					$customer_index = array_search($conv, $just_convenios_proprios);
					$cli = !$customer_index ? 'INDEFINIDO' : $conveniosProprios[$customer_index]['MANTENEDOR'];
				}
				//Código de Movimentação (C044)
	 			$cod_movimentacao = substr(file($bb[$i])[$k], 15, 2);
	 			//Segmento do registro detalhe
	 			$segment = substr(file($bb[$i])[$k], 13, 1);
	 			if ($segment == 'T') {
					$segmentoT = file($bb[$i])[$k];
					$segmentoU = file($bb[$i])[$k+1];
	 				$nosso_numero = trim(substr($segmentoT, 37, 20)); //Nosso número do registro
					$numero_registro = substr($segmentoT, 8, 5); //Sequencial do registro
					$ocorrencias_pgto = array('06', '17');
					$was_paid = in_array($cod_movimentacao, $ocorrencias_pgto);
					$was_rejected = $cod_movimentacao === '03';
					//Try new client
					if ( $cli != $sigla ) {
					   $sigla = $cli;
				   	}
		 			//Verifica o código de movimentação
		 			if ( $was_paid ) {
					 	//Verifica se o repasse foi atrasado
						if ( $data > fmtDatePattern(substr($segmentoU, 137, 8), '1') ) {
							$repasseAtrasado[] = array(
								'NOSSO_NUMERO' => $nosso_numero, 
								'CLIENTE' => $sigla, 
								'VALOR_TITULO' => number_format(substr($segmentoT, 81, 15) / 100, 2, ',', '.'),
								'VALOR_PAGO' => number_format(substr($segmentoU, 77, 15) / 100, 2, ',', '.'),
							);
						}
						//Verifica se o pagamento foi LQR
						$tax = substr($segmentoT, 198, 15);
						if ( $tax == str_pad(number_format($lqrTax, 2, '', ''), 15, '0', STR_PAD_LEFT) ) {
							$LQRpayments[] = array(
								'cliente' => $sigla,
								'valor_titulo' => number_format(substr($segmentoT, 81, 15) / 100, 2, ',', '.'),
								'vencimento' => null,
								'nosso_numero' => $nosso_numero
							);
						}
						//Create the file that will be overwritten
						$arquivo = 'IEDCBR_'.fmtDatePattern($data, '8').'_'.$sigla.'_001_'.$conv.'_'.$sequence.'.ret';
						//Open the file
						$fp = fopen($processed.$arquivo, 'a');
						//If was the first line of the file
						count(file($processed.$arquivo)) == 0 ? fwrite($fp, $header) : '';
						//Verificar se a checagem de duplicidades está ativa
						if ($checarDuplicidade == '1') {
							//Checa se existe duplicidade para jogar as mesmas no array
							if (count($duplicidades) > 0) {
								//Individualiza a duplicidade
								foreach ($duplicidades as $dp) {
									foreach ($dp as $duplicidade) {
										//Se o nosso número, sequencial e número do registro atual for igual ao da duplicidade de referencia (mesmo dia)
										//Ou se nosso número é igual o da referência mas o sequencial do arquivo não (dia anterior)
										//Então não escreve a linha, pois é duplicidade
										if ( $nosso_numero == $duplicidade['nosso_numero'] && $sequence == $duplicidade['seq_arquivo'] && $numero_registro == $duplicidade['num_registro']
											|| ($nosso_numero == $duplicidade['nosso_numero'] && $sequence != $duplicidade['seq_arquivo']) ) {
											if (!in_array($processed.$arquivo, $arquivos_com_duplicidade)) {
												$arquivos_com_duplicidade[] = $processed.$arquivo;
											}
											$duplicidadect[] = $cli;
											break;
										}
									}
								}
								//Escreve a linha
								$line = $segmentoT.$segmentoU; //Registos T e U
								fwrite($fp, $line);
							} else {
								$line = $segmentoT.$segmentoU; //Registos T e U
								fwrite($fp, $line);
							}
						} else {
							$line = $segmentoT.$segmentoU; //Registos T e U
							fwrite($fp, $line);
						}
						//Close the handler file
						fclose($fp);
					} else {
						$vencimento = substr($segmentoT, 73, 8);
						$valor_titulo = number_format(substr($segmentoT, 81, 15)/100, 2, ',', '.');
						switch ($cod_movimentacao) {
							case '02':
								$entrada_confirmada[] = array(
									'valor_titulo' => $valor_titulo,
									'vencimento' => $vencimento, 
									'nosso_numero' => $nosso_numero
								);
							break;
							case '03':
								$motivo = substr($segmentoT, 213, 10);
								$entrada_rejeitada[] = array(
									'valor_titulo' => $valor_titulo,
									'vencimento' => $vencimento,
									'nosso_numero' => $nosso_numero,
									'motivo' => $motivo
								);
							break;
							case '09':
								$titulos_baixados[] = array(
									'valor_titulo' => $valor_titulo,
									'vencimento' => $vencimento,
									'nosso_numero' => $nosso_numero	
								);
							break;
							case '14':
								$vencimento_alterado[] = array(
									'valor_titulo' => $valor_titulo,
									'vencimento' => $vencimento,
									'nosso_numero' => $nosso_numero	
								);
							break;
							case '26':
								$intrucoes_rejeitadas[] = array(
									'valor_titulo' => $valor_titulo,
									'vencimento' => $vencimento,
									'nosso_numero' => $nosso_numero	
								);
							break;
							case '28':
								$debito_tarifas[] = array(
									'valor_titulo' => $valor_titulo,
									'vencimento' => $vencimento,
									'nosso_numero' => $nosso_numero	
								);
							break;
							default:
								$outras_ocorrencias[] = array(
									'valor_titulo' => $valor_titulo,
									'vencimento' => $vencimento,
									'nosso_numero' => $nosso_numero	
								);
							break;
						}
					}
					
					$record_data = array(
						'BANCO' => substr($segmentoT, 0, 3),
						'CONVENIO' => substr($conv, 0, 7),
						'SIGLA_CLIENTE' => strtoupper($sigla),
						'COD_MOVIMENTO' => $cod_movimentacao,
						'NOSSO_NUMERO' => $nosso_numero,
						'SEU_NUMERO' => trim(substr($segmentoT, 105, 25)),
						'MOEDA' => substr($segmentoT, 130, 2),
						'AGENCIA' => substr($segmentoT, 17, 5),
						'AGENCIA_DV' => substr($segmentoT, 22, 1),
						'CONTA_CORRENTE' => substr($segmentoT, 23, 12),
						'CONTA_CORRENTE_DV' => substr($segmentoT, 35, 1),
						'VALOR' => substr($segmentoT, 81, 15) / 100,
						'VALOR_TARIFA' => $was_paid ? substr($segmentoT, 198, 15) / 100 : 0,
						'VALOR_ENCARGOS' => $was_paid ? substr($segmentoU, 17, 15) / 100 : 0,
						'VALOR_DESCONTO_CONCEDIDO' => $was_paid ? substr($segmentoU, 32, 15) / 100 : 0,
						'VALOR_ABATIMENTO' => $was_paid ? substr($segmentoU, 47, 15) / 100 : 0,
						'VALOR_PAGO' => $was_paid ? substr($segmentoU, 77, 15) / 100 : 0,
						'VALOR_CREDITADO' => $was_paid ? substr($segmentoU, 92, 15) / 100 : 0,
						'DATA_VCTO' => $was_rejected ? Util::FmtDate(substr($segmentoU, 137, 8), '25') : Util::FmtDate(substr($segmentoT, 73, 8), '25'),
						'DATA_ARQUIVO' => $data,
						'MOTIVO_REJEICAO' => $was_rejected ? trim(substr($segmentoT, 213, 10)) : '',
						'VALOR_TITULO' => substr($segmentoT, 81, 15) / 100,
						'TIPO_REGISTRO' => 'RETORNO'
					);

					if ( $was_paid ) {
						$record_data['DATA_PGTO'] = Util::FmtDate(substr($segmentoU, 137, 8), '25');
						$record_data['DATA_CREDITO'] = Util::FmtDate(substr($segmentoU, 145, 8), '25');
						$record_data['BANCO_RECEBEDOR'] = substr($segmentoT, 96, 3);
						$record_data['AGENCIA_RECEBEDORA'] = substr($segmentoT, 99, 5);
						$record_data['AGENCIA_RECEBEDORA_DV'] = substr($segmentoT, 104, 1);
						if ( $cod_movimentacao === '17' ) {
							unset($record_data['DATA_VCTO']);
						}
					}

					$records_to_add[] = $record_data;
				}
			}
		}
	}
	/*
	* (2º ETAPA)
	* Lista todos os arquivos e inclui o trailer de lote e de arquivo nos mesmos
	*/
	$dir2 = dir($processed);
	while ( $file = $dir2->read() ) {
		$fileName = explode('_', $file);
		if ( pathinfo($file, PATHINFO_EXTENSION) == 'ret' && $fileName[3] == '001' ) {
			$fp = fopen($processed.$file, 'a');
			fwrite($fp, $trailer);
			fclose($fp);
		}
	}
	/*
	* (3º ETAPA)
	* Relatórios dos títulos registrados
	* Informa oS status dos registros no arquivo
	*/
	relatorioTituloRegistrado($crud, 'OCORRÊNCIAS NÃO TRATADAS PELO SISTEMA', 'alert', '#F00', $outras_ocorrencias); //ACEITOS
	relatorioTituloRegistrado($crud, 'TÍTULOS COM ENTRADA CONFIRMADA', 'ok-circle', '#0E7322', $entrada_confirmada); //ACEITOS
	relatorioTituloRegistrado($crud, 'TÍTULOS COM ENTRADA REJEITADA', 'ban-circle', '#F00', $entrada_rejeitada); //NEGADOS
	relatorioTituloRegistrado($crud, 'TÍTULOS BAIXADOS', 'arrow-down', '#09F', $titulos_baixados); //BAIXADOS
	relatorioTituloRegistrado($crud, 'DÉBITO DE TARIFAS', 'usd', '#FF9500', $debito_tarifas); //DÉBITO DE TARIFAS
	relatorioTituloRegistrado($crud, 'TÍTULOS COM A DATA DE VENCIMENTO ALTERADA', 'refresh', '#069', $vencimento_alterado); //CONFIRMAÇÃO DE ALTERAÇÃO DE VENCIMENTO
	relatorioTituloRegistrado($crud, 'TÍTULOS COM INSTRUÇÕES REJEITADAS', 'remove', '#F00', $intrucoes_rejeitadas); //INSTRUÇÕES REJEITADAS
	if (count($entrada_rejeitada) > 0) {
		GerarPDF('TÍTULOS COM ENTRADA REJEITADA', array('Cliente', 'Pagador', 'Nosso Número', 'Dt. de Venc.', 'Valor (R$)', 'Motivo'), $entrada_rejeitada, 'Titulos Rejeitados/'.fmtDatePattern($data, '8').utf8_decode('-Titulos Rejeitados'), date('Y-m-d', strtotime("+1 day", strtotime($data))));
	}
	if (count($titulos_baixados) > 0) {
		GerarPDF('TÍTULOS BAIXADOS', array('Cliente', 'Pagador', 'Nosso Número', 'Dt. de Venc.', 'Valor (R$)'), $titulos_baixados, 'Titulos Baixados/'.fmtDatePattern($data, '8').utf8_decode('-Titulos Baixados'), date('Y-m-d', strtotime("+1 day", strtotime($data))));
	}
	/*
	* (4º ETAPA)
	* Montagem dos relatórios de pagamento para impressão
	* Lista todos os arquivos na pasta onde os retornos processados caem
	*/
	$dir3 = dir($processed);
	$vlrTotalRepasse = $vlrTotalRepasse17 = $vlrTotalRepasse1711 = $vlrTotalRepasse1705 = $qtdeTotalRepasse = $qtdeTotalRepasse17 = $qtdeTotalRepasse1711 = $qtdeTotalRepasse1705 = 0;
	$valorTotalTarifas = $valorTotalTarifas17 = $valorTotalTarifas1711 = $valorTotalTarifas1705 = 0;
	$valorTotalTarifasUNI = $qtdeTotalRepasseUNI = $valorTotalRepasseUNI = 0;
	//Creates the accordion
	echo '<label class="hdn"><input type="checkbox" checked="checked" name="toggle-selected-customers" /> Marcar/Desmarcar todos os clientes</label>';
	echo '<p class="hdn report-toggle"></p>';
	echo '<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">';
	//Lê todos os arquivos dentro da pasta PROCESSADOS
	while ( $files = $dir3->read() ) {	
		$fileName = explode('_', $files);
		//Verify the files' extension
		if ( pathinfo($files, PATHINFO_EXTENSION) == 'ret' && $fileName[3] == '001' ) {
			//Get date
			$convenio = substr(file("$processed\\$files")[0], 34, 7);
			$dateFile = substr(file("$processed\\$files")[0], 143, 8);
			$accordionDate = Util::FmtDate($dateFile, '3');
			$dateFile = Util::FmtDate($dateFile, '17');
			//Turns the arrays cleaned
			$nosso_nums = array();
			$valores = array();
			//Add the info to the arrays
			for ( $n = 2, $lenN = (count(file($processed.$files)) - 2); $n < $lenN; $n++ ) {
		    	$num = substr(file($processed.$files)[$n], 37, 17);
		    	$value = substr(file($processed.$files)[$n+1], 79, 13);
		    	if ( in_array(substr($num, 0, 7), $setydeiasConvs) || in_array(substr($num, 0, 7), $just_convenios_proprios) ) {
			    	$nosso_nums[] = $num;
			    	$valores[] = $value;
				}
		    }
		    //Aux var for counting values
		    $valorTotal = 0;
		    for ( $h = 0, $lenH = count($valores); $h < $lenH; $h++ ) {
		    	$valorTotal += $valores[$h];
			}
			//If the file has more than zero info
			if ( count($valores) > 0 ) {
				echo (in_array($fileName[4], $setydeiasConvs)) ? '<div class="panel panel-default">' : '<div class="panel panel-default hdn">';
				$fileName = explode('_', $files);
				//Verificando se o cliente possui tarifa personalizada
				if ( in_array($fileName[2], $CustomCustomers['CLI_SIGLA']) ) {
					$tarifaBB = $CustomCustomers['BB_18'][array_search($fileName[2], $CustomCustomers['CLI_SIGLA'])];
					$tarifaBB17 = $CustomCustomers['BB_1704'][array_search($fileName[2], $CustomCustomers['CLI_SIGLA'])];
					$tarifaBB1705 = $CustomCustomers['BB_1705'][array_search($fileName[2], $CustomCustomers['CLI_SIGLA'])];
					$tarifaBB1711 = $CustomCustomers['BB_1711'][array_search($fileName[2], $CustomCustomers['CLI_SIGLA'])];
					$personalizado = "<span class='glyphicon glyphicon-exclamation-sign required-alert'></span>";
				} else {
					$tarifaBB = $customerTars['BB18'][0]; //Carteira 18
					$tarifaBB17 = $customerTars['BB17_04'][0]; //Carteira 17-04
					$tarifaBB1705 = $customerTars['BB17_05'][0]; //Carteira 17-05
					$tarifaBB1711 = $customerTars['BB17_11'][0]; //Carteira 17-11
					$personalizado = "";
				}

				switch ($fileName[4]) {
				    case '1406548':
						echo '<div class="panel-heading customer_collapse" role="tab" id="heading'.$fileName[2].$accordionDate.'18">';
						echo '<input type="checkbox" name="customer_collapse_input" class="hdn" checked="checked" value="'.$fileName[2].'-'.$accordionDate.'-'.$convenio.'" />';
						echo '<h4 class="panel-title">';
				    	echo '<a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$fileName[2].$accordionDate.'18" aria-expanded="true" aria-controls="collapse'.$fileName[2].$accordionDate.'18">';
				    	echo '<span class="glyphicon glyphicon-folder-open"></span> &nbsp;'.$fileName[2].' - <b>Cr. 18</b><span>CT <img src="/painel/build/images/icons/checkbox.svg" width="20" /> | FTP <img src="/painel/build/images/icons/checkbox.svg" width="20" /> | Qtde: <b>' . count($valores) . '</b> | Tarifas (R$): <b>'.number_format((count($valores) * $tarifaBB), 2, ',', '.').'</b> '.$personalizado.'| Valores (R$): <b>'.fmtValue($valorTotal).'</b> | <span class="glyphicon glyphicon-calendar"></span> <b>'.$dateFile.'</b></span>';
					    echo '</a>';
					    echo '</h4>';
					    echo '</div>';
					    echo '<div id="collapse'.$fileName[2].$accordionDate.'18" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">';
					    $valorTotalTarifas += count($valores) * $tarifaBB;
					    break;
				   	case '2308855':
						echo '<div class="panel-heading customer_collapse" role="tab" id="heading'.$fileName[2].$accordionDate.'17">';
						echo '<input type="checkbox" name="customer_collapse_input" class="hdn" checked="checked" value="'.$fileName[2].'-'.$accordionDate.'-'.$convenio.'" />';
						echo '<h4 class="panel-title">';
				   		echo '<a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$fileName[2].$accordionDate.'17" aria-expanded="true" aria-controls="collapse'.$fileName[2].$accordionDate.'17">';
				    	echo '<span class="glyphicon glyphicon-folder-open"></span> &nbsp;'.$fileName[2].' - <b>Cr. 17/04</b><span>CT <img src="/painel/build/images/icons/checkbox.svg" width="20" /> | FTP <img src="/painel/build/images/icons/checkbox.svg" width="20" /> | Qtde: <b>' . count($valores) . '</b> | Tarifas (R$): <b>'.number_format((count($valores) * $tarifaBB17), 2, ',', '.').'</b> '.$personalizado.'| Valores (R$): <b>'.fmtValue($valorTotal).'</b> | <span class="glyphicon glyphicon-calendar"></span> <b>'.$dateFile.'</b></span>';
				    	echo '</a>';
					    echo '</h4>';	
					    echo '</div>';
				    	echo '<div id="collapse'.$fileName[2].$accordionDate.'17" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">';
				    	$valorTotalTarifas17 += count($valores) * $tarifaBB17;
				    	break;
				    case '2814485':
						echo '<div class="panel-heading customer_collapse" role="tab" id="heading'.$fileName[2].$accordionDate.'1711">';
						echo '<input type="checkbox" name="customer_collapse_input" class="hdn" checked="checked" value="'.$fileName[2].'-'.$accordionDate.'-'.$convenio.'" value="'.$fileName[2].$accordionDate.'" />';
						echo '<h4 class="panel-title">';
				   		echo '<a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$fileName[2].$accordionDate.'1711" aria-expanded="true" aria-controls="collapse'.$fileName[2].$accordionDate.'1711">';
				    	echo '<span class="glyphicon glyphicon-folder-open"></span> &nbsp;'.$fileName[2].' - <b>Cr. 17/11</b><span>CT <img src="/painel/build/images/icons/checkbox.svg" width="20" /> | FTP <img src="/painel/build/images/icons/checkbox.svg" width="20" /> | Qtde: <b>' . count($valores) . '</b> | Tarifas (R$): <b>'.number_format((count($valores) * $tarifaBB1711), 2, ',', '.').'</b> '.$personalizado.'| Valores (R$): <b>'.fmtValue($valorTotal).'</b> | <span class="glyphicon glyphicon-calendar"></span> <b>'.$dateFile.'</b></span>';
				    	echo '</a>';
					    echo '</h4>';	
					    echo '</div>';
				    	echo '<div id="collapse'.$fileName[2].$accordionDate.'1711" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">';
				    	$valorTotalTarifas1711 += count($valores) * $tarifaBB1711;
						break;
					case '3061856':
						echo '<div class="panel-heading customer_collapse" role="tab" id="heading'.$fileName[2].$accordionDate.'1705">';
						echo '<input type="checkbox" name="customer_collapse_input" class="hdn" checked="checked" value="'.$fileName[2].'-'.$accordionDate.'-'.$convenio.'" />';
						echo '<h4 class="panel-title">';
				   		echo '<a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$fileName[2].$accordionDate.'1705" aria-expanded="true" aria-controls="collapse'.$fileName[2].$accordionDate.'1705">';
				    	echo '<span class="glyphicon glyphicon-folder-open"></span> &nbsp;'.$fileName[2].' - <b>Cr. 17/05</b><span>CT <img src="/painel/build/images/icons/checkbox.svg" width="20" /> | FTP <img src="/painel/build/images/icons/checkbox.svg" width="20" /> | Qtde: <b>' . count($valores) . '</b> | Tarifas (R$): <b>'.number_format((count($valores) * $tarifaBB1705), 2, ',', '.').'</b> '.$personalizado.'| Valores (R$): <b>'.fmtValue($valorTotal).'</b> | <span class="glyphicon glyphicon-calendar"></span> <b>'.$dateFile.'</b></span>';
				    	echo '</a>';
					    echo '</h4>';	
					    echo '</div>';
				    	echo '<div id="collapse'.$fileName[2].$accordionDate.'1705" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">';
				    	$valorTotalTarifas1705 += count($valores) * $tarifaBB1705;
				    	break;
				    default:
							echo '<div class="panel-heading customer_collapse" role="tab" id="heading'.$fileName[2].$fileName[1].'">';
							echo '<input type="checkbox" name="customer_collapse_input" checked="checked" value="'.$fileName[2].'-'.$accordionDate.'-'.$convenio.'" />';
							echo '<h4 class="panel-title">';
								echo '<a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$fileName[2].$fileName[1].'" aria-expanded="true" aria-controls="collapse'.$fileName[2].$fileName[1].'">';
								echo '<span class="glyphicon glyphicon-folder-open"></span> &nbsp;'.$fileName[2].'<span>CT <img src="/painel/build/images/icons/checkbox.svg" width="20" /> | FTP <img src="/painel/build/images/icons/checkbox.svg" width="20" /> | Qtde: <b>' . count($valores) . '</b> | Tarifas (R$): <b>'.number_format((count($valores) * $tarifaBB), 2, ',', '.').'</b> '.$personalizado.'| Valores (R$): <b>'.fmtValue($valorTotal).'</b> | <span class="glyphicon glyphicon-calendar"></span> <b>'.$dateFile.'</b></span>';
								echo '</a>';
								echo '</h4>';	
								echo '</div>';
								echo '<div id="collapse'.$fileName[2].$fileName[1].'" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">';
								break;
				}
				//Tabela interna (dentro do panel)
			    echo '<div class="panel-body">';
			    echo '<table class="table table-striped table-condensed>"';
			    echo '<thead>';
			    echo '<tr><td><b>Nosso Número</b></td><td><b>Data do Pagamento</b></td><td><b>Valor do Pagamento</b></td></tr>';
			    echo '</thead>';
			    echo '<tbody>';
			    for ( $n = 0, $lenN = count($valores); $n < $lenN; $n++ ) {
				    echo '<tr>';
				    echo '<td>'.$nosso_nums[$n].'</td>';
				    echo "<td>$dateFile</td>";
				    echo '<td>'.fmtValue($valores[$n]).'</td>';
				    echo '</tr>';
				}
				echo '</tbody>';
			    echo '</table>';
			    echo '</div>';
			    echo '</div>';
			    echo '</div>';
			    //Se estiver nos convênios da Setydeias, escrever nas Contas Transitórias
			    if ( in_array($fileName[4], $setydeiasConvs) ) {
			    	//Verify if CT Subscribe was allowed
			    	if ( $writeInCT == '01' ) {
				    	//Write on Conta Transitorio of the client
				    	$accTrs = "\\contatransitoria\\".strtolower($fileName[2])."\\index-".$fileName[2].".xls";
				    	if ( file_exists($accTrs) ) {
				    		$sheet = PHPExcel_IOFactory::identify($accTrs);
				    		$objReader = PHPExcel_IOFactory::createReader($sheet);
					    	$objPHPExcel = $objReader->load($accTrs);
							//Loop for find info
							foreach ( $objPHPExcel->getWorksheetIterator() as $worksheet ) {
								$highestRow = $worksheet->getHighestRow();
								//Loop in all cells of the sheet
								for ( $cc = 11; $cc < $highestRow; $cc++ ) {
									if ( $worksheet->getCellByColumnAndRow(1, $cc)->getValue() == "" ) {
								 		$objWorksheet = $objPHPExcel->getActiveSheet();
								 		// Function should returns the formated date for excel
								 		$getDt = getFmtedDate($dataT, $dataE);
								 		// Declarating vars of functions @getFmtedDate
										$t_date = $getDt[0];
										$p_date = $getDt[1];
										// Function should get cell info
										//Verificando se o cliente possui tarifa personalizada
										if ( in_array($fileName[2], $CustomCustomers['CLI_SIGLA']) ) {
											$tarifaBB = $CustomCustomers['BB_18'][array_search($fileName[2], $CustomCustomers['CLI_SIGLA'])];
											$tarifaBB17 = $CustomCustomers['BB_1704'][array_search($fileName[2], $CustomCustomers['CLI_SIGLA'])];
											$tarifaBB1705 = $CustomCustomers['BB_1705'][array_search($fileName[2], $CustomCustomers['CLI_SIGLA'])];
											$tarifaBB1711 = $CustomCustomers['BB_1711'][array_search($fileName[2], $CustomCustomers['CLI_SIGLA'])];
										} else {
											$tarifaBB = $customerTars['BB18'][0]; //Carteira 18
											$tarifaBB17 = $customerTars['BB17_04'][0]; //Carteira 17-04
											$tarifaBB1705 = $customerTars['BB17_05'][0]; //Carteira 17-05
											$tarifaBB1711 = $customerTars['BB17_11'][0]; //Carteira 17-11
										}

										switch ($fileName[4]) {
											case '1406548':
												$tarifaBBCT = $tarifaBB;
												break;
											case '2308855':
												$tarifaBBCT = $tarifaBB17;
												break;
											case '2814485':
												$tarifaBBCT = $tarifaBB1711;
												break;
											case '3061856':
												$tarifaBBCT = $tarifaBB1705;
												break;
										}
										$valorTotalTarifasUNI += $tarifaBBCT * count($valores);
										getCellData($objWorksheet, $cc, $t_date, $p_date, $valorTotal, $valores, $tarifaBBCT, 'processamento', null, null, null, null, 'bb', null, $clientsWithPayments, $fileName[2]);
								 		//Creating the writer object
								 		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $sheet);
								 		$objWriter->setPreCalculateFormulas(false);
										$objWriter->save($accTrs);
								 		break;
									}
								}
							}
						} else {
							createAlert('danger', 'Conta Transitória não encontrada<b>!</b>');
						}
						//Guarda o cliente no array @clientsWithPayments para no final de todo o processamento
			    		//percorrer o array e inserir a string de transferência na conta transitória
			    		if ( !in_array($fileName[2], $clientsWithPayments) ) {
			    			$clientsWithPayments[] = $fileName[2];
						}
					} else {
						switch ($fileName[4]) {
							case '1406548':
								$tarifaBBCT = $tarifaBB;
								break;
							case '2308855':
								$tarifaBBCT = $tarifaBB17;
								break;
							case '2814485':
								$tarifaBBCT = $tarifaBB1711;
								break;
							case '3061856':
								$tarifaBBCT = $tarifaBB1705;
								break;
						}
						$valorTotalTarifasUNI += $tarifaBBCT * count($valores);
					}
					
					switch ($fileName[4]) {
						case '1406548':
							//Join the values for show at under table
					    	$vlrTotalRepasse += $valorTotal;
					    	$qtdeTotalRepasse += count($valores);
							break;
						case '2308855':
							//Join the values for show at under table
					    	$vlrTotalRepasse17 += $valorTotal;
					    	$qtdeTotalRepasse17 += count($valores);
							break;
						case '2814485':
							//Join the values for show at under table
					    	$vlrTotalRepasse1711 += $valorTotal;
					    	$qtdeTotalRepasse1711 += count($valores);
							break;
						case '3061856':
							//Join the values for show at under table
					    	$vlrTotalRepasse1705 += $valorTotal;
					    	$qtdeTotalRepasse1705 += count($valores);
							break;
					}
			    }
			    $qtdeTotalRepasseUNI = $qtdeTotalRepasse + $qtdeTotalRepasse17 + $qtdeTotalRepasse1711 + $qtdeTotalRepasse1705;
			    $valorTotalRepasseUNI = $vlrTotalRepasse + $vlrTotalRepasse17 + $vlrTotalRepasse1711 + $vlrTotalRepasse1705;
			}
		}
	}
	/*
	* Caso exista algum título com repasse atrasado
	* É mostrado na tela
	*/
	titulosAtrasados($repasseAtrasado);
	/*
	*
	*/
	pagamentosLQR($LQRpayments);
	if ( count($LQRpayments) > 0 ) {
		GerarPDF('TÍTULOS COM LQR (LIQUIDAÇÃO SEM REGISTRO)', array('Cliente', 'Pagador', 'Nosso Número', 'Valor (R$)'), $LQRpayments, 'Titulos LQR/'.utf8_decode('Titulos LQR-').fmtDatePattern($data, '8'), date('Y-m-d', strtotime("+1 day", strtotime($data))));
	}
	/*
	* (5º ETAPA)
	* Resumo dos pagamentos
	* Quantidade total de pagamentos, valor total arrecadado e custo de tarifas
	*/
	$tableData = array(
			'banco' => 'bb',
			'qtdeTitulos' => $qtdeTotalRepasseUNI,
			'valorTitulos' => $valorTotalRepasseUNI,
			'tarifaTitulos' => $valorTotalTarifasUNI,
			'carteiras' => array(
				'cr1711' => array('tipoCarteira' => 'cr1711', 'qtde' => $qtdeTotalRepasse1711, 'valor' => $vlrTotalRepasse1711, 'tarifas' => $valorTotalTarifas1711),
				'cr1705' => array('tipoCarteira' => 'cr1705', 'qtde' => $qtdeTotalRepasse1705, 'valor' => $vlrTotalRepasse1705, 'tarifas' => $valorTotalTarifas1705),
				'cr17' => array('tipoCarteira' => 'cr17', 'qtde' => $qtdeTotalRepasse17, 'valor' => $vlrTotalRepasse17, 'tarifas' => $valorTotalTarifas1711),
				'cr18' => array('tipoCarteira' => 'cr18', 'qtde' => $qtdeTotalRepasse, 'valor' => $vlrTotalRepasse, 'tarifas' => $valorTotalTarifas)
			)
		);
	getTableData($tableData); //Resumo de pagamentos
	/*
	* (6º ETAPA)
	* Mostrar as duplicidades, caso existam
	*/
	ShowDuplicidades($customer, $duplicidades, $setydeiasConvs, 'bb');
	/*
	* (7º ETAPA)
	* Remove as duplicidades dos arquivos que as possuem
	*/
	$FileHandler = new FilesHandler();
	$FileHandler->RemoveDuplicatedRecordsCNAB240($arquivos_com_duplicidade, $duplicidades, 'bb');
	/*
	* (8º ETAPA)
	* Enviar os relatórios de duplicidades para a hospedagem
	*/
	$FileHandler->SendToHost($duplicidades, $dataBase, 'bb');
?>