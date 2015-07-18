
Factory creation should be wrapped in a global function, as so:

	function db() {
		return new \Db();
		}

From here, chaining is condoned as much as possible...
	
	db()->select()

	rather than

	\Db::factory()->select()



