<?php
function getMedianDistribution($site=null) {
	if($site) {
		$count= intval(query_db_value("select count(*) from `benchmark` where `site`=:site", array('site' => $site['code'])));
	} else {
		$count= intval(query_db_value("select count(*) from `benchmark`"));
	}
	
	$percentile_50th= getPercentile($site, 50);
	$percentile_60th= getPercentile($site, 60);
	$percentile_70th= getPercentile($site, 70);
	$percentile_80th= getPercentile($site, 80);
	$percentile_90th= getPercentile($site, 90);
	$percentile_95th= getPercentile($site, 95);
	$percentile_98th= getPercentile($site, 98);
	$percentile_99th= getPercentile($site, 99);
	
	return array(
		50 => $percentile_50th,
		60 => $percentile_60th,
		70 => $percentile_70th,
		80 => $percentile_80th,
		90 => $percentile_90th,
		95 => $percentile_95th,
		98 => $percentile_98th,
		99 => $percentile_99th
	);
}

function getPercentile($site, $percentile) {
	$median= 0;
	if($site) {
		$avg= intval(query_db_value("select avg(`median`) from `benchmark` where `site`=:site", array('site' => $site['code'])));
		$total_count= intval(query_db_value("select count(*) from `benchmark` where `site`=:site", array('site' => $site['code'])));
		$count= intval(query_db_value("select count(*) from `benchmark` where `site`=:site and `median`<:median", array('site' => $site['code'], 'median' => $median)));
	} else {
		$avg= intval(query_db_value("select avg(`median`) from `benchmark`"));
		$total_count= intval(query_db_value("select count(*) from `benchmark`"));
		$count= intval(query_db_value("select count(*) from `benchmark` where `median`<:median", array('median' => $median)));
	}
	
	while($count/$total_count < $percentile/100) {
		$median= $median+25;
		
		if($site) {
			$count= intval(query_db_value("select count(*) from `benchmark` where `site`=:site and `median`<:median", array('site' => $site['code'], 'median' => $median)));
		} else {
			$count= intval(query_db_value("select count(*) from `benchmark` where `median`<:median", array('median' => $median)));
		}
	}
	
	return $median;
}

function getSites() {
	$cache_key= "sites";
	if(apc_exists($cache_key)) {
		return apc_fetch($cache_key);
	}
	
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
	
	try {
		$sites_rows= query_db_assoc($sql, null, false, true);
	} catch(Exception $ex) {
		return false;
	}
	
	foreach($sites_rows as $site) {
		if(!empty($site['urls'])) $site['urls']= unserialize($site['urls']);
		$sites[$site['code']]= $site;
	}
	
	apc_store($cache_key, $sites, 300);
	
	return $sites;
}

function insertAlert($site, $type, $url, $message) {
//	Do not record the alert more than once per period
	if(intval(query_db_value("select count(*) from `alert` where `site`=:site and `type`=:type and `url`=:url and `created_at`>(unix_timestamp()-60*30)", array('site' => $site, 'type' => $type, 'url' => $url))) > 0) {
		return;
	}
	
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

function insertSite($code, $domain, $alert_threshold, $ssl, $urls) {
	$sql= "insert into `site` (`code`, `domain`, `alert_threshold`, `ssl`, `urls`) values (:code, :domain, :alert_threshold, :ssl, :urls)";
	return query_db($sql, array(
		'code' => $code,
		'domain' => $domain,
		'alert_threshold' => $alert_threshold,
		'ssl' => $ssl,
		'urls' => $urls
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

function updateSite($id, $code, $domain, $alert_threshold, $ssl, $urls) {
	$sql= "update `site` set `code`=:code, `domain`=:domain, `alert_threshold`=:alert_threshold, `ssl`=:ssl, `urls`=:urls where `id`=:id";
	return query_db($sql, array(
		'code' => $code,
		'domain' => $domain,
		'alert_threshold' => $alert_threshold,
		'ssl' => $ssl,
		'urls' => serialize($urls),
		'id' => $id
	));
}