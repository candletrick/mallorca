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

	/** Is POST request? */
	static public $is_post = false;

	/** Is init? */
	static public $is_init = false;

	/** Form-submitted request data. */
	static public $data;

	/** The initial url parameters. */
	static public $json_get = array();

	/** Whether to wrap the request with HTML wrapper function. */
	static public $wrap = true;

	/** Returned data array. */
	static public $return;

	/**
		New mallorca style rendering.
		*/
	static public function render_path($q)
		{
		$out = static::call_path($q, self::$json_get);

		$content = is($out, 'content');

		// self::$stop for redirects
		$return = self::$stop ? self::$return : array(
			'request'=>$_POST,
			'.m-content'=>array('selector'=>'.m-content', 'content'=>$content, 'method'=>'replace')
			);

		echo json_encode($return);
		die;
		}

	/**
		Get a value that was initially in $_GET / url.
		\param	string	$name	key name
		\param	string	$else	fallback value
		*/
	static public function get($name, $else = '')
		{
		return is(self::$json_get, $name, req($name, $else));
		}

	/**
		Stop the request chain.
		*/
	static public function kill()
		{
		self::$stop = true;
		}

	/**
		Simulate a "redirect", in other words, stop the execution change and render path.
		\param	string	$path	Url path.
		\param	array	$params
		*/
	static public function redir($path, $params = array())
		{
		self::send(call_path($path, $params));
		self::kill();
		}

	/**
		Indicate not to wrap the display out.
		*/
	static public function no_wrap()
		{
		self::$wrap = false;
		}

	/**
		Set up any url parameters for use app-wide.
		*/
	static public function init()
		{
		self::$json_get = post('json_get', array());
		self::$is_post = $_SERVER['REQUEST_METHOD'] == 'POST';
		self::$is_init = post('init');
		}

	/**
		The main app response function.
		Only reacts to POST requests.
		Can handle paths via the new mallorca style.
		After that handles specific function calls with their contents keyed and returned.
		*/
	static public function respond()
		{
		// "requests" only come through POST
		if (! self::$is_post) return;
		else if (post('init') && is(self::$json_get, 'q')) {
			self::render_path(is(self::$json_get, 'q'));
			}
		else {
			$stack = isset($_POST['stack']) ? $_POST['stack'] : array('data-fn'=>stack(website()));

			self::respond_to_stack($stack);
			}
		}

	/**
		\param	array	$stack	Array of CSS selectors => PHP fn calls, example:
			'.main'=>fn('Login::display')
		\return	JSON encoded array of CSS selectors => content.
		*/
	static public function respond_to_stack($stack) // $stack = array())
		{
		$data = array();

		// more useful version to unparse here, so that complicated nested input names, need not be used
		if (is($stack, 'data-fn')) {
			$data_fn = array();
			parse_str(is($stack, 'data-fn'), $data_fn);
			parse_str(is($stack, 'data'), $data);
			}
		else {
			die('No stack.');
			}

		self::$data = $data;
		self::$return = array();
			// 'request'=>pv($data_fn)
			// );
		 
		foreach ($data_fn as $fn) {
			self::respond_to_one($fn);
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
		$params = is($v, 'params', array());
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
			self::$return['set_url'] = $v['path'] . (! empty($params) ? '&' . http_build_query($params) : '');
			$out = static::call_path($v['path'], $params, is($v, 'function', 'my_display'));
			$s = $out['content'];
			}

		// one more chance to kill
		if (self::$stop) {
			if (! empty(self::$return)) return;
			}

		$key = is($v, 'selector', is($out, 'selector'));

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
		A list of class types which render as modules.
		Only for use here.
		\param	string	$parent	Parent class name.
		*/
	static public function is_module($parent)
		{
		return in_array($parent, array(
			'Module',
			'Module\Dashboard',
			'Module\StepByStep',
			'Module\Lookup',
			'Module\Scroll',
			'Form\Create',
			));
		}

	/**
		Call a on() model object as a path.
		*/
	static public function call_path($path, $params = array(), $fn = 'my_display')
		{
		$parts = explode('/', $path);
		$method = array_pop($parts);

		$class = _to_class($path);
		$parent = get_parent_class($class);

		// module style
		if (self::is_module($parent)) {
			$fns = array($fn);
			return self::call_class($class, $fns, $params, true);
			}
		// model style
		else {
			$class = _to_class(implode('/', $parts) . '/model');
			if (! class_exists($class)) {
				die('Classes not found: ' . _to_class($path) . ' or ' . _to_class($path) . "\\Model");
				}
			$fns = array($fn);
			if ($method) array_unshift($fns, $method);

			return self::call_class($class, $fns, $params, true);
			}
		}

	/**
		Call simple instance function.
		*/
	static public function call_class($class, $fns = array(), $params = array(), $wrap = false)
		{
		$parent = get_parent_class($class);
		$uses = class_uses($class);
		$out = array();

		\Login::before_call($class);

		$parent = get_parent_class($class);
		$out = array();

		if (self::is_module($parent)) {
			$q = _to_path($class);
			$new = \Path::index($q);
			while (isset($new->child)) $new = $new->child;
			}
		else {
			$new = new $class(); 
			$ms = array_merge(self::$json_get, $params);
			if ($parent == 'Model') {
				$id = isset($new->keyname) ? is($ms, $new->keyname) : 0;
				$new->my_construct($id);
				}
			else $new->my_construct($ms);
			}

		$out = array();

		foreach ($fns as $index=>$step) {
			/** LEAVE HERE
			// parameter matching
			echo pv($new->my_allow());
			$allow = [];
			foreach ($new->my_allow() as $k=>$v) {
				if (preg_match("/^\d+$/", $k)) $allow[$v] = [];
				else $allow[$k] = $v;
				}

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

			// default wrappers
			if (isset($new->wrapper) && isset($new->wrapper[$step])) {
				$out['selector'] = $new->wrapper[$step];
				}

			// $new must be chaining
			if (is_object($new)) {
				$new = call_user_func_array(array($new, $step), $call_args);
				}
			}

		// add wrapper
		if (! in_array('NoWrap', $uses)
		&& self::$wrap
		&& ($wrap || self::$is_init)) {
			$new = \Path\Wrapper::my_wrapper($new);
			}

		// die($new);
		$out['content'] = $new;

		return $out;
		}
	}
