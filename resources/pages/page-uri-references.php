<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title>Test URI references</title>
</head>
<body>

<h1>Test URI references</h1>
<a href="http://<?=$_SERVER['HTTP_HOST']?>/resources/pages/test-uri-references.php" >Absolute URI</a><br>
<a href="//<?=$_SERVER['HTTP_HOST']?>/resources/pages/test-uri-references.php" >Network-path Reference</a><br>
<a href="/resources/pages/page-uri-references.php" >Absolute-path Reference</a><br>
<?php
	function getOS() {
		$uname = strtolower(php_uname());
		if (strpos($uname, "darwin") !== false) { return 'macosx'; }
		elseif (strpos($uname, "win") !== false) { return 'windows'; }
		elseif (strpos($uname, "linux") !== false) { return 'linux'; }
		else { return 'unknown'; }
	}
	if ('windows' !== getOS()) {
		echo '<a href="./test:uri:references.php" >Relative-path Reference</a><br>';
	}
?>
<a href="page-uri-references.php" >Relative-path Reference</a><br>
<a href="page-uri-references.php" >Relative-path Reference with dot-segments</a><br>
<a href="page-uri-references.php#results" >Same-Document Reference</a><br>

<p><a name="results"></p>
<p>ARBITRARY TEXT: And though of all men the moody captain of the Pequod was the least given to that sort of shallowest assumption;
	and though the only homage he ever exacted, was implicit, instantaneous obedience;
	though he required no man to remove the shoes from his feet ere stepping upon the quarter-deck;
	and though there were times when, owing to peculiar circumstances connected with events hereafter to be detailed,
	he addressed them in unusual terms, whether of condescension or IN TERROREM, or otherwise;
	yet even Captain Ahab was by no means unobservant of the paramount forms and usages of the sea.
</p>

</body>
</html>