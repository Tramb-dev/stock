<?php 
/**************************************************************/
/* categorie.php
/* Permet la gestion des catégories
/**************************************************************/
error_reporting(E_ALL);


require_once('src/config.php');
require_once('src/db.php');

mb_internal_encoding('UTF-8');


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
	<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
	<script src="js/jquery.colorbox-min.js"></script>
	<script src="js/jquery.tablesorter.js"></script>
	<script src="js/main.js"></script>
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
		<?php
			$db = db_connect();

			// Recherche des types pour les inputs
			try{
				$req = $db->query('SELECT id, rayon, type, type_unit FROM Type ORDER BY rayon, type');
				$types = $req->fetchAll(PDO::FETCH_ASSOC);
			}
			catch (PDOException $e){
				print "Erreur !: " . $e->getMessage() . "<br/>";
				die();
			}
			
			// Si on vient d'ajouter un nouveau type de produit
			if(isset($_POST['enregistrerType'])){
				if($_POST['type'] != ''){
					$rayon = htmlspecialchars($_POST['rayon']);
					$type = htmlspecialchars($_POST['type']);
					$type_unit = ($_POST['u_q'] == 0 || $_POST['u_q'] == 1) ? $_POST['u_q'] : 0;
					// Test savoir si le type existe déjà, si oui on informe l'utilisateur sinon on l'insert
					if(!in_array($type, array_column($types, 'type'))){
						try{
							$sth = $db->prepare('INSERT INTO Type (rayon, type, type_unit) VALUES (?, ?, ?)');
							$sth->execute(array($rayon, $type, $type_unit));
							$message = 'Catégorie enregistrée';
							array_unshift($types, array(
														'rayon'		=>	$rayon,
														'type' 		=>	$type,
														'type_unit'	=>	$type_unit
							));
						}
						catch (PDOException $e){
							print "Erreur !: " . $e->getMessage() . "<br/>";
							die();
						}
					}
					else {
						$message = 'Catégorie déjà existante';
					}
				}
				else
					$message = 'Catégorie manquante';
			}
			// Si on vient de modifier une catégorie
			elseif(isset($_POST['save']) && isset($_POST['id']) && ctype_digit($_POST['id']) && isset($_POST['type'])){
				$id = htmlspecialchars($_POST['id']);
				$rayon = htmlspecialchars($_POST['rayon']);
				$type = htmlspecialchars($_POST['type']);
				$type_unit = htmlspecialchars($_POST['u_q']);
				
				try{	
					$db->beginTransaction();
					$req = $db->prepare('UPDATE Type 
								SET rayon=:rayon, type=:type, type_unit=:type_unit
								WHERE id=:id');
								
					$req->bindParam(':id', $id, PDO::PARAM_INT);
					$req->bindParam(':rayon', $rayon, PDO::PARAM_STR);
					$req->bindParam(':type', $type, PDO::PARAM_STR);
					$req->bindParam(':type_unit', $type_unit, PDO::PARAM_INT);
					
					$req->execute();
					
					$db->commit();
				}
				catch (PDOException $e){
					print "Erreur !: " . $e->getMessage() . "<br/>";
					$db->rollback();
					die();
				}
				$message = 'Catégorie modifiée';
				$key = array_search($id, array_column($types, 'id'));
				$replace = array('id' => $id, 'rayon' => $rayon, 'type' => $type, 'type_unit' => $type_unit);
				$types = array_replace($types, array($key => $replace));
			}
			// Si on supprime la catégorie
			elseif(isset($_POST['delete']) && isset($_POST['id']) && ctype_digit($_POST['id'])){
				$id = htmlspecialchars($_POST['id']);
				try{
					$req = $db->exec('DELETE FROM Type WHERE id=' . $id);
				}
				catch (PDOException $e){
					print "Erreur !: " . $e->getMessage() . "<br/>";
					die();
				}
				$message = 'Catégorie supprimée';
				unset($types[$key]);
				$key = array_search($id, array_column($types, 'id'));
			}
			
		?>
			<form method="post" action="categorie.php" id="formType">
				<fieldset name="ajoutType">
					<legend>Ajout d'un nouveau type de produit</legend>
					<ul class="grid">
						<li class="input">
							<label for="rayon">Rayon : </label><input list="rayons" name="rayon" id="rayon">
							<datalist id="rayons">
								<?php
									foreach($types as $liste){
										echo '<option value="' . $liste['rayon'] . '">';
									}
								?>
							</datalist>
						</li>
						<li class="input requit">
							<label for="type">Type : </label><input type="text" name="type" id="type">
						</li>
						<li class="input">
							<span>Stockage par unité (µ) ou quantité (poids/volume) ?</span>
							<input type="radio" id="u" name="u_q" value="0" checked><label for="u">unité</label>
							<input type="radio" id="q" name="u_q" value="1"><label for="q">quantité</label>
						</li>
					</ul>
				</fieldset>
				<input type="submit" name="enregistrerType" value="Enregistrer le type"></input><br />
			</form>
			<div class="box">
				Légende : 
				<div class="categorie unité">Unité</div>
				<div class="categorie quantité">Quantité</div>
			</div>
			<div class="box">
				<?php
				$rayon = NULL;
				foreach($types as $liste){
					if($liste['rayon'] != $rayon && $rayon != NULL)
						echo '</div><div class="rayon"><div class="rayon_name">' . $liste['rayon'] . '</div>';
					elseif($rayon == NULL)
						echo '<div class="rayon"><div class="rayon_name">' . $liste['rayon'] . '</div>';
					$u_q = ($liste['type_unit'] == 0) ? 'unité' : 'quantité';
					echo '<div class="categorie ' . $u_q . '" id="' . $liste['id'] . '">';
					echo '<div>' . $liste['type'] . '</div>';
					echo '</div>';
					$rayon = $liste['rayon'];
				}
				?>
			</div>
		</div>
	</div>
	<footer>
		<?php include('src/footer.php'); ?>
	</footer>
	<div id="popup"></div>
</div>
</body>
</html>
<?php
$db = null;
?>
