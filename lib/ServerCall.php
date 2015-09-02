<?php

/**
	Prepare instructions for the server side function call.

	\sa call()
	\sa call_path()
	*/
class ServerCall
	{
	public $props;

	function __construct($props)
		{
		$this->props = $props;
		}

	/**
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
		*/
	function replaceWith($selector = '')
		{
		$this->props['method'] = 'replaceWith';
		$this->props['selector'] = $selector;
		return $this;
		}
		
	/**
		*/
	function html($selector = '')
		{
		$this->props['method'] = 'replace';
		$this->props['selector'] = $selector;
		return $this;
		}

	/**
		*/
	function append($selector = '')
		{
		$this->props['method'] = 'append';
		$this->props['selector'] = $selector;
		return $this;
		}

	/**
		*/
	function prepend($selector = '')
		{
		$this->props['method'] = 'prepend';
		$this->props['selector'] = $selector;
		return $this;
		}

	/**
		*/
	function insertAfter($selector = '')
		{
		$this->props['method'] = 'insertAfter';
		$this->props['selector'] = $selector;
		return $this;
		}

		/** @} */
	}
