<?php
namespace Db;

/**
	Query class.
	This class allows for a SQL (or another language) query to written
	in an abstract, chainable, nested, object format.

	There are main concepts:
	1. A column name only need to be written once, despite needing to appear in the select, where, group, and order clauses.
	2. A query is abstracted into PHP (or another language) so that one format can generate all the database specific syntax.
	3. A query is an object that can be modified concisely and exactly in any way desired later on in the code for making
		the most appropriate data calls, as few times as possible.

	Introduction
	===

	The function table() takes a table name and an array of column names or "meta" objects.
	The meta objects can chain functions that modify the column in any way desired across the query.
	For example added to the group by, order by, aliased, added to a function, etc.

	Furthermore, the meta object itself can be passed an array of column names or meta objects, allowing for
	nested queries and very exact joining logic without needing to alias any tables specifically.

	Example Queries
	===

	Select
	---
	table('users', array(
		'name'
		))
		=
		select name from users

	Left outer join
	---
	table('users', array(
		'name',
		m('group_name')->left('group', array('id'=>'group_id'))
		))
		=
		select 
			a.name,b.group_name
		from users a
		left outer join group b on b.id=a.group_id

	Double join
	---
	table('users', array(
		'name',
		m(array(
			'group_name',
			m('company')->left('company', array('id'=>'company_id'))
			))->left('group', array('id'=>'group_id'))
		))
		=
		select 
			a.name,b.group_name
		from users a
		left outer join group b on b.id=a.group_id
		left outer join company c on c.id=b.company_id

	Nested join
	---
	table('users', array(
		'name'
		m('posts')->left(table('blog', array(
			m('user_id')->group()
			m()->count()
			)), array('user_id'=>'id'))
		))

	...	
	*/
class Query {

	/** Table aliases. */
	public $keys = array();

	/** Joins. */
	public $joins = array();

	/** Wheres. */
	public $wheres = array();

	/** Group bys. */
	public $groups = array();

	/** Order bys. */
	public $orders = array();

	/** Limit. */
	public $limit = 0;

	/** Which alias the column belongs to. */
	public $belongs = array();

	/**
		*/
	public function __construct($table, $columns) {
		$this->table = $table;

		$map = array();
		$i = 0;
		foreach ($columns as $column) {
			$name = is_object($column) ? (
				isset($column->as) ? $column->as : $column->name
				) : $column;
			if (is_array($name)) $name = 'array_' . $i++;
			if ($name == '*') $map[] = $column;
			else $map[$name] = $column;
			}
		$this->columns = $map;
		}

	public function __toString() {
		return $this->text();
		}

	/**
		Join a table.
		*/
	public function join($m, $key, $depth = 1, $from = 'a') {
		$on = array();

		// Default join condition
		if (empty($m->conditions)) {
			$m->conditions = array('id'=>$m->table . "_id");
			}

		foreach ($m->conditions as $k=>$v) {
			$right = is_string($v) ? $from . ".$v" : $v;
			$on[] = "$key.$k=$right";
			}

		$tab = str_repeat("\t", $depth);

		// nested joins
		$table = is_object($m->table) ? "(\n$tab" . $m->table->text($depth + 1) . "\n$tab)" : $m->table;
		
		$join = $m->join_type . " join " . $table . " $key on " . implode(' and ', $on);
		$this->joins[] = $join;
		}

