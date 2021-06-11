<?php

require_once("includes/includes.php");

global $mysqli;
echo "debut de page";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$urlParams = explode('/', $_SERVER['REQUEST_URI']);
print_r($urlParams);
$functionName = $urlParams[3];
$functionName();

function uploadClassement(){
    set_error_handler(function ($severity, $message, $file, $line) {
        throw new ErrorException($message, $severity, $severity, $file, $line);
    });

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $json = file_get_contents('php://input');

        $donne_decodee = json_decode($json, true);
        $vraitemps=date('Y-m-d H:i:s');
  $query  = "SELECT * FROM  `chrono_lecteur`";
        $query .=" WHERE idLecteur='".$donne_decodee['idsportlab']."'";
        $query .=" AND date_min <'".$vraitemps."' AND date_max >'".$vraitemps."';";
        $result = $mysqli->query($query);
        $data=mysqli_fetch_array($result);//on recupere l'epreuve correspondant
        $idEpreuve=$data["idEpreuve"];
        date_default_timezone_set('UTC');
        $classement = array();
        try {
            foreach ($donne_decodee as $value2) {
                $value = (array) $value2;
                echo $value[0];
                $arrivant['position'] = $value['position']['result_position'];
                $arrivant['dossard'] = $value['tag'];
                $arrivant['nom'] = $value['lastname'];
                $arrivant['prenom'] = $value['firstname'];
                $arrivant['sexe'] = $value['gender'] . " (" . $value['position']['gender_position'] . ")";
                $arrivant['cat'] = $value['cat'] . " (" . $value['position']['cat_position'] . ")";
                $arrivant['club'] = $value['club'];
                $arrivant['vitesse'] = $value['average_speed'];
                $arrivant['temps'] = date("H:i:s",substr($value['time_total'], 0,-3));
                array_push($classement, $arrivant);
            }
        } catch (ErrorException $e) {
            echo "invalid data : " . $e->getMessage();
            die();
        }

        restore_error_handler();

        $out = "";
        $spaces = array();

        foreach ($classement[0] as $key => $value) {
            $len1 = strlen($key);
            $len2 = 0;
            foreach ($classement as $array) {
                if (strlen($array[$key]) > $len2) $len2 = strlen($array[$key]);
            }
            $out .= $key;
            if ($len2 > $len1) {
                $out .= str_repeat(" ", $len2 - $len1) . " ";
            } else {
                $out .= " ";
            }
            $spaces[$key] = max(array($len1, $len2));

        }
        $out .= "\n";

        foreach ($classement as $arrays) {
            //insert a faire ici
           $query_participant  = "SELECT * FROM  `k_participant`";
                        $query_participant .=" WHERE idEpreuve='".$idEpreuve."'";
                        $query_participant .=" AND dossard='".$classement['dossard']."';";
                        $result_participant = $mysqli->query($query_participant);
                        $data_participant=mysqli_fetch_array($result_participant);
            $query_resultat="INSERT INTO `r_resultats`(`idEpreuve`, `idEpreuveParcours`, `idResultatsParcours`, `classementConcurrent`, `nomConcurrent`, `prenomConcurrent`, `sexeConcurrent`, `dossardConcurrent`, `categorieConcurrent`, `tempsConcurrent`, `vitesseConcurrent`, `clubConcurrent`";
            $query_resultat.=" VALUES ";
            $query_resultat.="'".$idEpreuve."','".$data_participant['idEpreuveParcours']."','".$classement['position']."','".$classement['nom']."','".$classement['prenom']."','".$classement['sexe']."','".$classement['dossard']."','".$classement['cat']."','".$classement['temps']."','".$classement['vitesse']."','".$classement['club']."';";
             $result_resultat= $mysqli->query($query_resultat);
            foreach ($arrays as $cle => $val) {
                $out .= $val;

                if (strlen($val) < $spaces[$cle]) {
                    $out .= str_repeat(" ", $spaces[$cle] - strlen($val)) . " ";
                } else {
                    $out .= " ";
                }
            }
            $out .= "\n";
        }

        file_put_contents("./upload/" .$idEpreuve ."/classement.txt", $out);

        echo $out . "\n";

    } else {
        echo "Error: no post data";
    }
}

