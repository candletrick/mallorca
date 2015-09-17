<?php
namespace Login;

class Email extends \Module
	{
	/**
		*/
	static public function get_confirmation_link($email, $restore = false)
		{
		$link = \Login::encrypt($email . now(), \Login::salt());
		// $full = \Path::http() . \Config::$local_path . "user/register?confirmation_link=$link";

		$ok = \Db::match_update('user', array(
			'confirmation_link'=>$link,
			'confirmation_expires_at'=>date('Y-m-d H:i:s', strtotime('+30 day')),
			'is_confirmed'=>0,
			// if restoring add confirmed so no multiple emails
			), " where email=" . \Db::esc($email) . ($restore ? " and is_confirmed=1" : ''));

		if (! $ok) {
			return false;
			}

		return $link;
		}

	/**
		*/
	static public function send_reset_email($email)
		{
		if (! $email) {
			alert("Please enter an email...");
			return;
			}

		$link = \Login::encrypt($email, \Login::salt());
		$full = "http://" . $_SERVER['HTTP_HOST'] . \Config::$local_path . "user/reset&confirmation_link=$link&reset=1";
		$subject = "Reset Password for " . $_SERVER['HTTP_HOST'];
		$msg = "A request for password reset has been submitted.<br>
		If you requested this, go to this confirmation link to reset your password:<br><br><a href='$full'>Reset Password</a>";

		\Email::send($email, $subject, $msg);
		alert("An email containing a link to reset password has been sent to $email");
		\Db::match_update('user', array(
			'confirmation_link'=>$link,
			'confirmation_expires_at'=>date("Y-m-d H:i:s", strtotime('+1 day'))
			), " where email=" . \Db::esc($email));
		}

	/**
		Send confirmation email.
		*/
	static public function send_confirmation_email($email = '', $data = array(), $restore = false)
		{
		if (! $email) {
			alert('Your session has expired');
			return false;
			}

		$link = self::get_confirmation_link($email, $restore);
		$full = \Path::http() . \Config::$local_path . "login/home?confirmation_link=$link";

		if (! $full) {
			// alert('Link already sent.');
			return;
			}
		else {
			alert('Your session has expired.<br>An email is being sent to restore.');
			}

		if (! $restore) {
			$subject = "Complete registration for " . $_SERVER['HTTP_HOST'];
			$msg = "Please confirm your email address by following this link:<br><br><a target='_blank' href='$full'>$full</a>";
			}
		else {
			$subject = "Session expired for " . $_SERVER['HTTP_HOST'];
			$msg = "Please follow this link to restore your session:<br><br><a target='_blank' href='$full'>$full</a>";
			}

		\Email::send($email, $subject, $msg); 
		}
	}
