<?php
namespace User;

/**
	Standard user table structure.

	Pairs with the Perfect\Login class.
	*/
class Model extends \Model
	{
	use \NoAuth;

	public function my_table()
		{
		return 'user';
		}
	
	public function my_columns()
		{
		return [
			'id',
			on('name')->set_type('str', 140)->lookup->asc()->end,
			on('email')->set_type('str', 64)->lookup->end,
			on('password')->set_type('str', 64),
			on('salt') ->set_type('str', 32),
			on('confirmation_link', 64)->set_type('str', 64),
			'confirmation_expires_at',
			'user_type_id',
			'is_admin',
			'is_confirmed',
			'is_deleted',
			on('remember')->set_type('bool'),
			'created_on',
			];
		}

	public function my_save()
		{
		$data = \Request::$data;
		$np = is($data, 'new_password');

		if ($np) {
			$salt = \Login::salt();
			\Request::$data['password'] = \Login::encrypt($np, $salt);
			\Request::$data['salt'] = $salt;
			}

		parent::my_save();
		}

	/*
	public function register()
		{
		$login = new \Perfect\Login();
		return $login->my_register();
		}

	public function login()
		{
		$login = new \Perfect\Login();
		return $login->my_display();
		}

	public function forgot()
		{
		$login = new \Perfect\Login();
		return $login->forgot_display();
		}

	public function reset()
		{
		$login = new \Perfect\Login();
		return $login->reset_display();
		}
		*/
	}
