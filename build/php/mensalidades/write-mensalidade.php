<?php
	
	error_reporting(0);
	set_time_limit(0);
	include_once '../functions.php';
	include_once '../../../vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
	include_once '../../../vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php';
	include_once '../class/FirebirdCRUD.class.php';
	include_once '../class/Customer.class.php';
	include_once '../class/MailParams.class.php';
	include_once '../class/Extenso.class.php';
	include_once '../../../vendor/autoload.php';
	include_once '../class/Customer.class.php';
	use Dompdf\Dompdf;
	$crud = new FirebirdCRUD();

	try {
		$info = json_decode(file_get_contents('php://input'));
		//Parâmetros
		$data = $info->dataMensalidade;
		$writeInCT = $info->writeInCT;
		$createRecibo = $info->createRecibo;
		$sendAttach = $info->sendAttach;
		$customerList = $info->customerList;
		$customerList = "('".implode('\', \'', $customerList)."')";
		$writed_ct = $created = $ct_not_found = $not_created = $mail_send = $mail_not_send = array();
		$meses = array('Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
		
		if ( strlen($data) != 10 ) {
			echo json_encode(array('error' => "Informe uma data válida"));
			return false;
		}
		
		$customer = new Customer();
		$MailParams = new MailParams();

		//DADOS DO SACADO
		$dataToSelect = array(
			'table' => 'SACADOS s',
			'params' => 's.NOMSAC, s.TPDOCSAC, s.DOCSAC, s.REPASSE, s.CLI_SIGLA, s.TIPO_MENSALIDADE, s.MENSALIDADE, s.SUBSTITUTO_TRIBUTARIO, s.ISENTO_MENSALIDADE',
			'order' => array( 'param_order' => 's.CLI_SIGLA', 'order_by' => 'ASC' ),
			'where' => "s.STATUS = '0' AND s.CLI_SIGLA IN $customerList"
		);
		
		$selectedData = $crud->Select($dataToSelect);
		//DADOS DE MENSALIDADE
		$dataToSelect = array( 'table' => 'SALARIO_MINIMO sm', 'params' => 'sm.SALARIO_MINIMO' );
		$salario_minimo = $crud->Select($dataToSelect);
		$salario_minimo = ($salario_minimo['SALARIO_MINIMO'][0] * 1);
		
		for ( $i = 0 ; $i < count($selectedData['CLI_SIGLA']) ; $i++ ) {
			$sigla = $selectedData['CLI_SIGLA'][$i];
			$crud->Insert(array('table' => 'TEMP_MENSALIDADE', 'columns' => array('CUSTOMER' => $sigla)));
			$customer_name = $selectedData['NOMSAC'][$i];
			$tipo_cobranca = $selectedData['REPASSE'][$i] === '1' 
				? 'repasse' 
				: 'débito em conta nesta data, de acordo com o lançamento D.AUTORIZ, registrado na conta corrente do Banco do Brasil.';
			$tipodoc = $selectedData['TPDOCSAC'][$i] == '2' ? 'CNPJ' : 'CPF';
			$mask = $selectedData['TPDOCSAC'][$i] == '2' ? '%s%s.%s%s%s.%s%s%s/%s%s%s%s-%s%s' : '%s%s%s.%s%s%s.%s%s%s-%s%s';
			$docsac = vsprintf($mask, str_split($selectedData['DOCSAC'][$i]));
			$tipo_mensalidade = $selectedData['TIPO_MENSALIDADE'][$i];
			$valor_mensalidade = $selectedData['MENSALIDADE'][$i];
			if ( $tipo_mensalidade === '1' ) {
				$mensalidade = ($salario_minimo * $valor_mensalidade) / 100;
				$mensalidade_formatada = number_format($mensalidade, 2, ',', '.');
				$mensalidade_por_extenso = Extenso::converte($mensalidade_formatada, true);
				$mensalidade = $valor_mensalidade;
			} else {
				$mensalidade = $valor_mensalidade;
				$mensalidade_formatada = number_format($mensalidade, 2, ',', '.');
				$mensalidade_por_extenso = Extenso::converte($mensalidade_formatada, true);
			}
			$sub_trib = $selectedData['SUBSTITUTO_TRIBUTARIO'][$i];
			$isencao = $selectedData['ISENTO_MENSALIDADE'][$i];
			//GERANDO RECIBO E GUARDANDO NA PASTA DO CLIENTE
			if ( $createRecibo ) {
				$pathname = $customer->GetPathNameBySigla($sigla);
				$exploded_data = explode('/', $data);
				$dia = $exploded_data[0];
				$mes = $exploded_data[1];
				$ano = $exploded_data[2];
				$competencia = "$mes-$ano"; //mês-ano
				$checkPath = "C:\\Setydeias\\Setyware\\ADM77777\\Adm\\Clientes\\".strtoupper($pathname)."\\Recibos";
				$filename = "$checkPath\\$sigla - Mensalidade - $competencia.pdf";
				
				$dompdf = new Dompdf;
				$html = "
					<center><h1>Recibo</h1></center>
					<p align='justify'>Recebemos de $customer_name, $tipodoc $docsac,
					a importância de <b>R$ $mensalidade_formatada</b> ($mensalidade_por_extenso)
					referentes à <b>Prestação de Serviços de Assessoria e Processamento de Dados no mês anterior</b>,
					valor referente à manutenção do Sistema de Cobrança Bancária - Setyware; a ser paga pelo <b>sistema de $tipo_cobranca</b>.</p>
					<p>
						<br /><br /><br /><br /><br />
						<center>
							Fortaleza (CE), $dia de ".$meses[(int) $mes - 1]." de $ano<br /><br />
							<i>SETYDEIAS SERVIÇOS LTDA<br />
							CNPJ: 03.377.700/0001-98</i>
						</center>
					</p>";
				$dompdf->loadHtml($html);
				$dompdf->setPaper('A4', 'portrait');
				$dompdf->set_option('defaultFont', 'Courier');
				$dompdf->render();
				$output = $dompdf->output();
				
				//Tenta criar o arquivo, caso haja algum erro, tenta criar o diretório e criar o arquivo novamente
				if ( !file_put_contents($filename, $output) ) {
					if ( mkdir($checkPath, 0777, true) ) {
						file_put_contents($filename, $output);
						$created[] = $sigla;
					} else {
						$not_created[] = $filename;
					}
				} else {
					$created[] = $sigla;
				}
			}
			
			if ( $createRecibo && $sendAttach ) {
				$customer_mail = $customer->getMail($sigla);
				$mail = new PHPMailer;
				$mail->CharSet = 'UTF-8';
				$mail->isSMTP(); // Set mailer to use SMTP
				$mail->Host = $MailParams::_smtp_host(); // Specify main and backup SMTP servers
				$mail->SMTPAuth = true; // Enable SMTP authentication
				$mail->Username = $MailParams::_mail(); // SMTP username
				$mail->Password = $MailParams::_password(); // SMTP password
				$mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
				$mail->Port = $MailParams::_port(); // TCP port to connect to
				$mail->SMTPDebug = false;
				$mail->SMTPOptions = array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true));

				//Recipients
				$mail->setFrom($MailParams::_mail(), $MailParams::_name());
				//$mail->addAddress("brunodeveloper18@gmail.com", $customer_name); // Add a recipient
				$mail->addAddress($customer_mail, $customer_name); // Add a recipient
				$mail->addCC('setydeias@setydeias.com.br');
				
				//Attachments
				$mail->addAttachment($filename, 'Recibo.pdf');

				//Content
				$mail->isHTML(true); // Set email format to HTML
				$mail->Subject = "$sigla - Recibo de mensalidade";

				//Corpo do email
				$body = "<html><head>";
				$body .= "<meta charset='UTF-8' />";
				$body .= "<style>";
				$body .= "* {font-family: 'Verdana', sans-serif;}";
				$body .= "body {background: #f4f4f4;}";
				$body .= "span {display: block;padding:10px 0;}";
				$body .= "h3 {margin: 20px 0;color:#069;}";
				$body .= "</style>";
				$body .= "</head><body>";
				$body .= "<a href='http://setydeias.com.vc' target='_blank'><img src='http://setydeias.com.br/comercial/images/mail-header.jpg' alt='Cabeçalho' /></a><br/><br/>";
				$body .= "<section style='width:700px'>";
				$body .= "Olá, <br /><br />Segue em anexo o seu recibo de mensalidade.";
				$body .= "</section>";
				$body .= "</body></html>";
				$mail->Body = $body;
				
				$mail->send() ? $mail_send[] = $sigla : $mail_not_send[] = $sigla;
			}
			
			//ESCREVENDO A MENSALIDADE NA CONTA TRANSITÓRIA
			$xls = "..\\..\\..\\..\\..\\..\\contatransitoria\\".strtolower($sigla)."\\index-$sigla.xls";
			if ( $writeInCT ) {
				if ( file_exists($xls) ) {
					$objPHPExcel = PHPExcel_IOFactory::load($xls);
					//Loop inside of the worksheet
					foreach ( $objPHPExcel->getWorksheetIterator() as $worksheet ) {
						$highestRow = $worksheet->getHighestRow();
						//Loop in all cells of the sheet
						for ( $cc = 11; $cc < $highestRow; $cc++ ) {
							if ( $worksheet->getCellByColumnAndRow(1, $cc)->getValue() == "" ) {
								//Selecting the Activated Sheets
								$objWorksheet = $objPHPExcel->getActiveSheet();
								//Function should returns the formated date for excel
								$getDt = getFmtedDate($data);
								//Declarating vars of functions @getFmtedDate
								$date_m = $getDt[0];
								//Function should get cell info	
								getCellData($objWorksheet, $cc, $date_m, null, null, null, null, 'mensalidade', $salario_minimo, $isencao, $sub_trib, array('tipo_mensalidade' => $tipo_mensalidade, 'mensalidade' => $mensalidade), null, null, array(), $sigla);
								//Creating the writer object
								$objWriter = PHPExcel_IOFactory::createWriter( $objPHPExcel, 'Excel5' );
								$objWriter->setPreCalculateFormulas(false);
								$objWriter->save($xls);
								break;
							}
						}
					}
					$writed_ct[] = $sigla;
				} else {
					$ct_not_found[] = $sigla;
				}
			}
		}
		
		$crud->Delete(array('table' => 'TEMP_MENSALIDADE', 'where' => array(1 => 1)));
		echo json_encode(array(
			'writed_ct' => $writed_ct,
			'ct_not_found' => $ct_not_found,
			'created' => $created,
			'not_created' => $not_created,
			'mail_send' => $mail_send,
			'mail_not_send' => $mail_not_send
		));
	} catch ( Exception $e ) {
		$crud->Delete(array('table' => 'TEMP_MENSALIDADE', 'where' => array(1 => 1)));
		echo $e->getMessage();
	}