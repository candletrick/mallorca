<?php
namespace Input;

/**
	Display a date as 3 select boxes, Y M D
	*/
class DateTriple extends \Input
	{
	/** Sub select box fields. */
	public $parts = array();

	public function my_construct()
		{
		$name = $this->name;
		$days = range(1, 31);
		$months = array_combine(range(1, 12), array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'));
		$years = range(1990, 2016);
		$this->parts = array(
			input_select($name . '[month]', $months),
			input_select($name . '[day]', $days),
			input_select($name . '[year]', $years),
			);
		}

	public function set_value($date)
		{
		$this->parts[0]->set_value(date_to('n', $date));
		$this->parts[1]->set_value(date_to('j', $date));
		$this->parts[2]->set_value(date_to('Y', $date));
		return $this;
		}

	public function my_input()
		{
		$s = '';
		foreach ($this->parts as $k=>$v)
			{
			$s .= $v->add_class('triple')->my_display() . ' ';
			}
		return $s . "<div class='rug'></div>";
		}
	}
