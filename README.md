Mallorca Specification
====

Introduction
----

Intermediate, syntactical layer inbetween server and client.

Question? Why must a web application be based upon RESTful routes? What is a route?
When a comes down to it, a route is essentially a function call with four parameters:

- PATH  
- QUERY_STRING
- REQUEST_METHOD
- DATA

In favor of search engines, and application views / states correlating to user URLs, the route system remains. It has even been turned into a kind of pseudo language, where variables are parsed out of the path itself to manage controllers, actions, views, and IDs, in order to offer additional structure and granularity to what is essentially a function call.

Simple question: why not just make an actual function call, instead of this idiosyncratic path system? On the server side, this removes the entire step of defining routes and a router.

The response would be of course that the browser cannot call functions on the server. But it doesn't need to. It can simply make a request that supplies the function calls encoded as a hash! On the server side, this hash can be deconstructed and the functions called, after passing through an optional security / filtering / allowable layer.

To further this process, now we will make a function on the server side that forms as our 'transport layer', encoded function calls to itself. It passes these to the browser, which can pass them back and be interpreted.

Now we can write all kinds of frontend code but all in the server language, no additional JS or frontside work is needed save for special visual effects or optimizations where no data-alteration takes place. As long as the transport layer is well constructed, consistent, and well thought out we will not need to modify it any further.

So, the server side needs to be able to encrypt and decrpyt its own function calls. On the JS side, it is extremely simple. All that needs to happen is that it can send the request with the encrypted calls, receive the result, then delegate it out to elements based on very simple rules. These rules are simply, sets of selectors, and the type of operation, for instance: append, prepend, replaceWith.

Now we have dropped the need to specify routes and we are able to write frontend effects in the backend. The best part of all is that the page does not have to refresh because we can call only the functions we want specifically for what's changed, rather than re-rendering the whole page based on a route.

Since routes in their normal way are function calls after all, this system in no way prevents their usage, they are simple a special, 'route' type of function call and behave just like they do in normal applications. If we need the server to modify the url as the result of its action, we can do that too by call a 'path', the difference is, this time, the path can with complete specificity update specific divs with specific content rather than needing to do any kind of page reloading.

Since this a design pattern, the server language does not matter, their simply needs to be a script capable of receiving function calls to decode and doing so.

For reasons that will be explained in a different article, I personally am sticking with PHP for this purpose for the time being.

Syntax
----

Let's take the example of a button rendered on the server side. We have a function input_button, which returns an object. We will now chain a method off of this object to indicate a list of functions we'd like to be performed when the click happens.

Now here let's elaborate on exactly what this means in order to think of a consistent, intuitive way to write it.

1. We need to be able to write a list of function calls.  
2. If the function call returns some new rendering it needs to be able to be applied to a CSS selector, along with a type of appending / replacing operation.  
2a. We need to be able to call intermediary, purely JS functions before and after each of the functions,  
2b. or the entire set.  
2c. From the frontend side, the function calls can happen in a non-blocking, sequential manner or all at once.  
3. We need to be able to chain a function call into other calls off of itself, with parameters as each step.  
4. We need to be able to pipe a function call into other functions calls with their own parameters.  

These points will be discussed first. There are a few other points that I will mention now then discuss after:

5. We need user-inputted page data to come through for use by the functions.  
6. This needs to be able to come through the surrounding form, page-wide, or via specific selectors.  
7. We need to be able to sanitize / check the function calls.  
8. In a most ideal setting, but the most verbose, we can have named parameters that do not need to be in order and get delegated out perfectly to their functions.  

The first 4 are all part of the same code block, so here's an example:
	
	$i = input_button('save');

	// first step is the type of event
	$i->click();
	$i->keyup();

	// next step is a list of actions
	
	// this defines: make an instance of the class User and call the function new_admin
	$call = call('User', 'new_admin');
	$call = call('User', 'new_user');

	// now on click both these actions will be performed
	$i->click([
		$new_admin,
		$new_user,
		])

	// we need to be able to pass arguments
	// there are multiple ways to do this; what syntax do you prefer?
	$i->click([
		// one parameter functions
		call('User', $constructor)->chain('new_admin', 'Blake'),
		
		// functions with multiple parameters
		call('User', [$args])->chain('new_admin', ['Blake']),
		
		// call with a separate chain, cleaner if arguments aren't common
		call('User', 'new_admin')->args([$args], ['Blake'])
		
		// .. with one param
		call('User', 'new_admin')->args_one($constructor, 'Blake')
		])

	// we also need to be able to pipe functions one to the next, with additional params
	// piping can happen with either classes or plain functions
	$i->click([
		// pipe to class
		call('User')->chain('new_admin')->pipe('UserView', 'display')

		// pipe to function
		call('User', 'new_admin')->pipe_fn('serialize')

		// alternative chain syntax
		call('User', 'new_admin -> launchView -> display')

		// .. with params
		call('User', 'new_admin -> launchView -> display')
			->args([], ['partial'], ['as_table'])

		// pipe style
		call('User', 'new_admin | serialize | print_table')
		])

	// since php for instance can use get_class(), many times we do not need to write out the class name and can instead do:
	$i->click([
		// from within User
		call($this, 'new_admin')

		// or..
		$this->call('new_admin')
		])

	// lastly, lets demonstrate the return to JS for display, as well as calling before and after functions
	$i->click([
		$this->call('new_admin')
			->before('confirm')
			->html('.user-view'),
		
		$this->call('new_user')
			->after('say_success')
			->prepend('.my-div')
		])->after('do_something')
		// non-blocking in terms of what div gets refreshed, by default, otherwise, something like
		->same_time()

	// note that the order of the chaining methods in the above examples is not important

