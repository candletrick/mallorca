<?php
namespace Input;

/**
	Text input.
	*/
class Text extends \Input
	{
	/** Length. */
	public $len;

	/** Locked, display only. */
	public $lock = false;

	public function my_construct($len = 40, $value = '')
		{
		$this->len = $len;
		$this->value = $value;
		}

	public function my_input()
		{
		if ($this->lock) return $this->value;

		$mask = strpos($this->name, "phone") !== false ? 'phone'
		: '';

		return "<input class='input-text $this->classes' type='text' name='$this->name' id='$this->name'"
		// . " size='" . ($this->len + 2) . "' "
		. " maxlength='$this->len' "
		. ($this->mand ? " required='' " : '')
		. " placeholder='$this->hint' value='" . $this->esc_value() . "'"
		. "	onkeypress=\"return finalmask(event, this, '$mask', 0);\">";
		}

	/**
		Popup calendar beside text inputs.
		*/
	function calendar($field)
		{
		return "<div id='" . $field . "_calendar' class='calendar-wrapper' style='display:none;'></div>";
		}

	/**
		Lock to display only.
		*/
	public function lock()
		{
		$this->lock = true;
		return $this;
		}

	/**
		Add an autocomplete popup onto a text input.
		For this to work properly, also add a hidden input.
		For example, if you add input_text('doctor')->autocomplete(),
		also add input_hiddent('doctor_id')
		\param	array	$array	Array of key value pairs.
		\param	string	$cback	Javascript callback function for the row elements params (idname, id, val)
		*/
	public function autocomplete($array, $cback = 'ac_list') 
		{
		$s = "<script type='text/javascript'>$(document).ready(function() {
			var auto_{$this->name} = new autocomplete('$this->name', " . json_encode($array) .  ", '$cback');
			auto_{$this->name}.create();
			});</script>" 
		. "<span class='auto-complete'><div class='auto-fill'></div></span>";
		$this->addhtml = $s;
		return $this;
		}
	}
