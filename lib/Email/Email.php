<?php
require 'vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

class Email 
	{
	/*
	static public function send($to, $subject, $msg, $html = true, $fromname = 'Socrates', $from = 'socrates@emailsock.com', $headers = array())
		{
		$hs = "From: \"$fromname\" <$from>"
		. "\r\nReply-To: $from"
		. "\r\nX-Mailer: PHP/' . phpversion()"
		;

		// To send HTML mail, the Content-type header must be set
		if ($html) {
			$hs .= "\r\nMIME-Version: 1.0"
			. "\r\nContent-type: text/html; charset=iso-8859-1"
			;
			}

		$new_message_id = md5(time() . rand()) . $_SERVER['HTTP_HOST'];
		$hs .= "\r\nMessage-ID: $new_message_id";

		foreach ($headers as $k=>$v) $hs .= "\r\n$k: $v";

		$hs .= "\r\n";

		// testing
		// mail('fewkeep@gmail.com', $subject . " on behalf of " . $to, $msg, $headers);
		return mail($to, $subject, $msg, $hs);
		}
		*/

	static public function chain()
		{
		$mail = new \PHPMailer();
		//$mail->SMTPDebug = 3;                               // Enable verbose debug output

		// $mail->isSMTP();                                      // Set mailer to use SMTP
		// $mail->Host = 'smtp1.example.com;smtp2.example.com';  // Specify main and backup SMTP servers
		// $mail->SMTPAuth = true;                               // Enable SMTP authentication
		// $mail->Username = 'user@example.com';                 // SMTP username
		// $mail->Password = 'secret';                           // SMTP password
		// $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		// $mail->Port = 587;                                    // TCP port to connect to
		$mail->isHTML(true);                                  // Set email format to HTML

		return $mail;
		}

	// static public function send($to, $subject, $body, $html = true, $from_name, $from, $hash)
	static public function send($to, $subject, $msg, $html = true,
		$from_name = 'Socrates', $from = 'socrates@emailsock.com') //, $headers = array())
		{
		$mail = self::chain();

		$mail->From = $from;
		$mail->FromName = $from_name;
		// $mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
		$mail->addAddress($to);
		$mail->addReplyTo($from, $from_name);
		// $mail->addCC('cc@example.com');
		// $mail->addBCC('bcc@example.com');

		$new_message_id = md5(time() . rand()) . $_SERVER['HTTP_HOST'];
		// $hs .= "\r\nMessage-ID: $new_message_id";
		$mail->addCustomHeader('Message-ID', $new_message_id);

		$mail->Subject = $subject;
		$mail->Body    = $msg;

		$mail->send();
		// if(! $mail->send()) {
		// die('Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
		}
	}
