<?php
namespace Note;

class Schema extends \Schema
	{
	static function my_schema()
		{
		return schema('note', array(
			'note',
			'parent_id',
			'child_count',
			));
		}
	}
