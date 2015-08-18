<?php
namespace Email;

class Create extends \Form\Create
	{
	public function my_fields()
		{
		return array(
			"<span class='note'>Name should be lowercase with underscores to separate words, example: new_user</span>",
			input_text('name'),
			input_select('event', array_combine(\Notify::$events, array_map('_to_words', \Notify::$events))),
			"<span class='note'>Written description of the event under which the email should be sent.</span>",
			input_textarea('description'),
			input_checklist('recipients', array(
				input_check('send_to_family'),
				input_check('send_to_sitter'),
				input_check('send_to_all_available_sitters'),
				)),
			input_text('subject', 140),
			input_radio('content_type', array('HTML', 'Markdown')),
			input_textarea('content')->add_class('big'),
			input_submit('Save'),
			"<h3>Using Variables:</h3>",
			"<p>Relevant variables to the topic are inserted with this format: {{table_name.field_name}}<br>
			Examples: {{sitter.name}}, {{booking.book_on}}</p>",
			"<h3>Sitter Fields</h3>",
			implode(", ", array_keys(\Notify::get_sitter(1))),
			"<h3>Family Fields</h3>",
			implode(", ", array_keys(\Notify::get_family(1))),
			"<h3>Booking Fields</h3>",
			implode(", ", array_keys(\Notify::get_booking(1))),
			input_submit('preview')->label('Preview to fewkeep@gmail.com')
			);
		}

	public function my_update($d)
		{
		parent::my_update($d);

		if (is($d, 'preview')) {
			$data = array(
				'sitter'=>\Notify::get_sitter(1),
				'family'=>\Notify::get_family(1),
				'booking'=>\Notify::get_booking(1),
				);

			$parsed = \Notify::parse_data($d['content'], $data);

			die("<center><textarea cols='90' rows='30'>" . $parsed . "</textarea></center>");
			// \Notify::call_by_id($this->index->id);
			}
		}
	}
