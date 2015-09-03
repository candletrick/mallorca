<?php

/**
	This class modifies column descriptions for \\Db\\Query style queries.
	It is initiated with the m() function.

	For example:
	\code
	$results = select('my_table', [
		'id',
		m('name')->asc()->as('who')
		])->results();
	\endcode
	
	This class essentially is just an array whose properties can be set via __call(),
	in the above example, the result is a property of $this->asc with a value of 1,
	and a property of $this->as with a value of 'who'
	*/
class Meta
	{
	/**
		\param	array	$args	Array of properties to set.
		*/
	public function __construct($args)
		{
		foreach ($args as $k=>$v) {
			if ($k == 'this') continue;
			$this->$k = $v;
			}
		$this->as = $this->name;
		}

	/**
		No access to undefined properties.
		*/
	public function __get($name)
		{
		return '';
		}

	/**
		\param	string	$name	Property to set.
		\param	mixed	$params	Value to set it to.
		*/
	public function __call($name, $params)
		{
		if (count($params) > 1) {
			$this->$name = $params;
			}
		else if (empty($params)) $this->$name = 1;
		else $this->$name = array_shift($params);
		return $this;
		}

	/**
		\return $this->name
		*/
	public function __toString()
		{
		return $this->name;
		}

	/**
		Left outer join.
		\param	string	$table	Table to join.
		\param	array	$conditions Array of conditions joined with and, with the keys belonging to $table, and the values
			belonging to column names of the main table being joined onto,
			for example:
			\code
			echo select('message', [
				m('name')->left('user', ['id'=>'user_id'])
				])->text();
			\endcode
			Results in:
			select b.name from message a left outer join user b on a.user_id=b.id

			* Note, if the join follows a table_id naming convention, such as message.user_id => user.id,
			$conditions is not necessary to write out.
		\param	string	$join_type	Join type.
		*/
	public function left($table, $conditions = array(), $join_type = 'left outer')
		{
		if (is_object($table)) {
			// print_var(get_defined_vars());
			}
		$this->join = new Meta(get_defined_vars());
		return $this;
		}

	/**
		Inner join.
		\param	string	$join_type	Join type.
		\param	string	$table	Table to join.
		\param	array	$conditions Array of conditions.
		\sa $this->left()
		*/
	public function inner($table, $conditions = array(), $join_type = 'inner')
		{
		$this->join = new Meta(get_defined_vars());
		return $this;
		}

	/**
		Indicate the column should be between two values.
		\param	string	$start First value.
		\param	string	$end Second value.
		*/
	public function where_between($start, $end)
		{
		$this->start = $start;
		$this->end = $end;
		$this->where_between = true;
		return $this;
		}

	/**
		Substring of the column.
		\param	int	$start	Start index.
		\param	int	$end	End index.
		*/
	public function substr($start, $end)
		{
		$this->start = $start;
		$this->end = $end;
		$this->substr = true;
		return $this;
		}
	}

