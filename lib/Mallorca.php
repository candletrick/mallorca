<?php

/**
	\param bool $init Whether to run the request on first page load.
	*/
function mallorca_init($init = true) {
	// initialize
	return "<script type='text/javascript'>
var local_path = '" . http() . \Config::$local_path . "';
var mallorca_init = " . ($init ? 'true' : 'false') . ";
var json_get = " . json_encode($_GET) . ";
</script>";
	}

/**
	Standard mallorca HTML wrapper with a stick footer built in.
	*/
function mallorca_wrapper() {
	return div('wrapper', div('content') . div('push')) . div('footer')
	. mallorca_init()
	. script_tag('js/mallorca.js')
	;
	}

/* Mallorca Framework */

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

function pv($var)
	{
	return print_var($var, true);
	}

// function div($class, ...$args) {
function div() {
	$args = func_get_args();
	$class = array_shift($args);

	return "<div class='$class'>" . implode($args) . "</div>";
	}

function select($table, $columns = array('*')) {
	return new \Db\Query($table, $columns);
	}

function call($class, $fn, $params = array('')) {
	// allow $this to be passed for $class
	if (is_object($class)) {
		$class = get_class($class);
	
		// $parent = get_parent_class($class);
		// if ($parent ==
		}

	// allow piping
	$fns = explode(' | ', $fn);

	return new \ServerCall(array(
		'class'=>$class,
		'functions'=>$fns,
		'params'=>array($params)
		));
	}

function call_path($path = '', $params = array()) {
	return new \ServerCall(array(
		'path'=>$path,
		'selector'=>'.content',
		'params'=>$params
		));
	}

function schema($table, $columns) {
	// return \Schema::table($table, $cols);
	return new \Meta(array('table'=>$table, 'columns'=>$columns));
	}

function on($name) {
	return new On($name);
	}

function m($name = 1) {
	return new Meta(get_defined_vars());
	}

function ls() {
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
	static public $admin_email = 'yatsuha@fastmail.se';
	}
