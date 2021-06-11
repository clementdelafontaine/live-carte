<?php 
	session_start();
	require_once('connect_db.php');
	connect_db();
	require_once('functions.php');
    global $mysqli;
		
		$query  = "SELECT count(*) as nb FROM r_internaute as ri"; 
		$query .=" INNER JOIN r_inscriptionepreuveinternaute as riei ON ri.idInternaute = riei.idInternaute";
		$query .= " WHERE UPPER(ri.emailInternaute) = UPPER('".$_POST['email']."') ";
		$query .= " AND UPPER(ri.nomInternaute) = UPPER('".$_POST['nom']."') ";
		$query .= " AND UPPER(ri.prenomInternaute) = UPPER('".$_POST['prenom']."') ";
		$query .= " AND riei.idEpreuveParcours = ".$_POST['id_parcours']." ";
		//echo $query;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_array($result);
		
		echo json_encode(array('nb' =>$row[0]));
?>
