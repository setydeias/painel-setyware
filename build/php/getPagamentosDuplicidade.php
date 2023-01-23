<?php
	set_time_limit(0);
	ini_set('memory_limit', '-1');
	include_once('functions.php');
	connectFB();
	$data = json_decode(file_get_contents('php://input'), true);
	$data_base = $data['base'];
	$data_de = $data['de'];
	$data_ate = $data['ate'];
	$banco = $data['banco'];
	$nomeCliente = $data['nomeCliente'];
	$convenio = $data['convenio'];
	//Arrays/Variáveis auxiliares
	$auxK = 2;
	$params_arquivo_base = array();
	$duplicidades = array();
	$bancosCNAB240 = array('001', '104');
	$bancosCNAB400 = array('02R'); //02R -> BRADESCO
	//Verifica se todos os campos obrigatórios foram preenchidos
	if (!$data_base || !$data_de || !$data_ate || !$banco || !$convenio) :
		sendErrorMessage(false, 'Todos os campos são obrigatórios', 'danger');
	else:
		$data_base = fmtDatePattern($data['base'], '3');
		$data_de = fmtDatePattern($data['de'], '3');
		$data_ate = fmtDatePattern($data['ate'], '3');
		//Verifica se a data limite é menor que a data inicial
		if (fmtDatePattern($data_de, '9') > fmtDatePattern($data_ate, '9')) :
			sendErrorMessage(false, 'Campo <kbd>Até</kbd> deve ser maior ou igual ao campo <kbd>De</kbd>', 'danger');
		else :
			$path = 'C:/COBPOP/Arquivos/Retornos/Duplicidades/';
			$pathProcessadas = 'C:/COBPOP/Arquivos/Retornos/Duplicidades/Processadas/';
			$dir = dir($path);
			$dirToRemove = dir($pathProcessadas);
			//Se existir algum arquivo processado
			//Exclui eles
			while ($fileToRemove = $dirToRemove->read()) :
				if (is_file($pathProcessadas.$fileToRemove)) :
					unlink($pathProcessadas.$fileToRemove);
				endif;
			endwhile;
			//Verificando se existe o arquivo base com a data base informada
			while ($file = $dir->read()) :
				if (is_file($path.$file)) :
					$banco_arquivo = substr(file($path.$file)[0], 0, 3);
					if ($banco == $banco_arquivo) :
						$params_arquivo_base['banco'] = $banco;
						if (in_array($banco, $bancosCNAB240)) :
							$data_arquivo = substr(file($path.$file)[0], 143, 8);
						elseif (in_array($banco, $bancosCNAB400)):
							$data_arquivo = fmtDatePattern(substr(file($path.$file)[0], 94, 6), '11');
						else :
							sendErrorMessage(false, 'O banco informado não é válido', 'danger');
						endif;
						if ($data_arquivo == $data_base) :
							$params_arquivo_base['arquivo_base'] = $path.$file;
						endif;
					endif;
				endif;
			endwhile;
			//Verificando se o banco selecionado está presente em algum arquivo
			if (!array_key_exists('banco', $params_arquivo_base)) :
				sendErrorMessage(false, 'Nenhum arquivo com o banco selecionado foi encontrado', 'danger');
			else :
				//Se existir o banco
				//Verifica se existe o arquivo com a data informada
				if (!array_key_exists('arquivo_base', $params_arquivo_base)) :
					sendErrorMessage(false, 'Nenhum arquivo, para o banco selecionado, contém a data base informada', 'danger');
				else :
					//$params_arquivo_base['banco'] => Banco base
					//$params_arquivo_base['arquivo_base']) => Arquivo base
					//Abre o arquivo base e faz o loop varrendo o nosso número
					if (in_array($params_arquivo_base['banco'], $bancosCNAB240)) :
						$convenio = substr(file($params_arquivo_base['arquivo_base'])[0], 58, 6);
						//Lista apenas os registros detalhe
						for ($i = 2, $len = count(file($params_arquivo_base['arquivo_base'])) - 2; $i < $len; $i++ ) :
							$segmento = substr(file($params_arquivo_base['arquivo_base'])[$i], 13, 1);
							if ($segmento == 'T') :
								//Nosso número de referência para a comparação
								$nosso_num = ($convenio == '040450') ? substr(file($params_arquivo_base['arquivo_base'])[$i], 38, 18) : substr(file($params_arquivo_base['arquivo_base'])[$i], 39, 17);
								if ($params_arquivo_base['banco'] == '001' || $params_arquivo_base['banco'] == '104' && substr($nosso_num, 0, 6) != ' 24000') :
									//RecursiveIteratorIterator está sendo usado
									//pois uma instância do objeto DIR só pode ser instanciada uma vez
									$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
									$it->rewind();
									while ($it->valid()) :
									    if (!$it->isDot()) :
									    	$fileToCompare = $it->key();
									    	$convenioFileToCompare = substr(file($fileToCompare)[0], 58, 6);
									    	if ($convenioFileToCompare == $convenio) :
												$dateToCompare = substr(file($fileToCompare)[0], 143, 8);
												//Verifica se o arquivo está dentro do período informado
												//Se estiver, ele será verificado
												if (fmtDatePattern($dateToCompare, '9') >= fmtDatePattern($data_de, '9') && fmtDatePattern($dateToCompare, '9') <= fmtDatePattern($data_ate, '9')) :
													if ($data_base != $dateToCompare) :
														//Faz a verificação para saber se há alguma duplicidade
														for ($k = 2, $lenK = count(file($fileToCompare)) - 2; $k < $lenK; $k++ ) :
															$segmentoFileToCompare = substr(file($fileToCompare)[$k], 13, 1);
															if ($segmentoFileToCompare == 'T') :
																$nossoNumToCompare = ($convenio == '040450') 
																? substr(file($fileToCompare)[$k], 38, 18)
																: substr(file($fileToCompare)[$k], 39, 17);
																if ($params_arquivo_base['banco'] == '001' || $params_arquivo_base['banco'] == '104' && substr($nossoNumToCompare, 0, 6) != ' 24000') :
																	if ($nosso_num == $nossoNumToCompare) :
																		$arquivo = $pathProcessadas.'DUPLICIDADES_'.$convenio.'_'.fmtDatePattern($data_base, '9').'_'.fmtDatePattern($dateToCompare, '9').'.txt';
																		$fp = fopen($arquivo, 'a');
																		$line = $dateToCompare.$nosso_num.substr(file($fileToCompare)[$k+1], 77, 15).substr(file($fileToCompare)[$k+1], 145, 8).PHP_EOL;
																		$fw = fwrite($fp, $line);
																		fclose($fp);
																		break;
																	endif;
																endif;
															endif;
														endfor;
													else :
														//Faz a verificação para saber se há alguma duplicidade dentro do arquivo do mesmo dia
														for ($k = 2, $lenK = count(file($fileToCompare)) - 2; $k < $lenK; $k++) :
															$segmentoFileToCompare = substr(file($fileToCompare)[$k], 13, 1);
															if ($segmentoFileToCompare == 'T' && $k > $i) :
																$nossoNumToCompare = ($convenio == '040450') 
																? substr(file($fileToCompare)[$k], 38, 18)
																: substr(file($fileToCompare)[$k], 39, 17);
																if ($nosso_num == $nossoNumToCompare) :
																	$arquivo = $pathProcessadas.'DUPLICIDADES_'.$convenio.'_'.fmtDatePattern($data_base, '9').'_'.fmtDatePattern($dateToCompare, '9').'.txt';
																	$fp = fopen($arquivo, 'a');
																	$line = $dateToCompare.$nosso_num.substr(file($fileToCompare)[$k+1], 77, 15).substr(file($fileToCompare)[$k+1], 145, 8).PHP_EOL;
																	$fw = fwrite($fp, $line);
																	fclose($fp);
																endif;
															endif;
														endfor;
													endif;
												endif;
											endif;
									    endif;
									    $it->next();
									endwhile;
								endif;
							endif;
						endfor;
					elseif (in_array($params_arquivo_base['banco'], $bancosCNAB400)) :

					else :
						sendErrorMessage(false, 'O banco informado não é válido', 'danger');
					endif;
					//Verifica se foram encontradas duplicidades de pagamentos
					$dirFiles = dir($pathProcessadas);
					$dirTotalDuplicidade = dir($pathProcessadas);
					$qtdeFiles = glob($pathProcessadas.'*.txt');
					if (count($qtdeFiles) > 0) :
						$somaTotal = 0;
						$qtdeTotalPgtos = 0;
						echo '<h4><span style="margin: 20px 0 0 0;" class="glyphicon glyphicon-list-alt"></span> Pagamentos efetuados em duplicidade ocorridos no dia '.fmtDatePattern($data_base, '4').'</h4>';
						echo '<hr />';
						if (strlen($nomeCliente) > 0) :
							echo '<div class="panel panel-primary">';
							echo '<div class="panel-heading">Cliente: '.$nomeCliente.'</div>';
							echo '<div class="panel-body" style="padding: 10px;">';
						endif;
						echo '<table class="table table-condensed">';
						echo '<thead>';
						echo '<tr><td><b>Pagador</b></td><td><b>Data pgto original</td><td><b>Nosso Número</b></td><td><b>Valor do Pagamento (R$)</b></td><td><b>Data créd original</b></td></tr>';
						echo '</thead>';
						echo '<tbody>';
					    while ($fd = $dirTotalDuplicidade->read()) :
							if (is_file($pathProcessadas.$fd)) :
								for ($j = 0, $lenJ = count(file($pathProcessadas.$fd)); $j < $lenJ; $j++) :
									$dataDoPagamento = fmtDatePattern(substr(file($pathProcessadas.$fd)[$j], 0, 8), '4');
									$nossoNumero = substr(file($pathProcessadas.$fd)[$j], 8, 17);
									$dataDoCredito = fmtDatePattern(substr(file($pathProcessadas.$fd)[$j], 40, 8), '4');
									$valorDoPagamento = substr(file($pathProcessadas.$fd)[$j], 25, 15)/100;
									$matricula = substr($nossoNumero, 8, 3);
									if ($convenio == '040450') :
										$nossoNumero = substr(file($pathProcessadas.$fd)[$j], 8, 18);
										$dataDoCredito = fmtDatePattern(substr(file($pathProcessadas.$fd)[$j], 41, 8), '4');
										$valorDoPagamento = substr(file($pathProcessadas.$fd)[$j], 26, 15)/100;
										$matricula = substr($nossoNumero, 7, 3);
									endif;
									$qtdeTotalPgtos++;
									$somaTotal += $valorDoPagamento;
									$valorDoPagamento = number_format($valorDoPagamento, 2, ',', '.');
									echo '<tr>';
									echo '<td>'.getPagadorByOurNumber(pathName(takeClient($matricula)), $nossoNumero).'</td>';
									echo '<td>'.$dataDoPagamento.'</td>';
									echo '<td>'.$nossoNumero.'</td>';
									echo '<td align="center">'.$valorDoPagamento.'</td>';
									echo '<td>'.$dataDoCredito.'</td>';
									echo '</tr>';
								endfor;
							endif;
						endwhile;
						echo '</tbody>';
						echo '</table>';
						echo (strlen($nomeCliente) > 0) ? '</div></div></div>' : '';
						//Resumo de pagamentos em duplicidade
						echo '<table class="table table-bordered table-condensed">';
						echo '<thead>';
						echo '<tr><td>Qtde. de Duplicidades</td><td>Total Arrecadado (R$)</td></tr>';
						echo '</thead>';
						echo '<tbody>';
						echo '<tr><td>'.$qtdeTotalPgtos.'</td><td>'.number_format($somaTotal, 2, ',', '.').'</td></tr>';
						echo '</tbody>';
						echo '</table>';
					else :
						sendErrorMessage(false, 'Nenhum pagamento em duplicidade foi encontrado', 'info');
					endif;
				endif;
			endif;
		endif;
	endif;
	closeConFB();