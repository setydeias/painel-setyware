<?php
	
	include_once 'FirebirdCRUD.class.php';
	include_once 'Util.class.php';
	include_once 'LocawebFTP.class.php';
	include_once 'Customer.class.php';
	include_once 'DirManager.class.php';

	class FilesHandler {

		public $file;
		public $duplicidades;
		public $bank;
		public $con;
		public $customer;
		public $dir;

		public function __construct() {
			//Conexão com banco de dados
			$this->con = new FirebirdCRUD();
			$this->customer = new Customer();
			$this->dirManager = new DirManager();
			$this->dir = "C:\\Setydeias\\Setyware\\ADM77777\\Adm\\Clientes\\";
		}

		public function __destruct() {}

		/*
		* CONVERSÃO DE ARQUIVOS PARA O PROCESSAMENTO DE RETORNOS
		*/
		
		//Retorna os arquivos para a conversão
		public function getFilesToConvert() {
			//Obtendo o diretório onde os arquivos presentes serão convertidos
			$dir_ret_processados = $this->dirManager->getDirs(array('RETORNOS_PROCESSADOS'))['RETORNOS_PROCESSADOS'][0];
			//Obtendo os clientes que não terão arquivos de retorno convertidos
			$dataCustomerNotConvert = array(
				'table' => 'SACADOS s',
				'params' => 's.CLI_SIGLA',
				'where' => "s.CNAB240 = '1'"
			);
			$customers_file_not_convert = array_unique($this->con->Select($dataCustomerNotConvert)['CLI_SIGLA']);
			//Listando os arquivos processados
			$files = $this->dirManager->getFiles($dir_ret_processados, array('ret', 'RET'));
			
			if ( count($files) > 0 ) {
				//Matriz que receberá os arquivos que deverão ser convertidos
				$filesToConvert = array();

				foreach ( $files as $file ) {
					$fileName = basename($file);
					$sigla = explode('_', $fileName)[2];

					//Se o arquivo for de algum cliente na lista dos clientes que recebem retorno por email
					//Ele não entrará na matriz dos arquivos que serão convertidos
					if ( !in_array($sigla, $customers_file_not_convert) ) $filesToConvert[] = $fileName;
				}
				
				return $filesToConvert;
			} else {
				return null;
			}
		}

		//Converte os arquivos retornados no método @getFilesToConvert
		public function convertFileToFB() {	
			$files = $this->getFilesToConvert();

			//Obtém as tarifas de cada cliente
			$customer_taxes = $this->customer->getCustomerTaxes();
			
			if ( !is_null($files) ) {
				//Pasta onde os retornos são convertidos
				$dir_ret_processados = $this->dirManager->getDirs(array('RETORNOS_PROCESSADOS'))['RETORNOS_PROCESSADOS'][0];
				//Convertando os arquivos
				foreach ( $files as $file ) {
					//$file é o nome do arquivo a ser processado
					$file = $dir_ret_processados.$file;
					$banco = substr(file($file)[0], 0, 3);
					$desc_banco = substr(file($file)[0], 102, 15);
					$conv = $banco == '001' ? substr(file($file)[0], 34, 7) : substr(file($file)[0], 58, 6);

					if ( $banco == '001' && $desc_banco == 'BANCO DO BRASIL' || $banco == '104' && ($desc_banco == 'C ECON FEDERAL ' || $desc_banco === 'CAIXA ECONOMICA') ) {
						for ( $i = 2; $i < count(file($file)) - 2; $i++ ) {
							$segment = substr(file($file)[$i], 13, 1);
							$multa = substr(file($file)[$i+1], 18, 14);
							$arquivo = explode('\\', $file);
							$arquivoSRQ = explode('.', $arquivo[count($arquivo)-1]);
							$fileN = explode('_', $arquivoSRQ[0]);
							$sigla = $fileN[2]; 
							$tax = isset($customer_taxes[$sigla][$conv]) ? $customer_taxes[$sigla][$conv] : $customer_taxes[$sigla]['CEF'];
							$data = substr(file($file)[0], 143, 8);
							$valor = substr(file($file)[$i+1], 79, 13);
							$valorRest = $valor - $tax;
							$nomeArqui = $fileN[0].'_'.Util::FmtDate($data, '3').'_'.$sigla.'_'.$fileN[3].'_'.$fileN[4].'_'.$fileN[5].'.srq';
							$dateFB = Util::FmtDate($data, '16');
							$num = substr(file($file)[$i], 37, 17);
							if ( $segment == 'T' ) {
								$dtcred = Util::FmtDate(substr(file($file)[$i+1], 145, 8), '17');
								$dateCredFB = Util::FmtDate(substr(file($file)[$i+1], 145, 8), '16');
								$valor_nominal = substr(file($file)[$i], 81, 15)/100;
								$dataVcto = Util::FmtDate(substr(file($file)[$i], 73, 8), '16');
								if ( $dataVcto == '00/00/0000' ) $dataVcto = date('m')."/".date('d')."/".date('Y');
								$dataEmiss = $dataVcto; 
								if ( $conv == '040450' ) {
									$num = substr(file($file)[$i], 37, 19);
								} else if ( $conv == '264151' ) {
									$num = substr(file($file)[$i], 39, 17);
								}
								$fp = fopen($dir_ret_processados.pathinfo($file)['filename'].'.srq', 'a');

								$linhaRetorno = "STYRET
EXECUTE PROCEDURE BAIXAR_RETORNO '".trim($num)."', '".$dateFB."', '".$dateCredFB."', ".number_format($valor/100, 2, '.', '').", ".number_format($valorRest/100, 2, '.', '').", ".$tax.", ".number_format($multa/100, 2, '.', '').", '".$nomeArqui."', '".$dataEmiss."', '".$dataVcto."', ".number_format($valor_nominal, 2, '.', '').", '".$conv."';
COMMIT;OUTPUT 'C:\RelRetornosGrupo.ret'; SELECT 'X@'||B.CODCED_ID||'@'|| TRIM(B.NOMCED)||'@'|| A.CODSAC||'@'|| TRIM(C.NOMSAC)||'@'|| A.CODTIT||'@'|| TRIM(A.NOSSONUM)||'@'|| A.DTVCTO||'@'|| CAST(A.VLRTIT AS NUMERIC(9,2) )||'@'|| A.DTREC||'@'|| CAST(A.VLRREC AS NUMERIC(9,2) )||'@'|| CAST(A.VLMULTA AS NUMERIC(9,2) )||'@'|| CAST(A.VLJUROS AS NUMERIC(9,2) )||'@'|| CAST(A.VLDESCONTO AS NUMERIC(9,2) )||'@'|| CAST(A.TARIFA AS NUMERIC(9,2) )||'@'|| A.DTCRED||'@'||CAST( '".$dateFB."' AS DATE) ||'@' AS BAIXA FROM TITULOS A, CEDENTES B, SACADOS C WHERE A.CODSAC = C.CODSAC AND NOSSONUM = '".trim($num)."';".PHP_EOL;
								fwrite($fp, $linhaRetorno);
								fclose($fp);
							}
						}
					} else if ( $banco == '02R' || $banco == '237' && substr(file($file)[0], 76, 11) == '237BRADESCO' ) {
						for ( $k = 1; $k < count(file($file)) - 1; $k++ ) {
							$num = substr(file($file)[$k], 70, 11);
							$conv = substr(file($file)[$k], 29, 7);
							$date = substr(file($file)[0], 94, 6);
							$dateCred = substr(file($file)[$k], 295, 6);
							$valor = substr(file($file)[$k], 253, 13);
							$valor_nominal = substr(file($file)[$k], 152, 13);
							$arquivo = explode('\\', $file);
							$arquivoSRQ = explode('.', $arquivo[count($arquivo)-1]);
							$multa = substr(file($file)[$k], 267, 15);
							$fileN = explode('_', $arquivoSRQ[0]);
							$sigla = $fileN[2]; 
							$nomeArqui = $fileN[0].'_'.Util::FmtDate($date, '19').'_'.$sigla.'_'.$fileN[3].'_'.$fileN[4].'_'.$fileN[5].'.srq';
							$dateFB = Util::FmtDate($date, '18');
							$dateCredFB = Util::FmtDate($dateCred, '18');
							$fp = fopen($dir_ret_processados.pathinfo($file)['filename'].'.srq', 'a');

							$linhaRetorno = "STYRET
EXECUTE PROCEDURE BAIXAR_RETORNO '".trim($num)."', '".$dateFB."', '".$dateCredFB."', ".number_format($valor/100, 2, '.', '').", ".number_format($valor/100, 2, '.', '').", 0.00, ".number_format($multa/100, 2, '.', '').", '".$nomeArqui."', '".$dateFB."', '".$dateFB."', ".number_format($valor_nominal/100, 2, '.', '').", '".$conv."';
COMMIT;OUTPUT 'C:\RelRetornosGrupo.ret'; SELECT 'X@'||B.CODCED_ID||'@'|| TRIM(B.NOMCED)||'@'|| A.CODSAC||'@'|| TRIM(C.NOMSAC)||'@'|| A.CODTIT||'@'|| TRIM(A.NOSSONUM)||'@'|| A.DTVCTO||'@'|| CAST(A.VLRTIT AS NUMERIC(9,2) )||'@'|| A.DTREC||'@'|| CAST(A.VLRREC AS NUMERIC(9,2) )||'@'|| CAST(A.VLMULTA AS NUMERIC(9,2) )||'@'|| CAST(A.VLJUROS AS NUMERIC(9,2) )||'@'|| CAST(A.VLDESCONTO AS NUMERIC(9,2) )||'@'|| CAST(A.TARIFA AS NUMERIC(9,2) )||'@'|| A.DTCRED||'@'||CAST( '".$dateFB."' AS DATE) ||'@' AS BAIXA FROM TITULOS A, CEDENTES B, SACADOS C WHERE A.CODSAC = C.CODSAC AND NOSSONUM = '".trim($num)."';".PHP_EOL;
							fwrite($fp, $linhaRetorno);
							fclose($fp);
						}
					}
					//Apaga o arquivo com extensão .RET
					unlink($file);
				}
			}
		}

		/*
		* Modifica o arquivo de retorno para alterar a tarifa do cliente de acordo com o contrato
		*/

		public function updateTaxesOnFile($file, $customer) {
			//Obtém o conteúdo do arquivo original
			$handler = fopen($file, 'r');
			$content_handler = fread($handler, filesize($file));
			fclose($handler);
			$content = explode(PHP_EOL, $content_handler);

			//Obtém a tarifa do cliente
			$banco = substr($content[0], 0, 3);
			$conv = $banco == '001' ? substr($content[0], 34, 7) : substr($content[0], 58, 6);
			$lqrTax = $this->customer->getLqrTax(); //LQR
			$taxes = $this->customer->getCustomerTaxes($customer)[$customer]; //Tarifas diversas
			$customer_tax = isset($taxes) ? $taxes[$conv] : $taxes['CEF'];

			//Loop no conteúdo para filtrar o que deve ser alterado
			for ( $i = 0 ; $i < count($content) ; $i++ ) {
				$segmento = substr($content[$i], 13, 1);
				if ( $segmento == 'T' ) {
					//Pega determinada parte da linha e substitui pela tarifa do cliente formatado-a para o arquivo
					//Só troca a tarifa caso a mesma não seja proveniente de LQR
					$file_tax = substr($content[$i], 198, 15);
					
					if ( $file_tax != str_pad(number_format($lqrTax, 2, '', ''), 15, '0', STR_PAD_LEFT) ) {
						$new_content_value = substr_replace($content[$i], str_pad(number_format($customer_tax, 2, '', ''), 15, '0', STR_PAD_LEFT), 198, 15);
						$content[$i] = $new_content_value;
					}
				}
			}

			//Põe o ponteiro no começo do arquivo e escreve o novo conteúdo
			$handler = fopen($file, 'w+');
			fwrite($handler, implode(PHP_EOL, $content));
			fclose($handler);
		}

		/*
		* Separando os relatórios individualmente
		*/

		public function individualizeDuplicidades($duplicidades, $bank) {
			$customers = array();
			
			foreach ($duplicidades as $dp) :
				foreach ($dp as $duplicidade) :
					//Escolhendo o padrão do nosso número
					switch ($bank) :
						case 'bb':
							$customer = substr($duplicidade['nosso_numero'], 7, 3);
						break;
						case 'cefp':
							$customer = substr($duplicidade['nosso_numero'], 8, 3);
						break;
						case 'ceft':
							$customer = substr($duplicidade['nosso_numero'], 7, 3);
						break;
						default: //BRD
							$customer = substr($duplicidade['nosso_numero'], 1, 3);
						break;
					endswitch;

					$customers[$customer][] = $duplicidade;
				endforeach;
			endforeach;

			return $customers;
		}

		/*
		* Gera o relatório de duplicidades em PDF na pasta do cliente
		*/ 

		public function CreateReport(array $duplicidades, $data_arquivo, $bank = null) {
			//Matriz que recebe o cliente e as duplicidades
			$customers = $this->individualizeDuplicidades($duplicidades, $bank);
			
			//Requisição
		 	$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://localhost/painel/build/php/getPDFDuplicidades.php');
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, array(serialize($customers), $data_arquivo));
			curl_exec($ch);
			curl_close($ch);
		}

		/*
		* Envia o PDF gerado pela função @CreateReport para a hospedagem SETYDEIAS.COM.BR
		*/

		public function SendToHost($duplicidades, $data_arquivo, $bank) {
			//Matriz que recebe o cliente e as duplicidades
			$customers = $this->individualizeDuplicidades($duplicidades, $bank);
			//Retorna um array com a matrícula dos clientes que possuem duplicidades
			$keys = array_keys($customers);
			//Data do evento
			$data_evento = Util::FmtDate($data_arquivo, '8');
			$data_evento_pathname = Util::FmtDate($data_arquivo, '9');
			//Conexão FTP
			$ftp = new LocawebFTP();
			//Array que recebe o status de envio dos arquivos
			$send_status = array();
			//Enviando os arquivos para o servidor
			for ( $i = 0 ; $i < count($keys) ; $i++ ) :
				$customerPath = $this->customer->GetPathNameByCod($keys[$i]);
				$customerSigla = $this->customer->GetSiglaByCodSac($keys[$i]);
				$fileName = "DUPLICIDADES_".$data_evento."_".$customerSigla.".pdf";
				$fileToSend = $this->dir.$customerPath."\Duplicidades\\".$fileName;
				$remoteFile = "./public_html/contatransitoria/relatorios/".$data_evento_pathname."/".$fileName;

				$received = $ftp->send($fileToSend, $remoteFile);
				$send_status[$i]['info'] = $received;
				$send_status[$i]['file'] = $fileName;
			endfor;
			//Verificando se houve falhas no envio
			foreach ( $send_status as $status ) :
				if ( !$status['info']['received'] ) echo "<section class='alert alert-danger'>".$status['file'].": ".$status['info']['message']."</section>";
			endforeach;
		}

		/*
		* Remove os registros duplicados do arquivo de retorno padrão CNAB240
		*/ 

		public function RemoveDuplicatedRecordsCNAB240(array $files, array $duplicidades, $bank = null) {
			if (count($files) > 0) :
				//Passa por cada arquivo que possui duplicidade
				for ($i = 0; $i < count($files); $i++) :
					$arquivo = file($files[$i]); //Arquivo atual
					$sequencial_arquivo = substr($arquivo[0], 157, 6);
					$data_do_arquivo = substr($arquivo[0], 143, 8);
					//Loop dentro do arquivo atual
					for ($x = 2; $x < count($arquivo); $x++) :
						$segmento = substr($arquivo[$x], 13, 1);
						$num_registro = substr($arquivo[$x], 8, 5); //Numero do registro que será comparado
						switch ($bank) :
							case 'bb':
								$nosso_numero = trim(substr($arquivo[$x], 37, 20)); //Nosso número do registro que será comparado
							break;
							case 'cefp' :
								$nosso_numero = trim(substr($arquivo[$x], 39, 17)); //Nosso número do registro que será comparado
							break;
							case 'ceft' :
								$nosso_numero = trim(substr($arquivo[$x], 38, 18)); //Nosso número do registro que será comparado
							break;
						endswitch;
						if ($segmento == 'T') :
							//Loop nas duplicidades
							//Para encontrar a duplicidade do arquivo e retirá-la
							foreach ($duplicidades as $dp) :
								foreach ($dp as $duplicidade) :
									if (($duplicidade['seq_arquivo'] == $sequencial_arquivo && $duplicidade['num_registro'] == $num_registro && $duplicidade['nosso_numero'] == $nosso_numero)
										|| $duplicidade['seq_arquivo'] != $sequencial_arquivo && $duplicidade['nosso_numero'] == $nosso_numero ) :
										//Retira o registro T
										if (!file_put_contents($files[$i], str_replace($arquivo[$x], "", file_get_contents($files[$i])))) :
											echo 'Registro duplicado não foi retirado, favor checar';
										endif;
										//Retira o registro U
										if (!file_put_contents($files[$i], str_replace($arquivo[$x+1], "", file_get_contents($files[$i])))) :
											echo 'Registro duplicado não foi retirado, favor checar';
										endif;
									endif;
								endforeach;
							endforeach;
						endif;
					endfor;
				endfor;
				//Gera o relatório, em PDF, das duplicidades
				$this->CreateReport($duplicidades, $data_do_arquivo, $bank);
			endif;
		}

		/*
		* Remove os registros duplicados do arquivo de retorno padrão CNAB240
		*/ 

		public function RemoveDuplicatedRecordsCNAB400(array $files, array $duplicidades) {
			if (count($files) > 0) :
				//Passa por cada arquivo que possui duplicidade
				for ($i = 0; $i < count($files); $i++) :
					$arquivo = file($files[$i]); //Arquivo atual
					$sequencial_arquivo = substr($arquivo[0], 108, 5);
					$data_do_arquivo = fmtDatePattern(substr($arquivo[0], 94, 6), '11');
					//Loop dentro do arquivo atual
					for ($x = 1; $x < count($arquivo); $x++) :
						$num_registro = substr($arquivo[$x], 394, 6); //Numero do registro que será comparado
						$nosso_numero = trim(substr($arquivo[$x], 70, 11)); //Nosso número do registro que será comparado
						//Loop nas duplicidades
						//Para encontrar a duplicidade do arquivo e retirá-la
						foreach ($duplicidades as $dp) :
							foreach ($dp as $duplicidade) :
								if (($duplicidade['seq_arquivo'] == $sequencial_arquivo && $duplicidade['num_registro'] == $num_registro && $duplicidade['nosso_numero'] == $nosso_numero)
									|| $duplicidade['seq_arquivo'] != $sequencial_arquivo && $duplicidade['nosso_numero'] == $nosso_numero ) :
									//Retira o registro
									if (!file_put_contents($files[$i], str_replace($arquivo[$x], "", file_get_contents($files[$i])))) :
										echo 'Registro duplicado não foi retirado, favor checar';
									endif;
								endif;
							endforeach;
						endforeach;
					endfor;
				endfor;
				//Gera o relatório, em PDF, das duplicidades
				$this->CreateReport($duplicidades, $data_do_arquivo, 'brd');
			endif;
		}

		/*
		* AUXILIA NO PROCESSAMENTO DE REMESSAS
		* ATUALIZAR DATA DE VENCIMENTO E VALOR, NO CASO DE BAIXA
		* ATUALIZAR VALOR, NO CASO DE ALTERAÇÃO DE VENCIMENTO
		*/

		//Retorna os dados do arquivo
		public function GetDataReportBB($file) {
			try {
				$file = file($file);
				$data = array();

				for ( $i = 0 ; $i < count($file) ; $i++ ) {
					$info = explode(';', $file[$i]);
					$data[] = array(
						'agencia' => trim($info[1]),
						'conta' => trim($info[2]),
						'data_do_relatorio' => trim($info[3]),
						'carteira' => trim($info[4]),
						'variacao' =>  trim($info[5]),
						'pagador' => trim($info[6]),
						'nosso_numero' => substr(trim($info[7]), 0, -1),
						'seu_numero' => trim($info[8]),
						'valor_titulo' => trim($info[9]),
						'data_vencimento' => Util::FmtDate(trim($info[10]), '4'),
						'status' => trim($info[11]),
						'tipo_documento' => trim($info[13]),
						'documento' => trim($info[14])
						);
				}

				return $data;
			} catch (Exception $e) {
				echo $e->getMessage();
			}
		}

		//Insere os dados do relatório, trazidos pela função GetDataReportBB, no banco
		public function InsertDataReport(array $files) {
			for ( $i = 0; $i < count($files); $i++ ) {
				if ( !file_exists($files[$i]) ) {
					return false;
				} else {
					$data = $this->GetDataReportBB($files[$i]);
					
					for ( $k = 0; $k < count($data) ; $k++ ) {
						$agencia = $data[$k]['agencia'];
						$conta = $data[$k]['conta'];
						$pagador = $data[$k]['pagador'];
						$sigla = $data[$k]['sigla_cliente'];
						$nosso_numero = $data[$k]['nosso_numero'];
						$seu_numero = $data[$k]['seu_numero'];
						$valor = $data[$k]['valor_titulo'];
						$dt_vcto = $data[$k]['data_vencimento'];
						$status = $data[$k]['status'];
						$tipo_documento = $data[$k]['tipo_documento'];
						$documento = $data[$k]['documento'];
						
						$dataToInsert = array( 
							'table' => 'PROCESSAMENTO_REMESSAS', 
							'columns' => array(
								'AGENCIA' => $agencia,
								'CONTA' => $conta,
								'PAGADOR' => substr($pagador, 0, 20),
								'NOSSO_NUMERO' => $nosso_numero,
								'SIGLA_CLIENTE' => substr($this->customer->GetSiglaByCodSac(substr($nosso_numero, 7, 3)), 0, 3),
								'SEU_NUMERO' => $seu_numero,
								'VALOR' => $valor,
								'DATA_VENCIMENTO' => $dt_vcto,
								'STATUS' => $status,
								'TPDOC' => $tipo_documento,
								'DOCSAC' => $documento,
								'CONVENIO' => substr($nosso_numero, 0, 7)
							)
						);

						$info = $this->con->Insert($dataToInsert);
					}
				}
			}

			return true;
		}
		
		//Remove todos os títulos da tabela
		public function RemoveDataReport() {

			$dataToDelete = array(
				'table' => 'PROCESSAMENTO_REMESSAS pr'
				);

			$data = $this->con->Delete($dataToDelete);
		}

		//Retorna os parametros corretos de acordo com o código de movimentação e nosso número
		public function GetDataByOurNumber($nosso_num, $cod_mov) {

			if ( $cod_mov == "02" ) {
				$params = "pr.DATA_VENCIMENTO, pr.VALOR";
			} else if ( $cod_mov == "06" ) {
				$params = "pr.VALOR";
			}

			$dataToSelect = array(
				'table' => 'PROCESSAMENTO_REMESSAS pr',
				'params' => "$params, pr.STATUS",
				'where' => array('pr.NOSSO_NUMERO' => $nosso_num)
			);

			$data = $this->con->Select($dataToSelect);
			return $data;
		}

	}