<?php

class CoralLookup extends \Perfect\Lookup
	{
	public function filter()
		{
		$defs = [];
		foreach ($this->model->columns as $col) {
			if (! isset($col->lookup) || ! isset($col->lookup->hide)) {
				$defs[is_object($col) ? $col->get_name() : $col] = 1;
				}
			}
		return $defs;
		}

	/*
	public function my_query()
		{
		/
		return parent::my_query()->combine([
			m('content')->substr(1, 100)->as('content')
			]);
		// parent::my_query()->combine([m('parent_id')->blank()]);
		}
		*/

	public function nest_two($data, $a = 'tr', $b = 'td')
		{
		$s = '';
		$show = $this->filter();

		foreach ($data as $one) {
			$id = is($one, 'id');
			if ($b == 'td') {
				$s .= "<$a id='row_$id' class='data-group'>";
				// $s .= "<td>" . input_check('check_' . $id) . "</td>";
				}
			else {
				$s .= "<tr>";
				// <th></th>";
				}

			foreach ($one as $k=>$two) {
				// show
				if (! isset($show[$k])) continue;
				if ($k == 'id') continue;

				if ($b == 'th') {
					$sort = stack([
						// '.table-wrapper'=>$this->model->path_fn('lookup', 'sorted', ['sort'=>$two])
						call($this->model, 'lookup')->pipe('sorted', ['sort'=>$two])->html('.table-wrapper')
						]);
					$s .= "<$b class='data-fn' data-fn=\"$sort\">$two</$b>";
					}
				// else if ($k == 'content') {
				else {
					$df = stack([
						$this->model->path('form', ['id'=>$id])// pipe('my_display')->html('.lookup-wrapper')
						]);
					$s .= "<$b class='data-fn' data-fn=\"$df\">$two</$b>";
					}
				/*
				else if ($k == 'child_count') {
					$df = stack([
						'.table-wrapper'=>$this->model->path_fn('lookup', 'searched', ['parent_id'=>$id]),
						'.pre-table'=>div('banner', $one['content'] . " &bull; "
							. input_button('Back to All')->stack([	
								'.table-wrapper'=>$this->model->path_fn('lookup', 'searched', []),
								'.pre-table'=>''
								])
							)
						]);
					$s .= "<$b class='data-fn no-data' data-fn=\"$df\">$two</$b>";
					}
					*/
				// else $s .= "<$b>$two</$b>";
				}
			/*
			$s .= $b == 'td' ? "<$b>"
			. input_button('Caps')->click([
				call($this->model, 'caps | coral_lookup | my_rows', [], 'replaceWith')->replaceWith("#row_$id")
				])
			. input_button('Nest')->click([
				call($this->model, 'nest | coral_lookup | my_display', ['id'=>$id])->html('.lookup-wrapper')
				])->add_class('all-data')
			. input_button(' X ')->click([
				call($this->model, 'my_delete', ['id'=>$id])
				])
				// ->before('confirm_delete')
				->after('remove_row')
			. "</$b>" : '<th></th>';
				*/
			$s .= input_hidden('id', $id);
			$s .= "</$a>";
			}
		return $s;
		}
	}
