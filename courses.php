<?php 
/**************************************************************/
/* courses.php
/* Affichage de la liste de courses quand le stock d'une catégorie descend en dessous du seuil
/**************************************************************/
error_reporting(E_ALL);


require_once('src/config.php');
require_once('src/db.php');

mb_internal_encoding('UTF-8');
$db = db_connect();

?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Stock</title>
	<link rel="icon" type="image/png" href="favicon.jpg">
	<link rel="stylesheet" href="css/main.css" />
	<link href="css/tablesorter/theme.default.min.css" rel="stylesheet">
	<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
	<script type="text/javascript" src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
	<script type="text/javascript" src="js/jquery.colorbox-min.js"></script>
	<script type="text/javascript" src="js/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="js/tablesorter/jquery.tablesorter.widgets.min.js"></script>
	<script type="text/javascript" src="js/main.js"></script>
</head>

<body>
<div id="container">
	<div id="outer">
		<header>
			<?php 
			include('src/header.php'); 
			?>
		</header>
		<div id="wrapper">
			<table id="liste" class="tablesorter">
				<caption>Liste de courses</caption>
				<thead>
					<tr>
						<th>Rayon</th>
						<th>Produit</th>
						<th>Quantité</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
	<footer>
		<?php include('src/footer.php'); ?>
	</footer>
</div>
</body>
</html>
