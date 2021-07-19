<html>
    <body>
        <?php
             if($_SERVER["REQUEST_METHOD"] == 'POST' && isset($_POST['idEpreuve']) && isset($_POST['idParcours']) ){
                $uploadFolder = $_SERVER['DOCUMENT_ROOT'].'/temp/pageLive/'.$_POST['idEpreuve'];
                $jsFolder = '/temp/pageLive/'.$_POST['idEpreuve'];
                $idParcours = $_POST['idParcours'];

                // Vérification de l'upload du fichier (id : trace)
                if(isset($_FILES['trace']) && $_FILES['trace']['error'] == 0){
                    // Récupération du nom et de l'extension
                    $filename = basename($_FILES["trace"]["name"]);
                    $type = pathinfo($filename, PATHINFO_EXTENSION);
                    echo "filename : ".$filename." | type : ".$type." | idParcours : ".$idParcours;
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
                var file = "<?php echo $jsFolder.'/'.$filename; ?>";
                var uploadFolder = "<?php echo "$uploadFolder".'/'; ?>";
                var path = "<?php echo $jsFolder.'/';?>";
                var type = "<?php echo "$type";?>";
                var filename = "<?php echo $idParcours; ?>";
                console.log('filename : '+filename);

                // Variables de test d'extensions
                var isGeojson = /geojson|json/.test(type.toLowerCase());
                var isGpxKml = /gpx|kml/.test(type.toLowerCase());

                if (isGeojson || isGpxKml){
                    fetch(file)
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
                        // Edition du fichier geojson
                        // FeatureCollection/features
                        // Feature/properties : shape(MultiLine), id, name, distance, color
                        for (var key in data){
                            console.log();
                        }
                        // Boucle pour chaque point
                        // Feature/properties : shape(Marker), name, category ; optionnel : distance, url, popupContent
                        
                        // Feature/geometry : type(poin), coordinates ([x.000, y.000, z])
                        // Enregistrement du fichier geojson
                        var fd = new FormData();
                        fd.append("json", JSON.stringify(data));
                        fd.append("filename", uploadFolder+filename+'.geojson');
                        fetch("/temp/ajax_geojson.php", {method:"POST", body:fd});
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