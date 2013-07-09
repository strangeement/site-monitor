<?php
function benchmarkResults($site) {
	return query_db_object("select
		(select avg(`median`) from `benchmark` where `site`=:site) as `median`,
		(select avg(`median`) from `benchmark` where `site`=:site and `created_at`>unix_timestamp()-60*15) as `15m`,
		(select avg(`median`) from `benchmark` where `site`=:site and `created_at`>unix_timestamp()-60*30) as `30m`,
		(select avg(`median`) from `benchmark` where `site`=:site and `created_at`>unix_timestamp()-60*45) as `45m`,
		(select avg(`median`) from `benchmark` where `site`=:site and `created_at`>unix_timestamp()-60*60) as `1h`,
		(select avg(`median`) from `benchmark` where `site`=:site and `created_at`>unix_timestamp()-60*60*3 and `created_at`<unix_timestamp()-60*60) as `3h`,
		(select avg(`median`) from `benchmark` where `site`=:site and `created_at`>unix_timestamp()-60*60*12 and `created_at`<unix_timestamp()-60*60*3) as `12h`,
		(select avg(`median`) from `benchmark` where `site`=:site and `created_at`>unix_timestamp()-60*60*24*1 and `created_at`<unix_timestamp()-60*60*12) as `1d`,
		(select avg(`median`) from `benchmark` where `site`=:site and `created_at`>unix_timestamp()-60*60*24*3 and `created_at`<unix_timestamp()-60*60*24*1) as `3d`,
		(select avg(`median`) from `benchmark` where `site`=:site and `created_at`>unix_timestamp()-60*60*24*7 and `created_at`<unix_timestamp()-60*60*24*3) as `7d`,
		(select avg(`median`) from `benchmark` where `site`=:site and `created_at`>unix_timestamp()-60*60*24*30 and `created_at`<unix_timestamp()-60*60*24*7) as `30d`,
		(select avg(`median`) from `benchmark` where `site`=:site and `created_at`>unix_timestamp()-60*60*24*60 and `created_at`<unix_timestamp()-60*60*24*30) as `60d`,
		(select avg(`median`) from `benchmark` where `site`=:site and `created_at`>unix_timestamp()-60*60*24*120 and `created_at`<unix_timestamp()-60*60*24*60) as `120d`", array('site' => $site));
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