<?php
	
	//error_reporting(0);
	set_time_limit(0);
	include_once '../class/MailParams.class.php';
	include_once '../../../vendor/autoload.php';

	try {

		$info = json_decode(file_get_contents('php://input'));
		//Parâmetros
		$customer_name = $info->customerName;	
		$customer_mail = $info->customerMail;
		$concurso = $info->concurso;
		$data_concurso = $info->data_concurso;
		$numero_sorteio = $info->numeroSorteio;
		$condominio = $info->condominio;
		$unidade = $info->unidade;
		$cliente = $info->cliente;
		$link_loteria =$info->link_loteria;

		$acao = $info->acao;
			
		$MailParams = new MailParams();		
		
		$mail = new PHPMailer;
		$mail->CharSet = 'UTF-8';
		$mail->isSMTP(); // Set mailer to use SMTP
		$mail->Host = $MailParams::_smtp_host(); // Specify main and backup SMTP servers
		$mail->SMTPAuth = true; // Enable SMTP authentication
		$mail->Username = $MailParams::_mail(); // SMTP username
		$mail->Password = $MailParams::_password(); // SMTP password
		$mail->SMTPSecure = 'TLS'; // Enable TLS encryption, `ssl` also accepted
		$mail->Port = $MailParams::_port(); // TCP port to connect to
		$mail->SMTPDebug = true;
		$mail->SMTPOptions = array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true));

		//Recipients
		$mail->setFrom($MailParams::_mail(), $MailParams::_name());
		$mail->addAddress($customer_mail, $customer_name); // Add a recipient
		//$mail->addAddress("neto.marcal.ce@gmail.com", "Teste"); // Add a recipient for test
		$mail->addCC('setydeias@setydeias.com.br');
		
		//Attachments
		//$mail->addAttachment($filename, 'Recibo.pdf');

		//Content
		$mail->isHTML(true); // Set email format to HTML
		
		
		$mail->Subject =  setAcao($acao);

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
		
		$acao === "ativar" ? $body .=  "Olá! $customer_name, <br /><br /> Seu número de participação: <b>$numero_sorteio</b>,  referente a unidade <b>$unidade</b> do <br /> <b>$condominio</b>, foi ATIVADO com sucesso.<br /><br /> Boa sorte no PRÊMIO ADIMPLÊCIA!" : "";
		$acao === "desativar" ? $body .=  "Olá! $customer_name, <br /><br /> Seu número de participação do Prêmio adimplência: <b>$numero_sorteio</b>, referente a unidade <b>$unidade</b> do<br /> <b>$condominio</b> foi DESATIVADO." : "";
		$acao === "solicitarNumero" ? $body .=  "Olá! $customer_name, <br /><br />Seu número de participação do Prêmio adimplência referente a unidade <b>$unidade</b> do <b>$condominio</b> é: <b>$numero_sorteio</b>.<br /><br /> Boa sorte no PRÊMIO ADIMPLÊCIA!" : "";
		$acao === "confirmarSorteado" ? $body .=  "Olá! $customer_name, <br /><br /><br /> <span style='margin-left: 30px; line-height: 0.1'> PARABÉNS ! O seu número de participação: <b>$numero_sorteio</b> do Prêmio adimplência, referente</span> a unidade: <b>$unidade</b> do <b>$cliente</b>, foi CONTEMPLADO conforme<br /> o 1º prêmio da Loteria Fedaral
										no Concurso: <b>$concurso</b> realizado em: <b>$data_concurso</b>.<br />": "";


		$body .= "<br /><br /><b>Obs: </b> Dúvidas, favor entrar em contato nos fones:<br />(85) 3290-7777<br />(85) 3496-7777<br />(85) 9.8627-7777<br/ ><br /> Ou acesse nosso site: <a href='https://setydeias.com.br/#rodape'>setydeias.com.br/#rodape</a>";
		$body .= "<br /> Loteria Federal: <a href='$link_loteria'> $link_loteria</a><br />Acesse, consulte e confira. ";
		$body .= "</section>";
		$body .= "</body></html>";
		$mail->Body = $body;
		
		$mail->send();
			
	} catch ( Exception $e ) {	
		echo $e->getMessage();
	}

	function setAcao($dado) {
		switch ($dado) {
			case 'ativar':
				return 'Ativação do número de Sorteio.';
				break;
			case 'desativar':
				return 'Seu número de Sorteio foi DESATIVADO.';
				break;
			case 'solicitarNumero':
				return 'Solicitação do número de sorteio.';
				break;
			case 'confirmarSorteado':
				return 'Sorteio Prêmio Adimplência.';
				break;
			default:
				# code...
				break;
		}
	}