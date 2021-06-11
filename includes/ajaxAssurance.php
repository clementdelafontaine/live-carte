<?php 
 
require_once("includes.php");
require_once('functions.php');
global $mysqli;

$idInscriptionEpreuveInternaute = $_GET['id'];
$id_epreuve = extract_champ_id_epreuve_internaute('idEpreuve',$idInscriptionEpreuveInternaute);
$id_parcours = extract_champ_id_epreuve_internaute(' 	idEpreuveParcours',$idInscriptionEpreuveInternaute);
$tarif = $_GET['tarif'];
$idAssurance = $_GET['idAssurance'];
$relais = 'non';

if ($_GET['action']=='i') {
	$bdd_assu  = "INSERT INTO r_insc_assurance_annulation (idEpreuve, idEpreuveParcours, idAssuranceAnnulation, idInternauteInscriptionref, idInternauteReferent, montant, relais) ";
	$bdd_assu .= "VALUE (".$id_epreuve.", ".$id_parcours.", ".$idAssurance.", ".$idInscriptionEpreuveInternaute.",0, ".$tarif.",'".$relais."')";  
	$result_bdd_assu = $mysqli->query($bdd_assu);	
			
	$query = "UPDATE r_inscriptionepreuveinternaute SET assurance = ".$tarif." WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
	$result=$mysqli->query($query);
	$_SESSION['panier'] += $tarif;
	//echo $_SESSION['panier'];
}

elseif ($_GET['action']=='d') {
	
	$query_temp  = "DELETE FROM r_insc_assurance_annulation  ";
	$query_temp .= "WHERE idEpreuve=".$id_epreuve;
	$query_temp .= " AND idEpreuveParcours=".$id_parcours;
	$query_temp .= " AND idInternauteInscriptionref=".$idInscriptionEpreuveInternaute;
	$query_temp .= " AND montant=".$tarif;
	//echo $query_temp;
	$result_temp = $mysqli->query($query_temp);

	$query = "UPDATE r_inscriptionepreuveinternaute SET assurance = 0 WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
	$result=$mysqli->query($query);
	$_SESSION['panier'] -= $tarif;
}
$aff= array('comeback'=>'OK');
echo json_encode($aff);	
			
?>

