<?php
namespace Input;

class File extends \Input
	{
	/** The table the field refers to. */
	public $table;

	/** The id for the table in reference. */
	public $table_id;

	public function my_construct($table = '', $table_id = 0)
		{
		$this->table = $table;
		$this->table_id = $table_id;
		}
		
	public function my_input()
		{
		return 
		"<script>
		var drag_{$this->name} = new image_uploader(\"" . \Path::base_to('upload') . "\", \"$this->table\", \"$this->name\");
		drag_{$this->name}.init();
		</script>"
		. "<div class='drag-image photo' id='drag_image_$this->name'>Drag Here</div>"
		// . "<div class='photo-preview'></div>"
		. "<input type='file' id='$this->name' name='{$this->name}_file'>"
		. "<input type='hidden' name='{$this->name}' value='$this->value'>"
		. "<input type='hidden' value='$this->table_id' name='{$this->name}_table_id'>"
		// . ($this->value ? "<br /><img src='" . \Form\Create::upload_dir() . "/$this->value'>" : '');
		. ($this->value ? "<br /><img class='current-image' src='" . \Path::$local_path . "image.php?h=$this->value'>" : '');
		;
		}
	}
