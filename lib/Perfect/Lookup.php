<?php
namespace Perfect;

class Lookup extends \Perfect
	{
	public function my_display()
		{
		return $this->my_full($this->my_query()->results());
		}

	public function search_table()
		{
		return $this->schema->form()->my_search_form();
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
			div('banner', $this->my_banner())
			. div('table-wrapper', $this->my_table($data))
			);
		}

	public function my_banner()
		{
		return _to_words($this->schema->table)
		. ' &bull; ' . input_button('New')->stack([
			'.content'=>$this->schema->path('form')
			])
		;
		}

	public function my_table($data)
		{
		return "<table class='table'>" 
		. $this->nest_two(array(array_map('_to_words', array_keys(current($data)))), 'tr', 'th')
		. $this->nest_two($data)
		. "</table>";
		}

	public function nest_two($data, $a = 'tr', $b = 'td')
		{
		$s = '';
		foreach ($data as $one) {
			$df = $b == 'td' ?
				stack(['.content'=>$this->schema->path('form', ['id'=>$one['id']])])
				: '';
			$s .= "<$a class='data-fn' data-fn=\"$df\">";
			foreach ($one as $two) {
				if ($b == 'th') {
					$sort = stack(['.table-wrapper'=>$this->schema->path_fn('lookup', 'sorted', ['sort'=>$two])]);
					$s .= "<$b class='data-fn' data-fn=\"$sort\">$two</$b>";
					}
				else $s .= "<$b>$two</$b>";
				}
			$s .= "</$a>";
			}
		return $s;
		}

	public function sorted($data = [])
		{
		$sort = strtolower(is($data, 'sort'));
		$data = $this->my_query()->combine([m($sort)->asc()])->results();
		
		// return $sort . pv($data);
		return $this->my_table($data);
		}

	public function my_query()
		{
		return select($this->schema->table, $this->get_names());
		}
	}
	
