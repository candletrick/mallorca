<?php
require 'vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

/**
	Wrapper around PHPMailer for sending email.
	*/
class Email 
	{
	/**
		Factory method.
		Prepare PHPMailer object with defaults and return.
		\return PHPMailer
		*/
	static public function chain()
		{
		$mail = new \PHPMailer(true);
		$mail->SMTPDebug = 3;

		if (isset(\Config::$email['smtp'])) {
			$smtp = \Config::$email['smtp'];

			$mail->isSMTP();
			$mail->Host = $smtp['host'];
			$mail->SMTPAuth = true;
			$mail->Username = $smtp['username'];
			$mail->Password = $smtp['password'];
			$mail->SMTPSecure = $smtp['method'];
			$mail->Port = $smtp['port'];
			}

		$mail->isHTML(true);

		return $mail;
		}

	static public function send($to, $subject, $msg)
		{
		if (\Config::$local_path == '/') self::send_live($to, $subject, $msg);
		else self::send_local($to, $subject, $msg);
		}

	static public function send_local($to, $subject, $msg, $html = true)
		{
		$headers = 'From: "localhost" <fewkeep@gmail.com>'
		. "\r\nReply-To: fewkeep@gmail.com"
		. "\r\nX-Mailer: PHP/' . phpversion()"
		;

		// To send HTML mail, the Content-type header must be set
		if ($html) {
			$headers .= "\r\nMIME-Version: 1.0"
			. "\r\nContent-type: text/html; charset=iso-8859-1"
			. "\r\n"
			;
			}

		// testing
		// mail('fewkeep@gmail.com', $subject . " on behalf of " . $to, $msg, $headers);
		return mail($to, $subject, $msg, $headers);
		}

	/**
		Shorthand send function.
		\param	string	$to	To field.
		\param	string	$subject	Subject.
		\param	string	$msg	Body.
		*/
	static public function send_live($to, $subject, $msg)
		{
		$from_name = is(\Config::$email, 'from_name', 'Hello');
		$from_email = is(\Config::$email, 'from_email', 'hello@' . $_SERVER['HTTP_HOST']);

		$mail = self::chain();

		$mail->From = $from_email;
		$mail->FromName = $from_name;
		// $mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
		$mail->addAddress($to);
		$mail->addReplyTo($from_email, $from_name);

		$new_message_id = md5(time() . rand()) . $_SERVER['HTTP_HOST'];
		$mail->addCustomHeader('Message-ID', $new_message_id);

		$mail->Subject = $subject;
		$mail->Body    = $msg;

		if(! $mail->send()) {
			die('Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
			}
		}
	}
