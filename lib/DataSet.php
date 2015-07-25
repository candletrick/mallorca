<?php

/**
	Aims to be as abstracted a way of viewing data is for input_group and forms.
	*/
class DataSet
	{
	public $add = array();
	public $filter = array();
	public $name;
	public $results = array();
	public $row_fn;

	public function __construct($name, $columns = array('*'))
		{
		$this->name = $name;
		$this->columns = $columns;

		$this->query = select($name, $columns);
		}
	
	/**
		Allow assignment of callbacks.
		*/
	public function __call($name, $args)
		{
		if (isset($this->$name) && is_callable($this->$name)) {
			return call_user_func_array($this->$name, $args);
			}
		}

	public function results()
		{
		$this->results = select($this->name, array('*'))
			->combine($this->filter)
			->results();
		return $this->results();
		}

	public function my_display($class = '')
		{
		// $res = select($this->name, array('*'))->results();
		$res = $this->query->results();

		$rows = array();
		foreach ($res as $row) {
			$rows[] = $this->one_row($row);
			}

		return "<table class='$class'>"
		. implode('', $rows)
		. "</table>";
		}

	public function filter($filter)
		{
		$this->filter = $filter;
		return $this;
		}

	public function one_row($row)
		{
		$cells = array();

		$show = isset($this->row_fn) ? $this->row_fn($row) : $row;

		foreach ($show as $cell) {
			$cells[] = "<td>$cell</td>";
			}

		return "<tr class='control-group'>" 
		. "<td>" . input_hidden('row_id', $row['id']) . "</td>"
		. implode('', $cells)
		. "</tr>";
		}

	public function row($row_fn)
		{
		$this->row_fn = $row_fn;
		return $this;
		}
	}
