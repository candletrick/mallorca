<?php
namespace Perfect;

class Lookup extends \Perfect
	{
	public $wrapper = [
		'searched'=>'.table-wrapper',
		];

	public function my_display()
		{
		return $this->my_full($this->my_query()->results());
		}

	public function search_table()
		{
		return $this->model->form()->my_search_form();
		}

	public function my_full($data)
		{
		return 
		div('left', 
			div('search-table',
				div('hd', 'Search')
				. $this->search_table()
				)
			)
		. div('right',
			$this->model->my_quick_add()
			. div('banner', $this->my_banner())
			. div('pre-table')
			. div('table-wrapper', 
				$this->my_table($data)
				)
			);
		}

	public function my_banner()
		{
		return _to_words($this->model->table)
		. ' &bull; ' . input_button('New')->click([
			$this->model->path('form') // ->html('.lookup-wrapper')
			]);
		}

	public function my_table($data)
		{
		/*
		$keys = empty($data) ? $this->get_names()
		: array_keys(current($data));
		*/
		$keys = $this->filter();
		$th = [];
		foreach ($keys as $k=>$v) {
			$th[$k] = _to_words($k);
			}

		return "<table class='table'>" 
		. "<thead>" . $this->nest_two(array($th), 'tr', 'th') . "</thead>"
		. "<tbody>" . $this->nest_two($data) . "</tbody>"
		. "</table>";
		}

	public function my_rows()
		{
		$query = $this->my_query();
		if ($this->model->id) $query = $query->combine([m('id')->where($this->model->id)]);

		return $this->nest_two($query->results());
		}

	public function nest_two($data, $a = 'tr', $b = 'td')
		{
		$s = '';
		foreach ($data as $one) {
			$s .= "<$a>";
			foreach ($one as $two) {
				$s .= "<$b>$two</$b>";
				}
			$s .= "</$a>";
			}
		return $s;
		}

	public function searched()
		{
		$new = [];
		foreach (\Request::$data as $k=>$v) {
			if ($v) $new[] = m($k)->where_like($v);
			}

		// $q = $this->my_query()->combine($new);
		// die(pv($q));
		$data = $this->my_query()->combine($new)->results();
		
		return $this->my_table($data);
		}

	public function sorted($sort = '')
		{
		$sort = strtolower($sort);
		$data = $this->my_query()->combine([m($sort)->asc()])->results();
		
		return $this->my_table($data);
		}

	public function my_query()
		{
		return select($this->model->table, $this->get_names());
		}
	}
	
