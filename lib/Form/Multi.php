<?php
namespace Form;

class Multi extends \Form\Create
	{
	public $form;

	public function __construct($index)
		{
		$this->index = $index;
		$class = $this->my_form_create();
		$this->form = new $class($index, true);
		$this->fields = $this->my_fields();
		$this->mode = 'create';

		if (!empty($_POST)) {
			foreach ($_POST['nested'] as $id=>$data) {
				$this->process_post($data, false);
				}
			\Path::refresh($this->index->parent->key_pair);
			}

		parent::__construct($index);
		}

	public function my_query()
		{
		// return "select 1 where 1=0";
		}

	public function my_display()
		{
		$res = \Db::results($this->my_query());
		if (empty($res))
			return "<h2>Nothing for today.</h2>";

		echo "<div class='lookup-wrapper'>"
		. "<form action='" . \Path::here() . "' method='post'>" ;
		foreach ($res as $row) {
			$this->index->id = $row['id'];
			$fields = $this->my_filter($this->my_fields($row), $row);

			echo "<h2>" . $row['name'] . "</h2>";

			// set names
			foreach ($fields as $k=>$field) {
				if (! is_object($field)) continue;
				$field->set_name("nested[{$row['id']}][$field->name]");
				}
			echo self::control_group($fields)
			;
			}
		echo self::control_group(array(
			input_submit('Save')->add_class('button')
			));
		echo "</form></div>";
		}

	public function my_fields($data = array())
		{
		$this->form->index->parent->data = $data;
		return $this->form->my_fields();
		}
	}
