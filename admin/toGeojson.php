<html>
    <body>
        <?php
            if($_SERVER["REQUEST_METHOD"] == 'POST'){
                if(isset($_FILES['trace']) && $_FILES['trace']['error'] == 0){
                    basename($filename = $_FILES["trace"]["name"]);
                    echo 'tmp_name : '.$_FILES['trace']['tmp_name'];
                    echo 'uploaded : '.move_uploaded_file($_FILES['trace']['tmp_name'], '/temp/upload/'.$filename);
                    echo '/temp/upload/'.$filename;
                }
            }
        ?>
        <script>

        </script>
    </body>
</html>