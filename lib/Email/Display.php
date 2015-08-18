<?php
namespace Content;

/**
	** NOTE: this class is not meant to be used as a path.
	It is just for the dsiplay functionality.
	*/
class Display
	{
	static public function by_name($name, $wrapper = true)
		{
		$s = Markdown(\Db::value("select content from content where name=" . db()->esc($name)));
		return $wrapper ? "<div class='content-wrapper'>" . $s . "</div>"
		: $s;
		}
	}
