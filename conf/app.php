<?php
define('is_cli', empty($_SERVER['SERVER_PROTOCOL']));
define('site_path', '/var/www/html/site-monitor/');

define('dbhost', $dbconf['dbhost']);
define('dbname', $dbconf['dbname']);
define('dbuser', $dbconf['dbuser']);
define('dbpassword', $dbconf['dbpassword']);

define('default_alert_threshold', 500);