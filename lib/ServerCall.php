<?php

/**
	Prepare instructions for the server side function call.
	*/
class ServerCall
	{
	public $props;

	function __construct($props)
		{
		$this->props = $props;
		}

	function pipe($fn, $params = [])
		{
		$this->props['functions'][] = $fn;
		$this->props['params'][] = $params;
		return $this;
		}

	function replaceWith($selector)
		{
		$this->props['method'] = 'replaceWith';
		$this->props['selector'] = $selector;
		return $this;
		}
		
	function html($selector)
		{
		$this->props['method'] = 'replace';
		$this->props['selector'] = $selector;
		return $this;
		}

	function prepend($selector)
		{
		$this->props['method'] = 'prepend';
		$this->props['selector'] = $selector;
		return $this;
		}
	}
