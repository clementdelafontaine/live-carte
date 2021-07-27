<?php
    session_start();
    require_once('includes/connect_db.php');
    connect_db();

    $id_epreuve = 0;
    $id_parcours = 0;
    $id = 0;
    if (isset($_POST['id_epreuve']) && isset($_POST['id_parcours']) && isset($_POST['id'])){
        $id_epreuve = mysqli_real_escape_string($con, $_POST['id_epreuve']);
        $id_parcours = mysqli_real_escape_string($con, $_POST['id_parcours']);
        $id = mysqli_real_escape_string($con, $_POST['id']);

        $verifExistence = mysqli_query($con,"SELECT * FROM c_carto_points_interet WHERE id_epreuve='$id_epreuve' AND id_parcours='$id_parcours' AND id='$id';");
        $totalRows = mysqli_num_rows($verifExistence);

        if($totalRows > 0) {
            $query = "DELETE FROM c_carto_points_interet WHERE id_epreuve='$id_epreuve' AND id_parcours='$id_parcours' AND id='$id';";
            mysqli_query($con,$query);
            echo 1;
            exit;
        } else {
            echo 0;
            exit;
        }

        echo 0;
        exit;
    }
?>