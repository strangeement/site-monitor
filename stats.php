<?php require_once('inc/boot.php') ?>
<?php
$timeframe= isset($_GET['timeframe']) && !empty($_GET['timeframe']) ? urldecode($_GET['timeframe']) : 5;
$benchmarks= benchmarks($timeframe);
?>
<?php include('tpl/header.php'); ?>

<h1>Satistics</h1>
<br>

<?php include('tpl/nav.php') ?>
<?php include('tpl/timeframe.nav.php') ?>

<br>
<div id="chart"></div>
<script type="text/javascript">
	<?php $first_site= array_keys(current($benchmarks)); ?>
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