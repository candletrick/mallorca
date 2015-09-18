<?php
/* Mallorca Framework */

/**
	\param bool $init Whether to run the request on first page load.
	*/
function mallorca_init($init = true)
	{
	// initialize
	$get = $_GET;
	// $get = array('json_get'=>$_GET);

	return "<script type='text/javascript'>
var local_path = '" . http() . \Config::$local_path . "';
var mallorca_init = " . ($init ? 'true' : 'false') . ";
var json_get = '" . http_build_query($get) . "';
</script>"
	. style_tag('ext/mallorca/css/featherlight.css')
	// . style_tag('ext/mallorca/css/mallorca.css')

	. script_tag('ext/mallorca/js/effects.js')
	. script_tag('ext/mallorca/js/featherlight.js')
	. script_tag('ext/mallorca/js/mallorca.js')
	;
	}

/**
	Standard mallorca HTML wrapper with a stick footer built in.
	*/
function mallorca_wrapper($content = '')
	{
	return div('m-wrapper', div('m-content fade', $content) . div('m-push')) . div('m-footer')
	// . mallorca_init()
	;
	}


/**
	\return Full http atom for URLs including host, port, base folder.
	*/
function http()
	{
	return "http://" . $_SERVER['HTTP_HOST']
	. (isset($_SERVER['HTTP_PORT']) ? ':' . $_SERVER['HTTP_PORT'] : '')
	// . $_SERVER['PHP_SELF']
	;
	}

function stack($xs = array()) {
	$ys = array();
	foreach ($xs as $x) {
		if (is_object($x) && get_class($x) == 'ServerCall') {
			$ys[] = $x->props;
			}
		else $ys[] = $x;
		}
	return http_build_query($ys);
	}


function select($table, $columns = array('*'))
	{
	return new \Db\Query($table, $columns);
	}

function call($class, $fn, $params = array(''), $static = false)
	{
	// allow $this to be passed for $class
	if (is_object($class)) {
		$class = get_class($class);
		}

	// allow piping
	$fns = explode(' | ', $fn);

	return new \ServerCall(array(
		'static'=>$static,
		'class'=>$class,
		'functions'=>$fns,
		'params'=>array($params)
		));
	}

function callStatic($class, $fn, $params = array(''))
	{
	return call($class, $fn, $params, true);
	}

function call_path($path = '', $params = array()) {
	return new \ServerCall(array(
		'path'=>$path,
		'selector'=>'.m-content',
		'params'=>$params
		));
	}

/*
function schema($table, $columns)
	{
	// return \Schema::table($table, $cols);
	return new \Meta(array('table'=>$table, 'columns'=>$columns));
	}
	*/

function on($name)
	{
	return new On($name);
	}

function m($name = 1)
	{
	return new Meta(get_defined_vars());
	}

function ls()
	{
	return new Ls(func_get_args());
	}

/* CONFIG */

/**
	Define these values in your protected/_local.php file.
	*/
class Config {
	/** Array of keys: type, host, user, password, database. */
	static public $db = array();

	/** Directories to autoload from. */
	static public $autoload_dirs = array();

	/** Number of directories to escape up from Autoload.php location. */
	static public $autoload_ups = "/../..";

	/** The local url root prefix. */
	static public $local_path = '/';

	/** Home route when none is specified. */
	static public $home_path = '';

	/** Admin email. */
	static public $admin_email = 'fewkeep@gmail.com';

	/** Email settings. */
	static public $email = array(
		'from_name'=>'hello',
		'from_email'=>'hello@localhost', // . $_SERVER['HTTP_HOST'],
		);

	/** Stripe connection info. */
	static public $stripe = array(
		'secret_key'=>"sk_test_BQokikJOvBiI2HlWgH4olfQ2",
		'publishable_key'=>"pk_test_6pRNASCoBOKtIshFeQd4XMUh",
		);
	}
