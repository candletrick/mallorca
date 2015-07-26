<?php
namespace Schema;

class Meta 
	{
	function __construct()
		{
		$schema = $this->my_schema();
		$this->table = $schema->table;
		$this->columns = $schema->columns;
		$this->id = 0;
		$this->data = [];
		$this->path = strtolower(str_replace("\\", "/", get_class($this)));
		}

	/**
		Launchable call.
		*/
	function __call($fn, $params = [])
		{
		$class = _to_class($fn);
		$new = new $class();
		return $new->schema($this);
		}

	function params($params = [])
		{
		$this->params = $params;
		$id = is($params, 'id');
		if ($id) {
			$this->id = id_zero($id);
			$this->data = select($this->table, [m('id')->where($id), '*'])->one_row();
			}
		}

	function my_save($data = [])
		{
		$this->id = $id = \Db::match_upsert($this->table, $data, " where id=" . id_zero(is($data, 'id')));
		alert("Saved! New id: $id.");
		return $this;
		}

	function my_delete($data = [])
		{
		// die(pv($data));
		$id = id_zero(is($data, 'id'));
		if ($id) \Db::query("delete from $this->table where id=" . $id);
		}

	function call($fn)
		{
		return call($this, $fn);
		}

	function path($path, $params = [])
		{
		$class = str_replace('schema', $path, $this->path);
		return call_path($class, $params);
		}

	function path_fn($path, $fn, $params = [], $method = 'replace')
		{
		$class = str_replace('schema', $path, $this->path);
		return call_path_fn($class, $fn, $params, $method);
		}

	function lookup()
		{
		$o = new \Perfect\Lookup();
		return $o->schema($this);
		}

	function form()
		{
		$o = new \Perfect\Form($this);
		return $o->schema($this);
		}
	}
