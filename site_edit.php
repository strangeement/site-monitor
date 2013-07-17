<?php require_once('inc/boot.php') ?>
<?php
$code= isset($_GET['code']) ? urldecode($_GET['code']) : false;
if(!isset($sites[$code]) && !$code !== 'new') {
	redirect('/?error=' . urlencode('Invalid site code'));
}

$site= null;
if($site !== 'new') {
	$site= $sites[$code];
}
?>
<?php include('tpl/header.php'); ?>

<h1><?php if($site): ?>Edit <?= $site['code'] ?><?php else: ?>New site<?php endif; ?></h1>
<br>

<p><a href="/#sites">Back to sites</a></p>

<div class="well">
	<nav>
		<a href="/site/new">New</a>
		<?php if($site): ?>
		<a href="/site/<?= $site['code'] ?>">View</a>
		<a class="external" href="http://<?= $site['domain'] ?>">Visit</a>
		<?php endif; ?>
	</nav>
</div>

<?php include('tpl/nav.php') ?>

<form action="/site_save" method="post">
	<fieldset>
		<div class="control-group">
			<label class="control-label">Code</label>
			<div class="controls">
				<input type="text" name="code" value="<?php if($site): ?><?= $site['code'] ?><?php endif; ?>">
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label">Domain</label>
			<div class="controls">
				<input type="text" name="domain" value="<?php if($site): ?><?= $site['domain'] ?><?php endif; ?>">
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label">Alert threshold</label>
			<div class="controls">
				<input type="number" name="alerth_threshold" value="<?php if($site): ?><?= $site['alert_threshold'] ?><?php endif; ?>">
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label">SSL</label>
			<div class="controls">
				<input type="checkbox" name="ssl"<?php if($site && $site['ssl']): ?> checked<?php endif; ?>>
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label">URLs</label>
			<div class="controls">
				<table id="url-controls" class="table">
				<tr>
					<th>URL</th>
					<th>Benchmark</th>
					<th></th>
				</tr>
				<?php if(empty($site) && !empty($site['urls'])): ?>
				<tr>
					<td><input type="url" name="urls[0]"></td>
					<td><input type="checkbox" name="benchmark[0]"></td>
				</tr>
				<?php else: ?>
				<?php $i= 0; ?>
				<?php foreach($site['urls'] as $id => $url) : ?>
				<tr>
					<td><input type="text" name="urls[<?= $i ?>][url]" value="<?php if(isset($url['url'])): ?><?= $url['url'] ?><?php else: ?><?= $url ?><?php endif; ?>"></td>
					<td><input type="checkbox" name="urls[<?= $i ?>][benchmark]"<?php if(isset($url['benchmark']) && $url['benchmark']): ?> checked<?php endif; ?>></td>
					<td>
						<a>Delete</a>
						<a>Clone</a>
					</td>
				</tr>
				<?php $i++; ?>
				<?php endforeach; ?>
				<?php endif; ?>
				</table>
			</div>
		</div>
		
		<div class="form-actions">
			<?php if($site): ?><input type="hidden" name="id" value="<?= $site['id'] ?>">
			<?php endif; ?>
			<button type="submit">Save</button>
		</div>
	</fieldset>
</form>

<?php include('tpl/footer.php'); ?>