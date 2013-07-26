<nav>
	<h3>Timeframe</h3>
	<p>
	<a href="<?= $uri ?>/5">5m</a>
	<a href="<?= $uri ?>/15">15m</a>
	<a href="<?= $uri ?>/30">30m</a>
	<a href="<?= $uri ?>/45">45m</a>
	<a href="<?= $uri ?>/1h">1h</a>
	<a href="<?= $uri ?>/3h">3h</a>
	<a href="<?= $uri ?>/12h">12h</a>
	<a href="<?= $uri ?>/1d">1d</a>
	<a href="<?= $uri ?>/3d">3d</a>
	<a href="<?= $uri ?>/7d">7d</a>
	<a href="<?= $uri ?>/30d">30d</a>
	<a href="<?= $uri ?>">All</a>
	</p>
	<form action="<?= $uri ?>">
		<p>
			<input type="datetime" name="from" value="<?php if(isset($_GET['from'])): ?><?= urldecode($_GET['from']) ?><?php endif; ?>" placeholder="From" style="margin:0;">
			<input type="datetime" name="to" value="<?php if(isset($_GET['to'])): ?><?= urldecode($_GET['to']) ?><?php endif; ?>" placeholder="To" style="margin:0;">
			<select name="server">
				<option value="">All servers</option>
				<option>iweb</option>
				<option>linode</option>
				<option>linode-polymorphe</option>
			</select>
			<button type="submit">Filter</button>
		</p>
	</form>
</nav>