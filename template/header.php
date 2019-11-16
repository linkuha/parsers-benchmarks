<!DOCTYPE html>
<html lang="ru" class="no-js">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>Анализаторы</title>
	<meta name="description" content="">
	<meta name="author" content="linkuha@gmail.com">
	<meta name="HandheldFriendly" content="True">
	<meta name="MobileOptimized" content="320">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">

	<!-- Place favicon.ico and apple-touch-icon(s) here  -->

	<link rel="shortcut icon" href="/template/assets/img/favicon.ico">
	<link rel="apple-touch-icon" href="/template/assets/img/touch-icon-iphone.png">
	<link rel="apple-touch-icon" sizes="76x76" href="/template/assets/img/touch-icon-ipad.png">
	<link rel="apple-touch-icon" sizes="120x120" href="/template/assets/img/touch-icon-iphone-retina.png">
	<link rel="apple-touch-icon" sizes="152x152" href="/template/assets/img/touch-icon-ipad-retina.png">
	<link rel="apple-touch-startup-image" href="/template/assets/img/splash.320x460.png" media="screen and (min-device-width: 200px) and (max-device-width: 320px) and (orientation:portrait)">
	<link rel="apple-touch-startup-image" href="/template/assets/img/splash.768x1004.png" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait)">
	<link rel="apple-touch-startup-image" href="/template/assets/img/splash.1024x748.png" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape)">

	<!-- load Ink's css from the cdn -->
	<link rel="stylesheet" type="text/css" href="/template/assets/css/ink-flex.min.css">
	<link rel="stylesheet" type="text/css" href="/template/assets/css/font-awesome.min.css">

	<!-- load Ink's css for IE8 -->
	<!--[if lt IE 9 ]>
	<link rel="stylesheet" href="/template/assets/css/ink-ie.min.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<![endif]-->

	<!-- test browser flexbox support and load legacy grid if unsupported -->
	<script type="text/javascript" src="/template/assets/js/libs/modernizr.js"></script>
	<script type="text/javascript">
		Modernizr.load({
			test: Modernizr.flexbox,
			nope : '/template/assets/css/ink-legacy.min.css'
		});
	</script>

	<!-- load Ink's javascript files from the cdn -->
	<script type="text/javascript" src="/template/assets/js/libs/holder.js"></script>
	<script type="text/javascript" src="/template/assets/js/libs/ink-all.min.js"></script>
	<script type="text/javascript" src="/template/assets/js/libs/autoload.js"></script>

	<link rel="stylesheet" type="text/css" href="/template/assets/css/site-inline-styles.css">
</head>

<body>

<!--[if lte IE 9 ]>
<div class="ink-grid">
	<div class="ink-alert basic" role="alert">
		<button class="ink-dismiss">&times;</button>
		<p>
			<strong>You are using an outdated Internet Explorer version.</strong>
			Please <a href="http://browsehappy.com/">upgrade to a modern browser</a> to improve your web experience.
		</p>
	</div>
</div>
-->

<div class="wrap">
	<div class="top-menu">
		<nav class="ink-navigation ink-grid">
			<ul class="menu horizontal black">
				<li class="active"><a href="/">Анализ страницы</a></li>
				<li><a href="#">Анализ сайта</a></li>
				<li><a href="/resources/pages/page-decoding.html">Тесты работы с кодировками</a></li>
				<li><a href="/resources/pages/page-parsing-links.html">Варианты ссылок на странице</a></li>
				<li><a href="/webgrind">Webgrind</a></li>
			</ul>
		</nav>
	</div>