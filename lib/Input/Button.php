<?php
namespace Input;

class Button extends \Input
	{
	public $data_fn;
	public $after_fn;

	function my_construct()
		{
		$this->value = $this->label;
		$this->label = '';
		$this->classes = 'button';
		}

	function my_input()
		{
		$label = htmlspecialchars($this->value, ENT_QUOTES);
		return "<input type='$this->type' name='$this->name' value='$label' class='$this->classes' "
		. ($this->data_fn ? " data-fn=\"$this->data_fn\" " : '')
		. ($this->after_fn ? " after-fn=\"$this->after_fn\" " : '')
		. " $this->attrs>";
		}

	function refresh($refresh, $stack) {
		list($class, $fn) = $refresh;
		$stack['.' . $fn] = fn($class . '::' . $fn);
		return $this->stack($stack);
		}
		
	function stack($xs = array(), $after_fn = '')
		{
		$this->classes .= " action";
		$this->data_fn = stack($xs);
		if ($after_fn) $this->after_fn = $after_fn;
		return $this;
		}

	function merge($fn)
		{
		$this->data_fn .= merge($fn);
		return $this;
		}

	function link_to($path, $params = array())
		{
		$this->attrs = " onClick=\"window.location = '" . \Path::http() . \Path::base_to($path, $params) . "'; return false;\" ";
		return $this;
		}

	function set_value($v)
		{
		$this->attrs .= " data-value='$v' ";
		return $this;
		}
	}
