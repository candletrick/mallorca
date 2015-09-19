<?php
namespace Login;

/**
	Main login page.
	*/
class Home extends \Module
	{
	/**
		*/
	public function my_display() 
		{
		// login with cookies
		if (cook('login_email') && cook('login_password')) {
			$ok = self::validate(array(
				'email'=>cook('login_email'),
				'password'=>cook('login_password'),
				), true);
			if ($ok) \Path::base_redir(\Config::$home_path);
			}

		return self::my_form();
		}

	/**
		*/
	static public function my_form()
		{
		return ''
		. div('login-wrapper',
			div('control', div('label'), div('input coral', 'Login')),
			action_group([
				input_text('email')->label("Em:")
					->set_value(cook('login_email')),
				input_password('password')->label("Pa:"),
					// ->set_value(cook('login_password')),
				input_check('remember')->add_label_class('small')->label('Remember Me')
					->set_value(cook('login_remember')),
				input_button('Login')->add_class('data-enter')->label('ThY')->click(array_merge(array(
					self::call('validate_and_begin')->html('.m-content'),
					), website())),
				div('forgot', \Path::link_to('Forgot Password', 'login/forgot')),
				div('forgot', \Path::link_to('Register', 'login/register')),
				sesh_alert()
				])->data(\Request::$data)->my_display()
			);
		}

	/**
		*/
	static public function validate_and_begin()
		{
		$ok = self::validate();
		if (! $ok) {
			\Request::kill();
			return self::my_form();
			}
		}

	/**
		Validate user.
		\param	array	$d	User data array (email, password, confirmed).
		*/
	static public function validate($data = array(), $from_cookie = false)
		{
		$ok = false;

		if (empty($data)) {
			$data = \Request::$data;
			}

		if (empty($data)) {
			alert('Please login.');
			return false;
			}

		$email = is($data, 'email');
		$password = is($data, 'password');
		$remember = is($data, 'remember');

		// permanent admin
		$admin = \Login::$admin;
		if ($email == $admin['email'] && $password == $admin['password']) {
			\Login::begin_session($admin['id']);
			return true;
			}
			
		$row = select('user', array(
			'*',
			m('email')->where($email)
			))->one_row();

		$salted = $from_cookie ? $password : \Login::encrypt($password, $row['salt']);
		$remember = $from_cookie ? 1 : 0;

		// die($password . "\n" . $row['password'] . "\n" . var_dump(strcmp($password, $row['password'])));
		if (empty($row)) {
			alert("Email: " . $email . " is not recognized.");
			setcookie('login_email', '', -1000);
			setcookie('login_password', '', -1000);
			setcookie('login_remember', '', -1000);
			return false;
			}
		else if (! $row['is_confirmed']) {
			alert("Please check your email to confirm your account.");
			\Login\Email::send_confirmation_email($email);
			return false;
			}
		// the bypass allows the salted password to be used, from a cookie
		else if ($row['password'] == $salted) {
			// TODO this is for socrates only
			// $hard = "l0kxmal0y7&*";

			// cookies
			$path = '/';
			// the leading dot allows it to work for all subdomains
			$domain = '.' . \Config::$domain;
			$expire = \Login::cookie_expire($remember);
			setcookie('login_email', $email, $expire, $path, $domain);
			setcookie('login_password', $salted, $expire, $path, $domain);
			setcookie('login_remember', $remember, $expire, $path, $domain);
			// die('about to set cookies' . $expire . $email . $salted . $remember);

			alert('You are now logged in as ' . $email . '.');
			\Login::begin_session($row['id']);
			return true;
			}
		else {
			alert("Password is not correct.");
			return false;
			}
		}
	}
