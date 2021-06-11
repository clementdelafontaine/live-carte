<?php
	session_start();
	require_once('connect_db.php');
	require_once("functions_hugo.php");
	//require_once("slashes.php");
	connect_db();
	ini_set ('max_execution_time', 0);

	
	if( isset( $_POST['edition'] ) )
	{
		$option = array();
		$cpt=0;
		
		$query  = "SELECT * FROM r_epreuve ";
		$query .= "WHERE dateEpreuve >= '".date($_POST['edition']."-01-01")."' AND dateEpreuve <= '".date($_POST['edition']."-12-31")."' ";
		$query .= "AND paiement_cb like '1' AND valide like 'oui' ";
		$query .= "ORDER BY departement ASC, dateEpreuve ASC";
		$result = $mysqli->query($query);
			
		while($row=mysqli_fetch_assoc($result))
		{
			if($row['nomEpreuve'] != '')
			{
				$option[$cpt]['idEpreuve'] 		= $row['idEpreuve'];
				$option[$cpt]['departement'] 	= $row['departement'];
				$option[$cpt]['nomEpreuve'] 	= mb_convert_encoding($row['nomEpreuve'], "UTF-8", "Windows-1252");
				$cpt++;	
			}
		}

		$data = array('option'=>$option);
		echo json_encode($data);
	}
	else if(isset($_POST['epreuve']))
	{
		$option = array();
		$cpt=0;
		
		$query  = "SELECT * FROM r_epreuveparcours ";
		$query .= "WHERE idEpreuve = '".$_POST['epreuve']."'";
		$result = $mysqli->query($query);
			
		while($row=mysqli_fetch_assoc($result))
		{
			if($row['nomParcours'] != '')
			{
				$option[$cpt]['idEpreuveParcours'] 		= $row['idEpreuveParcours'];
				$option[$cpt]['nomParcours'] 	= mb_convert_encoding($row['nomParcours'], "UTF-8", "Windows-1252");
				$cpt++;	
			}
		}

		$data = array('option'=>$option);
		echo json_encode($data);
	}
	else if( isset( $_POST['historique'] ) )
	{
		$qHistorique = "SELECT texte, objet FROM emailing_historique WHERE id=".addslashes( $_POST['historique'] );
		$rHistorique = $mysqli->query( $qHistorique );
		$historique = mysqli_fetch_assoc( $rHistorique );

		$data = array('texte'=>$historique['texte'],'objet'=>$historique['objet']);
		echo json_encode($data);
	}
	else if( isset( $_POST['compte'] ) && $_POST['compte'] == 2 )
	{
		$donnees 		= explode("|",$_POST['donnees']);
		$typepublic 	= $donnees[0];
		$typemail 		= $donnees[1];
		$objet 			= $donnees[2];
		$dept 			= $donnees[3];
		$epreuve 		= $donnees[4];
		$mailcontent	= $donnees[5];
		$dateenvoi		= $donnees[6];
		$expediteur		= $donnees[7];
		$piecejointe	= $donnees[8];
		$parcours		= $donnees[9];
		$nbSend 		= 0;

		//Récupération des données internautes
		$qint  = "SELECT DISTINCT i.emailInternaute ";
		$qint .= "FROM r_internaute as i ";
		if($epreuve == 0 ) $qint .= "JOIN r_internautemailing as im ON i.idInternaute = im.idInternaute ";
		if($epreuve > 0 ) $qint .= "JOIN r_inscriptionepreuveinternaute as iei ON i.idInternaute = iei.idInternaute ";
		$qint .= "WHERE i.idInternaute > 0 ";
		if($epreuve > 0 ) $qint .= "AND iei.idEpreuve = ".intval($epreuve)." ";
		if($parcours > 0 ) $qint .= "AND iei.idEpreuveParcours = ".intval($parcours)." ";
		if($dept != '') $qint .= "AND LEFT(i.cpInternaute,2) in(".$dept.") ";
		if($typepublic != 'tous') $qint .= "AND i.typeInternaute like '".$typepublic."' ";
		if($typemail != 'tous' && $epreuve == 0) $qint .= "AND im.idTypeMailing = ".intval($typemail)." ";
		$qint .= "AND i.emailInternaute like '%@%' AND i.emailInternaute not like '%free.fr%'";
		$rint = $mysqli->query($qint);
		$nbinscrit1 = mysqli_num_rows($rint);

		/*if( $epreuve == 0 && $typepublic != 'organisateur' )
		{
			$qInscrit = "SELECT DISTINCT email FROM r_internautemailing_libre WHERE email like '%@%' ";
			if( $dept != '' ) $qInscrit .= "AND departement in(".$dept.") ";
			if( $typemail != 'tous' ) $qInscrit .= "AND typeepreuve = ".intval($typemail)." ";

			$rInscrit = $mysqli->query( $qInscrit );
			$nbinscrit1 += mysqli_num_rows($rInscrit);
		}*/

		/*$domain = array();
		while($row = mysqli_fetch_assoc($rint))
		{
			$email = explode("@",$row['emailInternaute']);
			if( empty( $domain["".$email[1].""] ) ) $domain["".$email[1].""] = 1;
			else $domain["".$email[1].""]++;
		}
		arsort($domain);
		print_r($domain);*/

		$data = array('count'=>$nbinscrit1, 'objet' => '');
		echo json_encode($data);
	}
	else if(isset($_POST['donnees']))
	{
		//Critères de construction et d'envoi de la newsletter
		$donnees 		= explode("|",$_POST['donnees']);
		$typepublic 	= $donnees[0];
		$typemail 		= $donnees[1];
		$objet 			= $donnees[2];
		$dept 			= $donnees[3];
		$epreuve 		= $donnees[4];
		$mailcontent	= $donnees[5];
		$dateenvoi		= $donnees[6];
		$expediteur		= $donnees[7];
		$piecejointe	= $donnees[8];
		$parcours		= $donnees[9];
		$nbSend 		= 0;
		$email = array();
		
		//Calcul des secondes restantes entre la date d'envoi et la date du moment
		$now = time();
		$date2 = strtotime(date('Y-m-d H:i:s',strtotime($dateenvoi)));
		$diff = $date2 - $now;
		if($diff < 0) $diff=0;

		if($_POST['send'] == 1) //Mode envoi réel
		{
			//Récupération des données internautes
			$qint  = "SELECT DISTINCT i.emailInternaute ";
			$qint .= "FROM r_internaute as i ";
			if($epreuve == 0 && $typepublic != "organisateur") $qint .= "JOIN r_internautemailing as im ON i.idInternaute = im.idInternaute ";
			if($epreuve > 0 ) $qint .= "JOIN r_inscriptionepreuveinternaute as iei ON i.idInternaute = iei.idInternaute ";
			$qint .= "WHERE i.idInternaute > 0 AND i.emailInternaute not like '%free.fr%' ";
			if($epreuve > 0 ) $qint .= "AND iei.idEpreuve = ".intval($epreuve)." ";
			if($parcours > 0 ) $qint .= "AND iei.idEpreuveParcours = ".intval($parcours)." ";
			if($dept != '') $qint .= "AND LEFT(i.cpInternaute,2) in(".$dept.") ";
			if($typepublic != 'tous') $qint .= "AND i.typeInternaute like '".$typepublic."' ";
			if($typemail != 'tous' && $epreuve == 0) $qint .= "AND im.idTypeMailing = ".intval($typemail)." ";
			$rint = $mysqli->query($qint);
			
			$i=1;
			$jk = 1;
			// Pour chaque internaute
			while($rowint = mysqli_fetch_array($rint))
			{

				$i++;
				// Envoi du mail
				if($rowint['emailInternaute'] != '' && filter_var($rowint['emailInternaute'], FILTER_VALIDATE_EMAIL))
				{
					$nbSend++;	
					array_push($email,$rowint['emailInternaute']);
				}
			
				if ($i >= 3000 )
				{
					array_push($email,'hugo@ats-sport.com');
					array_push($email,'contact@ats-sport.com');
					send_newsletter($email, $objet, $mailcontent, $diff, $expediteur, 'reel', $piecejointe);
					$i=1;
					$email=array();
				}
				
			}
			array_push($email,'hugo@ats-sport.com');
			array_push($email,'contact@ats-sport.com');
			send_newsletter($email, $objet, $mailcontent, $diff, $expediteur, 'reel', $piecejointe);
		}
		else //Mode test
		{
			if($_POST["emailtest"] != '') array_push($email,$_POST["emailtest"]);
			else array_push($email,'hugo@ats-sport.com');
			send_newsletter($email, $objet, $mailcontent, $diff, $expediteur, 'test', $piecejointe);
			$nbSend++;
		}
		$data = array('envoi'=>'ok','nbSend'=>$nbSend, 'objet'=>$objet);
		echo json_encode($data);
	}
	else
	{
		$data = array('envoi'=>'ko');
		echo json_encode($data);
	}
?>