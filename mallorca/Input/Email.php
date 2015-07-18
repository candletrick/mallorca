<?php
namespace Input;

class Email extends \Input\Text
	{
	public function check($value) {
		if (! preg_match("/.*?@.*?\.\w+$/", $value)) {
			$this->refocus('The email address is not an accepted format.');
			return false;
			}
		return true;
		}
	}
