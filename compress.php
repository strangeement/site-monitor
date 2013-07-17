<?php
require_once('inc/boot.php');

debug("Compress");

$before= intval(query_db_value("select count(*) as `count` from `benchmark`"));

$site= false;
if($argc === 2) {
	$site= $argv[1];
}

$interval= $argc === 3 && isset($argv[2]) && intval($argv[3]) > 0 ? intval($argv[2]) : 300;

if(!$site) {
	die("Usage: php compress.php sitecode\n");
} else if(!isset($sites[$site])) {
	die("Invalid site code: {$site}\n");
} else {
	$site= $sites[$site];
}

debug("Compress site {$site['code']} at interval {$interval}\n");

compressSiteBenchmarks($site, $interval);

$after= intval(query_db_value("select count(*) as `count` from `benchmark`"));
debug("Compressed " . ($before-$after) . " benchmarks ({$before} => {$after})");