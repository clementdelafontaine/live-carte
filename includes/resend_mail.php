<?php  
require_once("includes.php");
require_once('functions.php');
require_once('functions_mail.php');
require_once("numerotation.php");
 require_once("../libs/class.paiement.php");
require_once("../etransaction/class.paiement.php");
global $mysqli;
$email = $_POST['email'];	 
$id_internaute = $_POST['id_internaute'];
$id_epreuve = $_POST['id_epreuve'];
$id_parcours = $_POST['id_parcours'];
$etat_certif = $_POST['etat_certif'];
$paiement = $_POST['paiement'];
$action = $_POST['action'];
//$idInscriptionEpreuveInternaute=$_POST['idInscriptionEpreuveInternaute'];
$montant_inscription=$_POST['montant_inscription'];
$idEpreuveParcoursTarif = $_POST['idEpreuveParcoursTarif'];

		$query  = "SELECT cout_paiement_cb,payeur ";
		$query .= "FROM r_epreuve";
		$query .=" WHERE idEpreuve = ".$id_epreuve;
		$result = $mysqli->query($query);
		$data_epreuve=mysqli_fetch_array($result);
		$payeur = $data_epreuve['payeur'];
		$cout_paiement_cb = $data_epreuve['cout_paiement_cb'];
		
//$cout = $tarif+$cout_option_indiv_2014;//?????

$row_info = array();
//$tab_inscription = array();


		
			
$query =  "SELECT nomInternaute from r_internaute ";
//$query .= "INNER JOIN r_internaute as ri ON rii.idInternaute = ri.idInternaute ";
$query .= "WHERE idInternaute = ".$id_internaute." ";
$query .= "AND emailInternaute = '".$email."' ";
$result = $mysqli->query($query);
$row=mysqli_fetch_array($result);

