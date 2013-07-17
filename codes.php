<?php require_once('inc/boot.php') ?>
<?php
$errors= query_db_assoc("select * from `code` where `code` != 200 order by `created_at` desc limit 100");
?>
<?php include('tpl/header.php'); ?>

<h1>Errors</h1>
<br>

<?php include('tpl/nav.php') ?>

<table id="errors" class="table">
<tr>
	<th>Site</th>
	<th>URL</th>
	<th>Code</th>
	<th>Timestamp</th>
</tr>
<?php foreach($errors as $error): ?>
<tr>
	<td><a class="external" href="/site/<?= $error['site'] ?>"><?= $error['site'] ?></a></td>
	<td><a class="external" href="<?= $error['url'] ?>"><?= substr($error['url'], strpos($error['url'], '/', 10)) ?></a></td>
	<td><?= $error['code'] ?></td>
	<td><?= date('r', $error['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>