<?php

/**
	Model.
	*/
class Model
	{
	/**
		*/
	function __construct($id = 0)
		{
		$this->table = $this->my_table();
		$this->columns = $this->my_columns();
		$this->id = $id;
		$this->data = [];
		$this->path = strtolower(str_replace("\\", "/", get_class($this)));
		}

	/**
		Launchable call.
		*/
	function __call($fn, $params = [])
		{
		$class = _to_class($fn);
		if (! class_exists($class)) die("Class $class does not exist.");
		$new = new $class();
		return $new->model($this);
		}

	function my_allow()
		{
		return [
			'my_save',
			];
		}

	/**
		*/
	function params($params = [])
		{
		$this->params = $params;
		$id = is($params, 'id');
		if ($id) {
			$this->id = id_zero($id);
			$this->data = select($this->table, [m('id')->where($id), '*'])->one_row();
			}
		}

	function get_data()
		{
		if (empty($this->data)) {
			if ($this->id) $this->data = select($this->table, ['*', m('id')->where($this->id)])->one_row();
			}
		return $this->data;
		}

	function get($name)
		{
		return is($this->data, $name, "\$$name not set.");
		}

	/**
		*/
	function my_save()
		{
		$data = \Request::$data;
		$this->id = $id = \Db::match_upsert($this->table, $data, " where id=" . id_zero(is($data, 'id')));
		$this->data = select($this->table, ['*', m('id')->where($id)])->one_row();
		alert("Saved! New id: $id.");
		return $this;
		}

	/**
		*/
	function my_delete($id = 0)
		{
		// $id = id_zero(is($data, 'id'));
		if ($id) \Db::query("delete from $this->table where id=" . id_zero($id));
		}

	/**
		*/
	function call($fn, $params = [])
		{
		$params = array_merge([
			'id'=>$this->id,
			], $params);

		return call($this, $fn, $params);
		}

	/**
		*/
	function path($path, $params = [])
		{
		$class = str_replace('model', $path, $this->path);
		return call_path($class, $params);
		}

	/**
		*/
	function path_fn($path, $fn, $params = [], $method = 'replace')
		{
		$class = str_replace('model', $path, $this->path);
		return call_path_fn($class, $fn, $params, $method);
		}

	/**
		*/
	function lookup()
		{
		/*
		// $o = new \Perfect\Lookup();
		$ex = explode("\\", get_class($this));
		array_pop($ex);
		array_push($ex, 'Lookup');
		$lookup_class = implode("\\", $ex);
		if (class_exists($lookup_class)) {
			$mod = new $lookup_class();
			return $mod->my_display();
			}
			*/
		
		
		$o = new \CoralLookup();
		return $o->model($this);
		}

	/**
		*/
	function my_lookup_query()
		{
		return;
		}

	/**
		*/
	function form($id = 0)
		{
		$this->id = id_zero($id);
		if ($this->id) $this->data = select($this->table, ['*', m('id')->where($this->id)])->one_row();
		$o = new \Perfect\Form($this);
		return $o->model($this);
		}
	}
