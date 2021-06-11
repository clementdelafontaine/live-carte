<?php

function login_internaute_frontend ($login,$pass,$id=0) {

		//echo "login: ".$login;
		/*
		if($login == "admin")
		{
			echo $query  = "SELECT val FROM r_constant WHERE cle='passroot' AND val='".$pass."' ";
			$result = $mysqli->query($query);
			array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);

			if(($result != FALSE) && ($row=mysqli_fetch_array($result)) != FALSE)
			{
				$_SESSION["log_root"] = "oui";
				$_SESSION["typeInternaute"] = "admin";
				$_SESSION['unique_id_session'] = md5(uniqid());
				//require("admin_validation.php");
				//$p = admin_validation($p);
			}
		}
		*/

			$query  = "SELECT idInternaute, prenomInternaute, nomInternaute, sexeInternaute, emailInternaute, naissanceInternaute, clubInternaute, villeInternaute, index_telephone, telephone, typeInternaute, organisateur, coureur, fournisseur, avatar, adresseInternaute, cpInternaute, villeLatitude, villeLongitude, paysInternaute, dateConsultation, peremption_ski, peremption_vel, peremption_cap, fichier_cap ";
			$query .= "FROM r_internaute ";
			$query .= "WHERE loginInternaute='".addslashes($login)."' ";
			//if ($login=='admin') $query .= "AND passInternaute='".hhp($_POST['pass'])."' "; else $query .= "AND passInternaute='".addslashes($_POST['pass'])."' ";
			if ($id==0) $query .= "AND passInternaute='".hhp($_POST['pass'])."' "; else $query .= "AND idInternaute='".$id."' ";
			$query .= "AND validation='oui';";
			$result = $mysqli->query($query);
			
			if(($result != FALSE) && ($row=mysqli_fetch_array($result)) != FALSE)
			{
				$_SESSION["log_id"] = $row["idInternaute"];
				$_SESSION["prenomInternaute"] = $row["prenomInternaute"];
				$_SESSION["nomInternaute"] = $row["nomInternaute"];
				$_SESSION["sexeInternaute"] = $row["sexeInternaute"];
				$_SESSION["emailInternaute"] = $row["emailInternaute"];
				$_SESSION["naissanceInternaute"] = $row["naissanceInternaute"];
				$_SESSION["clubInternaute"] = $row["clubInternaute"];
				$_SESSION["villeInternaute"] = $row["villeInternaute"];
				$_SESSION["index_telephone"] = $row["index_telephone"];
				$_SESSION["telephone"] = $row["telephone"];
				$_SESSION["log_log"] = $login;
				$_SESSION["typeInternaute"] = $row["typeInternaute"];
				//if ($_SESSION["typeInternaute"] =='admin') $_SESSION["log_root"] = "oui";
				$_SESSION["log_coureur"] = $row["coureur"];
				$_SESSION["log_organisateur"] = $row["organisateur"];
				$_SESSION["log_fournisseur"] = $row["fournisseur"];
				$_SESSION["avatar"] = $row["avatar"];
				$_SESSION["adresseInternaute"] = $row["adresseInternaute"];
				$_SESSION["cpInternaute"] = $row["cpInternaute"];
				$_SESSION["villeLatitude"] = $row["villeLatitude"];
				$_SESSION["villeLongitude"] = $row["villeLongitude"];
				$_SESSION["paysInternaute"] = $row["paysInternaute"];
				$_SESSION["dateConsultation"] =$row['dateConsultation'];
		
				if (isset($row["peremption_ski"])) {
					$_SESSION["peremption_cert"] = "peremption_ski";
					$_SESSION["fichier_cert"] = 'fichier_ski';
					$_SESSION["type_cert"] = 4;
				}
				elseif (isset($row["peremption_tri"])) {
					$_SESSION["peremption_cert"] = "peremption_tri";
					$_SESSION["fichier_cert"] = 'fichier_tri';
					$_SESSION["type_cert"] = 1;
				}
				elseif (isset($row["peremption_vel"])) {
					$_SESSION["peremption_cert"] = "peremption_vel";
					$_SESSION["fichier_cert"] = 'fichier_vel';
					$_SESSION["type_cert"] = 2;
					
				}					
				elseif (isset($row["fichier_cap"])) {
					$_SESSION["peremption_cert"] = "peremption_cap";
					$_SESSION["fichier_cert"] = 'fichier_cap';
					$_SESSION["type_cert"] = 3;
					
				}

				$_SESSION['unique_id_session'] = md5(uniqid());
				//echo "TEST ".$SESSION["log_id"];
				
				$query  = "UPDATE r_internaute SET ";
				$query .= "dateConsultation='".date("Y-m-d H:i:s")."' ";
				$query .= "WHERE idInternaute='".$_SESSION["log_id"]."';";

				$result = $mysqli->query($query);
				array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
			} else {
				$erreur_login = $login;
			}
		
	
}

 
function hhp ($mdp) {
	$code = strlen($mdp);
	$code = ($code * 4)*($code/3);
	$sel = strlen($mdp);
	$sel2 = strlen($code.$mdp);
	$texte_hash = hash('sha256', $sel.$mdp.$sel2);
	$texte_hash_2 = hash('sha256', $texte_hash.$sel2);
	$final = $texte_hash.$texte_hash_2;
	substr($final , 7, 8);
	$final = strtoupper($final);
	return $final;
}
 
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
	//$xml = simplexml_load_file($request_url) or die("url not loading");
	$xml = simplexml_load_file($request_url);
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

