<?php

/**
	Generic database interface.
	Can use MySQL, SQL, or any other plugin that has been defined.
	The aim of this class is to contain functions that are common in their definitions between the systems.
	*/
class Db {
	/**
		Instance of the particular database, MySQL, SQL class, etc..
		You may usually access this with the global function db().
		*/
	static public $db;

	/**
		Connect to the database.
		\param	string	$db	Database type (MySQL, SQL, etc).
		\param	string	$host	Host.
		\param	string	$username	Username.
		\param	string	$password	Password.
		\param	string	$database	Database.
		*/
	static public function connect($type, $host, $user, $password, $database, $port, $socket) {
		$class = '\\Db\\' . _to_camel($type);
		self::$db = new $class($host, $user, $password, $database, $port, $socket);
		}

	/**
		Connect from Config vars set in protected/_local.php
		*/
	static public function connect_from_config() {
		extract(Config::$db);
		if (! isset($port)) $port = '3306';
		if (! isset($socket)) $socket = '/var/run/mysqld/mysqld.sock';

		self::connect($type, $host, $user, $password, $database, $port, $socket);
		}

	/**
		Escape a value.
		*/
	static public function esc($value) {
		return self::$db->esc($value);
		}

	/**
		*/
	static public function query($query) {
		return self::$db->query($query);
		}

	/**
		\param	string	$query
		\return Single value.
		*/
	static public function value($query) {
		$result = self::$db->query($query);
		$a = self::$db->row($result);
		if ($result) self::$db->free($result);
		if ($a) return array_shift($a);
		return null;
		}

	/**
		\param	string	$query
		\return Single associative row.
		*/
	static public function one_row($query) {
		$q = self::$db->query($query);
		$a = self::$db->row($q);
		return $a ? $a : array();
		}

	/**
		\param	string	$query
		\return	Associative array from two columns, ie) id=>name
		*/
	static public function two_column_array($query) {
		$out = array();
		$q = self::$db->query($query);
		while ($a = self::$db->row($q)) {
			$key = array_shift($a);
			$out[$key] = array_shift($a);
			}
		return $out;
		}

	/**
		\param	string	$query
		\return One column numeric array.
		*/
	static public function one_column_array($query) {
		$out = array();
		$q = self::$db->query($query);
		while ($a = self::$db->row($q)) {
			$out[] = array_shift($a);
			}
		return $out;
		}

	/**
		\param	string	$query
		\return Array of associative row arrays (all results).
		*/
	static public function results($query) {
		$results = array();
		$q = self::$db->query($query);
		while ($results[] = self::$db->row($q)) {}
		array_pop($results);
		return $results;
		}

	static public function results_by_key($key, $query) {
		$q = self::$db->query($query);
		$results = array();
		while ($a = self::$db->row($q)) { $results[$a[$key]] = $a; }
		return $results;
		}
	
	/**
		Helper function for match_query.
		\param	array	$data
		\return Pinned together where clause, unescaped.
		*/
	static public function where($data) {
		$pairs = array();
		foreach ($data as $k=>$v) $pairs[] = self::$db->ent($k) . '=' . $v;

		return implode(' and ', $pairs);
		}

	/**
		Take an array of data and insert it to a table with only the columns that match the schema.
		\param	string	$table	The table name.
		\param	array	$data	Unescaped key value array of data to be inserted.
		*/
	static private function match_query($query_type, $table, $data, $where = '') {
		$info = self::$db->describe($table);
		$use = array();
		foreach ($info as $row) {
			$name = $row['Field'];
			$type = $row['Type'];

			if (isset($data[$name])) {
				$value = strpos($type, 'char') !== false ? self::$db->esc($data[$name])
				: (strpos($type, 'dec') !== false ? (preg_replace("/[^\d\.-]/", '', $data[$name]) ?: 'NULL')
				: (strpos($type, 'int') !== false ? id_zero($data[$name])
				: ($type == 'date' ? self::$db->esc(date_to("Y-m-d", $data[$name]))
				: ($type == 'datetime' ? self::$db->esc(date_to("Y-m-d H:i:s", $data[$name]))
				: self::$db->esc($data[$name])))));
				$use[$name] = $value;
				}
			}

		return self::$db->$query_type($table, $use, $where);
		}

	/**
		Match a table schema and perform an insert with $data.
		\param	string	$table
		\param	array	$data
		*/
	static public function match_insert($table, $data) {
		return self::match_query('insert', $table, $data);
		}

	/**
		Match a table schema and perform an update.
		\param	string	$table
		\param	array	$data
		\param 	string	$where A where clause (needs to include "where".
		*/
	static public function match_update($table, $data, $where = " where 1=0 ") {
		return self::match_query('update', $table, $data, $where);
		}

	/**
		Match a table schema to perform an upsert with $data and where clause in $where.
		\param	string	$table
		\param	array	$data
		*/
	static public function match_upsert($table, $data, $where = " where 1=0 ") {
		return self::match_query('update', $table, $data, $where) ? self::value("select id from $table $where")
		: self::match_query('insert', $table, $data);
		}

	/**
		Insert a row where no rows exist matching all of $data.
		\param	string	$table
		\param	array	$data
		*/
	static public function insert_unique($table, $data) {
		if (! self::value("select id from $table where " . self::where($data))) self::$db->insert($table, $data);
		}

	/**
		Return nested lists reminiscent of group bys, without losing sub results.
		\param	array	$groups	Array of field names to nest by.
		\param	string	$query	The query string.
		\param	string	$row_format	Row format style, function name.
		*/
	static public function nest_by($groups = array(), $query, $row_format = '') {
		$q = self::$db->query($query);
		$out = array();
		while ($row = self::$db->row($q)) {
			self::array_bury($out, $groups, $row, $row_format);
			}
		return $out;
		}
	
	/**
		Helper for nest_by.
		\param	array	$a	Dereferenced array to fill.
		\param	array	$groups	The group names to bury.
		\param	array	$row	The current row.
		\param	string	$row_format	Row format string.
		*/
	static public function array_bury(&$a, $groups, $row, $row_format = '') {
		$col = array_shift($groups);
		$key = $row[$col];
		if (empty($groups)) $a[$key][] = $row_format ? $row_format($row) : $row;
		else self::array_bury($a[$key], $groups, $row, $row_format);
		}
	}
