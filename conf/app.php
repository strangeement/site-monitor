<?php
define('is_cli', empty($_SERVER['SERVER_PROTOCOL']));
define('site_path', '/var/www/html/site-monitor/');

define('dbhost', 'localhost');
define('dbname', 'site-monitor');
define('dbuser', 'site-monitor');
define('dbpassword', 'site-monitor');

define('default_alert_threshold', 500);