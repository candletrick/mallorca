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

	public function get_lookup()
		{
		$cols = [m('id')];
		foreach ($this->model->columns as $c) {
			if (! is_object($c)) continue;
			if (! isset($c->lookup)) continue;
			
			$m = m($c->get_name());
			foreach ($c->lookup as $k=>$v) $m = $m->$k($v);
			$cols[] = $m;
			}

		return $cols;
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
