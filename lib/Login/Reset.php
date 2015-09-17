<?php
namespace Login;

class Reset extends \Module
	{
	public function my_display()
		{
		return self::my_form();
		}

	static public function my_form()
		{
		// $get = \Request::$json_get;

		return div('login-wrapper',
			// pv($_REQUEST),
			div('control', div('label'), div('input coral', 'Reset<br>Password')),
			div('notice', (get('email') ? 'For ' . get('email') : '' )),
			// sesh_alert(),
			action_group(array(
				input_password('password', 60),
				input_password('password_confirm', 60)->label('Confirm'),
				// input_hidden('link', is($get, 'link')),
				// input_hidden('email', is($get, 'email')),
				input_button('Reset')->add_class('data-enter')->click(array_merge(array(
					self::call('handle_reset')->html('.m-content'),
					// call($this, 'my_display')->html('.m-content')
					// call_path('user/login')
					), website())),
				sesh_alert()
				))->my_display()
			);
		}

	/**
		*/
	static public function handle_reset()
		{
		$data = \Request::$data;
		// $email = is($d, 'email');
		$row = \Login::confirm_row(sesh('confirmation_link'));

		$password = is($data, 'password');
		$confirm = is($data, 'password_confirm');
		// $link = is($d, 'link');

		$ok = \Login::reset_password($row['email'], $password, $confirm, $row['confirmation_link']);
		if (! $ok) {
			\Request::kill();
			return self::my_form();
			}
		return $ok;
		}
	}
