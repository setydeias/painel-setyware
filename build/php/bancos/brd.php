<?php
	$tarOrig = $customerTars['BRD'][0];
	$pathOriginal = $orig;
	$pathCheques = $pathCheque; //Pagamentos em Cheque
	//Array created to @paymentOccurrence var
	$occurrences = $duplicidades = $arquivos_com_duplicidade = array();
	//Showing the bank's image
	echo '<img src="/painel/build/images/brd.jpg" width="200" /> <br/><br/>';
	//Path to processed files
	$pathAUX = $processed;
	$dirAUX = dir($pathAUX);
	/*
	* (1º ETAPA)
	* Lista os arquivos que contém pagamentos
	* Processa linha a linha e destina a informação do pagamento
	* ao devido cliente de acordo com o NOSSO NÚMERO do REGISTRO DETALHE
	*/
	for($i = 0, $lenI = count($brd); $i < $lenI; $i++):
		if (filesize($brd[$i]) > 1000) :
			/*
			* 1.1) CAPTURAÇÃO DAS INFORMAÇÕES GERAIS DO ARQUIVO (HEADER, TRAILER)
			*/
			//Capturing the file's header
			$header  = file($brd[$i])[0];
			//Capturing the file's trailer
			$trailer = file($brd[$i])[count(file($brd[$i]))-1];
			//Capturing the file's date
			$data = fmtDatePattern(substr(file($brd[$i])[0], 94, 6), '17');
			$dataBase = fmtDatePattern(substr(file($brd[$i])[0], 94, 6), '17');
			//Sequencial of file
			$sequence = substr(file($brd[$i])[0], 107, 6);
			//Capturing the string where there's the file
			$file = explode('\\', $brd[$i]);
			//Verificação de duplicidades
		 	if ($checarDuplicidade == '1') :
	 			$dataReferencia = date('Y-m-d', strtotime("-$diasDuplicidades days", strtotime($data))); //Data de referência para a comparação nas duplicidades
	 			$duplicidades[] = verificarDuplicidadeCNAB400($conn, '21777', $data, $dataBase, $dataReferencia, $pathOriginal, 'brd');
	 		endif;
			//Capturing the name of the file
			$fileName = explode('_', $file[count($file)-1]);
			//Aux var
			$sigla = null;
			/*
			* 1.2) VARREDURA DOS REGISTROS DETALHE, LINHA A LINHA
			* VERIFICA QUAL É O CLIENTE QUE POSSUI AQUELE PAGAMENTO
			* E VINCULA A INFORMAÇÃO DO PGTO AO SEU ARQUIVO DE RETORNO
			* 
			*/
			//List the file's informations of the T and U segments
			for($k = 1, $lenK = (count(file($brd[$i])) - 1); $k < $lenK; $k++):
				//Capturing the client
				$customerSigla = $customer->GetSiglaByCodSac(substr(file($brd[$i])[$k], 71, 3));
				$cli = strlen($customerSigla) <= 0 ? 'INDEFINIDO' : $customerSigla;
				//Verifica a ocorrência do pagamento
				$paymentOccurrence = substr(file($brd[$i])[$k], 108, 2);
				//Nosso número
				$nosso_numero = substr(file($brd[$i])[$k], 70, 11);
				//Número do registro
				$numero_registro = substr(file($brd[$i])[$k], 394, 6);
				//Try new client
				$cli != $sigla ? $sigla = $cli : '';
				//Create the file that will be overwritten
				$arquivo = 'IEDCBR_'.fmtDatePattern($data, '8').'_'.$sigla.'_237_0021777_'.$sequence.'.ret';
				//Check the payment occurrence
				//06 - Liquidação Normal
				//17 - Liquidação após baixa ou Título não registrado
				if ( $paymentOccurrence == '06' || $paymentOccurrence == '17' ) :
					//Open the file
					$fp = fopen($processed.$arquivo, 'a');
					if(count(file($processed.$arquivo)) == 0) fwrite($fp, $header);
					//Verifica se a checagem de duplicidades está ativa
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
									if (($nosso_numero == $duplicidade['nosso_numero'] && $sequence == $duplicidade['seq_arquivo'] && $numero_registro == $duplicidade['num_registro']) || ($nosso_numero == $duplicidade['nosso_numero'] && $sequence != $duplicidade['seq_arquivo']) ) :
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
							fwrite($fp, file($brd[$i])[$k]);
						endif;
					else :
						fwrite($fp, file($brd[$i])[$k]);
					endif;
					//fwrite($fp, file($brd[$i])[$k]);
					fclose($fp);
				else :
					//Probably that will be 16 - (Pagamento em Cheque)
					//Caso queira que o título vá para a pasta "Pagamentos em Cheque" é só tirar o comentário do bloco abaixo
					/*$arquivoCHQ = $pathCheques.fmtDatePattern($data, '6').'_237_0021777_'.$sigla.'_'.$sequence.'.ret';
					$fOpen = fopen($arquivoCHQ, 'a');
					fwrite($fOpen, file($brd[$i])[$k]);
					fclose($fOpen);*/
					$occurrences[] = $sigla;
				endif;
			endfor;	
		endif;
	endfor;
	//Procedure for add the trailer at the end of the file
	$path2 = $processed;
	$dir2 = dir($path2);
	while ( $file = $dir2->read() ) :
		$fileName = explode('_', $file);
		if ( pathinfo($file, PATHINFO_EXTENSION) == 'ret' && $fileName[3] == '237' ) :
			$fp = fopen($processed.$file, 'a');
			fwrite($fp, $trailer);
			fclose($fp);
		endif;
	endwhile;
	//List the files for show in the screen
	$dir3 = dir($path2);
	$vlrTotalRepasse = 0;
	$qtdeTotalRepasse = 0;
	//Creates the accordion
	echo '<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">';
	//Reading the files on the @path2 var
	while ( $file = $dir3->read() ) :
		if ( !is_dir($file) && $file != 'Originais' ) :
			$fileName = explode('_', $processed.$file);
			//Turns the arrays cleaned
			$nosso_nums = array();
			$valores    = array();
			//Verify the files' extension
			if(pathinfo($processed.$file, PATHINFO_EXTENSION) == 'ret' && $fileName[3] == '237'):
				//Add the info to the arrays
				for($n = 1, $lenN = (count(file($processed.$file)) -1); $n < $lenN; $n++):
			    	$num = substr(file($processed.$file)[$n], 70, 11);
			    	$value = substr(file($processed.$file)[$n], 253, 13);
				    $nosso_nums[] = $num;
				    $valores[] = $value;
			    endfor;
			    //Aux var for counting values
			    $valorTotal = 0;
			    for($h = 0; $lenH = count($valores), $h < $lenH; $h++):
			    	$valorTotal += $valores[$h];
			    endfor;
			    //If the file has more than zero info
			    if(count($valores) > 0):			    
				    $fileName = explode('_', $file);
					echo '<div class="panel panel-default">';
					echo '<div class="panel-heading" role="tab" id="headingOne">';
				    echo '<h4 class="panel-title">';
				    echo '<a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$fileName[2].$data.'" aria-expanded="true" aria-controls="collapse'.$fileName[2].$data.'">';
				    //Checking the existence of occurrences
				    $occurrence16 = (in_array($fileName[2], $occurrences)) ? '<font color="red">(TÍTULO PAGO EM CHEQUE)</font>' : '';
				    echo '<span class="glyphicon glyphicon-folder-open"></span> &nbsp;'.$fileName[2]. ' '. $occurrence16 . '<span class="pull-right">CT <img src="/painel/build/images/icons/checkbox.svg" width="20" /> | FTP <img src="/painel/build/images/icons/checkbox.svg" width="20" /> | Qtde: <b>' . count($valores) . '</b> | Tarifas (R$): <b>'.number_format((count($valores) * $tarOrig), 2, ',', '.').'</b> | Valores (R$): <b>'.fmtValue($valorTotal).'</b> | <span class="glyphicon glyphicon-calendar"></span> <b>'.fmtDatePattern($data, '15').'</b></span>';
				    echo '</a>';
				    echo '</h4>';
				    echo '</div>';
				    echo '<div id="collapse'.$fileName[2].$data.'" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">';
				    echo '<div class="panel-body">';
				    echo '<table class="table table-striped table-condensed>"';
				    echo '<thead>';
				    echo '<tr><td><b>Nosso Número</b></td><td><b>Data do Pagamento</b></td><td><b>Valor do Pagamento</b></td></tr>';
				    echo '</thead>';
				    echo '<tbody>';
				    for($n = 0; $lenN = count($valores), $n < $lenN; $n++):
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
			    		//Store client
			    		if ( !in_array($fileName[2], $clientsWithPayments) ) :
			    			$clientsWithPayments[] = $fileName[2];
			    		endif;
				    	//Write on Conta Transitoria of the client
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
										getCellData($objWorksheet, $cc, $t_date, $p_date, $valorTotal, $valores, $tarOrig, 'processamento', null, null, null, null, 'brd', null, $clientsWithPayments, $fileName[2]);
								 		//Creating the writer object
								 		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $sheet);
								 		$objWriter->setPreCalculateFormulas(false);
										$objWriter->save($accTrs);
								 		break;
									endif;
								endfor;
							endforeach;
						else :
							createAlert('danger', 'Arquivo não foi encontrado ou não existe<b>!</b>');
						endif;
					endif;
				endif;
				$qtdeTotalRepasse += count($valores);
				$vlrTotalRepasse  += $valorTotal;
			endif;
		endif;
	endwhile;
	/*
	* (2º ETAPA)
	* Resumo dos pagamentos
	* Quantidade total de pagamentos, valor total arrecadado e custo de tarifas
	*/
	$tableData = array(
			'banco' => 'brd',
			'qtdeTitulos' => $qtdeTotalRepasse,
			'valorTitulos' => $vlrTotalRepasse,
			'tarifaTitulos' => $qtdeTotalRepasse * $tarOrig
		);
	getTableData($tableData);
	/*
	* (3º ETAPA)
	* Mostrar as duplicidades, caso existam
	*/
	ShowDuplicidades($customer, $duplicidades, $setydeiasConvs, 'brd');
	/*
	* (4º ETAPA)
	* Remove as duplicidades dos arquivos que as possuem
	*/
	$FileHandler = new FilesHandler();
	$FileHandler->RemoveDuplicatedRecordsCNAB400($arquivos_com_duplicidade, $duplicidades);
	/*
	* (8º ETAPA)
	* Enviar os relatórios de duplicidades para a hospedagem
	*/
	$FileHandler->SendToHost($duplicidades, $dataBase);
?>