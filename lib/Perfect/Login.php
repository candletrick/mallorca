<?php
namespace Perfect;

/**
	Standard login.
	*/
class Login extends \Perfect
	{
	/**
		Normal display.
		*/
	public function my_display($label = 'Login', $after = 'validate', $data = [])
		{
		return ''
		. div('login-wrapper',
			div('control', div('label'), div('input coral', $label)),
			div('control', div('label'), div('input', alert(true))),
			action_group([
				input_text('email', 20)->label("Em:")
					->set_value(cook('login_email')),
				input_password('password')->label("Pa:")
					->set_value(cook('login_password')),
				input_check('remember')->add_label_class('small')->label('Remember Me')
					->set_value(cook('login_remember')),
				input_button('Login')->add_class('data-enter')->label('ThY')->click(array_merge([
					call($this, $after)->html('.content'),
					], website())),
				sesh_alert()
				])->data($data)->my_display()
			);
		}

	/**
		Registration display.
		*/
	public function my_register()
		{
		if (req('confirmation_link')) {
			$ok = self::confirm(req('confirmation_link'));
			if ($ok) return $this->my_display();
			}

		return $this->my_display('Register', 'register');
		}

	/**
		Validate user.
		\param	array	$d	User data array (email, password, confirmed).
		*/
	public function validate()
		{
		$ok = false;

		$d = \Request::$data;
		if (empty($d)) {
			if (! sesh('alert')) alert('Please login below..');
			}
		else {
			$a = \Db::one_row("select * from user where email=" . \Db::esc(is($d, 'email')));

			if (! $a) alert("Email not recognized.");
			else if ($a['password'] != self::encrypt(is($d, 'password'), $a['salt'])) alert("Password is not correct.");
			else if (! $a['is_confirmed']) alert("Please check your email to confirm your account.");
			else {
				// cookies
				$expire = is($d, 'remember') ? time() + (3600 * 72) : time() - 1000;
				setcookie('login_email', $d['email'], $expire);
				setcookie('login_password', $d['password'], $expire);
				setcookie('login_remember', is($d, 'remember'), $expire);

				alert('You are now logged in as ' . $a['email'] . '.');
				$_SESSION['login_id'] = $a['id'];
				$ok = true;
				}
			}

		if (! $ok) {
			\Request::kill();
			return $this->my_display('Login', 'validate', $d);
			}

		return $ok;
		// die(pv($ok));
		}

	/**
		Register new user.
		*/
	public function register()
		{
		$d = \Request::$data;
		$ok = self::create(is($d, 'email'), is($d, 'password'), $d);

		if ($ok) return 'Lok OK!';
		else return $this->my_register();
		}

	/**
		See if user exists.
		*/
	static public function exists($email)
		{
		return \Db::value("select 1 from user where email=" . \Db::esc($email));
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
			'is_confirmed'=>0,
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
		$full = \Path::http() . \Config::$local_path . "user/register?confirmation_link=$link";

		$subject = "Registration for " . $_SERVER['HTTP_HOST'];
		$msg = "Please confirm your email address by following this link: <a target='_blank' href='$full'>$full</a>";

		\Email::send($email, $subject, $msg); 
		\Db::match_update('user', array(
			'confirmation_link'=>$link,
			'confirmation_expires_at'=>date('Y-m-d H:i:s', strtotime('+30 day')),
			), " where email=" . \Db::esc($email));
		}

	/**
		Mark user as email confirmed.
		*/
	static public function confirm($code)
		{
		$ok = false;
		$a = \Db::one_row("select * from user where confirmation_link=" . \Db::esc($code)
		. " and datediff(now(),created_on)<=1");

		if (! empty($a)) {
			\Db::query("update user set is_confirmed=1 where confirmation_link=" . \Db::esc($code));
			\Email::send("fewkeep@gmail.com", "Confirmation request", "{$a['email']} has confirmed their link.");
			alert("Account confirmed! Please login below.");
			$ok = true;
			}
		else {
			alert("You have alerted coral!<br />This is NOT good.<br />I would get off of your computer immediately if I were you.");
			}

		return $ok;
		}
	}
