<?php
namespace Db;

/**
	Abstract database engine connection.
	*/
abstract class Engine {
	/** Connection handle. */
	public $conn;

	/**	Database type / name. */
	public $database;
	
	/** Array of child objects.  Ex) Table, Column. */
	private $children;

	/**
		*/
	public function __construct($host, $username, $password, $database) {
		$this->database = $database;
		$this->conn = $this->connect($host, $username, $password, $database);
		}

	/**
		Allow launching. Ex) db()->column() returns the specific column class, attached to this connection.
		*/
	public function __call($name, $params) {
		$class = '\\Db\\Mysql\\' . _to_camel($name);
		return new $class($this, $params);
		}

	/**
		\param	string	$host Host.
		\param	string	$username Username.
		\param	string	$password Password.
		\param	string	$database Database.
		*/
	abstract public function connect($host, $username, $password, $database);

	/**
		\param	string	$type	The type name.
		\param	string	$name	The field name.
		\param	string	$alias	Option alias.
		*/
	abstract public function format($type, $name, $alias = '');

	/**
		\param	mixed	$s	The value to escape.
		\return	Database specific escaped value.
		*/
	abstract public function esc($s);

	/**
		\param	string	$s	The entity name.
		\return Escaped entity name (tables, columns..).
		*/
	abstract public function ent($s);

	/**
		\param	string	$query	The query string.
		\return Query handle / object.
		*/
	abstract public function query($query);

	/**
		\param	handle	$handle	The query handle.
		\return An associative row.
		*/
	abstract public function row($handle);

	/**
		Free result handle.
		\param	handle	$result Result handle.
		*/
	abstract public function free($query);
	
	/**
		\param	string	$table	Table name.
		\param	array	$data	Data to insert.
		\return Database specific insert function.
		*/
	abstract public function insert($table, $data);

	/**
		\param	string	$table	Table name.
		\param	array	$data	Data to update.
		\param	string	$where	Fully formed where clause.
		*/
	abstract public function update($table, $data, $where = "1 = 0");

	/**
	public function results($query) {
		$results = array();
		$q = $this->query($query);
		while ($results[] = $this->row($q)) {}
		array_pop($results);
		return $results;
		}
		*/
	
	/**
		\param	string	$table Table name.
		\return Information schema array.
		*/
	abstract public function describe($table);
	}
