<?php
require_once('inc/boot.php');

$redirect= isset($_GET['redirect']) ? urldecode($_GET['redirect']) : '/';

apc_clear_cache('user');
redirect($redirect);