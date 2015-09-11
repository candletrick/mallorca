<?php

/**
	Very IMperfect class, however I chose the name because I originally had the thought:
	"perfect form" like an athletic technique, in my mind.

	This folder serves to replace \Module, though it may not do so,
	with a mallorca-style form editor, lookup, login, etc.
	*/
abstract class Perfect
	{
	/** Model. */
	public $model;

	/**
		Set the model
		\param	object	$model
		*/
	public function model($model)
		{
		$this->model = $model;
		return $this;
		}

	/**
		Must exist for now for \Request
		*/
	public function my_construct($params = array())
		{
		}

	/**
		Display function.
		*/
	public function my_display()
		{
		return 'Define my_display';
		}

	/**
		Strip from my_columns in the model, those with on()->edit,
		TODO refactor / simplify later.
		*/
	public function get_edit()
		{
		$query = $this->model->my_lookup_query();
		if ($query) {
			return $query->columns;
			}
		
		$cols = [m('id')];
		foreach ($this->model->columns as $c) {
			if (! is_object($c)) continue;
			if (! isset($c->edit)) continue;
			
			$cols[] = $c;
			}

		return $cols;
		}

	/**
		Strip from my_columns in the model, those with on()->lookup,
		TODO refactor / simplify later.
		*/
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
			foreach ($c->lookup as $k=>$v) {
				if (! is_array($v)) $v = [$v];
				$m = call_user_func_array([$m, $k], $v);
				}
			$cols[] = $m;
			}

		return $cols;
		}

	/**
		Strip just the names from the model's my_columns function.
		*/
	public function get_names()
		{
		$cols = array();
		foreach ($this->model->columns as $c) {
			$cols[] = is_object($c) ? $c->get_name(): $c;
			}

		return $cols;
		}
	}
