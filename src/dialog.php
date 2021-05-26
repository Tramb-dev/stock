<?php 
/**************************************************************/
/* dialog.php
/* Permet la modification d'un produit
/**************************************************************/
error_reporting(E_ALL);


require_once('config.php');
require_once('db.php');

mb_internal_encoding('UTF-8');
$db = db_connect();

// Si on modifie une référence
if(isset($_GET['modif']) && $_GET['modif'] == true && isset($_GET['id']) && ctype_digit($_GET['id'])){
	$id = $_GET['id'];
	require_once('globals.php');
	// Recherche des types pour les inputs
	try{
		$req = $db->query('SELECT id, rayon, type FROM Type ORDER BY type');
		$types = $req->fetchAll(PDO::FETCH_ASSOC);

		$requete = $db->query('SELECT r.t_id, r.marque, r.produit, r.quantite, r.unite, r.dlc_dluo, r.lieu, r.code, t.type
						FROM Reference r
						LEFT JOIN Type t ON t.id = r.t_id
						WHERE r.id ="' . $id . '"');
		$ref = $requete->fetch(PDO::FETCH_ASSOC);
		
		$req2 = $db->query('SELECT DATE_FORMAT(expire, "%m/%Y") AS expire, nombre FROM Dlc WHERE r_id="' . $id . '" ORDER BY expire');
		$dlcs = $req2->fetchAll(PDO::FETCH_ASSOC);
		
		$convers = conversionUnit($ref['quantite'], $ref['unite'], 'sortie');
		$quantite = explode('.', $convers[0]);
		if(isset($quantite[1])){
			if($quantite[1] == '00')
				$ref['quantite'] = $quantite[0];
			else
				$ref['quantite'] = $quantite[0] . '.' . $quantite[1];
		}
	}
	catch (PDOException $e){
		print "Erreur !: " . $e->getMessage() . "<br/>";
		die();
	}
?>
	<form method="post" action="stock.php" id="formRef">
		<div id="affich">
			<ul class="grid">
				<fieldset class="modRef">
				<legend>Modifier la référence</legend>
					<div class="input requit">
						<label for="type">Type : </label><select name="type" id="type">
						<?php
							foreach($types as $divste){
								echo '<option value="' . $divste['id'] . '" ' .  (($divste['id'] == $ref['t_id']) ? 'selected="selected"' : '') . '>' . $divste['type'] . '</option>';
							}
						?></select>
					</div>
					<div class="input">
						<label for="marque">Marque : </label><input type="text" name="marque" id="marque" value="<?php echo $ref['marque']; ?>">
					</div>
					<div class="input">
						<label for="produit">Produit : </label><input type="text" name="produit" id="produit" value="<?php echo $ref['produit']; ?>">
					</div>
					<div class="input">
						<label for="quantite">Quantité : </label><input type="number" name="quantite" id="quantite" min="0" step=".01" value="<?php echo $ref['quantite']; ?>">
					</div>
					<div class="input">
						<span>Unités : </span><input type="radio" id="g" name="unite" value="g" <?php echo (($convers[1] == 'g') ? 'checked' : ''); ?> ><label for="g">g</label>
						<input type="radio" id="kg" name="unite" value="kg" <?php echo (($convers[1] == 'kg') ? 'checked' : ''); ?>><label for="kg">kg</label>
						<input type="radio" id="mL" name="unite" value="mL" <?php echo (($convers[1] == 'mL') ? 'checked' : ''); ?>><label for="mL">mL</label>
						<input type="radio" id="cL" name="unite" value="cL" <?php echo (($convers[1] == 'cL') ? 'checked' : ''); ?>><label for="cL">cL</label>
						<input type="radio" id="L" name="unite" value="L" <?php echo (($convers[1] == 'L') ? 'checked' : ''); ?>><label for="L">L</label>
					</div>
					<div class="input">
						<span>DLC ou DLUO ? </span><input type="radio" id="dlc" name="dlc_dluo" value="0" <?php echo (($ref['dlc_dluo'] == 0) ? 'checked' : ''); ?>><label for="dlc">DLC</label>
						<input type="radio" id="dluo" name="dlc_dluo" value="1" <?php echo (($ref['dlc_dluo'] == 1) ? 'checked' : ''); ?>><label for="dluo">DLUO</label>
						<input type="radio" id="wt-dlc" name="dlc_dluo" value="2" <?php echo (($ref['dlc_dluo'] == 2) ? 'checked' : ''); ?>><label for="wt-dlc">Sans</label>
					</div>
					<div class="input">
						<span>Lieu de stockage : </span><input type="radio" id="cave" name="diveu" value="0" <?php echo (($ref['diveu'] == 0) ? 'checked' : ''); ?>><label for="cave">Cave</label>
						<input type="radio" id="congel" name="diveu" value="1" <?php echo (($ref['diveu'] == 1) ? 'checked' : ''); ?>><label for="congel">Congélateur</label>
					</div>
					<div class="input requit">
						<label for="code">Code barre : </label><input type="text" name="code" id="code" value="<?php echo $ref['code']; ?>">
					</div>
				</fieldset>
			</ul>
			<ul class="grid">
				<fieldset class="modRef">
				<legend>Produits en stock</legend>
					<?php
					try{
						$req = $db->query('SELECT id, DATE_FORMAT(expire, "%m/%Y") AS expire, nombre, date_entree FROM Dlc WHERE r_id="' . $id . '" ORDER BY expire, date_entree');
						while($dlcs = $req->fetch(PDO::FETCH_ASSOC)){
								echo '<div class="dlcs">';
									echo '<div>Date : ' . (($dlcs['expire'] != null) ? $dlcs['expire'] : $dlcs['date_entree']) . '</div>';
									echo '<div>Nombre : ' . $dlcs['nombre'] . '</div>';
								echo '</div>';
						}
					}
					catch (PDOException $e){
						print "Erreur !: " . $e->getMessage() . "<br/>";
						die();
					}
					?>
				</fieldset>
			</ul>
		</div>
		<input type="hidden" value="<?php echo $id; ?>" name="id">
		<input type="submit" value="Enregistrer" name="save">
		<input type="submit" value="Supprimer" name="delete">
	</form>

<?php
}
// Si on ajoute du stock à une référence
elseif(isset($_GET['ajout']) && $_GET['ajout'] == true && isset($_GET['id']) && ctype_digit($_GET['id'])){
	$id = $_GET['id'];
	
	try{
		$sql = $db->query('SELECT r.marque, r.produit, r.quantite, r.unite, r.dlc_dluo, t.type
										FROM Reference r
										LEFT JOIN Type t ON t.id = r.t_id
										WHERE r.id="' . $id . '"');
		$name = $sql->fetch(PDO::FETCH_ASSOC);		
	}
	catch (PDOException $e){
		print "Erreur !: " . $e->getMessage() . "<br/>";
		die();
	}
	
	if($name['marque'] == null)
		echo '<h1>' . $name['type'] . '</h1>';
	else
		echo '<h1>' . $name['marque'] . ' ' . $name['produit'] . '</h1>';
	
	if($name['quantite'] != null)
		echo '<h4>' . $name['quantite'] . ' ' . $name['unite'] . '</h4>';
	
	?>
	<form method="post" action="reference.php" id="formAjout">
		<fieldset>
			<?php
			switch($name['dlc_dluo']){
				case 0: 
					echo '<div class="dlc"><span>dlc</span></div><div class="input"><label for="date_dlc">Date : </label><input type="date" name="date_dlc" id="date_dlc"></div>';
					break;
				
				case 1: 
					echo '<div class="dluo"><span>dluo</span></div><div class="input"><label for="date_dlc">Date : </label><input type="date" name="date_dlc" id="date_dlc"></div>';
					break;
			}
			?>
			<div class="input">
				<label for="nb_dlc">Nombre d'éléments à ajouter : </label><input type="number" name="nb_dlc" id="nb_dlc" value="1">
			</div>
		</fieldset>
		<input type="hidden" value="<?php echo $id; ?>" name="id">
		<input type="hidden" value="<?php echo $name['dlc_dluo']; ?> name="dlc_dluo">
		<input type="submit" value="Ajouter" name="ajout">
	</form>
	<?php
}
// Si on enlève du stock, on enlève la première dlc arrivant à expiration, avec une confirmation de l'utilisateur
elseif(isset($_GET['retrait']) && $_GET['retrait'] == true && isset($_GET['id']) && ctype_digit($_GET['id'])){
	$id = $_GET['id'];
	
	try{
		$sql = $db->query('SELECT r.marque, r.produit, r.quantite, r.unite, r.dlc_dluo, t.type
										FROM Reference r
										LEFT JOIN Type t ON t.id = r.t_id
										WHERE r.id="' . $id . '"');
		$name = $sql->fetch(PDO::FETCH_ASSOC);		
	}
	catch (PDOException $e){
		print "Erreur !: " . $e->getMessage() . "<br/>";
		die();
	}
	
	if($name['marque'] == null)
		echo '<h1>' . $name['type'] . '</h1>';
	else
		echo '<h1>' . $name['marque'] . ' ' . $name['produit'] . '</h1>';
	
	if($name['quantite'] != null)
		echo '<h4>' . $name['quantite'] . ' ' . $name['unite'] . '</h4>';
	
	?>
	<form method="post" action="reference.php" id="formRetrait">
			<div>
		<?php
		try{
			$req = $db->query('SELECT id, DATE_FORMAT(expire, "%m/%Y") AS expire, nombre, date_entree FROM Dlc WHERE r_id="' . $id . '" ORDER BY expire, date_entree');
			while($dlcs = $req->fetch(PDO::FETCH_ASSOC)){
				echo '<fieldset>';
					echo '<div class="dlcs">';
						echo '<div>Date : ' . (($dlcs['expire'] != null) ? $dlcs['expire'] : $dlcs['date_entree']) . '</div>';
						echo '<div>Nombre : ' . $dlcs['nombre'] . '</div>';
					echo '</div>';
					echo '<div class="choixDlc">';
						echo '<input type="checkbox" id="' . $dlcs['id'] . '" name="aRetirer[]" value="' . $dlcs['id'] . '"><label for="aRetirer">Retirer ?</label>';
						echo '<label for="nb_stock">Nombre à retirer : </label><input type="number" name="nb_stock[]" id="nb_stock" value="0">';
					echo '</div>';
				echo '</fieldset>';
			}
		}
		catch (PDOException $e){
			print "Erreur !: " . $e->getMessage() . "<br/>";
			die();
		}
		?>
			</div>
		<input type="hidden" value="<?php echo $id; ?>" name="id">
		<input type="submit" value="Retirer" name="retrait">
	</form>
	<?php
}
// Si on modifie une catégorie
elseif(isset($_GET['cat']) && $_GET['cat'] == true && isset($_GET['id']) && ctype_digit($_GET['id'])){
	$id = $_GET['id'];
	// Recherche des types pour les inputs
	try{
		$req = $db->query('SELECT rayon, type FROM Type ORDER BY type');
		$types = $req->fetchAll(PDO::FETCH_ASSOC);
		
		$req2 = $db->query('SELECT rayon, type, type_unit FROM Type WHERE id=' . $id);
		$current = $req2->fetch(PDO::FETCH_ASSOC);
	}
	catch (PDOException $e){
		print "Erreur !: " . $e->getMessage() . "<br/>";
		die();
	}
	?>
		<form method="post" action="categorie.php" id="formCat">
		<div id="affich">
			<ul class="grid">
				<fieldset class="modRef">
				<legend>Modifier la catégorie <?php  ?></legend>
					<li class="input">
						<label for="rayon">Rayon : </label>
						<input list="rayons" name="rayon" id="rayon" value="<?php echo $current['rayon']; ?>">
						<datalist id="rayons">
							<?php
								foreach($types as $liste){
									if($liste['id'] == $id)
										$selected = 'selected';
									else
										$selected = '';
									echo '<option value="' . $liste['rayon'] . '" ' . $selected . '>';
								}
							?>
						</datalist>					
					</li>
					<li class="input">
						<label for="cat">Catégorie : </label>
						<input list="types" name="type" id="type" type="text" value="<?php echo $current['type']; ?>">
						<datalist id="types">
							<?php
								foreach($types as $liste){
									echo '<option value="' . $liste['type'] . '" ' . $selected . '>';
								}
							?>
						</datalist>					
					</li>
					<li class="input">
						<span>Stockage par unité (µ) ou quantité (poids/volume) ?</span>
						<input type="radio" id="u" name="u_q" value="0" <?php echo ($current['type_unit'] == 0) ? 'checked' : ''; ?>><label for="u">unité</label>
						<input type="radio" id="q" name="u_q" value="1" <?php echo ($current['type_unit'] == 1) ? 'checked' : ''; ?>><label for="q">quantité</label>
					</li>
					</fieldset>
			</ul>
		</div>
		<input type="hidden" value="<?php echo $id; ?>" name="id">
		<input type="submit" value="Enregistrer" name="save">
		<input type="submit" value="Supprimer" name="delete">
	</form>
	<?php
}
// Si on modifie les références d'une catégorie
elseif(isset($_GET['type']) && $_GET['type'] == true && isset($_GET['id']) && ctype_digit($_GET['id'])){
	$id = $_GET['id'];

	try{
		// Recherche des types pour les inputs
		$req = $db->query('SELECT id, rayon, type, type_unit FROM Type ORDER BY type');
		$types = $req->fetchAll(PDO::FETCH_ASSOC);
		$key = array_search($id, array_column($types, 'id'));
		
		echo '<h1>' . $types[$key]['type'] . '</h1>';
		
		$req2 = $db->query('SELECT id, t_id, marque, produit, quantite, unite, dlc_dluo, lieu, code
							FROM Reference r
							WHERE t_id = ' . $id . ' AND EXISTS (SELECT nombre FROM Dlc d WHERE d.nombre > 0 AND r.id = d.r_id)');
		while($ref = $req2->fetch(PDO::FETCH_ASSOC)){
			echo '<div class="cat_ref">';
				echo '<div>' . $ref['marque'] . ' ' . $ref['produit'] . '</div>';
				echo '<div>' . $ref['quantite'] . ' ' . $ref['unite'];
				echo '</div>';
				$req3 = $db->query('SELECT id, DATE_FORMAT(expire, "%d/%m/%Y") AS expire, nombre, DATE_FORMAT(date_entree, "%d/%m/%Y") AS date_entree FROM Dlc WHERE r_id = ' . $ref['id']);
				while($dlc = $req3->fetch(PDO::FETCH_ASSOC)){
					switch($ref['dlc_dluo']){
						case 0:
							echo '<div class="cat_dlc"><span class=dlc">' . $dlc['expire'] . '</span><span class="nombre">' . $dlc['nombre'] . '</span></div>';
							break;
							
						case 1:
							echo '<div class="cat_dlc"><span class=dluo">' . $dlc['expire'] . '</span><span class="nombre">' . $dlc['nombre'] . '</span></div>';
							break;
							
						case 2:
							echo '<div class="cat_dlc"><span class=wtdlc">' . $dlc['date_entree'] . '</span><span class="nombre">' . $dlc['nombre'] . '</span></div>';
							break;
							
						default:
							break;
					}
				}
			echo '</div>';
		}
	}
	catch (PDOException $e){
		print "Erreur !: " . $e->getMessage() . "<br/>";
		die();
	}
}

$db = null;
?>