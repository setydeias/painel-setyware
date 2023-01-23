<?php
	//Tarifas
	$tarAUTOAT = $customerTars['CEF_AUTOAT'][0];
	$tarAGH = $customerTars['CEF_AGENCIA'][0];
	$tarLOT = $customerTars['CEF_LOTERIAS'][0];
	$tarCOMP = $customerTars['CEF_COMPENSACAO'][0];
	$tarCT = $customerTars['CEF_CT'][0];
	//Arrays auxiliares
	$pgtoCHQ = $ourNumberOnCHQ = $duplicidades = $arquivos_com_duplicidade = array();
	$conveniosCEFP = array('0264151', '0689494');
	//Path to processed files
	$pathAUX = $processed;
	$dirAUX = dir($pathAUX);
	echo '<h4>PARANGABA</h4><hr/>';
	/*
	* (1º ETAPA)
	* Lista os arquivos que contém pagamentos
	* Processa linha a linha e destina a informação do pagamento
	* ao devido cliente de acordo com o NOSSO NÚMERO do REGISTRO DETALHE
	*/
	for ( $i = 0, $lenI = count($cefp); $i < $lenI; $i++ ) :
		if (filesize($cefp[$i]) > 1000) :
			/*
			* 1.1) CAPTURAÇÃO DAS INFORMAÇÕES GERAIS DO ARQUIVO (HEADER, TRAILER)
			*/
			//Capturing the file's header
			$header  = file($cefp[$i])[0].file($cefp[$i])[1];
			//Capturing the file's trailer
			$trailer = file($cefp[$i])[count(file($cefp[$i]))-2].file($cefp[$i])[count(file($cefp[$i]))-1];
			//Capturing the file's date
			$data = fmtDatePattern(substr(file($cefp[$i])[0], 143, 8), '1');
			$dataBase = fmtDatePattern(substr(file($cefp[$i])[0], 143, 8), '1');
			//Capturing the string where there's the file
			$file = explode('/', $cefp[$i]);
			//Sequencial
			$sequence = substr(file($cefp[$i])[0], 157, 6);
			//Convênio
			$convenio = substr(file($cefp[$i])[0], 58, 6);
			//Verificação de duplicidades
		 	if ($checarDuplicidade == '1') :
	 			$dataReferencia = date('Y-m-d', strtotime("-$diasDuplicidades days", strtotime($data))); //Data de referência para a comparação nas duplicidades
	 			$duplicidades[] = verificarDuplicidade($conn, $convenio, $data, $dataBase, $dataReferencia, $orig, 'cef');
	 		endif;
			//Aux vars
			$sigla = null;
			//Arrays auxiliares
			$entrada_confirmada = array();
			$entrada_rejeitada = array();
			$titulos_baixados = array();
			$vencimento_alterado = array();
			$intrucoes_rejeitadas = array();
			$outras_ocorrencias = array();
			/*
			* 1.2) VARREDURA DOS REGISTROS DETALHE, LINHA A LINHA
			* VERIFICA QUAL É O CLIENTE QUE POSSUI AQUELE PAGAMENTO
			* E VINCULA A INFORMAÇÃO DO PGTO AO SEU ARQUIVO DE RETORNO
			* 
			*/
			for ( $k = 2, $lenK = (count(file($cefp[$i])) - 2); $k < $lenK; $k++ ) :
				//Capturing the segment
				$segment = substr(file($cefp[$i])[$k], 13, 1);
				$cod_movimentacao = substr(file($cefp[$i])[$k], 15, 2);
				switch ($cod_movimentacao) :
					case '06':
						if ($segment == 'T') :
							//Data do crédito
							$dtcred = fmtDatePattern(substr(file($cefp[$i])[$k+1], 157, 8), '9');
							//Value for CHEQUE reference
							//Because the value must be inserted on CT
							$valueCHQ = substr((file($cefp[$i])[$k+1]), 79, 13);
							//Capturing our number
							$our_number = trim(substr(file($cefp[$i])[$k], 37, 19));
							//Número do registro
							$numero_registro = substr(file($cefp[$i])[$k], 8, 5);
							//Capturing the client
							$customerSigla = $customer->GetSiglaByCodSac(substr($our_number, 8, 3));
							$cli = strlen($customerSigla) <= 0 ? 'INDEFINIDO' : $customerSigla;
							//Try new client
							$cli != $sigla ? $sigla = $cli : '';
						endif;
						$convenio = str_pad($convenio, 7, '0', STR_PAD_LEFT);
						$arquivo = 'IEDCBR_'.fmtDatePattern($data, '8').'_'.$sigla.'_104_'.$convenio.'_'.$sequence.'.ret';
						//Open the file
						$fp = fopen($processed.$arquivo, 'a');
						if ( count(file($processed.$arquivo)) == 0 ) fwrite($fp, $header);
						//Verificando se existe pagamento em cheque
						if ($writeInCT == '01') :
							//Se a data de crédito for maior que a data de transferência
							//É pagamento em cheque
							if (fmtDatePattern($dataT, '13') >= $dtcred) :
								if ($checarDuplicidade == '1') :
									//Checa se existe duplicidade para jogar as mesmas no array
									if (count($duplicidades) > 0) :
										//Individualiza a duplicidade
										foreach ($duplicidades as $dp) :
											foreach ($dp as $duplicidade) :
												//Se o nosso número, sequencial e número do registro atual for igual ao da duplicidade de referencia (mesmo dia)
												//Ou se nosso número é igual o da referência mas o sequencial do arquivo não (dia anterior)
												//Então não escreve a linha, pois é duplicidade
												if (($our_number == $duplicidade['nosso_numero'] && $sequence == $duplicidade['seq_arquivo'] && $numero_registro == $duplicidade['num_registro'])
													|| ($our_number == $duplicidade['nosso_numero'] && $sequence != $duplicidade['seq_arquivo']) ) :
													if (!in_array($processed.$arquivo, $arquivos_com_duplicidade)) :
														$arquivos_com_duplicidade[] = $processed.$arquivo;
													endif;
													if (!in_array($sigla, $duplicidadect)) :
														$duplicidadect[] = $sigla;
													endif;
													break;
												endif;
											endforeach;
										endforeach;
										//Escreve a linha
										fwrite($fp, file($cefp[$i])[$k]);
									else :
										fwrite($fp, file($cefp[$i])[$k]);
									endif;
								else :
									fwrite($fp, file($cefp[$i])[$k]);
								endif;
							else :
								$arquivoCHQ = $pathCheque.fmtDatePattern($data, '8').'_104_'.$convenio.'_'.$sigla.'_'.$sequence.'.ret';
								$fOpen = fopen($arquivoCHQ, 'a');
								$wrt = fwrite($fOpen, file($cefp[$i])[$k]);
								//Guardando sigla e dados do cliente que teve pagamento em cheque
								$pgtoCHQ[] = $sigla;
							endif;
						else :
							if ($checarDuplicidade == '1') :
								//Checa se existe duplicidade
								//Se houver, não escreve
								if (count($duplicidades) > 0) :
									//Individualiza a duplicidade
									foreach ($duplicidades as $dp) :
										foreach ($dp as $duplicidade) :
											//Se o nosso número, sequencial e número do registro atual for igual ao da duplicidade de referencia (mesmo dia)
											//Ou se nosso número é igual o da referência mas o sequencial do arquivo não (dia anterior)
											//Então não escreve a linha, pois é duplicidade
											if (($our_number == $duplicidade['nosso_numero'] && $sequence == $duplicidade['seq_arquivo'] && $numero_registro == $duplicidade['num_registro'])
												|| ($our_number == $duplicidade['nosso_numero'] && $sequence != $duplicidade['seq_arquivo']) ) :
												if (!in_array($processed.$arquivo, $arquivos_com_duplicidade)) :
													$arquivos_com_duplicidade[] = $processed.$arquivo;
												endif;
												$duplicidadect[] = $cli;
												break;
											endif;
										endforeach;
									endforeach;
									//Escreve a linha
									fwrite($fp, file($cefp[$i])[$k]);
								else :
									fwrite($fp, file($cefp[$i])[$k]);
								endif;
							else :
								fwrite($fp, file($cefp[$i])[$k]);
							endif;
						endif;
						fclose($fp);
					break;
					default:
						if ($segment == 'T') :
							$vencimento = substr(file($cefp[$i])[$k], 73, 8);
							$valor_titulo = number_format(substr(file($cefp[$i])[$k], 81, 15)/100, 2, ',', '.');
							$nome_cliente = substr(file($cefp[$i])[$k], 148, 40);
							switch ($cod_movimentacao) :
								case '02':
									$entrada_confirmada[] = array(
										'valor_titulo' => $valor_titulo,
										'vencimento' => $vencimento, 
										'nosso_numero' => $our_number
									);
								break;
								case '03':
									$entrada_rejeitada[] = array(
										'valor_titulo' => $valor_titulo,
										'vencimento' => $vencimento,
										'nosso_numero' => $our_number
									);
								break;
								case '09':
									$titulos_baixados[] = array(
										'valor_titulo' => $valor_titulo,
										'vencimento' => $vencimento,
										'nosso_numero' => $our_number
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
								default:
									$outras_ocorrencias[] = array(
										'valor_titulo' => $valor_titulo,
										'vencimento' => $vencimento,
										'nosso_numero' => $nosso_numero	
									);
								break;
							endswitch;
						endif;
					break;
				endswitch;
			endfor;
		endif;
	endfor;
	/*
	* (2º ETAPA)
	* Lista todos os arquivos e inclui o trailer de lote e de arquivo nos mesmos
	*/
	$path2 = $processed;
	$dir2 = dir($path2);
	while ( $file = $dir2->read() ) :
		$fileName = explode('_', $file);
		if ( pathinfo($file, PATHINFO_EXTENSION) == 'ret' && $fileName[3] == '104' && in_array($fileName[4], $conveniosCEFP) ) :
			$fp = fopen($processed.$file, 'a');
			$write = fwrite($fp, $trailer);
			fclose($fp);
		endif;
	endwhile;
	/*
	* (3º ETAPA)
	* Montagem dos relatórios de pagamento para impressão
	* Lista todos os arquivos na pasta onde os retornos processados caem
	*/
	$dir3 = dir($path2);
	//Collapse aux
	$vlrTotalRepasse = 0;
	$qtdeTotalRepasse = 0;
	//Creates the accordion
	echo '<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">';
	//Reading the files on the @path2 var
	while ($file = $dir3->read()) :
		if (!is_dir($path2.$file)) :
			$fileName = explode('_', $file);
			//Turns the arrays cleaned
			$nosso_nums = array();
			$valores    = array();
			//Verify the files' extension
			if ( pathinfo($file, PATHINFO_EXTENSION) == 'ret' && $fileName[3] == '104' && in_array($fileName[4], $conveniosCEFP) ) :
				//Tax arrays
				$taxas = array();
				$valoresArquivo = array();
				//Add the info to the arrays
				for ( $n = 2, $lenN = (count(file($processed.$file)) -2); $n < $lenN; $n++ ) :
					$segment = substr(file($processed.$file)[$n], 13, 1);
					$tax = substr(file($processed.$file)[$n], 210, 3);
					$value = substr(file($processed.$file)[$n+1], 79, 13);
					if ( $segment == 'T' ) :
			    		$num = trim(substr(file($processed.$file)[$n], 37, 19));
						if ( in_array($tax, $cefTaxs) ) :
							$taxas[] = $tax;
							$valoresArquivo[] = $value;
						else:
							echo 'A tarifa do título com nosso número <b>'.$num.'</b> está diferente: R$ '.($tax/100).'<br/>';
						endif;
						$nosso_nums[] = $num;
				    	$valores[] = $value;
			    	endif;
			    endfor;
			    //Aux var for counting values
			    $valorTotal = 0;
			    for ( $h = 0, $lenH = count($valores); $h < $lenH; $h++) :
			    	$valorTotal += $valores[$h];
			    endfor;
			    //If the file has more than zero info
			    if ( count($valores) > 0 ) :
			    	$fileName = explode('_', $file);
			    	$siglaCli = $fileName[2];
			    	$occurrence = (in_array($fileName[2], $pgtoCHQ)) ? ' <font color="red">(TÍTULO PAGO EM CHEQUE)</font>' : '';
					echo '<div class="panel panel-default">';
					echo '<div class="panel-heading" role="tab" id="headingOne" style="background:#fff;">';
				    echo '<h4 class="panel-title">';
				    echo '<a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$siglaCli.$data.'" aria-expanded="true" aria-controls="collapse'.$siglaCli.$data.'">';
				    echo '<span class="glyphicon glyphicon-folder-open"></span> &nbsp;'.$siglaCli.$occurrence.'<span class="pull-right">CT <img src="/painel/build/images/icons/checkbox.svg" width="20" /> | FTP <img src="/painel/build/images/icons/checkbox.svg" width="20" /> | Qtde: <b>' . count($valores) . '</b> | Tarifas (R$): <b>'.number_format((count($valores) * $tarCT), 2, ',', '.').'</b> | Arrecadação (R$): <b>'.fmtValue($valorTotal).'</b> | <span class="glyphicon glyphicon-calendar"></span> <b>'.fmtDatePattern($data, '15').'</b></span>';
				    echo '</a>';
				    echo '</h4>';
				    echo '</div>';
				    echo '<div id="collapse'.$siglaCli.$data.'" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">';
				    echo '<div class="panel-body">';
				    echo '<table class="table table-striped table-condensed>"';
				    echo '<thead>';
				    echo '<tr><td><b>Nosso Número</b></td><td><b>Data do Pagamento</b></td><td><b>Valor do Pagamento</b></td></tr>';
				    echo '</thead>';
				    echo '<tbody>';
				    for ( $n = 0, $lenValues = count($valores); $n < $lenValues; $n++ ) :
				    	echo '<tr>';
				    	echo '<td>'.$nosso_nums[$n].'</td>';
				    	echo '<td>'.fmtDatePattern($data, '15').'</td>';
				    	echo '<td>'.fmtValue($valores[$n]).'</td>';
				    	echo '</tr>';
				    endfor;
				    echo '</tbody>';
				    echo '</table>';
				    echo '</div>';
				    echo '</div>';
				    echo '</div>';
				    if ( $writeInCT == '01' ) :
					    //Write on Conta Transitorio of the client
					    if ( count($taxas) > 0 ) :
					    	$accTrs = "\\contatransitoria\\".strtolower($fileName[2])."\\index-".$fileName[2].".xls";
					    	if ( file_exists($accTrs) ) :
								$sheet = PHPExcel_IOFactory::identify($accTrs);
								$objReader = PHPExcel_IOFactory::createReader($sheet);
								$objPHPExcel = $objReader->load($accTrs);
								//Loop for find info
								foreach ( $objPHPExcel->getWorksheetIterator() as $worksheet ) :
									$highestRow = $worksheet->getHighestRow();
									//Loop in all cells of the sheet
									for ( $cc = 11; $cc < $highestRow; $cc++ ) :
										if ( $worksheet->getCellByColumnAndRow(1, $cc)->getValue() == "" ) :
									 		$objWorksheet = $objPHPExcel->getActiveSheet();
									 		// Function should returns the formated date for excel
									 		$getDt = getFmtedDate($dataT, $dataE);
									 		// Declarating vars of functions @getFmtedDate
											$t_date = $getDt[0];
											$p_date = $getDt[1];
									    	// Function should get cell info
										    getCellData($objWorksheet, $cc, $t_date, $p_date, $valorTotal, $valores, $tarCT, 'processamento', null, null, null, null, 'cef', '1563', $clientsWithPayments, $fileName[2]);
											//Creating the writer object
									 		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $sheet);
									 		$objWriter->setPreCalculateFormulas(false);
											$objWriter->save($accTrs);
											break;
										endif;
									endfor;
								endforeach;
							else: 
								createAlert('danger', 'Arquivo não foi encontrado ou não existe<b>!</b>');
							endif;
						endif;
						//Store client
			    		if ( !in_array($fileName[2], $clientsWithPayments) ) :
			    			$clientsWithPayments[] = $fileName[2];
			    		endif;
					endif;
				endif;
				$qtdeTotalRepasse += count($valores);
				$vlrTotalRepasse += $valorTotal;
			endif;
		endif;
	endwhile;
	/*
	* (4º ETAPA)
	* Resumo dos pagamentos
	* Quantidade total de pagamentos, valor total arrecadado e custo de tarifas
	*/
	$tableData = array(
			'banco' => 'cef',
			'qtdeTitulos' => $qtdeTotalRepasse,
			'valorTitulos' => $vlrTotalRepasse,
			'tarifaTitulos' => $qtdeTotalRepasse * $tarCT
		);
	getTableData($tableData);
	/*
	* (5º ETAPA)
	* Mostrar as duplicidades, caso existam
	*/
	ShowDuplicidades($customer, $duplicidades, $setydeiasConvs, 'cef');
	/*
	* (6º ETAPA)
	* Remove as duplicidades dos arquivos que as possuem
	*/
	$FileHandler = new FilesHandler();
	$FileHandler->RemoveDuplicatedRecordsCNAB240($arquivos_com_duplicidade, $duplicidades, 'cefp');
	/*
	* (8º ETAPA)
	* Enviar os relatórios de duplicidades para a hospedagem
	*/
	$FileHandler->SendToHost($duplicidades, $dataBase, 'cefp');
?>