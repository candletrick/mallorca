<?php
namespace Login;

/**
	Forgot password form.
	*/
class Forgot extends \Module
	{
	/**
		*/
	public function my_display()
		{
		return div('login-wrapper',
			div('control', div('label'), div('input coral', 'Forgot<br>Password')),
			action_group(array(
				input_text('email', 60)->label('Em:'),
				input_button('Send Reset Email')->add_class('data-enter')->click([
					self::call('forgot_action'),
					call_path('login/home'),
					// call($this, 'my_display')->html('.m-content')
					]),
				sesh_alert(),
				))->my_display()
			);
		}

	/**
		*/
	static public function forgot_action()
		{
		\Login\Email::send_reset_email(is(\Request::$data, 'email'));
		}
	}
