<?php

	class Util {

		/*
		* Verifica se o documento é válido
		* CPF/CNPJ
		*/
		public static function ValidarDocumento($tipo, $doc) {

			switch ($tipo) :
				case '1':
					$doc = substr($doc, 4);
					if (!self::ValidarCPF($doc)) :
						return false;
					endif;
				break;
				case '2':
					$doc = substr($doc, 1);
					if (!self::ValidarCNPJ($doc)) :
						return false;
					endif;
				break;
			endswitch;

			return true;
		}

		//Validação de CPF
		public static function ValidarCPF($cpf = null) {
		    //Elimina possivel mascara
		    $cpf = preg_replace('[^0-9]', '', $cpf);
		    $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
		    //Verifica se o numero de digitos informados é igual a 11 
		    if (strlen($cpf) != 11) :
		        return false;
		    //Verifica se nenhuma das sequências invalidas abaixo 
		    //foi digitada. Caso afirmativo, retorna falso
		    elseif 
		       ($cpf == '00000000000' || 
		        $cpf == '11111111111' || 
		        $cpf == '22222222222' || 
		        $cpf == '33333333333' || 
		        $cpf == '44444444444' || 
		        $cpf == '55555555555' || 
		        $cpf == '66666666666' || 
		        $cpf == '77777777777' || 
		        $cpf == '88888888888' || 
		        $cpf == '99999999999') :
		        return false;
		    //Calcula os digitos verificadores para verificar se o
		    //CPF é válido
		    else :
		        for ($t = 9; $t < 11; $t++):
		             
		            for ($d = 0, $c = 0; $c < $t; $c++) :
		                $d += $cpf{$c} * (($t + 1) - $c);
		            endfor;
		            $d = ((10 * $d) % 11) % 10;
		            if ($cpf{$c} != $d) :
		                return false;
		            endif;
		        endfor;
		 
		        return true;
		    endif;
		}

		//Validação de CNPJ
		public static function ValidarCNPJ($cnpj = null) {
			//Elimina possivel mascara
			$cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);
			// Valida tamanho
			if (strlen($cnpj) != 14) :
				return false;
			endif;
			// Valida primeiro dígito verificador
			for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) :
				$soma += $cnpj{$i} * $j;
				$j = ($j == 2) ? 9 : $j - 1;
			endfor;
			$resto = $soma % 11;
			if ($cnpj{12} != ($resto < 2 ? 0 : 11 - $resto)) :
				return false;
			endif;
			// Valida segundo dígito verificador
			for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) :
				$soma += $cnpj{$i} * $j;
				$j = ($j == 2) ? 9 : $j - 1;
			endfor;
			$resto = $soma % 11;
			return $cnpj{13} == ($resto < 2 ? 0 : 11 - $resto);
		}

		//Retorna a quantidade de dias do mês anterior
		public static function GetQntMonthsDays() {
			$MonthsDays = array(31, date('Y') % 4 === 0 ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
			$indice = (int) date('m') == 1 ? 11 : (int) date('m') - 2;
			$dias_mes_anterior = $MonthsDays[$indice];

			switch ( $dias_mes_anterior ) {
				case '31':
					$dateRef = date('D') != 'Sat' ? date('Y-m-d', strtotime('-29 days', strtotime(date('Y-m-d')))) : date('Y-m-d', strtotime('-28 days', strtotime(date('Y-m-d'))));
				break;
				case '30':
					$dateRef = date('D') != 'Sat' ? date('Y-m-d', strtotime('-28 days', strtotime(date('Y-m-d')))) : date('Y-m-d', strtotime('-27 days', strtotime(date('Y-m-d'))));
				break;
				case '28':
					$dateRef = date('D') != 'Sat' ? date('Y-m-d', strtotime('-26 days', strtotime(date('Y-m-d')))) : date('Y-m-d', strtotime('-25 days', strtotime(date('Y-m-d'))));
				break;
				case '29':
					$dateRef = date('D') != 'Sat' ? date('Y-m-d', strtotime('-27 days', strtotime(date('Y-m-d')))) : date('Y-m-d', strtotime('-26 days', strtotime(date('Y-m-d'))));
				break;
			}

			return $dateRef;
		}

		public static function RemoverAcentos($string) {
			return preg_replace('/[`´^~\'"]/', null, iconv('UTF-8', 'ASCII//TRANSLIT', $string));
		}

		//Formatação de data
		public static function FmtDate($date, $type_date) {
			switch ($type_date) :
				//281116 to 28112016
				case '1':
					$d = substr($date, 0, 2);
					$m = substr($date, 2, 2);
					$y = "20".substr($date, 4, 2);

					$date = $d.$m.$y;
				break;
				//20161128 to 161128
				case '2':
					$d = substr($date, 6, 2);
					$m = substr($date, 4, 2);
					$y = substr($date, 2, 2);

					$date = $y.$m.$d;
				break;
				//28112016 to 161128
				case '3':
					$d = substr($date, 0, 2);
					$m = substr($date, 2, 2);
					$y = substr($date, 6, 2);

					$date = $y.$m.$d;
				break;
				//28112016 to 2016-11-28
				case '4':
					$d = substr($date, 0, 2);
					$m = substr($date, 2, 2);
					$y = substr($date, 4, 4);

					$date = $y.'-'.$m.'-'.$d;
				break;
				//2016-11-28 to 28112016
				case '5':
					$date = explode('-', $date);
					$d = $date[2];
					$m = $date[1];
					$y = $date[0];

					$date = $d.$m.$y;
				break;
				//28/11/2016 to 161128
				case '6':
					$date = explode('/', $date);
					$d = $date[0];
					$m = $date[1];
					$y = substr($date[2], 2, 2);

					$date = $y.$m.$d;
				break;
				//28/11/2016 to 2016_11_28
				case '7':
					$date = explode('/', $date);
					$d = $date[0];
					$m = $date[1];
					$y = $date[2];

					$date = $y.'_'.$m.'_'.$d;
				break;
				//2016-11-28 to 161128
				case '8':
					$date = explode('-', $date);
					$d = $date[2];
					$m = $date[1];
					$y = substr($date[0], 2, 2);

					$date = $y.$m.$d;
				break;
				//2016-11-28 to 2016_11_28
				case '9':
					$date = str_replace('-', '_', $date);
				break;
				//281116 to 2016-11-28
				case '10':
					$d = substr($date, 0, 2);
					$m = substr($date, 2, 2);
					$y = '20'.substr($date, 4, 2);

					$date = "$y-$m-$d";
				break;
				//281116 to 16-11-28
				case '11':
					$d = substr($date, 0, 2);
					$m = substr($date, 2, 2);
					$y = substr($date, 4, 2);

					$date = "$y-$m-$d";
				break;
				//16-11-28 to 2016-11-28
				case '12':
					$date = "20$date";
				break;
				//281116 to 28/11/2016
				case '13':
					$d = substr($date, 0, 2);
					$m = substr($date, 2, 2);
					$y = substr($date, 4, 2);

					$date = "$d/$m/20$y";
				break;
				//161128 to 2016-11-28
				case '14':
					$y = substr($date, 0, 2);
					$m = substr($date, 2, 2);
					$d = substr($date, 4, 2);

					$date = "20$y-$m-$d";
				break;
				//161128 to 28112016
				case '15':
					$y = substr($date, 0, 2);
					$m = substr($date, 2, 2);
					$d = substr($date, 4, 2);

					$date = "{$d}{$m}20${y}";
				break;
				//28112016 to 11/28/2016
				case '16':
					$d = substr($date, 0, 2);
					$m = substr($date, 2, 2);
					$y = substr($date, 4, 4);

					$date = "{$m}/{$d}/{$y}";
				break;
				//28112016 to 28/11/2016
				case '17':
					$d = substr($date, 0, 2);
					$m = substr($date, 2, 2);
					$y = substr($date, 4, 4);

					$date = "{$d}/{$m}/{$y}";
				break;
				//281116 to 11/28/2016
				case '18':
					$d = substr($date, 0, 2);
					$m = substr($date, 2, 2);
					$y = substr($date, 4, 2);

					$date = "{$m}/{$d}/20{$y}";
				break;
				//281116 to 161128
				case '19':
					$d = substr($date, 0, 2);
					$m = substr($date, 2, 2);
					$y = substr($date, 4, 2);

					$date = "{$y}{$m}{$d}";
				break;
				//2016-11-28 to 28/11/2016
				case '20':
					$date = explode('-', $date);
					$d = $date[2];
					$m = $date[1];
					$y = $date[0];

					$date = "$d/$m/$y";
				break;
				//2016-11-28 to 28/11/16
				case '21':
					$date = explode('-', $date);
					$d = $date[2];
					$m = $date[1];
					$y = substr($date[0], 2);

					$date = "$d/$m/$y";
				break;
				//28/11/2016 to 28/11/16
				case '22':
					$date = explode('/', $date);
					$d = $date[0];
					$m = $date[1];
					$y = substr($date[2], 2);

					$date = "$d/$m/$y";
				break;
				//28/11/2016 to 2016-11-28
				case '23':
					$date = explode('/', $date);
					$d = $date[0];
					$m = $date[1];
					$y = $date[2];

					$date = "$y-$m-$d";
				break;
				//2016-11-28 to 11/2016
				case '24':
					$date = explode('-', $date);
					$m = $date[1];
					$y = $date[0];
					
					$date = "$m/$y";
				break;
				//28112016 to 2016.11.28
				case '25':
					$d = substr($date, 0, 2);
					$m = substr($date, 2, 2);
					$y = substr($date, 4, 4);

					$date = "$y.$m.$d";
				break;
				//161128 to 28/11/2016
				case '26':
					$d = substr($date, -2);
					$m = substr($date, 2, -2);
					$y = substr($date, 0, 2);

					$date = "$d/$m/20$y";
				break;
				//28/11/2016 to 2016.11.2016
				case '27':
					$date = explode('/', $date);
					$date = array_reverse($date);
					$date = join('.', $date);
				break;
				//2016-11-28 to 281116
				case '28':
					$date = explode('-', $date);
					$d = $date[2];
					$m = $date[1];
					$y = substr($date[0], 2);
					
					$date = "$d$m$y";
				break;
			endswitch;
			
			return $date;
		}

		public static function Modulo11($num, $base) {
			$length = strlen($num);
			$produto = 2;
			$sum = 0;

			for ( $i = --$length ; $i >= 0 ; $i--, $produto++ ) {
				$sum += $num[$i] * $produto;
				if ( $produto == $base ) $produto = 1;
			}

			$resto = $sum % 11;
			$result = 11 - $resto;
			return $result;
		}

		public static function mb_str_pad( $input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT) {
			$diff = strlen( $input ) - mb_strlen( $input );
			return str_pad( $input, $pad_length + $diff, $pad_string, $pad_type );
		}

		public static function getBankName($codigo_banco) {
			switch ( $codigo_banco ) {
				case '001':
					return "Banco do Brasil";
				break;
				case '104':
					return "Caixa Econômica Federal";
				break;
				case '237':
					return "Bradesco";
				break;
				case '341':
					return "Itaú";
				break;
				default:
					return "BANCO NÃO ENCONTRADO";
				break;
			}
		}

		public function alignToRight($text, $qtde) {
			return str_pad($text, $qtde, '0', STR_PAD_LEFT);
		}

		public function alignToLeft($text, $qtde) {
			return str_pad($text, $qtde, ' ', STR_PAD_RIGHT);
		}

	}