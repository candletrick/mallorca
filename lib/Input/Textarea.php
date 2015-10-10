<?php
namespace Input;

class Textarea extends \Input
	{
	public $len;

	public function my_construct($len = 250)
		{
		$this->len = $len;
		}

	public function my_input()
		{
		// return "<textarea class='$this->classes' name='$this->name' rows='4' cols='44'>"
		// . $this->esc_value() . "</textarea>";
		return "<div class='tbox'><div class='counter' id='" . $this->name . "_count'>$this->len</div>"
		. "<textarea name='$this->name' id='$this->name'"
		// . " rows='$this->rows' cols='$this->columns'
		. " onfocus=\"return char_counter(event, this, $this->len);\"
		onkeydown=\"return char_counter(event, this,$this->len);\"
		onpaste=\"return char_counter(event, this,$this->len);\">$this->value</textarea><div class='rug'></div></div>";
		}
	}
