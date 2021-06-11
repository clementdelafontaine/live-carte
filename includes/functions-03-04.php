<?php  

function nb_jour_b2_date ($date1, $date2) {

		$datetime1 = new DateTime($date1);
		$datetime2 = new DateTime($date2);
		$interval = $datetime1->diff($datetime2);
		
		return abs($interval->format('%R%a'));
}

function nb_time_b2_date ($date1, $date2) {

		$date_retour=array();
		$datetime1 = new DateTime($date1);
		$datetime2 = new DateTime($date2);
		$since_start =  $datetime1->diff($datetime2);
		
		$heure = ($since_start->days * 24 * 60);
		$minutes = ($since_start->days * 24 * 60) + ($since_start->h * 60) + ($since_start->i);
		$secondes = ($since_start->days * 24 * 60 * 60) + ($since_start->h * 60 * 60) + ($since_start->i * 60 ) + $since_start->s;
		//$minutes += $since_start->h * 60;
		//$minutes += $since_start->i;
		
		return $date_retour = array('annee'=>$since_start->y, 'mois'=>$since_start->m, 'jour'=>$since_start->days, 'heure'=>$heure,'minute'=>$minutes,'seconde'=>$secondes);
}

function code_inscription($car) {
	$string = "";
	$chaine = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	srand((double)microtime()*1000000);
	for($i=0; $i<$car; $i++) {
	$string .= $chaine[rand()%strlen($chaine)];
	}
	return $string;
}

function code_inscription_chiffre($car) {
	$string = "";
	$chaine = "0123456789";
	srand((double)microtime()*1000000);
	for($i=0; $i<$car; $i++) {
	$string .= $chaine[rand()%strlen($chaine)];
	}
	return $string;
}

function code_separator($car) {
	$string = "";
	$chaine = "|@#aBcde12345";
	srand((double)microtime()*1000000);
	for($i=0; $i<$car; $i++) {
	$string .= $chaine[rand()%strlen($chaine)];
	}
	return $string;
}
function select_code_separator ($id_epreuve) {

		$query  = "SELECT * ";
		$query .= " FROM r_insc_champ_separator";
		$query .=" WHERE idEpreuve = ".$id_epreuve;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_array($result);

		if ($row != FALSE) { return $row['value_fonction']."+".$row['value_champ']."+".$row['value_parcours']; } else { return FALSE; }
}
function getXmlCoordsFromAdress($address)
{
	$coords=array();
	$base_url="http://maps.googleapis.com/maps/api/geocode/xml?";
	// ajouter &region=FR si ambiguité (lieu de la requete pris par défaut)
	$request_url = $base_url . "address=" . urlencode($address).'&sensor=false';
	$xml = simplexml_load_file($request_url) or die("url not loading");
	//print_r($xml);
	$coords['lat']=$coords['lon']='';
	$coords['status'] = $xml->status ;
	if($coords['status']=='OK')
	{
		$coords['lat'] = $xml->result->geometry->location->lat ;
		$coords['lon'] = $xml->result->geometry->location->lng ;
	}
	return $coords;
}
function besoin_certificat_medical($id_parcours) {		
	
			
			$query  = "SELECT certificatMedical ";
			$query .= "FROM r_epreuveparcours";
			$query .=" WHERE idEpreuveParcours = ".$id_parcours;
			$result_certif = $mysqli->query($query);
			$row_certif=mysqli_fetch_array($result_certif);
			
			return $row_certif['certificatMedical'];

}
function besoin_auto_parentale_parcours($id_parcours) {		
	
			
			$query  = "SELECT autoParentale ";
			$query .= "FROM r_epreuveparcours";
			$query .=" WHERE idEpreuveParcours = ".$id_parcours;
			$result_parentale = $mysqli->query($query);
			$row_parentale =mysqli_fetch_array($result_parentale );
			
			return $row_parentale['autoParentale'];

}

function type_certificat ($id_type_certificat) {
	

	$query  = "SELECT nomTypeEpreuve ";
	$query .= "FROM r_typeepreuve";
	$query .=" WHERE idTypeEpreuve = ".$id_type_certificat;
	$result = $mysqli->query($query);
	$row=mysqli_fetch_array($result);
	
	return $row['nomTypeEpreuve'];

}
function verif_certificat_organisateur ($idEpreuve, $idEpreuveParcours, $idInternaute) {
	
	$query  = "SELECT verif_certif, verif_auto_parentale ";
	$query .= "FROM r_inscriptionepreuveinternaute";
	$query .=" WHERE idEpreuve = ".$idEpreuve;
	$query .=" AND idEpreuveParcours = ".$idEpreuveParcours;
	$query .=" AND idInternaute = ".$idInternaute;
	//echo $query;
	$result = $mysqli->query($query);
	$row= mysqli_fetch_row($result);
	
	if(!empty($row[0])) {
		
	$tab_check = array('certif'=>$row[0],'auto_parentale'=>$row[1]);
	return $tab_check;
	}

}
function certif_medical_existe ($id_internaute,$query_update_certificat_fichier,$type_retour='fichier',$verif='non') { 
	
	$rep_fichiers_inscription  ="fichiers_insc".DIRECTORY_SEPARATOR;
	
	$query  = "SELECT nom_fichier ";
	$query .= "FROM r_epreuvefichier";
	$query .=" INNER JOIN r_internaute ON r_epreuvefichier.idEpreuveFichier = r_internaute.".$query_update_certificat_fichier;
	$query .=" WHERE r_internaute.idInternaute = ".$id_internaute;
	//echo $query;
	$result = $mysqli->query($query);
	$row= mysqli_fetch_row($result);
	//echo $row[0];
	if ($type_retour =='fichier')
	{
		if(!empty($row[0])) {
				if (substr ($row[0],-3) == 'pdf') { $class="fancybox-pdf"; $type_fichier = 'pdf'; }
				elseif(substr ($row[0],-3) == 'xls') { $class="fancybox-pdf"; $type_fichier = 'xls'; }
				elseif(substr ($row[0],-3) == 'gif') { $class="fancybox"; $type_fichier = 'gif'; }
				elseif(substr ($row[0],-3) == 'png') { $class="fancybox"; $type_fichier = 'png'; }
				else { $class="fancybox"; $type_fichier = 'jpg'; }
				
			$html = '<a data-fancybox-group="gallery" class="'.$class.'" href="'.$rep_fichiers_inscription.$row[0].'" target="_blank"><img src="images/'.$type_fichier.'.png" width="20px"></a>';
			return 	$html;				
		}
		else 
		{ 
			return $html =''; 
		}
	}
	else
	{
		if(!empty($row[0])) {

			if ($verif =='non') {
				return 'warning';
				exit();
				return $html = '<strong><i class="text-warning fa fa-file-pdf-o" data-original-title="Fourni mais non vérifié par l\'organisateur" data-placement="top" data-toggle="tooltip"></i></strong>';
			}
			else
			{
				return 'success';
				exit();
				return $html = '<i class="text-success fafa-file-pdf-o" data-original-title="Certificat validé" data-placement="top" data-toggle="tooltip"></i>';
			}
		
		}
		else 
		{ 
			if ($verif =='oui') {
				return 'success';
				exit();
				return $html = '<i class="text-success fafa-file-pdf-o" data-original-title="Certificat validé" data-placement="top" data-toggle="tooltip"></i>';
			
			} else {
				return 'danger';
				exit();
				return $html ='<i class="text-danger fa fa-file-pdf-o" data-original-title="Non fourni - cliquez à droite pour le fournir" data-placement="top" data-toggle="tooltip"></i> <a href="#" onclick="$(\'#internaute_'.$id_internaute.'\').show();$(\'#internaute_'.$id_internaute.'\').focus();return false;"><i class="text-danger fa fa-plus" data-original-title="Cliquez ICI pour fournir votre certificat" data-placement="top" data-toggle="tooltip"></a></i>'; 
			}
		}
		
		
	}
	
}

function besoin_auto_parentale ($id_parcours,$date_naissance) {
	
	$query  = "SELECT horaireDepart ";
	$query .= "FROM r_epreuveparcours";
	$query .=" WHERE idEpreuveParcours = ".$id_parcours;
	$result_parcours = $mysqli->query($query);

	$horairedepart = mysql_result($result_parcours , 0, 0);
	//$row=mysqli_fetch_array($result_parcours);
	$date_depart_course = strtotime($horairedepart);
	$date_mineur = date("Y-m-d",strtotime('-18 years',$date_depart_course));
	
	$nb_jour_18_ans = nb_time_b2_date($horairedepart,$date_mineur);
	$nb_jour_insc = nb_time_b2_date($horairedepart,$date_naissance);
	
	$nb_jour_insc['jour']. "-". $nb_jour_18_ans['jour'];
							
	if ( $nb_jour_insc['jour'] < $nb_jour_18_ans['jour'] && !empty($date_naissance)) return "oui"; else return "non";					

}
	
function auto_parentale_existe ($id_internaute, $id_parcours) {
	
	$rep_fichiers_inscription  ="fichiers_insc".DIRECTORY_SEPARATOR;
	
		$query  = "SELECT nom_fichier ";
		$query .= "FROM r_epreuvefichier";
		$query .=" INNER JOIN r_auto_parentale ON r_epreuvefichier.idEpreuveFichier = r_auto_parentale.idEpreuveFichier";
		$query .=" WHERE r_auto_parentale.idInternaute = ".$id_internaute;
		$query .=" AND r_auto_parentale.idEpreuveParcours = ".$id_parcours;
		//echo $query;
		//exit();
		$result = $mysqli->query($query);
		
		$row= mysqli_fetch_row($result);

		if(!empty($row[0])) {
			if (substr ($row[0],-3) == 'pdf') { $class="fancybox-pdf"; $type_fichier = 'pdf'; }
			elseif(substr ($row[0],-3) == 'xls') { $class="fancybox-pdf"; $type_fichier = 'xls'; }
			elseif(substr ($row[0],-3) == 'gif') { $class="fancybox"; $type_fichier = 'gif'; }
			elseif(substr ($row[0],-3) == 'png') { $class="fancybox"; $type_fichier = 'png'; }
			else { $class="fancybox"; $type_fichier = 'jpg'; }
			
			$html = '<a data-fancybox-group="gallery" class="'.$class.'" href="'.$rep_fichiers_inscription.$row[0].'" target="_blank"><img src="images/'.$type_fichier.'.png" width="20px"></a>';
		return 	$html;				
	} else { return $html =''; }

}
	
function modele_parentale_existe ($id_epreuve) {
	
	$rep_modele_auto_parentale  ="admin/fichiers_epreuves".DIRECTORY_SEPARATOR;
	
		$query  = "SELECT nom_fichier ";
		$query .= "FROM r_epreuvefichier";
		$query .=" INNER JOIN r_epreuve ON r_epreuvefichier.idEpreuveFichier = r_epreuve.fichier_auto_parentale";
		$query .=" WHERE r_epreuve.idEpreuve = ".$id_epreuve;
		//echo $query;
		//exit();
		$result = $mysqli->query($query);
		
		$row= mysqli_fetch_row($result);

		if(!empty($row[0])) {
			if (substr ($row[0],-3) == 'pdf') { $class="fancybox-pdf"; $type_fichier = 'pdf'; }
			elseif(substr ($row[0],-3) == 'xls') { $class="fancybox-pdf"; $type_fichier = 'xls'; }
			elseif(substr ($row[0],-3) == 'gif') { $class="fancybox"; $type_fichier = 'gif'; }
			elseif(substr ($row[0],-3) == 'png') { $class="fancybox"; $type_fichier = 'png'; }
			else { $class="fancybox"; $type_fichier = 'jpg'; }
			
			$html = '<a data-fancybox-group="gallery" class="'.$class.'" href="'.$rep_modele_auto_parentale.$row[0].'" target="_blank"><img src="images/'.$type_fichier.'.png" width="20px"></a>';
		return 	$html;				
	} else { return $html =''; }

}

function questiondivers_file_existe ($id_fichier,$type='insc_qd_file') {
	
	$rep_fichiers_inscription  ="fichiers_insc".DIRECTORY_SEPARATOR;
	
		$query  = "SELECT nom_fichier ";
		$query .= "FROM r_epreuvefichier";
		$query .=" WHERE idEpreuveFichier = ".$id_fichier;
		$query .=" AND type = '".$type."'";
		//echo $query;
		//exit();
		$result = $mysqli->query($query);
		$row= mysqli_fetch_row($result);

		if(!empty($row[0])) {
			
			if (substr ($row[0],-3) == 'pdf') { $class="fancybox-pdf"; $type_fichier = 'pdf'; }
			elseif(substr ($row[0],-3) == 'xls') { $class="fancybox-pdf"; $type_fichier = 'xls'; }
			elseif(substr ($row[0],-3) == 'gif') { $class="fancybox"; $type_fichier = 'gif'; }
			elseif(substr ($row[0],-3) == 'png') { $class="fancybox"; $type_fichier = 'png'; }
			else { $class="fancybox"; $type_fichier = 'jpg'; }
			
			$html = '<a data-fancybox-group="gallery" class="'.$class.'" href="/'.$rep_fichiers_inscription.$row[0].'" target="_blank"><img src="images/'.$type_fichier.'.png" width="20px"></a>';
		return 	$html;				
	} else { return $html =''; }

}

