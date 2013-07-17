<?php require_once('inc/boot.php') ?>
<?php
$validation_errors= query_db_assoc("select * from `validation` where `found` > 0 or `error` is not null order by `created_at` desc limit 100");
?>
<?php include('tpl/header.php'); ?>

<h1>Errors</h1>

<table id="slow-responses" class="table">
<tr>
	<th>Site</th>
	<th>URL</th>
	<th>Errors found</th>
	<th>Error</th>
	<th>Timestamp</th>
</tr>
<?php foreach($validation_errors as $validation): ?>
<tr>
	<td><a class="external" href="/site/<?= $validation['site'] ?>"><?= $validation['site'] ?></a></td>
	<td><a class="external" href="<?= $validation['url'] ?>"><?= substr($validation['url'], strpos($validation['url'], '/', 10)) ?></a></td>
	<td><?= $validation['found'] ?></td>
	<td><?= $validation['error'] ?></td>
	<td><?= date('r', $validation['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>