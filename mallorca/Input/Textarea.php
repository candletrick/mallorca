<?php
namespace Input;

class Textarea extends \Input
	{
	public function my_input()
		{
		return "<textarea class='$this->classes' name='$this->name' rows='4' cols='44'>"
		. $this->esc_value() . "</textarea>";
		}
	}
