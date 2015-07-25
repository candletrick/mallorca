<?php
namespace Path;

/**
	A navigation menu.
	*/
class Nav
	{
	/** Array of links. */
	public $links = array();
	
	/** Global key pair parameter to place on links. */
	public $keypair = array();

	/** Alphabetically sort. */
	public $alpha = false;

	function __toString()
		{
		return $this->display();
		}
	
	/**
		Call this method to immediately start chaining.
		\return Nav object.
		*/
	static function chain()
		{
		return new Nav();
		}
	
	/**
		*/
	public function keypair($keypair)
		{
		$this->keypair = $keypair;
		return $this;
		}

	/**
		Sort links alphabetically.
		*/
	public function alpha()
		{
		$this->alpha = true;
		return $this;
		}

	/**
		Conditional add.
		*/
	public function add_if($bool, $name, $path, $params = array(), $class = '')
		{
		if ($bool) $this->add($name, $path, $params, $class);
		return $this;
		}

	/**
		A chaining method.  Add a link to the object and return it back.
		\param	string	$name	The display name.
		\param	string	$path		The full path.
		\param	array	$params	URL parameters.
		\param	string	$class		CSS Class.
		\return This.
		*/
	public function add($name = 'Link', $path = 'event/list', $params = array(), $class = 'big')
		{
		if (! $params) $params = array();
		$params = array_merge($this->keypair, $params);
		$this->links[$name] = "<li" . (\Path::$q == $path ? " class='selected'" : '') . ">
		<a class='$class' href='" . \Path::base_to($path, $params) . "'>$name</a></li>";
		return $this;
		}
		
	/**
		Add a plain <li> item.
		\param	string	$a	Full anchor tag, or really any other html for that matter.
		\return This.
		*/
	function addpure($a)
		{
		$this->links[] = "<li>$a</li>";
		return $this;
		}
		
	function display($class = 'action_nav')
		{
		if ($this->alpha) ksort($this->links);
		$s = "<ul class='$class'>" . implode($this->links) . "</ul>";
		return $s;
		}
	}
