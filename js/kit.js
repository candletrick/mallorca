/**
	\file javascript/kit.js
	A file of utility javascript functions.
	*/

/**
	Create cookie.
	*/
function createCookie(name, val, days)
	{
	if (days)
		{
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        var expires = "; expires=" + date.toGMTString();
		}
	else var expires = "";
	document.cookie = escape(name) + "=" + escape(val) + expires + "; path=/";
	}
	
/**
	Add days to the value of a text input (used for dates).
	\param	string	el	The id of the element of whose date to push.
	\param	object	cal	The popup calendar associated with the input field, named {field_name}_o as per the popup_cal() function in markup.inc
	> Ex) end_date_o.
	\param	int	days	The number of days to add onto the date in the input field.
	\see popup_cal(), markup.inc
	*/
function pushdate(id, cal, days)
	{
	var el = $('#' + id);
	var d = days == 0 ? new Date() : new Date(el.attr('value'));
	var m = d.getTime()/1 + (days * 3600000 * 24);
	var o = new Date(m);
	
	el.attr('value', (o.getMonth()/1+1) + '/' + o.getDate() + '/' + o.getFullYear());
	cal.write_calendar(o,o);
	}

/**
	A function for refocusing on a required field in a form.
	This function works in conjunction with the mandatory() function.
	\param	object	o	The object of the element to refocus.
	\param	string	msg	The message to alert to the user upon refocus.
	*/
function refocus(o, msg)
	{
	setTimeout("document.getElementById('" + o.id + "').focus();",1); //The timeout ensures that the focus actually happens.
	if (msg == '') { return true; }
	alert(msg);
	return false;
	}

/**
	Check if text has been highlighted in an input field.
	Used to prevent users from highlighting and editing sections of a masked input field.
	\param	object	el	The object of the field.
	*/
function getRangeLength(el)
	{ 
	if (el.selectionStart) { return el.selectionEnd - el.selectionStart; }
	else if (document.selection)
		{
		el.focus();
		var r = document.selection.createRange(); 
		if (r == null) { return 0; } 
		return r.text.length;
		}  
	return 0; 
	}

/**
	Mask an input field.  This function works in cooperation with the inp() function in markup.inc,
	where the actual handlers are set.  It also works in cooperation with finalmask() here in kit.js.
	It is not to be called directly on its own.  This function controls the individual action to be taken for
	masking each letter, where as finalmask() controls the masking of the field on the whole, such as the mask pattern and what to do on blur.
	\param	event	evt	The javascript event.  This is always passed in the handler as the literal word event, such as onblur="onemask(event, ...  It is necessary for non IE browsers.
	\param	object	o	The input field object being masked.
	\param	string	mask	The mask pattern, given by finalmask().
	\param	string chr	The masking character to be added in at a given index of the mask string, such as (, -, or . 
	This is used for adding these characters in on a final loop sweep of the text.
	*/
function onemask(evt, o, mask, chr, type)
	{
	if (chr != '') { // use the masking character
		var s = chr;
		var c = s.charCodeAt(0);
		}
	else { // get the keystroke event
		var e = evt || window.event; // ff || ie
		var c = e.which || e.keyCode; // ff || ie		
		var s = String.fromCharCode(c);
		}

	// numbers only regex
	var pattern;
	if (type == 'money') pattern = /\d|\.|-/;
	else pattern = /[\d-]/;
	
	// non-letter chars
	if (c < 32 ) return true;
	/*
	else if (type == 'money')
		{
		// 111, 1., 1.11
		var pattern = /\d|\./; // /(^\d*$)|(^\d*\.($|\d$|\d\d$))/;
		return pattern.test(o.value + s);
		}
		*/
	else if (pattern.test(s) == true)
		{
		var len = o.value.length;
		
		// prevent mid-range selecting and replacing by typing
		if (getRangeLength(o) > 0)	{
			if (getRangeLength(o) != len) return false;
			//since this function must return true to keep focus on the input field, and IE is going to delete out the value and activate the key stroke due to selection
			//we must set a timeout on adding our first index mask character
			else	{
				setTimeout(function() {
					if (s != mask.charAt(0) && mask.charAt(0) != '.') o.value = mask.charAt(0) + o.value;
					}, 50);
				return true;
				}
			}
		
		if (s != mask.charAt(len) && mask.charAt(len) != '.') {
			o.value = o.value + mask.charAt(len); //add mask characters if at appropriate index
			}
		if (chr != '') o.value = o.value + s;
		return true;
		}
	return false;
	}
	
