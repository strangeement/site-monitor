<?php
function getSites() {
	$sql= "select *,
		(select count(*) from `code` where `code`.`site`=`site`.`code` and `code` <> 200) as `code_errors`,
		(select `found` from `validation` where `validation`.`site`=`site`.`code` and `found` > 1 or `error` is not null order by `created_at` limit 1) as `validation_errors`,
		(select avg(`median`) from `benchmark` where `benchmark`.`site`=`site`.`code`) as `median`,
		(select avg(`median`) from `benchmark` where `benchmark`.`site`=`site`.`code` and `created_at`>unix_timestamp()-60*5) as `5m`,
		(select avg(`median`) from `benchmark` where `benchmark`.`site`=`site`.`code` and `created_at`>unix_timestamp()-60*15) as `15m`,
		(select avg(`median`) from `benchmark` where `benchmark`.`site`=`site`.`code` and `created_at`>unix_timestamp()-60*30 and `created_at`<unix_timestamp()-60*15) as `30m`,
		(select avg(`median`) from `benchmark` where `benchmark`.`site`=`site`.`code` and `created_at`>unix_timestamp()-60*45 and `created_at`<unix_timestamp()-60*30) as `45m`,
		(select avg(`median`) from `benchmark` where `benchmark`.`site`=`site`.`code` and `created_at`>unix_timestamp()-60*60 and `created_at`<unix_timestamp()-60*45) as `1h`,
		(select avg(`median`) from `benchmark` where `benchmark`.`site`=`site`.`code` and `created_at`>unix_timestamp()-60*60*3 and `created_at`<unix_timestamp()-60*60) as `3h`,
		(select avg(`median`) from `benchmark` where `benchmark`.`site`=`site`.`code` and `created_at`>unix_timestamp()-60*60*12 and `created_at`<unix_timestamp()-60*60*3) as `12h`,
		(select avg(`median`) from `benchmark` where `benchmark`.`site`=`site`.`code` and `created_at`>unix_timestamp()-60*60*24*1 and `created_at`<unix_timestamp()-60*60*12) as `1d`,
		(select avg(`median`) from `benchmark` where `benchmark`.`site`=`site`.`code` and `created_at`>unix_timestamp()-60*60*24*3 and `created_at`<unix_timestamp()-60*60*24*1) as `3d`,
		(select avg(`median`) from `benchmark` where `benchmark`.`site`=`site`.`code` and `created_at`>unix_timestamp()-60*60*24*7 and `created_at`<unix_timestamp()-60*60*24*3) as `7d`,
		(select avg(`median`) from `benchmark` where `benchmark`.`site`=`site`.`code` and `created_at`>unix_timestamp()-60*60*24*30 and `created_at`<unix_timestamp()-60*60*24*7) as `30d`,
		(select avg(`median`) from `benchmark` where `benchmark`.`site`=`site`.`code` and `created_at`>unix_timestamp()-60*60*24*60 and `created_at`<unix_timestamp()-60*60*24*30) as `60d`,
		(select avg(`median`) from `benchmark` where `benchmark`.`site`=`site`.`code` and `created_at`>unix_timestamp()-60*60*24*120 and `created_at`<unix_timestamp()-60*60*24*60) as `120d` from `site` order by `code`";
	
	$sites= array();
	$sites_rows= query_db_assoc($sql);
	foreach($sites_rows as $site) {
		$sites[$site['code']]= $site;
	}
	
	return $sites;
}

function insertAlert($site, $type, $url, $message) {
//	Do not send the alert more than once per hour
//	if(intval(query_db_value("select count(*) from `alert` where `site`=:site and `type`=:type and `url`=:url and `created_at`>(unix_timestamp()-60*60)", array('site' => $site, 'type' => $type, 'url' => $url))) > 0) {
//		return;
//	}
	
	$sent= mail("richardvallee@gmail.com", 'Site monitor alert', $message);
	
	$sql= "insert into `alert` (`site`, `type`, `url`, `message`, `created_at`) values (:site, :type, :url, :message, unix_timestamp())";
	return query_db($sql, array(
		'site' => $site,
		'type' => $type,
		'url' => $url,
		'message' => $message
	));
}

function insertBenchmark($site, $url, $median, $min, $max) {
	$sql= "insert into `benchmark` (`site`, `url`, `median`, `min`, `max`, `created_at`) values (:site, :url, :median, :min, :max, unix_timestamp())";
	return query_db($sql, array(
		'site' => $site,
		'url' => $url,
		'median' => $median,
		'min' => $min,
		'max' => $max
	));
}

function insertResponseCode($site, $url, $code) {
	if(intval($code) === 200) {
		return;
	}
	
	$sql= "insert into `code` (`site`, `url`, `code`, `created_at`) values (:site, :url, :code, unix_timestamp())";
	return query_db($sql, array(
		'site' => $site,
		'url' => $url,
		'code' => $code
	));
}

function insertValidationErrors($site, $url, $found, $error) {
	$sql= "insert into `validation` (`site`, `url`, `found`, `error`, `created_at`) values (:site, :url, :found, :error, unix_timestamp())";
	return query_db($sql, array(
		'site' => $site,
		'url' => $url,
		'found' => $found,
		'error' => $error
	));
}