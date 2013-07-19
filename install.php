<?php
require_once('inc/boot.php');

if(!empty($_POST)) {
	$response= $_POST;
	$dbhost= isset($_POST['dbhost']) ? $_POST['dbhost'] : false;
	$dbname= isset($_POST['dbname']) ? $_POST['dbname'] : false;
	$dbuser= isset($_POST['dbuser']) ? $_POST['dbuser'] : false;
	$dbpassword= isset($_POST['dbpassword']) ? $_POST['dbpassword'] : false;
	
	if(!$dbhost || !$dbname || !$dbuser || !$dbpassword) {
		redirect('/install?' . http_build_query($response));
	}
	
	$dbconf= array(
		'dbhost' => $dbhost,
		'dbname' => $dbname,
		'dbuser' => $dbuser,
		'dbpassword' => $dbpassword
	);
	file_put_contents("conf/db.php", "<?php\n" . var_export($dbconf));
	
	$connection= mysql_connect($dbconf['dbhost'], $dbconf['dbuser'], $dbconf['dbpassword']);
	if(!$connection) {
		$response['error']= "Unable to connect to MySQL server {$dbhost}";
		redirect("/install?" . http_build_query($response));
	}
	
	$db= mysql_select_db($dbconf['dbname'], $connection);
	if(!$db) {
		$response['error']= "Unable to connect to select table {$dbname}";
		redirect("/install?" . http_build_query($response));
	}
	
	mysql_query(file_get_contents("conf/mysql.sql"), $db);
	
//	if(query_db_object("show databases like '{$dbname}'")) {
//		redirect("/?success=" . urlencode("Site monitor was successfully installed"));
//	}
}
?>
<?php include('tpl/header.php'); ?>
<h1>Install site-monitor</h1>

<h3>Configuration check</h3>
<div class="alert alert-success">You are ready to install Site </div>
<br>

<form method="post">
	<fieldset>
		<div class="control-group">
			<label class="control-label">Database host</label>
			<div class="controls"><input type="text" name="dbhost" placeholder="Database host" value="<?= $dbconf['dbhost'] ?>" required></div>
		</div>
		
		<div class="control-group">
			<label class="control-label">Database name</label>
			<div class="controls"><input type="text" name="dbname" placeholder="Database name" value="<?= $dbconf['dbname'] ?>" required></div>
		</div>
		
		<div class="control-group">
			<label class="control-label">Database user</label>
			<div class="controls"><input type="text" name="dbuser" placeholder="Database user" value="<?= $dbconf['dbuser'] ?>" required></div>
		</div>
		
		<div class="control-group">
			<label class="control-label">Database password</label>
			<div class="controls"><input type="text" name="dbpassword" placeholder="Database password" value="<?= $dbconf['dbpassword'] ?>" required></div>
		</div>
		
		<div class="form-actions">
			<button type="submit">Install</button>
		</div>
	</fieldset>
</form>
<?php include('tpl/footer.php'); ?>