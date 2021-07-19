<html>
    <body>
        <?php
             if($_SERVER["REQUEST_METHOD"] == 'POST' && isset($_POST['idEpreuve']) ){
                $uploadFolder = $_SERVER['DOCUMENT_ROOT'].'/temp/pageLive/'.$_POST['idEpreuve'];
                $jsFolder = '/temp/pageLive/'.$_POST['idEpreuve'];

                $type = "gpx";
                // Vérification de l'upload du fichier (id : trace)
                if(isset($_FILES['trace']) && $_FILES['trace']['error'] == 0){
                    // Récupération du nom et de l'extension
                    $filename = basename($_FILES["trace"]["name"]);
                    $type = pathinfo($filename, PATHINFO_EXTENSION);
                    echo "filename : ".$filename." | type : ".$type;
                    // Vérification de l'existence du dossier de téléchargement et création si non existant
                    if (!is_dir($uploadFolder)){
                        mkdir($uploadFolder);
                    }
                    if(!move_uploaded_file($_FILES['trace']['tmp_name'], $uploadFolder.'/'.$filename))
                        echo "Erreur dans le téléchargement de ".$filename;
                } else {
                    echo "Erreur dans le téléchargement du fichier";
                }
            } else {
                echo "Erreur dans l'envoi du formulaire";
            }
        ?>

        <script>
                // Récupération des variables
                var path = "<?php echo "$jsFolder"."/".$filename?>";
                var type = "<?php echo "$type"?>";

                // Variables de test d'extensions
                var isGeojson = /geojson|json/.test(type.toLowerCase());
                var isGpxKml = /gpx|kml/.test(type.toLowerCase());

                if (isGeojson || isGpxKml){
                    fetch(path)
                    .then(function (response) {
                        if (isGeojson)
                            return response.json();
                        else
                            return response.text();
                    })
                    .then(function (data) {
                        if (isGeojson)
                            return data;
                        else {
                            // Création du geoJSON en fonction du type (kml ou gpx)
                            var newGeoJSON = toGeoJSON.<?php echo $type ?>(new DOMParser().parseFromString(data, "text/xml"));
                            return newGeoJSON;
                        }
                    }).then(function (data) {
                        console.log(data);
                    });
                } else
                    alert("Format de fichier incorrect : "+type);

        </script>

        <!-- ================== BEGIN BASE JS ================== -->
	<?php
	$js = '<!-- ================== LEAFLET ================== -->
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script><script src="' . $site . 'assets/js/carte.js"></script>
		<script src="' . $site . 'assets/js/togeojson.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/@turf/turf@5/turf.min.js"></script>';
	echo $js;
	?>
	<!-- ================== END BASE JS ================== -->
    </body>
</html>