function datefr2en($mydate,$wtime=1){
   
   if ($wtime == 0) {
		@list($date,$horaire)=explode(' ',$mydate);
		@list($jour,$mois,$annee)=explode('/',$date);
		@list($heure,$minute)=explode(':',$horaire);
		return @date('Y-m-d H:i:s',strtotime($mois."/".$jour."/".$annee." ".$heure.":".$minute));
   }
   else
   {
		@list($jour,$mois,$annee)=explode('/',$mydate);
		return @date('Y-m-d',strtotime($mois."/".$jour."/".$annee)); 
	   
   }
}

function dateen2fr($mydate,$wtime=0){
   
   if ($wtime == 0) {
		
		@list($date,$horaire)=explode(' ',$mydate);
		@list($jour,$mois,$annee)=explode('-',$date);
		@list($heure,$minute,$seconde)=explode(':',$horaire);
		return @date('d-m-Y H:i:s',strtotime($mydate));
   }
   else
   {
		@list($jour,$mois,$annee)=explode('-',$date);
		@list($heure,$minute,$seconde)=explode(':',$horaire);
		return @date('d/m/Y',strtotime($mydate)); 
	   
   }
}


function info_participant_maj($id_internaute) {
	
		$query  = "SELECT * FROM r_internaute "; 
		$query .= " WHERE idInternaute = ".$id_internaute;

		//echo $query;
		$result_info = $mysqli->query($query);
		$row_info=mysqli_fetch_array($result_info);
		
		if ($row_info != FALSE ) {

				if (isset($row_info["peremption_ski"]) && $row_info["peremption_ski"] != '0000-00-00') {
					$date_certificat = date("d/m/Y",strtotime($row_info["peremption_ski"]));
					$fichier_certificat = 'fichier_ski';
					$type_certificat = type_certificat(4);
					$_SESSION["peremption_cert"] = $row_info["peremption_ski"];
					$_SESSION["fichier_cert"] = 'fichier_ski';
					$_SESSION["type_cert"] = 4;
				}
				elseif (isset($row_info["peremption_tri"]) && $row_info["peremption_tri"] != '0000-00-00') {
					$date_certificat = date("d/m/Y",strtotime($row_info["peremption_tri"]));
					$fichier_certificat = 'fichier_tri';
					$type_certificat = type_certificat(1);
					$_SESSION["peremption_cert"] = $row_info["peremption_tri"];
					$_SESSION["fichier_cert"] = 'fichier_tri';
					$_SESSION["type_cert"] = 1;
					
				}
				elseif (isset($row_info["peremption_vel"]) && $row_info["peremption_vel"] != '0000-00-00') {
					$date_certificat = date("d/m/Y",strtotime($row_info["peremption_vel"]));
					$fichier_certificat = 'fichier_vel';
					$type_certificat = type_certificat(2);
					$_SESSION["peremption_cert"] = $row_info["peremption_vel"];
					$_SESSION["fichier_cert"] = 'fichier_vel';
					$_SESSION["type_cert"] = 2;
					
				}				
				elseif (isset($row_info["peremption_cap"]) && $row_info["peremption_cap"] != '0000-00-00') {
					$date_certificat = date("d/m/Y",strtotime($row_info["peremption_cap"]));
					$fichier_certificat = 'fichier_cap';
					$type_certificat = type_certificat(3);
					$_SESSION["peremption_cert"] = $row_info["peremption_cap"];
					$_SESSION["fichier_cert"] = 'fichier_cap';
					$_SESSION["type_cert"] = 3;
				}			
				else {
					$date_certificat = '';
					$fichier_certificat ='fichier_cap';
					$type_certificat = type_certificat(3);
					$_SESSION["peremption_cert"] = '';
					$_SESSION["fichier_cert"] = 'fichier_cap';
					$_SESSION["type_cert"] = 3;
				}			
			
				$_SESSION["log_id"] = $row_info["idInternaute"];
				$_SESSION["prenomInternaute"] = $row_info["prenomInternaute"];
				$_SESSION["nomInternaute"] = $row_info["nomInternaute"];
				$_SESSION["sexeInternaute"] = $row_info["sexeInternaute"];
				$_SESSION["emailInternaute"] = $row_info["emailInternaute"];
				$_SESSION["naissanceInternaute"] = $row_info["naissanceInternaute"];
				$_SESSION["clubInternaute"] = $row_info["clubInternaute"];
				$_SESSION["villeInternaute"] = $row_info["villeInternaute"];
				$_SESSION["telephone"] = $row_info["telephone"];
				$_SESSION["log_log"] = $_POST['compte'] ;
				$_SESSION["typeInternaute"] = $row_info["typeInternaute"];
				$_SESSION["log_coureur"] = $row_info["coureur"];
				$_SESSION["log_organisateur"] = $row_info["organisateur"];
				$_SESSION["log_fournisseur"] = $row_info["fournisseur"];
				$_SESSION["avatar"] = $row_info["avatar"];
				$_SESSION["adresseInternaute"] = $row_info["adresseInternaute"];
				$_SESSION["cpInternaute"] = $row_info["cpInternaute"];
				$_SESSION["villeLatitude"] = $row_info["villeLatitude"];
				$_SESSION["villeLongitude"] = $row_info["villeLongitude"];
				$_SESSION["paysInternaute"] = $row_info["paysInternaute"];
				
				if (isset($row_info["peremption_ski"]) && $row_info["peremption_ski"] != '0000-00-00') {
					$_SESSION["peremption_cert"] = $row_info["peremption_ski"];
					$_SESSION["fichier_cert"] = 'fichier_ski';
					$_SESSION["type_cert"] = 4;
				}elseif (isset($row_info["peremption_tri"]) && $row_info["peremption_tri"] != '0000-00-00') {
					$_SESSION["peremption_cert"] = $row_info["peremption_tri"];
					$_SESSION["fichier_cert"] = 'fichier_tri';
					$_SESSION["type_cert"] = 1;
				}elseif (isset($row_info["peremption_vel"]) && $row_info["peremption_vel"] != '0000-00-00') {
					$_SESSION["peremption_cert"] = $row_info["peremption_vel"];
					$_SESSION["fichier_cert"] = 'fichier_vel';
					$_SESSION["type_cert"] = 2;
				}elseif (isset($row_info["peremption_cap"]) && $row_info["peremption_cap"] != '0000-00-00') {
					$_SESSION["peremption_cert"] = $row_info["peremption_cap"];
					$_SESSION["fichier_cert"] = 'fichier_cap';
					$_SESSION["type_cert"] = 3;
				}else {
					$_SESSION["peremption_cert"] = '';
					$_SESSION["fichier_cert"] = 'fichier_cap';
					$_SESSION["type_cert"] = 3;
				}
				//$_SESSION['unique_id_session'] = md5(uniqid());
				//$info_participant = array('nomInternaute'=>'|ok|');
		}
}
function extract_champ_remboursement($champ, $id_remboursement) {

		$query  = "SELECT ".$champ;
		$query .= " FROM r_inscriptionremboursement";
		$query .=" WHERE idInscriptionRemboursement = ".$id_remboursement;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; }
}

function extract_champ_parcours ($champ, $id_parcours) {

		$query  = "SELECT ".$champ;
		$query .= " FROM r_epreuveparcours";
		$query .=" WHERE idEpreuveParcours = ".$id_parcours;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; }
}

function extract_champ_epreuve ($champ, $id_epreuve,$and ='') {

		$query  = "SELECT ".$champ;
		$query .= " FROM r_epreuve";
		$query .=" WHERE idEpreuve = ".$id_epreuve." ";
		$query .= $and;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; }
}

function extract_champ_tarif ($champ, $idEpreuveParcoursTarif) {

		$query  = "SELECT ".$champ;
		$query .= " FROM r_epreuveparcourstarif";
		$query .=" WHERE idEpreuveParcoursTarif = ".$idEpreuveParcoursTarif;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; }
}

function extract_champ_id_epreuve_internaute ($champ, $id) {

		$query  = "SELECT ".$champ;
		$query .= " FROM r_inscriptionepreuveinternaute";
		$query .=" WHERE idInscriptionEpreuveInternaute = ".$id;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; }
}

function extract_champ_internaute ($champ, $id_internaute) {

		$query  = "SELECT ".$champ;
		$query .= " FROM r_internaute";
		$query .=" WHERE idInternaute = ".$id_internaute;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; }
}
function champ_inscrit_dotation ($idEpreuveParcours, $idInternaute, $lastid_epreuve_inscription, $idChampsSupDotation,$champ) {
	
		$query  = "SELECT ".$champ." ";
		$query .= "FROM r_insc_champssupdotation ";
		$query .="WHERE idEpreuveParcours = ".$idEpreuveParcours;
		$query .=" AND idInternaute = ".$idInternaute;
		$query .= " AND idInscriptionEpreuveInternaute=".$lastid_epreuve_inscription;
		$query .=" AND idChampsSupDotation = ".$idChampsSupDotation;
		
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; } else { return ''; }
	
}

function champ_inscrit_dotation_pre ($idEpreuveParcours, $idChampsSupDotation,$champ) {
	
		$query  = "SELECT ".$champ." ";
		$query .= "FROM r_insc_champssupdotation_pre ";
		$query .="WHERE idEpreuveParcours = ".$idEpreuveParcours;
		$query .=" AND idChampsSupDotation = ".$idChampsSupDotation;
		
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; } else { return ''; }
	
}

function champ_inscrit_questiondiverse ($idEpreuveParcours, $idInternaute, $lastid_epreuve_inscription, $idChampsSupQuestionDiverse,$champ) {
	
		$query  = "SELECT ".$champ." ";
		$query .= "FROM r_insc_champssupquestiondiverse ";
		$query .="WHERE idEpreuveParcours = ".$idEpreuveParcours;
		$query .=" AND idInternaute = ".$idInternaute;
		$query .= " AND idInscriptionEpreuveInternaute=".$lastid_epreuve_inscription;
		$query .=" AND idChampsSupQuestionDiverse = ".$idChampsSupQuestionDiverse;
		//if ($idChampsSupQuestionDiverse == 88) echo $query;
		//echo $query;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; } else { return ''; }
	
}

function champ_inscrit_questiondiverse_pre ($idEpreuveParcours, $idChampsSupQuestionDiverse,$champ) {
	
		$query  = "SELECT ".$champ." ";
		$query .= "FROM r_insc_champssupquestiondiverse_pre ";
		$query .="WHERE idEpreuveParcours = ".$idEpreuveParcours;
		$query .=" AND idChampsSupQuestionDiverse = ".$idChampsSupQuestionDiverse;
		//if ($idChampsSupQuestionDiverse == 88) echo $query;
		//echo $query;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; } else { return ''; }
	
}

function champ_inscrit_participation ($idEpreuveParcours, $idInternaute, $lastid_epreuve_inscription, $idChampsSupParticipation,$champ) {
	
		$query  = "SELECT ".$champ." ";
		$query .= "FROM r_insc_champssupparticipation ";
		$query .="WHERE idEpreuveParcours = ".$idEpreuveParcours;
		$query .=" AND idInternaute = ".$idInternaute;
		$query .= " AND idInscriptionEpreuveInternaute=".$lastid_epreuve_inscription;
		$query .=" AND idChampsSupParticipation = ".$idChampsSupParticipation;
		//echo $query;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; } else { return ''; }
	
}

function champ_inscrit_participation_pre ($idEpreuveParcours, $idChampsSupParticipation,$champ) {
	
		$query  = "SELECT ".$champ." ";
		$query .= "FROM r_insc_champssupparticipation_pre ";
		$query .="WHERE idEpreuveParcours = ".$idEpreuveParcours;
		$query .=" AND idChampsSupParticipation = ".$idChampsSupParticipation;
		//echo $query;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; } else { return ''; }
	
}

function champ_inscrit_dotation_commune ($idEpreuve, $idInternaute, $lastid_epreuve_inscription, $idChampsSupDotation,$champ) {
	
		$query  = "SELECT ".$champ." ";
		$query .= "FROM r_insc_champssupdotation_commune ";
		$query .="WHERE idEpreuve = ".$idEpreuve;
		$query .=" AND idInternaute = ".$idInternaute;
		$query .= " AND idInscriptionEpreuveInternaute=".$lastid_epreuve_inscription;
		$query .=" AND idChampsSupDotation = ".$idChampsSupDotation;
		//echo $query;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; } else { return ''; }
	
}

