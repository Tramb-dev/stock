<?php 
/**************************************************************/
/* new.php
/* Permet l'ajout d'un nouveau produit
/**************************************************************/
error_reporting(E_ALL);


require_once('src/config.php');
require_once('src/db.php');
require_once('src/globals.php');

mb_internal_encoding('UTF-8');


?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Stock</title>
	<link rel="stylesheet" href="css/main.css" />
	<link rel="icon" type="image/png" href="favicon.jpg">
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
				$req = $db->query('SELECT rayon, type FROM Type ORDER BY type');
				$types = $req->fetchAll(PDO::FETCH_ASSOC);
			}
			catch (PDOException $e){
				print "Erreur !: " . $e->getMessage() . "<br/>";
				die();
			}
			
			// Recherche des produits pour les inputs
			try{
				$req = $db->query('SELECT marque, produit, code FROM Reference ORDER BY marque');
				$produits = $req->fetchAll(PDO::FETCH_ASSOC);
			}
			catch (PDOException $e){
				print "Erreur !: " . $e->getMessage() . "<br/>";
				die();
			}

			// Si on vient d'ajouter une nouvelle référence
			if(isset($_POST['enregistrerRef'])){
				if(isset($_POST['type']) && isset($_POST['code'])){
					$type = htmlspecialchars($_POST['type']);
					$marque = (isset($_POST['marque'])) ? htmlspecialchars($_POST['marque']) : NULL;
					$produit = (isset($_POST['produit'])) ? htmlspecialchars($_POST['produit']) : NULL;
					$quantite = (isset($_POST['quantite'])) ? htmlspecialchars($_POST['quantite']) : '';
					$unite = (isset($_POST['unite'])) ? htmlspecialchars($_POST['unite']) : '';
					$lieu = (isset($_POST['lieu'])) ? htmlspecialchars($_POST['lieu']) : '';
					$dlc_dluo = (isset($_POST['dlc_dluo'])) ? htmlspecialchars($_POST['dlc_dluo']) : 2;
					$date = (isset($_POST['date_dlc']) && $_POST['date_dlc'] != '') ? htmlspecialchars($_POST['date_dlc']) : NULL;
					$nb_dlc = (isset($_POST['nb_dlc']) && $_POST['nb_dlc'] > 0) ? htmlspecialchars($_POST['nb_dlc']) : 1;
					$code = htmlspecialchars($_POST['code']);
					
					$tab = conversionUnit($quantite, $unite, 'base');

					try{
						$req = $db->query('SELECT id FROM Type WHERE type="' . $type . '"');
						$t_id = $req->fetch(PDO::FETCH_ASSOC);
					}
					catch (PDOException $e){
						print "Erreur !: " . $e->getMessage() . "<br/>";
						die();
					}

					if(is_array($t_id)){
						try{
							$db->beginTransaction();
							$req = $db->prepare('INSERT INTO Reference (t_id, marque, produit, quantite, unite, dlc_dluo, lieu, code) VALUES (:t_id, :marque, :produit, :quantite, :unite, :dlc_dluo, :lieu, :code)');
							
							$req->bindParam(':t_id', $t_id['id'], PDO::PARAM_INT);
							$req->bindParam(':marque', $marque, PDO::PARAM_STR);
							$req->bindParam(':produit', $produit, PDO::PARAM_STR);
							$req->bindParam(':quantite', $tab[0], PDO::PARAM_STR);
							$req->bindParam(':unite', $tab[1], PDO::PARAM_INT);
							$req->bindParam(':dlc_dluo', $dlc_dluo, PDO::PARAM_INT);
							$req->bindParam(':lieu', $lieu, PDO::PARAM_INT);
							$req->bindParam(':code', $code, PDO::PARAM_STR);
							
							$req->execute();
							
							$req_dlc = $db->prepare('INSERT INTO Dlc (r_id, expire, nombre) VALUES (LAST_INSERT_ID(), :expire, :nombre)');
							
							$req_dlc->bindParam(':expire', $date, PDO::PARAM_STR);
							$req_dlc->bindParam(':nombre', $nb_dlc, PDO::PARAM_INT);
							
							$req_dlc->execute();
							
							$db->commit();
							
							$message = 'Produit enregistré';
							array_unshift($produits, array('marque' => $marque, 'produit' => $produit, 'code' => $code));
						}
						catch (PDOException $e){
							print "Erreur !: " . $e->getMessage() . "<br/>";
							$db->rollback();
							die();
						}
					}
					else
						$message = 'Erreur dans le choix du type';
				}
				else
					$message = 'Type / code manquant ou code barre déjà existant';
			}			
		?>
			<form method="post" action="new.php" id="formProduit">
				<fieldset name="ajoutRef">
					<legend>Ajout d'un produit</legend>
					<ul class="grid">
						<li class="input requit">
							<label for="type">Type : </label><select name="type" id="type">
							<?php
								foreach($types as $liste){
									echo '<option value="' . $liste['type'] . '">' . $liste['type'] . '</option>';
								}
							?></select>
						</li>
						<li class="input">
							<label for="marque">Marque : </label><input list="marques" name="marque" id="marque">
							<datalist id="marques">
								<?php
									foreach($produits as $liste){
										echo '<option value="' . $liste['marque'] . '">';
									}
								?>
							</datalist>
						</li>
						<li class="input">
							<label for="produit">Produit : </label><input list="produits" name="produit" id="produit">
							<datalist id="produits">
								<?php
									foreach($produits as $liste){
										echo '<option value="' . $liste['produit'] . '">';
									}
								?>
							</datalist>
						</li>
						<li class="input">
							<label for="quantite">Quantité : </label><input type="number" name="quantite" id="quantite" min="0" step=".01">
						</li>
						<li class="input">
							<span>Unités : </span><input type="radio" id="g" name="unite" value="g" checked><label for="g">g</label>
							<input type="radio" id="kg" name="unite" value="kg"><label for="kg">kg</label>
							<input type="radio" id="mL" name="unite" value="mL"><label for="mL">mL</label>
							<input type="radio" id="cL" name="unite" value="cL"><label for="cL">cL</label>
							<input type="radio" id="L" name="unite" value="L"><label for="L">L</label>
						</li>
						<li class="input">
							<span>DLC ou DLUO ? </span><input type="radio" id="dlc" name="dlc_dluo" value="0"><label for="dlc">DLC</label>
							<input type="radio" id="dluo" name="dlc_dluo" value="1" checked><label for="dluo">DLUO</label>
							<input type="radio" id="wt-dlc" name="dlc_dluo" value="2"><label for="wt-dlc">Sans</label>
						</li>
						<li class="input">
							<label for="date_dlc">Date : </label><input type="date" name="date_dlc" id="date_dlc">
						</li>
						<li class="input">
							<label for="nb_dlc">Nombre d'éléments à ajouter : </label><input type="number" name="nb_dlc" id="nb_dlc" value="1">
						</li>
						<li class="input">
							<span>Lieu de stockage : </span><input type="radio" id="cave" name="lieu" value="0" checked><label for="cave">Cave</label>
							<input type="radio" id="congel" name="lieu" value="1"><label for="congel">Congélateur</label>
						</li>
						<li class="input requit">
							<label for="code">Code barre : </label><input type="text" name="code" id="code">
						</li>
					</ul>
				</fieldset>
				<input type="submit" name="enregistrerRef" value="Enregistrer le produit"></input><br />
			</form>
		</div>
		<footer>
			<?php include('src/footer.php'); ?>
		</footer>
	</div>
	<div id="popup"></div>
</div>
</body>
</html>
<?php
$db = null;
?>
