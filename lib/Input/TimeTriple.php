<?php
namespace Input;

/**
	Three part time select.
	*/
class TimeTriple extends DateTriple
	{
	public function my_construct()
		{
		$name = $this->name;
		$hours = range(1, 12);
		$minutes = array('00', '15', '30', '45');
		$periods = array('PM', 'AM');

		$this->parts = array(
			input_select($name . '[hour]', $hours),
			input_select($name . '[minute]', $minutes),
			input_select($name . '[period]', $periods),
			);
		}

	public function set_value($time)
		{
		$this->parts[0]->set_value(date_to('g', $time));
		$this->parts[1]->set_value(date_to('i', $time));
		$this->parts[2]->set_value(date_to('A', $time));
		return $this;
		}
	
	}
