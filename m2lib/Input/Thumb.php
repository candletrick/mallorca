<?php
namespace Input;

class Thumb extends \Input
	{
	public function my_construct($booking = array())
		{
		$this->booking = $booking;
		$this->mand = false;
		}

	public function my_input()
		{
		ob_start();
		if (is($this->booking, 'sitter_id')) {
			echo \Home\Sitters::grid("select a.id, a.name, a.bio, a.photo_path
				from sitter a where a.id=" . $this->booking['sitter_id']);
			echo "<div class='group-label'>Change Sitter</div>";
			}
		echo \Home\Sitters::grid(\Sitter\Schema::available_query($this->booking));
		return ob_get_clean();
		}

	/*
	public function check($value)
		{
		return true;
		}
		*/
	}
