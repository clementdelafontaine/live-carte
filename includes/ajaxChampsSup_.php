<?php 

	session_start();
	require_once('connect_db.php');
	connect_db();
	require_once('functions.php');
	require_once('numerotation.php');
	$update = $_GET['update'];
	if ($_GET['idInternaute'] !=0 ) $idInternaute = $_GET['idInternaute']; else $idInternaute = $_SESSION["log_id"];
	if ($_GET['idInscriptionEpreuveInternaute'] !=0 ) $idInscriptionEpreuveInternaute = $_GET['idInscriptionEpreuveInternaute']; else $idInscriptionEpreuveInternaute = $_SESSION['idInscriptionEpreuveInternaute'];
	$date_et_heure_du_jour = strtotime(date('Y-m-d H:i:s'));
	$json_total = $json_ages_limites = $json_tarif_et_promo = $json_dotation = $json_participation = $json_questiondiverse = '';
	$json_tarif_promo = array();
	$json_tarif_promo['tarif'] = 0;
	$json_tarif_promo['promo'] = 0;

	$type_certificat_bdd = type_epreuve($_GET['id_epreuve']);
	$type_epreuve = $type_certificat_bdd['idTypeEpreuve'];
					
	$insc_perso=$_GET['insc_perso'];
	//tarifs du parcours
	
	$certificatMedicalObligatoire = extract_champ_parcours ('certificatMedicalObligatoire', $_GET['id_parcours']);
	$parcours_relais = extract_champ_parcours ('relais', $_GET['id_parcours']);
	
	function limite_age ($id_parcours,$update,$type_epreuve) {
	
			$age=array();
			$query  = "SELECT age, ageLimite,horaireDepart ";
			$query .= "FROM r_epreuveparcours";
			$query .=" WHERE idEpreuveParcours = ".$id_parcours;
			$result_limite_age = $mysqli->query($query);
			$row_result_limite_age=mysqli_fetch_array($result_limite_age);
			
			if ($type_epreuve==1) {
				//echo $row_result_limite_age['horaireDepart'];
				list($année) = explode("-",$row_result_limite_age['horaireDepart']);
				$mois=12;
				$jour=31;
				$date_depart_inscription = strtotime($année."-".$mois."-".$jour." 23:59:00");
				//$row_result_limite_age['horaireDepart'];
				
			}
			else{ $date_depart_inscription = strtotime($row_result_limite_age['horaireDepart']); }
			
			$date_depart_course = strtotime($row_result_limite_age['horaireDepart']);
			
			list($date_de_depart,$heure) = explode(" ",$row_result_limite_age['horaireDepart']);
			$date_mineur = date("Y-m-d",strtotime('-18 years',$date_depart_course));
			$startdate = strtotime('-35 years',$date_depart_course);
			
			$age_min = $row_result_limite_age['age']; //12 ans par exemple
			$age_max = $row_result_limite_age['ageLimite']; // 120 ans par exemple
		
			$date_max = strtotime('-'.$age_max.' years',$date_depart_course);
			
			

			
			$date_min = strtotime('-'.$age_min.' years',$date_depart_inscription);
		
			$startdate_format=date("Y/m/d",$startdate);
			$date_max_format=date("Y/m/d",$date_max); // 120 ans
			$date_min_format=date("Y/m/d",$date_min); // 12 ans */
			
			$age = array('cal_start_date'=>$startdate_format,'cal_date_max'=>$date_max_format,'cal_date_min'=>$date_min_format, 'horairedepart' => $date_de_depart,'datemineur' => $date_mineur,'age'=>$row_result_limite_age['age'],'agelimite'=>$row_result_limite_age['ageLimite'],'update'=>$update);
			
			
			return $age ;

	}
	

		$nb_info_dossard = nombre_dossard($_GET['id_parcours']);
		$dossard_propose = numerotation_no_update($_GET['id_parcours'],$_GET['id_epreuve']);
		
		$tab_ages = limite_age($_GET['id_parcours'],$update,$type_epreuve);
		$tab_ages['auto_parentale'] = besoin_auto_parentale_parcours($_GET['id_parcours']); 
		$tab_ages['certif'] = besoin_certificat_medical($_GET['id_parcours']);
		$tab_ages['certif_obligatoire'] = $certificatMedicalObligatoire;
		$tab_ages['nb_dossard_attribue'] = $nb_info_dossard['nb_dossard_attribue'];
		$tab_ages['nb_dossard_disponible'] = $nb_info_dossard['nb_dossard_disponible'];
		$tab_ages['nb_dossard_reserve'] = $nb_info_dossard['nb_dossard_reserve'];
		$tab_ages['nb_dossard_parcours'] = $nb_info_dossard['nb_dossard_parcours'];
		$tab_ages['dossard_propose'] = $dossard_propose;
		$tab_ages['relais'] = $parcours_relais;
		
		if ($update==1) {
			$query_dossard=" SELECT dossard ";
			$query_dossard .=" FROM r_inscriptionepreuveinternaute ";
			$query_dossard .=" WHERE idEpreuve = ".$_GET['id_epreuve'];
			$query_dossard .=" AND idEpreuveParcours = ".$_GET['id_parcours'];
			$query_dossard .=" AND idInternaute = ".$idInternaute;
			$query_dossard .=" ORDER BY idInscriptionEpreuveInternaute DESC ";
			$query_dossard .=" LIMIT 1 ";
			$result_dossard = $mysqli->query($query_dossard);
			$row_dossard=mysqli_fetch_array($result_dossard);
			if ($row_dossard['dossard'] != 0) $tab_ages['dossard_propose'] = $row_dossard['dossard'];
		
		}
		

		$json_ages_limites = json_encode($tab_ages);

	function tarifs($id_tarif) {		
	
			
			$query  = "SELECT * ";
			$query .= "FROM r_epreuveparcourstarif";
			$query .=" WHERE idEpreuveParcoursTarif = ".$id_tarif;
			$result_tarifs = $mysqli->query($query);
			$tarifs=mysqli_fetch_array($result_tarifs);
	
			return $tarifs;
	}
	
	function tarifs_parcours($id_parcours) {		
	
			
			$query  = "SELECT * ";
			$query .= "FROM r_epreuveparcourstarif";
			$query .=" WHERE idEpreuveParcours = ".$id_parcours;
			$result_tarifs = $mysqli->query($query);
			//$tarifs=mysqli_fetch_array($result_tarifs);
	
			return $result_tarifs;
	}
	
	if (empty($_GET['id_tarif'])) 
	{	//$row_tarifs['tarif'] = 0; }
		//echo "empty";
		$tab_tarif = tarifs_parcours($_GET['id_parcours']);
		//print_r($tab_tarif);
		while ($row_tarifs=mysqli_fetch_array($tab_tarif))
		{ 

			if ($_GET['orga'] != 'oui') {

				if ( strtotime($row_tarifs['dateDebutTarif']) < $date_et_heure_du_jour AND $date_et_heure_du_jour < strtotime($row_tarifs['dateFinTarif'])) { 
					$json_tarif_promo['tarif'] =  $row_tarifs['tarif'];
					$json_tarif_promo['id_tarif'] =  $row_tarifs['idEpreuveParcoursTarif'] ;
							
				}
			}
			else
			{

					$json_tarif_promo['tarif'] =  $row_tarifs['tarif'];
					$json_tarif_promo['id_tarif'] =  $row_tarifs['idEpreuveParcoursTarif'] ;
					//echo $row_tarifs['tarif']."-".$row_tarifs['idEpreuveParcoursTarif'];
			
			}
			
		}
		
		
	}
	else	
	{ 
		//echo "NO empty";
		$row_tarifs= tarifs($_GET['id_tarif']);
		$json_tarif_promo['tarif'] =  $row_tarifs['tarif'];
		$json_tarif_promo['id_tarif'] =  $row_tarifs['idEpreuveParcoursTarif'] ; 
	}
	

		
		
		//$json_tarif_promo['auto_parentale'] = besoin_auto_parentale_parcours($_GET['id_parcours']); 
		//promo du parcours
		$reduction_promo_en_cours = 0;
		function promo($id_epreuve,$id_parcours=0) {		
		
				
				$query  = "SELECT * ";
				$query .= "FROM r_epreuveparcourstarifpromo";
				$query .=" WHERE idEpreuve = ".$id_epreuve;
				if ($id_parcours != 0) 	$query .=" AND idEpreuveParcours = ".$id_parcours;
				$query .=" AND etat = 'ACTIVE' ";
				$query .=" AND bon_dispo IS NULL ";
				$query .= " AND dateDebutTarifPromo < NOW() ";
				$query .= " AND dateFinTarifPromo > NOW() ";
				//echo $query;
				$result_promo= $mysqli->query($query);
				//$parcours=mysqli_fetch_array($result_parcours);
		
				return $result_promo;
		}											
		$tab_promo = promo($_GET['id_epreuve'],$_GET['id_parcours']);
		//print_r($tab_promo);
		while (($row_promo=mysqli_fetch_array($tab_promo)) != FALSE)
		{
			$json_tarif_promo['promo'] = $row_promo['idEpreuveParcoursTarifPromo'];
			//if ( strtotime($row_promo['dateDebutTarifPromo']) < $date_et_heure_du_jour AND $date_et_heure_du_jour < strtotime($row_promo['dateFinTarifPromo'])) {
				/*
				if ($row_promo['type_reduction']=='cout') 
				{ 
					//echo "xxx".$row_promo['prix_reduction'];
					$json_tarif_promo['promo'][$row_promo['idEpreuveParcoursTarifPromo']] = $row_promo['prix_reduction'];
				}
				else
				{
					//echo "yyy".$row_promo['type_reduction'];
					$json_tarif_promo['promo'][$row_promo['idEpreuveParcoursTarifPromo']] = $row_promo['type_reduction'];
					
				}
				$cpt++;
				*/
				//echo $cpt.$json_tarif_promo['promo'][$cpt];
			//}
			
			
			
			
		}
		//echo "GGGGG".$json_tarif_promo['promo'][0]."GGGGG";
		//print_r($json_tarif_promo);

			

	
		
		
		function promo_pre($idEpreuveParcoursTarifPromo) {		
		
				
				$query  = "SELECT * ";
				$query .= "FROM r_epreuveparcourstarifpromo_pre";
				$query .=" WHERE idEpreuveParcoursTarifPromo = ".$idEpreuveParcoursTarifPromo;
				$result_promo= $mysqli->query($query);
				$row_promo=mysqli_fetch_array($result_promo);
		
				return $row_promo['label'];
		}	
		
		if ($insc_perso==1) $json_tarif_promo['promo_pre'] = promo_pre($json_tarif_promo['promo']);
		//**** TEST ****//
		
		//echo $json_tarif_promo['promo_pre'];
		
		
		$json_tarif_et_promo = json_encode($json_tarif_promo);
		//print_r($json_tarif_et_promo);
		//$json = json_encode($_GET['id_parcours']);

	if ($_GET['id_parcours'] !=0) {
		
		$query_dotation  = "SELECT * FROM r_champssupdotation ";
		$query_dotation .= "WHERE idEpreuveParcours = ".$_GET['id_parcours'];
		$query_dotation .= " ORDER BY ordre ASC";
		
		$champ_dotation = array ('id' => 'idChampsSupDotation', 'nom' =>'nom', 'label'=>'label', 'critere'=>'critere', 'type_champ'=>'type_champ','date_butoir'=>'date_butoir', 'obligatoire'=>'obligatoire', 'ordre'=>'ordre');
		$result_dotation = $mysqli->query($query_dotation);
		$champ_row_dotation = array();
		$champs_dotation = array();
		$all_champs = array();
		
		
		$cpt = 0;
		while($row_dotation = mysqli_fetch_array($result_dotation))
		{

				$champ_row_dotation['champ'] = 'dotation';
				$champ_row_dotation['select'] = '';
				foreach ($champ_dotation as $k=>$i) {
					
					if ($k == 'date_butoir') {
					
						if ($date_et_heure_du_jour > strtotime($row_dotation[$i])) $row_dotation[$i] = 'KO'; else $row_dotation[$i] = 'OK';
					}

					$champ_row_dotation[$k] = $row_dotation[$i];
					//$champ_row_dotation[$k] = mb_convert_encoding($row_dotation[$i], "UTF-8", "Windows-1252");
					//echo $k."-".$champ_row_dotation[$k]."<br>";
				}
			$cpt++;
			
			if ($insc_perso ==1) {
				$champ_row_dotation['select'] = champ_inscrit_dotation_pre($_GET['id_parcours'], $champ_row_dotation['id'],'value');
				//echo $_GET['id_parcours']."-".$idInternaute."-".$champ_row_dotation['id'];
			}
			
			if ($update ==1) {
				$champ_row_dotation['select'] = champ_inscrit_dotation($_GET['id_parcours'], $idInternaute, $idInscriptionEpreuveInternaute, $champ_row_dotation['id'],'value');
				//echo $_GET['id_parcours']."-".$idInternaute."-".$champ_row_dotation['id'];
			}
			array_push($all_champs, $champ_row_dotation);

		}


		//print_r($champs_dotation);
		//$data = array('ville'=>$ville);
		/*$json_dotation = json_encode($champs_dotation);
		$supp_crochet = array("[", "]");
		$json_dotation = str_replace($supp_crochet, "", $json_dotation);
		$json_dotation = str_replace("},{", "}|{", $json_dotation);*/
		
	
		//CHAMPS PARTICIPATION			
		$query_participation  = "SELECT * FROM r_champssupparticipation ";
		$query_participation .= "WHERE idEpreuveParcours = ".$_GET['id_parcours'];
		$query_participation .= " ORDER BY ordre ASC";
		$champ_participation = array ('id' => 'idChampsSupParticipation', 'nom' =>'nom', 'label'=>'label','type_champ'=>'type_champ','prix'=>'prix', 'qte'=>'qte','date_butoir'=>'date_butoir', 'obligatoire'=>'obligatoire', 'ordre'=>'ordre');
		$result_participation = $mysqli->query($query_participation);
		$champ_row_participation = array();
		$champs_participation = array();
		$cpt = 0;
		while($row_participation = mysqli_fetch_array($result_participation))
		{
			
				$champ_row_participation['champ'] = 'participation';
				$champ_row_participation['select'] = '';
				foreach ($champ_participation as $k=>$i) {
					
					
					if ($k == 'date_butoir') {
					
						if ($date_et_heure_du_jour > strtotime($row_participation[$i])) $row_participation[$i] = 'KO'; else $row_participation[$i] = 'OK';
					}
					
					//$champ_row_participation[$k] = mb_convert_encoding($row_participation[$i], "UTF-8", "Windows-1252");
					$champ_row_participation[$k] = $row_participation[$i];
					//echo $k."-".$champ_row_participation[$k]."<br>";
					
				}
			
			$cpt++;
			if ($insc_perso ==1) {
				$champ_row_dotation['select'] = champ_inscrit_participation_pre($_GET['id_parcours'], $champ_row_dotation['id'],'value');
				//echo $_GET['id_parcours']."-".$idInternaute."-".$champ_row_dotation['id'];
			}
			if ($update ==1) {
				$champ_row_participation['select'] = champ_inscrit_participation($_GET['id_parcours'], $idInternaute, $idInscriptionEpreuveInternaute, $champ_row_participation['id'],'value');
				
			}
			//array_push($champs_participation, $champ_row_participation);
			array_push($all_champs, $champ_row_participation);
		}
			

		//print_r($champs);
		//$data = array('ville'=>$ville);
		/*$json_participation = json_encode($champs_participation);
		$supp_crochet = array("[", "]");
		$json_participation = str_replace($supp_crochet, "", $json_participation);
		$json_participation = str_replace("},{", "}|{", $json_participation);*/
		
		//CHAMPS QUESTION DIVERSE			
		$query_questiondiverse  = "SELECT * FROM r_champssupquestiondiverse ";
		$query_questiondiverse .= "WHERE idEpreuveParcours = ".$_GET['id_parcours'];
		$query_questiondiverse .= " ORDER BY ordre ASC";
		$champ_questiondiverse = array ('id' => 'idChampsSupQuestionDiverse', 'nom' =>'nom', 'label'=>'label', 'critere'=>'critere', 'type_champ'=>'type_champ','date_butoir'=>'date_butoir', 'obligatoire'=>'obligatoire', 'ordre'=>'ordre');
		$result_questiondiverse = $mysqli->query($query_questiondiverse);
		$champ_row_questiondiverse = array();
		$champs_questiondiverse = array();
		$cpt = 0;
		while($row_questiondiverse = mysqli_fetch_array($result_questiondiverse))
		{

				$champ_row_questiondiverse['champ'] = 'questiondiverse';
				$champ_row_questiondiverse['select'] = '';
				foreach ($champ_questiondiverse as $k=>$i) {
					
					if ($k == 'date_butoir') {
					
						if ($date_et_heure_du_jour > strtotime($row_questiondiverse[$i])) $row_questiondiverse[$i] = 'KO'; else $row_questiondiverse[$i] = 'OK';
					}
					//$champ_row_questiondiverse[$k] = mb_convert_encoding($row_questiondiverse[$i], "UTF-8", "Windows-1252");
					$champ_row_questiondiverse[$k] = $row_questiondiverse[$i];

				}
			$cpt++;
			if ($insc_perso ==1) 
			{
						
				$champ_row_questiondiverse['select'] = champ_inscrit_questiondiverse_pre($_GET['id_parcours'], $champ_row_questiondiverse['id'],'value');
				//echo 	"aa.".$champ_row_questiondiverse['select']."aaa";	
					/*if ( $champ_row_questiondiverse['type_champ'] == 'FILE' && $champ_row_questiondiverse['select'] !='') {
							
						$champ_row_questiondiverse['select'] = questiondivers_file_existe($champ_row_questiondiverse['select']);
					}*/
			}
			if ($update ==1) 
			{
						
				$champ_row_questiondiverse['select'] = champ_inscrit_questiondiverse($_GET['id_parcours'], $idInternaute, $idInscriptionEpreuveInternaute, $champ_row_questiondiverse['id'],'value');
				//echo 	"aa.".$champ_row_questiondiverse['select']."aaa";	
					if ( $champ_row_questiondiverse['type_champ'] == 'FILE' && $champ_row_questiondiverse['select'] !='') {
							
						$champ_row_questiondiverse['select'] = questiondivers_file_existe($champ_row_questiondiverse['select']);
					}
			}
					//array_push($champs_questiondiverse, $champ_row_questiondiverse);
					array_push($all_champs, $champ_row_questiondiverse);
		}
			

	}
			//$data = array('ville'=>$ville);
			/*$json_questiondiverse = json_encode($champs_questiondiverse);
			$supp_crochet = array("[", "]");
			$json_questiondiverse = str_replace($supp_crochet, "", $json_questiondiverse);
			$json_questiondiverse = str_replace("},{", "}|{", $json_questiondiverse);*/
			
			//print_r($all_champs);
			
			//echo "----</br>";
			//print_r($all_champs);
			$separator = select_code_separator ($_GET['id_epreuve']);
			if ($separator != FALSE) {
				list($separator_fonction,$separator_champ,$separator_parcours) = explode('+',$separator);
			}

			if (!empty($all_champs)) { usort($all_champs, "cmp"); $json_all = json_encode($all_champs);}
			//print_r($all_champs);
			$supp_crochet = array("[", "]");
			$json_all = str_replace($supp_crochet, "", $json_all);
			$json_all = str_replace("},{", "}".$separator_champ."{", $json_all);
			//echo $json_all;
			
			//if ($json_dotation) $json_total= $json_dotation;
			//if ($json_participation && $json_dotation) { $json_total.= "#".$json_participation; } else { $json_total.= $json_participation; };
			//if (($json_questiondiverse && $json_dotation) || ($json_questiondiverse && $json_participation) ) { $json_total.= "#".$json_questiondiverse; } else { $json_total.= $json_questiondiverse; };
			//if ($json_total) { $json_total = $json_tarif_et_promo."@".$json_total; } else {  $json_total = $json_tarif_et_promo; };
			if ($json_all) { $json_all = $json_ages_limites.$separator_fonction.$json_tarif_et_promo.$separator_fonction.$json_all; } else {  $json_all = $json_ages_limites.$separator_fonction.$json_tarif_et_promo; };
			
			echo $json_all;
	
?>