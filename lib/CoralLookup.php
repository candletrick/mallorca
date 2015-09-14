<?php

/**
	Rough version of a class allowing more control over rows.
	*/
class CoralLookup extends \Perfect\Lookup
	{
	/**
		Strip ->lookup properties from on() objects.
		*/
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

	/**
		Create, for instance, table rows, or other double nested tag structures.
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
				// if ($k == 'id') continue;

				if ($b == 'th') {
					$sort = stack([
						call($this->model, 'lookup')->pipe('sorted', ['sort'=>$two])->html('.table-wrapper')
						]);
					$s .= "<$b class='data-fn' data-fn=\"$sort\">$two</$b>";
					}
				else {
					$df = stack([
						$this->model->path('form', [$this->model->keyname=>$id])// pipe('my_display')->html('.lookup-wrapper')
						]);
					$s .= "<$b class='data-fn' data-fn=\"$df\">$two</$b>";
					}
				}
			$s .= input_hidden('id', $id);
			$s .= "</$a>";
			}
		return $s;
		}
	}
