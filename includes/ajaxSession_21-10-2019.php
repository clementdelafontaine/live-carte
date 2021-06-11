<?php 
require_once("includes.php");
require("functions_n.php");
require_once('functions_mail_n.php');
require_once("numerotation.php");

$insc_gratuite =$_POST['gratuit'];
$nb_inscrit = count($_SESSION['rieis']);

if ($nb_inscrit > 1 ) {
	
	$query = "SELECT ri.relais, ri.idInternauteref, rii.idEpreuveParcours, riei.idInscriptionEpreuveInternaute FROM r_internautereferent as ri ";
	$query .= "INNER JOIN r_insc_internautereferent as rii ON ri.idInternauteReferent = rii.idInternauteReferent ";
	$query .= "INNER JOIN r_inscriptionepreuveinternaute as riei ON ri.idInternauteref = riei.idInternaute ";
	$query .= "WHERE ri.idInternauteReferent = ".$_SESSION['id_ref_temp'];
	$query .= " ORDER BY riei.idInscriptionEpreuveInternaute  DESC LIMIT 1";
	$result = $mysqli->query($query);
	$row=mysqli_fetch_array($result);
	$id_internaute_ref = $row['idInternauteref'];
	$id_internauteInscription_ref = $row['idInscriptionEpreuveInternaute'];
	$id_internaute_idEpreuveParcours = $row['idEpreuveParcours'];
	$relais = $row['relais'];
		
		/*** LOGS ****/
		//*****fputs($fp," : if (nb_inscrit > 1 )  SELECT ri.idInternauteref : ".$query."\n");
		/*** LOGS ****/
						
}

$query =  "SELECT * from r_inscriptionepreuveinternaute as riei ";
$query .= "INNER JOIN r_internaute as ri ON riei.idInternaute = ri.idInternaute ";
$query .= "WHERE riei.idEpreuve = ".$_SESSION['idEpreuve']." ";
$query .= "AND riei.id_session = '".$_SESSION['unique_id_session']."' ";
$query .=" ORDER BY riei.idInscriptionEpreuveInternaute  DESC LIMIT ".$nb_inscrit;
$result = $mysqli->query($query);
	$frais_cheque_coureur_total = 0;
