<?php
$begin= microtime(true);
error_reporting(-1);
ini_set('display_errors', 'stdout');

define('is_cli', empty($_SERVER['SERVER_PROTOCOL']));
define('site_path', '/var/www/html/site-monitor/');

define('dbhost', 'localhost');
define('dbname', 'site-monitor');
define('dbuser', 'site-monitor');
define('dbpassword', 'site-monitor');

$controller= str_replace('.php', '', basename($_SERVER['SCRIPT_FILENAME']));

require_once('inc/functions.php');
require_once('inc/functions.db.php');
require_once('inc/dbo.php');

$sites= getSites();