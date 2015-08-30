<?php
namespace Module;

/**
	This is a class for a loose abstraction of pages where there
	is a form that, upon submission, refreshes a panel on the page and possibly itself.
	Examples are StepByStep (a one page multi step form), and Chat (a chat box and the list of chats).
	*/
class RefreshForm extends \Module
	{
	/** Flag to not return a JSON result. */
	public $no_json = false;

	function __construct($index)
		{
		$this->index = $index;
		if ($_SERVER['REQUEST_METHOD'] == 'POST') $this->my_post($_POST);
		}

	/**
		The function to run when a POST is submitted.
		The main refresh function.
		\param	$post	array	The $_POST or other array.
		\return Nothing. Echo the contents for the refresh in JSON.
		*/
	function my_post($post)
		{
		if (is($post, 'no_json')) {
			$this->no_json = true;
			return;
			}

		echo json_encode(array(
			'content'=>$this->my_list()
			));
		die;
		}
	
	/**
		Page header scripts.
		*/
	function my_head()
		{
		ob_start();

		// $this->groups['create_account']->call_save(array('email'=>'joyn','password'=>'doe'));
		?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script><!--online-->
<script type='text/javascript'>
/**
	Show or hide form save buttons based on whether the required fields are filled out.
	*/
function toggle_button()
	{
	var vals = true;
	$('.input input').each(function() {
		if (! $(this).attr('value') && $(this).hasClass('mand')) {
			// alert($(this).attr('name'));
			$('.button.hide').hide();
			vals = false;
			}
		});
	if (vals) $('.button').show();
	}

/**
	Check if the form has files to submit.
	*/
function are_files()
	{
	return $('input[type="file"]').length;
	}

/** 
	Get the refresh.
	\param	data	array	Additional POST data, in url format.
	*/
function get_refresh(data)
	{
	var tm = setTimeout(function() {
		$('.refresh').fadeOut(200);
		$('.please').fadeIn(200);	
		}, 1500);
	
	// setTimeout(function() {
	var _GET = "&<?php
	$get = $_GET;
	unset($get['q']);
	echo http_build_query($get);
	?>";

	$.post("index.php?q=<?php echo$this->index->path; ?>" + _GET, $('#comment-form').serialize() + data, function(html) {
		clearTimeout(tm);

		try {
			var coral = $.parseJSON(html);
			}
		catch (error) {
			alert(html);
			return;
			}
		$('.refresh').html(coral.content).fadeIn(200);
		$('#new_comment').attr('value', '').focus();

		$('.please').hide();

		toggle_button();
		$('.input *').keyup(toggle_button);
		$('.input *').change(toggle_button);
		$('.box').click(toggle_button);

		$('.button').click(function () {
			var v = $(this).attr('data-value') ? $(this).attr('data-value') : 1;

			$('<input>').attr({ type : 'hidden', name : $(this).attr('name'), value : v }).appendTo('.refresh');
			if (are_files()) {
				$('<input>').attr({ type : 'hidden', name : 'no_json', value : 1 }).appendTo('#comment-form');
				$('#comment-form').trigger('submit', [true]);
				}
			else get_refresh(''); // &' +  $(this).attr('name') + '=1');
			});

		set_calendars();
		});

	// }, 3000);
	}

$(document).ready(function() {
	<?php if (! $this->no_json) : ?>
	get_refresh('&<?php
	echo http_build_query($this->my_initial_params());
	?>');
	<?php endif; ?>

	$('#comment-form').submit(function(e, allow) {
		if (allow) return true;

		e.preventDefault();
		return false;
		});

	$('#new_comment').focus();
	});
</script>
		<?php
		return ob_get_clean();
		}

	function my_display()
		{
		ob_start();
		?>

<div class='refresh-form-wrapper'>
	<form autocomplete='off' name='register_form' action='<?php echo \Path::here(); ?>' method='post' id='comment-form'
	enctype='multipart/form-data'>
		<div class='please' style='text-align: left; display:none;'>Please wait...</div>
		<div class='refresh'>
		<?php
		if ($this->no_json) echo $this->my_list();
		?>
		</div>
		<div class='rug'></div>
		</form>
	</div>

		<?php
		return ob_get_clean();
		}

	/* REDEFINES */

	/**
		\return The content of the refresh panel.
		*/
	public function my_list()
		{
		return 'My List.';
		}

	/**
		An array of data or parameters to send for the initial refresh / page load.
		\return Array of parameters.
		*/
	public function my_initial_params()
		{
		return array();
		}
	}
