<?php 

session_start();
	require_once('connect_db.php');
	connect_db();
	require_once('functions.php');
	$json_info_participant = array();
	

	
	function info_participant ($compte,$pass,$id_parcours,$idInscriptionEpreuveInternaute) {
	
		$query  = "SELECT * FROM r_internaute "; 
		$query .= " WHERE loginInternaute = '".$compte."' ";
		$query .= " AND passInternaute = '".$pass."' ";
		$query .= " ORDER BY dateInscription DESC";
		$query .= " LIMIT 1";
		//echo $query;
		$result_info = $mysqli->query($query);
		$row_info=mysqli_fetch_array($result_info);
		
		
		
		if ($row_info != FALSE ) {

				$html_auto_parentale = '';
				$besoin_auto_parentale = besoin_auto_parentale($id_parcours,$row_info['naissanceInternaute']);
				if ($besoin_auto_parentale =='oui') $html_auto_parentale = auto_parentale_existe($row_info["idInternaute"],$id_parcours);
				//$html_auto_parentale = auto_parentale_existe($row_info["idInternaute"],$id_parcours);

				
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
			$info_participant = array('nomInternaute'=>$row_info['nomInternaute'],
									  'prenomInternaute'=>$row_info['prenomInternaute'],
									  'emailInternaute'=>$row_info['emailInternaute'],
									  'sexeInternaute'=>$row_info['sexeInternaute'],
									  'naissanceInternaute'=>date("d/m/Y",strtotime($row_info['naissanceInternaute'])),
									  'clubInternaute'=>$row_info['clubInternaute'],
									  'adresseInternaute'=>$row_info['adresseInternaute'],
									  'cpInternaute'=>$row_info['cpInternaute'],
									  'villeInternaute'=>$row_info['villeInternaute'],
									  'villeLatitude'=>$row_info['villeLatitude'],
									  'villeLongitude'=>$row_info['villeLongitude'],
									  'paysInternaute'=>$row_info['paysInternaute'],
									  'index_telephone'=>$row_info['index_telephone'],
									  'telephone'=>$row_info['telephone'],
									  'date_certificat'=>$date_certificat,
									  'fichier_certificat'=>$fichier_certificat,
									  'url_certificat'=>certif_medical_existe($row_info["idInternaute"],$fichier_certificat),
									  'type_certificat'=>$type_certificat,
									  'type_internaute'=>$row_info["typeInternaute"],
									  'avatar'=>$row_info["avatar"],
									  'html_auto_parental'=>$html_auto_parentale,
									  'log_id'=>$row_info["idInternaute"]
			);
			
				$_SESSION["log_id"] = $row_info["idInternaute"];
				$_SESSION["idInscriptionEpreuveInternaute"] = $idInscriptionEpreuveInternaute;
				$_SESSION["prenomInternaute"] = $row_info["prenomInternaute"];
				$_SESSION["nomInternaute"] = $row_info["nomInternaute"];
				$_SESSION["sexeInternaute"] = $row_info["sexeInternaute"];
				$_SESSION["emailInternaute"] = $row_info["emailInternaute"];
				$_SESSION["naissanceInternaute"] = $row_info["naissanceInternaute"];
				$_SESSION["clubInternaute"] = $row_info["clubInternaute"];
				$_SESSION["villeInternaute"] = $row_info["villeInternaute"];
				$_SESSION["index_telephone"] = $row_info["index_telephone"];
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
				
				if (isset($row_info["peremption_ski"])) {
					$_SESSION["peremption_cert"] = $row_info["peremption_ski"];
					$_SESSION["fichier_cert"] = 'fichier_ski';
					$_SESSION["type_cert"] = 4;
				}elseif (isset($row_info["peremption_tri"])) {
					$_SESSION["peremption_cert"] = $row_info["peremption_tri"];
					$_SESSION["fichier_cert"] = 'fichier_tri';
					$_SESSION["type_cert"] = 1;
				}elseif (isset($row_info["peremption_vel"])) {
					$_SESSION["peremption_cert"] = $row_info["peremption_vel"];
					$_SESSION["fichier_cert"] = 'fichier_vel';
					$_SESSION["type_cert"] = 2;
				}elseif (isset($row_info["peremption_cap"])) {
					$_SESSION["peremption_cert"] = $row_info["peremption_cap"];
					$_SESSION["fichier_cert"] = 'fichier_cap';
					$_SESSION["type_cert"] = 3;
				}
				$_SESSION['unique_id_session'] = md5(uniqid());
				//$info_participant = array('nomInternaute'=>'|ok|');
		}
		else
		{
			$info_participant = array('nomInternaute'=>'|ko|');
		
		}
	//print_r($info_participant);
		return $info_participant;
	}
	

		
		
		
		if ($_POST['compte'] =='' || $_POST['pass'] == '') {
			
			$info_participant = array('nomInternaute'=>'|ko|');
			
		}
		else
		{
			/*if(isset($_SESSION["log_id"]) ) {
				
				$info_participant = array('nomInternaute'=>$_SESSION["nomInternaute"] ,
									  'prenomInternaute'=>$_SESSION["prenomInternaute"],
									  'emailInternaute'=>$_SESSION["emailInternaute"],
									  'sexeInternaute'=>$_SESSION["sexeInternaute"],
									  'naissanceInternaute'=>date("d/m/Y",strtotime($_SESSION["naissanceInternaute"])),
									  'clubInternaute'=>$_SESSION["clubInternaute"],
									  'adresseInternaute'=>$_SESSION["adresseInternaute"],
									  'cpInternaute'=>$_SESSION["cpInternaute"],
									  'villeInternaute'=>$_SESSION["villeInternaute"],
									  'villeLatitude'=>$_SESSION["villeLatitude"],
									  'villeLongitude'=>$_SESSION["villeLongitude"],
									  'paysInternaute'=>$_SESSION["paysInternaute"],
									  'telephone'=>$_SESSION["telephone"]
									);
			}else
			{*/
		
				$info_participant = info_participant($_POST['compte'],$_POST['pass'],$_POST['id_parcours'], $_POST['idInscriptionEpreuveInternaute'] );
			//}

		}
		
		$json_info_participant = json_encode($info_participant);

		echo $json_info_participant;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
?>