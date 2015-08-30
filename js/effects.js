
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

	'remove_group' : function (th) {
		th.closest('.control-group, .data-group').remove();
		},

	'remove_this' : function (th) {
		th.remove();
		},

	'remove_row' : function (th) {
		th.closest('tr').remove();
		},

	'clear_inputs' : function (th) {
		$('.input-text').attr('value', '');
		},

	'advance_cat' : function (th) {
		var cats = $('.cats');
		var c = cats.attr('class');
		c = c.replace(/cats\s*/g, "");
		var m = c.match(/\d+/);
		var x = parseInt(m[0]);
		var y = x;
		y++;
		if (y > 5) y = 1;
		cats.animate({"opacity" : "0"}, 500, function() {
			$(this).removeClass('cat-' + x).addClass('cat-' + y);
			$(this).animate({"opacity" : "1"}, 500);
			});
		$('.input-text').attr('value', '');
		}

	}