function uploadPicture()// On recoit la photo
{
    global $mysqli;
    echo "rentre photo";
    date_default_timezone_set('Europe/Berlin');
    $date=date ('Y\/m\/d H:i:s');//mise en place de la date 

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
            $string=explode("_", $_FILES["file"]["name"]);
            $temps=explode("-", $string[2]);
            $dossard=$string[0];
            $indice=$temps[3];
            $vraitemps=$string[1]." ".$temps[0].":".$temps[1].":".$temps[2];//on recupere la date
       // $idSportLab=$_POST["path"];
       // global $mysqli;//on recupere l'idEpreuve: quand ce sera utile de la table chrono_lecteur
            $query  = "SELECT * FROM  `chrono_lecteur`";
            $query .=" WHERE idLecteur='".$_POST["sportlab"]."'";
            $query .=" AND date_min <'".$vraitemps."' AND date_max >'".$vraitemps."';";
            $result = $mysqli->query($query);
            $data=mysqli_fetch_array($result);//on recupere le num de l'epreuve qu'on est entrain d'enregister
            $idEpreuve=$data["idEpreuve"];
            echo $idEpreuve;

            $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
            $filename = $_FILES["file"]["name"];
            $filetype = $_FILES["file"]["type"];
            $filesize = $_FILES["file"]["size"];

            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (!array_key_exists($ext, $allowed)) die("Error: Please select a valid file format.");//verification du format 

            if (in_array($filetype, $allowed)) {
                mkdir("./upload/".$idEpreuve ."/". $_POST["sportlab"] ."/", 0777, true);//on crée le dossier ou on stockera la photo 
                if (file_exists("./upload/" .$idEpreuve ."/". $_POST["sportlab"] . "/" . $filename)) {
                    echo $filename . " already exists.";//si la photo existe déja, on ne fait rien et affiche ce message
                } else {
                    move_uploaded_file($_FILES["file"]["tmp_name"], "./upload/" .$idEpreuve ."/". $_POST["sportlab"] . "/" . $filename);//on enregistre la photo au bon endroit

                    $query_b = "INSERT INTO `k_photo`( `nomPhoto`, `idEpreuve`, `dossard`, `indicePhoto`, `date`,`idSportLab`) ";
                    $query_b .= "VALUES ('".$filename."','".$idEpreuve."','".$dossard."','".$indice."','".$vraitemps."','". $_POST["sportlab"] ."');";
                    echo "copie";
                    $result_b = $mysqli->query($query_b);//on l'enregistre dans la bd, le chemin, la date , le dossard
                    $query_a="SELECT * from `k_classement_reduit`";
                    $query_a.=" WHERE dossard='".$dossard."' AND tempsUnix='".$vraitemps."';";
                    $result_a = $mysqli->query($query_a);
                    $data_a=mysqli_fetch_array($result_a);//on verifie si un utilisateur lui est accordé
                    if (isset($data_a)){//si il existe on modifie la ligne photo pour dire qu'on en a une
                    $query_c = "UPDATE `k_classement_reduit`SET photo='1'";
                    $query_c .= " WHERE dossard='".$dossard."' AND tempsUnix='".$vraitemps."');";
                    $result_c = $mysqli->query($query_c);
                    }else{//sinon on cree la personne dans le classement reduit 
                        $nom='';
                        $prenom='';
                        $club='';

                        $query_participant  = "SELECT * FROM  `k_participant`";
                        $query_participant .=" WHERE idEpreuve='".$idEpreuve."'";
                        $query_participant .=" AND dossard='".$dossard."';";
                        $result_participant = $mysqli->query($query_participant);
                        $data_participant=mysqli_fetch_array($result_participant);
                        $nom=$data_participant['nom'];
                        echo $prenom;
                        $prenom=$data_participant['prenom'];
                        $club=$data_participant['club'];
                        $query_participant2="INSERT INTO `k_classement_reduit`(`dossard`, `tempsUnix`, `nom`, `prenom`, `club`, `photo`, `idSportLab`,`idEpreuve`)";
                        $query_participant2.=" VALUES ";
                        $query_participant2.="('".$dossard."','".$vraitemps."','".$nom."','".$prenom."','".$club."','1','".$_POST['sportlab']."','".$idEpreuve."')";
                        $result_participant2 = $mysqli->query($query_participant2);
                    }
                // move_uploaded_file($_FILES["file"]["tmp_name"], "./upload/" .  $_POST["sportlab"] ."/" . $filename);
                    $query_count="SELECT COUNT( * ) AS nbPhotos ";//on compte le nombre de photos
                    $query_count.="FROM k_photo ";
                    $query_count.="WHERE idEpreuve ='".$idEpreuve."'";
                    $result_count = $mysqli->query($query_count);
                    $data_count=mysqli_fetch_array($result_count);
                    echo $data_count['nbPhotos'];
                    if ($data_count['nbPhotos']<5){//on lance la création des pages a chaque nouvelles photos lors des 5 premieres
                        echo 'rentre premier photo\n';
                        $current_dir = getcwd();
                        $current_dir = str_replace("\\", "/", $current_dir);
                        echo $current_dir;
                        $ch=curl_init();

                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

                        curl_setopt($ch, CURLOPT_URL, "https://ats-sport.com/temp/creationHTML.php?idEpreuve=".$idEpreuve);


                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_VERBOSE, 1);
                        curl_setopt($ch, CURLOPT_HEADER, 1);


                        $response=curl_exec($ch);

                        $reponse=curl_getinfo($ch,CURLINFO_HTTP_CODE);
                        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

                        $header = substr($response, 0, $header_size);
                        $body = substr($response, $header_size);
                        echo "body:".$body;
                        if(curl_exec($ch) === false)
                        {
                            echo 'Curl error: ' . curl_error($ch);
                        }
                        else
                        {
                            echo 'Operation completed without any errors';
                        }


                        curl_close($ch);
                    }elseif ($data_count['nbPhotos']<50) {//ensuite on cree les pages toutes les 5 photos tant qu'on est en dessous de 50 
                    if (intdiv($data_count['nbPhotos'], 5)){
                         $ch=curl_init();//requete curl pour utiliser la page creationhtml.php, aucun autre moyen

                         curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

                         curl_setopt($ch, CURLOPT_URL, "https://ats-sport.com/temp/creationHTML.php?idEpreuve=".$idEpreuve);


                         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                         curl_setopt($ch, CURLOPT_VERBOSE, 1);
                         curl_setopt($ch, CURLOPT_HEADER, 1);


                         $response=curl_exec($ch);

                         $reponse=curl_getinfo($ch,CURLINFO_HTTP_CODE);
                         $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

                         $header = substr($response, 0, $header_size);
                         $body = substr($response, $header_size);
                         echo "body:".$body;
                         if(curl_exec($ch) === false)
                         {
                            echo 'Curl error: ' . curl_error($ch);
                        }
                        else
                        {
                            echo 'Operation completed without any errors';
                        }


                        curl_close($ch);
                    }
                }else{
                    if (intdiv($data_count['nbPhotos'], 10)){// puis on les créé chaque dizaine de photos
                     $ch=curl_init();

                     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

                     curl_setopt($ch, CURLOPT_URL, "https://ats-sport.com/temp/creationHTML.php?idEpreuve=".$idEpreuve);


                     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                     curl_setopt($ch, CURLOPT_VERBOSE, 1);
                     curl_setopt($ch, CURLOPT_HEADER, 1);


                     $response=curl_exec($ch);

                     $reponse=curl_getinfo($ch,CURLINFO_HTTP_CODE);
                     $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                     $header = substr($response, 0, $header_size);
                     $body = substr($response, $header_size);
                     echo "body:".$body;
                     if(curl_exec($ch) === false)
                     {
                        echo 'Curl error: ' . curl_error($ch);
                    }
                    else
                    {
                        echo 'Operation completed without any errors';
                    }


                    curl_close($ch);
                }
                echo "Your file was uploaded successfully.";
            }
        }
    } else {
        echo "Error: There was a problem uploading your file. Please try again.";
    }
} else {
    echo "Error: " . $_FILES["file"]["error"];
}
} else {
    echo "Error: no post file";
}

}

