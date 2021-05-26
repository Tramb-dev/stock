<?php 
/**************************************************************/
/* esp.php
/* Echange les infos avec le scanner de code barre
/**************************************************************/
require_once('config.php');
require_once('db.php');

// On récupère le code barre et on renvoie des infos dessus
if(isset($_GET['search'])){
	$code = htmlspecialchars($_GET['search']);
	
	if(!empty($code)){
		$db = db_connect();
		$db->exec('INSERT INTO test VALUES ("' . $code . '")');
		$sql = 'SELECT  DISTINCT r.id, r.marque, r.produit, r.quantite, r.unite, r.stock, d.dlc_dluo, DATE_FORMAT(d.expire, "%m/%Y") AS date, t.type, t.sous_type
								FROM Reference r
								LEFT JOIN Dlc d ON d.r_id = r.id
								LEFT JOIN Type t ON t.id = r.t_id
								WHERE r.code = "' . $code . '"';
		$requete = $db->query($sql);
		
		$tab = $requete->fetch(PDO::FETCH_ASSOC)
		?>
		<!doctype html>
		<html lang="fr">
		<head>
			<meta charset="utf-8" />
			<title>Stock</title>
			<link rel="stylesheet" href="../css/esp.css" />
		</head>

		<body>
		<div id="container">
			<div id="outer">
				<div id="wrapper">
					<table id="etat_commerce">
						<tr>
							<th>Type</th>
							<th>Marque</th>
							<th>Produit</th>
							<th>Quantité</th>
							<th>Stock</th>
							<th>Seuil</th>
							<th>Prochaine DLC/DLUO</th>
						</tr>
						<?php
						
						echo '<tr id="' . $tab['c_id'] . '">';
						echo '<td>' . $tab['type'] . ' ' . $tab['sous_type'] . '</td>';
						echo '<td>' . $tab['marque'] . '</td>';
						echo '<td>' . $tab['produit'] . '</td>';
						echo '<td>' . $tab['quantite'] . ' ' . $tab['unite'] . '</td>';
						echo '<td>' . $tab['stock'] . '</td>';
						echo '<td>' . $tab['seuil'] . '</td>';
						echo '<td class="' .  (($tab['dlc_dluo'] == 0) ? 'dlc' : 'dluo') . '">' . $tab['date'] . '</td>';
						echo '</tr>';
						
						$db = null;
						?>
					</table>
				</div>
			</div>
		</div>
		</body>
		</html>
		<?php
	}
}
// On ajoute en base
elseif(isset($_GET['add'])){
	
}
// On enlève de la base
elseif(isset($_GET['take'])){
	
}

?>