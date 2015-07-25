
Naming Conventions
==================

	- When a class is made to be extended, then the methods which are optionally to be overwritten have the prefix my_
	For example:

	class View
		function my_display
			echo "Hi"

	class MyView extends View
		function my_display
			echo "Hello"

Factory creation should be wrapped in a global function, as so:

	function db() {
		return new \Db();
		}

From here, chaining is encouraged as much as possible...
	
	db()->select()

	rather than

	\Db::factory()->select()



