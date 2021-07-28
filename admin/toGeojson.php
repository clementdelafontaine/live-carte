<html>
    <body>
        <?php
            session_start();
            require_once('includes/connect_db.php');
            connect_db();

             if($_SERVER["REQUEST_METHOD"] == 'POST' && isset($_POST['idEpreuve']) && isset($_POST['idParcours']) ){
                $uploadFolder = $_SERVER['DOCUMENT_ROOT'].'/temp/leaflet/tmp/'.$_POST['idEpreuve'];
                $uploadFolderGeojson = $_SERVER['DOCUMENT_ROOT'].'/temp/leaflet/geojson/'.$_POST['idEpreuve'];
                $jsFolderTmp = '/temp/leaflet/tmp/'.$_POST['idEpreuve'];
                $jsFolderId = '/temp/leaflet/geojson/'.$_POST['idEpreuve'];
                $idEpreuve = $_POST['idEpreuve'];
                $idParcours = $_POST['idParcours'];

                // Vérification de l'upload du fichier (id : trace)
                if(isset($_FILES['trace']) && $_FILES['trace']['error'] == 0){
                    // Récupération du nom et de l'extension
                    $filename = basename($_FILES["trace"]["name"]);
                    $type = pathinfo($filename, PATHINFO_EXTENSION);
                    echo "filename : ".$filename." | type : ".$type." | idParcours : ".$idParcours;
                    // Vérification de l'existence du dossier de téléchargement et création si non existant
                    if (!is_dir($uploadFolder))
                        mkdir($uploadFolder);
                    if(!move_uploaded_file($_FILES['trace']['tmp_name'], $uploadFolder.'/'.$filename))
                        echo "Erreur dans le téléchargement de ".$filename;
                        exit;
                    if($filename != "")
                        echo "<script type='text/javascript'>processFile();</script>";
                } else {
                    echo "Erreur dans le téléchargement du fichier";
                }

                // Vérification de l'existence du fichier contenant les liens
                if (!file_exists($uploadFolderGeojson."/url.json")){
                    $listeLiens = fopen("$uploadFolderGeojson", "w");
                    fwrite($listeLiens, json_encode(new stdClass));
                    fclose($listeLiens);
                }

                // Lecture du formulaire
                echo '<br> color : '.$_POST["color_trace"].' | distance : '.$_POST["distance"].' | nom :'. $_POST["name_parcours"];
                if(isset($_POST['color_trace'])){
                    $color = $_POST['color_trace'];
                }
                if(isset($_POST['distance']))
                    $distance = $_POST['distance'];
                if(isset($_POST['name_parcours']))
                    $name = $_POST['name_parcours'];

                // Récupération du tableau
                $id_point = array();
                $category = array();
                $dist_point = array();
                $popupContent = array();
                $i = 0;
                if(isset($_POST['id_point'])){
                    foreach($_POST['id_point'] as $row){
                        $id_point[$i] = $row;

                        if(isset($_POST['category_'.$row]))
                            $category[$row] = $_POST['category_'.$row];
                        if(isset($_POST['distance_depart_'.$row]))
                            $dist_point[$row] = $_POST['distance_depart_'.$row];
                        if(isset($_POST['popupContent_'.$row]))
                            $popupContent[$row] = $_POST['popupContent_'.$row];
                        $i++;
                    }
                }

                var_dump($id_point);var_dump($category);var_dump($dist_point);var_dump($popupContent);
            
                // Mise en BDD
                // " REPLACE INTO c_carto_points_interet "
            } else {
                echo "Erreur dans l'envoi du formulaire";
            }
        ?>

        <script>
            function processFile() {
                // Récupération des variables
                var file = "<?php echo $jsFolderTmp.'/'.$filename; ?>";
                var uploadFolder = "<?php echo "$uploadFolderGeojson".'/'; ?>";
                var path = "<?php echo $jsFolderTmp.'/';?>";
                var pathId = "<?php echo $jsFolderId.'/';?>";
                var type = "<?php echo "$type";?>";
                var idParcours = "<?php echo $idParcours; ?>";
                console.log('idParcours : '+idParcours);

                var name = "<?php echo $name; ?>";
                var distance = <?php echo $distance; ?>;
                var color = "<?php echo $color; ?>";;

                // Variables de test d'extensions
                var isGeojson = /geojson|json/.test(type.toLowerCase());
                var isGpxKml = /gpx|kml/.test(type.toLowerCase());

                if (isGeojson || isGpxKml){
                    fetch(file)
                    .then(function (response) {
                        console.log(response);
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
                        for (var feature in data.features){
                            var typeFeature = data.features[feature].geometry.type;
                            // Traitement Trace
                            if (typeFeature == "MultiLineString"){
                                var idIsSet = false, nameIsSet = false, distIsSet = false, colorIsSet = false;
                                for (var property in data.features[feature].properties) {
                                    // Vérification de l'existance des champs ; si oui : redéfinition
                                    switch (property){
                                        case "id":
                                            idIsSet = true;
                                            data.features[feature].properties[property] = idParcours;
                                            break;
                                        case "name":
                                            nameIsSet = true;
                                            data.features[feature].properties[property] = name;
                                            break;
                                        case "distance":
                                            distIsSet = true;
                                            data.features[feature].properties[property] = distance;
                                            break;
                                        case "color":
                                            colorIsSet = true;
                                            data.features[feature].properties[property] = color;
                                            break;
                                    }
                                    console.log("["+feature+"]"+"["+property+"]"+data.features[feature].properties[property]);
                                }
                                // Ajout des champs si non existants
                                if (!idIsSet)
                                    data.features[feature].properties["id"] = id;
                                if (!nameIsSet)
                                    data.features[feature].properties["name"] = name;
                                if (!distIsSet)
                                    data.features[feature].properties["distance"] = distance;
                                if (!idIsSet)
                                    data.features[feature].properties["color"] = color;
                            } else if (typeFeature == "Point"){
                                // Parcourir le tableau des points entrés dans le formulaire : s'il existe -> mettre à jour ; sinon -> créer le point
                                var pointExiste = true; // var temporaire

                                if (pointExiste){ // màj
                                    // Feature/properties : shape(Marker), idPoint, name, category ; optionnel : distance, url, popupContent
                                    for (var property in data.features[feature].properties) {
                                        var nameIsSet = false, cateIsSet = false, distIsSet = false, urlIsSet = false, popupIsSet = false;
                                        // Vérification de l'existance des champs ; si oui : redéfinition
                                        if (property == "name"){
                                            nameIsSet = true;
                                            // data.features[feature].properties[property] = name;
                                        }
                                        if (property == "category"){
                                            cateIsSet = true;
                                            // data.features[feature].properties[property] = category;
                                        }
                                        if (property == "distance"){
                                            distIsSet = true;
                                            // data.features[feature].properties[property] = distance;
                                        }
                                        if (property == "url"){
                                            urlIsSet = true;
                                            // data.features[feature].properties[property] = color;
                                        }
                                        if (property == "popupContent"){
                                            popupIsSet = true;
                                            // data.features[feature].properties[property] = color;
                                        }
                                    }
                                } else { // Ajout du point dans le json

                                }
                                
                                
                            }
                            // Feature/geometry : type(Point), coordinates ([x.000, y.000, z])
                        }
                        console.log(data);

                        // Enregistrement du fichier geojson
                        var fd = new FormData();
                        fd.append("json", JSON.stringify(data));
                        fd.append("filename", uploadFolder+idParcours+'.geojson');
                        fetch("/temp/ajax_geojson.php", {method:"POST", body:fd});

                        // Ajouter le nom dans la liste des cartes à afficher | le fichier a été créé dans le php s'il n'existait pas auparavent
                        readTextFile(pathId+"url.json")
                        .then(function (text) {
                            var liens = JSON.parse(text);

                            var idIsSet = false;
                            for(var key in liens) {
                                if(liens[key].id == idParcours)
                                    idIsSet = true;
                            }
                            if(!idIsSet){

                            }
                        });
                    });
                } else
                    alert("Format de fichier incorrect : "+type);
            }

        </script>

        <!-- ================== BEGIN BASE JS ================== -->
	<?php
	$js = '<!-- ================== LEAFLET ================== -->
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script><script src="../assets/js/carte.js"></script>
		<script src="../assets/js/togeojson.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/@turf/turf@5/turf.min.js"></script>';
	echo $js;
	?>
	<!-- ================== END BASE JS ================== -->
    </body>
</html>