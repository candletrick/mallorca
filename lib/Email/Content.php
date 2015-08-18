<?php

class Content
	{
	static public function get($name)
		{
		$v = \Db::value("select content from content where name=" . db()->esc($name));
		return $v ? $v : $name . " is not defined.";
		}
	}
