<?php
namespace Module;

/**
	Module for creating table lookups easily.
	*/
class Lookup extends \Module
	{
	/** Parent MyIndex object. */
	public $index;

	/** Display style. */
	public $style = 'as_table';

	/**
		*/
	public function __construct($index)
		{
		$this->index = $index;
		$this->cols = $this->my_columns() ?: array();
		$this->search = $this->my_search() ?: array();

		if (! empty($_POST)) {
			$query = $this->my_query(); // . " where " . $this->ajax_where();
			if (is_object($query)) {
				// where clause
				foreach (post('where', array()) as $k=>$v) {
					if ($v) $query = $query->combine(array(m($k)->where_like($v)));
					}
				// sort
				$sort = post('sort');
				if ($sort) {
					$asc = post('asc') ? 'asc' : 'desc';
					$query = $query->combine(array(m($sort)->$asc()));
					}

				// print_var($query);
				$results = $query->results();
				}
			else {
				$results = \Db::results($query);
				}
			// echo $this->{$this->style}($results); die;
			}	
		}
	
	public function ajax_where($and = '')
		{
		$pairs = array();
		foreach ($_POST as $k=>$v) {
			if ($v)
			$pairs[] = db()->ent($k) . " like " . db()->esc('%' . $v . '%');
			}
		return ! empty($pairs) ? " where " . implode(" and ", $pairs) . $and :
		($and ? " where $and " : '');
		}

	/**
		Default display function.
		*/
	public function my_display()
		{
		$query = $this->my_query();
		$rows = is_object($query) ? $query->limit(50)->results() : \Db::results($query);

		ob_start();
		echo "<div class='lookup-wrapper'>";
		echo "<div class='left'>";
		$this->search_table();
		// echo $this->index->my_left();
		echo "</div>";

		echo "<div class='right'>";
		echo "<div class='banner'>" . $this->my_name();

		// optional
		foreach (array('lookup', 'scroll', 'graph', 'complete', 'chat', 'missing') as $class) {
			$path = $this->index->parent->path . '/' . $class;
			if (\Path::$q == $path) echo " | " . _to_words($class);
			else if (class_exists(_to_class($path))) {
				echo " | <a href='" . \Path::base_to($path, $this->index->parent->key_pair)
				. "'>" . _to_words($class) . "</a>";
				}
			}

		echo " | <a href='" . $this->create_path() . "'>New</a></div>";
		$this->{$this->style}($rows);
		echo "</div>";
		echo "<div class='rug'></div>";
		echo "</div>";
		return ob_get_clean();
		}
	
	/**
		\return The search table form.
		*/
	public function search_table()
		{
		$inputs = array();
		foreach ($this->search as $k=>$v) {
			$inputs[] = input_text("where[$v]")->label(_to_words($v));
			}
		
		echo "<script type='text/javascript'>
		function load_up(data) {
			data += '&' + $('#search-form').serialize();
			$.post('" . \Path::base_to($this->index->path) . "', data, function (html) {
				$('.lookup-wrapper .right').html(html);
				});
			}

		$(document).ready(function() {
			$('.search-table input[type=\"text\"]').keyup(function (e) {
				load_up('');
				});
			});
		</script>"
		. "<form id='search-form'>"
		. "<div class='search-table'>"
		// . "<div class='expander' onClick=\"$('.search_hide').toggle();\">...search</div>"
		. "<div class='expander' onClick=\"$('.search_hide').toggle();\">..search..</div>"
		. "<div class='search_hide'>" 
			// . $lookup->search_table(0) .
			. "<div class='hd'>Search</div>"
			. \Form\Create::control_group($inputs)
			. $this->index->my_left()
			. "</div>"
		. "</div>"
		. "</form>"
		;
		}

	/**
		Path to the new row create page.
		*/
	public function create_path()
		{
		return \Path::base_to($this->index->parent->path . '/create', $this->index->parent->key_pair);
		}

	/**
		Path to the edit page.
		*/
	public function edit_path($id)
		{
		$params = array_merge($this->index->parent->key_pair, array($this->index->keyname=>$id));
		return \Path::base_to($this->index->parent->path . '/edit', $params);
		}

	/**
		Table style.
		*/
	public function as_table($rows)
		{
		$asc = 1 - post('asc');
		echo "<table class='table'>";
		echo "<thead>";
		foreach ($this->my_columns() as $col) {
			echo "<th onClick=\"load_up('sort=$col&asc=$asc');\">" . _to_words($col) . "</th>";
			}
		echo "</thead>";
		echo "<tbody>";
		$last = '';
		$my_split = $this->my_split();
		foreach ($rows as $row)
			{
			$split = is($row, $my_split);
			if ($my_split) $row[$my_split] = '';
			if ($split && $split != $last) {
				echo "<tr><td colspan='" . count($row) . "'><b>$split</b></td></tr>";
				}
			echo "<tr class='" . $this->my_row_classes($row) . "' onmouseover=\"$(this).addClass('selected');\" onmouseout=\"$(this).removeClass('selected');\"
			onClick=\"window.location='" . $this->edit_path($row['id']) . "';\">" . $this->my_row($row) . "</tr>";
			$last = $split;
			} 
		echo "</tbody>";
		echo "</table>";
		}

	/**
		Create array of table cells.
		*/
	protected function cells($cells = array())
		{
		return implode('', array_map(function ($x) { return "<td>$x</td>"; }, $cells));
		}

	/* REDEFINES */

	public function my_name()
		{
		return _to_words($this->index->parent->name);
		}

	public function my_table()
		{
		return str_replace("/", "_", $this->index->parent->path);
		}

	public function my_query()
		{
		return "select * from " . $this->my_table() . $this->ajax_where();
		}

	/**
		Define a callback for the row.
		\param	array	$row	Associative array of the row data.
		*/
	public function my_row($row)
		{
		// return "Define your row function.";
		return $this->cells(array_map(function ($x) use ($row) {
			return $row[$x];
			}, $this->my_columns()));
		}
	
	/**
		Callback for row classes to add.
		\param	array	$row
		*/
	public function my_row_classes($row)
		{
		return;
		}

	/**
		Searchable columns.
		*/
	public function my_search() { return $this->my_columns(); }

	/**
		Column to split rows by.
		*/
	public function my_split() { return ''; }

	/**
		Define the columns to be used from the query.
		*/
	public function my_columns() { return array(); }
	}
