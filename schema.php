<?php
require 'lib/Kit.php'; // Kit
require '../protected/socrates_local.php'; // Db and website() function
require 'lib/Autoload.php'; // Autoload

\Db::connect_from_config();
echo \Db\Schema\All::my_display();
