<?php
global $mysqli;
//require('functions.php');
/*
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else
                $ip = $_SERVER['REMOTE_ADDR'];

if ($ip != '81.56.158.138') { header('location: index.php'); } 
		*/
		$query_s  = " SELECT nsi ";
		$query_s .= " FROM r_epreuve ";
		$query_s .= " WHERE idEpreuve = ".$_GET['id_epreuve'];
		$result_s = $mysqli->query($query_s);
		$row_s=mysqli_fetch_row($result_s);
		//print_r($row_s);
		//exit();
		//if ($row_s[0] != 'oui') { header('Location: index.php'); }
		if ($row_s[0] != 'oui')	{  echo "<html><body><head><script> window.location.replace('insc.php?id_epreuve=".$id_epreuve."&step=start') </script></head></body></html>"; }
	
?>