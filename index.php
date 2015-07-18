<?php
// tool kit
require 'mallorca/Kit.php';

// autoload
require 'mallorca/Autoload.php';

// db, configuration
require '../protected/mallorca_local.php';

// session
session_start();

function website() {
	return array(
		'.main'=>call('Note\View', 'my_display')
		);
	}

\Request::respond();

include 'html.php';

