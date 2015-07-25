<?php

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
abstract class Schema extends \View {
	static public $allow = array(
		'my_save'=>array('data'=>array()),
		'my_delete'=>array('data'=>array())
		);

	static public $db = 'Mysql';

	static public function extract_data_types($columns) {
		$new = array();
		foreach ($columns as $column) {
			$class = is_object($column) ? get_class($column) : '';

			if ($class == 'Meta') $new[] = $column;
			else {
				$name = $class == 'On' ? $column->get_name() : $column;
				list($data_type, $opt) = isset($column->data) && isset($column->data->type) ?
					$column->data->type : self::guess_data_type($name);
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
		Render a lookup from the schema.
		*/
	static public function lookup() {
		$index = \Path::index('schema/lookup', array(static::my_schema()));

		return $index->my_display();
		}

	static public function show($id) {
		$data = static::query(array(
			m('id')->where($id)
			))->one_row();

        $s = '';
		foreach (static::my_schema()->columns as $on) {
			$name = $on->get_name();
			if (strpos($name, '_id')) continue;
			if (isset($on->show)) {
				$label = isset($on->show->label) ? $on->show->label : $name;
				$s .= $on->show->fn($label, is($data, $name));
				}
			}
		return $s;
		}

	/**
		\param string $name	Class name / stack index name.
		\param	array	$with	Array of additional fields.
		\sa stack()
		*/
	static public function control_group($name = '.save', $with = array(), $id = 1) {
		$cs = array();

		$cs = self::extract_inputs(static::my_schema()->columns);
		$cs = array_merge($cs, $with);

		$group = input_group($name, $cs);
		return $group;
		}

	/**
		Turn an array of On objects into inputs.
		\param	array	$ons
		*/
	static public function extract_inputs($ons) {
		$cs = array();

		// change DataType objects to Inputs
		foreach (self::extract_data_types($ons) as $column) {

			$fn = "input_" . $column->type;

			// STAY
			$input = $fn($column->name)
				->set_value($column->def)
				->label(_to_words($column->name))
				;

			// new style
			if (isset($column->on_o) && isset($column->on_o->input)) {
				foreach ($column->on_o->input as $k=>$v) {
					$input->$k($v);
					}
				}


			$cs[] = $input;
			}
		return $cs;
		}

	/**
		\param	array	$mods	Array of modifications to the query.
		\return The default query object with any modifications added.
		*/
	static public function query($mods) {
		return static::my_query()->combine($mods);
		}

	static public $data_defs = array(
			'height'=>array('str', 20),
			'weight'=>array('str', 7),
			'gpa'=>array('dec', array(3,1)),
			);

	static public function guess_data_type($name) {
		if ($name == 'id') return array('primary_key', '');
		else if (strpos($name, 'is_') !== false) return array('bool', '');
		else if (strpos($name, '_id')) return array('int', '');
		else if (strpos($name, '_at')) return array('datetime', '');
		else if (strpos($name, '_name')) return array('str', 140);
		else if (strpos($name, '_per_')) return array('int', 1000000);
		else if (strpos($name, '_blob')) return array('blob', '');
		else if (is(self::$data_defs, $name)) return self::$data_defs[$name];
		else return array('str', 250);
		}

	static public function guess_input_type($name) {
		if (strpos($name, '_photo')) return 'file';
		else if ($name == 'id' || strpos($name, '_id')) return 'hidden';
		else if ($name == 'bio') return 'textarea';
		return 'text';
		}

	/**
		Unpack self::my_table() into a fully formed object.
		*/
	static public function table($name, $args) {
		$table = db()->table();

		$args = self::extract_data_types($args);

		$columns = array();
		foreach ($args as $column) {
			$dtype = $column->dtype;
			$columns[$column->name] = $table->$dtype($column->name, $column->opt);
			}
			
		return new DataType(array(
			'table'=>$name,
			'name'=>$name,
			'columns'=>$args,
			'obs'=>$columns
			));
		}

	/**
		Evaluate the table for creation / alteration and display to screen.
		*/
	static public function create() {
		$table = db()->table();

		$schema = static::my_schema();
		$name = $schema->table;
		$types = self::extract_data_types($schema->columns);
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

	/**
		\return Default \Db\Query object.  Can be modified for one or multiple rows, limits, where clause, etc.
		*/
	static public function my_query() {
		$my_table = static::my_table();
		return select($my_table->table, array('*'));
		}

	/**
		*/
	static public function my_save($data) {
		$schema = static::my_schema();
		\Db::match_upsert($schema->table, $data, " where id=" . id_zero(is($data, 'id')));
		return div('sesh-alert', 'Saved.'); // print_r($data, true);
		}

	/**
		*/
	static public function my_delete($data) {
		$schema = static::my_schema();
		\Db::query("delete from " . $schema->table . " where id=" . id_zero(is($data, 'id')));
		}

	/**
		\return Schema / Table definition. Begin with return self::table(...)
		*/
	static public function my_table() {
		return static::my_schema();
		}

	/**
		The new name to use for the my_table().
		*/
	static public function my_schema() {
		die('Define my_schema() for ' . get_called_class());
		}
	}

