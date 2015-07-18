<?php
namespace Input;

class Select extends \Input
	{
	public $assoc = false;

	public function my_construct($options = array())
		{
		$this->options = $options;
		$this->assoc = array_keys($options) !== range(0, count($options) - 1);
		}

	public function my_input()
		{
		$id = str_replace('[', '_', str_replace(']', '', $this->name));
		$s = "<select class='$this->classes' name='$this->name' id='$id'>";
		foreach ($this->options as $k=>$v)
			{
			$value = $this->assoc ? $k : $v;
			$sel = strcmp($this->value, $value) == 0 ? ' selected ' : '';
			$s .= "<option value='$value' $sel>$v</option>";
			}
		$s .= "</select>";
		return $s;
		}
	}
