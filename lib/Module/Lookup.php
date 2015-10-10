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

		/*
		if (! empty($_POST)) {
			$query = $this->my_query(); // . " where " . $this->ajax_where();
			if (is_object($query)) {
				// where clause
				foreach (post('where') as $k=>$v) {
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
			echo $this->{$this->style}($results);
			die;
			}	
			*/
		}
	
	public function ajax_where($and = '')
		{
		/*
		$pairs = array();
		foreach ($_POST as $k=>$v) {
			if ($v)
			$pairs[] = db()->ent($k) . " like " . db()->esc('%' . $v . '%');
			}
		return ! empty($pairs) ? " where " . implode(" and ", $pairs) . $and :
		($and ? " where $and " : '');
		*/
		}

	/**
		Default display function.
		*/
	public function my_display()
		{
		// $query = $this->my_query();
		// $rows = is_object($query) ? $query->limit(50)->results() : \Db::results($query);

		return "<div class='lookup-wrapper'>"
		. "<div class='left'>"
			. $this->search_table()
			// echo get_class($this->index);
			. $this->index->my_left()
			. "</div>"

		// $this->my_banner();
		. "<div class='right'>"
		// $this->{$this->style}($rows);
		. $this->as_table() // $rows);
		. "</div>"
		. "<div class='rug'></div>"
		. "</div>"
		;
		}

	/*
	public function my_banner()
		{
		$parts = array();
		$parts[] = $this->my_name();

		// optional
		foreach (array('scroll', 'chat', 'sort') as $class) {
			if (class_exists(_to_class($this->index->parent->path . '/' . $class))) {
				$parts[] = "<a href='" . \Path::base_to($this->index->parent->path . '/' . $class, $this->index->parent->key_pair)
				. "'>" . _to_words($class) . "</a>";
				}
			}


		// $s .= " | <a href='" . $this->create_path() . "'>New</a></div>";
		// die($this->index->keyname);
		$new = $this->my_new_link();
		if ($new) $parts[] = $new;
			
		return div('banner', divider($parts));
		}

	public function hey()
		{
		return 'yo';
		}
		*/

	public function my_banner_tokens()
		{
		$ts = parent::my_banner_tokens();
		// $ts[] = "<a href='" . $this->create_path() . "'>New</a>";

		return $ts;
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
		
		return "<script type='text/javascript'>
		function load_up(data) {
			// data += '&' + $('#search-form').serialize();

			Mallorca.run_stack('" . stack([
				call($this, 'as_table')->html('.right')
				]) . "', $('#search-form').serialize() + data);

			/*
			$.post('" . \Path::here() . "', data, function (html) {
				$('.lookup-wrapper .right').html(html);
				});
				*/
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
			. \Module\Edit::control_group($inputs)
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
		return \Path::base_to($this->index->parent->path . '/edit', $this->index->parent->key_pair);
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
	public function as_table() // $rows)
		{
		$query = $this->my_query();

		// where
		$where = is(\Request::$data, 'where');
		if ($where) {
			$ws = [];
			foreach ($where as $k=>$v) {
				$ws[] = m($k)->where_like($v);
				}
			$query = $query->combine($ws);
			}

		// sort
		$sort = is(\Request::$data, 'sort');
		$asc = 1 - is(\Request::$data, 'asc');
		if ($sort) {
			$asc_fn = $asc ? 'asc' : 'desc';
			$query = $query->combine([
				m($sort)->$asc_fn()
				]);
			}

		// return $query->text();

		$rows = is_object($query) ? $query->limit(50)->results() : \Db::results($query);

		// $asc = 1 - post('asc');
		$s = '';
		$show = $asc ? '&uarr; ' : '&darr; ';
		$s .= $this->my_banner();
		$s .= "<table class='table'>";
		$s .= "<thead>";
		foreach ($this->my_columns() as $col) {
			$arrow = $col == $sort ? $show : '';
			$s .= "<th onClick=\"load_up('&sort=$col&asc=$asc');\">" . $arrow . _to_words($col) . "</th>";
			}
		$s .= "</thead>";
		$s .= "<tbody>";
		foreach ($rows as $row)
			{
			$s .= "<tr class='" . $this->my_row_classes($row) . "' onmouseover=\"$(this).addClass('selected');\" onmouseout=\"$(this).removeClass('selected');\"
			onClick=\"window.location='" . $this->edit_path($row['id']) . "';\">" . $this->my_row($row) . "</tr>";
			} 
		$s .= "</tbody>";
		$s .= "</table>";
		return $s;
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
		return _to_words($this->index->parent->name) . " Lookup";
		}

	public function my_table()
		{
		return str_replace("/", "_", $this->index->parent->path);
		}

	/*
	public function my_new_link()
		{
		$model = $this->model_class();

		return input_button('New')->click([
			// call(),
			call_path($this->index->parent->path . '/edit', [
				$this->index->keyname=>call($model, 'my_blank')
				])
			]);
		}
		*/

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
		Define the columns to be used from the query.
		*/
	public function my_columns() { return array(); }
	}
