<?php
/* TIMEZONE, INI */

date_default_timezone_set('America/Los Angeles');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('mysql.datetimeconvert', 'Off');

/* SUPERGLOBALS */

function get($a, $b = '') {
	return isset($_GET[$a]) ? $_GET[$a] : $b;
	}

function req($a, $b = '') {
	return isset($_REQUEST[$a]) ? $_REQUEST[$a] : $b;
	}

function post($a, $b = '') {
	return isset($_POST[$a]) ? $_POST[$a] : $b;
	}

function sesh($a, $b = '') {
	return isset($_SESSION[$a]) ? $_SESSION[$a] : $b;
	}

function cook($a, $b = '') {
	return isset($_COOKIE[$a]) ? $_COOKIE[$a] : $b;
	}

function is($array, $index, $else = '') {
	return isset($array[$index]) ? $array[$index] : $else;
	}

function iff($bool, $then, $else = '') {
	return $bool ? $then : $else;
	}

/* INPUTS */

function input_group($inputs = array()) {
	$name = 'input-group';
	return new \Input\Group($name, $inputs);
	}

function input_toggle($name, $options = array()) {
	$inp = new \Input\Toggle($name);
	$inp->options = $options;
	return $inp;
	}

function input_hidden($name, $value = '') {
	$inp =  new \Input\Hidden($name);
	$inp->value = $value;
	return $inp;
	}

function input_check($name) {
	return new \Input\Check($name);
	}

function input_swatch($name) {
	return new \Input\Swatch($name);
	}

function input_checklist() {
	$args = func_get_args();
	$name = array_shift($args);
	return new \Input\Checklist($name, $args);
	}

function input_file($name, $table = '', $table_id = 0) {
	return new \Input\File($name, $table, $table_id);
	}

function input_radio($name, $options = array()) {
	return new \Input\Radio($name, $options);
	}

function input_text($name, $len = 30, $value = '') {
	return new \Input\Text($name, $len, $value);
	}

function input_email($name) {
	return new \Input\Email($name);
	}

function input_phone($name, $value = '') {
	return new \Input\Phone($name, 12, $value);
	}

function input_password($name, $len = 10) {
	return new \Input\Password($name, $len);
	}

function input_stripe($name, $amount, $id) {
	return new \Input\Stripe($name, $amount, $id);
	}

function input_button($name) {
	return new \Input\Button($name);
	}

function input_submit($name) {
	$inp = new \Input\Button($name);
	return $inp->type('submit');
	}

function input_textarea($name, $len = 250) {
	return new \Input\Textarea($name, $len);
	}

function input_select($name, $options = array()) {
	return new \Input\Select($name, $options);
	}

function input_date($name, $value = '') {
	$inp = new \Input\Date($name, 10, $value);
	return $inp->add_class('date-calendar');
	}

function input_state($name) {
	return new \Input\State($name);
	}

function input_time($name) {
	return new \Input\Time($name);
	}

function input_thumb($name, $booking) {
	return new \Input\Thumb($name, $booking);
	}

function input_date_triple($name) {
	return new \Input\DateTriple($name);
	}

/* CHAINING FUNCTIONS */

function show($name, $rows) {
	return new \Module\Show($name, $rows);
	}

function table($rows) {
	return new \Table($rows);
	}
	
function db() {
	return \Db::$db;
	}

/* ESCAPING */

function id_zero($x) {
	return preg_match("/^\d+$/", $x) ? $x : 0;
	}

function _to_camel($s) {
	return implode('', array_map('ucfirst', explode('_', $s)));
	}

function _to_words($s) {
	return ucwords(str_replace("_", " ", $s));
	}

function _to_class($s) {
	$ex = explode('/', $s);
	$class = "\\" . implode("\\", array_map('_to_camel', $ex));
	return $class;
	}

/* IMAGES / TAGS */

function title_tag($title = 'My Page') {
	return '<title>' . $title . '</title>';
	}

function meta_tags() {
	return ''
	. '<meta charset="UTF-8" />'
	. '<meta name="viewport" content="width=device-width, user-scalable=0" />';
	}

function doctype() {
	return	'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
	}

function jquery_tag() {
	return '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>';
	}

function style_tag($path) {
	return "<link rel='stylesheet' type='text/css' href='" . \Config::$local_path . $path . "' media='screen'></link>";
	}

function script_tag($path) {
	return 	"<script type='text/javascript' src='" . \Config::$local_path . $path . "'></script>";
	}

function upload_tag($url) {
	return "<img src='" . \Path::$local_path . "image.php?h=$url'>";
	}

function image_tag($url, $folder = 'public/images') {
	return "<img src='" . image_url($url, $folder) . "' />";
	}

function image_url($url, $folder = 'public/images') {
	return \Path::$local_path . "$folder/$url";
	}

/**
	Standard mallorca HTML wrapper with a stick footer built in.
	*/
