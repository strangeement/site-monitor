<?php
require_once('inc/boot.php');

debug("Compress");

$site= 'all';
if($argc >= 2) {
	$site= $argv[1];
}

$interval= $argc >= 3 && isset($argv[2]) && intval($argv[2]) > 0 ? intval($argv[2]) : 300;
$after= $argc >= 4 && isset($argv[3]) && intval($argv[3]) > 0 ? intval($argv[3]) : 60*60;

$before= intval(query_db_value("select count(*) as `count` from `benchmark` where unix_timestamp()-`created_at` > :after",
	array('after' => $after)));

debug("Compress {$site} at interval {$interval}s. There are {$before} benchmarks after {$after}s");

if(!$site && $argc > 2) {
	die("Usage: php compress.php [sitecode] [interval]\n");
} else if(!$site && $interval) {
	$site= null;
	debug("Compress all sites at interval {$interval}\n");
} else if(!isset($sites[$site]) && $site !== 'all') {
	die("Invalid site code: {$site}\n");
} else if($site && isset($sites[$site])) {
	$site= $sites[$site];
	debug("Compress site {$site['code']} at interval {$interval}\n");
}

if($site && $site !== 'all') {
	compressSiteBenchmarks($site, $interval, $after);
} else {
	foreach($sites as $site) {
		compressSiteBenchmarks($site, $interval, $after);
	}
}

$after= intval(query_db_value("select count(*) as `count` from `benchmark` where unix_timestamp()-`created_at` > :after",
	array('after' => $after)));
debug("Compressed " . ($before-$after) . " benchmarks ({$before} => {$after})");