<?php
namespace Module;

class Mallorca extends \Module
	{
	static public function my_default()
		{
		return array(
			'.content'=>fn('Task::display')
			);
		}

	public function my_display()
		{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			// require 'ext/Kit.php'; // Kit
			// require 'ext/Autoload.php'; // Autoload
			// require 'ext/Functions.php'; // Functions
			// require '../protected/mallorca_local.php'; // Db and website() function
			ob_end_clean();
			echo \Request::respond();
			die;
			}
		ob_start();
		?>
<script type="text/javascript">
/**
	*/
function request(data) {
	$.post("<?php echo \Path::here(); ?>", data, function (html) {
		try {
			var page = $.parseJSON(html);
			}
		catch (error) {
			alert(html);
			return;
			}

		for (k in page) {
			if (k == 'post') {
				console.log(k);
				console.log(page[k]);
				continue;
				}
			$(k).html(page[k]).fadeIn(700);
			/*
			$("." + k).fadeOut(350, function() {
				$(this).html(k + page[k]).fadeIn(700);
				});
				*/
			}
			
		$(".action").unbind('click').click(function (e) {
			var fm = $(this).closest('form');
			request($(this).attr('data-fn') + (fm ? '&' + fm.serialize() : ""));
			});
		/*
		$("form").unbind('submit').submit(function (e) {
			e.preventDefault();
			alert('here');
			request($(this).serialize());
			return false;
			});
			*/

		$("input[type='text']").first().focus();
		});
	}

$(document).ready(function () {
	$(".content").hide();
	request({});
	});
</script>
<div class="content"></div>
		<?php
		return ob_get_clean();
		}
	}
