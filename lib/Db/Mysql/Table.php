<?php
namespace Db\Mysql;

/**
	Mysql table.
	*/
class Table extends \Db\Table {

	public function exists($table) {
		return 'select 1 from information_schema.tables'
		. " where table_schema=" . $this->parent->esc($this->parent->database)
		. " and table_name=" . $this->parent->esc($table) ;
		}

	public function create($table) {
		return 'create table ' . $this->parent->ent($table) . '(' . $this->primary_key() . ')';
		}

	public function primary_key() {
		return $this->parent->column('id', 'mediumint unsigned primary key auto_increment');
		}

	public function int($name, $max = 'int') {
		$type = ($max <= 255 ? 'tinyint(3) unsigned'
		: ($max <= 655535 ? 'smallint(5) unsigned'
		: ($max <= 16000000 ? 'mediumint(8) unsigned'
		: 'int unsigned')));
		return $this->parent->column($name, $type);
		}

	public function dec($name, $size = array(7, 2)) {
		list($len, $float) = $size;
		return $this->parent->column($name, "decimal($len,$float)");
		}

	public function ft($name) {
		return $this->parent->column($name, "varchar(5)");
		}

	public function lbs($name, $max = 500) {
		return $this->int($name, $max);
		}

	public function bool($name) {
		return $this->parent->column($name, "tinyint(1)");
		}

	public function blob($name) {
		return $this->parent->column($name, "blob");
		}

	public function date($name) {
		return $this->parent->column($name, "date");
		}
	
	public function datetime($name) {
		return $this->parent->column($name, "datetime");
		}

	public function time($name) {
		return $this->parent->column($name, 'time');
		}

	public function phone($name) {
		return $this->str($name, 13);
		}

	public function str($name, $len) {
		return $this->parent->column($name, "varchar($len)");
		}

	public function who() {
		return $this->parent->column('created_by', "mediumint(8) unsigned");
		}

	public function when() {
		return $this->parent->column('created_on', "datetime");
		}
	}
