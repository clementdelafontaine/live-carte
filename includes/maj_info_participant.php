<?php 


require_once("includes.php");
require_once('functions.php');
require_once('functions_mail.php');

global $mysqli;
//$chemin=getcwd().DIRECTORY_SEPARATOR;
$chemin='';
$rep_fichiers_temp  = $_SERVER["DOCUMENT_ROOT"]."/dl".DIRECTORY_SEPARATOR;
$rep_fichiers_inscription  = $_SERVER["DOCUMENT_ROOT"]."/fichiers_insc".DIRECTORY_SEPARATOR;
//if(rename($chemin.$rep_fichiers_temp."test.txt", $rep_fichiers_inscription."test.jpg")) { echo "OK"; } else {echo "KO";print_r(error_get_last());};


$id_internaute = $_POST['id_internaute'];
$id_inscription_epreuve_internaute = $_POST['idInscriptionEpreuveInternaute'];
$id_epreuve = $_POST['id_epreuve'];
$id_parcours = $_POST['id_parcours'];
$id_session = $_POST['id_session'];
$date_certificat = $_POST['date_certificat'];
$action = $_POST['action'];


if ($action=='CERTIF') {
	/*
		$query  = "SELECT idTypeEpreuve ";
		$query .= "FROM r_epreuve";
		$query .=" WHERE idEpreuve = ".$id_epreuve;
		$result = $mysqli->query($query);
		$data_epreuve=mysqli_fetch_array($result);
	*/
		$type_certificat_bdd = type_epreuve($id_epreuve);
		$query_update_certificat_fichier = $type_certificat_bdd['type_nom_bdd'];
		$query_insert_certificat = $type_certificat_bdd['nom_date_nom_bdd'];
		$query_update_certificat = $query_insert_certificat."= ' NOW() ' ";
	/*	
		if ($data_epreuve['idTypeEpreuve'] == 4) {
			$query_update_certificat = "peremption_ski= '".datefr2en($date_certificat)."' ";
			$query_update_certificat_fichier = "fichier_ski";
			$query_insert_certificat = "peremption_ski";
			
		}
		elseif ($data_epreuve['idTypeEpreuve'] == 1) {
			$query_update_certificat = "peremption_tri= '".datefr2en($date_certificat)."' ";
			$query_update_certificat_fichier = "fichier_tri";
			$query_insert_certificat = "peremption_tri";
		}
		elseif ($data_epreuve['idTypeEpreuve'] == 2) {
			$query_update_certificat = "peremption_vel= '".datefr2en($date_certificat)."' ";
			$query_update_certificat_fichier = "fichier_vel";
			$query_insert_certificat = "peremption_vel";
		}
		else {
		
			$query_update_certificat = "peremption_cap= '".datefr2en($date_certificat)."' ";
			$query_update_certificat_fichier = "fichier_cap";
			$query_insert_certificat = "peremption_cap";
		}
	*/	
			
				
								//gestion des certificats médicaux
							
							
								//test de présence de fichier certificat medicale en base temporaire
								$query_fichier  = "SELECT nom_fichier_reel,nom_fichier_temp,nom_fichier_affichage,date ";
								$query_fichier .= "FROM r_fichier_epreuve_temp ";
								$query_fichier .= "WHERE id_session =  '".$id_session."' ";
								$query_fichier .= "AND type = 'insc_certif' ";
								$query_fichier .= "AND num_parcours = 0 ";
								$query_fichier .= "ORDER BY date desc ";
								$query_fichier .= "LIMIT 1 ";
								$result_fichier = $mysqli->query($query_fichier);
								$row_temp=mysqli_fetch_array($result_fichier);
								//if ($row_temp != FALSE) { echo "OK"; } else { echo "KO"; }
							
							
							if ($row_temp != FALSE) {
							
								//existe il un ancien certificat dans ce sport ?
								$query  = "SELECT idEpreuveFichier, nom_fichier ";
								$query .= "FROM r_epreuvefichier";
								$query .=" INNER JOIN r_internaute ON r_epreuvefichier.idEpreuveFichier = r_internaute.".$query_update_certificat_fichier;
								$query .=" WHERE r_internaute.idInternaute = ".$id_internaute;
								$result = $mysqli->query($query);
								$row=mysqli_fetch_array($result);
								
								//si fichier présent on efface
								if(!empty($row['idEpreuveFichier'])) {
									$query_del  = "DELETE FROM r_epreuvefichier ";
									$query_del .= "WHERE idEpreuveFichier =  ".$row['idEpreuveFichier']." ";
									$result_del = $mysqli->query($query_del);;
									
									if (file_exists($rep_fichiers_inscription.$row['nom_fichier'])) {
										unlink($rep_fichiers_inscription.$row['nom_fichier']);
									}
									
								}
								
								//insertion du nouveau fichier							
								$query_fichier  = "SELECT nom_fichier_reel,nom_fichier_temp,nom_fichier_affichage,date ";
								$query_fichier .= "FROM r_fichier_epreuve_temp ";
								$query_fichier .= "WHERE id_session =  '".$id_session."' ";
								$query_fichier .= "AND type = 'insc_certif' ";
								$query_fichier .= "AND num_parcours = 0 ";
								$query_fichier .= "ORDER BY date desc ";
								$query_fichier .= "LIMIT 1 ";
								//echo "SELECTION DES FICHIERS TEMPORAIRES : ".$query_fichier;
								$result_fichier = $mysqli->query($query_fichier);
								while (($row=mysqli_fetch_array($result_fichier)) != FALSE)
								{
									if(isset($row['nom_fichier_temp']))
									{
										//echo $chemin.$rep_fichiers_temp.$row['nom_fichier_temp']."---".$chemin.$rep_fichiers_inscription.$row['nom_fichier_reel'];	
										//rename($chemin.$rep_fichiers_temp."test.txt", $chemin.$rep_fichiers_epreuves."test.jpg");
										//rename($chemin.$rep_fichiers_temp."822b5-photo_epreuve-3893536a2721ebf320e26676bf68d150.jpg", $rep_fichiers_epreuves.$row['nom_fichier_reel']);
										if(rename($chemin.$rep_fichiers_temp.$row['nom_fichier_temp'], $chemin.$rep_fichiers_inscription.$row['nom_fichier_reel']))
										{
											
											$query_ins  = "INSERT INTO r_epreuvefichier (idEpreuve, idEpreuveParcours, nom_fichier,nom_fichier_affichage,type,date) ";
											$query_ins .= "VALUES(".$id_epreuve.",".$id_parcours.",'".$row['nom_fichier_reel']."','".$row['nom_fichier_affichage']."','insc_certif','".$row['date']."')";
											$result = $mysqli->query($query_ins);
											$id_fichier_certif = $mysqli->insert_id;
											
											//echo "INSERTION DES FICHIERS: ".$query_ins;
	
											$query_del_fichier_temp  = "DELETE FROM r_fichier_epreuve_temp ";
											$query_del_fichier_temp .= "WHERE id_session =  '".$id_session."' ";
											$query_del_fichier_temp .= "AND type = 'insc_certif' ";
											$query_del_fichier_temp .= "AND num_parcours = 0 ";
											$query_del_fichier_temp .= "AND nom_fichier_temp = '".$row['nom_fichier_temp']."'";
											//echo $query_del;
											$result_del_fichier_temp = $mysqli->query($query_del_fichier_temp);
											
											$query  = "UPDATE r_internaute SET ";
											$query .= $query_update_certificat_fichier." = '".$id_fichier_certif."', ";
											$query .= $query_update_certificat;
											$query .= " WHERE idInternaute='".$id_internaute."'";
											$result_query = $mysqli->query($query);
											
											$aff= array('comeback'=>'OK');
											echo json_encode($aff);
											
											//$table_resume_array[$_POST['id_parcours_en_cours_'][$j]][] = array ( 'certif_medical' => 'fourni');
											 
										    //array_push($table_resume_array[$_POST['id_parcours_en_cours_'][$j]], array ( 'certif_medical' => 'fourni'));
										}else { 											
										$aff= array('comeback'=>'OK');
										echo json_encode($aff);}
									}
								}					
							}
							else
							{
								$query_fichier  = "SELECT nom_fichier_reel,nom_fichier_temp,nom_fichier_affichage,date ";
								$query_fichier .= "FROM r_fichier_epreuve_temp ";
								$query_fichier .= "WHERE id_session =  '".$id_session."' ";
								$query_fichier .= "AND type = 'insc_certif' ";
								$query_fichier .= "AND num_parcours = 0";
								//echo "SELECTION DES FICHIERS TEMPORAIRES - pas de besoin: ".$query_fichier;
								$result_fichier = $mysqli->query($query_fichier);
								
								while (($row=mysqli_fetch_array($result_fichier)) != FALSE)
								{
									//echo"aaaa";
									if (file_exists($chemin.$rep_fichiers_temp.$row['nom_fichier_temp'])) {
										unlink($chemin.$rep_fichiers_temp.$row['nom_fichier_temp']);
									}
									$query_del_fichier_temp  = "DELETE FROM r_fichier_epreuve_temp ";
									$query_del_fichier_temp .= "WHERE id_session =  '".$id_session."' ";
									$query_del_fichier_temp .= "AND type = 'insc_certif' ";
									$query_del_fichier_temp .= "AND num_parcours = 0 ";
									$query_del_fichier_temp .= "AND nom_fichier_temp = '".$row['nom_fichier_temp']."'";
									//echo $query_del;
									$result_del_fichier_temp = $mysqli->query($query_del_fichier_temp);
									
								
								}
											$aff= array('comeback'=>'KO');
											echo json_encode($aff);
							}
			
} 
elseif ($action=='PARENTALE') 
{
							
							
							$query_fichier  = "SELECT nom_fichier_reel,nom_fichier_temp,nom_fichier_affichage,date ";
							$query_fichier .= "FROM r_fichier_epreuve_temp ";
							$query_fichier .= "WHERE id_session =  '".$id_session."' ";
							$query_fichier .= "AND type = 'insc_parentale' ";
							$query_fichier .= "AND num_parcours = 0";
							//echo "SELECTION DES FICHIERS TEMPORAIRES [AUTO PARENTALE]: ".$query_fichier;
							$result_fichier = $mysqli->query($query_fichier);
							$row_temp=mysqli_fetch_array($result_fichier);
							
							if ( $row_temp != FALSE ) {
					
								//existe il déja une autorisation parentale?
								$query  = "SELECT r_epreuvefichier.idEpreuveFichier, nom_fichier ";
								$query .= "FROM r_epreuvefichier";
								$query .=" INNER JOIN r_auto_parentale ON r_epreuvefichier.idEpreuveFichier = r_auto_parentale.idEpreuveFichier";
								$query .=" WHERE r_auto_parentale.idInternaute = ".$id_internaute;
								$query .=" AND r_auto_parentale.idEpreuveParcours = ".$id_parcours;
								//echo $query;
								$result = $mysqli->query($query);
								$row=mysqli_fetch_array($result);
								
								//si fichier présent on efface
								if(!empty($row['idEpreuveFichier'])) {
									
									//suppression du fichier
									$query_del  = "DELETE FROM r_epreuvefichier ";
									$query_del .= "WHERE idEpreuveFichier =  ".$row['idEpreuveFichier']." ";
									$result_del = $mysqli->query($query_del);
									
									if (file_exists($rep_fichiers_inscription.$row['nom_fichier'])) {
										unlink($rep_fichiers_inscription.$row['nom_fichier']);
									}
									//suppression dans R-parentale
									
									$query_del  = "DELETE FROM r_auto_parentale ";
									$query_del .= "WHERE idEpreuveFichier =  ".$row['idEpreuveFichier']." ";
									$result_del = $mysqli->query($query_del);
								}
								
								//insertion du nouveau fichier							
								$query_fichier  = "SELECT nom_fichier_reel,nom_fichier_temp,nom_fichier_affichage,date ";
								$query_fichier .= "FROM r_fichier_epreuve_temp ";
								$query_fichier .= "WHERE id_session =  '".$id_session."' ";
								$query_fichier .= "AND type = 'insc_parentale' ";
								$query_fichier .= "AND num_parcours = 0";
								//echo "SELECTION DES FICHIERS TEMPORAIRES [AUTO PARENTALE]: ".$query_fichier;
								$result_fichier = $mysqli->query($query_fichier);
								while (($row=mysqli_fetch_array($result_fichier)) != FALSE)
								{
									if(isset($row['nom_fichier_temp']))
									{
										//echo $chemin.$rep_fichiers_temp.$row['nom_fichier_temp']."---".$rep_fichiers_inscription.$row['nom_fichier_reel'];	
										//rename($chemin.$rep_fichiers_temp."test.txt", $rep_fichiers_epreuves."test.jpg");
										//rename($chemin.$rep_fichiers_temp."822b5-photo_epreuve-3893536a2721ebf320e26676bf68d150.jpg", $rep_fichiers_epreuves.$row['nom_fichier_reel']);
										if(rename($chemin.$rep_fichiers_temp.$row['nom_fichier_temp'], $rep_fichiers_inscription.$row['nom_fichier_reel']))
										{
											
											$query_ins  = "INSERT INTO r_epreuvefichier (idEpreuve, idEpreuveParcours, nom_fichier,nom_fichier_affichage,type,date) ";
											$query_ins .= "VALUES(".$_POST['id_epreuve'].",".$id_parcours.",'".$row['nom_fichier_reel']."','".$row['nom_fichier_affichage']."','insc_parentale','".$row['date']."')";
											$result = $mysqli->query($query_ins);
											$id_fichier_certif = $mysqli->insert_id;
											
											//echo "INSERTION DES FICHIERS: ".$query_ins;
	
											$query_del_fichier_temp  = "DELETE FROM r_fichier_epreuve_temp ";
											$query_del_fichier_temp .= "WHERE id_session =  '".$id_session."' ";
											$query_del_fichier_temp .= "AND type = 'insc_parentale' ";
											$query_del_fichier_temp .= "AND num_parcours = ".$j." ";
											$query_del_fichier_temp .= "AND nom_fichier_temp = '".$row['nom_fichier_temp']."'";
											//echo $query_del;
											$result_del_fichier_temp = $mysqli->query($query_del_fichier_temp);
											
											$query  = "INSERT INTO r_auto_parentale (idEpreuveFichier, idInternaute, idInscriptionEpreuveInternaute, idEpreuve ,idEpreuveParcours) VALUES ( ";
											$query .= "".$id_fichier_certif.", ";
											$query .= "".$id_internaute.", ";
											$query .= "".$id_inscription_epreuve_internaute.", ";
											$query .= "".$id_epreuve.", ";
											$query .= "".$id_parcours.") ";
											//echo $query;
											$result_query = $mysqli->query($query);
										
										
										}
									}
								}
										$aff= array('comeback'=>'OK');
										echo json_encode($aff);}
								//$etat_auto_parentale= '<span class="label label-warning">Fournie</span>';
								//$etat_auto_parentale_bdd= 'oui';
								//array_push($table_resume_array[$_POST['id_parcours_en_cours_'][$j]], array ( 'autorisation_parentale' => 'fourni'));
								//$table_resume_array[$_POST['id_parcours_en_cours_'][$j]][] = array ( 'autorisation_parentale' => 'fourni');



}
else {
	
	$aff= array('comeback'=>'OK');
	echo json_encode($aff);
	
}


	
?>