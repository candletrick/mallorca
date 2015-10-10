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
		*/

	public function my_banner_tokens()
		{
		$ts = [
			(get('q') == $this->my_return_path() ? $this->my_return_text()
			: \Path::link_to($this->my_return_text(), $this->my_return_path(), $this->my_return_params(), 'banner'))
			// ucfirst($this->mode)
			];

		return $ts;
		}

	public function my_banner()
		{
		$model = _to_class($this->index->parent->path . '/model');

		return div('banner',
			div('title', divider($this->my_banner_tokens())),
		/*
		. ($this->mode == 'edit' ?
			// "<div class='delete'><a href='" . \Path::here(array('delete'=>1)) . "'>Delete</a></div>"
			input_button('Delete')->add_class('delete')->click([
				call($model, 'my_delete', ['id'=>$this->index->id]),
				call_path($this->my_return_path(), $this->my_return_params())
				])->before('confirm_delete')
			: '')
			);
			*/
			rug()
			);
		}

	function my_return_text()
		{
		return preg_replace("/y$/", "ie", _to_words($this->index->parent->name)) . "s";
		}

	function my_return_path()
		{
		return $this->index->parent->path . '/lookup';
		}

	function my_return_params()
		{
		return array();
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
