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
	
	/**
		New mallorca style rendering.
		*/
	static public function unfold($q)
		{
		$content = self::call_path($q);
		
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
		else if (post('q')) {
			$q = post('q');
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
			$stack = isset($_POST['stack']) ? $_POST['stack'] : ['data-fn'=>stack(website())];

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
		
		$json = array(
			'request'=>pv($stack)
			);
		 
		// die(pv($stack));

		foreach ($stack as $k=>$v) {
			$s = '';
			// $params = array_merge(is($v, 'params', array()), $data);
			$params = is($v, 'params', []);

			if (self::$stop) continue;
			if (! is_array($v)) {
				$s = $v;
				}
			else if (array_key_exists('class', $v)) {
				$s = self::call_class($v['class'], $v['functions'], $params, is($v, 'constructor'));
				}
			else if (array_key_exists('q', $v)) {
				$s = self::call_path($v['q'], $params, is($v, 'function', 'my_display'));
				// die(pv($params));
				$json['set_url'] = $v['q'] . '&' . http_build_query($params);
				}

			if (! isset($json[$k])) $json[$k] = array();

			$key = is($v, 'selector', $k);

			$json[$key] = array(
				'content'=>$s,
				'method'=>is($v, 'method', 'replace')
				);
			}

		echo json_encode($json);
		die;
		}

	/**
		Call a on() schema object as a path.
		*/
	static private function call_path($path, $params = [], $fn = 'my_display')
		{
		$parts = explode('/', $path);
		$method = array_pop($parts);

		$class = _to_class(implode('/', $parts) . '/model');
		$model = new $class();
		$model->params($params);

		// if (! method_exists($schema, $method)) die($method . " does not exist for $schema_class.");

		$body = $model->$method()->$fn($params);
		if ($fn == 'my_display') {
			$body = \Path\Wrapper::my_wrapper($body);
			}

		return $body;
		}

	/**
		Call simple instance function.
		*/
	static private function call_class($class, $fns, $params = array())
		{
		$new = new $class();

		foreach ($fns as $index=>$step) {
			$new = call_user_func_array([$new, $step], is($params, $index, []));
			}

		return $new;
		}
	}
