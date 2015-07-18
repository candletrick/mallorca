<?php
namespace Input;

class Toggle extends \Input
	{
	public function my_input()
		{
		$s = "<div class='toggle'><input type='hidden' id='{$this->name}' name='{$this->name}'>";
		foreach ($this->options as $k=>$v)
			{
			$s .= "<div class='box' onClick=\"
				$(this).addClass('selected');
				$(this).siblings().removeClass('selected');
				$('#{$this->name}').attr('value', $(this).find('span').html());
				\"><span class='text'>$v</span></div>";
			}
		$s .= "<div class='rug'></div></div>";
		return $s;
		}
	}
