<?php
/**
	Login class.	
	*/
class Login 
	{
	/** Login id. */
	static public $id = 0;

	/** Escaped id for queries. */
	static public $esc = 0;

	/** User data. */
	static private $data = array();
	
	/**
		\param	string	$name	Data key.
		\return Get user data by key.
		*/
	static public function get($name)
		{
		return array_key_exists($name, self::$data) ? self::$data[$name] : '';
		}

	/**
		Check if the user is logged in.
		Runs every page load.
		*/
	static public function check()
		{
		$id = id_zero(sesh('login_id'));

		if (sesh('login_id')) {
			// self::$data = \Db::one_row("select a.*, b.user_type
			// from user a left outer join user_type b on a.user_type_id=b.id where a.id=" . $id) ?: array();
			self::$data = \Db::one_row("select a.* from user a where a.id=" . $id) ?: array();
			if (empty(self::$data)) unset($_SESSION['login_id']);
			}

		self::$id = $id;
		self::$esc = db()->esc($id);
		}

	/**
		Validate user.
		\param	array	$d	User data array (email, password, confirmed).
		*/
	static public function validate($d) {
		$a = \Db::one_row("select * from user where email=" . db()->esc(is($d, 'email')));

		if (! $a) {
			alert("Email not recognized.");
			return false;
			}
		else if ($a['password'] != self::encrypt(is($d, 'password'), $a['salt'])) {
			alert("Password is not correct.");
			return false;
			}
		else if (! $a['confirmed']) {
			alert("Please check your email to confirm your account.");
			return false;
			}
		else {
			// cookies
			if (is($d, 'remember')) {
				$expire = time() + (3600 * 72);
				setcookie('bearcats_email', $d['email'], $expire);
				setcookie('bearcats_password', $d['password'], $expire);
				setcookie('bearcats_remember', $d['remember'], $expire);
				}
			else {
				setcookie('bearcats_email', $d['email'], time() - 1000);
				setcookie('bearcats_password', $d['password'], time() - 1000);
				setcookie('bearcats_remember', $d['remember'], time() - 1000);
				}
				

			alert('You are now logged in as ' . $a['email'] . '.');
			$_SESSION['login_id'] = $a['id'];
			return true;
			}
		}

	/**
		Encrypt password.
		*/
	public static function encrypt($password, $salt)
		{
		return hash_hmac('sha256', $salt . $password, "h0gburn");
		}

	/**
		Generate a random password.
		*/
	static public function random_password()
		{
		return implode(array_map(function ($x) {
			return chr(rand(32, 127));
			}, range(1, 8)));
		}

	/**
		Create salt.
		*/
	static public function salt()
		{
		return base64_encode(openssl_random_pseudo_bytes(24));
		}

	/**
		See if user exists.
		*/
	static public function exists($email)
		{
		return \Db::value("select 1 from user where email=" . db()->esc($email));
		}

	static public function is_admin()
		{
		// return self::get('email') == 'fewkeep@gmail.com';
		return self::get('email') == 'info@californiabearcats.com';
		}

	/**
		Create new user.
		*/
	static public function create($email, $password, $data = array())
		{
		if (! $email || ! $password) {
			$_SESSION['alert'] = "Please enter an email and password...";
			return;
			}

		if (self::exists($email)) {
			alert("There is already an account for $email.");
			return;	
			}

		$salt = self::salt();
		$new_id = \Db::match_insert("user", array(
			'name'=>is($data, 'name', $email),
			'email'=>$email,
			'salt'=>$salt,
			'password'=>self::encrypt($password, $salt),
			'created_on'=>date("Y-m-d H:i:s"),
			'confirmation_link'=>'',
			'confirmed'=>0,
			));

		self::send_confirmation_email($email, $data);
		$_SESSION['verify_email'] = $email;

		return $new_id;
		}

	/**
		Send confirmation email.
		*/
	static public function send_confirmation_email($email = '', $data = array())
		{
		if (! $email) {
			alert('Your session has expired');
			return false;
			}

		$link = self::encrypt($email, self::salt());
		/*
		$name_url = select('player_profile', array(
			'url',
			m(array(
				m('email')->left('user')->where($email)
				))->left('player')
			))->value();
			*/
		$full = 'http://' . $_SERVER['HTTP_HOST'] . "?confirmation_link=$link";
		$name_link = 'http://' . $_SERVER['HTTP_HOST'] . "/" . is($data, 'url');

		$subject = "Samsara Welcome";
		$msg = "Welcome to samsara.";

		\Email::send($email, $subject, $msg); 
		\Db::match_update('user', array(
			'confirmation_link'=>$link,
			'confirmation_expires_at'=>date('Y-m-d H:i:s', strtotime('+30 day')),
			), " where email=" . db()->esc($email)); // . " and confirmed=0 ");
		}

	/**
		Send reset email.
		*/
	static public function send_reset_email($email)
		{
		if (! $email) {
			alert("Please enter an email...");
			return false;
			}
		else if (! self::exists($email)) {
			alert($email . " is not registered currently.");
			return false;
			}

		$link = self::encrypt($email, self::salt());
		$full = 'http://' . $_SERVER['HTTP_HOST'] . "?confirmation_link=$link";

		\Email::send($email, "Reset Password", "A request for password reset has been submitted.\n\nIf you requested this, go to this confirmation link to reset your password: <a href='$full'>Reset Password</a>");

		\Db::match_update('user', array(
			'confirmation_link'=>$link,
			'confirmation_expires_at'=>date("Y-m-d H:i:s", strtotime('+1 day'))
			), " where email=" . db()->esc($email));

		return div('sesh-alert', "An email has been sent to $email.");
		}

	/**
		Mark user as email confirmed.
		*/
	static public function confirm($code)
		{
		if ($a = \Db::one_row("select * from user where confirmation_link="
			. db()->esc($code) . " and confirmation_expires_at>now()")) { // " and datediff(now(),created_on)<=1")) {
			db()->query("update user set confirmed=1 where confirmation_link=" . db()->esc($code));
			// \Email::send("fewkeep@gmail.com", "Confirmation request", "{$a['email']} has confirmed their link.");
			alert("Account confirmed! Please login below.");
			$_SESSION['login_id'] = $a['id'];
			self::check();
			return true;
			}
		// die("This is NOT good.<br />I would get off of your computer immediately if I were you.");
		alert('This confirmation link is expired or invalid. Please try the process again and if the problem persists contact support.');
		return false;
		}

	/**
		Reset password.
		*/
	static public function reset_password($email, $password, $confirm, $link)
		{
		if ($password != $confirm) {
			alert('The passwords entered do not match.');
			return false;
			}
		else if (strlen($password) < 8) {
			alert('Please choose a password of at least 8 characters.');
			return false;
			}
		else if (\Db::value("select 1 from user where confirmation_link=" . db()->esc($link)
		. " and confirmation_expires_at<now()")) {
			alert('This request is expired. Please re-submit.');
			return false;
			}

		$salt = self::salt();
		$up = \Db::match_update('user', array(
			'password'=>self::encrypt($password, $salt),
			'salt'=>$salt
			), " where email=" . db()->esc($email)
			. " and confirmation_link=" . db()->esc($link)
			. " and confirmation_expires_at>now()");

		if ($up) {
			alert('Your password has been reset.');
			return true;
			}
		else {
			alert('Error with password reset, please re-submit your request.');
			return false;
			}
		}
	}
