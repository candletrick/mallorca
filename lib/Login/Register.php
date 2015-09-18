<?php
namespace Login;

class Register extends \Module	
	{
	/**
		Registration display.
		*/
	public function my_display()
		{
		return self::my_form();
		/*
		$link = is(\Request::$json_get, 'confirmation_link');
		if ($link) {
			$ok = self::confirm($link);
			$web = website();
			\Request::send(array_pop($web)); 
			\Request::kill();
			}

		return $this->my_display('Register', 'register');
		*/
		}

	/**
		*/
	static public function my_form()
		{
		return ''
		. div('login-wrapper',
			div('control', div('label'), div('input coral', 'Register')),
			action_group([
				input_text('email')->label("Email:"),
				input_password('password')->label("Password:"),
				input_button('Register')->add_class('data-enter')->click(array(
					self::call('register')->html('.m-content'),
					)),
				sesh_alert()
				])->my_display()
			);
		}

	/**
		Register new user.
		*/
	static public function register()
		{
		$data = \Request::$data;
		$email = is($data, 'email');
		$password = is($data, 'password');

		$ok = \Login::create($email, $password, $data);

		// \Request::kill();

		if ($ok) {
			alert('An email has been sent to ' . $email . ' for confirmation.');
			return \Login\Home::my_form();
			}
		else {
			return self::my_form();
			}
		}
	}