function mallorca_wrapper() {
	return div('wrapper',
		div('content')
		. div('push')
		)
	. div('footer');
	}

/* MARKUP */

function rug() {
	return "<div class='rug'></div>";
	}

function print_var($var, $str = false) {
	$s = "<pre>" . print_r($var, true) . "</pre>";
	if ($str) return $s;
	echo $s;
	}

function pv($var)
	{
	return print_var($var, true);
	}
	
function newlines($s) {
	return "<p>" . str_replace("\n", "<br>", str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $s)) . "</p>";
	}

/* DATE FUNCTIONS */

function date_to($format, $date) {
	if (is_object($date)) return $date->format($format);

	$time = strtotime($date);
	return $date != '' && $time !== null ? date($format, $time) : '';
	}

/**
	Standard m/d/y date format.
	*/
function date_slashes($date) {
	return date_to('n/j/Y', $date);
	}

/**
	Standard who and when data to merge into queries.
	*/
function who_when() {
	return array(
		'created_by'=>\Login::$id,
		'created_on'=>date('Y-m-d H:i:s'),
		);
	}
	
/**
	MySQL now() equivalent.
	*/
function now() {
	return date('Y-m-d H:i:s');
	}

/**
	Convert a date to a "how long ago" statement.
	\return A string of how long ago $date was, in minutes, hours, or days respectively.
	\param string $date The date to convert.
	*/
function how_long_ago($date, $base = 60, $before = '', $after = '') {
	$qty = round((strtotime(date('Y-m-d')) - strtotime(date_to('Y-m-d', $date))) / 60); $ago = "m"; //minutes
	$neg = $qty < 0;
	$qty = abs($qty);
	if ($qty >= 60) 
		{
		$qty = round($qty / 60); $ago = " Hour";  //hours
		if ($qty >= 24)
			{
			$qty = round($qty / 24); $ago = " Day"; 
			if ($qty >= 7) { $qty = round($qty / 7); $ago = " Week"; }
			}
		else $qty = 0; // today
		}

	if ($qty > 1) $ago .= 's';

	return $qty == 0 ? "Today"
	: ($neg ? "<span class='future'>" . str_repeat("&bullet;", $qty - 1) . " In $qty $ago</span>"
	: "<span class='past'>$qty $ago Ago</span>");
	}

function day_diff($start, $end) {
	return date_diff(date_create($start), date_create($end))->format('%r%a');
	}

/**
	\return array of a date range.
	*/
function day_range($start, $end, $blank = array()) {
	$stime = strtotime($start);
	$range = range(0, day_diff($start, $end));
	$dates = array();
	foreach ($range as $i) {
		$dates[date('Y-m-d', strtotime(($i > 0 ? '+' : '') . $i . " days", $stime))] = $blank;
		}
	return $dates;
	}

/**
	Map all days over an array of select days.
	*/
function days_over($start, $end, $data) {
	$dates = day_range($start, $end, 0);

	$data = array_merge($dates, $data);
	ksort($data);

	return $data;
	}

/* FILES */

function file_dir($s) {
	$script = $_SERVER['SCRIPT_FILENAME'];
	$script = str_replace("/index.php", '', str_replace('\\', '/', $script));
	
	return $script . '/' . $s;
	}
	
/* SESSION */

function sesh_alert() {
	$alert = sesh('alert');
	if ($alert) return div('sesh-alert', $alert);
	$_SESSION['alert'] = '';
	}

function alert($msg = '') {
	$alert = sesh('alert');
	if ($msg === true) $_SESSION['alert'] = '';
	else if ($msg) $_SESSION['alert'] = $msg;
	return $alert;
	}

/* Mallorca Framework */

function stack($xs = array()) {
	return http_build_query(array('stack'=>$xs));
	}

function merge($fn, $param = array()) {
	$xs = array('stack_merge'=>$fn);
	if (! empty($param)) $xs['stack_merge_param'] = $param;
	return "&" . http_build_query($xs);
	}

function div() {
	$args = func_get_args();
	$class = array_shift($args);
	return "<div class='$class'>" . implode($args) . "</div>";
	}

function select($table, $columns = array('*')) {
	return new \Db\Query($table, $columns);
	}

function dataset($name) {
	return new DataSet($name);
	}

function fn($fn, $params = array()) {
	return array(
		'function'=>$fn,
		'params'=>$params
		);
	}

function call($class, $fn, $params = array(), $method = 'replace') {
	if (is_object($class)) $class = get_class($class);

	return array(
		'class'=>$class,
		'function'=>$fn,
		'params'=>$params,
		'method'=>$method,
		);
	}

function call_path_fn($path = '', $fn = '', $params = [], $method = 'replace') {
	return array(
		'q'=>$path,
		'function'=>$fn,
		'params'=>$params,
		'method'=>$method
		);
	}

function call_path($path = '', $params = []) {
	return array(
		'q'=>$path,
		'params'=>$params
		);
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

	/** The local url root prefix. */
	static public $local_path = '/';
	}
