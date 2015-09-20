<?php
/**
	All classes that are compatible to work with MyIndex
	should extend this class to make sure the basic functionality is covered.
	*/
class Module
	{
	/**
		Usually launches from an index.
		*/
	public function __construct($index)
		{
		$this->index = $index;
		}

	/**
		Display.
		*/
	public function my_display()
		{
		return "Define your display function.";
		}

	/**
		Any extra html <head> code.
		*/
	public function my_head()
		{
		return "";
		}

	/**
		Synonym for above.
		*/
	public function my_headers()
		{
		return "";
		}

	/**
		Redefine the index above you.
		*/
	public function my_index()
		{
		return;
		}

	/**
		Prepare a mallorca-style static function call.
		*/
	static public function call($fn, $params = array(''))
		{
		return callStatic(get_called_class(), $fn, $params);
		}

	static public function bare()
		{
		$class = get_called_class();
		return new $class(true, true);
		}
		
	public function model_class()
		{
		$class = get_class($this);
		// $id = $this->index->id;
		$ex = explode("\\", $class);
		array_pop($ex);
		array_push($ex, 'Model');
		return implode("\\", $ex);

		// return $model_class::one($id);
		}
	}
