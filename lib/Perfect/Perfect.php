<?php

class Perfect
	{
	public $model;

	public function model($model)
		{
		$this->model = $model;
		return $this;
		}

	public function my_display()
		{
		return 'Define my_display';
		}

	public function get_names()
		{
		$cols = array();
		// echo pv($this->model); die;
		foreach ($this->model->columns as $c) {
			$cols[] = is_object($c) ? $c->get_name(): $c;
			}

		return $cols;
		}
	}