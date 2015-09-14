<?php

/**
	Primarily, to define tables for auto-creating / updating from the PHP side.

	my_table(), and my_columns() being the required functions.

	At the time of writing, it is taking on the character of a \MyIndex replacement due to the nature
	of being called as a "path" by mallorca.
	*/
class Model
	{
	/** Table name. */
	public $table;

	/** Array of column names / on() objects */
	public $columns;

	/** Table key name, such as table_id */
	public $keyname;

	/** The model as a path prefix, for example: User/Model yields /user */
	public $path;

	/** Row data. */
	public $data;

	/** Row id. */
	public $id;

	function __construct()
		{
		$this->table = $this->my_table();
		$this->columns = $this->my_columns();
		$this->keyname = $this->table . '_id';
		$this->path = strtolower(str_replace("\\", "/", get_class($this)));
		$this->data = [];
		$this->id = 0;
		}

	/**
		Construct that is meant to take place from \Request, passing in the URL arguments
		appropriately to capture the id and any other params.
		*/
	function my_construct($params = [])
		{
		$this->id = id_zero(is($params, $this->keyname));
		$this->data = $this->id ? select($this->table, [
			m('id')->where($this->id),
			'*'
			])->one_row() : [];

		return $this;
		}

	/**
		Launchable call.
		*/
	function __call($fn, $params = [])
		{
		$class = "\\Perfect" . _to_class($fn);
		if (! class_exists($class)) die("Class $class does not exist.");
		$new = new $class();
		return $new->model($this);
		}

	/**
		Simply get a select object for a given model without instantiating.
		Intended to be modified after.
		*/
	static public function select()
		{
		$class = get_called_class();
		$model = new $class();
		return select($model->table, ['*']);
		}

	/**
	function my_allow()
		{
		return [
			'my_save',
			];
		}
		*/

	/**
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
		*/

	/**
		\param	string	$name	Column name.
		\return Column value if it exists.
		*/
	function get($name)
		{
		return is($this->data, $name, "\$$name not set.");
		}

	/**
		Perform match_upsert with \Request::$data, based on the model.
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
		Delete a row from the table.
		TODO add additional constraints redefine function
		\param	string	$id	Id of the row to delete.
		*/
	function my_delete($id = 0)
		{
		if ($id) \Db::query("delete from $this->table where id=" . id_zero($id));
		}

	function path($path, $params = [])
		{
		$class = str_replace('model', $path, $this->path);
		return call_path($class, $params);
		}

	function call($fn, $params = [])
		{
		$params = array_merge([
			'id'=>$this->id,
			], $params);

		return call($this, $fn, $params);
		}
	/**

	/**

	/**
	function path_fn($path, $fn, $params = [], $method = 'replace')
		{
		$class = str_replace('model', $path, $this->path);
		return call_path_fn($class, $fn, $params, $method);
		}
		*/

	/**
		*/
	function lookup()
		{
		$o = new \CoralLookup();
		return $o->model($this);
		}

	/**
		phase out
		*/
	function my_lookup_query()
		{
		return;
		}

	/**
	function form() // $id = 0)
		{
		$this->id = id_zero($id);
		if ($this->id) $this->data = select($this->table, ['*', m('id')->where($this->id)])->one_row();
		$o = new \Perfect\Form($this);
		return $o->model($this);
		}
		*/
	}
