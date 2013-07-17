<?php require_once('inc/boot.php') ?>
<?php
$alerts= query_db_assoc("select * from `alert` order by `created_at` desc limit 10", null, false, false);
$latest_errors= query_db_assoc("select * from `code` where `code` != 200 order by `created_at` desc limit 10");
$slow_responses= query_db_assoc("select * from `benchmark` where `median` > 1000 order by `created_at` desc limit 10");
$validation_errors= query_db_assoc("select * from `validation` where `found` > 0 or `error` is not null order by `created_at` desc limit 10");
?>
<?php include('tpl/header.php'); ?>

<h1>Site monitor</h1>
<br>

<?php include('tpl/nav.php') ?>

<div id="chart"></div>
<script type="text/javascript">
	var chart_series= [
	<?php
	foreach($sites as $site):
	?>
	{
        name: '<?= $site['code'] ?>',
        data: [<?= intval($site['30d']) ?>, <?= intval($site['7d']) ?>, <?= intval($site['3d']) ?>, <?= intval($site['1d']) ?>, <?= intval($site['12h']) ?>, <?= intval($site['3h']) ?>, <?= intval($site['1h']) ?>, <?= intval($site['45m']) ?>, <?= intval($site['30m']) ?>, <?= intval($site['15m']) ?>, <?= intval($site['5m']) ?>]
    },
    <?php endforeach; ?>
    ];
</script>
<br>

<?php if(count($alerts) > 0): ?>
<h2><a href="/alerts">Alerts</a></h2>
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
<br>
<?php endif; ?>

<h2 id="sites"><?= count($sites) ?> sites (<a href="/site/new">Add</a>)</h2>
<table class="table tablesorter">
<thead>
<tr>
	<th>Site</th>
	<th>Domain</th>
	<th>Error codes</th>
	<th>Validation errors</th>
	<th>12h</th>
	<th>1d</th>
	<th>3d</th>
	<th>7d</th>
	<th>30d</th>
	<th>Benchmark</th>
	<th></th>
</tr>
</thead>
<tbody>
<?php foreach($sites as $site): ?>
<tr>
	<td><a href="/site/<?= $site['code'] ?>"><?= $site['code'] ?></a></td>
	<td><a class="external" href="http://<?= $site['domain'] ?>"><?= $site['domain'] ?></a></td>
	<td><?= intval($site['code_errors']) === 0 ? '' : intval($site['code_errors']) ?></td>
	<td><?= $site['validation_errors'] ?></td>
	<td><?= intval($site['12h']) ?></td>
	<td><?= intval($site['1d']) ?></td>
	<td><?= is_null($site['3d']) ? '' : intval($site['3d']) ?></td>
	<td><?= is_null($site['7d']) ? '' : intval($site['7d']) ?></td>
	<td><?= is_null($site['30d']) ? '' : intval($site['30d']) ?></td>
	<td><?= intval($site['median']) ?></td>
	<td>
		<a href="/site/<?= $site['code'] ?>/edit?bust=<?= time() ?>">Edit</a>
		<a href="/benchmark/<?= $site['code'] ?>?bust=<?= time() ?>">Benchmark</a>
	</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<br>

<?php if(count($latest_errors) > 0): ?>
<h2><a href="/errors">Latest errors</a></h2>
<table id="errors" class="table">
<tr>
	<th>Site</th>
	<th>URL</th>
	<th>Code</th>
	<th>Timestamp</th>
</tr>
<?php foreach($latest_errors as $error): ?>
<tr>
	<td><a class="external" href="/site/<?= $error['site'] ?>"><?= $error['site'] ?></a></td>
	<td><a class="external" href="<?= $error['url'] ?>"><?= substr($error['url'], strpos($error['url'], '/', 10)) ?></a></td>
	<td><?= $error['code'] ?></td>
	<td><?= date('r', $error['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<?php if(count($slow_responses) > 0): ?>
<h2><a href="/slow-responses">Slow responses</a></h2>
<table id="slow-responses" class="table">
<tr>
	<th>Site</th>
	<th>URL</th>
	<th>Median</th>
	<th>Timestamp</th>
</tr>
<?php foreach($slow_responses as $benchmark): ?>
<tr>
	<td><a class="external" href="/site/<?= $benchmark['site'] ?>"><?= $benchmark['site'] ?></a></td>
	<td><a class="external" href="<?= $benchmark['url'] ?>"><?= substr($benchmark['url'], strpos($benchmark['url'], '/', 10)) ?></a></td>
	<td><?= $benchmark['median'] ?></td>
	<td><?= date('r', $benchmark['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<?php if(count($validation_errors) > 0): ?>
<h2><a href="/validation">Validation errors</a></h2>
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
<?php endif; ?>

<?php include('tpl/footer.php'); ?>