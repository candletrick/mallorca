<?php
namespace Input;

class Check extends \Input
	{
	public function my_input()
		{
		$sel = $this->value ? ' checked ' : '';
		return "<input type='hidden' value='0' name='$this->name'>"
		. "<input type='checkbox' name='$this->name' value='1' $sel>";
		}
	}
