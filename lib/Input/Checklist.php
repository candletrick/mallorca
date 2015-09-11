<?php
namespace Input;

/**
	Group of checkbox inputs.
	*/
class Checklist extends \Input
	{
	/** Array of input_check objects. */
	public $inputs;

	/** Display checks below. */
	public $checks_below = false;

	public function my_construct($inputs = array())
		{
		$this->inputs = is_array($inputs[0]) ? $inputs[0] : $inputs;
		}

	/**
		Make into a horizontal list.
		*/
	public function horiz()
		{
		return $this->add_class("horiz");
		}

	public function my_display()
		{
		$s = "<ul class='checklist $this->classes'>";
		foreach ($this->inputs as $input)
			{
			$label = "<span class='l'>$input->label</span>";
			$inp = "<span class='b'>" . $input->my_input() . "</span>";
			$s .= "<li>" . ($this->checks_below ? $label . $inp : $inp . $label) . "</li>";
			}
		return $s . "</ul>";
		}

	/**
		Display checks below.
		*/
	public function checks_below()
		{
		$this->checks_below = true;
		return $this->add_class('checks-below');
		}

	public function check($value)
		{
		return true;
		}
	}

