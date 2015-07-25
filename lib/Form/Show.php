<?php
namespace Form;

class Show extends \Module
	{
	public $query;

	/**
		Callbacks for formatting the columns.
		*/
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

	public function __toString()
		{
		return $this->my_display();
		}

	static public function query($query, $index = null)
		{
		$show = new \Form\Show($index);
		$show->query = $query;
		return $show->show();
		}

	public function my_display()
		{
		return "<div class='show-panel'>"
		. "<div class='banner'>" . $this->my_name() . $this->my_edit_link() . "</div>"
		. "<a class='delete-link' href='" . \Path::here(array('delete'=>1)) . "'>" . $this->my_delete_text() . "</a>"
		. $this->show()
		. "<div class='rug'></div>"
		. "</div>";
		}

	public function show()
		{
		$row = \Db::one_row($this->my_query());
		$keys = $this->label_filters($this->my_label_filters($row)); // keep first
		$filter = $this->filters($this->my_filters($row));

		$s = '';
		foreach ($filter as $k=>$v)
			{
			$label = $keys[$k];
			$s .= ''
			. "<div class='show-group'>"
				. ($label ? "<div class='label'>$label</div>" : '')
				. "<div class='value $k'>$v</div>"
				. "</div>"
			;
			}
		return $s;
		}

	public function label_filters($row)
		{
		foreach ($row as $k=>$v) {
			$row[$k] = _to_words($k);
			}

		$row['photo_path'] = '';
		$row['name'] = '';
		return $row;
		}

	/**
		Basic filters, always run.
		*/
	public function filters($row)
		{
		foreach ($row as $k=>$v) {
			$m = array();
			if (preg_match("/(^|_)id$/", $k)) unset($row[$k]);
			/*
			else if (preg_match("/_path$/", $k, $m)) {
				$row[$k] = "<img src='" . \Path::$local_path . "/uploads/" . $v . "'>";
				}
				*/
			else if ($k == 'bio') $row[$k] = markdown($v);
			}

		return $row;
		}

	/* REDEFINES */

	public function my_query()
		{
		if ($this->query) return $this->query;

		$fields = $this->my_fields();
		$select = empty($fields) ? '*' : implode(',', $fields);

		return "select $select from " . $this->index->parent->name . " where id=" . $this->index->id;
		}

	public function my_fields()
		{
		return array();
		}

	public function my_filters($row)
		{
		return $row;
		}

	public function my_label_filters($row)
		{
		return $row;
		}
	}
