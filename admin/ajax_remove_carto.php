<?php
    require_once('includes/functions.php');
    require_once("includes/includes.php");
    require_once('includes/connect_db.php');
	session_start();
    global $mysqli;

    $id_epreuve = 0;
    $id_parcours = 0;
    $id = 0;
    $totalRows = 0;
    // if (isset($_POST['id_epreuve']) && isset($_POST['id_parcours']) && isset($_POST['id'])){
        $id_epreuve = (int) $_POST['id_epreuve'];
        $id_parcours = (int) $_POST['id_parcours'];
        $id = (int) $_POST['id'];

        $verifExistence = $mysqli->query("SELECT * FROM c_carto_points_interet WHERE id_epreuve=$id_epreuve AND id_parcours=$id_parcours AND id=$id;");
        $totalRows = mysqli_num_rows($verifExistence);

        if($totalRows > 0) {
            $query = "DELETE FROM c_carto_points_interet WHERE id_epreuve='$id_epreuve' AND id_parcours='$id_parcours' AND id='$id';";
            $mysqli->query($query);
            echo 1;
            exit;
        } else if ($totalRows == 0){
            echo 2;
            exit;
        }

        echo 0;
        exit;
    // }
?>