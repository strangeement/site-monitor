<?php
require_once('inc/boot.php');

if(is_cli) {
	debug("Site monitor cron");
} else {
	include('tpl/header.php');
}

$before= intval(query_db_value("select count(*) as `count` from `benchmark`"));
compressBenchmarks();
$after= intval(query_db_value("select count(*) as `count` from `benchmark`"));
debug("Compressed " . ($before-$after) . " benchmarks ({$before} => {$after})");