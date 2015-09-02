var Mallorca = (function () {
	
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

	// clicked element
	var clicker;

	/**
		*/
	function request(data) {
		if (hold) return;

		// back button history
		requests.push(data);
		// only keep 10
		if (requests.length > 10) requests.shift();
		// if (location.hash) location.hash = requests.length;

		hold = true;

		if (loading_icon) {
			var hold_tm = setTimeout(function () {
				if (hold) $('.loading-mask').show();
				}, spinner_interval);
			}

		$.post(local_path + 'request.php', data, function (html) {
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
			// console.log(page);
			// if(typeof(console) !== 'undefined') console.log(pages);
			for (k in page) {
				if (k == 'request') {
					if(typeof(console) !== 'undefined') console.log(page[k]);
					continue;
					}

				pages.push({
					'selector' : page[k]['selector'],
					'content' : page[k]['content'],
					'method' : page[k]['method']
					});
				if (k == 'clear_url' && page[k] == true) {
					history.replaceState('', '', local_path + '' + location.hash);
					}
				else if (k == 'set_url') {
					// if (location.hash) location.hash = requests.length;
					// location.hash = page[k];
					// history.replaceState('', '', local_path + page[k]); // + location.hash); // '#' + requests.length); // location.hash);
					history.pushState('', '', local_path + page[k]); // + location.hash); // '#' + requests.length); // location.hash);
					}
				else if (k == 'set_title') {
					document.title = page[k];
					}
				}
			load_next(page_k, pages);

			/*
			for (k in page) {
				if (k == 'request') {
					if(typeof(console) !== 'undefined') console.log(page[k]);
					continue;
					}
				}
				*/
			});
		}


	function run_stack(data_fn, data) {
		var req = json_get;
		req['stack'] = {
			'data-fn' : data_fn,
			'data' : data
			};
		request(req);
		}

	function run_request(e) {
		e.stopPropagation();

		var data = $(this).hasClass('all-data') ? $(':input').serialize()
		: ($(this).hasClass('no-data') ? ''
		: $(this).closest('.data-group').find(':input').serialize());
		clicker = $(this);

		// before-fn
		var before = clicker.attr('before-fn');
		var before_param = clicker.attr('before-fn-param');
		if (typeof(effects[before]) !== 'undefined') {
			if (! effects[before](clicker, before_param)) return false;
			}

		run_stack($(this).attr('data-fn'), data);

		return false;
		}

	function done_loading() {

		if (clicker) {
			var after = clicker.attr('after-fn');
			if (typeof(effects[after]) !== 'undefined') effects[after](clicker);
			}

		// re-bind action buttons
		$(".action, .data-fn").unbind('click').click(run_request)

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
		var selector = pages[index].selector;
		var content = pages[index].content;
		var meth = pages[index].method;
		page_k++;
		if (! $(selector).length) load_next(page_k, pages);
		// var iv = $(selector).hasClass('no-fade') ? 0 : fade_interval;
		var iv = $(selector).hasClass('fade') ? fade_interval : 0;

		/*
		if (selector == '') {
			if (clicker) sel = clicker;
			else return false;
			}
		else sel = $(selector);
		*/
		var sel = selector;

		// alert(sel + $(sel).html());
		$(sel).fadeOut(iv, function() {
			if (meth == 'append') $(this).append(content);
			else if (meth == 'prepend') $(this).prepend(content);
			else if (meth == 'replaceWith') $(this).replaceWith(content);
			else if (meth == 'insertAfter') $(content).insertAfter(this);
			else $(this).html(content);
			
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

	function init() {
		// location.hash = '0';

		window.onpopstate = function (e) {
			e.preventDefault();
			if (hold) return true;
			requests.pop();
			var last = requests.pop();
			request(last);
			return false;
			};

		$(document).ready(function () {
			if (mallorca_init) {
				$(".content").hide();
				var req = {};
				for (k in json_get) req[k] = json_get[k];
				req.init = true;
				request(req);
				}
			else {
				done_loading();
				}

			});
		return this;
		}

	return {
		request: request,
		run_stack: run_stack,
		init: init
		}
	})().init();
