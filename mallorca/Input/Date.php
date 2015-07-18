<?php
namespace Input;

class Date extends \Input\Text
	{
	public function set_value($date)
		{
		$this->value = date_to("n/j/Y", $date);
		return $this;
		}
	}