	public function unpack_column($column, $key, $depth) {
		// Meta Object
		if (is_object($column)) {
			// Add any joins that the column suggests
			if (isset($column->join)) {
				$from = $key;
				$key = array_shift($this->keys);
				$this->join($column->join, $key, $depth, $from);
				}
			// "Recurse" through array if so.
			if (is_array($column->name)) {
				foreach ($column->name as $name) $this->unpack_column($name, $key, $depth);
				return;
				}

			// Set name
			$this->belongs[$column->name] = $key;
			$name = is_string($column->name) ? $key . "." . $column->name : $column->name;
			$original = $name;

			// Apply functions
			if (isset($column->cast)) $name = "cast($name as $column->cast)";

			if (isset($column->times)) $name = "$name * " . (is_string($column->times) ? is($this->belongs, $column->times, 'a') . '.' . $column->times : $column->times);
			if (isset($column->sum)) $name = "sum($name)";
			if (isset($column->round)) $name = "round($name, $column->round)";
			if (isset($column->substr)) $name = "substring($name, $column->start, $column->end)";
			if (isset($column->max)) $name = "max($name)";
			if (isset($column->count)) $name = "count(*)";
			if (isset($column->hours)) $name = "round(((60 * hour($name)) + minute($name)) / 60, 2)";
			if (isset($column->timediff)) {
				$diff = "timediff($name, $column->timediff)";
				$name = "round((60 * hour($diff)) + minute($diff), 2)";
				}
			if (isset($column->format)) $name = db()->format($column->format, $name);

			// null
			if (isset($column->ifnull)) $name = "ifnull($name, $column->ifnull)";


			// Add to clauses
			if (isset($column->group)) $this->groups[] = $name;
			if (isset($column->order)) $this->orders[] = $name;
			if (isset($column->desc)) $this->orders[] = "$name desc";
			if (isset($column->asc)) $this->orders[] = "$name asc";

			// where
			if (isset($column->where)) $this->wheres[] = "$name=" . db()->esc($column->where);
			else if (isset($column->where_like)) $this->wheres[] = "$name like " . db()->esc('%' . $column->where_like . '%');
			else if (isset($column->where_gte)) $this->wheres[] = "$name >= " . db()->esc($column->where_gte);
			else if (isset($column->where_not)) $this->wheres[] = "$name is null or ($name <> " . db()->esc($column->where_not) . ")";
			else if (isset($column->blank)) $this->wheres[] = "($name is null or $name='')";

			// Alias
			if ($column->as != '*') $name = $name . ($name == $column->as ? '' : " as $column->as");
			$this->names[] = $name;
			}
		// Repeat over array
		else if (is_array($column)) {
			foreach ($column as $one) $this->unpack_column($one, $key, $depth);
			return;
			}
		// Just a string
		else {
			$name = is_string($column) ? $key . "." . $column : $column;
			$this->names[] = $name;
			}
		}

	/**
		\return The completed query string.
		*/
	public function text($depth = 1) {
		// Set aliases from a-z
		$this->keys = range('a', 'z');
		$key = array_shift($this->keys);

		foreach ($this->columns as $c) $this->unpack_column($c, $key, $depth);

		// Visual tabs
		$tab = str_repeat("\t", $depth);
		$untab = str_repeat("\t", $depth - 1);

		// Full query
		return "select\n$tab"
		. implode(",\n$tab", $this->names)
		. "\n{$untab}from $this->table a"
		. (! empty($this->joins) ? "\n$untab" . implode("\n$untab", $this->joins) : '')
		. (! empty($this->wheres) ? "\n{$untab}where " . implode("\n{$untab}and ", $this->wheres) : '')
		. (! empty($this->groups) ? "\n{$untab}group by\n$tab" . implode(",\n$tab", $this->groups) : '')
		. (! empty($this->orders) ? "\n{$untab}order by\n$tab" . implode(",\n$tab", $this->orders) : '')
		. ($this->limit ? "\n{$untab}limit $this->limit" : '')
		;
		}

	/**
		*/
	public function results() {
		// return array($this->text());
		return \Db::results($this->text());
		}

	/**
		*/
	public function value($name) {
		$this->names = array($name);
		return \Db::value($this->text());
		}

	/**
		*/
	public function one_row() {
		return \Db::one_row($this->text());
		}

	/**
		*/
	public function two_column_array() {
		return \Db::two_column_array($this->text());
		}

	/**
		Add columns.
		*/
	public function combine($combine) {
		// matches by "as" property
		
		foreach ($combine as $meta) {
			$name = is_object($meta) ? (
				isset($meta->as) ? $meta->as : $meta->name
				) : $meta;
			if (! isset($this->columns[$name])) {
				$this->columns[$name] = $meta;
				}
			else if (! is_object($meta) || ! is_object($this->columns[$name])) {
				$this->columns[$name] = $meta;
				}
			else {
				unset($meta->name);
				foreach ($meta as $k=>$v) {
					$this->columns[$name]->$k = $v;
					}
				}
			}
				
		// $this->columns = array_merge($this->columns, $combine);
		return $this;
		}

	/**
		Set the limit.
		*/
	public function limit($limit) {
		$this->limit = $limit;
		return $this;
		}
	}
