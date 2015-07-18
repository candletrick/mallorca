<?php
namespace Input;

class Password extends \Input
	{
	public function my_input()
		{
		return ($this->hint ? "<div class='mand'>$this->hint</div>" : '')
		. "<input class='input-text $this->classes' type='password' name='$this->name' value='" . $this->esc_value() . "'>";
		}

	public function check($value)
		{
		if (strlen($value) < 8) {
			$this->refocus('Please choose a password of at least 8 characters.');
			return false;
			}
		return true;
		}
	}
