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

	/** Stack. */
	// static public $stack = array();

	/** The initial url parameters. */
	static public $json_get = array();

	static public $wrap = true;

	/** The initial url path. */
	// static public $path;

	/** Synonym for $path */
	// static public $q;

	/** Returned data array. */
	static public $return;

	/** Current index of the return array. */
	// static public $count;
	
	/**
		New mallorca style rendering.
		*/
	static public function unfold($q)
		{
		$out = static::call_path($q, self::$json_get);

		$content = is($out, 'content');
		// die(pv(self::$is_init));
		// die(pv($content));

		// self::$stop for redirects
		$return = self::$stop ? self::$return : array(
			// 'request'=>$_POST,
			'.content'=>array('selector'=>'.content', 'content'=>$content, 'method'=>'replace')
			);

		echo json_encode($return);
		die;
		}

	/**
		Stop the request chain.
		*/
	static public function kill()
		{
		self::$stop = true;
		}

	static public function redir($path, $params = array())
		{
		self::send(call_path($path, $params));
		self::kill();
		}

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
		// self::$q = is(self::$json_get, 'q');
		// unset(self::$json_get['q']);

		self::$is_post = $_SERVER['REQUEST_METHOD'] == 'POST';
		self::$is_init = post('init'); // ! isset($_POST['stack']);

		// $data = array();
		// $stack = isset($_POST['stack']) ? $_POST['stack'] : array('data-fn'=>stack(website()));
		/*
		$stack = self::$is_init ? array('data-fn'=>stack(website())) : post('stack');
			
		if (is($stack, 'data-fn')) {
			// $data_fn = array();
			// $stack = post('stack');
			parse_str(is($stack, 'data-fn'), self::$stack);
			parse_str(is($stack, 'data'), self::$data);
			// $stack = $data_fn;
			}
		// self::$stack = $data_fn;
		// self::$data = $data;

		/*
		// TODO make this more robust
		// first call_path is the "path"
		foreach (self::$stack as $s) {
			if (is($s, 'path')) {
				self::$path = is($s, 'path');
				break;
				}
			}
		
		self::$return = array(
			'request'=>print_r($stack, true)
			);
			*/
		}

	/**
		The main app response function.
		Only reacts to POST requests.
		Can handle paths via the new mallorca style.
		After that handles specific function calls with their contents keyed and returned.
		*/
	static public function respond()
		{
		// login check
		/*
		$logged_in = \Login::check();

		if (self::$is_post && ! $logged_in) {
			// die(self::$path . 'hey');
			// self::send(call_path('user/login')); self::kill();
			/*
			$login = new \Perfect\Login();
			$try = $login->validate();
			if ($try === true) self::respond_to_stack();
			else {
				echo json_encode(array(
					'.content'=>array('selector'=>'.content', 'content'=>$try, 'method'=>'replace')
					));
				die;
				}
			}
				*/

		// "requests" only come through POST
		if (! self::$is_post) return;
		else if (post('init') && is(self::$json_get, 'q')) {
			self::unfold(is(self::$json_get, 'q'));
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
			// $stack = $m;
			}
		else {
			die('No stack.');
			}

		self::$data = $data;
		self::$return = array(
			// don't show this because passwords could be there
			// 'request'=>print_r($stack, true)
			);
		 
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
			// die(pv(self::$return));
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
		"Redirect" in the middle of a request.
	static public function base_redir($path, $params = array())
		{
		if (! self::$is_post) {
			if ($path == req('q')) return;
			\Path::base_redir($path, $params);
			}
		else {
			/*
			if (self::$is_init && self::$q == $path) {
				return;
				}
			else
			if (self::$path == $path) {
				return;
				}
			echo json_encode(array(
				'redirect'=>\Path::base_to($path, $params)
				));
			die;
			}
		}
			*/

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
		if (in_array($parent, array('Module'))) {
			// $new = \Path::index($path);
			// while (isset($new->child)) $new = $new->child;
			// return $new->my_display();
			// $method = '
		// 	die($class);
			return self::call_class($class); // , array('my_display'), array($params)
			}
		// model style
		else {
			// $new = new $class();
			$class = _to_class(implode('/', $parts) . '/model');
			if (! class_exists($class)) {
				die('Classes not found: ' . _to_class($path) . ' or ' . _to_class($path) . "\\Model");
				}
			$fns = array($fn);
			if ($method) array_unshift($fns, $method);
			// die($class . $method);
			// $wrap = 
			// die(pv($params));

			return self::call_class($class, $fns, $params, true);
			}
			// $model = new $class();
			// $model->params($params);
		// self::call_class($class, array('my_display'), array($params));
		/*
		// module style
		if (in_array($parent, array('Module'))) {
			$new = \Path::index($path);
			while (isset($new->child)) $new = $new->child;
			return $new->my_display();
			}
		// model style
		else {
			// $new = new $class();
			$class = _to_class(implode('/', $parts) . '/model');
			if (! class_exists($class)) {
				die('Classes not found: ' . _to_class($path) . ' or ' . _to_class($path) . "\\Model");
				}
			$model = new $class();
			$model->params($params);

			// if (! method_exists($schema, $method)) die($method . " does not exist for $schema_class.");
			$call = call_user_func_array(array($model, $method), $params);
			$body = is_object($call) ? $call->$fn() : $call;

			// $body = $model->$method($params)->$fn();
			if (is_object($call) && $fn == 'my_display') {
				$body = \Path\Wrapper::my_wrapper($body);
				}

			return $body;
			}
			*/
		}

	/**
		Call simple instance function.
		*/
	static public function call_class($class, $fns = array(), $params = array(), $wrap = false)
		{
		$parent = get_parent_class($class);
		$uses = class_uses($class);
		$out = array();

		// all function calls happen here, so auth
		$logged_in = \Login::check();

		if (! $logged_in) {

			// try to login with cookies
			$ok = \Perfect\Login::try_confirmation_link();

			if ($ok) {
				// continue
				$web = website();
				self::send(array_pop($web));
				self::kill();
				}
			if (in_array('NoAuth', $uses)) {
				// OK (login class being called)
				}
			else {
				self::send(call_path('user/login'));
				self::kill();
				}
			}

		$parent = get_parent_class($class);
		$out = array();

		// modules must be constructed this way
		if (in_array($parent, array('Module'))) {
			// $q = is(self::$json_get, 'q');
			$q = _to_path($class);
			$new = \Path::index($q);
			// die(pv(self::$json_get) . pv($new));
			while (isset($new->child)) $new = $new->child;
			$out['content'] = $new->my_display();
			}
		else {
			   //  echo pv($params); die(pv(self::$json_get));
			$new = new $class();
			$new->params(array_merge(self::$json_get, $params));
			// }

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

				// $new must be chaining
				if (is_object($new))
					$new = call_user_func_array(array($new, $step), $call_args);
				}

			if (! in_array('NoWrap', $uses)
			&& self::$wrap
			&& ($wrap || self::$is_init)) {
				// if (is_object($call) && $fn == 'my_display') {
				$new = \Path\Wrapper::my_wrapper($new);
				}

			$out['content'] = $new;

			}

		// echo pv($out);

		return $out;
		}
	}
