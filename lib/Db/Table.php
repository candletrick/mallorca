<?php
namespace Db;

/**
	Abstract table definition for Schema class.
	*/
abstract class Table
	{
	/**
		Launches from Db object.
		\param	object	$parent	The parent Db instance.
		*/
	public function __construct($parent) {
	 	$this->parent = $parent;
	 	}

	/**
		\param	string	$table Table name.
		\return Query for existence of $table.
		*/
	abstract public function exists($table);

	/**
		\param	string	$table Table name.
		\return Query to create table.
		*/
	abstract public function create($table);


	/**
		\param	string $name Column name.
		\param	int	$max	Max value.
		\return Int column object.
		*/
	abstract public function int($name, $max = 'int');

	/**
		\param	string	$name Column name.
		\return Boolean column object.
		*/
	abstract public function bool($name);

	/**
		\param	string	$name Column name.
		\return Date column object.
		*/
	abstract public function date($name);
	
	/**
		\param	string	$name Column name.
		\return Time column object.
		*/
	abstract public function time($name);

	/**
		\param	string	$name Column name.
		\return Phone column object.
		*/
	abstract public function phone($name);

	/**
		\param	string	$name Column name.
		\param	int	$len	Column length.
		\return String column object.
		*/
	abstract public function str($name, $len);

	/**
		\return Creator / user column object.
		*/
	abstract public function who();

	/**
		\return Datestamp column object.
		*/
	abstract public function when();
	}
