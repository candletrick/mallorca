<?php

class On {
	private $child = false;
	private $parent;
	private $name;

	public function get_name() {
		return $this->name;
		}

	public function __construct($name) {
		$this->name = $name;
		}

	public function __get($name) {
		if ($name == 'end') return $this->parent;

		$th = $this->child ? $this->parent : $this;
		$th->$name = on($name);
		$th->$name->child = true;
		$th->$name->parent = $th;
		return $th->$name;
		}

	public function __call($fn, $params = array(1)) {
		if (property_exists($this, $fn) && is_callable($this->$fn)) {
			return call_user_func_array($this->$fn, $params);
			}
		// default value of 1
		$this->$fn = empty($params) ? 1 : array_shift($params);
		return $this;
		}
	}

