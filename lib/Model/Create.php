<?php
namespace Model;

/**
	A class for creating and altering database schemas from generic PHP definitions,
	so that the schema and display can be defined simultaneously, in one place.

	\code
	Example class:
	class Message extends Schema {
		static public function my_schema() {
			return schema('message', array(
				'name',
				'read',
				'created_by',
				);
			}
		}
	*/
abstract class Create 
	{
	/** Database type. */
	static public $db = 'Mysql';

	/**
		Default data definitions.
		*/
	static public $data_defs = array(
		'height'=>array('str', 20),
		'weight'=>array('str', 7),
		'gpa'=>array('dec', array(3,1)),
		);

	/**
		Extract data types from a model my_columns function.
		*/
	static public function extract_data_types($columns)
		{
		$new = array();
		foreach ($columns as $column) {
			$class = is_object($column) ? get_class($column) : '';

			if ($class == 'Meta') $new[] = $column;
			else {
				$name = $class == 'On' ? $column->get_name() : $column;
				$dt =  isset($column->data) && isset($column->data->type) ?
					$column->data->type : self::guess_data_type($name);
				$data_type = is($dt, 0, 'str');
				$opt = is($dt, 1, '');
				$type = self::guess_input_type($name);
				$new[] = m($name)
					->dtype($data_type)
					->opt($opt)
					->name($name)
					->type($type)
					->on_o($column)
					;
				}
			}
		return $new;
		}

	/**
		Determine the data type by name or explicitly.
		*/
	static public function guess_data_type($name)
		{
		if ($name == 'id') return array('primary_key', '');
		else if (strpos($name, 'is_') !== false) return array('bool', '');
		else if (strpos($name, '_id')) return array('int', '');
		else if (strpos($name, '_at')) return array('datetime', '');
		else if (strpos($name, '_on')) return array('date', '');
		else if (strpos($name, '_by')) return array('int', 100000);
		else if (strpos($name, '_name')) return array('str', 140);
		else if (strpos($name, '_per_')) return array('int', 1000000);
		else if (strpos($name, '_blob')) return array('blob', '');
		else if (is(self::$data_defs, $name)) return self::$data_defs[$name];
		else return array('str', 250);
		}

	/**
		Determine input type.
		*/
	static public function guess_input_type($name)
		{
		if (strpos($name, '_photo')) return 'file';
		else if ($name == 'id' || strpos($name, '_id')) return 'hidden';
		else if ($name == 'bio') return 'textarea';
		return 'text';
		}

	/**
		Evaluate the table for creation / alteration and display to screen.
		*/
	static public function create($name, $cols = [])
		{
		$table = db()->table();

		$types = self::extract_data_types($cols);
		$columns = array();
			
		/*
		$name = $schema->name;
		*/
		foreach ($types as $column) {
			$dtype = $column->dtype;
			$columns[$column->name] = $table->$dtype($column->name, $column->opt);
			}

		// Catch requested changes
		if (get('delete')) {
			$c = db()->column(get('delete'), '');
			if (\Db::value($c->exists($name))) db()->query($c->delete(get('table')));
			}
		else if (get('change')) {
			$c = is($columns, get('change'));
			if ($c) db()->query($c->alter(get('table')));
			}

		// Create table
		if (! \Db::value($table->exists($name))) {
			echo "\n|_Create table " . self::bold($name) . ": ";
			db()->query($table->create($name));
			echo($table->create($name));
			}
		else {
			echo "\n|_Table " . self::bold($name) . " exists.";
			}
			
		// Create columns
		foreach ($columns as $c) {
			if (! \Db::value($c->exists($name))) {
				echo "\n  |_Create column " . self::bold($c->name) . ": ";
				db()->query($c->create($name));
				echo($c->create($name));
				}
			}

		// Changes to columns
		foreach (db()->describe($name) as $c) {
			$column = is($columns, $c['Field']);
			if ($c['Field'] == 'id') continue; // skip id column
			if (! $column) {
				echo "\n |_Column: " . self::bold($c['Field']) . " no longer exists... ";
				echo \Db::value("select count(*) from {$name} where {$c['Field']} is not null");
				echo " rows have data in this field. ";
				echo "<a href='schema.php?table=$name&delete=" . $c['Field'] . "'>Delete?</a>";
				}
			else if ($c['Type'] != $column->type) {
				echo "\n |_Column: " . self::bold($c['Field']) . " changed from [" . $c['Type'] . "] to [" . $column->type . "]. ";
				echo "<a href='schema.php?table=$name&change=" . $c['Field'] . "'>Change?</a>";
				}
			}
		}

	/**
		Will modify when this class is changed to command line usage.
		*/
	static private function bold($s) {
		return "<b>$s</b>";
		}
	}

