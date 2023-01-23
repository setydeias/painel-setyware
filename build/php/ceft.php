<?php
	$convenio = '9040450';
	$convenioSRN = '2400000';
	$taxs = selectTaxForBank($conn, 'cef');
	$paths = selectWaytoPath($conn);
	$pathOriginal = $paths[2];
	$pathCheques = $paths[7];
	//Tars
	$tarAUTOAT = $taxs[0];
	$tarAGH = $taxs[1];
	$tarLOT = $taxs[3];
	$tarCOMP = $taxs[2];
	$tarCT = $taxs[4];
	$duplicidades = array();
	$pgtoDuplicidade = array();
	$arquivos_com_duplicidade = array();
	//Aux vars
	$pgtoCHQ = array(); //Pagamento em cheque
	$dataRef = "";
	$occurrece = "";
	$transferAux = ""; //Serve para o arquivo ftp_transfer.php
	$totalTarifas = 0;
	$writeInCT = $_POST['ct']; //Ação: 1 -> Escrever nas Contas Transitórias | 2 -> Não escrever nas Contas Transitórias
	$checarDuplicidade = $_POST['duplicidades']; //Ação: 1 -> Checar duplicidades | 2 -> Não checar duplicidades
	if ($checarDuplicidade == '1') :
		$diasDuplicidades = $_POST['checarDuplicidade'];
	endif;
	//Dirs
	$pathAUX = $processed;
	$dirAUX  = dir($pathAUX);
	//Verify if exist files on the path and, if exists, clean them
	while ( $files  = $dirAUX->read() ) :
		$fileName = explode('_', $files);
		if ( pathinfo($files, PATHINFO_EXTENSION) == 'ret' && $fileName[3] == '104' && ($fileName[4] == '0040450' || $fileName[4] == '0283175') ) :
			fopen($processed.$files, 'w+');
		endif;
	endwhile;
	echo '<h4>TERRA DA LUZ</h4><hr/>';
	/*
	* (1º ETAPA)
	* Lista os arquivos que contém pagamentos
	* Processa linha a linha e destina a informação do pagamento
	* ao devido cliente de acordo com o NOSSO NÚMERO do REGISTRO DETALHE
	*/
	for ( $i = 0, $lenI = count($ceft); $i < $lenI; $i++ ) :
		if (filesize($ceft[$i]) > 1000) :
			/*
			* 1.1) CAPTURAÇÃO DAS INFORMAÇÕES GERAIS DO ARQUIVO (HEADER, TRAILER)
			*/
			//Array auxiliar
			$ourNumberOnCHQ = array();
			//Registro header do arquivo
			$header = file($ceft[$i])[0].file($ceft[$i])[1];
			//Registro trailer do arquivo
			$trailer = file($ceft[$i])[count(file($ceft[$i]))-2].file($ceft[$i])[count(file($ceft[$i]))-1];
			//Data
			$data = fmtDatePattern(substr(file($ceft[$i])[0], 143, 8), '1');
			$dataBase = fmtDatePattern(substr(file($ceft[$i])[0], 143, 8), '1');
			//Verificação de duplicidades
		 	if ($checarDuplicidade == '1') :
	 			$dataReferencia = date('Y-m-d', strtotime("-$diasDuplicidades days", strtotime($data))); //Data de referência para a comparação nas duplicidades
	 			$duplicidades[] = verificarDuplicidade($conn, substr($convenio, 1, 6), $data, $dataBase, $dataReferencia, $pathOriginal, 'ceft');
	 		endif;
			//Separando o nome completo do caminho relativo
			//Para obter o nome do arquivo
			$file = explode('\\', $ceft[$i]);
			//Nome do arquivo
			$fileName = explode('_', $file[5]);
			//Variável auxiliar
			$sigla = null;
			/*
			* 1.2) VARREDURA DOS REGISTROS DETALHE, LINHA A LINHA
			* VERIFICA QUAL É O CLIENTE QUE POSSUI AQUELE PAGAMENTO
			* E VINCULA A INFORMAÇÃO DO PGTO AO SEU ARQUIVO DE RETORNO
			*/
			for ( $k = 2, $lenK = (count(file($ceft[$i])) - 2); $k < $lenK; $k++ ) :
				//Seleciona o segmento
				$segment = substr(file($ceft[$i])[$k], 13, 1);
				if ( $segment == 'T' && substr(file($ceft[$i])[$k], 37, 8) == $convenio ) :
					//Data do crédito
					$dtcred = fmtDatePattern(substr(file($ceft[$i])[$k+1], 157, 8), '9');
					//Value for CHEQUE reference
					//Because the value must be inserted on CT
					$valueCHQ = substr((file($ceft[$i])[$k+1]), 79, 13);
					//Capturing our number
					$our_number = substr(file($ceft[$i])[$k], 38, 18);
					//Número do registro
					$numero_registro = substr(file($ceft[$i])[$k], 8, 5);
					//Capturing the client
					$cli = strlen(takeClient(substr($our_number, 7, 3))) <= 0 ? 'INDEFINIDO' : takeClient(substr($our_number, 7, 3));
					$fileName[4] = '0040450';
					//Try new client
					$cli != $sigla ? $sigla = $cli : '';
				elseif ( $segment == ' ' ) :
					$header = file($ceft[$i])[0].file($ceft[$i])[$k+1];
					$sigla = 'SRN';
					$fileName[4] = '0283175';
				elseif ( $segment == 'T' && substr(file($ceft[$i])[$k], 38, 8) == $convenioSRN ) :
					//Capturing the client
					$cli = 'SRN';
					$fileName[4] = '0283175';
					//Data do crédito
					$dtcred = fmtDatePattern(substr(file($ceft[$i])[$k+1], 157, 8), '9');
					//Try new client
					$cli != $sigla ? $sigla = $cli : '';
				endif;
				//Sequencial
				$sequence = substr(file($ceft[$i])[0], 157, 6);
				//Create the file that will be overwritten
				$arquivo = 'IEDCBR_'.fmtDatePattern($data, '8').'_'.$sigla.'_104_'.$fileName[4].'_'.$sequence.'.ret';
				//Open the file
				$fp = fopen($processed.$arquivo, 'a');
				if (count(file($processed.$arquivo)) == 0) fwrite($fp, $header);
				if ($fileName[4] == '0283175' && $segment == ' ' || $segment == '0') :
					fwrite($fp, '');
				else:
					//Tratamento se o pagamento for efetuado em cheque
					$arquivoCHQ = $pathCheques.fmtDatePattern($data, '6').'_104_040450_'.$sigla.'_'.$sequence.'.ret';
					if ($writeInCT == '01') :
						if (fmtDatePattern($dataT, '13') >= $dtcred) :
							if ($checarDuplicidade == '1') :
								//Checa se existe duplicidade
								//Se houver, não escreve
								if (count($duplicidades) > 0) :
									//Individualiza a duplicidade
									foreach ($duplicidades as $dp) :
										foreach ($dp as $duplicidade) :
											//Se o nosso número, sequencial e número do registro atual for igual ao da duplicidade de referencia (mesmo dia)
											//Ou se nosso número é igual o da referência mas o sequencial do arquivo não (dia anterior)
											if ((($our_number == $duplicidade['nosso_numero'] && $sequence == $duplicidade['seq_arquivo'] && $numero_registro == $duplicidade['num_registro'])
												|| ($our_number == $duplicidade['nosso_numero'] && $sequence != $duplicidade['seq_arquivo'])) && $sigla != 'SRN') :
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
									//Escre a linha no arquivo
									fwrite($fp, file($ceft[$i])[$k]);
								else :
									fwrite($fp, file($ceft[$i])[$k]);
								endif;
							else :
								fwrite($fp, file($ceft[$i])[$k]);
							endif;
						else :
							if ($sigla != 'SRN') :
								$fOpen = fopen($arquivoCHQ, 'a');
								fwrite($fOpen, file($ceft[$i])[$k]);
								if ($segment == 'T') :
									$pgtoCHQ[] = $sigla;
									$pgtoCHQ[] = trim(substr($our_number, 0, strlen($our_number) - 1)).'-'.$valueCHQ.'-'.$dtcred;
									$ourNumberOnCHQ[] = $our_number;
								endif;
							endif;
						endif;
					else :
						if ($checarDuplicidade == '1') :
							//Checa se existe duplicidade
							//Se houver, não escreve
							$escreve = array();
							if (count($duplicidades) > 0) :
								//Individualiza a duplicidade
								foreach ($duplicidades as $dp) :
									foreach ($dp as $duplicidade) :
										//Se o nosso número, sequencial e número do registro atual for igual ao da duplicidade de referencia (mesmo dia)
										//Ou se nosso número é igual o da referência mas o sequencial do arquivo não (dia anterior)
										//Então não escreve a linha, pois é duplicidade
										if ((($our_number == $duplicidade['nosso_numero'] && $sequence == $duplicidade['seq_arquivo'] && $numero_registro == $duplicidade['num_registro'])
											|| ($our_number == $duplicidade['nosso_numero'] && $sequence != $duplicidade['seq_arquivo'])) && $sigla != 'SRN') :
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
								//Escre a linha no arquivo
								fwrite($fp, file($ceft[$i])[$k]);
							else :
								fwrite($fp, file($ceft[$i])[$k]);
							endif;
						else :
							fwrite($fp, file($ceft[$i])[$k]);
						endif;
					endif;
				endif;
			endfor;
		endif;
	endfor;
	/*
	* (2º ETAPA)
	* Lista todos os arquivos e inclui o trailer de lote e de arquivo nos mesmos
	*/
	$dir2 = dir($processed);
	while ( $file = $dir2->read() ) :
		$fileName = explode('_', $file);
		if ( (pathinfo($file, PATHINFO_EXTENSION) == 'ret' || pathinfo($file, PATHINFO_EXTENSION) == 'RET') && $fileName[3] == '104' && ($fileName[4] == '0040450' || $fileName[4] == '0283175') ) :
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
	$dir3 = dir($processed);
	//Collapse aux
	$m = 200;
	$vlrTotalRepasse = 0;
	$qtdeTotalRepasse = 0;
	//Creates the accordion
	echo '<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">';
	//Reading the files on the @path2 var
	while ( $file = $dir3->read() ) :
		if ( !is_dir($file) && $file != 'Originais' ) :
			//Aux counting var
			$counts = 0;
			$totalValues = 0;
			//File name
			$fileName = explode('_', $file);																																						
			//Turns the arrays cleaned																																						
			$nosso_nums = array();																																						
			$valores = array();																																						
			//Verify the files' extension																																						
			if ( pathinfo($file, PATHINFO_EXTENSION) == 'ret' && $fileName[3] == '104' && ($fileName[4] == '0040450' || $fileName[4] == '0283175') ) :
				//Tax arrays																																						
				$loterias = array();																																						
				$loteriasVlr = array(); 																																						
				$autoat = array();																																						
				$autoatVlr = array();																																						
				$comp = array();																																						
				$compVlr = array();																																						
				$agn = array();																																						
				$agnVlr = array();																																						
				//Add the info to the arrays																																						
				for ( $n = 2, $lenN = (count(file($processed.$file))); $n < $lenN; $n++ ) :																																						
					$segment = substr(file($processed.$file)[$n], 13, 1);																																						
					$tax = substr((file($processed.$file)[$n]), 210, 3);																																						
					if ( $segment == 'T' && substr(file($processed.$file)[$n], 38, 7) == $convenio ) :
						$value = substr((file($processed.$file)[$n+1]), 79, 13);																																						
			    		$num = substr(file($processed.$file)[$n], 37, 20);
			    		if ( in_array($num, $ourNumberOnCHQ) ) :			
			    			$value = 0;
							$counts++;																																			
						else :
							if ( $tax == '228' ) :
								$comp[] = $tax;																																						
								$compVlr[] = $value;																																						
							elseif ( $tax == '149' ) :
								$autoat[] = $tax;																																						
								$autoatVlr[] = $value;																																						
							elseif ( $tax == '161' ) :
								$loterias[] = $tax;																																						
								$loteriasVlr[] = $value;																																						
							elseif ( $tax == '224' ) :
								$agn[] = $tax;																																						
								$agnVlr[] = $value;																																						
							endif;																																						
						endif;
			    	elseif ( $segment == 'T' && substr(file($processed.$file)[$n], 38, 8) == $convenioSRN ) :
			    		$num = substr(file($processed.$file)[$n], 38, 19);																																						
			    	endif;																																						
			    	if ( $segment == 'T' ) :
						$valores[] = $value;
					    $nosso_nums[] = $num;
					endif;																																						
			    endfor;																																						
			    //Aux var for counting values																																	
			    $valorTotal = 0;
			    for ( $h = 0, $lenH = count($valores); $h < $lenH; $h++ ) :
			    	$valorTotal += $valores[$h];
			    endfor;
			    //If the file has more than zero info
			    if ( count($valores) > 0 ) :
			    	$totalValues = (count($valores) - $counts);
					echo ($fileName[2] == 'SRN') ? '<div class="panel panel-default hdn">' : '<div class="panel panel-default">';
					echo '<div class="panel-heading" role="tab" id="headingOne">';
				    echo '<h4 class="panel-title">';
				    echo '<a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$m.$data.'" aria-expanded="true" aria-controls="collapse'.$m.$data.'">';
				    $fileName  = explode('_', $file);
				    $occurrence = (in_array($fileName[2], $pgtoCHQ)) ? ' <font color="red">(TÍTULO PAGO EM CHEQUE)</font>' : '';
				    echo '<span class="glyphicon glyphicon-folder-open"></span> &nbsp;' .$fileName[2] . $occurrence . '<span class="pull-right">CT <img src="assets/images/icons/checkbox.svg" width="20" /> | FTP <img src="assets/images/icons/checkbox.svg" width="20" /> | Qtde: <b>' . $totalValues . '</b> | Arrecadação (R$): <b>'.fmtValue($valorTotal).'</b> | <span class="glyphicon glyphicon-calendar"></span> <b>'.fmtDatePattern($data, '15').'</b></span>';
				    echo '</a>';
				    echo '</h4>';
				    echo '</div>';
				    echo '<div id="collapse'.$m.$data.'" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">';
				    echo '<div class="panel-body">';
				    echo '<table class="table table-striped table-condensed>"';
				    echo '<thead>';
				    echo '<tr><td><b>Nosso Número</b></td><td><b>Data do Pagamento</b></td><td><b>Valor do Pagamento</b></td></tr>';
				    echo '</thead>';
				    echo '<tbody>';
				    for ( $n = 0, $lenN = count($valores); $n < $lenN; $n++ ) :
					    	echo '<tr>';
					    	if ( in_array($nosso_nums[$n], $ourNumberOnCHQ) ) :
					    		echo '<td><span style="color:red;">'.$nosso_nums[$n].'</span></td>';
					    		echo '<td><span style="color:red;">'.fmtDatePattern($data, '15').'</span></td>';
					    		echo '<td><span style="color:red;">'.fmtValue($valores[$n]).'</span></td>';
					    	else :
					    		echo '<td>'.$nosso_nums[$n].'</td>';
						    	echo '<td>'.fmtDatePattern($data, '15').'</td>';
						    	echo '<td>'.fmtValue($valores[$n]).'</td>';
					    	endif;
					    	echo '</tr>';
				    endfor;
				    echo '</tbody>';
				    echo '</table>';
				    echo '</div>';
				    echo '</div>';
				    echo '</div>';
				    echo '<div>';
				    if ( $writeInCT == '01' ) :
				    	$transferAux = true.'='.$dataT;
			    		//Store client
			    		if ( !in_array($fileName[2], $clientsWithPayments) && $fileName[2] != 'SRN') :
			    			if ( $totalValues > 0 ) :
			    				$clientsWithPayments[] = $fileName[2];
			    			endif;
			    		endif;
					    //Write on Conta Transitoria of the client
				    	$accTrs = "..\\..\\..\\contatransitoria\\".strtolower($fileName[2])."\\index-".$fileName[2].".xls";
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
										//Aux var to add line
										$addCC = 0;
									    if ( count($autoat) > 0 || count($agn) > 0 || count($comp) > 0 || count($loterias) > 0 ) :
									    	//Aux for counting total value of tars
									    	$sumTarsValue = 0;
									    	//Aux for counting total payments
									    	$sumPayments = 0;
									    	//Aux fot count total value of payments
									    	$sumPaymentsValues = 0;
									    	echo '<div style="padding:10px;">';
									    	echo '<table class="table table-condensed">';
									    	echo '<thead>';
									    	echo '<tr><td><b>Categoria</b></td><td><b>Qtde - Valor</b></td><td><b>Tarifas</b></td></tr>';
									    	echo '</thead>';
									    	echo '<tbody>';
										    if ( count($autoat) > 0 ) :
										    	echo '<tr>';
										    	echo '<td style="width:300px;">CORRESP BANC/INTERNET</td>';
										    	echo '<td>' .count($autoat).' - <b>R$ '.fmtValue(array_sum($autoatVlr)).'</b></td>';
										    	echo '<td><b>R$ '.number_format(count($autoat) * $tarAUTOAT, 2, ',', '.').'</b></td>';
										    	echo '</tr>';
										    	$sumTarsValue 		+= (count($autoat) * $tarAUTOAT);
										    	$sumPayments 		+= count($autoat);
										    	$sumPaymentsValues  += array_sum($autoatVlr);
										    	$addCC 				+= 2;
										   	endif;
										    if ( count($agn) > 0 ) :
										    	echo '<tr>';
										    	echo '<td style="width:300px;">AGÊNCIA CAIXA</td>';
										    	echo '<td>' .count($agn).' - <b>R$ '.fmtValue(array_sum($agnVlr)).'</b></td>';
										    	echo '<td><b>R$ '.number_format(count($agn) * $tarAGH, 2, ',', '.').'</b></td>';
										    	echo '</tr>';
										    	$sumTarsValue += (count($agn) * $tarAGH);
										    	$sumPayments += count($agn);
										    	$sumPaymentsValues  += array_sum($agnVlr);
										 		$addCC += 2;
										    endif;
										    if ( count($comp) > 0 ) :
										    	echo '<tr>';
										    	echo '<td style="width:300px;">COMPENSAÇÃO</td>';
										    	echo '<td>' .count($comp).' - <b>R$ '.fmtValue(array_sum($compVlr)).'</b></td>';
										    	echo '<td><b>R$ '.number_format(count($comp) * $tarCOMP, 2, ',', '.').'</b></td>';
										    	echo '</tr>';
										    	$sumTarsValue += (count($comp) * $tarCOMP);
										    	$sumPayments += count($comp);
										    	$sumPaymentsValues  += array_sum($compVlr);
										 		$addCC += 2;
										    endif;
										    if ( count($loterias) > 0 ) :
										    	echo '<tr>';
										    	echo '<td style="width:300px;">LOTERIAS</td>';
										    	echo '<td>' .count($loterias).' - <b>R$ '.fmtValue(array_sum($loteriasVlr)).'</td>';
										    	echo '<td><b>R$ '.number_format(count($loterias) * $tarLOT, 2, ',', '.').'</b></td>';
										    	echo '</tr>';
										    	$sumTarsValue += (count($loterias) * $tarLOT);
										    	$sumPayments += count($loterias);
										    	$sumPaymentsValues  += array_sum($loteriasVlr);
										 		$addCC += 2;
										   	endif;
										   	if ( $totalValues > 0 ) :
										   		$getCellInfo = getCellData($objWorksheet, $cc, $t_date, $p_date, $valorTotal, $totalValues, $tarCT, 'processamento', null, null, null, null, 'cef', '1559');
										   	endif;
									 		echo '<tr><td>Valor total de tarifas:</td><td></td><td style="color:red;font-weight:bold;">R$ '.number_format($sumTarsValue, 2, ',', '.').'</td></tr>';
										   	echo '</tbody>';
										   	echo '</table>';
										    echo '</div>';
										    $totalTarifas += $sumTarsValue;
										endif;
										//Creating the writer object
								 		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $sheet);
								 		$objWriter->setPreCalculateFormulas(false);
										$objWriter->save($accTrs);
										break;
									endif;
								endfor;
							endforeach;
						else:
							strtolower($fileName[2]) != 'srn' ? createAlert('danger', 'Arquivo não foi encontrado ou não existe<b>!</b>') : '';
						endif;
					elseif ( $writeInCT == '02' ) :
						$transferAux = false;
					    if ( count($autoat) > 0 || count($agn) > 0 || count($comp) || count($loterias) ) :
					    	$sumTars = 0;
					    	echo '<div style="padding:10px;">';
					    	echo '<table class="table table-condensed">';
					    	echo '<thead>';
					    	echo '<tr><td><b>Categoria</b></td><td><b>Qtde - Valor</b></td><td><b>Tarifas</b></td></tr>';
					    	echo '</thead>';
					    	echo '<tbody>';
						    if ( count($autoat) > 0 ) :
						    	echo '<tr>';
						    	echo '<td style="width:300px;">CORRESP BANC/INTERNET</td>';
						    	echo '<td>' .count($autoat).' - <b>R$ '.fmtValue(array_sum($autoatVlr)).'</b></td>';
						    	echo '<td><b>R$ '.number_format(count($autoat) * $tarAUTOAT, 2, ',', '.').'</b></td>';
						    	echo '</tr>';
						    	$sumTars += (count($autoat) * $tarAUTOAT);
						   	endif;
						    if ( count($agn) > 0 ) :
						    	echo '<tr>';
						    	echo '<td style="width:300px;">AGÊNCIA CAIXA</td>';
						    	echo '<td>' .count($agn).' - <b>R$ '.fmtValue(array_sum($agnVlr)).'</b></td>';
						    	echo '<td><b>R$ '.number_format(count($agn) * $tarAGH, 2, ',', '.').'</b></td>';
						    	echo '</tr>';
						    	$sumTars += (count($agn) * $tarAGH);
						    endif;
						    if ( count($comp) > 0 ) :
						    	echo '<tr>';
						    	echo '<td style="width:300px;">COMPENSAÇÃO</td>';
						    	echo '<td>' .count($comp).' - <b>R$ '.fmtValue(array_sum($compVlr)).'</b></td>';
						    	echo '<td><b>R$ '.number_format(count($comp) * $tarCOMP, 2, ',', '.').'</b></td>';
						    	echo '</tr>';
						    	$sumTars += (count($comp) * $tarCOMP);
						    endif;
						    if ( count($loterias) > 0 ) :
						    	echo '<tr>';
						    	echo '<td style="width:300px;">LOTERIAS</td>';
						    	echo '<td>' .count($loterias).' - <b>R$ '.fmtValue(array_sum($loteriasVlr)).'</td>';
						    	echo '<td><b>R$ '.number_format(count($loterias) * $tarLOT, 2, ',', '.').'</b></td>';
						    	echo '</tr>';
						    	$sumTars += (count($loterias) * $tarLOT);
						   	endif;
						   	echo '<tr><td>Valor total de tarifas:</td><td></td><td style="color:red;font-weight:bold;">R$ ' . number_format($sumTars, 2, ',', '.') . '</td></tr>';
						   	echo '</tbody>';
						   	echo '</table>';
						    echo '</div>';
						    $totalTarifas += $sumTars;
						endif;
					endif;
					echo ( $fileName[2] == 'SRN' ) ? '<br/>' : '';
					echo '</div>';
					$m += 1;
				endif;
				if ( $fileName[4] == '0040450' ) :
					$qtdeTotalRepasse += $totalValues;
					$vlrTotalRepasse += $valorTotal;
				endif;
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
			'tarifaTitulos' => $totalTarifas
		);
	getTableData($tableData);
	/*
	* (5º ETAPA)
	* Mostrar as duplicidades, caso existam
	*/
	ShowDuplicidades($duplicidades, $setydeiasConvs, 'ceft');
	/*
	* (6º ETAPA)
	* Remove as duplicidades dos arquivos que as possuem
	*/
	$RemoveFiles = new FilesHandler();
	$RemoveFiles->RemoveDuplicatedRecordsCNAB240($arquivos_com_duplicidade, $duplicidades, 'ceft');
?>