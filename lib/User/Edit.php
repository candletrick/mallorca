<?php
namespace User;

class Edit extends \Module\Edit
	{
	public function my_fields()
		{
		return [
			input_hidden('id', $this->index->id),
			input_text('email'),
			input_text('new_password'),
			input_check('is_confirmed'),
			input_check('is_admin'),
			$this->save_button(),
			];
		}
	}
