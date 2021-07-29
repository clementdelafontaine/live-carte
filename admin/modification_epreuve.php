<?php

require_once("includes/includes.php");
global $mysqli;
if ($_SESSION["typeInternaute"] == 'admin' || $_SESSION["typeInternaute"] == 'super_organisateur') $admin = 1;
$site = '/temp/';
$url_fichier = $site . 'admin/fichiers_epreuves/';
ini_set("display_errors", 0);
error_reporting(E_ALL);

function extract_organisateurs()
{
	global $mysqli;
	$query = "SELECT DISTINCT(ri.idInternaute), loginInternaute, nomInternaute, prenomInternaute FROM `r_internaute` as ri
	WHERE typeInternaute = 'organisateur' 
	AND nomInternaute != '' AND prenomInternaute != '' AND dateInscription > 2015-01-01 ORDER BY nomInternaute ASC";
	$result = $mysqli->query($query);
	return $result;
}

function tronque_texte($chaine, $max)
{

	global $mysqli;
	if (strlen($chaine) >= $max) {
		$chaine = substr($chaine, 0, $max);
		$espace = strrpos($chaine, " ");
		$chaine = substr($chaine, 0, $espace) . "...";
	}

	return $chaine;
}

function checkdir_temp_file($id_session)
{

	global $mysqli;

	$cpt = 0;
	$dir = "dl/";


	if (is_dir($dir)) {


		// si il contient quelque chose
		if ($dh = opendir($dir)) {

			// boucler tant que quelque chose est trouve
			while (($file = readdir($dh)) !== false) {

				// affiche le nom et le type si ce n'est pas un element du systeme

				if ($file != '.' && $file != '..' && preg_match('#\.(png|jpg|gif|pdf)$#i', $file)) {

					$filename = $type_filename . "-" . $_SESSION['unique_id_session'];
				}

				if (preg_match('#' . $filename . '#', $file)) {

					unlink($dir . $file);
				}
			}

			closedir($dh);
		}
	}
}

checkdir_temp_file($_SESSION['unique_id_session']);

function dateen2fr($mydate, $wtime = 0)
{

	global $mysqli;
	if ($wtime == 0) {
		@list($date, $horaire) = explode(' ', $mydate);
		@list($annee, $mois, $jour) = explode('-', $date);
		@list($heure, $minute, $seconde) = explode(':', $horaire);
		return @date('d/m/Y H:i', strtotime($mois . "/" . $jour . "/" . $annee . " " . $heure . ":" . $minute));
	} else {
		@list($annee, $mois, $jour) = explode('-', $mydate);
		return @date('d/m/Y', strtotime($mois . "/" . $jour . "/" . $annee));
	}
}

function select_pays_internaute($pays_internaute)
{

	global $mysqli;
	//echo 		$pays_internaute;		
	$query_pays  = "SELECT nom_fr_fr as nom_pays FROM `pays` ORDER by nom_fr_fr ASC ";

	$result_pays = $mysqli->query($query_pays);
	$aff = '';

	while ($row_pays = mysqli_fetch_array($result_pays)) {

		$aff .= '<option value="' . $row_pays['nom_pays'] . '" ';
		if ($pays_internaute == $row_pays['nom_pays']) {
			$aff .= "selected";
		}
		//elseif ($row_pays['nom_pays']=='France') { $aff .= "selected"; }
		$aff .= " >" . $row_pays['nom_pays'] . "</option>";
	}

	//echo $aff;
	return $aff;
}

//**** check si participants déja inscrits ****//
function select_participant_tarif($idEpreuveParcoursTarif)
{
	global $mysqli;
	$champs = array();

	$query = "SELECT count(idInscriptionEpreuveInternaute) FROM r_inscriptionepreuveinternaute WHERE idEpreuveParcoursTarif = " . $idEpreuveParcoursTarif . " AND paiement_type IN ('GRATUIT','AUTRE','CB','CHQ') ";
	$result = $mysqli->query($query);
	$row = mysqli_fetch_array($result);
	if (!empty($row[0])) return $row[0];
	else return 0;
}

function tarif_reduc_place($id_tarif)
{
	global $mysqli;
	$tar = array();

	$query_nb = "SELECT count(idInscriptionEpreuveInternaute) as nb_deja_pris FROM r_inscriptionepreuveinternaute WHERE  idEpreuveParcoursTarif = " . $id_tarif . " AND place_promo = 1";
	$result_nb = $mysqli->query($query_nb);
	$row_nb = mysqli_fetch_array($result_nb);

	$query  = "SELECT reduction,nb_dossard, nb_dossard_pris,tarif, reduction ";
	$query .= "FROM r_epreuveparcourstarif";
	$query .= " WHERE idEpreuveParcoursTarif = " . $id_tarif;
	$result_tarifs = $mysqli->query($query);
	$q1 = mysqli_fetch_array($result_tarifs);
	if ($q1['nb_dossard'] - $row_nb['nb_deja_pris'] <= 0) $q1['reduction'] = 0;
	$tar['tarif'] = $q1['tarif'] - $q1['reduction'];
	$tar['reduction'] = $q1['reduction'];
	$tar['nb_deja_pris'] = $row_nb['nb_deja_pris'];

	return $tar;
}

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="fr">
<!--<![endif]-->

<head>
	<meta charset="utf-8" />
	<title>Ats Sport | Édition des parcours</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<meta content="" name="description" />
	<meta content="" name="author" />

	<?php echo "</BR>header_css";
	require_once("includes/header_css_js_base.php"); ?>

	<!-- ================== BEGIN PAGE LEVEL STYLE ================== -->
	<link href="assets/plugins/bootstrap-datepicker/css/datepicker.css" rel="stylesheet" />
	<link href="assets/plugins/bootstrap-datepicker/css/datepicker3.css" rel="stylesheet" />
	<link href="assets/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" />

	<link href="assets/plugins/switchery/switchery.min.css" rel="stylesheet" />
	<link href="assets/plugins/powerange/powerange.min.css" rel="stylesheet" />

	<link href="assets/plugins/bootstrap-wizard/css/bwizard.min.css" rel="stylesheet" />
	<link href="assets/plugins/parsley/src/parsley.css" rel="stylesheet" />

	<link href="assets/plugins/ionicons/css/ionicons.min.css" rel="stylesheet" />

	<link href="assets/plugins/bootstrap-wysihtml5/src/bootstrap-wysihtml5.css" rel="stylesheet" />

	<link href="assets/plugins/parsley/src/parsley.css" rel="stylesheet" />

	<link href="assets/plugins/ionRangeSlider/css/ion.rangeSlider.css" rel="stylesheet" />
	<link href="assets/plugins/ionRangeSlider/css/ion.rangeSlider.skinNice.css" rel="stylesheet" />
	<link href="assets/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" />
	<link href="assets/plugins/bootstrap-fileinput/css/fileinput.css" media="all" rel="stylesheet" type="text/css" />


	<!-- ================== END PAGE LEVEL STYLE ================== -->
	<script src="assets/js/form-plugins.demo.js"></script>
	<link href="assets/plugins/datetimepicker-master/jquery.datetimepicker.css" rel="stylesheet" />

</head>

