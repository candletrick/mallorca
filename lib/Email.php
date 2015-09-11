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

		// $mail->isSMTP();
		// $mail->Host = 'smtp1.example.com;smtp2.example.com';
		// $mail->SMTPAuth = true;
		// $mail->Username = 'user@example.com';
		// $mail->Password = 'secret';
		// $mail->SMTPSecure = 'tls';
		// $mail->Port = 587;
		$mail->isHTML(true);

		return $mail;
		}

	/**
		Shorthand send function.
		\param	string	$to	To field.
		\param	string	$subject	Subject.
		\param	string	$msg	Body.
		*/
	static public function send($to, $subject, $msg)
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
