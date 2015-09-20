<?php
/* TIMEZONE, INI */

date_default_timezone_set('America/Los_Angeles');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('mysql.datetimeconvert', 'Off');

/* SUPERGLOBALS */

/**
	\defgroup superglobals Superglobal shorthand functions get(), post()...

	These are simple shorthand functions for accessing the superglobals and avoiding undefined indexes.
	Try to always use them to the right hand side of an equal sign.
	\code
	$var = $_GET['var']; //avoid
	$var = get('var'); //good
	\endcode

	\return The superglobal array value if it is set, the provided alternative value if it is given, otherwise ''.
	\param string $a Name of the index.
	\param string $b Value to return if the index is not set.

	@{*/

/**
	$_GET
	*/
function get($a, $b = '')
	{
	return isset($_GET[$a]) ? $_GET[$a] : $b;
	}

/**
	$_REQUEST
	*/
function req($a, $b = '')
	{
	return isset($_REQUEST[$a]) ? $_REQUEST[$a] : $b;
	}

/**
	$_POST
	*/
function post($a, $b = '')
	{
	return isset($_POST[$a]) ? $_POST[$a] : $b;
	}

/**
	$_POST
	*/
function sesh($a, $b = '')
	{
	return isset($_SESSION[$a]) ? $_SESSION[$a] : $b;
	}

/**
	$_POST
	*/
function cook($a, $b = '')
	{
	return isset($_COOKIE[$a]) ? $_COOKIE[$a] : $b;
	}

/**
	$_POST
	*/
function is($array, $index, $else = '')
	{
	return isset($array[$index]) ? $array[$index] : $else;
	}

	/**@}*/

/**
	Synonymous with ternary operator.

	\param	bool	$bool	Condition.
	\param	mixed	$then	To return if true.
	\param	mixed	$else	To return if false.
	*/
function iff($bool, $then, $else = '')
	{
	return $bool ? $then : $else;
	}

/* INPUTS */

/**
	\defgroup inputs Factory functions for input objects.
	@{*/

/* */
/**
	group
	*/
function input_group()
	{
	$args = func_get_args();
	$name = array_shift($args);

	// allow passing of inputs as array or argument list
	if (is_array(is($args, 0))) $args = $args[0];	

	return new \Input\Group($name, $args);
	}

/**
	new style
	*/
function action_group($inputs = array())
	{
	$name = 'input-group';
	return new \Action\Group($name, $inputs);
	}

/**
	toggle
	*/
function input_toggle($name, $options = array())
	{
	$inp = new \Input\Toggle($name);
	$inp->options = $options;
	return $inp;
	}

/**
	hidden
	*/
function input_hidden($name, $value = '')
	{
	$inp =  new \Input\Hidden($name);
	$inp->value = $value;
	return $inp;
	}

/**
	money
	*/
function input_money($name, $len = 30, $value = '')
	{
	return new \Input\Money($name, $len, $value);
	}

/**
	check
	*/
function input_check($name)
	{
	return new \Input\Check($name);
	}

/**
	swatch
	*/
function input_swatch($name)
	{
	return new \Input\Swatch($name);
	}

/**
	checklist
	*/
function input_checklist()
	{
	$args = func_get_args();
	$name = array_shift($args);
	return new \Input\Checklist($name, $args);
	}

/**
	file
	*/
function input_file($name, $table = '', $table_id = 0)
	{
	return new \Input\File($name, $table, $table_id);
	}

/**
	radio
	*/
function input_radio($name, $options = array())
	{
	return new \Input\Radio($name, $options);
	}

/**
	text
	*/
function input_text($name, $len = '', $value = '')
	{
	return new \Input\Text($name, $len, $value);
	}

/**
	email
	*/
function input_email($name)
	{
	return new \Input\Email($name);
	}

/**
	duration
	*/
function input_duration($name)
	{
	return new \Input\Duration($name);
	}

/**
	phone
	*/
function input_phone($name, $value = '')
	{
	return new \Input\Phone($name, 12, $value);
	}

/**
	password
	*/
function input_password($name, $len = 10)
	{
	return new \Input\Password($name, $len);
	}

/**
	stripe
	*/
function input_stripe($name, $amount, $id)
	{
	return new \Input\Stripe($name, $amount, $id);
	}

/**
	button
	*/
function input_button($name)
	{
	return new \Input\Button($name);
	}

/**
	submit
	*/
function input_submit($name)
	{
	$inp = new \Input\Button($name);
	return $inp->type('submit');
	}

/**
	textarea
	*/
