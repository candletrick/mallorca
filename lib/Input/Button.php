<?php
namespace Input;

/**
	Button
	*/
class Button extends \Input
	{
	/**
		data-fn actions to be performed.
		\sa stack()
		*/
	public $data_fn;

	/** JS function to run after click event.  */
	public $after_fn;
	
	/** JS function to run before click event.  */
	public $before_fn;

	/** Param for $before_fn */
	public $before_param;

	/**
		*/
	function my_construct()
		{
		$this->my_label = $this->label;
		$this->label = '';
		$this->classes = 'button';
		}

	/**
		Set label.
		Needs to be a little different for a button.
		*/
	function label($label = '')
		{
		$this->my_label = $label;
		return $this;
		}

	/**
		*/
	function my_input()
		{
		$label = htmlspecialchars($this->my_label, ENT_QUOTES);
		return "<input type='$this->type' name='$this->name' value='$label' class='$this->classes' "
		. ($this->data_fn ? " data-fn=\"$this->data_fn\" " : '')
		. ($this->after_fn ? " after-fn=\"$this->after_fn\" " : '')
		. ($this->before_fn ? " before-fn=\"$this->before_fn\" " : '')
		. ($this->before_fn ? " before-fn-param=\"$this->before_param\" " : '')
		. " $this->attrs>";
		}

	/**
		Indicate JS functions (from effects.js) to run before click event.
		\param	string	$before	Function name.
		\param	string	$param	Parameter if there is one.
		*/
	function before($before, $param = '')
		{
		$this->before_fn = $before;
		$this->before_param = $param;
		return $this;
		}

	/**
		Indicate JS functions (from effects.js) to run after click event.
		\param	string	$after	Function name.
		*/
	function after($after)
		{
		$this->after_fn = $after;
		return $this;
		}

	/**
		Indicate a mallorca-style call stack to be performed on click.
		\param	array	$xs	Array of call() function calls.
		*/
	function click($xs = array())
		{
		$this->classes .= " data-fn action";
		$this->data_fn = stack($xs);
		return $this;
		}

	function stack($xs = array(), $after_fn = '')
		{
		$this->classes .= " action";
		$this->data_fn = stack($xs);
		if ($after_fn) $this->after_fn = $after_fn;
		return $this;
		}
	/*
	// phase this out

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

	function refresh($refresh, $stack)
		{
		list($class, $fn) = $refresh;
		$stack['.' . $fn] = fn($class . '::' . $fn);
		return $this->stack($stack);
		}
		*/

	/**
		*/
	function set_value($v)
		{
		$this->attrs .= " data-value='$v' ";
		return $this;
		}
	}
