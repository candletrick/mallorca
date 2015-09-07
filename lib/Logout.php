<?php

class Logout extends \Module
	{
	public function __construct()
		{
		unset($_SESSION['login_id']);
		session_destroy();
		session_start();

		// $expire = is($d, 'remember') ? time() + (3600 * 72) : time() - 1000;
		setcookie('logout', true, (3600 * 60));
		// die(pv($_COOKIE));
		$_SESSION['logout'] = true;
		alert('You are now logged out.');
		// \Request::base_redir('user/login');
		\Request::redir('user/login');
		}

	public function my_display()
		{

		}
	}
