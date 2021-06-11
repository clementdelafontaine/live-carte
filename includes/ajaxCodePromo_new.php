<?php 
require_once('includes.php');
require_once('functions.php');

	$json_code_promo = array();

	
	//$key = array_search($_GET['code'], $_SESSION["code_promo"]);
	//if ($key) { $json_code_promo = array('etat' =>'KO','valeur' =>0); echo json_encode($json_code_promo); exit(); }
	
	$date_et_heure_du_jour = strtotime(date('Y-m-d H:i:s'));
	$action=$_POST['action'];
	$idrefpc = $_POST['idrefpc'];
	$id_parcours = $_POST['id_parcours'];
	$id_epreuve = $_POST['id_epreuve'];
	//$nb_insc = $_GET['nb_insc'];
	$parcours=$_POST['id_parcours'];
	$code_promo = strtoupper($_POST['code_promo']);
	//$_SESSION["code_promo"][] = $_GET['code'];
	
	if ($code_promo  =='') $code_promo ='code_promo_vide';
	$code_promo_label = array();
	$check = 0;
	//$supp_crochet = array("[", "]");
	
	
	
	if ($action==1) {
			$query  = "SELECT * ";
			$query .= "FROM r_epreuveparcourstarifpromo";
			//if (isset($id_parcours)) $query .=" WHERE idEpreuveParcours = ".$id_parcours." AND ( nb_fois_utilisable > 0 OR bon_dispo > 0) " ; else $query .=" WHERE ( nb_fois_utilisable > 0 OR bon_dispo > 0) ";
			//$query .=" WHERE idEpreuveParcours = ".$id_parcours;
			$query .=" WHERE idEpreuve = ".$id_epreuve;
			$query .=" AND idEpreuveParcours IN (".$parcours.") ";
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
				
					$type_promo = $row_code_promo['type_reduction'];
					$tarif_promo = $row_code_promo['prix_reduction'];
					$tarif_promo_pourcentage = $row_code_promo['pourcentage'];
					$id_tarif_promo = $row_code_promo['idEpreuveParcoursTarifPromo'];
					
			
				
					$check =1;
				
			}			
			if ($check ==1 ) 
			{
				//$json_code_promo = array('etat' =>'OK','valeur' =>$json_tarif_promo,'id' =>$id_json_tarif_promo, 'type'=>$json_type_promo);
				
				$participation = extract_champ_epreuve_internaute('participation',$idrefpc);
				$montant_inscription = extract_champ_epreuve_internaute('montant_inscription',$idrefpc);
				
				if ($type_promo=='pourcentage') //POURCENTAGE SUR LA TOTALITE
				{ 
					//montant_inscription x (1 - pourcentage/100)
					
					$montant_inscription_tmp = $montant_inscription * (1-($tarif_promo_pourcentage/100));
					$tarif_promo = $montant_inscription * ($tarif_promo_pourcentage/100);
					$montant_inscription = $montant_inscription_tmp;
				}
				elseif ($type_promo=='pourcentage_tout') //INSCRIPTION GRATUITE
				{ 
					$tarif_promo = $montant_inscription;
					$montant_inscription = $participation;
					
				}
				elseif ($type_promo=='tout') //INSCRIPTION GRATUITE + LES PARTICIPATIONS
				{ 
					$tarif_promo = $montant_inscription;
					$montant_inscription = 0;

				}
				else //REDUCTION DE X EUROS
				{
					
					$montant_inscription = $montant_inscription - $tarif_promo;
					
				}				
					
					//calcul du cout
					
					
					
					
					$query="UPDATE r_inscriptionepreuveinternaute SET ";
					$query .=" montant_inscription = ".$montant_inscription. ", ";
					$query .=" label_code_promo = '".$code_promo. "', ";
					$query .=" montant_code_promo = ".$tarif_promo. " ";
					//$query .=" participation = ".$participation. " ";
					$query .=" WHERE idInscriptionEpreuveInternaute = ".$idrefpc;
					//echo "SOLO :".$query;
					$result_query = $mysqli->query($query);	
					$json_code_promo = array('etat' =>'OK','action'=>1);
				
			}
			else
			{
				$json_code_promo = array('etat' =>'KO','valeur' =>0);
				
				
			}
	}
	elseif ($action==2)
	{
			$participation = extract_champ_epreuve_internaute('participation',$idrefpc);
			$montant_code_promo = extract_champ_epreuve_internaute('montant_code_promo',$idrefpc);
			$montant_inscription = extract_champ_epreuve_internaute('montant_inscription',$idrefpc);
			
			$montant_inscription = $montant_inscription + $montant_code_promo;
			
					$query="UPDATE r_inscriptionepreuveinternaute SET ";
					$query .=" montant_inscription = ".$montant_inscription. ", ";
					$query .=" label_code_promo = 'Aucun', ";
					$query .=" montant_code_promo = NULL ";
					//$query .=" participation = ".$participation. " ";
					$query .=" WHERE idInscriptionEpreuveInternaute = ".$idrefpc;
					//echo "SOLO :".$query;
					$result_query = $mysqli->query($query);
			
			
			$json_code_promo = array('etat' =>'OK','action'=>2);
			
			
			
		
		
		
	}
			echo json_encode($json_code_promo);

	
?>