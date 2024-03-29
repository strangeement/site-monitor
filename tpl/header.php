<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Site monitor</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <link rel="stylesheet" href="/static/css/bootstrap.css">
        <link rel="stylesheet" href="/static/css/bootstrap-responsive.css">
        <link rel="stylesheet" href="/static/css/jquery.tablesorter.css">
        <link rel="stylesheet" href="/static/css/main.css">

        <script src="/static/js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
        <script type="text/javascript">
        	var chart_points= null;
        </script>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->

        <!-- This code is taken from http://twitter.github.com/bootstrap/examples/hero.html -->

        <div class="navbar navbar-inverse navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <a class="brand" href="/">Site monitor</a>
                    <div class="nav-collapse collapse">
                        <ul class="nav">
                            <li class="active"><a href="/">Home</a></li>
                            <li><a href="/monitor?bust=<?= time() ?>">Monitor</a></li>
                            <li><a href="/alert?bust=<?= time() ?>">Alert</a></li>
                            <li><a href="/clear?redirect=<?= urlencode($uri) ?>">Clear</a></li>
                            <li><a class="external" href="http://dev/">Dev</a></li>
                        </ul>
                        <?php /*<form class="navbar-form pull-right">
                            <input class="span2" type="text" placeholder="Email">
                            <input class="span2" type="password" placeholder="Password">
                            <button type="submit" class="btn">Sign in</button>
                        </form>
                        */ ?>
                    </div><!--/.nav-collapse -->
                </div>
            </div>
        </div>

        <div id="main" class="container">
        	<?php if(isset($_GET['success'])): ?>
        	<div class="alert alert-success"><?= urldecode($_GET['success']) ?></div>
        	<?php endif; ?>