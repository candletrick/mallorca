<?php
namespace Input;

class Time extends Select
	{
	public function my_construct($options = array())
		{
		$name = $this->name;
		$this->hours(4, 24);
		}

	public function hours($start, $end, $interval = 15)
		{
		$hours = array(''=>'None');
		foreach (range($start, $end) as $x) {
			$count = 0;
			while ($count < 60) {
				$index = ($x * 60) + $count;
				$time = ($x > 12 ? ($x - 12) : $x)
					. ':' . ($count == 0 ? '00' : $count)
					. ($x == 12 && $count == 0 ? ' PM (Noon)'
					: ($x == 24 && $count == 0 ? ' AM (Midnight)'
					: ($x >= 12 ? ' PM'
					: ' AM')));
				$hours[$index] = $time;
				$count += $interval;
				if ($x == $end) break;
				}
			}
			
		$this->options = $hours;
		$this->assoc = true;
		return $this;
		}

	public function set_value($time)
		{
		$ex = explode(':', $time);
		if (count($ex) < 2) return $this;
		$this->value = ((int)$ex[0] * 60) + $ex[1];
		return $this;
		}

	public function duration($min = 1, $other = 'end_time')
		{
		$min = 60 * $min;
		$this->addhtml .= "<script>
			$(document).ready(function() {
				var sth = $('#$this->name');
				var eth = $('#$other');
				sth.change(function() {
					var s = +$(this).attr('value');
					var e = +eth.attr('value');
					if (e - s < $min) eth.attr('value', $min + s);
					});
				eth.change(function() {
					var s = +sth.attr('value');
					var e = +$(this).attr('value');
					if (e - s < $min) sth.attr('value', e - $min);
					});
				});
			</script>";
		return $this;
		}
	}
