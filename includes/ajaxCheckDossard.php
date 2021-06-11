<?php 

require_once('connect_db.php');
connect_db();
session_start();
require_once('functions.php');
require_once('numerotation.php');
require_once('slashes.php');
global $mysqli;
$id_epreuve = $_POST['id_epreuve'];
$id_parcours = $_POST['id_parcours'];
$dossard = $_POST['dossard'];
			
			$query_de = "SELECT dossardDeb,dossardFin FROM r_epreuveparcours ";
			$query_de .="WHERE idEpreuveParcours = ".$id_parcours." ";
			$result_de = $mysqli->query($query_de);
			$row_de= mysqli_fetch_row($result_de);
			
			if ($dossard < $row_de[0]) { 
				
				$dossard_propose = numerotation_no_update($id_parcours,$id_epreuve,$id);
				$aff= array('comeback'=>'KO','dossard'=>$dossard,'dossard_out'=>1,'dossard_min'=>$row_de[0],'dossard_propose'=>$dossard_propose);
				echo json_encode($aff);
				exit();
			}
			elseif ($dossard > $row_de[1]) {
				
				$dossard_propose = numerotation_no_update($id_parcours,$id_epreuve,$id);
				$aff= array('comeback'=>'KO','dossard'=>$dossard,'dossard_out'=>2,'dossard_max'=>$row_de[1],'dossard_propose'=>$dossard_propose);
				echo json_encode($aff);
				exit();	
			}
			
			$query_de = "SELECT idInscriptionEpreuveInternaute FROM r_inscriptionepreuveinternaute ";
			$query_de .="WHERE dossard = ".$dossard." ";
			$query_de .= "AND idEpreuve = ".$id_epreuve." ";
			$query_de .= "AND idEpreuveParcours = ".$id_parcours." ";
			$result_de = $mysqli->query($query_de);
			$row_de= mysqli_fetch_row($result_de);
			
			if (!empty($row_de[0])) {
				
				$dossard_propose = numerotation_no_update($id_parcours,$id_epreuve,$id);
				$aff= array('comeback'=>'KO','dossard'=>$dossard,'dossard_propose'=>$dossard_propose);
				echo json_encode($aff);
				exit();
			}
			else
			{ 
				$aff= array('comeback'=>'OK');
				echo json_encode($aff);
				exit();
				
				
			}

?>