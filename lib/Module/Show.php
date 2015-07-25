<?php
namespace Module;

/**
	Class for showing a row of data.
	*/
class Show extends \Module
	{
	public $column_fns = array(
		'photo_path'=>'upload_tag'
		);

	public function __construct($index)
		{
		$this->index = $this->parent = $index;

		if (get('delete')) {
			$this->my_delete();
			}
		}

	/**
		Allow assignment of callbacks.
		*/
	public function __call($name, $args)
		{
		if (isset($this->$name) && is_callable($this->$name)) {
			return call_user_func_array($this->$name, $args);
			}
		}

	public function my_display()
		{
		return "<div class='show-panel'>"
		. "<div class='banner'>" . _to_words($this->my_name()) . $this->my_edit_link() . "</div>"
		. "<a onClick=\"return confirm('Are you sure that you want to " . $this->my_delete_text() . "?');\" class='delete-link' href='" . \Path::here(array('delete'=>1)) . "'>" . $this->my_delete_text() . "</a>"
		. $this->my_rows()
		. rug()
		. "</div>";
		}

	public function my_rows()
		{
		$s = '';
		$row = \Db::one_row($this->my_query());
		$labels = $this->my_labels($row);
		$values = $this->my_values($row);

		foreach ($row as $k=>$v)
			{
			$label = is($labels, $k, _to_words($k));
			$value = is($values, $k, $v);
			// $value = isset($this->column_fns[$k]) ? $this->column_fns[$k]($v) : $v;

			// filters
			if (preg_match("/_id$/", $k)) continue;

			$s .= ''
			. "<div class='show-group'>"
			. ($label ? "<div class='label'>$label</div>" : '')
			. "<div class='value $k'>$value</div>"
			. "</div>";
			}
		return $s;
		}

	public function my_labels($data)
		{
		return array();
		}

	public function my_name()
		{
		return $this->index->parent->name;
		}

	public function my_delete_text()
		{
		return 'Delete';
		}

	public function my_edit_link()
		{
		return " &gt; " . \Path::link_to('Edit', $this->index->parent->path . '/edit', $this->index->key_pair);
		}
	}

