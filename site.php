<?php require_once('inc/boot.php') ?>
<?php
$code= isset($_GET['code']) ? urldecode($_GET['code']) : false;
if(!isset($sites[$code])) {
	redirect('/?error=' . urlencode('Invalid site code'));
}

$site= $sites[$code];

$timeframe= isset($_GET['timeframe']) && !empty($_GET['timeframe']) ? urldecode($_GET['timeframe']) : false;
if($timeframe) {
	$benchmark= benchmarks($timeframe, $site['code']);
} else {
	$benchmark= $site;
}

$uri= "/site/{$code}";

$benchmarks= query_db_assoc("select * from `benchmark` where `site`=:site order by `created_at` desc limit 20", array('site' => $site['code']));
$codes= query_db_assoc("select * from `code` where `site`=:site order by `created_at` desc limit 20", array('site' => $site['code']));
?>
<?php include('tpl/header.php'); ?>

<h1><?= $site['code'] ?> monitor</h1>

<div class="alert">
	<a class="external" href="http://<?= $site['domain'] ?>">Visit</a>
	<a href="/benchmark/<?= $site['code'] ?>?redirect=<?= urlencode("/site/{$site['code']}") ?>;bust=<?= time() ?>">Benchmark</a>
</div>

<?php include('tpl/timeframe.nav.php') ?>
<div id="chart"></div>
<script type="text/javascript">
	chart_points= [<?php if($timeframe): ?>'<?= implode('\',\'', array_keys($benchmark)) ?>'<?php else: ?>'-120d', '-60d', '-30d', '-7d', '-3d', '-1d', '-12h', '-3h', '-1h', '-45m', '-30m', '-15m', '-5m'<?php endif; ?>];

	var chart_series= [
	{
        name: '<?= $code ?>',
        data: [<?php if($timeframe): ?><?= implode(',', array_values($benchmark)) ?><?php else: ?><?= intval($benchmark['120d']) ?>, <?= intval($benchmark['60d']) ?>, <?= intval($benchmark['30d']) ?>, <?= intval($benchmark['7d']) ?>, <?= intval($benchmark['3d']) ?>, <?= intval($benchmark['1d']) ?>, <?= intval($benchmark['12h']) ?>, <?= intval($benchmark['3h']) ?>, <?= intval($benchmark['1h']) ?>, <?= intval($benchmark['45m']) ?>, <?= intval($benchmark['30m']) ?>, <?= intval($benchmark['15m']) ?>, <?= intval($benchmark['5m']) ?><?php endif; ?>]
    }
    ];
</script>
<br>

<div id="site-stats" class="well">
	<div class="stat">URLs monitored: <?= count(unserialize($site['urls'])) ?></div>
	<div class="stat">Median response time: <?= intval($site['median']) ?>ms</div>
	<div class="stat">Response errors: <?= query_db_value("select count(*) from `code` where `site`=:site and `code` <> 200", array('site' => $code)) ?></div>
</div>

<h2>Benchmarks</h2>
<table id="benchmarks" class="table">
<tr>
	<th>URL</th>
	<th>Min</th>
	<th>Max</th>
	<th>Median</th>
	<th>Timestamp</th>
</tr>
<?php foreach($benchmarks as $benchmark): ?>
<tr class="<?php if(intval($benchmark['median']) > 800): ?>alert-800<?php elseif(intval($benchmark['median']) > 500): ?>alert-500<?php endif; ?>">
	<td><a class="external" href="<?= $benchmark['url'] ?>"><?= preg_replace("/https?:\/\/{$site['domain']}/i", '', $benchmark['url']) ?></a></td>
	<td><?= $benchmark['min'] ?></td>
	<td><?= $benchmark['max'] ?></td>
	<td><?= $benchmark['median'] ?></td>
	<td><?= date('c', $benchmark['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>

<h2>Response codes</h2>
<table id="codes" class="table">
<tr>
	<th>URL</th>
	<th>Code</th>
	<th>Timestamp</th>
</tr>
<?php foreach($codes as $code): ?>
<tr class="<?php if(intval($code['code']) !== 200): ?>error<?php endif; ?>">
	<td><a class="external" href="<?= $code['url'] ?>"><?= preg_replace("/https?:\/\/{$site['domain']}/i", '', $code['url']) ?></a></td>
	<td><?= $code['code'] ?></td>
	<td><?= date('c', $code['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>

<?php include('tpl/footer.php'); ?>