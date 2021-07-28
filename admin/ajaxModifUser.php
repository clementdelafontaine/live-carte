<?php 
ini_set("display_errors", 0);
error_reporting(E_ERROR);
	require_once('functions.php');
	require_once('numerotation.php');
	require_once('slashes.php');
	session_start();
	require_once('connect_db.php');
	connect_db();
	global $mysqli;
	
	if (empty($_POST['idInscriptionEpreuveInternaute'])) {
		
		list($champ,$table,$id) = explode("--",$_POST['name']);
	}
	else
	{
		$id = $_POST['idInscriptionEpreuveInternaute'];
		$table = 'r_inscriptionepreuveinternaute';
		$champ ='info_cheque';
	}
	
	list($id_epreuve,$id_parcours) = explode("--",$_POST['pk']);
	$value = $_POST['value'];
	
	//echo $champ." ".$table." ".$id." ".$id_epreuve." ".$id_parcours." ".$value;
	
	if ($table == 'internaute') {
		
		$query = " UPDATE r_internaute SET ";
		$query .= "".$champ."='".addslashes_form_to_sql($value)."' ";
		$query .= "WHERE idInternaute=".$id.";";
		//echo $query;
		$result = $mysqli->query($query);
			$aff= array('comeback'=>'OK');
			echo json_encode($aff);
			exit();
	}
	/*elseif ($table == 'inscription') {
		
		$query = " UPDATE r_inscriptionepreuveinternaute SET ";
		$query .= "".$champ."='".addslashes_form_to_sql($value)."' ";
		$query .= "WHERE idInscriptionEpreuveInternaute=".$id.";";
		echo $query;
		//***$result = $mysqli->query($query);
			$aff= array('comeback'=>'OK');
			echo json_encode($aff);
			exit();
	}*/
	else
	{
		
		if ($champ == 'dossard') {
			
			$query_de = "SELECT dossardDeb,dossardFin FROM r_epreuveparcours ";
			$query_de .="WHERE idEpreuveParcours = ".$id_parcours." ";
			$result_de = $mysqli->query($query_de);
			$row_de= mysqli_fetch_row($result_de);
			if ($value < $row_de[0]) { 
				
				$aff= array('comeback'=>'KO','dossard'=>$value,'idInscriptionEpreuveInternaute'=>$id,'dossard_out'=>1);
				echo json_encode($aff);
				exit();
			}
			elseif ($value > $row_de[1]) {
				
				$aff= array('comeback'=>'KO','dossard'=>$value,'idInscriptionEpreuveInternaute'=>$id,'dossard_out'=>2);
				echo json_encode($aff);
				exit();	
			}
			
			$query_de = "SELECT idInscriptionEpreuveInternaute FROM r_inscriptionepreuveinternaute ";
			$query_de .="WHERE dossard = ".$value." ";
			$query_de .= "AND idEpreuve = ".$id_epreuve." ";
			$query_de .= "AND idEpreuveParcours = ".$id_parcours." ";
			$result_de = $mysqli->query($query_de);
			$row_de= mysqli_fetch_row($result_de);
			if (!empty($row_de[0])) {
				
				$dossard_propose = numerotation_no_update($id_parcours,$id_epreuve,$id);
				$aff= array('comeback'=>'KO','dossard'=>$value,'idInscriptionEpreuveInternaute'=>$id,'dossard_propose'=>$dossard_propose);
				echo json_encode($aff);
				exit();
			}
				$query = " UPDATE r_inscriptionepreuveinternaute SET ";
				$query .= "".$champ."='".addslashes_form_to_sql($value)."' ";
				$query .=", paiement_montant = 0 ";
				$query .= " WHERE idInscriptionEpreuveInternaute=".$id.";";
				//echo $query;
				$result = $mysqli->query($query);
		
		}
		
		if ($champ =='paiement_type') {
			
			$sql_plus ='';
			$date = 'NOW()';
			//echo $champ;
			
				
			if ($value=='SUPPRESSION') 
			{ 
					$date = 'NULL';
					$sql_plus = ', dossard = 0, frais_cb = 0 ';
					$dossard = 0;				
				
				$sql_plus .= ', paiement_date = '.$date.' ';
			
			
				$query = " UPDATE r_inscriptionepreuveinternaute SET ";
				$query .= "".$champ."='".addslashes_form_to_sql($value)."' ";
				$query .= $sql_plus;
				$query .=", paiement_montant = 0 ";
				$query .= " WHERE idInscriptionEpreuveInternaute=".$id.";";
				//echo $query;
				$result = $mysqli->query($query);
				//echo $id_parcours;
			
				$id_relais =  internaute_referent_internautes($id);
					
					if (!empty($id_relais['idInscriptionEpreuveInternautes'])) {
					
						$champs=explode("|",$id_relais['idInscriptionEpreuveInternautes']);	
						$first = TRUE;
						$aff_html_dossards = '';
							foreach ($champs as $key=>$idInscriptionEpreuveInternaute) {
		
									$q1  = "UPDATE r_inscriptionepreuveinternaute SET ".$champ."='".addslashes_form_to_sql($value)."', frais_cb=0, paiement_date = NULL,dossard = 0,paiement_montant = 0,paiement_date = NULL ";
									$q1 .= "WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
									//echo "inscrit seul: ".$q1;
									$mysqli->query($q1);
								
									if ($first==FALSE) $aff_html_dossards .='|';
									$aff_html_dossards .= '<i data-toggle="tooltip" data-placement="top" data-original-title="Dossard non attribué" class="text-danger fa fa-ban"></i>';
									$first=FALSE;
							}
								$queryupiei=" UPDATE r_insc_internautereferent
									SET paiement_type='".$value."', paiement_date = NULL
									WHERE idEpreuve = ".$id_epreuve."
									AND idInternauteReferent = ".$id_relais['idInternauteReferent'];
									$resultupiei = $mysqli->query($queryupiei);
					

					
					
					}
				$aff_html_dossard = '<i data-toggle="tooltip" data-placement="top" data-original-title="Dossard non attribué" class="text-danger fa fa-ban"></i>'; 
				$aff= array('comeback'=>'OK','dossard'=>$dossard,'idInscriptionEpreuveInternaute'=>$id,'html_dossard'=>$aff_html_dossard,'aff_html_dossards'=>$aff_html_dossards,'html_ref_internautes'=>$id_relais['idInscriptionEpreuveInternautes'],'paiement_type'=>$value);
				echo json_encode($aff);
				exit();	
			}
			
		
			if ($value=='ATTENTE' || $value=='ATTENTE CHQ') 
			{ 
					
				//$payeur = extract_champ_epreuve('payeur',$id_epreuve);
				//$cout_paiement_cheque = extract_champ_epreuve('cout_paiement_cheque',$id_epreuve);
				//$cout_paiement_cb = extract_champ_epreuve('cout_paiement_cb',$id_epreuve);
				
				$montant_inscription = extract_champ_epreuve_internaute_temp('cout',$id);
				$participation = extract_champ_epreuve_internaute_temp('participation',$id);	
					
					$date = 'NULL';
					$sql_plus = ', dossard = 0, frais_cb = 0, frais_cheque = 0, montant_inscription= '.($montant_inscription+$participation)." ";
					$dossard = 0;				
				
				$sql_plus .= ', paiement_date = '.$date.' ';
				//}
				$query = " UPDATE r_inscriptionepreuveinternaute SET ";
				$query .= "".$champ."='".addslashes_form_to_sql($value)."' ";
				$query .= $sql_plus;
				$query .=", paiement_montant = 0 ";
				$query .= " WHERE idInscriptionEpreuveInternaute=".$id.";";
				//echo $query;
				$result = $mysqli->query($query);
			
			
				$id_relais =  internaute_referent_internautes($id);
					
					if (!empty($id_relais['idInscriptionEpreuveInternautes'])) {
					
						$champs=explode("|",$id_relais['idInscriptionEpreuveInternautes']);
						$cpt_internautes = count($champs);						
						$first = TRUE;
						$aff_html_dossards = '';
							foreach ($champs as $key=>$idInscriptionEpreuveInternaute) {
		
									$montant_inscription = extract_champ_epreuve_internaute_temp('cout',$idInscriptionEpreuveInternaute);
									$participation = extract_champ_epreuve_internaute_temp('participation',$idInscriptionEpreuveInternaute);
									$q1  = "UPDATE r_inscriptionepreuveinternaute SET ".$champ."='".addslashes_form_to_sql($value)."', paiement_date = NULL,dossard = 0,montant_inscription = ".($montant_inscription+$participation).",participation = ".$participation.",paiement_date = NULL, frais_cb = 0, frais_cheque = 0, paiement_montant = 0 ";
									$q1 .= "WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
									//echo "inscrit seul: ".$q1;
									$mysqli->query($q1);
					
								if ($first==FALSE) $aff_html_dossards .='|';
								$aff_html_dossards .= '<i data-toggle="tooltip" data-placement="top" data-original-title="Dossard non attribué" class="text-danger fa fa-ban"></i>';
								$first=FALSE;
							
							
							}
								
								//if($payeur != 'coureur') { $frais = $frais*($cpt_internautes+1); }
								
								$queryupiei=" UPDATE r_insc_internautereferent
									SET paiement_type='".$value."', paiement_date=NOW(), paiement_date = NULL
									WHERE idEpreuve = ".$id_epreuve."
									AND idInternauteReferent = ".$id_relais['idInternauteReferent'];
									$resultupiei = $mysqli->query($queryupiei);
					

					
					}
					
				$aff_html_dossard = '<i data-toggle="tooltip" data-placement="top" data-original-title="Dossard non attribué" class="text-danger fa fa-ban"></i>'; 
				$aff= array('comeback'=>'OK','dossard'=>$dossard,'idInscriptionEpreuveInternaute'=>$id,'html_dossard'=>$aff_html_dossard,'aff_html_dossards'=>$aff_html_dossards,'html_ref_internautes'=>$id_relais['idInscriptionEpreuveInternautes'],'paiement_type'=>$value);
				echo json_encode($aff);
				exit();		
			}
			
			if ($value=='CHQ' || $value =='CHQ RECU') 
			{
				//$value='CHQ';
				
				$payeur = extract_champ_epreuve('payeur',$id_epreuve);
				$cout_paiement_cheque = extract_champ_epreuve('cout_paiement_cheque',$id_epreuve);
				$montant_des_inscriptions = 0;
				
				$dossard_referent = numerotation($id_parcours,$id_epreuve,$id);
				$query = " UPDATE r_inscriptionepreuveinternaute SET ";
				$query .= "dossard = ".$dossard_referent.", ";
				$query .= "paiement_type ='CHQ', ";
				$query .= "paiement_date = NOW() ";
				$query .= "WHERE idInscriptionEpreuveInternaute=".$id.";";
				$result = $mysqli->query($query);
				
				$etat_paiement_type_referent  = extract_champ_epreuve_internaute('paiement_type',$id);
				$id_relais =  internaute_referent_internautes($id);
				$dossard_equipe = extract_champ_parcours('dossard_equipe',$id_parcours);
				$dossard=0;
				//echo $id_relais['idInternauteReferent'];
				//echo $id_relais['idInscriptionEpreuveInternautes'];
	
				if (!empty($id_relais['idInscriptionEpreuveInternautes'])) {
				
					$champs=explode("|",$id_relais['idInscriptionEpreuveInternautes']);
					$cpt_internautes = count($champs);					
					
					$first = TRUE;
					$aff_html_dossards = '';
						
						foreach ($champs as $key=>$idInscriptionEpreuveInternaute) {
							
							$cout = extract_champ_epreuve_internaute_temp('cout',$idInscriptionEpreuveInternaute);
							$id_parcours_internaute = extract_champ_epreuve_internaute_temp('idParcours',$idInscriptionEpreuveInternaute);
							$participation = extract_champ_epreuve_internaute_temp('participation',$idInscriptionEpreuveInternaute);
							$paiement_montant = $cout + $participation;
							
							if ($dossard_equipe=='non') $dossard = numerotation($id_parcours_internaute,$id_epreuve,$idInscriptionEpreuveInternaute);
							
							$paiement_type = extract_champ_epreuve_internaute('paiement_type',$idInscriptionEpreuveInternaute);
							$paiement_type_not_in = array('SUPPRESSION','REMBOURSE');
							if (!in_array($paiement_type, $paiement_type_not_in || $etat_paiement_type_referent =='SUPPRESSION')) {
								
								if ($payeur!='coureur') {
									$q1  = "UPDATE r_inscriptionepreuveinternaute SET paiement_type ='CHQ', frais_cheque = ".$cout_paiement_cheque.", frais_cb = 0, paiement_date = NOW(),dossard = ".$dossard.",paiement_montant = ".($paiement_montant).",participation = ".($participation).",montant_inscription = ".($paiement_montant)." ";
									$q1 .= "WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
									//echo "inscrit seul: ".$q1;
									$mysqli->query($q1);
								}
								else
								{
									$q1  = "UPDATE r_inscriptionepreuveinternaute SET paiement_type ='CHQ', frais_cb = 0, frais_cheque = 0, paiement_date = NOW(),dossard = ".$dossard.",paiement_montant = ".($paiement_montant).",participation = ".($participation).",montant_inscription = ".($paiement_montant)." ";
									$q1 .= "WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
									//echo "inscrit seul: ".$q1;
									$mysqli->query($q1);
									
								}
								$montant_des_inscriptions += ($cout+$participation);
								if ($first==FALSE) $aff_html_dossards .='|';
								$aff_html_dossards .= '<a href="#" id="dossard--inscription--'.$idInscriptionEpreuveInternaute.'" class="editable editable-click" data-type="number" data-pk="'.$id_epreuve.'--'.$id_parcours.'" data-title="Modifier le dossard"><strong>'.$dossard.'</strong></a>';
								$first=FALSE;
							}
						}
				

				
				}
				
	
				$date_insc = extract_champ_epreuve_internaute('date_insc',$id);
				
				$cout = extract_champ_epreuve_internaute_temp('cout',$id);
				$participation = 0;
				$participation = extract_champ_epreuve_internaute_temp('participation',$id);
				
				//if(empty($participation)) $participation = extract_champ_epreuve_internaute_temp('participation',$id);
				$frais_cheque = extract_champ_epreuve_internaute_temp('frais_cheque',$id);
				
				
				$frais = 0;
				if($payeur == 'coureur') { $frais = $frais_cheque; $montant_des_inscriptions +=$frais; $montant_total_ref = $cout + $participation + $frais; } else { $frais = $cout_paiement_cheque; $montant_total_ref = $cout + $participation; } 
				
				$montant_des_inscriptions+= ($cout+$participation);
				
				$query = " UPDATE r_inscriptionepreuveinternaute SET ";
				$query .= "paiement_montant = ".($montant_total_ref).", ";
				$query .= "montant_inscription = ".($cout+$participation).", ";
				$query .= "participation = ".($participation).", ";
				$query .= "frais_cb = 0, ";
				$query .= "frais_cheque = ".$frais.", ";
				$query .= "paiement_date = NOW() ";
				$query .= "WHERE idInscriptionEpreuveInternaute=".$id.";";
				//echo $query;
				$result = $mysqli->query($query);
				//$aff_html_dossard = '<a href="#" id="dossard--inscription--'.$id.'" class="editable editable-click" data-type="number" data-pk="'.$id_epreuve.'--'.$id_parcours.'" data-title="Modifier le dossard"><strong>'.$dossard.'</strong></a>';
							
				if (!empty($id_relais['idInscriptionEpreuveInternautes'])) {			
							
							if($payeur != 'coureur') { $frais = $frais*($cpt_internautes+1); }
							$queryupiei=" UPDATE r_insc_internautereferent
								SET paiement_type='CHQ', paiement_date=NOW(), montant = ".$montant_des_inscriptions.", frais_cb = ".($frais)."
								WHERE idEpreuve = ".$id_epreuve."
								AND idInternauteReferent = ".$id_relais['idInternauteReferent'];
								//AND paiement_type in('ATTENTE','ATTENTE CHQ')
								//AND paiement_date IS NULL";
								$resultupiei = $mysqli->query($queryupiei);
				}
				
				
				$aff_html_dossard = '<a href="#" id="dossard--inscription--'.$id.'" class="editable editable-click" data-type="number" data-pk="'.$id_epreuve.'--'.$id_parcours.'" data-title="Modifier le dossard"><strong>'.$dossard_referent.'</strong></a>'; 
				$aff= array('comeback'=>'OK','dossard'=>$dossard_referent,'idInscriptionEpreuveInternaute'=>$id,'html_dossard'=>$aff_html_dossard,'aff_html_dossards'=>$aff_html_dossards,'html_ref_internautes'=>$id_relais['idInscriptionEpreuveInternautes'],'paiement_type'=>$value);
				echo json_encode($aff);
				exit();	
			}
	
			if ($value=='GRATUIT') 
			{
			
				//$payeur = extract_champ_epreuve('payeur',$id_epreuve);
				//$cout_paiement_cheque = extract_champ_epreuve('cout_paiement_cheque',$id_epreuve);
				//$montant_des_inscriptions = 0;
				
				$dossard_referent = numerotation($id_parcours,$id_epreuve,$id);
				$query = " UPDATE r_inscriptionepreuveinternaute SET ";
				$query .= "dossard = ".$dossard_referent.", ";
				$query .= "paiement_type ='GRATUIT' ";
				$query .= "WHERE idInscriptionEpreuveInternaute=".$id.";";
				$result = $mysqli->query($query);
				
				$etat_paiement_type_referent  = extract_champ_epreuve_internaute('paiement_type',$id);
				$id_relais =  internaute_referent_internautes($id);
				$dossard_equipe = extract_champ_parcours('dossard_equipe',$id_parcours);
				$dossard=0;
				//echo $id_relais['idInternauteReferent'];
				//echo $id_relais['idInscriptionEpreuveInternautes'];
	
				if (!empty($id_relais['idInscriptionEpreuveInternautes'])) {
				
					$champs=explode("|",$id_relais['idInscriptionEpreuveInternautes']);	
					$first = TRUE;
					$aff_html_dossards = '';
						
						foreach ($champs as $key=>$idInscriptionEpreuveInternaute) {
							
							$cout = extract_champ_epreuve_internaute('montant_inscription',$idInscriptionEpreuveInternaute);
							if ($dossard_equipe=='non') $dossard = numerotation($id_parcours,$id_epreuve,$idInscriptionEpreuveInternaute);
							
							$paiement_type = extract_champ_epreuve_internaute('paiement_type',$idInscriptionEpreuveInternaute);
							$paiement_type_not_in = array('SUPPRESSION','REMBOURSE');
							if (!in_array($paiement_type, $paiement_type_not_in || $etat_paiement_type_referent =='SUPPRESSION')) {
								
								if ($payeur!='coureur') {
									$q1  = "UPDATE r_inscriptionepreuveinternaute SET paiement_type ='GRATUIT', montant_inscription = 0, frais_cheque = 0, frais_cb = 0, paiement_date = NOW(),dossard = ".$dossard.",paiement_montant = 0, participation = 0 ";
									$q1 .= "WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
									//echo "inscrit seul: ".$q1;
									$mysqli->query($q1);
								}
								else
								{
									$q1  = "UPDATE r_inscriptionepreuveinternaute SET paiement_type ='GRATUIT', montant_inscription = 0, frais_cb = 0, frais_cheque = 0, paiement_date = NOW(),dossard = ".$dossard.",paiement_montant = 0, participation = 0 ";
									$q1 .= "WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
									//echo "inscrit seul: ".$q1;
									$mysqli->query($q1);
									
								}
								$montant_des_inscriptions += $cout;
								if ($first==FALSE) $aff_html_dossards .='|';
								$aff_html_dossards .= '<a href="#" id="dossard--inscription--'.$idInscriptionEpreuveInternaute.'" class="editable editable-click" data-type="number" data-pk="'.$id_epreuve.'--'.$id_parcours.'" data-title="Modifier le dossard"><strong>'.$dossard.'</strong></a>';
								$first=FALSE;
							}
						}
				

				
				}
				
	
				$date_insc = extract_champ_epreuve_internaute('date_insc',$id);
				
				//$participation = extract_champ_epreuve_internaute('participation',$id);
				if(empty($participation)) $participation = extract_champ_epreuve_internaute_temp('participation',$id);
				//$cout = extract_champ_epreuve_internaute('montant_inscription',$id);
				//$frais_cheque = extract_champ_epreuve_internaute_temp('frais_cheque',$id);
				
				
				//$frais = 0;
				//if($payeur == 'coureur') { $frais = $frais_cheque; $montant_des_inscriptions +=$frais; } else { $frais = $row['cout_paiement_cheque']; } 
				
				//$montant_des_inscriptions+= $cout;
				
				$query = " UPDATE r_inscriptionepreuveinternaute SET ";
				$query .= "paiement_montant = 0, ";
				$query .= "montant_inscription = 0, ";
				$query .= "frais_cb = 0, ";
				$query .= "frais_cheque = 0, ";
				$query .= "paiement_date = NOW() ";
				$query .= "WHERE idInscriptionEpreuveInternaute=".$id.";";
				//echo $query;
				$result = $mysqli->query($query);
				//$aff_html_dossard = '<a href="#" id="dossard--inscription--'.$id.'" class="editable editable-click" data-type="number" data-pk="'.$id_epreuve.'--'.$id_parcours.'" data-title="Modifier le dossard"><strong>'.$dossard.'</strong></a>';
							
				if (!empty($id_relais['idInscriptionEpreuveInternautes'])) {			
							
							
							$queryupiei=" UPDATE r_insc_internautereferent
								SET paiement_type='GRATUIT', paiement_date=NOW(), montant = 0, frais_cb = 0
								WHERE idEpreuve = ".$id_epreuve."
								AND idInternauteReferent = ".$id_relais['idInternauteReferent'];
								//AND paiement_type in('ATTENTE','ATTENTE CHQ')
								//AND paiement_date IS NULL";
								$resultupiei = $mysqli->query($queryupiei);
				}
				
				
				$aff_html_dossard = '<a href="#" id="dossard--inscription--'.$id.'" class="editable editable-click" data-type="number" data-pk="'.$id_epreuve.'--'.$id_parcours.'" data-title="Modifier le dossard"><strong>'.$dossard_referent.'</strong></a>'; 
				$aff= array('comeback'=>'OK','dossard'=>$dossard_referent,'idInscriptionEpreuveInternaute'=>$id,'html_dossard'=>$aff_html_dossard,'aff_html_dossards'=>$aff_html_dossards,'html_ref_internautes'=>$id_relais['idInscriptionEpreuveInternautes'],'paiement_type'=>$value);
				echo json_encode($aff);
				exit();	
			}
		
		
		
		
		
		}
		if ($champ== 'observation') {
		if ($id_epreuve==4247)
		{
			echo "value : ".$value."</br>";
			
			echo "value après : ".preg_replace("#\n|\t|\r#","-",$value)."</br>";
			
			
			
		}
		$value=preg_replace("#\n|\t|\r#","-",$value);
		$query = " UPDATE r_inscriptionepreuveinternaute SET ";
		$query .= "".$champ."='".addslashes_form_to_sql($value)."' ";
		$query .= "WHERE idInscriptionEpreuveInternaute=".$id.";";
		//echo $query;
		$result = $mysqli->query($query);
			$aff= array('comeback'=>'OK');
			echo json_encode($aff);
			exit();
		
		}
		
		if ($champ == 'dossard' || empty($_POST['idInscriptionEpreuveInternaute'])) {
		
			if ($dossard==0) { $aff_html_dossard = '<i data-toggle="tooltip" data-placement="top" data-original-title="Dossard non attribué" class="text-danger fa fa-ban"></i>'; }
			else { $aff_html_dossard = '<a href="#" id="dossard--inscription--'.$id.'" class="editable editable-click" data-type="number" data-pk="'.$id_epreuve.'--'.$id_parcours.'" data-title="Modifier le dossard"><strong>'.$dossard.'</strong></a>'; }
			
			$aff= array('comeback'=>'OK','dossard'=>$dossard,'idInscriptionEpreuveInternaute'=>$id,'html_dossard'=>$aff_html_dossard);
			echo json_encode($aff);
		}
		/*
		if (($value=='CHQ' || $value=='GRATUIT') || || !empty($_POST['idInscriptionEpreuveInternaute'])) {
		
	
			if ($dossard==0) { $aff_html_dossard = '<i data-toggle="tooltip" data-placement="top" data-original-title="Dossard non attribué" class="text-danger fa fa-ban"></i>'; }
			else { $aff_html_dossard = '<a href="#" id="dossard--inscription--'.$id.'" class="editable editable-click" data-type="number" data-pk="'.$id_epreuve.'--'.$id_parcours.'" data-title="Modifier le dossard"><strong>'.$dossard.'</strong></a>'; }
			
			$aff= array('comeback'=>'OK','dossard'=>$dossard,'idInscriptionEpreuveInternaute'=>$id,'html_dossard'=>$aff_html_dossard);
			echo json_encode($aff);
		}
		*/
	}