function dateen2frv2($mydate,$wtime=0){
   
   if ($wtime == 0) {
		
		@list($date,$horaire)=explode(' ',$mydate);
		@list($jour,$mois,$annee)=explode('-',$date);
		@list($heure,$minute,$seconde)=explode(':',$horaire);
		$date= @date('d/m/Y',strtotime($mydate)).' à '.$heure.':'.$minute;
		return $date;
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
function extract_nom_groupe ($idEpreuvePersoPre) {

		$query  = "SELECT groupe ";
		$query .= " FROM r_epreuveperso_pre ";
		$query .=" WHERE idEpreuvePersoPre = ".$idEpreuvePersoPre;
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
		//echo $query." -###- ";
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; } else { return ''; }
	
}

function champ_inscrit_questiondiverse_commune_pre ($idEpreuve, $idChampsSupQuestionDiverse,$champ) {
	
		$query  = "SELECT ".$champ." ";
		$query .= "FROM r_insc_champssupquestiondiverse_commune_pre ";
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
			if (!isset($value)) $string .= $critere; else $string .= $critere."(".$value.")";
			
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
			if (!isset($value)) $string .= $critere; else $string .= $critere."(".$value.")";
			
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

function recup_champ_dotation_inscrit ($idEpreuve, $idEpreuveParcours, $idInternaute, $idInscriptionEpreuveInternaute, $language='FR') {
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

	$query  = "SELECT rcd.idChampsSupQuestionDiverse, label, value, ordre, verifie, date_verifie, type_champ ";
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
			
			
					if ($k=='label')
					{
						//echo $k."-".$row_questiondiverse[$i]."-";
						$tmp_lg = explode("|",$row[$i]);
						$cpt_lg = count($tmp_lg);
						//echo "---".$language;
						$lg_ko=0;
						if ($cpt_lg>1) 
						{
							if ($language=='EN')
							{
								if (isset($tmp_lg[1])) { $row[$i]=$tmp_lg[1]; } else { $lg_ko=1;$row[$i]=$tmp_lg[0]; }
							}
							elseif ($language=='DE')
							{
								if (isset($tmp_lg[2])) { $row[$i]=$tmp_lg[2]; } else { $lg_ko=1;$row[$i]=$tmp_lg[0]; }
							}
							elseif ($language=='IT')
							{
								if (isset($tmp_lg[3])) { $row[$i]=$tmp_lg[3]; } else { $lg_ko=1;$row[$i]=$tmp_lg[0]; }
							}
							elseif ($language=='ES')
							{
								if (isset($tmp_lg[4])) { $row[$i]=$tmp_lg[4]; } else { $lg_ko=1;$row[$i]=$tmp_lg[0]; }
							}
							else
							{
								$row[$i]=$tmp_lg[0];
							}
						}
						else
						{
								$lg_ko=1;
								$row[$i]=$tmp_lg[0];
						}

					}
					$champ_row_questiondiverse[$k] = $row[$i];
		}
		
		if ( $champ_row_questiondiverse['type_champ'] == 'FILE') {
						
			$champ_row_questiondiverse['value'] = questiondivers_file_existe_tmp($champ_row_questiondiverse['value']);
			if ($champ_row_questiondiverse['value'] != '') 
			{
				$color = '';
				if ($row['verifie']=='non') $color='info-danger';
				$champ_row_questiondiverse['value'] = $champ_row_questiondiverse['value'].' - Vérifié : <b><span class="'.$color.'">'.$row['verifie'].'</span></b>';
			}
			else
			{
				$champ_row_questiondiverse['value'] = '<b><span class="info-danger">Document non fourni</span></b>';
				
			}
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

	
	function recup_champ_dotation_inscrit_epreuve ($idEpreuve, $idInternaute, $idInscriptionEpreuveInternaute,$language='FR') {
	//echo $langage;
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
			//echo $k."-".$row[$i]."-";

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
					
					if ($k=='label')
					{
						//echo $k."-".$row[$i]."-";
						$tmp_lg = explode("|",$row[$i]);
						$cpt_lg = count($tmp_lg);
						//echo "---".$language;
						$lg_ko=0;
						if ($cpt_lg>1) 
						{
							if ($language=='EN')
							{
								if (isset($tmp_lg[1])) { $row[$i]=$tmp_lg[1]; } else { $lg_ko=1;$row[$i]=$tmp_lg[0]; }
							}
							elseif ($language=='DE')
							{
								if (isset($tmp_lg[2])) { $row[$i]=$tmp_lg[2]; } else { $lg_ko=1;$row[$i]=$tmp_lg[0]; }
							}
							elseif ($language=='IT')
							{
								if (isset($tmp_lg[3])) { $row[$i]=$tmp_lg[3]; } else { $lg_ko=1;$row[$i]=$tmp_lg[0]; }
							}
							elseif ($language=='ES')
							{
								if (isset($tmp_lg[4])) { $row[$i]=$tmp_lg[4]; } else { $lg_ko=1;$row[$i]=$tmp_lg[0]; }
							}
							else
							{
								$row[$i]=$tmp_lg[0];
							}
						}
						else
						{
								$lg_ko=1;
								$row[$i]=$tmp_lg[0];
						}

					}
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
		
			$taux_inscription = 0.025; // taux de 2.5% au dessus de 100 € si les frais ne sont pas fixes
			$taux_participation = 0.025; // taux de 2.5% pour les participations

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
				else if($tarif <= 100)
				{
					$frais_cb = 2.5;
					if($participation >0) $frais_cb += $participation*$taux_participation;
				}
				else if($tarif > 100)
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
	$query_info   .= "riit.idEpreuveParcoursTarif,riit.nomInternaute,riit.prenomInternaute,riit.emailInternaute,riit.dateInscription,riit.certificatMedical,riit.idSession,riit.codeSecurite, riit.autoParentale, riit.cout,riit.participation,riit.frais_cb,riit.frais_cheque,riit.reference_cheque,riit.idEpreuveParcoursTarifPromo, riit.code_promo,riit.valeur_code_promo,riit.place_promo, riit.valeur_place_promo,riit.info_diverses,riit.equipe,riit.groupe, riit.categorie, ";
	$query_info   .= "re.nomEpreuve, re.dateEpreuve, rep.nomParcours, rep.infoParcoursInscription, rep.horaireDepart, ";
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
//echo 	$query_info;


	
	$result_info = $mysqli->query($query_info);
	$row_info=mysqli_fetch_array($result_info);
	
	$row_info['montant'] = $cout;
	
	$row_info['mode_paiement'] = $type_paiement;
	//print_r($row_info);
	return $row_info;
}

function info_internaute_send_mail_test ($id_internaute,$cout,$type_paiement) {
	
	$query_info   = "SELECT DISTINCT rii.idInternaute, rii.idInscriptionEpreuveInternaute, rii.idEpreuve,rii.idEpreuveParcours as idParcours, ";
	$query_info   .= "rii.idEpreuveParcoursTarif,ri.nomInternaute,ri.prenomInternaute,ri.emailInternaute,rii.date_insc,ri.passInternaute, rii.verif_certif as certificatMedical, rii.verif_auto_parentale as autoParentale, rii.id_session as idSession,rii.paiement_montant,(rii.montant_inscription-rii.participation) as cout,rii.frais_cb,rii.participation, rii.frais_cheque,rii.info_cheque,rii.idEpreuveParcoursTarifPromo, rii.label_code_promo as code_promo,rii.montant_code_promo as valeur_code_promo,rii.place_promo, rii.valeur_place_promo, rii.info_diverses,rii.equipe, rii.groupe,rii.categorie,   ";
	$query_info   .= "re.nomEpreuve, re.dateEpreuve, rep.nomParcours, rep.horaireDepart, ";
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
	$query_info   .= "re.nomEpreuve, re.dateEpreuve, rep.nomParcours, ";
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

require_once('Browser.php');
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
		
	
	if (!empty($data_epreuve['emailInscription'])) { return $data_epreuve['emailInscription'];} else { return 'contact@Ats Sport.com'; }
}

function info_parcours($id_epreuve,$id_parcours,$relais=1,$select_parcours='') {		
		$date_et_heure_du_jour = strtotime(date('Y-m-d H:i:s'));
		
		$query  = "SELECT * FROM r_epreuveparcours ";
		$query .= "INNER JOIN r_typeparcours ON r_epreuveparcours.idTypeParcours = r_typeparcours.idTypeParcours ";
		$query .= "INNER JOIN r_epreuveparcourstarif ON r_epreuveparcours.idEpreuveParcours = r_epreuveparcourstarif.idEpreuveParcours ";
		$query .= "WHERE r_epreuveparcours.idEpreuve = ".$id_epreuve;
		
		if ($select_parcours !='') {
			$query .= " AND r_epreuveparcours.pre_selection= '".$select_parcours."'";
		}
		$query .= " AND dateDebutTarif <=  NOW() ";
		$query .= "AND dateFinTarif >=  NOW() ";
		//$query .= "AND (nb_dossard-nb_dossard_pris > 0 OR nb_dossard IS NULL)";
		//$query .= "AND visible_inscription ='oui'";
		
		if (!empty($id_parcours)) {
			$query .=" AND r_epreuveparcours.idEpreuveParcours= ".$id_parcours;
		}
		if ($relais==0) {
				$query .=" AND r_epreuveparcours.relais= 0";
		}
		$query .= " ORDER BY r_epreuveparcours.ordre_affichage ASC, r_epreuveparcourstarif.idEpreuveParcoursTarif ASC";
		$result_select_parcours = $mysqli->query($query);

		//print_r($info_parcours);
		return $result_select_parcours;

}
function info_parcours_profile($id_epreuve,$id_parcours,$relais=1) {		
		$date_et_heure_du_jour = strtotime(date('Y-m-d H:i:s'));
		
		$query  = "SELECT rep.idEpreuveParcours,rept.idEpreuveParcoursTarif,rep.nomParcours,rept.desctarif FROM r_epreuveparcours as rep ";
		$query .= "INNER JOIN r_epreuveparcourstarif rept ON rep.idEpreuveParcours = rept.idEpreuveParcours ";
		$query .= "WHERE rep.idEpreuve = ".$id_epreuve;
		//$query .= " AND dateDebutTarif <=  NOW() ";
		//$query .= "AND dateFinTarif >=  NOW()";
	
		if (!empty($id_parcours)) {
			$query .=" AND rep.idEpreuveParcours= ".$id_parcours;
		}
		if ($relais==0) {
				$query .=" AND rep.relais= 0";
		}
		//echo $query .= " ORDER BY rep.ordre_affichage ASC";
		$result_select_parcours = $mysqli->query($query);

		//print_r($info_parcours);
		return $result_select_parcours;

}
function nombre_dossard ($id_parcours) {

	$dossard_affecte = array();
								
	$query  = "SELECT dossard FROM r_inscriptionepreuveinternaute 
			   WHERE idEpreuveParcours = ".$id_parcours." AND paiement_type IN('CHQ','CB','AUTRE','GRATUIT') ORDER BY dossard ASC";	
	//$query ;														
	$result = $mysqli->query($query);
	$nb_dossard_deja_attribue = 0;
	while($row = mysqli_fetch_array($result)) 
	{
		//array_push($dossard_affecte,intval($row['dossard']));
		//if (intval($row['dossard']) != 0) $nb_dossard_deja_attribue++;
		if (intval($row['dossard']!=0)) array_push($dossard_affecte,intval($row['dossard']));
	}
	$nb_dossard_deja_attribue = count(array_unique($dossard_affecte));
	
	//Récupération du 1er dossard à attribuer sur le parcours
	$query = "SELECT dossardDeb, dossardFin, nbexclusion, dossards_exclus FROM r_epreuveparcours WHERE idEpreuveParcours = ".$id_parcours."";
	//echo $query;
	$result = $mysqli->query($query);
	$borne = mysqli_fetch_array($result);
	$dossardmin = $borne['dossardDeb'];
	$dossardmax = $borne['dossardFin'];
	if ($dossardmin > 0 && $dossardmax > 0 ) {
		$nb_dossard_du_parcours = ($dossardmax - $dossardmin)+1;
		
		$doss = $dossardmin;
		
		$nbexclusion = $borne['nbexclusion'];
		$plage_exclusion = explode(":",$borne['dossards_exclus']);
		
		$nb_plage_exclusion = count($plage_exclusion);
		$nb_exclusion = 0;
		$nb_dossards_exclus = 0;
		$nb_dossard_disponible = 0;

		$nb_dossard_dans_exlus = 0;
		foreach ($plage_exclusion as $pl)
		{
			
			$exclus = explode("-",$pl);
			$nb_exclusion += ($exclus[1] -$exclus[0])+1;
			for($e=$exclus[0]; $e<=$exclus[1]; $e++)
			{ array_push($dossard_affecte,intval($e)); }
				
				$query  = "SELECT count(dossard) FROM r_inscriptionepreuveinternaute 
							WHERE idEpreuveParcours = ".$id_parcours." AND paiement_type IN('CHQ','CB','AUTRE','GRATUIT') ";	
				$query .= " AND dossard  BETWEEN ".$exclus[0]." AND ".$exclus[1]." ";
					
				$result = $mysqli->query($query);
				$row=mysqli_fetch_array($result);
				//echo $query." ///// dans la plage  ".$exclus[0]." AND ".$exclus[1]." il y a : ".$row[0]." dossards attribués //////";
				//echo "##".$pl."##";			
			$nb_dossard_dans_exlus += $row[0];
			
		}

		//print_r($dossard_affecte);
		//$nb_dossard_disponible = ($nb_dossard_du_parcours - $nb_exclusion-$nb_dossard_deja_attribue);
		
		$nb_dossard_disponible = ($nb_dossard_du_parcours - $nb_dossard_deja_attribue-$nb_exclusion+$nb_dossard_dans_exlus);
		//echo $nb_dossard_disponible."--".$nb_dossard_du_parcours."++".$nb_dossard_deja_attribue."++".$nb_exclusion;
	}
	else
	{
		$nb_dossard_disponible = 1;
	}
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

function check_internaute_existant_v2 ($nom,$prenom,$sexe,$date_naissance,$email,$club,$adresse,$cp,$ville,$lat_ville,$long_ville,$pays,$index_telephone,$telephone,$code_inscription='') {

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
			$query .= "passInternaute= '".hhp($code_inscription)."', ";
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
	$query_int_ref = " SELECT riei.idInscriptionEpreuveInternaute, riei.groupe, rir.idInternauteref, rir.idInscriptionEpreuveInternautes, riei.montant_inscription, re.payeur, riir.idInternauteReferent,riir.paiement_type,re.cout_paiement_cb,rir.idInternautes, riir.idEpreuveParcours, riir.montant, riir.frais_cb FROM r_internautereferent as rir ";
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
	$champ['groupe'] = $row['groupe'];
	if ($row != FALSE) return $champ;
}

function internaute_referent_internautes_admin ($idInternauteRef ) {
/*
	$idInternauteRef = 172371;
	$id_epreuve = 4031;
	$id_parcours = 7069;
	*/
	$champ = array();
	$query_int_ref = " SELECT riei.idInscriptionEpreuveInternaute, riei.groupe, rir.idInternauteref, rir.idInscriptionEpreuveInternautes, riei.montant_inscription, re.payeur, riir.idInternauteReferent,riir.paiement_type,re.cout_paiement_cb,rir.idInternautes, riir.idEpreuveParcours, riir.montant, riir.frais_cb FROM r_internautereferent as rir ";
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
	//echo $query;
	$result = $mysqli->query($query_int_ref);
	$row=mysqli_fetch_array($result);
	$champ['idInternauteref'] = $row['idInternauteref'];
	$champ['idInternautes'] = $row['idInternautes'];
	$champ['idInscriptionEpreuveInternautes'] = $row['idInscriptionEpreuveInternautes'];
	$champ['idEpreuveParcours'] = $row['idEpreuveParcours'];
	$champ['paiement_type'] = $row['paiement_type'];
	$champ['montant_ats'] = $row['montant'];
	if ($row['payeur']=='coureur') {
		$champ['montant'] = $row['montant'] - $row['frais_cb'];
	}
	else 
	{
		$champ['montant'] = $row['montant'];
	}
	
	$champ['frais_cb'] = $row['frais_cb'];
	$champ['idInscriptionEpreuveInternaute'] = $row['idInscriptionEpreuveInternaute'];
	$champ['idInternauteReferent'] = $row['idInternauteReferent'];
	$champ['montant_inscription'] = $row['montant_inscription'];
	$champ['groupe'] = $row['groupe'];
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
	
	$champs=array();
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

		if( !empty( $row_eif['nom_fichier'] ) )
			$css .='.bg-image { background: rgba(0, 0, 0, 0) url("admin/fichiers_epreuves/'.$row_eif['nom_fichier'].'") no-repeat fixed center center; }';
		
	}
	$champs['css'] = $css;
	$champs['aff_nom_licence'] = $row_ep['aff_nom_licence'];
	$champs['url_image'] = 'admin/fichiers_epreuves/'.$row_eif['nom_fichier'];
	//print_r($champs);
	return $champs;
	
	
}
function inscription_perso($idEpreuve,$idEpreuveParcours='',$codeactivation='')	{
	
	$champs=array();
	
	$query_ep="SELECT idEpreuvePersoPre, groupe, codeActivation, idEpreuveParcours, paiement_indiv from r_epreuveperso_pre WHERE idEpreuve = ".$idEpreuve;
	if (!empty($idEpreuveParcours)) {
		$query_ep .= " AND idEpreuveParcours = ".$idEpreuveParcours;
	}
	if (!empty($codeactivation)) {
		$query_ep .= " AND codeActivation = '".$codeactivation."' ";
	}
	$query_ep .= " AND dateDebut <=  NOW() ";
	$query_ep .= "AND dateFin >=  NOW() ";
	$query_ep .= "AND active ='oui' ";
	$result_ep = $mysqli->query($query_ep);
	$row_ep=mysqli_fetch_array($result_ep);
	
	$inscription_groupe = extract_champ_epreuve('inscription_groupe',$idEpreuve);
	if ($row_ep != FALSE) {
		
		$champs['codeActivation'] = $row_ep['codeActivation'];
		$champs['groupe'] = $row_ep['groupe'];
		$champs['idEpreuveParcours'] = $row_ep['idEpreuveParcours'];
		$champs['inscription_groupe']=$inscription_groupe;
		$champs['idEpreuvePersoPre']= $row_ep['idEpreuvePersoPre'];
		$champs['paiement_indiv']= $row_ep['paiement_indiv'];
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
function extract_champ_epreuve_internaute_temp ($champ, $idInscriptionEpreuveInternaute) {

		$query  = "SELECT ".$champ;
		$query .= " FROM r_insc_internaute_temp ";
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
	$query_b .= "VALUES (".$idEpreuve.", ".$idEpreuveParcours.", ".$idInternauteConnect.", '".$login."','".$typeidInternauteConnect."', '".$ipidInternauteConnect."', '".$idInscriptionEpreuveInternaute."', ".$idInternaute.",'".$action."', NOW())";
	$result_b = $mysqli->query($query_b);	
	
}
function extract_champ_parcours_id_epreuve ($champ, $id_epreuve) {

		$query  = "SELECT ".$champ;
		$query .= " FROM r_epreuveparcours";
		$query .=" WHERE idEpreuve = ".$id_epreuve;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);

		if ($row != FALSE) { return $row[0]; }
}
function select_equipe($idEpreuve,$idEpreuveParcours='')
{
	
	$query = "SELECT idInscriptionEpreuveInternautes, nomInternaute, prenomInternaute, equipe, idInscriptionEpreuveInternaute, ri.idInternaute FROM r_inscriptionepreuveinternaute AS riei ";
	$query .= "INNER JOIN r_internaute AS ri ON  riei.idInternaute = ri.idInternaute ";
	$query .= "INNER JOIN r_internautereferent AS rir ON  riei.idInscriptionEpreuveInternaute = rir.idInternauteInscriptionref ";
	$query .= " WHERE equipe <> 'Aucune' AND paiement_type IN ('CB','CHQ','GRATUIT','AUTRE','ATTENTE CHQ','ATTENTE') ";
	$query .= " AND riei.idEpreuve = ".$idEpreuve ;
	if (!empty($idEpreuveParcours)) {
		$query .= " AND riei.idEpreuveParcours = ".$idEpreuveParcours." " ;
	}
	$query .= " ORDER BY date_insc DESC ";
	//echo $query;
	$result = $mysqli->query($query);
	$champs = array();
	
	while($row = mysqli_fetch_array($result))
	{
		if ($row['idInscriptionEpreuveInternautes'] !='') { $tmp = explode("|",$row['idInscriptionEpreuveInternautes']); $nb = count($tmp)+1;} else { $tmp=0; $nb = 1;}
		//if (empty($tmp)) echo "vide"; else echo "plein";
		//$nb = (count($tmp)+1);
		//print_r($tmp);
		//echo "$".$tmp."#".$row['idInscriptionEpreuveInternautes']."#-".$row['equipe']." nb : ".$nb."-";
		$champs[] = array('nom'=>$row['nomInternaute'], 'prenom'=>$row['prenomInternaute'], 'idInternaute'=>$row['idInternaute'],'idInscriptionEpreuveInternaute'=>$row['idInscriptionEpreuveInternaute'],'equipe' => $row['equipe'],'nb' => $nb );
	
	}
		//print_r($champs);
	return $champs;
	
}
function NomFichierValide($str, $charset='utf-8')
{
    
    $url = $str;
    $url = preg_replace('#Ç#', 'C', $url);
    $url = preg_replace('#ç#', 'c', $url);
    $url = preg_replace('#é|è|ê|ë#', 'e', $url);
    $url = preg_replace('#È|É|Ê|Ë#', 'E', $url);
    $url = preg_replace('#à|á|â|ã|ä|å#', 'a', $url);
    $url = preg_replace('#@|À|Á|Â|Ã|Ä|Å#', 'A', $url);
    $url = preg_replace('#ì|í|î|ï#', 'i', $url);
    $url = preg_replace('#Ì|Í|Î|Ï#', 'I', $url);
    $url = preg_replace('#ð|ò|ó|ô|õ|ö#', 'o', $url);
    $url = preg_replace('#Ò|Ó|Ô|Õ|Ö#', 'O', $url);
    $url = preg_replace('#ù|ú|û|ü#', 'u', $url);
    $url = preg_replace('#Ù|Ú|Û|Ü#', 'U', $url);
    $url = preg_replace('#ý|ÿ#', 'y', $url);
    $url = preg_replace('#Ý#', 'Y', $url);
	$str = $url;	
	
	$str = htmlentities($str, ENT_NOQUOTES, $charset);
    
	$str = preg_replace('`\s+`', '_', trim($str));
	$str = str_replace("'", "_", $str);
	$str = preg_replace('/\./', '_', $str);
	$str = preg_replace('`_+`', '_', trim($str));
	
	$str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
    $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
    
    return $str;
}

function inscription_mailing ($idInternaute,$idTypeEpreuve) 
{
	
	$query_b = "INSERT INTO `r_internautemailing` (`idInternaute`, `idTypeMailing`, `date`) ";
	$query_b .= " VALUES (".$idInternaute.", ".$idTypeEpreuve.", NOW())";
	$result_b = $mysqli->query($query_b);	
	
	
	
}


function inscription_chq_recu ($id_parcours, $id_epreuve, $id,$ref,$info_cheque='null') 
{
	$infos=array();
	$payeur = extract_champ_epreuve('payeur',$id_epreuve);
				$cout_paiement_cheque = extract_champ_epreuve('cout_paiement_cheque',$id_epreuve);
				$montant_des_inscriptions = 0;
				
				if($ref!='ok') {
					$dossard_referent = numerotation($id_parcours,$id_epreuve,$id);
					$query = " UPDATE r_inscriptionepreuveinternaute SET ";
					$query .= "dossard = ".$dossard_referent.", ";
					$query .= "paiement_type ='CHQ', ";
					$query .= "paiement_date = NOW() ";
					$query .= "WHERE idInscriptionEpreuveInternaute=".$id.";";
					$result = $mysqli->query($query);
				}
				
				$etat_paiement_type_referent  = extract_champ_epreuve_internaute('paiement_type',$id);
				$id_relais =  internaute_referent_internautes_admin($id);
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
									$q1  = "UPDATE r_inscriptionepreuveinternaute SET paiement_type ='CHQ', frais_cheque = ".$cout_paiement_cheque.", frais_cb = 0, paiement_date = NOW(),dossard = ".$dossard.",paiement_montant = ".($paiement_montant).",participation = ".($participation).", info_cheque='".$info_cheque."', montant_inscription = ".($paiement_montant)." ";
									$q1 .= "WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
									//echo "inscrit seul: ".$q1;
									$mysqli->query($q1);
								}
								else
								{
									$q1  = "UPDATE r_inscriptionepreuveinternaute SET paiement_type ='CHQ', frais_cb = 0, frais_cheque = 0, paiement_date = NOW(),dossard = ".$dossard.",paiement_montant = ".($paiement_montant).",participation = ".($participation).", info_cheque='".$info_cheque."', montant_inscription = ".($paiement_montant)." ";
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
			
			$infos['dossard_referent']=	$dossard_referent;
			$infos['$id_relais_ref']=$id_relais['idInscriptionEpreuveInternautes'];
			$infos['aff_html_dossards']=$aff_html_dossards;
			return $infos;
				 
	
}

function maj_tarif_reduc_place($id_tarif,$action='info',$idInscriptionEpreuveInternaute=0)
{
	$reduc = array();
	$query =  "SELECT nb_dossard, nb_dossard_pris, reduction FROM r_epreuveparcourstarif ";
	$query .= " WHERE idEpreuveParcoursTarif = ".$id_tarif;
	$query .= " AND nb_dossard IS NOT NULL";
	//echo $query;
	$result = $mysqli->query($query);
	$row=mysqli_fetch_array($result);

	if ($row != FALSE) { 
		
		if ($action=='info') { 
		
			//ECHO "INFO";
			$reduc['reduc_place'] = TRUE;
			$reduc['reduction_place'] = $row['reduction'];		
			return $reduc; 
		}
		else
		{
		$query_up = "UPDATE r_epreuveparcourstarif SET nb_dossard_pris = (nb_dossard_pris + 1) ";
		$query_up .= " WHERE idEpreuveParcoursTarif =".$id_tarif;
		$result_up = $mysqli->query($query_up);
		
		if ($result_up == TRUE && $idInscriptionEpreuveInternaute !=0) {
			$query_up ="UPDATE r_inscriptionepreuveinternaute SET ";
			$query_up .="place_promo =1, ";
			$query_up .="valeur_place_promo = ".$row['reduction']." ";
			$query_up .="WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute;
			$result_up = $mysqli->query($query_up);
		}
		$reduc['reduc_place'] = TRUE;
		$reduc['reduction_place'] = $row['reduction'];
		return $reduc;
		}
		
	}
	else
	{
			$reduc['reduc_place'] = FALSE;
			$reduc['reduction_place'] = 0;		
			return $reduc;
	}
	
}

function questiondivers_file_existe_tmp ($id_fichier,$type='insc_qd_file') {
	
	$rep_fichiers_inscription  ="fichiers_insc".DIRECTORY_SEPARATOR;
	
		$query  = "SELECT nom_fichier_affichage ";
		$query .= "FROM r_epreuvefichier";
		$query .=" WHERE idEpreuveFichier = ".$id_fichier;
		$query .=" AND type = '".$type."'";
		//echo $query;
		//exit();
		$result = $mysqli->query($query);
		$row= mysqli_fetch_row($result);

		if(!empty($row[0])) {
			return 	$row[0];
			
	} else { return $html =''; }

}

function champ_inscrit_questiondiverse_file ($idEpreuveParcours, $idInternaute, $lastid_epreuve_inscription, $idChampsSupQuestionDiverse,$champ) {
	
		$champs = array();
		$query  = "SELECT ".$champ.", idInscchampssupquestiondiverse, verifie, date_verifie ";
		$query .= "FROM r_insc_champssupquestiondiverse ";
		$query .="WHERE idEpreuveParcours = ".$idEpreuveParcours;
		$query .=" AND idInternaute = ".$idInternaute;
		$query .= " AND idInscriptionEpreuveInternaute=".$lastid_epreuve_inscription;
		$query .=" AND idChampsSupQuestionDiverse = ".$idChampsSupQuestionDiverse;
		//if ($idChampsSupQuestionDiverse == 88) echo $query;
		//echo $query;
		$result = $mysqli->query($query);
		//$row=mysqli_fetch_row($result);
		while (($row=mysqli_fetch_array($result)) != FALSE)
		{
				$champs[] =$row[$champ].'#'.$row['idInscchampssupquestiondiverse'].'#'.$row['verifie'].'#'.$row['date_verifie'];
				//$champs[] =$row[$champ];

		}
		if (isset($champs)) { return $champs; }
	
}

function code_tel ($code=33) {
	
					
	$query  = "SELECT iso,phonecode FROM pays2 ORDER BY iso ASC ";	
	//$query ;														
	$result = $mysqli->query($query);
	$aff = '';
	while($row = mysqli_fetch_array($result)) 
	{
		$aff .= '<option value="'.$row['phonecode'].'"';
		if ($row['phonecode']==$code) $aff .= ' selected ';
		elseif ($row['phonecode']==33) $aff .= ' selected ';
		//echo '<option value="'.$phonecode.'" >'.$iso.' (+'.$phonecode.')</option>';
		$aff .='>'.$row['iso'].' (+'.$row['phonecode'].')</option>';
		//echo $aff;	
	
	}
	//print_r($champs['iso']);
	return $aff;	
}

function code_tel_iso3 ($code=33) {
	
					
	$query  = "SELECT iso3,phonecode FROM pays2 ORDER BY name ASC ";	
	//$query ;														
	$result = $mysqli->query($query);
	$aff = '';
	while($row = mysqli_fetch_array($result)) 
	{
		$aff .= '<option value="'.$row['phonecode'].'"';
		if ($row['phonecode']==$code) $aff .= ' selected ';
		elseif ($row['phonecode']==33) $aff .= ' selected ';
		//echo '<option value="'.$phonecode.'" >'.$iso.' (+'.$phonecode.')</option>';
		$aff .='>'.$row['iso3'].' (+'.$row['phonecode'].')</option>';
		//echo $aff;	
	
	}
	//print_r($champs['iso']);
	return $aff;	
}

function code_tel_pays ($code=33) {
	
					
	$query  = "SELECT name,phonecode FROM pays2 ORDER BY name ASC ";	
	//$query ;														
	$result = $mysqli->query($query);
	$aff = '';
	while($row = mysqli_fetch_array($result)) 
	{
		$aff .= '<option value="'.$row['phonecode'].'"';
		if ($row['phonecode']==$code) $aff .= ' selected ';
		elseif ($row['phonecode']==33) $aff .= ' selected ';
		//echo '<option value="'.$phonecode.'" >'.$iso.' (+'.$phonecode.')</option>';
		$aff .='>'.$row['name'].' (+'.$row['phonecode'].')</option>';
		//echo $aff;	
	
	}
	//print_r($champs['iso']);
	return $aff;	
}

/*
function code_tel ($code=33) {
	
	$def=0;
	if ($code==33) $def=1;				
	$query  = "SELECT iso,phonecode FROM pays2 ORDER BY iso ASC ";	
	//$query ;														
	$result = $mysqli->query($query);
	$aff = '';
	while($row = mysqli_fetch_array($result)) 
	{
		$aff .= '<option value="'.$row['phonecode'].'"';
		if ($def != 1)		
		{	
			if ($row['phonecode']==intval($code)) { $aff .= ' selected '; }
			
			
		}
		else
		{
			if ($row['phonecode']==33) { $aff .= ' selected '; }
		}
		//echo '<option value="'.$phonecode.'" >'.$iso.' (+'.$phonecode.')</option>';
		$aff .='>'.$row['iso'].' (+'.$row['phonecode'].')</option>';
		//echo $aff;	
	
	}
	//print_r($champs['iso']);
	return $aff;
	
	
	
}
*/
function mail_langage($lng)
{
	
	$champs=array();
	
	if ($lng=='FR') {
		$champs['nom'] = "Nom";
		$champs['prenom'] = "Prénom";
		$champs['date_de_naissance'] = "Date de Naissance";
		$champs['adresse'] = "Adresse";
		$champs['code_postal'] = "Code postal";
		$champs['ville'] = "Ville";
		$champs['pays'] = "Pays";
		$champs['email'] ="Email";
		$champs['categorie'] ="Catégorie";
		$champs['club'] ="Club";
		$champs['licence'] ="Certificat Médical ou licence";
		$champs['auto_parentale'] ="Autorisation parentale";
		$champs['fourni'] ="Fourni";
		$champs['non_fourni'] ="Non fourni";
		$champs['pas_de_besoin'] ="Pas de besoin";
		$champs['mode_paiement'] ="Mode de Paiement";
		$champs['type_paiement_CB'] ="CB";
		$champs['montant_inscription'] ="Montant de l'inscription";
		$champs['montant_participation'] ="Montant des participations";
		$champs['montant_frais'] ="Montant des frais d'inscriptions";
		$champs['total_engagement'] ="Total de votre engagement";
		$champs['unite'] ="unité(s)";
		$champs['verifie_ffa'] ="Vérifié FFA";
		
		$champs['inscription_epreuve'] ="Inscription à l'épreuve";
		$champs['resume_information'] ="Résumé de vos informations";
		$champs['question_subs'] ="Vos réponses aux questions subsidiaires";
		$champs['voir_liste_inscrits'] ="Voir la liste des inscrits";
		$champs['editer_mon_inscription'] ="Editer mon inscription";
		$champs['code_securite'] ="Code de sécurité";
		$champs['mail_generique'] ="Ceci est un mail générique - ne pas y répondre";
		$champs['gratuit'] ="GRATUIT";
		$champs['code_promo']="Code promo utilisé";
		$champs['editer_info_inscription']= "Vous pouvez éditer vos informations (ajout certificat médical / effectuer un paiement etc ...) en cliquant sur le lien ci-dessous";
	}
	else
	{
		$champs['nom'] = "Surname";
		$champs['prenom'] = "First name";
		$champs['date_de_naissance'] = "Birth date";
		$champs['adresse'] = "Full address";
		$champs['code_postal'] = "Postal Code";
		$champs['ville'] = "City";
		$champs['pays'] = "Country";
		$champs['email'] ="Email";
		$champs['categorie'] ="Category";
		$champs['club'] ="Team";
		$champs['licence'] ="License";
		$champs['auto_parentale'] ="Parental authorization";
		$champs['fourni'] ="Provided";
		$champs['non_fourni'] ="not provided";
		$champs['pas_de_besoin'] ="No need";
		$champs['mode_paiement'] ="Payment method";
		$champs['type_paiement_CB'] ="Credit Card";
		$champs['montant_inscription'] ="Amount of registration";
		$champs['montant_participation'] ="Amount of shareholdings";
		$champs['montant_frais'] ="Amount of registration fees";
		$champs['total_engagement'] ="Total of your commitment";
		$champs['unite'] ="unit(s)";
		$champs['verifie_ffa'] ="Check FFA";
		
		$champs['inscription_epreuve'] ="Registration for the event";
		$champs['resume_information'] ="Summary of your information";
		$champs['question_subs'] ="Your answers to subsidiary questions";
		$champs['voir_liste_inscrits'] ="See the list of registered";
		$champs['editer_mon_inscription'] ="Edit my registration";
		$champs['code_securite'] ="Security code";
		$champs['mail_generique'] ="This is a generic mail - do not reply";
		$champs['gratuit'] ="FREE";
		$champs['code_promo']="Promo code used";
		$champs['editer_info_inscription']= "You can edit your information (add medical certificate / make a payment etc ...) by clicking on the link below";

	

	}
	return $champs;
	
	
}

function check_epreuve_redirection($id_epreuve) {
	
		$champs=array();
		$query  = "SELECT redirection, id_epreuve_redirection ";
		$query .= "FROM r_epreuve";
		$query .=" WHERE idEpreuve = ".$id_epreuve;
		$result = $mysqli->query($query);
		$row= mysqli_fetch_row($result);

		if($row[0]=='oui') 
		{
			$champs['nom'] = extract_champ_epreuve('nomEpreuve',$row[1]);
			$champs['id_epreuve'] = $row[1];	
			return $champs;
		}
}

function webservice_ffa ($nom,$prenom,$sexe,$date_n,$num_lic,$id_epreuve) {
/*
echo "NOM : ".$nom ."</br>";
echo "PRENOM : ".$prenom."</br>";
echo "SEXE : ".$sexe."</br>";
echo "ANNEE DE NAISSANCE : ".$date_n."</br>";
*/

$CMPCOD = extract_champ_epreuve('CMPCOD_FFA',$id_epreuve);
//if ($id_epreuve==4615) $CMPCOD = 195921;
//echo $CMPCOD = extract_champ_epreuve('nomEpreuve',$id_epreuve);
//echo 'https://webservicesffa.athle.fr/st_chrono/stchrono.asmx/STCHRONO?UID=YASC&MDP=Atsats1!&NUMREL='.$num_lic.'&NOM='.$nom .'&PRENOM='.$prenom .'&SEXE='.$sexe.'&DATENAI='.$date_n.'&CMPCOD='.$CMPCOD.'&ID_ACT_EXT=&ID_CMP_EXT=&MSG_RETOUR=HTTP/1.1';
//http://webservicesffa.athle.fr/st_chrono/stchrono.asmx/STCHRONO?UID=YASC&MDP=Carlos5!&NUMREL=&NOM=ROUX&PRENOM=DAVID&SEXE=M&DATENAI=1970&CMPCOD=195921&ID_ACT_EXT=&ID_CMP_EXT=&MSG_RETOUR=string%20HTTP/1.1
// Get cURL resource
$curl = curl_init();
curl_setopt($curl, CURLOPT_HEADER, 0);
// Set some options - we are passing in a useragent too here
curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => 'https://webservicesffa.athle.fr/st_chrono/stchrono.asmx/STCHRONO?UID=YASC&MDP=Atsats1!&NUMREL='.$num_lic.'&NOM='.$nom.'&PRENOM='.$prenom.'&SEXE='.$sexe.'&DATENAI='.$date_n.'&CMPCOD='.$CMPCOD.'&ID_ACT_EXT=&ID_CMP_EXT=&MSG_RETOUR=HTTP/1.1',
    //CURLOPT_USERAGENT => 'Codular Sample cURL Request'
));

//if ($id_epreuve==4615) echo $curl;
// Send the request & save response to $resp
$resp = curl_exec($curl);
//if ($id_epreuve==4615) print_r($resp);
//if ($id_epreuve==4615) echo "</br><b>SORTIE</b></br>";

if( $resp ) { 
		$champs=array();
		$list = explode(',',$resp);
		
		$champs['INFOFLG']=$list[0];
		$champs['RELFLG']=$list[1];
		$champs['MUTFLG']=$list[2];
		$champs['CERTIFFGL']=$list[3];
		$champs['CMPCOD']=$list[4];
		$champs['ID_ACT_EXT']=$list[5];
		$champs['ID_CMP_EXT']=$list[6];
		$champs['NUMREL']=$list[7];
		$champs['NOM']=$list[8];
		$champs['PRENOM']=$list[9];
		$champs['SEXE']=$list[10];
		$champs['DATE_NAI']=$list[11];
		$champs['NATCOD']=$list[12];
		$champs['RELCOD']=$list[13];
		$champs['DFINREL']=$list[14];
		$champs['CATCOD']=$list[15];
		$champs['STRCODNUM_CLU']=$list[16];
		$champs['STRNOMABR_CLU']=$list[17];
		$champs['STRNOM_CLU']=$list[18];
		$champs['STRCODNUM_CLUM']=$list[19];
		$champs['STRNOMABR_CLUM']=$list[20];
		$champs['STRNOM_CLUM']=$list[21];
		$champs['STRCODNUM_CLUE']=$list[22];
		$champs['STRNOMABR_CLUE']=$list[23];
		$champs['STRNOM_CLUE']=$list[24];
		$champs['STRNOMABR_DEP']=$list[25];
		$champs['STRNOMABR_LIG']=$list[26];
		$champs['MSG_RETOUR']=$list[27];
		$champs['MSG_RETOUR_TEXT']=str_replace('</string>','',$list[28]);
		
		//****<\/string>
		//print_r($list);
		/*
		//print_r($list);
		echo "INFOFLG  		flag de l’exactitude des informations : ".$list[0]."</br>";
		echo "RELFLG 		flag sur la validité de la relation LIC/TP/CF : ".$list[1]."</br>";
		echo "MUTFLG  		flag sur concernant la mutation de l’athlète  : ".$list[2]."</br>";
		echo "CERTIFFGL		flag concernant la nécessité de vérifier le certificat médical du coureur : ".$list[3]."</br>";
		
		
		echo "CMPCOD  		Code la compétition pour la demande : ".$list[4]."</br>";
		echo "ID_ACT_EXT  		Id Acteur de la BDD de la Ste de chrono pour cette demande : ".$list[5]."</br>";
		echo "ID_CMP_EXT  		Id Compétition de la BDD de la Ste de chrono pour cette demande : ".$list[6]."</br>";
		
		echo "NUMREL 		numéro de relation (lic, tp, cf) trouvée : ".$list[7]."</br>";
		
		echo "NOM  			nom de la personne trouvée : ".$list[8]."</br>";
		echo "PRENOM  		prénom de la personne trouvée  : ".$list[9]."</br>";
		echo "SEXE  			sexe de la personne trouvée : ".$list[10]."</br>";
		echo "DATE_NAI  		date de naissance de la personne trouvée : ".$list[11]."</br>";
		
		echo "NATCOD 		nationalité de la personne trouvée : ".$list[12]."</br>";
		echo "RELCOD 		type de licence de la personne trouvée : ".$list[13]."</br>";
		echo "DFINREL 		date de fin de la relation : ".$list[14]."</br>";
		echo "CATCOD		catégorie de la personne trouvée : ".$list[15]."</br>";
		
		echo "STRCODNUM_CLU 	numéro du club de la personne trouvée : ".$list[16]."</br>";
		echo "STRNOMABR_CLU 	nom abrégé du club de la personne trouvée : ".$list[17]."</br>";
		echo "STRNOM_CLU		nom du club de la personne trouvée : ".$list[18]."</br>";
		echo "STRCODNUM_CLUM 	numéro du club maitre de la personne trouvée : ".$list[19]."</br>";
		echo "STRNOMABR_CLUM 	nom abrégé du club maitre de la personne trouvée : ".$list[20]."</br>";
		echo "STRNOM_CLUM		nom du club maitre de la personne trouvée : ".$list[21]."</br>";
		echo "STRCODNUM_CLUE	numéro du club entreprise de la personne trouvée : ".$list[22]."</br>";
		echo "STRNOMABR_CLUE	nom abrégé du club entreprise de la personne trouvée : ".$list[23]."</br>";
		echo "STRNOM_CLUE		nom du club entreprise de la personne trouvée : ".$list[24]."</br>";
		echo "STRNOMABR_DEP 	libellé abrégé département de la personne trouvée : ".$list[25]."</br>";
		echo "STRNOMABR_LIG 	libellé abrégé ligue de la personne trouvée : ".$list[26]."</br>";
		
		echo "MSG_RETOUR 		message d’erreur, comprenant le code d’erreur entre parenthèse : ".$list[27]."</br>";
		*/
		
}
// Close request to clear up some resources
curl_close($curl);

return $champs;
}

function extract_relais ($id_epreuve,$equipe=0)
{
	
	$query = "SELECT idEpreuveParcours, relais FROM r_epreuveparcours WHERE idEpreuve = ".$id_epreuve." ORDER BY idEpreuveParcours";
	$result = $mysqli->query($query);
	$id_relais = 0;
	$cpt = 0;
	$cpt_parcours = 0;
	while($row = mysqli_fetch_array($result)) 
	{
		if ($row['relais'] > 0) { 
			$cpt++;
			if ($cpt ==1) { $id_relais = $row['idEpreuveParcours']; }
			
		 }
			$cpt_parcours++;
	}
	if ($cpt==$cpt_parcours) return $id_relais;
	elseif ($cpt > 0 && $equipe==1) return $id_relais; 
	else return 0;
}

//Hugo
function nom_type_epreuve()
{
	$typeEpreuve = array();
	$cpt=0;

	$query  = "SELECT idTypeEpreuve,nomTypeEpreuve FROM r_typeepreuve";
	$result = $mysqli->query($query);
	while( $row = mysqli_fetch_assoc( $result ) )
	{
		$typeEpreuve[$cpt]['idTypeEpreuve']  = $row['idTypeEpreuve'];
		$typeEpreuve[$cpt]['nomTypeEpreuve'] = $row['nomTypeEpreuve'];
		$cpt++;
	}

	return $typeEpreuve;
}

function region()
{
	$regions = array();
	$cpt = 0;

	$query  = "SELECT * FROM regions";
	$result = $mysqli->query($query);
	while( $row = mysqli_fetch_assoc( $result ) )
	{
		$regions[$cpt]['num_region'] = $row['num_region'];
		$regions[$cpt]['nom'] = $row['nom'];
		$cpt++;
	}

	return $regions;
}

function departement()
{
	$departements = array();
	$cpt = 0;

	$query  = "SELECT * FROM departements";
	$result = $mysqli->query($query);
	while( $row = mysqli_fetch_assoc( $result ) )
	{
		$departements[$cpt]['num_departement'] = $row['num_departement'];
		$departements[$cpt]['nom'] = $row['nom'];
		$cpt++;
	}

	return $departements;
}

function evenements_a_venir( $nb = 5 )
{
	$evenement = array();

	$qevenement  = "SELECT * FROM r_epreuve ";
	$qevenement .= "WHERE dateEpreuve >= '".date("Y-m-d")."' ";
	$qevenement .= "AND paiement_cb like '1' ";
	$qevenement .= "ORDER BY dateEpreuve ASC ";
	$qevenement .= "LIMIT ".$nb;

	$result = $mysqli->query($qevenement);
	while( $row = mysqli_fetch_assoc( $result ) )
	{
		//Affiche de l'épreuve
		$qPhoto = "SELECT nom_fichier FROM r_epreuvefichier WHERE idEpreuve = ".$row['idEpreuve']." AND type like 'photo_epreuve'";
		$rPhoto = $mysqli->query( $qPhoto );
		$photo  = mysqli_fetch_assoc( $rPhoto );

		$evenement[$cpt]['nomEpreuve'] = $row['nomEpreuve'];
		$evenement[$cpt]['dateEpreuve'] = $row['dateEpreuve'];
		$evenement[$cpt]['nomFichier'] = $photo['nom_fichier'];
		$evenement[$cpt]['idEpreuve'] = $row['idEpreuve'];
		$evenement[$cpt]['villeEpreuve'] = $row['ville'];
		$evenement[$cpt]['departementEpreuve'] = $row['departement'];
		$evenement[$cpt]['dateFinEpreuve'] = $row['DateFinEpreuve'];

		$qLive = "SELECT COUNT(*) as nb FROM chrono_resultats WHERE idEpreuve = ".$row['idEpreuve'];
		$rLive = $mysqli->query( $qLive );
		$live  = mysqli_fetch_assoc( $rLive ); 

		$evenement[$cpt]['live'] = ( $live['nb'] > 0 ? true:false );

		$cpt++;
	}

	return $evenement;
}

function derniers_resultats( $nb = 5 )
{
	$evenement = array();

	$qevenement  = "SELECT * FROM r_epreuve AS e ";
	$qevenement .= "JOIN r_resultatsparcours as ep ON e.idEpreuve = ep.idEpreuve ";
	$qevenement .= "WHERE e.dateEpreuve <= '".date("Y-m-d")."' ";
	$qevenement .= "GROUP BY e.idEpreuve ";
	$qevenement .= "ORDER BY e.dateEpreuve DESC ";
	$qevenement .= "LIMIT ".$nb;

	$result = $mysqli->query($qevenement);
	while( $row = mysqli_fetch_assoc( $result ) )
	{
		//Nombre de classés
		$count = "SELECT * FROM r_resultats WHERE idEpreuve = ".$row['idEpreuve'];
		$rcount = $mysqli->query( $count );

		//Affiche de l'épreuve
		$qPhoto = "SELECT nom_fichier FROM r_epreuvefichier WHERE idEpreuve = ".$row['idEpreuve']." AND type like 'photo_epreuve'";
		$rPhoto = $mysqli->query( $qPhoto );
		$photo  = mysqli_fetch_assoc( $rPhoto );

		$evenement[$cpt]['nomEpreuve'] = $row['nomEpreuve'];
		$evenement[$cpt]['dateEpreuve'] = $row['dateEpreuve'];
		$evenement[$cpt]['nomFichier'] = $photo['nom_fichier'];
		$evenement[$cpt]['idEpreuve'] = $row['idEpreuve'];
		$evenement[$cpt]['nb'] = mysqli_num_rows( $rcount );
		$cpt++;
	}

	return $evenement;
}

function insert_internaute( $nom, $prenom, $sexe="M", $naissance="01-01-1980", $email="", $adresse="", $cp="", $ville="", $pays="", $type="coureur", $telephone="" )
{
	$internaute  = "INSERT INTO r_internaute (loginInternaute, passInternaute, validation, sexeInternaute, nomInternaute, prenomInternaute, naissanceInternaute, ";
	$internaute .= "emailInternaute, adresseInternaute, cpInternaute, villeInternaute, paysInternaute, typeInternaute, dateInscription, dateConsultation, telephone) VALUES (";
	$internaute .= "'".addslashes( $nom ).date( "His" )."', ";
	$internaute .= "'".addslashes( code_inscription( 6 ) )."', ";
	$internaute .= "'non', ";
	$internaute .= "'".addslashes( $sexe )."', ";
	$internaute .= "'".addslashes( strtoupper( $nom ) )."', ";
	$internaute .= "'".addslashes( strtoupper( $prenom ) )."', ";
	$internaute .= "'".addslashes( date( "Y-m-d", strtotime( str_replace( "/", "-", $naissance ) ) ) )."', ";
	$internaute .= "'".addslashes( $email )."', ";
	$internaute .= "'".addslashes( $adresse )."', ";
	$internaute .= "'".addslashes( $cp )."', ";
	$internaute .= "'".addslashes( $ville )."', ";
	$internaute .= "'".addslashes( $pays )."', ";
	$internaute .= "'".$type."', ";
	$internaute .= "'".date("Y-m-d H:i:s")."', ";
	$internaute .= "'".date("Y-m-d H:i:s")."',";
	$internaute .= "'".addslashes( $telephone )."')";
	$result = $mysqli->query( $internaute );

	if( $result ) return $mysqli->insert_id;
	else return false;
}

function insert_epreuve( $data, $id )
{
	$epreuve  = "INSERT INTO r_epreuve (idTypeEpreuve, nomEpreuve, dateEpreuve, departement, idInternaute, valide, ";
	$epreuve .= "nbParticipantsAttendus, nomStructureLegale, siteInternet, description, ville, payeur, administrateur) VALUES (";
	$epreuve .= "'".$data['type_epreuve']."', ";
	$epreuve .= "'".addslashes( $data['nomepreuve'] )."', ";
	$epreuve .= "'".date( "Y-m-d", strtotime( $data['date'] ) )."', ";
	$epreuve .= "'".addslashes( $data['departement'] )."', ";
	$epreuve .= $id.", ";
	$epreuve .= "'oui', ";
	$epreuve .= "'".addslashes( $data['participation'] )."', ";
	$epreuve .= "'".addslashes( $data['structure'] )."', ";
	$epreuve .= "'".addslashes( $data['site'] )."', ";
	$epreuve .= "'".addslashes( $data['description'] )."', ";
	$epreuve .= "'".addslashes( $data['ville'] )."', ";
	$epreuve .= "'coureur', ";		
	$epreuve .= "185009)";
	$result = $mysqli->query( $epreuve );

	if( $result )
	{
		$idEpreuve = $mysqli->insert_id;
		$separator_fonction = code_separator(5);
		$separator_champ = code_separator(5);
		$separator_parcours = code_separator(5);
								
		$query_separator  = "INSERT INTO r_insc_champ_separator ";
		$query_separator .= "(idEpreuve, value_fonction, value_champ, value_parcours) VALUES (";
		$query_separator .= $idEpreuve.",";
		$query_separator .= "'".$separator_fonction."',";
		$query_separator .= "'".$separator_champ."',";
		$query_separator .= "'".$separator_parcours."')";
		$result_query_separator = $mysqli->query( $query_separator );

		return true;
	}
	else
	{
		return false;
	}
}

function inscription_libre_newsletter( $email, $types, $categorie, $departement, $zone )
{
	foreach( $types as $type )
	{
		$query  = "INSERT INTO r_internautemailing_libre ";
		$query .= "(email, typeepreuve, typeinfo, departement, zone_geographique, date) VALUES (";
		$query .= "'".$email."',";
		$query .= $type.",";
		$query .= "'".$categorie."',";
		$query .= "'".$departement."',";
		$query .= "'".$zone."',";
		$query .= "NOW() )";
		$result = $mysqli->query( $query );
	}
}
function internaute_inscription_multiple_v2 ($id_epreuve,$id_parcours,$idInternaute,$idInscriptionEpreuveInternauteRef) {
/*
	$id_epreuve = 4031;
	$id_parcours = 7069;
	$idInternaute= 187030;
	*/
	$champ = array();
	$query_int_ref = " SELECT rir.idInternauteRef,ri.nomInternaute, ri.prenomInternaute, ri.emailInternaute, riei.idInscriptionEpreuveInternaute FROM r_internautereferent as rir ";
	$query_int_ref .= " INNER JOIN r_inscriptionepreuveinternaute  as riei ON rir.idInternauteInscriptionref = riei.idInscriptionEpreuveInternaute  ";
	$query_int_ref .= " INNER JOIN r_insc_internautereferent as riir ON rir.idInternauteReferent = riir.idInternauteReferent ";
	$query_int_ref .= " INNER JOIN r_internaute as ri ON rir.idInternauteRef = ri.idInternaute ";
	//$query_int_ref .= " INNER JOIN b_paiements as bp ON CONCAT('M-',rir.idInternauteReferent) = bp.reference ";
	$query_int_ref .= " WHERE riir.idEpreuveParcours LIKE CONCAT('%',".$id_parcours.",'%') ";
	$query_int_ref .= " AND riir.idEpreuve = ".$id_epreuve." ";
	//echo $query_int_ref .= " AND rir.idInscriptionEpreuveInternautes LIKE CONCAT('%',".$idInscriptionEpreuveInternautes.",'%') ";
	//$query_int_ref .= " AND rir.idInternauteref = ".$idInternauteRef ;
	$query_int_ref .= " AND rir.idInternautes LIKE CONCAT('%',".$idInternaute.",'%') ";
	$query_int_ref .= " AND rir.idInscriptionEpreuveInternautes LIKE CONCAT('%',".$idInscriptionEpreuveInternauteRef.",'%') ";
	$query_int_ref .= " AND riir.paiement_type IN ('ATTENTE','ATTENTE CHQ','CB','GRATUIT','CHQ','REMBOURSE','A REMBOURSER','AUTRE') ";
	//$query_int_ref .= " ORDER BY rir.idInternauteReferent ASC ";
	//$query_int_ref .= " LIMIT 1 ";	
	$result = $mysqli->query($query_int_ref);
	$row=mysqli_fetch_array($result);
	//if ($idInternaute == 187030) echo $query_int_ref;
	//$paiement = $row['ri.nomInternaute'].
	//if(!empty($row['prenomInternaute'])) 
		
	if(!empty($row['prenomInternaute'])) 
	{
		$champ['idInternauteRef'] = $row['idInternauteRef'];
		$champ['idInternauteInscriptionRef'] = $row['idInscriptionEpreuveInternaute'];
		//$champ['Ref'] = substr($row['prenomInternaute'], 0, 1).". ".$row['nomInternaute'];
		$champ['Ref'] = $row['prenomInternaute']." ".$row['nomInternaute'];
		return $champ;
	}
}

function internaute_ref ($idInscriptionEpreuveInternaute,$idEpreuve) { 
	
	 if ($_SESSION["typeInternaute"] == 'admin' || $_SESSION["typeInternaute"] == 'super_organisateur') $admin=1;
	
	$query_int_ref = " SELECT riir.montant, riei.paiement_type, riei.paiement_date, riei.observation, riei.commentaire, rir.idInternauteRef,rir.idInternautes,rir.idInternauteReferent,rir.idInscriptionEpreuveInternautes,riei.idEpreuveParcours FROM r_internautereferent as rir ";
	$query_int_ref .= " INNER JOIN r_insc_internautereferent as riir ON rir.idInternauteReferent = riir.idInternauteReferent ";
	$query_int_ref .= " INNER JOIN r_inscriptionepreuveinternaute as riei ON rir.idInternauteInscriptionref = riei.idInscriptionEpreuveInternaute ";
	$query_int_ref .= " WHERE rir.idInternauteInscriptionRef = ".$idInscriptionEpreuveInternaute;
	if (!empty($idEpreuve)) $query_int_ref .= " AND rir.idEpreuve = ".$idEpreuve;
	//$query_int_ref .= " AND rir.idInternautes IS NOT NULL ";
	$query_int_ref .= " ORDER BY idInternauteReferent DESC  ";
	//if ($idInscriptionEpreuveInternaute==214163) echo $query_int_ref;	
	//echo $query_int_ref;
	//$query_int_ref .= " AND rir.idEpreuve = ".$idEpreuve;
	$result = $mysqli->query($query_int_ref);
	$row=mysqli_fetch_array($result);
	//if ($idInscriptionEpreuveInternaute==202688) echo $query_int_ref;
	$internautes = array();

		$query_bp .= " SELECT montant, statut FROM b_paiements WHERE reference = CONCAT('M-','".$row['idInternauteReferent']."') ";
		$result_bp = $mysqli->query($query_bp);
		$row_bp=mysqli_fetch_array($result_bp);
		if($row_bp == FALSE || $row_bp['statut']=='ATTENTE') { $b_paiement = 'KO'; $statut = 'INCONNU'; } else { $b_paiement = 'OK'; $statut = $row_bp['statut'];}
		
	if($row['idInternautes'] != '') {
		$champs=explode("|",$row['idInternautes']);
		//if ($admin==1) $champs_inscriptions=explode("|",$row['idInscriptionEpreuveInternautes']);
		$champs_inscriptions=explode("|",$row['idInscriptionEpreuveInternautes']);
		//print_r($champs_inscriptions);
		
		
		
		$first=TRUE;
		//$tmp_internautes = "<small>";
		//$tmp_internautes = '<i class="fa fa-users"></i>';
		

		$tmp_internautes = '';
		$tmp_idinscriptionsinternautes = array();
		$tmp_noms_internautes = array();
		$cpt=0;
			foreach ($champs as $key=>$idInternaute) {
				
				$query_ri  ="SELECT ri.nomInternaute, ri.prenomInternaute FROM r_internaute as ri";
				//$query_riei .=" INNER JOIN r_epreuveparcourstarif as rept ON riei.idEpreuveParcoursTarif = rept.idEpreuveParcoursTarif";
				//$query_riei .=" INNER JOIN r_epreuve as re ON riei.idEpreuve = re.idEpreuve ";
				//$query_riei .=" WHERE idEpreuve = ".$id_epreuve." ";
				$query_ri .=" WHERE ri.idInternaute = ".$idInternaute." ";
				$result_ri = $mysqli->query($query_ri);
				$row_ri=mysqli_fetch_array($result_ri);
				//print_r($row_ri);
				//echo $row_ri['nomInternaute'];
				//echo "user : ".$idInscriptionEpreuveInternaute." - prix : ".$row_riei[0]."</br>";
				//if ($first == FALSE ) $tmp_internautes .= "</br>";
				if ($first == FALSE )$tmp_internautes .= ", ";

					$tmp_idinscriptionsinternautes[] = $champs_inscriptions[$cpt].";".$idInternaute;
					$tmp_noms_internautes[] = $row_ri['prenomInternaute']." ".$row_ri['nomInternaute'];

				//$tmp_internautes .= substr($row_ri['prenomInternaute'], 0, 1).". ".$row_ri['nomInternaute'];
				$tmp_internautes .= $row_ri['nomInternaute']." ".$row_ri['prenomInternaute'];
				if ($admin==1)  $tmp_internautes .= " ".$champs_inscriptions[$cpt];
				$cpt++;
				$first = FALSE;
				
				//if ($cpt > 1) $concatene .="|";
				//$concatene .= ($champ + $idInternauteAdd);
				//echo $champ."</br>";
	
	
			}
			//$tmp_internautes .= "</small>";
			$internautes['idRef'] = $row['idInternauteRef'];
			$internautes['idRefPaiement'] = $row['idInternauteReferent'];
			
			if ($row['paiement_type']=='CHQ' || $row['paiement_type']=='AUTRE' || $row['paiement_type']=='GRATUIT')
			{
				$internautes['idRefEtat'] = 'OK';
				$internautes['idRefMontant'] = $row['montant'];
			}
			else
			{
				$internautes['idRefEtat'] = $b_paiement;
				$internautes['idRefMontant'] = $row_bp['montant'];
			
			}

			$internautes['idRefMontantDate'] = $row['paiement_date'];
			$internautes['idRefStatut'] = $statut;
			$internautes['observation'] = $row['observation'];
			$internautes['commentaire'] = $row['commentaire'];
			$internautes['noms_Internautes'] = $tmp_internautes;
			$internautes['noms_Internautes_seuls'] = $tmp_noms_internautes;
			$internautes['idInscriptionEpreuveInternautes'] = $tmp_idinscriptionsinternautes;
	}
	else
	{
		$internautes['idRefPaiement'] = $row['idInternauteReferent'];
		$internautes['idRef'] = $row['idInternauteRef'];
		$internautes['idRefEtat'] = $b_paiement;
		$internautes['idRefMontant'] = $row_bp['montant'];
		$internautes['observation'] = $row['observation'];
		$internautes['commentaire'] = $row['commentaire'];
		$internautes['idRefStatut'] = $statut;
		$internautes['idEpreuveParcours'] = $row['idEpreuveParcours'];
		}
	//if ($idInscriptionEpreuveInternaute==214161) print_r($internautes);
	if(!empty($internautes['idRef'])) return $internautes;

}

function options_plus($idEpreuve,$idEpreuveParcours='',$idOptionsPlus='')	
{
	
	$champs=array();
	
	$query_ep="SELECT * FROM r_options_plus WHERE idEpreuve = ".$idEpreuve;
	if (!empty($idEpreuveParcours)) {
		$query_ep .= " AND idEpreuveParcours = ".$idEpreuveParcours;
	}
	if (!empty($idOptionsPlus)) {
		$query_ep .= " AND idOptionsPlus = '".$idOptionsPlus."' ";
	}
	$query_ep .= " AND dateDebut <=  NOW() ";
	$query_ep .= "AND dateFin >=  NOW() ";
	$query_ep .= "AND active ='oui' ";
	return $result_ep = $mysqli->query($query_ep);
	//$row_ep=mysqli_fetch_array($result_ep);
	
	//$inscription_groupe = extract_champ_epreuve('inscription_groupe',$idEpreuve);
	/*
	if ($row_ep != FALSE) {
		
		$champs['idOptionPlus'] = $row_ep['$idOptionPlus'];
		$champs['nom'] = $row_ep['nom'];
		$champs['label'] = $row_ep['label'];
		$champs['prix'] = $row_ep['prix'];
		$champs['qte'] = $row_ep['qte'];
		$champs['information']= $row_ep['information'];
		$champs['url_image']= $row_ep['url_image'];
		$champs['dateDebut']=$row_ep['dateDebut'];
		$champs['dateFin']= $row_ep['dateFin'];
		$champs['active']= $row_ep['active'];
		return $champs;
		
	}
	*/
	
}
?>