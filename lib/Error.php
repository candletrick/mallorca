<?php
/* Error Handling */

ini_set('gd.jpeg_ignore_warning', true);

/**
	Write errors to an 'error_log.txt' file.
	*/
function kit_error($error_level, $error_message, $error_file = '', $error_line = 0)
	{
	$error_log = fopen('error_log.txt', 'a+');
	if (!$error_log) return false;
	if (!$error_file)
		{
		$a = debug_backtrace();
		$b = array_pop($a);
		$error_file = $b['file'];
		$error_line = $b['line'];
		}
	$error_file .= isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
	$name = 'Unknown'; // sesh('realname', $_SERVER['REMOTE_ADDR']);
	fprintf($error_log, "%s %s - %s\n\n\t %s line: %s\n\n",	
	date("n/j g:i", time()), $name, $error_file, $error_message, $error_line);
	return true;
	}

// errors
if (\Path::$local_path != '/') {
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	}
else {
	set_error_handler('kit_error');
	}
