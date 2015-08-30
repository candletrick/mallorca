<?php
namespace Module;

/**
	Dashboards.
	*/
class Dashboard extends \Module
	{
	/** Count of panels. */
	public $num;

	public function my_display()
		{
		return "<div class='dashboard'>"
			. "<div class='banner'><div class='text'>" . $this->my_title() . "</div></div>"
			. $this->my_panels()
			. "<div class='rug'></div>"
			. "</div>"
			;
		}

	/**
		\param	string	$titile	Title / header.
		\param	string	$content	Panel content.
		\param	int	$cols	How many columns does the panel occupy?
		*/
	public function panel($title, $content, $cols = 1)
		{
		$left = $this->num % 2 == 0 ? 'left' : '';
		if ($cols == 1) $this->num++;
		if ($cols == 2) $left .= ' wide';

		return "<div class='panel $left'>"
			. "<div class='title'>$title</div>"
			. $content
			. "</div>"
			;
		}

	/**
		Everyone has a message panel, so a standard function.
		\return Message panel.
		*/
	public function my_messages()
		{
		return 
		$this->panel('Messages ' . \Path::link_to('New', 'message/chat'), 
			table(db()->results("select
				b.id,
				b.name,
				a.message
			from message a
			left outer join user b on a.created_by=b.id
			where a.send_to=" . \Login::$esc))->rowlink(function ($row) {
				return \Path::base_to('message/chat', array('send_to'=>$row['id']));
				})->column_fn('name', function ($x) {
				return "<b>$x</b>";
					})
			)
			;
		}

	/* REDEFINES */

	/**
		\return Dashboadrd title.
		*/
	public function my_title()
		{
		return 'Dashboard';
		}
	}

