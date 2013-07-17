<?php
$begin= microtime(true);
error_reporting(-1);
ini_set('display_errors', 'stdout');

require_once('conf/app.php');

$controller= str_replace('.php', '', basename($_SERVER['SCRIPT_FILENAME']));
$uri= isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : false;

date_default_timezone_set('America/Montreal');

require_once('inc/functions.php');
require_once('inc/functions.db.php');
require_once('inc/dbo.php');

$sites= getSites();