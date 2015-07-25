<?php

class Perfect
	{
	public $schema;

	public function schema($schema)
		{
		$this->schema = $schema;
		return $this;
		}

	public function my_display()
		{
		return 'Define my_display';
		}

	public function get_names()
		{
		$cols = array();
		foreach ($this->schema->columns as $c) {
			$cols[] = is_object($c) ? $c->get_name(): $c;
			}

		return $cols;
		}
	}
