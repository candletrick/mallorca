<?php
namespace User;

class Model extends \Model
	{
	public function my_table()
		{
		return 'user';
		}
	
	public function my_columns()
		{
		return [
			'id',
			on('name')
				->data->type(['str', 140])->end
				->lookup->asc()->end,
			on('email')
				->data->type(['str', 64])->end
				->lookup->end,
			on('password')
				->data->type(['str', 64])->end,
			on('salt')
				->data->type(['str', 32])->end,
			on('confirmation_link', 64)
				->data->type(['str', 64])->end,
			'confirmation_expires_at',
			'is_confirmed',
			'user_type_id',
			'is_deleted',
			'created_on',
			];
		}
	}
