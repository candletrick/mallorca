<?php
/**
	MyIndex.
	This is a simple routing system.

	It's a way of combining controllers and views.

	lib/ is the base folder for the entire application.
	After that, the file structure mimics path structure.
	Meaning that to go to path: my/example/path would relate to lib/My/Example/Path.php
	* Note: it's fine for classes not meant to be accessed directly to still exist side by side in the lib folder.

	My/Example/Path.php need only be a class with a function called my_display().
	Typically, it would extend some type of "module", a lookup, editor, comment viewer, calendar,
	etc (each having their own display function), and simply fill in the necessary variables.
	
	To add more power to the system, each directory is permitted to have an Index.php file,
	who's constructor and display function would be called first, allowing you to "wrap"
	around the subdirectories with logic and formatting.

	The Index.php files are not mandatory, and those steps will merely be skipped if the don't exist.
	However, in the example above, the full playing out might look something like this:

	Start by constructing My/Index.php,
	with child My/Example/Index.php,
	with child My/Example/Path.php.

	Call My\Index::my_display(), which can optionally call $this->child->my_display() down the chain.

	Furthermore, the subdirectories / classes can choose to redefine their parent classes and / or functions.

	The goal is to really keep as much control as possible in the destination, and as much duplication
	away and above, in the indexes.

	Index.php's can also define data sets to be used among all their children.
	*/
class MyIndex
	{
	/** Full name along the path. */
	public $name;

	/** Name token along the path. */
	public $token;
	
	/** Array of remaining paths.  For example is name is "my", paths is array('example', 'path') */
	public $paths;

	/** Parent MyIndex object. */
	public $parent;

	/** Url path prefix.  When at My/Example/Index.php, would be "my/example" */
	public $path;

	/** Potential class name. For instance My\Example.  Won't necessarily exist. */
	public $class;

	/** Potential index class name.  For instance My\Example\Index. */
	public $index;

	/** Child object, either an Index.php, or the final class with whatever it extends. */
	public $child;

	/** Path ending synonyms. */
	static public $synonym = array(
		'edit'=>'create',
		);

	/**
		Walk down and initialize the children.
		*/
	public function __construct($name, $paths = array(), $parent = null, $child = null, $params = array())
		{
		$this->name = $name;
		$this->token = $name;
		$this->paths = $paths;
		$this->parent = $parent;

		$this->path = ($this->parent ? $this->parent->path . '/' : '') . $this->name;
		$this->path = ($this->parent ? $this->parent->path . '/' : '') . $this->name;
		$this->class = $class = $this->class_from($this->name);
		$this->index = $index = $this->class_from($this->name, 'index');
			
		$this->keytype = 'id';
		if ($this->parent) {
			$this->name = $this->parent->name . '_' . $this->name;
			$this->keyname = $this->parent->name . '_' . $this->keytype;
			// request / mallorca style
			// $json_get = req('json_get');
			// $this->id = id_zero(! empty($json_get) ? is($json_get, $this->keyname) : req($this->keyname));
			$this->id = id_zero(\Request::get($this->keyname, req($this->keyname)));
			$this->key_pair = array($this->keyname=>$this->id);
			}
		else {
			$this->key_pair = array();
			}
		
		$this->data = $this->my_data() ?: array();

		$next = array_shift($paths);

		if ($child) {
			$this->child = $child;
			}
		else if (empty($this->paths)) {
			$synonym = $this->class_from(is(self::$synonym, $this->name));
			if (class_exists($class)) {
				$this->child = new $class($this, $params);
				}
			else if (class_exists($synonym)) {
				$this->child = new $synonym($this, $params);
				}
			else if (class_exists($index)) {
				$this->child = new $index('emptiness', array(),$this, null, $params);
				}
			}
		else if (class_exists($index)) {
			$this->child = new $index($next, $paths,$this, null, $params);
			}
		else {
			$this->child = new \MyIndex($next, $paths, $this, null, $params);
			}

		// check permissions
		$this->my_allow();

		/* * //debugging
		echo "<pre>";
		print_r(array(
			'class_type'=>get_class($this),
			'name'=>$name,
			'paths'=>implode(',', $this->paths),
			'path'=>$this->path,
			'class'=>$this->class,
			'index'=>$this->index,
			'parent'=>$this->parent->name,
			'child'=>get_class($this->child),
			));
		echo "</pre>";
		/* */
		}

	public function get($key)
		{
		return array_key_exists($key, $this->data) ? $this->data[$key] : "$key is not set.";
		}

	/**
		*/
	public function adjust_path()
		{
		$this->parent->child = $this;
		}

	/**
		Make class name string from array of arguments.
		*/
	private function class_from()
		{
		$args = func_get_args();
		$end = implode("\\", array_map('_to_camel', $args));
		return ($this->parent ? $this->parent->class : '')
		. ($this->name ? '\\' : '')
		. $end;
		}

	/**
		*/
	public function dive($name, $alt)
		{
		$child = $this;
		while (isset($child->child)) $child = $child->child;

		while (1) {
			if (is_callable(array($child, $name))) return $child->$name();
			else if (isset($child->parent)) $child = $child->parent;
			else {
				return $alt;
				}
			}
		}

	/* THESE FUNCTIONS MAY BE REDEFINED */

	public function my_display()
		{
		if (! $this->child) {
			return "Page does not exist.";
			}
		return $this->child->my_display();
		}

	/* Any html head code to add. */
	public function my_head()
		{
		}

	/* Any left panel code to add. */
	public function my_left()
		{
		}

	/* Redefine index above. */
	public function my_index()
		{
		}

	/* */
	public function my_data()
		{
		return array();
		}

	/**
		Use this function to control access to directories.
		*/
	public function my_allow()
		{
		}
	}
