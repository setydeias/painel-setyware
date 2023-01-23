<?php
	
	error_reporting(0);
    include_once '../../../vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
    include_once 'MailParams.class.php';

    class Mail {

        public function __construct() {
			$this->mail = new PHPMailer;
		}
		
		public function __destruct() { }
        
        public function send(array $data) {

			$mail = $data['mail'];
			$name = $data['name'];
			$subject = $data['subject'];
			$body = $data['body'];

			$MailParams = new MailParams();
			$this->mail->CharSet = 'UTF-8';
			$this->mail->isSMTP(); // Set mailer to use SMTP
			$this->mail->Host = $MailParams::_smtp_host(); // Specify main and backup SMTP servers
			$this->mail->SMTPAuth = true; // Enable SMTP authentication
			$this->mail->Username = $MailParams::_mail(); // SMTP username
			$this->mail->Password = $MailParams::_password(); // SMTP password
			$this->mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
			$this->mail->Port = $MailParams::_port(); // TCP port to connect to
			$this->mail->SMTPDebug = false;
			$this->mail->SMTPOptions = array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				)
			);

			$this->mail->setFrom($MailParams::_mail(), $MailParams::_name());
			/* $mailToSend = $enviroment == "production" ? $customerMail : "brunodeveloper18@gmail.com"; 
			$nameToSend = $enviroment == "production" ? $customerName : "Bruno Pontes";
			 */
			$this->mail->addAddress($mail, $name); // Add a recipient
			
			//$mail->addAttachment($file_to_attach); // Add attachments
			$this->mail->isHTML(true); // Set email format to HTML

			$this->mail->Subject = $subject;
			//Message body
			$this->mail->Body = $body;
			
			$this->mail->send();
        }

    }