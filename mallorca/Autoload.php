<?php
/**
	Autoloader.
	Searches my_lib, then lib dir for My/Example.php, then My/Example/Example.php.
	*/
function autoload_lib($name)
	{
	$a = explode("\\", $name);
	$end = $a[count($a) - 1];
	$name = str_replace("\\", "/", $name);

	$folders = array('mallorca', 'lib');

	foreach ($folders as $folder) {
		$path = __DIR__ . "/../$folder/$name";

		$dir = "$path/$end.php";
		$file = "$path.php";

		if (file_exists($file)) require $file;
		else if (file_exists($dir)) require $dir;
		}
	}
spl_autoload_register('autoload_lib');

