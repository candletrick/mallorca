<?php
namespace Db;

/**
	Generic column interface for automatic schema creation / updating.
	*/
abstract class Column {
	/** Parent object, \Db\Table. */
	public $parent;

	/** Column name. */
	public $name;

	/** Full column type, example int unsigned. */
	public $type;

	/**
		\param	object	$parent	Follows the launch style from table. Ex) $table->column()
		\param	array	$params	Associative array with keys name, type, that maps safely.
		*/
	public function __construct($parent, $params)
		{
		$this->parent = $parent;
		extract(array_combine(array('name', 'type'), $params));
		$this->name = $name;
		$this->type = $type;
		}

	/**
		Return the column string formed when object is echoed.
		*/
	public function __toString() {
		return $this->name . ' ' . $this->type;
		}

	/**
		\param	string	$table	Table name.
		\return If this column exists in $table.
		*/
	abstract public function exists($table);

	/**
		\param	string	$table	Table name.
		\return Query to create this column in $table.
		*/
	abstract public function create($table);

	/**
		\param	string	$table	Table name.
		\return Query to delete this column from $table.
		*/
	abstract public function delete($table);

	/**
		\param	string	$table	Table name.
		\return Query to alter the column to $this->type in $table.
		*/
	abstract public function alter($table);
	}