function input_textarea($name, $len = 250)
	{
	return new \Input\Textarea($name, $len);
	}

/**
	select
	*/
function input_select($name, $options = array())
	{
	return new \Input\Select($name, $options);
	}

/**
	timer
	*/
function input_timer($name, $key, $id)
	{
	return new \Input\Timer($name, $key, $id);
	}

/**
	date
	*/
function input_date($name, $value = '')
	{
	$inp = new \Input\Date($name, 10, $value);
	return $inp->add_class('date-calendar');
	}

/**
	state
	*/
function input_state($name)
	{
	return new \Input\State($name);
	}

/**
	time
	*/
function input_time($name)
	{
	return new \Input\Time($name);
	}

/**
	thumb
	*/
function input_thumb($name, $booking)
	{
	return new \Input\Thumb($name, $booking);
	}

/**
	date_triple
	*/
function input_date_triple($name)
	{
	return new \Input\DateTriple($name);
	}
	/** @} */

/* CHAINING FUNCTIONS */

function show($name, $rows)
	{
	return new \Module\Show($name, $rows);
	}

function table($rows)
	{
	return new \Table($rows);
	}
	
function db()
	{
	return \Db::$db;
	}

/* ESCAPING */

function safe_id($name, $default = 0)
	{
	$id = req($name, $default);
	if (preg_match("/^[0-9]+$/", $id) && $id > 0) return $id;
	return $default;
	}

function id_zero($x)
	{
	// return is_string($x) && preg_match("/^\d+$/", $x) ? $x : 0;
	return preg_match("/^\d+$/", $x) ? $x : 0;
	}

function _to_camel($s)
	{
	return implode('', array_map('ucfirst', explode('_', $s)));
	}

function _to_words($s)
	{
	return ucwords(str_replace("_", " ", $s));
	}

function _to_path($s)
	{
	// $s = preg_replace("/^\\/", "", $s);
	if (strpos($s, "\\") === 0) $s = substr($s, 1);
	$s = str_replace("\\", "/", $s);
	// $s = str_replace("_", "/", $s);
	return strtolower($s);
	}

function _to_class($s)
	{
	$ex = explode('/', $s);
	$class = "\\" . implode("\\", array_map('_to_camel', $ex));
	return $class;
	}

/* IMAGES / TAGS */

/**
	\defgroup tag_helpers	HTML / header tag helpers.

	@{*/

function title_tag($title = 'My Page')
	{
	return '<title>' . $title . '</title>' . "\n";
	}

function meta_tags()
	{
	return ''
	. '<meta charset="UTF-8" />' . "\n"
	. '<meta name="viewport" content="width=device-width, user-scalable=0" />' . "\n";
	}

function doctype()
	{
	return	'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
	}

function jquery_tag()
	{
	return '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>' . "\n";
	}

function style_tag($path)
	{
	return "<link rel='stylesheet' type='text/css' href='" . \Config::$local_path . $path . "' media='screen'></link>\n";
	}

function script_tag($path)
	{
	return 	"<script type='text/javascript' src='" . \Config::$local_path . $path . "'></script>\n";
	}

function upload_tag($url)
	{
	return "<img src='" . \Config::$local_path . "image.php?h=$url'>";
	}

function image_tag($url, $folder = 'public/images')
	{
	return "<img src='" . image_url($url, $folder) . "' />";
	}

function image_url($url, $folder = 'public/images')
	{
	return \Config::$local_path . "$folder/$url";
	}

	/** @} */


/* MARKUP */

function rug()
	{
	return "<div class='rug'></div>";
	}

function print_var($var, $str = false)
	{
	$s = "<pre>" . print_r($var, true) . "</pre>";
	if ($str) return $s;
	echo $s;
	}

function newlines($s)
	{
	return "<p>" . str_replace("\n", "<br>", str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $s)) . "</p>";
	}

/**
	\return Shorthand print_r with clean spacing as a string.
	*/
function pv($var)
	{
	return print_var($var, true);
	}

/**
	Implode strings around a global divider.
	*/
function divider($xs)
	{
	return implode(' &bull; ', $xs);
	}

// function div($class, ...$args) {
function div()
	{
	$args = func_get_args();
	$class = array_shift($args);

	return "<div class='$class'>" . implode($args) . "</div>";
	}

function span()
	{
	$args = func_get_args();
	$class = array_shift($args);

	return "<span class='$class'>" . implode($args) . "</span>";
	}

/* DATE FUNCTIONS */

