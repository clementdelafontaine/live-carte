<?php 
require_once("includes.php");
require("functions_n.php");
require_once('functions_mail_n.php');
require_once("numerotation.php");
global $mysqli;
$insc_gratuite =$_POST['gratuit'];
$simul =$_POST['simul'];
$nb_inscrit = count($_SESSION['rieis']);

		$fp = fopen("../logs_insc.txt","a");
		fputs($fp,"DATE : ".date("d/m/Y H-i-s")." : ".$_SERVER['REQUEST_URI']." - IP CLIENTS : ".get_ip()." \n");
		fputs($fp," : ------------ AJAX SESSION ----------------\n");
		fputs($fp, " insc_gratuite : ".$insc_gratuite."\n");
		fputs($fp, " simul : ".$simul."\n");
		fputs($fp, " nb_inscrit : ".$nb_inscrit."\n");
		
		
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
		fputs($fp," : if (nb_inscrit > 1 )  SELECT ri.idInternauteref : ".$query."\n");
		/*** LOGS ****/
						
}

$query =  "SELECT * from r_inscriptionepreuveinternaute as riei ";
$query .= "INNER JOIN r_internaute as ri ON riei.idInternaute = ri.idInternaute ";
$query .= "WHERE riei.idEpreuve = ".$_SESSION['idEpreuve']." ";
$query .= "AND riei.id_session = '".$_SESSION['unique_id_session']."' ";
$query .=" ORDER BY riei.idInscriptionEpreuveInternaute  DESC LIMIT ".$nb_inscrit;
$result = $mysqli->query($query);

		/*** LOGS ****/
		fputs($fp," SELECT * from r_inscriptionepreuveinternaute as riei : ".$query."\n");
		/*** LOGS ****/
		
		
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
		fputs($fp," : if (insc_gratuite==1) UPDATE r_inscriptionepreuveinternaute : ".$q1."\n");
		/*** LOGS ****/
		
		if ($nb_inscrit > 1 && $row['idInscriptionEpreuveInternaute'] != $id_internauteInscription_ref ) {
			
			$donnees = array();
		
			$row_info = info_internaute_send_mail_test ($row['idInscriptionEpreuveInternaute'],$row['cout'],'GRATUIT');
			$row_info['referent'] = $id_internaute_ref;
			
			array_push($donnees,$row_info);
			//print_r($donnees);
			$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
			$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
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
				$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
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
			$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
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
		fputs($fp," : if (insc_gratuite==1) UPDATE r_insc_internautereferent : ".$queryupiei."\n");
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
				$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
			}
		}
		else
		{
			$donnees = array();
			
			
			$row_info = info_internaute_send_mail_test ($row['idInscriptionEpreuveInternaute'],$row['cout'],'GRATUIT');
			array_push($donnees,$row_info);
			//print_r($donnees);
			$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
			
			$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
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
				$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
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
	if ($simul==1) 
	{
		


		//ECHO "SIMULATION";


		/*
		* Le paiement est accepté
		* On recherche le montant d'origine pour le comparer avec le montant retourné
		*/
		$query="SELECT montant,id_epreuve FROM b_paiements WHERE reference='".$_POST['Ref']."'";
		$result=$mysqli->query($query);
		$row=mysqli_fetch_array($result);
		$verif_montant=($row['montant']*100);
		$montant=$row['montant'];
		$id_epreuve = $row['id_epreuve'];
		$payeur = extract_champ_epreuve ('payeur',$id_epreuve);
		
		/*** LOGS ****/
		fputs($fp," : if (simul==1) SELECT montant,id_epreuve : ".$query."\n");
		/*** LOGS ****/	
		
		//$payeur = 'organisateur';
		//;
		/**
		* On vérifie que le code erreur soit à 00000
		* Et que le montant retourné soit égal à celui d'origines
		*/

		$reference = $_POST['Ref'];
		
		if(preg_match('/^M-/', $reference, $matches)==1)
		{
			// Paiement multiple
			
			list($index, $reference)= explode("-", $reference);
			
			if(preg_match('/^T/', $reference, $matches)==1) 
			{ 
				//echo "PRESENCE d'un T";
				list($index, $reference)= explode("T", $reference);
				//echo "REFERENCE : ".$reference;
				$reference = "M-".extract_champ_r_idInternauteInscriptionref('idInternauteReferent',$reference);
				
			} 
			else 
			{ 
				//echo "PAS DE T"; 
			}
					
			$query="SELECT montant,id_epreuve FROM b_paiements WHERE reference='".$reference."'";
			$result=$mysqli->query($query);
			$row=mysqli_fetch_array($result);
			$verif_montant=($row['montant']*100);
			$montant=$row['montant'];
			$id_epreuve = $row['id_epreuve'];
			$payeur = extract_champ_epreuve ('payeur',$id_epreuve);

	
			$query = "SELECT id_referant,id_epreuve FROM b_paiements
					  WHERE reference = '".addslashes($reference)."' AND paiement_date IS NULL";
			$result = $mysqli->query($query);
			$row=mysqli_fetch_row($result);
		

		
			
			// Si reference absente on refuse
			if ($row === FALSE) 
			{
				header('Content-Type: text/plain');
				echo "Invalid reference";
				exit;
			}
			else
			{   
				
				list($index, $reference)= explode("-", $reference);
				
				$val='';
				$val=mysqli_fetch_array($result);
				$idInternautes = array();
				$idInscriptionEpreuveInternautes = array();
				
				/*
				//$query = "SELECT idInternauteref, idInternautes, idInscriptionEpreuveInternautes, idEpreuveParcours FROM  r_internautereferent ";
				$query = "SELECT idInternauteref,riir.idEpreuveParcours,rir.idInscriptionEpreuveInternautes as idIEs, riit.idInscriptionEpreuveInternaute FROM r_internautereferent AS rir ";
				$query .= "INNER JOIN r_insc_internautereferent AS riir ON rir.idInternauteReferent = riir.idInternauteReferent ";
				$query .= "INNER JOIN r_insc_internaute_temp AS riit ON rir.idInternauteref = riit.idInternaute ";
				$query .= "WHERE rir.idInternauteReferent = ".addslashes($reference);
				//echo $query;
				

				
				
				$result = $mysqli->query($query);
				$row=mysqli_fetch_array($result);
				*/
				$id_relais =  internaute_referent_internautes_ref($reference);
                //***$course_relais =  extract_champ_parcours('relais');

				//print_r($id_relais);
				//exit();nomReferent
				$dossard_equipe = extract_champ_parcours('dossard_equipe',$id_relais['idEpreuveParcours']);
						
				$id_internaute_referent = $id_relais['idInternauteref'];
				$id_internaute_inscription_referent = $id_relais['idInscriptionEpreuveInternaute'];
				
				$dossard_referent = numerotation($id_relais['idEpreuveParcours'],$id_epreuve,$id_internaute_inscription_referent);
				
				$idInscriptionEpreuveInternautes = explode('|', $id_relais['idInscriptionEpreuveInternautes']);
				$cpt_internautes = count($idInscriptionEpreuveInternautes);
				
				if ( extract_champ_epreuve('payeur',$id_epreuve)== 'organisateur' ) $paiement_frais_cb = 0; else $paiement_frais_cb = 1;
				$montant_total_coureurs = 0;
				$patronymes_inscrits = '';
				$first==TRUE;
				foreach ($idInscriptionEpreuveInternautes as $key=>$idInscriptionEpreuveInternaute) {
					
					
					
					$query_tmp = "SELECT montant_inscription,idInternaute,participation,frais_cb FROM r_inscriptionepreuveinternaute ";
					$query_tmp.= " WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
					$result_tmp = $mysqli->query($query_tmp);
					$row_tmp=mysqli_fetch_row($result_tmp);
					
					//$idInscriptionEpreuveInternaute;
				
					$donnees = array();
			
					$row_info = info_internaute_send_mail_test ($idInscriptionEpreuveInternaute,($row_tmp[0]+$row_tmp[2]),'CB');
					$row_info['referent'] = $row_info['idInternauteReferent'];
				

					
					//print_r($donnees);
					
					
					if ($payeur=='coureur') {
					
						$montant_total = $row_info['cout'] + $row_info['participation'] + $row_info['frais_cb'] + $row_info['Prix_OptionPlus'] + $row_info['assurance'];
						$frais_cb = 0;
					}
					else
					{
						$montant_total = $row_info['cout'] + $row_info['participation'] + $row_info['Prix_OptionPlus'] + $row_info['assurance'];;
						$frais_cb = $row_info['frais_cb'];
					}
					$nomParcours=extract_champ_parcours('nomParcours',$row_info['idEpreuveParcours']);
					if ($first==TRUE)
					{
						$patronymes_inscrits = '<i>'.$nomParcours.'</i> - '.$row_info['nomInternaute']." ".$row_info['prenomInternaute']." [ ".$montant_total." € ]";
						$first=FALSE;
					}
					else
					{	
						$patronymes_inscrits .= "<br/>".'<i>'.$nomParcours.'</i> - '.$row_info['nomInternaute']." ".$row_info['prenomInternaute']." [ ".$montant_total." € ]";
					}
					
					$row_info['paiement_montant'] = $montant_total;
					
					if (empty($row_info['InscritParidInternaute']))
					{

						$row_info['nomReferent']=extract_champ_internaute('nomInternaute',$id_internaute_referent);
						$row_info['prenomReferent']=extract_champ_internaute('prenomInternaute',$id_internaute_referent);	
					}
					else
					{
						$row_info['nomReferent']=extract_champ_internaute('nomInternaute',$row_info['InscritParidInternaute']);
						$row_info['prenomReferent']=extract_champ_internaute('prenomInternaute',$row_info['InscritParidInternaute']);					
						
					}
					$id_tmp =  check_internaute_existant_v3 ($row_info['nomInternaute'],$row_info['prenomInternaute'],$row_info['naissanceInternaute'],$row_info['sexeInternaute']);
					//echo "id_tmp : --".$id_tmp."---"; 
					if (empty($id_tmp)) $row_info['ref']=extract_champ_internaute('passInternaute',$row_info['idInternaute']);
					
					array_push($donnees,$row_info);
					//print_r($donnees);
					$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
					$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
					
					if ($payeur=='coureur') {
					
						$montant_total = $row_info['cout'] + $row_info['participation'] + $row_info['frais_cb'] + $row_info['Prix_OptionPlus'] + $row_info['assurance'];
						$frais_cb = 0;
					}
					else
					{
						$montant_total = $row_info['cout'] + $row_info['participation'] + $row_info['Prix_OptionPlus'] + $row_info['assurance'];;
						$frais_cb = $row_info['frais_cb'];
					}
					
					/*
					if ($paiement_frais_cb==1) $row_info['frais_cb']= extract_champ_b_paiements('frais_cb',$reference); else $row_info['frais_cb']=0;
					$row_info['paiement_montant'] = (($_GET['Montant']/100));
					*/
					
					$queryupiei=" UPDATE r_inscriptionepreuveinternaute AS iei
					SET iei.paiement_type='TEST',iei.paiement_date=NOW(), iei.paiement_montant = ".$montant_total.", iei.frais_cheque = 0, iei.frais_cb= ".$frais_cb."
					WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
					/*
					AND iei.idEpreuve = ".$row_info['idEpreuve']."
					
					AND iei.idEpreuveParcours = ".$row_info['idParcours']."
					AND iei.idInternaute = ".$row_info['idInternaute']."
					*/
					$queryupiei .= " AND iei.paiement_type in('ATTENTE','ATTENTE CHQ')";
					$queryupiei .= " AND iei.paiement_date IS NULL";
					
					//****$resultupiei = $mysqli->query($queryupiei);
					$affected = mysqli_affected_rows();//foreach ($row_info['idInternaute'] as $key=>$idInternaute ) 
					//echo $queryupiei;
					$email_organisateur = recup_mail_organisateur_epreuve($row_info['idEpreuve']);
					
					$dossard=0;
					//if ($dossard_equipe=='non') $dossard = numerotation($row_info['idParcours'],$row_info['idEpreuve'],$idInscriptionEpreuveInternaute);
					
					$dossard = numerotation($row_info['idParcours'],$row_info['idEpreuve'],$idInscriptionEpreuveInternaute,$id_internaute_inscription_referent);
					
					if ($row_info['place_promo'] ==1) {
						maj_tarif_reduc_place($row_info['idEpreuveParcoursTarif'],'update',$idInscriptionEpreuveInternaute);
					}
					
					if ($row_info['code_promo'] != 'Aucun') {
				
						//mise_a_jour_code_promo ($row_info['idEpreuve'],$row_info['idParcours'],$row_info['code_promo'],$idInscriptionEpreuveInternaute);
						//mise_a_jour_code_promo ($row_info['idEpreuve'],$row_info['idParcours'],$row_info['code_promo'],$row_info['valeur_code_promo'],$row_info['idEpreuveParcoursTarifPromo'], $idInscriptionEpreuveInternaute);
					}

					//mail à l'organisateur
					if (!empty($email_organisateur)) {
	
						$row_info['numerotation'] = $dossard;
						$row_info['type_mail'] = 'organisateur';			
						$donnees[0]=$row_info;
						$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);					
					}
					$montant_total_coureurs += $montant_total;
				}
				
				//envoi email au référent
				
				$donnees = array();
				/*
				$query_tmp = "SELECT cout,idInternaute,participation,frais_cb FROM r_insc_internaute_temp ";
				$query_tmp.= " WHERE idInscriptionEpreuveInternaute = ".$id_internaute_inscription_referent;
				$result_tmp = $mysqli->query($query_tmp);
				$row_tmp=mysqli_fetch_row($result_tmp);
				*/
				
				$row_info = info_internaute_send_mail_test ($id_internaute_inscription_referent,0,'CB');
				$row_info['referents'] = $row_tmp[1] ;
				if (empty($row_info['valeur_code_promo'])) $row_info['valeur_code_promo'] = 0; 
				
				$row_info['frais_cb'] = extract_champ_b_paiements('frais_cb',"M-".$reference);
				
				$frais_cb = $row_info['frais_cb'];
				
				//echo "payeur : ".$payeur;
				if ($payeur=='coureur') {
				
					$montant_total = ($row_info['cout'] + $row_info['participation'] + $frais_cb + $row_info['Prix_OptionPlus'] + $row_info['assurance']) - $row_info['valeur_code_promo'];
					$frais_cb_referent = $frais_cb ;
					
				}
				else
				{
					$montant_total = ($row_info['cout'] + $row_info['participation'] + $row_info['Prix_OptionPlus'] + $row_info['assurance'])- $row_info['valeur_code_promo'];
					$frais_cb_referent = ($frais_cb*($cpt_internautes+1));
					
				}
				//echo "frais_cb_referent : ".$frais_cb_referent;
				$row_info['montant_total_coureurs'] = $montant_total_coureurs;
				$row_info['paiement_montant'] = $montant_total + $montant_total_coureurs;
				$row_info['patronymes_inscrits'] = $patronymes_inscrits;
				
				array_push($donnees,$row_info);
				//print_r($donnees);
				$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
				$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
				

				
				$queryupiei=" UPDATE r_inscriptionepreuveinternaute AS iei
				SET iei.paiement_type='TEST',iei.paiement_date=NOW(), iei.paiement_montant = ".$montant_total.", iei.frais_cheque = 0, iei.frais_cb= ".$frais_cb."
				WHERE idInscriptionEpreuveInternaute = ".$id_internaute_inscription_referent;
				$queryupiei .= " AND iei.paiement_type in('ATTENTE','ATTENTE CHQ')";
				$queryupiei .= " AND iei.paiement_date IS NULL";
				/*
				WHERE iei.idEpreuve = ".$row_info['idEpreuve']."
				AND iei.idEpreuveParcours = ".$row_info['idParcours']."
				AND iei.idInternaute = ".$row_info['idInternaute']."
				AND iei.paiement_type in('ATTENTE','ATTENTE CHQ')
				AND iei.paiement_date IS NULL";
				*/
				//****$resultupiei = $mysqli->query($queryupiei);
				$affected = mysqli_affected_rows();//foreach ($row_info['idInternaute'] as $key=>$idInternaute ) 
				//print_r($idInscriptionEpreuveInternautes);
				$queryupiei=" UPDATE r_insc_internautereferent
				SET paiement_type='TEST', paiement_date=NOW(), montant = ".($_POST['Montant']/100).", frais_cb= ".$frais_cb_referent." 
				WHERE idInternauteReferent = ".$reference;
		
				
				//****$resultupiei = $mysqli->query($queryupiei);
				$affected = mysqli_affected_rows();
				//echo $queryupiei;
				$querybp="UPDATE b_paiements SET montant = ".($_POST['Montant']/100).", paiement_type='TEST', paiement_date = NOW(), statut = 'VALIDEE'
				WHERE reference = 'M-".addslashes($reference)."' AND paiement_date IS NULL";
				$result_bp = $mysqli->query($querybp);
				//$querybp;

				
				//$dossard = numerotation($row_info['idParcours'],$row_info['idEpreuve'],$id_internaute_inscription_referent);
				
				if ($row_info['place_promo'] ==1) {
					maj_tarif_reduc_place($row_info['idEpreuveParcoursTarif'],'update',$idInscriptionEpreuveInternaute);
				}
				
				if ($row_info['code_promo'] != 'Aucun') {
				
					//mise_a_jour_code_promo ($row_info['idEpreuve'],$row_info['idParcours'],$row_info['code_promo'],$id_internaute_inscription_referent);
					mise_a_jour_code_promo ($row_info['idEpreuve'],$row_info['idParcours'],$row_info['code_promo'],$row_info['valeur_code_promo'],$row_info['idEpreuveParcoursTarifPromo'], $id_internaute_inscription_referent);
				}
				
					//mail à l'organisateur
					if (!empty($email_organisateur)) {
	
						$row_info['numerotation'] = $dossard;
						$row_info['type_mail'] = 'organisateur';			
						$donnees[0]=$row_info;
						//***$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);	
					}
					

				
			}
		}
		else if(preg_match('/^A-/', $reference, $matches)==1)
		{
			// Paiement multiple
			
			list($index, $idreference,$idpaiementunique)= explode("-", $reference);
			//echo "idreference :".$idreference. " - idpaimentunique : ".$idpaiementunique;
			/*
			if(preg_match('/^T/', $reference, $matches)==1) 
			{ 
				echo "PRESENCE d'un T";
				list($index, $reference)= explode("T", $reference);
				echo "REFERENCE : ".$reference;
				echo $reference = "M-".extract_champ_r_idInternauteInscriptionref('idInternauteReferent',$reference);
				
			} 
			else 
			{ 
				//echo "PAS DE T"; 
			}
			*/
			
			$query="SELECT montant,id_epreuve,id_referant FROM b_paiements WHERE reference='".$reference."'";
			$result=$mysqli->query($query);
			$row=mysqli_fetch_array($result);
			$verif_montant=($row['montant']*100);
			$montant=$row['montant'];
			$id_epreuve = $row['id_epreuve'];
			$id_referant = $row['id_referant'];
			$payeur = extract_champ_epreuve ('payeur',$id_epreuve);

			/*** LOGS ****/
			fputs($fp," : if (simul==1) SELECT montant,id_epreuve : ".$query."\n");
			/*** LOGS ****/	
			
			
			
			
			$query = "SELECT id_referant,id_epreuve FROM b_paiements
					  WHERE reference = '".addslashes($reference)."' AND paiement_date IS NULL";
			$result = $mysqli->query($query);
			$row=mysqli_fetch_row($result);
		
			/*** LOGS ****/
			fputs($fp," : if (simul==1) SELECT id_referant,id_epreuve : ".$query."\n");
			/*** LOGS ****/	
		
			
			// Si reference absente on refuse
			if ($row === FALSE) 
			{
				header('Content-Type: text/plain');
				echo "Invalid reference";
				exit;
			}
			else
			{   
				
				//***list($index, $reference)= explode("-", $reference);
				
				$val='';
				$val=mysqli_fetch_array($result);
				$idInternautes = array();
				$idInscriptionEpreuveInternautes = array();
				
				/*
				//$query = "SELECT idInternauteref, idInternautes, idInscriptionEpreuveInternautes, idEpreuveParcours FROM  r_internautereferent ";
				$query = "SELECT idInternauteref,riir.idEpreuveParcours,rir.idInscriptionEpreuveInternautes as idIEs, riit.idInscriptionEpreuveInternaute FROM r_internautereferent AS rir ";
				$query .= "INNER JOIN r_insc_internautereferent AS riir ON rir.idInternauteReferent = riir.idInternauteReferent ";
				$query .= "INNER JOIN r_insc_internaute_temp AS riit ON rir.idInternauteref = riit.idInternaute ";
				$query .= "WHERE rir.idInternauteReferent = ".addslashes($reference);
				//echo $query;
				

				
				
				$result = $mysqli->query($query);
				$row=mysqli_fetch_array($result);
				*/
				

				/*
					$idInternauteRef = 172371;
					$id_epreuve = 4031;
					$id_parcours = 7069;
					*/
					$internautes_equipe_all = array();
					$query_int_ref = " SELECT rir.idInternauteReferent FROM r_internautereferent as rir ";
					$query_int_ref .= " INNER JOIN r_insc_internautereferent as riir ON rir.idInternauteReferent = riir.idInternauteReferent ";
					$query_int_ref .= " WHERE id_unique_paiement = '".addslashes($idpaiementunique)."'";
					//$query_int_ref .= " AND rir.idInternautes LIKE CONCAT('%',".$idInternaute.",'%') ";
					//$query_int_ref .= " AND riir.paiement_type NOT IN ('SUPPRESSION','REMBOURSE') ";
					$query_int_ref .= " AND riir.paiement_date is NULL ";
					$query_int_ref .= " AND rir.idEpreuve =".$id_epreuve;
					$query_int_ref .= " ORDER BY rir.idInternauteReferent DESC ";
					//$query_int_ref .= " LIMIT 1 ";

					/*** LOGS ****/
					fputs($fp," : if (simul==1) SELECT rir.idInternauteReferent : ".$query_int_ref."\n");
					/*** LOGS ****/					
					
					$result = $mysqli->query($query_int_ref);
					$nb_equipe = mysqli_num_rows($result);
					
					$first_ref==TRUE;
					$cpt = 1;
					$patronymes_inscrits_ref ='';
					$cestlereferant = 0;
					while($row = mysqli_fetch_array($result)) 
					{
				
						//echo "EQUIPE : ".$row['idInternauteReferent']."</br>";
						$id_relais =  internaute_referent_internautes_ref($row['idInternauteReferent']);
						//***$course_relais =  extract_champ_parcours('relais');
		
						//print_r($id_relais);
						//exit();nomReferent
						$dossard_equipe = extract_champ_parcours('dossard_equipe',$id_relais['idEpreuveParcours']);
								
						$id_internaute_referent = $id_relais['idInternauteref'];
						$id_internaute_inscription_referent = $id_relais['idInscriptionEpreuveInternaute'];
						
						$dossard_referent = numerotation($id_relais['idEpreuveParcours'],$id_epreuve,$id_internaute_inscription_referent);
						
						$idInscriptionEpreuveInternautes = explode('|', $id_relais['idInscriptionEpreuveInternautes']);
						$cpt_internautes = count($idInscriptionEpreuveInternautes);
						
						if ( extract_champ_epreuve('payeur',$id_epreuve)== 'organisateur' ) $paiement_frais_cb = 0; else $paiement_frais_cb = 1;
						$montant_total_coureurs = 0;
						$patronymes_inscrits = '';
						$first==TRUE;
						
						foreach ($idInscriptionEpreuveInternautes as $key=>$idInscriptionEpreuveInternaute) 
						{
							$internautes_equipe_all[] = $idInscriptionEpreuveInternaute;
							//echo "TEST : ".$idInscriptionEpreuveInternautes[0]. " - SECOND : ".$idInscriptionEpreuveInternautes[1];
						
							$query_tmp = "SELECT montant_inscription,idInternaute,participation,frais_cb FROM r_inscriptionepreuveinternaute ";
							$query_tmp.= " WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
							$result_tmp = $mysqli->query($query_tmp);
							$row_tmp=mysqli_fetch_row($result_tmp);
							
							/*** LOGS ****/
							fputs($fp," : if (simul==1) SELECT montant_inscription,idInternaute : ".$query_tmp."\n");
							/*** LOGS ****/	
						
							$donnees = array();
					
							$row_info = info_internaute_send_mail_test ($idInscriptionEpreuveInternaute,($row_tmp[0]+$row_tmp[2]),'CB');
							$row_info['referent'] = $row_info['idInternauteReferent'];
						
		
							
							//print_r($donnees);
							
							
							if ($payeur=='coureur') {
							
								$montant_total = $row_info['tarif'] + $row_info['participation'] + $row_info['frais_cb'] + $row_info['Prix_OptionPlus'] + $row_info['assurance'];
								$frais_cb = 0;
							}
							else
							{
								$montant_total = $row_info['tarif'] + $row_info['participation'] + $row_info['Prix_OptionPlus'] + $row_info['assurance'];;
								$frais_cb = $row_info['frais_cb'];
							}
							$nomParcours=extract_champ_parcours('nomParcours',$row_info['idEpreuveParcours']);
							
							if ($first==TRUE)
							{
								$patronymes_inscrits = '<i>'.$nomParcours.'</i> - '.$row_info['nomInternaute']." ".$row_info['prenomInternaute'];
								$first=FALSE;
							}
							else
							{	
								$patronymes_inscrits .= "<br/>".'<i>'.$nomParcours.'</i> - '.$row_info['nomInternaute']." ".$row_info['prenomInternaute'];
							}
							
							if ($first_ref==TRUE)
							{
								$patronymes_inscrits_ref .= '<i>'.$nomParcours.'</i> - '.$row_info['nomInternaute']." ".$row_info['prenomInternaute'];
								$first_ref=FALSE;
							}
							else
							{	
								$patronymes_inscrits_ref .= "<br/>".'<i>'.$nomParcours.'</i> - '.$row_info['nomInternaute']." ".$row_info['prenomInternaute'];
							}
							
							$row_info['paiement_montant'] = $montant_total;
							

		
								$row_info['nomReferent']=extract_champ_internaute('nomInternaute',$id_internaute_referent);
								$row_info['prenomReferent']=extract_champ_internaute('prenomInternaute',$id_internaute_referent);	

								$row_info['nomReferent_paiement']=extract_champ_internaute('nomInternaute',$row_info['InscritParidInternaute']);
								$row_info['prenomReferent_paiement']=extract_champ_internaute('prenomInternaute',$row_info['InscritParidInternaute']);					
								
							
							$id_tmp =  check_internaute_existant_v3 ($row_info['nomInternaute'],$row_info['prenomInternaute'],$row_info['naissanceInternaute'],$row_info['sexeInternaute']);
							//echo "id_tmp : --".$id_tmp."---"; 
							if (empty($id_tmp)) $row_info['ref']=extract_champ_internaute('passInternaute',$row_info['idInternaute']);
							if (!empty($row_info['idOptionPlus'])) $row_info['nomOptionPlus'] = extract_options_plus('label',$row_info['idOptionPlus']);
							
							array_push($donnees,$row_info);
							//print_r($donnees);
							
							$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
							$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
							
							if ($payeur=='coureur') {
							
								$montant_total = $row_info['cout'] + $row_info['participation'] + $row_info['frais_cb'] + $row_info['Prix_OptionPlus'] + $row_info['assurance'];
								$frais_cb = 0;
							}
							else
							{
								$montant_total = $row_info['cout'] + $row_info['participation'] + $row_info['Prix_OptionPlus'] + $row_info['assurance'];;
								$frais_cb = $row_info['frais_cb'];
							}
							
							/*
							if ($paiement_frais_cb==1) $row_info['frais_cb']= extract_champ_b_paiements('frais_cb',$reference); else $row_info['frais_cb']=0;
							$row_info['paiement_montant'] = (($_GET['Montant']/100));
							*/
							
							$queryupiei=" UPDATE r_inscriptionepreuveinternaute AS iei
							SET iei.paiement_type='TEST',iei.paiement_date=NOW(), iei.paiement_montant = ".$montant_total.", iei.frais_cheque = 0, iei.frais_cb= ".$frais_cb."
							WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
							$queryupiei .= " AND iei.paiement_type in('ATTENTE','ATTENTE CHQ')";
							$queryupiei .= " AND iei.paiement_date IS NULL";
							
							/*** LOGS ****/
							fputs($fp," : if (simul==1) UPDATE r_inscriptionepreuveinternaute : ".$queryupiei."\n");
							/*** LOGS ****/							
							
							$resultupiei = $mysqli->query($queryupiei);
							$affected = mysqli_affected_rows();//foreach ($row_info['idInternaute'] as $key=>$idInternaute ) 
							//echo $queryupiei;
							$email_organisateur = recup_mail_organisateur_epreuve($row_info['idEpreuve']);
							
							$dossard=0;
							//if ($dossard_equipe=='non') $dossard = numerotation($row_info['idParcours'],$row_info['idEpreuve'],$idInscriptionEpreuveInternaute);
							
							$dossard = numerotation($row_info['idParcours'],$row_info['idEpreuve'],$idInscriptionEpreuveInternaute,$id_internaute_inscription_referent);
							
							if ($row_info['place_promo'] ==1) {
								maj_tarif_reduc_place($row_info['idEpreuveParcoursTarif'],'update',$idInscriptionEpreuveInternaute);
							}
							
							if ($row_info['code_promo'] != 'Aucun') {
						
								//mise_a_jour_code_promo ($row_info['idEpreuve'],$row_info['idParcours'],$row_info['code_promo'],$idInscriptionEpreuveInternaute);
								//mise_a_jour_code_promo ($row_info['idEpreuve'],$row_info['idParcours'],$row_info['code_promo'],$row_info['valeur_code_promo'],$row_info['idEpreuveParcoursTarifPromo'], $idInscriptionEpreuveInternaute);
							}
		
							//mail à l'organisateur
							if (!empty($email_organisateur)) {
			
								$row_info['numerotation'] = $dossard;
								$row_info['type_mail'] = 'organisateur';			
								$donnees[0]=$row_info;
								$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);					
							}
							$montant_total_coureurs += $montant_total;
						}
					
					//envoi email au référent
					
					$donnees = array();
					$donnees_ref = array();
					/*
					$query_tmp = "SELECT cout,idInternaute,participation,frais_cb FROM r_insc_internaute_temp ";
					$query_tmp.= " WHERE idInscriptionEpreuveInternaute = ".$id_internaute_inscription_referent;
					$result_tmp = $mysqli->query($query_tmp);
					$row_tmp=mysqli_fetch_row($result_tmp);
					*/
					$internautes_equipe_all[] = $id_internaute_inscription_referent;
					$row_info = info_internaute_send_mail_test ($id_internaute_inscription_referent,0,'CB');
					//print_r($row_info);
					$row_info['referents'] = $row_tmp[1] ;
					if (empty($row_info['valeur_code_promo'])) $row_info['valeur_code_promo'] = 0; 
					
					if ($cpt==$nb_equipe) $row_info['frais_cb'] = extract_champ_b_paiements('frais_cb',$reference);
					
					$frais_cb = $row_info['frais_cb'];
					
					//echo "payeur : ".$payeur;
					if ($payeur=='coureur') {
					
						//echo $row_info['cout'];
						$montant_total = ($row_info['cout'] + $row_info['participation'] + $frais_cb + $row_info['Prix_OptionPlus'] + $row_info['assurance']) - $row_info['valeur_code_promo'];
						$frais_cb_referent = $frais_cb ;
						
					}
					else
					{
						$montant_total = ($row_info['cout'] + $row_info['participation'] + $row_info['Prix_OptionPlus'] + $row_info['assurance'])- $row_info['valeur_code_promo'];
						$frais_cb_referent = ($frais_cb*($cpt_internautes+1));
						
					}
					//echo "MONTANT TOTAL : ".$montant_total;

					$id_tmp =  check_internaute_existant_v3 ($row_info['nomInternaute'],$row_info['prenomInternaute'],$row_info['naissanceInternaute'],$row_info['sexeInternaute']);
							//echo "id_tmp : --".$id_tmp."---"; 
					if (empty($id_tmp)) $row_info['ref']=extract_champ_internaute('passInternaute',$row_info['idInternaute']);
							
					$row_info['nomReferent_paiement']=extract_champ_internaute('nomInternaute',$row_info['InscritParidInternaute']);
					$row_info['prenomReferent_paiement']=extract_champ_internaute('prenomInternaute',$row_info['InscritParidInternaute']);	
					
					//echo "frais_cb_referent : ".$frais_cb_referent;
					$row_info['montant_total_coureurs'] = $montant_total_coureurs;

					//echo "CPT : ".$cpt." - ".$nb_equipe;
					if ($cpt<$nb_equipe) $patronymes_inscrits_ref .= "<br/>".'<i>'.$row_info['nomParcours'].'</i> - '.$row_info['nomInternaute']." ".$row_info['prenomInternaute']." [ ".$montant_total." € ]";
					
					if ($cpt==$nb_equipe && $row_info['idInternaute'] == $id_referant) 
					{ 
						//Echo "JE SUIS ICI";
						$row_info['patronymes_inscrits_all'] = $patronymes_inscrits_ref;
						$row_info['paiement_montant'] = extract_champ_b_paiements('montant',$reference);
						$cestlereferant = 1;
						
					}
					
					if ($nb_equipe > 1) {
						$row_info['paiement_montant'] = $montant_total-$frais_cb ;
						//***$patronymes_inscrits_ref .= "<br/>".'<i>'.$row_info['nomParcours'].'</i> - '.$row_info['nomInternaute']." ".$row_info['prenomInternaute']." [ ".$row_info['paiement_montant']." € ]";
						$row_info['frais_cb'] = $frais_cb_referent = $frais_cb = 0;
					}

					
					
					$row_info['patronymes_inscrits'] = $patronymes_inscrits;
					
					//$row_info['idInternaute']
					if (!empty($row_info['idOptionPlus'])) $row_info['nomOptionPlus'] = extract_options_plus('label',$row_info['idOptionPlus']);
					//echo $row_info['idOptionPlus'];
					//print_r($option_plus);
					if ($cestlereferant == 1)
					{
						//ECHO "referant";
						array_push($donnees_ref,$row_info);
						//print_r($donnees_ref);						
						
						
					}
					else
					{
						//ECHO "AUTRE";
						array_push($donnees,$row_info);
						//print_r($donnees);
					
						$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
						$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
					}
	
					
					$queryupiei=" UPDATE r_inscriptionepreuveinternaute AS iei
					SET iei.paiement_type='TEST',iei.paiement_date=NOW(), iei.paiement_montant = ".$montant_total.", iei.frais_cheque = 0, iei.frais_cb= ".$frais_cb."
					WHERE idInscriptionEpreuveInternaute = ".$id_internaute_inscription_referent;
					$queryupiei .= " AND iei.paiement_type in('ATTENTE','ATTENTE CHQ')";
					$queryupiei .= " AND iei.paiement_date IS NULL";
					$resultupiei = $mysqli->query($queryupiei);
					$affected = mysqli_affected_rows();//foreach ($row_info['idInternaute'] as $key=>$idInternaute ) 
					
							/*** LOGS ****/
							fputs($fp," : if (simul==1) UPDATE r_inscriptionepreuveinternaute : ".$queryupiei."\n");
							/*** LOGS ****/
							
					//print_r($idInscriptionEpreuveInternautes);
					$queryupiei=" UPDATE r_insc_internautereferent
					SET paiement_type='TEST', paiement_date=NOW()  
					WHERE idInternauteReferent = ".$row['idInternauteReferent'];
					$resultupiei = $mysqli->query($queryupiei);
					$affected = mysqli_affected_rows();
					//echo $queryupiei;

							/*** LOGS ****/
							fputs($fp," : if (simul==1) UPDATE r_insc_internautereferent : ".$queryupiei."\n");
							/*** LOGS ****/	
					
					//$dossard = numerotation($row_info['idParcours'],$row_info['idEpreuve'],$id_internaute_inscription_referent);
					
					if ($row_info['place_promo'] ==1) {
						maj_tarif_reduc_place($row_info['idEpreuveParcoursTarif'],'update',$idInscriptionEpreuveInternaute);
					}
					
					if ($row_info['code_promo'] != 'Aucun') {
					
						//mise_a_jour_code_promo ($row_info['idEpreuve'],$row_info['idParcours'],$row_info['code_promo'],$id_internaute_inscription_referent);
						mise_a_jour_code_promo ($row_info['idEpreuve'],$row_info['idParcours'],$row_info['code_promo'],$row_info['valeur_code_promo'],$row_info['idEpreuveParcoursTarifPromo'], $id_internaute_inscription_referent);
					}
					
						//mail à l'organisateur
						if (!empty($email_organisateur)) {
		
							$row_info['numerotation'] = $dossard;
							$row_info['type_mail'] = 'organisateur';			
							$donnees[0]=$row_info;
							$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);	
						}
				$cpt++;
				}
					
					
					//TRAITMEENT DES COMPTES SEULS
					//***$row_info=array();
					$donnees=array();
					//print_r($internautes_equipe_all);
					$excludes_riei = implode(',',$internautes_equipe_all);
					
					$query = "SELECT montant_inscription, idInscriptionEpreuveInternaute, idInternaute, idEpreuve FROM r_inscriptionepreuveinternaute
					  WHERE idEpreuve = ".$id_epreuve." AND id_unique_paiement = '".addslashes($idpaiementunique)."' AND idInscriptionEpreuveInternaute NOT IN (".$excludes_riei.") AND paiement_type in('ATTENTE','SUPPRESSION','ATTENTE CHQ') AND paiement_date IS NULL";					
					 $result = $mysqli->query($query);
							
							/*** LOGS ****/
							fputs($fp," : if (simul==1) SELECT montant_inscription, : ".$query."\n");
							/*** LOGS ****/				
				
				while($row=mysqli_fetch_array($result))
				{
					$donnees = array();
					$query2 = "UPDATE r_inscriptionepreuveinternaute SET
							paiement_date = NOW(),
							paiement_montant = '".$row['montant_inscription']."' ,
							paiement_type = 'CB'
							WHERE idInscriptionEpreuveInternaute = ".$row['idInscriptionEpreuveInternaute']." AND paiement_type in('ATTENTE','SUPPRESSION','ATTENTE CHQ') AND paiement_date IS NULL";
					$result = $mysqli->query($query2);
					$affected = mysqli_affected_rows();				
							
							/*** LOGS ****/
							fputs($fp," : if (simul==1) UPDATE r_inscriptionepreuveinternaute : ".$query2."\n");
							/*** LOGS ****/						
					
					if ( extract_champ_epreuve('payeur',$id_epreuve)== 'organisateur' ) $paiement_frais_cb = 0; else $paiement_frais_cb = 1;


					$row_info = info_internaute_send_mail_test ($row['idInscriptionEpreuveInternaute'],$row['montant_inscription'],'CB');
					//print_r($row_info);
					$row_info['frais_cb']=0;
					$row_info['paiement_montant'] = $row['montant_inscription'];
					//temp
					//***$row_info['emailInternaute']= 'jf@chauveau.nom.fr';
					//temp
					
					
					//OPTION PLUS			
					if ($row_info['idOptionPlus'] != '')
					{
						$query_option_plus = "SELECT label FROM r_options_plus WHERE idOptionPlus = ".$row_info['idOptionPlus'];
						$result_option_plus = $mysqli->query($query_option_plus);
						$row_option_plus= mysqli_fetch_row($result_option_plus);
						$row_info['nom_option_plus'] = $row_option_plus[0];
						$row_info['prix_option_plus'] = $row_info['Prix_OptionPlus'];
							/*** LOGS ****/
							fputs($fp," : if (simul==1) SELECT label : ".$query_option_plus."\n");
							/*** LOGS ****/	
					}
					//OPTION PLUS
					//echo "id_referant".$id_referant;
					if ($row_info['idInternaute'] == $id_referant) 
					{ 
						$row_info['patronymes_inscrits_all'] = $patronymes_inscrits_ref;
						$row_info['paiement_montant'] = extract_champ_b_paiements('montant',$reference);
						$row_info['frais_cb'] = extract_champ_b_paiements('frais_cb',$reference);
					}
					else
					{
						$patronymes_inscrits_ref .= "<br/>".'<i>'.$row_info['nomParcours'].'</i> - '.$row_info['nomInternaute']." ".$row_info['prenomInternaute']." [ ".$row['montant_inscription']." € ]";
					}
					
					if (!empty($row_info['idOptionPlus'])) $row_info['nomOptionPlus'] = extract_options_plus('label',$row_info['idOptionPlus']);
					
					$row_info['nomReferent_paiement']=extract_champ_internaute('nomInternaute',$row_info['InscritParidInternaute']);
					$row_info['prenomReferent_paiement']=extract_champ_internaute('prenomInternaute',$row_info['InscritParidInternaute']);	
					
					$id_tmp =  check_internaute_existant_v3 ($row_info['nomInternaute'],$row_info['prenomInternaute'],$row_info['naissanceInternaute'],$row_info['sexeInternaute']);
							//echo "id_tmp : --".$id_tmp."---"; 
					if (empty($id_tmp)) $row_info['ref']=extract_champ_internaute('passInternaute',$row_info['idInternaute']);
					
					//print_r($row_info);
					array_push($donnees,$row_info);
					
		

					$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
					//$corps = "<p><a href='http://ats-sport.com/inscriptions.php?id_epreuve=".$row_info['idEpreuve']."&id_parcours=".$row_info['idParcours']."&id=".$row_info['idSession']."&date_inscription=".$row_info['dateInscription']."&action=update' target='_blank'> Cliquez ICI pour la mise à jour de votre inscription</a></p>";
					//$corps .="<p>Votre code de sécurité pour pouvoir effectuer des modifications : <b>".$row_info['codeSecurite']."</p>";	
					
					$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
					
					/*** if($affected == 0) send_mail_etat_paiement('individuel',$query2,$query,addslashes($reference)); ****/
					$dossard_iei = extract_champ_id_epreuve_internaute('dossard',$row_info['idInscriptionEpreuveInternaute']);
					if ($dossard_iei == 0) $dossard = numerotation($row_info['idParcours'],$row_info['idEpreuve'],$row_info['idInscriptionEpreuveInternaute']); else $dossard = $dossard_iei;
					
					if ($row_info['place_promo'] ==1) {
						maj_tarif_reduc_place($row_info['idEpreuveParcoursTarif'],'update',$row_info['idInscriptionEpreuveInternaute']);
					}
					
					if ($row_info['code_promo'] != 'Aucun') {
				
						//mise_a_jour_code_promo ($row_info['idEpreuve'],$row_info['idParcours'],$row_info['code_promo'],$row_info['idInscriptionEpreuveInternaute']);
						mise_a_jour_code_promo ($row_info['idEpreuve'],$row_info['idParcours'],$row_info['code_promo'],$row_info['valeur_code_promo'],$row_info['idEpreuveParcoursTarifPromo'], $row_info['idInscriptionEpreuveInternaute']);
					}
					//echo $query = "UPDATE b_paiements SET montant = ".($_GET['Montant']/100).", paiement_date = NOW(), statut='VALIDEE' WHERE reference = '".addslashes($reference)."'";
					//$mysqli->query($query);
					
					$email_organisateur = recup_mail_organisateur_epreuve($row_info['idEpreuve']);
					
					if (!empty($email_organisateur)) {
	
						$row_info['numerotation'] = $dossard;
						$row_info['type_mail'] = 'organisateur';			
						$donnees[0]=$row_info;
						$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);	
					}					
					
					
					
					
					
					
				}
					if ($cestlereferant == 1)
					{
						$donnees_ref[0]['frais_cb'] = extract_champ_b_paiements('frais_cb',$reference);
						
						if ($payeur=='coureur') {
						
							$donnees_ref[0]['montant_total_coureurs'] = ($_POST['Montant']/100) - (($donnees_ref[0]['tarif'] + $donnees_ref[0]['participation'] + $donnees_ref[0]['frais_cb'] + $donnees_ref[0]['Prix_OptionPlus'] + $donnees_ref[0]['assurance']) - $donnees_ref[0]['valeur_code_promo']);
							
							
						}
						else
						{
							$donnees_ref[0]['montant_total_coureurs'] = ($_POST['Montant']/100) - (($donnees_ref[0]['tarif'] + $donnees_ref[0]['participation'] + $donnees_ref[0]['Prix_OptionPlus'] + $donnees_ref[0]['assurance']) - $donnees_ref[0]['valeur_code_promo']);
							
						}						
						$donnees_ref[0]['paiement_montant'] = ($_POST['Montant']/100);
						//mail si capitaine équipe est le référent payant
						//print_r($donnees_ref);
						$donnees_ref[0]['patronymes_inscrits_all'] = $patronymes_inscrits_ref;
						$sujet = "Ats Sport - Inscription à l'épreuve ".$donnees_ref[0]['nomEpreuve']." - ".$donnees_ref[0]['nomParcours'];
						$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$donnees_ref[0]['emailInternaute'], $donnees_ref[0]['nomInternaute']." ".$donnees_ref[0]['prenomInternaute'], $sujet,$donnees_ref);		
					}
					
					
					$query_int_ref = " SELECT rir.idInternauteReferent FROM r_internautereferent as rir ";
					$query_int_ref .= " INNER JOIN r_insc_internautereferent as riir ON rir.idInternauteReferent = riir.idInternauteReferent ";
					$query_int_ref .= " WHERE id_unique_paiement = 'R-".addslashes($idpaiementunique)."'";
					//$query_int_ref .= " AND rir.idInternautes LIKE CONCAT('%',".$idInternaute.",'%') ";
					//$query_int_ref .= " AND riir.paiement_type NOT IN ('SUPPRESSION','REMBOURSE') ";
					$query_int_ref .= " AND riir.paiement_date is NULL ";
					$query_int_ref .= " AND rir.idEpreuve =".$id_epreuve;
					$result = $mysqli->query($query_int_ref);
					$row=mysqli_fetch_row($result);

							/*** LOGS ****/
							fputs($fp," : if (simul==1) SELECT rir.idInternauteReferent : ".$query_int_ref."\n");
							/*** LOGS ****/	
							
					$queryupiei=" UPDATE r_insc_internautereferent
					SET paiement_type='TEST', paiement_date=NOW()  
					WHERE idInternauteReferent = ".$row[0];
					$result = $mysqli->query($queryupiei);
					
							/*** LOGS ****/
							fputs($fp," : if (simul==1) UPDATE r_insc_internautereferent : ".$queryupiei."\n");
							/*** LOGS ****/	
							
					$querybp="UPDATE b_paiements SET montant = ".($_POST['Montant']/100).", paiement_type = 'TEST', paiement_date = NOW(), statut = 'VALIDEE'
					WHERE reference = '".addslashes($reference)."' AND paiement_date IS NULL";
					$result_bp = $mysqli->query($querybp);
							/*** LOGS ****/
							fputs($fp," : if (simul==1) UPDATE b_paiements : ".$querybp."\n");
							/*** LOGS ****/
			}
		} 		
		else 
		{
			$donnees = array();
			//echo "paiement classique";
			// Paiement classique
			
			// Payment has been accepeted on the test server
			$query = "SELECT idInscriptionEpreuveInternaute, idInternaute, idEpreuve FROM r_inscriptionepreuveinternaute
					  WHERE idInscriptionEpreuveInternaute = ".addslashes($reference)." AND paiement_type in('ATTENTE','SUPPRESSION','ATTENTE CHQ') AND paiement_date IS NULL";
			/*		  
			$query = "SELECT riit.idInscriptionEpreuveInternaute, riit.idInternaute, riit.idEpreuve,riit.frais_cb FROM r_insc_internaute_temp as riit
			INNER JOIN r_inscriptionepreuveinternaute as riei ON riit.idInscriptionEpreuveInternaute = riei.idInscriptionEpreuveInternaute
					  WHERE riit.idInscriptionEpreuveInternaute = ".addslashes($reference)." AND riei.paiement_type in('ATTENTE','SUPPRESSION','ATTENTE CHQ') AND riei.paiement_date IS NULL";
			*/
			$result = $mysqli->query($query);
			
			if ( extract_champ_epreuve('payeur',$id_epreuve)== 'organisateur' ) $paiement_frais_cb = 0; else $paiement_frais_cb = 1;
			
			//echo "count".count($result);
			// Si reference absente on refuse
			if(count($result) != 1)
			{
				header('Content-Type: text/plain');
				echo "Invalid reference";
				exit;
			}
			else
			{
				/*
				while($row=mysqli_fetch_array($result))
				{
					$idInternaute = $row['idInternaute'];
					$idEpreuve = $row['idEpreuve'];
					$frais_cb = $row['frais_cb'];
				}
				*/
				$frais_cb = $row['frais_cb']  = extract_champ_b_paiements('frais_cb',$reference);
				
				$query2 = "UPDATE r_inscriptionepreuveinternaute SET
							paiement_date = NOW(),
							paiement_montant = '".addslashes(($_POST['Montant']/100))."' ,
							paiement_type = 'TEST'
							WHERE idInscriptionEpreuveInternaute = '".addslashes($reference)."' AND paiement_type in('ATTENTE','SUPPRESSION','ATTENTE CHQ') AND paiement_date IS NULL";
				$result = $mysqli->query($query2);
				$affected = mysqli_affected_rows();
							/*** LOGS ****/
							fputs($fp," : if (simul==1) UPDATE r_inscriptionepreuveinternaute : ".$query2."\n");
							/*** LOGS ****/

				if ( extract_champ_epreuve('payeur',$id_epreuve)== 'organisateur' ) $paiement_frais_cb = 0; else $paiement_frais_cb = 1;


					$row_info = info_internaute_send_mail_test ($reference,(($_POST['Montant']/100)),'CB');
					//print_r($row_info);
					if ($paiement_frais_cb==1) $row_info['frais_cb']= extract_champ_b_paiements('frais_cb',$reference); else $row_info['frais_cb']=0;
					//echo "XXXXXXXXXXXXX".$row_info['frais_cb'];
					$row_info['paiement_montant'] = (($_POST['Montant']/100));
					//temp
					//***$row_info['emailInternaute']= 'jf@chauveau.nom.fr';
					//temp
					
					
					
					//OPTION PLUS			
					/*
					if ($row_info['idOptionPlus'] != '')
					{
						$query_option_plus = "SELECT label FROM r_options_plus WHERE idOptionPlus = ".$row_info['idOptionPlus'];
						$result_option_plus = $mysqli->query($query_option_plus);
						$row_option_plus= mysqli_fetch_row($result_option_plus);
						$row_info['nom_option_plus'] = $row_option_plus[0];
						$row_info['prix_option_plus'] = $row_info['Prix_OptionPlus'];
					}
					*/
					if (!empty($row_info['idOptionPlus'])) $row_info['nomOptionPlus'] = extract_options_plus('label',$row_info['idOptionPlus']);
					//OPTION PLUS
					
					if (!empty($row_info['InscritParidInternaute']))
					{
						$row_info['nomReferent_paiement']=extract_champ_internaute('nomInternaute',$row_info['InscritParidInternaute']);
						$row_info['prenomReferent_paiement']=extract_champ_internaute('prenomInternaute',$row_info['InscritParidInternaute']);
						$id_tmp =  check_internaute_existant_v3 ($row_info['nomInternaute'],$row_info['prenomInternaute'],$row_info['naissanceInternaute'],$row_info['sexeInternaute']);
							//echo "id_tmp : --".$id_tmp."---"; 
						if (empty($id_tmp)) $row_info['ref']=extract_champ_internaute('passInternaute',$row_info['idInternaute']);						
						
					}
					
					//print_r($row_info);
					array_push($donnees,$row_info);
					
		

					$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
					//$corps = "<p><a href='http://ats-sport.com/inscriptions.php?id_epreuve=".$row_info['idEpreuve']."&id_parcours=".$row_info['idParcours']."&id=".$row_info['idSession']."&date_inscription=".$row_info['dateInscription']."&action=update' target='_blank'> Cliquez ICI pour la mise à jour de votre inscription</a></p>";
					//$corps .="<p>Votre code de sécurité pour pouvoir effectuer des modifications : <b>".$row_info['codeSecurite']."</p>";	
					
					//***$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
					//echo "toto";
					
					/*
					$query_del =  "DELETE FROM r_insc_internaute_temp ";
					$query_del .= "WHERE idInscriptionEpreuveInternaute = ".$row_info['idInscriptionEpreuveInternaute']." ";
					$query_del .= "AND idInternaute = ".$row_info['idInternaute']." ";
					$query_del .= "AND idEpreuve = ".$row_info['idEpreuve'];
					//****$result_del = $mysqli->query($query_del);
					*/
					
					/*** if($affected == 0) send_mail_etat_paiement('individuel',$query2,$query,addslashes($reference)); ****/
					$dossard_iei = extract_champ_id_epreuve_internaute('dossard',$row_info['idInscriptionEpreuveInternaute']);
					if ($dossard_iei == 0) $dossard = numerotation($row_info['idParcours'],$row_info['idEpreuve'],$row_info['idInscriptionEpreuveInternaute']); else $dossard = $dossard_iei;
					
					if ($row_info['place_promo'] ==1) {
						maj_tarif_reduc_place($row_info['idEpreuveParcoursTarif'],'update',$row_info['idInscriptionEpreuveInternaute']);
					}
					
					if ($row_info['code_promo'] != 'Aucun') {
				
						//mise_a_jour_code_promo ($row_info['idEpreuve'],$row_info['idParcours'],$row_info['code_promo'],$row_info['idInscriptionEpreuveInternaute']);
						mise_a_jour_code_promo ($row_info['idEpreuve'],$row_info['idParcours'],$row_info['code_promo'],$row_info['valeur_code_promo'],$row_info['idEpreuveParcoursTarifPromo'], $row_info['idInscriptionEpreuveInternaute']);
					}
					$query = "UPDATE b_paiements SET montant = ".($_POST['Montant']/100).", paiement_type = 'TEST', paiement_date = NOW(), statut='VALIDEE' WHERE reference = '".addslashes($reference)."'";
					$mysqli->query($query);
							
							/*** LOGS ****/
							fputs($fp," : if (simul==1) UPDATE b_paiements : ".$query."\n");
							/*** LOGS ****/					
					
					$email_organisateur = recup_mail_organisateur_epreuve($row_info['idEpreuve']);
					
					if (!empty($email_organisateur)) {
	
						$row_info['numerotation'] = $dossard;
						$row_info['type_mail'] = 'organisateur';			
						$donnees[0]=$row_info;
						$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);	
					}
					//$result = $mysqli->query($query);	


			}

		}






		
		
		
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
			
							/*** LOGS ****/
							fputs($fp," : if (type_paiement== 'AUTRE') INSERT INTO r_insc_assurance_annulation : ".$bdd_assu."\n");
							/*** LOGS ****/	
							
			$q1  = "UPDATE r_inscriptionepreuveinternaute SET assurance = ".$search_assure['montant']." ";
			$q1 .= "WHERE idInscriptionEpreuveInternaute = ".$row['idInscriptionEpreuveInternaute'];
			$mysqli->query($q1);					
							/*** LOGS ****/
							fputs($fp," : if (type_paiement== 'AUTRE') UPDATE r_inscriptionepreuveinternaute : ".$q1."\n");
							/*** LOGS ****/				
			$q1  = "UPDATE r_insc_internaute_temp SET assurance = ".$search_assure['montant']." ";
			$q1 .= "WHERE idInscriptionEpreuveInternaute = ".$row['idInscriptionEpreuveInternaute'];
			$mysqli->query($q1);
							/*** LOGS ****/
							fputs($fp," : if (type_paiement== 'AUTRE') UPDATE r_insc_internaute_temp : ".$q1."\n");
							/*** LOGS ****/	
			$frais_assurance +=$search_assure['montant'];			
		}			
		
		
		
		//CODE PROMO
		
		$q1  = "UPDATE r_inscriptionepreuveinternaute SET paiement_type = 'AUTRE', paiement_date = NOW(), montant_inscription = ".(($row['cout'] + $row['participation'])-$row['valeur_code_promo']).", paiement_montant = ".(($row['cout'] + $row['participation'])-$row['valeur_code_promo']);
		$q1 .= " WHERE idInscriptionEpreuveInternaute = ".$row['idInscriptionEpreuveInternaute'];
		$mysqli->query($q1);
							/*** LOGS ****/
							fputs($fp," : if (type_paiement== 'AUTRE') UPDATE r_inscriptionepreuveinternaute : ".$q1."\n");
							/*** LOGS ****/	
		/*** LOGS ****/
		fputs($fp," : if (type_paiement== 'AUTRE') UPDATE r_inscriptionepreuveinternaute : ".$q1."\n");
		/*** LOGS ****/
		
		if ($nb_inscrit > 1 && $row['idInscriptionEpreuveInternaute'] != $id_internauteInscription_ref ) {
			
			$donnees = array();
		
			$row_info = info_internaute_send_mail ($row['idInscriptionEpreuveInternaute'],$row['cout'],'GRATUIT');
			$row_info['referent'] = $id_internaute_ref;
			
			array_push($donnees,$row_info);
		
			$sujet = "Ats Sport - Inscription à l'epreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
			$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
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
				$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
			}
		}
		elseif ($nb_inscrit > 1 && $row['idInscriptionEpreuveInternaute'] == $id_internauteInscription_ref ) 
		{
			
			$donnees = array();
		
			$row_info = info_internaute_send_mail ($row['idInscriptionEpreuveInternaute'],$row['cout'],'GRATUIT');
			$row_info['referents'] = $id_internaute_ref ;
			
			array_push($donnees,$row_info);
		
			$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
			$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
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
							fputs($fp," : if (type_paiement== 'AUTRE') UPDATE r_insc_internautereferent : ".$queryupiei."\n");
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
				$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
			}
		}
		else
		{
			$donnees = array();
			
			
			$row_info = info_internaute_send_mail ($row['idInscriptionEpreuveInternaute'],$row['cout'],'GRATUIT');
			array_push($donnees,$row_info);
			//print_r($donnees);
			$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
			
			$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
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
				$mail_send = send_mail ('webmaster@ats-sport.com','ATS Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);
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

if ($insc_gratuite==0)
{
	

				
				if ($_SESSION['bp_paiement_autre_multiple']==1)
				{
					$queryupiei=" UPDATE r_insc_internautereferent
					SET frais_cb = 0 
					WHERE idInternauteReferent IN (".implode(",", $_SESSION['equipes']).")" ;
					//****$resultupiei = $mysqli->query($queryupiei);					
							
							/*** LOGS ****/
							fputs($fp," : if (insc_gratuite == 0 && _SESSION['bp_paiement_autre_multiple'] ) NON APPLIQUE DANS LA BDD: UPDATE r_insc_internautereferent : ".$queryupiei."\n");
							/*** LOGS ****/			
					
					if ($_SESSION['info_caddie']>1) {
						
						//print_r($_SESSION['rieis']);
						//print_r($_SESSION['idInternautes']);
						//print_r($_SESSION['equipes']);
						//$_SESSION['rieis'][0] = 598690;
						//$_SESSION['idInternautes'][0] = 488073;
						
						$id_internaute_referent_equipe_solo = $_SESSION['rieis'][0];
						$id_internaute_inscription_referent = $_SESSION['idInternautes'][0];
						
						unset($_SESSION['rieis'][0]);
						unset($_SESSION['idInternautes'][0]);
						
						$all_id_internaute = implode('|',$_SESSION['idInternautes']);
						$all_id_inscription_internaute = implode('|',$_SESSION['rieis']);
						
						
						$query_referent = "INSERT INTO r_internautereferent ";
						$query_referent .= "(idInternauteref, idInternauteInscriptionref, idInternautes, idInscriptionEpreuveInternautes,relais,idEpreuve,NomEquipe,NomRespEquipe,TelRespEquipe, InscritParidInternaute, id_unique_paiement) VALUES ";
						$query_referent .="(".$id_internaute_inscription_referent.", ".$id_internaute_referent_equipe_solo.", '".$all_id_internaute."','".$all_id_inscription_internaute."','non',".$_SESSION['idEpreuve'].", 'Aucune', UPPER('".$_SESSION['NomRespEquipe']."'), '".$_SESSION['TelRespEquipe']."',".$_SESSION['InscritParidInternaute'].",'R-".$_SESSION['id_unique_paiement']."' )";					
						$result_query = $mysqli->query($query_referent);	
						$_SESSION['id_ref_temp'] = $id_referent = $mysqli->insert_id;

							/*** LOGS ****/
							fputs($fp," : if (insc_gratuite == 0 && _SESSION['bp_paiement_autre_multiple'] && _SESSION['info_caddie']>1 ) INSERT INTO r_internautereferent : ".$query_referent."\n");
							/*** LOGS ****/		
							
						$query_referent = "INSERT INTO r_insc_internautereferent ";
						$query_referent .= "(idInternauteReferent, idEpreuve, idEpreuveParcours, frais_cb, montant) VALUES ";
						$query_referent .="(".$id_referent.",".$_SESSION['idEpreuve'].",0,".$_SESSION['somme_frais_cb'].",".$_SESSION['somme_total'].")";
						$result_query = $mysqli->query($query_referent);
						
							/*** LOGS ****/
							fputs($fp," : if (insc_gratuite == 0 && _SESSION['bp_paiement_autre_multiple'] && _SESSION['info_caddie']>1 ) INSERT INTO r_insc_internautereferent : ".$query_referent."\n");
							/*** LOGS ****/	
					}
					
				}
				
				if ($_SESSION['nb_inscrit']	> 1 ) {
					
					$query_referent = "INSERT INTO r_internautereferent ";
					$query_referent .= "(idInternauteref, idInternauteInscriptionref, idInternautes, idInscriptionEpreuveInternautes,relais,idEpreuve,NomEquipe,NomRespEquipe,TelRespEquipe, InscritParidInternaute, id_unique_paiement) VALUES ";
					$query_referent .="(".$_SESSION['id_internaute_referent_equipe_solo'].", ".$_SESSION['id_internaute_inscription_referent'].", '".$_SESSION['all_id_internaute']."','".$_SESSION['all_id_inscription_internaute']."','non',".$_SESSION['idEpreuve'].", 'Aucune', UPPER('".$_SESSION['NomRespEquipe']."'), '".$_SESSION['TelRespEquipe']."',".$_SESSION['InscritParidInternaute'].",'".$_SESSION['id_unique_paiement']."' )";
					$result_query = $mysqli->query($query_referent);
							/*** LOGS ****/
							fputs($fp," : if (insc_gratuite == 0 && _SESSION['nb_inscrit']	> 1 ) INSERT INTO r_internautereferent : ".$query_referent."\n");
							/*** LOGS ****/
					
					//$query_referent;
					$_SESSION['id_ref_temp'] = $id_referent = $mysqli->insert_id;
					//***$_SESSION['id_ref_bp'] = 'M-'.$_SESSION['id_ref_temp'];
					//if (!empty($equipe)) $query_temp .= "'".addslashes($equipe)."',"; else $query_temp .= "'Aucune',";
					//f($payeur == 'coureur') $prix_total_multi = ($_POST['prix_total'] + $frais_cb_total); else $prix_total_multi = $_POST['prix_total'];
						
					$frais_cb_tmp = 0;
					if ($_SESSION['bp_paiement_autre_multiple'] == 0 ) 
					{
						$frais_cb_tmp = $_SESSION['somme_frais_cb'];
						
					}												
					
					if (isset($_SESSION['idEpreuvePersoPre']) && $_SESSION['paiement_indiv'] =='non')
					{
		
						$query_referent = "INSERT INTO r_insc_internautereferent ";
						$query_referent .= "(idInternauteReferent, idEpreuve, idEpreuveParcours, frais_cb, montant) VALUES ";
						$query_referent .="(".$id_referent.",".$_SESSION['idEpreuve'].",'".$_SESSION['all_id_parcours']."',".$frais_cb_tmp.",".$_SESSION['somme_total'].")";
						//echo "#2 : ".$query_referent;
						$result_query = $mysqli->query($query_referent);
						
							/*** LOGS ****/
							fputs($fp," : if (insc_gratuite == 0 && _SESSION['nb_inscrit']	> 1 && isset(_SESSION['idEpreuvePersoPre']) && _SESSION['paiement_indiv'] =='non') INSERT INTO r_insc_internautereferent : ".$query_referent."\n");
							/*** LOGS ****/						
						
					}
					else
					{
						$query_referent = "INSERT INTO r_insc_internautereferent ";
						$query_referent .= "(idInternauteReferent, idEpreuve, idEpreuveParcours, frais_cb, montant) VALUES ";
						$query_referent .="(".$id_referent.",".$_SESSION['idEpreuve'].",'".$_SESSION['all_id_parcours']."',".$frais_cb_tmp.",".$_SESSION['somme_total'].")";
						//echo "#3 : ".$query_referent;
						$result_query = $mysqli->query($query_referent);
							
							/*** LOGS ****/
							fputs($fp," : if (insc_gratuite == 0 && _SESSION['nb_inscrit']	> 1 && isset(_SESSION['idEpreuvePersoPre']) && _SESSION['paiement_indiv'] =='non' | ELSE) INSERT INTO r_insc_internautereferent : ".$query_referent."\n");
							/*** LOGS ****/	
						
					}
				}
				
				$bpaiements  = "INSERT INTO b_paiements (reference, montant, frais_cb, date, id_referant, id_epreuve, statut, nomInternaute, prenomInternaute, emailInternaute) ";
				if (empty($_SESSION['id_internaute_referent'])) 
				{ 
					
					if (!empty($_SESSION['InscritParidInternaute']) || $_SESSION['InscritParidInternaute']!='NULL')
					{
						$id_bp_ref = $_SESSION['InscritParidInternaute'];
					}
					else
					{
						 $id_bp_ref = 999999;
					}
				}
				else
				{
					$id_bp_ref = $_SESSION['id_internaute_referent']; 
					
				}
				
				$bpaiements .= "VALUE ('".$_SESSION['id_ref_bp']."', ".round($_SESSION['somme_total'], 2).", ".round($_SESSION['somme_frais_cb'], 2).", NOW(), ".$id_bp_ref.", ".$_SESSION['idEpreuve'].", 'ATTENTE','".addslashes_form_to_sql($_SESSION['nom_Internaute'])."','".addslashes_form_to_sql($_SESSION['prenom_Internaute'])."','".$_SESSION['email_internaute']."')";  
				$result = $mysqli->query($bpaiements);
				//echo $bpaiements;
							/*** LOGS ****/
							fputs($fp," : if (insc_gratuite == 0 && _SESSION['nb_inscrit']	> 1 vePersoPre']) ) SESSION id_internaute_referent = *".$_SESSION['id_internaute_referent']."* SESSION InscritParidInternaute = *".$_SESSION['InscritParidInternaute']."*  - id_bp_ref = *".$id_bp_ref."* - INSERT INTO b_paiements : ".$bpaiements."\n");
							/*** LOGS ****/					
}

	
							/*** LOGS ****/
							fputs($fp," : SESSION EFFACEE : PANIER : ".$_SESSION['panier']." - REIS : ".$_SESSION['rieis']." - NOM/PRENOM : ".$_SESSION['nom_Internaute']." / ". $_SESSION['prenom_Internaute']." \n");
							/*** LOGS ****/	
	unset($_SESSION['panier'],$_SESSION['tarifs'],$_SESSION['idEpreuvePersoPre'],$_SESSION['paiement_indiv'],$_SESSION['groupe'],$_SESSION['somme_frais_cb'],$_SESSION['rieis']);
	unset($_SESSION['option_plus'],$_SESSION['idEpreuve'],$_SESSION['nb_relais'],$_SESSION['new_user'],$_SESSION['info_caddie'],$_SESSION['id_ref_temp'],$_SESSION['unique_id_session']);
	unset($_SESSION['id_internaute_referent'],$_SESSION['nom_Internaute'],$_SESSION['prenom_Internaute']);
	unset($_SESSION['bp_paiement_autre_multiple'],$_SESSION['autre_personne'],$_SESSION['equipes'],$_SESSION['equipes_rieis'],$_SESSION['equipe_participation'],$_SESSION['equipe_tarif_et_option']);
	unset($_SESSION['equipes_Idref'],$_SESSION['nb_inscrit'],$_SESSION['idInternaute'],$_SESSION['id_unique_paiement'],$_SESSION['nb_inscription_solo']);
	$_SESSION['idInternautes'] = $_SESSION['equipes'] = array();
	

	$json = array('etat' =>'OK');
	echo json_encode($json);
				

?>