<?php

/**
	This class allows mallorca.js.php to call php functions directly from the browser.
	It interprets the request, runs the functions, and attaches their results to CSS selectors in order,
	returned as a JSON array as such.
	*/
class Request
	{
	/** Stop execution. */
	static public $stop = false;

	/** Form-submitted request data. */
	static public $data;

	/** Returned data array. */
	static public $return;

	/** Current index of the return array. */
	static public $count;
	
	/**
		New mallorca style rendering.
		*/
	static public function unfold($q)
		{
		$get = $_POST;
		unset($get['q']);

		$content = static::call_path($q, $get);
		
		echo json_encode(array(
			'POST'=>$_POST,
			'.content'=>array(
				'content'=>$content,
				'method'=>'replace'
				)
			));
		die;
		}

	/**
		Stop the request chain.
		*/
	static public function kill()
		{
		self::$stop = true;
		}

	/**
		The main app response function.
		Only reacts to POST requests.
		Can handle paths via the new mallorca style.
		After that handles specific function calls with their contents keyed and returned.
		*/
	static public function respond()
		{
		// only comes through from POST
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			return;
			}
		else if (post('path')) {
			$q = post('path');
			self::unfold($q);
			/* makes modules work
			$content = \Path::interpret(post('q'));
			echo json_encode(array(
				'POST'=>$_POST,
				'.content'=>array(
					'content'=>$content->my_display(),
					'method'=>'replace'
					)
				));
			die;
			*/
			}
		else {
			$stack = isset($_POST['stack']) ? $_POST['stack'] : array('data-fn'=>stack(website()));

			self::respond_to($stack);
			}
		}

	/**
		\param	array	$stack	Array of CSS selectors => PHP fn calls, example:
			'.main'=>fn('Login::display')
		\return	JSON encoded array of CSS selectors => content.
		*/
	static public function respond_to($stack = array())
		{
		$data = array();

		// more useful version to unparse here, so that complicated nested input names, need not be used
		if (is($stack, 'data-fn')) {
			$m = array();
			parse_str(is($stack, 'data-fn'), $m);
			parse_str(is($stack, 'data'), $data);
			$stack = $m;
			}

		self::$data = $data;
		
		self::$return = array(
			'request'=>print_r($stack, true)
			);
		 
		 // die(pv($stack));

		foreach ($stack as $v) {
			self::respond_to_one($v);
			}

		echo json_encode(self::$return);
		die;
		}

	/**
		Loop over this function.
		*/
	static public function respond_to_one($v)
		{
		$s = '';
		// $params = array_merge(is($v, 'params', array()), $data);
		$params = is($v, 'params', array());
		// $key = is($v, 'selector', $k);
		$out = array();

		if (self::$stop) return;
		if (! is_array($v)) {
			$s = $v;
			}
		else if (array_key_exists('class', $v)) {
			$out = static::call_class($v['class'], $v['functions'], $params, is($v, 'constructor'));
			$s = $out['content'];
			}
		else if (array_key_exists('path', $v)) {
			$s = static::call_path($v['path'], $params, is($v, 'function', 'my_display'));
			self::$return['set_url'] = $v['path'] . (! empty($params) ? '&' . http_build_query($params) : '');
			}

		// if (! isset(self::$return[self::$count])) self::$return[self::$count] = array();

		$key = is($v, 'selector', is($out, 'selector'));

		// $json[$key] = array(
		// self::$return[self::$count++] = array(
		self::$return[] = array(
			'selector'=>$key,
			'content'=>$s,
			'method'=>is($v, 'method', 'replace')
			);
		}

	/**
		Call in the middle of already back on the server side.
		*/
	static public function send($call)
		{
		self::respond_to_one($call->props);
		}

	/**
		Call a on() model object as a path.
		*/
	static public function call_path($path, $params = array(), $fn = 'my_display')
		{
		$parts = explode('/', $path);
		$method = array_pop($parts);

		$class = _to_class(implode('/', $parts) . '/model');
		$model = new $class();
		// $model->params($params);

		// if (! method_exists($schema, $method)) die($method . " does not exist for $schema_class.");
		$call = call_user_func_array(array($model, $method), $params);
		$body = is_object($call) ? $call->$fn() : $call;

		// $body = $model->$method($params)->$fn();
		if (is_object($call) && $fn == 'my_display') {
			$body = \Path\Wrapper::my_wrapper($body);
			}

		return $body;
		}

	/**
		Call simple instance function.
		*/
	static public function call_class($class, $fns, $params = array())
		{
		$parent = get_parent_class($class);

		// modules must be constructed this way
		if (in_array($parent, array('Module'))) {
			$new = \Path::index(req('q'));
			while (isset($new->child)) $new = $new->child;
			}
		else {
			$new = new $class();
			}

 		// echo pv(class_uses($new)); die(pv(class_implements($new)));
 		$out = array();

		foreach ($fns as $index=>$step) {
			/*
			// parameter matching
			echo pv($new->my_allow());
			$allow = [];
			foreach ($new->my_allow() as $k=>$v) {
				if (preg_match("/^\d+$/", $k)) $allow[$v] = [];
				else $allow[$k] = $v;
				}
			 // echo $step; die(pv($allow));

			if (! isset($allow[$step])) {
				die("You must add $step to my_allow()");
				}

			$args = is($params, $index, []);
			$call_args = [];
			foreach ($allow[$step] as $k=>$v) {
				$call_args[$k] = is($args, $k, $v);
				}
				*/
			$call_args = is($params, $index, array());

			// $ref = new ReflectionMethod($new, $step); echo pv($ref->getParameters());

			// default wrappers
			if (isset($new->wrapper) && isset($new->wrapper[$step])) {
				$out['selector'] = $new->wrapper[$step];
				}

			$new = call_user_func_array(array($new, $step), $call_args);
			}

		$out['content'] = $new;

		// echo pv($out);

		return $out;
		}
	}