function champ_inscrit_dotation_commune_pre ($idEpreuve, $idChampsSupParticipation,$champ) {
	
		$query  = "SELECT ".$champ." ";
		$query .= "FROM r_insc_champssupdotation_commune_pre ";
		$query .="WHERE idEpreuve = ".$idEpreuve;
		$query .=" AND idChampsSupDotation = ".$idChampsSuDotation;
		//echo $query;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; } else { return ''; }
	
}

function champ_inscrit_participation_commune ($idEpreuve, $idInternaute, $lastid_epreuve_inscription, $idChampsSupParticipation,$champ) {
	
		$query  = "SELECT ".$champ." ";
		$query .= "FROM r_insc_champssupparticipation_commune ";
		$query .="WHERE idEpreuve = ".$idEpreuve;
		$query .=" AND idInternaute = ".$idInternaute;
		$query .= " AND idInscriptionEpreuveInternaute=".$lastid_epreuve_inscription;
		$query .=" AND idChampsSupParticipation = ".$idChampsSupParticipation;
		//echo $query;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; } else { return ''; }
	
}

function champ_inscrit_participation_commune_pre ($idEpreuve, $idChampsSupParticipation,$champ) {
	
		$query  = "SELECT ".$champ." ";
		$query .= "FROM r_insc_champssupparticipation_commune_pre ";
		$query .="WHERE idEpreuve = ".$idEpreuve;
		$query .=" AND idChampsSupParticipation = ".$idChampsSupParticipation;
		//echo $query;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; } else { return ''; }
	
}

function champ_inscrit_questiondiverse_commune ($idEpreuve, $idInternaute, $lastid_epreuve_inscription, $idChampsSupQuestionDiverse,$champ) {
	
		$query  = "SELECT ".$champ." ";
		$query .= "FROM r_insc_champssupquestiondiverse_commune  ";
		$query .="WHERE idEpreuve = ".$idEpreuve;
		$query .=" AND idInternaute = ".$idInternaute;
		$query .= " AND idInscriptionEpreuveInternaute=".$lastid_epreuve_inscription;
		$query .=" AND idChampsSupQuestionDiverse = ".$idChampsSupQuestionDiverse;
		//if ($idChampsSupQuestionDiverse == 88) echo $query;
		//echo $query;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; } else { return ''; }
	
}

function champ_inscrit_questiondiverse_commune_pre ($idEpreuve, $idChampsSupQuestionDiverse,$champ) {
	
		$query  = "SELECT ".$champ." ";
		$query .= "FROM r_insc_champssupquestiondiverse_commune _pre ";
		$query .="WHERE idEpreuve = ".$idEpreuve;
		$query .=" AND idChampsSupQuestionDiverse = ".$idChampsSupQuestionDiverse;
		//if ($idChampsSupQuestionDiverse == 88) echo $query;
		//echo $query;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; } else { return ''; }
	
}
function extract_champ_participation ($idEpreuveParcours, $idChampsSupParticipation,$champ) {
	
		$query  = "SELECT ".$champ." ";
		$query .= "FROM r_champssupparticipation ";
		$query .=" WHERE idEpreuveParcours = ".$idEpreuveParcours;
		$query .=" AND idChampsSupParticipation = ".$idChampsSupParticipation;
		//echo $query;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; } else { return ''; }
	
}

function extract_champ_questiondiverse_internaute ($idEpreuveParcours, $idChampsSupQuestionDiverse,$idInternaute,$champ) {
	
		$query  = "SELECT ".$champ." ";
		$query .= "FROM r_insc_champssupquestiondiverse ";
		$query .="WHERE idEpreuveParcours = ".$idEpreuveParcours;
		$query .=" AND idChampsSupQuestionDiverse = ".$idChampsSupQuestionDiverse;
		$query .=" AND idInternaute = ".$idInternaute;
		//echo $query;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);
		//echo "aaa".$row[0];

		if ($row != FALSE) { return $row[0]; } else { return ''; }
	
}
function extract_champ_participation_commune ($idEpreuve, $idChampsSupParticipation,$champ) {
	
		$query  = "SELECT ".$champ." ";
		$query .= "FROM r_champssupparticipation_commune ";
		$query .="WHERE idEpreuve = ".$idEpreuve;
		$query .=" AND idChampsSupParticipationCommune = ".$idChampsSupParticipation;
		//echo $query;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; } else { return ''; }
	
}

function update_qte_champ_dotation_select_commune ($id_champ,$critere_select,$action) {
	
		$query  = "SELECT critere ";
		$query .= "FROM r_champssupdotation_commune";
		$query .=" WHERE idChampsSupDotationCommune = ".$id_champ;
		//echo $query;
		$cpt=-1;
		if ($action == 'plus') $cpt=1;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);
		$value_tmp = explode(';',$row[0]);
		//print_r($value_tmp);
		$first= FALSE;
		foreach ($value_tmp as $nom) {
			
			$critere_temp = explode('(',$nom);
			$value = str_replace(")","", $critere_temp[1]);
			$critere = $critere_temp[0];
			
			if ($critere == $critere_select) { 
				if ($value != '*') {
					$value = $value + $cpt; 
				}
			}
			if ($first==TRUE) $string .=";";
			//echo $critere." - ".$value."</br>";
			if ($value == '') $string .= $critere; else $string .= $critere."(".$value.")";
			
			 $first= TRUE;
		}
		$query  = "UPDATE r_champssupdotation_commune SET ";
		$query .= " critere ='".addslashes($string)."'";
		$query .= " WHERE idChampsSupDotationCommune=".$id_champ;
		$result_query = $mysqli->query($query);
		//$nb_rows_affected = mysqli_affected_rows();
	
}

function update_qte_champ_dotation_select ($id_champ,$critere_select,$action) {
	
		$query  = "SELECT critere ";
		$query .= "FROM r_champssupdotation";
		$query .=" WHERE idChampsSupDotation = ".$id_champ;
		//echo $query;
		$cpt=-1;
		if ($action == 'plus') $cpt=1;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);
		$value_tmp = explode(';',$row[0]);
		//print_r($value_tmp);
		$first= FALSE;
		foreach ($value_tmp as $nom) {
			
			$critere_temp = explode('(',$nom);
			$value = str_replace(")","", $critere_temp[1]);
			$critere = $critere_temp[0];
			
			if ($critere == $critere_select) { 
				if ($value != '*') {
					$value = $value + $cpt; 
				}
			}
			if ($first==TRUE) $string .=";";
			//echo $critere." - ".$value."</br>";
			if ($value == '') $string .= $critere; else $string .= $critere."(".$value.")";
			
			 $first= TRUE;
		}
		$query  = "UPDATE r_champssupdotation SET ";
		$query .= " critere ='".addslashes($string)."'";
		$query .= " WHERE idChampsSupDotation=".$id_champ;
		$result_query = $mysqli->query($query);
		//$nb_rows_affected = mysqli_affected_rows();
	
}
function recup_reglement_epreuve ($id_epreuve) {
	$rep_fichiers_reglement  ="admin/fichiers_epreuves".DIRECTORY_SEPARATOR;
	
		$query  = "SELECT reglement, nomEpreuve ";
		$query .= "FROM r_epreuve";
		$query .=" WHERE idEpreuve = ".$id_epreuve;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);
		list($type,$value) = explode("|||", $row[0]);
		//echo $type."-".$value;
		if ($type == 1) $html = ' ( <a href="'.$value.'" target="_blank"><em>Lire sur le site de l\'organisateur</em></a> )';
		elseif ($type == 2) { 
		
			//$html = '<div><a class="reglement_modal" href="#reglement" class="btn btn-primary">Réglement de l\'épreuve</a></div>';
			/*
			$html .='	<div class="modal" id="epre_reglement">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header" style="text-align: right;">
										Réglement de l\'epreuve '.$row[1].'
									</div>
								<div class="modal-body">'.$value.'</div>
								<div class="modal-footer">
									<a href="#" class="btn btn-primary" data-dismiss="modal">Fermer</a>
								</div>
							</div>
						</div>
					</div>';
				*/
			$html = '	<a class="reglement_epreuve_modal btn btn-primary btn-xs pull-right" href="#reglement_epreuve" >Réglement de l\'épreuve</a>
										<div class="col-md-12" id="reglement_epreuve" style="display:none">'.$value.'</div>';															
					
		}
		else
		{
		
			$query_fichier  = "SELECT nom_fichier ";
			$query_fichier  .= "FROM r_epreuvefichier";
			$query_fichier  .=" WHERE idEpreuveFichier = ".$value;
			$query_fichier  .=" AND type = 'docs_reglement'";
			$result_fichier = $mysqli->query($query_fichier);
			$row_fichier=mysqli_fetch_row($result_fichier);
			
			/*if (substr ($row[0],-3) == 'pdf') { $class="fancybox-pdf"; $type_fichier = 'pdf'; }
			elseif(substr ($row[0],-3) == 'xls') { $class="fancybox-pdf"; $type_fichier = 'xls'; }
			elseif(substr ($row[0],-3) == 'gif') { $class="fancybox"; $type_fichier = 'gif'; }
			elseif(substr ($row[0],-3) == 'png') { $class="fancybox"; $type_fichier = 'png'; }
			else { $class="fancybox"; $type_fichier = 'jpg'; }*/
			
			$html = '<a class="fancybox-pdf btn btn-primary btn-xs pull-right" href="'.$rep_fichiers_reglement.$row_fichier[0].'" id="fancybox-pdf" >Réglement de l\'épreuve</a>';
			
			//$html = '	<a class="reglement_epreuve_modal btn btn-primary btn-xs pull-right" href="#reglement_epreuve" >Réglement de l\'épreuve</a>
										//<div class="col-md-12" id="reglement_epreuve" style="display:none">'.$value.'</div>';
			
		}
		return $html;
}

function cmp($a, $b)
{
	if ($a == $b) {
		return 0;
	}
	return ($a['ordre'] < $b['ordre']) ? -1 : 1;
}