This gives us a pretty specific, yet very simple way to define behavior for any given element on the fly without switching around files, and as we think about it. Also, by defining the specification in these objects we can free ourselves to clarifying their implementation later, since they are simply objects containing strings and arrays for now.

This brings us to the other points, of receiving user data and filtering properly.
The first, basic, assumption, is that I can either receive by default, or specify to receive, the data for the surrounding form of the element on which an event has been performed. With jQuery, this need not even be a form which is highly convenient since forms can't nest and introduce unwanted side effects on a page not meant to refresh. Furthermore, we may want to receive data from the whole page, or other specific wrappers, by selector.

Now, how does this data get received? It is too loose to simply throw it in with everything to the first parameter. There are two suggestions, which are not exclusive. One is that the data simply remains in $_REQUEST or a global static version with some degree of sanity, say, Request::$data. The second version could also have functions such as Request::strip_with_prefix('row_'); The other way, again, which can be used in conjuction, is to have a function / style for properly receiving and cleaning data and for that matter the function calls themselves. First, we want a global version. In this, there are two styles of receiving the data. One is parameter matching. The second is explicitly, through a function.

	class Call {

		// this means that the function can receive something from the post named 'level'
		// and use it as its first param
		public $allow = [
			'new_admin'=>[
				'level'=>'',
				// OR even specify a required data type, or default parameter
				'level'=>0
				]
			];

		function new_admin($level = 0) {}
		}

In the above example, $level might first be specified as so:

	$i->click([
		call('User', 'new_admin')->args([], 1)
		])

However in the case that this element is present at the time of the form submit:

	<input name='level' value='2'>

It would override the default parameter during the post and be what gets passed to the function.
	
Optionally, we could block this behavior to make the parameter call explicit:

	$i->click([
		call('User', 'new_admin')->final_args([], 1)
		])

The next method of sanitizing is through a function:

	class Call {

		// here $fn is the array of chains
		// $args is the array of args for each chain
		// $data is the user submitted data
		function my_allow($fn, $args, $data) {
			if ($fn == 'new_admin') {
				$level = isset($data['level']) ? $data['level'] : $args['level'];
				$this->new_admin($level);
				}
			}

		// callback style
		public $allow = [
			'new_admin'=>'handle_new_admin'
			'new_user'=>function ($args, $data) {
				},
			]

		function handle_new_admin($args, $data) {
			}

		// the benefit to this style would be to specify a handler functions for
		// specific type of functions which all have the same behavior, say for instance
		// receiving a pure $data array
		// thus it could also be written as so
		public $handler = [
			'handle_data'=>['new_admin', 'new_user', 'new_*']
			]

		function handle_data($args, $data, $fn) {
			$this->id = $data['id'];
			$data = array_map('strtoupper', $data);
			$this->$fn($data);
			}

		// one might even specify this at large with a wildcard
		public $fn_pattern = [
			'new_*'=>'handle_data'
			]

		// one might also separate the function calling, and the data filtering layer
		public $fn_allow = [
			// any function matching this name allowed
			'new_*'
			];
		}

The submitted data and function calls must pass through both the $allow property and the my_allow function, in that order.

Another way to indicate a function is allowed would be to extend an Allow class.

	class Me extends Allow

A few other things need to be considered:
- The ability to call static functions.
- The ability to call global functions.

These should all have general rules as well as extensible rules.

Since both sides of the coin are defined on the server (though ultimately comes back from the user), if you wanted to get really granular you could restrict the page from even rendering if it is attempting to specify a return function call which is not allowed! Otherwise you could simply inform if it is not found / allowed.. after the fact.