if (!empty($_POST['idInscriptionEpreuveInternaute'])) 
{
			$aff= array('comeback'=>'OK');
			echo json_encode($aff);

}

exit();


	if ($_GET['type'] == 'dotation') {
			
			if (isset($_POST['epre_parcours_select_dotation']) && is_array($_POST['epre_parcours_select_dotation'])) {
				foreach ($_POST['epre_parcours_select_dotation'] as $rushcheck) {
					foreach ($rushcheck as $final) {
						$term = $final;
					}
				}
			}

			$query  = "SELECT * FROM `r_dotationpreremplie` ";
			$query .= "WHERE idDotationPreRemplie = ".$term;
			$champ = array ('id' => 'idDotationPreRemplie', 'nom' =>'nom', 'label'=>'label', 'critere'=>'critere', 'unite'=>'unite', 'type_champ'=>'type_champ','date_butoir'=>'date_butoir', 'obligatoire'=>'obligatoire');
			
	}
	else if ($_GET['type'] == 'questiondiverse') {
			
			if (isset($_POST['epre_parcours_select_questiondiverse']) && is_array($_POST['epre_parcours_select_questiondiverse'])) {
				foreach ($_POST['epre_parcours_select_questiondiverse'] as $rushcheck) {
					foreach ($rushcheck as $final) {
						$term = $final;
					}
				}
			}

			$query  = "SELECT * FROM `r_questiondiversepreremplie` ";
			$query .= "WHERE idQuestionDiversePreremplie = ".$term;
			$champ = array ('id' => 'idQuestionDiversePreremplie', 'nom' =>'nom', 'label'=>'label', 'critere'=>'critere','unite'=>'unite', 'type_champ'=>'type_champ', 'date_butoir'=>'date_butoir', 'obligatoire'=>'obligatoire');
	}
	else {
	
			if (isset($_POST['epre_parcours_select_participation']) && is_array($_POST['epre_parcours_select_participation'])) {
				foreach ($_POST['epre_parcours_select_participation'] as $rushcheck) {
					foreach ($rushcheck as $final) {
						$term = $final;
					}
				}
			}
			if ($_GET['numparc'] != 0) {
				$query  = "SELECT * FROM `r_participationpreremplie` ";
				$query .= "WHERE idParticipationPreRemplie = ".$term;
				$champ = array ('id' => 'idParticipationPreRemplie', 'nom' =>'nom', 'label'=>'label', 'type_champ'=>'type_champ', 'prix'=>'prix', 'date_butoir'=>'date_butoir','obligatoire'=>'obligatoire');
			}
			else
			{
				$query  = "SELECT * FROM `r_participationpreremplie_commune` ";
				$query .= "WHERE idParticipationPreRemplieCommune = ".$term;
				$champ = array ('id' => 'idParticipationPreRemplieCommune', 'nom' =>'nom', 'label'=>'label', 'type_champ'=>'type_champ', 'prix'=>'prix', 'date_butoir'=>'date_butoir','obligatoire'=>'obligatoire');
			
			
			
			}
	}

			//echo $term;
			//$term = $_POST['epre_parcours_select_dotation_submit'];
			//$term = str_replace(" ","-",$_GET['term']);

		
			$result = $mysqli->query($query);
			$champ_row = array();
			$champs = array();
			$cpt = 0;
			while($row = mysqli_fetch_array($result))
			{
				
					foreach ($champ as $k=>$i) {
						$champ_row[$k] = mb_convert_encoding($row[$i], "UTF-8", "Windows-1252");
					}
				
				$cpt++; 
				array_push($champs, $champ_row);
			}

			//$data = array('ville'=>$ville);
			$json = json_encode($champs);
			$test = array("[", "]");
			$json = str_replace($test, "", $json);

			echo $json;
			//print_r($dotation);
			
			//echo json_encode($ville);
		//}
?>