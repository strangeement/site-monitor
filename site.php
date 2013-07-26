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
$median= intval(query_db_value("select avg(`median`) from `benchmark` where `site`=:site", array('site' => $code)));
$distribution= getMedianDistribution($site);
?>
<?php include('tpl/header.php'); ?>

<h1><?= $site['code'] ?> monitor</h1>

<?php include('tpl/site.nav.php') ?>

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
	<div class="stat">Server: <?= $site['server'] ?></div>
	<div class="stat">URLs monitored: <?= count($site['urls']) ?></div>
	<div class="stat">Median response time: <?= intval($site['median']) ?>ms</div>
	<div class="stat">Response errors: <?= query_db_value("select count(*) from `code` where `site`=:site and `code` <> 200", array('site' => $code)) ?></div>
	<br>
	<div class="stat">
		<h4>Distribution</h4>
		<table class="table">
			<tr>
				<td>50%</td>
				<td><?= $distribution[50] ?>ms</td>
			</tr>
			<tr>
				<td>60%</td>
				<td><?= $distribution[60] ?>ms</td>
			</tr>
			<tr>
				<td>70%</td>
				<td><?= $distribution[70] ?>ms</td>
			</tr>
			<tr>
				<td>80%</td>
				<td><?= $distribution[80] ?>ms</td>
			</tr>
			<tr>
				<td>90%</td>
				<td><?= $distribution[90] ?>ms</td>
			</tr>
			<tr>
				<td>95%</td>
				<td><?= $distribution[95] ?>ms</td>
			</tr>
			<tr>
				<td>98%</td>
				<td><?= $distribution[98] ?>ms</td>
			</tr>
			<tr>
				<td>99%</td>
				<td><?= $distribution[99] ?>ms</td>
			</tr>
		</table>
	</div>
</div>

<h2><a href="/benchmarks/<?= $site['code'] ?>">Benchmarks</a></h2>
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

<h2><a href="/codes/<?= $site['code'] ?>">Response codes</a></h2>
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