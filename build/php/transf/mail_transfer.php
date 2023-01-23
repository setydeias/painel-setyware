<?php
	error_reporting(E_ALL);
	include_once '../../../vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
	include_once '../class/FirebirdCRUD.class.php';
	include_once '../class/FilesHandler.class.php';
	include_once '../class/DirManager.class.php';
	include_once '../class/CloudServer.class.php';
	include_once '../class/MailParams.class.php';
	$data = json_decode(file_get_contents('php://input'), true);
	$unlink_files = $data['unlink_files'];
	//Define se é teste ou produção
	$enviroment = "production";
    //Objetos
	$crud = new FirebirdCRUD();
	$FilesHandler = new FilesHandler();
	$DirManager = new DirManager();
	$CloudServer = new CloudServer();
	$MailParams = new MailParams();
	//Status do servidor
	$dataToSelectConfig = array(
		'table' => 'SERVIDOR_NUVENS svn',
		'params' => 'svn.ENVIAR_SERVIDOR, svn.CONVERTER_ARQUIVOS'
		);
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
		'where' => "c.EMAIL is not null"
	);
	$customersWithMail = $crud->Select($dataToSelectCustomersWithMail);
	$customers = array();
	//Agrupa as informações por sigla
	for ( $i = 0 ; $i < count($customersWithMail['CLI_SIGLA']) ; $i++ ) {
		$customer_name = $customersWithMail['NOMSAC'][$i];
		$customer_sigla = $customersWithMail['CLI_SIGLA'][$i];
		$customer_mail = $customersWithMail['EMAIL'][$i];
		$customers[$customer_sigla][] = array('NOME' => $customer_name, 'EMAIL' => $customer_mail);
	}
    //Pastas
    $path = $DirManager->getDirs(array('RETORNOS_PROCESSADOS'))['RETORNOS_PROCESSADOS'][0];
    $files = scandir($path);
    $allowed_extensions = array('ret', 'RET', 'srq', 'SRQ');
	//Mensagem padrão CNAB240
	function mensagemCNAB240( $customerName ) {
		$msg_cnab240 = "<section style='font-family:\"Verdana\"'>";
		$msg_cnab240 .= "<img src='http://setydeias.com.br/comercial/images/mail-header.jpg' alt='Cabeçalho da página' /><br /><br />";
		$msg_cnab240 .= "<span style='font-size:16px;'>{$customerName}</span>,<br /><br />";
		$msg_cnab240 .= "Segue em anexo o arquivo de retorno processado no dia ".date('d')."/".date('m').".<br /><br />";
		$msg_cnab240 .= "<i>Atenciosamente<br />Suporte<br />Setydeias Serviços Ltda.</i>";
		$msg_cnab240 .= "</section>";

		return $msg_cnab240;
	}
	//Mensagem padrão FIREBIRD
	function mensagemFIREBIRD( $customerName ) {
		$msg_firebird = "<section style='font-family:\"Verdana\"'>";
		$msg_firebird .= "<img src='http://setydeias.com.br/comercial/images/mail-header.jpg' alt='Cabeçalho da página' /><br /><br />";
		$msg_firebird .= "<span style='font-size:16px;'>{$customerName}</span>,<br /><br />";
		$msg_firebird .= "Segue em anexo o arquivo de retorno processado no dia ".date('d')."/".date('m').".<br /><br />";
		$msg_firebird .= "<b>Para efetuar a leitura do arquivo de retorno, acesse o link a seguir e saiba mais:<br /><br /></b> <a href='http://setydeias.com.br/wiki/retornos/leitura_manual'>http://setydeias.com.br/wiki/retornos/leitura_manual<a/><br /><br />";
		$msg_firebird .= "<i>Atenciosamente<br />Suporte<br />Setydeias Serviços Ltda.</i>";
		$msg_firebird .= "</section>";

		return $msg_firebird;
	}

	$customers_without_mail = array(); //Clientes que não possuem email
	$customers_mail_not_sent = array(); //Emails que não foram enviados por algum motivo
	$customers_mail_sent = array(); //Emails enviados com sucesso

	//Loop nos arquivos de retorno
    for ( $i = 0 ; $i < count($files) ; $i++ ) {
		//Verifica se os arquivos estão dentro das extensões permitidas
        if ( in_array(pathinfo($files[$i], PATHINFO_EXTENSION), $allowed_extensions) ) {
			//Sigla do cliente
			$customer = explode('_', $files[$i])[2];
			//Verifica se o cliente possui email
			if ( array_key_exists( $customer, $customers ) ) {
				$nome_email = $customers[$customer][0]['NOME'];
				$email = $customers[$customer][0]['EMAIL'];
				$corpo_email = in_array( $customer, $cnab240_customers ) 
					? mensagemCNAB240( $nome_email ) 
					: mensagemFIREBIRD( $nome_email );
				$file_to_attach = $path.$files[$i];
			} else {
				//Se o cliente não possuir email
				//Volta ao começo do loop para checar próximo cliente
				$customers_without_mail[] = $customer;
				continue;
			}

			//Se o cliente possuir email, envia
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
			$mail->SMTPOptions = array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				)
			);

			$mail->setFrom($MailParams::_mail(), $MailParams::_name());
			$mailToSend = $enviroment == "production" ? $customerMail : "brunodeveloper18@gmail.com"; 
			$nameToSend = $enviroment == "production" ? $customerName : "Bruno Pontes";
			
			$mail->addAddress($mailToSend, $nameToSend); // Add a recipient
			
			$mail->addAttachment($file_to_attach); // Add attachments
			$mail->isHTML(true); // Set email format to HTML

			$mail->Subject = 'Arquivo de Retorno '.date('d').'/'.date('m') ;
			//Message body
			$mail->Body = $corpo_email;
			
			!$mail->send() 
				? $customers_mail_not_sent[] = $customer 
				: $customers_mail_sent[] = $customer;

			//Apaga o arquivo
			if ( $unlink_files ) unlink($file_to_attach);
		}
	}

	echo json_encode(array(
		'mailSent' => array_values(array_unique($customers_mail_sent)),
		'mailNotSent' => array_values(array_unique($customers_mail_not_sent)),
		'customersWithoutMail' => array_values(array_unique($customers_without_mail))
	));