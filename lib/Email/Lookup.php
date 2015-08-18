<?php
namespace Email;

class Lookup extends \Module\Lookup
	{
	function my_query()
		{
		return select('email', array('id', 'name', 'subject', 'content'));
		}


	function my_columns()
		{
		return array('name', 'subject', 'content');
		}

	function my_row($row)
		{
		$c = $row['content'];
		$n = strpos($c, "\n");
		$len = $n && $n < 40 ? $n : 40;
		return $this->cells(array($row['name'], $row['subject'], substr($c, 0, $len) . '...'));
		}
	}
