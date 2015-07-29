<?php
namespace Perfect;

class Form extends \Perfect
	{
	public function my_display()
		{
		// \Request::$data
		 // die($this->model->id);
		return div('banner', 
			div('title', input_button('lookup')->stack([
				// call($this->model, 'lookup | my_display')->html('.lookup-wrapper')
				$this->model->path('lookup', ['id'=>0])
				])
				. ' &gt; '
				. 'New ' . _to_words($this->model->table)
				)
			. div('f-right', input_button('Delete')->click([
				call($this->model, 'my_delete', ['id'=>$this->model->id]),
				$this->model->path('lookup')
				])->before('confirm_delete')->add_class('delete')
				)
			)
			. $this->my_form()
			. div('save-status')
			;
		}

	public function my_form()
		{
		return $this->control_group($this->my_inputs());
		}

	public function my_search_form()
		{
		return "<script type='text/javascript'>
		var searching;

		$(document).ready(function() {
			$('.search-wrapper .input-text').keyup(function() {
				var data = $(this).closest('.data-group').find(':input').serialize();
				var data_fn = '" . stack([
					call($this->model, 'lookup | searched')
						// ->html('.table-wrapper')
					// '.table'=>$this->model->path_fn('lookup', 'searched')
					]) . "';
				searching = setTimeout(function () {
					Mallorca.run_stack(data_fn, data);
					}, 444);
				})
			.keydown(function () {
				clearTimeout(searching);
				});
			});
		</script>"
		. div('search-wrapper',
			$this->control_group($this->my_search_inputs())
			);
		}

	public function my_search_inputs()
		{
		foreach ($this->get_names() as $name) {
			if ($name == 'id') continue;
			yield input_text($name);
			}
		}

	public function my_inputs()
		{
		foreach ($this->model->columns as $col) {

			$o = is_object($col);
			$name = $o ? $col->get_name() : $col;

			$input_fn = 'input_text';

			// filters
			if ($name == 'id') continue;
			if (isset($col->data)) {
				if (isset($col->data->type)) {
					list($type, $opt) = $col->data->type;
					if ($type == 'str' && $opt > 100) {
						$input_fn = 'input_textarea';
						}
					}
				}

			$input =$input_fn($name);

			// set data
			$input->set_value(is($this->model->data, $name));
			yield $input;
			}
		yield input_hidden('id', $this->model->id);
		yield input_button('Save')->click([
			call($this->model, 'my_save'),
			$this->model->path('lookup')
			// '.save-status'=>$this->model->call('my_save')
			]);
		}

	public function control_group($inputs)
		{
		$inps = [];
		foreach ($inputs as $inp) {
			$inps[] = $this->control($inp);
			}

		return div('control-group data-group', implode('', $inps));
		}

	public function control($input)
		{
		$label = div('label', $input->label);
		$inp = div('input', $input->my_input());

		return div('control', $label . $inp);
		}
	}