function addDossard(){//ajouter un dossard a une photo déjà existante
    global $mysqli;
    echo "rentre photo";
    date_default_timezone_set('Europe/Berlin');
    $date=date ('Y\/m\/d H:i:s');//mise en place de la date     
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $json = file_get_contents('php://input');
        $donne_decodee = json_decode($json, true);
        // on rajoute le dossard en updatant la table k_photo et en rajoutant le dossard
        $string=explode("_", $donne_decodee['nomPhoto']);
        $temps=explode("-", $string[2]);
        $dossard=$string[0];
        $indice=$temps[3];
        $vraitemps=$string[1]." ".$temps[0].":".$temps[1].":".$temps[2];//on recupere la date
       // $idSportLab=$_POST["path"];
       // global $mysqli;//on recupere l'idEpreuve: quand ce sera utile de la table chrono_lecteur
        $query  = "SELECT * FROM  `chrono_lecteur`";
        $query .=" WHERE idLecteur='".$donne_decodee["sportlab"]."'";
        $query .=" AND date_min <'".$vraitemps."' AND date_max >'".$vraitemps."';";
        $result = $mysqli->query($query);
            $data=mysqli_fetch_array($result);//on recupere le num de l'epreuve qu'on est entrain d'enregister
            $idEpreuve=$data["idEpreuve"];
            echo $idEpreuve;

            $query_b = "INSERT INTO `k_photo`( `nomPhoto`, `idEpreuve`, `dossard`, `indicePhoto`, `date`,`idSportLab`) ";
            $query_b .= "VALUES ('".$donne_decodee['nomPhoto']."','".$idEpreuve."','".$dossard."','".$indice."','".$vraitemps."','". $donne_decodee["sportlab"] ."');";
            echo "copie";
                    $result_b = $mysqli->query($query_b);//on l'enregistre dans la bd, le chemin, la date , le dossard
                    $query_a="SELECT * from `k_classement_reduit`";
                    $query_a.=" WHERE dossard='".$dossard."' AND tempsUnix='".$vraitemps."';";
                    $result_a = $mysqli->query($query_a);
                    $data_a=mysqli_fetch_array($result_a);//on verifie si un utilisateur lui est accordé
                    if (isset($data_a)){//si il existe on modifie la ligne photo pour dire qu'on en a une
                    $query_c = "UPDATE `k_classement_reduit`SET photo='1'";
                    $query_c .= " WHERE dossard='".$dossard."' AND tempsUnix='".$vraitemps."');";
                    $result_c = $mysqli->query($query_c);
                    }else{//sinon on cree la personne dans le classement reduit 
                        $nom='';
                        $prenom='';
                        $club='';

                        $query_participant  = "SELECT * FROM  `k_participant`";
                        $query_participant .=" WHERE idEpreuve='".$idEpreuve."'";
                        $query_participant .=" AND dossard='".$dossard."';";
                        $result_participant = $mysqli->query($query_participant);
                        $data_participant=mysqli_fetch_array($result_participant);
                        $nom=$data_participant['nom'];
                        echo $prenom;
                        $prenom=$data_participant['prenom'];
                        $club=$data_participant['club'];
                        $query_participant2="INSERT INTO `k_classement_reduit`(`dossard`, `tempsUnix`, `nom`, `prenom`, `club`, `photo`, `idSportLab`,`idEpreuve`)";
                        $query_participant2.=" VALUES ";
                        $query_participant2.="('".$dossard."','".$vraitemps."','".$nom."','".$prenom."','".$club."','1','".$donne_decodee['sportlab']."','".$idEpreuve."')";
                        $result_participant2 = $mysqli->query($query_participant2);
                    }


                } else {
                    echo "Error: no post data";
                }
            }
