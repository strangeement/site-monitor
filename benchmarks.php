<?php require_once('inc/boot.php') ?>
<?php
$site= false;

$code= isset($_GET['site']) ? urldecode($_GET['site']) : false;
if($code && !isset($sites[$code])) {
	redirect('/?error=' . urlencode('Invalid site code'));
} else {
	$site= $sites[$code];
}

$sort= isset($_GET['sort']) && !empty($_GET['sort']) ? urldecode($_GET['sort']) : false;
$order_by= 'order by `created_at` desc';
if($sort) {
	if($sort === 'oldest') $order_by= "order by `created_at`";
	else if($sort === 'fastest') $order_by= "order by `median`";
	else if($sort === 'slowest') $order_by= "order by `median` desc";
}

$page= isset($_GET['page']) && intval($_GET['page']) > 0 ? intval($_GET['page']) : 1;
$offset= ($page-1)*100;

if($site) {
	$count= intval(query_db_value("select count(*) from `benchmark` where `site`=:site", array('site' => $code)));
	$benchmarks= query_db_assoc("select * from `benchmark` where `site`=:site {$order_by} limit 100 offset {$offset}",
		array('site' => $site['code']));
} else {
	$count= intval(query_db_value("select count(*) from `benchmark` {$order_by}"));
	$benchmarks= query_db_assoc("select * from `benchmark` {$order_by} limit 100 offset {$offset}");
}
?>
<?php include('tpl/header.php'); ?>

<h1>Benchmarks<?php if($site): ?> for <?= $site['domain'] ?><?php endif; ?></h1>
<br>

<?php if($site): ?>
<?php include('tpl/site.nav.php') ?>
<?php endif; ?>

<div class="well">
	<h3>Filters</h3>
	<form id="filters" action="/benchmarks">
		<fieldset>
			<div class="row">
				<div class="control-group span3">
					<label class="control-label">Site</label>
					<select name="site" onchange="$('#filters').submit()">
						<option value="">All sites</option>
						<?php foreach($sites as $site): ?>
						<option value="<?= $site['code'] ?>"<?php if($site && $code === $site['code']): ?> selected<?php endif; ?>><?= $site['domain'] ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				
				<div class="control-group span3">
					<label class="control-label">Sort by</label>
					<select name="sort" onchange="$('#filters').submit()">
						<option value=""<?php if(!$sort): ?> selected<?php endif; ?>>Latest</option>
						<option value="oldest"<?php if($sort === 'oldest'): ?> selected<?php endif; ?>>Oldest</option>
						<option value="slowest"<?php if($sort === 'slowest'): ?> selected<?php endif; ?>>Slowest</option>
						<option value="fastest"<?php if($sort === 'fastest'): ?> selected<?php endif; ?>>Fastest</option>
					</select>
				</div>
			</div>
		</fieldset>
	</form>
</div>

<?= displayPagination('benchmarks', 'benchmarks', $_GET, $page, 100, $count) ?>

<table id="benchmarks" class="table">
<tr>
	<th>Site</th>
	<th>URL</th>
	<th>Min</th>
	<th>Max</th>
	<th>Median</th>
	<th>Timestamp</th>
</tr>
<?php foreach($benchmarks as $benchmark): ?>
<tr class="<?php if(intval($benchmark['median']) > 2000): ?>alert-2000<?php elseif(intval($benchmark['median']) > 1200): ?>alert-1200<?php elseif(intval($benchmark['median']) > 800): ?>alert-800<?php elseif(intval($benchmark['median']) > 500): ?>alert-500<?php endif; ?>">
	<td><a class="external" href="/site/<?= $benchmark['site'] ?>"><?= $benchmark['site'] ?></a></td>
	<td><a class="external" href="<?= $benchmark['url'] ?>"><?= substr($benchmark['url'], strpos($benchmark['url'], '/', 10)) ?></a></td>
	<td><?= $benchmark['min'] ?></td>
	<td><?= $benchmark['max'] ?></td>
	<td><?= $benchmark['median'] ?></td>
	<td><?= strftime('%c', $benchmark['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>

<?= displayPagination('benchmarks', 'benchmarks', null, $page, 100, $count) ?>

<?php include('tpl/footer.php'); ?>