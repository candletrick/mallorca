
/**
	th = $(this) by convention
	*/

var effects = {

	'remove_row' : function (th) {
		th.closest('tr').remove();
		},

	'clear_inputs' : function (th) {
		$('.input-text').attr('value', '');
		}

	}