<body>
	<!-- begin #page-loader 
	<div id="page-loader" class="fade in"><span class="spinner"></span></div> -->
	<div></div>
	<!-- begin #page-container 
	<div id="page-container" class="fade page-sidebar-fixed page-header-fixed gradient-enabled"> -->
	<div>
		<!-- begin #header -->
		<?php include("includes/header.php"); ?>
		<!-- end #header -->
		<!-- begin #sidebar (menu) -->
		<?php include("includes/sidebar.php"); ?>
		<!-- end #sidebar (menu) -->

		<!-- begin #content -->
		<div id="content" class="content">
			<!-- begin breadcrumb -->
			<ol class="breadcrumb pull-right">
				<li><a href="javascript:;">Accueil</a></li>
				<li class="active">Édition des parcours</li>
			</ol>
			<!-- end breadcrumb -->
			<!-- begin page-header -->
			<h1 class="page-header">Édition des parcours</h1>
			<!-- end page-header -->
			<?php

			global $_POST;
			global $parametre;

			//purge des fichiers temporaires restants
			$query_del  = "DELETE FROM r_fichier_epreuve_temp ";
			$query_del .= "WHERE id_session =  '" . $_SESSION['unique_id_session'] . "' ";
			$result_del = $mysqli->query($query_del);


			$query = "SELECT val FROM r_constant WHERE cle like 'conditionInscription'";
			$result = $mysqli->query($query);
			$condition = mysqli_fetch_array($result);

			$f = new stdClass();
			$f->typeepreuve = array();
			$query  = "SELECT idTypeEpreuve, nomTypeEpreuve ";
			$query .= "FROM r_typeepreuve;";
			$result = $mysqli->query($query);
			array_push($p->query, (($result != FALSE) ? "ok" : "er") . " : " . $query);
			while (($row = mysqli_fetch_array($result)) != FALSE)
				$f->typeepreuve[$row['idTypeEpreuve']] = $row['nomTypeEpreuve'];

			$f->typeparcours = array();
			$query  = "SELECT idTypeParcours, nomTypeParcours, idTypeEpreuve ";
			$query .= "FROM r_typeparcours ORDER BY idTypeEpreuve,idTypeParcours;";
			$result = $mysqli->query($query);
			array_push($p->query, (($result != FALSE) ? "ok" : "er") . " : " . $query);
			while (($row = mysqli_fetch_array($result)) != FALSE) {
				$f->typeparcours[$row['idTypeParcours']][0] = $row['nomTypeParcours'];
				$f->typeparcours[$row['idTypeParcours']][1] = $row['idTypeEpreuve'];
			}


			$f->nom = "";
			$f->epre_date = date("d/m/Y");
			$f->epre_date_fin = date("d/m/Y");
			$f->nbparc = 1;
			$f->selected_typeepreuve = -1;
			$f->payeur = "";
			$f->epre_payeur_cout_cb = 0;
			$j = 1;
			$f->parc_nom[$j] = "";
			$f->parc_dossard[$j] = 0;
			$f->parc_dossardFin[$j] = 0;
			$f->parc_dossardExclus[$j] = '';
			$f->parc_ordre[$j] = 0;
			$f->relais[$j] = 0;
			$f->relais_min[$j] = 0;
			$f->limit_age[$j] = 0;
			$f->parc_age_debut[$j] = 18;
			$f->parc_age_fin[$j] = 120;
			$f->parc_type[$j] = -1;
			$f->parc_date[$j] = date("d/m/Y H:m");
			$f->parc_heure[$j] = 0;
			$f->parc_min[$j] = 0;
			$f->parc_nbprix[$j] = 1;
			$f->parc_nbexclusion[$j] = 1;
			$jj = 1;
			$f->date_debut_tarif[$j][$jj] = "";

			$f->departement = "";
			$f->nbparticipant = "";
			$f->structurelegale = "";
			$f->siteinternet = "";
			$f->siteFacebook = "";
			$f->siteTwitter = "";

			$f->ville = "";
			$f->pays = "France";
			$f->siteetlieu = "";
			$f->nomorga = "";
			$f->prenomorga = "";
			$f->telorga = "";
			$f->emailorga = "";
			$f->newloginorga = "";
			$f->newmdporga = "";
			$f->newnomorga = "";
			$f->newprenomorga = "";
			$f->newsexeorga = "";
			$f->newemailorga = "";
			$f->newtelorga = "";
			$f->reference = "";
			$f->urlImage = "";
			$f->newnomadmin = "";
			$f->newprenomadmin = "";
			$f->newloginadmin = "";
			$f->newmdpadmin = "";
			$f->newsexeadmin = "";
			$f->newemailadmin = "";
			$f->newteladmin = "";
			$f->nomadmin = "";
			$f->prenomadmin = "";
			$f->teladmin = "";
			$f->emailadmin = "";
			$f->epre_CMPCOD_FFA = "";
			$f->epre_cat_annee = "";
			$f->periode_reversement_inscriptions = "";
			$f->chrono_ats_sport = 0;
			//JEFF

			$f->idEpreuve = '';
			$f->repertoire_docs = 'fichiers_epreuves/';

			//Clément
			//Query points d'intérêt carte
			if (isset($_GET['epre_id'])) {
				$epre_id = $_GET['epre_id'];
				$query = "SELECT * FROM c_carto_points_interet";
				$query .= " ORDER BY id_epreuve, id_parcours";
				$result = $mysqli->query($query);
				array_push($p->query, (($result != FALSE) ? "ok" : "er") . " : " . $query);
				while (($row = mysqli_fetch_array($result)) != FALSE) {
					$infos_points[$row['id_epreuve']][$row['id_parcours']][$row['id']] =
						array(
							'id' => $row['id'],
							'categorie' => $row['categorie'],
							'id_lecteur' => $row['id_lecteur'],
							'popupContent' => $row['popupContent'],
							'x' => $row['x'],
							'y' => $row['y'],
							'z' => $row['z']
						);
				}

				$query = "SELECT * FROM c_gpx";
				$query .= " ORDER BY id_epreuve, id_parcours";
				$result = $mysqli->query($query);
				array_push($p->query, (($result != FALSE) ? "ok" : "er") . " : " . $query);
				while (($row = mysqli_fetch_array($result)) != FALSE) {
					$infos_trace[$row['id_epreuve']][$row['id_parcours']] =
						array(
							'couleur' => $row['couleur'],
							'distance' => $row['distance']
						);
				}
				var_dump($infos_trace['7232']['17568']);
			}

			//JEFF
			//MODIFICATION
			if (isset($_POST['epre_id']) || isset($_GET['epre_id'])) {
				if (isset($_POST['epre_id'])) {
					$epre_id = isset($_POST['epre_id']) ? $_POST['epre_id'] : "";
					$query  = "SELECT e.idTypeEpreuve, e.nomEpreuve, e.dateEpreuve, e.nombreParcours, e.departement, ";
					$query .= "e.idInternaute, e.nbParticipantsAttendus, e.nomStructureLegale, e.siteInternet, e.siteFacebook, e.siteTwitter,";
					$query .= "e.contactInscription, e.telInscription, e.emailInscription, e.dateDebutInscription, ";
					$query .= "e.dateFinInscription, e.description, e.reglement, e.ville, e.pays, e.sitelieu, ";
					$query .= "e.referencer, e.urlImage, e.dateInscription, e.paiement_cb, e.payeur, e.cout_paiement_cb, e.devisChrono, e.administrateur, e.CMPCOD_FFA , e.webservice_FFTRI, cat_annee, e.insc_dossard_dernier, e.insc_aff_place_restante, e.periode_reversement_inscriptions, e.chrono_ats_sport, ";
					$query .= "i.loginInternaute, i.passInternaute, i.nomInternaute, i.prenomInternaute, i.telephone, i.emailInternaute ";
					$query .= "FROM r_epreuve as e JOIN r_internaute as i ON e.idInternaute = i.idInternaute ";
					$query .= "WHERE idEpreuve='" . $epre_id . "';";
				} else if (isset($_GET['epre_id'])) {

					$epre_id = isset($_GET['epre_id']) ? $_GET['epre_id'] : "";
					$query  = "SELECT e.idTypeEpreuve, e.nomEpreuve, e.dateEpreuve, e.dateFinEpreuve, e.nombreParcours, e.departement, ";
					$query .= "e.idInternaute,e.dateFinInscription, e.description, e.informations_epreuve, e.reglement, e.ville, e.pays, e.sitelieu, ";
					$query .= "i.loginInternaute, i.passInternaute, i.nomInternaute, i.prenomInternaute, i.telephone, i.emailInternaute ";
					$query .= "FROM r_epreuve as e JOIN r_internaute as i ON e.idInternaute = i.idInternaute ";

					if ($_SESSION['typeInternaute'] == 'admin') {
						$query .= "WHERE e.idEpreuve='" . $epre_id . "' AND e.administrateur=" . $_SESSION['log_id'] . ";";
					} elseif ($_SESSION["typeInternaute"] == 'super_organisateur') {
						$query .= "WHERE e.idEpreuve='" . $epre_id . "' AND e.super_organisateur=" . $_SESSION['log_id'] . ";";
					} else $query .= "WHERE e.idEpreuve='" . $epre_id . "' AND e.idInternaute=" . $_SESSION['log_id'] . ";";
				}

				$result = $mysqli->query($query);
				array_push($p->query, (($result != FALSE) ? "ok" : "er") . " : " . $query);

				if (($row = mysqli_fetch_array($result)) != FALSE) {
					$f->idEpreuve = $epre_id;

					//recup docs Epreuve				
					$dateEpreuve = explode("-", $row["dateEpreuve"]);
					$dateDebut = explode("-", $row["dateDebutInscription"]);
					$dateFinDateTime = explode(" ", $row["dateFinInscription"]);
					$timeFin = explode(":", $dateFinDateTime[1]);

					$f->nom						= sql_to_form($row['nomEpreuve']);
					$f->epre_date				= sql_to_form(dateen2fr($row['dateEpreuve'], 1));
					$f->epre_date_fin			= sql_to_form(dateen2fr($row['dateFinEpreuve'], 1));
					$f->nbparc					= $row['nombreParcours'];
					$f->epre_organisateur		= $row['idInternaute'];
					$f->selected_typeepreuve	= $row['idTypeEpreuve'];
					$f->description				= sql_to_form($row['description']);
					$f->informations_epreuve	= sql_to_form($row['informations_epreuve']);
					$f->ville					= sql_to_form($row['ville']);
					$f->pays					= sql_to_form($row['pays']);
					$f->siteetlieu				= sql_to_form($row['sitelieu']);
					$f->reference				= $row['referencer'];
					$f->urlImage				= $row['urlImage'];
					$f->loginorga				= $row['loginInternaute'];
					$f->passorga				= $row['passInternaute'];
					$f->nomorga					= $row['nomInternaute'];
					$f->prenomorga				= $row['prenomInternaute'];
					$f->telorga					= $row['telephone'];
					$f->emailorga				= $row['emailInternaute'];


					//JEFF Docs_Parcours	
					$tab_docs_parcours = array();
					$query  = "SELECT nom_fichier, 	nom_fichier_affichage, idEpreuveParcours, date, type, idEpreuveFichier  ";
					$query .= "FROM r_epreuvefichier ";
					$query .= "WHERE idEpreuve='" . $epre_id . "' ";
					$query .= "AND type= 'docs_parcours' ";
					$query .= "ORDER BY idEpreuveFichier;";
					$result = $mysqli->query($query);
					array_push($p->query, (($result != FALSE) ? "ok" : "er") . " : " . $query);
					while (($row = mysqli_fetch_array($result)) != FALSE) {
						$tab_docs_parcours[$row['idEpreuveParcours']][$row['idEpreuveFichier']] = array(sql_to_form($row['nom_fichier']), $row['date'], $row['type'], sql_to_form($row['nom_fichier_affichage']));
					}

					$query  = "SELECT idEpreuveParcours, idTypeParcours, nomParcours, nbtarif, horaireDepart, dossardDeb, dossardFin, nbexclusion, dossards_exclus, ordre_affichage, relais, min_relais, ageLimite, age, ParcoursDescription, certificatMedical, dossard_equipe, certificatMedicalObligatoire, date_max_depose_certif, autoParentale, infoParcoursInscription, visible_liste_inscrit, distance ";
					$query .= "FROM r_epreuveparcours ";
					$query .= "WHERE idEpreuve='" . $epre_id . "' ";
					$query .= "ORDER BY idEpreuveParcours;";

					$result = $mysqli->query($query);
					array_push($p->query, (($result != FALSE) ? "ok" : "er") . " : " . $query);
					$nb_parcours =  mysqli_num_rows($result);

					$j = 0;
					while (($row = mysqli_fetch_array($result)) != FALSE) {
						$j++;
						$tab_id[$j][0] 				= $row['idEpreuveParcours'];
						$tab_distance_parcours[$j][0] 				= $row['distance'];
						$tab_code_promo_id[$j][0]   			= $row['idEpreuveParcours'];
						$tab_docs_parcours[$j][0]				= $row['idEpreuveParcours'];
						$f->parc_id[$j]			= $row['idEpreuveParcours'];
						if ($nb_parcours > 5) $length_aff_parcours = 10;
						else $length_aff_parcours = 60;

						if ($j == 1) $f->parc_nom[$j]	= $row['nomParcours'];
						else $f->parc_nom[$j] = addslashes_form_to_sql($row['nomParcours']);
						$f->parc_type[$j]			= $row['idTypeParcours'];
						$f->parc_ordre[$j]			= $row['ordre_affichage'];
						$f->parc_date[$j] = dateen2fr($row['horaireDepart']);
						$f->parc_heure[$j]			= $heure[0];
						$f->parc_min[$j]			= $heure[1];
					}
				} else {
					include("includes/footer_js_base.php");
			?>

					<div class="modal" id="affichage_modal_exemple_0" aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
								<div style="text-align: right;" class="modal-header">
									header
								</div>
								<div class="modal-body">
									<fieldset> Exemple des champs prédéfinis-</fieldset>
									<hr>
									<div id="exemple_champs_param_0"></div>
								</div>
								<div class="modal-footer"><a data-dismiss="modal" id="fermer_resultat" class="btn btn-primary" href="#">OK</a>
								</div>
							</div>
						</div>
					</div>

					<script>
						$('#affichage_modal_exemple_0').modal({
							open: true,
							modal: true,
						});
					</script>
					<div class="row">
						<!-- begin col-12 -->
						<div class="col-md-12">
							<!-- begin panel -->
							<div class="panel panel-inverse">
								<div class="panel-heading">
									<div class="panel-heading-btn">
										Vous n'avez pas accès à cette épreuve !
									</div>
								</div>
							</div>
						</div>
					</div>
			<?php
					header('Location: login_v2.php');
					exit;
				}
			} else {


				unset($_SESSION['mod_epre_id']);
				unset($_SESSION['mod_epre_ids']);
				unset($_SESSION['mod_epre_ids_code_promo']);
			}


			?>
			</form> <!-- begin row -->
			<div class="row">
				<!-- begin col-12 -->
				<div class="col-md-12">
					<!-- begin panel -->
					<div class="panel panel-inverse">
						<div class="panel-heading">
							<div class="panel-heading-btn">

								<?php if (isset($_GET['epre_id']) && ($_GET['epre_button'] != 'Modifier la fiche de cette course')) { ?>
									<a class="btn-sm m-r-5 btn-success" href="creation_epreuve.php?epre_id=<?php echo $_GET['epre_id']; ?>&epre_button=Modifier la fiche de cette course" type="button"><i class="fa fa-edit"></i> Editer cette épreuve</a>
								<?php } else if (isset($_GET['epre_id']) && ($_GET['epre_button'] == 'Modifier la fiche de cette course')) {
								?>
									<a class="btn-sm m-r-5 btn-danger" href="creation_epreuve.php?epre_id=<?php echo $_GET['epre_id']; ?>" type="button"><i class="fa fa-times"></i> Ne plus editer</a>
									<a class="btn-sm m-r-5 btn-primary" href="liste_des_inscrits_v2.php?id_epreuve=<?php echo $_GET['epre_id']; ?>&jours=tous" type="button"><i class="fa fa-users"></i> Gestion des inscriptions</a>
									<a class="btn-sm m-r-5 btn-primary" href="/epreuve.php?id_epreuve=<?php echo $_GET['epre_id']; ?>" type="button" target="_blank"><i class="fa fa-list"></i> Voir la fiche épreuve </a>
									<!--<a class="btn-sm m-r-5 btn-info" href="/inscriptions.php?id_epreuve=<?php echo $_GET['epre_id']; ?>&panel=iframe" type="button" target="_blank"><i class="fa fa-chain"></i> lien Iframe </a>/-->
								<?php } ?>

							</div>
							<h2 class="panel-title" id="nom_epreuve"><?php if ($f->nom != '') echo $f->nom;
																		else echo "Nouvelle épreuve"; ?></h2>
						</div>
						<div class="panel-body">
							<div id="wizard">
								<!-- begin Wizard -->
								<ol>
									<li>
										Parcours
										<small>Renseignez les parcours</small>
									</li>

									<?php if ($_GET['epre_button'] == 'Modifier la fiche de cette course' || !isset($_GET['epre_id'])) { ?>
										<li>
											Validation
										</li>
									<?php } ?>
								</ol>
								<!-- Wizatd 1 = édition des tracés/parcours -->
								<div class="wizard-step-1">
									<div class="panel panel-inverse panel-with-tabs">
										<div class="panel-heading p-0">
											<div class="panel-heading-btn m-r-10 m-t-10">
											</div>
											<div class="tab-overflow">
												<ul class="nav nav-tabs nav-tabs-inverse">
													<?php if ($nb_parcours > 5) { ?>
														<li class="active" id="P1"><a href="#parcours_1" data-toggle="tab" id="name_parcours_1"><i id="badge_parcours_1" class="badge badge-secondary" data-toggle="tooltip" data-placement="top" data-original-title="<?php if (isset($_GET['epre_id'])) echo $f->parc_nom[1];
																																																																		else echo "Parcours N°1"; ?>">P1</i></a></li>
													<?php } else { ?>
														<li class="active" id="P1"><a href="#parcours_1" data-toggle="tab" id="name_parcours_1"><?php if (isset($_GET['epre_id'])) echo tronque_texte($f->parc_nom[1], $length_aff_parcours);
																																				else echo "Parcours N°1"; ?></a></li>
													<?php  } ?>
													<li id="ajouter_parcours" id="P2"><a href="#" class="add-parcours">Parcours +</a>
												</ul>
											</div>
										</div>
										<!-- Début formulaire -->
										<?php if (isset($_GET['epre_id'])) { ?>
											<form action="toGeojson.php" method="POST" data-parsley-validate="true" name="form-wizard" class="form-horizontal" id="formGeo" enctype="multipart/form-data">
											<?php } ?>
											<input type="hidden" name="idEpreuve" value="<?php echo $_GET['epre_id']; ?>">
											<input type="hidden" name="idParcours" value="<?php echo $tab_id[1][0]; ?>">

											<div class="tab-content" id="divTab">
												<div class="tab-pane fade active in" id="parcours_1">
													<fieldset>
														<legend class="pull-left width-full"><b>Informations sur le parcours </b> id_parcours : <?php if (isset($tab_id[1][0])) echo $tab_id[1][0]; ?></legend>
														<div class="row-margin-left">
															<div class="col-md-4">
																<div class="form-group m-r-20 input-group-lg">
																	<h4>Nom de la course*</h4>
																	<input type="text" name="name_parcours" value="<?php if ($f->parc_nom[1] != '') echo $f->parc_nom[1];
																													else echo "Mon premier parcours"; ?>" placeholder="Nom du parcours" class="form-control input-group-lg" data-parsley-group="wizard-step-1" onchange="$('a#name_parcours_1').text(this.value);" required />

																</div>
															</div>
														</div> <!-- end row -->
														<div class="row-margin-left">
															<!-- begin row -->
															<div class="form-group">
															</div>
														</div>
													</fieldset>
													<fieldset>
														<legend>Édition du tracé</legend>
														<div class="row row-margin-left">
															<div class="col-md-5">
																<div class="form-group m-r-20 input-group-lg">
																	<h4>Télécharger le parcours (geojson, gpx, kml)</h4>
																	<input type="file" id="trace" name="trace" accept=".kml,.geojson,.json,.gpx" data-parsley-group="wizard-step-1">
																</div>
															</div>
															<div class="col-md-2">
																<div class="form-group m-r-20 input-group-lg">
																	<?php if (isset($_GET['epre_id'])) {
																		$id_epr = $_GET['epre_id'];
																		$id_parcours = $tab_id[1][0];
																		if (isset($infos_trace["$id_epr"]["$id_parcours"]["couleur"]))
																			$couleur = $infos_trace["$id_epr"]["$id_parcours"]["couleur"];
																		else
																			$couleur = "#B52038";
																		if (isset($infos_trace["$id_epr"]["$id_parcours"]["distance"]))
																			$distance = $infos_trace["$id_epr"]["$id_parcours"]["distance"];
																		else
																			$distance = 0;

																		$epContent = 		'<h4>Couleur</h4>';
																		$epContent .= 			'<select name="color_trace" class="form-select form-select-lg mb-3" aria-label=".form-select-lg">';
																		$epContent .= 				'<option value="#B52038" ' . (($couleur == "#B52038") ? "selected" : "") . '>Rouge</option>';
																		$epContent .= 				'<option value="#C0DBD5" ' . (($couleur == "#C0DBD5") ? "selected" : "") . '>Vert</option>';
																		$epContent .= 				'<option value="#406D94" ' . (($couleur == "#406D94") ? "selected" : "") . '>Bleu</option>';
																		$epContent .= 				'<option value="#8D2D54" ' . (($couleur == "#8D2D54") ? "selected" : "") . '>Bordeau</option>';
																		$epContent .= 				'<option value="#B5B2D0" ' . (($couleur == "#B5B2D0") ? "selected" : "") . '>Violet</option>';
																		$epContent .= 				'<option value="#EECCA5" ' . (($couleur == "#EECCA5") ? "selected" : "") . '>Jaune</option>';
																		$epContent .= 				'<option value="#D28271" ' . (($couleur == "#D28271") ? "selected" : "") . '>Orange</option>';
																		$epContent .= 			'</select>';
																		$epContent .= 		'	</div>';
																		$epContent .= 	'</div>';
																		$epContent .= '<div class="col-md-1">';
																		$epContent .= 	'<div class="form-group m-r-20 input-group-lg">';
																		$epContent .= 		'<h4>Distance</h4>';
																		$epContent .= 			'<input type="text" name="distance" value="' . $distance . '" class="form-control input-group-lg" data-parsley-group="wizard-step-1" required />';
																		$epContent .=	'</div>';
																		$epContent .= '</div>';

																		echo $epContent;
																	} ?>
																</div>

													</fieldset> <!-- Début Points d'intérêt -->
													<fieldset>
														<legend>Points d'intérêt de la course</legend>

														<div class="row row-margin-left">
															<div class="table-responsive">
																<table id="table_point_<?php echo $tab_id[1][0] ?>" class="table table-striped table-bordered">
																	<thead>
																		<tr>
																			<th>#</th>
																			<th class="text-center">Catégorie</th>
																			<th class="text-center">Coordonnées</th>
																			<th class="text-center">Commentaire</th>
																			<th class="text-center">id_lecteur</th>
																			<th class="text-center">Modifier</th>
																		</tr>
																	</thead>
																	<tbody id="tbody_point_<?php echo $tab_id[1][0] ?>">
																		<!-- Début table rows -->
																		<?php
																		if (isset($_GET['epre_id'])) {
																			$id_epr = $_GET['epre_id'];
																			$id_parcours = $tab_id[1][0];
																			foreach ($infos_points[$id_epr][$id_parcours] as $row) {
																				$td_content = '<tr id="tr_' . $row['id'] . '" class="tablerow" value="' . $row['id'] . '">';
																				$td_content .=	'<td>';
																				$td_content .= 		'<h4>' . $row['id'] . '</h4>';
																				$td_content .= 		'<input type="hidden" name="id_' . $row['id'] . '" value="' . $row['id'] . '">';
																				$td_content .=	'</td> <!-- id du point -->';
																				$td_content .=	'<td>';
																				$td_content .=		'<select name="category_' . $row['id'] . '" class="form-select form-select-lg mb-3" style="max-width:90%;" aria-label=".form-select-lg" data-parsley-group="wizard-step-1">';
																				$td_content .=			'<option value="Départ" ' . (($row['categorie'] == "Départ") ? "selected" : "") . '>Départ</option>';
																				$td_content .=			'<option value="Arrivée" ' . (($row['categorie'] == "Arrivée") ? "selected" : "") . '>Arrivée</option>';
																				$td_content .=			'<option value="Départ/Arrivée"  ' . (($row['categorie'] == "Départ/Arrivée") ? "selected" : "") . '>Départ/Arrivée</option>';
																				$td_content .=			'<option value="Ravitaillement"  ' . (($row['categorie'] == "Ravitaillement") ? "selected" : "") . '>Ravitaillement</option>';
																				$td_content .=			'<option value="Point intermédiaire"  ' . (($row['categorie'] == "Point intermédiaire") ? "selected" : "") . '>Point intermédiaire</option>';
																				$td_content .=		'</select>';
																				$td_content .=	'</td>';
																				$td_content .=	'<td>';
																				$td_content .=		'<input type="text" name="distance_depart_' . $row['id'] . '" value="' . $row['x'] . ',' . $row['y'] . ',' . $row['z'] . '" class="form-control input-group-lg" data-parsley-group="wizard-step-1" required>';
																				$td_content .=	'</td>';
																				$td_content .=	'<td>';
																				$td_content .=		'<input type="text" name="popupContent_' . $row['id'] . '" class="form-control input-group-lg" data-parsley-group="wizard-step-1" value="' . $row['popupContent'] . '">';
																				$td_content .=	'</td>';
																				$td_content .=	'<td>';
																				$td_content .=		'<input type="text" name="id_lecteur_' . $row['id'] . '" class="form-control input-group-lg" data-parsley-group="wizard-step-1" value="' . $row['id_lecteur'] . '">';
																				$td_content .=	'</td>';
																				$td_content .=	'<td style="text-align: center">';
																				$td_content .=	'<input type="hidden" name="id_' . $id_epr . '_' . $id_parcours . '_' . $row['id'] . '" value="' . $id_epr . '_' . $id_parcours . '_' . $row['id'] . '">';
																				$td_content .= '<a class="btn btn-danger btn-xs"><i class="fa fa-2x fa-trash-o bouton_suppression_point"></i></a></td>';
																				$td_content .= '</tr>';

																				echo $td_content;
																			}
																		}
																		?>
																		<!-- Ajout d'un point -->
																		<tr id="tr_0">
																			<td id="td_id_0">
																			</td> <!-- id du point -->
																			<td>
																				<select id="category_0" class="form-select form-select-lg mb-3" style="max-width:90%;" aria-label=".form-select-lg" data-parsley-group="wizard-step-1">
																					<option value="Départ">Départ</option>
																					<option value="Arrivée">Arrivée</option>
																					<option value="Départ/Arrivée">Départ/Arrivée</option>
																					<option value="Ravitaillement">Ravitaillement</option>
																					<option value="Point intermédiaire">Point intermédiaire</option>
																				</select>
																			</td>
																			<td>
																				<input id="distance_depart_0" type="text" class="form-control input-group-lg" data-parsley-group="wizard-step-1" placeholder="0.0000, 00.0000, 000">
																			</td>
																			<td>
																				<input type="text" id="popupContent_0" class="form-control input-group-lg" data-parsley-group="wizard-step-1" placeholder="Message à afficher dans la popup du point">
																			</td>
																			<td>
																				<input type="text" id="id_lecteur_0" class="form-control input-group-lg" data-parsley-group="wizard-step-1">
																			</td>
																			<td style="text-align: center"><a id="bouton_ajout_point" class="btn btn-success btn-xs bouton_ajout_point" data-original-title="Ajouter un point"><i class="fa fa-2x fa-plus-circle"></i></a></td>
																		</tr>
																	</tbody>
																</table>
															</div>
														</div> <!-- end row -->

													</fieldset> <!-- Fin Points d'intérêt -->
													<!-- Début submit -->
													<div class="row">
														<div class="col-md-2 offset-md-2">
															<?php if (isset($_GET['epre_id']) && ($_GET['epre_button'] == 'Modifier la fiche de cette course')) { ?>
																<p><button class="btn btn-danger btn-lg" type="submit"><strong>Mettre à jour votre épreuve</strong></button></p>
															<?php } else { ?>

																<p><button style="display:none" id="enre_epreuve" class="btn btn-danger btn-lg" type="submit"><strong>Enregistrer votre épreuve</strong></button></p>
															<?php } ?>
														</div>
													</div>
												</div>
											</div>
											</form> <!-- end form -->
									</div>
								</div> <!-- end wizard 1/-->

								<?php if ($_GET['epre_button'] == 'Modifier la fiche de cette course' || !isset($_GET['epre_id'])) { ?>
									<div class="wizard-step-2">

										<div class="jumbotron m-b-0 text-center">
											<?php if (isset($_GET['epre_id']) && ($_GET['epre_button'] == 'Modifier la fiche de cette course')) { ?>
												<h2>Votre épreuve est prête à être mise à jour </h2>
											<?php } else { ?>

												<h2>Votre épreuve est prête à être validée </h2>
											<?php } ?>

											<h4>Résumé de votre épreuve</h4>
											<div class="col-md-3">&nbsp;</div>
											<div class="col-md-6">
												<table class="table table-condensed m-b-0">

													<tbody>
														<tr>
															<td class="semi-bold">Nom</td>
															<td id="affiche_validation_nom_epreuve"><em><?php if (isset($f->nom)) echo $f->nom; ?></em></td>
														</tr>
														<tr>
															<td class="semi-bold">Date</td>
															<td id="affiche_validation_date_debut_epreuve"><em>Du <span><?php if (isset($f->epre_date)) echo $f->epre_date; ?></span> au <span id="affiche_validation_date_fin_epreuve"><?php if (isset($f->epre_date_fin)) echo $f->epre_date_fin; ?></span></em></td>
														</tr>
														<tr>
															<td class="semi-bold">Nombre de parcours</td>
															<td id="affiche_validation_nombre_parcours"><em><?php if (isset($f->nbparc)) echo $f->nbparc; ?></em></td>
														</tr>
													</tbody>
												</table>
											</div>
											<div class="col-md-3">&nbsp;</div>

											<div>
												<?php if (!isset($_GET['epre_id']) && ($_GET['epre_button'] != 'Modifier la fiche de cette course')) { ?>
												<?php } ?>
												<?php if (isset($_GET['epre_id']) && ($_GET['epre_button'] == 'Modifier la fiche de cette course')) { ?>
													<p><button class="btn btn-danger btn-lg" type="submit"><strong>Mettre à jour votre épreuve</strong></button></p>
												<?php } else { ?>

													<p><button style="display:none" id="enre_epreuve" class="btn btn-danger btn-lg" type="submit"><strong>Enregistrer votre épreuve</strong></button></p>
												<?php } ?>

												<input type="hidden" id="epre_nbparc" name="epre_nbparc" value="<?php if (isset($f->nbparc)) echo $f->nbparc; ?>">

											</div>
										</div>

										<div class="modal" id="resultat" data-keyboard="false" data-backdrop="static">
											<!-- data-keyboard="false" data-backdrop="static" /-->
											<div class="modal-dialog">
												<div class="modal-content">
													<div class="modal-header" style="text-align: right;">
														ATS-SPORT - EDITION PARCOURS
													</div>
													<div class="modal-body" id="return_resultat">
														<h4>VOS PARCOURS ONT ÉTÉ MIS À JOUR</h4>
													</div>
													<div class="modal-footer">
														<a href="#" class="btn btn-primary" id="fermer_resultat_final">Fermer</a>
													</div>
												</div><!-- /.modal-content -->
											</div><!-- /.modal-dialog -->
										</div>
									<?php } ?>
									</div>
							</div> <!-- end Wizard -->
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- end row -->
	</div> <!-- end #content -->
	<div id="affichage_modal"> </div>

	<!-- begin scroll to top btn -->
	<a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top fade" data-click="scroll-top"><i class="fa fa-angle-up"></i></a>
	<!-- end scroll to top btn -->
	</div>
	<!-- end page container -->

	<?php include("includes/footer_js_base.php"); ?>

	<!-- ================== BEGIN PAGE LEVEL JS ================== -->
	<script src="assets/plugins/parsley/dist/parsley.min.js"></script>
	<script src="assets/plugins/bootstrap-wizard/js/bwizard.min.js"></script>
	<script src="assets/js/form-wizards-validation.demo.js"></script>
	<script src="assets/js/form-slider-switcher.demo.js"></script>
	<script src="assets/js/form-plugins.demo.js"></script>

	<script src="assets/plugins/masked-input/masked-input.min.js"></script>
	<script src="assets/plugins/bootstrap-fileinput/js/fileinput.min.js" type="text/javascript"></script>
	<script src="assets/plugins/bootstrap-fileinput/js/fileinput_locale_fr.js" type="text/javascript"></script>
	<script src="assets/plugins/datetimepicker-master/jquery.datetimepicker.js" type="text/javascript"></script>
	<script src="assets/plugins/ionRangeSlider/js/ion-rangeSlider/ion.rangeSlider.min.js" type="text/javascript"></script>
	<script src="assets/plugins/switchery/switchery.min.js"></script>
	<script src="assets/plugins/bootstrap-wysihtml5/lib/js/wysihtml5-0.3.0.js"></script>
	<script src="assets/plugins/bootstrap-wysihtml5/src/bootstrap-wysihtml5.js"></script>
	<script src="assets/js/form-wysiwyg.demo.js"></script>

	<script type="text/javascript" src="../2017/js/jquery.cycle.all.js"></script>
	<!-- ================== LEAFLET ================== -->
	<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
	<script src="../assets/js/carte.js"></script>
	<script src="../assets/js/togeojson.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/@turf/turf@5/turf.min.js"></script>

	<!-- ================== END PAGE LEVEL JS ================== -->

	<script>
		// Script Clément envoi formulaire
		// #TODO Ajouter event pour chaque formulaire
		let form = document.getElementById('formGeo');
		form.addEventListener('submit', function(event) {
			<?php
			//Synthèse des variables
			$i = 0;
			$id_parcours = array();
			foreach ($f->parc_id as $parcours) {
				$i++;
				$id_parcours[$i] = $parcours;
			}
			?>
			const nb_parcours = <?php echo $i ?>;
			const id_parcours = <?php echo json_encode($id_parcours) ?>;
			var nb_points = 0;
			var id_point = [];

			form.querySelectorAll('.tablerow').forEach(item => {
				nb_points++;
				id_point[nb_points] = item.id.split("_")[1];
			});

			for (var i = 1; i < nb_parcours + 1; i++) {
				var input = document.createElement('input');
				input.type = "text";
				input.name = "id_point[]";
				input.value = id_point[i];
				input.type = "hidden";
				form.prepend(input);
			}

			var input = document.createElement('input');
			input.type = "text";
			input.name = "nb_points";
			input.value = nb_points;
			input.type = "hidden";
			form.prepend(input);

			let geoFile = document.getElementById('trace');
			console.log(geoFile);

			// Pour chaque parcours récupérer leurs id

			// Set un tableau de fichiers
			let trace = geoFile.files[0];
			// console.log('trace : ' + trace.text());
			form.submit();
		});

		// Script ajout de point
		<?php
		//Synthèse des variables
		$i = 0;
		$id_parcours = array();
		foreach ($f->parc_id as $parcours) {
			$i++;
			$id_parcours[$i] = $parcours;
		}
		?>

		var nb_parcours = <?php echo $i ?>;
		var id_parcours = <?php echo json_encode($id_parcours) ?>;
		const tbodyEl = [];
		const tableEl = [];

		for (var i = 1; i < 2; i++) { //i<nb_parcours+1
			tbodyEl[i] = document.getElementById("tbody_point_" + id_parcours[i]);
			tableEl[i] = document.getElementById("table_point_" + id_parcours[i]);
			// Création evenements
			tableEl[i].addEventListener('click', onDeleteRow);
		}

		document.querySelectorAll('.bouton_ajout_point').forEach(item => {
			item.addEventListener('click', ajouterPoint);
		});

		function ajouterPoint(e) {
			// S'il y a déjà une ligne récupérer l'id du dernier point sinon partir de 1
			currentTable = e.currentTarget.closest('table');
			requiredField = e.currentTarget.closest('tr').children[2].firstElementChild;
			if (requiredField.value == "") {
				requiredField.style.backgroundColor = "#ffcccb";
				if (requiredField.nextSibling.tagName != "SPAN")
					requiredField.insertAdjacentHTML("afterend", "<span>mauvais format</span>");
				return;
			}

			if (countRows(currentTable) >= 1) {
				var id = parseInt(document.getElementById("tr_0").previousElementSibling.querySelector("td").querySelector("h4").innerHTML) + 1;
			} else {
				var id = 1;
			}

			const id_epreuve = <?php echo $_GET['epre_id'] ?>;
			var id_parcours = currentTable.id.split("_");
			id_parcours = id_parcours[2];
			const category = document.getElementById("category_0").value;
			const distance_depart = document.getElementById("distance_depart_0").value;
			const popupContent = document.getElementById("popupContent_0").value;
			const id_lecteur = document.getElementById("id_lecteur_0").value;

			document.getElementById("tr_0").insertAdjacentHTML('beforebegin', `
				<tr id="tr_${id}" class="tablerow">
					<td id="td_id_${id}"><h4>${id}</h4></td>
					<td>
						<select id="category_${id}" name="category_${id}" class="form-select form-select-lg mb-3" style="max-width:90%;" aria-label=".form-select-lg" data-parsley-group="wizard-step-1">
							<option value="Départ" ${((category == "Départ") ? "selected" : "")}>Départ</option>
							<option value="Arrivée" ${((category == "Arrivée") ? "selected" : "")}>Arrivée</option>
							<option value="Départ/Arrivée" ${((category == "Départ/Arrivée") ? "selected" : "")}>Départ/Arrivée</option>
							<option value="Ravitaillement" ${((category == "Ravitaillement") ? "selected" : "")}>Ravitaillement</option>
							<option value="Point intermédiaire" ${((category == "Point intermédiaire") ? "selected" : "")}>Point intermédiaire</option>
						</select>
					</td>
					<td>
						<input id="distance_depart_${id}" type="text" name="distance_depart_${id}" class="form-control input-group-lg" data-parsley-group="wizard-step-1" required value="${distance_depart}">
					</td>
					<td>
						<input type="text" name="popupContent_${id}" class="form-control input-group-lg" data-parsley-group="wizard-step-1" value="${popupContent}">
					</td>
					<td>
						<input type="text" name="id_lecteur_${id}" class="form-control input-group-lg" data-parsley-group="wizard-step-1" value="${id_lecteur}">
					</td>
					<td style="text-align: center">
						<a class="btn btn-danger btn-xs">
							<input type="hidden" name="id_${id_epreuve}_${id_parcours}_${id}" value="${id_epreuve}_${id_parcours}_${id}">
							<i class="fa fa-2x fa-trash-o bouton_suppression_point"></i>
						</a>
					</td>																
				</tr>
			`);
			document.getElementById(`category_${id}`).value = category;

			//Effacer le contenu de la ligne d'insertion
			var nodes = document.getElementById("category_0").children;
			for (var i = 1; i < nodes.length; i++) {
				if (nodes[i].nodeName.toLowerCase() == 'option')
					nodes[i].setAttribute('selected', false);
			}
			document.getElementById("category_0").firstElementChild.setAttribute('selected', true);
			document.getElementById("distance_depart_0").value = "";
			document.getElementById("popupContent_0").value = "";
			document.getElementById("id_lecteur_0").value = "";
		}

		function onDeleteRow(e) {
			const btn = e.target;
			if (!btn.classList.contains('bouton_suppression_point')) {
				return;
			}
			var verification = confirm("Êtes vous sûr de vouloir supprimer ce point ?");
			if (verification) {
				var ids = btn.parentElement.previousElementSibling.value.split('_');
				console.log('btn : ' + btn.parentElement.previousElementSibling.value + ' id : ' + ids[2] + ' id_parcours : ' + ids[1] + ' id_epreuve ' + ids[0])
				$.ajax({
					url: "ajax_remove_carto.php",
					type: 'POST',
					data: {
						id_epreuve: ids[0],
						id_parcours: ids[1],
						id: ids[2]
					},
					success: function(reponse) {
						if (reponse == 1 || reponse == 2) {
							btn.closest('tr').remove();
							if (reponse == 2)
								alert('Le point n\'était pas en BDD');
						} else {
							alert('id invalides');
						}
					}
				});
			}
		}

		function countRows(table) {
			return table.rows.length - 2;
		}
	</script>



	<script>
		<?php if (isset($_GET['epre_id']) && ($_GET['epre_button'] != 'Modifier la fiche de cette course')) { ?>

			$('#form-parcours input').attr('readonly', 'readonly');
			$('#form-parcours select').attr('disabled', 'disabled');
			$('#form-parcours textarea').attr('readonly', 'readonly');
			$('#ajouter_parcours').hide();

		<?php } ?>

		function parcours_content(id) {
			content = '<fieldset>';
			content += '<input type="hidden" id="id_table_parcours[' + <?php echo $nbparc; ?> + ']" name="id_table_parcours[' + <?php echo $nbparc; ?> + ']" value="<?php echo $tab_id[$nbparc][0]; ?>" />';
			content += '<legend class="pull-left width-full">Information sur le parcours</legend>';
			content += '<div class="row-margin-left">';
			content += '	<div class="col-md-4">';
			content += '		<div class="form-group m-r-20 input-group-lg">';
			content += '			<h4>Nom de la course*</h4>';
			content += '			<input type="text" value="<?php echo $f->parc_nom[$nbparc]; ?>" name="epre_parc_nom[' + <?php echo $nbparc; ?> + ']" id="epre_parc_nom' + <?php echo $nbparc; ?> + '" placeholder="Nom du parcours" class="form-control input-lg" data-parsley-group="wizard-step-2" onchange="$(\'a#name_parcours_' + <?php echo $nbparc; ?> + '\').text(this.value);" required />';
			content += '		</div>';
			content += '	</div>';

			content += '<div class="col-md-2">';
			content += '	<div class="form-group m-r-20 input-group-lg">';
			content += '		<h4>Ordre d\'affichage</h4>';
			content += '		<input type="text" name="epre_parc_ordre[' + <?php echo $nbparc; ?> + ']" id="epre_parc_ordre' + <?php echo $nbparc; ?> + '" value="<?php echo $f->parc_ordre[$nbparc]; ?>" class="form-control input-group-lg" data-parsley-group="wizard-step-2" required />';
			content += '	</div>';
			content += '</div>';
			content += '</fieldset>';

			content += '<fieldset>';
			content += '	<legend>Édition du tracé</legend>';
			content += '	<div class="row row-margin-left">';
			content += '	</div>';

			content += '<fieldset>';
			content += '	<legend>Points d\'intérêt de la course</legend>';
			content += '	<div class="row row-margin-left">';
			content += '	</div>';
			content += '</fieldset>';

			return content;
		}

		function nombre_alea() {
			var nbr_ch = 2; //  generation d'un nombre a 5 chiffres modifier si besoin
			var x = Math.random();
			var nb = x * Math.pow(10, nbr_ch);
			nb_g = Math.round(nb);
			return nb_g;
		}

		$(".nav-tabs").on("click", "a", function(e) {
				e.preventDefault();
				if (!$(this).hasClass('add-parcours')) {
					//console.log(this);
					//console.log($(this).show());
					$(this).tab('show');
					//$(this).addClass('badge badge-primary');
				}
				//$('#badge_parcours_1').addClass('badge badge-primary');
			})
			.on("click", "span", function(e) {
				e.preventDefault();
				var anchor = $(this).siblings('a');
				$(anchor.attr('href')).remove();
				$(this).parent().remove();
				$(".nav-tabs li").children('a').first().click();
				$('#epre_nbparc').val($("input[id*='epre_parc_nom']").length);

			});

		$('.add-parcours').click(function(e) {

			e.preventDefault();

			var id = nombre_alea();

			var tabId = 'parcours_' + id;

			content = parcours_content(id);

			$(this).closest('li').before('<li id="P' + id + '"><a id="name_parcours_' + id + '" href="#parcours_' + id + '">Nouveau parcours</a> <span><i class="fa fa-times"></i></span></li>');
			$('.tab-content').append('<div class="tab-pane" id="' + tabId + '">' + content + '</div>');

			var id_tmp = $(".nav-tabs").children().length;
			$('.nav-tabs li:nth-child(' + (id_tmp - 1) + ') a').click();

			FormSliderSwitcher.init("#relais-dossard_equipe-" + id + ", #auto_parentale-" + id + ",#button-visible_liste_inscrit-" + id + ", #champ_supp_" + id + ",#certif_medical-" + id + ",#button-certif_medical-" + id + ",#relais-" + id + ",#age-" + id + ",#plus-de-tarif-" + id + ",#plage-exclusion-" + id + ", #affichage_code_promo_" + id);

			$('#epre_nbparc').val($("input[id*='epre_parc_nom']").length);

			handelTooltipPopoverActivation();
			handleFormWysihtml5Parcours('parcours_description_wysihtml5_' + id);
			handleFormWysihtml5Parcours('info_complementaire_parcours_wysihtml5_' + id);
			//Activation date et heure de départ de la course
			$('#date_parcours_timepicker_start_' + id).datetimepicker({
				format: 'd/m/Y H:i',
				lang: 'fr',
				step: 15
			});
			//Activation date et heure de départ code_promo
			$('#date_timepicker_start_code_promo_' + id).datetimepicker({
				format: 'd/m/Y H:i',
				lang: 'fr',
				onShow: function(ct) {
					this.setOptions({
						maxDate: getDate($('#date_timepicker_end_code_promo_' + id).val()) ? getDate($('#date_timepicker_end_code_promo_' + id).val()) : false
					})
				},
				timepicker: true
			});
			$('#date_timepicker_end_code_promo_' + id).datetimepicker({
				format: 'd/m/Y H:i',
				lang: 'fr',
				onShow: function(ct) {
					this.setOptions({
						minDate: getDate($('#date_timepicker_start_code_promo_' + id).val()) ? getDate($('#date_timepicker_start_code_promo_' + id).val()) : false
					})
				},
				timepicker: true
			});

			//gestion des fichiers	
			var inputfps = new Array();
			inputfps[id] = $("#fichier_parcours_sup_" + id);
			inputfps[id].fileinput({
				uploadUrl: 'submit_file.php',
				uploadAsync: false,
				showUpload: false, // hide upload button
				showRemove: false, // hide remove button
				allowedFileExtensions: ['jpg', 'png', 'gif', 'pdf'],
				uploadExtraData: function() {
					return {
						parcours: id
					};
				},
				maxFileSize: 2000,
				maxFileCount: 5
			}).on("filebatchselected", function(event, files) {
				// trigger upload method immediately after files are selected
				inputfps[id].fileinput("upload");
			});
		});


		// Génération des onglets par parcours
		<?php if ($f->nbparc > 1) {
			for ($nbparc = 2; $nbparc < ($f->nbparc + 1); $nbparc++) { ?>

				var tabId = 'parcours_' + <?php echo $nbparc; ?>;

				content = '<fieldset>';
				content += '<input type="hidden" id="id_table_parcours[' + <?php echo $nbparc; ?> + ']" name="id_table_parcours[' + <?php echo $nbparc; ?> + ']" value="<?php echo $tab_id[$nbparc][0]; ?>" />';
				content += '<legend class="pull-left width-full">Information sur le parcours</legend>';
				content += '<div class="row-margin-left">';
				content += '	<div class="col-md-4">';
				content += '		<div class="form-group m-r-20 input-group-lg">';
				content += '			<h4>Nom de la course*</h4>';
				content += '			<input type="text" value="<?php echo $f->parc_nom[$nbparc]; ?>" name="epre_parc_nom[' + <?php echo $nbparc; ?> + ']" id="epre_parc_nom' + <?php echo $nbparc; ?> + '" placeholder="Nom du parcours" class="form-control input-lg" data-parsley-group="wizard-step-2" onchange="$(\'a#name_parcours_' + <?php echo $nbparc; ?> + '\').text(this.value);" required />';
				content += '		</div>';
				content += '	</div>';

				content += '<div class="col-md-2">';
				content += '	<div class="form-group m-r-20 input-group-lg">';
				content += '		<h4>Ordre d\'affichage</h4>';
				content += '		<input type="text" name="epre_parc_ordre[' + <?php echo $nbparc; ?> + ']" id="epre_parc_ordre' + <?php echo $nbparc; ?> + '" value="<?php echo $f->parc_ordre[$nbparc]; ?>" class="form-control input-group-lg" data-parsley-group="wizard-step-2" required />';
				content += '	</div>';
				content += '</div>';
				content += '</fieldset>';

				content += '<fieldset>';
				content += '	<legend>Édition du tracé</legend>';
				content += '	<div class="row row-margin-left">';
				content += '	</div>';

				content += '<fieldset>';
				content += '	<legend>Points d\'intérêt de la course</legend>';
				content += '	<div class="row row-margin-left">';
				content += '	</div>';
				content += '</fieldset>';

				$('.add-parcours').closest('li').before('<li id="P' + <?php echo $nbparc; ?> + '"><a id="name_parcours_' + <?php echo $nbparc; ?> + '" href="#parcours_' + <?php echo $nbparc; ?> + '">parcours ' + <?php echo $nbparc; ?> + '</a> <span><i class="ion-close"></i></span></li>');
				$('.tab-content').append('<div class="tab-pane" id="' + tabId + '">' + content + '</div>');

				var inputfpsupdate_<?php echo $nbparc; ?> = $("#fichier_parcours_sup_<?php echo $nbparc; ?>");
				inputfpsupdate_<?php echo $nbparc; ?>.fileinput({
							uploadUrl: 'submit_file.php',
							uploadAsync: false,
							showUpload: false, // hide upload button
							showRemove: false, // hide remove button
							allowedFileExtensions: ['jpg', 'png', 'gif', 'pdf'],

							<?php if ($f->docs_parcours_id[$nbparc][0] != '') { ?>
								<?php if ($f->idEpreuve != '') { ?>

									initialPreview: [
										<?php for ($i = 0; $i < count($f->docs_parcours_rep[$nbparc]); $i++) { ?> "<img src='<?php echo $f->docs_parcours_rep[$nbparc][$i]; ?>' class='file-preview-image-120'>",
										<?php } ?>
									],
								<?php } ?>
								<?php if (($_GET['epre_button'] == 'Modifier la fiche de cette course')) { ?>
									initialPreviewConfig: [
										<?php for ($i = 0; $i < count($f->docs_parcours[$nbparc]); $i++) { ?> {
												caption: "<?php echo $f->docs_parcours_affichage[$nbparc][$i]; ?>",
												width: "120px",
												url: "<?php echo $f->url_del_docs_parcours[$nbparc][$i]; ?>",
												key: 1
											},
										<?php } ?>
									],
								<?php } elseif ($f->idEpreuve != '') { ?>
									initialPreviewConfig: [
										<?php for ($i = 0; $i < count($f->docs_parcours[$nbparc]); $i++) { ?> {
												caption: "<?php echo $f->docs_parcours_affichage[$nbparc][$i]; ?>",
												width: "120px",
												key: 1
											},
										<?php } ?>
									],
								<?php } ?>

								uploadExtraData: function() {
									return {
										parcours: <?php echo $nbparc; ?>,
										update: 1,
										idEpreuve: <?php echo $f->idEpreuve; ?>,
										num_parcours: <?php echo $f->docs_parcours_id[$nbparc][0]; ?>,
									};
								<?php } else { ?>
									uploadExtraData: function() {
											return {
												parcours: <?php echo $nbparc; ?>
											};
										<?php } ?>

										},
										maxFileSize: 2000,
										maxFileCount: 5
								}).on("filebatchselected", function(event, files) {
							// trigger upload method immediately after files are selected
							inputfpsupdate_<?php echo $nbparc; ?>.fileinput("upload");
						}); <?php if ($nb_parcours > 5) { ?> $('a#name_parcours_' + <?php echo $nbparc; ?>).html(' <i id="badge_parcours_<?php echo $nbparc; ?>" class="badge badge-primary" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo $f->parc_nom[$nbparc]; ?>">P<?php echo $nbparc; ?></i>'); <?php } else { ?> $('a#name_parcours_' + <?php echo $nbparc; ?>).text('<?php echo tronque_texte($f->parc_nom[$nbparc], $length_aff_parcours); ?>'); <?php  } ?>



					<?php } ?>
					var nb_li = $(".nav-tabs").children().length; //think about it ;)

					<?php } ?>

					function etat_change_checkbox(name) {

						$('#' + name).live('change', function() {
							if (($(this).prop('checked')) == true) {
								$('#div-' + name).show()
							} else {
								$('#div-' + name).hide()
							}
						});
					}

					function etat_change_checkbox_button(name) {

						$('#' + name).live('change', function() {
							if (($(this).prop('checked')) == false) {
								if (($('#button-' + name).prop('checked')) == true) {
									$('#button-' + name).click();
								}
							}
						});
					}
					//soumission du formulaire
					$('[data-parsley-validate="true"]').on('submit', function(e) {
						e.preventDefault();
						var targetUrl = $(this).attr('action');
						//alert(targetUrl);
						scriptCharset: "iso-8859-1",

							$.post(targetUrl, $(this).serialize()).done(function(data) {

								var returnData = data;

								url_return = 'creation_epreuve.php?epre_id=' + data + '&epre_button=Modifier la fiche de cette course';

								$("#fermer_resultat_final").attr("href", url_return);

								$("#resultat").modal({
									open: true,
									modal: true,
									keyboard: false

								});
							});
					});

					function vider_champ(num_parc) {

						var champ = ["dotation", "participation", "questiondiverse"];
						champ.forEach(function(champ) {

							//copie_champ_create(num_parc,entry);
							$('#table_up_' + champ + '\\[' + num_parc + '\\]').empty();
							$('#affichage_modal_' + champ + '\\[' + num_parc + '\\]').empty();
							$('#div_up_' + champ + '\\[' + num_parc + '\\]').hide();
						});


					}

					function copie_champ(num_parc) {

						var champ = ["dotation", "participation", "questiondiverse"];
						champ.forEach(function(entry) {

							copie_champ_create(num_parc, entry);
						});
					}

					function copie_champ_create(num_parc, champ) {

						$('#table_up_' + champ + '\\[' + num_parc + '\\]').empty();
						$('#affichage_modal_' + champ + '\\[' + num_parc + '\\]').empty();

						var cpt = 1;
						var num = new Array();
						var copie_parc;
						$(".nav-tabs li").each(function(index) {
							num[cpt] = $(this).attr('id');
							num[cpt] = num[cpt].replace("P", "");

							if (num[cpt] == num_parc) {
								cpt = cpt - 1;
								copie_parc = num[cpt];
								return false;
							}

							cpt++;
						});

						var nb_tr = $("tr[id*='tr_" + champ + "\\[" + copie_parc + "\\]']").length;

						if ($('#div_up_' + champ + '\\[' + copie_parc + '\\]').css('display') != 'none') {
							$('#div_up_' + champ + '\\[' + num_parc + '\\]').show();

							var content = '';

							for (id = 1; id <= nb_tr; id++) {
								content += '			<tr id="tr_' + champ + '[' + num_parc + ']_' + id + '">';
								content += '				<td id="td_ordre_' + champ + '_' + num_parc + '_' + id + '"><input size="1" type="text" value="' + id + '" name="epre_parcours_input_ordre_' + champ + '[' + num_parc + '][' + id + ']" id="epre_parcours_input_ordre_' + champ + '[' + num_parc + '][' + id + ']"> </td>';
								content += '				<td id="td_nom_' + champ + '_' + num_parc + '_' + id + '">';
								content += $('#td_nom_' + champ + '_' + copie_parc + '_' + id).html() + '</td>';
								content += '				<td id="td_select_' + champ + '_' + num_parc + '_' + id + '">';
								content += '				</td><td id="td_action_' + champ + '[' + num_parc + ']_' + id + '"><a class="fa fa-2x fa-wrench m-r-5" onclick="editer_fiche_champ(\'' + champ + '\',' + num_parc + ',' + id + ');" href="javascript:;"></a><a class="fa fa-2x fa-times" <i="" onclick="bouton_supprimer_champ(\'' + champ + '\',' + num_parc + ',' + id + ');" href="javascript:;"></a>';
								content += '				</td>';
								content += '			</tr>';
							}

							$('#table_up_' + champ + '\\[' + num_parc + '\\]').append(content);
							for (id = 1; id <= nb_tr; id++) {
								var ddl = $('#epre_parcours_select_' + champ + '\\[' + copie_parc + '\\]\\[' + id + '\\]').clone();
								var selectedValue = $("#epre_parcours_select_" + champ + "\\[" + copie_parc + "\\]\\[" + id + "\\] option:selected").val();
								ddl.find("option[value = '" + selectedValue + "']").attr("selected", "selected");
								$('#td_select_' + champ + '_' + num_parc + '_' + id).append(ddl);
								$('td#td_select_' + champ + '_' + num_parc + '_' + id + ' #epre_parcours_select_' + champ + '\\[' + copie_parc + '\\]\\[' + id + '\\]').attr('id', 'epre_parcours_select_' + champ + '[' + num_parc + '][' + id + ']');
								$('td#td_select_' + champ + '_' + num_parc + '_' + id + ' #epre_parcours_select_' + champ + '\\[' + num_parc + '\\]\\[' + id + '\\]').attr('name', 'epre_parcours_select_' + champ + '[' + num_parc + '][' + id + ']');
								$('td#td_select_' + champ + '_' + num_parc + '_' + id + ' #epre_parcours_select_' + champ + '\\[' + num_parc + '\\]\\[' + id + '\\]').attr('onchange', 'Get_Champs_Sup(\'' + champ + '\',' + num_parc + ',' + id + ')');

								Create_Modal('' + champ + '', num_parc, id, 1);

								$('#epre_parcours_input_ordre_' + champ + '\\[' + num_parc + '\\]\\[' + id + '\\]').val($('#epre_parcours_input_ordre_' + champ + '\\[' + copie_parc + '\\]\\[' + id + '\\]').val());
								$('#epre_parcours_nom_' + champ + '\\[' + num_parc + '\\]\\[' + id + '\\]').val($('#epre_parcours_nom_' + champ + '\\[' + copie_parc + '\\]\\[' + id + '\\]').val());
								$('#epre_parcours_label_' + champ + '\\[' + num_parc + '\\]\\[' + id + '\\]').val($('#epre_parcours_label_' + champ + '\\[' + copie_parc + '\\]\\[' + id + '\\]').val());

								if (champ == 'dotation' || champ == 'questiondiverse') {

									$('#epre_parcours_critere_' + champ + '\\[' + num_parc + '\\]\\[' + id + '\\]').val($('#epre_parcours_critere_' + champ + '\\[' + copie_parc + '\\]\\[' + id + '\\]').val());
									$('#epre_parcours_type_champ_' + champ + '\\[' + num_parc + '\\]\\[' + id + '\\]').val($('#epre_parcours_type_champ_' + champ + '\\[' + copie_parc + '\\]\\[' + id + '\\]').val());
								} else {
									$('input:radio[id=epre_parcours_type_champ_' + champ + '\\[' + num_parc + '\\]\\[' + id + '\\]]').filter('[value=' + $('input:radio[id=epre_parcours_type_champ_' + champ + '\\[' + copie_parc + '\\]\\[' + id + '\\]]:checked').val() + ']').attr('checked', true);
									$('#epre_parcours_prix_' + champ + '\\[' + num_parc + '\\]\\[' + id + '\\]').val($('#epre_parcours_prix_' + champ + '\\[' + copie_parc + '\\]\\[' + id + '\\]').val());
									$('#epre_parcours_qte_' + champ + '\\[' + num_parc + '\\]\\[' + id + '\\]').val($('#epre_parcours_qte_' + champ + '\\[' + copie_parc + '\\]\\[' + id + '\\]').val());
								}
								$('input:radio[id=epre_parcours_critere_obligatoire_' + champ + '\\[' + num_parc + '\\]\\[' + id + '\\]]').filter('[value=' + $('input:radio[id=epre_parcours_critere_obligatoire_' + champ + '\\[' + copie_parc + '\\]\\[' + id + '\\]]:checked').val() + ']').attr('checked', true);
								$('input:radio[id=epre_parcours_critere_obligatoire_' + champ + '\\[' + num_parc + '\\]\\[' + id + '\\]]').filter('[value=non]').attr('checked', true);
								$('#epre_parcours_date_butoir_' + champ + '\\[' + num_parc + '\\]\\[' + id + '\\]').val($('#epre_parcours_date_butoir_' + champ + '\\[' + copie_parc + '\\]\\[' + id + '\\]').val());
							}
							$('#epre_parc_nbchamps' + champ + '\\[' + num_parc + '\\]').val(nb_tr);
						}
					}

					$('#form-parcours input').keypress(
						function(event) {
							if (event.keyCode == 13) {
								event.preventDefault();
								alert('Il faut effectuer toutes les étapes pour valider votre épreuve.');
							}
						}
					);



					$(document).ready(function() {
						App.init();
						$('[data-toggle="tooltip"]').tooltip();
						FormWizardValidation.init();
						FormSliderSwitcher.init("input[id*='email_'], input[id*='button-visible_liste_inscrit-'],input[id*='relais-dossard_equipe-'], input[id*='relais-'],input[id*='auto_parentale-'], input[id*='certif_medical-'],input[id*='button-certif_medical_-'],input[id*='age-'],input[id*='plus-de-tarif'],input[id*='plage-exclusion-'],input[id*='inscription-'],input[id*='affichage_code_promo'],input[id*='champ_supp']");
						FormWysihtml5.init();
						FormPlugins.init();
						<?php for ($nb = 1; $nb <= 12; $nb++) { ?>

							$('#date_timepicker_start_tarifs_1_' + <?php echo $nb; ?>).datetimepicker({
								format: 'd/m/Y H:i',
								lang: 'fr',
								step: 15,
								onShow: function(ct) {
									this.setOptions({
										minDate: new Date(),
										maxDate: getDate($('#date_timepicker_end_tarifs_1_' + <?php echo $nb; ?>).val()) ? getDate($('#date_timepicker_end_tarifs_1_' + <?php echo $nb; ?>).val()) : false
									})
								},
							});

							$('#date_timepicker_end_tarifs_1_' + <?php echo $nb; ?>).datetimepicker({
								format: 'd/m/Y H:i',
								lang: 'fr',
								step: 15,
								onShow: function(ct) {
									this.setOptions({
										minDate: getDate($('#date_timepicker_start_tarifs_1_' + <?php echo $nb; ?>).val()) ? getDate($('#date_timepicker_start_tarifs_1_' + <?php echo $nb; ?>).val()) : false
									})
								},
							});
						<?php } ?>
						//perso epreuve image de fond
						var input_image_de_fond = $("#epreuve_image_de_fond");
						input_image_de_fond.fileinput({
							overwriteInitial: false,
							uploadUrl: 'submit_file.php',
							uploadAsync: false,
							dropZoneEnabled: false,
							showPreview: true,
							showUpload: false, // hide upload button
							showRemove: false, // hide remove button
							allowedFileExtensions: ['jpg'],
							maxFileSize: 2000,
							maxFileCount: 1,
							<?php if ($f->epreuve_image_de_fond_fichier_rep != '') { ?>
								<?php if ($f->idEpreuve != '') { ?>

									initialPreview: [
										"<img src='<?php echo $f->epreuve_image_de_fond_fichier_rep; ?>' class='file-preview-image'>",
									],
								<?php } ?>
								<?php if (($_GET['epre_button'] == 'Modifier la fiche de cette course')) { ?>
									initialPreviewConfig: [{
										caption: "<?php echo $f->epreuve_image_de_fond_fichier_affichage; ?>",
										width: "120px",
										url: "<?php echo $f->url_del_epreuve_image_de_fond_fichier; ?>",
										key: 1
									}, ],
									uploadExtraData: function() {
										return {
											update: 1,
											idEpreuve: <?php echo $f->idEpreuve; ?>
										};
									},
								<?php } elseif ($f->idEpreuve != '') { ?>
									initialPreviewConfig: [{
										caption: "<?php echo $f->epreuve_image_de_fond_fichier_affichage; ?>",
										width: "120px",
										key: 1
									}, ],
								<?php } ?>
							<?php } ?>
						}).on("filebatchselected", function(event, files) {
							// trigger upload method immediately after files are selected
							input_image_de_fond.fileinput("upload");
						});
						//init visuel si fichier reglement
						<?php if ($type_reglement == 3) { ?>
							aff_select_reglement('fichier');

						<?php } ?>
					});

					var drop_zone = true;
					if (navigator.userAgent.match(/(android|iphone|ipad|blackberry|symbian|symbianos|symbos|netfront|model-orange|javaplatform|iemobile|windows phone|samsung|htc|opera mobile|opera mobi|opera mini|presto|huawei|blazer|bolt|doris|fennec|gobrowser|iris|maemo browser|mib|cldc|minimo|semc-browser|skyfire|teashark|teleca|uzard|uzardweb|meego|nokia|bb10|playbook)/gi)) {
						if (((screen.width >= 480) && (screen.height >= 800)) || ((screen.width >= 800) && (screen.height >= 480)) || navigator.userAgent.match(/ipad/gi)) {
							//alert('tablette');
							drop_zone = false;
						} else {
							//alert('mobile');
							drop_zone = false;
						}
					}

					var input = $("#epre_photo"); input.fileinput({
						uploadUrl: 'submit_file.php',
						uploadAsync: false,
						dropZoneEnabled: drop_zone,
						showUpload: false, // hide upload button
						showRemove: false, // hide remove button
						allowedFileExtensions: ['jpg', 'png', 'gif'],
						overwriteInitial: true,
						maxFileSize: 2000,
						maxFilesCount: 1,
						<?php if ($f->photo_epreuve_rep != '') { ?>
							<?php if ($f->idEpreuve != '') { ?>

								initialPreview: [
									"<img src='<?php echo $f->photo_epreuve_rep; ?>' class='file-preview-image'>",
								],
							<?php } ?>
							<?php if (($_GET['epre_button'] == 'Modifier la fiche de cette course')) { ?>
								initialPreviewConfig: [{
									caption: "<?php echo $f->photo_epreuve_affichage; ?>",
									width: "120px",
									url: "<?php echo $f->url_del_photo_epreuve; ?>",
									key: 1
								}, ],
								uploadExtraData: function() {
									return {
										update: 1,
										idEpreuve: <?php echo $f->idEpreuve; ?>
									};
								},
							<?php } elseif ($f->idEpreuve != '') { ?>
								initialPreviewConfig: [{
									caption: "<?php echo $f->photo_epreuve_affichage; ?>",
									width: "120px",
									key: 1
								}, ],
							<?php } ?>
						<?php } ?>

					}).on("filebatchselected", function(event, files) {
						// trigger upload method immediately after files are selected
						input.fileinput("upload");
					});


					var input_fes = $("#fichier_epreuve_sup"); input_fes.fileinput({
						overwriteInitial: false,
						uploadUrl: 'submit_file.php',
						uploadAsync: false,
						showUpload: false, // hide upload button
						showRemove: false, // hide remove button
						allowedFileExtensions: ['jpg', 'png', 'gif', 'pdf'],
						maxFileSize: 2000,
						maxFileCount: 10,
						<?php if (count($f->docs_epreuve_rep) > 0) { ?>
							<?php if ($f->idEpreuve != '') { ?>

								initialPreview: [
									<?php for ($i = 0; $i < count($f->docs_epreuve_rep); $i++) { ?> "<img src='<?php echo $f->docs_epreuve_rep[$i]; ?>' class='file-preview-image-120'>",
									<?php } ?>
								],
							<?php } ?>
							<?php if (($_GET['epre_button'] == 'Modifier la fiche de cette course')) { ?>
								initialPreviewConfig: [
									<?php for ($i = 0; $i < count($f->docs_epreuve); $i++) { ?> {
											caption: "<?php echo $f->docs_epreuve_affichage[$i]; ?>",
											width: "120px",
											url: "<?php echo $f->url_del_docs_epreuve[$i]; ?>",
											key: 1
										},
									<?php } ?>
								],
								uploadExtraData: function() {
									return {
										update: 1,
										idEpreuve: <?php echo $f->idEpreuve; ?>
									};
								},
							<?php } elseif ($f->idEpreuve != '') { ?>
								initialPreviewConfig: [
									<?php for ($i = 0; $i < count($f->docs_epreuve); $i++) { ?> {
											caption: "<?php echo $f->docs_epreuve_affichage[$i]; ?>",
											width: "120px",
											key: 1
										},
									<?php } ?>
								],
							<?php } ?>
						<?php } ?>
					}).on("filebatchselected", function(event, files) {
						// trigger upload method immediately after files are selected
						input_fes.fileinput("upload");
					});

					var input_fps = $("#fichier_parcours_sup_1"); input_fps.fileinput({
							uploadUrl: 'submit_file.php',
							uploadAsync: false,
							showUpload: false, // hide upload button
							showRemove: false, // hide remove button
							allowedFileExtensions: ['jpg', 'png', 'gif', 'pdf'],

							<?php if ($f->docs_parcours_id[1][0] != '') { ?>
								<?php if ($f->idEpreuve != '') { ?>

									initialPreview: [
										<?php for ($i = 0; $i < count($f->docs_parcours_rep[1]); $i++) { ?> "<img src='<?php echo $f->docs_parcours_rep[1][$i]; ?>' class='file-preview-image-120'>",
										<?php } ?>
									],
								<?php } ?>
								<?php if (($_GET['epre_button'] == 'Modifier la fiche de cette course')) { ?>
									initialPreviewConfig: [
										<?php for ($i = 0; $i < count($f->docs_parcours[1]); $i++) { ?> {
												caption: "<?php echo $f->docs_parcours_affichage[1][$i]; ?>",
												width: "120px",
												url: "<?php echo $f->url_del_docs_parcours[1][$i]; ?>",
												key: 1
											},
										<?php } ?>
									],
								<?php } elseif ($f->idEpreuve != '') { ?>
									initialPreviewConfig: [
										<?php for ($i = 0; $i < count($f->docs_parcours[1]); $i++) { ?> {
												caption: "<?php echo $f->docs_parcours_affichage[1][$i]; ?>",
												width: "120px",
												key: 1
											},
										<?php } ?>
									],
								<?php } ?>

								uploadExtraData: function() {
									return {
										parcours: 1,
										update: 1,
										idEpreuve: <?php echo $f->idEpreuve; ?>,
										num_parcours: <?php echo $f->docs_parcours_id[1][0]; ?>,
									};
								<?php } else { ?>
									uploadExtraData: function() {
											return {
												parcours: 1
											};
										<?php } ?>

										},
										maxFileSize: 2000,
										maxFileCount: 5
								}).on("filebatchselected", function(event, files) {
							// trigger upload method immediately after files are selected
							input_fps.fileinput("upload");
						});

						function check_pattern(value, champ, numparc, id) {

							if ($('#aff_critere_ul_' + champ + '_' + numparc + '_' + id + ':visible').length == 1) {

								var pattern = /(((^[a-zA-Záàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ]+\([0-9]+\))|(^[a-zA-Záàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ]+\([\*]{1}\)))((;[a-zA-Záàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ]+\([0-9]+\))|(;[a-zA-Záàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ]+\([\*]{1}\)))*)/;
								if (pattern.exec(value)) {
									test = pattern.exec(value);
									$('#epre_parcours_critere_' + champ + '\\[' + numparc + '\\]\\[' + id + '\\]').val(test[0]);
									return false;
								} else {
									alert('Séparez les critères par des ";" et indiquez la quantité disponible entre parenthèse (mettre une "*" pour illimité)');
									return false;
								}
							} else {

								var pattern = /^(([a-zA-Z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ\/]+)(;[a-zA-Z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ\/]+)*)/;
								if (!pattern.exec(value)) {
									alert('Séparez les critères par des ";"</br>Gestion des quantités non disponible pour ce type de champ');
									return false;
								}
							}

						}

						function Create_Modal_Iframe(titre, id_parcours, url, height, width) {
							height = 750;
							content = '<div id="modal_champs_' + id_parcours + '" class="modal">';

							content += '				<div class="modal-dialog" style="width:1000px;"><a class="modal-close close" href="#"></a>';
							content += '					<div class="modal-content" >';
							content += '						<div class="modal-body" id="return_resultat" style="width:100%;height:' + (height - 170) + 'px">';
							content += '	<div class="col-sm-12 col-centered">';
							content += '<iframe src="' + url + '" scrolling="yes" frameBorder="0" width="100%" height="' + (height - 200) + 'px" allowfullscreen="allowfullscreen" ></iframe>';
							content += '	</div>';
							content += '						</div>';
							content += '						<div class="modal-footer">';
							content += '							<a data-dismiss="modal" class="btn btn-sm btn-default" href="javascript:;" ><strong>Revenir à la fiche epreuve</strong></a>';
							content += '						</div>';
							content += '					</div>';
							content += '				</div>';
							content += '</div>';

							$('#affichage_modal').append(content);
						}

						function affiche_modal(titre, id_parcours, url) {

							$('#affichage_modal').html('');

							Create_Modal_Iframe(titre, id_parcours, url, $(window).height(), $(window).width());

							$('#modal_champs_' + id_parcours).modal({
								open: true,
								modal: true,
							});
						}

						function email_obligatoire(value, id) {
							console.log(value + ' - ' + id);
							if (value === true) {

								$('#epre_inscr_email').attr('data-parsley-group', 'wizard-step-3');
								$('#epre_inscr_email').prop('required', true);
								$('#epre_inscr_email').val('<?php echo $f->inscr_email ?>');
								$('#epre_inscr_email').addClass('parsley-error');
							} else {
								$('#epre_inscr_email').attr('data-parsley-group', '');
								$('#epre_inscr_email').prop('required', false);
								$('#epre_inscr_email').val('');
								$('#parsley-id-' + id).hide();
								$('#epre_inscr_email').removeClass('parsley-error');
							}
						}

						function check_condition(value) {
							if (value == true) {
								$('#enre_epreuve').show();
							} else {
								$('#enre_epreuve').hide();
							}

						}

						function remove(id) {
							$('#' + id).remove();
						}

						function send_resultats(idEpreuve, id) {
							$.ajax({
								type: 'POST',
								dataType: 'json',
								url: 'includes/ajaxEnvoiResultats.php',
								data: 'idEpreuve=' + idEpreuve + '&id=' + id,
								success: function(data) {
									if (data.envoi == 'ok')
										notification('Notification', 'l\'email a bien été envoyé', 5000, 'ok');
									else
										notification('Notification', 'l\'email n\'a pas été envoyé<br>Vérifiez l\'adresse email et si les résultats sont en ligne', 5000, 'ko')
								},
								error: function(xhr, ajaxOptions, thrownError) {}
							});
						}

						function notification(title, text, time, image) {
							$.gritter.add({
								title: title,
								text: text,
								time: time,
								image: 'assets/plugins/gritter/images/' + image + '.png'
							});
							return false;
						}

						function show_nsi(value) {
							if (value == false) {
								$('#epre_nsi_nouveau').show();
								$('#epre_nsi_nouveau_2').show();
								$('#epre_nsi_ancien').hide();
								$('#epre_nsi_ancien_2').hide();
							} else {
								$('#epre_nsi_nouveau').hide();
								$('#epre_nsi_nouveau_2').hide();
								$('#epre_nsi_ancien').show();
								$('#epre_nsi_ancien_2').show();
							}

						}
	</script>
</body>

</html>