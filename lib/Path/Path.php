<?php
/**
	A class for handling the route, as well as providing functions
	for links, refreshes..

	This is meant to interact with MyIndex.
	*/

class Path
	{
	/** The path, passed in as a GET parameter from .htaccess */
	static public $q;
	
	/** Local URL prefix. Define in ../protected/<app>_local.php' */
	static public $local_path;

	/** Default path to render if none provided. Define in ../protected/<app>_local.php' */
	static public $home_path;

	/** Is local. */
	static public $is_local = true;

	/** Admin email. */
	static public $admin_email = 'fewkeep@gmail.com';

	/**
		The main function of the application.  Grab the route and hand it off to the index system.
		*/
	static public function interpret($path = '')
		{
		if (! $path) {
			$path = self::$q = get('q');
			if (! self::$q) $path = self::$q = self::$home_path;
			}

		$paths = explode('/', $path);
		$name = array_shift($paths);
		
		$index = $wrapper = new \Path\Wrapper($name, $paths);

		// walk down the children,
		while (isset($index->child)) $index = $index->child;

		// and check for index redefinitions
		while ($my = $index->my_index()) {
			// don't remake original
			if ($my == 'path/wrapper') {
				$wrapper->child = $index;
				break;
				}
			else {
				$class = '\\' . implode('\\', array_map('_to_camel', explode('/', $my)));
				$index = new $class($name, $paths, null, $index);
				}
			}

		return $wrapper; // ->my_display();
		}
	/**
		The main function of the application.  Grab the route and hand it off to the index system.
	static public function interpret($wraps = array('Path', 'Wrapper'))
		{
		self::$q = req('q', 'home/flip');
		// if (! self::$q) self::$q = 'login';

		$paths = explode('/', self::$q);
		$name = array_shift($paths);
		

		$wrap_class = "\\" . implode("\\", $wraps);
		$index = $wrapper = new $wrap_class($name, $paths);

		// walk down the children,
		while (isset($index->child)) $index = $index->child;

		// and check for index redefinitions
		while ($my = $index->my_index()) {
			// don't remake original
			if ($my == 'path/wrapper') {
				$wrapper->child = $index;
				break;
				}
			else {
				$class = '\\' . implode('\\', array_map('_to_camel', explode('/', $my)));
				$index = new $class($name, $paths, null, $index);
				}
			}

		echo $wrapper->my_display();
		}
		*/

	/**
		*/
	static public function index($path, $params = array()) {

		$paths = explode('/', $path);
		$name = array_shift($paths);

		// $wrap_class = "Path\\Wrapper"; // "\\" . implode("\\", $wraps);
		$wrap_class = "MyIndex";
		$index = $wrapper = new $wrap_class($name, $paths, null, null, $params);

		// walk down the children,
		while (isset($index->child)) $index = $index->child;

		// and check for index redefinitions
		while ($my = $index->my_index()) {
			// don't remake original
			if ($my == 'path/wrapper') {
				$wrapper->child = $index;
				break;
				}
			else {
				$class = '\\' . implode('\\', array_map('_to_camel', explode('/', $my)));
				$index = new $class($name, $paths, null, $index, $params);
				}
			}
		return $wrapper;
		}

	/**
		The $_GET array minus any class variables (q).
		*/
	static public function _GET()
		{
		$get = $_GET;
		unset($get['q']);
		return $get;
		}

	/**
		Redirect to path.
		\param	string $path	The path to redirect to.
		*/
	static public function base_redir($path, $params = array())
		{
		header("Location: " . self::base_to($path, $params));
		die;
		}

	/**
		\param string	$path	The target path.
		\return	Full URL to target path.
		*/
	static public function post_to($path, $params = array())
		{
		return "index.php?q=" . $path . (! empty($params) ? '&' . http_build_query($params) : '');
		}

	/**
		\param string	$path	The target path.
		\return	Full URL to target path.
		*/
	static public function base_to($path, $params = array())
		{
		return \Config::$local_path . $path . (! empty($params) ? '&' . http_build_query($params) : '');
		}

	/**
		\return Link.
		*/
	static public function link_to($text, $path, $params = array(), $class = '')
		{
		return "<a class='$class' href='" . self::base_to($path, $params) . "'>$text</a>";
		}

	/**
		\return Path of the current page.
		*/
	static public function here($params = array())
		{
		$get = $_GET;
		unset($get['q']);
		$params = array_merge($get, $params);
		return self::$local_path . self::$q . (! empty($params) ? '&' . http_build_query($params) : '');
		}

	/**
		Refresh the current page.
		*/
	static public function refresh($params = array())
		{
		header("Location: " . self::here($params));
		die;
		}
		
	/**
		\return Full http atom for URLs including host, port, base folder.
		*/
	static public function http()
		{
		return "http://" . $_SERVER['HTTP_HOST']
		. (isset($_SERVER['HTTP_PORT']) ? ':' . $_SERVER['HTTP_PORT'] : '')
		// . $_SERVER['PHP_SELF']
		;
		}
	}