/**
	Mask an input field.  This function is called and used specifically by inp() in markup.inc.
	Available patterns:
	- phone (...)...-.....
	- fax "
	- ssn ...-..-....
	- zip .....-....
	- date ....-..-..
	\param	event	evt	The javascript event object (always passed in as the word *event*).  Necessary for non IE browsers.
	\param	object	o	The object of the input field to be masked.  Passed as *this*.
	\param	string	type	The name of the type of masking to be done from the options specified above.
	\param	int	blur	Flag specifying if this is the final mask check or not (0 or 1).  The final check is done on blur, while the intermittent checks are done on key press.
	*/
function finalmask(evt, o, type, blur)
	{
	var pattern, mask, len, s, c, i;
	
	// if (type == 'phone') mask = '(...)...-....';
	if (type == 'phone') mask = '...-...-....';
	else if (type == 'fax') mask = '(...)...-....';
	else if (type == 'ssn') mask = '...-..-....';
	else if (type == 'zip') mask = '.....-....';
	else if (type == 'date') mask = '....-..-..';
	else if (type == 'int' || type == 'money') mask = '';
	else return true;
	
	// final check
	if (blur == 1) 
		{
		var cpy = o.value;
		o.value = '';
		// pass over the string again with mask
		for (i = 0; i < cpy.length; i++) {
			onemask(evt, o, mask, cpy.charAt(i), type);
			}
		
		if (type == 'zip' && o.value.length == 5) o.value = o.value + '-0000';
		else if (type == 'money' && cpy) {
			pattern = /^-*(\d*$)|(\d*\.($|\d$|\d\d$))/;
			if (pattern.test(o.value) == false) return refocus(o, type + ' value is invalid...');
			o.value = cpy.replace(/^\./, "0.");
			o.value = o.value.replace(/\.$/, "");
			}
			
		else if (type == 'email' && o.value.length > 0) {
			pattern = /^.+@.+\.[a-z]+$/;
			if (pattern.test(o.value) == false) return refocus(o,type + ' is invalid...');
			}
		// This validation was added to the Time In Motion module to ensure the quantity is greater than 0
		else if (type == 'int' && o.value == '0') return refocus(o, 'Quantity must be greater than 0...');
		else if (o.value.length > 0 &&
			o.value.length < mask.length) return refocus(o,type + ' is invalid...');
			
		return true;
		}
		
	return onemask(evt, o, mask, '', type);
	}
	
/**
	Define a set of mandatory fields on a form, and enforce that they be completed before the form can be submitted.
	This function will also re-mask fields that have a class name indicating they should be masked, such as *phone, zip, etc*.
	\param	object	fm	The form object.
	\param	array	arr	An array of field names which are to be held as mandatory.
	*/
function mandatory(fm,arr)
	{
	var i, o;
	var elem = fm.elements;
	for (i = 0; i < elem.length; ++i)
		{
		if (! finalmask(null, elem[i],elem[i].className,1)) { return false; }
		}
	for (i = 0; i < arr.length; ++i)
		{
		o = document.getElementById(arr[i]);
		if (o && (o.value == '' || (o.type == 'checkbox' && o.checked == false)))
			{ return refocus(o, arr[i] + ' is required...'); }
		}
	return true;
	}

/**
	Count characters in a text area and display the remaining character count.
	This isn't ever called explicitly to my knowledge, but is called by the tbox() function in markup.inc.
	\param	event	evt	The javascript event.
	\param	object	o	The object of the textarea.
	\param	int	max	The max amount of characters allowed.
	*/
function char_counter(evt, o, max)
	{
	var len = o.value.length;
	var e = evt || window.event;
	var c = e.which || e.keyCode;
	
	// length reached and not delete
	console.log(c);
	if (c != 8 && !e.ctrlKey && len == max) { 
		return false;
		} 
	// tab
	else if (c == 9 && o.value.charAt(o.value.length - 1) == '\n') {
		evt.preventDefault();
		o.value = o.value + "    ";
		return false;
		}
	// return
	else if (c == 13) {
		var m = o.value.match(/\n(\s+)[^\n].*$/);
		if (m.length) {
			evt.preventDefault();
			o.value = o.value + "\n" + m[1];
			return false;
			}
		}

	
	var t = setTimeout(function(){
		if (o.value.length > max) {
			alert('Your text will be cut to ' + max + ' characters.');
			o.value = o.value.substr(0, max - 1);
			}
		document.getElementById(o.name + '_count').innerHTML = '' + (max - o.value.length);
		}, 1);

	return true;
	}

/**
	Default callback for the list items of autocomplete.
	*/
