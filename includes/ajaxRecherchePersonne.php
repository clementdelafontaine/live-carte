<?php 
 
global $mysqli;
require_once("includes.php");
require_once("functions.php");	
if ($_GET['action']=='fast') {
	$term = $_GET['q'];
				
				if ($_SESSION["typeInternaute"] == "admin") {
					$query = "SELECT riei.idInscriptionEpreuveInternaute, rep.idEpreuve, rep.idEpreuveParcours, rep.nomParcours, i.idInternaute, i.nomInternaute, i.prenomInternaute,i.sexeInternaute,i.naissanceInternaute, i.emailInternaute, i.clubInternaute, i.adresseInternaute, i.cpInternaute, i.villeInternaute, i.paysInternaute,i.index_telephone,i.telephone FROM r_internaute AS i
							  INNER JOIN r_inscriptionepreuveinternaute as riei ON i.idInternaute = riei.idInternaute
							  INNER JOIN r_epreuveparcours as rep ON riei.idEpreuveParcours = rep.idEpreuveParcours
								WHERE CONCAT(i.nomInternaute, ' ', i.prenomInternaute) LIKE '".$term."%' 
								ORDER BY rep.idEpreuveParcours DESC LIMIT 50";	
				}
				else
				{
					$query = "SELECT iei.idInscriptionEpreuveInternaute, iei.idEpreuveParcours, rep.idEpreuve, rep.idEpreuveParcours, rep.nomParcours, i.idInternaute, i.nomInternaute, i.prenomInternaute,i.sexeInternaute,i.naissanceInternaute, i.emailInternaute, i.clubInternaute, i.adresseInternaute, i.cpInternaute, i.villeInternaute, i.paysInternaute,i.index_telephone,i.telephone FROM r_inscriptionepreuveinternaute AS iei, r_internaute AS i, r_epreuve AS e
						INNER JOIN r_epreuveparcours as rep ON iei.idEpreuveParcours = rep.idEpreuveParcours
						WHERE e.idInternaute = '".$_SESSION["log_id"]."'
						AND e.idEpreuve = iei.idEpreuve
						AND iei.idInternaute = i.idInternaute
						AND CONCAT(i.nomInternaute, ' ', i.prenomInternaute) LIKE '".$term."%'
						ORDER BY rep.idEpreuveParcours DESC LIMIT 50";
					
					$query = "SELECT riei.idInscriptionEpreuveInternaute, rep.idEpreuve, rep.idEpreuveParcours, rep.nomParcours, i.idInternaute, i.nomInternaute, i.prenomInternaute,i.sexeInternaute,i.naissanceInternaute, i.emailInternaute, i.clubInternaute, i.adresseInternaute, i.cpInternaute, i.villeInternaute, i.paysInternaute,i.index_telephone,i.telephone FROM r_internaute AS i
							  INNER JOIN r_inscriptionepreuveinternaute as riei ON i.idInternaute = riei.idInternaute
							  INNER JOIN r_epreuveparcours as rep ON riei.idEpreuveParcours = rep.idEpreuveParcours
							  INNER JOIN r_epreuve as re ON rep.idEpreuve= re.idEpreuve
							  WHERE re.idInternaute = '".$_SESSION["log_id"]."'
								AND CONCAT(i.nomInternaute, ' ', i.prenomInternaute) LIKE '".$term."%' 
								ORDER BY rep.idEpreuveParcours DESC LIMIT 50";	
				
				}
				//echo $query;
				$result = $mysqli->query($query);
				$internaute_row = array();
				$internaute = array();
				$cpt=0;
				while($row = mysqli_fetch_array($result))
				{
						
						$internaute_row['prenomInternaute'] = $row['prenomInternaute'];
						$internaute_row['sexeInternaute'] = $row['sexeInternaute'];
						$internaute_row['naissanceInternaute'] = dateen2fr($row['naissanceInternaute'],1);
						$internaute_row['emailInternaute'] = $row['emailInternaute'];
						$internaute_row['clubInternaute'] = $row['clubInternaute'];
						$internaute_row['adresseInternaute'] = $row['adresseInternaute'];
						$internaute_row['cpInternaute'] = $row['cpInternaute'];
						$internaute_row['villeInternaute'] = $row['villeInternaute'];
						$internaute_row['paysInternaute'] = $row['paysInternaute'];
						$internaute_row['index_telephone'] = $row['index_telephone'];
						$internaute_row['telephone'] = $row['telephone'];

						$internaute_row['value'] = $row['nomInternaute']." ".$row['prenomInternaute'];
						$internaute_row['idInternaute'] = $row['idInternaute'];
						
						$date_naissance = $internaute_row['naissanceInternaute'];
						
						$sexe = $internaute_row['sexeInternaute'];
						
						$internaute_row['idInscriptionEpreuveInternaute'] = $row['idInscriptionEpreuveInternaute'];
						$internaute_row['idEpreuve'] = $row['idEpreuve'];
						$internaute_row['idEpreuveParcours'] = $row['idEpreuveParcours'];
						
						if ($type_epreuve==2) $sexe_cat = $sexe; else $sexe_cat = 'MF';
						$cat = calcul_categorie(substr($date_naissance,-4),'code',$type_epreuve,$sexe_cat);
						
						$internaute_row['categorie'] =	$cat['code'];	
						
						$internaute_row['title'] = strtoupper($row['nomInternaute']." ".$row['prenomInternaute']." né le ".$row['naissanceInternaute']." / Parcours : ".$row['nomParcours']);
						if ($_SESSION["typeInternaute"] == "admin") $internaute_row['title'] .= ' ('.$internaute_row['idEpreuve'].')';
						if (!empty($row['clubInternaute'])) $internaute_row['label'] .= ' et a pour club : '.$row['clubInternaute'];
						
					array_push($internaute, $internaute_row);
					$cpt++;
				}

				if($cpt == 0) {
					$internaute_row['label'] = 'Aucun résultat';
					$internaute_row['value'] = '';
					$internaute_row['id'] = '0';
					array_push($internaute, $internaute_row);
				}
				//$data = array('ville'=>$ville);
				$json = json_encode($internaute);
				echo $_GET['callback'] . '('.$json.')';
				//print_r($json);
	
}else {
				$term = $_GET['term'];
				$term = str_replace(" ","-",$_GET['term']);
				
				$idEpreuve = $_GET['id_epreuve'];
				$type_certificat_bdd = type_epreuve($idEpreuve );
				$type_epreuve = $type_certificat_bdd['idTypeEpreuve'];
				
				

				/*if(isset($_SESSION['typeInternaute']) && $_SESSION['typeInternaute'] == 'inscripteur')
				{
					$query = "SELECT i.nomInternaute, i.prenomInternaute FROM r_inscriptionepreuveinternaute AS iei, r_internaute AS i, r_epreuve AS e, h_intervenantsepreuve AS intep 
						WHERE intep.idInternaute = '".$_SESSION["log_id"]."'
						AND e.idEpreuve = iei.idEpreuve
						AND iei.idInternaute = i.idInternaute
						AND i.nomInternaute LIKE '".$term."%' LIMIT 10";
				}
				else
				{*/
				//echo $_SESSION["typeInternaute"];
				if ($_SESSION["typeInternaute"] == "admin") {
					$query = "SELECT  DISTINCT(i.nomInternaute), i.idInternaute, i.prenomInternaute,i.sexeInternaute,i.naissanceInternaute, i.emailInternaute, i.clubInternaute, i.adresseInternaute, i.cpInternaute, i.villeInternaute, i.paysInternaute,i.index_telephone,i.telephone FROM r_internaute AS i
						WHERE CONCAT(i.nomInternaute,' ', i.prenomInternaute) LIKE '".$term."%' GROUP BY CONCAT(i.nomInternaute,' ', i.prenomInternaute,i.naissanceInternaute) ORDER BY i.nomInternaute ASC, i.prenomInternaute ASC LIMIT 300 ";	
				}
				else
				{
					$query = "SELECT  DISTINCT(i.idInternaute), i.nomInternaute, i.prenomInternaute,i.sexeInternaute,i.naissanceInternaute, i.emailInternaute, i.clubInternaute, i.adresseInternaute, i.cpInternaute, i.villeInternaute, i.paysInternaute,i.index_telephone,i.telephone FROM r_inscriptionepreuveinternaute AS iei, r_internaute AS i, r_epreuve AS e
						WHERE e.idInternaute = '".$_SESSION["log_id"]."'
						AND e.idEpreuve = iei.idEpreuve
						AND iei.idInternaute = i.idInternaute
						AND CONCAT(i.nomInternaute,' ', i.prenomInternaute) LIKE '".$term."%' GROUP BY CONCAT(i.nomInternaute,' ', i.prenomInternaute,i.naissanceInternaute) ORDER BY i.nomInternaute ASC, i.prenomInternaute ASC LIMIT 70";	
				}
				//}
				//echo $query;
				$result = $mysqli->query($query);
				$internaute_row = array();
				$internaute = array();
				$cpt=0;
				while($row = mysqli_fetch_array($result))
				{
						$internaute_row['prenomInternaute'] = $row['prenomInternaute'];
						$internaute_row['sexeInternaute'] = $row['sexeInternaute'];
						$internaute_row['naissanceInternaute'] = dateen2fr($row['naissanceInternaute'],1);
						$internaute_row['emailInternaute'] = $row['emailInternaute'];
						$internaute_row['clubInternaute'] = $row['clubInternaute'];
						$internaute_row['adresseInternaute'] = $row['adresseInternaute'];
						$internaute_row['cpInternaute'] = $row['cpInternaute'];
						$internaute_row['villeInternaute'] = $row['villeInternaute'];
						$internaute_row['paysInternaute'] = $row['paysInternaute'];
						$internaute_row['index_telephone'] = $row['index_telephone'];
						$internaute_row['telephone'] = $row['telephone'];
						$internaute_row['label'] = $row['nomInternaute']." ".$row['prenomInternaute']." né le ".$row['naissanceInternaute'];
						if (!empty($row['clubInternaute'])) $internaute_row['label'] .= ' et a pour club : '.$row['clubInternaute'];
						$internaute_row['value'] = $row['nomInternaute'];
						$internaute_row['idInternaute'] = $row['idInternaute'];
						
						$date_naissance = $internaute_row['naissanceInternaute'];
						
						$sexe = $internaute_row['sexeInternaute'];
						
						if ($type_epreuve==2) $sexe_cat = $sexe; else $sexe_cat = 'MF';
						$cat = calcul_categorie(substr($date_naissance,-4),'code',$type_epreuve,$sexe_cat);
						
						$internaute_row['categorie'] =	$cat['code'];	
				
				
					array_push($internaute, $internaute_row);
					$cpt++;
				}

				if($cpt == 0) {
					$internaute_row['label'] = 'Aucun résultat';
					$internaute_row['value'] = '';
					$internaute_row['id'] = '0';
					array_push($internaute, $internaute_row);
				}
				//$data = array('ville'=>$ville);
				$json = json_encode($internaute);
				print_r($json);
}
?>
