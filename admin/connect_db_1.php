<?php 
define('URL_DEV','');

function connect_db()
{
	global $mysqli;	
	// if (($mysql = mysql_connect("sql-server", "sql-ats","FCzNJ6DtjwqRMUHG")) == FALSE) // Ancien serveur pro-libre
	if (( $mysqli = mysqli_connect("localhost", "ATSPC", "vdg9H@65")) == FALSE) return FALSE;
	// if (mysql_select_db("sql-ats") == FALSE) //Ancien serveur pro-libre et base local
	$mysqli->set_charset("utf8");
	if (mysqli_select_db($mysqli,"ATSPC") == FALSE)	//Nouveau serveur OVH pro
	
		return FALSE;
	return $mysqli;
}


?>