function ac_list(th, idname, id)
	{
	$('#' + idname).attr('value', $(this).html()); // set text value
	$('#' + idname + '_id').attr('value', id); // set id value
	$(this).parent().parent().hide();
	}

/**
	Link set version.
	\param	obj		th		$(this)	<li> element.
	\param	string	dest	The php url destination.
	*/
function ac_linkset(th, idname, id)
	{
	var htm = th.find('span').html();
	$('#' + idname ).attr('value', htm); // set text value
	
	if (actionable()) {
		th.find('span').html('Nested.');
		action_set({'nest_id' : id });
		return false;
		}
	else {
		location.hash = "#include/comment&coral_id=" + id;
		}
	
	$('#' + idname + '_id').attr('value', id); // set id value
	setTimeout(function() { th.parent().parent().hide(); }, 700);
	}
	
/**
	Add an autocompleting div onto a text input.
	\param	string	idname	ID name.
	\param	array	arr	Array of key value pairs.
	*/
function autocomplete(idname, arr, li_fn)
	{

	this.create = function() {
		if (typeof(li_fn) === 'undefined') li_fn = 'ac_list';
		$('#' + idname).bind('contextmenu', function() { return false; });
		$('#' + idname).keydown(function(e) { autocomplete_keydown(e, this); });
		autocomplete_write_list('', $('#' + idname));
		};

	function autocomplete_keydown(e, th)
		{
		// arrowing
		var k = e.which;
		
		var nex, prev;
		var sel = $('#auto_' + idname + ' li.selected');
		if (sel.length == 0) {
			sel = nex = $('#auto_' + idname + ' li').first();
			prev = $('#auto_' + idname + ' li').last();
			}
		else {
			nex = sel.next();
			prev = sel.prev();
			}
		
		if (k == 40) { // down arrow
			sel.removeClass('selected');
			nex.addClass('selected');
			return false;
			}
		else if (k == 38) {		// up arrow
			
			sel.removeClass('selected');
			prev.addClass('selected');
			return false;
			}
		else if (k == 13) { // enter
			e.preventDefault();
			// $(this).attr('value', sel.html());
			// window[li_fn](sel, idname, i);
			sel.click();
			// $('#auto_' + idname).hide();
			return false;
			}

		var v = $(th).attr('value');
		if (k >=32) v += String.fromCharCode(k);
		v = v.toLowerCase();
		// var id = $(this).attr('id');
		autocomplete_write_list(v, th);
		
		return true;
		}

	function autocomplete_write_list(v, th)
		{
		var auto = $(th).parent().find('.auto-fill');
		var s = '';
		var n = 0;

		if (v == '') {
			auto.hide();
			return true;
			}
		/*
		if (v == '') {
			for (i in arr) {
				if (arr[i].pinned == 1) {
					s += "<li onClick=\"" + li_fn + "($(this), '" + idname + "', '" + arr[i].id + "');\">"
						+ arr[i].coral + "</li>";
					n++;
					}
				}
			auto.html("<ul id='auto_" + idname + "'>" + s + "</ul>").show();
			// auto.hide();
			return true;
			}
			*/
		
		for (i in arr) {
			if (arr[i].toLowerCase().indexOf(v) >= 0) {
				s += "<li onClick=\"" + li_fn + "($(this), '" + idname + "', '" + i + "');\">"
					+ arr[i]+ "</li>";
				n++;
				}
			}
		auto.html("<ul id='auto_" + idname + "'>" + s + "</ul>").show();
		return true;
		}
	}

function set_calendars()
	{
	$('.date-calendar').bind('focus', function() {
		var n = $(this).attr('name');
		var c = $('#' + n + '_calendar');
		if (cal_stop == 0) c.show();
		})
	.bind('blur', function() {
		if (cal_stop == 0 && c.is(':visible')) c.hide();
		else {
			cal_stop = 0;
			$(this).focus();
			}
		})
	.bind('change', function() {
		n + "_o";
		// {$name}_o.write_calendar(this.value,this.value);\"";
		});
	}

/**
	When a changeable form input is a certain value, show or hide other
	blocks.
	\param	string	name	Changing input name.
	\param	string	value	Value at which to show $names.
	\param	array	names
	*/
function show_when(name, value, names)
	{
	var th = $('#' + name + ", input[name=" + name + "]:checked");
	var v = th.attr('value');
	var block_ids = names.map(function (x) { return '#' + x + '_control'; }).join(', ');
	var blocks = $(block_ids);

	// alert(name + v + value);
	if (v == value && th.is(':visible')) {
		blocks.show();
		}
	else {
		blocks.hide();
		}
	}
