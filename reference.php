<?php 
/**************************************************************/
/* reference.php
/* Affichage de l'état du stock des produits du commerce
/**************************************************************/
error_reporting(E_ALL);


require_once('src/config.php');
require_once('src/db.php');
require_once('src/globals.php');

mb_internal_encoding('UTF-8');
$db = db_connect();

?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Stock</title>
	<link rel="stylesheet" href="css/main.css" />
	<link rel="stylesheet" href="css/colorbox.css"> 
	<link rel="icon" type="image/png" href="favicon.jpg">
	<link href="css/tablesorter/widget.grouping.min.css" rel="stylesheet">
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
			// Si on vient de modifier une référence
			if(isset($_POST['save']) && isset($_POST['id']) && ctype_digit($_POST['id']) && isset($_POST['code']) && ctype_digit($_POST['code']) && isset($_POST['type'])){
				$id = htmlspecialchars($_POST['id']);
				$t_id = htmlspecialchars($_POST['type']);
				$marque = (isset($_POST['marque'])) ? htmlspecialchars($_POST['marque']) : NULL;
				$produit = (isset($_POST['produit'])) ? htmlspecialchars($_POST['produit']) : NULL;
				$quantite = (isset($_POST['quantite'])) ? htmlspecialchars($_POST['quantite']) : '';
				$unite = (isset($_POST['unite'])) ? htmlspecialchars($_POST['unite']) : '';
				$lieu = (isset($_POST['lieu'])) ? htmlspecialchars($_POST['lieu']) : '';
				$code = floatval($_POST['code']);
				
				$tab = conversionUnit($quantite, $unite, 'base');
				
				try{	
					$db->beginTransaction();
					$req = $db->prepare('UPDATE Reference 
								SET t_id=:t_id, marque=:marque, produit=:produit, quantite=:quantite, unite=:unite, lieu=:lieu, code=:code 
								WHERE id=:id');
								
					$req->bindParam(':id', $id, PDO::PARAM_INT);
					$req->bindParam(':t_id', $t_id, PDO::PARAM_INT);
					$req->bindParam(':marque', $marque, PDO::PARAM_STR);
					$req->bindParam(':produit', $produit, PDO::PARAM_STR);
					$req->bindParam(':quantite', $tab[0], PDO::PARAM_STR);
					$req->bindParam(':unite', $tab[1], PDO::PARAM_INT);
					$req->bindParam(':lieu', $lieu, PDO::PARAM_INT);
					$req->bindParam(':code', $code, PDO::PARAM_STR);
					
					$req->execute();
					
					$db->commit();
					
				}
				catch (PDOException $e){
					print "Erreur !: " . $e->getMessage() . "<br/>";
					$db->rollback();
					die();
				}
				$message = 'Référence modifiée';
			}
			// Si on supprime la référence
			elseif(isset($_POST['delete']) && isset($_POST['id']) && ctype_digit($_POST['id'])){
				$id = htmlspecialchars($_POST['id']);
				try{
					$req = $db->exec('DELETE FROM Reference WHERE id="' . $id . '"');
				}
				catch (PDOException $e){
					print "Erreur !: " . $e->getMessage() . "<br/>";
					die();
				}
				$message = 'Référence supprimée';
			}
			// Si on ajoute en stock
			elseif(isset($_POST['ajout']) && isset($_POST['id']) && ctype_digit($_POST['id']) && isset($_POST['nb_dlc'])){
				$r_id = htmlspecialchars($_POST['id']);
				$nb_dlc = ($_POST['nb_dlc'] < 1) ? 1 : htmlspecialchars($_POST['nb_dlc']);
				$date = (isset($_POST['date_dlc']) && isValid($_POST['date_dlc'])) ? $_POST['date_dlc'] : null;

				try{
					$req = $db->prepare('INSERT INTO Dlc (r_id, expire, nombre) VALUES (:r_id, :expire, :nombre)');
					
					$req->bindParam(':r_id', $r_id, PDO::PARAM_INT);
					$req->bindParam(':expire', $date, PDO::PARAM_STR);
					$req->bindParam(':nombre', $nb_dlc, PDO::PARAM_INT);
					
					$req->execute();
				}
				catch (PDOException $e){
					print "Erreur !: " . $e->getMessage() . "<br/>";
					die();
				}
				$message = 'Produits ajoutés au stock';
			}
			// Si on enlève du stock
			elseif(isset($_POST['retrait']) && isset($_POST['id']) && ctype_digit($_POST['id']) && isset($_POST['aRetirer']) && isset($_POST['nb_stock'])){
				$r_id = htmlspecialchars($_POST['id']);
				
				try{
					$req = $db->query('SELECT id, DATE_FORMAT(expire, "%m/%Y") AS expire, nombre, DATE_FORMAT(date_entree, "%m/%Y") AS date_entree FROM Dlc WHERE r_id="' . $r_id . '" ORDER BY expire, date_entree');
					$dlcs = $req->fetchAll(PDO::FETCH_ASSOC);
				}
				catch (PDOException $e){
					print "Erreur !: " . $e->getMessage() . "<br/>";
					die();
				}

				// S'il y a plusieurs dlc
				if(is_array($_POST['nb_stock'])){
					// Plusieurs éléments à retirer
					if(is_array($_POST['aRetirer'])){
						foreach($_POST['aRetirer'] as $sup){
							$key = array_search($sup, array_column($dlcs, 'id'));
							// Si il reste encore des éléments après ce qu'on vient de retirer
							if($_POST['nb_stock'][$key] < $dlcs[$key]['nombre']){
								$nombre = $dlcs[$key]['nombre'] - $_POST['nb_stock'][$key];
								$requete = $db->exec('UPDATE Dlc SET nombre="' . $nombre . '" WHERE id="' . $dlcs[$key]['id'] . '"');
								$message = $_POST['nb_stock'][$key] . ' élements retirés de la référence avec une DLC/DLUO ' . (($dlcs[$key]['expire'] != null) ? $dlcs[$key]['expire'] : $dlcs[$key]['date_entree']);
							}
							else{
								$requete = $db->exec('DELETE FROM Dlc WHERE id="' . $dlcs[$key]['id'] . '"');
								$message = 'La référence avec une DLC/DLUO ' . (($dlcs[$key]['expire'] != null) ? $dlcs[$key]['expire'] : $dlcs[$key]['date_entree']) . ' a été complétement retirée.';
							}
						}
					}
					else{
						$key = array_search($_POST['aRetirer'], array_column($dlcs, 'id'));
						// Si il reste encore des éléments après ce qu'on vient de retirer
						if($_POST['nb_stock'][$key] < $dlcs[$key]['nombre']){
							$nombre = $dlcs[$key]['nombre'] - $_POST['nb_stock'][$key];
							$requete = $db->exec('UPDATE Dlc SET nombre="' . $nombre . '" WHERE id="' . $dlcs[$key]['id'] . '"');
							$message = $_POST['nb_stock'][$key] . ' élements retirés de la référence avec une DLC/DLUO ' . (($dlcs[$key]['expire'] != null) ? $dlcs[$key]['expire'] : $dlcs[$key]['date_entree']);
						}
						else{
							$requete = $db->exec('DELETE FROM Dlc WHERE id="' . $dlcs[$key]['id'] . '"');
							$message = 'La référence avec une DLC/DLUO ' . (($dlcs[$key]['expire'] != null) ? $dlcs[$key]['expire'] : $dlcs[$key]['date_entree']) . ' a été complétement retirée.';
						}
					}
				}
				else{
					// Si il reste encore des éléments après ce qu'on vient de retirer
					if($_POST['nb_stock'] < $dlcs[0]['nombre']){
						$nombre = $dlcs[0]['nombre'] - $_POST['nb_stock'];
						$requete = $db->exec('UPDATE Dlc SET nombre="' . $nombre . '" WHERE id="' . $dlcs[0]['id'] . '"');
						$message = $_POST['nb_stock'] . ' élements retirés de la référence avec une DLC/DLUO ' . (($dlcs[0]['expire'] != null) ? $dlcs[0]['expire'] : $dlcs[0]['date_entree']);
					}
					else{
						$requete = $db->exec('DELETE FROM Dlc WHERE id="' . $dlcs[0]['id'] . '"');
						$message = 'La référence n\'est plus en stock';
					}
				}
			}
			
			?>   
		</header>
		<div id="wrapper">
			<table id="etat_ref" class="tablesorter">
				<caption>Etat du stock</caption>
				<thead>
					<tr>
						<th colspan="2" class="type">Type</th>
						<th class="marque">Marque</th>
						<th class="produit">Produit</th>
						<th class="quantite">Quantité</th>
						<th class="stock">Stock</th>
						<th class="next_dlc">Prochaine DLC/DLUO</th>
						<th class="lieu">Lieu</th>
						<th class="barcode" colspan="2">Code barre</th>
					</tr>
				</thead>
				<tbody>
					<?php
					
					try{
						$req = $db->query('SELECT rayon, type FROM Type ORDER BY type');
						$types = $req->fetchAll(PDO::FETCH_ASSOC);
					}
					catch (PDOException $e){
						print "Erreur !: " . $e->getMessage() . "<br/>";
						die();
					}
					
					$sql = 'SELECT r.id, r.marque, r.produit, r.quantite, r.unite, r.dlc_dluo, r.lieu, r.code, t.type, v.stock, v.r_id, v.expire 
									FROM Reference r 
									LEFT JOIN Type t ON t.id = r.t_id 
									LEFT JOIN V_Dlc_grp_r_id v ON v.r_id = r.id 
									ORDER BY t.type ';
					$requete = $db->query($sql);
					
					while($tab = $requete->fetch(PDO::FETCH_ASSOC)){
						if($tab['expire'] != null && compareDlc($tab['expire']) == true)
							$aConsommer = ' aConsommer';
						else
							$aConsommer = '';
						echo '<tr class="produit" id="' . $tab['id'] . '">';
						echo '<td class="ajout"><span>+</span></td>';
						echo '<td class="type">' . $tab['type'] . '</td>';
						echo '<td class="marque">' . $tab['marque'] . '</td>';
						echo '<td class="produit">' . $tab['produit'] . '</td>';
						
						$convers = conversionUnit($tab['quantite'], $tab['unite'], 'sortie');
						$quantite = explode('.', $convers[0]);
						if(isset($quantite[1])){
							if($quantite[1] == '00')
								echo '<td class="quantite">' . $quantite[0] . ' ' . $convers[1] . '</td>';
							else
								echo '<td class="quantite">' . $quantite[0] . '.' . $quantite[1] . ' ' . $convers[1] . '</td>';
						}
						
						echo '<td class="stock">' . $tab['stock'] . '</td>';
						switch($tab['dlc_dluo']){
							case 0: 
								echo '<td class="dlc' . $aConsommer . '">' . $tab['expire'] . '<span>dlc</span></td>';
								break;
							
							case 1: 
								echo '<td class="dluo' . $aConsommer . '">' . $tab['expire'] . '<span>dluo</span></td>';
								break;
								
							case 2: 
								echo '<td class="wt-dlc">' . $tab['expire'] . '</td>';
								break;
						}
						echo '<td class="lieu">' . (($tab['lieu'] == 0) ? 'Cave' : 'Congélateur') . '</td>';
						echo '<td class="barcode">' . $tab['code'] . '</td>';
						echo '<td class="retrait"><span>&#8211;</span></td>';
						echo '</tr>';
					}
					
					$db = null;
					?>
				</tbody>
			</table>
		</div>
	</div>
	<footer>
		<?php include('src/footer.php'); ?>
	</footer>
	<div id="popup">
	</div>	
</div>
</body>
</html>

<?php
// Vérifie la validité de la date fournie par l'utilisateur
function isValid($date, $format = 'Y-m-d'){
	$dt = DateTime::createFromFormat($format, $date);
	return $dt && $dt->format($format) === $date;
}

// Compare la date de la DLC avec la date actuelle
function compareDlc ($date){
	$dateAFormater = explode('/', $date);
	$dateDlc = new DateTime(intval($dateAFormater['2']) . '-' . intval($dateAFormater['1']) . '-' . intval($dateAFormater['0']));
	$dateActuelle = new DateTime('now');
	$dateDlc->sub(new DateInterval('P1M'));	
	
	return $dateDlc <= $dateActuelle;
}