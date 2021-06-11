<?php  
require_once("includes.php");
require_once('functions.php');
require_once('functions_mail.php');
require_once("numerotation.php");
require_once("../libs/class.paiement.php");
require_once("../etransaction/class.paiement.php");

	$fp = fopen("logs_send_mail.txt","a");
	fputs($fp,date("d/m/Y His")." : ".$_SERVER['REQUEST_URI']."\n");
	
$type_paiement = $_POST['type'];	 
$id_epreuve = $_POST['id_epreuve'];
$id_referent = $_POST['id_referent'];
$id_session_unique = $_POST['id_session_unique'];
$cout = $_POST['prix_total'];
$nb_inscrit = $_POST['nb_inscrit'];
$cout_paiement_cb = $_POST['cout_paiement_cb'];
$frais_cb = $_POST['frais_cb'];
$cout_paiement_cheque = $_POST['cout_paiement_cheque'];
$frais_cheque = $_POST['frais_cheque'];
$payeur = $_POST['payeur'];
$email_organisateur = recup_mail_organisateur_epreuve($id_epreuve);


//$cout = $tarif+$cout_option_indiv_2014;//?????

$tab_temp = array();
$tab_inscription = array();

if ($nb_inscrit > 1 ) {
	
	$query = "SELECT ri.idInternauteref, rii.idEpreuveParcours FROM r_internautereferent as ri ";
	$query .= "INNER JOIN r_insc_internautereferent as rii ON ri.idInternauteReferent = rii.idInternauteReferent ";
	$query .= "WHERE ri.idInternauteReferent = ".$id_referent;
	$result = $mysqli->query($query);
	$row=mysqli_fetch_array($result);
	$id_internaute_ref = $row['idInternauteref'];
	$id_internaute_idEpreuveParcours = $row['idEpreuveParcours'];
		
		/*** LOGS ****/
		fputs($fp," : if (nb_inscrit > 1 )  SELECT ri.idInternauteref : ".$query."\n");
		/*** LOGS ****/
						
}
		
			
$query =  "SELECT * from r_insc_internaute_temp as rii ";
$query .= "INNER JOIN r_internaute as ri ON rii.idInternaute = ri.idInternaute ";
$query .= "WHERE rii.idEpreuve = ".$id_epreuve." ";
$query .= "AND rii.idSession = '".$id_session_unique."' ";
$result = $mysqli->query($query);

		
		/*** LOGS ****/
		fputs($fp," : SELECT * from r_insc_internaute_temp : ".$query."\n");
		/*** LOGS ****/
		
