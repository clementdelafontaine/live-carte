<?php
    require_once('includes/functions.php');
    require_once("includes/includes.php");
    require_once('includes/connect_db.php');
	session_start();
    global $mysqli;
    
    if (isset($_POST['idEpreuve']) AND $_POST['idEpreuve'] > 0){
        $query = "SELECT nom_fichier FROM r_epreuvefichier";
        $query .= " WHERE idEpreuve='".$_POST['idEpreuve']."' AND type='photo_epreuve' ORDER BY idEpreuveFichier DESC LIMIT 1";
        $result = $mysqli->query($query);
        $result=mysqli_fetch_array($result);
        echo $result[0];
    }
?>