/**
	Convert almost any date to specified format.
	Thanks to the PHP date() function.
	Unlike PHP's date alone, if the conversion was unsuccessful the original string will be returned.
	This function also handles potential Date objects (no longer an issue, yet can come up in working with SQL).
	\param string $format See PHP's date() for details.
	\param mixed $date The input date.
	*/
function date_to($format, $date)
	{
	if (is_object($date)) return $date->format($format);

	$time = strtotime($date);
	return $date != '' && $time !== null ? date($format, $time) : '';
	}

/**
	Standard m/d/y date format.
	*/
function date_slashes($date)
	{
	return date_to('n/j/Y', $date);
	}

/**
	Standard who and when data to merge into queries.
	*/
function who_when()
	{
	return array(
		'created_by'=>\Login::$id,
		'created_on'=>date('Y-m-d H:i:s'),
		);
	}
	
/**
	MySQL now() equivalent.
	*/
function now()
	{
	return date('Y-m-d H:i:s');
	}

/**
	Convert a date to a "how long ago" statement.
	\return A string of how long ago $date was, in minutes, hours, or days respectively.
	\param string $date The date to convert.
	*/
function how_long_ago($date, $base = 60, $before = '', $after = '')
	{
	$qty = round((strtotime(date('Y-m-d H:i:s')) - strtotime(date_to('Y-m-d H:i:s', $date))) / 60); $ago = "m"; //minutes
	$neg = $qty < 0;
	$qty = abs($qty);
	if ($qty >= 60) 
		{
		$qty = round($qty / 60); $ago = " Hour";  //hours
		if ($qty >= 24)
			{
			$qty = round($qty / 24); $ago = " Day"; 
			if ($qty >= 7)
	{ $qty = round($qty / 7); $ago = " Week"; }
			}
		else $qty = 0; // today
		}

	if ($qty > 1) $ago .= 's';

	return $qty == 0 ? "Today"
	: ($neg ? "<span class='future'>" . str_repeat("&bullet;", $qty - 1) . " In $qty $ago</span>"
	: "<span class='past'>$qty $ago Ago</span>");
	}

/**
	Simplified day_diff formula.
	\param	date $start	Start date.
	\param	date $end	End date.
	*/
function day_diff($start, $end)
	{
	return date_diff(date_create($start), date_create($end))->format('%r%a');
	}

/**
	\return array of a date range.
	*/
function day_range($start, $end, $blank = array())
	{
	$stime = strtotime($start);
	$range = range(0, day_diff($start, $end));
	$dates = array();
	foreach ($range as $i)
	{
		$dates[date('Y-m-d', strtotime(($i > 0 ? '+' : '') . $i . " days", $stime))] = $blank;
		}
	return $dates;
	}

/**
	Map all days over an array of select days.
	\param	date	$start	Start date.
	\param	date	$end	End date.
	\param	array	$data	Data. Array of dates(keys)=>mixed(row data)
	\return	Modified $data array.
	*/
function days_over($start, $end, $data)
	{
	$dates = day_range($start, $end, 0);

	$data = array_merge($dates, $data);
	ksort($data);

	return $data;
	}

/* FILES */

/**
	Get the full file directory to a file from the relative path.
	\param	$s	Bare file name.
	*/
function file_dir($s)
	{
	$script = $_SERVER['SCRIPT_FILENAME'];
	$script = str_replace("/index.php", '', str_replace('\\', '/', $script));
	
	return $script . '/' . $s;
	}
	
/* SESSION */

/**
	Get the _SESSION alert message and clear.
	\return sesh-alert div
	*/
function sesh_alert()
	{
	$alerts = sesh('alert');
	if (! is_array($alerts)) $_SESSION['alert'] = array();

	// limit to 4
	while (count($_SESSION['alert']) > 1) {
		array_shift($_SESSION['alert']);
		}

	$alerts = sesh('alert');
	$divs = array();
	foreach ($alerts as $k=>$v) {
		$divs[] = div('row', $v . " <span class='dismiss data-fn' dismiss' after-fn=\"remove_row\" data-fn=\"" . stack(array(
			call('Login', 'dismiss_alert', array('id'=>$k))
			)) . "\">dismiss</span>");
		}

	return div('sesh-alert', implode('', $divs));
	}

/**
	Add an alert message to the session for displaying across pages.
	\param	string	$msg	Message to add.
	*/
function alert($msg = '')
	{
	if (! isset($_SESSION['alert'])) $_SESSION['alert'] = array();

	if ($msg && is_string($msg)) {
		array_push($_SESSION['alert'], $msg);
		}
	}