while (($row=mysqli_fetch_array($result)) != FALSE) {
	

	$id_referant_insc_seul = $row['idInscriptionEpreuveInternaute'];
	$email_referant_seul = $row['emailInternaute'];
	$id_internaute_referant_insc_seul = $row['idInternaute'];
	
	if ($type_paiement== 'GRATUIT') {
	
		$q1  = "UPDATE r_inscriptionepreuveinternaute SET paiement_type = 'GRATUIT', paiement_date = NOW() ";
		$q1 .= "WHERE idEpreuveParcoursTarif = ".$row['idEpreuveParcoursTarif']." and idEpreuveParcours = ".$row['idParcours']." and idEpreuve = ".$row['idEpreuve']." and idInternaute=".$row['idInternaute']."";
		$mysqli->query($q1);
		
		/*** LOGS ****/
		fputs($fp," : if (type_paiement== 'GRATUIT') UPDATE r_inscriptionepreuveinternaute : ".$q1."\n");
		/*** LOGS ****/
		
		if ($nb_inscrit > 1 && $row['idInternaute'] != $id_internaute_ref ) {
			
			$donnees = array();
		
			$row_info = info_internaute_send_mail ($row['idInscriptionEpreuveInternaute'],$row['cout'],'GRATUIT');
			$row_info['referent'] = $id_internaute_ref;
			
			array_push($donnees,$row_info);
		
			$sujet = "Ats Sport - Inscription à l'epreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
			$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
		   $dossard = numerotation($row['idParcours'],$row['idEpreuve'],$row['idInscriptionEpreuveInternaute']);
			
			
			if ($row_info['code_promo'] != 'Aucun') {
				
				mise_a_jour_code_promo ($row['idEpreuve'],$row['idParcours'],$row_info['code_promo'],$row['idInscriptionEpreuveInternaute']);
			}
			
			//mail à l'organisateur
			if (!empty($email_organisateur)) {
				
				$row_info['numerotation'] = $dossard;
				$row_info['type_mail'] = 'organisateur';			
				$donnees[0]=$row_info;
				$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
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
			$dossard = numerotation($row['idParcours'],$row['idEpreuve'],$row['idInscriptionEpreuveInternaute']);
			
			$queryupiei=" UPDATE r_insc_internautereferent
			SET paiement_type='GRATUIT', montant = ".$cout.",
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
			
			if ($row_info['code_promo'] != 'Aucun') {
				
				mise_a_jour_code_promo ($row['idEpreuve'],$row['idParcours'],$row_info['code_promo'],$row['idInscriptionEpreuveInternaute']);
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
			$dossard = numerotation($row['idParcours'],$row['idEpreuve'],$row['idInscriptionEpreuveInternaute']);
			
			if ($row_info['code_promo'] != 'Aucun') {
				
				mise_a_jour_code_promo ($row['idEpreuve'],$row['idParcours'],$row_info['code_promo'],$row['idInscriptionEpreuveInternaute']);
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
		$result_del = $mysqli->query($query_del);
		
		/*** LOGS ****/
		fputs($fp," : GRATUIT / DELETE FROM r_insc_internaute_temp : ".$query_del."\n");
		/*** LOGS ****/
		
	}
	
	
		if ($type_paiement== 'CHQ') {
		
		$frais_cheque_coureur  =0;
			
			if($payeur == 'coureur')
			{ 
				$frais_cheque_coureur = $frais_cheque = round(($frais_cheque/$nb_inscrit), 2);
				
			}
			else //Si les frais sont à la charge de l'organisateur (inclus dans le prix du parcours)
			{
				$frais_cheque = $cout_paiement_cheque;
				//Enregistrement des frais supplémentaires quel que soit le payeur
			
			}
		
		
		
		$q1  = "UPDATE r_inscriptionepreuveinternaute SET paiement_type ='CHQ', frais_cheque = ".$frais_cheque_coureur." ";
		$q1 .= "WHERE idEpreuveParcoursTarif = ".$row['idEpreuveParcoursTarif']." and idEpreuveParcours = ".$row['idParcours']." and idEpreuve = ".$row['idEpreuve']." and idInternaute=".$row['idInternaute']."";
		 $mysqli->query($q1);
		//exit();
		/*** LOGS ****/
		fputs($fp," : if (type_paiement== 'CHQ') inertnaute gratuit seul UPDATE r_inscriptionepreuveinternaute : ".$q1."\n");
		/*** LOGS ****/
		//echo "nb_inscrit: ".$nb_inscrit."</br>";
		//echo "idInternaute : ".$row['idInternaute']."</br>";
		//echo "id_internaute_ref : ".$id_internaute_ref."</br>";
		if ($nb_inscrit > 1 && $row['idInternaute'] != $id_internaute_ref ) {
			
			$donnees = array();
		
			$row_info = info_internaute_send_mail ($row['idInscriptionEpreuveInternaute'],($row['cout'] + $row['participation']+$frais_cheque_coureur),'CHEQUE');
			$row_info['referent'] = $id_internaute_ref;
			
			array_push($donnees,$row_info);
			//print_r($donnees);
			
			$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
			$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
			
		/*** LOGS ****/
		fputs($fp," : CHQ : mail_send (non referent) : ".$mail_send."\n");
		/*** LOGS ****/
		
			if ($row_info['code_promo'] != 'Aucun') {
				
				mise_a_jour_code_promo ($row['idEpreuve'],$row['idParcours'],$row_info['code_promo'],$row['idInscriptionEpreuveInternaute']);
			}
			
			//mail à l'organisateur
			if (!empty($email_organisateur)) {
				$dossard = '0';
				$row_info['numerotation'] = $dossard;
				$row_info['type_mail'] = 'organisateur';			
				$donnees[0]=$row_info;
				$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
			}
		}
		elseif ($nb_inscrit > 1 && $row['idInternaute'] == $id_internaute_ref ) 
		{
			
			$donnees = array();
		
			$row_info = info_internaute_send_mail ($row['idInscriptionEpreuveInternaute'],($row['cout'] + $row['participation']+$frais_cheque_coureur),'CHEQUE');
			$row_info['referents'] = $id_internaute_ref ;
			
			array_push($donnees,$row_info);
			//print_r($donnees);

			$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
			$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
		
		/*** LOGS ****/
		fputs($fp," : CHQ : mail_send (referent) : ".$mail_send."\n");
		/*** LOGS ****/
		
			$queryupiei=" UPDATE r_insc_internautereferent
			SET paiement_type='CHQ', montant = ".($cout+$frais_cheque)." 
			WHERE idEpreuve = ".$row['idEpreuve']."
			AND idEpreuveParcours REGEXP '".$id_internaute_idEpreuveParcours."'
			AND idInternauteReferent = ".$id_referent."
			AND paiement_type in('ATTENTE')
			AND paiement_date IS NULL";
			$resultupiei = $mysqli->query($queryupiei);
		
		/*** LOGS ****/
		fputs($fp," : elseif (nb_inscrit > 1 && row['idInternaute'] == id_internaute_ref ) UPDATE r_insc_internautereferent : ".$queryupiei."\n");
		/*** LOGS ****/
	
			if ($row_info['code_promo'] != 'Aucun') {
				
				mise_a_jour_code_promo ($row['idEpreuve'],$row['idParcours'],$row_info['code_promo'],$row['idInscriptionEpreuveInternaute']);
			}
			
			//mail à l'organisateur
			if (!empty($email_organisateur)) {
				$dossard = '0';
				$row_info['numerotation'] = $dossard;
				$row_info['type_mail'] = 'organisateur';			
				$donnees[0]=$row_info;
				$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
			}
		}
		else
		{
			$donnees = array();
			
			
			$row_info = info_internaute_send_mail ($row['idInscriptionEpreuveInternaute'],($row['cout'] + $row['participation']+$frais_cheque_coureur),'CHEQUE');
			array_push($donnees,$row_info);
		
			$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
		
			$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
			
		/*** LOGS ****/
		fputs($fp," : CHQ : mail_send (Seul) : ".$mail_send."\n");
		/*** LOGS ****/
		
			if ($row_info['code_promo'] != 'Aucun') {
				
				mise_a_jour_code_promo ($row['idEpreuve'],$row['idParcours'],$row_info['code_promo'],$row['idInscriptionEpreuveInternaute']);
			}		
			
			//mail à l'organisateur
			if (!empty($email_organisateur)) {
				$dossard = '0';
				$row_info['numerotation'] = $dossard;
				$row_info['type_mail'] = 'organisateur';			
				$donnees[0]=$row_info;
				$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
			}
		}
		
		if ($mail_send == 'ok') {
			


			$queryupriet=" UPDATE r_insc_internaute_temp
			SET  typePaiement='CHQ', frais_cheque = ".$frais_cheque."
			WHERE idEpreuve = ".$row['idEpreuve']."
			AND idParcours = ".$row['idParcours']."
			AND idInscriptionEpreuveInternaute = ".$row['idInscriptionEpreuveInternaute']."
			AND typePaiement in('ATTENTE')";
			//echo $queryupriet;
			$resultupriet = $mysqli->query($queryupriet);
			/*
			$q1  = "UPDATE r_inscriptionepreuveinternaute SET frais_cheque = ".$frais_cheque." ";
			$q1 .= "WHERE idEpreuveParcoursTarif = ".$row['idEpreuveParcoursTarif']." and idEpreuveParcours = ".$row['idParcours']." and idEpreuve = ".$row['idEpreuve']." and idInternaute=".$row['idInternaute']."";
					//echo $q1;
			$mysqli->query($q1);
			*/
			$query_del =  "DELETE FROM r_insc_internaute_temp ";
			$query_del .= "WHERE idInscInternauteTemp = ".$row['idInscInternauteTemp'];
			//$result_del = $mysqli->query($query_del);
		/*** LOGS ****/
		fputs($fp," : CHQ / UPDATE r_insc_internaute_temp : ".$queryupriet."\n");
		/*** LOGS ****/
		
		
		}
		

	
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
		/*** LOGS ****/
		fputs($fp," : CB / if(payeur == 'coureur') : ".$q1."\n");
		/*** LOGS ****/
				}
				else //Si les frais sont à la charge de l'organisateur (inclus dans le prix du parcours)
				{
					//Enregistrement des frais supplémentaires quel que soit le payeur
					$q1  = "UPDATE r_inscriptionepreuveinternaute SET frais_cb = ".$cout_paiement_cb." ";
					$q1 .= "WHERE idEpreuveParcoursTarif = ".$row['idEpreuveParcoursTarif']." and idEpreuveParcours = ".$row['idParcours']." and idEpreuve = ".$row['idEpreuve']." and idInternaute=".$row['idInternaute']."";
					$mysqli->query($q1);
		
		/*** LOGS ****/
		fputs($fp," : CB / if(payeur == 'organisateur') : ".$q1."\n");
		/*** LOGS ****/
				
				}
			
			$queryupriet=" UPDATE r_insc_internaute_temp
			SET typePaiement='CB' 
			WHERE idEpreuve = ".$row['idEpreuve']."
			AND idParcours = ".$row['idParcours']."
			AND idInscriptionEpreuveInternaute = ".$row['idInscriptionEpreuveInternaute']."
			AND typePaiement in('ATTENTE')";
			//echo $queryupriet;
			$resultupriet = $mysqli->query($queryupriet);
			
		}
}


	if ($type_paiement== 'CB') {
	
			//echo $frais_cb;
			//echo $cout_paiement_cb;
			/*$query_tarif = "SELECT tarif FROM r_epreuveparcourstarif WHERE idEpreuveParcoursTarif = ".$row['idEpreuveParcoursTarif'];
			$resul_tarif = $mysqli->query($query_tarif );
			$row_tarif = mysqli_fetch_array($resul_tarif);
			$tarif = $row_tarif['tarif'];*/
				if($payeur == 'coureur') $total = $cout+$frais_cb; else $total = $cout;
				
			
			//pour test
			//$total=1;
				if ($nb_inscrit >1 && $id_referent!=0 ) {
					
					$q1  = "UPDATE r_insc_internautereferent SET frais_cb = ".round($frais_cb, 2)." ";
					$q1 .= "WHERE idInternauteReferent = ".$id_referent." and idEpreuve = ".$id_epreuve."";
					//echo $q1;
					$mysqli->query($q1);
		
		/*** LOGS ****/
		fputs($fp," : CB / if (nb_inscrit >1 && id_referent!=0 ) : ".$q1."\n");
		/*** LOGS ****/
				
					$query_ref= "SELECT emailInternaute,rif.idInternauteref FROM r_internaute as ri ";
					$query_ref.="INNER JOIN r_internautereferent as rif ON ri.idInternaute = rif.idInternauteref ";
					$query_ref.="WHERE rif.idInternauteReferent = ".$id_referent;
		
		/*** LOGS ****/
		fputs($fp," : CB / if (nb_inscrit >1 && id_referent!=0 ) SELECT emailInternaute : ".$query_ref."\n");
		/*** LOGS ****/
		
					$result_ref = $mysqli->query($query_ref);
					$row_ref = mysqli_fetch_array($result_ref);
					
					$bpaiements  = "INSERT INTO b_paiements (reference, montant, frais_cb, date, id_referant, id_epreuve, statut) ";
					$bpaiements .= "VALUE ('M-".$id_referent."', ".round($total, 2).", ".($payeur == 'coureur'?round($frais_cb, 2):$cout_paiement_cb).", NOW(), ".$row_ref['idInternauteref'].", ".$id_epreuve.", 'ATTENTE')";  
					$result = $mysqli->query($bpaiements);
					$html_ca = create_form_ca("M-".$id_referent, round($total, 2), $row_ref['emailInternaute']);
		
		/*** LOGS ****/
		fputs($fp," : CB / if (nb_inscrit >1 && id_referent!=0 ) INSERT INTO b_paiements : ".$bpaiements."\n");
		/*** LOGS ****/
		
				}else // pas de référent, inscription seule
				{
					
					$bpaiements  = "INSERT INTO b_paiements (reference, montant, frais_cb, date, id_referant, id_epreuve, statut) ";
					$bpaiements .= "VALUE ('".$id_referant_insc_seul."', ".round($total, 2).", ".($payeur == 'coureur'?round($frais_cb, 2):$cout_paiement_cb).", NOW(), ".$id_internaute_referant_insc_seul.", ".$id_epreuve.", 'ATTENTE')";  
					$result = $mysqli->query($bpaiements);
					
					$html_ca = create_form_ca($id_referant_insc_seul, round($total, 2), $email_referant_seul);
		
		/*** LOGS ****/
		fputs($fp," : CB / pas de référent, inscription seule INSERT INTO b_paiements : ".$bpaiements."\n");
		/*** LOGS ****/
		
	
					
					
				}
				//echo "total".$total = $cout+$frais_cb;
				

				//echo $row_email['emailInternaute'];
				
//echo $bpaiements;


				$aff= array('comeback'=>'|CB|','frais_coureur' =>$cout_paiement_cb, 'html_ca'=>$html_ca);
				//print_r($tab_inscription);
				echo json_encode($aff);
				
				//***session_destroy();
				//***unset($_SESSION);
	}
	elseif ($type_paiement== 'CHQ') {

			
			$aff= array('comeback'=>'|CHQ|');
			//print_r($tab_inscription);
			echo json_encode($aff);
			//ENVOYER UN EMAIL 
			
			//***session_destroy();
			//***unset($_SESSION);
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
					
					//***session_destroy();
					//***unset($_SESSION);
	
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