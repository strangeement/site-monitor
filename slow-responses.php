<?php require_once('inc/boot.php') ?>
<?php
$slow_responses= query_db_assoc("select * from `benchmark` where `median` > 1000 order by `created_at` desc limit 100");
?>
<?php include('tpl/header.php'); ?>

<h1>Errors</h1>

<table id="slow-responses" class="table">
<tr>
	<th>Site</th>
	<th>URL</th>
	<th>Min</th>
	<th>Max</th>
	<th>Median</th>
	<th>Timestamp</th>
</tr>
<?php foreach($slow_responses as $benchmark): ?>
<tr>
	<td><a class="external" href="/site/<?= $benchmark['site'] ?>"><?= $benchmark['site'] ?></a></td>
	<td><a class="external" href="<?= $benchmark['url'] ?>"><?= substr($benchmark['url'], strpos($benchmark['url'], '/', 10)) ?></a></td>
	<td><?= $benchmark['min'] ?></td>
	<td><?= $benchmark['max'] ?></td>
	<td><?= $benchmark['median'] ?></td>
	<td><?= date('c', $benchmark['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>