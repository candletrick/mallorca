<?php

/**
	Viewable class.
	stack() function must exist in a class extending this.
	*/
abstract class View {
	/** Array of function names that are bound / auto-refreshing / self-updating. */
	static public $bound;

	/**
		Array of function names and default parameters that are permitted to be called.
		This also ensures type safety!
		Ex) array(
			'my_view'=>array(
				'msg'=>'hello'
				)
			)
		*/
	static public $allowed;

	/**
		Use this function to put a self-binding refresh wrapper around your function.
		However, when you define your function, it must take as it's first parameter an array.
		This array will contain the class name and function name and can be based, for instance,
		to input_button->refresh($refresh, array(...
		to bind the self-refreshing action to the stack.
		*/
	static public function bound($name) {
		$args = func_get_args();
		$name = array_shift($args);
		array_unshift($args, array(get_called_class(), $name));
		static::$allow[$name] = array('refresh'=>array(get_called_class(), $name));
		return div($name, call_user_func_array("static::$name", $args));
		}
	}
