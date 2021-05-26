<?php
/**************************************************************/
/* globals.php
/* Contient les fonctions d'execution courantes sur la bdd et de formatage des chaines.
/**************************************************************/

require_once('config.php');
require_once('db.php');



/************/
/* Cherche une valeur dans un tableau récurssivement */
/* $needle : la valeur à chercher
/* $haystack : le tableau dans lequel chercher
/*************/
function recursive_array_search($needle,$haystack) {
    foreach($haystack as $key=>$value) {
        $current_key=$key;
        if($needle===$value OR (is_array($value) && recursive_array_search($needle,$value) !== false)) {
            return $current_key;
        }
    }
    return false;
}

/************/
/* Cherche une clé dans un tableau récurssivement */
/* $needle : la clé à chercher
/* $tab : le tableau dans lequel chercher
/*************/
function recursive_key_search($needle, $tab)
{
    foreach($tab as $key=>$value) {
        $current_key=$key;
        if($needle===$key OR (is_array($value) && recursive_key_search($needle,$value) !== false)) {
            return $current_key;
        }
    }
    return false;
}

/************/
/* Convertit une date US en FR */
/* $date : la date à convertir
/*************/
function dateFr($date)
{
	return date('d/m/Y', strtotime($date));
}

/************/
/* Convertit un champ time sql en heures et minutes */
/* $heure : le champ time à convertir
/*************/
function heureminute($heure)
{
	$arrayheure = explode(':',$heure);
	$newheure = $arrayheure[0].':'.$arrayheure[1];
	return $newheure;
}

/************/
/* Convertit une quantité ou un volume */
/* $quantite : la quantite à convertir
/* $unite : l'unité d'entrée à changer ou non en g (0) ou mL (1)
/* $sens : à savoir si on doit convertir vers la base ou vers la sortie
/*************/
function conversionUnit($quantite, $unite, $sens = 'base'){
	if($sens == 'base'){
		switch($unite){
			case 'kg':
				$quantite *= 1000;
				$unite = 0;
				break;
				
			case 'L':
				$quantite *= 1000;
				$unite = 1;
				break;

			case 'cL':
				$quantite *= 10;
				$unite = 1;
				break;

			default:
				break;
		}
	}
	else{
		if($unite == 0){
			if($quantite >= 1000){
				$quantite /= 1000;
				$unite = 'kg';
			}
			else
				$unite = 'g';
		}
		else{
			if($quantite >= 1000){
				$quantite /= 1000;
				$unite = 'L';
			}
			elseif($quantite >= 10 && $quantite < 1000){
				$quantite /= 10;
				$unite = 'cL';
			}
			else
				$unite = 'mL';
		}
	}
	
	return array($quantite, $unite);
}
?>