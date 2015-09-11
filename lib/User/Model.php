<?php
namespace User;

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
			on('name')
				->data->type('str', 140)->end
				->lookup->asc()->end,
			on('email')
				->data->type('str', 64)->end
				->lookup->end,
			on('password')
				->data->type('str', 64)->end,
			on('salt')
				->data->type('str', 32)->end,
			on('confirmation_link', 64)
				->data->type('str', 64)->end,
			'confirmation_expires_at',
			'user_type_id',
			'is_admin',
			'is_confirmed',
			'is_deleted',
			on('remember')
				->data->type('bool')->end,
			'created_on',
			];
		}

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
	}
