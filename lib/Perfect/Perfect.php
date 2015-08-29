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

		$query = $this->model->my_lookup_query();
		if ($query) {
			return $query->columns;
			}
		
		$cols = [m('id')];
		foreach ($this->model->columns as $c) {
			if (! is_object($c)) continue;
			if (! isset($c->lookup)) continue;
			
			$m = m($c->get_name());
			// $cols[] = $c->lookup;
			foreach ($c->lookup as $k=>$v) {
				if (! is_array($v)) $v = [$v];
				$m = call_user_func_array([$m, $k], $v);
				}
			$cols[] = $m;
			}

		// die(pv($cols));

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