function recup_champ_dotation_inscrit ($idEpreuve, $idEpreuveParcours, $idInternaute, $idInscriptionEpreuveInternaute) {
	$all_champs = array();
	$champ_row_dotation = array();
	
	$champ_dotation = array ('id' => 'idChampsSupDotation', 'label'=>'label', 'value'=>'value','ordre'=>'ordre','type_champ'=>'type_champ');
	$champ_participation = array ('id' => 'idChampsSupParticipation', 'label'=>'label', 'value'=>'value','ordre'=>'ordre','prix_total'=>'prix_total');
	$champ_questiondiverse = array ('id' => 'idChampsSupQuestionDiverse', 'label'=>'label', 'value'=>'value','ordre'=>'ordre','type_champ'=>'type_champ');
	
	$query  = "SELECT rcd.idChampsSupDotation, label, value, ordre, type_champ ";
	$query .= "FROM r_champssupdotation as rcd ";
	$query .= "INNER JOIN r_insc_champssupdotation as ricd ON rcd.idChampsSupDotation = ricd.idChampsSupDotation ";
	$query .= "INNER JOIN r_inscriptionepreuveinternaute as riei ON ricd.idInscriptionEpreuveInternaute = riei.idInscriptionEpreuveInternaute ";
	$query .= "WHERE ricd.idEpreuve = ".$idEpreuve." ";
	$query .= "AND ricd.idEpreuveParcours = ".$idEpreuveParcours." ";
	//$query .= "AND ricd.idInternaute = ".$idInternaute." ";
	$query .= "AND ricd.idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute." ";
	$query .= "ORDER BY rcd.ordre ASC";
	//echo $query;
	$result = $mysqli->query($query);
	$champ_row_dotation['champ'] ='dotation';
	while (($row=mysqli_fetch_array($result)) != FALSE)
	{
		foreach ($champ_dotation as $k=>$i) {
			
			$champ_row_dotation[$k] = $row[$i];
			

		}
		if ( $champ_row_dotation['type_champ'] == 'CASE') {
						
			$champ_row_dotation['value'] = str_replace('_+_',';',$champ_row_dotation['value']);
		
		}
	array_push($all_champs, $champ_row_dotation);
	}


	$champ_row_participation= array();
	
	$query  = "SELECT rcd.idChampsSupParticipation, label, value, ordre, prix_total ";
	$query .= "FROM r_champssupparticipation as rcd ";
	$query .= "INNER JOIN r_insc_champssupparticipation as ricd ON rcd.idChampsSupParticipation = ricd.idChampsSupParticipation ";
	$query .= "INNER JOIN r_inscriptionepreuveinternaute as riei ON ricd.idInscriptionEpreuveInternaute = riei.idInscriptionEpreuveInternaute ";
	$query .= "WHERE ricd.idEpreuve = ".$idEpreuve." ";
	$query .= "AND ricd.idEpreuveParcours = ".$idEpreuveParcours." ";
	//$query .= "AND ricd.idInternaute = ".$idInternaute." ";
	$query .= "AND ricd.idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute." ";
	$query .= "ORDER BY rcd.ordre ASC";
	//echo $query;
	$result = $mysqli->query($query);
	$champ_row_participation['champ'] ='participation';
	while (($row=mysqli_fetch_array($result)) != FALSE)
	{
		foreach ($champ_participation as $k=>$i) {
			
			$champ_row_participation[$k] = $row[$i];

		}
	array_push($all_champs, $champ_row_participation);
	}
	//print_r($all_champs);

	$query  = "SELECT rcd.idChampsSupQuestionDiverse, label, value, ordre, type_champ ";
	$query .= "FROM r_champssupquestiondiverse as rcd ";
	$query .= "INNER JOIN r_insc_champssupquestiondiverse as ricd ON rcd.idChampsSupQuestionDiverse = ricd.idChampsSupQuestionDiverse ";
	$query .= "INNER JOIN r_inscriptionepreuveinternaute as riei ON ricd.idInscriptionEpreuveInternaute = riei.idInscriptionEpreuveInternaute ";
	$query .= "WHERE ricd.idEpreuve = ".$idEpreuve." ";
	$query .= "AND ricd.idEpreuveParcours = ".$idEpreuveParcours." ";
	//$query .= "AND ricd.idInternaute = ".$idInternaute." ";
	$query .= "AND ricd.idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute." ";
	$query .= "ORDER BY rcd.ordre ASC";
	//echo $query;
	$result = $mysqli->query($query);
	$champ_row_questiondiverse['champ'] ='questiondiverse';
	while (($row=mysqli_fetch_array($result)) != FALSE)
	{
		foreach ($champ_questiondiverse as $k=>$i) {
			
			$champ_row_questiondiverse[$k] = $row[$i];

		}
		
		if ( $champ_row_questiondiverse['type_champ'] == 'FILE') {
						
			$champ_row_questiondiverse['value'] = questiondivers_file_existe($champ_row_questiondiverse['value']);
		
		}
		
		if ( $champ_row_questiondiverse['type_champ'] == 'CASE') {
						
			$champ_row_questiondiverse['value'] = str_replace('_+_',';',$champ_row_questiondiverse['value']);
		
		}
	array_push($all_champs, $champ_row_questiondiverse);
	}
	
	
	usort($all_champs, "cmp");
	//print_r($all_champs);
	return $all_champs;
	}

	
	function recup_champ_dotation_inscrit_epreuve ($idEpreuve, $idInternaute, $idInscriptionEpreuveInternaute) {
	$all_champs = array();
	$champ_row_dotation = array();
	$champ_row_participation= array();
	$champ_row_questiondiverse= array();
	
	$champ_dotation = array ('id' => 'idChampsSupDotationCommune', 'label'=>'label', 'value'=>'value','ordre'=>'ordre','type_champ'=>'type_champ');
	$champ_participation = array ('id' => 'idChampsSupParticipationCommune', 'label'=>'label', 'value'=>'value','ordre'=>'ordre','prix_total'=>'prix_total');
	$champ_questiondiverse = array ('id' => 'idChampsSupQuestionDiverseCommune', 'label'=>'label', 'value'=>'value','ordre'=>'ordre','type_champ'=>'type_champ');
	
	$query  = "SELECT rcd.idChampsSupDotationCommune, label, value, ordre, type_champ ";
	$query .= "FROM r_champssupdotation_commune as rcd ";
	$query .= "INNER JOIN r_insc_champssupdotation_commune as ricd ON rcd.idChampsSupDotationCommune = ricd.idChampsSupDotation ";
	$query .= "INNER JOIN r_inscriptionepreuveinternaute as riei ON ricd.idInscriptionEpreuveInternaute = riei.idInscriptionEpreuveInternaute ";
	$query .= "WHERE ricd.idEpreuve = ".$idEpreuve." ";
	//$query .= "AND ricd.idEpreuveParcours = ".$idEpreuveParcours." ";
	//$query .= "AND ricd.idInternaute = ".$idInternaute." ";
	$query .= "AND riei.idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute." ";
	$query .= "ORDER BY rcd.ordre ASC";
	//echo $query;
	$result = $mysqli->query($query);
	$champ_row_dotation['champ'] ='dotation';
	while (($row=mysqli_fetch_array($result)) != FALSE)
	{
		foreach ($champ_dotation as $k=>$i) {
			
			$champ_row_dotation[$k] = $row[$i];
			

		}
		if ( $champ_row_dotation['type_champ'] == 'CASE') {
						
			$champ_row_dotation['value'] = str_replace('_+_',';',$champ_row_dotation['value']);
		
		}
	array_push($all_champs, $champ_row_dotation);
	}

	
	
	
	$query  = "SELECT rcd.idChampsSupParticipationCommune, label, value, ordre, prix_total ";
	$query .= "FROM r_champssupparticipation_commune as rcd ";
	$query .= "INNER JOIN r_insc_champssupparticipation_commune as ricd ON rcd.idChampsSupParticipationCommune = ricd.idChampsSupParticipation ";
	$query .= "INNER JOIN r_inscriptionepreuveinternaute as riei ON ricd.idInscriptionEpreuveInternaute = riei.idInscriptionEpreuveInternaute ";
	$query .= "WHERE ricd.idEpreuve = ".$idEpreuve." ";
	//$query .= "AND ricd.idInternaute = ".$idInternaute." ";
	$query .= "AND ricd.idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute." ";
	$query .= "ORDER BY rcd.ordre ASC";
	//echo $query;
	$result = $mysqli->query($query);
	$champ_row_participation['champ'] ='participation';
	while (($row=mysqli_fetch_array($result)) != FALSE)
	{
		foreach ($champ_participation as $k=>$i) {
			
			$champ_row_participation[$k] = $row[$i];

		}
	array_push($all_champs, $champ_row_participation);
	}
	//print_r($all_champs);
	
	
	$query  = "SELECT rcd.idChampsSupQuestionDiverseCommune, label, value, ordre, type_champ ";
	$query .= "FROM r_champssupquestiondiverse_commune as rcd ";
	$query .= "INNER JOIN r_insc_champssupquestiondiverse_commune as ricd ON rcd.idChampsSupQuestionDiverseCommune = ricd.idChampsSupQuestionDiverse ";
	$query .= "INNER JOIN r_inscriptionepreuveinternaute as riei ON ricd.idInscriptionEpreuveInternaute = riei.idInscriptionEpreuveInternaute ";
	$query .= "WHERE ricd.idEpreuve = ".$idEpreuve." ";
	//$query .= "AND ricd.idEpreuveParcours = ".$idEpreuveParcours." ";
	//$query .= "AND ricd.idInternaute = ".$idInternaute." ";
	$query .= "AND riei.idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute." ";
	$query .= "ORDER BY rcd.ordre ASC";
	//echo $query;
	$result = $mysqli->query($query);
	$champ_row_questiondiverse['champ'] ='questiondiverse';
	while (($row=mysqli_fetch_array($result)) != FALSE)
	{
		foreach ($champ_questiondiverse as $k=>$i) {
			
			$champ_row_questiondiverse[$k] = $row[$i];

		}
		
		if ( $champ_row_questiondiverse['type_champ'] == 'FILE') {
						
			$champ_row_questiondiverse['value'] = questiondivers_file_existe($champ_row_questiondiverse['value'],'insc_qd_file_commune');
		
		}
		
		if ( $champ_row_questiondiverse['type_champ'] == 'CASE') {
						
			$champ_row_questiondiverse['value'] = str_replace('_+_',';',$champ_row_questiondiverse['value']);
		
		}
	array_push($all_champs, $champ_row_questiondiverse);
	}
	
	
	usort($all_champs, "cmp");
	//print_r($all_champs);
	return $all_champs;
}
	
	
function tronque_texte ($chaine,$max) {

	if (strlen($chaine) >= $max)
	{
	$chaine = substr($chaine, 0, $max);
	$espace = strrpos($chaine, " ");
	$chaine = substr($chaine, 0, $espace)."...";
	}

return $chaine;
}

		function calcul_frais_cb($tarif,$cout, $cout_paiement_cb=0,$participation=0) {
		
			$taux_inscription = 0.025;
			$taux_participation = 0.05;
			//echo $tarif."-";
			//echo $cout_paiement_cb;
			//$participation = $cout-$tarif;
			//echo $participation=50;
			if ($cout_paiement_cb==0) 
			{
				if($tarif == 0)
				{
					$frais_cb = 0;
					if($participation > 0) $frais_cb += $participation*$taux_participation;
				}
				else if($tarif <= 8)
				{
					$frais_cb = 0.75;
					if($participation >0) $frais_cb += $participation*$taux_participation;
				}
				else if($tarif <= 30)
				{
					$frais_cb = 1;
					if($participation >0) $frais_cb += $participation*$taux_participation;
				}
				else if($tarif <= 50)
				{
					$frais_cb = 1.5;
					if($participation >0) $frais_cb += $participation*$taux_participation;
				}
				else if($tarif <= 80)
				{
					$frais_cb = 2;
					if($participation >0) $frais_cb += $participation*$taux_participation;
				}
				else if($tarif <= 125)
				{
					$frais_cb = 2.5;
					if($participation >0) $frais_cb += $participation*$taux_participation;
				}
				else if($tarif > 125)
				{
					$frais_cb = ($tarif*$taux_inscription);
					if($participation >0) $frais_cb += $participation*$taux_participation;
				}
			}
			else //Si les frais sont fixes
				{
					$frais_cb = $cout_paiement_cb;
				}
			
			
			return $frais_cb;
		}

function calcul_frais_cheque($tarif,$cout) {

	if($tarif == 0)
	{
		$frais_cheque = 0;
		if($cout > 0) $frais_cheque = $cout*0.025;
	}
	else if($tarif <= 25)
	{
		$frais_cheque = 1.0;
		if($cout >=25) $frais_cheque += ($cout - 25)*0.025;
	}
	else if($tarif <= 35)
	{
		$frais_cheque = 1.5;
		if($cout >=35 ) $frais_cheque += ($cout - 35)*0.025;
	}
	else if($tarif <= 50)
	{
		$frais_cheque = 2.0;
		if($cout >=50) $frais_cheque += ($cout - 50)*0.025;
	}
	else if($tarif > 50)
	{
		$frais_cheque = 2.5;
		if($cout > 50) $frais_cheque += ($cout - 50)*0.025;
	}
	else //Si les frais sont fixes
	{
		$frais_cheque = $cout_paiement_cheque;
	}
	
	return $frais_cheque;
}

function calcul_categorie($annee_naissance,$champ,$idTypeEpreuve=3,$sexe='MF',$idEpreuve='') {
	
	if(!empty($idEpreuve))
	{	
		$sql = "SELECT idCategorieAge FROM r_categorie_age WHERE idEpreuve =".$idEpreuve;
		$requete = $mysqli->query($sql);
		$nb = mysqli_num_rows($requete);
	}
	
	$query = "SELECT ".$champ." from r_categorie_age ";
	$query .= "WHERE annee_naissance_debut <=".$annee_naissance." AND annee_naissance_fin >=".$annee_naissance;
	$query .= " AND idTypeEpreuve = ".$idTypeEpreuve;
	$query .= " AND sexe = '".$sexe."'";
	if(!empty($nb)) $query .= " AND idEpreuve = ".$idEpreuve."";
	$result = $mysqli->query($query);
	$row=mysqli_fetch_array($result);
	//echo $query;
	return $row;
	
}


function check_paiement_internaute ($idEpreuve, $idEpreuveParcours, $idInternaute) {
	
	$query  = "SELECT paiement_date, paiement_type, montant_inscription, frais_cb, frais_cheque ";
	$query .= "FROM r_inscriptionepreuveinternaute";
	$query .=" WHERE idEpreuve = ".$idEpreuve;
	$query .=" AND idEpreuveParcours = ".$idEpreuveParcours;
	$query .=" AND idInternaute = ".$idInternaute;
	//echo $query;
	$result = $mysqli->query($query);
	$row=mysqli_fetch_array($result);

	$tab_check = array('paiement_date'=>$row['paiement_date'],'paiement_type'=>$row['paiement_type'],'montant_inscription'=>$row['montant_inscription'],'frais_cb'=>$row['frais_cb'],'frais_cheque'=>$row['frais_cheque']);
	//print_r($tab_check);
	return $tab_check;

	
}

