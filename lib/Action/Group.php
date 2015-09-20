<?php
namespace Action;

/**
	Synonymous for input_group, but a new version.

	Defines a group of inputs / form.
	*/
class Group
	{
	/** Name. */
	public $name;
	
	/** Display label. */
	public $label = '';

	/** Array of inputs. */
	public $inputs = array();

	/** Data to fill the inputs with. */
	public $data = array();

	/** Allow passing in a step by step group. */
	public $allow = true;
	
	/** Make uneditable after completion. */
	public $lock = false;

	/** Use as a flag to takeover display. */
	public $takeover = false;

	/* Check function. */
	public $check_fn;

	/**
		*/
	public function __construct($name, $inputs)
		{
		$this->name = $name;
		$this->inputs = $inputs;
		}

	/**
		Set properties dynamically.
		*/
	public function __call($name, $args = array())
		{
		// dynamic function
		if (isset($this->$name) && is_callable($this->$name)) {
			return call_user_func_array($this->$name, $args);
			}
		// set property
		else {
			$this->$name = $args[0];
			return $this;
			}
		}

	/**
		*/
	public function __toString()
		{
		return $this->my_display();
		}

	/**
		Make all fields mandatory.
		*/
	public function mand_all()
		{
		foreach ($this->inputs as $input) $input->mand();
		return $this;
		}

	/**
		Make all fields optional.
		*/
	public function opt_all()
		{
		foreach ($this->inputs as $input) $input->opt();
		return $this;
		}

	/**
		Set the data for the group.
		\return $this
		*/
	public function data($data)
		{
		if (! empty($data)) {
			$this->data = $data;
			foreach ($this->inputs as $input)
				{
				if (! is_object($input)) continue;
				//checklist
				if ($input->type == 'checklist') {
					foreach ($input->inputs as $check) {
						if (array_key_exists($check->name, $data)) $check->set_value($data[$check->name]);
						}
					}

				else if (array_key_exists($input->name, $data)) $input->set_value($data[$input->name]);
				}
			}

		// check mands after setting data
		// if (! $this->mandatory($data)) $this->allow = false;

		return $this;
		}

	/**
		Run a check for all inputs to see that they meet mandatory conditions.
		Set $this->allow at the same time.
		\param	array	$data	Data to check.
		\return boolean
		*/
	public function mandatory($data)
		{
		//|| ! $this->check_fns[$input->name](is($data, $input->name), $input))
		$ok = true;
		foreach ($this->inputs as $input)
			{
			if (! is_object($input)) continue;
			if ($input->mand
				&& ! in_array($input->type, array('button', 'submit', 'hidden'))
				// keep this first (custom) check
				// && (! isset($this->check_fns[$input->name]) || ! $this->check_fns[$input->name](is($data, $input->name), $input))
				// this second
				&& ! $input->check(is($data, $input->name))
				) {
				$ok = false;
				$this->allow = false;
				}
			}

		// run custom check
		if (isset($this->check_fn)) $this->check_fn($data, $this);

		// return $this->allow;
		return $ok;
		}

	/**
		Allow to pass in a step by step if.
		\param	boolean $bool Main condition.
		\param	mixed	$data	Optional row data to apply mandatory() to.
		*/
	public function allow($bool, $data = array())
		{
		if (! empty($data)) {
			$this->allow = $bool && $this->mandatory($data);
			}
		else $this->allow = $bool;

		return $this;
		}
	
	/**
		Set a custom check function for saving.
		*/
	public function check($fn)
		{
		$this->check_fn = $fn;
		return $this;
		}
		
	/**
		"refocus" an invalid input by re-rendering it as required.
		\param	string	$name	Input name.
		\param	string	$msg	Message.
		*/
	public function refocus($name, $msg)
		{
		foreach ($this->inputs as $inp) {
			if ($name == $inp->name) {
				$inp->refocus($msg);
				$this->allow = false;
				}
			}
		}

	/**
		Alias for my_display
		*/
	public function display()
		{
		return $this->my_display();
		}

	/**
		Render the input group.
		*/
	public function my_display($class = '')
		{
		$cs = array();
		foreach ($this->inputs as $input) {
			if (! is_object($input)) $cs[] = div('control', div('label'), div('input', $input));
			else if ($input->type == 'hidden') $cs[] = $input->my_display();
			else {
				$cs[] = div('control ' . $input->type,
					div('label ' . $input->label_classes, $input->label),
					div('input', $input->my_display())
					);
				}
			}
		return div('control-group data-group ' . $class, implode('', $cs));
		}
	}
