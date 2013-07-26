<?php require_once('inc/boot.php') ?>
<?php
$server= isset($_GET['server']) ? urldecode($_GET['server']) : false;
$timeframe= isset($_GET['timeframe']) && !empty($_GET['timeframe']) ? urldecode($_GET['timeframe']) : 15;
$from= isset($_GET['from']) && !empty($_GET['from']) ? urldecode($_GET['from']) : null;
$to= isset($_GET['to']) && !empty($_GET['to']) ? urldecode($_GET['to']) : null;
$benchmarks= benchmarks($from || $to ? array('from' => $from, 'to' => $to) : $timeframe);
$uri= '/stats';
?>
<?php include('tpl/header.php'); ?>

<h1>Satistics</h1>
<br>

<?php include('tpl/nav.php') ?>
<?php include('tpl/timeframe.nav.php') ?>

<br>
<div id="chart"></div>
<script type="text/javascript">
	<?php
	$first_site= reset($benchmarks);
	$first_site= array_keys($first_site);
	?>
	var chart_points= ['<?= implode('\',\'', $first_site) ?>'];

	var chart_series= [
	<?php foreach($benchmarks as $site => $benchmark): ?>
	{
        name: '<?= $site ?>',
        data: [<?= implode(',', $benchmark) ?>]
    },
    <?php endforeach; ?>
    ];
</script>
<br>

<?php include('tpl/footer.php'); ?>