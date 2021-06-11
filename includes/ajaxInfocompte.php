<?php  

global $mysqli;
session_start();
	require_once('connect_db.php');
	connect_db();

	$json_info_compte = array();
	
	function info_compte ($compte,$pass) {
	    global $mysqli;
		$query  = "SELECT loginInternaute, passInternaute FROM r_internaute"; 
		$query .= " WHERE loginInternaute = '".$compte."' ";
		$query .= " AND passInternaute = '".$pass."' ";
		//echo $query;
		$result_info = $mysqli->query($query);
		$row_info=mysqli_fetch_array($result_info);
		
		if ($row_info != FALSE ) {

			$info_compte = array('loginInternaute'=>$row_info['nomInternaute'],
									  'prenomInternaute'=>$row_info['prenomInternaute'],
									  'sexeInternaute'=>$row_info['sexeInternaute'],
									  'naissanceInternaute'=>date("d/m/Y",strtotime($row_info['naissanceInternaute'])),
									  'clubInternaute'=>$row_info['clubInternaute'],
									  'adresseInternaute'=>$row_info['adresseInternaute'],
									  'cpInternaute'=>$row_info['cpInternaute'],
									  'villeInternaute'=>$row_info['villeInternaute'],
									  'villeLatitude'=>$row_info['villeLatitude'],
									  'villeLongitude'=>$row_info['villeLongitude'],
									  'paysInternaute'=>$row_info['paysInternaute'],
									  'telephone'=>$row_info['telephone']
			);
		
		}
	//print_r($info_compte);
	return $info_compte;
	}	
	
	
	
	
	
?>