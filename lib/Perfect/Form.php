<?php
namespace Perfect;

class Form extends \Perfect
	{
	public function my_display()
		{
		return div('banner', 
			div('title', input_button('lookup')->stack(['.content'=>$this->schema->path('lookup')])
				. ' &gt; '
				. 'New ' . _to_words($this->schema->table)
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
					'.table'=>$this->schema->path_fn('lookup', 'searched')
					]) . "';
				searching = setTimeout(function () {
					run_stack(data_fn, data);
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
		foreach ($this->schema->columns as $col) {

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
			$input->set_value(is($this->schema->data, $name));
			yield $input;
			}
		yield input_hidden('id', $this->schema->id);
		yield input_button('Save')->stack([
			'.save-status'=>$this->schema->call('my_save')
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
