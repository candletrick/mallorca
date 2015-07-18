<?php
require 'mallorca/Kit.php'; // Kit
require 'mallorca/Autoload.php'; // Autoload
require '../protected/mallorca_local.php'; // Db and website() function

echo \Db\Schema\All::my_display();
