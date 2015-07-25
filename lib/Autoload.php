<?php
/**
	Autoloader.
	Searches Config::$autoload_dirs for My/Example.php, then My/Example/Example.php.
	*/
function autoload_lib($name)
	{
	$a = explode("\\", $name);
	$end = $a[count($a) - 1];
	$name = str_replace("\\", "/", $name);

	$folders = \Config::$autoload_dirs;

	$errors = array();
	foreach ($folders as $folder) {

		$path = __DIR__ . "/../.." . \Config::$local_path . "$folder/$name";

		$dir = "$path/$end.php";
		$file = "$path.php";

		if (file_exists($file)) {
			require $file;
			return;
			}
		else if (file_exists($dir)) {
			require $dir;
			return;
			}
		else {
			$errors[] = $file;
			$errors[] = $dir;
			}
		}
	/*
	if (! empty($errors)) {
		die('<pre>'
			. "Can't find possible files to include:\n"
			. implode("\n", array_map('strtolower', $errors))
			. '</pre>'
			);
		}
		*/
	}
spl_autoload_register('autoload_lib');

