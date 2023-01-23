<?php
	error_reporting(0);
	include_once '../../../vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
	include_once '../class/FirebirdCRUD.class.php';
	include_once '../class/FilesHandler.class.php';
	include_once '../class/DirManager.class.php';
	include_once '../class/MailParams.class.php';
	include_once '../class/CloudServer.class.php';
	include_once '../class/Customer.class.php';
	//Objetos
	$crud = new FirebirdCRUD();
	$Customer = new Customer();
	$FilesHandler = new FilesHandler();
	$DirManager = new DirManager();
	$MailParams = new MailParams();
	$CloudServer = new CloudServer();
	$DirManager = new DirManager();
	//Extensões permitidas
	$allowed_extensions = array('ret', 'RET', 'srq', 'SRQ');
	$path = $DirManager->getDirs(array('RETORNOS_PROCESSADOS'))['RETORNOS_PROCESSADOS'][0];
	$data = json_decode(file_get_contents('php://input'), true);
	/*
	* Verifica se os arquivos a serem enviados foram informados
	* Caso não tenham sido informados, todos os arquivos da pasta serão enviados
	*/
	if ( !isset($data['data']['filesInfo']) ) {
		$files = $DirManager->getFiles($path, array('ret', 'RET'));
		$filesToSend = array();
		
		foreach ( $files as $filename ) {
			$filename = explode('_', basename($filename));
			$filesToSend[] = array( 'customer' => $filename[2], 'date' => $filename[1], 'convenio' => $filename[4]);
		}
	} else {
		$filesToSend = $data['data']['filesInfo'];	
	}

	$unlink_files = $data['data']['unlinkFiles'];
	//Define se é teste ou produção
	$enviroment = "production";
	$path_enviroment = $enviroment == "teste" ? "clientes-teste" : "clientes";
	//Status do servidor
	$dataToSelectConfig = array( 'table' => 'SERVIDOR_NUVENS svn', 'params' => 'svn.ENVIAR_SERVIDOR, svn.CONVERTER_ARQUIVOS' );
	$state = $crud->Select($dataToSelectConfig);
	$send_state = $state['ENVIAR_SERVIDOR'][0];
	//Se o status da conversão de arquivos estiver ativo
	//Converte os arquivos
	$convert_state = $state['CONVERTER_ARQUIVOS'][0];
	if ( $convert_state == '1' ) $FilesHandler->convertFileToFB();
	//Seleciona os clientes padrão CNAB240
	$dataToSelectCNAB240 = array(
		'table' => 'SACADOS s',
		'params' => 's.CLI_SIGLA',
		'where' => 's.CNAB240 = 1'
	);
	$cnab240_customers = $crud->Select($dataToSelectCNAB240)['CLI_SIGLA'];
	//Seleciona os clientes que possuem email cadastrado
	//independente do padrão (CNAB240 | FIREBIRD)
	$dataToSelectCustomersWithMail = array(
		'table' => 'SACADOS s',
		'params' => 's.NOMSAC, c.EMAIL, s.CLI_SIGLA',
		'inner_join' => array(
			'table' => 'CONTATOS c',
			'on' => 'c.CODSAC, s.CODSAC'
		),
		'where' => "s.RETORNO_POR_EMAIL = 1 AND c.EMAIL is not null"
	);
	$customersWithMail = $crud->Select($dataToSelectCustomersWithMail);
	$customers_to_send_mail = array();
	//Agrupa as informações por sigla
	for ( $i = 0 ; $i < count($customersWithMail['CLI_SIGLA']) ; $i++ ) {
		$customer_name = $customersWithMail['NOMSAC'][$i];
		$customer_sigla = $customersWithMail['CLI_SIGLA'][$i];
		$customer_mail = $customersWithMail['EMAIL'][$i];
		$customers_to_send_mail[$customer_sigla][] = array('NOME' => $customer_name, 'EMAIL' => $customer_mail);
	}
	//Pastas
    $path = $DirManager->getDirs(array('RETORNOS_PROCESSADOS'))['RETORNOS_PROCESSADOS'][0];
	$files = scandir($path);
	$filesAvailableToSend = array();
	$filesToUnlink = array();
	//Retorna os arquivos selecionaodos pelo cliente para enviar ao servidor
	foreach ( $filesToSend as $fileToSend ) {
		foreach ( $files as $file_processed ) {
			if ( in_array(pathinfo($file_processed, PATHINFO_EXTENSION), $allowed_extensions) ) {
				if ( strstr($file_processed, $fileToSend['customer']) && strstr($file_processed, $fileToSend['date']) && strstr($file_processed, $fileToSend['convenio']) ) {
					$filesAvailableToSend[] = $file_processed;
					break;
				}
				
				if ( !in_array($file_processed, $filesToUnlink) ) {
					$filesToUnlink[] = $file_processed;
				}
			}
		}
	}
	//Arquivos que serão excluidos da pasta
	$filesToUnlink = array_diff($files, $filesAvailableToSend);
	//Mensagem padrão CNAB240
	function mailBody( $customerName ) {
		$msg_cnab240 = "<section style='font-family:\"Verdana\"'>";
		$msg_cnab240 .= "<img src='http://setydeias.com.br/comercial/images/mail-header.jpg' alt='Cabeçalho da página' /><br /><br />";
		$msg_cnab240 .= "<span style='font-size:16px;'>{$customerName}</span>,<br /><br />";
		$msg_cnab240 .= "Segue em anexo o arquivo de retorno processado no dia ".date('d')."/".date('m').".<br /><br />";
		$msg_cnab240 .= "<i>Atenciosamente<br />Suporte<br />Setydeias Serviços Ltda.</i>";
		$msg_cnab240 .= "</section>";

		return $msg_cnab240;
	}

	$customers_mail_not_sent = array(); //Emails que não foram enviados por algum motivo
	$customers_mail_sent = array(); //Emails enviados com sucesso
	$customers_server_send = array(); //Arquivos que devem ser enviados para o servidor

	foreach ( $filesAvailableToSend as $file ) {
		$filename = "$path$file";
		$file = explode('_', $file);
		$customerSigla = $filename[2];
		
		//Retorna apenas os arquivos que serão enviados
		if ( !array_key_exists( $customerSigla, $customers_to_send_mail ) ) {
			$customers_server_send[] = $filename;
			continue;
		}
		//Envia email caso o cliente esteja na lista dos clientes que recebem
		//o retorno por email
		foreach ( $customers_to_send_mail[$customerSigla] as $customer ) {
			$customer_name = $customer['NOME'];
			$customer_mail = $customer['EMAIL'];
			$corpo_email = mailBody( $customer_name );

			$mail = new PHPMailer;
			
			$mail->CharSet = 'UTF-8';
			$mail->isSMTP(); // Set mailer to use SMTP
			$mail->Host = $MailParams::_smtp_host(); // Specify main and backup SMTP servers
			$mail->SMTPAuth = true; // Enable SMTP authentication
			$mail->Username = $MailParams::_mail(); // SMTP username
			$mail->Password =  $MailParams::_password(); // SMTP password
			$mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
			$mail->Port = $MailParams::_port(); // TCP port to connect to
			$mail->SMTPDebug = false;
			$mail->SMTPOptions = array( 'ssl' => array( 'verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true ));
			$mail->setFrom($MailParams::_mail(), $MailParams::_name());
			$mailToSend = $enviroment == "production" ? $customer_mail : "brunodeveloper18@gmail.com"; 
			$nameToSend = $enviroment == "production" ? $customer_name : "Bruno Pontes";
			
			$mail->addAddress($mailToSend, $nameToSend); // Add a recipient
			$mail->addAttachment($filename); // Add attachments
			$mail->isHTML(true); // Set email format to HTML

			$mail->Subject = 'Arquivo de Retorno '.date('d').'/'.date('m');
			//Message body
			$mail->Body = $corpo_email;
			
			!$mail->send() 
				? $customers_mail_not_sent[] = $customerSigla 
				: $customers_mail_sent[] = $customerSigla;
		}

		unlink($filename);
	}
	
	$files_not_sent = array(); //Caso a transferência de arquivos esteja ativada e o mesmo não seja enviado
	$files_sent = array(); //Arquivos enviados

	//Checa se a transferência para o servidor está ativa
	if ( $send_state == 1 ) {
		//Conecta ao servidor via FTP
		$CloudServer->connect();
		//Loop nos arquivos que serão enviados para o servidor
		foreach ( $customers_server_send as $file ) {
			$customer_sigla = explode('_', $file)[2];
			$pathname = strtolower($Customer->GetPathNameBySigla($customer_sigla));
			$path_to_send = "/$path_enviroment/$pathname/retorno/";
			$filename_to_send = basename($file);

			if ( $CloudServer->send($file, "/$path_to_send/$filename_to_send") ) {
				$files_sent[] = $customer_sigla;
			} else {
				//Caso o arquivo não tenha sido enviado
				//Será criada uma nova pasta com o @pathname do cliente
				//e tenta enviar o arquivo novamente
				$CloudServer->createDir($path_to_send, true);
				$CloudServer->send($file, "/$path_to_send/$filename_to_send") ? $files_sent[] = $customer_sigla : $files_not_sent[] = $customer_sigla;
			}
			
			if ( $unlink_files ) unlink($file);
		}
	}
	//Remove os arquivos que não foram enviados pro servidor
	foreach ( $filesToUnlink as $file ) {
		if ( in_array(pathinfo($file, PATHINFO_EXTENSION), $allowed_extensions) ) {
			unlink("$path$file");
		}
	}
	
	echo json_encode(array(
		'mailSent' => array_values(array_unique($customers_mail_sent)),
		'mailNotSent' => array_values(array_unique($customers_mail_not_sent)),
		'filesSent' => array_values(array_unique($files_sent)),
		'filesNotSent' => array_values(array_unique($files_not_sent))
	));