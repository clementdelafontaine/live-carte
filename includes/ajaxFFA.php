<?php 
	require_once('functions.php');
	require_once('connect_db.php');
	connect_db();
global $mysqli;
//$nom = $_GET['n'];
$nom =str_replace(' ','%20',$_GET['n']);
$prenom = str_replace(' ','%20',$_GET['p']);
$sexe = $_GET['s'];
$date_n = $_GET['a'];
$num_lic = $_GET['num'];
$id_epreuve = $_GET['id_epreuve'];
/*
echo "<b>ENTREE</b></br>";

echo "NOM : ".$nom ."</br>";
echo "PRENOM : ".$prenom."</br>";
echo "SEXE : ".$sexe."</br>";
echo "ANNEE DE NAISSANCE : ".$date_n."</br>";
*/
//echo "pppp".$test = extract_champ_parcours('nomParcours',7189);
$champs = webservice_ffa ($nom,$prenom,$sexe,$date_n,$num_lic,$id_epreuve);

$json = json_encode($champs);
print_r($json);
?>