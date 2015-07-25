<?php
namespace Input;

class Submit extends \Input
	{
	
	function my_construct()
		{
		// $this->label = '';
		$this->classes
		}

	function my_input()
		{
		return "<input type='submit' name='$this->name' value='$this->label' class='$this->classes'>";
		}
	}
