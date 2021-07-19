<?php
    if (isset($_POST['json']) && isset($_POST['filename'])){
        file_put_contents($_POST['filename'], $_POST['json']);
    }
?>