if (!empty($row['nomInternaute'])) {
				
				
				
				if ($action=='CB') 	{
					
					$donnees = array();
					
					$query =  "SELECT * from r_insc_internaute_temp as rii ";
					$query .= "INNER JOIN r_internaute as ri ON rii.idInternaute = ri.idInternaute ";
					//$query .= "INNER JOIN b_paiements as bp ON rii.idInscriptionEpreuveInternaute = bp.reference ";
					$query .= "WHERE rii.idInternaute = ".$id_internaute." ";
					$query .= "AND rii.idParcours = ".$id_parcours." ";
					$query .= "AND rii.idEpreuve = ".$id_epreuve." ";
					$result = $mysqli->query($query);
					$row=mysqli_fetch_array($result);
					$idInscriptionEpreuveInternaute = $row['idInscriptionEpreuveInternaute'];
					
					//l'inscription du participant n'est pas en mode temporaire.
					
					if (empty($idInscriptionEpreuveInternaute)) 
					{
					
						$query =  "SELECT idInscriptionEpreuveInternaute,paiement_type,paiement_date FROM r_inscriptionepreuveinternaute ";
						$query .= "WHERE idInternaute = ".$id_internaute." ";
						$query .= "AND idEpreuveParcours = ".$id_parcours." ";
						$query .= "AND idEpreuve = ".$id_epreuve." ";
						//$query .= "AND paiement_type = 'ATTENTE' ";
						$result = $mysqli->query($query);
						//echo $query;
						$row=mysqli_fetch_array($result);
						$idInscriptionEpreuveInternaute = $row['idInscriptionEpreuveInternaute'];
						
						if (!empty($idInscriptionEpreuveInternaute) && $row['paiement_type'] == 'ATTENTE') {
							
							//on test si le paiement est déjà en attente
							$query_bp = "SELECT montant FROM b_paiements WHERE reference = ".$idInscriptionEpreuveInternaute;
							$result_bp = $mysqli->query($query_bp);
							$row_bp=mysqli_fetch_array($result_bp);
							
							if (empty($row_bp['montant'])) {
								$bpaiements  = "INSERT INTO b_paiements (reference, montant, frais_cb, date, id_referant, id_epreuve, statut) ";
								$bpaiements .= "VALUE ('".$idInscriptionEpreuveInternaute."', ".round($montant_inscription, 2).", ".($payeur == 'coureur'?round($frais_cb, 2):$cout_paiement_cb).", NOW(), ".$id_internaute.", ".$id_epreuve.", 'ATTENTE')";  
								$result = $mysqli->query($bpaiements);
							}
							
							if($payeur == 'coureur')
							{ 
						
								//Enregistrement des frais supplémentaires quel que soit le payeur
								$q1  = "UPDATE r_inscriptionepreuveinternaute SET frais_cb = ".round(($frais_cb), 2)." ";
								$q1 .= "WHERE idEpreuveParcoursTarif = ".$idEpreuveParcoursTarif." and idEpreuveParcours = ".$id_parcours." and idEpreuve = ".$id_epreuve." and idInternaute=".$id_internaute."";
								//echo $q1;
								$mysqli->query($q1);
								//$frais_cb += $row['frais_cb'];
							}
							else //Si les frais sont à la charge de l'organisateur (inclus dans le prix du parcours)
							{
								//Enregistrement des frais supplémentaires quel que soit le payeur
								$q1  = "UPDATE r_inscriptionepreuveinternaute SET frais_cb = ".$cout_paiement_cb." ";
								$q1 .= "WHERE idEpreuveParcoursTarif = ".$idEpreuveParcoursTarif." and idEpreuveParcours = ".$id_parcours." and idEpreuve = ".$id_epreuve." and idInternaute=".$id_internaute."";
								$mysqli->query($q1);
							}
							
							$html_ca = create_form_ca($idInscriptionEpreuveInternaute,$montant_inscription, $email);
							$aff= array('comeback'=>'OK','html_ca'=>$html_ca);
							echo json_encode($aff);
							
							
							
							/*
							$id_session_temp = uniqid();
							$q1  = "UPDATE r_inscriptionepreuveinternaute SET id_session = '".md5($id_session_temp)."' ";
							$q1 .= "WHERE idInscriptionEpreuveInternaute = ".$row['idInscriptionEpreuveInternaute'];
							//echo $q1;
							$mysqli->query($q1);
							

							if ($action=='CB') {
								
								$html_ca = create_form_ca($idInscriptionEpreuveInternaute,$montant_inscription, $email);
								$aff= array('comeback'=>'OK','html_ca'=>$html_ca);
								echo json_encode($aff);	
							
							}else {
								
								$row_info = info_internaute_resend_mail ($idInscriptionEpreuveInternaute,$etat_certif,$paiement,$id_session_temp);
								array_push($donnees,$row_info);
								//print_r($donnees);
								$sujet = "ats-sport - Mise à jour de votre inscription sur l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
								//$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
							
								
								$aff= array('comeback'=>'OK');
								echo json_encode($aff);	
								
								
								
							}
							*/
							//
						}
						elseif (!empty($idInscriptionEpreuveInternaute) && $row['paiement_type'] == 'CHQ') 
						{
						
							
							
							//$info_epreuve= info_epreuve_parcours($id_epreuve, $id_parcours);
							
							
							//$aff= array('comeback'=>'KO','type_paiement'=>'CHQ','epreuve'=>$info_epreuve['nomEpreuve'],'parcours'=>$info_epreuve['nomParcours']);
							$aff= array('comeback'=>'KO','type_paiement'=>'CHQ');
							echo json_encode($aff);	
						
						}
						else
						{
							$aff= array('comeback'=>'KO');
							echo json_encode($aff);	
						
						
						}
					} 
					else// Inscription en temporaire.
					{
					
					
							//on test si le paiement est déjà en attente
							$query_bp = "SELECT montant FROM b_paiements WHERE reference = ".$idInscriptionEpreuveInternaute;
							$result_bp = $mysqli->query($query_bp);
							$row_bp=mysqli_fetch_array($result_bp);
							
							if (empty($row_bp['montant'])) {
								$bpaiements  = "INSERT INTO b_paiements (reference, montant, frais_cb, date, id_referant, id_epreuve, statut) ";
								$bpaiements .= "VALUE ('".$idInscriptionEpreuveInternaute."', ".round($montant_inscription, 2).", ".($payeur == 'coureur'?round($frais_cb, 2):$cout_paiement_cb).", NOW(), ".$id_internaute.", ".$id_epreuve.", 'ATTENTE')";  
								$result = $mysqli->query($bpaiements);
							}
							
							if($payeur == 'coureur')
							{ 
						
								//Enregistrement des frais supplémentaires quel que soit le payeur
								$q1  = "UPDATE r_inscriptionepreuveinternaute SET frais_cb = ".round(($frais_cb), 2)." ";
								$q1 .= "WHERE idEpreuveParcoursTarif = ".$idEpreuveParcoursTarif." and idEpreuveParcours = ".$id_parcours." and idEpreuve = ".$id_epreuve." and idInternaute=".$id_internaute."";
								//echo $q1;
								$mysqli->query($q1);
								//$frais_cb += $row['frais_cb'];
							}
							else //Si les frais sont à la charge de l'organisateur (inclus dans le prix du parcours)
							{
								//Enregistrement des frais supplémentaires quel que soit le payeur
								$q1  = "UPDATE r_inscriptionepreuveinternaute SET frais_cb = ".$cout_paiement_cb." ";
								$q1 .= "WHERE idEpreuveParcoursTarif = ".$idEpreuveParcoursTarif." and idEpreuveParcours = ".$id_parcours." and idEpreuve = ".$id_epreuve." and idInternaute=".$id_internaute."";
								$mysqli->query($q1);
							}
							
							$html_ca = create_form_ca($idInscriptionEpreuveInternaute,$montant_inscription, $email);
							$aff= array('comeback'=>'OK','html_ca'=>$html_ca);
							echo json_encode($aff);
					
					}
				}
				else
				{
					$aff= array('comeback'=>'OK');
					echo json_encode($aff);
				
				}
					
					
					/*
					$row_info = info_internaute_send_mail ($idInscriptionEpreuveInternaute,$row['cout'],'A PAYER');
					//print_r($row_info);
					//$row_info['referent'] = $id_internaute_ref;					
					array_push($donnees,$row_info);
					
						$aff= array('comeback'=>'KO');
					
					}*/
/*
					$row_info = info_internaute_send_mail ($idInscriptionEpreuveInternaute,$row['cout'],'A PAYER');
					//print_r($row_info);
					//$row_info['referent'] = $id_internaute_ref;					
					array_push($donnees,$row_info);
					
					

					$sujet = "ats-sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
					//$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
					if ($action=='CB') {
						
						if($payeur == 'coureur')
						{ 
					
							//Enregistrement des frais supplémentaires quel que soit le payeur
							$q1  = "UPDATE r_inscriptionepreuveinternaute SET frais_cb = ".round(($frais_cb), 2)." ";
							$q1 .= "WHERE idEpreuveParcoursTarif = ".$idEpreuveParcoursTarif." and idEpreuveParcours = ".$id_parcours." and idEpreuve = ".$id_epreuve." and idInternaute=".$id_internaute."";
							//echo $q1;
							$mysqli->query($q1);
							//$frais_cb += $row['frais_cb'];
						}
						else //Si les frais sont à la charge de l'organisateur (inclus dans le prix du parcours)
						{
							//Enregistrement des frais supplémentaires quel que soit le payeur
							$q1  = "UPDATE r_inscriptionepreuveinternaute SET frais_cb = ".$cout_paiement_cb." ";
							$q1 .= "WHERE idEpreuveParcoursTarif = ".$idEpreuveParcoursTarif." and idEpreuveParcours = ".$id_parcours." and idEpreuve = ".$id_epreuve." and idInternaute=".$id_internaute."";
							$mysqli->query($q1);
						}
						
						$query_bp = "SELECT montant FROM b_paiements WHERE reference = ".$idInscriptionEpreuveInternaute;
						$result_bp = $mysqli->query($query_bp);
						$row_bp=mysqli_fetch_array($result_bp);
						
						if (empty($row_bp['montant'])) {
							$bpaiements  = "INSERT INTO b_paiements (reference, montant, frais_cb, date, id_referant, id_epreuve, statut) ";
							$bpaiements .= "VALUE ('".$idInscriptionEpreuveInternaute."', ".round($montant_inscription, 2).", ".($payeur == 'coureur'?round($frais_cb, 2):$cout_paiement_cb).", NOW(), ".$id_internaute.", ".$id_epreuve.", 'ATTENTE')";  
							$result = $mysqli->query($bpaiements);
						}
						
						$html_ca = create_form_ca($idInscriptionEpreuveInternaute,$montant_inscription, $email);
						$aff= array('comeback'=>'OK','html_ca'=>$html_ca);
						echo json_encode($aff);	
					
					}
					else {
						
						$aff= array('comeback'=>'OK');
					}
					
					*/
					
}
else
{
	
						$aff= array('comeback'=>'KO');
					//print_r($tab_inscription);
					echo json_encode($aff);
}



