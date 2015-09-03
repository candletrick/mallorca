<?php

class Logout extends \Module
	{
	public function __construct()
		{
		unset($_SESSION['login_id']);
		session_destroy();
		session_start();
		alert('You are now logged out.');
		\Request::base_redir('user/login');
		}

	public function my_display()
		{

		}
	}
