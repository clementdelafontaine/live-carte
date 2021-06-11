<?php 

	session_start();
	require_once('connect_db.php');
	connect_db();
	require_once('functions.php');
	require_once('numerotation.php');
	$update = $_GET['update'];
	if ($_GET['idInternaute'] !=0 ) $idInternaute = $_GET['idInternaute']; else $idInternaute = $_SESSION["log_id"];
	if ($_GET['idInscriptionEpreuveInternaute'] !=0 ) $idInscriptionEpreuveInternaute = $_GET['idInscriptionEpreuveInternaute']; else $idInscriptionEpreuveInternaute = $_SESSION['idInscriptionEpreuveInternaute'];
	if (!isset($_SERVER['HTTP_X_GT_LANG'])) $language = strtolower(extract_champ_internaute('langage',$idInternaute)); else $language = strtolower($_SERVER['HTTP_X_GT_LANG']);
	//echo extract_champ_internaute('langage',$idInternaute);
	//echo $_SERVER['HTTP_X_GT_LANG'];
	//echo $language;
	//echo "eee";
	$date_et_heure_du_jour = strtotime(date('Y-m-d H:i:s'));
	$json_total = $json_ages_limites = $json_tarif_et_promo = $json_dotation = $json_participation = $json_questiondiverse = '';
	$json_tarif_promo = array();
	$json_tarif_promo['tarif'] = 0;
	$json_tarif_promo['promo'] = 0;

	$type_certificat_bdd = type_epreuve($_GET['id_epreuve']);
	$type_epreuve = $type_certificat_bdd['idTypeEpreuve'];
					
	$insc_perso=$_GET['insc_perso'];
	$insc_perso_ca=$_GET['ca'];
	if (isset($_GET['cpp'])) $code_promo_profile=$_GET['cpp']; else $code_promo_profile=0;
	//tarifs du parcours
	//exit();
	$certificatMedicalObligatoire = extract_champ_parcours ('certificatMedicalObligatoire', $_GET['id_parcours']);
	$parcours_relais = extract_champ_parcours ('relais', $_GET['id_parcours']);
	
	function limite_age ($id_parcours,$update,$type_epreuve) {
	
			//echo "tto";
			$age=array();
			$query  = "SELECT age, ageLimite,horaireDepart ";
			$query .= "FROM r_epreuveparcours";
			$query .=" WHERE idEpreuveParcours = ".$id_parcours;
			$result_limite_age = $mysqli->query($query);
			$row_result_limite_age=mysqli_fetch_array($result_limite_age);
			$cat_annee = extract_champ_epreuve('cat_annee',$_GET['id_epreuve']);
			
			if ($cat_annee=='oui') {
				//echo $row_result_limite_age['horaireDepart'];
				list($année) = explode("-",$row_result_limite_age['horaireDepart']);
				$mois=12;
				$jour=31;
				$date_depart_course = $date_depart_inscription = strtotime($année."-".$mois."-".$jour." 23:59:00");
				$mois=01;
				$jour=01;
				$date_depart_course = strtotime($année."-".$mois."-".$jour." 23:59:00");
				//echo $date_depart_inscription ;
				//$row_result_limite_age['horaireDepart'];
				
			}
			else
			{ 
			
				$date_depart_inscription = strtotime($row_result_limite_age['horaireDepart']);
				$date_depart_course = strtotime($row_result_limite_age['horaireDepart']);

			}
			
			
			
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
			//echo $date_min_format;
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
					if ($row_tarifs['nb_dossard'] - $row_tarifs['nb_dossard_pris'] <=0) $row_tarifs['reduction']=0;
					$json_tarif_promo['tarif'] =  $row_tarifs['tarif']-$row_tarifs['reduction'];
					$json_tarif_promo['id_tarif'] =  $row_tarifs['idEpreuveParcoursTarif'] ;
					$json_tarif_promo['nb_dossard_tarif_max'] =  $row_tarifs['nb_dossard'] ;
					$json_tarif_promo['nb_dossard_tarif_pris'] =  $row_tarifs['nb_dossard_pris'] ;
					$json_tarif_promo['nb_dossard_reduction'] =  $row_tarifs['reduction'] ;
							
				}
			}
			else
			{
					if ($row_tarifs['nb_dossard'] - $row_tarifs['nb_dossard_pris'] <=0) $row_tarifs['reduction']=0;
					$json_tarif_promo['tarif'] =  $row_tarifs['tarif']-$row_tarifs['reduction'];
					$json_tarif_promo['id_tarif'] =  $row_tarifs['idEpreuveParcoursTarif'] ;
					$json_tarif_promo['nb_dossard_tarif_max'] =  $row_tarifs['nb_dossard'] ;
					$json_tarif_promo['nb_dossard_tarif_pris'] =  $row_tarifs['nb_dossard_pris'] ;
					$json_tarif_promo['nb_dossard_reduction'] =  $row_tarifs['reduction'] ;
					//echo $row_tarifs['tarif']."-".$row_tarifs['idEpreuveParcoursTarif'];
			
			}
			
		}
		
		
	}
	else	
	{ 
		//echo "NO empty";
		$row_tarifs= tarifs($_GET['id_tarif']);
		if ($row_tarifs['nb_dossard'] - $row_tarifs['nb_dossard_pris'] <=0) $row_tarifs['reduction']=0;
		$json_tarif_promo['tarif'] =  $row_tarifs['tarif']-$row_tarifs['reduction'];
		$json_tarif_promo['id_tarif'] =  $row_tarifs['idEpreuveParcoursTarif'] ;
		$json_tarif_promo['nb_dossard_tarif_max'] =  $row_tarifs['nb_dossard'] ;
		$json_tarif_promo['nb_dossard_tarif_pris'] =  $row_tarifs['nb_dossard_pris'] ;
		$json_tarif_promo['nb_dossard_reduction'] =  $row_tarifs['reduction'] ;		
	}
	

		
		
		//$json_tarif_promo['auto_parentale'] = besoin_auto_parentale_parcours($_GET['id_parcours']); 
		//promo du parcours
		$reduction_promo_en_cours = 0;
		function promo($id_epreuve,$id_parcours=0,$code_promo_profile) {		
		
				
				if ($code_promo_profile !=0) {
					
					$query  = "SELECT * ";
					$query .= "FROM r_epreuveparcourstarifpromo";
					$query .=" WHERE idEpreuveParcoursTarifPromo = ".$code_promo_profile;
				
				}
				else
				{
					$query  = "SELECT * ";
					$query .= "FROM r_epreuveparcourstarifpromo";
					$query .=" WHERE idEpreuve = ".$id_epreuve;
					if ($id_parcours != 0) 	$query .=" AND idEpreuveParcours = ".$id_parcours;
					$query .=" AND etat = 'ACTIVE' ";
					//$query .=" AND bon_dispo IS NOT NULL ";
					$query .= " AND dateDebutTarifPromo < NOW() ";
					$query .= " AND dateFinTarifPromo > NOW() ";
				}
				//echo $query;
				$result_promo= $mysqli->query($query);
				//$parcours=mysqli_fetch_array($result_parcours);
		
				return $result_promo;
		}											
		$tab_promo = promo($_GET['id_epreuve'],$_GET['id_parcours'],$code_promo_profile);
		//print_r($tab_promo);
		while (($row_promo=mysqli_fetch_array($tab_promo)) != FALSE)
		{
			
			if ($code_promo_profile !=0 ) 
			{	
				if ($row_promo['type_reduction']=='cout')
				{
					$json_tarif_promo['valeur_promo'] = $row_promo['prix_reduction'];
				}
				elseif ($row_promo['type_reduction']=='inscription')
				{
					$json_tarif_promo['valeur_promo'] = 'inscription';
				}
				elseif ($row_promo['type_reduction']=='tout')
				{
					$json_tarif_promo['valeur_promo'] = 'tout';
				}
				else 
				{
					//$('#tarif_en_cours_'+nb_insc).val())*(value_pourcentage/100
					$json_tarif_promo['valeur_promo'] = $row_promo['pourcentage'];
					
				}
				$json_tarif_promo['type_promo'] = $row_promo['type_reduction'];
			}	
			
				
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

			

	
		
		
		function promo_pre($insc_perso_ca) {		
		
				
				$query  = "SELECT retp.label FROM r_epreuveparcourstarifpromo_pre as retp ";
				$query .= "INNER JOIN r_epreuveperso_pre as rep ";
				$query .= "ON retp.idEpreuvePersoPre = rep.idEpreuvePersoPre ";
				$query .= "WHERE rep.codeActivation = '".$insc_perso_ca."' ";
				$query .= "AND rep.dateDebut < NOW() ";
				$query .= "AND rep.dateFin > NOW() ";
				$result_promo= $mysqli->query($query);
				$row_promo=mysqli_fetch_array($result_promo);
		
				return $row_promo['label'];
		}	
		
		if ($insc_perso==1) $json_tarif_promo['promo_pre'] = promo_pre($insc_perso_ca);
		//**** TEST ****//
		
		//echo $json_tarif_promo['promo_pre'];
		
		
		$json_tarif_et_promo = json_encode($json_tarif_promo);
		//print_r($json_tarif_et_promo);
		//$json = json_encode($_GET['id_parcours']);
		
		//*** OPTION PLUS ****/
		/*
		$option_plus=options_plus($_GET['id_epreuve'],$_GET['id_parcours']);
		
		$html_option_plus = '<h4 class="col-sm-3 control-label m-t-15">Option supplémentaire</h4>';
		$html_option_plus .= '<div class="col-sm-7 input-group input-group-lg">';				
		$html_option_plus .= '<SELECT class="form-control" name="select_option[1]" id="select_option_1" required onchange="change_prix_option(1,this.value);">';
			while($row_option_plus = mysqli_fetch_array($option_plus)) {														
																		
				$html_option_plus .= '<OPTION VALUE="'.$row_option_plus['prix'].';\''.addslashes_form_to_sql($row_option_plus['information']).'\'" >'.$row_option_plus['label'].' [+ '.$row_option_plus['prix'].' € ]</OPTION>';
			} 
		$html_option_plus .='</select>';
		$html_option_plus .= '</div>';
		$html_option_plus .= '<h4 class="m-l-40 m-t-15 badge badge-warning prix-ats" id="prix_option_affichage_1"> 0 €</h4><a href="javascript:;" data-toggle="popover" title="Information importante" data-content="" class="m-l-10" id="option_plus_information_1" style="display:none"><i class="fa fa-1x fa-exclamation-circle"/></i></a>';															
		$html_option_plus .='<input type="hidden" id="option_plus_en_cours_1" name="option_plus_en_cours[1]" value="0">';
		$json_html_option_plus = json_encode($html_option_plus);
		*/
		
		
		$option_plus=options_plus($_GET['id_epreuve'],$_GET['id_parcours']);
		//print_r($option_plus);
		$champ_option_plus = array ('id' => 'idOptionPlus', 'nom' =>'nom', 'label'=>'label', 'prix'=>'prix', 'qte'=>'qte','information'=>'information', 'url_image'=>'url_image', 'dateDebut'=>'dateDebut', 'dateFin'=>'dateFin', 'active'=>'active');
		$champ_row_option_plus = array();
		$champs_option_plus = array();
		//$champ_option_plus['select'] = '';
		
		
		
		$cpt = 0;
		while($row_option_plus = mysqli_fetch_array($option_plus))
		{

				//$champ_row_dotation['champ'] = 'dotation';
				//$champ_row_dotation['select'] = '';
				foreach ($champ_option_plus as $k=>$i) {
					
					$champ_row_option_plus[$cpt][$k] = $row_option_plus[$i];
					//$champ_row_dotation[$k] = mb_convert_encoding($row_dotation[$i], "UTF-8", "Windows-1252");
					//echo $k."-".$champ_row_dotation[$k]."<br>";
				}
			if ($update ==1) {
				$champ_row_option_plus[$cpt]['select'] = extract_champ_id_epreuve_internaute('idOptionPlus',$idInscriptionEpreuveInternaute);
				//echo $_GET['id_parcours']."-".$idInternaute."-".$champ_row_dotation['id'];
			}
			$cpt++;
			/*
			if ($insc_perso ==1) {
				$champ_row_dotation['select'] = champ_inscrit_dotation_pre($_GET['id_parcours'], $champ_row_dotation['id'],'value');
				//echo $_GET['id_parcours']."-".$idInternaute."-".$champ_row_dotation['id'];
			}
			*/

		}		
		
		
		//*** OPTION PLUS ****/
		// ASSURANCE 
		$assurance=assurance_annulation($_GET['id_epreuve'],'','non');
		//print_r($assurance);
		$champ_assurance = array ('id' => 'idAssuranceAnnulation', 'type' =>'type', 'pourcentage'=>'pourcentage', 'informations'=>'informations', 'date'=>'date','active'=>'active');
		$champ_row_assurance = array();
		$champs_assurance = array();
		//$champ_option_plus['select'] = '';
		$cpt = 0;
		
		$champ_row_assurance[0]['id'] = $assurance['idAssuranceAnnulation'];
		$champ_row_assurance[0]['type'] = $assurance['type'];
		$champ_row_assurance[0]['pourcentage'] = $assurance['pourcentage'];
		$champ_row_assurance[0]['informations'] = $assurance['informations'];
		$champ_row_assurance[0]['date']= $assurance['date'];
		$champ_row_assurance[0]['active']= $assurance['active'];		
		if ($update ==1) { $champ_row_assurance[0]['select'] = extract_champ_id_epreuve_internaute('assurance',$idInscriptionEpreuveInternaute); }
	
		//print_r($champ_row_assurance);
		
	if ($_GET['id_parcours'] !=0) {
		
		$query_dotation  = "SELECT * FROM r_champssupdotation ";
		$query_dotation .= "WHERE idEpreuveParcours = ".$_GET['id_parcours'];
		$query_dotation .= " ORDER BY ordre ASC";
		
		$champ_dotation = array ('id' => 'idChampsSupDotation', 'nom' =>'nom', 'label'=>'label', 'critere'=>'critere', 'type_champ'=>'type_champ','date_butoir'=>'date_butoir', 'obligatoire'=>'obligatoire', 'ordre'=>'ordre', 'information'=>'information');
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
		$champ_participation = array ('id' => 'idChampsSupParticipation', 'nom' =>'nom', 'label'=>'label','type_champ'=>'type_champ','prix'=>'prix', 'qte'=>'qte','date_butoir'=>'date_butoir', 'obligatoire'=>'obligatoire', 'ordre'=>'ordre', 'information'=>'information', 'url_image'=>'url_image');
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
		//******MODIF********
		$champ_questiondiverse = array ('id' => 'idChampsSupQuestionDiverse', 'nom' =>'nom', 'label'=>'label', 'critere'=>'critere', 'unite'=>'unite', 'type_champ'=>'type_champ','date_butoir'=>'date_butoir', 'obligatoire'=>'obligatoire', 'ordre'=>'ordre', 'verifie'=>'verifie', 'date_verifie'=>'date_verifie', 'information'=>'information');
		//******MODIF********
		$result_questiondiverse = $mysqli->query($query_questiondiverse);
		$champ_row_questiondiverse = array();
		$champs_questiondiverse = array();
		$cpt = 0;
		while($row_questiondiverse = mysqli_fetch_array($result_questiondiverse))
		{

				$champ_row_questiondiverse['champ'] = 'questiondiverse';
				$champ_row_questiondiverse['select'] = '';
				$champ_row_questiondiverse['id_file'] = '';
				$champ_row_questiondiverse['id_riqd_tmp'] = '';
				$champ_row_questiondiverse['id_verifie_file'] = '';
				$champ_row_questiondiverse['id_date_verifie_file'] = '';
				$champ_row_questiondiverse['id_file_record'] = 'on';
				foreach ($champ_questiondiverse as $k=>$i) {
					
					if ($k == 'date_butoir') {
					
						if ($date_et_heure_du_jour > strtotime($row_questiondiverse[$i])) $row_questiondiverse[$i] = 'KO'; else $row_questiondiverse[$i] = 'OK';
					}
					//language
					if ($k=='label')
					{
						//echo $k."-".$row_questiondiverse[$i]."-";
						$tmp_lg = explode("|",$row_questiondiverse[$i]);
						$cpt_lg = count($tmp_lg);
						//echo "---".$language."----".$cpt_lg;
						$lg_ko=0;
						if ($cpt_lg>1) 
						{
							if ($language=='en')
							{
								if (isset($tmp_lg[1])) { $row_questiondiverse[$i]=$tmp_lg[1]; } else { $lg_ko=1;$row_questiondiverse[$i]=$tmp_lg[0]; }
							}
							elseif ($language=='de')
							{
								if (isset($tmp_lg[2])) { $row_questiondiverse[$i]=$tmp_lg[2]; } else { $lg_ko=1;$row_questiondiverse[$i]=$tmp_lg[0]; }
							}
							elseif ($language=='it')
							{
								if (isset($tmp_lg[3])) { $row_questiondiverse[$i]=$tmp_lg[3]; } else { $lg_ko=1;$row_questiondiverse[$i]=$tmp_lg[0]; }
							}
							elseif ($language=='es')
							{
								
								if (isset($tmp_lg[4])) { $row_questiondiverse[$i]=$tmp_lg[4]; } else { $lg_ko=1;$row_questiondiverse[$i]=$tmp_lg[0]; }
							}
							else
							{
								$row_questiondiverse[$i]=$tmp_lg[0];
							}
						}
						else
						{
								$lg_ko=1;
								$row_questiondiverse[$i]=$tmp_lg[0];
						}

					}
					//echo $row_questiondiverse[$i];
					if ($k=='critere')
					{
						//echo $k."-".$row_questiondiverse[$i]."-";
						$tmp_lg_f = explode(";",$row_questiondiverse[$i]);
						$rec='';
						$first=TRUE;
						if (count($tmp_lg_f)>1)
						{
							
							
							
							foreach ($tmp_lg_f as $key=>$critere)
							{
								$tmp='';
								$tmp_lg_c = explode("|",$critere);
								$cpt_lg_c = count($tmp_lg_c);
								//print_r($tmp_lg_c);
								//echo "---".$language;
								$lg_ko_c=0;
								if ($cpt_lg_c>1) 
								{
									if ($language=='en')
									{
										if (isset($tmp_lg_c[1])) { $tmp=$tmp_lg_c[1]; } else { $lg_ko=1;$tmp=$tmp_lg_c[0]; }
									}
									elseif ($language=='de')
									{
										if (isset($tmp_lg_c[2])) { $tmp=$tmp_lg_c[2]; } else { $lg_ko=1;$tmp=$tmp_lg_c[0]; }
									}
									elseif ($language=='it')
									{
										if (isset($tmp_lg_c[3])) { $tmp=$tmp_lg_c[3]; } else { $lg_ko=1;$tmp=$tmp_lg_c[0]; }
									}
									elseif ($language=='es')
									{
										if (isset($tmp_lg_c[4])) { $tmp=$tmp_lg_c[4]; } else { $lg_ko=1;$tmp=$tmp_lg_c[0]; }
									}
									else
									{
										$tmp=$tmp_lg_c[0];
									}
								}
								else
								{
										$lg_ko_c=1;
										$tmp=$tmp_lg_c[0];
								}
								
								if ($first==TRUE)
								{
									$rec = $tmp;
									$first=FALSE;
									
								}
								else
								{
									$rec .= ";".$tmp;
									
								}
								//echo $rec."#";
							}
							$row_questiondiverse[$i]=$rec;
						}
					//echo $row_questiondiverse[$i];
					}
					//$champ_row_questiondiverse[$k] = mb_convert_encoding($row_questiondiverse[$i], "UTF-8", "Windows-1252");
					$champ_row_questiondiverse[$k] = $row_questiondiverse[$i];

				}
			//language
			//echo $lg_ko;
			if ($lg_ko==1) $champ_row_questiondiverse['gt_translate_keys'] = array(array('key'=>'label','format'=>'text'));
			//language
					if ( $champ_row_questiondiverse['type_champ'] == 'FILE' && $update ==0)
					{
						
						$champ_row_questiondiverse['id_file_record'] = 'on';
						
						
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
			//******MODIF********			
				//echo $champ_row_questiondiverse['id'];
				
				//print_r($champ_row_questiondiverse['select']);	
					if ( $champ_row_questiondiverse['type_champ'] == 'FILE' )
					{
						$champ_row_questiondiverse['select'] = champ_inscrit_questiondiverse_file($_GET['id_parcours'], $idInternaute, $idInscriptionEpreuveInternaute, $champ_row_questiondiverse['id'],'value');
						//print_r($champ_row_questiondiverse['select']);
						if ($champ_row_questiondiverse['select'] !='')	
						{
							
							$first= FALSE;
							foreach ($champ_row_questiondiverse['select'] as $select_file)
							{
								//echo $select_file;
								$value_tmp = explode('#',$select_file);
								
								if ($first==TRUE) { 
								
									if (!empty($value_tmp[0])) $tmp .=";"; 
									$id_tmp .=";"; 
									$id_riqd_tmp .=";";  
									$id_verifie_file .=";"; 
									$id_date_verifie_file .=";";
								
								}
								
								$tmp .= questiondivers_file_existe($value_tmp[0]);
								$id_tmp .= $value_tmp[0];
								$id_riqd_tmp .= $value_tmp[1];
								$id_verifie_file .= $value_tmp[2];
								$id_date_verifie_file.= $value_tmp[3];
								//echo $i."-".$select_file."#".$tmp."-";
								$first= TRUE;
							}
							//echo  $tmp;
							$champ_row_questiondiverse['select'] = $tmp;
							$champ_row_questiondiverse['id_file'] = $id_tmp;
							$champ_row_questiondiverse['id_riqd_tmp'] = $id_riqd_tmp;
							$champ_row_questiondiverse['id_verifie_file'] = $id_verifie_file;
							$champ_row_questiondiverse['id_date_verifie_file'] = $id_date_verifie_file;
							//$champ_row_questiondiverse['select'] = questiondivers_file_existe($champ_row_questiondiverse['select']);
							//print_r($champ_row_questiondiverse);
							if ( $first== TRUE) $champ_row_questiondiverse['id_file_record'] = '';
						}else { $champ_row_questiondiverse['id_file_record'] = 'on';}
					}
					else
					{
						
						$champ_row_questiondiverse['select'] = champ_inscrit_questiondiverse($_GET['id_parcours'], $idInternaute, $idInscriptionEpreuveInternaute, $champ_row_questiondiverse['id'],'value');
					}
			//******MODIF********
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
			//echo $json_all;
			$supp_crochet = array("[", "]");
			$json_all = str_replace($supp_crochet, "", $json_all);
			//echo $json_all;
			$json_all = str_replace("},{", "}".$separator_champ."{", $json_all);
			//echo $json_all;
			//		$json_option_plus = json_encode($champ_row_option_plus);
			if (!empty($champ_row_option_plus)) { 
				//usort($champ_row_option_plus, "cmp"); 
				$json_option_plus = json_encode($champ_row_option_plus);
				$supp_crochet = array("[", "]");
				$json_option_plus = str_replace($supp_crochet, "", $json_option_plus);
				//echo $json_all;
				$json_option_plus = str_replace("},{", "}".$separator_champ."{", $json_option_plus);			
			}
			else
			{
				$json_option_plus ='';
			}
			
			if (!empty($champ_row_assurance)) { 
				//usort($champ_row_option_plus, "cmp"); 
				$json_assurance = json_encode($champ_row_assurance);
				$supp_crochet = array("[", "]");
				$json_assurance = str_replace($supp_crochet, "", $json_assurance);
				//echo $json_all;
				$json_assurance = str_replace("},{", "}".$separator_champ."{", $json_assurance);			
			}
			else
			{
				$json_assurance ='';
			}
			//echo $json_assurance;

			
			//echo $json_all;
			//if ($json_dotation) $json_total= $json_dotation;
			//if ($json_participation && $json_dotation) { $json_total.= "#".$json_participation; } else { $json_total.= $json_participation; };
			//if (($json_questiondiverse && $json_dotation) || ($json_questiondiverse && $json_participation) ) { $json_total.= "#".$json_questiondiverse; } else { $json_total.= $json_questiondiverse; };
			//if ($json_total) { $json_total = $json_tarif_et_promo."@".$json_total; } else {  $json_total = $json_tarif_et_promo; };
			if ($json_all) { $json_all = $json_ages_limites.$separator_fonction.$json_tarif_et_promo.$separator_fonction.$json_option_plus.$separator_fonction.$json_assurance.$separator_fonction.$json_all; } else {  $json_all = $json_ages_limites.$separator_fonction.$json_tarif_et_promo.$separator_fonction.$json_option_plus.$separator_fonction.$json_assurance; };
			
			echo $json_all;
	
?>