<?php 
 
require_once("includes.php");
require_once('functions.php');


$idInscriptionEpreuveInternaute = $_POST['idInscriptionEpreuveInternaute'];
$etat = $_POST['etat'];

$query = "UPDATE r_inscriptionepreuveinternaute SET dossard_retire = '".$etat."' WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
$result=$mysqli->query($query);

$nb = "SELECT sum(dossard_retire) as nb_dossard_retire FROM r_inscriptionepreuveinternaute WHERE dossard_retire like 'oui' AND idEpreuve = (SELECT idEpreuve FROM r_inscriptionepreuveinternaute WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute.")";
$nb_res = $mysqli->query($nb);
$nb_dossard_retire = mysqli_fetch_assoc($nb_res);

$aff= array('comeback'=>'OK','nb_dossard_retire'=>$nb_dossard_retire['nb_dossard_retire']);
echo json_encode($aff);	
			
?>

