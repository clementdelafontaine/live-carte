<?php 
//print_r($_POST);
require_once("includes/includes.php");
require_once("includes/function_image.php");
global $mysqli;

ini_set("display_errors", 0);
error_reporting(E_ALL);

	if (!function_exists('mysql_result')) {
		function mysql_result($result, $number=0, $field=0) 
		{
			mysqli_data_seek($result, $number);
			$row = mysqli_fetch_array($result);
			return $row[$field];
		}
	}
	
function parcours_tarifs($epre_id)
{
	global $mysqli;
	$tab_id = array();				
	$tab_tarif = array();
	$query  = "SELECT idEpreuveParcoursTarif, idEpreuveParcours, desctarif, tarif, dateDebutTarif, dateFinTarif,nb_dossard,nb_dossard_pris,reduction ";
	$query .= "FROM r_epreuveparcourstarif ";
	$query .= "WHERE idEpreuve='".$epre_id."' ";
	$query .= "ORDER BY idEpreuveParcoursTarif;";
	$result = $mysqli->query($query);

	while (($row=mysqli_fetch_array($result)) != FALSE)
	{
		$tab_tarif[$row['idEpreuveParcours']][$row['idEpreuveParcoursTarif']] = array(sql_to_form($row['desctarif']), $row['tarif'], $row['dateDebutTarif'], $row['dateFinTarif'], $row['nb_dossard'], $row['nb_dossard_pris'], $row['reduction']);
	}

	$query  = "SELECT idEpreuveParcours, idTypeParcours, nomParcours, nbtarif, horaireDepart, dossardDeb, dossardFin, nbexclusion, dossards_exclus, ordre_affichage, relais, ageLimite, age, ParcoursDescription, certificatMedical, dossard_equipe, certificatMedicalObligatoire, date_max_depose_certif, autoParentale, infoParcoursInscription, visible_liste_inscrit ";
	$query .= "FROM r_epreuveparcours ";
	$query .= "WHERE idEpreuve='".$epre_id."' ";
	$query .= "ORDER BY idEpreuveParcours;";
	$result = $mysqli->query($query);
	$nb_parcours =  mysqli_num_rows($result); 
	$j = 0;
	while (($row=mysqli_fetch_array($result)) != FALSE)
	{
		$j++;
		$tab_id[$j][0] = $row['idEpreuveParcours'];

		$jj = 0;
		foreach ($tab_tarif[$row['idEpreuveParcours']] as $k=>$i)
		{
			$jj++;
			$tab_id[$j][1][$jj] = $k;
		}
	}
	return $tab_id;	
}
			
$tab_id = parcours_tarifs($_POST['idEpreuve']);

function datefr2en($mydate,$wtime=0)
{   
global $mysqli;
   if ($wtime == 0) {
		@list($date,$horaire)=explode(' ',$mydate);
		@list($jour,$mois,$annee)=explode('/',$date);
		@list($heure,$minute)=explode(':',$horaire);
		return @date('Y-m-d H:i:s',strtotime($mois."/".$jour."/".$annee." ".$heure.":".$minute));
   }
   else
   {
		@list($jour,$mois,$annee)=explode('/',$mydate);
		return @date('Y-m-d',strtotime($mois."/".$jour."/".$annee));   
   }
}

function code_separator($car) {
	global $mysqli;
	$string = "";
	$chaine = "|@#aBcde12345";
	srand((double)microtime()*1000000);
	for($i=0; $i<$car; $i++) {
	$string .= $chaine[rand()%strlen($chaine)];
	}
	return $string;
}

function generer_code_promo($car) {
	global $mysqli;
	$string = "";
	$chaine = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	srand((double)microtime()*1000000);
	for($i=0; $i<$car; $i++) {
	$string .= $chaine[rand()%strlen($chaine)];
	}
	return $string;
}

function histo_back_end($idEpreuve, $idEpreuveParcours, $idInscriptionEpreuveInternaute,$idInternaute,$action,$infos_complementaires)
{	
	global $mysqli;
	$typeidInternauteConnect = $_SESSION['typeInternaute'];
	$idInternauteConnect = $_SESSION["log_id"];
	$login = $_SESSION["log_log"];
        if (empty($login)) $login ='root';

	$ipidInternauteConnect = "0.0.0.0";
	$localisation ='';
	$query_b = "INSERT INTO `r_histo_action_backend` (`idEpreuve`, `idEpreuveParcours`, `idInternauteConnect`, login, `typeidInternauteConnect`, `ipidInternauteConnect`, `localisation`, `idInscriptionEpreuveInternaute`, `idInternaute`, `action`, date, infos_complementaires) ";
	$query_b .= "VALUES (".$idEpreuve.", ".$idEpreuveParcours.", ".$idInternauteConnect.", '".$login."','".$typeidInternauteConnect."', '".$ipidInternauteConnect."', '".$localisation."', '".$idInscriptionEpreuveInternaute."', '".$idInternaute."','".$action."', NOW(), '".addslashes_form_to_sql($infos_complementaires)."')";
	$result_b = $mysqli->query($query_b);	
}

