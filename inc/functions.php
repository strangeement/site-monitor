<?php
function benchmark($url) {
	$sess_id= uniqid();
	
	$ab= "ab -n 1 -c 1 -C PHPSESSID={$sess_id} \"{$url}\"";
	$ab= shell_exec($ab);
	
	if(preg_match('/Total:\s+([0-9]+)\s+([0-9]+)\s+([0-9\.]+)\s+([0-9]+)\s+([0-9]+)/', $ab, $total)) {
		$median= intval($total[4]);
		$min= intval($total[1]);
		$max= intval($total[5]);
		
		return array(
			'median' => $median,
			'min' => $min,
			'max' => $max
		);
	}
	
	return false;
}

function benchmarks($timeframe='1h', $site=null) {
	global $sites;
	
	$from= 60*60;
	if(preg_match('/^\d+$/', $timeframe)) {
		$from= intval($timeframe)*60;
	} else if(preg_match('/(\d+)(\w)/i', $timeframe, $timeframe_match)) {
		$value= $timeframe_match[1];
		$unit= $timeframe_match[2];
		if($unit === 'm') {
			$from= $value * 60;
		} else if($unit === 'h') {
			$from= $value * 60 * 60;
		} else if($unit === 'd') {
			$from= $value * 60 * 60 * 24;
		} else if($unit === 'w') {
			$from= $value * 60 * 60 * 24 * 7;
		} else if($unit === 'm') {
			$from= $value * 60 * 60 * 24 * 30;
		}
	}
	
	$sql= "select * from `benchmark` where " . ($site ? "`site`=:site and" : '') . " `created_at` > unix_timestamp()-{$from} order by `created_at` desc";
	
	if($site) {
		$benchmarks= query_db_assoc($sql, array('site' => $site));
	} else {
		$benchmarks= query_db_assoc($sql);
		$benchmarks= array_reverse($benchmarks);
	}
	
	$timekeys= array();
	
	$time_resolution= 'Y-m-d H:i';
	if($from >= 60*60*24) {
		$time_resolution= 'Y-m-d H';
	}
	
	if($from >= 60*60*24*7) {
		$time_resolution= 'Y-m-d';
	}
	
	$benchmarks_sorted= array();
	foreach($benchmarks as $benchmark) {
		if(!isset($benchmarks_sorted[$benchmark['site']])) {
			$benchmarks_sorted[$benchmark['site']]= array();
		}
		
		$timekey= date($time_resolution, $benchmark['created_at']);
		if(!in_array($timekey, $timekeys)) {
			array_push($timekeys, $timekey);
		}
		
		$benchmarks_sorted[$benchmark['site']][$timekey]= intval($benchmark['median']);
	}
	
	foreach($benchmarks_sorted as &$benchmark) {
		foreach($timekeys as $timekey) {
			if(!isset($benchmark[$timekey])) {
				$benchmark[$timekey]= 0;
			}
		}
		
		ksort($benchmark, SORT_STRING);
	}
	
	if($site) {
		return $benchmarks_sorted[$site];
	}
	
	ksort($benchmarks_sorted);
	
	return $benchmarks_sorted;
}

function checkAlerts() {
	$error_codes= query_db_assoc("select * from `code` where `code` <> 200 and `created_at` > unix_timestamp()-60");
	foreach($error_codes as $error_code) {
		insertAlert($error_code['site'], 'error', $error_code['url'], "{$error_code['url']} returned {$error_code['code']}");
	}
	
	$slow_sites= query_db_assoc("select *, avg(`median`) as `median_avg` from `benchmark` where `created_at`>unix_timestamp()-60*5 group by `site` having `median_avg` > 500 order by `site`");
	
	global $sites;
	foreach($slow_sites as $slow_site) {
		if(!isset($sites[$slow_site['site']])) {
			continue;
		}
		
		if(intval($slow_site['median_avg']) > intval($sites[$slow_site['site']]['alert_threshold'])) {
			insertAlert($slow_site['site'], 'slow', $slow_site['url'], "{$slow_site['url']} has an average of " . intval($slow_site['median_avg']) . "ms for the last 5 minutes");
		}
	}
}

function checkInstall() {
	global $config, $dbconf;
	
	$connection= mysql_connect($dbconf['dbhost'], $dbconf['dbuser'], $dbconf['dbpassword']);
	if(!$connection) {
		redirect("/install");
	}
	
	$db= mysql_select_db($dbconf['dbname'], $connection);
	if(!$db) {
		redirect("/install");
	}
}

function compressBenchmarks() {
	global $sites;
	
	foreach($sites as $site) {
		compressSiteBenchmarks($site, 300);
	}
}

