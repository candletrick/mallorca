<?php
namespace Module;

/**
	A class for one page, multi section forms.

	Use my_fields() to return an array of InputGroup objects for each section of the form.
	For saving each section of the form, create functions with the same names
	as the InputGroup/s, or create a custom __call handler with the same premise.
	*/
class StepByStep extends RefreshForm
	{
	/** Input groups. */
	public $groups = array();

	/** The current input group. */
	public $current_group;

	/**
		*/
	public function __construct($index)
		{
		// mandatory
		$mand_ok = false;

		$groups = array();
		foreach ($this->my_fields() as $group) $groups[$group->name] = $group;

		// saving
		$save = post('input_group_save');
		if ($save) {


			// clean post
			$_POST = \Form\Create::clean_post($_POST, $groups[$save]->inputs);

			// add who when
			$_POST = array_merge(who_when(), $_POST);

			// check for files
			$files = ! empty($_FILES);
			if ($files) $_POST = array_merge($_POST, \Form\Create::file_save());

			// check for missing
			$mand_ok = $groups[$save]->mandatory($_POST);

			// call save function
			$this->$save($_POST);

			/*
			if ($mand_ok) {
				}
			else {
				// give data for reload
				$groups[$save]->data($_POST);
				}
				*/

			// since ajax file upload is not currently implemented, need to do a full page refresh
			// on file pages
			if ($files && $mand_ok) \Path::base_redir(\Path::$q, \Path::_GET());
			}

		/* reset groups if save worked
		if ($mand_ok) {
			$groups = $this->my_fields();
			}
		else {
			// print_var($groups);
			}
			*/

		$groups = $this->my_fields();
		$this->groups = array();
		reset($groups);
		if (! isset($this->current_group)) $this->current_group = current($groups)->name;
		$set = false;
		foreach ($groups as $group) {
			$this->groups[$group->name] = $group;
			if (! $group->allow && ! $set) {
				$this->current_group = $group->name;
				$set = true;
				}
			// set all remaining steps as incomplete
			// else if ($set) $group->allow = false;
			}
		if (post('current_group')) {
			$this->current_group = post('current_group');
			}

		parent::__construct($index);
		}

	/**
		The display function.
		What shows within each slide as it's called.
		*/
	public function my_list()
		{
		$prev = $group = null;
		foreach ($this->groups as $k=>$v) {
			if ($this->current_group == $k) {
				$group = $v;
				break;
				}
			$prev = $v;
			}

		// $group = is($this->groups, $this->current_group);
		$th = $this;

		$s = ''
		. "<div class='alert'>" . alert() . "</div>"
		. "<input type='hidden' name='input_group_save' value='$this->current_group'>"
		. "<div class='group-label'>$group->label"
			. ($prev && $group->takeover ? " | <a href='#' onClick=\"get_refresh('&current_group={$prev->name}');\">Back</a>" : '')
			. "</div>"
			;

		// using this for full width breakaways, like sitter selection
		if ($group->takeover) {
			$s .= implode('', $group->inputs);
			}

		// standard steps + inputs display
		else {
			$s .= ''
			. "<div class='steps-expander' onClick=\"$('.steps').toggle();\">&darr; Touch to expand steps</div>"
			. "<div class='" . $this->my_steps_class() . "'>"
				. "<ul>"
				. implode('', array_map(function ($x) use ($th) {
					if ($x->skip) return;

					$class = $th->current_group == $x->name ? 'current' : '';
					$class .= $x->allow ? ' complete' : '';
					$class .= $x->lock ? ' lock' : '';

					$click = ! $x->lock ? " onClick=\"get_refresh('&current_group=$x->name');\" " : '';
					$link = "<span class='text' $click>" . _to_words($x->name) . "</span>";
					return "<li class='$class'>$link</li>";
					}, $this->groups))
				. "</ul>"
				. "</div>"
				. \Form\Create::control_group($group->inputs)
				. "<div class='rug'></div>"
				;
			}
		return $s;
		}

	/**
		Steps definition.
		\return Array of input_group objects.
		*/
	public function my_fields()
		{
		}

	/* Standard input groups. */

	/**
		Account creation input group.
		*/
	public function input_group_account($title = "Parent Registration")
		{
		return input_group('create_account'
			,input_text('email')
			,input_password('password')
			,input_button('Next')->add_class('hide')
			)->label($title)
			->allow(\Login::$id || \Login::exists(sesh('verify_email')))
			->lock(\Login::$id || \Login::exists(sesh('verify_email')))
			;
		}

	/**
		Email verification input group.
		*/
	public function input_group_verify()
		{
		return input_group("verify_email"
			,input_button('login')->label('Go to Login')->link_to('login')
			,input_button('resend')->label("Resend email.")->link_to('login', array('resend'=>1))
			,input_button('reset')->label("Start over.")
			)->label("Please check your email for account verification.")
			->allow(\Login::$id)
			->lock(\Login::$id)
			;
		}

	/**
		Address.
		*/
	public function input_group_address($address = array())
		{
		return input_group('address'
			,input_hidden('address_id', is($address, 'address_id'))
			,input_text('address')
			,input_text('cross_street')->opt()
			,input_text('city')
			,input_text('state')
			,input_text('zip')
			,input_textarea('special_directions')->opt()
			,input_button('Next')->add_class('hide')
			)->label("Location")
			->allow(is($address, 'address'))
			->data($address)
			;
		}

	/* Save functions. */

	/* 
		These are the functions that run upon submitting
		input_group/s individually, identified by name.

		Take the name of an input_group, it's first parameter,
		and define a function by the same name which is what will run upon
		saving that step.

		Two examples are provided for the standard input groups above.
		Be aware of a name conflict if you use a different input_group of the same name
		but do not redefine the functions.
		*/

	/**
		On save of verify_email panel.
		*/
	public function verify_email($d)
		{
		// reset register process
		if (is($d, 'reset')) unset($_SESSION['verify_email']);
		}

	/**
		On save of the address panel.
		*/
	public function save_address($d)
		{
		$d = array_merge($d, who_when());

		$id = is($d, 'address_id');
		if ($id) \Db::match_update('address', $d, " where id=" . id_zero($id));
		else $id = \Db::match_insert('address', $d);

		return $id;
		}

	/**
		In case function does not exist.
		*/
	public function __call($name, $params) { return; }

	/* Other redefines. */

	/**
		\return CSS class for the steps panel.
		*/
	public function my_steps_class()
		{
		return 'steps';
		}
	}
