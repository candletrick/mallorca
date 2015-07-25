<?php
class Meta
	{
	public function __construct($args) {
		foreach ($args as $k=>$v) {
			if ($k == 'this') continue;
			$this->$k = $v;
			}
		$this->as = $this->name;
		}

	public function __get($name) {
		return '';
		}

	public function __call($name, $params) {
		if (count($params) > 1) {
			$this->$name = $params;
			}
		else if (empty($params)) $this->$name = 1;
		else $this->$name = array_shift($params);
		return $this;
		}

	public function __toString() {
		return $this->name;
		}

	public function left($table, $conditions = array(), $join_type = 'left outer') {
		if (is_object($table)) {
			// print_var(get_defined_vars());
			}
		$this->join = new Meta(get_defined_vars());
		return $this;
		}

	public function inner($table, $conditions = array(), $join_type = 'inner') {
		$this->join = new Meta(get_defined_vars());
		return $this;
		}

	public function substr($start, $end) {
		$this->start = $start;
		$this->end = $end;
		$this->substr = true;
		return $this;
		}

	}

