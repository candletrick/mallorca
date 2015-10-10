<?php
namespace Module;

class Sort extends \Module
	{
	public function my_headers()
		{
		return 	"<script src=\"//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js\"></script>";
		}

	public function my_display()
		{
		$query = $this->my_query();

		$i = 0;
		$cells = [];
		foreach (\Db::results($query) as $row) {
			$cells[] = "<div class='cell cell-$i'" . (is($row, 'preview') ? " style='opacity:0.1'" : '') . ">"
			. input_hidden('cell_' . $row['id'], $row['id'])
			. $this->my_cell($row)
			. "</div>"
			;
			$i++;
			}

		echo $this->my_script()
		. div('data-group',
			div('lookup-wrapper refresh-form-wrapper',
				$this->my_banner(),
				input_button('Save Changes')->click([
					self::call('my_save')
					])->after('say_saved'),
				alert(),
				"<p><b>Drag and drop to change the order.</b></p>",
				div('grid',
					implode('', $cells)
					),
				rug()
				)
			);
		}

	public function my_script()
		{
		ob_start();
		?>
<script type='text/javascript'>
	
function set_swappable()
	{
	var pre;
	$('.cell').draggable({
		snap: true,
		snapMode:'inner',
		zIndex:100,
		snapTolerance:10,
		revert:true,
		start: function(event, ui)
			{
			pre = $(this).prev('div');
			}
		});
	$('.cell').droppable({
		drop: function(event, ui)
			{
			var k1 = $(this).html();
			var k2 = ui.draggable.html();
			$(this).html(k2);
			ui.draggable.html(k1);
			}
		});
	}

$(document).ready(function() {
	set_swappable();
	});
</script>
	<?php
	return ob_get_clean();
		}

	static public function my_table()
		{
		// return $this->index->token;
		}

	public function my_query()
		{
		return "select * from " . $this->my_table() . " order by sort_order asc";
		}

	static public function my_save()
		{
		$table = static::my_table();
		$i = 0;
		foreach (\Request::$data as $k=>$v) {
			if (strpos($k, 'cell') === false) continue;
			$i++;
			\Db::query("update $table set sort_order=$i where id=" . id_zero($v));
			}
		}
	}

