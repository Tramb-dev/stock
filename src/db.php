<?php
/**************************************************************/
/* db.php
/* Contient les fonctions relatives aux connexions à la BDD
/**************************************************************/

/* Connection à la base de données */
function db_connect()
{
	try
	{
		$db = new PDO('mysql:host=' . SQL_DB . ';port=' . SQL_PORT . ';dbname=' . SQL_NAME, SQL_LOGIN, SQL_PASS, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
	}
	catch (PDOException $e)
	{
		print "Erreur !: " . $e->getMessage() . "<br/>";
		die();
	}
	return $db;
}

?>