exit();

while (($row=mysqli_fetch_array($result)) != FALSE) {
	

	$id_referant_insc_seul = $row['idInscriptionEpreuveInternaute'];
	$email_referant_seul = $row['emailInternaute'];
	$id_internaute_referant_insc_seul = $row['idInternaute'];
	
	if ($type_paiement== 'GRATUIT') {
	
		$q1  = "UPDATE r_inscriptionepreuveinternaute SET paiement_type = 'GRATUIT' ";
		$q1 .= "WHERE idEpreuveParcoursTarif = ".$row['idEpreuveParcoursTarif']." and idEpreuveParcours = ".$row['idParcours']." and idEpreuve = ".$row['idEpreuve']." and idInternaute=".$row['idInternaute']."";
		$mysqli->query($q1);
		
		if ($nb_inscrit > 1 && $row['idInternaute'] != $id_internaute_ref ) {
			
			$donnees = array();
		
			$row_info = info_internaute_send_mail ($row['idInscriptionEpreuveInternaute'],$row['cout'],'GRATUIT');
			$row_info['referent'] = $id_internaute_ref;
			
			array_push($donnees,$row_info);
		
			$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
			$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
		    numerotation($row['idParcours'],$row['idEpreuve'],$row['idInscriptionEpreuveInternaute']);
			
			if ($row_info['code_promo'] != 'Aucun') {
				
				mise_a_jour_code_promo ($row['idEpreuve'],$row['idParcours'],$row_info['code_promo'],$row['idInscriptionEpreuveInternaute']);
			}
		
		}
		elseif ($nb_inscrit > 1 && $row['idInternaute'] == $id_internaute_ref ) 
		{
			
			$donnees = array();
		
			$row_info = info_internaute_send_mail ($row['idInscriptionEpreuveInternaute'],$row['cout'],'GRATUIT');
			$row_info['referents'] = $id_internaute_ref ;
			
			array_push($donnees,$row_info);
		
			$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
			$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
			numerotation($row['idParcours'],$row['idEpreuve'],$row['idInscriptionEpreuveInternaute']);
			
			$queryupiei=" UPDATE r_insc_internautereferent
			SET paiement_type='GRATUIT', montant = ".$cout." 
			WHERE idEpreuve = ".$row['idEpreuve']."
			AND idEpreuveParcours REGEXP '".$id_internaute_idEpreuveParcours."'
			AND idInternauteReferent = ".$id_referent."
			AND paiement_type in('ATTENTE')
			AND paiement_date IS NULL";
			$resultupiei = $mysqli->query($queryupiei);
			
			if ($row_info['code_promo'] != 'Aucun') {
				
				mise_a_jour_code_promo ($row['idEpreuve'],$row['idParcours'],$row_info['code_promo'],$row['idInscriptionEpreuveInternaute']);
			}
		
		}
		else
		{
			$donnees = array();
			
			
			$row_info = info_internaute_send_mail ($row['idInscriptionEpreuveInternaute'],$row['cout'],'GRATUIT');
			array_push($donnees,$row_info);
			//print_r($donnees);
			$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
			
			$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
			numerotation($row['idParcours'],$row['idEpreuve'],$row['idInscriptionEpreuveInternaute']);
			
			if ($row_info['code_promo'] != 'Aucun') {
				
				mise_a_jour_code_promo ($row['idEpreuve'],$row['idParcours'],$row_info['code_promo'],$row['idInscriptionEpreuveInternaute']);
			}
		}
		//Numérotation
		//numerotation($row['idParcours'],$row['idEpreuve'],$row['idInscriptionEpreuveInternaute']);

		//ENVOYER UN EMAIL 
		
		$query_del =  "DELETE FROM r_insc_internaute_temp ";
		$query_del .= "WHERE idInscInternauteTemp = ".$row['idInscInternauteTemp'];
		$result_del = $mysqli->query($query_del);
		
	}
	
	
		if ($type_paiement== 'CHQ') {
	
		$q1  = "UPDATE r_inscriptionepreuveinternaute SET paiement_type = 'CHQ' ";
		$q1 .= "WHERE idEpreuveParcoursTarif = ".$row['idEpreuveParcoursTarif']." and idEpreuveParcours = ".$row['idParcours']." and idEpreuve = ".$row['idEpreuve']." and idInternaute=".$row['idInternaute']."";
		 $mysqli->query($q1);
		
		if ($nb_inscrit > 1 && $row['idInternaute'] != $id_internaute_ref ) {
			
			$donnees = array();
		
			$row_info = info_internaute_send_mail ($row['idInscriptionEpreuveInternaute'],$row['cout'],'CHEQUE');
			$row_info['referent'] = $id_internaute_ref;
			
			array_push($donnees,$row_info);
			//print_r($donnees);
			
			$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
			$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
			
			if ($row_info['code_promo'] != 'Aucun') {
				
				mise_a_jour_code_promo ($row['idEpreuve'],$row['idParcours'],$row_info['code_promo'],$row['idInscriptionEpreuveInternaute']);
			}
		}
		elseif ($nb_inscrit > 1 && $row['idInternaute'] == $id_internaute_ref ) 
		{
			
			$donnees = array();
		
			$row_info = info_internaute_send_mail ($row['idInscriptionEpreuveInternaute'],$row['cout'],'CHEQUE');
			//echo "referents".$row_info['referents'] = $id_internaute_ref ;
			
			array_push($donnees,$row_info);
			//print_r($donnees);

			$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
			$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
			//exit();
			$queryupiei=" UPDATE r_insc_internautereferent
			SET paiement_type='CHQ', montant = ".$cout." 
			WHERE idEpreuve = ".$row['idEpreuve']."
			AND idEpreuveParcours REGEXP '".$id_internaute_idEpreuveParcours."'
			AND idInternauteReferent = ".$id_referent."
			AND paiement_type in('ATTENTE')
			AND paiement_date IS NULL";
			$resultupiei = $mysqli->query($queryupiei);
		
			if ($row_info['code_promo'] != 'Aucun') {
				
				mise_a_jour_code_promo ($row['idEpreuve'],$row['idParcours'],$row_info['code_promo'],$row['idInscriptionEpreuveInternaute']);
			}
		}
		else
		{
			$donnees = array();
			
			
			$row_info = info_internaute_send_mail ($row['idInscriptionEpreuveInternaute'],$row['cout'],'CHEQUE');
			array_push($donnees,$row_info);
		
			$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
		
			$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
			
			if ($row_info['code_promo'] != 'Aucun') {
				
				mise_a_jour_code_promo ($row['idEpreuve'],$row['idParcours'],$row_info['code_promo'],$row['idInscriptionEpreuveInternaute']);
			}		
		}
		
		$query_del =  "DELETE FROM r_insc_internaute_temp ";
		$query_del .= "WHERE idInscInternauteTemp = ".$row['idInscInternauteTemp'];
		$result_del = $mysqli->query($query_del);
	
	
	}
	

	
	
	
	
	
	
	
	
	/*
	$tab_temp = array();
	$date_insc = str_replace(" ", "_", $row['dateInscription']);
	
	$sujet = "ats-sport - Inscription Epreuve ".extract_champ_epreuve('nomEpreuve', $id_epreuve)." - ".extract_champ_parcours('nomParcours',$row['id_parcours']);
	$lien = "<a href='http://ats.g1id.fr/color_admin_v1.8/frontend/one-page-parallax/template_content_html/inscriptions.php?id_epreuve=".$id_epreuve."&id_parcours=".$row['idParcours'] ."&id=".$row['idSession']."&date_inscription=".$date_insc."&action=update' target='_blank'> Cliquez ICI pour la mise à jour de votre inscription</a>";
	$corps = "<p>".$lien."</p>";
	$corps .="<p>Votre code de sécurité pour pouvoir effectuer des modifications : <b>".$row['codeSecurite']."</p>";
	//echo $sujet." - ".$corps;
	$tab_temp['lien'] = $lien;
	$tab_temp['code'] = $row['codeSecurite'];
	
	array_push($tab_inscription,$tab_temp);
	
	$mail_send = send_mail ('contact@ats-sport.com','Ats Sport Inscription',$row['emailInternaute'], $row['nomInternaute']." ".$row['prenomInternaute'], $sujet,$corps,"jf@chauveau.nom.fr");
	if ($mail_send == 'ok') {
		
		$query_del =  "DELETE FROM r_insc_internaute_temp ";
		$query_del .= "WHERE idInscInternauteTemp = ".$row['idInscInternauteTemp'];
		$result_del = $mysqli->query($query_del);
		
		
		
	}
	*/
	
		if ($type_paiement== 'CB') {
	
		
			
			

			//$cout = $row['tarif']+$cout_option_indiv_2014;
			
				//Si les frais sont à la charge du coureur
				//echo $row['tarif'];
				
				if($payeur == 'coureur')
				{ 
					
					//Enregistrement des frais supplémentaires quel que soit le payeur
					$q1  = "UPDATE r_inscriptionepreuveinternaute SET frais_cb = ".round(($frais_cb/$nb_inscrit), 2)." ";
					$q1 .= "WHERE idEpreuveParcoursTarif = ".$row['idEpreuveParcoursTarif']." and idEpreuveParcours = ".$row['idParcours']." and idEpreuve = ".$row['idEpreuve']." and idInternaute=".$row['idInternaute']."";
					//echo $q1;
					$mysqli->query($q1);
					//$frais_cb += $row['frais_cb'];
				}
				else //Si les frais sont à la charge de l'organisateur (inclus dans le prix du parcours)
				{
					//Enregistrement des frais supplémentaires quel que soit le payeur
					$q1  = "UPDATE r_inscriptionepreuveinternaute SET frais_cb = ".$cout_paiement_cb." ";
					$q1 .= "WHERE idEpreuveParcoursTarif = ".$row['idEpreuveParcoursTarif']." and idEpreuveParcours = ".$row['idParcours']." and idEpreuve = ".$row['idEpreuve']." and idInternaute=".$row['idInternaute']."";
					$mysqli->query($q1);
				}
			
			
			
		}
}


	if ($type_paiement== 'CB') {
	
			//echo $frais_cb;
			//echo $cout_paiement_cb;
			/*$query_tarif = "SELECT tarif FROM r_epreuveparcourstarif WHERE idEpreuveParcoursTarif = ".$row['idEpreuveParcoursTarif'];
			$resul_tarif = $mysqli->query($query_tarif );
			$row_tarif = mysqli_fetch_array($resul_tarif);
			$tarif = $row_tarif['tarif'];*/
			
			$total = $cout+$frais_cb;
			//pour test
			//$total=1;
				if ($nb_inscrit >1 && $id_referent!=0 ) {
					
					$q1  = "UPDATE r_insc_internautereferent SET frais_cb = ".round($frais_cb, 2)." ";
					$q1 .= "WHERE idInternauteReferent = ".$id_referent." and idEpreuve = ".$id_epreuve."";
					//echo $q1;
					$mysqli->query($q1);
					
					$query_ref= "SELECT emailInternaute,rif.idInternauteref FROM r_internaute as ri ";
					$query_ref.="INNER JOIN r_internautereferent as rif ON ri.idInternaute = rif.idInternauteref ";
					$query_ref.="WHERE rif.idInternauteReferent = ".$id_referent;
				//echo $query_email;
					$result_ref = $mysqli->query($query_ref);
					$row_ref = mysqli_fetch_array($result_ref);
					
					$bpaiements  = "INSERT INTO b_paiements (reference, montant, frais_cb, date, id_referant, id_epreuve, statut) ";
					$bpaiements .= "VALUE ('M-".$id_referent."', ".round($total, 2).", ".($payeur == 'coureur'?round($frais_cb, 2):$cout_paiement_cb).", NOW(), ".$row_ref['idInternauteref'].", ".$id_epreuve.", 'ATTENTE')";  
					$result = $mysqli->query($bpaiements);
					$html_ca = create_form_ca("M-".$id_referent, round($total, 2), $row_ref['emailInternaute']);
				
				}else // pas de référent, inscription seule
				{
					
					$bpaiements  = "INSERT INTO b_paiements (reference, montant, frais_cb, date, id_referant, id_epreuve, statut) ";
					$bpaiements .= "VALUE ('".$id_referant_insc_seul."', ".round($total, 2).", ".($payeur == 'coureur'?round($frais_cb, 2):$cout_paiement_cb).", NOW(), ".$id_internaute_referant_insc_seul.", ".$id_epreuve.", 'ATTENTE')";  
					$result = $mysqli->query($bpaiements);
					
					$html_ca = create_form_ca($id_referant_insc_seul, round($total, 2), $email_referant_seul);
					

					
					
				}
				//echo "total".$total = $cout+$frais_cb;
				

				//echo $row_email['emailInternaute'];
				
//echo $bpaiements;


				$aff= array('comeback'=>'|CB|','frais_coureur' =>$cout_paiement_cb, 'html_ca'=>$html_ca);
				//print_r($tab_inscription);
				echo json_encode($aff);
				
				session_destroy();
				unset($_SESSION);
	}
	elseif ($type_paiement== 'CHQ') {

			
			$aff= array('comeback'=>'|CHQ|');
			//print_r($tab_inscription);
			echo json_encode($aff);
			//ENVOYER UN EMAIL 
			
			session_destroy();
			unset($_SESSION);
	}
	else
	{
		/*
					$donnees = array();
					
					if ($nb_inscrit >1 && $id_referent!=0 ) {
	
						$id_internaute_ref = $id_referent;
					}
					else
					{
						$id_internaute_ref = $id_referant_insc_seul;
					}

					$row_info = info_internaute_send_mail ($id_internaute_ref,0,'GRATUIT');
					array_push($donnees,$row_info);
		
					$sujet = "ats-sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
		
					$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
					
					$query_del =  "DELETE FROM r_insc_internaute_temp ";
					$query_del .= "WHERE idInscriptionEpreuveInternaute = ".$row_info['idInscriptionEpreuveInternaute']." ";
					$query_del .= "AND idInternaute = ".$row_info['idInternaute']." ";
					$query_del .= "AND idEpreuve = ".$row_info['idEpreuve'];
					//***$result_del = $mysqli->query($query_del);
		
					//***numerotation($row_info['idParcours'],$row_info['idEpreuve'],$row_info['idInscriptionEpreuveInternaute']);
					*/
					$aff= array('comeback'=>'|GRATUIT|');
					//print_r($tab_inscription);
					echo json_encode($aff);
					
					//session_destroy();
					//unset($_SESSION);
	
	}
	//session_destroy();
	//unset($_SESSION);
	//session_start();
	//$_SESSION['unique_id_session'] = md5(uniqid());
/*								
$sujet = "ats-sport - Inscription Epreuve ".extract_champ_epreuve('nomEpreuve', $id_epreuve)." - ".extract_champ_parcours('nomParcours',$id_parcours);
$corps = "<p><a href='http://ats.g1id.fr/color_admin_v1.8/frontend/one-page-parallax/template_content_html/inscriptions.php?id_epreuve=".$id_epreuve."&id_parcours=".$id_parcours ."&id=".$id_session_unique."&date_inscription=".$date_insc."&action=update' target='_blank'> Cliquez ICI pour la mise à jour de votre inscription</a></p>";
$corps .="<p>Votre code de sécurité pour pouvoir effectuer des modifications : <b>".$code_securite."</p>";
//****$mail_send = send_mail ('contact@ats-sport.com','Ats Sport Inscription',$value['email'], $value['nom']." ".$value['prenom'], $sujet,$corps,"jf@chauveau.nom.fr"); */

	
?>