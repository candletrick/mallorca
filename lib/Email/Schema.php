<?php
namespace Email;

class Schema extends \Db\Schema
	{
	public function my_name()
		{
		return 'email';
		}

	public function my_columns()
		{
		$t = db()->table();
		return array(
			$t->primary_key(),
			$t->str('name', 400),
			$t->str('event', 60),
			$t->str('description', 250),
			$t->str('subject', 200),
			$t->str('content', 20000),
			$t->str('content_type', 20),
			$t->who(),
			$t->when(),
			);
		}

	}