function check_insert_club_internaute ($club) {

	$query = "SELECT nomclub FROM r_club_internaute ";
	$query .="WHERE nomclub like '%".$club."%'";
	$result = $mysqli->query($query);
	$row=mysqli_fetch_array($result);
	
	if (empty($row['nomclub'])){
		
		$query_i = "INSERT into r_club_internaute (nomclub) VALUES ('".$club."')";
		$result_i = $mysqli->query($query_i);
	}
	
	
}

function info_internaute_send_mail ($id_internaute,$cout,$type_paiement) {  
	
	$query_info   = "SELECT DISTINCT riit.idInternaute, riit.idInscriptionEpreuveInternaute, riit.idInternauteReferent, riit.idEpreuve,riit.idParcours, ";
	$query_info   .= "riit.idEpreuveParcoursTarif,riit.nomInternaute,riit.prenomInternaute,riit.emailInternaute,riit.dateInscription,riit.certificatMedical,riit.idSession,riit.codeSecurite, riit.autoParentale, riit.cout,riit.participation,riit.frais_cb,riit.frais_cheque,riit.reference_cheque,riit.idEpreuveParcoursTarifPromo, riit.code_promo,riit.valeur_code_promo,riit.info_diverses,riit.equipe,riit.groupe, riit.categorie, ";
	$query_info   .= "re.nomEpreuve, rep.nomParcours, rep.infoParcoursInscription, ";
	$query_info   .= "ri.naissanceInternaute, ri.clubInternaute, ri.adresseInternaute, ri.cpInternaute, ri.villeInternaute, ri.paysInternaute ";
	//$query_info   .= ",ricsd.idChampsSupDotation, ricsd.value ";
	$query_info .= "FROM r_insc_internaute_temp as riit ";
	$query_info .= " INNER JOIN r_epreuve as re ON riit.idEpreuve = re.idEpreuve ";
	$query_info .= " INNER JOIN r_internaute as ri ON riit.idInternaute = ri.idInternaute ";
	$query_info .= " INNER JOIN r_epreuveparcours as rep ON riit.idParcours = rep.idEpreuveParcours ";
	//$query_info .= " INNER JOIN r_insc_champssupdotation as ricsd ON (riit.idInternaute = ricsd.idInternaute ";
	//$query_info .= " AND riit.idEpreuve = ricsd.idEpreuve AND riit.idParcours = ricsd.idEpreuveParcours) ";
	
	
	//$query_info   = " riit.autoParentale,riit.idSession,riit.codeSecurite,riit.typePaiement,riit.code_promo,riit.frais_cb,riit.envoiEmailInscription, ";
	//$query_info  .= " e.nomEpreuve, e.idTypeEpreuve, e.dateEpreuve, e.emailInscription, e.urlImage, ep.relais, ";
	
	$query_info .= "WHERE riit.	idInscriptionEpreuveInternaute = ".$id_internaute." AND riit.typePaiement in('ATTENTE','CB','ATTENTE CHQ')";
	
	//$query_info .= "INNER JOIN r_insc_champssupdotation as rics ON = ".$idInscriptionEpreuveInternaute." ";
	//$query_info .= "AND idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute." ";
	//echo $query_info .= "AND idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute." ";	


	
	$result_info = $mysqli->query($query_info);
	$row_info=mysqli_fetch_array($result_info);
	
	$row_info['montant'] = $cout;
	
	$row_info['mode_paiement'] = $type_paiement;
	//print_r($row_info);
	return $row_info;
}

function info_internaute_send_mail_test ($id_internaute,$cout,$type_paiement) {
	
	$query_info   = "SELECT DISTINCT rii.idInternaute, rii.idInscriptionEpreuveInternaute, rii.idEpreuve,rii.idEpreuveParcours as idParcours, ";
	$query_info   .= "rii.idEpreuveParcoursTarif,ri.nomInternaute,ri.prenomInternaute,ri.emailInternaute,rii.date_insc,ri.passInternaute, rii.verif_certif as certificatMedical, rii.verif_auto_parentale as autoParentale, rii.id_session as idSession,rii.paiement_montant,(rii.montant_inscription-rii.participation) as cout,rii.frais_cb,rii.participation, rii.frais_cheque,rii.info_cheque,rii.idEpreuveParcoursTarifPromo, rii.label_code_promo as code_promo,rii.montant_code_promo as valeur_code_promo,rii.info_diverses,rii.equipe, rii.groupe,rii.categorie,   ";
	$query_info   .= "re.nomEpreuve, rep.nomParcours, ";
	$query_info   .= "ri.naissanceInternaute, ri.clubInternaute, ri.adresseInternaute, ri.cpInternaute, ri.villeInternaute, ri.paysInternaute ";
	//$query_info   .= ",ricsd.idChampsSupDotation, ricsd.value ";
	$query_info .= "FROM r_inscriptionepreuveinternaute as rii ";
	$query_info .= " INNER JOIN r_epreuve as re ON rii.idEpreuve = re.idEpreuve ";
	$query_info .= " INNER JOIN r_internaute as ri ON rii.idInternaute = ri.idInternaute ";
	$query_info .= " INNER JOIN r_epreuveparcours as rep ON rii.idEpreuveParcours = rep.idEpreuveParcours ";
	//$query_info .= " INNER JOIN r_insc_champssupdotation as ricsd ON (riit.idInternaute = ricsd.idInternaute ";
	//$query_info .= " AND riit.idEpreuve = ricsd.idEpreuve AND riit.idParcours = ricsd.idEpreuveParcours) ";
	
	
	//$query_info   = " riit.autoParentale,riit.idSession,riit.codeSecurite,riit.typePaiement,riit.code_promo,riit.frais_cb,riit.envoiEmailInscription, ";
	//$query_info  .= " e.nomEpreuve, e.idTypeEpreuve, e.dateEpreuve, e.emailInscription, e.urlImage, ep.relais, ";
	
	$query_info .= "WHERE rii.idInscriptionEpreuveInternaute = ".$id_internaute." AND rii.paiement_type in('ATTENTE','ATTENTE CHQ','CB','CHQ','GRATUIT','AUTRE')";
	
	//$query_info .= "INNER JOIN r_insc_champssupdotation as rics ON = ".$idInscriptionEpreuveInternaute." ";
	//$query_info .= "AND idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute." ";
	//echo $query_info .= "AND idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute." ";	


	
	$result_info = $mysqli->query($query_info);
	$row_info=mysqli_fetch_array($result_info);
	
	$row_info['montant'] = $cout;
	
	$row_info['mode_paiement'] = $type_paiement;
	
	return $row_info;
}

function info_internaute_resend_mail ($id_internaute,$etat_certif,$paiement,$id_session_temp) {
	
	$query_info   = "SELECT DISTINCT rii.idInternaute, rii.idInscriptionEpreuveInternaute, rii.idEpreuve,rii.idEpreuveParcours, ";
	$query_info   .= "rii.idEpreuveParcoursTarif,ri.nomInternaute,ri.prenomInternaute,ri.emailInternaute,rii.date_insc,ri.passInternaute, rii.verif_auto_parentale, rii.paiement_montant,  ";
	$query_info   .= "re.nomEpreuve, rep.nomParcours, ";
	$query_info   .= "ri.naissanceInternaute, ri.clubInternaute, ri.adresseInternaute, ri.cpInternaute, ri.villeInternaute, ri.paysInternaute ";
	//$query_info   .= ",ricsd.idChampsSupDotation, ricsd.value ";
	$query_info .= "FROM r_inscriptionepreuveinternaute as rii ";
	$query_info .= " INNER JOIN r_epreuve as re ON rii.idEpreuve = re.idEpreuve ";
	$query_info .= " INNER JOIN r_internaute as ri ON rii.idInternaute = ri.idInternaute ";
	$query_info .= " INNER JOIN r_epreuveparcours as rep ON rii.idEpreuveParcours = rep.idEpreuveParcours ";
	//$query_info .= " INNER JOIN r_insc_champssupdotation as ricsd ON (riit.idInternaute = ricsd.idInternaute ";
	//$query_info .= " AND riit.idEpreuve = ricsd.idEpreuve AND riit.idParcours = ricsd.idEpreuveParcours) ";
	
	
	//$query_info   = " riit.autoParentale,riit.idSession,riit.codeSecurite,riit.typePaiement,riit.code_promo,riit.frais_cb,riit.envoiEmailInscription, ";
	//$query_info  .= " e.nomEpreuve, e.idTypeEpreuve, e.dateEpreuve, e.emailInscription, e.urlImage, ep.relais, ";
	
	$query_info .= "WHERE rii.idInscriptionEpreuveInternaute = ".$id_internaute." ";
	//$query_info .= "INNER JOIN r_insc_champssupdotation as rics ON = ".$idInscriptionEpreuveInternaute." ";
	//$query_info .= "AND idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute." ";
	//echo $query_info .= "AND idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute." ";	


	
	$result_info = $mysqli->query($query_info);
	$row_info=mysqli_fetch_array($result_info);
	$row_info['idParcours'] = $row_info['idEpreuveParcours'];
	$row_info['idSession'] = $id_session_temp;
	$row_info['etat_certif'] = $etat_certif;
	$row_info['paiement'] = $paiement;
	$row_info['resendmail'] = '1';
	
	return $row_info;
}

function type_epreuve($id_epreuve) {
	
	$query = "SELECT type_nom_bdd, nom_date_nom_bdd, rt.idTypeEpreuve ";
	$query .="FROM r_typeepreuve as rt ";
	$query .="INNER JOIN r_epreuve as re ON rt.idTypeEpreuve = re.idTypeEpreuve ";
	$query .="WHERE re.idEpreuve = ".$id_epreuve;
	$result = $mysqli->query($query);
	$row_info=mysqli_fetch_array($result);
	
	return $row_info;
	
}

/*
function mise_a_jour_code_promo($idEpreuve, $idEpreuveParcours, $code_promo,$idInscriptionEpreuveInternaute) {
	
	$query = "SELECT label, nb_fois_utilisable, prix_reduction FROM r_epreuveparcourstarifpromo ";
	$query .="WHERE idEpreuve = ".$idEpreuve." AND idEpreuveParcours = ".$idEpreuveParcours;
	$result = $mysqli->query($query);
	$row=mysqli_fetch_array($result);
	$labels = explode('|',$row['label']);
	$first = TRUE;
	$label_insert = '';
	$montant = $row['prix_reduction'];
	foreach ($labels as $key=>$label) {
		
		if ( $label != 	$code_promo )
		{
			if($first==FALSE) $label_insert .="|";
			$label_insert .= $label ;
			$first=FALSE;
		}
	}
	
	if ($first==FALSE) {
		
		$query ="UPDATE r_epreuveparcourstarifpromo SET ";
		$query.="label ='".$label_insert."', ";
		$query.="nb_fois_utilisable = ".($row['nb_fois_utilisable']-1)." ";
		$query .="WHERE idEpreuve = ".$idEpreuve." AND idEpreuveParcours = ".$idEpreuveParcours;
		$result = $mysqli->query($query);
		
		$query ="UPDATE r_inscriptionepreuveinternaute SET ";
		$query.="label_code_promo ='".$code_promo."', ";
		$query.="montant_code_promo = ".$montant." ";
		$query .="WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
		$result = $mysqli->query($query);
	}
	
}
*/
function mise_a_jour_code_promo($idEpreuve, $idEpreuveParcours, $code_promo,$valeur_code_promo, $idEpreuveParcoursTarifPromo,$idInscriptionEpreuveInternaute) {
	
	$update_internaute = FALSE;
	
	$query = "SELECT * FROM r_epreuveparcourstarifpromo ";
	$query .="WHERE idEpreuveParcoursTarifPromo = ".$idEpreuveParcoursTarifPromo;
	$result = $mysqli->query($query);
	$row=mysqli_fetch_array($result);
	//echo $code_promo;
	if (empty($row['nb_fois_utilisable']))
	{ 
		$labels = explode('|',$row['label']);
		//print_r($labels);
		$first = TRUE;
		$label_insert = '';
		$montant = $valeur_code_promo;
		foreach ($labels as $key=>$label) 
		{
			
			if ( $label != 	$code_promo )
			{
				if($first==FALSE) $label_insert .="|";
				$label_insert .= $label ;
				$first=FALSE;
			}
		}
		
		if ($first==TRUE ) $label_insert = '';
		
			$labels_utilise = $row['code_promo_utilise']; 
			
			if (empty($labels_utilise))
			{
				$labels_utilise = $code_promo;
			}
			else
			{
				$labels_utilise .= "|".$code_promo;
			}
	
			
			$query ="UPDATE r_epreuveparcourstarifpromo SET ";
			$query.="label ='".$label_insert."', ";
			$query.="bon_dispo = bon_dispo-1, ";
			$query.="nb_utilise = nb_utilise+1, ";
			$query.="code_promo_utilise = '".$labels_utilise."' ";
			$query .="WHERE idEpreuveParcoursTarifPromo = ".$idEpreuveParcoursTarifPromo;
			$result = $mysqli->query($query);
			$update_internaute = TRUE;
		
	}
	else
	{
		$label = $row['label'];
		$montant = $valeur_code_promo;
		if ( $label == $code_promo )
		{
			$query ="UPDATE r_epreuveparcourstarifpromo SET ";
			$query.="nb_fois_utilisable = nb_fois_utilisable-1, ";
			$query.="nb_utilise = nb_utilise+1 ";
			$query .="WHERE idEpreuveParcoursTarifPromo = ".$idEpreuveParcoursTarifPromo;
			$result = $mysqli->query($query);
			$update_internaute = TRUE;
		}
	
	}
	if ($update_internaute == TRUE ) {
	$query ="UPDATE r_inscriptionepreuveinternaute SET ";
	$query.="idEpreuveParcoursTarifPromo =".$idEpreuveParcoursTarifPromo.", ";
	$query.="label_code_promo ='".$code_promo."', ";
	$query.="montant_code_promo = ".$montant." ";
	$query .="WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
	$result = $mysqli->query($query);
	}
	
}

