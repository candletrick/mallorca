<?php

class Login extends \Module
	{
	use \NoAuth;

	/** Login id. */
	static public $id = 0;

	/** Escaped id for queries. */
	static public $esc = 0;

	/** User data. */
	static private $data = array();

	/** Permanent login. */
	static public $admin = array(
		'id'=>999999,
		'email'=>'admin',
		'password'=>'0987654321',
		'is_confirmed'=>true,
		'is_admin'=>true,
		);
	
	/**
		\param	string	$name	Data key.
		\return Get user data by key.
		*/
	static public function get($name, $else = '')
		{
		return array_key_exists($name, self::$data) ? self::$data[$name] : $else;
		}
	/**
		Check if the user is logged in.
		Runs every page load.
		*/
	static public function check()
		{
		// check confirmation links
		$link = get('confirmation_link');
		if ($link) {
			$ok = self::confirm($link);

			if ($ok) {
				$row = self::confirm_row($link);
				if (get('reset')) {
					alert('Please reset your password for ' . $row['email'] . '.');
					\Path::base_redir('login/reset');
					}
				else {
					// TODO make more stringent later
					// allow confirmation link to log you in
					// self::begin_session($row['id']);
					$_SESSION['login_id'] = $row['id'];
					\Path::base_redir('/');
					}
				}
			}

		$id = id_zero(sesh('login_id'));

		if ($id == self::$admin['id']) self::$data = self::$admin;
		else {
			self::$data = select('user', ['*', m('id')->where($id)])->one_row() ?: array();
			}

		if (empty(self::$data)) {
			if (is($_SERVER, 'REQUEST_METHOD') == 'POST') {
				// die('Not logged in.');
				return false;
				}
			else if (! in_array(get('q'), array(
				'login/home',
				'login/register',
				'login/forgot',
				'login/reset',
				))) {
				\Path::base_redir('login/home');
				}
			}

		self::$id = $id;
		self::$esc = \Db::esc($id);

		return true;
		}

	static public function begin_session($id)
		{
		$_SESSION['login_id'] = $id;
		self::check();
		}

	/**
		\return true If logged in user is admin.
		*/
	static public function is_admin()
		{
		return self::get('is_admin');
		}

	/* DISPLAY FUNCTIONS */

	public function dismiss_alert($id = 0)
		{
		if (isset($_SESSION['alert'][$id])) unset($_SESSION['alert'][$id]);
		}

	/**
		*/
	public function valid_check()
		{
		$ok = $this->validate();
		if (! $ok) {
			\Request::kill();
			return $this->my_display();
			}
		}
	
	/**
		See if user exists.
		*/
	static public function exists($email)
		{
		return \Db::value("select id from user where email=" . \Db::esc($email));
		}

	/**
		Create salt.
		*/
	static public function salt()
		{
		return base64_encode(openssl_random_pseudo_bytes(24));
		}

	/**
		Encrypt password.
		*/
	static public function encrypt($password, $salt)
		{
		return hash_hmac('sha256', $salt . $password, "h0gburn");
		}

	/**
		Cookies valid for 3 days.
		*/
	static public function cookie_expire($remember = true)
		{
		return $remember ? time() + (3600 * 72) : time() - 1000;
		}

	/**
		Create new user.
		*/
	static public function create($email, $password, $data = array(), $notify = true)
		{
		if (! $email || ! $password) {
			alert("Please enter an email and password...");
			return false;
			}

		$id = self::exists($email);
		if ($id) {
			alert("There is already an account for $email.");
			return $id;
			}

		$salt = self::salt();
		$new_id = \Db::match_insert("user", array(
			'name'=>is($data, 'name', $email),
			'email'=>$email,
			'salt'=>$salt,
			'password'=>self::encrypt($password, $salt),
			'created_on'=>date("Y-m-d H:i:s"),
			'confirmation_link'=>'',
			'is_confirmed'=>0,
			));

		if ($notify) \Login\Email::send_confirmation_email($email, $data);
		// $_SESSION['verify_email'] = $email;

		return $new_id;
		}

	/**
		*/
	static public function try_confirmation_link()
		{
		/*
		// see if link present
		$link = is(\Request::$json_get, 'confirmation_link');
		if ($link) {
			$ok = self::confirm($link);
			if ($ok) {
				return true;
				}
			return false;
			}

		// login with cookies
		$ok = false;
		$email = cook('login_email');
		$password = cook('login_password');
		if (! cook('logout') && ! sesh('logout') && $email && $password) {
			$login = new \Perfect\Login();
			$ok = $login->validate(array(
				'email'=>$email,
				'password'=>$password,
				'remember'=>true
				));

			return $ok;
			}

		$document_id = is(\Request::$json_get, 'document_id');
		if ($document_id) unset($_SESSION['logout']);
		
		if (! $ok && ! sesh('logout')) {
			// try off document_id
			if (! $email && $document_id) {
				// $document_id = is(\Request::$json_get, 'document_id');
				$user = select('document', array(
					m('id')->where($document_id),
					m('email')->left('user', ['id'=>'created_by'])
					))->one_row();
				if (! empty($user)) {
					$email = $user['email'];
					}
				}
			if ($email) {
				// alert('Your session has expired. An email is being sent to re-enable.');
				self::send_confirmation_email($email, [], true);
				}
			else {
				// alert('Please login below..');
				}
			}

		return $ok;
		*/
		}

	static public function confirm_row($link)
		{
		return \Db::one_row("select * from user where confirmation_link=" . \Db::esc($link)
		. " and datediff(confirmation_expires_at, now())>=0"); //  and is_confirmed<>1");
		}

	/**
		Mark user as email confirmed.
		*/
	static public function confirm($link)
		{
		$row = self::confirm_row($link);

		if (! empty($row)) {
			\Db::query("update user set is_confirmed=1 where confirmation_link=" . \Db::esc($link));

			$_SESSION['confirmation_link'] = $link;
			setcookie('login_email', $row['email'], self::cookie_expire());

			alert("Account confirmed! Please login.");

			// self::begin_session($row['id']);
			// \Path::base_redir('/');

			return true;
			}
		else {
			alert("Game over, you've been found out.");
			return false;
			}
		}

	/**
		Reset user password.
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
		$update = \Db::match_update('user', array(
			'password'=>self::encrypt($password, $salt),
			'salt'=>$salt,
			'is_confirmed'=>1
			), " where email=" . db()->esc($email)
			. " and confirmation_link=" . db()->esc($link)
			. " and confirmation_expires_at>now()");

		if ($update) {
			$row = self::confirm_row($link);
			self::begin_session($row['id']);
			alert('Password reset successfully');
			return true;
			}
		else {
			alert('Error with password reset, please re-submit your request.');
			return false;
			}
		}

	/**
		A function to run before making calls via request.
		TODO make it so that it only calls once
		\param 	string	$class	The name of the class calling.
		*/
	static public function before_call($class)
		{
		/*
		$uses = class_uses($class);
		$logged_in = self::check();
		// die(pv($_SESSION));

		if (! $logged_in) {

			// try to login with cookies
			$ok = \Perfect\Login::try_confirmation_link();
			// die(pv($ok));

			if ($ok) {
				// continue
				$web = website();
				\Request::send(array_pop($web));
				\Request::kill();
				}
			if (in_array('NoAuth', $uses)) {
				// OK (login class being called)
				}
			else {
				\Request::send(call_path('user/login'));
				\Request::kill();
				}
			}
			*/
		}
	}
