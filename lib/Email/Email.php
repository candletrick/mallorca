<?php

class Email
	{
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
	}
