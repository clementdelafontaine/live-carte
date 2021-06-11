<?php

require_once("includes.php");
require_once('functions.php');
global $mysqli;
$idInternaute = $_POST['idInternaute'];
$idInscriptionEpreuveInternaute = $_POST['id'];
$etat = $_POST['etat'];
$type_epreuve = $_POST['type_epreuve'];

if (!empty($idInscriptionEpreuveInternaute))
{

		$query ="UPDATE r_inscriptionepreuveinternaute SET `vli` = '".$etat."' WHERE `r_inscriptionepreuveinternaute`.`idInscriptionEpreuveInternaute` = ".$idInscriptionEpreuveInternaute;
		$result=$mysqli->query($query);

	
	
}
else
{
	if ($etat =='non')
	{
		$query ="DELETE FROM r_internautemailing WHERE idInternaute=".$idInternaute;
		$result=$mysqli->query($query);
	}
	else
	{
		$query = "INSERT INTO r_internautemailing (idInternaute,idTypeMailing,date) VALUES (".$idInternaute.",".$type_epreuve.",NOW())";
		$result=$mysqli->query($query);
	}
}
/*
$query = "UPDATE r_internaute SET newsletter = '".$etat."' WHERE idInternaute = ".$idInternaute;
$result=$mysqli->query($query);
/*
$nb = "SELECT sum(dossard_retire) as nb_dossard_retire FROM r_inscriptionepreuveinternaute WHERE dossard_retire like 'oui' AND idEpreuve = (SELECT idEpreuve FROM r_inscriptionepreuveinternaute WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute.")";
$nb_res = $mysqli->query($nb);
$nb_dossard_retire = mysqli_fetch_assoc($nb_res);
*/

$aff= array('comeback'=>'OK');
echo json_encode($aff);	

?>