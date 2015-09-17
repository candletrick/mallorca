<?php
namespace User;

class Lookup extends \Module\Lookup
	{
	public function my_query()
		{
		return Model::select();
		}

	public function my_columns()
		{
		return [
			'email',
			];

		}
	}
