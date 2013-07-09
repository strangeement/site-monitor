<?php require_once('inc/boot.php') ?>
<?php
$latest_errors= query_db_assoc("select * from `code` where `code` != 200 order by `created_at` desc limit 10");
$slow_responses= query_db_assoc("select * from `benchmark` where `median` > 1000 order by `created_at` desc limit 10");
$validation_errors= query_db_assoc("select * from `validation` where `found` > 0 or `error` is not null order by `created_at` desc limit 10");
?>
<?php include('tpl/header.php'); ?>

<h1>Site monitor</h1>
<br>

<div class="alert">
	<a href="/codes">Response codes</a>
	<a href="/benchmarks">Benchmarks</a>
	<a href="/validation">Validation</a>
</div>

<div id="chart"></div>
<script type="text/javascript">
	var chart_series= [
	<?php foreach($sites as $site):
	$site_benchmark= benchmarkResults($site['code']);
	?>
	{
        name: '<?= $site['code'] ?>',
        data: [<?= intval($site_benchmark['30d']) ?>, <?= intval($site_benchmark['7d']) ?>, <?= intval($site_benchmark['3d']) ?>, <?= intval($site_benchmark['1d']) ?>, <?= intval($site_benchmark['12h']) ?>, <?= intval($site_benchmark['3h']) ?>, <?= intval($site_benchmark['1h']) ?>, <?= intval($site_benchmark['45m']) ?>, <?= intval($site_benchmark['30m']) ?>, <?= intval($site_benchmark['15m']) ?>, <?= intval($site_benchmark['5m']) ?>]
    },
    <?php endforeach; ?>
    ];
</script>
<br>

<h2><?= count($sites) ?> sites</h2>
<table class="table tablesorter">
<thead>
<tr>
	<th>Site</th>
	<th>Domain</th>
	<th>Error codes</th>
	<th>Validation errors</th>
	<th>24h</th>
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
	<td><?= $site['domain'] ?></td>
	<td><?= query_db_value("select count(*) from `code` where `site`=:site and `code` <> 200", array('site' => $site['code'])) ?></td>
	<td><?= query_db_value("select `found` from `validation` where `site`=:site and `found` > 1 or `error` is not null order by `created_at` limit 1", array('site' => $site['code'])) ?></td>
	<td><?= intval(query_db_value("select avg(`median`) from `benchmark` where `site`=:site and `created_at` > unix_timestamp()-60*60*24", array('site' => $site['code']))) ?></td>
	<td><?= intval(query_db_value("select avg(`median`) from `benchmark` where `site`=:site and `created_at` > unix_timestamp()-60*60*24*3", array('site' => $site['code']))) ?></td>
	<td><?= intval(query_db_value("select avg(`median`) from `benchmark` where `site`=:site and `created_at` > unix_timestamp()-60*60*24*7", array('site' => $site['code']))) ?></td>
	<td><?= intval(query_db_value("select avg(`median`) from `benchmark` where `site`=:site and `created_at` > unix_timestamp()-60*60*24*30", array('site' => $site['code']))) ?></td>
	<td><?= intval(query_db_value("select avg(`median`) from `benchmark` where `site`=:site", array('site' => $site['code']))) ?></td>
	<td>
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