function detect_info_client() {

require('Browser.php');
$browser = new Browser();

	// Simple browser and OS detection script. This will not work if User Agent is false.
$agent = $_SERVER['HTTP_USER_AGENT'];

// Detect Device/Operating System
if(preg_match('/Linux/i',$agent)) $os = 'Linux';
elseif(preg_match('/Mac/i',$agent)) $os = 'Mac'; 
elseif(preg_match('/iPhone/i',$agent)) $os = 'iPhone'; 
elseif(preg_match('/iPad/i',$agent)) $os = 'iPad'; 
elseif(preg_match('/Droid/i',$agent)) $os = 'Droid'; 
elseif(preg_match('/Unix/i',$agent)) $os = 'Unix'; 
elseif(preg_match('/Windows/i',$agent)) $os = 'Windows';
else $os = 'Unknown';

// Browser Detection
if(preg_match('/Firefox/i',$agent)) $br = 'Firefox'; 
elseif(preg_match('/Mac/i',$agent)) $br = 'Mac';
elseif(preg_match('/Chrome/i',$agent)) $br = 'Chrome'; 
elseif(preg_match('/Opera/i',$agent)) $br = 'Opera'; 
elseif(preg_match('/MSIE/i',$agent)) $br = 'IE'; 
else $br = 'Unknown';

$info_navigateur = array ('os'=>$os, 'browser'=>$browser->getBrowser(), 'version'=>$browser->getVersion(), 'ip_client'=>get_ip()); 
return $info_navigateur;
//return 'OS : '.$os.' - Navigateur : '.$browser->getBrowser().' - Version : '.$browser->getVersion()." - IP client : ".get_ip();
	

}

