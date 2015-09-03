<?php

/**
	This class follows the pattern of Meta,
	but is more general for the experimental pattern of defining edit, lookup, data type, etc
	type properties directly chained from a table definition.

	This class also allows "double" chaining.

	For example:
	\code
	on('name')
		->lookup->asc()->end
		->edit->input('text', 25)->end
		->data->type('str', 25)->end
	\endcode

	When a property is called it becomes a "child" On class.
	When a function is called, it behaves the same way as in Meta, that is the function name becomes the property name,
	and the parameters become it's value.
	
	The ->end property "escapes" back out the parent On class, allowing for the double chaining effect.
	*/
class On
	{
	/** Whether the class is a child. */
	private $child = false;

	/** Parent On class. */
	private $parent;

	/** The one reserved property, the name of the parent. */
	private $name;

	/**
		\return $this->name
		*/
	public function get_name()
		{
		return $this->name;
		}

	/**
		\param	string	$name	The name.
		*/
	public function __construct($name)
		{
		$this->name = $name;
		}

	/**
		Create a child On class.
		*/
	public function __get($name)
		{
		if ($name == 'end') return $this->parent;

		$th = $this->child ? $this->parent : $this;
		$th->$name = on($name);
		$th->$name->child = true;
		$th->$name->parent = $th;
		return $th->$name;
		}

	/**
		\param	string	$fn	The function will be set as a property.
		\param	array	$params	It's value will be the parameters.
		*/
	public function __call($fn, $params = array(1))
		{
		if (property_exists($this, $fn) && is_callable($this->$fn)) {
			return call_user_func_array($this->$fn, $params);
			}
		// default value of 1
		if (empty($params)) $this->$fn = 1;
		else {
			// $shift = array_shift($params);
			// echo pv($params);
			$this->$fn = $params;
			}
		return $this;
		}

	/**
		Shorthand for ->data->type()
		*/
	public function set_type($type, $opt = '')
		{
		return $this->data->type($type, $opt)->end;
		}
	}

