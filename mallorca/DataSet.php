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

	public function __construct($name)
		{
		$this->name = $name;
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
		$res = select($this->name, array('*'))->results();

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

		foreach ($row as $cell) {
			$cells[] = "<td>$cell</td>";
			}
		foreach ($this->add as $cell) {
			$cells[] = "<td>$cell</td>";
			}

		return "<tr class='control-group'>" 
		. "<td>" . input_hidden('row_id', $row['id']) . "</td>"
		. implode('', $cells)
		. "</tr>";
		}

	public function add_col($col)
		{
		$this->add[] = $col;
		return $this;
		}
	}
