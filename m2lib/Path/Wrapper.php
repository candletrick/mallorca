<?php
namespace Path;

class Wrapper extends \MyIndex
	{
	public function my_display()
		{
		ob_start();
		?>
		<?php
		if (is_object($this->child)) {
			echo $this->child->my_display();
			}
		?>
		<?php
		return ob_get_clean();
		}
	}
