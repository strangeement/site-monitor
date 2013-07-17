<?php
require_once('inc/boot.php');

if(is_cli) {
	debug("Site monitor");
} else {
	include('tpl/header.php');
}

foreach($sites as $site) {
	if(is_cli) debug("\nMonitoring site {$site['domain']}");
	
	$domain= $site['domain'];
	
	if(is_cli) debug("Benchmark");
	if(isset($site['urls']) && !empty($site['urls'])) {
		foreach($site['urls'] as $url) {
			$benchmark= true;
			if(is_array($url)) {
				$benchmark= isset($url['benchmark']) && $url['benchmark'];
				$url= $url['url'];
			}
			
			if(!preg_match('/https?:\/\//i', $url)) {
				$url= "http://{$domain}{$url}";
			} else if(strpos($url, '$domain') !== false) {
				$url= str_replace('$domain', $domain, $url);
			}
			
			$code= get_url_code("{$url}");
//			debug("Response for {$url}: {$code}");
			insertResponseCode($site['code'], $url, $code);
			
			if($benchmark) {
				$benchmark= benchmark($url);
				if($benchmark) {
					debug("Benchmark for {$url}: {$benchmark['median']}ms");
					insertBenchmark($site['code'], $url, $benchmark['median'], $benchmark['min'], $benchmark['max']);
					debug("Current run time: " . intval((microtime(true)-$begin)*1000) . "ms.");
				}
			}
		}
	}
	
	if(is_cli) debug("Validate HTML");
	if(isset($site['validate-html']) && !empty($site['validate-html'])) {
		foreach($site['validate-html'] as $alias => $url) {
			if(!preg_match('/https?:\/\//i', $url)) {
				$url= "http://{$domain}{$url}";
			} else if(strpos($url, '$domain') !== false) {
				$url= str_replace('$domain', $domain, $url);
			}
			
			$validation= validateHTML($url);
			if($validation !== true) {
				$found= isset($validation['found']) ? intval($validation['found']) : null;
				$error= isset($validation['error']) ? $validation['error'] : null;
				
				if(is_cli) debug("HTML validation failed for {$url}: found {$found} errors");
//				if($error) {
//					if(is_cli) debug($error);
//				}
				
				insertValidationErrors($site['code'], $url, $found, $errors);
			}
		}
	}
}

if(is_cli) {
	$ms= intval((microtime(true)-$begin)*1000);
	$completed_in= "{$ms}ms";
	if($ms > 1000) {
		$completed_in= ($ms/1000) . 's';
	}
	
	debug("\nCompleted in {$completed_in}");
} else {
	include('tpl/footer.php');
}