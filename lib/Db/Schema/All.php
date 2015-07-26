<?php
namespace Db\Schema;

/**
	Scans directories for Schema files and creates / modifies tables accordingly.
	*/
class All {
	static public $dir = array('my_lib', 'lib');
	
	static public function my_display() {
		echo "<pre>Scanning <b>" . implode(', ', self::$dir) . "</b> for tables to create..."; 
		self::list_dir();
		echo "</pre>";
		}

	static public function list_dir() {
		foreach (self::$dir as $d) {
			self::scan_dir($d);
			}
		}

	static public function scan_dir($dir = '') {
		$d = dir($dir);
		while ($file = $d->read())
			{
			$full = "$dir/$file";
			if (preg_match("/^\./", $file)) continue;
			if (is_dir($full)) self::scan_dir($full);
			else {
				$class = str_replace('.php', '', $full);
				$class = str_replace(self::$dir, '', $class);
				$class = str_replace('/', '\\', $class);
				// echo $full . " - " . $class . "<br>";
				
				if ($class != "\\Db\\Schema\\Schema"
					&& $class != "\\Schema\\Schema"
					&& strpos($class, 'Schema') !== false
					&& class_exists($class)
					&& (get_parent_class($class) == 'Schema'
					|| get_parent_class($class) == 'Schema\Meta')
					) {
						// $schema = new $class($this->index);
						echo "\n\nFound $class.";
						$inst = new $class();
						$schema = $inst->my_schema();
						\Schema::create($schema);
						// $class::create();
					}
				}
			}
		}
	}