function uploadData(){//enregistrement des passages sans photos
    global $mysqli;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $json = file_get_contents('php://input');
        $donne_decodee = json_decode($json, true);
        //$temps=getdate($donne_decodee['timestamp']);
        //echo "temps : ".$temps;
        echo "donne temps: ".$donne_decodee['timestamp'];
        //$vraitemps=$temps['year']."-".$temps['mon']."-".$temps['mday']." ".$temps['hours'].":".$temps['minutes'].":".$temps['seconds'];//on recupere la date sous la bonne forme 
        echo "sportlab".$donne_decodee["sportlab"];
        print_r($donne_decodee);
        $query  = "SELECT * FROM  `chrono_lecteur`";
        $query .=" WHERE idLecteur='".$donne_decodee["sportlab"]."'";
        $query .=" AND date_min <'".$donne_decodee['timestamp']."' AND date_max >'".$donne_decodee['timestamp']."';";
        echo $query;
        $result = $mysqli->query($query);
        $data=mysqli_fetch_array($result);//on recupere l'epreuve correspondant 
        $idEpreuve=$data["idEpreuve"];
        $nom="";
        $prenom="";
        $club="";
        $query_participant  = "SELECT * FROM  `k_participant`";
        $query_participant .=" WHERE idEpreuve='".$idEpreuve."'";
        $query_participant .=" AND dossard='".$donne_decodee['serial']."';";
        $result_participant = $mysqli->query($query_participant);
        $data_participant=mysqli_fetch_array($result_participant);//on recupere les donnes du participant 
        $query_live="UPDATE r_epreuve SET Live=oui WHERE idEpreuve=".$idEpreuve;
        $result_live=$mysqli->query($query_live);
        
        $nom=$data_participant['nom'];
        $prenom=$data_participant['prenom'];
        $club=$data_participant['club'];
        $query_update  = "UPDATE `r_epreuve` SET Live='oui' WHERE idEpreuve=".$idEpreuve;
        $result_update = $mysqli->query($query_update);
        $query_a="SELECT * from `k_classement_reduit`";
        $query_a.=" WHERE dossard='".$donne_decodee['serial']."' AND tempsUnix='".$donne_decodee['timestamp']."';";
        $result_a = $mysqli->query($query_a);
                    $data_a=mysqli_fetch_array($result_a);//on verifie si un utilisateur lui est accordé
                    if (isset($data_a)){//si il existe on modifie la ligne photo pour dire qu'on en a une
                    }else{//sinon on cree la personne dans le classement reduit 
                        $nom='';
                        $prenom='';
                        $club='';
                        $query_participant  = "SELECT * FROM  `k_participant`";
                        $query_participant .=" WHERE idEpreuve='".$idEpreuve."'";
                        $query_participant .=" AND dossard='".$donne_decodee["serial"]."';";
                        $result_participant = $mysqli->query($query_participant);
                        $data_participant=mysqli_fetch_array($result_participant);
                        $nom=$data_participant['nom'];
                        $prenom=$data_participant['prenom'];
                        $club=$data_participant['club'];
                        $query_participant2="INSERT INTO `k_classement_reduit`(`dossard`, `tempsUnix`, `nom`, `prenom`, `club`, `photo`, `idSportLab`,`idEpreuve`)";
                        $query_participant2.=" VALUES ";
                        $query_participant2.="('".$donne_decodee['serial']."','".$donne_decodee['timestamp']."','".$nom."','".$prenom."','".$club."','0','".$donne_decodee['sportlab']."','".$idEpreuve."')";
        $result_participant2 = $mysqli->query($query_participant2);//on enregistre ces donnés en y rajoutant le temps et dossard lors du passage
    }
                     $query_count="SELECT COUNT( * ) AS nbPhotos ";//on compte le nombre de photos
                     $query_count.="FROM k_photo ";
                     $query_count.="WHERE idEpreuve ='".$idEpreuve."'";
                     $result_count = $mysqli->query($query_count);
                     $data_count=mysqli_fetch_array($result_count);
                     echo $data_count['nbPhotos'];
                    if ($data_count['nbPhotos']<5){//on lance la création des pages a chaque nouvelles photos lors des 5 premieres
                        echo 'rentre premier photo\n';
                        $current_dir = getcwd();
                        $current_dir = str_replace("\\", "/", $current_dir);
                        echo $current_dir;
                        $ch=curl_init();

                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

                        curl_setopt($ch, CURLOPT_URL, "https://ats-sport.com/temp/creationHTML.php?idEpreuve=".$idEpreuve);


                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_VERBOSE, 1);
                        curl_setopt($ch, CURLOPT_HEADER, 1);


                        $response=curl_exec($ch);

                        $reponse=curl_getinfo($ch,CURLINFO_HTTP_CODE);
                        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

                        $header = substr($response, 0, $header_size);
                        $body = substr($response, $header_size);
                        echo "body:".$body;
                        if(curl_exec($ch) === false)
                        {
                            echo 'Curl error: ' . curl_error($ch);
                        }
                        else
                        {
                            echo 'Operation completed without any errors';
                        }


                        curl_close($ch);
                    }elseif ($data_count['nbPhotos']<50) {//ensuite on cree les pages toutes les 5 photos tant qu'on est en dessous de 50 
                    if (intdiv($data_count['nbPhotos'], 5)){
                         $ch=curl_init();//requete curl pour utiliser la page creationhtml.php, aucun autre moyen

                         curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

                         curl_setopt($ch, CURLOPT_URL, "https://ats-sport.com/temp/creationHTML.php?idEpreuve=".$idEpreuve);


                         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                         curl_setopt($ch, CURLOPT_VERBOSE, 1);
                         curl_setopt($ch, CURLOPT_HEADER, 1);


                         $response=curl_exec($ch);

                         $reponse=curl_getinfo($ch,CURLINFO_HTTP_CODE);
                         $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

                         $header = substr($response, 0, $header_size);
                         $body = substr($response, $header_size);
                         echo "body:".$body;
                         if(curl_exec($ch) === false)
                         {
                            echo 'Curl error: ' . curl_error($ch);
                        }
                        else
                        {
                            echo 'Operation completed without any errors';
                        }


                        curl_close($ch);
                    }
                }else{
                    if (intdiv($data_count['nbPhotos'], 10)){// puis on les créé chaque dizaine de photos
                     $ch=curl_init();

                     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

                     curl_setopt($ch, CURLOPT_URL, "https://ats-sport.com/temp/creationHTML.php?idEpreuve=".$idEpreuve);


                     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                     curl_setopt($ch, CURLOPT_VERBOSE, 1);
                     curl_setopt($ch, CURLOPT_HEADER, 1);


                     $response=curl_exec($ch);

                     $reponse=curl_getinfo($ch,CURLINFO_HTTP_CODE);
                     $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                     $header = substr($response, 0, $header_size);
                     $body = substr($response, $header_size);
                     echo "body:".$body;
                     if(curl_exec($ch) === false)
                     {
                        echo 'Curl error: ' . curl_error($ch);
                    }
                    else
                    {
                        echo 'Operation completed without any errors';
                    }


                    curl_close($ch);
                }
            }
        //TODO sauvegarder chaque $donne_decodee (array serial=>[numeroDossard], timestamp=>[tempsUnix] ) (dans la BD ?)

        } else {
            echo "Error: no post data";
        }
    }

    function uploadParticipant()
    {global $mysqli;
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $json = file_get_contents('php://input');
        $donne_decodee = json_decode($json, true);// on recoit un tableau de participant
        print_r("retour:".$donne_decodee[0 ]);
        $vraitemps=date('Y-m-d H:i:s');

        $query  = "SELECT * FROM  `chrono_lecteur`";
        $query .=" WHERE idLecteur='".$donne_decodee['idsportlab']."'";
        $query .=" AND date_min <'".$vraitemps."' AND date_max >'".$vraitemps."';";
        $result = $mysqli->query($query);
        $data=mysqli_fetch_array($result);//on recupere l'epreuve correspondant
        $idEpreuve=$data["idEpreuve"];
        $query_suppr  = "TRUNCATE TABLE `k_participant`";
        $result_suppr = $mysqli->query($query_suppr);
        


        foreach ($donne_decodee[0] as $participant) {//pour chaque participant, on l'enregistre dans la bd 
        //a changer pour faire en sorte que ca prenne un fichier 
        $query_a="SELECT * from `r_epreuveparcours`";
         $query_a.=" WHERE nomParcours='".$participant['parcours'].";";
         $result_a = $mysqli->query($query_a);
         $data_a=mysqli_fetch_array($result_a);
         if (isset($data_a)){
             $query_participant="INSERT INTO `k_participant`(`id`, `dossard`, `parcours`, `nom`, `prenom`, `genre`, `club`,`idEpreuve`,idEpreuveParcours)";
        $query_participant.=" VALUES ";
        $query_participant.="('".$participant['id']."','".$participant['dossard']."','".$participant['parcours']."','".$participant['nom']."','".$participant['prenom']."','".$participant['genre']."','".$participant['club']."','".$idEpreuve."','".$data_a['idEpreuveParcours']."')";
        $result_part = $mysqli->query($query_participant);
         }else{
             $query_participant2="INSERT INTO `r_epreuveparcours`(idEpreuve,nomParcours)";
                        $query_participant2.=" VALUES ";
                        $query_participant2.="('".$idEpreuve."','".$participant['parcours'].")";
        $result_participant2 = $mysqli->query($query_participant2);
         }
       
    }

}    else {
    echo "Error: no post data";
}
}

function hidePicture(){
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $json = file_get_contents('php://input');
        $donne_decodee = json_decode($json, true);

        $query="UPDATE `k_photo` SET visible = 1 WHERE nomPhoto='".$donne_decodee[0]."'";
        $result = $mysqli->query($query);

    } else {
        echo "Error: no post data";
    }
}


function showPicture(){
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $json = file_get_contents('php://input');
        $donne_decodee = json_decode($json, true);
        $query="UPDATE `k_photo` SET visible = 0 WHERE nomPhoto='".$donne_decodee[0]."'";
        $result = $mysqli->query($query);


    } else {
        echo "Error: no post data";
    }
}
/*
function deletePicture(){
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $json = file_get_contents('php://input');
        $donne_decodee = json_decode($json, true);

        //TODO "DELETE FROM tablePhotos WHERE nom='" . $donne_deconne . "';"

    } else {
        echo "Error: no post data";
    }
}
*/
echo "yes";