<?php

class Logout extends \Module
	{
	public function __construct()
		{
		unset($_SESSION['login_id']);
		session_destroy();
		session_start();

		setcookie('login_email', '', (3600 * -1));
		setcookie('login_password', '', (3600 * -1));
		setcookie('login_remember', '', (3600 * -1));
		setcookie('logout', true, (3600 * 60));

		$_COOKIE = array();
		$_SESSION['logout'] = true;

		alert('You are now logged out.');
		// \Request::base_redir('user/login');
		\Request::redir('user/login');
		}

	public function my_display()
		{

		}
	}
