<?php

/**
	This class allows mallorca.js.php to call php functions directly from the browser.
	It interprets the request, runs the functions, and attaches their results to CSS selectors in order,
	returned as a JSON array as such.
	*/
class Request
	{
	/** Base path for the application. */
	static public $local_path = '';

	/** Stop execution. */
	static public $stop = false;
	
	/** Remove url. */
	static public $clear_url = false;

	/**
		\param	array	$stack	Array of CSS selectors => PHP fn calls, example:
			'.main'=>fn('Login::display')
		\return	JSON encoded array of CSS selectors => content.
		*/
	static public function respond($stack = array())
		{
		// only comes through from POST
		if (empty($_POST)) return;

		if (empty($stack)) {
			$stack = isset($_POST['stack']) ? post('stack')
			: (! post('stack_merge') ? website() : array());
			}

		$data = array();

		// more useful version to unparse here, so that complicated nested input names, need not be used
		if (is($stack, 'data-fn')) {
			$m = array();
			parse_str(is($stack, 'data-fn'), $m);
			parse_str(is($stack, 'data'), $data);
			$stack = $m['stack'];
			}
		
		$json = array(
			'POST'=>print_r($_POST, true),
			);

		foreach ($stack as $k=>$v) {
			$s = '';
			if (self::$stop) continue;
			if (! is_array($v)) {
				$s = $v;
				}
			else if (array_key_exists('class', $v)) {
				$params = array_merge(is($v, 'params', array()), $data);
				$s = self::call_class($v['class'], $v['function'], $params);
				}
			else if (isset($v['function'])) {
				$s = self::call_fn($v['function'], is($v, 'params', array()));
				}

			if (! isset($json[$k])) $json[$k] = array();

			$json[$k] = array(
				'content'=>$s,
				'method'=>is($v, 'method', 'replace')
				);
			}

		$sm = post('stack_merge');
		if ($sm && ! self::$stop) {
			$json = array_merge($json, self::call_fn($sm, post('stack_merge_param', array())));
			}
		
		$json['clear_url'] = self::$clear_url;

		// ob_end_clean();
		echo json_encode($json);
		die;
		}

	/**
		\param	string	$fn		The function name.
		\param	array	$args	Arguments.
		\return	Function called with arguments.
		*/
	static private function call_fn($fn, $args = array()) {
		list($class, $method) = explode('::', $fn . '::');
		if (! $method || ! method_exists($class, $method)) return;
	
		if (isset($class::$allow[$method])) {
			$params = $class::$allow[$method];
			}
		else if(is_array($class::$bound) && in_array($method, $class::$bound)) {
			$class::bound($method);
			$params = $class::$allow[$method];
			}
		else {
			die("You must add $method to the \$allow or \$bound arrays.");
			break;
			}

		// Merge in new params
		foreach ($args as $kk=>$vv) {
			if ($vv) $params[$kk] = $vv;
			}

		return call_user_func_array($fn, $params);
		}

	/**
		Call simple instance function.
		*/
	static private function call_class($class, $fn, $params = array())
		{
		$new = new $class();
		return $new->$fn($params);
		}
	}
