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
		*/
	public function my_banner()
		{
		$parts = array();
		$parts[] = $this->my_name();

		// optional
		foreach (array('lookup', 'scroll', 'chat', 'sort') as $class) {
			if ($this->index->token == $class) continue;
			if (class_exists(_to_class($this->index->parent->path . '/' . $class))) {
				$parts[] = "<a href='" . \Path::base_to($this->index->parent->path . '/' . $class, $this->index->parent->key_pair)
				. "'>" . _to_words($class) . "</a>";
				}
			}


		// $s .= " | <a href='" . $this->create_path() . "'>New</a></div>";
		// die($this->index->keyname);
		// $new = $this->my_new_link();
		// if ($new) $parts[] = $new;
			
		return div('banner', divider($parts));
		}

	/**
		*/
	public function my_name()
		{
		return _to_words($this->index->parent->name) . ' ' . ucwords($this->index->token);
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
