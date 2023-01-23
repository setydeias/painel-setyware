<?php
	set_time_limit(0);
	ini_set('display_errors', 1);
	ini_set('memory_limit', '-1');
	include_once 'class/Util.class.php';
	include_once 'class/Customer.class.php';

	function connectFB() {
		$con = ibase_connect('localhost:C:\Setydeias\Setyware\ADM77777\ADM77777.gdb', 'SYSDBA', 'masterkey') or die(ibase_errmsg());
		
		return $con;
	}

	function GetDocBeneficiario($sigla) {
		$data = array();
		$sql = ibase_prepare("SELECT TPDOCSAC, DOCSAC FROM SACADOS WHERE CLI_SIGLA = '$sigla'");
		$result = ibase_execute($sql);

		if ($result) :
			while ($row = ibase_fetch_object($result)) :
				$data['tpdocsac'] = $row->TPDOCSAC;
				$data['docsac'] = $row->DOCSAC;
			endwhile;
		endif;

		return $data;
	}

	/* function alert type */
	function createAlert($type, $msg) {
		echo '<div>';
		echo '<div class="alert alert-'.$type.' alert-dismissible hdn">';
		echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.$msg.'</div>';
		echo '</div>';
		echo '<div>';
	}

	function takeClient($sig) {
		$sigla = null;

		if (strlen($sig) > 0) :
			$query = "SELECT CLI_SIGLA FROM SACADOS WHERE CODSAC = '$sig' OR REPASSE_VARIACAO = '$sig'";
			$result = ibase_query($query);

			if ($result) :
				while($row = ibase_fetch_object($result)) :
					$sigla = $row->CLI_SIGLA;
				endwhile;

				$sigla = (strlen($sigla) < 1) ? 'INDEFINIDO' : $sigla;	
			else :
				$sigla = 'INDEFINIDO';
			endif;
		else:
			$sigla = 'INDEFINIDO';
		endif;
		
		return $sigla;
	}

	function takeMat($sig) {
		$q = "SELECT CODSAC FROM SACADOS WHERE CLI_SIGLA = '$sig'";
		$res = ibase_query($q);

		while($row = ibase_fetch_object($res)) {
			$cod = $row->CODSAC;
		}

		return str_pad($cod, 5, 0, STR_PAD_LEFT);
	}

	function takeSite($sig) {
		$query = "SELECT SITE FROM SACADOS WHERE CLI_SIGLA = '$sig'";
		$result = ibase_query($query);

		while($row = ibase_fetch_object($result)) {
			$site = $row->SITE;
		}

		return $site;
	}

	function closeConFB() {
		return ibase_close();
	}

	function pathName($sign) {
		if ( strlen($sign) == 3 ) :
			$query = "SELECT CLI_SIGLA, CODSAC FROM SACADOS WHERE CLI_SIGLA = '$sign'";
			$result = ibase_query($query);
			
			while($row = ibase_fetch_object($result)) {
				$sigla = $row->CLI_SIGLA;
				$cod   = $row->CODSAC;
			}
		else :
			return 'indefinido';
		endif;

		return strtolower($sigla).str_pad($cod, 5, 0, STR_PAD_LEFT);
	}

	function fmtValue($value) {
		$posicao = "";
        for ($i = 0; $i < strlen($value); $i++) {
            if(substr($value, $i, 1) > 0):
                $posicao .= $i;
            	break;
            endif;
        }
        //Integer convertion
        $valor_documento = number_format(substr($value, intval($posicao))/100, 2, ',', '.');
        return $valor_documento;
	}

	function fmtValueFB($value) {
		$posicao = "";
        for ($i = 0; $i < strlen($value); $i++) {
            if(substr($value, $i, 1) > 0):
                $posicao .= $i;
            	break;
            endif;
        }
        //Integer convertion
        $valor_documento = number_format(substr($value, intval($posicao))/100, 2, '.', '');
        return $valor_documento;
	}

	function fmtdateFB($data) {
		$dia = substr($data, 0, 2);
		$mes = substr($data, 2, 2);
		$ano = substr($data, 4, 4);
		
		return $mes.'/'.$dia.'/'.$ano;
	}

	function fmtdateFBBRD($data) {
		$dia = substr($data, 0, 2);
		$mes = substr($data, 2, 2);
		$ano = substr($data, 4, 2);
		
		return $mes.'/'.$dia.'/20'.$ano;
	}

	function fmtDateFileName($data) {
		if(strlen($data) == 8):
			$dia = substr($data, 0, 2);
			$mes = substr($data, 2, 2);
			$ano = substr($data, 6, 2);
		elseif(strlen($data) == 6):
			$dia = substr($data, 0, 2);
			$mes = substr($data, 2, 2);
			$ano = substr($data, 4, 2);
		endif;

		return $ano.$mes.$dia;
	}

	function fmtDate($date) {
		$d = substr($date, 0, 2);
		$m = substr($date, 2, 2);
		$a = substr($date, 6, 2);

		$data = $d.$m.$a; 

		return $data;
	}

	function fmtDateBB($date) {
		$d = substr($date, 0, 2);
		$m = substr($date, 2, 2);
		$a = substr($date, 4, 4);

		return $d.'/'.$m.'/'.$a;
	}

	function fmtDatePattern($date, $type) {
		switch ($type) :
			//13122016 to 2016-12-13
			case '1':
				$day = substr($date, 0, 2);
				$month = substr($date, 2, 2);
				$year = substr($date, 4, 4);
				$date = $year.'-'.$month.'-'.$day;
				break;
			//13/12/2016 to 2016-12-13
			case '2':
				$date = explode('/', $date);
				$day = $date[0];
				$month = $date[1];
				$year = $date[2];
				$date = $year.'-'.$month.'-'.$day;
				break;
			//13/12/2016 to 13122016
			case '3':
				$date = explode('/', $date);
				$day = $date[0];
				$month = $date[1];
				$year = $date[2];
				$date = $day.$month.$year;
				break;
			//13122016 to 13/12/2016
			case '4':
				$day = substr($date, 0, 2);
				$month = substr($date, 2, 2);
				$year = substr($date, 4, 4);
				$date = $day.'/'.$month.'/'.$year;
				break;
			//13122016 to 131216
			case '5':
				$day = substr($date, 0, 2);
				$month = substr($date, 2, 2);
				$year = substr($date, 6, 2);
				$date = $day.$month.$year;
				break;
			//131216 to 161213
			case '6':
				$day = substr($date, 0, 2);
				$month = substr($date, 2, 2);
				$year = substr($date, 4, 2);
				$date = $year.$month.$day;
				break;
			//131216 to 13/12/2016
			case '7':
				$day = substr($date, 0, 2);
				$month = substr($date, 2, 2);
				$year = substr($date, 4, 2);
				$date = $day.'/'.$month.'/20'.$year;
				break;
			//2016-12-13 to 161213
			case '8':
				$date = explode('-', $date);
				$day = $date[2];
				$month = $date[1];
				$year = substr($date[0], 2, 2);
				$date = $year.$month.$day;
				break;
			//13122016 to 161213
			case '9':
				$day = substr($date, 0, 2);
				$month = substr($date, 2, 2);
				$year = substr($date, 6, 2);
				$date = $year.$month.$day;
				break;
			//13/12/2016 to 20161213
			case '10':
				$date = explode('/', $date);
				$day = $date[0];
				$month = $date[1];
				$year = $date[2];
				$date = $year.$month.$day;
				break;
			//131216 to 13122016
			case '11':
				$day = substr($date, 0, 2);
				$month = substr($date, 2, 2);
				$year = substr($date, 4, 2);
				$date = $day.$month.'20'.$year;
				break;
			//161213 to 13/12/2016
			case '12':
				$day = substr($date, 4, 2);
				$month = substr($date, 2, 2);
				$year = substr($date, 0, 2);
				$date = $day.'/'.$month.'/20'.$year;
				break;
			//13/12/2016 to 161213
			case '13':
				$date = explode('/', $date);
				$day = $date[0];
				$month = $date[1];
				$year = substr($date[2], 2, 2);
				$date = $year.$month.$day;
				break;
			//2016-12-13 to 131216
			case '14':
				$date = explode('-', $date);
				$day = $date[2];
				$month = $date[1];
				$year = substr($date[0], 2, 2);
				$date = $day.$month.$year;
				break;
			//2016-12-13 to 13/12/2016
			case '15':
				$date = explode('-', $date);
				$day = $date[2];
				$month = $date[1];
				$year = $date[0];
				$date = $day.'/'.$month.'/'.$year;
				break;
			//2016-12-13 to 13122016
			case '16':
				$date = explode('-', $date);
				$day = $date[2];
				$month = $date[1];
				$year = $date[0];
				$date = $day.$month.$year;
				break;
			//131216 to 2016-12-13
			case '17':
				$day = substr($date, 0, 2);
				$month = substr($date, 2, 2);
				$year = '20'.substr($date, 4, 2);
				$date = $year.'-'.$month.'-'.$day;
				break;
		endswitch;

		return $date;
	}

	function fmtHour($hour) {
		$horas = substr($hour, 0, 2);
		$minutos = substr($hour, 2, 2);
		$segundos = substr($hour, 2, 2);

		return $horas.':'.$minutos.':'.$segundos;
	}

	function fmtDateExcel($date) {
		$d = substr($date, 0, 2);
		$m = substr($date, 2, 2);
		$a = substr($date, 4, 2);

		return $a.'-'.$m.'-'.$d;
	}

	function fmtDateBRD($date) {
		if(strlen($date) == 6):
			$d = substr($date, 0, 2);
			$m = substr($date, 2, 2);
			$a = substr($date, 4, 2);
		elseif(strlen($date) == 8):
			$d = substr($date, 0, 2);
			$m = substr($date, 2, 2);
			$a = substr($date, 6, 2);
		endif;

		return $a.$m.$d;
	}

	function fmtDateNoQuotes($date) {
		$d = explode('/', $date);
		return substr($d[2], 2, 2).$d[1].$d[0];
	}

	function fmtDateNoHyphen($date) {
		$d = explode('-', $date);
		return substr($d[0], 2, 2).$d[1].$d[2];
	}

	function fmtDateBR($date) {
		$d = substr($date, 0, 2);
		$m = substr($date, 2, 2);
		$a = substr($date, 4, 2);

		return $d.'/'.$m.'/20'.$a;
	}

	function fmtDateBR2($date) {
		$d = substr($date, 4, 2);
		$m = substr($date, 2, 2);
		$a = substr($date, 0, 2);

		return $d.'/'.$m.'/20'.$a;
	}

	function fmtDateBR3($date) {
		$d = substr($date, 0, 2);
		$m = substr($date, 2, 2);
		$a = substr($date, 4, 2);

		return $d.'/'.$m.'/20'.$a;
	}

	function fmtDateDBT($date) {
		$a = substr($date, 2, 2);
		$m = substr($date, 4, 2);
		$d = substr($date, 6, 2);

		return $a.$m.$d;
	}

	function fmtRemessDateToUS($date) {
		$d = substr($date, 0, 2);
		$m = substr($date, 2, 2);
		$a = substr($date, 4, 2);

		return $a.'-'.$m.'-'.$d;
	}

	function fmtRemessDateToBR($date) {
		$dt = explode('-', $date);
		$d = $dt[2];
		$m = $dt[1];
		$a = $dt[0];

		return $d.'/'.$m.'/'.$a;
	}

	function fmtDateFBTransfer($date) {
		$dt = explode('/', $date);
		$d  = $dt[1];
		$m  = $dt[0];
		$a  = $dt[2];

		return $d.'/'.$m.'/'.$a;
	}

	function valorDocumentoFB($string) {
        for ($i = 0; $i < strlen($string); $i++) {
            if(substr($string, $i, 1) > 0):
                $posicao .= $i;
            endif;
        }
        $position = $posicao[0];
        $valor_documento = number_format(substr($valor, $position)/100, 2, '.', '.');
        return $valor_documento;
	}

	function getMotivoOcorrencia($crud, $numero_ocorrencia, $tipo_ocorrencia, $dominio_ocorrencia) {
		if ($numero_ocorrencia == '00') :
			$motivo = 'Ocorrência não encontrada';
		else :
			$dataToSelect = array(
				'table' => 'OCORRENCIAS o',
				'params' => 'o.MOTIVO_OCORRENCIA',
				'where' => array(
					'o.NUMERO_OCORRENCIA' => $numero_ocorrencia,
					'o.TIPO_OCORRENCIA' => $tipo_ocorrencia,
					'o.DOMINIO_OCORRENCIA' => $dominio_ocorrencia
					)
				);

			$ocorrencia = $crud->Select($dataToSelect);
			$motivo = count($ocorrencia) > 0 ? $ocorrencia['MOTIVO_OCORRENCIA'][0] : 'Ocorrência não encontrada';
		endif;

		return $motivo;
	}

	function relatorioTituloRegistrado($crud, $titulo, $icon, $corIcone, $matriz) {
		if (count($matriz) > 0) :
			$complemento = count($matriz) != 1 ? 's' : '';
			echo '<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">';
			echo '<div class="panel panel-default">';
			echo '<div class="panel-heading" role="tab" id="headingOne" style="background:#fff;">';
			echo '<h4 class="panel-title">';
			echo '<a role="button" data-toggle="collapse" data-parent="#accordion" href="#'.$icon.'" aria-expanded="true" aria-controls="'.$icon.'">';
			echo '['.$titulo.'] <span class="glyphicon glyphicon-'.$icon.'" style="color:'.$corIcone.';"></span> <span class="pull-right"><span class="badge" style="background:'.$corIcone.';">'.count($matriz).' título'.$complemento.'</span></span>';
			echo '</a>';
			echo '</h4>';
			echo '</div>';
			echo '<div id="'.$icon.'" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">';
			echo '<div class="panel-body">';
			echo '<table class="table table-condensed">';
			echo '<thead>';
			echo '<tr><td><b>Nosso Número</b></td><td><b>Data de Vencimento</b></td><td><b>Valor do Título (R$)</b></td></tr>';
			echo '</thead>';
			foreach ($matriz as $titulo) :
				echo '<tr>';
				echo '<td>'.$titulo['nosso_numero'].'</td>';
				echo '<td>'.fmtDatePattern($titulo['vencimento'], '4').'</td>';
				echo '<td>'.$titulo['valor_titulo'].'</td>';
				//Se existir a chave 'motivo' é porque o titulo foi rejeitado
				if (array_key_exists('motivo', $titulo)) :
					$motivo = array();
					for ($i = 0; $i < (strlen(trim($titulo['motivo']))/2); $i++) :
						if ($i == 0) :
							$motivo[] = getMotivoOcorrencia($crud, substr($titulo['motivo'], $i, 2), 'c047', 'A');	
						else :
							$motivo[] = getMotivoOcorrencia($crud, substr($titulo['motivo'], $i+$i, 2), 'c047', 'A');
						endif;
					endfor;
					echo '<td>'.implode('<br/>', $motivo).'</td>';
				endif;
				echo '</tr>';
			endforeach;
			echo '</table>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		endif;
	}

	function fmtNumber($number) {
		if(strlen($number) == 8) {
			if(substr($number, 0, 1) == '3') { //if was fixphone
				$number = '(85) '.substr($number, 0, 4).'-'.substr($number, 4, 4);
			} else { //If is smartphone
				$number = '(85) 9'.substr($number, 0, 4).'-'.substr($number, 4, 4);
			}
		} else if(strlen($number) == 9) {
			$number = '(85) '.substr($number, 0, 5).'-'.substr($number, 5, 4);
		} else if(strlen($number) == 10) {
			if(substr($number, 2, 1) == '3') {
				$number = '('.substr($number, 0, 2).') '.substr($number, 2, 4).'-'.substr($number, 6, 4);
			} else {
				$number = '('.substr($number, 0, 2).') 9'.substr($number, 0, 4).'-'.substr($number, 6, 4);
			}
		} else if(strlen($number) == 11) {
			$number = '('.substr($number, 0, 2).') '.substr($number, 2, 4).'-'.substr($number, 6, 4);
		}

		return $number;
	}

	function fmtDoc($doc) {
		if(strlen($doc) == 14) {
			$doc = substr($doc, 0, 2).'.'.substr($doc, 2, 3).'.'.substr($doc, 5, 3).'/'.substr($doc, 8, 4).'-'.substr($doc, 12, 2);
		} else if(strlen($doc) == 11) {
			$doc = substr($doc, 0, 3).'.'.substr($doc, 3, 3).'.'.substr($doc, 6, 3).'-'.substr($doc, 9, 2);
		}

		return $doc;
	}

	function fmtCEP($cep) {
		return substr($cep, 0, 2).'.'.substr($cep, 2, 3).'-'.substr($cep, 5, 3);
	}

	function fmtDtAssoc($date) {
		$dt = explode('-', $date);
		$d = $dt[2];
		$m = $dt[1];
		$a = $dt[0];

		return $d.'/'.$m.'/'.$a;
	}

	function takeSTR($str) {
		$monthsEN = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		$monthsPT = array('Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
		for($x = 0; $lenX = strlen($str), $x < $lenX; $x++):
			if(in_array(substr($str, $x, 3), $monthsEN)):
				$day   = substr($str, $x+4, 2);
				$month = str_replace($monthsEN, $monthsPT, substr($str, $x, 3));
				if(strstr($str, ':')):
					$expSTR = explode(':', $str);
					$hour = substr($expSTR[0], -2).':'.substr($expSTR[1], 0, 2);
				endif;
			endif;
		endfor;

		if(!isset($hour)):
			return $day.' de '.$month;
		else:
			return $day.' de '.$month.' às '.$hour;
		endif;
	}

	function verificarDuplicidade($connection, $conv, $data, $dataBase, $dataReferencia, $path, $bank) {
		/*
		* $conv = Convênio de referência para recuperar os arquivos que serão comparados
		* $data = Data de pagamento do arquivo base
		* $dataBase = $data formatada
		* $dataReferencia = Data limite de comparação dos arquivos
		* $path = Local onde os arquivos estão
		* $bank = Banco para referência de posições das variáveis
		*/
		$duplicidades = $arquivos_referencia = array();
		$pathOriginal = $path;
		$indice_duplicidade = 0;
		$sequencial_arquivo_original = null;
		//Retorna os arquivos que devem ser comparados
		while ($dataBase != $dataReferencia) {
			$pathToCompare = dir($path);
			//Percorre um loop na pasta contida na variável $path
 			while ($file = $pathToCompare->read()) {
 				//Deve ser um arquivo com extensão .ret
 				if (pathinfo($file, PATHINFO_EXTENSION) == 'ret') {
 					if ($bank == 'bb' || $bank == 'cef' || $bank == 'ceft') {
		 				$dataToCompare = substr(file($pathOriginal.$file)[0], 143, 8);
		 				if ($bank == 'bb') {
		 					$convToCompare = substr(file($pathOriginal.$file)[0], 34, 7);
						} else if ($bank == 'cef' || $bank == 'ceft') {
		 					$convToCompare = substr(file($pathOriginal.$file)[0], 58, 6);
						}
	 					//Trás apenas os arquivos dentro do período solicitado e do mesmo convênio
	 					if ($dataToCompare == fmtDatePattern($dataBase, '16') && $conv == $convToCompare) {
	 						//Retorna o sequencial do arquivo original do dia base
	 						$sequencial_arquivo_original = substr(file($pathOriginal.$file)[0], 157, 6);
	 						$fileToCompare = file($pathOriginal.$file);
	 						$arquivos_referencia[] = $pathOriginal.$file;
						}
					}
				}
			}
 			$pathToCompare = null;
 			$dataBase = date('Y-m-d', strtotime('-1 days', strtotime($dataBase)));
		}
 		//Faz um loop nos arquivos de referência
 		//para cadastrá-los no banco de dados e checar a duplicidade
 		if (count($arquivos_referencia) > 0) {
 			//Array que conterá os nossos números do arquivo do mesmo dia
 			$nossos_numeros_ref = $nossos_numeros_auxdata = array();
	 		//Registra os nossos_números em uma tabela temporária
	 		for ($i = 0; $i < count($arquivos_referencia); $i++) {
				$arquivo_ref = file($arquivos_referencia[$i]); //Arquivo de referência
	 			//Arquivo do dia não entra no banco de dados
	 			//Apenas dias anteriores
	 			if ( $i === 0 ) {
	 				//Loop dentro do arquivo do mesmo dia 
	 				for ($x = 2; $x < count($arquivo_ref) - 2; $x++) {
		 				$segmento = substr($arquivo_ref[$x], 13, 1);
		 				$cod_mov = substr($arquivo_ref[$x], 15, 2);
						//$nosso_num serve para checar se o nosso número não é SRN, caso $bank == 'ceft'
						$nosso_num = $bank === 'bb' ? trim(substr($arquivo_ref[$x], 37, 10)) : trim(substr($arquivo_ref[$x], 39, 10));
						//Se o registro for T
						if ($segmento == 'T' && ($cod_mov == '06' || $cod_mov == '17') && $nosso_num != '2400000000') {
							$seq_arquivo = substr($arquivo_ref[0], 157, 6);
							$numero_registro = substr($arquivo_ref[$x], 8, 5);
							if ($bank == 'bb') {
								$nosso_numero_original = trim(substr($arquivo_ref[$x], 37, 20));
							} else if ($bank == 'cef') {
								$nosso_numero_original = trim(substr($arquivo_ref[$x], 39, 17));
							} else if ($bank == 'ceft') {
								$nosso_numero_original = trim(substr($arquivo_ref[$x], 38, 18));
							}
							$valor = substr($arquivo_ref[$x+1], 77, 15)/100;
							$dtpgto = substr($arquivo_ref[$x+1], 137, 8);
							$dtcred = substr($arquivo_ref[$x+1], 145, 8);
							
							$nossos_numeros_ref[] = $nosso_numero_original;
							$nossos_numeros_auxdata[] = $seq_arquivo.'|'.$numero_registro.'|'.$valor.'|'.$dtpgto.'|'.$dtcred;
						}
					}
		 			//Array com as duplicidades do mesmo dia
					$duplicidadesMesmoDia = array_diff_assoc($nossos_numeros_ref, array_unique($nossos_numeros_ref));
		 			foreach ($duplicidadesMesmoDia as $key => $duplicidade) {
		 				/*
						* dadosDaDuplicidade[0] => Sequencial do arquivo
						* dadosDaDuplicidade[1] => Número do registro no arquivo
						* dadosDaDuplicidade[2] => Valor do pagamento
						* dadosDaDuplicidade[3] => Data de pagamento
						* dadosDaDuplicidade[4] => Data de crédito
		 				*/
		 				$dadosDaDuplicidade = explode('|', $nossos_numeros_auxdata[$key]);

		 				$duplicidades[$indice_duplicidade]['seq_arquivo'] = $dadosDaDuplicidade[0];
						$duplicidades[$indice_duplicidade]['num_registro'] = $dadosDaDuplicidade[1];
						$duplicidades[$indice_duplicidade]['nosso_numero'] = $duplicidade;
						$duplicidades[$indice_duplicidade]['valor'] = $dadosDaDuplicidade[2];
						$duplicidades[$indice_duplicidade]['data_pgto_original'] = $dadosDaDuplicidade[3];
						$duplicidades[$indice_duplicidade]['data_credito_original'] = $dadosDaDuplicidade[4];
						$indice_duplicidade++;
					}
				} else {
		 			//Loop dentro do arquivo
		 			for ($x = 2; $x < count($arquivo_ref) - 2; $x++) {
		 				$segmento = substr($arquivo_ref[$x], 13, 1);
		 				$cod_mov = substr($arquivo_ref[$x], 15, 2);
						//$nosso_num serve para checar se o nosso número não é SRN, caso $bank == 'ceft'
						$nosso_num = $bank === 'bb' ? trim(substr($arquivo_ref[$x], 37, 10)) : trim(substr($arquivo_ref[$x], 39, 10));
						//Se o registro for T
						if ( $segmento == 'T' && ($cod_mov == '06' || $cod_mov == '17') && $nosso_num != '2400000000' ) {
							$seq_arquivo = substr($arquivo_ref[0], 157, 6);
							$numero_registro = substr($arquivo_ref[$x], 8, 5);
							if ( $bank == 'bb' ) {
								$nosso_numero_original = trim(substr($arquivo_ref[$x], 37, 20));
							} else if ($bank == 'cef') {
								$nosso_numero_original = trim(substr($arquivo_ref[$x], 39, 17));
							} else if ($bank == 'ceft') {
								$nosso_numero_original = trim(substr($arquivo_ref[$x], 38, 18));
							}
							
							$valor = substr($arquivo_ref[$x+1], 77, 15)/100;
							$dtpgto = substr($arquivo_ref[$x+1], 137, 8);
							$dtcred = substr($arquivo_ref[$x+1], 145, 8);
							//Insere o registro no banco de dados
							$columns = array(
								'SEQUENCIAL_ARQUIVO' => $seq_arquivo,
								'DATA_PGTO' => $dtpgto,
								'DATA_CRED' => $dtcred,
								'NUMERO_REGISTRO' => $numero_registro,
								'NOSSO_NUMERO' => $nosso_numero_original,
								'VALOR' => $valor
							);
							
							$connection->Insert(array(
								'table' => 'PROCESSAMENTO_DUPLICIDADES',
								'columns' => $columns
							));
						}
					}
				}
			}
			
	 		//Seleciona os registros duplicados, caso existam
			$nossos_numeros_refs = join("', '", $nossos_numeros_ref);
			$getDuplicatedReg = $connection->Select(array(
				'table' => 'PROCESSAMENTO_DUPLICIDADES',
				'params' => 'SEQUENCIAL_ARQUIVO, DATA_PGTO, DATA_CRED, NUMERO_REGISTRO, NOSSO_NUMERO, VALOR',
				'where' => 'NOSSO_NUMERO in (\''.$nossos_numeros_refs.'\')'
			));
			
			if ( isset($getDuplicatedReg['NOSSO_NUMERO']) ) {
				for ( $d = 0 ; $d < count($getDuplicatedReg['NOSSO_NUMERO']) ; $d++ ) {
					$duplicidades[$indice_duplicidade]['seq_arquivo'] = $getDuplicatedReg['SEQUENCIAL_ARQUIVO'][$d];
					$duplicidades[$indice_duplicidade]['data_pgto_original'] = $getDuplicatedReg['DATA_PGTO'][$d];
					$duplicidades[$indice_duplicidade]['data_credito_original'] = $getDuplicatedReg['DATA_CRED'][$d];
					$duplicidades[$indice_duplicidade]['num_registro'] = $getDuplicatedReg['NUMERO_REGISTRO'][$d];
					$duplicidades[$indice_duplicidade]['nosso_numero'] = $getDuplicatedReg['NOSSO_NUMERO'][$d];
					$duplicidades[$indice_duplicidade]['valor'] = $getDuplicatedReg['VALOR'][$d];
					$indice_duplicidade++;
				}
			}
			//Deleta os registros temporários
			$connection->Delete(array( 'table' => 'PROCESSAMENTO_DUPLICIDADES' ));
		}

 		return $duplicidades;
	}

	function verificarDuplicidadeCNAB400($connection, $conv, $data, $dataBase, $dataReferencia, $path) {
		/*
		* $conv = Convênio de referência para recuperar os arquivos que serão comparados
		* $data = Data de pagamento do arquivo base
		* $dataBase = $data formatado
		* $dataReferencia = Data limite de comparação dos arquivos
		* $path = Local onde os arquivos estão
		*/
		$duplicidades = array();
		$arquivos_referencia = array();
		$pathOriginal = $path;
		$indice_duplicidade = 0;
		$sequencial_arquivo_original = null;
		//Retorna os arquivos que devem ser comparados
		while ($dataBase != $dataReferencia) {
			$pathToCompare = dir($path);
			//Percorre um loop na pasta contida na variável $path
 			while ($file = $pathToCompare->read()) {
 				//Deve ser um arquivo com extensão .ret
 				if (pathinfo($file, PATHINFO_EXTENSION) == 'ret' && substr(file($pathOriginal.$file)[0], 0, 3) == '02R') {
	 				$dataToCompare = substr(file($pathOriginal.$file)[0], 94, 6);
 					//Trás apenas os arquivos dentro do período solicitado e do mesmo convênio
 					if ($dataToCompare == fmtDatePattern($dataBase, '14')) {
 						//Retorna o sequencial do arquivo original do dia base
 						if ($dataToCompare == fmtDatePattern($data, '14')) {
 							$sequencial_arquivo_original = substr(file($pathOriginal.$file)[0], 157, 6);
						}
 						$fileToCompare = file($pathOriginal.$file);
 						$arquivos_referencia[] = $pathOriginal.$file;
					}
				}
			}
 			$pathToCompare = null;
 			$dataBase = date('Y-m-d', strtotime('-1 days', strtotime($dataBase)));
		}
 		//Faz um loop nos arquivos de referência
		//para cadastrá-los no banco de dados e checar a duplicidade
 		if (count($arquivos_referencia) > 0) {
 			//Array que conterá os nossos números do arquivo do mesmo dia
 			$nossos_numeros_ref = array();
 			$nossos_numeros_auxdata = array();
	 		//Registra os nossos_números em uma tabela temporária
	 		for ($i = 0; $i < count($arquivos_referencia); $i++) {
	 			$arquivo_ref = file($arquivos_referencia[$i]); //Arquivo de referência
	 			//Arquivo do dia não entra no banco de dados
	 			//Apenas dias anteriores
	 			if ($i === 0) {
	 				//Loop dentro do arquivo do mesmo dia
	 				for ($x = 1; $x < count($arquivo_ref) - 1; $x++) {
		 				$cod_mov = substr($arquivo_ref[$x], 108, 2);
						if ($cod_mov == '06' || $cod_mov == '17') {
							$seq_arquivo = substr($arquivo_ref[0], 108, 5);
							$numero_registro = substr($arquivo_ref[$x], 394, 6);
							$nosso_numero_original = trim(substr($arquivo_ref[$x], 70, 11));
							$valor = substr($arquivo_ref[$x], 253, 13)/100;
							$dtpgto = substr($arquivo_ref[$x], 110, 6);
							$dtcred = substr($arquivo_ref[$x], 295, 6);
							
							$nossos_numeros_ref[] = $nosso_numero_original;
							$nossos_numeros_auxdata[] = $seq_arquivo.'|'.$numero_registro.'|'.$valor.'|'.$dtpgto.'|'.$dtcred;
						}
					}
		 			//Array com as duplicidades do mesmo dia
					$duplicidadesMesmoDia = array_diff_assoc($nossos_numeros_ref, array_unique($nossos_numeros_ref));
		 			foreach ($duplicidadesMesmoDia as $key => $duplicidade) {
		 				/*
						* dadosDaDuplicidade[0] => Sequencial do arquivo
						* dadosDaDuplicidade[1] => Número do registro no arquivo
						* dadosDaDuplicidade[2] => Valor do pagamento
						* dadosDaDuplicidade[3] => Data de pagamento
						* dadosDaDuplicidade[4] => Data de crédito
		 				*
		 				*/
		 				$dadosDaDuplicidade = explode('|', $nossos_numeros_auxdata[$key]);

		 				$duplicidades[$indice_duplicidade]['seq_arquivo'] = $dadosDaDuplicidade[0];
						$duplicidades[$indice_duplicidade]['num_registro'] = $dadosDaDuplicidade[1];
						$duplicidades[$indice_duplicidade]['nosso_numero'] = $duplicidade;
						$duplicidades[$indice_duplicidade]['valor'] = $dadosDaDuplicidade[2];
						$duplicidades[$indice_duplicidade]['data_pgto_original'] = $dadosDaDuplicidade[3];
						$duplicidades[$indice_duplicidade]['data_credito_original'] = $dadosDaDuplicidade[4];
						$indice_duplicidade++;
					}
				} else {
		 			//Loop dentro do arquivo
		 			for ($x = 1; $x < count($arquivo_ref) - 1; $x++) {
		 				$cod_mov = substr($arquivo_ref[$x], 108, 2);
		 				
						if ($cod_mov == '06' || $cod_mov == '17') {
							$seq_arquivo = substr($arquivo_ref[0], 108, 5);
							$numero_registro = substr($arquivo_ref[$x], 394, 6);
							$nosso_numero_original = trim(substr($arquivo_ref[$x], 70, 11));
							$valor = substr($arquivo_ref[$x], 253, 13)/100;
							$dtpgto = substr($arquivo_ref[$x], 110, 6);
							$dtcred = substr($arquivo_ref[$x], 295, 6);
							//Insere o registro no banco de dados
							$columns = array(
								'SEQUENCIAL_ARQUIVO' => $seq_arquivo,
								'DATA_PGTO' => $dtpgto,
								'DATA_CRED' => $dtcred,
								'NUMERO_REGISTRO' => $numero_registro,
								'NOSSO_NUMERO' => $nosso_numero_original,
								'VALOR' => $valor
							);
							
							$connection->Insert(array(
								'table' => 'PROCESSAMENTO_DUPLICIDADES',
								'columns' => $columns
							));
						}
					}
				}
			}
	 		//Seleciona os registros duplicados, caso existam
	 		$nossos_numeros_refs = join("', '", $nossos_numeros_ref);
	 		$getDuplicatedReg = $connection->Select(array(
				'table' => 'PROCESSAMENTO_DUPLICIDADES',
				'params' => 'SEQUENCIAL_ARQUIVO, DATA_PGTO, DATA_CRED, NUMERO_REGISTRO, NOSSO_NUMERO, VALOR',
				'where' => 'NOSSO_NUMERO in (\''.$nossos_numeros_refs.'\')'
			));

			for ( $d = 0 ; $d < count($getDuplicatedReg['NOSSO_NUMERO']) ; $d++ ) {
				$duplicidades[$indice_duplicidade]['seq_arquivo'] = $getDuplicatedReg['SEQUENCIAL_ARQUIVO'][$d];
				$duplicidades[$indice_duplicidade]['num_registro'] = $getDuplicatedReg['NUMERO_REGISTRO'][$d];
				$duplicidades[$indice_duplicidade]['nosso_numero'] = $getDuplicatedReg['NOSSO_NUMERO'][$d];
				$duplicidades[$indice_duplicidade]['valor'] = $getDuplicatedReg['VALOR'][$d];
				$duplicidades[$indice_duplicidade]['data_pgto_original'] = $getDuplicatedReg['DATA_PGTO'][$d];
				$duplicidades[$indice_duplicidade]['data_credito_original'] = $getDuplicatedReg['DATA_CRED'][$d];
				$indice_duplicidade++;
			}
			//Deleta os registros temporários
			$connection->Delete(array(
				'table' => 'PROCESSAMENTO_DUPLICIDADES'
			));
		}

 		return $duplicidades;
	}

	//Títulos com repasse atrasado
	function titulosAtrasados(array $data) {
		if ( count($data) > 0 ) {
			echo '<div class="panel panel-danger">';
			echo '<div class="panel-heading"><h4><span class="glyphicon glyphicon-time"></span> TÍTULOS COM REPASSE ATRASADO</h4></div>';
			echo '<div class="panel-body">';
			echo '<table class="table table-condensed table-striped">';
			echo '<thead><tr><td>Cliente</td><td>Nosso número</td><td>Valor do título (R$)</td><td>Valor pago (R$)</td></tr></thead>';
			echo '<tbody>';
			foreach ( $data as $titulo ) {
				echo '<tr>';
				echo "<td>{$titulo['CLIENTE']}<br />";
				echo "<td>{$titulo['NOSSO_NUMERO']}<br />";
				echo "<td>{$titulo['VALOR_TITULO']}<br />";
				echo "<td>{$titulo['VALOR_PAGO']}<br />";
				echo "<tr>";
			}
			echo '</tbody></table>';
			echo '</div></div>';
		}
	}

	//Títulos com LQR
	function pagamentosLQR(array $data) {
		if ( count($data) > 0 ) {
			//Filtrando quantidade de pagamentos por cliente
			$payments = array();
			foreach ( $data as $info ) {
				$payments[$info['cliente']][] = $info;
			}
			echo '<div class="panel panel-info">';
			echo '<div class="panel-heading"><h4><span class="glyphicon glyphicon-time"></span> TÍTULOS COM LQR (<b>LIQUIDAÇÃO SEM REGISTRO</b>)</h4></div>';
			echo '<div class="panel-body">';
			foreach ( $payments as $customer => $info ) {
				echo '<div class="panel panel-default">';
				echo '<div class="panel-heading" role="tab" id="heading'.$customer.'" style="background:#fff;">';
				echo '<h4 class="panel-title">';
				echo '<a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$customer.'" aria-expanded="true" aria-controls="collapse'.$customer.'">';
				echo '<span class="glyphicon glyphicon-folder-open"></span> &nbsp;'.$customer.'</b><span class="pull-right">Qtde: '.count($info).'</span>';
				echo '</a>';
				echo '</h4>';
				echo '</div>';
				echo '<div id="collapse'.$customer.'" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading'.$customer.'">';
				echo '<div class="panel-body">';
				echo '<table class="table table-condensed table-striped">';
				echo '<thead>';
				echo '<tr><td>Nosso Número</td></tr>';
				echo '</thead>';
				echo '<tbody>';
				foreach ( $info as $payment ) {
					echo '<tr><td>'.$payment['nosso_numero'].'</td></tr>';
				}
				echo '</tbody>';
				echo '</table>';
				echo '</div>';
				echo '</div>';
				echo '</div>';
			}
			echo '</div></div>';
		}
	}

	function getMatricula($convenio) {
		$matricula = null;

		switch ($convenio) {
			case '0934658': //SDI
				$matricula = '161';
			break;
			case '1222639': //IAV
				$matricula = '138';
			break;
			case '0960823': //MSN
				$matricula = '088';
			break;
			case '1450647': //GLN
				$matricula = '082';
			break;
			case '0904551': //ALF
				$matricula = '087';
			break;
			case '0223951': //JKC
				$matricula = '152';
			break;
			case '2880844': //MSN
				$matricula = '088';
			break;
		}

		return $matricula;
	}

	function GerarPDF($titulo, $header, $data, $nome_arquivo, $dia_util) {
		if (count($data) > 0) :
		 	$curlData = array($titulo, $header, $data, $nome_arquivo, $dia_util);
		 	$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://localhost/painel/build/php/getPDF.php');
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, serialize($curlData));
			curl_exec($ch);
			curl_close($ch);
		endif;
	}

	function GerarPDFDuplicidades($data) {
		if (count($data) > 0) :
		 	$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://localhost/painel/build/php/getPDFDuplicidades.php');
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_exec($ch);
			curl_close($ch);
		endif;
	}

	function ShowDuplicidades($customer, array $duplicidades, array $setydeiasConvs, $banco) {
		//Escolhendo o banco
		switch ($banco) :
			case 'bb':
				$banco_string = 'do Banco do Brasil';
			break;
			case 'brd':
				$banco_string = 'do Bradesco S.A.';
			break;
			case 'cef':
				$banco_string = 'da Caixa Econômica Federal';
			break;
			case 'ceft':
				$banco_string = 'da Caixa Econômica Federal';
			break;
		endswitch;
		//Arrays auxiliares
		$ToShow = array(); //Matriz que armazena as duplicidades
		//Retorna a matriz com as duplicidades reais
		foreach ($duplicidades as $dp) :
			foreach ($dp as $duplicidade) :
				$ToShow[] = $duplicidade;
			endforeach;
		endforeach;
		//Quantidade de duplicidades encontradas
		$qtde_duplicidades = count($ToShow);
		if ($qtde_duplicidades > 0) :
			$plural = ($qtde_duplicidades > 1) ? 's' : '';
			echo '<div class="panel panel-default">';
			echo '<div class="panel-heading"><h4><span class="glyphicon glyphicon-list-alt"></span> Relatório de pagamentos em duplicidade '.$banco_string.' <span class="pull-right badge" style="background:#09f;">'.$qtde_duplicidades.' duplicidade'.$plural.' encontrada'.$plural.'</span></h4></div>';
			echo '<div class="panel-body" style="padding:10px;">';
			echo '<table class="table table-condensed">';
			echo '<thead>';
			echo '<tr><td><b>Cliente</b></td><td><b>Pagador</b></td><td><b>Data pgto original</td><td><b>Nosso Número</b></td><td><b>Valor do Pagamento (R$)</b></td><td><b>Data créd original</b></td></tr>';
			echo '</thead>';
			echo '<tbody>';
			foreach ($ToShow as $duplicidade) :
				$seq_arquivo = $duplicidade['seq_arquivo'];
				($banco != 'brd') ?	$numero_registro = $duplicidade['num_registro'] : '';
				$nosso_numero = $duplicidade['nosso_numero'];
				if ($banco == 'bb') :
					$convenio = substr($nosso_numero, 0, 7);
					$matricula = (in_array($convenio, $setydeiasConvs)) ? substr($nosso_numero, 7, 3) : getMatricula($convenio);
				elseif ($banco == 'cef') :
					$convenio = substr($nosso_numero, 2, 6);
					$matricula = (in_array($convenio, $setydeiasConvs)) ? substr($nosso_numero, 8, 3) : getMatricula($convenio);
				elseif ($banco == 'ceft') :
					$convenio = substr($nosso_numero, 1, 6);
					$matricula = (in_array($convenio, $setydeiasConvs)) ? substr($nosso_numero, 7, 3) : getMatricula($convenio);
				elseif ($banco == 'brd') :
					$matricula = substr($nosso_numero, 1, 3);
				endif;
				$valor = $duplicidade['valor'];
				$dtpgto = $duplicidade['data_pgto_original'];
				$dtcred = $duplicidade['data_credito_original'];
				$cliente = $customer->GetSiglaByCodSac($matricula);
				$pathname = $customer->GetPathNameBySigla($cliente);
				$pagador = getPagadorByOurNumber($pathname, $nosso_numero);
				echo '<tr>';
				echo '<td>'.$cliente.'</td>';
				echo '<td>'.utf8_decode($pagador).'</td>';
				echo '<td>'.fmtDatePattern($dtpgto, '4').'</td>';
				echo '<td>'.$nosso_numero.'</td>';
				echo '<td>'.number_format($valor, 2, ',', '.').'</td>';
				echo '<td>'.fmtDatePattern($dtcred, '4').'</td>';
				echo '</tr>';
			endforeach;
			echo '</tbody>';
			echo '</table>';
			echo '</div>';
			echo '</div>';
			echo '<hr />';
		endif;
	}

	function getTableData(array $data) {

		$banco = $data['banco'];
		$qtdeTotalTitulos = $data['qtdeTitulos'];
		$valorTotalTitulos = number_format($data['valorTitulos']/100, 2, ',', '.');
		$tarifasTotalTitulos = number_format($data['tarifaTitulos'], 2, ',', '.');

		if ($banco == 'bb') :
			foreach ($data['carteiras'] as $carteira) :
				if ($carteira['qtde'] > 0) :
					switch ($carteira['tipoCarteira']) :
						case 'cr1711':
							$tipoCarteira = '(Cr. 17/11)';
						break;
						case 'cr1705':
							$tipoCarteira = '(Cr. 17/05)';
						break;
						case 'cr17':
							$tipoCarteira = '(Cr. 17/04)';
						break;
						case 'cr18':
							$tipoCarteira = '(Cr. 18)';
						break;
					endswitch;
					echo '<table class="table table-condensed table-bordered" style="margin:20px 0;background:#fff;">';
					echo '<thead>';
					echo '<tr>';
					echo '<td>Quantidade de títulos <b>'.$tipoCarteira.'</b></td><td>Valor arrecadado (R$) <b>'.$tipoCarteira.'</b></td><td>Custo de tarifas (R$) <b>'.$tipoCarteira.'</b></td>';
					echo '</tr>';
					echo '</thead>';
					echo '<tbody>';
					echo '<tr>';
					echo '<td>'.$carteira['qtde'].'</td><td>'.number_format($carteira['valor']/100, 2, ',', '.').'</td><td>'.number_format($carteira['tarifas'], 2, ',', '.').'</td>';
					echo '</tr>';
					echo '</tbody>';
					echo '</table>';
				endif;
			endforeach;
		endif;

		echo '<table class="table table-condensed table-bordered" style="margin:20px 0;background:#fff;">';
		echo '<thead>';
		echo '<tr>';
		echo '<td>Quantidade total de títulos</td><td>Total do valor arrecadado (R$)</td><td>Custo total de tarifas (R$)</td>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		echo '<tr>';
		echo '<td>'.$qtdeTotalTitulos.'</td><td>'.$valorTotalTitulos.'</td><td>'.$tarifasTotalTitulos.'</td>';
		echo '</tr>';
		echo '</tbody>';
		echo '</table>';

	}

	function getRemAtServer($ftp_con, $rem, $remOrig) {
		$listFiles = ftp_nlist($ftp_con, './clientes/remessas');
		$listFilesRaw = ftp_rawlist($ftp_con, './clientes/remessas');
		
		if ( count($listFiles) ) {
			$infoClientsForRem = array();
			for ( $i = 0; $i < count($listFiles) ; $i++ ) {
				$expFile = explode('/', $listFiles[$i]);
				if ( $expFile[count($expFile)-1] != 'Recebidas na Setydeias' ) {
					//Se copiar o arquivo do SVN para a pasta local e para a pasta de arquivos de remessa originais
					if ( ftp_get($ftp_con, $rem.$expFile[count($expFile)-1], '\\clientes\\remessas\\'.$expFile[count($expFile)-1], FTP_BINARY ) &&
					   ftp_get($ftp_con, $remOrig.$expFile[count($expFile)-1], '\\clientes\\remessas\\'.$expFile[count($expFile)-1], FTP_BINARY) ) {
						//Retorna o tamanho do arquivo remoto e local, respectivamente
						//Caso queira converter em KB: divide o resultado da variável por 1024 e coloca dentro da função round
						$fileRemoteSize = ftp_size($ftp_con, $listFiles[$i]);
						$fileLocalSize  = filesize($remOrig.$expFile[count($expFile)-1]);
						if ( $fileRemoteSize == $fileLocalSize ) {
							//Exclui o arquivo original do SVN
							//ftp_delete($ftp_con, '\\clientes\\remessas\\'.$expFile[count($expFile)-1]);
						} else {
							echo '<script>alert("Arquivo não foi deletado!");</script>';
						}
						$infoClientsForRem[] = takeSTR($listFilesRaw[$i]);
					}
				}
			}
			return $infoClientsForRem;
		}
	}

	/*======================================= PHPEXCEL Functions ===========================================================*/

	function getFmtedDate($dataTransf, $dataEvent = null) {
		if ( is_null($dataEvent) ) :
			//Month date
	 		$m_day   = substr($dataTransf, 0, 2);
			$m_month = substr($dataTransf, 3, 2);
			$m_year  = substr($dataTransf, 6, 4);
			$m_date  = PHPExcel_Shared_Date::FormattedPHPToExcel($m_year, $m_month, $m_day);
			//Setting color
			$assets = array($m_date);
		else :
			//Transf date
	 		$t_day   = substr($dataTransf, 0, 2);
			$t_month = substr($dataTransf, 3, 2);
			$t_year  = substr($dataTransf, 6, 4);
			$t_date  = PHPExcel_Shared_Date::FormattedPHPToExcel($t_year, $t_month, $t_day);
	 		//Payment date
	 		$p_day   = substr($dataEvent, 0, 2);
			$p_month = substr($dataEvent, 3, 2);
			$p_year  = substr($dataEvent, 6, 4);
			$p_date  = PHPExcel_Shared_Date::FormattedPHPToExcel($p_year, $p_month, $p_day);
			//Formatted dates
	 		$assets = array($t_date, $p_date);
	 	endif;

	 	return $assets;
	}

	//For BB and BRD
	function getCellData($objWorksheet, $cc, $t_date, $p_date, $valorTotal, $valores, $tarifa, $type, $mens, $isenc, $sub_trib, $dados_mensalidade, $bank, $ag, array $arrayHasPayment, $sigla) {
		//Colors
		$blue = array( 'font' => array( 'color' => array( 'rgb' => '0000FF') ) );
	 	$purple = array( 'font' => array( 'color' => array( 'rgb' => '7F007F') ) );
		$yellow = array( 'font' => array( 'color' => array( 'rgb' => '4E5E0B') ) );
		$green = array( 'font' => array( 'color' => array( 'rgb' => '133002') ) );
		
	 	switch ($type) :
			case 'mensalidade':
				$tipo_mensalidade = $dados_mensalidade['tipo_mensalidade'];
				$mensalidade = $dados_mensalidade['mensalidade'];
				$customer_obj = new Customer();
		 		//Aux var to count how much rows was adds
		 		$lines = 1;
		 		//Isenção (linhas)
		 		if ( $isenc == 1 ) $lines += 1;
		 		//Substituto Tributário (linhas)
				if ( $sub_trib == 1 ) $lines += 1;
				//Verifica se o cliente é repasse
				$isRepasse = $customer_obj->IsRepasse($sigla);
				$isentoDebitoAutomatico = $customer_obj->isentoDebitoAutomatico($sigla);
				$taxes = $customer_obj->getPrintDeliveryTaxes();
				$shouldPayDebitoAutomatico = !$isRepasse && !$isentoDebitoAutomatico;
				$lines += $shouldPayDebitoAutomatico ? 1 : 0;
		 		$objWorksheet->insertNewRowBefore($cc, $lines);
				//First line
		 		$objWorksheet->setCellValue('A'.$cc, '=IF(B'.$cc.'<>"",TEXT(B'.$cc.',"DDD"),"")')
		 		->setCellValue('B'.$cc, $t_date)
		 		->setCellValue('D'.$cc, 'Pgto serv prestados mes anterior')
		 		->setCellValue('F'.$cc, $tipo_mensalidade === '1' ? '='.$mens.'*'.$mensalidade.'%' : '='.$mensalidade)
		 		->setCellValue('G'.$cc, 'D');
		 		//Formatting first line cells
		 		$objWorksheet->getStyle('D'.$cc)->applyFromArray($yellow);
		 		$objWorksheet->getStyle('F'.$cc)->applyFromArray($yellow);
		 		$objWorksheet->getStyle('G'.$cc)->applyFromArray($yellow);
		 		//Isenção (writing rows)
		 		if ( $isenc == 1 ) :
		 			$objWorksheet->setCellValue('A'.($cc+1), '=IF(B'.($cc+1).'<>"",TEXT(B'.($cc+1).',"DDD"),"")')
			 		->setCellValue('B'.($cc+1), $t_date)
			 		->setCellValue('D'.($cc+1), 'Compensação por parceria')
			 		->setCellValue('F'.($cc+1), $tipo_mensalidade === '1' ? '='.$mens.'*'.$mensalidade.'%' : '='.$mensalidade)
			 		->setCellValue('G'.($cc+1), 'C');
			 		//Formatting first line cells
			 		$objWorksheet->getStyle('D'.($cc+1))->applyFromArray($yellow);
			 		$objWorksheet->getStyle('F'.($cc+1))->applyFromArray($yellow);
			 		$objWorksheet->getStyle('G'.($cc+1))->applyFromArray($yellow);
		 		endif;
		 		//Substituto Tributário (writing rows)
		 		if ( $sub_trib == 1 ) :
		 			$objWorksheet->setCellValue('A'.($cc+1), '=IF(B'.($cc+1).'<>"",TEXT(B'.($cc+1).',"DDD"),"")')
			 		->setCellValue('B'.($cc+1), $t_date)
			 		->setCellValue('D'.($cc+1), 'ISS Retido (2%) - Substituto Tributário')
			 		->setCellValue('F'.($cc+1), $tipo_mensalidade === '1' ? '='.$mens.'*'.($mensalidade/100).'*'.(2/100) : '='.$mensalidade.'*'.(2/100))
			 		->setCellValue('G'.($cc+1), 'C');
			 		//Formatting first line cells
			 		$objWorksheet->getStyle('D'.($cc+1))->applyFromArray($yellow);
			 		$objWorksheet->getStyle('F'.($cc+1))->applyFromArray($yellow);
			 		$objWorksheet->getStyle('G'.($cc+1))->applyFromArray($yellow);
				endif;
				//Débito em conta
				if ( $shouldPayDebitoAutomatico ) {
					$objWorksheet->setCellValue('A'.($cc+1), '=IF(B'.($cc+1).'<>"",TEXT(B'.($cc+1).',"DDD"),"")')
			 		->setCellValue('B'.($cc+1), $t_date)
					->setCellValue('D'.($cc+1), 'Tarifa de Débito em Conta')
					->setCellValue('E'.($cc+1), '1')
			 		->setCellValue('F'.($cc+1), $taxes['DEBITO_CONTA'])
			 		->setCellValue('G'.($cc+1), 'D');
			 		//Formatting first line cells
					 $objWorksheet->getStyle('D'.($cc+1))->applyFromArray($yellow);
					 $objWorksheet->getStyle('E'.($cc+1))->applyFromArray($yellow);
					 $objWorksheet->getStyle('F'.($cc+1))->applyFromArray($yellow);
					 $objWorksheet->getStyle('G'.($cc+1))->applyFromArray($yellow);	 
				}
		 		//Last column
		 		$firstStr = strstr($objWorksheet->getCell('AC'.($cc-1))->getValue(), 'Transf');
		 		$finalStr = substr($firstStr, 0, strpos($firstStr, '",$'));
		 	break;
			case 'processamento':
				$valores = ( gettype($valores) == 'array' ) ? count($valores) : $valores;
				//Verifica se existe algum pagamento nos bancos processados anteriormente
				//Se houver, adiciona apenas mais 2 linhas, se não, adiciona 3
				$linhas_add = 3;
				if ( count($arrayHasPayment) > 0 && in_array($sigla, $arrayHasPayment) ) $linhas_add = 2;
		 		//Adding more rows
		 		$lines = $linhas_add;
		 		$objWorksheet->insertNewRowBefore($cc+1, $lines);
		 		//Inserting the title of the movement according the bank
		 		switch ( $bank ) {
		 			case 'bb':
		 				$arrayData = array(
		 					array('Boletos recebidos via Banco do Brasil'),
		 					array('Tarifas de recebimentos BB')
		 				);

		 				$objWorksheet->fromArray($arrayData, NULL, 'D'.$cc);
		 				break;
		 			case 'brd':
		 				$arrayData = array(
		 					array('Boletos recebidos via Bradesco'),
		 					array('Tarifas de recebimentos BRD')
		 				);

		 				$objWorksheet->fromArray($arrayData, NULL, 'D'.$cc);
		 				break;
		 			case 'cef':
		 				switch ($ag) :
		 					case '1559':
		 						$arrayData = array(
				 					array('Boletos recebidos via Caixa Econômica 1559'),
				 					array('Tarifas de recebimentos CEF 1559')
				 				);

				 				$objWorksheet->fromArray($arrayData, NULL, 'D'.$cc);
		 						break;
		 					case '1563':
		 						$arrayData = array(
				 					array('Boletos recebidos via Caixa Econômica 1563'),
				 					array('Tarifas de recebimentos CEF 1563')
				 				);

				 				$objWorksheet->fromArray($arrayData, NULL, 'D'.$cc);
		 						break;
		 					default:
		 						$arrayData = array(
				 					array('Boletos recebidos via Caixa Econômica'),
				 					array('Tarifas de recebimentos CEF')
				 				);

				 				$objWorksheet->fromArray($arrayData, NULL, 'D'.$cc);
		 						break;
		 				endswitch;
		 				break;
		 		}
				//First line
		 		$arrayData = array(
 					array('=IF(B'.$cc.'<>"",TEXT(B'.$cc.',"DDD"),"")', $t_date, $p_date, NULL, NULL, fmtValue($valorTotal), 'C'),
 					array('=IF(B'.($cc+1).'<>"",TEXT(B'.($cc+1).',"DDD"),"")', $t_date, $p_date, NULL, $valores, $tarifa, 'D')
 				);

 				$objWorksheet->fromArray($arrayData, NULL, 'A'.$cc);
		 		//Formatting cells
		 		$objWorksheet->getStyle('D'.$cc)->applyFromArray($blue);
		 		$objWorksheet->getStyle('F'.$cc)->applyFromArray($blue);
		 		$objWorksheet->getStyle('G'.$cc)->applyFromArray($blue);
		 		$objWorksheet->getStyle('D'.($cc+1))->applyFromArray($purple);
		 		$objWorksheet->getStyle('E'.($cc+1))->applyFromArray($purple);
		 		$objWorksheet->getStyle('F'.($cc+1))->applyFromArray($purple);
		 		$objWorksheet->getStyle('G'.($cc+1))->applyFromArray($purple);
			break;
			case 'remessa':
				$lines = 1;
				$objWorksheet->insertNewRowBefore($cc+1, $lines);

				$arrayData = array(array('=IF(B'.$cc.'<>"",TEXT(B'.$cc.',"DDD"),"")', $t_date, $p_date, $mens, $valorTotal, $tarifa, 'D'));
				$objWorksheet->fromArray($arrayData, NULL, 'A'.$cc);

				//Formatting cells
				$objWorksheet->getStyle('D'.$cc)->applyFromArray($green);
				$objWorksheet->getStyle('E'.$cc)->applyFromArray($green);
				$objWorksheet->getStyle('F'.$cc)->applyFromArray($green);
				$objWorksheet->getStyle('G'.$cc)->applyFromArray($green);
			break;
		endswitch;
 		//Include the formulas
 		$arrayData = array(); 
 		for ( $r = 0; $r <= ($lines+1); $r++ ) {

 			//String de transferência
 			$firstStr = strstr($objWorksheet->getCell('AC'.($cc-1))->getValue(), 'Transf');
			$finalStr = substr($firstStr, 0, strpos($firstStr, '",$'));
			$finalStr != "" ? $finalStr : $finalStr = substr($firstStr, 0, strpos($firstStr, '";$'));

 			$arrayData[] = array(
 				$type == 'remessa' && $valores == 'unico' ? '=IF(D'.($cc+$r).'="","",F'.($cc+$r).')' : '=IF(D'.($cc+$r).'="","",L'.($cc+$r).'*F'.($cc+$r).')',
				'=IF(D'.($cc+$r).'="","",IF(C'.($cc+$r).'<=TODAY(),IF(G'.($cc+$r).'="D","D","C"),"*"))',
				'=IF(D'.($cc+$r).'="","",ABS(M'.($cc+$r).'))',
				'=IF(D'.($cc+$r).'="","",IF(M'.($cc+$r).'>=0,"C","D"))',
				'=IF(E'.($cc+$r).'=0,1,E'.($cc+$r).')',
				'=IF(D'.($cc+$r).'="",0,M'.(($cc+$r)-1).'+H'.($cc+$r).'*IF(I'.($cc+$r).'="D",-1,1)*IF(C'.($cc+$r).'<=TODAY(),1,0))',
				'=(IF(I'.($cc+$r).'="C",H'.($cc+$r).',0)+IF(I'.($cc+$r).'="D",-1*H'.($cc+$r).'))*(IF(C'.($cc+$r).'>TODAY(),0,1))',
				'=(IF(G'.($cc+$r).'="C",H'.($cc+$r).',0)+IF(G'.($cc+$r).'="D",-1*H'.($cc+$r).'))*(IF(C'.($cc+$r).'>TODAY(),1,0))',
				NULL,
				'=IF($D'.($cc+$r).'="Tarifas de recebimentos BB";$H'.($cc+$r).';0)', 
				'=ROUND(IF(Q'.($cc+$r).'>0;E'.($cc+$r).';0);0)',
				'=IF($D'.($cc+$r).'="Boletos recebidos via Banco do Brasil";$H'.($cc+$r).';0)',
				'=IF($D'.($cc+$r).'="Tarifas de recebimentos CEF 1559";$H'.($cc+$r).';0)',
				'=ROUND(IF(T'.($cc+$r).'>0;E'.($cc+$r).';0);0)',
				'=IF($D'.($cc+$r).'="Boletos recebidos via Caixa Econômica 1559";$H'.($cc+$r).';0)',
				'=IF($D'.($cc+$r).'="Tarifas de recebimentos CEF 1563";$H'.($cc+$r).';0)',
				'=ROUND(IF(W'.($cc+$r).'>0;E'.($cc+$r).';0);0)',
				'=IF($D'.($cc+$r).'="Boletos recebidos via Caixa Econômica 1563";$H'.($cc+$r).';0)',
				'=IF($D'.($cc+$r).'="Tarifas de recebimentos BRD";$H'.($cc+$r).';0)',
				'=ROUND(IF(Z'.($cc+$r).'>0;$E'.($cc+$r).';0);0)',
				'=IF($D'.($cc+$r).'="Boletos recebidos via Bradesco";$H'.($cc+$r).';0)',
				'=IF($D'.($cc+$r).'="'.$finalStr.'";$H'.($cc+$r).';0)',
				NULL,
				'=IF(C'.($cc+$r).'="";"";MONTH(C'.($cc+$r).'))',
				'=Q'.($cc+$r).'+T'.($cc+$r).'+W'.($cc+$r).'+Z'.($cc+$r),
				'=R'.($cc+$r).'+U'.($cc+$r).'+X'.($cc+$r).'+AA'.($cc+$r),
				'=S'.($cc+$r).'+V'.($cc+$r).'+Y'.($cc+$r).'+AB'.($cc+$r)
 			);
	 	}

	 	$objWorksheet->fromArray($arrayData, NULL, 'H'.$cc);
	}

	function addTransfString(array $clients, $dataT, $dataE, $duplicidades = null) {
		//Color
		$red = array( 'font' => array( 'color' => array( 'rgb' => 'FF0000') ) );
		$yellow = array( 'font' => array( 'color' => array( 'rgb' => '4E5E0B') ) );
		$blue = array( 'font' => array( 'color' => array( 'rgb' => '0000FF') ) );
		//Formatting date
		//Transf date
 		$t_day   = substr($dataT, 0, 2);
		$t_month = substr($dataT, 3, 2);
		$t_year  = substr($dataT, 6, 4);
		$t_date  = PHPExcel_Shared_Date::FormattedPHPToExcel($t_year, $t_month, $t_day);
		//Loop for write ct
		for ( $i = 0; $i < count($clients); $i++ ) {
			$accTrs = "\\contatransitoria\\".strtolower($clients[$i])."\\index-".$clients[$i].".xls";
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
					 		//Add lines
					 		if ( in_array($clients[$i], $duplicidades) ) {
						 		$lines = 1;
			 					$objWorksheet->insertNewRowBefore($cc+2, $lines);
			 				}
					 		//Transf string text
					 		$firstStr = strstr($objWorksheet->getCell('AC'.($cc-1))->getValue(), 'Transf');
		 					$finalStr = substr($firstStr, 0, strpos($firstStr, '",$'));
							//Escrever string de transferência na conta transitória
							//Se existir duplicidades, então escreve uma linha acima da string de transferência
							if ( in_array($clients[$i], $duplicidades) && count($duplicidades) > 0 ) {
						 		$arrayData = array(
						 			array('=IF(B'.$cc.'<>"";TEXT(B'.$cc.';"DDD");"")', $t_date, NULL, 'HOUVE PAGAMENTOS EM DUPLICIDADE'),
									array('=IF(B'.$cc.'<>"";TEXT(B'.$cc.';"DDD");"")', $t_date, NULL, $finalStr, NULL, '=IF($K$3=104,IF(M'.($cc-1).'<10,0,J'.($cc-1).'),IF(M'.($cc-1).'<=0,0,J'.($cc-1).'))', 'D')
						 			//array('=IF(B'.$cc.'<>"";TEXT(B'.$cc.';"DDD");"")', $t_date, NULL, $finalStr, NULL, '=IF(M'.($cc-1).'<=10;0;J'.($cc-1).')', 'D')
						 		);

						 		$objWorksheet->fromArray($arrayData, NULL, 'A'.$cc);
						 		//Hyperlink
						 		$objWorksheet->getCell('D'.$cc)->setDataType(PHPExcel_Cell_DataType::TYPE_STRING2);
								$url = 'http://setydeias.com/contatransitoria/relatorios/'.Util::FmtDate($dataE, '7').'/DUPLICIDADES_'.Util::FmtDate($dataE, '6').'_'.strtoupper($clients[$i]).'.pdf';
						 		$objWorksheet->getCell('D'.$cc)->getHyperlink()->setUrl($url);
						 		//Formatting cells colors
								$objWorksheet->getStyle('D'.$cc)->applyFromArray($blue);
								$objWorksheet->getStyle('D'.$cc)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('EBE715');
						 		$objWorksheet->getStyle('D'.($cc+1))->applyFromArray($red);
						 		$objWorksheet->getStyle('F'.($cc+1))->applyFromArray($red);
						 		$objWorksheet->getStyle('G'.($cc+1))->applyFromArray($red);
						 	} else {
						 		$arrayData = array(array('=IF(B'.$cc.'<>"";TEXT(B'.$cc.';"DDD");"")', $t_date, NULL, $finalStr, NULL, '=IF($K$3=104,IF(M'.($cc-1).'<10,0,J'.($cc-1).'),IF(M'.($cc-1).'<=0,0,J'.($cc-1).'))', 'D'));
						 		$objWorksheet->fromArray($arrayData, NULL, 'A'.$cc);
						 		//Formatting cells colors
						 		$objWorksheet->getStyle('D'.$cc)->applyFromArray($red);
						 		$objWorksheet->getStyle('F'.$cc)->applyFromArray($red);
						 		$objWorksheet->getStyle('G'.$cc)->applyFromArray($red);
						 	}
					 		//Creating the writer object
					 		$objWriter = PHPExcel_IOFactory::createWriter( $objPHPExcel, $sheet );
					 		$objWriter->setPreCalculateFormulas(false);
							$objWriter->save($accTrs);
					 		break;
						}
					}
				}
			} else {
				createAlert('danger', '['.$clients[$i].'] Conta transitória não foi encontrada');
			}
		}
	}

	//The follow functions are dependents

	function getInformations($sigla, $num) {
		$cli      = "";
		$num = explode('-', $num);
		if (substr($num[0], 2, 6) == '264151') :
			$database = $sigla.str_pad(substr($num[0], 8, 3), 5, 0, STR_PAD_LEFT);
		else :	
			$database = $sigla.str_pad(substr($num[0], 7, 3), 5, 0, STR_PAD_LEFT);
		endif;
		$con = ibase_connect('localhost:C:\\Setydeias\\Setyware\\ADM77777\\ADM77777.GDB', 'SYSDBA', 'masterkey');
        $query = ibase_query($con, "SELECT sv.IP_SERVER FROM SERVIDOR_NUVENS_PARAMS sv");
        $ip_server = ibase_fetch_object($query)->IP_SERVER;
        ibase_close($con);
		//Acessa o arquivo atualizarRemoto.php que está no servidor nas nuvens
		//Descompacta o banco de dados que está zipado, se o mesmo existir
		$ch = curl_init(); //Inicia a biblioteca cURL
		//$req = curl_setopt($ch, CURLOPT_URL, "187.45.218.234:7777/app/2via/sistema/atualizarRemoto.php?siglacodigo=$database"); //Faz a requisição
		$req = curl_setopt($ch, CURLOPT_URL, "$ip_server/app/2via/sistema/atualizarRemoto.php?siglacodigo=$database"); //Faz a requisição
		curl_exec($ch); //Executa a requisição
		curl_close($ch); //Encerra o uso da biblioteca
		//Acessa o banco
		//$dbh = ibase_connect('187.45.218.234:C:\ServidorWeb\xampp\htdocs\app\2via\clientes\\'.$database.'\\'.$database.'.gdb', 'SYSDBA', 'masterkey') or die(ibase_errmsg());
		$dbh = ibase_connect($ip_server.':E:\ServidorWeb\xampp\htdocs\app\2via\clientes\\'.$database.'\\'.$database.'.gdb', 'SYSDBA', 'masterkey') or die(ibase_errmsg());
		
		$result = ibase_query("SELECT s.NOMSAC FROM SACADOS s INNER JOIN TITULOS t ON s.CODSAC = t.CODSAC WHERE t.NOSSONUM = '$num[0]'");

		while ( $row = ibase_fetch_object($result) ) :
			$cli = utf8_encode($row->NOMSAC);
			$data[] = $sigla;
			$data[] = $cli.'-'.$num[1].'-'.$num[2];
		endwhile;

		return $data;
	}

	function getClientOurNumber($info, $dT, $dE) {
		$toWrite = array();
		//Info has two informations
		//One is client, the second is the our_number
		for ( $i = 0, $arrLen = count($info); $i < $arrLen; $i++ ) :
			//If $i is couple
			if ( $i % 2 == 0 ) :
				$toWrite[] = getInformations($info[$i], $info[$i+1]);
			endif;
		endfor;
		
		if ( count($toWrite) > 0 ) :
			for ( $i = 0; $i < count($toWrite); $i++ ) :
				for ( $j = 0; $j < count($toWrite[$i]); $j++ ) :
					if ( $j == 0 ) :
						$client  = $toWrite[$i][$j];
						$inform  = explode('-', $toWrite[$i][$j+1]); //Nome e valor
						$pagador = $inform[0];
						$valor   = $inform[1];
						$credDt  = $inform[2];
						//Carregando conta transitória
						$accTrs  = "..\\..\\..\\contatransitoria\\".strtolower($client)."\\index-".$client.".xls";
						if ( file_exists($accTrs) ) :
							$objPHPExcel = PHPExcel_IOFactory::load($accTrs);
							//Loop for find info
							foreach ( $objPHPExcel->getWorksheetIterator() as $worksheet ) :
								$highestRow = $worksheet->getHighestRow();
								//Loop in all cells of the sheet
								for ( $cc = 11; $cc < $highestRow; $cc++ ) :
									if ( $worksheet->getCellByColumnAndRow(1, $cc)->getValue() == "" ) :
								 		$objWorksheet = $objPHPExcel->getActiveSheet();
								 		//Formatting data
								 		$t_day   = substr($dT, 0, 2);
										$t_month = substr($dT, 3, 2);
										$t_year  = substr($dT, 6, 4);
										$t_date  = PHPExcel_Shared_Date::FormattedPHPToExcel($t_year, $t_month, $t_day);
										$e_day   = substr($credDt, 0, 2);
										$e_month = substr($credDt, 3, 2);
										$e_year  = substr($credDt, 6, 4);
										$e_date  = PHPExcel_Shared_Date::FormattedPHPToExcel($e_year, $e_month, $e_day);
										//Inserting lines
										$objWorksheet->insertNewRowBefore($cc, 2);
								 		//Writing
								 		//Line 1
								 		$objWorksheet->setCellValue('A'.$cc, '=IF(B'.$cc.'<>"",TEXT(B'.$cc.',"DDD"),"")');
								 		$objWorksheet->getCell('B'.$cc)->setValue( $t_date );
								 		$objWorksheet->getCell('C'.$cc)->setValue( $e_date );
								 		$objWorksheet->getCell('D'.$cc)->setValue( 'TÍTULO PAGO EM CHEQUE NO DIA '.$dE );
								 		$objWorksheet->setCellValue('F'.$cc, $valor);
								 		$objWorksheet->getCell('G'.$cc)->setValue( 'C' );
								 		//Line 2
								 		$objWorksheet->setCellValue('A'.($cc+1), '=IF(B'.($cc+1).'<>"",TEXT(B'.($cc+1).',"DDD"),"")');
								 		$objWorksheet->getCell('B'.($cc+1))->setValue( $t_date );
								 		$objWorksheet->getCell('C'.($cc+1))->setValue( $e_date );
								 		$objWorksheet->getCell('D'.($cc+1))->setValue( 'PAGADOR: ' . $pagador );
								 		//Formatting colors
								 		$phpColor = new PHPExcel_Style_Color();
								 		$phpColor->setRGB('232324');
								 		$objWorksheet->getStyle('D'.$cc)->getFont()->setColor( $phpColor );
								 		$objWorksheet->getStyle('E'.$cc)->getFont()->setColor( $phpColor );
								 		$objWorksheet->getStyle('F'.$cc)->getFont()->setColor( $phpColor );
								 		$objWorksheet->getStyle('G'.$cc)->getFont()->setColor( $phpColor );
								 		$objWorksheet->getStyle('D'.($cc + 1))->getFont()->setColor( $phpColor );
								 		//Transf string text
								 		$firstStr = strstr($objWorksheet->getCell('Z'.($cc-1))->getValue(), 'Transf');
					 					$finalStr = substr($firstStr, 0, strpos($firstStr, '",$'));
								 		//Include the formulas
								 		for ( $r = 0; $r <= 2; $r++ ) :
									 		$objWorksheet->setCellValue('H'.($cc+$r),  '=IF(D'.($cc+$r).'="","",L'.($cc+$r).'*F'.($cc+$r).')');
									 		$objWorksheet->setCellValue('I'.($cc+$r),  '=IF(D'.($cc+$r).'="","",IF(C'.($cc+$r).'<=TODAY(),IF(G'.($cc+$r).'="D","D","C"),"*"))');
									 		$objWorksheet->setCellValue('J'.($cc+$r),  '=IF(D'.($cc+$r).'="","",ABS(M'.($cc+$r).'))');
									 		$objWorksheet->setCellValue('K'.($cc+$r),  '=IF(D'.($cc+$r).'="","",IF(M'.($cc+$r).'>=0,"C","D"))');
									 		$objWorksheet->setCellValue('L'.($cc+$r),  '=IF(E'.($cc+$r).'=0,1,E'.($cc+$r).')');
									 		$objWorksheet->setCellValue('M'.($cc+$r),  '=IF(D'.($cc+$r).'="",0,M'.(($cc+$r)-1).'+H'.($cc+$r).'*IF(I'.($cc+$r).'="D",-1,1)*IF(C'.($cc+$r).'<=TODAY(),1,0))');
									 		$objWorksheet->setCellValue('N'.($cc+$r),  '=(IF(I'.($cc+$r).'="C",H'.($cc+$r).',0)+IF(I'.($cc+$r).'="D",-1*H'.($cc+$r).'))*(IF(C'.($cc+$r).'>TODAY(),0,1))');
									 		$objWorksheet->setCellValue('O'.($cc+$r),  '=(IF(G'.($cc+$r).'="C",H'.($cc+$r).',0)+IF(G'.($cc+$r).'="D",-1*H'.($cc+$r).'))*(IF(C'.($cc+$r).'>TODAY(),1,0))');
									 		$objWorksheet->setCellValue('Q'.($cc+$r),  '=IF($D'.($cc+$r).'="Tarifas de recebimentos BB";$H'.($cc+$r).';0)');
									 		$objWorksheet->setCellValue('R'.($cc+$r),  '=ROUND(IF(Q'.($cc+$r).'>0;E'.($cc+$r).';0);0)');
									 		$objWorksheet->setCellValue('S'.($cc+$r),  '=IF($D'.($cc+$r).'="Boletos recebidos via Banco do Brasil";$H'.($cc+$r).';0)');
									 		$objWorksheet->setCellValue('T'.($cc+$r),  '=IF($D'.($cc+$r).'="Tarifas de recebimentos CEF";$H'.($cc+$r).';0)');
									 		$objWorksheet->setCellValue('U'.($cc+$r),  '=ROUND(IF(T'.($cc+$r).'>0;E'.($cc+$r).';0);0)');
									 		$objWorksheet->setCellValue('V'.($cc+$r),  '=IF($D'.($cc+$r).'="Boletos recebidos via Caixa Econômica";$H'.($cc+$r).';0)');
									 		$objWorksheet->setCellValue('W'.($cc+$r),  '=IF($D'.($cc+$r).'="Tarifas de recebimentos BRD";$H'.($cc+$r).';0)');
									 		$objWorksheet->setCellValue('X'.($cc+$r),  '=ROUND(IF(W'.($cc+$r).'>0;$E'.($cc+$r).';0);0)');
									 		$objWorksheet->setCellValue('Y'.($cc+$r),  '=IF($D'.($cc+$r).'="Boletos recebidos via Bradesco";$H'.($cc+$r).';0)');
									 		$objWorksheet->setCellValue('Z'.($cc+$r),  '=IF($D'.($cc+$r).'="'.$finalStr.'";$H'.($cc+$r).';0)');
									 		$objWorksheet->setCellValue('AB'.($cc+$r), '=IF(C'.($cc+$r).'="";"";MONTH(C'.($cc+$r).'))');
									 		$objWorksheet->setCellValue('AC'.($cc+$r), '=Q'.($cc+$r).'+T'.($cc+$r).'+W'.($cc+$r));
									 		$objWorksheet->setCellValue('AD'.($cc+$r), '=R'.($cc+$r).'+U'.($cc+$r).'+X'.($cc+$r));
									 		$objWorksheet->setCellValue('AE'.($cc+$r), '=S'.($cc+$r).'+V'.($cc+$r).'+Y'.($cc+$r));
									 	endfor;
								 		//Creating the writer object
								 		$objWriter = PHPExcel_IOFactory::createWriter( $objPHPExcel, 'Excel5' );
								 		$objWriter->setPreCalculateFormulas(false);
										$objWriter->save($accTrs);
										break;
								 	endif;
								endfor;
							endforeach;
						endif;
					endif;
				endfor;
			endfor;
		endif;
	}

	//======================================= FUNÇÕES DE REMESSA REGISTRADA 

	function getRemNumber($con) {
		$stmt = $con->prepare("SELECT * FROM remessa_registrada");
		$stmt->execute();

		if ( $stmt->rowCount() > 0 ) :
			while ( $row = $stmt->fetch(PDO::FETCH_OBJ) ) :
				$bb = $row->remessa_bb;
				$brd = $row->remessa_brd;
				$cef = $row->remessa_cef;
			endwhile;

			return array($bb, $brd, $cef);
		endif;

	}

	//======================================= FUNÇÕES DE PROCESSAMENTO DE RETORNOS DUPLICADOS

	function getPagadorByOurNumber($cli, $number, $encode = null) {
		$str_conn = "firebird:dbname=localhost:D:\\Laboratorio - Setyware\\Setydeias\\Setyware\\".$cli."\\".$cli.".gdb;host=localhost";
		try {
			$conx = new PDO($str_conn, "SYSDBA", "masterkey");
			$conx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			if ($conx) :
				$stmt = $conx->prepare("SELECT s.NOMTITSAC FROM SACADOS s INNER JOIN TITULOS t ON t.CODSAC = s.CODSAC WHERE t.NOSSONUM = '$number'");
				$stmt->execute();

				$nomeCliente = 'NAO ENCONTRADO';
				while ($row = $stmt->fetch(PDO::FETCH_OBJ)) :
					$nomeCliente = $row->NOMTITSAC;
				endwhile;

				$conx = null;
				if (!is_null($encode)) :
					return utf8_decode($nomeCliente);
				else :
					return utf8_encode($nomeCliente);
				endif;
			endif;
		} catch (PDOException $e) {
			$nomeCliente = 'NÃO ENCONTRADO';
			return $nomeCliente;
		}
	}

	function sendErrorMessage($executed, $error, $typeError) {
		$params = array(
			'executed' => $executed,
			'error' => $error,
			'typeError' => $typeError
		);
		echo json_encode($params);
		return false;
	}

?>