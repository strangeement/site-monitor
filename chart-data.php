<?php require_once('inc/boot.php') ?>
<?php header("Content-Type: application/json");
?>[
<?php
$i= 1;
$count= count($sites);
foreach($sites as $site):
$site_benchmark= benchmarkResults($site['code']);
?>
{
    "name":"<?= $site['code'] ?>",
    "data":["<?= intval($site_benchmark['30d']) ?>", "<?= intval($site_benchmark['7d']) ?>", "<?= intval($site_benchmark['3d']) ?>", "<?= intval($site_benchmark['1d']) ?>", "<?= intval($site_benchmark['12h']) ?>", "<?= intval($site_benchmark['3h']) ?>", "<?= intval($site_benchmark['1h']) ?>", "<?= intval($site_benchmark['45m']) ?>", "<?= intval($site_benchmark['30m']) ?>", "<?= intval($site_benchmark['15m']) ?>", "<?= intval($site_benchmark['5m']) ?>"]
}<?php if($i++ < $count): ?>,<?php endif; ?>
<?php endforeach; ?>
]