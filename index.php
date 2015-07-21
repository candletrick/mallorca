<?php
// tool kit
require 'm2lib/Kit.php';

// autoload
require 'm2lib/Autoload.php';

// db, configuration
require '../protected/mallorca2_local.php';

// session
session_start();

function website() {
	return array(
		'.main'=>call('Note\View', 'my_display')
		);
	}

\Request::respond();

include 'html.php';

