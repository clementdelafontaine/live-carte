<?php 
	session_start();
	require_once('connect_db.php');
	connect_db();
	$json_code_promo = array();
	global $mysqli;
	
	//$key = array_search($_GET['code'], $_SESSION["code_promo"]);
	//if ($key) { $json_code_promo = array('etat' =>'KO','valeur' =>0); echo json_encode($json_code_promo); exit(); }
	
	$date_et_heure_du_jour = strtotime(date('Y-m-d H:i:s'));
	
	$id_parcours = $_GET['id_parcours'];
	
	$nb_insc = $_GET['nb_insc'];
	
	$code_promo = strtoupper($_GET['code']);
	//$_SESSION["code_promo"][] = $_GET['code'];
	
	if ($code_promo  =='' || $code_promo  =='*') $code_promo ='code_promo_vide';
	$code_promo_label = array();
	$check = 0;
	//$supp_crochet = array("[", "]");
	
	

	
			$query  = "SELECT * ";
			$query .= "FROM r_epreuveparcourstarifpromo";
			//if (isset($id_parcours)) $query .=" WHERE idEpreuveParcours = ".$id_parcours." AND ( nb_fois_utilisable > 0 OR bon_dispo > 0) " ; else $query .=" WHERE ( nb_fois_utilisable > 0 OR bon_dispo > 0) ";
			$query .=" WHERE idEpreuveParcours = ".$id_parcours;
			$query .=" AND ( nb_fois_utilisable > 0 OR bon_dispo > 0)";
			//$query .=" AND ";
			//echo $query .="  AND COLLATE UTF8_GENERAL_CI label like '".$code_promo."' OR label like '%|".$code_promo."|%' OR label like '%|".$code_promo."' OR label like '".$code_promo."|%' ";
			$query .="  AND CAST(label AS BINARY) rlike '[[:<:]]".$code_promo."[[:>:]]'";
			//$query .=" AND ";
			$query .=" AND etat = 'ACTIVE'";
			$query .= " AND dateDebutTarifPromo < NOW() ";
			$query .= " AND dateFinTarifPromo > NOW() ";
			//echo $query;
			//exit();
			$result_code_promo = $mysqli->query($query);
			//$row_code_promo = mysqli_fetch_array($result_code_promo);
			
			while (($row_code_promo = mysqli_fetch_array($result_code_promo)) != FALSE)
			{
				
				if ($row_code_promo['type_reduction']=='cout') 
				{ 
					//echo "xxx".$row_promo['prix_reduction'];
					$json_type_promo = $row_code_promo['type_reduction'];
					$json_tarif_promo = $row_code_promo['prix_reduction'];
					$id_json_tarif_promo = $row_code_promo['idEpreuveParcoursTarifPromo'];
				}
				elseif ($row_code_promo['type_reduction']=='pourcentage') 
				{ 
					//echo "xxx".$row_promo['prix_reduction'];
					$json_type_promo = $row_code_promo['type_reduction'];
					$json_tarif_promo = $row_code_promo['pourcentage'].'%';
					$id_json_tarif_promo = $row_code_promo['idEpreuveParcoursTarifPromo'];
				}
				elseif ($row_code_promo['type_reduction']=='pourcentage_tout') 
				{ 
					//echo "xxx".$row_promo['prix_reduction'];
					$json_type_promo = $row_code_promo['type_reduction'];
					$json_tarif_promo = $row_code_promo['pourcentage'].'%';
					$id_json_tarif_promo = $row_code_promo['idEpreuveParcoursTarifPromo'];
				}
				else
				{
					//echo "yyy".$row_promo['type_reduction'];
					$json_type_promo = $row_code_promo['type_reduction'];
					$json_tarif_promo= $row_code_promo['type_reduction'];
					$id_json_tarif_promo = $row_code_promo['idEpreuveParcoursTarifPromo'];
					
				}
				
			$check =1;
				
			}			
			if ($check ==1 ) 
			{
				$json_code_promo = array('etat' =>'OK','valeur' =>$json_tarif_promo,'id' =>$id_json_tarif_promo, 'type'=>$json_type_promo);
			}
			else
			{
				$json_code_promo = array('etat' =>'KO','valeur' =>0);
				
				
			}
			echo json_encode($json_code_promo);
			exit();
			
			
			
			
			if( $row_code_promo != FALSE ) {
			
				$code_promo_label = explode("|",$row_code_promo['label']);
				
				foreach ($code_promo_label as $label){
				
					if ( $label == $code_promo) {
					/*$query_insert = "INSERT INTO r_insc_internaute_code_promo_temp (idEpreuveParcours, nb_insc, idSession, Label, prix) VALUES (";
					$query_insert .= "".$id_parcours.",";
					$query_insert .= "".$nb_insc.",";
					$query_insert .= "'".$_SESSION['unique_id_session']."',";
					$query_insert .= "'".$label."',";
					$query_insert .= "".$row_code_promo['prix_reduction'].")";
					$result_insert= $mysqli->query($query_insert);*/
						$check =1;
					}
				}
			
				if ($check ==1 ) $json_code_promo = array('etat' =>'OK','valeur' =>$row_code_promo['prix_reduction']); else $json_code_promo = array('etat' =>'KO','valeur' =>0);
				//print_r($json_code_promo);
				//$json_code_promo = str_replace($supp_crochet, "", $json_code_promo);
				echo json_encode($json_code_promo);
			} else
			{
				$json_code_promo = array('etat' =>'KO','valeur' =>0);
				echo json_encode($json_code_promo);
			}
	
	
	exit();
	
	
	
	
	
	$json_total = $json_tarif_et_promo = $json_dotation = $json_participation = $json_questiondiverse = '';
	$json_tarif_promo = array();
	$json_tarif_promo['tarif'] = 0;
	$json_tarif_promo['promo'] = 0;
	//tarifs du parcours
	function tarifs($id_parcours) {		
	global $mysqli;
			
			$query  = "SELECT * ";
			$query .= "FROM r_epreuveparcourstarif";
			$query .=" WHERE idEpreuveParcours = ".$id_parcours;
			$query .=" ORDER by dateDebutTarif ASC";
			$result_tarifs = $mysqli->query($query);
			//$parcours=mysqli_fetch_array($result_parcours);
	
			return $result_tarifs;
	}
	
	$tab_tarif = tarifs($_GET['id_parcours']);
	
	
	while (($row_tarifs=mysqli_fetch_array($tab_tarif)) != FALSE)
	{ 
		if ( strtotime($row_tarifs['dateDebutTarif']) < $date_et_heure_du_jour AND $date_et_heure_du_jour < strtotime($row_tarifs['dateFinTarif'])) { 
			$json_tarif_promo['tarif'] =  $row_tarifs['tarif'] ;
					
		}											
	}
												
	//promo du parcours
	$reduction_promo_en_cours = 0;
	function promo($id_parcours) {		
	global $mysqli;
			
			$query  = "SELECT * ";
			$query .= "FROM r_epreuveparcourstarifpromo";
			$query .=" WHERE idEpreuveParcours = ".$id_parcours;
			$result_promo= $mysqli->query($query);
			//$parcours=mysqli_fetch_array($result_parcours);
	
			return $result_promo;
	}											
	$tab_promo = promo($_GET['id_parcours']);
	$row_promo=mysqli_fetch_array($tab_promo);
	if ($row_promo != FALSE) {
		
		if ( strtotime($row_promo['dateDebutTarifPromo']) < $date_et_heure_du_jour AND $date_et_heure_du_jour < strtotime($row_promo['dateFinTarifPromo'])) {
			$json_tarif_promo['promo'] = $row_promo['prix_reduction'];
			 
		}

	}
	//print_r($json_tarif_promo);
	$json_tarif_et_promo = json_encode($json_tarif_promo);
	//echo $json_tarif_et_promo;
	//$json = json_encode($_GET['id_parcours']);

	
	$query_dotation  = "SELECT * FROM r_champssupdotation ";
	$query_dotation .= "WHERE idEpreuveParcours = ".$_GET['id_parcours'];
	$query_dotation .= " ORDER BY ordre ASC";
	
	$champ_dotation = array ('id' => 'idChampsSupDotation', 'nom' =>'nom', 'label'=>'label', 'critere'=>'critere', 'type_champ'=>'type_champ','date_butoir'=>'date_butoir', 'obligatoire'=>'obligatoire', 'ordre'=>'ordre');
	$result_dotation = $mysqli->query($query_dotation);
	$champ_row_dotation = array();
	$champs_dotation = array();
	$all_champs = array();
	
	//function de triage des champs
	function cmp($a, $b)
	{
		global $mysqli;
		if ($a == $b) {
			return 0;
		}
		return ($a['ordre'] < $b['ordre']) ? -1 : 1;
	}
	
	
	
	$cpt = 0;
			while($row_dotation = mysqli_fetch_array($result_dotation))
			{

					$champ_row_dotation['champ'] = 'dotation';
					foreach ($champ_dotation as $k=>$i) {
						
						$champ_row_dotation[$k] = $row_dotation[$i];

					}
				$cpt++; 
				//array_push($champs_dotation, $champ_row_dotation);
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
	$champ_participation = array ('id' => 'idChampsSupParticipation', 'nom' =>'nom', 'label'=>'label','prix'=>'prix', 'qte'=>'qte','date_butoir'=>'date_butoir', 'obligatoire'=>'obligatoire', 'ordre'=>'ordre');
	$result_participation = $mysqli->query($query_participation);
	$champ_row_participation = array();
	$champs_participation = array();
	$cpt = 0;
			while($row_participation = mysqli_fetch_array($result_participation))
			{
				
					$champ_row_participation['champ'] = 'participation';
					foreach ($champ_participation as $k=>$i) {
						$champ_row_participation[$k] = $row_participation[$i];
						//echo $champ_row[$k]."<br>";
					}
				
				$cpt++; 
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
					foreach ($champ_questiondiverse as $k=>$i) {
						
						$champ_row_questiondiverse[$k] = $row_questiondiverse[$i];

					}
				$cpt++; 
				//array_push($champs_questiondiverse, $champ_row_questiondiverse);
				array_push($all_champs, $champ_row_questiondiverse);
			}
			


			//$data = array('ville'=>$ville);
			/*$json_questiondiverse = json_encode($champs_questiondiverse);
			$supp_crochet = array("[", "]");
			$json_questiondiverse = str_replace($supp_crochet, "", $json_questiondiverse);
			$json_questiondiverse = str_replace("},{", "}|{", $json_questiondiverse);*/
			
			//print_r($all_champs);
			usort($all_champs, "cmp");
			//echo "----</br>";
			//print_r($all_champs);
			$json_all = json_encode($all_champs);
			$supp_crochet = array("[", "]");
			$json_all = str_replace($supp_crochet, "", $json_all);
			$json_all = str_replace("},{", "}|{", $json_all);
			//echo $json_all;
			
			//if ($json_dotation) $json_total= $json_dotation;
			//if ($json_participation && $json_dotation) { $json_total.= "#".$json_participation; } else { $json_total.= $json_participation; };
			//if (($json_questiondiverse && $json_dotation) || ($json_questiondiverse && $json_participation) ) { $json_total.= "#".$json_questiondiverse; } else { $json_total.= $json_questiondiverse; };
			//if ($json_total) { $json_total = $json_tarif_et_promo."@".$json_total; } else {  $json_total = $json_tarif_et_promo; };
			if ($json_all) { $json_all = $json_tarif_et_promo."@".$json_all; } else {  $json_all = $json_tarif_et_promo; };
			
			echo $json_all;
	
?>