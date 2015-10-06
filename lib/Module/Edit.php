<?php
namespace Module;

abstract class Edit extends \Module
	{
	/** Edit or create. */
	public $mode;

	function __construct($index, $nothing = false)
		{
		if ($nothing) return true;

		// die(pv($index));

		$this->index = $index;
		$this->data = $this->my_data() ?: array();
		$this->fields = $this->my_fields();
		$this->mode = $this->index->id ? 'edit' : 'create'; // $this->index->name;
		$this->multipart = false;

		// allow array of inputs, or input groups
		if (is_object($this->fields[0]) && get_class($this->fields[0]) == "Input\\Group") {
			$fields = array();
			foreach ($this->fields as $group) {
				foreach ($group->inputs as $input) $fields[] = $input;
				}
			$this->fields = $fields;
			}

		// set values
		foreach ($this->fields as $input) {
			if (! is_object($input)) continue;
			if ($input->type == 'checklist') {
				foreach ($input->inputs as $k=>$v) {
					$input->input[$k] = $v->set_value(is($this->data, $v->name, $v->value));
					}
				}
			else $input = $input->set_value(is($this->data, $input->name, $input->value));
			if ($input->type == 'file') $this->multipart = true;
			}
		
		/*
		if (! empty($_POST))
			{
			
			$_POST = self::clean_post($_POST, $this->fields);
			$_POST = array_merge($_POST, self::file_save());

			if ($this->mode == 'create') $this->my_create($_POST);
			else $this->my_update($_POST);

			\Path::refresh($this->index->parent->key_pair);
			}
		else if (get('delete'))
			{
			$this->my_delete();
			\Path::base_redir($this->index->parent->path . '/lookup');
			}
			*/
		}

	/**
		Clean up the post array.
		\param	array	$post	Data to clean.
		\return Array.
		*/
	static public function clean_post($post, $fields = array())
		{
		$types = array();
		foreach ($fields as $inp) {
			if (! is_object($inp)) continue;
			$types[$inp->name] = $inp->type;
			}

		foreach ($post as $k=>$v) {
			$type = is($types, $k);
			// date triple
			if ($type == 'datetriple') {
				$post[$k] = date_to("Y-m-d", is($v, 'year') . '-' . is($v, 'month') . '-' . is($v, 'day'));
				}
			else if ($type == 'time') {
				if ($v == '') $post[$k] = '';
				else $post[$k] = substr('0' . floor($v / 60), -2) . ':' . substr('0' . ($v % 60), -2) . ':00';
				// $post[$k] = date_to("H:i", is($v, 'hour') . ':' . is($v, 'minute') . ' ' . is($v, 'period'));
				}
			}
		return $post;
		}

	/**
		\param	array	$fields	Array of Form\Input objects.
		*/
	static public function control_group($fields)
		{
		return action_group($fields)->my_display();
		/*
		return "<div class='control-group data-group'>"
		. implode('', array_map('self::control', $fields))
		. "</div>";
		*/
		}

	static public function control($input)
		{
		if (! is_object($input)) return $input;
		if ($input->type == 'hidden') return $input->my_display();

		return "<div class='control $input->outer_classes' id='{$input->name}_control'>"
			. "<div class='label $input->label_classes'>"
				. (! in_array($input->type, array('submit', 'button', 'stripe')) ?
					($input->mand ? "<span class='mand'>* </span>" : '')
					. $input->label
					: '')
				. "</div>"
			. "<div class='input'>" . $input->my_display() . "</div>"
			. "</div>"
			;
		}

	public function save_button()
		{
		$model = _to_class($this->index->parent->path . '/model');
		return input_button('Save')->click([
			call($model, 'my_save'),
			call_path($this->my_return_path(), $this->my_return_params())
			]);
		}

	public function my_display()
		{
		$model = _to_class($this->index->parent->path . '/model');

		return ''
		. "<script>
		$(document).ready(function() {
			set_calendars();
			});
		</script>"
		. "<div class='lookup-wrapper'>"
		. "<div class='banner'>"
		. "<div class='title'><a class='banner' href='" . \Path::base_to($this->my_return_path(), $this->my_return_params()) . "'>"
		. $this->my_return_text() . "</a> &gt; " . ucfirst($this->mode) . "</div>"
		. ($this->mode == 'edit' ?
			// "<div class='delete'><a href='" . \Path::here(array('delete'=>1)) . "'>Delete</a></div>"
			input_button('Delete')->add_class('delete')->click([
				call($model, 'my_delete', ['id'=>$this->index->id]),
				call_path($this->my_return_path(), $this->my_return_params())
				])->before('confirm_delete')
			: '')
			. "<div class='rug'></div>"
			. "</div>"
		. "<div class='" . $this->my_class() . "'>"
		. "<form id='form_create' action='" . \Path::here() . "' method='post' "
		. ($this->multipart ? " enctype='multipart/form-data' " : '')
		. ">"
		. self::control_group($this->fields)	
		. "</form>"
		. "</div>"
		. "</div>"
		;
		}

	static public function upload_dir($full = false)
		{
		$dir = '../protected/uploads';
		return $full ? file_dir($dir) : \Path::$local_path . $dir;
		}

	static public function file_save()
		{
		$out = array();
		foreach ($_FILES as $name=>$file)
			{
			/*
			if ($input->type != 'file'
			|| ! isset($_FILES[$input->name])
			|| strlen($_FILES[$input->name]['name']) <= 0) continue;
			*/

			if (strlen($file['name']) <= 0) continue;

			// $file = $_FILES[$input->name];
			
			if ($file['error'] > 0)
				$alert = 'Image upload error ' . $file['error'] . '...<br>';
			else if ($file['size'] > 5000000) 
				$alert = 'Image must be less than 5M...<br>';
			else if (! preg_match("/jpeg|png/", $file['type'])) // != 'image/jpeg')
				$alert = 'Image must be a jpeg or png...<br>';
			else 
				{
				$hash = hash_file('md5', $file['tmp_name']);
				$photo = preg_replace('/(.*?)(\.\w+)$/', $hash . "$2", $file['name']); // . '.jpg';
				
				$dir = self::upload_dir(true);
				if (! is_dir($dir)) mkdir($dir, 0777, true);
				
				$resize = $dir . '/' . $file['name'];
				move_uploaded_file($file['tmp_name'], $resize);
				\Image\Thumbnail::convert_one($resize, "$dir/$photo");
				unlink($resize);
				$out[$name] = $photo;
				}
			}
		return $out;
		}

	function my_data()
		{
		return $this->index->id ?
		\Db::one_row("select * from {$this->index->parent->name} where id=" . $this->index->id)
		: array();
		}

	function my_update($d)
		{
		\Db::match_update($this->index->parent->name, $d, " where id=" . $this->index->id);
		}

	function my_create($d)
		{
		\Db::match_insert($this->index->parent->name, $d);
		\Path::base_redir($this->index->parent->path . '/lookup', $this->index->parent->key_pair);
		}

	function my_delete()
		{
		return db()->query("delete from {$this->index->parent->name} where id=" . $this->index->id);
		}

	function my_return_text()
		{
		return preg_replace("/y$/", "ie", _to_words($this->index->parent->name)) . "s";
		}

	function my_return_path()
		{
		return $this->index->parent->path . '/lookup';
		}

	function my_return_params()
		{
		return array();
		}

	function my_class()
		{
		return 'form';
		}
	}
