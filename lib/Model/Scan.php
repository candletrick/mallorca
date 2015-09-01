<?php
namespace Model;

/**
	Scans directories for Schema files and creates / modifies tables accordingly.
	*/
class Scan 
	{
	/**
		Main display.
		*/
	static public function my_display()
		{
		$dirs = \Config::$autoload_dirs;
		echo "<pre>Scanning <b>" . implode(', ', $dirs) . "</b> for tables to create..."; 
		foreach ($dirs as $d) {
			self::scan_dir($d, $d);
			}
		echo "</pre>";
		}

	/**
		Recursively scan project directory.
		*/
	static public function scan_dir($dir = '', $partial = '')
		{
		$d = dir($dir);
		while ($file = $d->read())
			{
			$full = "$dir/$file";
			if (preg_match("/^\./", $file)) continue;
			if (is_dir($full)) self::scan_dir($full, $partial);
			else {
				$class = str_replace('.php', '', $full);
				$class = str_replace($partial, '', $class);
				$class = str_replace('/', '\\', $class);

				if ($class != "\\Model\\Model"
					// && strpos($class, 'Model') !== false
					&& class_exists($class)
					&& get_parent_class($class) == 'Model'
					) {
					echo "\n\nFound $class.";
					$model = new $class();
					\Model\Create::create($model->my_table(), $model->my_columns());
					}
				}
			}
		}
	}
