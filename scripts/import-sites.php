<?php
chdir($_SERVER['DOCUMENT_ROOT']);
require_once('inc/boot.php');

$sites= array();
$sites_confs= glob("conf/sites/*.php");
foreach($sites_confs as $code => $site) {
	$sites[]= include($site);
}

foreach($sites as $site) {
	if(query_db_object("select * from `site` where `code`=:code", array('code' => $site['code']))) {
		$sql= "update `site` set `domain`=:domain, `ssl`=:ssl, `urls`=:urls, `alert_threshold`=:alert_threshold where `code`=:code";
		query_db($sql, array(
			'code' => $site['code'],
			'domain' => $site['domain'],
			'ssl' => isset($site['ssl']) && $site['ssl'],
			'urls' => serialize($site['benchmark']),
			'alert_threshold' => isset($site['alert_threshold']) && !empty($site['alert_threshold']) ? intval($site['alert_threshold']) : default_alert_threshold
		));
		continue;
	}
	
	$sql= "insert into `site` (`code`, `domain`, `ssl`, `urls`, `alert_threshold`, `created_at`) values (:code, :domain, :ssl, :urls, :alert_threshold, unix_timestamp())";
	query_db($sql, array(
		'code' => $site['code'],
		'domain' => $site['domain'],
		'ssl' => isset($site['ssl']) && $site['ssl'],
		'urls' => serialize($site['benchmark']),
		'alert_threshold' => isset($site['alert_threshold']) && !empty($site['alert_threshold']) ? intval($site['alert_threshold']) : default_alert_threshold
	));
}