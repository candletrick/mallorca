<?php

class Email
	{
	static public function send($to, $subject, $msg, $html = true)
		{
		$headers = 'From: "SocratesFwds+Ideas" <abcdef@emailsock.com>'
		. "\r\nReply-To: socrates@emailsock.com"
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
	}
