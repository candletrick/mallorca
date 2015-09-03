<?php
namespace Action;

/**
	Synonymous for input_group, but a new version.
	*/
class Group
	{
	public $name;
	public $label = '';
	public $inputs = array();
	public $data = array();

	/** Allow passing in a step by step group. */
	public $allow = true;
	
	public $skip = false;

	/** Make uneditable after completion. */
	public $lock = false;

	/** Use as a flag to takeover display. */
	public $takeover = false;

	/* Check function. */
	public $check_fn;

	public function __construct($name, $inputs)
		{
		$this->name = $name;
		$this->inputs = $inputs;
		}

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

	public function mand_all()
		{
		foreach ($this->inputs as $input) $input->mand();
		return $this;
		}

	public function opt_all()
		{
		foreach ($this->inputs as $input) $input->opt();
		return $this;
		}

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
	
	public function check($fn)
		{
		$this->check_fn = $fn;
		return $this;
		}
		
	public function refocus($name, $msg)
		{
		foreach ($this->inputs as $inp) {
			if ($name == $inp->name) {
				$inp->refocus($msg);
				$this->allow = false;
				}
			}
		}

	public function display()
		{
		return $this->my_display();
		}

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

	/*
	public function label($label)
		{
		$this->label = $label;
		return $this;
		}
	
	public function skip($bool)
		{
		$this->skip = $bool;
		return $this;
		}

		*/
	}
