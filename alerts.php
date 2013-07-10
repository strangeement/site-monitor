<?php require_once('inc/boot.php') ?>
<?php
$alerts= query_db_assoc("select * from `alert` order by `created_at` desc limit 100", null, false, false);
?>
<?php include('tpl/header.php'); ?>

<h1>Alerts</h1>

<table id="alerts" class="table tablesorter">
<tr>
	<th>Site</th>
	<th>Type</th>
	<th>Message</th>
	<th>Timestamp</th>
</tr>
<?php foreach($alerts as $alert): ?>
<tr>
	<td><a href="/site/<?= $alert['site'] ?>"><?= $alert['site'] ?></a></td>
	<td><?= $alert['type'] ?></td>
	<td><a class="external" href="<?= $alert['url'] ?>"><?= $alert['message'] ?></a></td>
	<td><?= date('r', $alert['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>