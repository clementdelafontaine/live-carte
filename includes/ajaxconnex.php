<?php 

require_once('includes.php');

require_once('functions.php');

require_once("functions_mail_n.php"); //Retiré pour la version locale


ini_set("display_errors", 0);
error_reporting(E_ALL);


//UPDATE `r_internaute` SET dateConsultation  = 0  WHERE idInternaute = 453820

		$fp = fopen("../logs_insc.txt","a");
		fputs($fp,"DATE : ".date("d/m/Y H-i-s")." : ".$_SERVER['REQUEST_URI']." - IP CLIENTS : ".get_ip()." \n");
		fputs($fp," : ------------- AJAX CONNEXION ------------------\n");
		
function check_internaute_existant_v3 ($nom,$prenom,$sexe,$date_naissance,$email,$code_inscription) {
global $mysqli;
	//naissanceInternaute
	//sexeInternaute
		$champ =array();
		$query  = "SELECT idInternaute, loginInternaute FROM r_internaute as ri"; 
		$query .= " WHERE UPPER(ri.nomInternaute) = UPPER('".$nom."') ";
		//$query .= " AND UPPER(ri.prenomInternaute) = UPPER('".$prenom."') ";
		$query .= " AND ri.naissanceInternaute = '".$date_naissance."' ";
		$query .= " AND ri.sexeInternaute = '".$sexe."' ";
		//if(!empty($email)) {
			$query .= " AND ri.emailInternaute = '".$email."' ";
		//}
		$query .= " LIMIT 1";
		//echo $query;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_array($result);
		
		$id_internaute = $row[0];
		
		//$pass_internaute = $row[1];
		//839E1709D776A6E3033A5FFFE2E1FFD0ED643FEC530AEA5B20BCC462469D00BD8F01E738C304096543A327AE3EAD36F012B085031917353D26A2BF8B9472E012
		$champ['id_internaute']=$row[0];
		$champ['loginInternaute']=$row[1];
		//$champ['pass_internaute']=$row[1];
		
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
			$query .= "DateConsultation = NOW() ";
			//}
			//$query .= "clubInternaute= '".addslashes_form_to_sql($club)."', ";
			//$query .= "adresseInternaute= '".addslashes_form_to_sql($adresse)."', ";
			//$query .= "cpInternaute= '".$cp."', ";
			//$query .= "ageLimite= ".((isset($_POST['epre_limit_age'][$j]) && $_POST['epre_limit_age'][$j] == 1)?1:0).", ";
			//$query .= "villeInternaute= '".addslashes_form_to_sql($ville)."', ";
			//$query .= "villeLatitude= '".addslashes_form_to_sql($lat_ville)."', ";
			//$query .= "villeLongitude= '".addslashes_form_to_sql($long_ville)."', ";
			//$query .= "paysInternaute= '".addslashes_form_to_sql($pays)."', ";
			//$query .= "natInternaute= '".addslashes_form_to_sql($nat)."', ";
			//$query .= "typeInternaute= '".addslashes_form_to_sql($_POST['insc_type_internaute'])."', ";
			//$query .= "index_telephone= '".addslashes_form_to_sql($index_telephone)."', ";
			//$query .= "telephone= '".addslashes_form_to_sql($telephone)."' ";
			//$query .= $query_update_certificat." = '".$date_certificat."'";
			//$query .= ")";
			$query .= " WHERE idInternaute= ".$champ['id_internaute']."";
			$result_query = $mysqli->query($query);

		/*** LOGS ****/
		fputs($fp," *function check_internaute_existant_v3 - UPDATE r_internaute SET : ".$query."\n");
		/*** LOGS ****/
		
			return $champ;
		}
}
if ($_POST['action']==3)
{

delete_inscription_internaute (0,$_POST['idInscriptionEpreuveInternaute'],$_POST['id_epreuve'],$_POST['id_parcours'],'');	
echo json_encode(array('supp' =>'oui' ));	
	
}
elseif ($_POST['action']==2)
{
	if (isset($_POST['value']) && isset($_POST['idInternaute']))
	{
		
		$queryu ="UPDATE r_internaute SET profilInternaute = '".$_POST['value']."' WHERE idInternaute = ".$_POST['idInternaute'];
		
		/*** LOGS ****/
		fputs($fp," _POST['action']==2 - UPDATE r_internaute SET profilInternaute : ".$queryuy."\n");
		/*** LOGS ****/
		
		$mysqli->query($queryu);
		
		$_SESSION["profilInternaute"] = $_POST['value'];
		
				if ($_POST['value']=='ski') {
					$_SESSION["peremption_cert"] = "peremption_ski";
					$_SESSION["fichier_cert"] = 'fichier_ski';
					$_SESSION["type_cert"] = 4;
					$_SESSION["file_new"] = 0;
					$_SESSION["profilInternaute_affiche"] = 'Skieur';
				}
				elseif ($_POST['value']=='tri') {
					$_SESSION["peremption_cert"] = "peremption_tri";
					$_SESSION["fichier_cert"] = 'fichier_tri';
					$_SESSION["type_cert"] = 1;
					$_SESSION["file_new"] = 0;
					$_SESSION["profilInternaute_affiche"] = 'Triathlète';
				}
				elseif ($_POST['value']=='vel') {
					$_SESSION["peremption_cert"] = "peremption_vel";
					$_SESSION["fichier_cert"] = 'fichier_vel';
					$_SESSION["type_cert"] = 2;
					$_SESSION["file_new"] = 0;
					$_SESSION["profilInternaute_affiche"] = 'Cycliste / VTTiste';
					
				}					
				else
				{
					$_SESSION["peremption_cert"] = "peremption_cap";
					$_SESSION["fichier_cert"] = 'fichier_cap';
					$_SESSION["type_cert"] = 3;
					$_SESSION["file_new"] = 1;
					$_SESSION["profilInternaute_affiche"] = 'Course à pied';
					
				}
		echo json_encode(array('update' =>'OK'));
		
		
		
	}
	
	
}
else
{
	if ($_POST['new_pass_user']==1) {
		
		$ref=$_POST['pass_temp'];
		$id_ref=$_POST['id_ref'];
		$query = "SELECT idInternaute, loginInternaute, nomInternaute, prenomInternaute, emailInternaute,naissanceInternaute FROM r_internaute WHERE ";
		$query .=" dateConsultation = 0 AND passInternaute = '".$ref."' AND idInternaute = ".$id_ref;
		$result = $mysqli->query($query);
		$count=mysqli_num_rows($result);
	
		if ($count > 0) {
	
			$row=mysqli_fetch_array($result);
			
				$query  = "UPDATE r_internaute SET ";
	
				$query .= "passInternaute= '".hhp($_POST['pass'])."', ";
				$query .= "DateConsultation = NOW() ";
				$query .= " WHERE idInternaute= ".$row['idInternaute']."";
				$result_query = $mysqli->query($query);
						
		/*** LOGS ****/
		fputs($fp," _POST['new_pass_user']==1 - UPDATE r_internaute SET : ".$query."\n");
		/*** LOGS ****/						
				//login_internaute_frontend ($login,$_POST['pass']);	
		
				//$row_info = info_internaute_send_mail ($reference,(($_GET['Montant']/100)),'CB');
				//$row_info['emailInternaute']= 'jf@chauveau.nom.fr';
				$donnees = array();
				$row_info = array();
	
				$row_info['emailInternaute']= $row["emailInternaute"];
				$row_info['nomInternaute']= $row["nomInternaute"];
				$row_info['prenomInternaute']= $row["prenomInternaute"];
				$row_info['naissanceInternaute'] = dateen2fr($row["naissanceInternaute"]);
				$row_info['login'] = $row['loginInternaute'];
				$row_info['idInternaute'] = $row['idInternaute'];
				$row_info['insc']= 1;
				$row_info['mdp_temp'] = $_POST['pass'];
				//print_r($row_info);
				array_push($donnees,$row_info);
				//print_r($donnees);
				$sujet = "Bienvenue chez ATS-SPORT ! - La référence des inscriptions en ligne";
				$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
				
				echo json_encode(array('connect' =>'OK'));	
	
		
		
		}
		
		
		
		
		
	}
	elseif ($_POST['new_user']==1) {
	
	$checkInternaute = check_internaute_existant_v3 ($_POST['nom'],$_POST['prenom'],$_POST['sexe'],datefr2en($_POST['date_naissance']),$_POST['email'],$_POST['pass']);
	
	$_SESSION["user_no_compte"] = 0;
	if (empty($_POST['pass'])) 
	{
		$_POST['pass'] = code_inscription(5);
		$_SESSION["user_no_compte"] = 1;
	}
	$id_internaute = $checkInternaute['id_internaute'];
								if (empty($id_internaute)) {
	
								$login_end = code_inscription_chiffre(6);
								$login = substr($_POST['nom'],0,5).$login_end;
									//$code_inscription = code_inscription(6);
									//echo $_POST['date_naissance'];
									//$_POST['date_naissance'] = str_replace("/", "-", $_POST['date_naissance']);
									
									$query  = "INSERT INTO r_internaute ";
									$query .= "(loginInternaute, passInternaute, validation, dateInscription, nomInternaute, ";
									$query .= "prenomInternaute, sexeInternaute, naissanceInternaute, emailInternaute,  ";
									$query .= "typeInternaute, coureur, organisateur,etat    ";
									//$query .= "typeInternaute, coureur, organisateur, index_telephone, telephone,    ";
									//$query .= "clubInternaute,adresseInternaute,cpInternaute,villeInternaute,  ";
									//$query .= "villeLatitude,villeLongitude,paysInternaute,natInternaute, langage";
									$query .= ") ";
									//if ($besoin_certif != 0) $query .= ",".$query_insert_certificat.") "; else $query .= ") ";
									
									$query .= " VALUES (";
									$query .= "'".addslashes_form_to_sql($login)."', ";
									$query .= "'".hhp($_POST['pass'])."', ";
									$query .= "'non', ";
									$query .= "'".date("Y-m-d H:i:s")."', ";
									$query .= "UPPER('".addslashes_form_to_sql(trim($_POST['nom']))."'), ";
									$query .= "UPPER('".addslashes_form_to_sql($_POST['prenom'])."'), ";
									$query .= "'".addslashes_form_to_sql($_POST['sexe'])."', ";
									$query .= "'".datefr2en($_POST['date_naissance'])."', ";
									$query .= "'".addslashes_form_to_sql($_POST['email'])."', ";
									$query .= "'coureur', ";
									$query .= "'non', ";
									$query .= "'non', ";
									$query .= "'nouveau' ";
									/*
									$query .= "'".addslashes_form_to_sql($_POST['insc_index_telephone'][$j])."', ";
									$query .= "'".addslashes_form_to_sql($_POST['insc_telephone'][$j])."', ";
									$query .= "'".addslashes_form_to_sql($_POST['insc_club'][$j])."', ";
									$query .= "'".addslashes_form_to_sql($_POST['insc_adresse'][$j])."', ";
									$query .= "'".$_POST['insc_cp'][$j]."', ";
									$query .= "'".addslashes_form_to_sql($_POST['insc_ville'][$j])."', ";
									$query .= "'".addslashes_form_to_sql($lat_ville[$j])."', ";
									$query .= "'".addslashes_form_to_sql($long_ville[$j])."', ";
									$query .= "'".addslashes_form_to_sql($_POST['insc_pays'][$j])."', "; //
									$query .= "'".addslashes_form_to_sql($_POST['insc_nat'][$j])."', "; //
									$query .= "'".$lng_internaute."' ";
									*/
									$query .= ") ";
									
									/*** LOGS ****/
									fputs($fp," _POST['new_user']==11 - INSERT INTO r_internaute : ".$query."\n");
									/*** LOGS ****/	
									
									//if ($besoin_certif != 0) $query .= ",'".$date_certficat."') "; else $query .= ") ";
									//echo $query;
									$result_query = $mysqli->query($query);
									login_internaute_frontend ($login,$_POST['pass']);
									$connect='oui'; 
																//$id_internaute = $mysqli->insert_id;
								
	
																	
									
									
									$donnees = array();
									$row_info = array();
									
									//$row_info = info_internaute_send_mail ($reference,(($_GET['Montant']/100)),'CB');
									//$row_info['emailInternaute']= 'jf@chauveau.nom.fr';
									$row_info['emailInternaute']= $_SESSION["emailInternaute"];
									$row_info['nomInternaute']= $_SESSION["nomInternaute"];
									$row_info['prenomInternaute']= $_SESSION["prenomInternaute"];
									$row_info['naissanceInternaute'] = dateen2fr($_SESSION["naissanceInternaute"]);
									$row_info['login'] = $_SESSION["log_log"];
									$row_info['idInternaute'] = $_SESSION["log_id"];
									$row_info['insc']= 1;
									$row_info['mdp_temp'] = $_POST['pass'];
									$row_info['user_no_compte'] = $_SESSION["user_no_compte"];
									array_push($donnees,$row_info);
									
									$sujet = "Bienvenue chez ATS-SPORT ! - La référence des inscriptions en ligne";
									if ($_SESSION["user_no_compte"]==0)
									{
										$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
									}
									echo json_encode(array('user_no_compte'=>$_SESSION["user_no_compte"], 'connect' =>$connect, 'nom'=>$_SESSION["nomInternaute"], 'prenom'=>$_SESSION["prenomInternaute"]));					
									
								}
								else
								{
									if ($_SESSION["user_no_compte"]==0)
									{
										login_internaute_frontend ($checkInternaute['loginInternaute'],$_POST['pass']);
									}
									else
									{
										login_internaute_frontend ($checkInternaute['loginInternaute'],$_POST['pass'], $id_internaute);
										
									}
									
									$connect='oui'; 
										//echo "je suis ici";						
									
							
									
									$donnees = array();
									$row_info = array();
									
									//$row_info = info_internaute_send_mail ($reference,(($_GET['Montant']/100)),'CB');
									//$row_info['emailInternaute']= 'jf@chauveau.nom.fr';
									$row_info['emailInternaute']= $_SESSION["emailInternaute"];
									$row_info['nomInternaute']= $_SESSION["nomInternaute"];
									$row_info['prenomInternaute']= $_SESSION["prenomInternaute"];
									$row_info['naissanceInternaute'] = dateen2fr($_SESSION["naissanceInternaute"]);
									$row_info['login'] = $_SESSION["log_log"];
									$row_info['idInternaute'] = $_SESSION["log_id"];
									$row_info['insc']= 1;
									$row_info['mdp_temp'] = $_POST['pass'];
									$row_info['user_no_compte'] = $_SESSION["user_no_compte"];
									array_push($donnees,$row_info);
									//print_r($donnees);
									$sujet = "Bienvenue chez ATS-SPORT ! - La référence des inscriptions en ligne";
									if ($_SESSION["user_no_compte"]==0)
									{
										$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
									}
									echo json_encode(array('user_no_compte'=>$_SESSION["user_no_compte"], 'connect' =>$connect, 'nom'=>$_SESSION["nomInternaute"], 'prenom'=>$_SESSION["prenomInternaute"]));
								}
								//mailing
								//***inscription_mailing($id_internaute,$type_certificat_bdd['idTypeEpreuve']);
	
	}
	else
	{	
	
			//echo $_POST['login']."-".$_POST['pass'];
			login_internaute_frontend ($_POST['login'],$_POST['pass']);
			if (isset($_SESSION['log_id'])) { $connect='oui'; } else { $connect='non'; }
		/*	
		session_start();
		session_destroy();
		session_unset();
		unset($_SESSION);
		*/
			/*
			$query  = "SELECT count(*) as nb FROM r_internaute as ri"; 
			$query .=" INNER JOIN r_inscriptionepreuveinternaute as riei ON ri.idInternaute = riei.idInternaute";
			$query .= " WHERE UPPER(ri.emailInternaute) = UPPER('".$_POST['email']."') ";
			$query .= " AND UPPER(ri.nomInternaute) = UPPER('".$_POST['nom']."') ";
			$query .= " AND UPPER(ri.prenomInternaute) = UPPER('".$_POST['prenom']."') ";
			$query .= " AND riei.idEpreuveParcours = ".$_POST['id_parcours']." ";
			//echo $query;
			$result = $mysqli->query($query);
			$row=mysqli_fetch_array($result);
			*/
			echo json_encode(array('connect' =>$connect, 'nom'=>$_SESSION["nomInternaute"], 'prenom'=>$_SESSION["prenomInternaute"]));
	}
}
?>
