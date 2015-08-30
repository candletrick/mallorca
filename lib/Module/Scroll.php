<?php
namespace Module;

class Scroll extends \Module
	{
	public function my_display()
		{
		return "<div class='journal-scroll'>"
		. implode('', array_map(function ($row) {
			return "<div class='entry'>"
			. "<div class='date'>"
				. $row['created_on']
				. " | " . \Path::link_to('Edit', 'journal/entry/edit', array('journal_id'=>$row['journal_id'], 'entry_id'=>$row['id']))
				. "</div>"
			. str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", str_replace("\n", "<br>", $row['content']))
			. "</div>";
			}, \Db::results($this->my_query())))
			. "</div>"
		;

		}
	}
