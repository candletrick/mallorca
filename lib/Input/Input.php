<?php
/**
	Input class.
	This class is for all form inputs.
	Each file in the directory extends this class.
	*/
abstract class Input
	{
	/** Name. */
	public $name;

	/** Label. */
	public $label;

	/** Value. */
	public $value;

	/** Text hint. */
	public $hint;

	/** Mandatory. */
	public $mand = true;

	/** String of class names. */
	public $classes = 'mand';

	/** Label classes. */
	public $label_classes = '';

	/** Outer classes for wrapping divs. */
	public $outer_classes = '';

	/** Input attributes. */
	public $attrs = '';

	/** HTML to append. */
	public $addhtml = '';

	/** Input type. */
	public $type = '';

	public function __construct()
		{
		$args = func_get_args();

		$this->name = $name = array_shift($args);
		$this->label = ucwords(str_replace('_', ' ', $name));

		$ex = explode("\\", get_class($this));
		$this->type = strtolower(array_pop($ex));

		// call class specific constructor with remaining args
		call_user_func_array(array($this, "my_construct"), $args);
		}

	public function __toString()
		{
		return $this->my_display();
		}

	/**
		Set the value of the input.
		\param	string $value
		*/
	public function set_value($value)
		{
		$this->value = $value;
		return $this;
		}

	/**
		Set an input hint / placeholder.
		\param string $hint
		*/
	public function set_hint($hint)
		{
		$this->hint = $hint;
		return $this;
		}

	/**
		\return the HTML escaped version of the value of the input.
		*/
	public function esc_value()
		{
		return htmlspecialchars($this->value, ENT_QUOTES);
		}

	/**
		Add a class to the control wrapper.
		\param	string $class
		*/
	public function outer_class($class)
		{
		$this->outer_classes .= " $class";
		return $this;
		}

	/**
		Make the label show to the left.
		*/
	public function left()
		{
		$this->outer_classes .= " left";
		return $this;
		}

	/**
		Hide the input.
		*/
	public function hide()
		{
		$this->outer_classes .= ' hide';
		return $this;
		}

	/**
		Respond to click_toggle.
		*/
	public function show_if($name, $expected, $value = '')
		{
		$this->outer_classes .= $name . '_' . $expected . ($expected == $value ? '' : ' hide');
		return $this;
		}

	/**
		Explicitly set the input type.
		\param	string	$type
		*/
	public function type($type)
		{
		$this->type = $type;
		return $this;
		}

	/**
		\param	string	$label	Label text.
		*/
	public function label($label)
		{
		$this->label = $label;
		return $this;
		}

	/**
		\param string	$attrs	Fully formed list of attributes for example:
			->attrs(" style='' ")
		*/
	public function attrs($attrs)
		{
		$this->attrs .= $attrs;
		return $this;
		}

	/**
		\param	string $class Class or classes to remove.
		*/
	public function remove_class($class = '')
		{
		$this->classes = $class ? str_replace($class, '', $this->classes) : '';
		return $this;
		}

	/**
		Class to add to the input wrapper / other,
		depends on display function.
		\param string $class.
		*/
	public function add_class($class)
		{
		$this->classes .= ' ' . $class;
		return $this;
		}

	/**
		\param	string	$class Class to add to the label.
		*/
	public function add_label_class($class)
		{
		$this->label_classes .= ' ' . $class;
		return $this;
		}

	/**
		Show other inputs based on value.
		Uses javascript.
		\param	string	$value	When this inputs value is $value.
		\param	array	$names	Show these named fields.	
		\param	boolean $initial	Apply the rule on the initial page load.
		*/
	public function show_when($value, $names = array(), $initial = true)
		{
		$init = $initial ? 'true' : 'false';
		$fn = "show_when('$this->name', '$value', " . json_encode($names) . ", $init)";
		$this->addhtml .= "<script>
		$(document).ready(function() {"
			. ($initial ? $fn : '')
			. "
			$('#$this->name, input[name=$this->name]').click(function() {
				$fn;
				});
			});
		</script>";
		return $this;
		}

	/**
		Add a note to the input.
		\param string $note
		*/
	public function note($note)
		{
		$this->addhtml .= " <span class='note'>$note</span>";
		return $this;
		}

	/**
		Extra HTML to be displayed after the input.
		\param string $html
		*/
	public function addhtml($html = '')
		{
		$this->addhtml .= $html;
		return $this;
		}

	/**
		Make mandatory.
		*/
	public function mand()
		{
		$this->mand = true;
		return $this;
		}

	/**
		Make optional.
		*/
	public function opt()
		{
		$this->mand = false;
		$this->remove_class('mand');
		return $this;
		}

	/* OPTIONAL REDEFINES. */

	public function my_construct()
		{
		// add as many params as you like
		// follow examples
		// NOTE: since no parameters are specified in the definiton here,
		// all parameters must have defaults.
		}

	/**
		The final display function.
		\sa my_input()
		*/
	public function my_display()
		{
		return $this->my_input() . $this->addhtml;
		}

	/**
		Refocus / highlight the input if there was an error.
		\param string	$msg	Message.
		*/
	public function refocus($msg)
		{
		$this->label($msg)
			->add_label_class('refocus')
			->add_class('refocus')
			;
		return $this;
		}

	/**
		Check conditions that should be met by input type.
		\param	string	$value	Submitted value.
		*/
	public function check($value)
		{
		if (! $value) {
			$this->refocus(_to_words($this->name) . " is required.");
			return false;
			}
		return true;
		}
	}