function get_ip() {
	// IP si internet partagé
	if (isset($_SERVER['HTTP_CLIENT_IP'])) {
		return $_SERVER['HTTP_CLIENT_IP'];
	}
	// IP derrière un proxy
	elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	// Sinon : IP normale
	else {
		return (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
	}
}

function controle_epreuve_parcours ($idEpreuve, $idEpreuveParcours) {

		$query  = "SELECT nbtarif ";
		$query .= "FROM r_epreuveparcours";
		$query .=" WHERE idEpreuve = ".$idEpreuve;
		if (!empty($idEpreuveParcours)) $query .=" AND idEpreuveParcours = ".$idEpreuveParcours;

		$query .=" AND nbtarif > 0";
		$result = $mysqli->query($query);
		$row=mysqli_fetch_array($result);
		
		if (!empty($row['nbtarif'])) { return 'OK';} else { return 'KO'; }

}

function controle_date_debut_inscription ($idEpreuve) {

		$query  = "SELECT idEpreuve FROM `r_epreuve` WHERE `dateDebutInscription` <= NOW() AND idEpreuve=".$idEpreuve;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_array($result);
		return $row['idEpreuve'];
		
		//if (!empty($row['idEpreuve'])) { return 'OK';} else { return 'KO'; }

}
function recup_mail_organisateur_epreuve ($idEpreuve) {

	$query = "SELECT emailInscription FROM `r_epreuve` WHERE `idEpreuve` = ".$idEpreuve." AND emailinscription_recevoir = 1";
	$result = $mysqli->query($query);
	$row=mysqli_fetch_array($result);
	
	if (!empty($row['emailInscription'])) { return $row['emailInscription'];} else { return ''; }
}

function info_epreuve_parcours ($idEpreuve,$idParcours) {

		$query  = "SELECT re.nomEpreuve, rep.nomParcours ";
		$query .= "FROM r_epreuve re";
		$query .=" INNER JOIN r_epreuveparcours as rep ON re.idEpreuve = rep.idEpreuve";
		$query .=" WHERE idEpreuve = ".$idEpreuve;
		$query .=" AND idEpreuveParcours = ".$idParcours;
		$result = $mysqli->query($query);
		$data_epreuve=mysqli_fetch_array($result);
	
	if (!empty($data_epreuve['nomEpreuve'])) { return $row;} else { return ''; }
}

function email_organisateur ($idEpreuve) {

		$query  = "SELECT emailInscription  ";
		$query .= "FROM r_epreuve ";
		$query .=" WHERE idEpreuve = ".$idEpreuve;
		$query .=" AND emailinscription_recevoir = 1";
		$result = $mysqli->query($query);
		$data_epreuve=mysqli_fetch_array($result);
		
	
	if (!empty($data_epreuve['emailInscription'])) { return $data_epreuve['emailInscription'];} else { return 'contact@pointcourse.com'; }
}

function info_parcours($id_epreuve,$id_parcours,$relais=1) {		
		$date_et_heure_du_jour = strtotime(date('Y-m-d H:i:s'));
		
		$query  = "SELECT * FROM r_epreuveparcours ";
		$query .= "INNER JOIN r_typeparcours ON r_epreuveparcours.idTypeParcours = r_typeparcours.idTypeParcours ";
		$query .= "INNER JOIN r_epreuveparcourstarif ON r_epreuveparcours.idEpreuveParcours = r_epreuveparcourstarif.idEpreuveParcours ";
		$query .= "WHERE r_epreuveparcours.idEpreuve = ".$id_epreuve;
		$query .= " AND dateDebutTarif <=  NOW() ";
		$query .= "AND dateFinTarif >=  NOW()";
	
		if (!empty($id_parcours)) {
			$query .=" AND r_epreuveparcours.idEpreuveParcours= ".$id_parcours;
		}
		if ($relais==0) {
				$query .=" AND r_epreuveparcours.relais= 0";
		}
		$query .= " ORDER BY r_epreuveparcours.ordre_affichage ASC";
		$result_select_parcours = $mysqli->query($query);

		//print_r($info_parcours);
		return $result_select_parcours;

}
Function nombre_dossard ($id_parcours) {

	$dossard_affecte = array();
								
	$query  = "SELECT dossard FROM r_inscriptionepreuveinternaute 
			   WHERE idEpreuveParcours = ".$id_parcours." AND paiement_type NOT IN('SUPPRESSION','ATTENTE','REMBOURSE') ORDER BY dossard ASC";	
	//$query ;														
	$result = $mysqli->query($query);
	$nb_dossard_deja_attribue = 0;
	while($row = mysqli_fetch_array($result)) 
	{
		array_push($dossard_affecte,intval($row['dossard']));
		if (intval($row['dossard']) != 0) $nb_dossard_deja_attribue++;
	}
	
	//Récupération du 1er dossard à attribuer sur le parcours
	$query = "SELECT dossardDeb, dossardFin, nbexclusion, dossards_exclus FROM r_epreuveparcours WHERE idEpreuveParcours = ".$id_parcours."";
	//echo $query;
	$result = $mysqli->query($query);
	$borne = mysqli_fetch_array($result);
	$dossardmin = $borne['dossardDeb'];
	$dossardmax = $borne['dossardFin'];

	$nb_dossard_du_parcours = ($dossardmax - $dossardmin)+1;
	
	$doss = $dossardmin;
	
	$nbexclusion = $borne['nbexclusion'];
	$plage_exclusion = explode(":",$borne['dossards_exclus']);
	
	$nb_plage_exclusion = count($plage_exclusion);
	$nb_exclusion = 0;
	$nb_dossards_exclus = 0;
	$nb_dossard_disponible = 0;
	
	for($j=0; $j<$nbexclusion; $j++)
	{
		$exclus = explode("-",$plage_exclusion[$j]);
		$nb_exclusion += ($exclus[1] -$exclus[0])+1;
		for($e=$exclus[0]; $e<=$exclus[1]; $e++)
			array_push($dossard_affecte,intval($e));
		
	//echo ($exclus[1] -$exclus[0])."-";
	}
	//echo $nb_exclusion;
	//$nb_dossard_disponible = ($nb_dossard_du_parcours - $nb_exclusion-$nb_dossard_deja_attribue);
	$nb_dossard_disponible = ($nb_dossard_du_parcours - $nb_dossard_deja_attribue);
	
	$nb_info_dossard = array('nb_dossard_attribue'=>$nb_dossard_deja_attribue, 'nb_dossard_disponible'=>$nb_dossard_disponible, 'nb_dossard_reserve'=>$nb_exclusion, 'nb_dossard_parcours'=>$nb_dossard_du_parcours);
	return $nb_info_dossard;

}

function check_internaute_existant ($nom,$prenom,$date_naissance,$idInternaute) {

	//naissanceInternaute
	//sexeInternaute
		$query  = "SELECT idInternaute FROM r_internaute as ri"; 
		$query .= " WHERE UPPER(ri.nomInternaute) = UPPER('".$nom."') ";
		$query .= " AND UPPER(ri.prenomInternaute) = UPPER('".$prenom."') ";
		$query .= " AND ri.naissanceInternaute = '".$date_naissance."' ";
		$query .= " AND ri.idInternaute = ".$idInternaute." ";
		/*if(!empty($email)) {
			$query .= " AND ri.emailInternaute = '".$email."' ";
		}*/
		$result = $mysqli->query($query);
		$row=mysqli_fetch_array($result);
		return $row[0];
}

function check_internaute_existant_v2 ($nom,$prenom,$sexe,$date_naissance,$email,$club,$adresse,$cp,$ville,$lat_ville,$long_ville,$pays,$index_telephone,$telephone) {

	//naissanceInternaute
	//sexeInternaute
		$champ =array();
		$query  = "SELECT idInternaute,passInternaute FROM r_internaute as ri"; 
		$query .= " WHERE UPPER(ri.nomInternaute) = UPPER('".$nom."') ";
		$query .= " AND UPPER(ri.prenomInternaute) = UPPER('".$prenom."') ";
		$query .= " AND ri.naissanceInternaute = '".$date_naissance."' ";
		$query .= " AND ri.sexeInternaute = '".$sexe."' ";
		//if(!empty($email)) {
			$query .= " AND ri.emailInternaute = '".$email."' ";
		//}
		$query .= " ORDER BY idInternaute DESC ";
		$query;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_array($result);
		
		$id_internaute = $row[0];
		$pass_internaute = $row[1];
		
		$champ['id_internaute']=$row[0];
		$champ['pass_internaute']=$row[1];
		
		if (!empty($champ['id_internaute']))
		{
		
			$query  = "UPDATE r_internaute SET ";
			//$query .= "dateInscription=NOW(), ";
			//$query .= "nomInternaute=UPPER('".addslashes_form_to_sql($nom)."'), ";
			//$query .= "prenomInternaute=UPPER('".addslashes_form_to_sql($prenom)."'), ";
			//$query .= "sexeInternaute='".$sexe."', ";
			//$query .= "naissanceInternaute= '".$date_naissance."', ";
			//if(!empty($email)) {
			//$query .= "emailInternaute= '".addslashes_form_to_sql($email)."', ";
			//}
			$query .= "clubInternaute= '".addslashes_form_to_sql($club)."', ";
			$query .= "adresseInternaute= '".addslashes_form_to_sql($adresse)."', ";
			$query .= "cpInternaute= '".$cp."', ";
			//$query .= "ageLimite= ".((isset($_POST['epre_limit_age'][$j]) && $_POST['epre_limit_age'][$j] == 1)?1:0).", ";
			$query .= "villeInternaute= '".addslashes_form_to_sql($ville)."', ";
			$query .= "villeLatitude= '".addslashes_form_to_sql($lat_ville)."', ";
			$query .= "villeLongitude= '".addslashes_form_to_sql($long_ville)."', ";
			$query .= "paysInternaute= '".addslashes_form_to_sql($pays)."', ";
			//$query .= "typeInternaute= '".addslashes_form_to_sql($_POST['insc_type_internaute'])."', ";
			$query .= "index_telephone= '".addslashes_form_to_sql($index_telephone)."', ";
			$query .= "telephone= '".addslashes_form_to_sql($telephone)."' ";
			//$query .= $query_update_certificat." = '".$date_certificat."'";
			//$query .= ")";
			$query .= " WHERE idInternaute= ".$champ['id_internaute']."";
			$result_query = $mysqli->query($query);
							
			return $champ;
		}
}

function check_inscription_internaute_existant ($idEpreuve,$idEpreuveParcours,$idTarif,$id_internaute) {

	//naissanceInternaute
	//sexeInternaute
		$query  = "SELECT idInscriptionEpreuveInternaute FROM r_inscriptionepreuveinternaute "; 
		$query .= " WHERE idEpreuve = ".$idEpreuve;
		$query .= " AND idEpreuveParcours = ".$idEpreuveParcours;
		$query .= " AND idEpreuveParcoursTarif = ".$idTarif;
		$query .= " AND idInternaute = ".$id_internaute;
		$query .= " AND paiement_type NOT IN ('SUPPRESSION','REMBOURSE')";
		$query;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_array($result);
		if ($row != FALSE) 	return $row[0];
}

function check_inscription_internaute_temp ($idEpreuve,$idEpreuveParcours,$idTarif,$id_internaute,$id_session) {

	//naissanceInternaute
	//sexeInternaute
		$query  = "SELECT idInscInternauteTemp FROM r_insc_internaute_temp "; 
		$query .= " WHERE idEpreuve = ".$idEpreuve;
		$query .= " AND idParcours = ".$idEpreuveParcours;
		$query .= " AND idEpreuveParcoursTarif = ".$idTarif;
		$query .= " AND idInternaute = ".$id_internaute;
		$query;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_array($result);
		
		if(!empty($row[0])) {
			
			$query_del =  "DELETE FROM r_insc_internaute_temp ";
			$query_del .= " WHERE idInscInternauteTemp = ".$row[0]." ";
			//$query_del .= "AND idEpreuve = ".$row_info['idEpreuve'];
			$result_del = $mysqli->query($query_del);
			
			/*
			$query  = "UPDATE r_insc_internaute_temp SET ";
			$query .= " idSession ='".addslashes($id_session)."'";
			$query .= " WHERE idInscInternauteTemp=".$row[1];
			$result_query = $mysqli->query($query);
			*/
		
		
		}
		
		return $row[0];
}

function check_inscription_internaute_temp_v2 ($idInscriptionEpreuveInternaute) {

	//naissanceInternaute
	//sexeInternaute
		$query  = "SELECT idInscInternauteTemp FROM r_insc_internaute_temp "; 
		$query .= " WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
		
		/*$query .= " AND idParcours = ".$idEpreuveParcours;
		$query .= " AND idEpreuveParcoursTarif = ".$idTarif;
		$query .= " AND idInternaute = ".$id_internaute;
		$query;*/
		$result = $mysqli->query($query);
		$row=mysqli_fetch_array($result);
		
		/*if(!empty($row[0])) {
			
			$query  = "UPDATE r_insc_internaute_temp SET ";
			$query .= " idSession ='".addslashes($id_session)."'";
			$query .= " WHERE idInscInternauteTemp=".$row[1];
			$result_query = $mysqli->query($query);
		
		
		}*/
		if(!empty($row['idInscInternauteTemp'])) return $row['idInscInternauteTemp'];
}

function liste_moyen_de_paiement($type='TOUS') {
	
	
	
	$query  = "SELECT paiement_type FROM r_typepaiement ";
	if ($type!='TOUS') { $query.= ' WHERE paiement_type IN ('.$type.')'; }
	$query.= " ORDER BY idtypepaiement ASC";
	
	$result = $mysqli->query($query);
	$champs = array();
	
	while($row = mysqli_fetch_array($result))
	{
			
		$champs[] = array('value'=>$row['paiement_type'],'text' => $row['paiement_type']);
	
	}	
	return $champs;
}
function internaute_referent_inscription_multiple_calcul_cout ($id_epreuve,$id_parcours,$idInternauteRef ) {
/*
	$idInternauteRef = 172371;
	$id_epreuve = 4031;
	$id_parcours = 7069;
	*/
	$champ = array();
	$query_int_ref = " SELECT rir.idInternauteref, rir.idInscriptionEpreuveInternautes, riit.cout, riit.participation, re.payeur, rept.tarif, riir.idInternauteReferent,riir.paiement_type,riir.montant, riir.frais_cb FROM r_internautereferent as rir ";
	$query_int_ref .= " INNER JOIN r_inscriptionepreuveinternaute  as riei ON rir.idInternauteInscriptionref = riei.idInscriptionEpreuveInternaute ";
	$query_int_ref .= " INNER JOIN r_insc_internaute_temp  as riit ON riei.idInscriptionEpreuveInternaute = riit .idInscriptionEpreuveInternaute ";
	$query_int_ref .= " INNER JOIN r_epreuveparcourstarif as rept ON riei.idEpreuveParcoursTarif = rept.idEpreuveParcoursTarif";
	$query_int_ref .= " INNER JOIN r_insc_internautereferent as riir ON rir.idInternauteReferent = riir.idInternauteReferent ";
	$query_int_ref .= " INNER JOIN r_epreuve as re ON riir.idEpreuve = re.idEpreuve ";
	//$query_int_ref .= " INNER JOIN r_internaute as ri ON rir.idInternauteRef = ri.idInternaute ";
	//$query_int_ref .= " INNER JOIN b_paiements as bp ON CONCAT('M-',rir.idInternauteReferent) = bp.reference ";
	//$query_int_ref .= " WHERE riir.idEpreuveParcours LIKE CONCAT('%',".$id_parcours.",'%') ";
	//$query_int_ref .= " AND riir.idEpreuve = ".$id_epreuve." ";
	//echo $query_int_ref .= " AND rir.idInscriptionEpreuveInternautes LIKE CONCAT('%',".$idInscriptionEpreuveInternautes.",'%') ";
	//$query_int_ref .= " AND rir.idInternauteref = ".$idInternauteRef ;
	$query_int_ref .= " WHERE riei.idInscriptionEpreuveInternaute = ".$idInternauteRef ;
	//$query_int_ref .= " AND rir.idInternautes LIKE CONCAT('%',".$idInternaute.",'%') ";
	$query_int_ref .= " AND riir.paiement_type IN ('ATTENTE','ATTENTE CHQ') ";
	$query_int_ref .= " AND riei.montant_inscription is NOT NULL ";
	$query_int_ref .= " ORDER BY rir.idInternauteReferent DESC ";
	$query_int_ref .= " LIMIT 1 ";	
	//echo $query_int_ref;	
	$result = $mysqli->query($query_int_ref);
	$row=mysqli_fetch_array($result);
	
	
	if ($row != FALSE ) {
	//while (($row=mysqli_fetch_array($result)) != FALSE)
	//{
		//$concatene ='';
		/*
		$champs=explode("|",$row['idInscriptionEpreuveInternautes']);
		//$num=count($champs);
		//$cpt = 1;
		$total_a_payer = 0;
		
		
		foreach ($champs as $key=>$idInscriptionEpreuveInternaute) { 
			
			$query_riei  ="SELECT cout, participation FROM r_insc_internaute_temp";
			//$query_riei .=" INNER JOIN r_epreuveparcourstarif as rept ON riei.idEpreuveParcoursTarif = rept.idEpreuveParcoursTarif";
			//$query_riei .=" INNER JOIN r_epreuve as re ON riei.idEpreuve = re.idEpreuve ";
			//$query_riei .=" WHERE idEpreuve = ".$id_epreuve." ";
			$query_riei .=" WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute." ";
			$result_riei = $mysqli->query($query_riei);
			$row_riei=mysqli_fetch_array($result_riei);
			
			//echo "user : ".$idInscriptionEpreuveInternaute." - prix : ".$row_riei[0]."</br>";
			$total_a_payer += ($row_riei['cout'] + $row_riei['participation']);
			
			
			//if ($cpt > 1) $concatene .="|";
			//$concatene .= ($champ + $idInternauteAdd);
			//echo $champ."</br>";
			$cpt++;

		}*/
	//}
		/*
		$total_a_payer += ($row['cout'] + $row['participation']);
		//$total_a_payer +=$row['montant_inscription'];
		//$row['payeur'] = 'coureur';
		if ($row['payeur']=='coureur') {
			
			if ($row['paiement_type'] == 'ATTENTE') $total_a_payer += round(calcul_frais_cb($row['tarif'],$total_a_payer),2); else $total_a_payer += round(calcul_frais_cheque($row['tarif'],$total_a_payer),2);;
		}
		*/
		$champ['idInternauteReferent'] =  	"M-".$row['idInternauteReferent'];
		$champ['cout_total'] =  	$row['montant'];
		$champ['frais_cb'] =  	$row['frais_cb'];
		$champ['montant_inscription_ref'] =  	$row['cout'] + $row['participation'];
		$champ['montant_inscription_participation_ref'] =  	$row['participation'];
		return $champ;
	}
}
function internautes_enfants ($reference,$id_epreuve) {

	$reference =substr($reference, 2);
	$query = "SELECT idInscriptionEpreuveInternautes FROM r_internautereferent WHERE idInternauteReferent = ".$reference." AND idEpreuve = ".$id_epreuve;
	$result = $mysqli->query($query );
	$row=mysqli_fetch_array($result);
	$champs=explode("|",$row['idInscriptionEpreuveInternautes']);
	$internautes = array();
	$cpt=0;
		foreach ($champs as $key=>$idInscriptionEpreuveInternaute) {
			
			/*
			$query_int_ref = " SELECT riei.montant_inscription, ri.nomInternaute, ri.prenomInternaute FROM  r_inscriptionepreuveinternaute  as riei ";
			$query_int_ref .= " INNER JOIN r_internautereferent as rir ON rir.idInternauteref = riei.idInternaute ";
			$query_int_ref .= " INNER JOIN r_internaute as ri ON riei.idInternaute = ri.idInternaute";
			$query_int_ref .= " INNER JOIN r_insc_internautereferent as riir ON rir.idInternauteReferent = riir.idInternauteReferent ";
			$query_int_ref .= " WHERE rir.idInternauteref = ".$idInscriptionEpreuveInternaute ;
			echo $query_int_ref .= " ORDER BY ri.nomInternaute ASC ";
			//echo $query_int_ref .= " LIMIT 1 ";	
			$result = $mysqli->query($query_int_ref);
			$row=mysqli_fetch_array($result);
			*/
			
			$query_riei  ="SELECT participation, montant_inscription, ri.nomInternaute, ri.prenomInternaute FROM r_inscriptionepreuveinternaute as riei";
			$query_riei .=" INNER JOIN r_internaute as ri ON riei.idInternaute = ri.idInternaute";
			//$query_riei .=" INNER JOIN r_epreuveparcourstarif as rept ON riei.idEpreuveParcoursTarif = rept.idEpreuveParcoursTarif";
			//$query_riei .=" INNER JOIN r_epreuve as re ON riei.idEpreuve = re.idEpreuve ";
			//$query_riei .=" WHERE idEpreuve = ".$id_epreuve." ";
			$query_riei .=" WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute." ";
			$query_riei .=" AND paiement_type NOT IN ('SUPPRESSION','REMBOURSE','A REMBOURSE')";
			$result= $mysqli->query($query_riei);
			$row=mysqli_fetch_array($result);
			//$row_riei=mysqli_fetch_row($result_riei);
			
		
			
				$internautes[$cpt]['patronyme'] = $row['nomInternaute']." ".$row['prenomInternaute'];
				$internautes[$cpt]['montant_inscription'] = $row['montant_inscription'];
				$internautes[$cpt]['participation'] = $row['participation'];
				$cpt++;

			//echo "user : ".$idInscriptionEpreuveInternaute." - prix : ".$row_riei[0]."</br>";
			//$total_a_payer += $row_riei[0];
			
			
			//if ($cpt > 1) $concatene .="|";
			//$concatene .= ($champ + $idInternauteAdd);
			//echo $champ."</br>";
			//$cpt++;

		}
		//print_r($internautes);
		return $internautes;

}
function internaute_referent_internautes_ref ($reference ) {
/*
	$idInternauteRef = 172371;
	$id_epreuve = 4031;
	$id_parcours = 7069;
	*/
	$champ = array();
	$query_int_ref = " SELECT riei.idEpreuveParcours, riei.idInscriptionEpreuveInternaute, rir.idInternauteref, rir.idInternauteInscriptionref, rir.idInscriptionEpreuveInternautes, riei.montant_inscription, re.payeur, riir.idInternauteReferent,riir.paiement_type,re.cout_paiement_cb,rir.idInternautes FROM r_internautereferent as rir ";
	$query_int_ref .= " INNER JOIN r_inscriptionepreuveinternaute  as riei ON rir.idInternauteInscriptionref = riei.idInscriptionEpreuveInternaute ";
	$query_int_ref .= " INNER JOIN r_insc_internautereferent as riir ON rir.idInternauteReferent = riir.idInternauteReferent ";
	$query_int_ref .= " INNER JOIN r_epreuve as re ON riir.idEpreuve = re.idEpreuve ";
	//$query_int_ref .= " INNER JOIN r_internaute as ri ON rir.idInternauteRef = ri.idInternaute ";
	//$query_int_ref .= " INNER JOIN b_paiements as bp ON CONCAT('M-',rir.idInternauteReferent) = bp.reference ";
	//$query_int_ref .= " WHERE riir.idEpreuveParcours LIKE CONCAT('%',".$id_parcours.",'%') ";
	//$query_int_ref .= " AND riir.idEpreuve = ".$id_epreuve." ";
	//echo $query_int_ref .= " AND rir.idInscriptionEpreuveInternautes LIKE CONCAT('%',".$idInscriptionEpreuveInternautes.",'%') ";
	//$query_int_ref .= " AND rir.idInternauteref = ".$idInternauteRef ;
	//$query_int_ref .= " WHERE riei.idInscriptionEpreuveInternaute = ".$idInternauteRef ;
	$query_int_ref .= " WHERE riir.idInternauteReferent = ".addslashes($reference);
	//$query_int_ref .= " AND rir.idInternautes LIKE CONCAT('%',".$idInternaute.",'%') ";
	//$query_int_ref .= " AND riir.paiement_type NOT IN ('SUPPRESSION','REMBOURSE') ";
	$query_int_ref .= " AND riei.montant_inscription is NOT NULL ";
	//$query_int_ref .= " ORDER BY rir.idInternauteReferent ASC ";
	//$query_int_ref .= " LIMIT 1 ";	
	$result = $mysqli->query($query_int_ref);
	$row=mysqli_fetch_array($result);
	$champ['idInternauteref'] = $row['idInternauteref'];
	$champ['idEpreuveParcours'] = $row['idEpreuveParcours'];
	$champ['idInternautes'] = $row['idInternautes'];
	$champ['idInscriptionEpreuveInternautes'] = $row['idInscriptionEpreuveInternautes'];
	$champ['idInscriptionEpreuveInternaute'] = $row['idInternauteInscriptionref'];
	$champ['idInternauteReferent'] = $reference;
	$champ['montant_inscription'] = $row['montant_inscription'];
	if ($row != FALSE) return $champ;
}
function internaute_referent_internautes ($idInternauteRef ) {
/*
	$idInternauteRef = 172371;
	$id_epreuve = 4031;
	$id_parcours = 7069;
	*/
	$champ = array();
	$query_int_ref = " SELECT riei.idInscriptionEpreuveInternaute, rir.idInternauteref, rir.idInscriptionEpreuveInternautes, riei.montant_inscription, re.payeur, riir.idInternauteReferent,riir.paiement_type,re.cout_paiement_cb,rir.idInternautes, riir.idEpreuveParcours, riir.montant, riir.frais_cb FROM r_internautereferent as rir ";
	$query_int_ref .= " INNER JOIN r_inscriptionepreuveinternaute  as riei ON rir.idInternauteInscriptionref = riei.idInscriptionEpreuveInternaute  ";
	$query_int_ref .= " INNER JOIN r_insc_internautereferent as riir ON rir.idInternauteReferent = riir.idInternauteReferent ";
	$query_int_ref .= " INNER JOIN r_epreuve as re ON riir.idEpreuve = re.idEpreuve ";
	//$query_int_ref .= " INNER JOIN r_internaute as ri ON rir.idInternauteRef = ri.idInternaute ";
	//$query_int_ref .= " INNER JOIN b_paiements as bp ON CONCAT('M-',rir.idInternauteReferent) = bp.reference ";
	//$query_int_ref .= " WHERE riir.idEpreuveParcours LIKE CONCAT('%',".$id_parcours.",'%') ";
	//$query_int_ref .= " AND riir.idEpreuve = ".$id_epreuve." ";
	//echo $query_int_ref .= " AND rir.idInscriptionEpreuveInternautes LIKE CONCAT('%',".$idInscriptionEpreuveInternautes.",'%') ";
	//$query_int_ref .= " AND rir.idInternauteref = ".$idInternauteRef ;
	$query_int_ref .= " WHERE riei.idInscriptionEpreuveInternaute = ".$idInternauteRef ;
	//$query_int_ref .= " AND rir.idInternautes LIKE CONCAT('%',".$idInternaute.",'%') ";
	//$query_int_ref .= " AND riir.paiement_type NOT IN ('SUPPRESSION','REMBOURSE') ";
	$query_int_ref .= " AND riei.montant_inscription is NOT NULL ";
	//$query_int_ref .= " ORDER BY rir.idInternauteReferent ASC ";
	//$query_int_ref .= " LIMIT 1 ";	
	$result = $mysqli->query($query_int_ref);
	$row=mysqli_fetch_array($result);
	$champ['idInternauteref'] = $row['idInternauteref'];
	$champ['idInternautes'] = $row['idInternautes'];
	$champ['idInscriptionEpreuveInternautes'] = $row['idInscriptionEpreuveInternautes'];
	$champ['idEpreuveParcours'] = $row['idEpreuveParcours'];
	$champ['paiement_type'] = $row['paiement_type'];
	$champ['montant'] = $row['montant'];
	$champ['frais_cb'] = $row['frais_cb'];
	$champ['idInscriptionEpreuveInternaute'] = $row['idInscriptionEpreuveInternaute'];
	$champ['idInternauteReferent'] = $row['idInternauteReferent'];
	$champ['montant_inscription'] = $row['montant_inscription'];
	if ($row != FALSE) return $champ;
}

function internaute_referent ($idInternauteRef ) {
/*
	$idInternauteRef = 172371;
	$id_epreuve = 4031;
	$id_parcours = 7069;
	*/
	$champ = array();
	$query_int_ref = " SELECT  rir.idInternauteReferent, rir.idInternauteref, rir.idInternautes FROM r_internautereferent as rir ";
	$query_int_ref .= " WHERE rir.idInscriptionEpreuveInternautes LIKE '%".$idInternauteRef."%'" ;

	$result = $mysqli->query($query_int_ref);
	$row=mysqli_fetch_array($result);
	$champ['idInternauteReferent'] = $row['idInternauteReferent'];
	$champ['idInternauteref'] = $row['idInternauteref'];
	$champ['idInternautes'] = $row['idInternautes'];
	if ($row != FALSE) return $champ;
}

function info_epreuve_perso($idEpreuve)	{
	
	$css ='';
	
	$query_ep="SELECT * from r_epreuveperso WHERE idEpreuve = ".$idEpreuve;
	$result_ep = $mysqli->query($query_ep);
	$row_ep=mysqli_fetch_array($result_ep);
	
	if (!empty($row_ep['panel_color'])) {
		
		$css= '.panel-inverse > .panel-heading { background:'.$row_ep['panel_color'].'}';
	}
	
	if ($row_ep['image_fond'] != 0) {
		$query_eif  = "SELECT nom_fichier ";
		$query_eif .= "FROM r_epreuvefichier ";
		$query_eif .= "WHERE idEpreuveFichier = ".$row_ep['image_fond']." ";
		$query_eif .= "AND type = 'photo_insc_fond'";
		$result_eif = $mysqli->query($query_eif);
		$row_eif=mysqli_fetch_array($result_eif);
		
		$css .='.bg-image { background: rgba(0, 0, 0, 0) url("admin/fichiers_epreuves/'.$row_eif['nom_fichier'].'") no-repeat fixed center center; }';
		
	}
	return $css;
	
	
}
function inscription_perso($idEpreuve,$idEpreuveParcours)	{
	
	$champs=array();
	
	$query_ep="SELECT groupe, codeActivation from r_epreuveperso_pre WHERE idEpreuve = ".$idEpreuve;
	$query_ep .= " AND idEpreuveParcours = ".$idEpreuveParcours;
	$query_ep .= " AND dateDebut <=  NOW() ";
	$query_ep .= "AND dateFin >=  NOW()";
	$result_ep = $mysqli->query($query_ep);
	$row_ep=mysqli_fetch_array($result_ep);
	
	if ($row_ep != FALSE) {
		
		$champs['codeActivation'] = $row_ep['codeActivation'];
		$champs['groupe'] = $row_ep['groupe'];
		return $champs;
		
	}
	
	
}
function calcul_frais_remboursement($somme)
{
	
	$taux = 3;
	$frais_remboursement = ($somme/100)*$taux;
	return $frais_remboursement;

	
	
}
function extract_champ_epreuve_internaute ($champ, $idInscriptionEpreuveInternaute) {

		$query  = "SELECT ".$champ;
		$query .= " FROM r_inscriptionepreuveinternaute ";
		$query .=" WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; }
}

function tarif_en_cours ($idEpreuve,$idParcours,$tarif, $date='NOW()') {
	
			$champs=array();
			$query_tarif = "SELECT idEpreuveParcoursTarif, tarif FROM `r_epreuveparcourstarif` WHERE (".$date." BETWEEN `dateDebutTarif` AND `dateFinTarif`) ";
			$query_tarif .= " AND idEpreuve = ".$idEpreuve." ";
			$query_tarif .=" AND idEpreuveParcours = ".$idParcours;
			$query_tarif .= " AND tarif = ".$tarif." ";
			//echo $row_user['idEpreuveParcours']." ".$row_user['idInscriptionEpreuveInternaute']." ".$query_tarif."</br>";
			$result_tarif = $mysqli->query($query_tarif);
			$row_tarif= mysqli_fetch_array($result_tarif);
			//print_r($row_tarif);
			if ($row_tarif !=FALSE) {
					$champs['idEpreuveParcoursTarif'] = $row_tarif['idEpreuveParcoursTarif'];
					$champs['tarif'] = $row_tarif['tarif'];
					//print_r($champs);
					return $champs;
			}
			
}

function format_url($chaine) { 

	// en minuscule
    $chaine=strtolower($chaine);
	
	// supprime les caracteres speciaux
    $accents = Array("/é/", "/è/", "/ê/","/ë/", "/ç/", "/à/", "/â/","/á/","/ä/","/ã/", "/å/", "/î/", "/ï/", "/í/", "/ì/", "/ù/", "/ô/", "/ò/", "/ó/", "/ö/");
    $sans = Array("e", "e", "e", "e", "c", "a", "a","a", "a","a", "a", "i", "i", "i", "i", "u", "o", "o", "o", "o");
    $chaine = preg_replace($accents, $sans, $chaine);  
    $chaine = preg_replace('#[^A-Za-z0-9]#', '-', $chaine);
 
   // Remplace les tirets multiples par un tiret unique
   $chaine = ereg_replace( "\-+", '-', $chaine );
   
   // Supprime le dernier caractère si c'est un tiret
   $chaine = rtrim( $chaine, '-' );
 
    while (strpos($chaine,'--') !== false) 
		$chaine = str_replace('--', '-', $chaine);
 
    return $chaine; 
	
}

function histo_back_end($idEpreuve, $idEpreuveParcours, $idInscriptionEpreuveInternaute,$idInternaute,$action) {
	
	$typeidInternauteConnect = $_SESSION['typeInternaute'];
	$idInternauteConnect = $_SESSION["log_id"];
	$login = $_SESSION["log_log"];
	$inc = detect_info_client();
	$ipidInternauteConnect = $inc['ip_client'];
	
	$query_b = "INSERT INTO `r_histo_action_backend` (`idEpreuve`, `idEpreuveParcours`, `idInternauteConnect`, login, `typeidInternauteConnect`, `ipidInternauteConnect`, `idInscriptionEpreuveInternaute`, `idInternaute`, `action`, date) ";
	$query_b .= "VALUES (".$idEpreuve.", ".$idEpreuveParcours.", ".$idInternauteConnect.", '".$login."','".$typeidInternauteConnect."', '".$ipidInternauteConnect."', ".$idInscriptionEpreuveInternaute.", ".$idInternaute.",'".$action."', NOW())";
	$result_b = $mysqli->query($query_b);	
	
}
?>