function compressSiteBenchmarks($site, $interval) {
	$benchmarks= query_db_assoc("select `benchmark`.*, round(`created_at`/{$interval}) as `interval` from `benchmark` where `site`=:site and unix_timestamp()-`created_at` > 60*60 order by `interval`", array('site' => $site['code']));
	
	debug("Compressing " . count($benchmarks) . " benchmarks for {$site['domain']}");
	
	$n= count($benchmarks);
	
	$intervals= array();
	foreach($benchmarks as $benchmark) {
		if(!isset($intervals[$benchmark['interval']])) {
			$intervals[$benchmark['interval']]= array();
		}
		
		$intervals[$benchmark['interval']][]= $benchmark;
	}
	
	foreach($intervals as $benchmarks) {
		while(count($benchmarks) > 1) {
			$benchmark= reset($benchmarks);
			$merge_benchmark= array_pop($benchmarks);
			
			$new_min= (intval($benchmark['min']) + intval($merge_benchmark['min'])) / 2;
			$new_max= (intval($benchmark['max']) + intval($merge_benchmark['max'])) / 2;
			$new_median= (intval($benchmark['median']) + intval($merge_benchmark['median'])) / 2;
			
			$sql= "update `benchmark` set `min`=:min, `max`=:max, `median`=:median where `id`=:id";
			query_db($sql, array(
				'min' => $new_min,
				'max' => $new_max,
				'median' => $new_median,
				'id' => $benchmark['id']
			));
			
			query_db("delete from `benchmark` where `id`=:id", array('id' => $merge_benchmark['id']));
			$n--;
		
			if($n % 100 === 0) {
				debug("{$n} remaining");
			}
		}
	}
}

function debug($var) {
	if(!is_cli) {
		echo '<pre>' . print_r($var, true) . "</pre>\n";
	} else {
		if(is_string($var)) {
			echo "{$var}\n";
		} else {
			var_dump($var);
		}
		
		flush();
	}
}

function displayPagination($id, $prefix, $qs, $page, $page_size, $list_size) {
	require_once('inc/pagination.php');
	
	if(is_null($qs) || !is_array($qs)) {
		$qs= array();
	}
	
	$qs['page']= '%';
	
	$p= new pagination($id);
	$p->Items($list_size);
	$p->limit($page_size);
	$p->changeClass('pagination');
	$p->currentPage($page);
	 
	$p->urlFriendly();
	$p->target("/{$prefix}" . (is_null($qs) ? null : '?' . str_replace('%25', '%', http_build_query($qs))));
	return $p->getOutput();
}

function download_file($url, $path=false, $skip_existing=false, $referer_url=null) {
	$ch = curl_init();
	
	if(!$path) {
		$path= site_path . "cache/" . preg_replace('/[^a-z0-9_-]/i', '', $url);
	}
	
	if(file_exists($path) && ($skip_existing === true || (intval($skip_existing) > 1 && time()-filemtime($path) < intval($skip_existing)*60))) {
//		debug("Get {$path} from cache");
		return $path;
	}
	
	$out = fopen($path, 'wb');
	
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_REFERER, dirname($url));
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FILE, $out);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64; rv:7.0.1) Gecko/20100101 Firefox/7.0.1");
	
	if(isset($referer_url)) {
		curl_setopt($ch, CURLOPT_REFERER, $referer_url);
	}
	
	$result = curl_exec($ch);
	$error = curl_error($ch);
	
	curl_close($ch);
	fclose($out);
	
	return $path;
}

function get_url_content($url, $path=false, $skip_existing=true) {
	$path= download_file($url, $path, $skip_existing);
	return file_get_contents($path);
}

function get_url_code($url) {
	$ch= curl_init();
	
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64; rv:7.0.1) Gecko/20100101 Firefox/7.0.1");
	
	$code= false;
	$response= curl_exec($ch);
	
	if($response) {
		$status= curl_getinfo($ch);
		if($status && isset($status['http_code'])) {
			$code= $status['http_code'];
		}
	}
	
	$error= curl_error($ch);
	curl_close($ch);
	
	return $code;
}

function plural($nb, $singular, $plural) {
	return $nb > 1 ? $plural : $singular;
}

function redirect($path) {
	
	
	header("Location: {$path}");
	exit;
}

function validateHTML($url) {
	global $debug, $errors;
	
	$html= get_url_content($url, false, false);
	if(!$html) {
		return array('error' => "Empty content returned for {$url}");
	} else if(strpos($html, 'Fatal error') !== false) {
		if($debug) debug("Fatal error found for {$page} ({$url})");
		return array('error' => "Fatal error found for {$url}");
	}
	
	$tidy = new tidy();
	$tidy_options= array(
		'new-blocklevel-tags' => 'header,nav,aside,footer'
	);
	$tidy->parseString($html, $tidy_options, 'utf8');
	$tidy->diagnose();
	$tidy_errors= $tidy->errorBuffer;
	
	if(preg_match('/([0-9]+) errors were found/', $tidy_errors, $found)) {
		$found= intval($found[1]);
		if($found > 0) {
			return array('found' => $found, 'error' => $tidy_errors);
		}
	}
	
	return true;
}

function validateJSON($url, $page) {
	global $debug;
	if($debug) debug("Validating JSON for {$page} ({$url})");
	
	$json= get_url_content($url, false, false);
	$json= json_decode($json);

	if(!$json) {
		global $errors;
		$errors[]= "<a href=\"{$url}\">{$page}</a> JSON is invalid or empty";
		return false;
	}
	
	return true;
}