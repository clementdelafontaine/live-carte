<html>

<body>
    <?php
    global $mysqli;
    require('includes/includes.php');
    require('includes/functions.php');
    require_once('includes/connect_db.php');

    if ($_SERVER["REQUEST_METHOD"] == 'POST' && isset($_POST['idEpreuve']) && isset($_POST['idParcours'])) {
        $uploadFolder = $_SERVER['DOCUMENT_ROOT'] . '/temp/leaflet/tmp/' . $_POST['idEpreuve'];
        $uploadFolderGeojson = $_SERVER['DOCUMENT_ROOT'] . '/temp/leaflet/geojson/' . $_POST['idEpreuve'];
        $jsFolderTmp = '/temp/leaflet/tmp/' . $_POST['idEpreuve'];
        $jsFolderId = '/temp/leaflet/geojson/' . $_POST['idEpreuve'];
        $idEpreuve = $_POST['idEpreuve'];
        $idParcours = $_POST['idParcours'];

        // Vérification de l'upload du fichier (id : trace)
        if (isset($_FILES['trace']) && $_FILES['trace']['error'] == 0) {
            // Récupération du nom et de l'extension
            $filename = basename($_FILES["trace"]["name"]);
            $type = pathinfo($filename, PATHINFO_EXTENSION);
            $fichierCharge = true;
            echo "filename : " . $filename . " | type : " . $type . " | idParcours : " . $idParcours;
            // Vérification de l'existence du dossier de téléchargement et création si non existant
            if (!is_dir($uploadFolder))
                mkdir($uploadFolder);
            if (!move_uploaded_file($_FILES['trace']['tmp_name'], $uploadFolder . '/' . $filename))
                echo "Erreur dans le téléchargement de " . $filename;
        } else {
            // Si le fichier existe on le charge

            echo "Erreur dans le téléchargement du fichier";
        }

        // Vérification de l'existence du fichier contenant les liens
        if (!file_exists($uploadFolderGeojson . "/url.json")) {
            $listeLiens = fopen("$uploadFolderGeojson", "w");
            fwrite($listeLiens, json_encode(new stdClass));
            fclose($listeLiens);
        }

        // Lecture du formulaire
        echo '<br> color : ' . $_POST["color_trace"] . ' | distance : ' . $_POST["distance"] . ' | nom :' . $_POST["name_parcours"];
        if (isset($_POST['color_trace']))
            $couleur = $_POST['color_trace'];
        else
            $couleur = "";
        if (isset($_POST['distance']))
            $distance = $_POST['distance'];
        else
            $distance = 0;
        if (isset($_POST['name_parcours']))
            $name = $_POST['name_parcours'];

        // Récupération du tableau des points d'intérêt
        $id_point = array();
        $category = array();
        $coord_dist_point = array();
        $popupContent = array();
        $idLecteur = array();
        $i = 0;
        if (isset($_POST['id_point'])) {
            foreach ($_POST['id_point'] as $row) {
                if($row != "undefined"){
                    $id_point[$i] = $row;

                    if (isset($_POST['category_' . $row]))
                        $category[$row] = $_POST['category_' . $row];
                    if (isset($_POST['distance_depart_' . $row]))
                        $coord_dist_point[$row] = $_POST['distance_depart_' . $row];
                    if (isset($_POST['popupContent_' . $row]))
                        $popupContent[$row] = htmlspecialchars($_POST['popupContent_' . $row]);
                    else
                        $popupContent[$row] = "";
                    if (isset($_POST['id_lecteur_' . $row]))
                        $idLecteur[$row] = $_POST['id_lecteur_' . $row];
                    else
                        $idLecteur[$row] = "NULL";
                    $i++;
                }
            }
        }

        // Convertir une distance en coordonnées ici

        var_dump($id_point);
        var_dump($category);
        var_dump($coord_dist_point);
        var_dump($popupContent);
        var_dump($idLecteur);

        // Mise en BDD
        // c_carto_points_interet (id_epreuve,id_parcours,id,categorie,popupContent,id_lecteur,x,y,z)
        if (isset($_POST['id_point'])) {
            foreach($id_point as $id){
                if($id != "undefined"){
                    $xyz = explode(",", $coord_dist_point[$id]);
                    $x = $xyz[0];
                    $y = $xyz[1];
                    $z = $xyz[2];
                    echo "x : $x, y : $y, z : $z";
                    $query = "INSERT INTO c_carto_points_interet VALUES (".$idEpreuve.", ".$idParcours.", ".$id.", '".$category[$id]."','".$popupContent[$id]."' ,".$idLecteur[$id]." ,".$x." ,".$y." ,".$z.")";
                    $query .= " ON DUPLICATE KEY UPDATE categorie='".$category[$id]."', popupContent='".$popupContent[$id]."', id_lecteur=".$idLecteur[$id].", x=".$x.", y=".$y.", z=".$z;
                    echo " | ".$query;
                    $result = $mysqli->query($query);
                }
            }
        }

        // c_gpx (id_epreuve, id_parcours, couleur, distance)
        $query = "INSERT INTO c_gpx VALUES (".$idEpreuve.", ".$idParcours.", '".$couleur."', ".$distance.")";
        $query .= " ON DUPLICATE KEY UPDATE couleur='".$couleur."', distance=".$distance;
        echo " | ".$query;
        $result = $mysqli->query($query);
    } else {
        echo "Erreur dans l'envoi du formulaire";
    }
    ?>

    <script>
        function processFile() {
            function setupVariables(){
            // Récupération des variables
            // Chemins d'enregistrement
            var uploadFolder = "<?php echo "$uploadFolderGeojson" . '/'; ?>";
            var path = "<?php echo $jsFolderTmp . '/'; ?>";
            var pathId = "<?php echo $jsFolderId . '/'; ?>";
            alert(uploadFolder);
            // Trace
            var fichierCharge = <?php echo ((isset($fichierCharge) ? "true" : "false")) ?>;
            if (fichierCharge) {
                var file = "<?php echo $jsFolderTmp . '/' . $filename; ?>";
                var type = "<?php echo "$type"; ?>";
                <?php $fonctionConversion = $type; ?>;
                // Variables de test d'extensions
                isGeojson = /geojson|json/.test(type.toLowerCase());
                isGpxKml = /gpx|kml/.test(type.toLowerCase());
            } else {
                var type = null;
                <?php $fonctionConversion = "doNothing"; ?>;
                var isGeojson = false;
                var isGpxKml = false;
            }

            var idParcours = "<?php echo $idParcours; ?>";
            var name = "<?php echo $name; ?>";
            var distance = <?php echo $distance; ?>;
            var color = "<?php echo $couleur; ?>";

            // Points d'intérêt
            var id_point = <?php echo ((isset($id_point)) ?  json_encode(($id_point)) : ""); ?>;
            var category = <?php echo ((isset($category)) ?  json_encode(($category)) : ""); ?>;
            var coord_dist_point = <?php echo ((isset($coord_dist_point)) ?  json_encode(($coord_dist_point)) : ""); ?>;
            var popupContent = <?php echo ((isset($popupContent)) ?  json_encode(($popupContent)) : ""); ?>;

            console.log('idParcours : ' + idParcours);
            id_point.forEach(function(id) {
                console.log("id_point : " + id + " | category : " + category[id] + " | coord : " + coord_dist_point[id] + " | popupContent : " + popupContent[id]);
            });

            // #TODO Passer de distance à coordonnées si nécessaire


            console.log("isGeojson : " + isGeojson + " | iskmlgpx : " + isGpxKml);
            return new Promise(function(resolve, reject){});
            }
            setupVariables().then( function() {
            if (isGeojson || isGpxKml) {
                fetch(file)
                    .then(function(response) {
                        console.log(response);
                        if (isGeojson)
                            return response.json();
                        else if (isGpxKml)
                            return response.text();
                    })
                    .then(function(data) {
                        if (isGeojson)
                            return data;
                        else if (isGpxKml) {
                            // Création du geoJSON en fonction du type (kml ou gpx)
                            var newGeoJSON = toGeoJSON.<?php echo $fonctionConversion ?>(new DOMParser().parseFromString(data, "text/xml"));
                            return newGeoJSON;
                        }
                    }).then(function(data) {
                        editionGeojson(data);
                    });
            } else {
                console.log("Format de fichier incorrect : " + type);
                var newGeoJSON = {
                    "type": "FeatureCollection",
                    "features": [{}]
                };
                editionGeojson(newGeoJSON);
            }})
        }

        function editionGeojson(data) {
            // Edition du fichier geojson
            // FeatureCollection/features
            // Feature/properties : shape(MultiLine), id, name, distance, color
            for (var feature in data.features) {
                if(data.features[feature].geometry != undefined)
                    var typeFeature = data.features[feature].geometry.type;
                else
                    var typeFeature = "";
                // Traitement Trace
                if (typeFeature == "MultiLineString") {
                    var idIsSet = false,
                        nameIsSet = false,
                        distIsSet = false,
                        colorIsSet = false;
                    for (var property in data.features[feature].properties) {
                        // Vérification de l'existance des champs ; si oui : redéfinition
                        switch (property) {
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
                        console.log("[" + feature + "]" + "[" + property + "]" + data.features[feature].properties[property]);
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
                } else if (typeFeature == "Point") {
                    // Parcourir le tableau des points entrés dans le formulaire : s'il existe -> mettre à jour ; sinon -> créer le point
                    var pointExiste = true; // var temporaire

                    if (pointExiste) { // màj
                        // Feature/properties : shape(Marker), idPoint, name, category ; optionnel : distance, url, popupContent
                        for (var property in data.features[feature].properties) {
                            var nameIsSet = false,
                                cateIsSet = false,
                                distIsSet = false,
                                urlIsSet = false,
                                popupIsSet = false;
                            // Vérification de l'existance des champs ; si oui : redéfinition
                            if (property == "name") {
                                nameIsSet = true;
                                // data.features[feature].properties[property] = name;
                            }
                            if (property == "category") {
                                cateIsSet = true;
                                // data.features[feature].properties[property] = category;
                            }
                            if (property == "distance") {
                                distIsSet = true;
                                // data.features[feature].properties[property] = distance;
                            }
                            if (property == "url") {
                                urlIsSet = true;
                                // data.features[feature].properties[property] = color;
                            }
                            if (property == "popupContent") {
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
            fd.append("filename", uploadFolder + idParcours + '.geojson');
            fetch("/temp/ajax_geojson.php", {
                method: "POST",
                body: fd
            });

            // Ajouter le nom dans la liste des cartes à afficher | le fichier a été créé dans le php s'il n'existait pas auparavent
            readTextFile(pathId + "url.json")
                .then(function(text) {
                    var liens = JSON.parse(text);

                    var idIsSet = false;
                    for (var key in liens) {
                        if (liens[key].id == idParcours)
                            idIsSet = true;
                    }
                    if (!idIsSet) {

                    }
                });
        }

        processFile();
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