$frais_cb_total= 0;	
while (($row=mysqli_fetch_array($result)) != FALSE) {
	

	$id_referant_insc_seul = $row['idInscriptionEpreuveInternaute'];
	$email_referant_seul = $row['emailInternaute'];
	$id_internaute_referant_insc_seul = $row['idInternaute'];
	$nom_internaute_referant_insc_seul = $row['nomInternaute'];
	$prenom_internaute_referant_insc_seul = $row['prenomInternaute'];

	if ($insc_gratuite==1) {
	
		$q1  = "UPDATE r_inscriptionepreuveinternaute SET paiement_type = 'GRATUIT', paiement_date = NOW(), montant_inscription = 0 ";
		$q1 .= "WHERE idInscriptionEpreuveInternaute = ".$row['idInscriptionEpreuveInternaute'];
		$mysqli->query($q1);
		
		/*** LOGS ****/
		//fputs($fp," : if (type_paiement== 'GRATUIT') UPDATE r_inscriptionepreuveinternaute : ".$q1."\n");
		/*** LOGS ****/
		
		if ($nb_inscrit > 1 && $row['idInscriptionEpreuveInternaute'] != $id_internauteInscription_ref ) {
			
			$donnees = array();
		
			$row_info = info_internaute_send_mail_test ($row['idInscriptionEpreuveInternaute'],$row['cout'],'GRATUIT');
			$row_info['referent'] = $id_internaute_ref;
			
			array_push($donnees,$row_info);
			//print_r($donnees);
			$sujet = "Ats Sport - Inscription à l'epreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
			$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
		    $dossard = numerotation($row['idParcours'],$row['idEpreuve'],$row['idInscriptionEpreuveInternaute'],$id_internauteInscription_ref);
			
			if ($row_info['place_promo'] ==1) {
				maj_tarif_reduc_place($row['idEpreuveParcoursTarif'],'update',$row['idInscriptionEpreuveInternaute']);
			}
			
			if ($row_info['code_promo'] != 'Aucun') {
				
				mise_a_jour_code_promo ($row['idEpreuve'],$row['idParcours'],$row_info['code_promo'],$row_info['valeur_code_promo'],$row_info['idEpreuveParcoursTarifPromo'], $row['idInscriptionEpreuveInternaute']);
			}
			
			//mail à l'organisateur
			if (!empty($email_organisateur)) {
				
				$row_info['numerotation'] = $dossard;
				$row_info['type_mail'] = 'organisateur';			
				$donnees[0]=$row_info;
				//***$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
			}
		}
		elseif ($nb_inscrit > 1 && $row['idInscriptionEpreuveInternaute'] == $id_internauteInscription_ref ) 
		{
			
			$donnees = array();
		
			$row_info = info_internaute_send_mail_test ($row['idInscriptionEpreuveInternaute'],$row['cout'],'GRATUIT');
			$row_info['referents'] = $id_internaute_ref ;
			
			array_push($donnees,$row_info);
			//print_r($donnees);
			$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
			$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
			$dossard = numerotation($row['idParcours'],$row['idEpreuve'],$row['idInscriptionEpreuveInternaute'],$id_internauteInscription_ref);
			
			$queryupiei=" UPDATE r_insc_internautereferent
			SET paiement_type='GRATUIT', montant = 0,
			paiement_date = NOW() 
			WHERE idEpreuve = ".$row['idEpreuve']."
			AND idEpreuveParcours REGEXP '".$id_internaute_idEpreuveParcours."'
			AND idInternauteReferent = ".$_SESSION['id_ref_temp']."
			AND paiement_type in('ATTENTE')";
			//AND paiement_date IS NULL";
			$resultupiei = $mysqli->query($queryupiei);
		
		/*** LOGS ****/
		//fputs($fp," : elseif (nb_inscrit > 1 && row['idInternaute'] == id_internaute_ref ) UPDATE r_insc_internautereferent : ".$queryupiei."\n");
		/*** LOGS ****/
			if ($row_info['place_promo'] ==1) {
				maj_tarif_reduc_place($row['idEpreuveParcoursTarif'],'update',$row['idInscriptionEpreuveInternaute']);
			}
			
			if ($row_info['code_promo'] != 'Aucun') {
				
				mise_a_jour_code_promo ($row['idEpreuve'],$row['idParcours'],$row_info['code_promo'],$row_info['valeur_code_promo'],$row_info['idEpreuveParcoursTarifPromo'], $row['idInscriptionEpreuveInternaute']);
			}
			
			//mail à l'organisateur
			if (!empty($email_organisateur)) {
				$row_info['numerotation'] = $dossard;
				$row_info['type_mail'] = 'organisateur';			
				$donnees[0]=$row_info;
				//***$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
			}
		}
		else
		{
			$donnees = array();
			
			
			$row_info = info_internaute_send_mail_test ($row['idInscriptionEpreuveInternaute'],$row['cout'],'GRATUIT');
			array_push($donnees,$row_info);
			//print_r($donnees);
			$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
			
			$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
			$dossard = numerotation($row['idParcours'],$row['idEpreuve'],$row['idInscriptionEpreuveInternaute']);
			
			if ($row_info['place_promo'] ==1) {
				maj_tarif_reduc_place($row['idEpreuveParcoursTarif'],'update',$row['idInscriptionEpreuveInternaute']);
			}
			
			if ($row_info['code_promo'] != 'Aucun') {
				
				mise_a_jour_code_promo ($row['idEpreuve'],$row['idParcours'],$row_info['code_promo'],$row_info['valeur_code_promo'],$row_info['idEpreuveParcoursTarifPromo'], $row['idInscriptionEpreuveInternaute']);
			}
			
			//mail à l'organisateur
			if (!empty($email_organisateur)) {
				$row_info['numerotation'] = $dossard;
				$row_info['type_mail'] = 'organisateur';			
				$donnees[0]=$row_info;
				//$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
			}
		
		}
		//Numérotation
		//numerotation($row['idParcours'],$row['idEpreuve'],$row['idInscriptionEpreuveInternaute']);

		//ENVOYER UN EMAIL 
		
		$query_del =  "DELETE FROM r_insc_internaute_temp ";
		$query_del .= "WHERE idInscInternauteTemp = ".$row['idInscInternauteTemp'];
		//***$result_del = $mysqli->query($query_del);
		
		/*** LOGS ****/
		//*****fputs($fp," : GRATUIT / DELETE FROM r_insc_internaute_temp : ".$query_del."\n");
		/*** LOGS ****/
		//TEMPO MAIL GRATUIT
		$data['objet'] = "INSCRIPTION GRATUITE - ".$row_info['nomInternaute']." ".$row_info['prenomInternaute'];
		$data['evenement'] = "Ats Sport - Inscription à l'épreuve ".$row['idEpreuve']." - ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
		$data['message'] = "Date : ".date("Y-m-d H:i:s")." - MODE GRATUIT : ".$row_info['mode_gratuit']." - Email : ".$row_info['emailInternaute'];
		$data['email'] = "webmaster@ats-sport.com";
		//send_mail_gratuit($data);
		
	}

	if ($type_paiement== 'AUTRE') {
	
		
		$search_assure = in_array_r($row['idInscriptionEpreuveInternaute'], $assure);
		if ($relais !='oui') $relais='non';
		$frais_assurance = 0;
		if ($search_assure==true)
		{
			//echo "JASSURE  !!! ".$assus."----".$assu."-----".$row['idInscriptionEpreuveInternaute'];
					
			$bdd_assu  = "INSERT INTO r_insc_assurance_annulation (idEpreuve, idEpreuveParcours, idAssuranceAnnulation, idInternauteInscriptionref, idInternauteReferent, montant, relais) ";
			$bdd_assu .= "VALUE (".$id_epreuve.", ".$row['idParcours'].", ".$id_assurance_annulation.", ".$row['idInscriptionEpreuveInternaute'].",".$id_referent.", ".$search_assure['montant'].",'".$relais."')";  
			$result_bdd_assu = $mysqli->query($bdd_assu);	
					
			$q1  = "UPDATE r_inscriptionepreuveinternaute SET assurance = ".$search_assure['montant']." ";
			$q1 .= "WHERE idInscriptionEpreuveInternaute = ".$row['idInscriptionEpreuveInternaute'];
			$mysqli->query($q1);					
			
			$q1  = "UPDATE r_insc_internaute_temp SET assurance = ".$search_assure['montant']." ";
			$q1 .= "WHERE idInscriptionEpreuveInternaute = ".$row['idInscriptionEpreuveInternaute'];
			$mysqli->query($q1);
			$frais_assurance +=$search_assure['montant'];			
		}			
		
		
		
		//CODE PROMO
		
		$q1  = "UPDATE r_inscriptionepreuveinternaute SET paiement_type = 'AUTRE', paiement_date = NOW(), montant_inscription = ".(($row['cout'] + $row['participation'])-$row['valeur_code_promo']).", paiement_montant = ".(($row['cout'] + $row['participation'])-$row['valeur_code_promo']);
		$q1 .= " WHERE idInscriptionEpreuveInternaute = ".$row['idInscriptionEpreuveInternaute'];
		$mysqli->query($q1);
		
		/*** LOGS ****/
		fputs($fp," : if (type_paiement== 'AUTRE') UPDATE r_inscriptionepreuveinternaute : ".$q1."\n");
		/*** LOGS ****/
		
		if ($nb_inscrit > 1 && $row['idInscriptionEpreuveInternaute'] != $id_internauteInscription_ref ) {
			
			$donnees = array();
		
			$row_info = info_internaute_send_mail ($row['idInscriptionEpreuveInternaute'],$row['cout'],'GRATUIT');
			$row_info['referent'] = $id_internaute_ref;
			
			array_push($donnees,$row_info);
		
			$sujet = "Ats Sport - Inscription à l'epreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
			$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
		   $dossard = numerotation($row['idParcours'],$row['idEpreuve'],$row['idInscriptionEpreuveInternaute'],$id_internauteInscription_ref);
			
			if ($row_info['place_promo'] ==1) {
				maj_tarif_reduc_place($row['idEpreuveParcoursTarif'],'update',$row['idInscriptionEpreuveInternaute']);
			}
			
			if ($row_info['code_promo'] != 'Aucun') {
				
				mise_a_jour_code_promo ($row['idEpreuve'],$row['idParcours'],$row_info['code_promo'],$row_info['valeur_code_promo'],$row_info['idEpreuveParcoursTarifPromo'], $row['idInscriptionEpreuveInternaute']);
			}
			
			//mail à l'organisateur
			if (!empty($email_organisateur)) {
				
				$row_info['numerotation'] = $dossard;
				$row_info['type_mail'] = 'organisateur';			
				$donnees[0]=$row_info;
				$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
			}
		}
		elseif ($nb_inscrit > 1 && $row['idInscriptionEpreuveInternaute'] == $id_internauteInscription_ref ) 
		{
			
			$donnees = array();
		
			$row_info = info_internaute_send_mail ($row['idInscriptionEpreuveInternaute'],$row['cout'],'GRATUIT');
			$row_info['referents'] = $id_internaute_ref ;
			
			array_push($donnees,$row_info);
		
			$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
			$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
			$dossard = numerotation($row['idParcours'],$row['idEpreuve'],$row['idInscriptionEpreuveInternaute'],$id_internauteInscription_ref);
			
			$queryupiei=" UPDATE r_insc_internautereferent
			SET paiement_type='AUTRE', montant = 0,
			paiement_date = NOW(), 
			WHERE idEpreuve = ".$row['idEpreuve']."
			AND idEpreuveParcours REGEXP '".$id_internaute_idEpreuveParcours."'
			AND idInternauteReferent = ".$id_referent."
			AND paiement_type in('ATTENTE')";
			//AND paiement_date IS NULL";
			$resultupiei = $mysqli->query($queryupiei);
		
		/*** LOGS ****/
		fputs($fp," : elseif (nb_inscrit > 1 && row['idInternaute'] == id_internaute_ref ) UPDATE r_insc_internautereferent : ".$queryupiei."\n");
		/*** LOGS ****/
			if ($row_info['place_promo'] ==1) {
				maj_tarif_reduc_place($row['idEpreuveParcoursTarif'],'update',$row['idInscriptionEpreuveInternaute']);
			}
			
			if ($row_info['code_promo'] != 'Aucun') {
				
				mise_a_jour_code_promo ($row['idEpreuve'],$row['idParcours'],$row_info['code_promo'],$row_info['valeur_code_promo'],$row_info['idEpreuveParcoursTarifPromo'], $row['idInscriptionEpreuveInternaute']);
			}
			
			//mail à l'organisateur
			if (!empty($email_organisateur)) {
				$row_info['numerotation'] = $dossard;
				$row_info['type_mail'] = 'organisateur';			
				$donnees[0]=$row_info;
				$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
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
			$dossard = numerotation($row['idParcours'],$row['idEpreuve'],$row['idInscriptionEpreuveInternaute'],$id_internauteInscription_ref);
			
			if ($row_info['place_promo'] ==1) {
				maj_tarif_reduc_place($row['idEpreuveParcoursTarif'],'update',$row['idInscriptionEpreuveInternaute']);
			}
			
			if ($row_info['code_promo'] != 'Aucun') {
				
				mise_a_jour_code_promo ($row['idEpreuve'],$row['idParcours'],$row_info['code_promo'],$row_info['valeur_code_promo'],$row_info['idEpreuveParcoursTarifPromo'], $row['idInscriptionEpreuveInternaute']);
			}
			
			//mail à l'organisateur
			if (!empty($email_organisateur)) {
				$row_info['numerotation'] = $dossard;
				$row_info['type_mail'] = 'organisateur';			
				$donnees[0]=$row_info;
				$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
			}
		
		}
		//Numérotation
		//numerotation($row['idParcours'],$row['idEpreuve'],$row['idInscriptionEpreuveInternaute']);

		//ENVOYER UN EMAIL 
		
		$query_del =  "DELETE FROM r_insc_internaute_temp ";
		$query_del .= "WHERE idInscInternauteTemp = ".$row['idInscInternauteTemp'];
		//***$result_del = $mysqli->query($query_del);
		
		/*** LOGS ****/
		//*****fputs($fp," : GRATUIT / DELETE FROM r_insc_internaute_temp : ".$query_del."\n");
		/*** LOGS ****/
		
	}
}


	
	unset($_SESSION['panier'],$_SESSION['tarifs'],$_SESSION['idEpreuvePersoPre'],$_SESSION['paiement_indiv'],$_SESSION['groupe'],$_SESSION['somme_frais_cb'],$_SESSION['rieis']);
	unset($_SESSION['option_plus'],$_SESSION['idEpreuve'],$_SESSION['nb_relais'],$_SESSION['new_user'],$_SESSION['info_caddie'],$_SESSION['id_ref_temp'],$_SESSION['unique_id_session']);
	
	$json = array('etat' =>'OK');
	echo json_encode($json);
				

?>