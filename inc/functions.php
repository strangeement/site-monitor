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
		$benchmarks= query_db_assoc($sql, $params);
		$benchmarks= array_reverse($benchmarks);
	}
	
	$timekeys= array();
	
	$benchmarks_sorted= array();
	foreach($benchmarks as $benchmark) {
		if(!isset($benchmarks_sorted[$benchmark['site']])) {
			$benchmarks_sorted[$benchmark['site']]= array();
		}
		
		$timekey= date('Y-m-d H:i', $benchmark['created_at']);
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

function debug($var) {
	if(!is_cli) {
		echo '<pre>' . print_r($var, true) . "</pre>\n";
	} else {
		if(is_string($var)) {
			echo "{$var}\n";
		} else {
			var_dump($var);
		}
	}
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