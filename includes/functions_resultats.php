<?php 
	require_once("includes/connect_db.php");
	connect_db();

	function _date( $date )
	{
		return date( "Y-m-d H:i:s", strtotime( $date ) );
	}

	function infos_epreuve( $idEpreuve )
	{
		$qepreuve = "SELECT * FROM r_epreuve WHERE idEpreuve = ".$idEpreuve;
		$result = $mysqli->query($qepreuve);
		$row = mysqli_fetch_assoc($result);

		return $row;
	}

	function parcours( $idEpreuve )
	{
		$parcours = array();
		$qparcours = "SELECT parcours FROM chrono_resultats WHERE idEpreuve = ".$idEpreuve." GROUP BY parcours";

		$result = $mysqli->query( $qparcours );
		while( $row = mysqli_fetch_assoc( $result ) )
		{
			array_push( $parcours, $row['parcours'] );
		}
		return $parcours;
	}

	function categorie_parcours( $idEpreuve, $parcours )
	{
		$categories = array();

		$qcategorie  = "SELECT DISTINCT(iei.categorie) AS categorie FROM chrono_resultats AS cr ";
		$qcategorie .= "JOIN r_inscriptionepreuveinternaute AS iei ON cr.idInternaute = iei.idInternaute ";
		$qcategorie .= "WHERE iei.idEpreuve = ".$idEpreuve." AND cr.parcours like '".$parcours."' AND cr.passage = 1 AND cr.cache = 0 AND status like 'OK'";	

		$result = $mysqli->query( $qcategorie );
		while( $row = mysqli_fetch_assoc( $result ) )
		{
			array_push( $categories, $row['categorie'] );
		}
		return $categories;
	}

	function runner( $idEpreuve, $dossard = '', $idInternaute = '' )
	{
		//Grâce au numéro de la puce on recupère l'id du coureur + id du parcours concerné pour l'épreuve concernée
		$qrunner  ="SELECT * FROM r_internaute AS i ";
		$qrunner .="JOIN r_inscriptionepreuveinternaute AS iei ON i.idInternaute = iei.idInternaute ";
		$qrunner .="WHERE iei.idEpreuve = ".$idEpreuve." ";
		if( $idInternaute != '' ) $qrunner .="AND iei.idInternaute = ".$idInternaute." ";
		$qrunner .="AND iei.dossard = ".$dossard." ";
		$qrunner .="ORDER BY iei.idInscriptionEpreuveInternaute DESC limit 1";

		$result = $mysqli->query( $qrunner );
		$runner = mysqli_fetch_assoc( $result );
			
		if( !$runner ) return false;
		else return $runner;
	}

	function lieu( $idEpreuve )
	{
		$lieux = array();

		$qLieux = "SELECT lieu, distance_depart FROM chrono_lecteur WHERE idEpreuve = ".$idEpreuve." ORDER BY distance_depart";
		$result = $mysqli->query( $qLieux );
		while( $row = mysqli_fetch_assoc( $result ) ) 
			$lieux[$row['lieu']] = $row['distance_depart'];

		$lieux["ARRIVEE"] = 0;

		return $lieux;
	}

	function vague( $idEpreuve, $vague = 0 )
	{
		$vagues = array();

		$qvague  = "SELECT * FROM chrono_vague ";
		$qvague .= "WHERE idEpreuve = ".$idEpreuve." ";
		if( $vague > 0 ) $qvague .= "AND vague = ".$vague." ";
		$qvague .= "ORDER BY vague ASC";

		$result = $mysqli->query( $qvague );
		while( $row = mysqli_fetch_assoc( $result ) ) 
			$vagues["'".$row['vague']."'"] = $row['date'];

		if( $vague > 0 ) return $vagues["'".$vague."'"];
		else return $vagues;
	}

	function temps( $depart, $t )
	{
		//Formatage du temps
		$ref = new DateTime( $depart );
		$current = 	new DateTime( $t );		
		$temps = $ref->diff( $current );	
		$temps = $temps->format( "%H:%I:%S" );

		return $temps;
	}

	/*function temps( $depart, $t )
	{
		$date1 = $depart;
		$date2 = $t;

		$ref = new DateTime( $date1 );
		$current =  new DateTime( $date2 );     
		$temps = $ref->diff( $current );    
		$temps2 = $ref->diff( $current );  
		$temps = $temps->format( "%I:%S" );
		 
		$time1 = strtotime($date1);
		$time2 = strtotime($date2);
		if( $time1 > $time2 ) {
		    $time = $time1 - $time2;
		} else {
		    $time = $time2 - $time1;
		}
		 
		$time = $time / 3600;
		//echo "Il y a ".round($time)." heures";

		if( $time < 3600 )
			return '0:'.$temps;
		else
			return round($time,0,PHP_ROUND_HALF_DOWN).':'.$temps;
	}*/

	function vitesse( $distance, $temps )
	{
		if( $distance == '' ) return "-";

		$heures = date("G", strtotime( $temps ) );
		$minutes = date("i", strtotime( $temps ) );
		$secondes = date("s", strtotime( $temps ) );

		$duree = $heures+($minutes/60)+($secondes/3600);

		return round(($distance / $duree),1);
	}

	function premiers( $idEpreuve )
	{
		$premiers = array();
		
		$qpremier  = "SELECT min(horaire) as horaire, lieu, parcours FROM chrono_resultats ";
		$qpremier .= "WHERE idEpreuve = ".$idEpreuve." AND passage = 1 AND cache = 0 AND status like 'OK' GROUP BY lieu, parcours";
		
		$result = $mysqli->query( $qpremier );
		while( $row = mysqli_fetch_assoc( $result ) )
		{
			$premiers["'".$row['lieu'].utf8_decode( $row['parcours'] )."'"] = $row['horaire'];
		}

		return $premiers;
	}

	function heures_de_passage( $idEpreuve, $parcours = '', $lieu = '', $sexe = '', $categorie = '', $clm = false )
	{
		$horaires = array();

		if( !$clm ) $qplace  = "SELECT iei.dossard, cr.horaire as temps, cr.parcours, cr.lieu, i.sexeInternaute, iei.categorie FROM chrono_resultats AS cr ";
		else $qplace  = "SELECT iei.dossard, TIMEDIFF(cr.horaire,iei.vague) as temps, cr.parcours, cr.lieu, i.sexeInternaute, iei.categorie FROM chrono_resultats AS cr ";
		$qplace .= "JOIN r_internaute AS i ON cr.idInternaute = i.idInternaute ";
		$qplace .= "JOIN r_inscriptionepreuveinternaute AS iei ON cr.dossard = iei.dossard ";
		$qplace .= "WHERE cr.idEpreuve = ".$idEpreuve." AND iei.idEpreuve = ".$idEpreuve." AND cr.passage = 1 AND cr.cache = 0 AND status like 'OK' ";
		if( $parcours != '' ) $qplace .= "AND cr.parcours like '".$parcours."' ";
		if( $lieu != '' ) $qplace .= "AND cr.lieu like '".$lieu."' ";
		if( $sexe != '' ) $qplace .= "AND i.sexeInternaute like '".$sexe."' "; 
		if( $categorie != '' ) $qplace .= "AND iei.categorie like '".$categorie."' "; 
		$qplace .= "AND iei.dossard <> 0 GROUP BY cr.dossard, lieu ORDER BY temps ASC, cr.indice ASC";

		$result = $mysqli->query( $qplace );
		while( $row = mysqli_fetch_assoc( $result ) )
		{
			array_push( $horaires, $row );
		}

		return $horaires;
	}

	function place( $array, $value )
	{
		return array_search( $value, $array )+1;
	}

	function send_mail_suivre_un_coureur( $idEpreuve, $parcours, $lieu, $dossard, $nom, $prenom, $horaire )
	{
		//Descriptif de l'épreuve
		$epreuve  = infos_epreuve( $idEpreuve );
		$passages = heures_de_passage( $idEpreuve, $parcours, $lieu );

		//Entêtes de l'email
		$headers  = 'MIME-Version: 1.0' . "\r\n";
	    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: noreply@ats-sport.com' . "\r\n";
		$headers .= 'X-Mailer: PHP/' . phpversion();

		//Corps de l'email
		$message  = "<html><head></head>"."\r\n";
		$message .= "<body>"."\r\n";
		$message .= "Bonjour,<br /><br />"."\r\n";
		$message .= $prenom." ".$nom." est passé en ".place( $passages, $dossard )."° place à ".$lieu." le ".date("d/m/Y", strtotime( $horaire ) )." à ".date("H:i:s", strtotime( $horaire ) )."<br />"."\r\n";
		$message .= "<a href='https://www.ats-sport.com/liveResults/Resultats/direct/".$idEpreuve."'>Suivez ".$epreuve['nomEpreuve']." en direct</a><br />"."\r\n";
		$message .= "<br />"."\r\n";
		$message .= "Sportivement,"."\r\n";
		$message .= "L'équipe d'ATS-SPORT<br />"."\r\n";
		$message .= "04 67 45 41 10 | www.ats-sport.com"."\r\n";
		$message .= "</body></html>"."\r\n";
		
		$qsuivre_un_coureur  = "SELECT * FROM chrono_email ";
		$qsuivre_un_coureur .= "WHERE idEpreuve = ".$idEpreuve." ";
		$qsuivre_un_coureur .= "AND (dossard = ".$dossard." OR nom like '".$nom."') ";
		$qsuivre_un_coureur .= "AND envoye = 0";

		$result = $mysqli->query( $qsuivre_un_coureur );
		while( $row = mysqli_fetch_assoc( $result ) )
		{
			mail($row['email'], "Suivi de la course ".$epreuve['nomEpreuve'], utf8_decode($message), $headers);
			$mysqli->query( "UPDATE chrono_email SET envoye = 1 WHERE id = ".$row['id'] );
		}
	}

	function array_column($input, $column_key, $index_key = NULL)
	{
        if (!is_array($input)) {
            trigger_error(__FUNCTION__ . '() expects parameter 1 to be array, ' . gettype($input) . ' given', E_USER_WARNING);
            return FALSE;
        }
       
        $ret = array();
        foreach ($input as $k => $v) {       
            $value = NULL;
            if ($column_key === NULL) {
                $value = $v;
            }
            else {
                $value = $v[$column_key];
            }
           
            if ($index_key === NULL || !isset($v[$index_key])) {
                $ret[] = $value;
            }
            else {
                $ret[$v[$index_key]] = $value;
            }  
        }
       
        return $ret;
    }
?>