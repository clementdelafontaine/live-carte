<?php  

require_once("includes.php");
require_once("functions.php");														
$data_tmp = $data = array();
global $mysqli;
$id_epreuve = $_GET['id_epreuve'];
$id_parcours = $_GET['id_parcours'];

$choix_equipe = select_equipe($id_epreuve,$id_parcours);
$nb_relais = extract_champ_parcours('relais',$id_parcours);
//echo $nb_relais;
foreach ($choix_equipe as $key=>$equipe) {
	
	if ($equipe['nb']<$nb_relais) {
		$data_tmp[]=array('value'=>$equipe['idInscriptionEpreuveInternaute'].';'.$equipe['idInternaute'],'affichage'=> $equipe['equipe']." (".$equipe['nom']." ".$equipe['prenom'].")");
		}
	}
//array_push($data, $data_tmp);
echo $json = json_encode($data_tmp);
//print_r($json);


?>
