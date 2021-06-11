<?php 
	session_start();
	require_once('connect_db.php');
	connect_db();
	require_once('functions.php');
	$update = $_GET['update'];
	$id_epreuve = $_GET['id_epreuve'];
	if ($_GET['idInternaute'] !=0 ) $idInternaute = $_GET['idInternaute']; else $idInternaute = $_SESSION["log_id"];
	if ($_GET['idInscriptionEpreuveInternaute'] !=0 ) $idInscriptionEpreuveInternaute = $_GET['idInscriptionEpreuveInternaute']; else $idInscriptionEpreuveInternaute = $_SESSION['idInscriptionEpreuveInternaute'];
	//echo $idInternaute;
	$date_et_heure_du_jour = strtotime(date('Y-m-d H:i:s'));
	$insc_perso=$_GET['insc_perso'];
	
	$all_champs = array();
	//CHAMPS PARTICIPATION			
	$query_participation  = "SELECT * FROM r_champssupparticipation_commune ";
	$query_participation .= "WHERE idEpreuve = ".$id_epreuve;
	$query_participation .= " ORDER BY ordre ASC";
	$champ_participation = array ('id' => 'idChampsSupParticipationCommune', 'nom' =>'nom', 'label'=>'label','type_champ'=>'type_champ','prix'=>'prix', 'qte'=>'qte','date_butoir'=>'date_butoir', 'obligatoire'=>'obligatoire', 'ordre'=>'ordre');
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
					$champ_row_participation['select'] = champ_inscrit_participation_commune_pre($id_epreuve, $champ_row_participation['id'],'value');
					//echo "xxx".$champ_row_participation['select'];
				}
				if ($update ==1) {
					$champ_row_participation['select'] = champ_inscrit_participation_commune($id_epreuve, $idInternaute, $idInscriptionEpreuveInternaute , $champ_row_participation['id'],'value');
					//echo "xxx".$champ_row_participation['select'];
				}
				//array_push($champs_participation, $champ_row_participation);
				array_push($all_champs, $champ_row_participation);
			}
			
			//print_r($all_champs);
			
			//echo "----</br>";
			//print_r($all_champs);
			$separator = select_code_separator ($id_epreuve);
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
			if ($json_all) { $json_all = $json_all; } else {  $json_all = ""; };
			
			echo $json_all;
	
?>