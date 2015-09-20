/**
	A class that allows php functions to be called from the frontend.
	*/
var Mallorca = (function () {
	
	/** Pages index */
	var page_k;

	/** Hold while loading */
	var hold = false;

	/** Wait to show spinner */
	var hold_tm;

	/** Debug loading icon */
	var loading_icon = true;

	/** Interval between panel loading */
	var fade_interval = 140;

	/** The wait before beginning the spinner */
	var spinner_interval = 1500; //777

	/** Request history for the back button */
	var requests = [];

	/** clicked element */
	var clicker;

	/** functions to run once ready */
	var ready_fn = [];

	/**
	 	Run request.
	 	\param	string	data
		*/
	function request(data) {
		if (hold) return;

		// back button history
		var hist = {};
		for (x in data) hist[x] = data[x];
		requests.push(hist);
		// only keep 10
		if (requests.length > 10) requests.shift();
		// if (location.hash) location.hash = requests.length;

		hold = true;

		if (loading_icon) {
			var hold_tm = setTimeout(function () {
				if (hold) $('.loading-mask').show();
				}, spinner_interval);
			}

		$.post(local_path + 'request.php?' + json_get, data, function (html) {
			try {
				var page = $.parseJSON(html);
				}
			catch (error) {
				alert(html);
				// $.featherlight($('<pre>').html(html));
				hold = false;
				$('.loading-mask').hide();
				return;
				}

			// iterate incrementally
			console.log(page);
			page_k = 0;
			var pages = [];
			for (k in page) {
				if (k == 'request') {
					console.log(page[k]);
					continue;
					}
				// else if (k == 'clear_url' && page[k] == true) {
					// history.replaceState('', '', local_path + '' + location.hash);
					// }
				else if (k == 'set_url') {
					// alert(k);
					// TODO keep working on, just replacing to redirect for now
					window.location = local_path + page[k];
					// history.pushState('', '', local_path + page[k]);
					}
				else if (k == 'set_title') {
					document.title = page[k];
					}
				else if (k == 'redirect') {
					window.location = page[k];
					return false;
					}
				else {
					pages.push({
						'selector' : page[k]['selector'],
						'content' : page[k]['content'],
						'method' : page[k]['method']
						});
					}
				}
			load_next(page_k, pages);
			});
		}


	/**
		This function able to be called more directly, in the case of custom refresh sections.
		\param	string	data_fn Prepare this with PHP's stack()
		\param	string	data	Form or otherwise
		*/
	function run_stack(data_fn, data) {
		// var req = json_get;
		var req = {}; // json_get;
		req['stack'] = {
			'data-fn' : data_fn,
			'data' : data
			};
		request(req);
		}

	/**
		The request preparation function used here.
		It seeks to gather all input data up to the nearest parent element with the class "data-group"
		That is, from the button that was clicked submitting the request.
		If the clicking element has the class "all-data", all input data from the page will be passed along.
		*/
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

	/**
		Cleanup after request has been processed and completed.
		Primarily resets click events on all new data-fn elements.
		*/
	function done_loading() {

		if (clicker) {
			var after = clicker.attr('after-fn');
			if (typeof(effects[after]) !== 'undefined') effects[after](clicker);
			}

		// re-bind action buttons
		$(".data-fn").unbind('click').click(run_request)

		hold = false;
		if (loading_icon) {
			clearTimeout(hold_tm);
			$('.loading-mask').hide();
			}

		$('.input-text').keyup(set_enters);

		for (fn in ready_fn) {
			ready_fn[fn]();
			}
		ready_fn = [];
		}

	/**
		Load the next element / request.
		*/
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
		var sel = selector;

		if (sel == '') return false;
		if ($(sel).length != 1) {
			alert(sel + ' has ' + $(sel).length + ' results. Please choose a unique selector.');
			return false;
			}

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
		var k = e.which || e.keyCode; // ff || ie		

		if (k == 13) {
			$(this).closest('.control-group').find('.data-enter').click();
			}
		}

	/**
		Initialize.
		*/
	function init() {

		// back button functionality
		window.onpopstate = function (e) {
			e.preventDefault();
			if (hold) return true;
			requests.pop();
			var last = requests.pop();
			if (last) request(last);
			return false;
			};

		$(document).ready(function () {
			if (mallorca_init) {
				$(".content").hide();
				var req = {};
				// for (k in json_get) req[k] = json_get[k];
				req.init = true;
				request(req);
				}
			else {
				done_loading();
				}

			});
		return this;
		}

	/**
		Indicate whether mallorca is on hold (running a request).
		*/
	function on_hold() {
		return hold;
		}

	/**
		Add a function to a queue to run after mallorca finishes it's request.
		The usage would be, you are returning some inline script from your request itself.
		It sholud wait until it itself is done loading to run.
		*/
	function ready(fn) {
		if (hold) ready_fn.push(fn);
		else fn();
		}

	return {
		request: request,
		run_stack: run_stack,
		init: init,
		on_hold: on_hold,
		ready: ready
		}

	})().init();
