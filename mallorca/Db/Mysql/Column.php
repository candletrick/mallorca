<?php
namespace Db\Mysql;

/**
	Mysql column.
	*/
class Column extends \Db\Column {
	public function exists($table) {
		return 'select 1 from information_schema.columns'
		. " where table_schema=" . $this->parent->esc($this->parent->database)
		. " and table_name=" . $this->parent->esc($table)
		. " and column_name=" . $this->parent->esc($this->name);
		}

	public function create($table) {
		return 'alter table ' . $this->parent->ent($table) . ' add column ' . $this->parent->ent($this->name)
		. ' ' . $this->type;
		}

	public function delete($table) {
		return 'alter table ' . $this->parent->ent($table) . ' drop column ' . $this->parent->ent($this->name);
		}

	public function alter($table) {
		return 'alter table ' . $this->parent->ent($table) . ' change column ' 
		. ' ' . $this->parent->ent($this->name)
		. ' ' . $this->parent->ent($this->name)
		. ' ' . $this->type;
		}
	}
