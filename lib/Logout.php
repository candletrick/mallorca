<?php

class Logout extends \Module
	{
	public function __construct()
		{
		unset($_SESSION['login_id']);
		session_destroy();
		session_start();

		$path = '/';
		$domain = '.' . \Config::$domain;
		setcookie('login_email', '', (3600 * -1), $path, $domain);
		setcookie('login_password', '', (3600 * -1), $path, $domain);
		setcookie('login_remember', '', (3600 * -1), $path, $domain);
		setcookie('logout', true, (3600 * 60), $path, $domain);

		$_COOKIE = array();
		$_SESSION['logout'] = true;

		alert('You are now logged out.');
		\Path::base_redir('login/home');
		// \Request::redir('login/home');
		}

	public function my_display()
		{

		}
	}
