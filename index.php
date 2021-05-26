<?php 
/**************************************************************/
/* index.php
/**************************************************************/
error_reporting(E_ALL);

header('Location: reference.php'); 

require_once('src/config.php');
include ('src/globals.php');

mb_internal_encoding('UTF-8');
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>Stock</title>
	<link rel="icon" type="image/png" href="favicon.jpg">
</head>

<body>
<div id="container">
	<div id="outer">
		<header>
			<?php include('src/header.php'); ?>   
		</header>
		<div id="wrapper">

		</div>
	</div>
	<div id="popup"></div>
</div>
</body>
</html>
