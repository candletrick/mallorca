<?php
namespace Schema;

class Lookup extends \Module\Lookup
	{
	public function __construct($index, $params)
		{
		list($schema) = $params;
		
		$this->table = $schema->table;
		foreach ($schema->columns as $column) {
			if (is_object($column)) {
				// apply lookup properties
				if (isset($column->lookup)) {
					$m = m($column->get_name());
					// print_var($m);
					foreach ($column->lookup as $k=>$v) {
						// echo $k . $v;
						$m->$k($v);
						}
					$this->columns[] = $m;
					}
				else $this->columns[] = $column->get_name();
				}
			else $this->columns[] = $column;
			}
		parent::__construct($index);
		}

	public function my_query() {
		return select($this->table, $this->columns);
		}
	
	public function my_columns() {
		$xs = array();
		foreach ($this->columns as $column) {
			 // print_var($column);
			if (is_object($column) && isset($column->show)) {
				$xs[] = $column->as;
				}
			// $xs[] = is_object($column) ? $column->as : $column;
			}
		return $xs;
		}
	}
