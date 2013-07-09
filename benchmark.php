<?php
$begin= time();

require_once('inc/boot.php');
$code= isset($_GET['code']) ? urldecode($_GET['code']) : false;
if(!isset($sites[$code])) {
	redirect('/?error=' . urlencode('Invalid site code'));
}

$site= $sites[$code];

$domain= $site['domain'];
$median= null;

if(is_cli) debug("Benchmark {$domain}");

if(isset($site['benchmark']) && !empty($site['benchmark'])) {
	foreach($site['benchmark'] as $alias => $url) {
		if(!preg_match('/https?:\/\//i', $url)) {
			$url= "http://{$domain}{$url}";
		} else if(strpos($url, '$domain') !== false) {
			$url= str_replace('$domain', $domain, $url);
		}
		
		$code= get_url_code("{$url}");
//			debug("Response for {$url}: {$code}");
		insertResponseCode($site['code'], $url, $code);
		
		$benchmark= benchmark($url);
		if($benchmark) {
			debug("Benchmark for {$url}: {$benchmark['median']}ms");
			insertBenchmark($site['code'], $url, $benchmark['median'], $benchmark['min'], $benchmark['max']);
			$median= $benchmark['median'];
		}
	}
}

if(is_cli) debug("Done.");
else {
	$redirect= isset($_GET['redirect']) ? urldecode($_GET['redirect']) : '/';
	redirect("{$redirect}?success=" . urlencode("{$domain} successfully benchmarked in " . intval((time()-$begin)) . " seconds with median {$median} ms"));
}