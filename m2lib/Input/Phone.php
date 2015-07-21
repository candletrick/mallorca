<?php
namespace Input;

class Phone extends \Input\Text
	{
	public function my_construct($len = 12, $value = '')
		{
		$this->len = 12;
		$this->value = $value;
		}

	public function check($value)
		{
		if (strlen($value) != $this->len) {
			$this->refocus('Phone number is too short.');
			return false;
			}
		return true;
		}
	}
