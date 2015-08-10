
/**
	th = $(this) by convention
	*/

var effects = {

	'select_me' : function (th) {
		th.addClass('selected');
		th.siblings().removeClass('selected');
		},

	'confirm_delete' :  function (th) {
		return confirm("Are you sure you want to delete?");
		},

	'remove_row' : function (th) {
		th.closest('tr').remove();
		},

	'clear_inputs' : function (th) {
		$('.input-text').attr('value', '');
		}

	}
