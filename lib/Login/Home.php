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
				input_password('password')->label("Pa:")
					->set_value(cook('login_password')),
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
	static public function validate($data = array(), $bypass = false)
		{
		$ok = false;

		if ($bypass) {
			return true;
			}

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

		if (empty($row)) {
			alert("Email: " . $email . " is not recognized.");
			setcookie('login_email', '', -1000);
			setcookie('login_password', '', -1000);
			setcookie('login_remember', '', -1000);
			return false;
			}
		else if ($row['password'] != \Login::encrypt($password, $row['salt'])) {
			alert("Password is not correct.");
			return false;
			}
		else if (! $row['is_confirmed']) {
			alert("Please check your email to confirm your account.");
			\Login\Email::send_confirmation_email($email);
			return false;
			}
		else {
			// TODO this is for socrates only
			// $hard = "l0kxmal0y7&*";

			// cookies
			$expire = \Login::cookie_expire($remember);
			setcookie('login_email', $email, $expire);
			// TODO save password in hashed form
			setcookie('login_password', $password, $expire);
			setcookie('login_remember', $remember, $expire);

			alert('You are now logged in as ' . $email . '.');
			\Login::begin_session($row['id']);
			return true;
			}
		}
	}
