<?php
namespace Input;

class Hidden extends \Input
	{
	function my_input()
		{
		return "<input type='hidden' name='{$this->name}' value='" . $this->esc_value() . "'>";
		}
	}
