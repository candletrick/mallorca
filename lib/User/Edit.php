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
			$this->save_button(),
			];
		}
	}