function nom_code_promo($car) {
	global $mysqli;
	$string = "";
	$chaine = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	srand((double)microtime()*1000000);
	for($i=0; $i<$car; $i++) {
	$string .= $chaine[rand()%strlen($chaine)];
	}
	return $string;
}
function clearFolder($id_session) {
	global $mysqli;
$dir ="dl/";
	if (is_dir($dir)) {
	
		// si il contient quelque chose
		if ($dh = opendir($dir)) {
	
			// boucler tant que quelque chose est trouve
			while (($file = readdir($dh)) !== false) {
	
				// affiche le nom et le type si ce n'est pas un element du systeme
				
				if( $file != '.' && $file != '..' && preg_match('#\.(png|jpg|gif|pdf)$#i', $file)) {

					$filename = $type_filename."-".$id_session;
				
					if(preg_match('#'.$filename.'#', $file)){
						unlink($dir.$filename);
					}
				}
			}
			closedir($dh);
		}
	}
}
	global $mysqli;
	global $_POST;
	global $_GET;
	$p->centre = "";
	$nomfichier = "";
	$error=0;
	$chemin=getcwd().DIRECTORY_SEPARATOR;
	$rep_fichiers_temp  ="dl".DIRECTORY_SEPARATOR;
	$rep_fichiers_epreuves  ="fichiers_epreuves".DIRECTORY_SEPARATOR;
	$id_session = $_SESSION['unique_id_session'];

	if (isset($_GET['epre_button']))
	{
			$idE = $_POST['idEpreuve'];
			$nb_parcours_existant = sizeof($_SESSION['mod_epre_ids_'.$idE]);

			$nb_parcours_soumis = $_POST['epre_nbparc'];
			
					//**** photo de l'épreuve
					$query  = "SELECT nom_fichier_reel,nom_fichier_temp,nom_fichier_affichage,date ";
					$query .= "FROM r_fichier_epreuve_temp ";
					$query .= "WHERE id_session =  '".$id_session."' ";
					$query .= "AND type = 'photo_epreuve' ";
					$query .= "AND action = 'ins' ";

					$result = $mysqli->query($query);
					$row=mysqli_fetch_array($result);
		
					if(isset($row['nom_fichier_temp']))
					{
						if(rename($chemin.$rep_fichiers_temp.$row['nom_fichier_temp'], $rep_fichiers_epreuves.$row['nom_fichier_reel']))
						{
							$query  = "DELETE FROM r_epreuvefichier ";
							$query .= "WHERE idEpreuve =  ".$idE." ";
							$query .= "AND type = 'photo_epreuve' ";
							$result = $mysqli->query($query);							
							
							$query  = "INSERT INTO r_epreuvefichier(idEpreuve, idEpreuveParcours, nom_fichier,nom_fichier_affichage,type,date) ";
							$query .= "VALUES(".$idE.",0,'".$row['nom_fichier_reel']."','".$row['nom_fichier_affichage']."','photo_epreuve','".$row['date']."')";

							$result = $mysqli->query($query );
							
							$query  = "DELETE FROM r_fichier_epreuve_temp ";
							$query .= "WHERE id_session =  '".$id_session."' ";
							$query .= "AND type = 'photo_epreuve' ";
							$query .= "AND action = 'ins' ";
							$query .= "AND nom_fichier_temp = '".$row['nom_fichier_temp']."' ";
							$result = $mysqli->query($query);
							
						}
					}
					
					//suppression des fichiers photo epreuve à la demande de l'utilisateur
					$query  = "SELECT nom_fichier_reel ";
					$query .= "FROM r_fichier_epreuve_temp ";
					$query .= "WHERE id_session =  '".$id_session."' ";
					$query .= "AND type = 'photo_epreuve' ";
					$query .= "AND action = 'del' ";
					$result = $mysqli->query($query);

					while (($row=mysqli_fetch_array($result)) != FALSE)
					{
						$query_del =  "DELETE FROM r_epreuvefichier ";
						$query_del .= "WHERE nom_fichier = '".$row['nom_fichier_reel']."' ";
						$query_del .= "AND idEpreuve = ".$idE." ";
						$query_del .= "AND type = 'photo_epreuve' ";
						$result_del = $mysqli->query($query_del);

						if ($result_del  != FALSE)
						{
							unlink("fichiers_epreuves/".$row['nom_fichier_reel']);
						}
					}

					//*** Fichier de l'épreuve
					$query  = "SELECT nom_fichier_reel,nom_fichier_temp,nom_fichier_affichage,date ";
					$query .= "FROM r_fichier_epreuve_temp ";
					$query .= "WHERE id_session =  '".$id_session."' ";
					$query .= "AND type = 'docs_epreuve' ";
					$query .= "AND action = 'ins' ";
					$result = $mysqli->query($query);
					while (($row=mysqli_fetch_array($result)) != FALSE)
					{
						if(isset($row['nom_fichier_temp']))
						{
							if(rename($chemin.$rep_fichiers_temp.$row['nom_fichier_temp'], $rep_fichiers_epreuves.$row['nom_fichier_reel']))
							{
								$query_ins  = "INSERT INTO r_epreuvefichier(idEpreuve, idEpreuveParcours, nom_fichier,nom_fichier_affichage,type,date) ";
								$query_ins .= "VALUES(".$idE.",0,'".$row['nom_fichier_reel']."','".$row['nom_fichier_affichage']."','docs_epreuve','".$row['date']."')";

								$result_ins = $mysqli->query($query_ins);
								
								$query_del  = "DELETE FROM r_fichier_epreuve_temp ";
								$query_del .= "WHERE id_session =  '".$id_session."' ";
								$query_del .= "AND type = 'docs_epreuve' ";
								$query_del .= "AND nom_fichier_temp = '".$row['nom_fichier_temp']."' ";
								$result_del = $mysqli->query($query_del);
							}
						}
					}
					
					//suppression des fichiers docs epreuve à la demande de l'utilisateur
					$query  = "SELECT nom_fichier_reel ";
					$query .= "FROM r_fichier_epreuve_temp ";
					$query .= "WHERE id_session =  '".$id_session."' ";
					$query .= "AND type = 'docs_epreuve' ";
					$query .= "AND action = 'del' ";
					$result = $mysqli->query($query);

					while (($row=mysqli_fetch_array($result)) != FALSE)
					{
						$query_del =  "DELETE FROM r_epreuvefichier ";
						$query_del .= "WHERE nom_fichier = '".$row['nom_fichier_reel']."' ";
						$query_del .= "AND idEpreuve = ".$idE." ";
						$query_del .= "AND type = 'docs_epreuve' ";
						$result_del = $mysqli->query($query_del);

						if ($result_del  != FALSE)
						{
							unlink("fichiers_epreuves/".$row['nom_fichier_reel']);
						}
					}
					
			for ($j=1;$j<=$nb_parcours_existant;$j++)
			{
				$exclus = array();
				for ($jj=1; $jj<=$_POST['epre_parc_nbexclusion'][$j]; $jj++)
				$exclus[$j][$jj] = !empty($_POST['parc_dossard_exclus_min'][$j][$jj]) && !empty($_POST['parc_dossard_exclus_max'][$j][$jj])? intval($_POST['parc_dossard_exclus_min'][$j][$jj])."-".intval($_POST['parc_dossard_exclus_max'][$j][$jj]) : "0-0";
					
				$parc_dossardExclus = implode(":",$exclus[$j]);
				
				//Le parcours existe - On update / insert ou efface les données.
				if (in_array($_SESSION['mod_epre_ids_'.$idE][$j][0], $_POST['id_table_parcours'])) 
				{
					$id = $_SESSION['mod_epre_ids_'.$idE][$j][0];
					
					$query  = "UPDATE r_epreuveparcours SET ";
					$query .= "idTypeParcours='".intval($_POST['epre_parc_type'][$j])."', ";
					$query .= "nomParcours='".addslashes_form_to_sql($_POST['epre_parc_nom'][$j])."', ";
					$query .= "nbtarif='".intval($_POST['epre_parc_nbprix'][$j])."', ";
					$query .= "horaireDepart='".datefr2en($_POST['epre_parc_date'][$j])."', ";
					$query .= "dossardDeb= ".intval($_POST['parc_dossard'][$j]).", ";
					$query .= "dossardFin= ".intval($_POST['parc_dossardFin'][$j]).", ";
					$query .= "nbexclusion= ".intval($_POST['epre_parc_nbexclusion'][$j]).", ";
					$query .= "dossards_exclus= '".$parc_dossardExclus."', ";
					$query .= "ordre_affichage= ".intval($_POST['epre_parc_ordre'][$j]).", ";
					$query .= "dossard_equipe = '".(($_POST['dossard_equipe'][$j] == 'oui')?'oui':'non')."', ";
					$query .= "relais= ".((isset($_POST['relais'][$j]) && $_POST['relais'][$j] == 1)?$_POST['relais_nb_personne'][$j]:0).", ";
					$query .= "min_relais= ".((isset($_POST['relais'][$j]) && $_POST['relais'][$j] == 1)?$_POST['relais_nb_personne_min'][$j]:0).", ";
					$query .= "ageLimite= ".intval($_POST['epre_parc_age_fin'][$j]).", ";
					$query .= "age= ".intval($_POST['epre_parc_age_debut'][$j]).", ";
					$query .= "ParcoursDescription= '".addslashes_form_to_sql($_POST['parcours_description'][$j])."', ";
					$query .= "infoParcoursInscription= '".addslashes_form_to_sql($_POST['info_complementaire_parcours'][$j])."', ";
					$query .= "certificatMedical= '".((isset($_POST['certif_medical']) && $_POST['certif_medical'][$j] == 1)?1:0)."', ";
					$query .= "certificatMedicalObligatoire = '".((isset($_POST['certif_medical_obligatoire']) && $_POST['certif_medical_obligatoire'][$j] == 'oui')?'oui':'non')."', ";
					$query .= "date_max_depose_certif='".(empty($_POST['epre_parc_max_date_certif'][$j])?datefr2en($_POST['epre_parc_date'][$j]):datefr2en($_POST['epre_parc_max_date_certif'][$j]))."', ";		
					$query .= "	autoParentale= '".((isset($_POST['auto_parentale']) && $_POST['auto_parentale'][$j] == 1)?1:0)."', ";
					$query .= " visible_liste_inscrit = '".((isset($_POST['visible_liste_inscrit']) && $_POST['visible_liste_inscrit'][$j] == 'oui')?'oui':'non')."' "; 
					$query .= "WHERE idEpreuveParcours='".$_SESSION['mod_epre_ids_'.$idE][$j][0]."'";
					$infos_complementaires .= $query."-- ***** --\n";
					$result = $mysqli->query($query);

					//*** Fichier des parcours
					$query  = "SELECT nom_fichier_reel,nom_fichier_temp,nom_fichier_affichage,date ";
					$query .= "FROM r_fichier_epreuve_temp ";
					$query .= "WHERE id_session =  '".$id_session."' ";
					$query .= "AND type = 'docs_parcours' ";
					$query .= "AND num_parcours = ".$j;
					$query .= " AND action = 'ins' ";
					//echo "SELECTION DES FICHIERS TEMPORAIRES : ".$query;
					
					$result = $mysqli->query($query);
					while (($row=mysqli_fetch_array($result)) != FALSE)
					{
						if(isset($row['nom_fichier_temp']))
						{
							if(rename($chemin.$rep_fichiers_temp.$row['nom_fichier_temp'], $rep_fichiers_epreuves.$row['nom_fichier_reel']))
							{
								$query_ins  = "INSERT INTO r_epreuvefichier(idEpreuve, idEpreuveParcours, nom_fichier,nom_fichier_affichage,type,date) ";
								$query_ins .= "VALUES(".$idE.",".$id.",'".$row['nom_fichier_reel']."','".$row['nom_fichier_affichage']."','docs_parcours','".$row['date']."')";
								$result_ins = $mysqli->query($query_ins);
								
								$query_del  = "DELETE FROM r_fichier_epreuve_temp ";
								$query_del .= "WHERE id_session =  '".$id_session."' ";
								$query_del .= "AND type = 'docs_parcours' ";
								$query_del .= "AND num_parcours = ".$j." ";
								$query_del .= "AND nom_fichier_temp = '".$row['nom_fichier_temp']."'";
								$result_del = $mysqli->query($query_del);
							}
						}
					}
					
					//suppression des fichiers docs parcours à la demande de l'utilisateur
					$query  = "SELECT nom_fichier_reel ";
					$query .= "FROM r_fichier_epreuve_temp ";
					$query .= "WHERE id_session =  '".$id_session."' ";
					$query .= "AND type = 'docs_parcours' ";
					$query .= "AND num_parcours = ".$id." ";
					$query .= "AND action = 'del' ";
					$result = $mysqli->query($query);
					//echo "suppression des fichiers parcours à la demande de l'utilisateur : ".$query;
					while (($row=mysqli_fetch_array($result)) != FALSE)
					{
						$query_del =  "DELETE FROM r_epreuvefichier ";
						$query_del .= "WHERE nom_fichier = '".$row['nom_fichier_reel']."' ";
						$query_del .= "AND idEpreuve = ".$idE." ";
						$query_del .= "AND type = 'docs_parcours' ";
						$query_del .= "AND idEpreuveParcours = ".$id;
						$result_del = $mysqli->query($query_del);
						//echo "DELETE : ".$query_del;
						if ($result_del  != FALSE)
						{
							unlink("fichiers_epreuves/".$row['nom_fichier_reel']);
						}
					}
					
					//Update / insert Tarifs
					foreach ($_POST['epre_parc_descprix'][$j] as $jj=>$nom_prix) 
					{
						if ($nom_prix != '') {
							
							if (isset($_SESSION['mod_epre_ids_'.$idE][$j][1][$jj])) {
								
								$query  = "UPDATE r_epreuveparcourstarif SET ";
								$query .= "desctarif='".addslashes_form_to_sql($nom_prix)."', ";
								$query .= "tarif='".preg_replace("/,/", ".", $_POST['epre_parc_prix'][$j][$jj])."', ";
								
								$query .= "dateDebutTarif='".datefr2en($_POST['date_debut_tarif'][$j][$jj])."', ";
								$query .= "dateFinTarif='".datefr2en($_POST['date_fin_tarif'][$j][$jj])."', ";
								
								if (!empty($_POST['epre_parc_places_nb_dossard'][$j][$jj])) $epre_parc_places_nb_dossard = intval($_POST['epre_parc_places_nb_dossard'][$j][$jj]) ; else $epre_parc_places_nb_dossard='NULL';
								if (!empty($_POST['epre_parc_prix_nb_dossard'][$j][$jj])) $epre_parc_prix_nb_dossard = preg_replace("/,/", ".", $_POST['epre_parc_prix_nb_dossard'][$j][$jj]) ; else $epre_parc_prix_nb_dossard='NULL';
							
								$query .= "nb_dossard=".$epre_parc_places_nb_dossard.", ";
								$query .= "reduction=".$epre_parc_prix_nb_dossard." ";
								
								$query .= "WHERE idEpreuveParcoursTarif='".$_SESSION['mod_epre_ids_'.$idE][$j][1][$jj]."';";
								
								$result = $mysqli->query($query);
								unset($_SESSION['mod_epre_ids_'.$idE][$j][1][$jj]);
							}
							else
							{
								$query  = "INSERT INTO r_epreuveparcourstarif ";
								$query .= "(idEpreuve, idEpreuveParcours, desctarif, tarif, dateDebutTarif, dateFinTarif, nb_dossard, reduction) VALUES ";
								$query .= "('".$idE."', ";
								$query .= "'".$id."', ";
								$query .= "'".addslashes_form_to_sql($nom_prix)."', ";
								$query .= "'".preg_replace("/,/", ".", $_POST['epre_parc_prix'][$j][$jj])."', ";
								
								$query .= "'".(($_POST['date_debut_tarif'][$j][$jj] == '')?(date("Y")-1).'-01-01 00:00:00':datefr2en($_POST['date_debut_tarif'][$j][$jj]))."', ";
								$query .= "'".(($_POST['date_fin_tarif'][$j][$jj] == '')?implode('-', array_reverse(explode('/', $_POST['epre_inscr_debut'])))." 00:00:00":datefr2en($_POST['date_fin_tarif'][$j][$jj]))."', ";
								
								if (!empty($_POST['epre_parc_places_nb_dossard'][$j][$jj])) $epre_parc_places_nb_dossard = intval($_POST['epre_parc_places_nb_dossard'][$j][$jj]) ; else $epre_parc_places_nb_dossard='NULL';
								if (!empty($_POST['epre_parc_prix_nb_dossard'][$j][$jj])) $epre_parc_prix_nb_dossard = preg_replace("/,/", ".", $_POST['epre_parc_prix_nb_dossard'][$j][$jj]) ; else $epre_parc_prix_nb_dossard='NULL';
							
								$query .= "".$epre_parc_places_nb_dossard.", ";
								$query .= "".$epre_parc_prix_nb_dossard.") ";
								
								$result = $mysqli->query($query);
							}
							
						}
						$infos_complementaires .= $query ."-- ***** --\n";
					}
					
					//Delete tarifs
					foreach ($_SESSION['mod_epre_ids_'.$idE][$j][1] as $key) {
					
							$query  = "DELETE FROM r_epreuveparcourstarif WHERE idEpreuveParcoursTarif=".$key;
							$infos_complementaires .= $query."-- ***** --\n";
							$result = $mysqli->query($query);
							$infos_complementaires .= $query ."-- ***** --\n";
					}
				
				unset($_POST['id_table_parcours'][array_search($_SESSION['mod_epre_ids_'.$idE][$j][0], $_POST['id_table_parcours'])]);
				
				}
				else
				{
									
				$query  = "DELETE FROM r_epreuveparcours ";
				$query .= "WHERE idEpreuveParcours='".$_SESSION['mod_epre_ids_'.$idE][$j][0]."'";
				$result = $mysqli->query($query);

				$query  = "DELETE FROM r_epreuveparcourstarif ";
				$query .= "WHERE idEpreuveParcours='".$_SESSION['mod_epre_ids_'.$idE][$j][0]."'";
				$result = $mysqli->query($query);
				
				$query  = "DELETE FROM r_champssupdotation ";
				$query .= "WHERE idEpreuveParcours='".$_SESSION['mod_epre_ids_'.$idE][$j][0]."'";
				$result = $mysqli->query($query);
				
				$query  = "DELETE FROM r_champssupparticipation ";
				$query .= "WHERE idEpreuveParcours='".$_SESSION['mod_epre_ids_'.$idE][$j][0]."'";
				$result = $mysqli->query($query);
				
				$query  = "DELETE FROM r_champssupquestiondiverse ";
				$query .= "WHERE idEpreuveParcours='".$_SESSION['mod_epre_ids_'.$idE][$j][0]."'";
				$result = $mysqli->query($query);
				
				$query  = "DELETE FROM r_r_epreuveparcourstarifpromo ";
				$query .= "WHERE idEpreuveParcours='".$_SESSION['mod_epre_ids_'.$idE][$j][0]."'";
				$result = $mysqli->query($query);
				
				$query = "SELECT nom_fichier FROM r_epreuvefichier ";
				$query .="WHERE idEpreuveParcours '".$_SESSION['mod_epre_ids_'.$idE][$j][0]."'";
				$result = $mysqli->query($query);
				$row=mysqli_fetch_array($result);
				unlink("fichiers_epreuves/".$row['nom_fichier']);
				
				$query  = "DELETE FROM r_epreuvefichier ";
				$query .= "WHERE idEpreuveParcours='".$_SESSION['mod_epre_ids_'.$idE][$j][0]."'";
				$result = $mysqli->query($query);
				
				$infos_complementaires .= "EFFACEMENT DU PARCOURS : ".$_SESSION['mod_epre_ids_'.$idE][$j][0]."-- ***** --\n";
				unset($_POST['id_table_parcours'][array_search($_SESSION['mod_epre_ids_'.$idE][$j][0], $_POST['id_table_parcours'])]);
				}
			}
			//nouveau parcours
			
			if (count($_POST['id_table_parcours']))
			{
				$j = str_replace("NP", "", $np);

					$query  = "INSERT INTO r_epreuveparcours ";
					$query .= "(idEpreuve, idTypeParcours, nomParcours, nbtarif, horaireDepart, dossardDeb, dossardFin, nbexclusion, dossards_exclus, ordre_affichage, dossard_equipe, relais, min_relais, ageLimite, age, ParcoursDescription, certificatMedical, certificatMedicalObligatoire, date_max_depose_certif, autoParentale, infoParcoursInscription, visible_liste_inscrit) VALUES ";
					$infos_complementaires .= "AJOUT PARCOURS : ".$query ."-- ***** --\n";
					$first = TRUE;
					$base_j=$j;
					foreach ($_POST['id_table_parcours'] as $np)
					{
						$j = str_replace("NP", "", $np);
						$exclus = array();
						
						for ($jj=1; $jj<=$_POST['epre_parc_nbexclusion'][$j]; $jj++)
							$exclus[$j][$jj] = !empty($_POST['parc_dossard_exclus_min'][$j][$jj]) && !empty($_POST['parc_dossard_exclus_max'][$j][$jj])? intval($_POST['parc_dossard_exclus_min'][$j][$jj])."-".intval($_POST['parc_dossard_exclus_max'][$j][$jj]) : "0-0";
						
						$parc_dossardExclus = implode(":",$exclus[$j]);
						//datefr2en(
						$query .= ($first) ? "" : " , ";
						$query .= "('".$idE."', ";
						$query .= "'".intval($_POST['epre_parc_type'][$j])."', ";
						$query .= "'".addslashes_form_to_sql($_POST['epre_parc_nom'][$j])."', ";
						$query .= "'".intval($_POST['epre_parc_nbprix'][$j])."', ";
						$query .= "'".datefr2en($_POST['epre_parc_date'][$j])."', ";
						$query .= intval($_POST['parc_dossard'][$j]).",";
						$query .= intval($_POST['parc_dossardFin'][$j]).",";
						$query .= intval($_POST['epre_parc_nbexclusion'][$j]).",";
						$query .= "'".$parc_dossardExclus."',";
						$query .= intval($_POST['epre_parc_ordre'][$j]).",";
						$query .= "'".(($_POST['dossard_equipe'][$j] == 'oui')?'oui':'non')."', ";
						$query .= ((isset($_POST['relais'][$j]) && $_POST['relais'][$j] == 1)?$_POST['relais_nb_personne'][$j]:0).",";
						$query .= ((isset($_POST['relais'][$j]) && $_POST['relais'][$j] == 1)?$_POST['relais_nb_personne_min'][$j]:0).",";
						$query .= intval($_POST['epre_parc_age_fin'][$j]).",";
						$query .= intval($_POST['epre_parc_age_debut'][$j]).",";
						$query .= "'".addslashes_form_to_sql($_POST['parcours_description'][$j])."',";
						$query .= "'".((isset($_POST['certif_medical'][$j]) && $_POST['certif_medical'][$j] == 1)?1:0)."', ";
						$query .= "'".((isset($_POST['certif_medical_obligatoire'][$j]) && $_POST['certif_medical_obligatoire'][$j] == 'oui')?'oui':'non')."', ";
					    $query .= "'".(empty($_POST['epre_parc_max_date_certif'][$j])?datefr2en($_POST['epre_parc_date'][$j]):datefr2en($_POST['epre_parc_max_date_certif'][$j]))."', ";	
						$query .= "'".((isset($_POST['auto_parentale'][$j]) && $_POST['auto_parentale'][$j] == 1)?1:0)."', ";
						$query .= "'".addslashes_form_to_sql($_POST['info_complementaire_parcours'][$j])."',";
						$query .= "'".((isset($_POST['visible_liste_inscrit'][$j]) && $_POST['visible_liste_inscrit'][$j] == 'oui')?'oui':'non')."') ";
						$first = FALSE;
					}

					$result = $mysqli->query($query);
					array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);

					if ($result != FALSE)
					{
						$id = $mysqli->insert_id;
						$first = TRUE;
						
						$query_tarif  = "INSERT INTO r_epreuveparcourstarif ";
						$query_tarif .= "(idEpreuve, idEpreuveParcours, desctarif, tarif, dateDebutTarif, dateFinTarif,nb_dossard,reduction) VALUES ";
				
						$first_numerotation = TRUE;
						
						foreach ($_POST['id_table_parcours'] as $np)
						{
							$j = str_replace("NP", "", $np);
							
							$query_fichier  = "SELECT nom_fichier_reel,nom_fichier_temp,nom_fichier_affichage,date ";
							$query_fichier .= "FROM r_fichier_epreuve_temp ";
							$query_fichier .= "WHERE id_session =  '".$id_session."' ";
							$query_fichier .= "AND type = 'docs_parcours' ";
							$query_fichier .= "AND num_parcours = ".$j;
							//echo "SELECTION DES FICHIERS TEMPORAIRES : ".$query_fichier;
							$result_fichier = $mysqli->query($query_fichier);
							while (($row=mysqli_fetch_array($result_fichier)) != FALSE)
							{
								if(isset($row['nom_fichier_temp']))
								{
									if(rename($chemin.$rep_fichiers_temp.$row['nom_fichier_temp'], $rep_fichiers_epreuves.$row['nom_fichier_reel']))
									{
										$query_ins  = "INSERT INTO r_epreuvefichier (idEpreuve, idEpreuveParcours, nom_fichier,nom_fichier_affichage,type,date) ";
										$query_ins .= "VALUES(".$idE.",".$id.",'".$row['nom_fichier_reel']."','".$row['nom_fichier_affichage']."','docs_parcours','".$row['date']."')";
										
										$result = $mysqli->query($query_ins);

										$query_del_fichier_temp  = "DELETE FROM r_fichier_epreuve_temp ";
										$query_del_fichier_temp .= "WHERE id_session =  '".$id_session."' ";
										$query_del_fichier_temp .= "AND type = 'docs_parcours' ";
										$query_del_fichier_temp .= "AND num_parcours = ".$j." ";
										$query_del_fichier_temp .= "AND nom_fichier_temp = '".$row['nom_fichier_temp']."'";
										$result_del_fichier_temp = $mysqli->query($query_del_fichier_temp);
									}
								}
							}
							
							for ($jj=1; $jj<=intval($_POST['epre_parc_nbprix'][$j]); $jj++)
							{
								$query_tarif .= ($first) ? "" : " , ";
								$query_tarif .= "('".$idE."', ";
								$query_tarif .= "'".$id."', ";
								$query_tarif .= "'".addslashes_form_to_sql($_POST['epre_parc_descprix'][$j][$jj])."', ";
								$query_tarif .= "'".preg_replace("/,/", ".", $_POST['epre_parc_prix'][$j][$jj])."', ";
								
								$query_tarif .= "'".(($_POST['date_debut_tarif'][$j][$jj] == '')?(date("Y")-1).'-01-01 00:00:00':datefr2en($_POST['date_debut_tarif'][$j][$jj]))."', ";
								$query_tarif .= "'".(($_POST['date_fin_tarif'][$j][$jj] == '')?implode('-', array_reverse(explode('/', $_POST['epre_inscr_debut'])))." 00:00:00":datefr2en($_POST['date_fin_tarif'][$j][$jj]))."', ";
								
								if (!empty($_POST['epre_parc_places_nb_dossard'][$j][$jj])) $epre_parc_places_nb_dossard = intval($_POST['epre_parc_places_nb_dossard'][$j][$jj]) ; else $epre_parc_places_nb_dossard='NULL';
								if (!empty($_POST['epre_parc_prix_nb_dossard'][$j][$jj])) $epre_parc_prix_nb_dossard = preg_replace("/,/", ".", $_POST['epre_parc_prix_nb_dossard'][$j][$jj]) ; else $epre_parc_prix_nb_dossard='NULL';
								
								$query_tarif .= "".$epre_parc_places_nb_dossard .", ";
								$query_tarif .= "".$epre_parc_prix_nb_dossard.") ";
								
								$first = FALSE;
							}

							$id++;
						}

						$result_tarif = $mysqli->query($query_tarif);

						$result_code_promo = $mysqli->query($query_code_promo); 

						array_push($p->query, (($result_code_promo != FALSE)?"ok":"er")." : ".$query_code_promo);
	
						array_push($p->query, (($result_tarif != FALSE)?"ok":"er")." : ".$query_tarif);
					}
				$j++;
			}

		$modif_alert='- modif -';
		// modification
		//récupération du fichier réglement
			
			$query_fichier_temp  = "SELECT nom_fichier_reel,nom_fichier_temp,nom_fichier_affichage,date ";
			$query_fichier_temp .= "FROM r_fichier_epreuve_temp ";
			$query_fichier_temp .= "WHERE id_session =  '".$id_session."' ";
			$query_fichier_temp .= "AND type = 'docs_reglement' ";
			$query_fichier_temp .= "AND action = 'ins' ";
			$result_fichier_temp = $mysqli->query($query_fichier_temp);
			$row_fichier_temp=mysqli_fetch_array($result_fichier_temp);			
			
			if(isset($row_fichier_temp['nom_fichier_temp']))
			{
				$query_del =  "DELETE FROM r_epreuvefichier ";
				$query_del .= "WHERE idEpreuve = ".$idE." ";
				$query_del .= "AND type = 'docs_reglement' ";
				$result_del = $mysqli->query($query_del);
				
				if(rename($chemin.$rep_fichiers_temp.$row_fichier_temp['nom_fichier_temp'], $rep_fichiers_epreuves.$row_fichier_temp['nom_fichier_reel']))
				{
					$query  = "INSERT INTO r_epreuvefichier(idEpreuve, idEpreuveParcours, nom_fichier,nom_fichier_affichage,type,date) ";
					$query .= "VALUES(".$idE.",0,'".$row_fichier_temp['nom_fichier_reel']."','".$row_fichier_temp['nom_fichier_affichage']."','docs_reglement','".$row_fichier_temp['date']."')";

					$result = $mysqli->query($query );
					
					$id_docs_reglement= $mysqli->insert_id;
					
					$query  = "DELETE FROM r_fichier_epreuve_temp ";
					$query .= "WHERE id_session =  '".$id_session."' ";
					$query .= "AND type = 'docs_reglement' ";
					$result = $mysqli->query($query);
					
				}	
				$reglement_a_inserer  = "3|||".$id_docs_reglement;
			}	
			elseif ($_POST['choix_reglement_course'] == 'url' && empty($row_fichier_temp['nom_fichier_temp'])) {

				$reglement_a_inserer  = "1|||".$_POST['epre_reglement_url'];
						
						$query_del =  "DELETE FROM r_epreuvefichier ";
						$query_del .= "WHERE idEpreuve = ".$idE." ";
						$query_del .= "AND type = 'docs_reglement' ";
						$result_del = $mysqli->query($query_del);
			}
			elseif ($_POST['choix_reglement_course'] == 'texte' && empty($row_fichier_temp['nom_fichier_temp'])) {
			
				$reglement_a_inserer  = "2|||".$_POST['epre_reglement_texte'];
						
						$query_del =  "DELETE FROM r_epreuvefichier ";
						$query_del .= "WHERE idEpreuve = ".$idE;
						$query_del .= " AND type = 'docs_reglement' ";
						$result_del = $mysqli->query($query_del);
				
			}else 
			{
				$query  = "SELECT reglement FROM r_epreuve ";
				$query .= "WHERE idEpreuve = ".$idE." ";
				$result = $mysqli->query($query);
				$row=mysqli_fetch_array($result);
				$reglement_a_inserer  = $row['reglement'];
			}
			
			//récupération du fichier auto_parentale
			
			$query_fichier_temp  = "SELECT action,nom_fichier_reel,nom_fichier_temp,nom_fichier_affichage,date ";
			$query_fichier_temp .= "FROM r_fichier_epreuve_temp ";
			$query_fichier_temp .= "WHERE id_session =  '".$id_session."' ";
			$query_fichier_temp .= "AND type = 'docs_parentale' ";
			$result_fichier_temp = $mysqli->query($query_fichier_temp);
			$row_fichier_temp=mysqli_fetch_array($result_fichier_temp);			
			
			if(isset($row_fichier_temp['nom_fichier_temp']) && $row_fichier_temp['action']=='ins')
			{
				$query_del =  "DELETE FROM r_epreuvefichier ";
				$query_del .= "WHERE idEpreuve = ".$idE." ";
				$query_del .= "AND type = 'docs_parentale' ";
				$result_del = $mysqli->query($query_del);
				
				if(rename($chemin.$rep_fichiers_temp.$row_fichier_temp['nom_fichier_temp'], $rep_fichiers_epreuves.$row_fichier_temp['nom_fichier_reel']))
				{
					$query  = "INSERT INTO r_epreuvefichier(idEpreuve, idEpreuveParcours, nom_fichier,nom_fichier_affichage,type,date) ";
					$query .= "VALUES(".$idE.",0,'".$row_fichier_temp['nom_fichier_reel']."','".$row_fichier_temp['nom_fichier_affichage']."','docs_parentale','".$row_fichier_temp['date']."')";
					$result = $mysqli->query($query );
					
					$id_docs_autoparentale= $mysqli->insert_id;
					
					$query  = "DELETE FROM r_fichier_epreuve_temp ";
					$query .= "WHERE id_session =  '".$id_session."' ";
					$query .= "AND type = 'docs_parentale' ";
					$result = $mysqli->query($query);
					
				}	
				$autoparentale_a_inserer  = $id_docs_autoparentale;
			}
			elseif(isset($row_fichier_temp['nom_fichier_temp']) && $row_fichier_temp['action']=='del')
			{
						$query_del =  "DELETE FROM r_epreuvefichier ";
						$query_del .= "WHERE idEpreuve = ".$idE." ";
						$query_del .= "AND type = 'docs_parentale' ";
						$result_del = $mysqli->query($query_del);
						$autoparentale_a_inserer  = '';
			}
			else
			{
				$query  = "SELECT fichier_auto_parentale FROM r_epreuve ";
				$query .= "WHERE idEpreuve = ".$idE." ";
				$result = $mysqli->query($query);
				$row=mysqli_fetch_array($result);
				$autoparentale_a_inserer  = $row['fichier_auto_parentale'];
			}
			
			//récupération du fichier image de fond
			
			$query_fichier_temp  = "SELECT nom_fichier_reel,nom_fichier_temp,nom_fichier_affichage,date,action ";
			$query_fichier_temp .= "FROM r_fichier_epreuve_temp ";
			$query_fichier_temp .= "WHERE id_session =  '".$id_session."' ";
			$query_fichier_temp .= "AND type = 'photo_insc_fond' ";
			$result_fichier_temp = $mysqli->query($query_fichier_temp);
			$row_fichier_temp=mysqli_fetch_array($result_fichier_temp);			
			
			if(isset($row_fichier_temp['nom_fichier_temp']) && $row_fichier_temp['action']=='ins')
			{
				$query_del =  "DELETE FROM r_epreuvefichier ";
				$query_del .= "WHERE idEpreuve = ".$idE." ";
				$query_del .= "AND type = 'photo_insc_fond' ";
				$result_del = $mysqli->query($query_del);				
				
				if(rename($chemin.$rep_fichiers_temp.$row_fichier_temp['nom_fichier_temp'], $rep_fichiers_epreuves.$row_fichier_temp['nom_fichier_reel']))
				{
					$query  = "INSERT INTO r_epreuvefichier(idEpreuve, idEpreuveParcours, nom_fichier,nom_fichier_affichage,type,date) ";
					$query .= "VALUES(".$idE.",0,'".$row_fichier_temp['nom_fichier_reel']."','".$row_fichier_temp['nom_fichier_affichage']."','photo_insc_fond','".$row_fichier_temp['date']."')";
					$result = $mysqli->query($query);
					
					$id_docs_image_fond_epreuve = $mysqli->insert_id;
					
					$query  = "DELETE FROM r_fichier_epreuve_temp ";
					$query .= "WHERE id_session =  '".$id_session."' ";
					$query .= "AND type = 'photo_insc_fond' ";
					$result = $mysqli->query($query);
					
				}	
				$image_fond_epreuve_a_inserer = $id_docs_image_fond_epreuve;
			}
			elseif(isset($row_fichier_temp['nom_fichier_temp']) && $row_fichier_temp['action']=='del')  
			{
						$query_del =  "DELETE FROM r_epreuvefichier ";
						$query_del .= "WHERE idEpreuve = ".$idE." ";
						$query_del .= "AND type = 'photo_insc_fond' ";
						$result_del = $mysqli->query($query_del);
						$image_fond_epreuve_a_inserer  = '';						
			}
			else
			{
				$query  = "SELECT image_fond FROM r_epreuveperso ";
				$query .= "WHERE idEpreuve = ".$idE." ";
				$result = $mysqli->query($query);
				$row=mysqli_fetch_array($result);
				$image_fond_epreuve_a_inserer  = $row['image_fond'];
			}
		
			$query = "SELECT idEpreuvePerso FROM r_epreuveperso WHERE idEpreuve = ".$idE;
			$result = $mysqli->query($query);
			$row=mysqli_fetch_array($result);
			
			if ($row != FALSE) {
				
				$query  = "UPDATE r_epreuveperso SET ";
				$query .= "image_fond='".$image_fond_epreuve_a_inserer."', ";
				$query .= "panel_color='".$_POST['epreuve_panel_couleur']."' ";
				$query .= "WHERE idEpreuve='".$idE."';";
				$result = $mysqli->query($query);
			}
			else
			{
				$query  = "INSERT INTO r_epreuveperso(idEpreuve, panel_color, image_fond) ";
				$query .= "VALUES(".$idE.",'".$_POST['epreuve_panel_couleur']."','".$image_fond_epreuve_a_inserer."')";
				$result = $mysqli->query($query);
			}
			//panel de fond
			
		$query  = "UPDATE r_epreuve SET ";
		$query .= "idTypeEpreuve='".$_POST['epre_type']."', ";
		if(isset($_SESSION['log_root']) && isset($_POST['idorga']) && $_POST['idorga'] != '') $query .= "idInternaute=".$_POST['idorga'].", ";
		$query .= "nomEpreuve='".addslashes_form_to_sql($_POST['epre_nom'])."', ";
		$query .= "dateEpreuve='".datefr2en($_POST['epre_date'],1)."', ";
		$query .= "dateFinEpreuve='".datefr2en($_POST['epre_date_fin'],1)."', ";
		$query .= "nombreParcours='".intval($_POST['epre_nbparc'])."', ";
		$query .= "idInternaute=".intval($_POST['epre_organisateur']).", ";
		$query .= "departement='".$_POST['epre_departement']."', ";
		$query .= "nbParticipantsAttendus='".addslashes_form_to_sql($_POST['epre_nbparticipant'])."', ";
		$query .= "nomStructureLegale='".addslashes_form_to_sql($_POST['epre_structurelegale'])."', ";
		$query .= "siteInternet='".addslashes_form_to_sql($_POST['epre_siteinternet'])."', ";
		$query .= "siteFacebook='".addslashes_form_to_sql($_POST['epre_sitefacebook'])."', ";
		$query .= "siteTwitter='".addslashes_form_to_sql($_POST['epre_sitetwitter'])."', ";
		$query .= "contactInscription='".addslashes_form_to_sql($_POST['epre_inscr_contact'])."', ";
		$query .= "telInscription='".addslashes_form_to_sql($_POST['epre_inscr_tel'])."', ";
		$query .= "emailInscription='".addslashes_form_to_sql($_POST['epre_inscr_email'])."', ";
		$query .= "emailInscription_recevoir='".((isset($_POST['epre_inscr_email_recevoir']) && $_POST['epre_inscr_email_recevoir'] == 1)?1:0)."', ";
		$query .= "dateDebutInscription='".datefr2en($_POST['epre_inscr_debut'],1)."', ";
		$query .= "dateFinInscription='".datefr2en($_POST['epre_inscr_fin'])."', ";
		$query .= "description='".addslashes_form_to_sql($_POST['epre_description'])."', ";
		$query .= "informations_epreuve='".addslashes_form_to_sql($_POST['epre_informations_epreuve'])."', ";
		$query .= "reglement='".addslashes_form_to_sql($reglement_a_inserer)."', ";
		$query .= "fichier_auto_parentale='".$autoparentale_a_inserer."', ";
		$query .= "ville='".addslashes_form_to_sql($_POST['epre_ville'])."', ";
		$query .= "pays='".addslashes_form_to_sql($_POST['epre_pays'])."', ";
		$query .= "sitelieu='".addslashes_form_to_sql($_POST['epre_siteetlieu'])."', ";
		$query .= "urlImage='".(($nomfichier!="")?$nomfichier:$_POST['epre_photo_'])."', ";
		$query .= "paiement_cb='".((isset($_POST['utilisation_service_inscription']) && $_POST['utilisation_service_inscription'] == 1)?1:0)."', ";
		$query .= "paiement_cheque='".((isset($_POST['epre_paiementcheque']) == 1)?1:0)."', ";
		$query .= "coordonnees_paiement_cheque='".addslashes_form_to_sql($_POST['epre_coordonnees_cheque'])."', ";
		$query .= "infos_paiement_cheque_groupe='".addslashes_form_to_sql($_POST['epre_info_paiement_cheque_groupe'])."', ";
		$query .= "infos_paiement_IBAN_groupe='".addslashes_form_to_sql($_POST['epre_info_paiement_iban_groupe'])."', ";
		$query .= "payeur='".addslashes_form_to_sql($_POST['payeur'])."', ";
			if(is_numeric($_POST['epre_payeur_cout_cb']))
			{
				$query .= "cout_paiement_cb = '".$_POST['epre_payeur_cout_cb']."', ";
			}
			else
			{
				$query .= "cout_paiement_cb = '0', ";
			}
		$query .= "liste_engage_ctrl='".((isset($_POST['epre_liste_engage_ctrl']) == 1)?1:0)."', ";
		$query .= "visible_calendrier='".((isset($_POST['epre_visible_calendrier']) == 1)?1:0)."', ";
		$query .= "nsi='".((isset($_POST['epre_nsi']) == 1)?"non":"oui")."', ";
		$query .= "devisChrono='".((isset($_POST['epre_devischrono']) && $_POST['epre_devischrono'] == 1)?1:0)."', ";
		//FFA
		if ($_POST['epre_webservice_ffa'] == 'oui') {
			
			if ($_POST['epre_CMPCOD_FFA'] !='') $query .= "CMPCOD_FFA= '".addslashes_form_to_sql($_POST['epre_CMPCOD_FFA'])."', "; else $query .= "CMPCOD_FFA = null ,";
		}
		else
		{
			$query .= "CMPCOD_FFA = null ,";
		}
		//FFA
		//FFTRI
		if ($_POST['epre_webservice_fftri'] == 'oui') {
			
			$query .= "webservice_FFTRI= 'oui', "; 
		}
		else
		{
			$query .= "webservice_FFTRI = 'non' ,";
		}
		//FFTRI
		//Atlas
		if ($_POST['epre_webservice_Atlas'] == 'oui') {
			
			$query .= "webService_Atlas= 'oui' , ";
			if ($_POST['epre_race_ID']!=''){
			$query .= "IDAtlas='".addslashes_form_to_sql($_POST['epre_race_ID'])."',";
		}
		}
		else
		{
			$query .= " webService_Atlas= 'non' ,";
			$query .= "IDAtlas= null ,";
		}
		//Atlas
		$query .= " cat_annee= '".addslashes_form_to_sql($_POST['epre_cat_annee'])."', ";
		$query .= " insc_dossard_dernier= '".addslashes_form_to_sql($_POST['epre_insc_dossard_dernier'])."', ";
		$query .= " insc_aff_place_restante= '".addslashes_form_to_sql($_POST['epre_insc_place_restante'])."', ";
		$query .= " periode_reversement_inscriptions= '".addslashes_form_to_sql($_POST['periode_reversement_inscriptions'])."', ";
		$query .= " chrono_ats_sport='".((isset($_POST['chrono_ats_sport']) && $_POST['chrono_ats_sport'] == 1)?1:0)."', ";
		$query .= "dateModification='".date("Y-m-d")."' ";
		if(!isset($_SESSION['log_root']) && (isset($_POST['utilisation_service_inscription']) && $_POST['utilisation_service_inscription'] == 1))
			$query .= ",validation3='1' ";
		
		$query .= "WHERE idEpreuve='".$idE."';";
		//CMPCOD_FFA
		$infos_complementaires .= $query ."-- ***** --\n";
		//echo $query;
		$result = $mysqli->query($query);
		
			if ($result != FALSE)
			{
				$p->centre  = "<BR>\n";
				$p->centre .= "<BR>\n";
				$p->centre .= "<CENTER>\n";
				$p->centre .= " <p class='txtLibre' style='text-align:center;'>Votre épreuve a été modifiée<p>\n";
				$p->centre .= " <a href='index.php'> accueil </a>";
				$p->centre .= "</CENTER>\n";
				
		//==========================================Mise à jour des envois de mail organisateur==============================================================
		//===================================================================================================================================================
		if(isset($_SESSION['log_log']) && !isset($_SESSION['log_root']))
			{
			$query  = "INSERT INTO b_historique_mail_orga_epreuve ";
			$query .= "(idEpreuve, Date_envoi, Type_mail, Observation)  ";
			$query .= " VALUES ( '".$idE."', ";
			$query .= "'".date("Y-m-d")."', ";
			$query .= "'MAJ Orga', ";
			$query .= "'Mise à jour niveau des paramètres avancés') ";

			$result = $mysqli->query($query);
		
			}
			}
			
		unset($_SESSION['mod_epre_id_'.$idE]);
		unset($_SESSION['mod_epre_ids_'.$idE]);

		//suppression des fichiers téléchargés temporaires restants :
		$query_del  = "DELETE FROM r_fichier_epreuve_temp ";
		$query_del .= "WHERE id_session =  '".$id_session."' ";
		$result_del = $mysqli->query($query_del);

		histo_back_end($idE, 0, 0,0,'FICHE_EPREUVE_MODIFIEE',$infos_complementaires);

		/*
		=========================================================================
			24/01/2019 enregistrement des données de l'onglet correspondance
		=========================================================================
		*/

		$mysqli->query("DELETE FROM r_epreuvecorrespondance WHERE idEpreuve=".$idE);
		foreach( $_POST['epre_email_resultats'] as $kk => $mm )
		{
			if( !empty( $mm ) )
			{
				$correspondance  = "INSERT INTO r_epreuvecorrespondance(idEpreuve, email, public, typeEnvoi) VALUES(";
				$correspondance .= $idE.", ";
				$correspondance .= "'".$mm."', ";
				$correspondance .= "'".$_POST['epre_email_resultats_public'][$kk]."', ";	
				$correspondance .= "'".$_POST['epre_email_resultats_type_envoi'][$kk]."')";
				
				$mysqli->query($correspondance);
			}
		}
	}
	else //Mode normal insertion d'une nouvelle épreuve
	{
					    
		$idadmin = 0;
		if((isset($_SESSION['log_root']) || isset($_SESSION['typeInternaute']) && $_SESSION['typeInternaute'] == 'adminepreuve') && !isset($_POST['idorga']))
		{
			//Enregistrement de l'organisateur

			$query  = "INSERT INTO r_internaute ";
			$query .= "(loginInternaute, passInternaute, validation, dateInscription, nomInternaute, ";
			$query .= "prenomInternaute, sexeInternaute, naissanceInternaute, emailInternaute,  ";
			$query .= "typeInternaute, coureur, organisateur, telephone) VALUES (";
			$query .= "'".addslashes_form_to_sql($_POST['newloginorga'])."', ";
			$query .= "'".addslashes_form_to_sql($_POST['newmdporga'])."', ";
			$query .= "'oui', ";
			$query .= "'".date("Y-m-d H:i:s")."', ";
			$query .= "'".addslashes_form_to_sql($_POST['newnomorga'])."', ";
			$query .= "'".addslashes_form_to_sql($_POST['newprenomorga'])."', ";
			$query .= "'".addslashes_form_to_sql($_POST['newsexeorga'])."', ";
			$query .= "'1980-01-01', ";
			$query .= "'".addslashes_form_to_sql($_POST['newemailorga'])."', ";
			$query .= "'organisateur', ";
			$query .= "'oui', ";
			$query .= "'oui', ";
			$query .= "'".addslashes_form_to_sql($_POST['newtelorga'])."');";
			$result_query = $mysqli->query($query);
			if (!$result_query) {$error++;}
			$idorga = $mysqli->insert_id;
			
			//Enregistrement de l'administrateur
			$query  = "INSERT INTO r_internaute ";
			$query .= "(loginInternaute, passInternaute, validation, dateInscription, nomInternaute, ";
			$query .= "prenomInternaute, sexeInternaute, naissanceInternaute, emailInternaute,  ";
			$query .= "typeInternaute, coureur, organisateur, telephone) VALUES (";
			$query .= "'".addslashes_form_to_sql($_POST['newloginadmin'])."', ";
			$query .= "'".addslashes_form_to_sql($_POST['newmdpadmin'])."', ";
			$query .= "'oui', ";
			$query .= "'".date("Y-m-d H:i:s")."', ";
			$query .= "'".addslashes_form_to_sql($_POST['newnomadmin'])."', ";
			$query .= "'".addslashes_form_to_sql($_POST['newprenomadmin'])."', ";
			$query .= "'".addslashes_form_to_sql($_POST['newsexeadmin'])."', ";
			$query .= "'1980-01-01', ";
			$query .= "'".addslashes_form_to_sql($_POST['newemailadmin'])."', ";
			$query .= "'adminepreuve', ";
			$query .= "'oui', ";
			$query .= "'oui', ";
			$query .= "'".addslashes_form_to_sql($_POST['newteladmin'])."');";
			$result_query = $mysqli->query($query);
			if (!$result_query) {$error++;}
			$idadmin = $mysqli->insert_id;
		}
	
		$reglement_a_inserer = '';
		$autoparentale_a_inserer = '';
		// insertion de l'épreuve
		$query  = "INSERT INTO r_epreuve ";
		$query .= "(idTypeEpreuve, nomEpreuve, dateEpreuve, dateFinEpreuve, nombreParcours, departement, ";
		$query .= "idInternaute, valide, nbParticipantsAttendus, nomStructureLegale, siteInternet, siteFacebook, siteTwitter, ";
		$query .= "contactInscription, telInscription, emailInscription, emailInscription_recevoir, dateDebutInscription, ";
		$query .= "dateFinInscription, description, informations_epreuve, reglement, fichier_auto_parentale, ville, pays, sitelieu,  ";
		$query .= "referencer, urlImage, paiement_cb, paiement_cheque, coordonnees_paiement_cheque, infos_paiement_cheque_groupe, infos_paiement_IBAN_groupe, payeur, cout_paiement_cb, liste_engage_ctrl, visible_calendrier, nsi, devisChrono, dateInscription, administrateur, super_organisateur,CMPCOD_FFA, webservice_FFTRI, cat_annee,insc_dossard_dernier,insc_aff_place_restante, periode_reversement_inscriptions,chrono_ats_sport,webService_Atlas,IDAtlas) VALUES (";
		$query .= "'".$_POST['epre_type']."', ";
		$query .= "'".addslashes_form_to_sql($_POST['epre_nom'])."', ";
		$query .= "'".implode('-', array_reverse(explode('/', $_POST['epre_date'])))."', ";
		$query .= "'".implode('-', array_reverse(explode('/', $_POST['epre_date_fin'])))."', ";
		$query .= "'".intval($_POST['epre_nbparc'])."', ";
		$query .= "'".$_POST['epre_departement']."', ";

		$query .= "'".intval($_POST['epre_organisateur'])."', ";
		if(isset($_SESSION['log_root']) || (isset($_SESSION['typeInternaute']) && $_SESSION['typeInternaute'] == 'adminepreuve')) $query .= "'oui', ";
		else $query .= "'oui', ";
		$query .= "'".addslashes_form_to_sql($_POST['epre_nbparticipant'])."', ";
		$query .= "'".addslashes_form_to_sql($_POST['epre_structurelegale'])."', ";
		$query .= "'".addslashes_form_to_sql($_POST['epre_siteinternet'])."', ";
		$query .= "'".addslashes_form_to_sql($_POST['epre_sitefacebook'])."', ";
		$query .= "'".addslashes_form_to_sql($_POST['epre_sitetwitter'])."', ";
		$query .= "'".addslashes_form_to_sql($_POST['epre_inscr_contact'])."', ";
		$query .= "'".addslashes_form_to_sql($_POST['epre_inscr_tel'])."', ";
		$query .= "'".addslashes_form_to_sql($_POST['epre_inscr_email'])."', ";
		$query .= "'".(isset($_POST['epre_inscr_email_recevoir'])?1:0)."', ";
		$query .= "'".datefr2en($_POST['epre_inscr_debut'],1)."', ";
		$query .= "'".datefr2en($_POST['epre_inscr_fin'])."', ";		  
		$query .= "'".addslashes_form_to_sql($_POST['epre_description'])."', ";
		$query .= "'".addslashes_form_to_sql($_POST['epre_informations_epreuve'])."', ";
		$query .= "'".addslashes_form_to_sql($reglement_a_inserer)."', ";
		$query .= "'".$autoparentale_a_inserer."', ";
		$query .= "'".addslashes_form_to_sql($_POST['epre_ville'])."', ";
		$query .= "'".addslashes_form_to_sql($_POST['epre_pays'])."', ";
		$query .= "'".addslashes_form_to_sql($_POST['epre_siteetlieu'])."', ";
		$query .= "'".(isset($_POST['epre_reference']) ?"oui":"non")."', ";
		$query .= "'".(($nomfichier!="")?$nomfichier:$_POST['epre_photo_'])."', ";
		$query .= "'".((isset($_POST['utilisation_service_inscription']) && $_POST['utilisation_service_inscription'] == 1 && !isset($_GET['action']))?1:0)."', ";
		$query .= "'".((isset($_POST['epre_paiementcheque']) == 1)?1:0)."', ";
		$query .= "'".addslashes_form_to_sql($_POST['epre_coordonnees_cheque'])."', ";
		$query .= "'".addslashes_form_to_sql($_POST['epre_info_paiement_cheque_groupe'])."', ";
		$query .= "'".addslashes_form_to_sql($_POST['epre_info_paiement_iban_groupe'])."', ";
		$query .= "'".addslashes_form_to_sql($_POST['payeur'])."', ";

		if(is_numeric($_POST['epre_payeur_cout_cb']))
		{
			$query .= "'".$_POST['epre_payeur_cout_cb']."', ";
		}
		else
		{
			$query .= "'0', ";
		}
				
		$query .= "'".((isset($_POST['epre_liste_engage_ctrl']) == 1)?1:0)."', ";
		$query .= "'".((isset($_POST['epre_visible_calendrier']) == 1)?1:0)."', ";
		$query .= "'".((isset($_POST['epre_nsi']) == 1)?"non":"oui")."', ";
		$query .= "'".((isset($_POST['epre_devischrono']) && $_POST['epre_devischrono'] == 1)?1:0)."', ";
		$query .= "'".date("Y-m-d H:i:s")."', ";		
		if(isset($_SESSION['log_root']) && !isset($_POST['idadmin'])) $query .= $idadmin.",";
		else if(isset($_SESSION['log_root']) && isset($_POST['idadmin'])) $query .= $_POST['idadmin'].",";
		else $query .= "185009,";
		if ($_SESSION['typeInternaute']=="super_organisateur") $query .= $_SESSION['log_id'].","; else $query .= "'NULL',";
		//FFA
		if ($_POST['epre_webservice_ffa'] == 'oui')
		{
			if ($_POST['epre_CMPCOD_FFA'] !='') $query .= "'".addslashes_form_to_sql($_POST['epre_CMPCOD_FFA'])."', "; else $query .= "null ,";
		}
		else
		{
			$query .= "null ,";
		}
		//FFA
		//FFTRI
		if ($_POST['epre_webservice_fftri'] == 'oui') {
			
			$query .= " 'oui', "; 
		}
		else
		{
			$query .= " 'non' ,";
		}
		//FFTRI
		$query .= "'".addslashes_form_to_sql($_POST['epre_cat_annee'])."', ";
		$query .= "'".addslashes_form_to_sql($_POST['epre_insc_dossard_dernier'])."', ";
		$query .= "'".addslashes_form_to_sql($_POST['epre_insc_place_restante'])."', ";
		$query .= "'".addslashes_form_to_sql($_POST['periode_reversement_inscriptions'])."', ";
		$query .= "'".(isset($_POST['chrono_ats_sport']) && $_POST['chrono_ats_sport'] == 1?1:0)."', ";
		if ($_POST['epre_webservice_Atlas'] == 'oui') {
			
			$query .= " 'oui', ";
			$query .= "'".addslashes_form_to_sql($_POST['epre_race_ID'])."')"; 
		}
		else
		{
			$query .= " 'non' ,";
			$query .= " null )";
		}
		//echo $query;
		$result = $mysqli->query($query);
		if (!$result) {$error++;}
		
		array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
		if ($result != FALSE)
		{
			$idE = $mysqli->insert_id;

			//generation du code séparateur pour les inscriptions.
							
				$separator_fonction = code_separator(5);
				$separator_champ = code_separator(5);
				$separator_parcours = code_separator(5);
				
				$query_separator  = "INSERT INTO r_insc_champ_separator ";
				$query_separator .= "(idEpreuve, value_fonction, value_champ, value_parcours) VALUES (";
				$query_separator .= "".$idE.", ";
				$query_separator .= " '".$separator_fonction."', ";
				$query_separator .= "'".$separator_champ."', ";
				$query_separator .= "'".$separator_parcours."') ";
				///cho $query_separator;
				$result_query_separator = $mysqli->query($query_separator);
			
			//récupération du fichier réglement
			
			if ($_POST['choix_reglement_course'] == 'url') {

				$reglement_a_inserer  = "1|||".$_POST['epre_reglement_url'];
			}
			elseif ($_POST['choix_reglement_course'] == 'texte') {
			
				$reglement_a_inserer  = "2|||".$_POST['epre_reglement_texte'];
				
			}else {
				//**** doc pdf du reglement
				$query  = "SELECT nom_fichier_reel,nom_fichier_temp,nom_fichier_affichage,date ";
				$query .= "FROM r_fichier_epreuve_temp ";
				$query .= "WHERE id_session =  '".$id_session."' ";
				$query .= "AND type = 'docs_reglement' ";
				//echo "xxxxx : ".$query;
				$result = $mysqli->query($query);
				$row=mysqli_fetch_array($result);
	
				if(isset($row['nom_fichier_temp']))
				{
					if(rename($chemin.$rep_fichiers_temp.$row['nom_fichier_temp'], $rep_fichiers_epreuves.$row['nom_fichier_reel']))
					{
						$query  = "INSERT INTO r_epreuvefichier(idEpreuve, idEpreuveParcours, nom_fichier,nom_fichier_affichage,type,date) ";
						$query .= "VALUES(".$idE.",0,'".$row['nom_fichier_reel']."','".$row['nom_fichier_affichage']."','docs_reglement','".$row['date']."')";
						$result = $mysqli->query($query );
						
						$id_docs_reglement= $mysqli->insert_id;
						
						$query  = "DELETE FROM r_fichier_epreuve_temp ";
						$query .= "WHERE id_session =  '".$id_session."' ";
						$query .= "AND type = 'docs_reglement' ";
						$query .= "AND nom_fichier_temp = '".$row['nom_fichier_temp']."' ";
						$result = $mysqli->query($query);
					}
				}
				
				$reglement_a_inserer  = "3|||".$id_docs_reglement;
			}
			
			//update du champt reglement
			$query  = "UPDATE r_epreuve SET ";
			$query .= "reglement='".addslashes_form_to_sql($reglement_a_inserer)."' ";
			$query .= "WHERE idEpreuve = ".$idE;
			$result = $mysqli->query($query);
			
			//**** doc pdf autorisation parentale
				$query  = "SELECT nom_fichier_reel,nom_fichier_temp,nom_fichier_affichage,date ";
				$query .= "FROM r_fichier_epreuve_temp ";
				$query .= "WHERE id_session =  '".$id_session."' ";
				$query .= "AND type = 'docs_parentale' ";
				$result = $mysqli->query($query);
				$row=mysqli_fetch_array($result);
	
				if(isset($row['nom_fichier_temp']))
				{
					if(rename($chemin.$rep_fichiers_temp.$row['nom_fichier_temp'], $rep_fichiers_epreuves.$row['nom_fichier_reel']))
					{
						$query  = "INSERT INTO r_epreuvefichier(idEpreuve, idEpreuveParcours, nom_fichier,nom_fichier_affichage,type,date) ";
						$query .= "VALUES(".$idE.",0,'".$row['nom_fichier_reel']."','".$row['nom_fichier_affichage']."','docs_parentale','".$row['date']."')";
						$result = $mysqli->query($query );
						
						$id_docs_autoparentale= $mysqli->insert_id;
						
						$query  = "DELETE FROM r_fichier_epreuve_temp ";
						$query .= "WHERE id_session =  '".$id_session."' ";
						$query .= "AND type = 'docs_parentale' ";
						$query .= "AND nom_fichier_temp = '".$row['nom_fichier_temp']."' ";
						$result = $mysqli->query($query);
					}
				}
				
				$autoparentale_a_inserer  = $id_docs_autoparentale;

			//panel de fond

			$query  = "INSERT INTO r_epreuveperso(idEpreuve, panel_color) ";
			$query .= "VALUES(".$idE.",'".$_POST['epreuve_panel_couleur']."')";
			$result = $mysqli->query($query);
			
			//**** image_fond_epreuve
				$query  = "SELECT nom_fichier_reel,nom_fichier_temp,nom_fichier_affichage,date ";
				$query .= "FROM r_fichier_epreuve_temp ";
				$query .= "WHERE id_session =  '".$id_session."' ";
				$query .= "AND type = 'photo_insc_fond' ";
				$result = $mysqli->query($query);
				$row=mysqli_fetch_array($result);
	
				if(isset($row['nom_fichier_temp']))
				{
					if(rename($chemin.$rep_fichiers_temp.$row['nom_fichier_temp'], $rep_fichiers_epreuves.$row['nom_fichier_reel']))
					{
						$query  = "INSERT INTO r_epreuvefichier(idEpreuve, idEpreuveParcours, nom_fichier,nom_fichier_affichage,type,date) ";
						$query .= "VALUES(".$idE.",0,'".$row['nom_fichier_reel']."','".$row['nom_fichier_affichage']."','photo_insc_fond','".$row['date']."')";
						$result = $mysqli->query($query );
						
						$id_docs_epreuve_image_fond= $mysqli->insert_id;
						
						$query  = "DELETE FROM r_fichier_epreuve_temp ";
						$query .= "WHERE id_session =  '".$id_session."' ";
						$query .= "AND type = 'photo_insc_fond' ";
						$query .= "AND nom_fichier_temp = '".$row['nom_fichier_temp']."' ";
						$result = $mysqli->query($query);
					}
				}
				
				$epreuve_image_fond_a_inserer  = $id_docs_epreuve_image_fond;
				
				
			//update de l'image
			$query  = "UPDATE r_epreuveperso SET ";
			$query .= "image_fond='".$image_fond_epreuve_a_inserer."' ";
			$query .= "WHERE idEpreuve='".$idE."';";
			$result = $mysqli->query($query);
			
			//récupération des fichiers de l'épreuve

			//**** photo de l'épreuve
			$query  = "SELECT nom_fichier_reel,nom_fichier_temp,nom_fichier_affichage,date ";
			$query .= "FROM r_fichier_epreuve_temp ";
			$query .= "WHERE id_session =  '".$id_session."' ";
			$query .= "AND type = 'photo_epreuve' ";
			$result = $mysqli->query($query);
			$row=mysqli_fetch_array($result);

			if(isset($row['nom_fichier_temp']))
			{
				if(rename($chemin.$rep_fichiers_temp.$row['nom_fichier_temp'], $rep_fichiers_epreuves.$row['nom_fichier_reel']))
				{
					$query  = "INSERT INTO r_epreuvefichier(idEpreuve, idEpreuveParcours, nom_fichier,nom_fichier_affichage,type,date) ";
					$query .= "VALUES(".$idE.",0,'".$row['nom_fichier_reel']."','".$row['nom_fichier_affichage']."','photo_epreuve','".$row['date']."')";
					$result = $mysqli->query($query );
					
					$query  = "DELETE FROM r_fichier_epreuve_temp ";
					$query .= "WHERE id_session =  '".$id_session."' ";
					$query .= "AND type = 'photo_epreuve' ";
					$query .= "AND nom_fichier_temp = '".$row['nom_fichier_temp']."' ";
					$result = $mysqli->query($query);
					
				}
			}
			
			//*** Fichier de l'épreuve
			$query  = "SELECT nom_fichier_reel,nom_fichier_temp,nom_fichier_affichage,date ";
			$query .= "FROM r_fichier_epreuve_temp ";
			$query .= "WHERE id_session =  '".$id_session."' ";
			$query .= "AND type = 'docs_epreuve' ";
			$result = $mysqli->query($query);
			while (($row=mysqli_fetch_array($result)) != FALSE)
			{
				if(isset($row['nom_fichier_temp']))
				{
					if(rename($chemin.$rep_fichiers_temp.$row['nom_fichier_temp'], $rep_fichiers_epreuves.$row['nom_fichier_reel']))
					{
						$query_ins  = "INSERT INTO r_epreuvefichier(idEpreuve, idEpreuveParcours, nom_fichier,nom_fichier_affichage,type,date) ";
						$query_ins .= "VALUES(".$idE.",0,'".$row['nom_fichier_reel']."','".$row['nom_fichier_affichage']."','docs_epreuve','".$row['date']."')";
						$result_ins = $mysqli->query($query_ins);
						
						$query_del  = "DELETE FROM r_fichier_epreuve_temp ";
						$query_del .= "WHERE id_session =  '".$id_session."' ";
						$query_del .= "AND type = 'docs_epreuve' ";
						$query_del .= "AND nom_fichier_temp = '".$row['nom_fichier_temp']."' ";
						$result_del = $mysqli->query($query_del);
					}
				}
			}

			if(isset($_SESSION['log_root']) || (isset($_SESSION['typeInternaute']) && $_SESSION['typeInternaute'] == 'adminepreuve'))
			{
				$q  = "INSERT INTO r_inscriptionepreuve(idEpreuve, valide, envoiMail) ";
				$q .= "VALUES('".$idE."','oui','oui')";
				$result = $mysqli->query($q);
				if (!$result) {$error++;}
			}
			
			$query  = "INSERT INTO r_epreuveparcours ";
			$query .= "(idEpreuve, idTypeParcours, nomParcours, nbtarif, horaireDepart, dossardDeb, dossardFin, nbexclusion, dossards_exclus, dossard_equipe, ordre_affichage, relais, min_relais, ageLimite, age, ParcoursDescription, certificatMedical, certificatMedicalObligatoire, date_max_depose_certif, autoParentale, infoParcoursInscription, visible_liste_inscrit) VALUES ";
			$first = TRUE;
			
			foreach ($_POST['epre_parc_nom'] as $j=>$nom) {

				$exclus = array();
				for ($jj=1; $jj<=$_POST['epre_parc_nbexclusion'][$j]; $jj++)
				{	
					$exclus[$j][$jj] = !empty($_POST['parc_dossard_exclus_min'][$j][$jj]) && !empty($_POST['parc_dossard_exclus_max'][$j][$jj])? intval($_POST['parc_dossard_exclus_min'][$j][$jj])."-".intval($_POST['parc_dossard_exclus_max'][$j][$jj]) : "0-0";
				}
				$parc_dossardExclus = implode(":",$exclus[$j]);

				$query .= ($first) ? "" : " , ";
				$query .= "('".$idE."', ";
				$query .= "'".intval($_POST['epre_parc_type'][$j])."', ";
				$query .= "'".addslashes_form_to_sql($nom)."', ";
				$query .= "'".intval($_POST['epre_parc_nbprix'][$j])."', ";
				//JEFF
				
				$query .= "'".datefr2en($_POST['epre_parc_date'][$j])."', ";
				//JEFF
				$query .= intval($_POST['parc_dossard'][$j]).",";
				$query .= intval($_POST['parc_dossardFin'][$j]).",";
				$query .= intval($_POST['epre_parc_nbexclusion'][$j]).",";
				$query .= "'".$parc_dossardExclus."',";
				$query .= "'".(($_POST['dossard_equipe'][$j] == 'oui')?'oui':'non')."', ";
				$query .= intval($_POST['epre_parc_ordre'][$j]).",";
				$query .= ((isset($_POST['relais'][$j]) && $_POST['relais'][$j] == 1)?$_POST['relais_nb_personne'][$j]:0).",";
				$query .= ((isset($_POST['relais'][$j]) && $_POST['relais'][$j] == 1)?$_POST['relais_nb_personne_min'][$j]:0).",";
				$query .= intval($_POST['epre_parc_age_fin'][$j]).",";
				$query .= intval($_POST['epre_parc_age_debut'][$j]).",";
				$query .= "'".addslashes_form_to_sql($_POST['parcours_description'][$j])."',";
				
				$query .= "'".((isset($_POST['certif_medical'][$j]) && $_POST['certif_medical'][$j] == 1)?1:0)."', ";
				$query .= "'".((isset($_POST['certif_medical_obligatoire'][$j]) && $_POST['certif_medical_obligatoire'][$j] == 'oui')?'oui':'non')."', ";
				$query .= "'".(empty($_POST['epre_parc_max_date_certif'][$j])?datefr2en($_POST['epre_parc_date'][$j]):datefr2en($_POST['epre_parc_max_date_certif'][$j]))."', ";	
				$query .= "'".((isset($_POST['auto_parentale'][$j]) && $_POST['auto_parentale'][$j] == 1)?1:0)."', ";
				$query .= "'".addslashes_form_to_sql($_POST['info_complementaire_parcours'][$j])."',";
				$query .= "'".((isset($_POST['visible_liste_inscrit'][$j]) && $_POST['visible_liste_inscrit'][$j] == 'oui')?'oui':'non')."') ";
				$first = FALSE;
			}
			
			$result = $mysqli->query($query);
			if (!$result) {$error++;}
			array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);

			if ($result != FALSE)
			{
				$query  = "SELECT idEpreuveParcours ";
				$query .= "FROM r_epreuveparcours ";
				$query .= "WHERE idEpreuve='".$idE."' ";
				$query .= "ORDER BY idEpreuveParcours;";
								
				$result = $mysqli->query($query);
				array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);

				$id = $mysqli->insert_id;
				if (($row=mysqli_fetch_array($result)) != FALSE)
					$id=$row['idEpreuveParcours'];

				$first = $first_dotation = $first_participation = $first_participation_commune = $first_questiondiverse = $first_numerotation = TRUE;

				$query_tarif  = "INSERT INTO r_epreuveparcourstarif ";
				$query_tarif .= "(idEpreuve, idEpreuveParcours, desctarif, tarif, dateDebutTarif, dateFinTarif, nb_dossard, reduction) VALUES ";
				
				$query_code_promo  = "INSERT INTO r_epreuveparcourstarifpromo ";
				$query_code_promo .= "(idEpreuve, idEpreuveParcours, nom, label, numerotation, bon_dispo,nb_fois_utilisable, dateDebutTarifPromo, dateFinTarifPromo, prix_reduction) VALUES ";
				
				foreach ($_POST['epre_parc_nom'] as $j=>$nom) {
					
					//*** Fichier des parcours
					$query_fichier  = "SELECT nom_fichier_reel,nom_fichier_temp,nom_fichier_affichage,date ";
					$query_fichier .= "FROM r_fichier_epreuve_temp ";
					$query_fichier .= "WHERE id_session =  '".$id_session."' ";
					$query_fichier .= "AND type = 'docs_parcours' ";
					$query_fichier .= "AND num_parcours = ".$j;
					$result_fichier = $mysqli->query($query_fichier);
					while (($row=mysqli_fetch_array($result_fichier)) != FALSE)
					{
						if(isset($row['nom_fichier_temp']))
						{
							if(rename($chemin.$rep_fichiers_temp.$row['nom_fichier_temp'], $rep_fichiers_epreuves.$row['nom_fichier_reel']))
							{
								$query_ins  = "INSERT INTO r_epreuvefichier(idEpreuve, idEpreuveParcours, nom_fichier,nom_fichier_affichage,type,date) ";
								$query_ins .= "VALUES(".$idE.",".$id.",'".$row['nom_fichier_reel']."','".$row['nom_fichier_affichage']."','docs_parcours','".$row['date']."')";

								$result_ins = $mysqli->query($query_ins);
								
								$query_del  = "DELETE FROM r_fichier_epreuve_temp ";
								$query_del .= "WHERE id_session =  '".$id_session."' ";
								$query_del .= "AND type = 'docs_parcours' ";
								$query_del .= "AND num_parcours = ".$j." ";
								$query_del .= "AND nom_fichier_temp = '".$row['nom_fichier_temp']."'";

								$result_del = $mysqli->query($query_del);
							}
						}
					}
					
					if ($_POST['code_promo_nom'][$j] != '') 
					{	
						if ($_POST['code_promo_label'][$j] == '')
						{
							$_POST['code_promo_label'][$j] = nom_code_promo(5);
						}
						
						if ((intval($_POST['code_promo_numero_depart'][$j]) == 0 && intval($_POST['code_promo_numero_fin'][$j]) == 0))
						{
							$query_code_promo .= ($first_numerotation) ? "" : " , ";
							$query_code_promo .= "('".$idE."', ";
							$query_code_promo .= "'".$id."', ";
							$query_code_promo .= "'".addslashes_form_to_sql($_POST['code_promo_nom'][$j])."', ";
							$query_code_promo .= "'".addslashes_form_to_sql($_POST['code_promo_label'][$j])."', ";
							$query_code_promo .= "'".addslashes_form_to_sql(intval($_POST['code_promo_numero_depart'][$j]).",".intval($_POST['code_promo_numero_fin'][$j]))."', ";
							$query_code_promo .= "'".intval($_POST['code_promo_fois_utilisable'][$j])."', ";
							$query_code_promo .= "'".intval($_POST['code_promo_fois_utilisable'][$j])."', ";
							
							$query_code_promo .= "'".(($_POST['code_promo_debut_date'][$j] == '')?(date("Y")-1).'-01-01 00:00:00':datefr2en($_POST['code_promo_debut_date'][$j]))."', ";
							$query_code_promo .= "'".(($_POST['code_promo_fin_date'][$j] == '')?(date("Y")-1).'-01-01 00:00:00':datefr2en($_POST['code_promo_fin_date'][$j]))."', ";
							
							$query_code_promo .= "'".preg_replace("/,/", ".", $_POST['code_promo_prix_reduction'][$j])."')";
							$first_numerotation = FALSE;
						}
						else
						{
								$query_code_promo .= ($first_numerotation) ? "" : " , ";
								$query_code_promo .= "('".$idE."', ";
								$query_code_promo .= "'".$id."', ";
								$query_code_promo .= "'".addslashes_form_to_sql($_POST['code_promo_nom'][$j])."', ";
								$query_code_promo .= "'".addslashes_form_to_sql($_POST['code_promo_label'][$j])."', ";
								$query_code_promo .= "'".addslashes_form_to_sql(intval($_POST['code_promo_numero_depart'][$j]).",".intval($_POST['code_promo_numero_fin'][$j]))."', ";
								$query_code_promo .="'";
								$first_num = TRUE;
								
								for ($cpt=intval($_POST['code_promo_numero_depart'][$j]);$cpt<=intval($_POST['code_promo_numero_fin'][$j]);$cpt++) 
								{
									$query_code_promo .= ($first_num) ? "" : "|";
									
									$query_code_promo .="".addslashes_form_to_sql(generer_code_promo(5))."";
									
									$first_num = FALSE;
								}
								$query_code_promo .= "', ";
								$query_code_promo .= "'".intval($_POST['code_promo_fois_utilisable'][$j])."', ";
								
								$query_code_promo .= "'".(($_POST['code_promo_debut_date'][$j] == '')?(date("Y")-1).'-01-01 00:00:00':datefr2en($_POST['code_promo_debut_date'][$j]))."', ";
								$query_code_promo .= "'".(($_POST['code_promo_fin_date'][$j] == '')?(date("Y")-1).'-01-01 00:00:00':datefr2en($_POST['code_promo_fin_date'][$j]))."', ";
																
								$query_code_promo .= "'".preg_replace("/,/", ".", $_POST['code_promo_prix_reduction'][$j])."')";
								$first_numerotation = FALSE;
						}
					}
				
				if ($first_numerotation == FALSE) 
				{ 
					$result = $mysqli->query($query_code_promo); 
				}
		
					for ($jj=1; $jj<=intval($_POST['epre_parc_nbprix'][$j]); $jj++)
					{
						if ($_POST['epre_parc_descprix'][$j][$jj] !='')
						{	
							$query_tarif .= ($first) ? "" : " , ";
							$query_tarif .= "('".$idE."', ";
							$query_tarif .= "'".$id."', ";
							$query_tarif .= "'".addslashes_form_to_sql($_POST['epre_parc_descprix'][$j][$jj])."', ";
							$query_tarif .= "'".preg_replace("/,/", ".", $_POST['epre_parc_prix'][$j][$jj])."', ";
							//JEFF
							
							$query_tarif .= "'".(($_POST['date_debut_tarif'][$j][$jj] == '')?(date("Y")-1).'-01-01 00:00:00':datefr2en($_POST['date_debut_tarif'][$j][$jj]))."', ";
							$query_tarif .= "'".(($_POST['date_fin_tarif'][$j][$jj] == '')?implode('-', array_reverse(explode('/', $_POST['epre_inscr_debut'])))." 00:00:00":datefr2en($_POST['date_fin_tarif'][$j][$jj]))."', ";
							if (!empty($_POST['epre_parc_places_nb_dossard'][$j][$jj])) $epre_parc_places_nb_dossard = intval($_POST['epre_parc_places_nb_dossard'][$j][$jj]) ; else $epre_parc_places_nb_dossard='NULL';
							if (!empty($_POST['epre_parc_prix_nb_dossard'][$j][$jj])) $epre_parc_prix_nb_dossard = preg_replace("/,/", ".", $_POST['epre_parc_prix_nb_dossard'][$j][$jj]) ; else $epre_parc_prix_nb_dossard='NULL';
							
							$query_tarif .= "".$epre_parc_places_nb_dossard.", ";
							$query_tarif .= "".$epre_parc_prix_nb_dossard.") ";
							
							//JEFF
							$first = FALSE;
						}
					}

					$id++;
				}
				
				$result = $mysqli->query($query_tarif);
				array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
				$result_dotation = $mysqli->query($query_dotation);
				$result_participation = $mysqli->query($query_participation);
				$result_questiondiverse = $mysqli->query($query_questiondiverse);
				
				if ($result != FALSE)
				{
					$p->centre  = "<BR>\n";
					$p->centre .= "<BR>\n";
					$p->centre .= "<CENTER>\n";
					if(isset($_SESSION['log_root']) || (isset($_SESSION['typeInternaute']) && $_SESSION['typeInternaute'] == 'adminepreuve'))
						$p->centre .= " <h5>L'épreuve a bien été ajoutée !</h5>\n";
					else
						$p->centre .= " <h5>Votre épreuve va être ajoutée après validation du webmaster</h5>\n";
					$p->centre .= " <a href='index.php'> accueil </a>";
					$p->centre .= "</CENTER>\n";
				}

				//suppression des fichiers téléchargés temporaires restants :
				$query_del  = "DELETE FROM r_fichier_epreuve_temp ";
				$query_del .= "WHERE id_session =  '".$id_session."' ";
				$result_del = $mysqli->query($query_del);;
				clearFolder($id_session);
			}

			/*
			=========================================================================
				24/01/2019 enregistrement des données de l'onglet correspondance
			=========================================================================
			*/

			foreach( $_POST['epre_email_resultats'] as $kk => $mm )
			{
				if( !empty( $mm ) )
				{
					$correspondance  = "INSERT INTO r_epreuvecorrespondance(idEpreuve, email, public, typeEnvoi) VALUES(";
					$correspondance .= $idE.", ";
					$correspondance .= "'".$mm."', ";
					$correspondance .= "'".$_POST['epre_email_resultats_public'][$kk]."', ";	
					$correspondance .= "'".$_POST['epre_email_resultats_type_envoi'][$kk]."')";
				
					$mysqli->query($correspondance);
				}
			}		
		}
	}
	
	//=======================================================================Envoi du mail et traitement via function_mail===============================================================
	//==================================================================================================================================================================================
	
	if(isset($_POST['epre_mail_orga']) && $_POST['epre_mail_orga']!='')
	{
	send_mail_organisateur($idE, $_POST['epre_mail_orga'], $_POST['epre_observation_orga'], $_POST['mail_sup'], $_POST['epre_text_orga']);		
	}
	
	$query  = " SELECT e.idInternaute";
	$query .= " FROM  r_epreuve as e";
	$query .= " WHERE e.idEpreuve='".$idE."' ";
	$result=$mysqli->query($query);
	
	$idorga= mysql_result($result , 0, 0);
		
	$p->centre = "<BR><BR><CENTER> L'enregistrement est en cours... <br/><br/> <img src='images/tempo.gif'></CENTER>\n";

	header('Refresh: 3;URL=index.php?file=epre&epre_id='.$idE.'&orga_id='.$idorga.'&epre_button=1');

	if ($p->centre == "")
		$p->centre = "<BR><BR><CENTER> erreur </CENTER>\n";
	
	if ($_POST['idEpreuve'] != '') $num_epreuve_id = $_POST['idEpreuve']; else $num_epreuve_id = $idE;
	
	if ($error !=0) { echo "error"; } else {echo $num_epreuve_id; }
?>