<?php
require 'm2lib/Kit.php'; // Kit
require 'm2lib/Autoload.php'; // Autoload
require '../protected/mallorca2_local.php'; // Db and website() function

echo \Db\Schema\All::my_display();
