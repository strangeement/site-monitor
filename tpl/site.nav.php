<div class="well">
	<nav>
		<a class="external" href="http://<?= $site['domain'] ?>">Visit</a>
		<a href="/site/<?= $site['code'] ?>">View</a>
		<a href="/site/<?= $site['code'] ?>/edit">Edit</a>
		<a href="/benchmark/<?= $site['code'] ?>?redirect=<?= urlencode("/site/{$site['code']}") ?>;bust=<?= time() ?>">Benchmark</a>
	</nav>
</div>