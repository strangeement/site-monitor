<?php
chdir($_SERVER['DOCUMENT_ROOT']);
require_once('inc/boot.php');

foreach($sites as $site) {
	if(query_db_object("select * from `site` where `code`=:code", array('code' => $site['code']))) {
		$sql= "update `site` set `domain`=:domain, `ssl`=:ssl, `urls`=:urls where `code`=:code";
		query_db($sql, array(
			'code' => $site['code'],
			'domain' => $site['domain'],
			'ssl' => isset($site['ssl']) && $site['ssl'],
			'urls' => serialize($site['benchmark'])
		));
		continue;
	}
	
	$sql= "insert into `site` (`code`, `domain`, `ssl`, `urls`, `created_at`) values (:code, :domain, :ssl, :urls, unix_timestamp())";
	query_db($sql, array(
		'code' => $site['code'],
		'domain' => $site['domain'],
		'ssl' => isset($site['ssl']) && $site['ssl'],
		'urls' => serialize($site['benchmark'])
	));
}