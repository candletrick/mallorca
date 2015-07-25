<?php
$path = \Config::$local_path;
?>
<script type='text/javascript'>
// Pages index
var page_k;

// Hold while loading
var hold = false;

// Wait to show spinner
var hold_tm;

// Debug loading icon
var loading_icon = true;

// Interval between panel loading
var fade_interval = 140;

// The wait before beginning the spinner
var spinner_interval = 1500; //777

// Request history for the back button
var requests = [];

/**
	*/
function request(data) {
	if (hold) return;

	// back button history
	requests.push(data);
	// only keep 10
	if (requests.length > 10) requests.shift();
	if (location.hash) location.hash = '';

	hold = true;

	if (loading_icon) {
		var hold_tm = setTimeout(function () {
			if (hold) $('.loading-mask').show();
			}, spinner_interval);
		}

	$.post("<?php echo $path . '/index.php'; ?>", data, function (html) {
		try {
			var page = $.parseJSON(html);
			}
		catch (error) {
			alert(html);
			hold = false;
			$('.loading-mask').hide();
			return;
			}

		// iterate incrementally
		page_k = 0;
		var pages = [];
		// console.log(pages);
		for (k in page) {
			pages.push({
				'k' : k,
				'v' : page[k]['content'],
				'method' : page[k]['method']
				});
			if (k == 'clear_url' && page[k] == true) {
				history.replaceState('', '', '<?php echo $path; ?>' + '' + location.hash);
				}
			else if (k == 'set_url') {
				history.replaceState('', '', '<?php echo $path; ?>' + page[k] + '' + location.hash);
				}
			else if (k == 'set_title') {
				document.title = page[k];
				}
			}
		load_next(page_k, pages);

		for (k in page) {
			if (k == 'request') {
				console.log(page[k]);
				continue;
				}
			}
		});
	}

var clicker;

function run_request() {
	// var fm = $(this).closest('form');
	var fm = $(this).hasClass('all-data') ? $(':input') : $(this).closest('.control-group').find(':input');

	clicker = $(this);

	// request($(this).attr('data-fn') + (fm ? '&' + fm.serialize() : ""));
	request({
		'stack' : {
			'data-fn' : $(this).attr('data-fn'),
			'data' : fm.serialize()
			}
		});
	}

function done_loading() {

	if (clicker) {
		var after = clicker.attr('after-fn');
		if (typeof(effects[after]) !== 'undefined') effects[after](clicker);
		}

	// re-bind action buttons
	$(".action, .data-fn").unbind('click').click(run_request)
	/*
	$('.input-text').keyup(function (e) {
		var k = e.which;
		if (k == 13) {
			$(this).closest('.control-group').find('.submit').first().click();
			}
		});
		*/

	hold = false;
	if (loading_icon) {
		clearTimeout(hold_tm);
		$('.loading-mask').hide();
		}

	$('.input-text').keyup(set_enters);
	}

function load_next(index, pages) {
	var init = true;

	if (index > pages.length - 1) {
		page_k = 0;
		done_loading();
		return;
		}
	var k = pages[index].k;
	var v = pages[index].v;
	var meth = pages[index].method;
	page_k++;
	if (! $(k).length) load_next(page_k, pages);
	// var iv = $(k).hasClass('no-fade') ? 0 : fade_interval;
	var iv = $(k).hasClass('fade') ? fade_interval : 0;

	var sel = k == 'this' ? this : k;

	$(sel).animate({
		opacity : 0,
		}, iv, 'linear', function() {
		if (meth == 'append') $(this).append(v);
		else if (meth == 'prepend') $(this).prepend(v);
		else $(this).html(v);
		
		$(this).fadeIn(iv, function() {
			load_next(page_k, pages);
			})
		});
	}

/**
	Make it possible to submit forms by clicking enter.
	*/
function set_enters(e) {
	// var e = evt || window.event; // ff || ie
	var k = e.which || e.keyCode; // ff || ie		

	if (k == 13) {
		$(this).closest('.control-group').find('.data-enter').click();
		}
	}

$(document).ready(function () {
	$(".content").hide();
	request(<?php echo json_encode($_GET); ?>);

	// location.hash = 'login';

	window.onhashchange = function (e) {
		if (hold) return true;
		requests.pop();
		var last = requests.pop();
		request(last);
		e.preventDefault();
		return false;
		};
	});
</script>
