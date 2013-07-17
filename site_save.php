<?php
require_once('inc/boot.php');

$site= null;
$id= isset($_POST['id']) ? intval($_POST['id']) : null;
if($id) {
	$site= query_db_object("select * from `site` where `id`=:id", array('id' => $id));
	if(!$site) {
		redirect("/site/new?error=" . urlencode("Invalid site"));
	}
}

$code= isset($_POST['code']) ? $_POST['code'] : null;
$domain= isset($_POST['domain']) ? $_POST['domain'] : null;
$alert_threshold= isset($_POST['alert_threshold']) ? intval($_POST['alert_threshold']) : 1000;
$ssl= isset($_POST['ssl']);
$urls= isset($_POST['urls']) ? $_POST['urls'] : null;

$errors= array();

if(empty($code)) {
	$errors['code']= "Code cannot be empty";
}

if(empty($code)) {
	$errors['domain']= "Domain cannot be empty";
}

if(!empty($errors)) {
	$response= array('errors' => $errors);
	redirect("/site/" . ($site ? "{$site['code']}/new" : 'new'));
}

if($urls) {
	foreach($urls as &$url) {
		$url['benchmark']= isset($url['benchmark']);
	}
}

if($id) {
	updateSite($id, $code, $domain, $alert_threshold, $ssl, $urls);
} else {
	insertSite($code, $domain, $alert_threshold, $ssl, $urls);
}

apc_delete("sites");

redirect("/site/{$code}");