<?php

/**
	Prepare instructions for the server side function call.

	\sa call()
	\sa call_path()
	*/
class ServerCall
	{
	/** Properties. */
	public $props;

	/**
		\param	array	$props	Array of properties.
		*/
	function __construct($props)
		{
		$this->props = $props;
		}

	/**
		Send the results of a function call (returning $this),
		into another method of the class with optional params.
		\param	string	$fn	Function name.
		\param	array	$params	Parameters.
		*/
	function pipe($fn, $params = array())
		{
		$this->props['functions'][] = $fn;
		$this->props['params'][] = $params;
		return $this;
		}

	/**
		\defgroup jquery_methods jQuery HTML manipulation methods.

		Their counterparts are defined in mallorca.js
		@{ */

	/**
		jQuery replaceWith
		*/
	function replaceWith($selector = '')
		{
		$this->props['method'] = 'replaceWith';
		$this->props['selector'] = $selector;
		return $this;
		}
		
	/**
		jQuery html
		*/
	function html($selector = '')
		{
		$this->props['method'] = 'replace';
		$this->props['selector'] = $selector;
		return $this;
		}

	/**
		jQuery append
		*/
	function append($selector = '')
		{
		$this->props['method'] = 'append';
		$this->props['selector'] = $selector;
		return $this;
		}

	/**
		jQuery prepend
		*/
	function prepend($selector = '')
		{
		$this->props['method'] = 'prepend';
		$this->props['selector'] = $selector;
		return $this;
		}

	/**
		jQuery insertAfter
		*/
	function insertAfter($selector = '')
		{
		$this->props['method'] = 'insertAfter';
		$this->props['selector'] = $selector;
		return $this;
		}

		/** @} */
	}
