<?php
namespace Input;

class Radio extends \Input
	{
	public function my_construct($options = array())
		{
		$this->options = $options;
		$this->assoc = range(0, count($options) - 1) !== array_keys($options);
		}

	public function assoc()
		{
		$this->assoc = true;
		return $this;
		}

	public function my_input()
		{
		$s = "<ul class='radio $this->classes'>";
		$i = 0;
		foreach ($this->options as $k=>$v)
			{
			$display = $v;
			$value = $this->assoc ? $k : $v;
			$sel = strcmp($this->value, $value) == 0 || ($i == 0 && ! isset($this->value)) ? ' checked ' : '';
			$s .= "<li><input type='radio' name='$this->name' value='$value' $this->attrs $sel><span class='text'>$display</span></li>";
			$i++;
			}
		return $s . "</ul>";
		}

	public function horiz()
		{
		$this->classes .= " horiz";
		return $this;
		}

	public function click_toggle()
		{
		$classes = array();
		foreach ($this->options as $k=>$v) {
			$classes[] = '.' . $this->name . '_' . $k;
			}

		$this->attrs .= " onClick=\"
			$('" . implode(', ', $classes) . "').hide();
			$('." . $this->name . "_' + $(this).attr('value')).show();
			\"
			";
		return $this;
		}
	}
