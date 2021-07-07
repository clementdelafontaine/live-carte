<?php 

require_once("includes/includes.php");
global $mysqli;
if ($_SESSION["typeInternaute"] == 'admin' || $_SESSION["typeInternaute"] == 'super_organisateur') $admin=1;

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

function tronque_texte ($chaine,$max) {

global $mysqli;
	if (strlen($chaine) >= $max)
	{
	$chaine = substr($chaine, 0, $max);
	$espace = strrpos($chaine, " ");
	$chaine = substr($chaine, 0, $espace)."...";
	}

return $chaine;
}
function checkdir_temp_file($id_session) {
	
	global $mysqli;

$cpt = 0;
$dir ="dl/";


	if (is_dir($dir)) {
		
	
		// si il contient quelque chose
		if ($dh = opendir($dir)) {
	
			// boucler tant que quelque chose est trouve
			while (($file = readdir($dh)) !== false) {
	
				// affiche le nom et le type si ce n'est pas un element du systeme
				
				if( $file != '.' && $file != '..' && preg_match('#\.(png|jpg|gif|pdf)$#i', $file)) {

						$filename = $type_filename."-".$_SESSION['unique_id_session'];
					}
					
					if(preg_match('#'.$filename.'#', $file)){
					
						unlink($dir.$file);
					}
			}

			closedir($dh);
		}
	}
		
}

checkdir_temp_file($_SESSION['unique_id_session']);
	
function dateen2fr($mydate,$wtime=0){

global $mysqli;
   if ($wtime == 0) {
		@list($date,$horaire)=explode(' ',$mydate);
		@list($annee,$mois,$jour)=explode('-',$date);
		@list($heure,$minute,$seconde)=explode(':',$horaire);
		return @date('d/m/Y H:i',strtotime($mois."/".$jour."/".$annee." ".$heure.":".$minute));
   }
   else
   {
		@list($annee,$mois,$jour)=explode('-',$mydate);
		return @date('d/m/Y',strtotime($mois."/".$jour."/".$annee));
   }
   
}

function select_pays_internaute ($pays_internaute) {

global $mysqli;	
	//echo 		$pays_internaute;		
	$query_pays  = "SELECT nom_fr_fr as nom_pays FROM `pays` ORDER by nom_fr_fr ASC ";	
															
	$result_pays = $mysqli->query($query_pays);
	$aff = '';
	
	while($row_pays = mysqli_fetch_array($result_pays)) {
	
		$aff .= '<option value="'.$row_pays['nom_pays'].'" ';
		if ($pays_internaute == $row_pays['nom_pays']) { $aff .= "selected"; } 
		//elseif ($row_pays['nom_pays']=='France') { $aff .= "selected"; }
		$aff .=" >".$row_pays['nom_pays']."</option>";
	}

	//echo $aff;
	return $aff;	
}

//**** check si participants déja inscrits ****//
function select_participant_tarif ($idEpreuveParcoursTarif)
{ 
	global $mysqli;
	$champs=array();
	
	$query = "SELECT count(idInscriptionEpreuveInternaute) FROM r_inscriptionepreuveinternaute WHERE idEpreuveParcoursTarif = ".$idEpreuveParcoursTarif." AND paiement_type IN ('GRATUIT','AUTRE','CB','CHQ') ";
	$result = $mysqli->query($query);
	$row=mysqli_fetch_array($result);
	if (!empty($row[0])) return $row[0]; else return 0;
	
}

function tarif_reduc_place($id_tarif)
{		
	global $mysqli;
	$tar = array();
	
	$query_nb = "SELECT count(idInscriptionEpreuveInternaute) as nb_deja_pris FROM r_inscriptionepreuveinternaute WHERE  idEpreuveParcoursTarif = ".$id_tarif." AND place_promo = 1";
	$result_nb = $mysqli->query($query_nb);
	$row_nb=mysqli_fetch_array($result_nb);
	
	$query  = "SELECT reduction,nb_dossard, nb_dossard_pris,tarif, reduction ";
	$query .= "FROM r_epreuveparcourstarif";
	$query .=" WHERE idEpreuveParcoursTarif = ".$id_tarif;
	$result_tarifs = $mysqli->query($query);
	$q1=mysqli_fetch_array($result_tarifs);
	if ($q1['nb_dossard']-$row_nb['nb_deja_pris'] <=0) $q1['reduction']=0;
	$tar['tarif'] = $q1['tarif']-$q1['reduction'];
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
	<title>Ats Sport | Informations sur le(s) parcours</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<meta content="" name="description" />
	<meta content="" name="author" />
	
    <?php echo "</BR>header_css";
	require_once ("includes/header_css_js_base.php"); ?>
	
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
		<?php include ("includes/header.php"); ?>
		<!-- end #header -->
		<!-- begin #sidebar (menu) -->
		<?php include ("includes/sidebar.php"); ?>
		<!-- end #sidebar (menu) -->
		
		<!-- begin #content -->
		<div id="content" class="content">
			<!-- begin breadcrumb -->
			<ol class="breadcrumb pull-right">
				<li><a href="javascript:;">Accueil</a></li>
				<li class="active">Informations sur le(s) parcours</li>
			</ol>
			<!-- end breadcrumb -->
			<!-- begin page-header -->
			<h1 class="page-header">Informations sur le(s) parcours</h1>
			<!-- end page-header -->
<?php 

//echo "totot".$_SESSION["log_id"]; 

		global $_POST;
		global $parametre;
	
		//purge des fichiers temporaires restants
		$query_del  = "DELETE FROM r_fichier_epreuve_temp ";
		$query_del .= "WHERE id_session =  '".$_SESSION['unique_id_session']."' ";
		$result_del = $mysqli->query($query_del);
		
		
		$query="SELECT val FROM r_constant WHERE cle like 'conditionInscription'";
		$result=$mysqli->query($query);
		$condition=mysqli_fetch_array($result);
		
		$f = new stdClass();
		$f->typeepreuve = array();
		$query  = "SELECT idTypeEpreuve, nomTypeEpreuve ";
		$query .= "FROM r_typeepreuve;";
		$result = $mysqli->query($query);
		array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
		while (($row=mysqli_fetch_array($result)) != FALSE)
			$f->typeepreuve[$row['idTypeEpreuve']] = $row['nomTypeEpreuve'];

		$f->typeparcours = array();
		$query  = "SELECT idTypeParcours, nomTypeParcours, idTypeEpreuve ";
		$query .= "FROM r_typeparcours ORDER BY idTypeEpreuve,idTypeParcours;";
		$result = $mysqli->query($query);
		array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
		while (($row=mysqli_fetch_array($result)) != FALSE)
		{
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
		/*** JEFF **/
		$f->heure_min_debut_tarif[$j][$jj] = "";
		$f->heure_min_fin_tarif[$j][$jj] = "";
		/*** JEFF **/
		$f->heure_debut_tarif[$j][$jj] = "";
		$f->min_debut_tarif[$j][$jj] = "";
		$f->date_fin_tarif[$j][$jj] = "";
		$f->heure_fin_tarif[$j][$jj] = "";
		$f->min_fin_tarif[$j][$jj] = "";
		$f->parc_prix[$j][$jj] = "";
		$f->parc_prix_nb_dossard[$j][$jj] = "";
		$f->parc_places_nb_dossard[$j][$jj] = "";
		$f->parc_places_nb_dossard_restantes[$j][$jj] = "";
		$f->parc_descprix[$j][$jj] = "";
		$f->departement = "";
		$f->nbparticipant = "";
		$f->structurelegale = "";
		$f->siteinternet = "";
		$f->siteFacebook= "";
		$f->siteTwitter= "";
		$f->inscr_contact = "";
		$f->inscr_tel = "";
		$f->inscr_email = "";
		$f->inscr_email_recevoir = "";
		$f->inscr_debut = date("d/m/Y H:m");
		$f->inscr_fin = date("d/m/Y H:m");
		$f->inscr_fin_heure = "";
		$f->inscr_fin_min = "";
		$f->description = "Historique, dénivelé, ambiance, accès, douche, repas...";
		$f->reglement = "";
		$f->reglement_url = "";
		$f->reglement_texte = "";
		$f->reglement_fichier = "";
		$f->modele_auto_parentale = "";
		$f->modele_auto_parentale_url = "";
		$f->modele_auto_parentale_texte = "";
		$f->modele_auto_parentale_fichier = ""; 
		
		$f->epreuve_image_de_fond = "";
		$f->epreuve_image_de_fond_url = "";
		$f->epreuve_image_de_fond_texte = "";
		$f->epreuve_image_de_fond_fichier = ""; 
		$f->epreuve_panel_couleur = '';
		
		$f->ville = "";
		$f->pays = "France";
		$f->siteetlieu = "";
		$f->paiement_cb = 0;
		$f->paiement_cheque = 0;
		$f->devis_chrono = 0;
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
		
		$f->idEpreuve = '';
		$f->repertoire_docs = 'fichiers_epreuves/';
		
		$f->nb_photo_epreuve = 1;
		$f->photo_epreuve = '';
		$f->photo_epreuve_affichage = '';
		$f->photo_epreuve_rep = '';
		$f->url_del_photo_epreuve = '';
		
		$f->nb_docs_epreuve = 10;
		$f->docs_epreuve = array();
		$f->docs_epreuve_affichage = array();
		$f->docs_epreuve_rep = array();
		$f->url_del_docs_epreuve = array();

		$f->nb_docs_parcours = 5;
		$f->docs_parcours = array();
		$f->docs_parcours_affichage = array();
		$f->docs_parcours_rep = array();
		$f->url_del_docs_parcours = array();
		
		$f->visible_liste_inscrit[$j] ='oui';
		$f->email_resultats = array();

		//JEFF
		//MODIFICATION
		if (isset($_POST['epre_id']) || isset($_GET['epre_id']))
		{
			if (isset($_POST['epre_id']))
			{
				$epre_id = isset($_POST['epre_id'])?$_POST['epre_id']:"";
				$query  = "SELECT e.idTypeEpreuve, e.nomEpreuve, e.dateEpreuve, e.nombreParcours, e.departement, ";
				$query .= "e.idInternaute, e.nbParticipantsAttendus, e.nomStructureLegale, e.siteInternet, e.siteFacebook, e.siteTwitter,";
				$query .= "e.contactInscription, e.telInscription, e.emailInscription, e.dateDebutInscription, ";
				$query .= "e.dateFinInscription, e.description, e.reglement, e.ville, e.pays, e.sitelieu, ";
				$query .= "e.referencer, e.urlImage, e.dateInscription, e.paiement_cb, e.payeur, e.cout_paiement_cb, e.devisChrono, e.administrateur, e.CMPCOD_FFA , e.webservice_FFTRI, cat_annee, e.insc_dossard_dernier, e.insc_aff_place_restante, e.periode_reversement_inscriptions, e.chrono_ats_sport, ";
				$query .= "i.loginInternaute, i.passInternaute, i.nomInternaute, i.prenomInternaute, i.telephone, i.emailInternaute ";
				$query .= "FROM r_epreuve as e JOIN r_internaute as i ON e.idInternaute = i.idInternaute ";
				$query .= "WHERE idEpreuve='".$epre_id."';";
			}
			else if (isset($_GET['epre_id']))
			{
				
				$epre_id = isset($_GET['epre_id'])?$_GET['epre_id']:"";
				$query  = "SELECT e.idTypeEpreuve, e.nomEpreuve, e.dateEpreuve, e.dateFinEpreuve, e.nombreParcours, e.departement, ";
				$query .= "e.idInternaute, e.nbParticipantsAttendus, e.nomStructureLegale, e.siteInternet, e.siteFacebook, e.siteTwitter,";
				$query .= "e.contactInscription, e.telInscription, e.emailInscription, e.emailinscription_recevoir, e.dateDebutInscription, ";
				$query .= "e.dateFinInscription, e.description, e.informations_epreuve, e.reglement, e.ville, e.pays, e.sitelieu, ";
				$query .= "e.referencer, e.urlImage, e.dateInscription, e.paiement_cb, e.paiement_cheque, e.coordonnees_paiement_cheque, e.infos_paiement_cheque_groupe, e.infos_paiement_IBAN_groupe, e.payeur, e.cout_paiement_cb, e.devisChrono, e.administrateur, e.liste_engage_ctrl,e.visible_calendrier, e.CMPCOD_FFA, e.webservice_FFTRI, cat_annee, e.insc_dossard_dernier, e.insc_aff_place_restante, e.periode_reversement_inscriptions, e.chrono_ats_sport,e.nsi,  ";
				$query .= "i.loginInternaute, i.passInternaute, i.nomInternaute, i.prenomInternaute, i.telephone, i.emailInternaute ";
				$query .= "FROM r_epreuve as e JOIN r_internaute as i ON e.idInternaute = i.idInternaute ";
				
				if($_SESSION['typeInternaute']=='admin') { $query .= "WHERE e.idEpreuve='".$epre_id."' AND e.administrateur=".$_SESSION['log_id'].";"; }
				elseif ($_SESSION["typeInternaute"] == 'super_organisateur') { $query .= "WHERE e.idEpreuve='".$epre_id."' AND e.super_organisateur=".$_SESSION['log_id'].";"; }
				else $query .= "WHERE e.idEpreuve='".$epre_id."' AND e.idInternaute=".$_SESSION['log_id'].";";
			}
		
			$result = $mysqli->query($query);
			array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
		
			if (($row=mysqli_fetch_array($result)) != FALSE)
			{
				$f->idEpreuve = $epre_id;
				
				//recup photo Epreuve
				$query_pe  = "SELECT nom_fichier, nom_fichier_affichage, date, type ";
				$query_pe .= "FROM r_epreuvefichier ";
				$query_pe .= "WHERE idEpreuve = ".$epre_id." ";
				$query_pe .= "AND type = 'photo_epreuve' ORDER BY idEpreuveFichier DESC LIMIT 1 ";
				$result_pe = $mysqli->query($query_pe);
				if (($row_pe=mysqli_fetch_array($result_pe)) != FALSE)
				{
					$f->photo_epreuve = $row_pe['nom_fichier'];
					$f->photo_epreuve_affichage = $row_pe['nom_fichier_affichage'];
					$f->photo_epreuve_rep = $f->repertoire_docs.$f->photo_epreuve;
					$f->url_del_photo_epreuve = 'submit_file.php?action=del&update=1&idEpreuve='.$f->idEpreuve.'&file='.$f->photo_epreuve.'&type=photo_epreuve&num_parcours=0';
				}
				
				//recup docs Epreuve
				$query_pe  = "SELECT nom_fichier, nom_fichier_affichage,date, type ";
				$query_pe .= "FROM r_epreuvefichier ";
				$query_pe .= "WHERE idEpreuve = ".$epre_id." ";
				$query_pe .= "AND type = 'docs_epreuve'";

				$result_pe = $mysqli->query($query_pe);
				while (($row_pe=mysqli_fetch_array($result_pe)) != FALSE)
				{
					$ext = explode('.', $row_pe['nom_fichier']);
					$extension = $ext[1];
					
					if ($extension == 'pdf') { $img_src="images/".$extension.".png"; } else { $img_src=$f->repertoire_docs.$row_pe['nom_fichier']; }
					
					$f->docs_epreuve[] = $row_pe['nom_fichier'];
					$f->docs_epreuve_affichage[] = $row_pe['nom_fichier_affichage'];
					$f->docs_epreuve_rep[] = $img_src;
					$f->url_del_docs_epreuve[] = 'submit_file.php?action=del&update=1&idEpreuve='.$f->idEpreuve.'&file='.$row_pe['nom_fichier'].'&type=docs_epreuve&num_parcours=0';
					
				}
				
				$dateEpreuve = explode("-", $row["dateEpreuve"]);
				$dateDebut = explode("-", $row["dateDebutInscription"]);
				$dateFinDateTime = explode(" ", $row["dateFinInscription"]);
				$timeFin = explode(":", $dateFinDateTime[1]);
				
				//recupération du reglement
				list($type_reglement, $valeur_reglement) = explode ('|||',$row['reglement']);

				if ($type_reglement == 1 ) { $f->reglement_url = $valeur_reglement; }
				elseif ($type_reglement == 2) { $f->reglement_texte = $valeur_reglement; }
				else {
				
					//recup doc reglement
					$query_pe  = "SELECT nom_fichier, nom_fichier_affichage, date, type ";
					$query_pe .= "FROM r_epreuvefichier ";
					$query_pe .= "WHERE idEpreuve = ".$epre_id." ";
					$query_pe .= "AND type = 'docs_reglement'";
					$result_pe = $mysqli->query($query_pe);
					if (($row_pe=mysqli_fetch_array($result_pe)) != FALSE)
					{
						$img_src="images/pdf.png";
						$f->reglement_fichier = $row_pe['nom_fichier'];
						$f->reglement_fichier_affichage = $row_pe['nom_fichier_affichage'];
						$f->reglement_fichier_rep = $img_src;
						$f->url_del_reglement_fichier= 'submit_file.php?action=del&update=1&idEpreuve='.$f->idEpreuve.'&file='.$f->reglement_fichier.'&type=docs_reglement&num_parcours=0';
					}
			
				}
					//recup modele_auto_parentale
					$query_pe  = "SELECT nom_fichier, nom_fichier_affichage, date, type ";
					$query_pe .= "FROM r_epreuvefichier ";
					$query_pe .= "WHERE idEpreuve = ".$epre_id." ";
					$query_pe .= "AND type = 'docs_parentale'";
					$result_pe = $mysqli->query($query_pe);
					if (($row_pe=mysqli_fetch_array($result_pe)) != FALSE)
					{
						$img_src="images/pdf.png";
						$f->modele_auto_parentale_fichier = $row_pe['nom_fichier'];
						$f->modele_auto_parentale_fichier_affichage = $row_pe['nom_fichier_affichage'];
						$f->modele_auto_parentale_fichier_rep = $img_src;
						$f->url_del_modele_auto_parentale_fichier= 'submit_file.php?action=del&update=1&idEpreuve='.$f->idEpreuve.'&file='.$f->modele_auto_parentale_fichier.'&type=docs_reglement&num_parcours=0';
					}
					
					
					//recup panel color
					$query_pc  = "SELECT panel_color, image_fond ";
					$query_pc .= "FROM r_epreuveperso  ";
					$query_pc .= "WHERE idEpreuve = ".$epre_id." ";
					$result_pc = $mysqli->query($query_pc);
					$row_pc=mysqli_fetch_array($result_pc);
					$f->epreuve_panel_couleur = $row_pc['panel_color'];
					
					//recup image_fond_epreuve
					$query_pe  = "SELECT nom_fichier, nom_fichier_affichage, date, type ";
					$query_pe .= "FROM r_epreuvefichier ";
					$query_pe .= "WHERE idEpreuveFichier = ".$row_pc['image_fond']." ";
					$query_pe .= "AND type = 'photo_insc_fond'";
					$result_pe = $mysqli->query($query_pe);
					
					if (($row_pe=mysqli_fetch_array($result_pe)) != FALSE)
					{
						//$img_src="images/jpg.png";
						$f->epreuve_image_de_fond_fichier = $row_pe['nom_fichier'];
						$f->epreuve_image_de_fond_fichier_affichage = $row_pe['nom_fichier_affichage'];
						$f->epreuve_image_de_fond_fichier_rep = $f->repertoire_docs.$f->epreuve_image_de_fond_fichier;
						$f->url_del_epreuve_image_de_fond_fichier= 'submit_file.php?action=del&update=1&idEpreuve='.$f->idEpreuve.'&file='.$f->epreuve_image_de_fond_fichier.'&type=docs_reglement&num_parcours=0';
					}
				
				$f->nom						= sql_to_form($row['nomEpreuve']);
				$f->epre_date				= sql_to_form(dateen2fr($row['dateEpreuve'],1));
				$f->epre_date_fin			= sql_to_form(dateen2fr($row['dateFinEpreuve'],1));
				$f->nbparc					= $row['nombreParcours'];
				$f->epre_organisateur		= $row['idInternaute'];
				$f->selected_typeepreuve	= $row['idTypeEpreuve'];
				$f->departement				= $row['departement'];
				$f->nbparticipant			= sql_to_form($row['nbParticipantsAttendus']);
				$f->structurelegale			= sql_to_form($row['nomStructureLegale']);
				$f->siteinternet			= sql_to_form($row['siteInternet']);
				$f->siteFacebook			= sql_to_form($row['siteFacebook']);
				$f->siteTwitter				= sql_to_form($row['siteTwitter']);
				$f->inscr_contact			= sql_to_form($row['contactInscription']);
				$f->inscr_tel				= sql_to_form($row['telInscription']);
				$f->inscr_email				= sql_to_form($row['emailInscription']);
				$f->inscr_email_recevoir	= $row['emailinscription_recevoir'];
				$f->inscr_debut				= sql_to_form(dateen2fr($row['dateDebutInscription'],1));	
				$f->inscr_fin 				= sql_to_form(dateen2fr($row['dateFinInscription']));
				$f->inscr_fin_min			= $timeFin[1];
				$f->inscr_fin_heure			= $timeFin[0];
				$f->description				= sql_to_form($row['description']);
				$f->informations_epreuve	= sql_to_form($row['informations_epreuve']);
				$f->ville					= sql_to_form($row['ville']);
				$f->pays					= sql_to_form($row['pays']);
				$f->siteetlieu				= sql_to_form($row['sitelieu']);
				$f->reference				= $row['referencer'];
				$f->urlImage				= $row['urlImage'];
				$f->paiement_cb				= $row['paiement_cb'];
				$f->paiement_cheque			= $row['paiement_cheque'];
				$f->epre_coordonnees_cheque = $row['coordonnees_paiement_cheque'];
				$f->epre_info_paiement_cheque_groupe = $row['infos_paiement_cheque_groupe'];
				$f->epre_info_paiement_iban_groupe = $row['infos_paiement_IBAN_groupe'];
				$f->epre_coordonnees_cheque = $row['coordonnees_paiement_cheque'];
				$f->payeur					= $row['payeur'];
				$f->epre_payeur_cout_cb		= $row['cout_paiement_cb'];
				$f->devis_chrono			= $row['devisChrono'];
				$f->loginorga				= $row['loginInternaute'];
				$f->passorga				= $row['passInternaute'];
				$f->nomorga					= $row['nomInternaute'];
				$f->prenomorga				= $row['prenomInternaute'];
				$f->telorga					= $row['telephone'];
				$f->emailorga				= $row['emailInternaute'];
				$f->epre_liste_engage_ctrl	= $row['liste_engage_ctrl'];
				$f->epre_visible_calendrier	= $row['visible_calendrier'];
				$f->epre_nsi				= $row['nsi'];
				$f->periode_reversement_inscriptions = $row['periode_reversement_inscriptions'];
				$f->chrono_ats_sport 		= $row['chrono_ats_sport'];

				//FFA
				$f->epre_CMPCOD_FFA	= $row['CMPCOD_FFA'];
				//FFA
				
				//FFTRI
				$f->epre_CMPCOD_FFTRI	= $row['webservice_FFTRI'];
				//FFTRI
				
				$f->epre_cat_annee = $row['cat_annee'];
				$f->epre_insc_dossard_dernier = $row['insc_dossard_dernier'];
				$f->epre_insc_aff_place_restante = $row['insc_aff_place_restante'];
				if(isset($_SESSION['log_root']) && $row['administrateur'] != '')
				{
					$qadmin  = "SELECT i.nomInternaute, i.prenomInternaute, i.telephone, i.emailInternaute ";
					$qadmin .= "FROM r_internaute AS i JOIN r_epreuve AS e ON i.idInternaute = e.administrateur WHERE e.administrateur = ".$row['administrateur']." AND e.idEpreuve = ".$epre_id."";
					$radmin  = $mysqli->query($qadmin);
					$rowadmin = mysqli_fetch_array($radmin);
					$f->nomadmin	= $rowadmin['nomInternaute'];
					$f->prenomadmin	= $rowadmin['prenomInternaute'];
					$f->teladmin	= $rowadmin['telephone'];
					$f->emailadmin	= $rowadmin['emailInternaute'];
				}
				
				$tab_id = array();				
				$tab_tarif = array();
				$query  = "SELECT idEpreuveParcoursTarif, idEpreuveParcours, desctarif, tarif, dateDebutTarif, dateFinTarif,nb_dossard,nb_dossard_pris,reduction ";
				$query .= "FROM r_epreuveparcourstarif ";
				$query .= "WHERE idEpreuve='".$epre_id."' ";
				$query .= "ORDER BY idEpreuveParcoursTarif;";
				$result = $mysqli->query($query);
				array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
				while (($row=mysqli_fetch_array($result)) != FALSE)
				{
					$tab_tarif[$row['idEpreuveParcours']][$row['idEpreuveParcoursTarif']] = array(sql_to_form($row['desctarif']), $row['tarif'], $row['dateDebutTarif'], $row['dateFinTarif'], $row['nb_dossard'], $row['nb_dossard_pris'], $row['reduction']);
				}

				//JEFF Docs_Parcours	
				$tab_docs_parcours = array();
				$query  = "SELECT nom_fichier, 	nom_fichier_affichage, idEpreuveParcours, date, type, idEpreuveFichier  ";
				$query .= "FROM r_epreuvefichier ";
				$query .= "WHERE idEpreuve='".$epre_id."' ";
				$query .= "AND type= 'docs_parcours' ";
				$query .= "ORDER BY idEpreuveFichier;";
				//echo $query;
				$result = $mysqli->query($query);
				array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
				while (($row=mysqli_fetch_array($result)) != FALSE)
				{
					$tab_docs_parcours[$row['idEpreuveParcours']][$row['idEpreuveFichier']] = array(sql_to_form($row['nom_fichier']), $row['date'], $row['type'],sql_to_form($row['nom_fichier_affichage']));
				}

				//Table Code Promo
				$tab_code_promo_id = array();				
				$tab_code_promo = array();
				
				$query  = "SELECT * ";
				$query .= "FROM r_epreuveparcourstarifpromo ";
				$query .= "WHERE idEpreuve='".$epre_id."' ";
				$query .= "ORDER BY idEpreuveParcoursTarifPromo;";

				$result = $mysqli->query($query);
				array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
				if ($result != FALSE)
				{
					while (($row=mysqli_fetch_array($result)) != FALSE)
					{
						$tab_code_promo[$row['idEpreuveParcours']] = array($row['idEpreuveParcoursTarifPromo'], sql_to_form($row['nom']), $row['label'], $row['numerotation'], $row['nb_fois_utilisable'], $row['dateDebutTarifPromo'], $row['dateFinTarifPromo'], $row['prix_reduction']);
					}
				}
				
				
				$query  = "SELECT idEpreuveParcours, idTypeParcours, nomParcours, nbtarif, horaireDepart, dossardDeb, dossardFin, nbexclusion, dossards_exclus, ordre_affichage, relais, min_relais, ageLimite, age, ParcoursDescription, certificatMedical, dossard_equipe, certificatMedicalObligatoire, date_max_depose_certif, autoParentale, infoParcoursInscription, visible_liste_inscrit ";
				$query .= "FROM r_epreuveparcours ";
				$query .= "WHERE idEpreuve='".$epre_id."' ";
				$query .= "ORDER BY idEpreuveParcours;";

				$result = $mysqli->query($query);
				array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
				$nb_parcours =  mysqli_num_rows($result); 

				$j = 0;
				while (($row=mysqli_fetch_array($result)) != FALSE)
				{
					$j++;
					$tab_id[$j][0] 				= $row['idEpreuveParcours'];
					$tab_code_promo_id[$j][0]   			= $row['idEpreuveParcours'];
					$tab_docs_parcours[$j][0]				= $row['idEpreuveParcours'];
					$f->parc_id[$j]			= $row['idEpreuveParcours'];
					if ($nb_parcours > 5) $length_aff_parcours = 10; else $length_aff_parcours = 60;
					
					if ($j==1) $f->parc_nom[$j]	= $row['nomParcours']; else $f->parc_nom[$j] = addslashes_form_to_sql($row['nomParcours']);
					$f->parc_dossard[$j]		= $row['dossardDeb'];
					$f->parc_dossardFin[$j]		= $row['dossardFin'];
					$f->parc_dossardExclus[$j]	= $row['dossards_exclus'];
					$f->parc_age_debut[$j]			= $row['age'];
					$f->parc_age_fin[$j]			= $row['ageLimite'];
					$f->parc_type[$j]			= $row['idTypeParcours'];
					$f->parc_ordre[$j]			= $row['ordre_affichage'];
					$f->parc_date[$j] = dateen2fr($row['horaireDepart']);
					$f->parc_heure[$j]			= $heure[0];
					$f->parc_min[$j]			= $heure[1];
					$f->parc_nbprix[$j]			= $row['nbtarif'];
					$f->parc_nbexclusion[$j]	= $row['nbexclusion'];
					//JEFF
					$f->parc_dossardExclus[$j] = $row['dossards_exclus'];
					if ($j==1) $f->parcours_description[$j] = str_replace(array("\r\n", "\n", "\r"), ' ', $row['ParcoursDescription']); else $f->parcours_description[$j] = addslashes(str_replace(array("\r\n", "\n", "\r"), ' ', $row['ParcoursDescription']));
					
					if ($j==1) $f->info_complementaire_parcours[$j] = str_replace(array("\r\n", "\n", "\r"), ' ', $row['infoParcoursInscription']); else $f->info_complementaire_parcours[$j] = addslashes(str_replace(array("\r\n", "\n", "\r"), ' ', $row['infoParcoursInscription']));
					//JEFF
					$f->relais[$j] 				= $row['relais'];
					$f->relais_min[$j] 				= $row['min_relais'];
					$f->dossard_equipe[$j] 				= $row['dossard_equipe'];
					$f->certif_medical[$j] 				= $row['certificatMedical'];
					$f->certif_medical_obligatoire[$j]	= $row['certificatMedicalObligatoire'];
					if (!empty($row['date_max_depose_certif']))	$f->parc_max_date_certif[$j] = dateen2fr($row['date_max_depose_certif']);
					$f->auto_parentale[$j] 				= $row['autoParentale'];
					$f->visible_liste_inscrit[$j] 				= $row['visible_liste_inscrit'];
					$jj = 0;
					
					//promo
					$tab_code_promo_id[$j][1] = $tab_code_promo[$row['idEpreuveParcours']][0];
					$f->code_promo_nom[$j] = $tab_code_promo[$row['idEpreuveParcours']][1];
					$f->code_promo_label[$j] = $tab_code_promo[$row['idEpreuveParcours']][2];
					$f->code_promo_nb_fois_utilisable[$j] = $tab_code_promo[$row['idEpreuveParcours']][4];
					$tmp_numérotation = explode(",",$tab_code_promo[$row['idEpreuveParcours']][3]);
						$f->code_promo_numerotation_depart[$j] = $tmp_numérotation[0];
						$f->code_promo_numerotation_arrivee[$j] = $tmp_numérotation[1];
					
					$f->code_promo_date_debut[$j] = dateen2fr($tab_code_promo[$row['idEpreuveParcours']][5]);
					$f->code_promo_date_fin[$j] = dateen2fr($tab_code_promo[$row['idEpreuveParcours']][6]);
					$f->code_promo_prix_reduction[$j]  = $tab_code_promo[$row['idEpreuveParcours']][7];

					//docs_parcours
					$jj = 0;
					foreach ($tab_docs_parcours[$row['idEpreuveParcours']] as $k=>$i)
					{
						$ext = explode('.', $i[0]);
						$extension = $ext[1];
						if ($extension == 'pdf') { $img_src="images/".$extension.".png"; } else { $img_src=$f->repertoire_docs.$i[0]; }
						
						
						$f->docs_parcours_id[$j][$jj] = $row['idEpreuveParcours'];
						$f->docs_parcours[$j][$jj] = $i[0];
						$f->docs_parcours_affichage[$j][$jj] = $i[3];
						$f->docs_parcours_rep[$j][$jj] = $img_src;
						$f->url_del_docs_parcours[$j][$jj] = 'submit_file.php?action=del&update=1&idEpreuve='.$f->idEpreuve.'&file='.$i[0].'&type=docs_parcours&num_parcours='.$row['idEpreuveParcours'];
						$jj++;
					}
					
					
					$jj = 0;
					foreach ($tab_tarif[$row['idEpreuveParcours']] as $k=>$i)
					{

						$jj++;
						$tab_id[$j][1][$jj] = $k;
						$f->parc_tarif_inscrit[$j][$jj] = select_participant_tarif($k);
						
						if ($f->parc_tarif_inscrit[$j][$jj] > 0 ) 
						{ 
							$f->parc_tarif_inscrit_aff[$j][$jj]='readonly'; 
						}
						else 
						{
							
							$f->parc_tarif_inscrit_aff[$j][$jj]='';
							$f->parc_tarif_inscrit[$j][$jj]=0; 
						}
						//echo "k : ".$k." info : ".$f->parc_tarif_inscrit[$j][$jj]." type :".$f->parc_tarif_inscrit_aff[$j][$jj];
						$f->date_fin_tarif[$j][$jj] = dateen2fr($i[3]);
						$f->heure_fin_tarif[$j][$jj] = date("H",strtotime($i[3]));
						$f->min_fin_tarif[$j][$jj] = date("i",strtotime($i[3]));
						$f->date_debut_tarif[$j][$jj] = dateen2fr($i[2]);
						$f->heure_debut_tarif[$j][$jj] = date("H",strtotime($i[2]));
						$f->min_debut_tarif[$j][$jj] = date("i",strtotime($i[2]));
						$f->parc_prix[$j][$jj] = $i[1];
						
						$f->parc_prix_nb_dossard[$j][$jj] = $i[6];
						$f->parc_places_nb_dossard[$j][$jj] = $i[4];
						
						$tar = tarif_reduc_place($k);
						
						$f->parc_places_nb_dossard_restantes[$j][$jj] = $tar['nb_deja_pris'];
						
						$f->parc_descprix[$j][$jj] = $i[0];
						/*** JEFF ***/
						$f->heure_min_debut_tarif[$j][$jj] = $f->heure_debut_tarif[$j][$jj].":".$f->min_debut_tarif[$j][$jj];
						$f->heure_min_fin_tarif[$j][$jj] = $f->heure_fin_tarif[$j][$jj].":".$f->min_fin_tarif[$j][$jj];
						/*** JEFF ***/
					}
					
				}

				/*
				=========================================================================
					24/01/2019 enregistrement des données de l'onglet correspondance
				=========================================================================
				*/

				$emails_correspondance = "SELECT * FROM r_epreuvecorrespondance WHERE idEpreuve=".$epre_id." ORDER BY id";
				$result = $mysqli->query($emails_correspondance);
				$nb_emails = 0;
				while( $row = mysqli_fetch_assoc($result) )
				{
					$f->email_resultats[$nb_emails]['id']			= $row['id'];
					$f->email_resultats[$nb_emails]['email']		= $row['email'];
					$f->email_resultats[$nb_emails]['public']		= $row['public'];
					$f->email_resultats[$nb_emails]['typeEnvoi']	= $row['typeEnvoi'];
					$f->email_resultats[$nb_emails]['flag']			= $row['flag'];
					$nb_emails++;
				}

				if ((isset($_POST['epre_button']) && $_POST['epre_button'] == 'Modifier la fiche de cette course') || 
					(isset($_GET['epre_button']) && $_GET['epre_button'] == 'Modifier la fiche de cette course') || 
					(isset($_GET['epre_button']) && $_GET['epre_button'] == '1'))
				{
					$modif = true;
					$_SESSION['mod_epre_id_'.$epre_id] = true;
					$_SESSION['mod_epre_ids_'.$epre_id] = $tab_id;
					$_SESSION['mod_epre_ids_code_promo_'.$epre_id] = $tab_code_promo_id;
				}
				else
				{
					$modif = false;
					unset($_SESSION['mod_epre_id_'.$epre_id]);
					unset($_SESSION['mod_epre_ids_'.$epre_id]);
					unset($_SESSION['mod_epre_ids_code_promo_'.$epre_id]);
				}
			}
			else
			{
			include ("includes/footer_js_base.php");
			?>
																	
																			<div class="modal" id="affichage_modal_exemple_0" aria-hidden="true">	
																				<div class="modal-dialog">
																					<div class="modal-content">
																						<div style="text-align: right;" class="modal-header">
																							header
																						</div>
																						<div class="modal-body">
																							<fieldset > Exemple des champs prédéfinis-</fieldset><hr>
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
		}
		else
		{
			
			
					unset($_SESSION['mod_epre_id']);
					unset($_SESSION['mod_epre_ids']);
					unset($_SESSION['mod_epre_ids_code_promo']);
					
		}
		

?>
</form>		<!-- begin row -->
    <div class="row">
        <!-- begin col-12 -->
        <div class="col-md-12"> 
            <!-- begin panel -->
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
						
						<?php if (isset($_GET['epre_id']) && ($_GET['epre_button'] != 'Modifier la fiche de cette course')) { ?>
							<a class="btn-sm m-r-5 btn-success" href="creation_epreuve.php?epre_id=<?php echo $_GET['epre_id']; ?>&epre_button=Modifier la fiche de cette course" type="button"><i class="fa fa-edit"></i> Editer cette épreuve</a>
                        <?php } 
						else if (isset($_GET['epre_id']) && ($_GET['epre_button'] == 'Modifier la fiche de cette course')) {
						?>
							<a class="btn-sm m-r-5 btn-danger" href="creation_epreuve.php?epre_id=<?php echo $_GET['epre_id']; ?>" type="button"><i class="fa fa-times"></i> Ne plus editer</a>
							<a class="btn-sm m-r-5 btn-primary" href="liste_des_inscrits_v2.php?id_epreuve=<?php echo $_GET['epre_id']; ?>&jours=tous" type="button"><i class="fa fa-users"></i> Gestion des inscriptions</a>
							<a class="btn-sm m-r-5 btn-primary" href="/epreuve.php?id_epreuve=<?php echo $_GET['epre_id']; ?>" type="button" target="_blank"><i class="fa fa-list"></i> Voir la fiche épreuve </a>
							<!--<a class="btn-sm m-r-5 btn-info" href="/inscriptions.php?id_epreuve=<?php echo $_GET['epre_id']; ?>&panel=iframe" type="button" target="_blank"><i class="fa fa-chain"></i> lien Iframe </a>/-->						
						<?php } ?>
						
                    </div>
                    <h2 class="panel-title" id="nom_epreuve"><?php if ($f->nom !='') echo $f->nom; else echo "Nouvelle épreuve"; ?></h2>
                </div>
                <div class="panel-body">
				<?php if (isset($_GET['epre_id'])) { ?>
					<form action="epreuve_submit.php?epre_id=<?php echo $_GET['epre_id']; ?>&epre_button=Modifier la fiche de cette course&modif=<?php echo $_SESSION['mod_epre_id']; ?>" method="POST" data-parsley-validate="true" name="form-wizard" class="form-horizontal" id="form-parcours" enctype="multipart/form-data">
								<script>
				</script>
				<?php }
				else { ?>
					<form  action="epreuve_submit.php" method="POST" data-parsley-validate="true" name="form-wizard" class="form-horizontal" id="form-parcours" enctype="multipart/form-data">

				<?php } //print_r($_SESSION);?>
					<input type="hidden" name="idEpreuve" value="<?php echo $_GET['epre_id']; ?>">
					<div id="wizard"> <!-- begin Wizard -->
                        <ol>
                            <li>
                                Epreuve 
                                <small>Information sur votre épreuve</small>
                            </li>
                            <li>
                                Parcours
                                <small>Renseignez les parcours</small>
                            </li>
                            <li>
                                Contact
                            </li>
                            <li>
                                Réglement
                            </li>
                            <li>
                                Inscription en ligne
                            </li>
                            <li>
                                Correspondance
                            </li>
                        <?php 	if ($_GET['epre_button'] == 'Modifier la fiche de cette course' || !isset($_GET['epre_id'])) { ?>
							<li>
                                Validation
                               </li>
						<?php } ?>
                        </ol>
							<div class="wizard-step-1"> <!-- begin wizard step-1 -->
								<fieldset>
									<!---------------------->
									<!-- Intégration Hugo -->
									<!---------------------->
                                    <legend class="pull-left width-full">Informations générales sur l'épreuve <button class="btn btn-info btn-xs m-r-5 m-b-5" type="button" data-toggle="modal" data-target="#ModalHelpIG"><i class="fa fa-2x fa-question-circle"></i></button></legend>
									<!-- Modal Aide à l'utilisation by Hugo -->
									<div class="modal fade" id="ModalHelpIG" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
									  <div class="modal-dialog modal-m" role="document">
										<div class="modal-content">
										  <div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
											<h4 class="modal-title" id="myModalLabel">Aide à l'utilisation</h4>
										  </div>
										  <div class="modal-body">
											<h5 class="text-info">A quoi vont servir ces informations ?</h5>
											<br />
											<p class="text-justify">
												Elles sont nécessaires pour le référencement de votre épreuve dans notre calendrier et pour l'envoi des newsletters hebdomadaires ciblées.
												<br /><u>Assurez vous notamment de la véracité des éléments suivants :</u><br />
												<ol>
													<li>Nom de l'épreuve</li>
													<li>Type d'épreuve</li>
													<li>Date de l'évènement</li>
													<li>Ville et département</li>
												</ol>
											</p>
											<br />
											<p><i class="fa fa-2x fa-exclamation-circle"></i> N'oubliez pas d'insérer une image représentant votre épreuve</p>
											<q>Une belle image vaut parfois mieux qu'un long discours</q>
										  </div>
										</div>
									  </div>
									</div>
									<!-------------------------->
									<!-- Fin intégration Hugo -->
									<!-------------------------->
										<div class="row">	
											<div class="col-sm-5">
	
												<label><b>Photo de l'épreuve</b><br /><span style="font-size:11px;">Format conseillé : <b>A4 (596 x 842 pixels)</b> de type .jpg, .gif ou .png, 2Mo maximum</label>

												<input id="epre_photo" name="epre_photo[]" type="file" class="form-control file-loading" onchange="$('#epre_photo_').val(this.value);">
												<INPUT type="hidden" name="epre_photo_" id="epre_photo_" value="<?php echo $f->urlImage; ?>">

											</div>
											<div class="col-sm-7">
												
												<div class="form-group">
													<h4 class="col-sm-4 control-label">Nom de l'épreuve </h4>
													<div class="col-sm-8">
														<input type="text" class="form-control btn-info input-lg text-black" name="epre_nom" id="epre_nom" value="<?php echo $f->nom; ?>" data-parsley-group="wizard-step-1" required onchange="$('#nom_epreuve').html(this.value);$('#affiche_validation_nom_epreuve').html(this.value)">
													</div>
												</div>
										
												<div class="form-group">
													<label class="col-sm-4 control-label">Type d'épreuve </label>
													<div class="col-sm-8">
														<SELECT name="epre_type" id="epre_type" class="form-control">
														<?php foreach ($f->typeepreuve as $k=>$i) {
															echo "<OPTION VALUE=".$k.(($f->selected_typeepreuve == $k)?" SELECTED":"").">".$i."</OPTION>";
															 } ?>
															</SELECT>
													</div>
												</div>
										<div class="form-group">
													<label class="col-md-4 control-label">Dates de l'événement</label>
													<div class="col-md-8">
														<div class="input-group">
															<input id="date_timepicker_start" name="epre_date" value="<?php if(isset($f->epre_date)) echo $f->epre_date; ?>" type="text"  class="form-control" placeholder="Date de départ" data-parsley-group="wizard-step-1" required />
															<span class="input-group-addon"> au </span>
															<input id="date_timepicker_end" name="epre_date_fin" value="<?php echo $f->epre_date_fin; ?>" type="text"  class="form-control" placeholder="Date de fin" data-parsley-group="wizard-step-1" required />
														</div>
													</div>
												</div>
			
												<div class="form-group">
													<label class="col-sm-4 control-label">Ville </label>
													<div class="col-sm-8">
														<input name="epre_ville" id="jquery-autocomplete-ville" class="form-control txt-auto" value="<?php echo $f->ville; ?>" placeholder="Entrez le nom de votre ville"/>
													</div>
												</div>
												<div class="form-group">
													<label class="col-sm-4 control-label">Numéro du département</label>
													<div class="col-sm-2">
														<input type="text" id="epre_departement" name="epre_departement" class="form-control txt-auto" value="<?php echo $f->departement; ?>" placeholder="Ex : 85"/>
													</div>
												</div>
												
											
												<div class="form-group">
													<label class="col-sm-4 control-label">Pays</label>
													<div class="col-sm-2">
															<select class="form-control input-inline input-sm" name="epre_pays" id="epre_pays" >
																<?php if (!empty($f->pays)) { echo  select_pays_internaute($f->pays); } else { echo select_pays_internaute("France"); }?>
															</select>
													</div>
												</div>
												
												<div class="form-group">
													<label class="col-sm-4 control-label">Indications - Accès</label>
													<div class="col-sm-8">
														<input type="text" class="form-control" name="epre_siteetlieu" id="epre_siteetlieu" value="<?php echo $f->siteetlieu; ?>" maxlength=50>
													</div>
												</div>
		
												<div class="form-group">
													<label class="col-md-4 control-label">Participants attendus</label>
													<div class="col-md-8">
														<input type="text" id="default_rangeSlider" name="epre_nbparticipant" value="<?php echo $f->nbparticipant; ?>" />
													</div>
												</div>
												<?php if($admin==1)  { ?>
													<div class="form-group">
														<label class="col-md-4 control-label">Organisateur gestionnaire</label>
														<div class="col-md-8">
															<SELECT class="form-control" name="epre_organisateur" id="epre_organisateur" >
																<?php 
			
																$organisateurs=extract_organisateurs();
																?> <OPTION VALUE="<?php echo $_SESSION['log_id']; ?>"> Administrateur </OPTION> <?php
																while ($row_orga=mysqli_fetch_array($organisateurs))
																{ 
													
																?>
																	
																	<OPTION VALUE="<?php echo $row_orga['idInternaute']; ?>" <?php if ($row_orga['idInternaute'] == $f->epre_organisateur) echo "selected"; ?> ><?php echo $row_orga['nomInternaute']." ".$row_orga['prenomInternaute']." [".$row_orga['loginInternaute']."]"; ; ?></OPTION>
									
																<?php } ?>
															</SELECT><br>
															<?php echo "Identifiant : ".$f->loginorga."  -  Email : ".$f->emailorga; ?>
														</div>
													</div>
													<div class="form-group">
														<label class="col-md-4 control-label">Epreuve avec Webservice FFA</label>
														<div class="">
															<div class="form-inline col-md-2">
																<select class="form-control" name="epre_webservice_ffa">
																	<OPTION VALUE="non" > Non </OPTION>
																	<OPTION VALUE="oui" <?php if ($f->epre_CMPCOD_FFA != '') echo "selected"; else echo ""; ?>> Oui </OPTION>
																</select>
															</div>
															<div class="form-inline" id="aff_cmpcod">
																<label class="control-label">Numéro CMPCOD_FFA</label>
																<input class="input-inline form-control" id="epre_CMPCOD_FFA" name="epre_CMPCOD_FFA" value="<?php echo $f->epre_CMPCOD_FFA; ?>" type="text"  class="form-control" placeholder="" />
															</div>
														</div>
													</div>
													<div class="form-group">
														<label class="col-md-4 control-label">Epreuve avec Webservice FFTri</label>
														<div class="">
															<div class="form-inline col-md-2">
																<select class="form-control" name="epre_webservice_fftri">
																	<OPTION VALUE="non" > Non </OPTION>
																	<OPTION VALUE="oui" <?php if ($f->epre_CMPCOD_FFTRI == 'oui') echo "selected"; else echo ""; ?>> Oui </OPTION>
																</select>
															</div>
														</div>
													</div>													
													<div class="form-group">
														<label class="col-md-4 control-label">Epreuve avec catégorie prise sur l'année de naissance</label>
														<div class="">
															<div class="form-inline col-md-2">
																<select class="form-control" name="epre_cat_annee">
																	<OPTION VALUE="non" > Non </OPTION>
																	<OPTION VALUE="oui" <?php if ($f->epre_cat_annee == 'oui') echo "selected"; else echo ""; ?>> Oui </OPTION>
																</select>
															</div>
														</div>
													</div>
													
													<div class="form-group">
														<label class="col-md-4 control-label">Numérotation des dossards à la suite ? (pas de remplacement des numéros de dossards non utilisés) </label>
														<div class="">
															<div class="form-inline col-md-2">
																<select class="form-control" name="epre_insc_dossard_dernier">
																	<OPTION VALUE="non" > Non </OPTION>
																	<OPTION VALUE="oui" <?php if ($f->epre_insc_dossard_dernier == 'oui') echo "selected"; else echo ""; ?>> Oui </OPTION>
																</select>
															</div>
														</div>
													</div>
													<div class="form-group">
														<label class="col-md-4 control-label">Ne pas afficher les places restantes sur la fiche d'inscription</label>
														<div class="">
															<div class="form-inline col-md-2">
																<select class="form-control" name="epre_insc_place_restante">
																	<OPTION VALUE="non" > Non </OPTION>
																	<OPTION VALUE="oui" <?php if ($f->epre_insc_aff_place_restante == 'oui') echo "selected"; else echo ""; ?>> Oui </OPTION>
																</select>
															</div>
														</div>
													</div>															
												<?php } else { ?>
												
												<input type="hidden" value="<?php echo $_SESSION['log_id']; ?>" name="epre_organisateur">
												<input type="hidden" value="<?php echo $f->epre_CMPCOD_FFA; ?>" name="epre_CMPCOD_FFA">
												<input type="hidden" value="<?php echo $f->epre_cat_annee; ?>" name="epre_cat_annee">
												
												<?php if (isset($f->epre_CMPCOD_FFA)) { ?>
													<input type="hidden" value="oui" name="epre_webservice_ffa">  
													<div class="form-group">
														<label class="col-md-4 control-label">Epreuve avec Webservice FFA</label>
															<div class="form-inline col-md-2">
																<select class="form-control" disabled>
																	<OPTION VALUE="oui"> Oui </OPTION>
																</select>
															</div>														
														<div class="">
															<div class="form-inline" id="aff_cmpcod">
																<label class="control-label">Numéro CMPCOD_FFA</label>
																<input class="input-inline form-control" value="<?php echo $f->epre_CMPCOD_FFA; ?>" type="text"  class="form-control" disabled/>
															</div>
														</div>
													</div>
													<?php													
													} else { ?>
													<input type="hidden" value="non" name="epre_webservice_ffa"> <?php 
													} ?>
												
												<?php } ?>
											</div> <!-- End Col-7 /-->
											<div class="col-sm-12">
												<fieldset>
													<legend class="pull-left width-full">Description</legend>
													<div class="row">
														<div class="panel-body panel-form m-b-20">
															<textarea name="epre_description" class="textarea form-control" id="wysihtml5" placeholder="Historique, dénivelé, ambiance, accès, douche, repas..." rows="12"><?php echo $f->description; ?></textarea>
														</div>
													</div>
												</fieldset>
											</div>
											<div class="col-sm-12">
												<fieldset>
													<legend class="pull-left width-full">Informations complémentaires affichées sur la fiche d'inscription</legend>
													<div class="row">
														<div class="panel-body panel-form m-b-20">
															<textarea name="epre_informations_epreuve" class="textarea form-control" id="wysihtml5" placeholder="Ex: Cette épreuve n'accepte pas les inscriptions sur place." rows="12"><?php echo $f->informations_epreuve; ?></textarea>
														</div>
													</div>
												</fieldset>
											</div>
											<div class="col-sm-12">
												<fieldset>
													<legend class="pull-left width-full">Fichiers supplémentaires</legend>
													<div class="col-sm-12"> 
														<div class="panel-body panel-form">
															<div class="form-group">
																<label class="control-label">10 fichiers maximum. Formats autorisés : <b>.jpg, .gif , .png ou .pdf, 2Mo maximum</b></label>
																<input id="fichier_epreuve_sup" name="fichier_epreuve_sup[]" type="file" class="file" multiple >
															</div>
													</div>
													</div>
												</fieldset>
												
											<fieldset>
												<!---------------------->
												<!-- Intégration Hugo -->
												<!---------------------->
												<legend class="pull-left width-full">Champs supplémentaires 
													<button class="btn btn-info btn-xs m-r-5 m-b-5" type="button" data-toggle="modal" data-target="#ModalHelpCS"><i class="fa fa-2x fa-question-circle"></i></button>
												
												</legend>
												<!-- Modal Aide à l'utilisation by Hugo -->
												<div class="modal fade" id="ModalHelpCS" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
												  <div class="modal-dialog modal-m" role="document">
													<div class="modal-content">
													  <div class="modal-header">
														<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
														<h4 class="modal-title" id="myModalLabel">Aide à l'utilisation</h4>
													  </div>
													  <div class="modal-body">
														<h5 class="text-info">A quoi correspondent les champs supplémentaires ?</h5>
														<br />
														<p class="text-justify">
															Ce sont les champs ou informations ne rentrant pas dans le cadre des champs génériques type nom, prénom, sexe, etc.
															Il en existe <u>3</u> sortes que vous pourrez définir sur <b>l'ensemble de l'épreuve</b> ou sur <b>un parcours en particulier</b>.
															<br />
															<br />
															<u><b>Question Commerce</b></u>
															<br />
															Ce sont les champs ayant une notion de tarif, exemple : pasta party, tee Shirt, assurance annulation, etc<br />
															Vous pourrez leurs affecter une quantité limite et une date limite de réservation.
															<br />
															<br />
															<u><b>Champs de type dotation</b></u>
															<br />
															Ce sont les champs non tarifés ayant une notion de quantité limite. Vous pourrez leurs appliquer <b>un compteur</b>.
															<br />
															<br />
															<u><b>Champs de type questions diverses</b></u>
															<br />
															Ce sont toutes les autres informations dont vous auriez besoin ne rentrant pas dans le cadre des 2 premières catégories.
															n° de licence, personnes à prevenir en cas de problème, etc.
														</p>
													  </div>
													</div>
												  </div>
												</div>
											<!-------------------------->
											<!-- Fin intégration Hugo -->
											<!-------------------------->
											<?php 
											if ((isset($_POST['epre_button']) && $_POST['epre_button'] == 'Modifier la fiche de cette course') || 
												(isset($_GET['epre_button']) && $_GET['epre_button'] == 'Modifier la fiche de cette course') || 
												(isset($_GET['epre_button']) && $_GET['epre_button'] == '1'))
											{ ?>
												
												<p class="text-center">
													
													<a class="btn btn-info m-r-5" href="javascript:;" onclick="affiche_modal('Question Quantité',0,'https://www.ats-sport.com/<?php echo URL_DEV; ?>admin/creation_champs.php?epre_id=<?php echo $_GET['epre_id']; ?>&champ=d_epreuve')" class="btn btn-danger m-b-15 m-l-5" data-toggle="tooltip" data-placement="top" data-original-title=""><b>Question Quantité</b></br>Ex: taille/tee-shirt</a>
													<a class="btn btn-primary m-r-5" href="javascript:;" onclick="affiche_modal('Question Commerce',0,'https://www.ats-sport.com/<?php echo URL_DEV; ?>admin/creation_champs.php?epre_id=<?php echo $_GET['epre_id']; ?>&id_parcours=<?php echo $f->parc_id[1]; ?>&champ=p_epreuve')" class="btn btn-danger m-b-15 m-l-5" data-toggle="tooltip" data-placement="top" data-original-title=""><b>Question Commerce</b></br>Ex: repas, hébergement</a>
													<a class="btn btn-default m-r-5" href="javascript:;" onclick="affiche_modal('Autres Questions',0,'https://www.ats-sport.com/<?php echo URL_DEV; ?>admin/creation_champs.php?epre_id=<?php echo $_GET['epre_id']; ?>&id_parcours=<?php echo $f->parc_id[1]; ?>&champ=q_epreuve')" class="btn btn-danger m-b-15 m-l-5" data-toggle="tooltip" data-placement="top" data-original-title=""><b>Autres Questions</b></br>Ex: profession, intérêts</a>

												</p>

											<?php } else { ?>
											
											<div class="panel-body">
												<p class="text-center">

													<span class="alert alert-danger fade in m-b-15" href="javascript:;" <i class="fa fa-2x fa-money"></i><strong> Vous pourrez après enregistrement de votre épreuve ajouter des questions complémentaires.</strong></span>

												</p>
											</div>

											<?php } ?>
											</fieldset>
												
											</div>
										</div>
                                </fieldset>
							</div> <!-- end wizard step-1 -->
     
                            <div class="wizard-step-2">
								<fieldset>
                                    <legend class="pull-left width-full">Informations sur les parcours </legend>
										
								</fieldset>
                                    
									<!-- Modal Aide à l'utilisation by Hugo -->
									<div class="modal fade" id="ModalHelpILReduc" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
									  <div class="modal-dialog modal-m" role="document">
										<div class="modal-content">
										  <div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
											<h4 class="modal-title" id="myModalLabel">Aide à l'utilisation</h4>
										  </div>
										  <div class="modal-body">
											<h5 class="text-info">Nouveauté - Réduction sur les X premières places</h5>
											<br />
											<p class="text-justify">
												Désormais vous pouvez définir une réduction sur le prix de base (champs réduction) pour les x premières places (champs places max) enregistrées.
												la colonne "prises" permet de voir combien de place ont déjà été achetées. Vous pouvez ajuster à tout moment le nombre de places disponibles, égale ou supérieur au nombre de place déjà prises.
											</p>
											<p class="text-danger text-justify">Laissez ou remettez les champs vides pour ne pas faire jouer cette réduction.</p>
										  </div>
										</div>
									  </div>
									</div>
									
								<div class="panel panel-inverse panel-with-tabs">
									<div class="panel-heading p-0">
										<div class="panel-heading-btn m-r-10 m-t-10">
										</div>
										<div class="tab-overflow">
											<ul class="nav nav-tabs nav-tabs-inverse">
											<?php if ($nb_parcours > 5) { ?> 
												<li class="active" id="P1"><a href="#parcours_1" data-toggle="tab"   id="name_parcours_1"><i id="badge_parcours_1" class="badge badge-secondary" data-toggle="tooltip" data-placement="top" data-original-title="<?php if (isset($_GET['epre_id'])) echo $f->parc_nom[1]; else echo "Parcours N°1";?>">P1</i></a></li>
											<?php } else { ?>
												<li class="active" id="P1"><a href="#parcours_1" data-toggle="tab"   id="name_parcours_1"><?php if (isset($_GET['epre_id'])) echo tronque_texte($f->parc_nom[1],$length_aff_parcours); else echo "Parcours N°1";?></a></li>
											<?php  } ?>			
												<li id="ajouter_parcours" id="P2"><a href="#" class="add-parcours">Parcours +</a>
											</ul>
										</div>
									</div>
									<div class="tab-content" id="divTab">
										<div class="tab-pane fade active in" id="parcours_1">
											<input type="hidden" id="id_table_parcours[1]" name="id_table_parcours[1]" value="<?php echo $tab_id[1][0]; ?>" />
											<fieldset>
												<legend class="pull-left width-full">Informations sur le parcours</legend>
												<div class="row-margin-left">
													<div class="col-md-4">
														<div class="form-group m-r-20 input-group-lg">
															<h4>Nom de la course*</h4>

																<input type="text" name="epre_parc_nom[1]" id="epre_parc_nom1" value="<?php if ($f->parc_nom[1] != '') echo $f->parc_nom[1]; else echo "Mon premier parcours"; ?>" placeholder="Nom du parcours" class="form-control input-group-lg" data-parsley-group="wizard-step-2" onchange="$('a#name_parcours_1').text(this.value);" required />

														</div>
													</div>
													<div class="col-md-3">
														<div class="form-group m-r-20">
															<h4>Date et heure de départ </h4>
															
														<div class="input-group input-group-lg">
															<input id="date_parcours_timepicker_start_1" name="epre_parc_date[1]" value="<?php echo $f->parc_date[1]; ?>" type="text"  class="form-control" placeholder="Date de départ" />
															<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
														</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="form-group">
															<h4>Format de la course </h4>
															<div class="input-group input-group-lg">
																<?php $type = ''; ?>
																<SELECT class="form-control" name="epre_parc_type[1]" id="epre_parc_type[1]">
																	<?php foreach ($f->typeparcours as $k=>$i) { ?>
																		<?php if( $type != $f->typeepreuve[$i[1]] ): ?>
																			<optgroup label="<?php echo $f->typeepreuve[$i[1]]; ?>">
																		<?php endif; ?>

																				<OPTION VALUE="<?php echo $k; ?>"<?php if($f->parc_type[1] == $k) echo " SELECTED"; else echo ""; ?>><?php echo $i[0]; ?></OPTION>
																		
																		<?php $type = $f->typeepreuve[$i[1]]; ?>
																		<?php if( $type != $f->typeepreuve[$i[1]] ): ?>
																			</optgroup>
																		<?php endif; ?>
																	<?php } ?>
																	<OPTION VALUE='-1'<?php (($f->parc_type[1] == -1)?" SELECTED":"") ?>>Autre</OPTION>
																</SELECT>
															</div>
														</div>
													</div>
													<div class="col-md-2">
														<div class="form-group m-r-20 input-group-lg">
															<h4>Ordre d'affichage</h4>
																<input type="text" name="epre_parc_ordre[1]" id="epre_parc_ordre1" value="<?php if ($f->parc_ordre[1] != '') echo $f->parc_ordre[1]; else echo "1"; ?>" class="form-control input-group-lg" data-parsley-group="wizard-step-2" required />
														</div>
													</div>													

												</div> <!-- end row -->
												<div class="row-margin-left"> <!-- begin row -->
													<div class="form-group">
	
													<label class="col-md-2 control-label m-t-25">Tranche d'âge autorisé  : De</label>
														<div class="col-md-3 m-t-25">
															<div class="input-group">
																<select class="form-control" name="epre_parc_age_debut[1]">
																<?php for ($i=0;$i<=150;$i++) { ?>
																	<OPTION VALUE="<?php echo $i; ?>" <?php if($f->parc_age_debut[1] == $i) { echo " SELECTED"; } ?>><?php echo $i; ?></OPTION>
																<?php } ?>
																</select>
																<span class="input-group-addon">a</span>
																<select class="form-control" name="epre_parc_age_fin[1]">
																<?php for ($i=0;$i<=150;$i++) { ?>
																	<OPTION VALUE="<?php echo $i; ?>" <?php if($f->parc_age_fin[1] == $i) { echo " SELECTED"; } ?>><?php echo $i; ?></OPTION>
																<?php } ?>
																</select>
																<span class="input-group-addon"> ans</span>
															</div>
														</div>
													</div>

													<div class="form-group"><hr>
														<div class="col-md-3">
															<div class="form-inline">
																<label class="control-label">S’agit-il d’un parcours individuel ou par équipe ?</label>
																<input type="checkbox" name="relais[1]" id="relais-1" value="1" data-render="switchery" data-theme="blue" <?php if ($f->relais[1] > 0) echo "checked"; else echo ""; ?> onchange="etat_change_checkbox('relais-1'); "/>
															</div>
														</div>
														<span <?php if ($f->relais[1] > 0) echo "style='display:visible'"; else echo "style=\"display:none\""; ?> id="div-relais-1">
															<div class="col-md-2" >
																<div class="form-inline">
																	<label>Nombre de personne max?</label>
																	<select class="form-control" name="relais_nb_personne[1]">
																	<?php for ($i=2;$i<=10;$i++) { ?>
																		<OPTION VALUE="<?php echo $i; ?>" <?php if($f->relais[1] == $i) echo " SELECTED"; else echo ""; ?>><?php echo $i; ?></OPTION>
																	<?php } ?>
																	</select>
																</div>
																<div class="form-inline">
																	<label>Nombre de personne min ? (laisser 0 pour fixer le nombre de personne à celui de max</label>
																	<select class="form-control" name="relais_nb_personne_min[1]">
																	<?php for ($i=0;$i<=10;$i++) { ?>
																		<OPTION VALUE="<?php echo $i; ?>" <?php if($f->relais_min[1] == $i) echo " SELECTED"; else echo ""; ?>><?php echo $i; ?></OPTION>
																	<?php } ?>
																	</select>
																</div>
															</div>
															<div class="col-md-6" >
																<div class="form-inline">
																	<label class="control-label">Le Numéro de dossard est-il identique pour l’ensemble des équipiers ?</label>
																	<input type="checkbox" name="dossard_equipe[1]" id="relais-dossard_equipe-1" value="oui" data-render="switchery" data-theme="blue" <?php if ($f->dossard_equipe[1] == 'oui') echo "checked"; else echo ""; ?> onchange="etat_change_checkbox('relais-dossard_equipe-1');"/>
																</div>
															</div>
														</span>
													</div>
													<div class="form-group"><hr>
														<div class="col-md-5">
															<div class="form-inline">
																<label class="control-label">Les coureurs doivent-il présenter une licence ou un certificat médical ?</label>
																<input type="checkbox" name="certif_medical[1]" id="certif_medical-1" value="1" data-render="switchery" data-theme="blue" <?php if ($f->certif_medical[1] == 1) echo "checked"; else echo ""; ?> onchange="etat_change_checkbox('certif_medical-1'); etat_change_checkbox_button('certif_medical-1');"/>
															</div>
														</div>

													
														<div class="col-md-5" <?php if ($f->certif_medical[1] == 1) echo "style='display:visible'"; else echo "style=\"display:none\""; ?> id="div-certif_medical-1">
															<div class="form-inline">
																<label class="control-label">La fourniture du document est-elle obligatoire à l’inscription ?</label>
																<input type="checkbox" name="certif_medical_obligatoire[1]" id="button-certif_medical-1" value="oui" data-render="switchery" data-theme="blue" <?php if ($f->certif_medical_obligatoire[1] == 'oui') echo "checked"; else echo ""; ?>/>
															    <label class="control-label m-t-10">date et heure limite de dépôt de ce dernier sur le site web </label>
																<input class="form-control m-t-10" id="date_max_certif_timepicker_1" name="epre_parc_max_date_certif[1]" value="<?php echo $f->parc_max_date_certif[1]; ?>" type="text"  class="form-control" placeholder="Date max dépose des certificats" />

															</div>
														</div>
													</div>
													<div class="form-group"><hr>
														<div class="col-md-5">
															<div class="form-inline">
																<label class="control-label">Souhaitez vous recueillir une autorisation parentale pour les mineurs?</label>
																<input type="checkbox" name="auto_parentale[1]" id="auto_parentale-1" value="1" data-render="switchery" data-theme="blue" <?php if ($f->auto_parentale[1] == 1) echo "checked"; else echo ""; ?>/>
															</div>
														</div>
													</div>
													<div class="form-group"><hr>
														<div class="col-md-4">
															<div class="form-inline m-t-25">
																<label class="control-label">Les inscrits sont-ils visibles depuis la liste des engagés ?</label>
																<input type="checkbox" name="visible_liste_inscrit[1]" id="button-visible_liste_inscrit-1" value="oui" data-render="switchery" data-theme="blue" <?php if ($f->visible_liste_inscrit[1] == 'oui') echo "checked"; else echo ""; ?>/>
															</div>
														</div>
													</div>	
											</fieldset>
										
											<fieldset>
												<legend>Affectation des Dossards</legend>
											    
												<div class="row row-margin-left">
													<div class="form-group">
														<label class="control-label col-md-5 col-sm-5">Dossard de * :</label>
														<div class="col-md-2 col-sm-2">
															<input type="text" data-parsley-type="digits" data-parsley-group="wizard-step-2" name="parc_dossard[1]" value="<?php if ($f->parc_dossard[1] != "") echo $f->parc_dossard[1]; else echo "";?>" maxlength="10" class="form-control" />
														</div>
													</div>													
													<div class="form-group">
														<label class="control-label col-md-5 col-sm-5">à * :</label>
														<div class="col-md-2 col-sm-2">
																<input type="text" data-parsley-type="digits" data-parsley-group="wizard-step-2" name="parc_dossardFin[1]" value="<?php if ($f->parc_dossardFin[1] != "") echo $f->parc_dossardFin[1]; else echo "";?>" maxlength="10" class="form-control" />
														</div>
													</div>	

												</div> <!-- end row -->
												<div class="row row-margin-left">
													<div class="form-inline m-b-10">
														<label class="control-label">Souhaitez vous des plages d'exclusion (7 max) ? (laisser vide si vous ne souhaitez pas en mettre) </label>
														<input type="checkbox" id="plage-exclusion-1" data-render="switchery" data-theme="blue" onchange="etat_change_checkbox('plage-exclusion-1');" <?php if ($f->parc_nbexclusion[1][0] != 0) echo "checked"; else echo ""; ?>/>
													</div>
												</div>
											    <div class="row row-margin-left" id="div-plage-exclusion-1" <?php if ($f->parc_nbexclusion[1][0] != 0) echo "style='display:visible'"; else echo "style=\"display:none\""; ?>>
													<?php

													$plage_exclusion = array();
													$exclusion = array();
													
													if (isset($f->parc_dossardExclus[1])) {
														$exclusion = explode(":",$f->parc_dossardExclus[1]);
														$nb_plage_exclusion = count($exclusion);

														for ($x=0;$x<$nb_plage_exclusion;$x++) { $plage_exclusion[] = explode("-",$exclusion[$x]); }

													}

													for ($jj=1;$jj<=7;$jj++) 
													{
													?>
													<div class="form-group">
														<label class="control-label col-md-3 col-sm-3">Exclusion N°<?php echo $jj;?> >>Dossard de * :</label>
														<div class="col-md-2 col-sm-2">
															<input type="text" data-parsley-type="digits" id='parc_dossard_exclus_min[1][<?php echo $jj; ?>]' name='parc_dossard_exclus_min[1][<?php echo $jj; ?>]' value='<?php if($plage_exclusion[($jj-1)][0] != '') echo $plage_exclusion[($jj-1)][0]; else ""; ?>' maxlength='10' class="form-control" onchange="calcul_nb_dossard_exclu(1)"/>
															<input type="text"  name="parc_dossard_exclus_min_control[1][<?php echo $jj; ?>]" value="<?php if ($plage_exclusion[0] != '') echo $plage_exclusion[0]; else echo ""; ;?>" size="1" maxlength="10" style="display:none">															
														</div>
														<div class="col-md-1 col-sm-1">
														<label class="control-label">à * :</label>
														</div>
														<div class="col-md-2 col-sm-2">
																<input type="text" data-parsley-type="digits" id='parc_dossard_exclus_max[1][<?php echo $jj; ?>]' name='parc_dossard_exclus_max[1][<?php echo $jj; ?>]' value='<?php if ($plage_exclusion[($jj-1)][1] != '') echo $plage_exclusion[($jj-1)][1]; else echo ""; ?>' maxlength='10' class="form-control" onchange="calcul_nb_dossard_exclu(1)"/>
																<input type="text"  name="parc_dossard_exclus_max_control[1][<?php echo $jj; ?>]" value="<?php if ($plage_exclusion[1] != '') echo $plage_exclusion[1]; else echo ""; ;?>" size="1" maxlength="10" style="display:none">
														</div>
													</div>													
													<?php }	?>
											    </div>
												<input type="hidden" id="epre_parc_nbexclusion[1]" name="epre_parc_nbexclusion[1]" value="<?php if(isset($f->parc_nbexclusion[1])) echo $f->parc_nbexclusion[1]; else echo "0";?>">
											</fieldset>	<!-- Fin Numérotation -->

											<fieldset>
											
												<legend class="pull-left width-full">Option supplémentaires payantes
									
												</legend>

											
											<?php 
											if ((isset($_POST['epre_button']) && $_POST['epre_button'] == 'Modifier la fiche de cette course') || 
												(isset($_GET['epre_button']) && $_GET['epre_button'] == 'Modifier la fiche de cette course') || 
												(isset($_GET['epre_button']) && $_GET['epre_button'] == '1'))
											{ ?>	
												<?php //if ($admin==1) { ?>
													<p class="text-center">
														<a class="btn btn-info m-r-5" href="javascript:;" onclick="affiche_modal('Gestion des codes promos du parcours',0,'https://www.ats-sport.com/<?php echo URL_DEV; ?>admin/creation_options.php?epre_id=<?php echo $_GET['epre_id']; ?>&id_parcours=<?php echo $f->parc_id[1]; ?>')" class="btn btn-danger m-b-15 m-l-5"><b>Option tarifées supplémentaires payantes au parcours</b></a>
													</p>
												<?php //} else { ?>
												<!--
													<p class="text-center">
														<span class="alert alert-warning"><b>Veuillez contacter ATS-SPORT pour la mise en place de ces options</b></span>
													</p>
												-->
												<?php //} ?>
											
											<?php } else { ?>
											<div class="panel-body">
												<p class="text-center">

													<span class="alert alert-danger fade in m-b-15" href="javascript:;" <i class="fa fa-2x fa-money"></i><strong> Veuillez pré-enregistrer l'épreuve avant l'édition des options supplémentaires</strong></span>

												</p>
											</div>
											<?php } ?>
											
											</fieldset>
											
											<fieldset>
												<legend>Tarifs associés</legend>
												<div class="row row-margin-left m-l-10">
													<div class="form-group col-md-1 text-left">
														<label class="control-label num">N°</label>
													</div>
													<div class="form-group col-md-2 m-r-5 text-left">
														<label class="control-label num">Nom</label>
													</div>
													<div class="form-group col-md-1 m-r-5 text-left">
														<label class="control-label num">Prix</label>
													</div>
													<div class="form-group date-time-font col-md-5 m-r-5 text-left">
														<label class="control-label num">Date de départ et de fin du tarif</label>
													</div>
													<div class="form-group col-md-3 text-left">
														<label class="control-label num">Réduction / places max / prises
															<button class="btn btn-info btn-xs" type="button" data-toggle="modal" data-target="#ModalHelpILReduc">
																<i class="fa fa-question-circle"></i>
															</button>
														</label>
													</div>													

												</div>
												<?php for ($jj=1;$jj<=3;$jj++) { ?>
												<div class="row row-margin-left m-l-10">
													<div class="form-group col-md-1">
														<label class="control-label num"><?php echo $jj; ?> [ <?php echo $f->parc_tarif_inscrit[1][$jj]; ?> ]</label>
													</div>
													<div class="form-group col-md-2 m-r-5">
														<input type="text" name="epre_parc_descprix[1][<?php echo $jj; ?>]" id="epre_parc_descprix[1][<?php echo $jj; ?>]" value="<?php echo $f->parc_descprix[1][$jj]; ?>" class="form-control" placeholder="Description" onchange="calcul_nb_tarif(1);" <?php echo $f->parc_tarif_inscrit_aff[1][$jj]; ?>>
													</div>
													<div class="form-group col-md-1 m-r-5">
														<input type="text"  name="epre_parc_prix[1][<?php echo $jj; ?>]" id="epre_parc_prix[1][<?php echo $jj; ?>]" value="<?php echo $f->parc_prix[1][$jj]; ?>" class="form-control" placeholder="€" <?php echo $f->parc_tarif_inscrit_aff[1][$jj]; ?>>
													</div>
													<div class="form-group date-time-font col-md-5 m-r-5 ">
					
														<div class="input-group">
															<span class="input-group-addon"> Du </span>
															<input id="date_timepicker_start_tarifs_1_<?php echo $jj; ?>" name="date_debut_tarif[1][<?php echo $jj; ?>]" value="<?php if ($f->date_debut_tarif[1][$jj] != '') echo $f->date_debut_tarif[1][$jj];?>" type="text"  class="form-control" />
															<span class="input-group-addon"> au </span>
															<input id="date_timepicker_end_tarifs_1_<?php echo $jj; ?>" name="date_fin_tarif[1][<?php echo $jj; ?>]" value="<?php if ($f->date_fin_tarif[1][$jj] != '') echo $f->date_fin_tarif[1][$jj]; ?>" type="text"  class="form-control" />
														</div>

													</div>
		
													<div class="form-group col-md-1 m-r-5">
														<input type="text"  name="epre_parc_prix_nb_dossard[1][<?php echo $jj; ?>]" id="epre_parc_prix_nb_dossard[1][<?php echo $jj; ?>]" value="<?php echo $f->parc_prix_nb_dossard[1][$jj]; ?>" class="form-control" placeholder="€">
													</div>
													<div class="form-group col-md-1 m-r-5">
														<input type="text"  name="epre_parc_places_nb_dossard[1][<?php echo $jj; ?>]" id="epre_parc_places_nb_dossard[1][<?php echo $jj; ?>]" value="<?php echo $f->parc_places_nb_dossard[1][$jj]; ?>" class="form-control" placeholder="place(s)">
													</div>
													<div class="form-group col-md-1 m-t-5">
														<span class="control-label num"><?php echo $f->parc_places_nb_dossard_restantes[1][$jj]; ?></span>
													</div>													

												</div>
												<?php } ?>
												
													<div class="row row-margin-left m-l-10" >
														<div class="form-inline m-t-25 m-b-25">
															<label class="control-label">Encore plus de tarifs ?</label>
															<input type="checkbox" id="plus-de-tarif-1" data-render="switchery" data-theme="blue" <?php if (isset($f->parc_descprix[1][4])) echo "checked"; else echo ""; ?> onchange="etat_change_checkbox('plus-de-tarif-1');"/>
														</div>
													</div>
												<div class="" id="div-plus-de-tarif-1" <?php if (isset($f->parc_descprix[1][4])) "style='display:visible'"; else echo "style=\"display:none\""; ?> >
													<?php for ($jj=4;$jj<=12;$jj++) { ?>
													<div class="row row-margin-left m-l-10">
														<div class="form-group col-md-1">
															<label class="control-label num"><?php echo $jj; ?></label>
														</div>
														<div class="form-group col-md-2 m-r-5">
															<input type="text" name="epre_parc_descprix[1][<?php echo $jj; ?>]" id="epre_parc_descprix[1][<?php echo $jj; ?>]"  value="<?php echo $f->parc_descprix[1][$jj]; ?>" class="form-control" placeholder="Description" onchange="calcul_nb_tarif(1);">
														</div>
														<div class="form-group col-md-1 m-r-5">
															<input type="text"  name="epre_parc_prix[1][<?php echo $jj; ?>]" id="epre_parc_prix[1][<?php echo $jj; ?>]" value="<?php echo $f->parc_prix[1][$jj]; ?>" class="form-control" placeholder="€">
														</div>
														
														<div class="form-group date-time-font col-md-5 m-r-5 ">
															<div class="input-group">
																<span class="input-group-addon"> Du </span>
																<input id="date_timepicker_start_tarifs_1_<?php echo $jj; ?>" name="date_debut_tarif[1][<?php echo $jj; ?>]" value="<?php if ($f->date_debut_tarif[1][$jj] != '') echo $f->date_debut_tarif[1][$jj];?>" type="text"  class="form-control" />
																<span class="input-group-addon"> au </span>
																<input id="date_timepicker_end_tarifs_1_<?php echo $jj; ?>" name="date_fin_tarif[1][<?php echo $jj; ?>]" value="<?php if ($f->date_fin_tarif[1][$jj] != '') echo $f->date_fin_tarif[1][$jj]; ?>" type="text"  class="form-control" />
															</div>
														</div>
														<div class="form-group col-md-1 m-r-5">
															<input type="text"  name="epre_parc_prix_nb_dossard[1][<?php echo $jj; ?>]" id="epre_parc_prix_nb_dossard[1][<?php echo $jj; ?>]" value="<?php echo $f->parc_prix_nb_dossard[1][$jj]; ?>" class="form-control" placeholder="€">
														</div>
														<div class="form-group col-md-1 m-r-5">
															<input type="text"  name="epre_parc_places_nb_dossard[1][<?php echo $jj; ?>]" id="epre_parc_places_nb_dossard[1][<?php echo $jj; ?>]" value="<?php echo $f->parc_places_nb_dossard[1][$jj]; ?>" class="form-control" placeholder="place(s)">
														</div>
														<div class="form-group col-md-1 m-t-5">
															<span class="control-label num"><?php echo $f->parc_places_nb_dossard_restantes[1][$jj]; ?></span>
														</div>		

													</div>
													<?php } ?>
												</div>
												<input type="hidden" id="epre_parc_nbprix[1]" name="epre_parc_nbprix[1]" value="<?php if(isset($f->parc_nbprix[1])) echo $f->parc_nbprix[1]; ?>">
											</fieldset>	<!-- Fin Tarifs -->
											
											<fieldset>
											
												<legend class="pull-left width-full">Codes promotions

												</legend>

											
											<?php 
											if ((isset($_POST['epre_button']) && $_POST['epre_button'] == 'Modifier la fiche de cette course') || 
												(isset($_GET['epre_button']) && $_GET['epre_button'] == 'Modifier la fiche de cette course') || 
												(isset($_GET['epre_button']) && $_GET['epre_button'] == '1'))
											{ ?>	
												<p class="text-center">
													<a class="btn btn-info m-r-5" href="javascript:;" onclick="affiche_modal('Gestion des codes promos du parcours',0,'https://www.ats-sport.com/<?php echo URL_DEV; ?>admin/creation_promo.php?epre_id=<?php echo $_GET['epre_id']; ?>&id_parcours=<?php echo $f->parc_id[1]; ?>')" class="btn btn-danger m-b-15 m-l-5"><b>Générer des codes promo pour ce parcours</b></a>
												</p>

											
											<?php } else { ?>
											<div class="panel-body">
												<p class="text-center">

													<span class="alert alert-danger fade in m-b-15" href="javascript:;" <i class="fa fa-2x fa-money"></i><strong> Veuillez pré-enregistrer l'épreuve avant l'édition des codes promos</strong></span>

												</p>
											</div>
											<?php } ?>
											
											</fieldset>
											
											<fieldset>
														<legend class="pull-left width-full">Insérez ici les  informations transmises au coureur dans l’email de confirmation d’inscription</legend>
														<div class="row-margin-left">
															<div class="panel-body panel-form m-b-20">
																<textarea name="info_complementaire_parcours[1]" class="textarea form-control" id="info_complementaire_parcours_wysihtml5_1" placeholder="Indiquez ici des informations importantes ou complémentaires qui seront indiqué sur le mail d'inscription" rows="12"><?php echo $f->info_complementaire_parcours[1]; ?></textarea>
															</div>
														</div>

											</fieldset>
											
											<fieldset>
											
												<legend class="pull-left width-full">Champs supplémentaires 
												
												</legend>

											
											<?php 
											if ((isset($_POST['epre_button']) && $_POST['epre_button'] == 'Modifier la fiche de cette course') || 
												(isset($_GET['epre_button']) && $_GET['epre_button'] == 'Modifier la fiche de cette course') || 
												(isset($_GET['epre_button']) && $_GET['epre_button'] == '1'))
											{ ?>	
												<p class="text-center">
													<a class="btn btn-info m-r-5" href="javascript:;" onclick="affiche_modal('Question Quantité',0,'https://www.ats-sport.com/<?php echo URL_DEV; ?>admin/creation_champs.php?epre_id=<?php echo $_GET['epre_id']; ?>&id_parcours=<?php echo $f->parc_id[1]; ?>&champ=d_parcours')" class="btn btn-danger m-b-15 m-l-5" data-toggle="tooltip" data-placement="top" data-original-title=""><b>Question Quantité</b></br>Ex: taille/tee-shirt</a>
													<a class="btn btn-primary m-r-5" href="javascript:;" onclick="affiche_modal('Question Commerce',0,'https://www.ats-sport.com/<?php echo URL_DEV; ?>admin/creation_champs.php?epre_id=<?php echo $_GET['epre_id']; ?>&id_parcours=<?php echo $f->parc_id[1]; ?>&champ=p_parcours')" class="btn btn-danger m-b-15 m-l-5" data-toggle="tooltip" data-placement="top" data-original-title=""><b>Question Commerce</b></br>Ex: repas, hébergement</a>
													<a class="btn btn-default m-r-5" href="javascript:;" onclick="affiche_modal('Autres Questions',0,'https://www.ats-sport.com/<?php echo URL_DEV; ?>admin/creation_champs.php?epre_id=<?php echo $_GET['epre_id']; ?>&id_parcours=<?php echo $f->parc_id[1]; ?>&champ=q_parcours')" class="btn btn-danger m-b-15 m-l-5" data-toggle="tooltip" data-placement="top" data-original-title=""><b>Autres Questions</b></br>Ex: profession, intérêts</a>
												</p>

											
											<?php } else { ?>
											<div class="panel-body">
												<p class="text-center">

													<span class="alert alert-danger fade in m-b-15" href="javascript:;" <i class="fa fa-2x fa-money"></i><strong>Vous pourrez après enregistrement de votre épreuve, ajouter des questions complémentaires pour ce parcours.
</strong></span>

												</p>
											</div>
											<?php } ?>
											
											</fieldset>

												<fieldset>
													<legend class="pull-left width-full">Fichiers supplémentaires pour ce parcours</legend>
													<div class="col-sm-12">
														<div class="panel-body panel-form">
															<div class="form-group">
																<label class="control-label"><b>5</b> fichiers maximum. Formats autorisés : <b>.jpg, .gif , .png ou .pdf, 2Mo maximum</b></label>
																<input id="fichier_parcours_sup_1" name="fichier_parcours_sup_1[]" type="file" class="file" multiple >
															</div>
														</div>
													</div>
												</fieldset>
												
											<fieldset>

														<legend class="pull-left width-full">Description du parcours</legend>
														<div class="row-margin-left">
															<div class="panel-body panel-form m-b-20">
																<textarea name="parcours_description[1]" class="textarea form-control" id="parcours_description_wysihtml5_1" placeholder="Historique, dénivelé, ambiance, accès, douche, repas..." rows="12"><?php echo $f->parcours_description[1]; ?></textarea>
															</div>
														</div>


											</fieldset>

										</div>
									</div>
								</div>
                            </div> <!-- end wizard 3/-->
                            <div class="wizard-step-3">
                                <fieldset>
                                    <legend class="pull-left width-full">Contact - Information importante : Qui contacter pour s'inscrire?</legend>

									<div class="form-group">
										<label class="col-sm-2 control-label" ><span class="text-danger">*</span> Nom de la structure</label>
										<div class="col-sm-6">
											<input type="text" class="form-control" name="epre_structurelegale" id="epre_structurelegale" value="<?php echo $f->structurelegale; ?>" maxlength="150" data-parsley-group="wizard-step-3" required>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">Site Web </label>
										<div class="col-sm-6">
											<INPUT class="form-control" placeholder="ex : http://www.traildescalades.fr" type="text" name="epre_siteinternet" id="epre_siteinternet" value="<?php echo $f->siteinternet; ?>" maxlength="150">
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">Page Facebook </label>
										<div class="col-sm-6">
											<INPUT class="form-control" type="text" placeholder="ex : https://www.facebook.com/traildescalades" name="epre_sitefacebook" id="epre_sitefacebook" value="<?php echo $f->siteFacebook; ?>" maxlength="150">
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">Page Twitter </label>
										<div class="col-sm-6">
											<INPUT class="form-control" type="text" placeholder="ex : https://twitter.com/ffathletisme" name="epre_sitetwitter" id="epre_sitetwitter" value="<?php echo $f->siteTwitter; ?>" maxlength="150">
										</div>
									</div>
									
									<div class="form-group">
										<label class="col-sm-2 control-label">Tél. Inscription </label>
										<div class="col-sm-6">
											<INPUT class="form-control" type="text" name="epre_inscr_tel" id="epre_inscr_tel" value="<?php echo $f->inscr_tel; ?>" maxlength="150">
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">Email des inscriptions </label>
										<div class="col-sm-6">
											<INPUT class="form-control" type="text" name="epre_inscr_email" id="epre_inscr_email" value="<?php echo $f->inscr_email ?>" maxlength="150" >
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-4 control-label">Souhaitez vous recevoir un email à chaque inscription ? (email obligatoire à remplir ci-dessus)</label>
										<div class="col-sm-6">
											<input type="checkbox" value="1"  onchange="email_obligatoire(this.checked,$(this).attr('data-parsley-id'))" name="epre_inscr_email_recevoir" id="email_recevoir" data-render="switchery" data-theme="blue" data-change="check-switchery-check-ag" <?php if ($f->inscr_email_recevoir == 1) echo "checked"; else echo ""; ?>/>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-12">
											<div class="panel panel-default">
												<div class="panel-body">
													<TEXTAREA class="textarea form-control" id="wysihtml5" placeholder="Indiquez ici les coordonnées postales de la structure organisatrice" name="epre_inscr_contact" cols="10" rows="15"><?php echo $f->inscr_contact; ?></TEXTAREA>
												</div> <!-- End Panel-body /-->
											</div> <!-- End Panel-Default /-->
										</div>
									</div>
                                </fieldset>
                            </div>
                            <div class="wizard-step-4">
                                <fieldset>
                                    <legend class="pull-left width-full">Réglement</legend>
									
									<div class="row">	
										<div class="col-sm-12">
											<div class="panel panel-default">
												<div class="panel-heading"><h3 class="panel-title">Insérez ci-dessous, le règlement de la course qui sera présenté lors de l'inscription d'un participant</h3></div>
													<div class="panel-body">
														<div class="form-group ">
															<div class="col-sm-4">
																<select class="form-control" id="choix_reglement_course" name="choix_reglement_course" onchange="aff_select_reglement(this.value);">
																	<option value="url" <?php if ($type_reglement == 1) echo 'selected'; ?> >Réglement sur votre site Internet </option>
																	<option value="texte" <?php if ($type_reglement == 2) echo 'selected'; ?>>Réglement au format texte</option>
																	<option value="fichier" <?php if ($type_reglement == 3) echo 'selected'; ?>>Réglement au format .pdf</option>
																</select>
															</div>
														</div>
														<div class="form-group" id="aff_reglement_url" <?php if ($type_reglement == 2) echo 'style="display:none"'; else echo 'style="display:visible"' ?>>
															<div class="col-sm-6">
																<INPUT class="form-control" type="text" name="epre_reglement_url" id="epre_reglement_input" value="<?php echo $f->reglement_url ?>" maxlength="255" placeholder="Indiquer l'url de votre réglément (http:// ...)">	
															</div>
														</div>														
														<div class="form-group" style="display:none" id="aff_reglement_fichier">
															<div class="col-sm-6">
																<span><?php echo $f->reglement ?></span>
																<input id="epre_reglement_fichier" name="epre_reglement_fichier[]" type="file" class="form-control file-loading">
															</div>
														</div>
														<div class="form-group" id="aff_reglement_texte" <?php if ($type_reglement == 2) echo 'style="display:visible"'; else echo 'style="display:none"' ?> >
															<div class="col-sm-12">
																<TEXTAREA class="textarea form-control" id="wysihtml5" placeholder="Réglement à définir" name="epre_reglement_texte" rows="20" cols="75" data-parsley-group="wizard-step-4"><?php echo $f->reglement_texte ?></TEXTAREA>
															</div>
														</div>															
													</div>
											</div>
										</div>
									</div>
									
									<div class="row">	
										<div class="col-sm-12">
											<div class="panel panel-default">
												<div class="panel-heading"><h3 class="panel-title">Facultatif : insérez ci-dessous un modèle .pdf d’autorisation parentale</h3></div>
													<div class="panel-body">
														<div class="form-group ">
														</div>
												
														<div class="form-group">
															<div class="col-sm-6">
																<span><?php echo $f->modele_auto_parentale ?></span>
																<input id="modele_auto_parentale" name="modele_auto_parentale[]" type="file" class="form-control file-loading">
															</div>
														</div>
														
													</div>
											</div>
										</div>
									</div>

									<div class="row">	
										<div class="col-sm-12">
											<div class="panel panel-default">
												<div class="panel-heading"><h3 class="panel-title">Design : Adapter le formulaire d’inscription à votre gout !!(image .jpg de taille 1920x1080px)</h3></div>
													<div class="panel-body">
														<div class="form-group ">
														</div>
												
														<div class="form-group">
															<div class="col-sm-6">
																<span><?php echo $f->epreuve_image_de_fond ?></span>
																<input id="epreuve_image_de_fond" name="epreuve_image_de_fond[]" type="file" class="form-control file-loading">
															</div>
														</div>
														
														
														<div class="form-group">
														<label class="col-sm-2 control-label">Couleur du panel ? </label>
															<div class="col-sm-2">
																<INPUT class="form-control" type="text" name="epreuve_panel_couleur" id="epreuve_panel_couleur" value="<?php echo $f->epreuve_panel_couleur ?>" maxlength="255" placeholder="ex : #123456">	
															</div>
														</div>
														
													</div>
											</div>
										</div>
									</div>
                                </fieldset>
                            </div>
                            <div class="wizard-step-5">
                                <fieldset>
                                <fieldset>
									<!---------------------->
									<!-- Intégration Hugo -->
									<!---------------------->
                                    <legend class="pull-left width-full">Inscription en ligne <button class="btn btn-info btn-xs m-r-5 m-b-5" type="button" data-toggle="modal" data-target="#ModalHelpIL"><i class="fa fa-2x fa-question-circle"></i></button></legend>
									<!-- Modal Aide à l'utilisation by Hugo -->
									<div class="modal fade" id="ModalHelpIL" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
									  <div class="modal-dialog modal-m" role="document">
										<div class="modal-content">
										  <div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
											<h4 class="modal-title" id="myModalLabel">Aide à l'utilisation</h4>
										  </div>
										  <div class="modal-body">
											<h5 class="text-info">Comment activer le service d'inscriptions en ligne pour mon épreuve ?</h5>
											<br />
											<p class="text-justify">
												Vous devez cliquer sur le bouton d'activation du service de gestion des engagements en ligne,
												et vous assurer que les dates d'ouvertures et de fermetures du service soient correctes
											</p>
											<p class="text-danger text-justify">Attention, vous devez également vous assurer que les dates des tarifs (onglet parcours) associés à vos parcours soient cohérentes.</p>
										  </div>
										</div>
									  </div>
									</div>

								<!-------------------------->
								<!-- Fin intégration Hugo -->
								<!-------------------------->
									<div class="form-group">
										<div class="col-md-3">
												<label class="control-label">Ouverture et de fermeture des inscriptions</label> 
											</div>
													<div class="col-md-4">
														<div class="input-group">
															<input id="date_timepicker_start_inscr" name="epre_inscr_debut" value="<?php echo $f->inscr_debut; ?>" type="text"  class="form-control" />
															<span class="input-group-addon"> au </span>
															<input id="date_timepicker_end_inscr" name="epre_inscr_fin" value="<?php echo $f->inscr_fin; ?>" type="text"  class="form-control" />
														</div>
													</div>
									</div>
									<div class="form-group">
											<div class="col-md-3">
												<label for="inputName" class="control-label">Frais de transaction à la charge </label>
											</div>
											<?php if(!$modif || $admin==1)
											{ ?>
												<div class="col-md-2">
													<SELECT class="form-control" name="payeur" id="payeur">
														<OPTION VALUE="coureur"<?php if ($f->payeur == "coureur") echo " selected"; else echo ""; ?>>du coureur</OPTION>
														<OPTION VALUE="organisateur"<?php if ($f->payeur == "organisateur") echo " selected"; else echo "";?>>de l'organisateur</OPTION>
													</SELECT>

												</div>
												<?php if($admin==1) { ?>
													<div class="col-md-2">
															Frais (€) <input size="2" class="input-inline" type="text" name="epre_payeur_cout_cb" id="payeur_cout_cb" value="<?php echo $f->epre_payeur_cout_cb; ?>" />
													</div>
												<?php } ?> 
											<?php }
											else
											{ ?>
												<div class="col-md-12">
														<label for="inputName" class="control-label"><?php if ($f->payeur == 'coureur') echo "du "; else echo "de l' ";?><?php echo $f->payeur ?><span style="font-size:11px;">(Veuillez-nous contacter pour modifier ce paramètre)</span>
														<input type="hidden" name="payeur" value="<?php echo $f->payeur; ?>" />
														</label>
													</div>
											<?php } ?> 
									</div>

									<div class="form-group">
										<div class="col-md-3">
											<label for="periode_reversement_inscriptions" class="control-label">Période de reversement des inscriptions </label>
										</div>
										<div class="col-md-2">
											<SELECT class="form-control" name="periode_reversement_inscriptions" id="periode_reversement_inscriptions">
												<OPTION VALUE="epreuve"<?php if ($f->periode_reversement_inscriptions == "epreuve") echo " selected"; ?>>Après l'épreuve</OPTION>
												<OPTION VALUE="trimestriel"<?php if ($f->periode_reversement_inscriptions == "trimestriel") echo " selected"; ?>>Trimestriel</OPTION>
												<OPTION VALUE="mensuel"<?php if ($f->periode_reversement_inscriptions == "mensuel") echo " selected"; ?>>Mensuel</OPTION>
											</SELECT>
										</div>
										<div class="col-md-7">
											<p class="text-primary">pour toutes demandes particulières merci d'écrire à <a href="mailto:contact@ats-sport.com">contact@ats-sport.com</a></p>
										</div>
									</div>
									<div class="form-group">
										<div class="col-md-4">
											<div class="form-inline">
												<label class="control-label">Acceptez vous les paiements par chèque ? (voir conditions générales)</label>
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-inline">
												<input class="pull-right" type="checkbox" name="epre_paiementcheque" id="inscription-ligne-cheque" value="1" data-render="switchery" data-theme="blue" <?php if ($f->paiement_cheque == 1) echo "checked"; ?> onchange="etat_change_checkbox('inscription-ligne-cheque');"/>
											</div>
										</div>
									</div>
									
									<div class="form-group">
										<div class="col-md-4">
											<div class="form-inline">
												<label class="control-label">Liste des engagés à la demande (email et date de naissance) </label>
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-inline">
												<input class="pull-right" type="checkbox" name="epre_liste_engage_ctrl" id="inscription-liste-engage-ctrl" value="1" data-render="switchery" data-theme="blue" <?php if ($f->epre_liste_engage_ctrl == 1) echo "checked"; ?> />
											</div>
										</div>
									</div>
									
									<div class="form-group">
										<div class="col-md-4">
											<div class="form-inline">
												<label class="control-label">Visible sur le calendrier de ats-sport.com ?</label>
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-inline">
												<input class="pull-right" type="checkbox" name="epre_visible_calendrier" id="inscription-visible_calendrier" value="1" data-render="switchery" data-theme="blue" <?php if ($f->epre_visible_calendrier == 1) echo "checked"; ?> />
											</div>
										</div>
									</div>
									
									<div class="form-group">
										<div class="col-md-4">
											<div class="form-inline">
												<label class="control-label">Souhaitez vous revenir au système d'inscription V1 ?</label>
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-inline">
												<input onchange ="show_nsi(this.checked);" class="pull-right" type="checkbox" name="epre_nsi" id="inscription-nsi" value="1" data-render="switchery" data-theme="blue" <?php if ($f->epre_nsi == "non" ) echo "checked"; ?> />
											</div>
										</div>
									</div>
									
									
								<!---------------------->
								<!-- Intégration Hugo -->
								<!---------------------->
								<fieldset>
									<br />
									<legend class="pull-left width-full">Méthode 1 – intégrer notre solution au sein de votre site Web - Attention, votre site doit être sécurisé (https) <button class="btn btn-info btn-xs m-r-5 m-b-5" type="button" data-toggle="modal" data-target="#ModalHelp"><i class="fa fa-2x fa-question-circle"></i></button></legend>
									<?php if (isset($_GET['epre_id']) && ($_GET['epre_button'] == 'Modifier la fiche de cette course')) { ?>
										<h6>Inscription : </h6>
										<div id="epre_nsi_nouveau" <?php if ($f->epre_nsi == "non" ) echo 'style="display:none"'; ?> >
											<pre><code>&lt;iframe src="https://www.ats-sport.com/insc.php?id_epreuve=<?php echo $_GET['epre_id']; ?>&step=start&panel=iframe" width="900" height="1200"&gt;&lt;/iframe&gt;</code></pre>						
										</div>
										<div id="epre_nsi_ancien" <?php if ($f->epre_nsi == "oui" ) echo 'style="display:none"'; ?>>
											<pre><code>&lt;iframe src="https://www.ats-sport.com/inscriptions.php?id_epreuve=<?php echo $_GET['epre_id']; ?>&panel=iframe" width="900"height="1200"&gt;&lt;/iframe&gt;</code></pre>
										</div>
										<h6>Liste d’engagés : </h6>										
										<pre><code>&lt;iframe src="https://www.ats-sport.com/liste_des_inscrits.php?id_epreuve=<?php echo $_GET['epre_id']; ?>&panel=iframe" width="900"height="1200"&gt;&lt;/iframe&gt;</code></pre>
									<?php } else { ?>
										<pre><code>Vos liens seront disponibles dès que l'épreuve sera enregistrée</code></pre>	
									<?php } ?>
								</fieldset>								
								<fieldset>
									<br />
									<legend class="pull-left width-full">Méthode 2 – Diriger les coureurs vers ATS-Sport  à l’aide des liens suivants :</legend>
									<?php if (isset($_GET['epre_id']) && ($_GET['epre_button'] == 'Modifier la fiche de cette course')) { ?>
										<h6>Inscription : </h6>
										<div id="epre_nsi_nouveau_2" <?php if ($f->epre_nsi == "non" ) echo 'style="display:none"'; ?> >
											<pre>https://www.ats-sport.com/insc.php?id_epreuve=<?php echo $_GET['epre_id']; ?></pre>
										</div>
										<div id="epre_nsi_ancien_2" <?php if ($f->epre_nsi == "oui" ) echo 'style="display:none"'; ?> >
											<pre>https://www.ats-sport.com/inscriptions.php?id_epreuve=<?php echo $_GET['epre_id']; ?></pre>
										</div>
										
										<h6>Liste d’engagés : </h6>
										<pre>https://www.ats-sport.com/liste_des_inscrits.php?id_epreuve=<?php echo $_GET['epre_id']; ?></pre>
									<?php } else { ?>
											<pre><code>Vos liens  seront disponibles dès que l'épreuve sera enregistrée</code></pre>	
									<?php } ?>
								</fieldset>
								<fieldset>
									<br />
									<legend class="pull-left width-full">Assistance téléphonique</legend>
											<pre><code class="text-center"><p>Vous pouvez nous joindre au </br><b>04 67 45 41 10</b></br>ou par email</br><b><a href="mailto:contact@ats-sport.com">contact@ats-sport.com</a></b></p></code></pre>	
								</fieldset>

								<div class="form-group"  id="div-inscription-ligne-cheque" <?php if ($f->paiement_cheque == 1) echo "style='display:visible'"; else echo "style='display:none'"?>>
										<div class="col-sm-12">
											<div class="panel panel-default">
												<div class="panel-body">
													<TEXTAREA class="textarea form-control" id="wysihtml5" placeholder="Merci d'indiquer les coordonnées de la personne qui recevra les chèques" name="epre_coordonnees_cheque" cols="5" rows="10"><?php echo $f->epre_coordonnees_cheque; ?></TEXTAREA>
												</div> <!-- End Panel-body /-->
											</div> <!-- End Panel-Default /-->
										</div>
									</div>



								</fieldset>
									<fieldset>
									<legend class="pull-left width-full">Informations relatives au paiment par chèque des inscriptions par groupe</legend>	
									<div class="form-group"  id="div-inscription-ligne-cheque" >
									
									
										<div class="col-sm-12">
											<div class="panel panel-default">
												<div class="panel-body">
													<TEXTAREA class="textarea form-control" id="wysihtml5" placeholder="Merci d'indiquer les coordonnées de la personne qui recevra les chèques du groupe " name="epre_info_paiement_cheque_groupe" cols="5" rows="10"><?php echo $f->epre_info_paiement_cheque_groupe; ?></TEXTAREA>
												</div> <!-- End Panel-body /-->
											</div> <!-- End Panel-Default /-->
										</div>
									</div>
									</fieldset>
									
									<fieldset>
									<legend class="pull-left width-full">Informations relatives au paiment par IBAN des inscriptions par groupe</legend>	
									<div class="form-group"  id="div-inscription-ligne-cheque" >
									
									
										<div class="col-sm-12">
											<div class="panel panel-default">
												<div class="panel-body">
													<TEXTAREA class="textarea form-control" id="wysihtml5" placeholder="Merci d'indiquer les informations bancaire pour le paiement du groupe" name="epre_info_paiement_iban_groupe" cols="5" rows="10"><?php echo $f->epre_info_paiement_iban_groupe; ?></TEXTAREA>
												</div> <!-- End Panel-body /-->
											</div> <!-- End Panel-Default /-->
										</div>
									</div>
									</fieldset>								
								<!-- Modal Aide à l'utilisation by Hugo -->
								<div class="modal fade" id="ModalHelp" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
								  <div class="modal-dialog modal-m" role="document">
									<div class="modal-content">
									  <div class="modal-header">
										<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
										<h4 class="modal-title" id="myModalLabel">Aide à l'utilisation</h4>
									  </div>
									  <div class="modal-body">
										<h4>Intégrer le formulaire d'inscription sur mon site</h4>
										<h6><i class="fa fa-2x fa-wordpress"></i> Wordpress</h6>
										<u>Pré requis</u>
										<ul>
											<li>Accès au compte administrateur ou à un compte contributeur de votre site Wordpress</li>
											<li>Avoir les droits pour insérer tout code HTML (iframe)</li>
										</ul>
										<video controls src="images/wordpress.mp4" width="auto" height="315">Voir</video>
										<br /><br />
										<h6><i class="fa fa-2x fa-joomla"></i> Joomla</h6>
										<u>Pré requis</u>
										<ul>
											<li>Accès au compte administrateur ou à un compte contributeur de votre site Joomla</li>
											<li>Installer et utiliser JCE Editor (plugin Joomla)</li>
										</ul>
										<video controls src="images/joomla.mp4" width="auto" height="315">Voir</video>
									  </div>
									</div>
								  </div>
								</div>
								<!-------------------------->
								<!-- Fin intégration Hugo -->
								<!-------------------------->								
                            </div>
                            <div class="wizard-step-6">
                                <fieldset>
                                    <legend class="pull-left width-full">Correspondance</legend>
                                    <h5>Saisissez les adresses emails : presses, partenaires ou autres où envoyer les résultats après la course (une à une)</h5><br>
									<?php for( $email=0; $email<10; $email++ ) { ?>
									<div class="form-group" id="email<?php echo $email; ?>">
										<label class="col-sm-1 control-label" >Email</label>
										<div class="col-sm-3">
											<input type="text" class="form-control" placeholder="email@ats-sport.com" name="epre_email_resultats[]" value="<?php echo $f->email_resultats[$email]['email']; ?>" maxlength="150">
										</div>
										<label class="col-sm-1 control-label" >Public</label>
										<div class="col-sm-2">
											<select class="form-control" name="epre_email_resultats_public[]">
												<option value="defaut" selected>choisir ...</option>
												<option value="presse" <?php if ($f->email_resultats[$email]['public'] == "presse") echo 'selected'; ?>>Presse</option>
												<option value="partenaire" <?php if ($f->email_resultats[$email]['public'] == "partenaire") echo 'selected'; ?>>Partenaire</option>
												<option value="federation" <?php if ($f->email_resultats[$email]['public'] == "federation") echo 'selected'; ?>>Fédération</option>
												<option value="autre" <?php if ($f->email_resultats[$email]['public'] == "autre") echo 'selected'; ?>>Autre</option>
											</select>
										</div>
										<label class="col-sm-1 control-label" >Type d'envoi</label>
										<div class="col-sm-2">
											<select class="form-control" name="epre_email_resultats_type_envoi[]">
												<option value="defaut" selected>choisir ...</option>
												<option value="immediat" <?php if ($f->email_resultats[$email]['typeEnvoi'] == "immediat") echo 'selected'; ?>>Envoi immédiat</option>
												<option value="24h" <?php if ($f->email_resultats[$email]['typeEnvoi'] == "24h") echo 'selected'; ?>>Envoi 24h après</option>
												<option value="48h" <?php if ($f->email_resultats[$email]['typeEnvoi'] == "48h") echo 'selected'; ?>>Envoi 48h après</option>
											</select>
										</div>
										<div class="col-sm-2">
											<?php if( !empty( $f->email_resultats[$email]['email'] ) ): ?>
												<button type="button" class="btn btn-info" onclick="send_resultats(<?php echo $_GET['epre_id'] ?>,<?php echo $f->email_resultats[$email]['id'] ?>)">Envoyer</button>
												<button type="button" class="btn btn-danger" onclick="remove('email<?php echo $email; ?>')">Supprimer</button>
											<?php endif; ?>
										</div>
									</div>
									<?php } ?>
                                </fieldset>
                            </div>
							<?php if ($_GET['epre_button'] == 'Modifier la fiche de cette course' || !isset($_GET['epre_id'])) { ?>
                             <div class="wizard-step-7">
								
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
                                        <td id="affiche_validation_nom_epreuve" ><em><?php if(isset($f->nom)) echo $f->nom; ?></em></td>
                                    </tr>
                                    <tr>
                                        <td class="semi-bold">Date</td>
                                        <td id="affiche_validation_date_debut_epreuve"><em>Du <span ><?php if(isset($f->epre_date)) echo $f->epre_date; ?></span> au <span id="affiche_validation_date_fin_epreuve"><?php if(isset($f->epre_date_fin)) echo $f->epre_date_fin; ?></span></em></td>
                                    </tr>
                                    <tr>
                                        <td class="semi-bold">Nombre de parcours</td>
                                        <td id="affiche_validation_nombre_parcours"><em><?php if(isset($f->nbparc)) echo $f->nbparc; ?></em></td>
                                    </tr>
                                </tbody>
                            </table>
							</div>
							<div class="col-md-3">&nbsp;</div>

									<div class="">
									<div class="form-group">
										<div class="">
											<div class="form-inline">
												<label class="control-label">Souhaiteriez-vous recevoir une offre pour le chronométrage de votre épreuve ?</label>
												<input class="pull-right" type="checkbox" name="epre_devischrono" id="inscription-ligne" value="1" data-render="switchery" data-theme="blue" <?php if ($f->devis_chrono == 1) echo "checked"; ?>/>
											</div>
										</div>
									</div>
									<?php if( $admin==1 ) : ?>
									<div class="form-group">
										<div class="col-md-12">
											<div class="form-inline">
												<label class="control-label">Chronométrage par ATS-SPORT ? </label>
												<input class="pull-right" type="checkbox" name="chrono_ats_sport" id="inscription-chrono_ats_sport" value="1" data-render="switchery" data-theme="blue" <?php if($f->chrono_ats_sport == 1) echo "checked"; ?>/>
											</div>
										</div>
									</div>
									<?php endif; ?>
									<div class="form-group">
										<div class="col-md-12">
											<div class="form-inline">
												<label class="control-label">Cochez pour valider et activer notre solution… </label>
												<input class="pull-right" type="checkbox" name="utilisation_service_inscription" id="inscription-ligne" value="1" data-render="switchery" data-theme="blue" <?php if($f->paiement_cb == 1) echo "checked"; ?>/>
											</div>
										</div>
									</div>
									<?php if (!isset($_GET['epre_id']) && ($_GET['epre_button'] != 'Modifier la fiche de cette course')) { ?>
									<div class="form-group">
									<div class="col-md-3">
									</div>
										<div class="col-md-7">
											<div class="form-inline">
												<label class="control-label m-r-10" >Veuillez accepter les conditions d'utilisation de ce service (voir ci-contre) afin </s><b>d'enregistrer l'épreuve</b></s> </label>

												<input onchange="check_condition(this.checked);" class="pull-right" type="checkbox" name="condition_inscription" id="inscription-ligne-condition" value="1" data-parsley-group="wizard-step-7" data-parsley-error-message="Vous devez valider les conditions générales" data-render="switchery" data-theme="blue" <?php if($f->paiement_cb == 1) echo "checked"; ?> required />
											</div>
										</div>
										
										<div class="col-md-1">
												<a data-toggle="modal" data-keyboard="false" data-backdrop="static" href="#TCGU" class="btn btn-primary">Conditions d'utilisation</a>	
													<div class="modal" id="TCGU">
														<div class="modal-dialog modal-lg">
															<div class="modal-content">
																<div class="modal-header" style="text-align: right;">
																	<a href="#" class="btn btn-primary" data-dismiss="modal">Fermer</a>
																</div>
																<div class="modal-body"><?php echo $condition["val"] ?></div>
																<div class="modal-footer">
																	<a href="#" class="btn btn-primary" data-dismiss="modal">Fermer</a>
																</div>
															</div><!-- /.modal-content -->
														</div><!-- /.modal-dialog -->
													</div>
										</div>
									<div class="col-md-3">
									</div>
									</div>
									<?php } ?>
									<?php if (isset($_GET['epre_id']) && ($_GET['epre_button'] == 'Modifier la fiche de cette course')) { ?>
									<p><button class="btn btn-danger btn-lg" type="submit"><strong>Mettre à jour votre épreuve</strong></button></p>
									<?php } else { ?>
									
									<p><button style="display:none" id="enre_epreuve" class="btn btn-danger btn-lg" type="submit"><strong>Enregistrer votre épreuve</strong></button></p>
									<?php } ?>
										
										<input type="hidden" id="epre_nbparc" name="epre_nbparc" value="<?php if(isset($f->nbparc)) echo $f->nbparc; ?>">

									</div>
								</div>

								<div class="modal" id="resultat" data-keyboard="false" data-backdrop="static"> 
								<!-- data-keyboard="false" data-backdrop="static" /-->
									<div class="modal-dialog">
										<div class="modal-content">
											<div class="modal-header" style="text-align: right;">
												ATS-SPORT - CREATION ET EDITION EPREUVE
											</div>
											<div class="modal-body" id="return_resultat"><h4>VOTRE EPREUVE EST ENREGISTREE</h4></div>
											<div class="modal-footer">
												<a href="#" class="btn btn-primary" id="fermer_resultat_final" >Fermer</a>
											</div>
										</div><!-- /.modal-content -->
									</div><!-- /.modal-dialog -->
								</div>
							<?php } ?>
                            </div>
                        </div> <!-- end Wizard -->
					</form>
					</div>
				</div>
			</div>
		</div>
	</div>
            <!-- end row -->
</div> <!-- end #content -->
 <div id="affichage_modal"> </div>		
        <!-- begin theme-panel -->
		 <?php //include ("includes/panel.php"); ?>
        <!-- end theme-panel -->
		
		<!-- begin scroll to top btn -->
		<a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top fade" data-click="scroll-top"><i class="fa fa-angle-up"></i></a>
		<!-- end scroll to top btn -->
	</div>
	<!-- end page container -->
	
     <?php include ("includes/footer_js_base.php"); ?>
		
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
	<!-- ================== END PAGE LEVEL JS ================== -->
	<script>

<?php if (isset($_GET['epre_id']) && ($_GET['epre_button'] != 'Modifier la fiche de cette course')) { ?>
	
								$('#form-parcours input').attr('readonly', 'readonly');
								$('#form-parcours select').attr('disabled', 'disabled');
								$('#form-parcours textarea').attr('readonly', 'readonly');
								$('#ajouter_parcours').hide();
								
<?php } ?>								

function parcours_content(id) {
content = '<input type="hidden" id="id_table_parcours[' + id + ']" name="id_table_parcours[' + id + ']" value="NP' + id + '" />';
content +='<fieldset>';
content += '<legend class="pull-left width-full">Identification</legend>';
content += '<div class="row-margin-left">';
content += '	<div class="col-md-4">';
content += '		<div class="form-group m-r-20 input-group-lg">';
content += '			<h4>Nom de la course*</h4>';
content += '			<input type="text" name="epre_parc_nom[' + id + ']" id="epre_parc_nom' + id + '" placeholder="Nom du parcours" class="form-control input-lg" data-parsley-group="wizard-step-2" onchange="$(\'a#name_parcours_' + id + '\').text(this.value);" required />';
content += '		</div>';
content += '	</div>';
content += '	<div class="col-md-3">';
content += '		<div class="form-group m-r-20">';
content += '			<h4>Date et heure de départ </h4>';
content += '				<div class="input-group input-group-lg">';
content += '					<input id="date_parcours_timepicker_start_' + id + '" name="epre_parc_date[' + id + ']" type="text"  class="form-control" placeholder="Date de départ" />';
content += '					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>';
content += '				</div>'; 
content += '		</div>';
content += '	</div>';
content += '	<div class="col-md-3">';
content += '		<div class="form-group">';
content += '		<h4>Format de la course </h4>';
content += '			<div class="input-group input-group-lg">';
<?php $type = ''; ?>
content += '				<SELECT class="form-control" name="epre_parc_type[' + id + ']" id="epre_parc_type[' + id + ']">';
<?php foreach ($f->typeparcours as $k=>$i) { ?>
	<?php if( $type != $f->typeepreuve[$i[1]] ) : ?>
content += '					<OPTGROUP label="<?php echo $f->typeepreuve[$i[1]]; ?>">';
	<?php endif; ?>
content += '					<OPTION VALUE="<?php echo $k; ?>"><?php echo $i[0]; ?></OPTION>';
<?php $type = $f->typeepreuve[$i[1]]; ?>
	<?php if( $type != $f->typeepreuve[$i[1]] ) : ?>
content += '					</OPTGROUP>';
	<?php endif; ?>
<?php } ?>
content += '					<OPTION VALUE="-1">Autre</OPTION>';
content += '				</SELECT>';
content += '			</div>';
content += '		</div>';
content += '	</div>';                               
content += '</div>';

content += '<div class="col-md-2">';
content += '	<div class="form-group m-r-20 input-group-lg">';
content += '		<h4>Ordre d\'affichage</h4>';
content += '		<input type="text" name="epre_parc_ordre[' + id + ']" id="epre_parc_ordre' + id + '" value="' + ($(".nav-tabs").children().length) + '" class="form-control input-group-lg" data-parsley-group="wizard-step-2" required />';
content += '	</div>';
content += '</div>';
	   
content += '<div class="form-group">';				

content += '<label class="col-md-2 control-label m-t-25">Tranche d\'âge autorisé  : De</label>';
content += '<div class="col-md-3 m-t-25">';
content += '	<div class="input-group">';
content += '		<select class="form-control" name="epre_parc_age_debut[' + id + ']">';
<?php for ($i=0;$i<=150;$i++) { ?>
content += '			<OPTION VALUE="<?php echo $i; ?>" <?php if ( $i == "18" ) echo " SELECTED" ; ?>><?php echo $i; ?></OPTION>';
<?php } ?>
content += '		</select>';
content += '		<span class="input-group-addon">a</span>';
content += '		<select class="form-control" name="epre_parc_age_fin[' + id + ']">';
<?php for ($i=0;$i<=150;$i++) { ?>
content += '			<OPTION VALUE="<?php echo $i; ?>" <?php if ( $i == "120" ) echo " SELECTED" ; ?>><?php echo $i; ?></OPTION>';
<?php } ?>
content += '		</select>';
content += '<span class="input-group-addon"> ans</span>';
content += '	</div>';
content += '</div>';
content += '</div>';
										
content += '<div class="form-group"><hr>';

content += '	<div class="col-md-3">';
content += '		<div class="form-inline">';
content += '			<label class="control-label">S\'agit-il d’un parcours individuel ou par équipe ?</label>';
content += '			<input type="checkbox" id="relais-' + id + '" name="relais[' + id + ']" value="1" data-render="switchery" data-theme="blue" onchange="etat_change_checkbox(\'relais-'+ id +'\'); "/>';
content += '		</div>';
content += '	</div>';

content += '	<span style="display:none" id="div-relais-'+ id +'">';
content += '	<div class="col-md-2" >';
content += '		<div class="form-inline">';
content += '			<label>Nombre de personne ?</label>';
content += '				<select class="form-control" name="relais_nb_personne[' + id + ']">';
<?php for ($i=2;$i<=10;$i++) { ?>
content += '					<OPTION VALUE="<?php echo $i; ?>"><?php echo $i; ?></OPTION>';
<?php } ?>
content += '				</select>';
content += '		</div>';
content += '	</div>';

content += '	<div class="col-md-2" >';
content += '		<div class="form-inline">';
content += '			<label>Nombre de personne min ? (laisser 0 pour fixer le nombre de personne à celui de max</label>';
content += '				<select class="form-control" name="relais_nb_personne_min[' + id + ']">';
<?php for ($i=0;$i<=10;$i++) { ?>
content += '					<OPTION VALUE="<?php echo $i; ?>"><?php echo $i; ?></OPTION>';
<?php } ?>
content += '				</select>';
content += '		</div>';
content += '	</div>';

content += '	<div class="col-md-6" >';
content += '		<div class="form-inline">';
content += '			<label class="control-label">Le Numéro de dossard est-il identique pour l’ensemble des équipiers ?</label>';
content += '			<input type="checkbox" name="dossard_equipe[' + id + ']" id="relais-dossard_equipe-' + id + '" value="oui" data-render="switchery" data-theme="blue" onchange="etat_change_checkbox(\'relais-dossard_equipe-' + id + '\');"/>';
content += '		</div>';
content += '	</div>';
content += '	</span>';
content += '</div>';

content += '<div class="form-group"><hr>';

content += '	<div class="col-md-5">';
content += '		<div class="form-inline">';
content += '			<label class="control-label">Les coureurs doivent-il présenter une licence ou un certificat médical ?</label>';
content += '			<input type="checkbox" name="certif_medical[' + id + ']" id="certif_medical-' + id + '" value="1" data-render="switchery" data-theme="blue" onchange="etat_change_checkbox(\'certif_medical-' + id + '\');etat_change_checkbox_button(\'certif_medical-' + id + '\');"/>';
content += '		</div>';
content += '	</div>';

content += '	<div class="col-md-5">';
content += '		<div class="form-inline" id="div-certif_medical-' + id + '" style="display:none">';
content += '			<label class="control-label">La fourniture du document est-elle obligatoire à l’inscription ?</label>';
content += '			<input type="checkbox" name="certif_medical_obligatoire[' + id + ']" id="button-certif_medical-' + id + '" value="oui" data-render="switchery" data-theme="blue"  />';
content += '			<label class="control-label">date et heure limite de dépôt de ce dernier sur le site web </label>';
content += '			<input class="form-control" id="date_max_certif_timepicker_' + id + '" name="epre_parc_max_date_certif[' + id + ']" value="" type="text"  class="form-control" placeholder="Date max dépose des certificats" />';
content += '		</div>';
content += '	</div>';
content += '</div>';

content += '<div class="form-group"><hr>';				
content += '	<div class="col-md-5">';
content += '		<div class="form-inline m-t-25">';
content += '			<label class="control-label">Souhaitez vous recueillir une autorisation parentale pour les mineurs?</label>';
content += '			<input type="checkbox" name="auto-parentale[' + id + ']" id="auto_parentale-' + id + '" value="1" data-render="switchery" data-theme="blue" />';
content += '		</div>';
content += '	</div>';
content += '</div>';

content += '<div class="form-group"><hr>';

content += '	<div class="col-md-4">';
content += '		<div class="form-inline m-t-25">';
content += '			<label class="control-label">Les inscrits sont-ils visibles depuis la liste des engagés ?</label>';
content += '			<input type="checkbox" name="visible_liste_inscrit[' + id + ']" id="button-visible_liste_inscrit-' + id + '" value="oui" data-render="switchery" data-theme="blue"  checked/>';
content += '		</div>';
content += '	</div>';
content += '</div>';

content += '</fieldset>';

content += '<fieldset>';
content += '	<legend>Affectation des Dossards</legend>';
content += '	<div class="row row-margin-left">';
content += '		<div class="form-group">';
content += '			<label class="control-label col-md-5 col-sm-5">Dossard de * :</label>';
content += '			<div class="col-md-2 col-sm-2">';
content += '				<input type="text" data-parsley-type="digits" data-parsley-group="wizard-step-2" name="parc_dossard[' + id + ']" maxlength="10" class="form-control"/>';
content += 				'</div>';
content += '		</div>';												
content += '		<div class="form-group">';
content += '			<label class="control-label col-md-5 col-sm-5">à * :</label>';
content += '			<div class="col-md-2 col-sm-2">';
content += '				<input type="text" data-parsley-type="digits" data-parsley-group="wizard-step-2" name="parc_dossardFin[' + id + ']" maxlength="10" class="form-control"/>';
content += '			</div>';
content += '		</div>';
content += '	</div>';
content += '	<div class="row row-margin-left">';
content += '		<div class="form-inline m-t-25 m-b-25">';
content += '			<label class="control-label">Souhaitez vous des plages d\'exclusion (7 max) ? (laisser vide si vous ne souhaitez pas en mettre)</label>';
content += '			<input type="checkbox" id="plage-exclusion-' + id + '" data-render="switchery" data-theme="blue" <?php if ($f->parc_nbexclusion[1] > 0) echo "style=\"display:visible\""; else echo "style=\"display:none\""; ?> onchange="etat_change_checkbox(\'plage-exclusion-' + id + '\');"/>';
content += '		</div>';
content += '	</div>';
content += '	<div class="row row-margin-left" id="div-plage-exclusion-' + id + '" style="display:none">';
<?php 	

for ($jj=1;$jj<=7;$jj++) 
{
?>
content += '		<div class="form-group">';
content += '			<label class="control-label col-md-3 col-sm-3">Excusion N°<?php echo $jj;?> >>Dossard de * :</label>';
content += '			<div class="col-md-2 col-sm-2">';
content += '				<input type="text" data-parsley-type="digits" id="parc_dossard_exclus_min[' + id + '][<?php echo $jj; ?>]" name="parc_dossard_exclus_min[' + id + '][<?php echo $jj; ?>]" maxlength="10" class="form-control" onchange="calcul_nb_dossard_exclu(' + id + ')"/>';
content += '				<input type="text"  name="parc_dossard_exclus_min_control[' + id + '][<?php echo $jj; ?>]" size="1" maxlength="10" style="display:none">';															
content += '			</div>';
content += '		<div class="col-md-1 col-sm-1">';												
content += '			<label class="control-label">à * :</label>';
content += '		</div>';
content += '		<div class="col-md-2 col-sm-2">';
content += '			<input type="text" data-parsley-type="digits" id="parc_dossard_exclus_max[' + id + '][<?php echo $jj; ?>]" name="parc_dossard_exclus_max[' + id + '][<?php echo $jj; ?>]" maxlength="10" class="form-control" onchange="calcul_nb_dossard_exclu(' + id + ')"/>';
content += '			<input type="text"  name="parc_dossard_exclus_max_control[' + id + '][<?php echo $jj; ?>]" size="1" maxlength="10" style="display:none">';
content += '		</div>';
content += '	</div>';
<?php }	?>
content += '	</div>';
content += '<input type="hidden" id="epre_parc_nbexclusion[' + id + ']" name="epre_parc_nbexclusion[' + id + ']" value="0">';
content += '</fieldset>';

content += '<fieldset>';
content += '	<legend class="pull-left width-full">Option supplémentaires payantes pour le parcours ';
content += '	</legend>';
content += '<div class="panel-body">';
content += '	<p class="text-center">';
content += '		<span class="alert alert-danger fade in m-b-15" href="javascript:;" <i class="fa fa-2x fa-money"></i><strong>Vous pourrez après enregistrement de votre épreuve, ajouter des options complémentaires payantes pour ce parcours</strong></span>';
content += '</p>';
content += '</div>';
content += '</fieldset>';

content += '<fieldset>';
content += '	<legend>Tarifs associés</legend>';
content += '		<div class="row row-margin-left m-l-10">';
content += '			<div class="form-group col-md-1 text-left">';
content += '				<label class="control-label num">N°</label>';
content += '			</div>';
content += '			<div class="form-group col-md-2 m-r-5 text-left">';
content += '				<label class="control-label num">Nom</label>';
content += '			</div>';
content += '			<div class="form-group col-md-1 m-r-5 text-left">';
content += '				<label class="control-label num">Prix</label>';
content += '			</div>';
content += '			<div class="form-group date-time-font col-md-5 m-r-5 text-left">';
content += '				<label class="control-label num">Date de départ et de fin du tarif</label>';
content += '			</div>';
content += '			<div class="form-group col-md-3 text-left">';
content += '				<label class="control-label num">Réduction / places max / prises';
content += '					<button class="btn btn-info btn-xs" type="button" data-toggle="modal" data-target="#ModalHelpILReduc">';
content += '						<i class="fa fa-question-circle"></i>';
content += '					</button>';
content += '				</label>';
content += '			</div>';											
content += '		</div>';

<?php for ($jj=1;$jj<=3;$jj++) { ?>
content += '	<div class="row row-margin-left m-l-10">';
content += '		<div class="form-group col-md-1">';
content += '			<label class="control-label num"><?php echo $jj; ?></label>';
content += '		</div>';
content += '		<div class="form-group col-md-2 m-r-5">';
content += '			<input type="text" name="epre_parc_descprix[' + id + '][<?php echo $jj; ?>]" id="epre_parc_descprix[' + id + '][<?php echo $jj; ?>]"  class="form-control" placeholder="Description" onchange="calcul_nb_tarif(' + id + ');" >';
content += '		</div>';
content += '		<div class="form-group col-md-1 m-r-5">';
content += '			<input type="text"  name="epre_parc_prix[' + id + '][<?php echo $jj; ?>]" id="epre_parc_prix[' + id + '][<?php echo $jj; ?>]" class="form-control" placeholder="€">';
content += '		</div>';
content += '		<div class="form-group date-time-font col-md-5 m-r-5 ">';
content += '			<div class="input-group">';
content += '            <span class="input-group-addon"> Du </span>';
content += '				<input id="date_timepicker_start_tarifs_' + id + '_<?php echo $jj; ?>" name="date_debut_tarif[' + id + '][<?php echo $jj; ?>]" type="text"  class="form-control" />';
content += '				<span class="input-group-addon"> au </span>';
content += '				<input id="date_timepicker_end_tarifs_' + id + '_<?php echo $jj; ?>" name="date_fin_tarif[' + id + '][<?php echo $jj; ?>]" type="text"  class="form-control" />';
content += '			</div>';
content += '		</div>';

content += '		<div class="form-group col-md-1 m-r-5">';
content += '			<input type="text"  name="epre_parc_prix_nb_dossard[' + id + '][<?php echo $jj; ?>]" id="epre_parc_prix_nb_dossard[' + id + '][<?php echo $jj; ?>]" class="form-control" placeholder="€">';
content += '		</div>';
content += '		<div class="form-group col-md-1 m-r-5">';
content += '			<input type="text"  name="epre_parc_places_nb_dossard[' + id + '][<?php echo $jj; ?>]" id="epre_parc_places_nb_dossard[' + id + '][<?php echo $jj; ?>]" class="form-control" placeholder="place(s)">';
content += '		</div>';
content += '		<div class="form-group col-md-1 m-t-5">';
content += '			<span class="control-label num"></span>';
content += '		</div>	';
		
content += '	</div>';
<?php } ?>
content += '	<div class="row row-margin-left m-l-10" >';
content += '		<div class="form-inline m-t-25 m-b-25">';
content += '			<label class="control-label">Encore plus de tarifs ?</label>';
content += '			<input type="checkbox" id="plus-de-tarif-' + id + '" data-render="switchery" data-theme="blue" data-change="check-switchery-plus-de-tari" onchange="etat_change_checkbox(\'plus-de-tarif-' + id + '\');"/>';
content += '		</div>';
content += '	</div>';
content += '<div class="" id="div-plus-de-tarif-' + id + '" style="display:none">';
<?php for ($jj=4;$jj<=12;$jj++) { ?>
content += '	<div class="row row-margin-left m-l-10">';
content += '		<div class="form-group col-md-1">';
content += '			<label class="control-label num"><?php echo $jj; ?></label>';
content += '		</div>';
content += '		<div class="form-group col-md-2 m-r-5">';
content += '			<input type="text" name="epre_parc_descprix[' + id + '][<?php echo $jj; ?>]" id="epre_parc_descprix[' + id + '][<?php echo $jj; ?>]"  class="form-control" placeholder="Description" onchange="calcul_nb_tarif(' + id + ');">';
content += '		</div>';
content += '		<div class="form-group col-md-1 m-r-5">';
content += '			<input type="text"  name="epre_parc_prix[' + id + '][<?php echo $jj; ?>]" id="epre_parc_prix[' + id + '][<?php echo $jj; ?>]" class="form-control" placeholder="€">';
content += '		</div>';
content += '		<div class="form-group date-time-font col-md-5 m-r-5 ">';
content += '			<div class="input-group">';
content += '            	<span class="input-group-addon"> Du </span>';
content += '				<input id="date_timepicker_start_tarifs_' + id + '_<?php echo $jj; ?>" name="date_debut_tarif[' + id + '][<?php echo $jj; ?>]" type="text"  class="form-control" />';
content += '				<span class="input-group-addon"> au </span>';
content += '				<input id="date_timepicker_end_tarifs_' + id + '_<?php echo $jj; ?>" name="date_fin_tarif[' + id + '][<?php echo $jj; ?>]" type="text"  class="form-control" />';
content += '			</div>';
content += '		</div>';

content += '		<div class="form-group col-md-1 m-r-5">';
content += '			<input type="text"  name="epre_parc_prix_nb_dossard[' + id + '][<?php echo $jj; ?>]" id="epre_parc_prix_nb_dossard[' + id + '][<?php echo $jj; ?>]" class="form-control" placeholder="€">';
content += '		</div>';
content += '		<div class="form-group col-md-1 m-r-5">';
content += '			<input type="text"  name="epre_parc_places_nb_dossard[' + id + '][<?php echo $jj; ?>]" id="epre_parc_places_nb_dossard[' + id + '][<?php echo $jj; ?>]" class="form-control" placeholder="place(s)">';
content += '		</div>';
content += '		<div class="form-group col-md-1 m-t-5">';
content += '			<span class="control-label num"></span>';
content += '		</div>	';
	
content += '	</div>';
<?php } ?>
content += '</div>';
content += '<input type="hidden" id="epre_parc_nbprix[' + id + ']" name="epre_parc_nbprix[' + id + ']">';
content += '</fieldset>';

content += '<fieldset>';
content += '	<legend class="pull-left width-full">Codes promotions';
content += '	</legend>';
content += '<div class="panel-body">';
content += '	<p class="text-center">';
content += '		<span class="alert alert-danger fade in m-b-15" href="javascript:;" <i class="fa fa-2x fa-money"></i><strong>Vous pourrez après enregistrement de votre épreuve, ajouter des codes promos pour ce parcours</strong></span>';
content += '</p>';
content += '</div>';
content += '</fieldset>';

content += '<fieldset>';
content += '			<legend class="pull-left width-full">Insérez ici les  informations transmises au coureur dans l’email de confirmation d’inscription</legend>';
content += '				<div class="row-margin-left">';
content += '					<div class="panel-body panel-form m-b-20">';
content += '						<textarea name="info_complementaire_parcours[' + id + ']" class="textarea form-control" id="info_complementaire_parcours_wysihtml5_' + id + '" placeholder="Historique, dénivelé, ambiance, accès, douche, repas..." rows="12"></textarea>';
content += '					</div>';
content += '				</div>';
content += '</fieldset>';

content += '<fieldset>';
content += '	<legend class="pull-left width-full">Champs supplémentaires';
content += '	</legend>';
content += '		<div class="panel-body">';
content += '			<p class="text-center">';
content += '				<span class="alert alert-danger fade in m-b-15" href="javascript:;" <i class="fa fa-2x fa-money"></i><strong>Vous pourrez après enregistrement de votre épreuve, ajouter des questions complémentaires pour ce parcours</strong></span>';
content += '			</p>';
content += '		</div>';
content += '</fieldset>';

content += '<fieldset>';
content += '	<legend class="pull-left width-full">Fichiers supplémentaires pour ce parcours</legend>';
content += '		<div class="col-sm-12">';
content += '			<div class="panel-body panel-form">';
content += '				<div class="form-group">';
content += '					<label class="control-label"><b>5</b> fichiers maximum. Formats autorisés : <b>.jpg, .gif , .png ou .pdf, 2Mo maximum</b></label>';
content += '					<input id="fichier_parcours_sup_' + id + '" name="fichier_parcours_sup_' + id + '[]" type="file" class="file" multiple >';
content += '				</div>';
content += '			</div>';
content += '		</div>';
content += '</fieldset>';

content += '<fieldset>';
content += '			<legend class="pull-left width-full">Description du parcours</legend>';
content += '				<div class="row-margin-left">';
content += '					<div class="panel-body panel-form m-b-20">';
content += '						<textarea name="parcours_description[' + id + ']" class="textarea form-control" id="parcours_description_wysihtml5_' + id + '" placeholder="Historique, dénivelé, ambiance, accès, douche, repas..." rows="12"></textarea>';
content += '					</div>';
content += '				</div>';
content += '</fieldset>';

return content;
}

	function calcul_nb_tarif(nb) {
			var nb_val_tarif = 0;

			for (i=1;i<13;i++)
			{
				if ($("#epre_parc_descprix\\[" + nb +"\\]\\[" + i + "\\]").val()) {
					nb_val_tarif++;
					}
			}
			$("#epre_parc_nbprix\\[" + nb +"\\]").val(nb_val_tarif);
		//});
	}
	
	function calcul_nb_dossard_exclu(nb) {
			var nb_val_dossard_exclu = 0;

			for (i=1;i<21;i++)
			{
				if (($("#parc_dossard_exclus_min\\[" + nb +"\\]\\[" + i + "\\]").val()) && ($("#parc_dossard_exclus_max\\[" + nb +"\\]\\[" + i + "\\]").val())) {
					nb_val_dossard_exclu++;
					}
			}
			$("#epre_parc_nbexclusion\\[" + nb +"\\]").val(nb_val_dossard_exclu);
	}


function aff_select_reglement(valeur) {

	$('#aff_reglement_'+ valeur).show();
	
	if (valeur == 'url') { 
		
		$('#aff_reglement_fichier').hide();
		$('#aff_reglement_texte').hide();
	}
	else if (valeur == 'fichier') {
			
		var input_epre_reglement_fichier = $("#epre_reglement_fichier");
		input_epre_reglement_fichier.fileinput({
				overwriteInitial: false,
				uploadUrl:'submit_file.php',
				uploadAsync: false,
				dropZoneEnabled: false,
				showPreview: true,
				showUpload: false, // hide upload button
				showRemove: false, // hide remove button
				allowedFileExtensions : ['pdf'],
				maxFileSize: 2000,
				maxFileCount: 1,
				<?php if ($f->reglement_fichier_rep != '') { ?>
					<?php if ($f->idEpreuve != '') { ?>
					
							initialPreview: [
								"<img src='<?php echo $f->reglement_fichier_rep; ?>' class='file-preview-image'>",
							],
					<?php } ?>	
					<?php if (($_GET['epre_button'] == 'Modifier la fiche de cette course')) { ?>
							initialPreviewConfig: [
								{caption: "<?php echo $f->reglement_fichier_affichage; ?>", width: "120px", url: "<?php echo $f->url_del_reglement_fichier; ?>", key: 1},
							],
							uploadExtraData: function() {
								return {
									update: 1,
									idEpreuve: <?php echo $f->idEpreuve; ?>
								};
							},
					<?php } elseif($f->idEpreuve != '') { ?>
							initialPreviewConfig: [
								{caption: "<?php echo $f->reglement_fichier_affichage; ?>", width: "120px", key: 1},
							],
					<?php } ?>			
				<?php } ?>	
			}).on("filebatchselected", function(event, files) {
			// trigger upload method immediately after files are selected
			input_epre_reglement_fichier.fileinput("upload");
		});
		$('#aff_reglement_url').hide();
		$('#aff_reglement_texte').hide();
	}
	
	else {
		$('#aff_reglement_fichier').hide();
		$('#aff_reglement_url').hide();
	}
}



function nombre_alea(){
	var nbr_ch=2; //  generation d'un nombre a 5 chiffres modifier si besoin
	var x=Math.random();
	var nb=x*Math.pow(10,nbr_ch);
	nb_g=Math.round(nb);
	return nb_g;
}

$(".nav-tabs").on("click", "a", function (e) {
        e.preventDefault();
        if (!$(this).hasClass('add-parcours')) {
			//console.log(this);
			//console.log($(this).show());
            $(this).tab('show');
			//$(this).addClass('badge badge-primary');
        }
		//$('#badge_parcours_1').addClass('badge badge-primary');
    })
    .on("click", "span", function (e) {
        e.preventDefault();
		var anchor = $(this).siblings('a');
        $(anchor.attr('href')).remove();
        $(this).parent().remove();
        $(".nav-tabs li").children('a').first().click();
		$('#epre_nbparc').val($("input[id*='epre_parc_nom']").length);
		
    });

$('.add-parcours').click(function (e) {
    
	e.preventDefault();

	var id= nombre_alea();
	
    var tabId = 'parcours_' + id;

content = parcours_content(id);

    $(this).closest('li').before('<li id="P' + id +'"><a id="name_parcours_' + id +'" href="#parcours_' + id + '">Nouveau parcours</a> <span><i class="fa fa-times"></i></span></li>');
    $('.tab-content').append('<div class="tab-pane" id="' + tabId + '">' + content + '</div>');
	
   var id_tmp = $(".nav-tabs").children().length;
   $('.nav-tabs li:nth-child(' + (id_tmp-1) + ') a').click();
	
   FormSliderSwitcher.init("#relais-dossard_equipe-"+id+", #auto_parentale-"+id + ",#button-visible_liste_inscrit-" + id + ", #champ_supp_" + id + ",#certif_medical-" + id + ",#button-certif_medical-" + id + ",#relais-" + id + ",#age-" + id + ",#plus-de-tarif-" + id + ",#plage-exclusion-" + id + ", #affichage_code_promo_" + id);

   $('#epre_nbparc').val($("input[id*='epre_parc_nom']").length);
   
   	handelTooltipPopoverActivation();
	handleFormWysihtml5Parcours('parcours_description_wysihtml5_' + id);
	handleFormWysihtml5Parcours('info_complementaire_parcours_wysihtml5_' + id);
	//Activation date et heure de départ de la course
	  $('#date_parcours_timepicker_start_'+ id).datetimepicker({
		format:'d/m/Y H:i',
		lang:'fr',
		step:15
 });

 //Activation date et heure dépose max certif
 $('#date_max_certif_timepicker_'+ id).datetimepicker({
		format:'d/m/Y H:i',
		lang:'fr',
		step:15
 });
	//Activation date et heure de départ code_promo
  $('#date_timepicker_start_code_promo_'+ id).datetimepicker({
  format:'d/m/Y H:i',
  lang:'fr',
  onShow:function( ct ){
   this.setOptions({
    maxDate:getDate($('#date_timepicker_end_code_promo_'+ id).val())?getDate($('#date_timepicker_end_code_promo_'+ id).val()):false
   })
  },
  timepicker:true
 });
 $('#date_timepicker_end_code_promo_'+ id).datetimepicker({
  format:'d/m/Y H:i',
    lang:'fr',
  onShow:function( ct ){
   this.setOptions({
    minDate:getDate($('#date_timepicker_start_code_promo_'+ id).val())?getDate($('#date_timepicker_start_code_promo_'+ id).val()):false
   })
  },
  timepicker:true
 });
	//Activation des dates et heures des tarifs
<?php for ($nb=1;$nb<=12;$nb++) { ?>

		$('#date_timepicker_start_tarifs_'+ id + '_' + <?php echo $nb; ?>).datetimepicker({
		format:'d/m/Y H:i',
		lang:'fr',
		step:15,
		onShow:function( ct ){
		this.setOptions({
			minDate:new Date(),
			maxDate:getDate($('#date_timepicker_end_tarifs_'+ id + '_' + <?php echo $nb; ?>).val())?getDate($('#date_timepicker_end_tarifs_'+ id + '_' + <?php echo $nb; ?>).val()):false
		})
		},
		});
	
		$('#date_timepicker_end_tarifs_'+ id + '_' + <?php echo $nb; ?>).datetimepicker({
		format:'d/m/Y H:i',
			lang:'fr',
			step:15,
		onShow:function( ct ){
		this.setOptions({
			minDate:getDate($('#date_timepicker_start_tarifs_'+ id + '_' + <?php echo $nb; ?>).val())?getDate($('#date_timepicker_start_tarifs_'+ id + '_' + <?php echo $nb; ?>).val()):false
		})
		},
	});
<?php } ?>	
	
	//gestion des fichiers	
	var inputfps = new Array();		
	inputfps[id] = $("#fichier_parcours_sup_" + id);
	inputfps[id].fileinput({
		uploadUrl:'submit_file.php',
		uploadAsync: false,
		showUpload: false, // hide upload button
		showRemove: false, // hide remove button
		allowedFileExtensions : ['jpg', 'png','gif','pdf'],
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



<?php if ($f->nbparc > 1 ) { 
	for($nbparc=2;$nbparc<($f->nbparc+1);$nbparc++ ) { ?>

		var tabId = 'parcours_' + <?php echo $nbparc; ?>;

content ='<fieldset>';
content += '<input type="hidden" id="id_table_parcours['+ <?php echo $nbparc; ?> +']" name="id_table_parcours['+ <?php echo $nbparc; ?> +']" value="<?php echo $tab_id[$nbparc][0]; ?>" />';
content += '<legend class="pull-left width-full">Identification</legend>';
content += '<div class="row-margin-left">';
content += '	<div class="col-md-4">';
content += '		<div class="form-group m-r-20 input-group-lg">';
content += '			<h4>Nom de la course*</h4>';
content += '			<input type="text" value="<?php echo $f->parc_nom[$nbparc]; ?>" name="epre_parc_nom['+ <?php echo $nbparc; ?> +']" id="epre_parc_nom'+ <?php echo $nbparc; ?> +'" placeholder="Nom du parcours" class="form-control input-lg" data-parsley-group="wizard-step-2" onchange="$(\'a#name_parcours_'+ <?php echo $nbparc; ?> +'\').text(this.value);" required />';
content += '		</div>';
content += '	</div>';

content += '	<div class="col-md-3">';
content += '		<div class="form-group m-r-20">';
content += '			<h4>Date et heure de départ </h4>';
content += '				<div class="input-group input-group-lg">';
content += '					<input id="date_parcours_timepicker_start_' + <?php echo $nbparc; ?> + '" name="epre_parc_date[' + <?php echo $nbparc; ?> + ']" value="<?php echo $f->parc_date[$nbparc];?>" type="text"  class="form-control" placeholder="Date de départ" />';
content += '					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>';
content += '				</div>'; 
content += '		</div>';
content += '	</div>';

content += '	<div class="col-md-3">';
content += '		<div class="form-group">';
content += '		<h4>Format de la course </h4>';
content += '			<div class="input-group input-group-lg">';
<?php $type = ''; ?>
content += '				<SELECT class="form-control" name="epre_parc_type[' + <?php echo $nbparc; ?> + ']" id="epre_parc_type[' + <?php echo $nbparc; ?> + ']">';
<?php foreach ($f->typeparcours as $k=>$i) { ?>
	<?php if( $type != $f->typeepreuve[$i[1]] ) : ?>
content += '					<OPTGROUP label="<?php echo $f->typeepreuve[$i[1]]; ?>">';
	<?php endif; ?>
content += '					<OPTION VALUE="<?php echo $k; ?>"<?php if($f->parc_type[$nbparc] == $k) echo " SELECTED"; else echo ""; ?>><?php echo $i[0]; ?></OPTION>';
<?php $type = $f->typeepreuve[$i[1]]; ?>
	<?php if( $type != $f->typeepreuve[$i[1]] ) : ?>
content += '					</OPTGROUP>';
	<?php endif; ?>
<?php } ?>
content += '					<OPTION VALUE="-1">Autre</OPTION>';
content += '				</SELECT>';
content += '			</div>';
content += '		</div>';
content += '	</div>';                                 
content += '</div>';

content += '<div class="col-md-2">';
content += '	<div class="form-group m-r-20 input-group-lg">';
content += '		<h4>Ordre d\'affichage</h4>';
content += '		<input type="text" name="epre_parc_ordre['+ <?php echo $nbparc; ?> +']" id="epre_parc_ordre'+ <?php echo $nbparc; ?> +'" value="<?php echo $f->parc_ordre[$nbparc]; ?>" class="form-control input-group-lg" data-parsley-group="wizard-step-2" required />';
content += '	</div>';
content += '</div>';

content += '<div class="row-margin-left">';

content += '<div class="form-group">';	

content += '<label class="col-md-2 control-label m-t-25">Tranche d\'âge autorisé  : De</label>';
content += '<div class="col-md-3 m-t-25">';
content += '	<div class="input-group">';
content += '		<select class="form-control" name="epre_parc_age_debut['+ <?php echo $nbparc; ?> +']">';
<?php for ($i=0;$i<=150;$i++) { ?>
content += '			<OPTION VALUE="<?php echo $i; ?>" <?php if($f->parc_age_debut[$nbparc] == $i) { echo " SELECTED"; } ?>><?php echo $i; ?></OPTION>';
<?php } ?>
content += '		</select>';
content += '		<span class="input-group-addon">a</span>';
content += '		<select class="form-control" name="epre_parc_age_fin['+ <?php echo $nbparc; ?> +']">';
<?php for ($i=0;$i<=150;$i++) { ?>
content += '			<OPTION VALUE="<?php echo $i; ?>" <?php if($f->parc_age_fin[$nbparc] == $i) { echo " SELECTED"; } ?>><?php echo $i; ?></OPTION>';
<?php } ?>
content += '		</select>';
content += '<span class="input-group-addon"> ans</span>';
content += '	</div>';
content += '</div>';
content += '</div>';

content += '<div class="form-group"><hr>';

content += '	<div class="col-md-3">';
content += '		<div class="form-inline">';
content += '			<label class="control-label">S\'agit-il d’un parcours individuel ou par équipe ?</label>';
content += '			<input type="checkbox" id="relais-'+ <?php echo $nbparc; ?> +'" name="relais['+ <?php echo $nbparc; ?> +']" value="1" data-render="switchery" data-theme="blue" <?php if ($f->relais[$nbparc] > 0) echo "checked"; else echo ""; ?> onchange="etat_change_checkbox(\'relais-'+ <?php echo $nbparc; ?> +'\');"/>';
content += '		</div>';
content += '	</div>';

content += '<span <?php if ($f->relais[$nbparc] > 0) echo "style=\"display:visible\""; else echo "style=\"display:none\""; ?> id="div-relais-'+ <?php echo $nbparc; ?> +'"> ';
content += '	<div class="col-md-2" >';
content += '		<div class="form-inline">';
content += '			<label>Nombre de personne ?</label>';
content += '				<select class="form-control" name="relais_nb_personne['+ <?php echo $nbparc; ?> +']">';
<?php for ($i=2;$i<=10;$i++) { ?>
content += '					<OPTION VALUE="<?php echo $i; ?>" <?php if($f->relais[$nbparc] == $i) echo " SELECTED"; else echo ""; ?>><?php echo $i; ?></OPTION>';
<?php } ?>
content += '				</select>';
content += '		</div>';
content += '	</div>';

//
content += '	<div class="col-md-2" >';
content += '		<div class="form-inline">';
content += '			<label>Nombre de personne min ? (laisser 0 pour fixer le nombre de personne à celui de max</label>';
content += '				<select class="form-control" name="relais_nb_personne_min['+ <?php echo $nbparc; ?> +']">';
<?php for ($i=0;$i<=10;$i++) { ?>
content += '					<OPTION VALUE="<?php echo $i; ?>" <?php if($f->relais_min[$nbparc] == $i) echo " SELECTED"; else echo ""; ?>><?php echo $i; ?></OPTION>';
<?php } ?>
content += '				</select>';
content += '		</div>';
content += '	</div>';

content += '	<div class="col-md-6" >';
content += '		<div class="form-inline">';
content += '			<label class="control-label">Le Numéro de dossard est-il identique pour l’ensemble des équipiers ?</label>';
content += '			<input type="checkbox" name="dossard_equipe['+ <?php echo $nbparc; ?> +']" id="relais-dossard_equipe-'+ <?php echo $nbparc; ?> +'" value="oui" data-render="switchery" data-theme="blue" <?php if ($f->dossard_equipe[$nbparc] == 'oui') echo "checked"; else echo ""; ?> onchange="etat_change_checkbox(\'relais-dossard_equipe-'+ <?php echo $nbparc; ?> +'\');"/>';
content += '		</div>';
content += '	</div>';

content += '</div>';
content += '</span>';
content += '<div class="form-group"><hr>';

content += '	<div class="col-md-5">';
content += '		<div class="form-inline">';
content += '			<label class="control-label">Les coureurs doivent-il présenter une licence ou un certificat médical ?</label>';
content += '			<input type="checkbox" name="certif_medical['+ <?php echo $nbparc; ?> +']" id="certif_medical-'+ <?php echo $nbparc; ?> +'" value="1" data-render="switchery" data-theme="blue" <?php if ($f->certif_medical[$nbparc] == 1) echo "checked"; else echo ""; ?> onchange="etat_change_checkbox(\'certif_medical-'+ <?php echo $nbparc; ?> +'\');etat_change_checkbox_button(\'certif_medical-'+ <?php echo $nbparc; ?> +'\');"/>';
content += '		</div>';
content += '	</div>';

content += '	<div class="col-md-5">';
content += '		<div class="form-inline" <?php if ($f->certif_medical[$nbparc] == 1) echo "style=\"display:visible\""; else echo "style=\"display:none\""; ?> id="div-certif_medical-'+ <?php echo $nbparc; ?> +'">';
content += '			<label class="control-label">La fourniture du document est-elle obligatoire à l’inscription ?</label>';
content += '			<input type="checkbox" name="certif_medical_obligatoire['+ <?php echo $nbparc; ?> +']" id="button-certif_medical-'+ <?php echo $nbparc; ?> +'" value="oui" data-render="switchery" data-theme="blue" <?php if ($f->certif_medical_obligatoire[$nbparc] == 'oui') echo "checked"; else echo ""; ?> />';
content += '			<label class="control-label">date et heure limite de dépôt de ce dernier sur le site web</label>';
content += '			<input class="form-control" id="date_max_certif_timepicker_'+ <?php echo $nbparc; ?> +'" name="epre_parc_max_date_certif['+ <?php echo $nbparc; ?> +']" value="<?php echo $f->parc_max_date_certif[$nbparc]; ?>" type="text"  class="form-control" placeholder="Date max dépose des certificats" />';
content += '		</div>';
content += '	</div>';
content += '</div>';

content += '<div class="form-group"><hr>';	
content += '	<div class="col-md-5">';
content += '		<div class="form-inline">';
content += '			<label class="control-label">Souhaitez vous recueillir une autorisation parentale pour les mineurs?</label>';
content += '			<input type="checkbox" name="auto_parentale['+ <?php echo $nbparc; ?> +']" id="auto_parentale-'+ <?php echo $nbparc; ?> +'" value="1" data-render="switchery" data-theme="blue" <?php if ($f->auto_parentale[$nbparc] == 1) echo "checked"; else echo ""; ?>/>';
content += '		</div>';
content += '	</div>';
content += '</div>';

content += '<div class="form-group"><hr>';	
content += '	<div class="col-md-4">';
content += '		<div class="form-inline m-t-25">';
content += '			<label class="control-label">Les inscrits sont-ils visibles depuis la liste des engagés ?</label>';
content += '			<input type="checkbox" name="visible_liste_inscrit['+ <?php echo $nbparc; ?> +']" id="button-visible_liste_inscrit-'+ <?php echo $nbparc; ?> +'" value="oui" data-render="switchery" data-theme="blue" <?php if ($f->visible_liste_inscrit[$nbparc] == 'oui') echo "checked"; else echo ""; ?>/>';
content += '		</div>';
content += '	</div>';
content += '</div>';

content += '</fieldset>';

content += '<fieldset>';
content += '	<legend>Affectation des Dossards</legend>';
content += '	<div class="row row-margin-left">';
content += '		<div class="form-group">';
content += '			<label class="control-label col-md-5 col-sm-5">Dossard de * :</label>';
content += '			<div class="col-md-2 col-sm-2">';
content += '				<input type="text" data-parsley-type="digits" data-parsley-group="wizard-step-2" name="parc_dossard['+ <?php echo $nbparc; ?> +']" maxlength="10" class="form-control" required value="<?php if ($f->parc_dossard[$nbparc] != "") echo $f->parc_dossard[$nbparc]; else echo "";?>"/>';
content += 				'</div>';
content += '		</div>';												
content += '		<div class="form-group">';
content += '			<label class="control-label col-md-5 col-sm-5">à * :</label>';
content += '			<div class="col-md-2 col-sm-2">';
content += '				<input type="text" data-parsley-type="digits" data-parsley-group="wizard-step-2" name="parc_dossardFin['+ <?php echo $nbparc; ?> +']" maxlength="10" class="form-control" required value="<?php if ($f->parc_dossardFin[$nbparc] != "") echo $f->parc_dossardFin[$nbparc]; else echo "";?>"/>';
content += '			</div>';
content += '		</div>';
content += '	</div>';
content += '	<div class="row row-margin-left">';
content += '		<div class="form-inline m-t-25 m-b-25">';
content += '			<label class="control-label">Souhaitez vous des plages d\'exclusion (7 max) ? (laisser vide si vous ne souhaitez pas en mettre)</label>';
content += '			<input type="checkbox" id="plage-exclusion-'+ <?php echo $nbparc; ?> +'" data-render="switchery" data-theme="blue"  onchange="etat_change_checkbox(\'plage-exclusion-'+ <?php echo $nbparc; ?> +'\');" <?php if ($f->parc_nbexclusion[$nbparc][0] != 0) echo "checked"; else echo ""; ?>/>';
content += '		</div>';
content += '	</div>';
content += '	<div class="row row-margin-left" id="div-plage-exclusion-'+ <?php echo  $nbparc; ?> +'" <?php if ($f->parc_nbexclusion[$nbparc][0] != 0) echo "style=\'display:visible\'"; else echo "style=\'display:none\'"; ?>>';
<?php 	
$plage_exclusion = array();
$exclusion = array();

if (isset($f->parc_dossardExclus[$nbparc])) {
	$exclusion = explode(":",$f->parc_dossardExclus[$nbparc]);
	$nb_plage_exclusion = count($exclusion);

	for ($x=0;$x<$nb_plage_exclusion;$x++) { $plage_exclusion[] = explode("-",$exclusion[$x]); }
}
for ($jj=1;$jj<=7;$jj++) 
{
?>
content += '		<div class="form-group">';
content += '			<label class="control-label col-md-3 col-sm-3">Excusion N°<?php echo $jj;?> >>Dossard de * :</label>';
content += '			<div class="col-md-2 col-sm-2">';
content += '				<input type="text" data-parsley-type="digits" id="parc_dossard_exclus_min['+ <?php echo $nbparc; ?> +'][<?php echo $jj; ?>]" name="parc_dossard_exclus_min['+ <?php echo $nbparc; ?> +'][<?php echo $jj; ?>]" maxlength="10" class="form-control" value="<?php if($plage_exclusion[($jj-1)][0] != "") echo $plage_exclusion[($jj-1)][0]; else ""; ?>" onchange="calcul_nb_dossard_exclu('+ <?php echo $nbparc; ?> +')"/>';
content += '				<input type="text"  name="parc_dossard_exclus_min_control['+ <?php echo $nbparc; ?> +'][<?php echo $jj; ?>]" size="1" maxlength="10" style="display:none">';															
content += '			</div>';
content += '		<div class="col-md-1 col-sm-1">';												
content += '			<label class="control-label">à * :</label>';
content += '		</div>';
content += '		<div class="col-md-2 col-sm-2">';
content += '			<input type="text" data-parsley-type="digits" id="parc_dossard_exclus_max['+ <?php echo $nbparc; ?> +'][<?php echo $jj; ?>]" name="parc_dossard_exclus_max['+ <?php echo $nbparc; ?> +'][<?php echo $jj; ?>]" maxlength="10" class="form-control" onchange="calcul_nb_dossard_exclu('+ <?php echo $nbparc; ?> +')" value="<?php if ($plage_exclusion[($jj-1)][1] != "") echo $plage_exclusion[($jj-1)][1]; else echo ""; ?>"/>';
content += '			<input type="text"  name="parc_dossard_exclus_max_control['+ <?php echo $nbparc; ?> +'][<?php echo $jj; ?>]" size="1" maxlength="10" style="display:none">';
content += '		</div>';
content += '	</div>';
<?php }	?>
content += '	</div>';
content += '<input type="hidden" id="epre_parc_nbexclusion['+ <?php echo $nbparc; ?> +']" name="epre_parc_nbexclusion['+ <?php echo $nbparc; ?> +']" value="<?php  if(isset($f->parc_nbexclusion[$nbparc])) echo $f->parc_nbexclusion[$nbparc];else echo "0";?>">';
content += '</fieldset>';

content += '<fieldset>';
content += '	<legend class="pull-left width-full">Options supplémentaires payantes';
content += '	</legend>';

content += '<p class="text-center">';
content += '	<a class="btn btn-info m-r-5" href="javascript:;" onclick="affiche_modal(\'Gestion des options supplémentaires\',0,\'https://www.ats-sport.com/<?php echo URL_DEV; ?>admin/creation_options.php?epre_id=<?php echo $_GET['epre_id']; ?>&id_parcours=<?php echo $f->parc_id[$nbparc]; ?>\')" class="btn btn-danger m-b-15 m-l-5"><b>Option tarifées supplémentaires payantes au parcours</b></a>';
content += '</p>';

content += '</fieldset>';

content += '<fieldset>';
content += '	<legend>Tarifs associés</legend>';
content += '		<div class="row row-margin-left m-l-10">';
content += '			<div class="form-group col-md-1 text-left">';
content += '				<label class="control-label num">N°</label>';
content += '			</div>';
content += '			<div class="form-group col-md-2 m-r-5 text-left">';
content += '				<label class="control-label num">Nom</label>';
content += '			</div>';
content += '			<div class="form-group col-md-1 m-r-5 text-left">';
content += '				<label class="control-label num">Prix</label>';
content += '			</div>';
content += '			<div class="form-group date-time-font col-md-5 m-r-5 text-left">';
content += '				<label class="control-label num">Date de départ et de fin du tarif</label>';
content += '			</div>';
content += '			<div class="form-group col-md-3 text-left">';
content += '				<label class="control-label num">Réduction / places max / prises';
content += '					<button class="btn btn-info btn-xs" type="button" data-toggle="modal" data-target="#ModalHelpILReduc">';
content += '						<i class="fa fa-question-circle"></i>';
content += '					</button>';
content += '				</label>';
content += '			</div>';											
content += '		</div>';

<?php for ($jj=1;$jj<=3;$jj++) { ?>
content += '	<div class="row row-margin-left m-l-10">';
content += '		<div class="form-group col-md-1">';
content += '			<label class="control-label num"><?php echo $jj; ?> [ <?php echo $f->parc_tarif_inscrit[$nbparc][$jj]; ?> ]</label>';
content += '		</div>';
content += '		<div class="form-group col-md-2 m-r-5">';
content += '			<input type="text" name="epre_parc_descprix['+ <?php echo $nbparc; ?> +']['+<?php echo $jj; ?>+']" id="epre_parc_descprix['+ <?php echo $nbparc; ?> +'][<?php echo $jj; ?>]"  class="form-control" placeholder="Description" value="<?php echo $f->parc_descprix[$nbparc][$jj]; ?>" onchange="calcul_nb_tarif('+ <?php echo $nbparc; ?> +');" <?php echo $f->parc_tarif_inscrit_aff[$nbparc][$jj]; ?>>';
content += '		</div>';
content += '		<div class="form-group col-md-1 m-r-5">';
content += '			<input type="text"  name="epre_parc_prix['+ <?php echo $nbparc; ?> +']['+<?php echo $jj; ?>+']" id="epre_parc_prix['+ <?php echo $nbparc; ?> +']['+<?php echo $jj; ?>+']" class="form-control" placeholder="€" value="<?php echo $f->parc_prix[$nbparc][$jj]; ?>" <?php echo $f->parc_tarif_inscrit_aff[$nbparc][$jj]; ?>>';
content += '		</div>';

content += '		<div class="form-group date-time-font col-md-5 m-r-5 ">';
content += '			<div class="input-group">';
content += '            	<span class="input-group-addon"> Du </span>';
content += '				<input id="date_timepicker_start_tarifs_' + <?php echo $nbparc; ?> + '_<?php echo $jj; ?>" name="date_debut_tarif[' + <?php echo $nbparc; ?> + '][<?php echo $jj; ?>]" type="text"  class="form-control" value="<?php echo $f->date_debut_tarif[$nbparc][$jj]; ?>"/>';
content += '				<span class="input-group-addon"> au </span>';
content += '				<input id="date_timepicker_end_tarifs_' + <?php echo $nbparc; ?> + '_<?php echo $jj; ?>" name="date_fin_tarif[' + <?php echo $nbparc; ?> + '][<?php echo $jj; ?>]" type="text"  class="form-control" value="<?php echo $f->date_fin_tarif[$nbparc][$jj]; ?>"/>';
content += '			</div>';
content += '		</div>';	

content += '		<div class="form-group col-md-1 m-r-5">';
content += '			<input type="text"  name="epre_parc_prix_nb_dossard[' + <?php echo $nbparc; ?> + '][<?php echo $jj; ?>]" id="epre_parc_prix_nb_dossard[' + <?php echo $nbparc; ?> + '][<?php echo $jj; ?>]" value="<?php echo $f->parc_prix_nb_dossard[$nbparc][$jj]; ?>" class="form-control" placeholder="€">';
content += '		</div>';
content += '		<div class="form-group col-md-1 m-r-5">';
content += '			<input type="text"  name="epre_parc_places_nb_dossard[' + <?php echo $nbparc; ?> + '][<?php echo $jj; ?>]" id="epre_parc_places_nb_dossard[' + <?php echo $nbparc; ?> + '][<?php echo $jj; ?>]" value="<?php echo $f->parc_places_nb_dossard[$nbparc][$jj]; ?>" class="form-control" placeholder="place(s)">';
content += '		</div>';
content += '		<div class="form-group col-md-1 m-t-5">';
content += '			<span class="control-label num"><?php echo $f->parc_places_nb_dossard_restantes[$nbparc][$jj]; ?></span>';
content += '		</div>	';	

content += '	</div>';
<?php } ?>
content += '	<div class="row row-margin-left m-l-10" >';
content += '		<div class="form-inline m-t-25 m-b-25">';
content += '			<label class="control-label">Encore plus de tarifs ?</label>';
content += '			<input type="checkbox" id="plus-de-tarif-'+ <?php echo $nbparc; ?> +'" data-render="switchery" data-theme="blue" data-change="check-switchery-plus-de-tari" <?php if (isset($f->parc_descprix[$nbparc][4])) echo "checked"; else echo ""; ?> onchange="etat_change_checkbox(\'plus-de-tarif-'+ <?php echo $nbparc; ?> +'\');"/>';
content += '		</div>';
content += '	</div>';
content += '<div class="row row-margin-left m-l-10" id="div-plus-de-tarif-'+ <?php echo $nbparc; ?> +'" <?php if (isset($f->parc_descprix[$nbparc][4])) echo "style=\'display:visible\'"; else echo "style=\'display:none\'"; ?>>';
<?php for ($jj=4;$jj<=12;$jj++) { ?>
content += '	<div class="row row-margin-left m-l-10">';
content += '		<div class="form-group col-md-1">';
content += '			<label class="control-label num"><?php echo $jj; ?></label>';
content += '		</div>';
content += '		<div class="form-group col-md-2 m-r-5">';
content += '			<input type="text" name="epre_parc_descprix['+ <?php echo $nbparc; ?> +'][<?php echo $jj; ?>]" id="epre_parc_descprix['+ <?php echo $nbparc; ?> +'][<?php echo $jj; ?>]"  class="form-control" placeholder="Description" value="<?php echo $f->parc_descprix[$nbparc][$jj]; ?>" onchange="calcul_nb_tarif('+ <?php echo $nbparc; ?> +');">';
content += '		</div>';
content += '		<div class="form-group col-md-1 m-r-5">';
content += '			<input type="text"  name="epre_parc_prix['+ <?php echo $nbparc; ?> +'][<?php echo $jj; ?>]" id="epre_parc_prix['+ <?php echo $nbparc; ?> +'][<?php echo $jj; ?>]" class="form-control" placeholder="€" value="<?php echo $f->parc_prix[$nbparc][$jj]; ?>">';
content += '		</div>';

content += '		<div class="form-group date-time-font col-md-5 m-r-5 ">';
content += '			<div class="input-group">';
content += '            	<span class="input-group-addon"> Du </span>';
content += '				<input id="date_timepicker_start_tarifs_' + <?php echo $nbparc; ?> + '_<?php echo $jj; ?>" name="date_debut_tarif[' + <?php echo $nbparc; ?> + '][<?php echo $jj; ?>]" type="text"  class="form-control" value="<?php echo $f->date_debut_tarif[$nbparc][$jj]; ?>"/>';
content += '				<span class="input-group-addon"> au </span>';
content += '				<input id="date_timepicker_end_tarifs_' + <?php echo $nbparc; ?> + '_<?php echo $jj; ?>" name="date_fin_tarif[' + <?php echo $nbparc; ?> + '][<?php echo $jj; ?>]" type="text"  class="form-control" value="<?php echo $f->date_fin_tarif[$nbparc][$jj]; ?>"/>';
content += '			</div>';
content += '		</div>';

content += '		<div class="form-group col-md-1 m-r-5">';
content += '			<input type="text"  name="epre_parc_prix_nb_dossard[' + <?php echo $nbparc; ?> + '][<?php echo $jj; ?>]" id="epre_parc_prix_nb_dossard[' + <?php echo $nbparc; ?> + '][<?php echo $jj; ?>]" value="<?php echo $f->parc_prix_nb_dossard[$nbparc][$jj]; ?>" class="form-control" placeholder="€">';
content += '		</div>';
content += '		<div class="form-group col-md-1 m-r-5">';
content += '			<input type="text"  name="epre_parc_places_nb_dossard[' + <?php echo $nbparc; ?> + '][<?php echo $jj; ?>]" id="epre_parc_places_nb_dossard[' + <?php echo $nbparc; ?> + '][<?php echo $jj; ?>]" value="<?php echo $f->parc_places_nb_dossard[$nbparc][$jj]; ?>" class="form-control" placeholder="place(s)">';
content += '		</div>';
content += '		<div class="form-group col-md-1 m-t-5">';
content += '			<span class="control-label num"><?php echo $f->parc_places_nb_dossard_restantes[$nbparc][$jj]; ?></span>';
content += '		</div>	';	

content += '	</div>';
<?php } ?>
content += '</div>';
content += '<input type="hidden" id="epre_parc_nbprix['+ <?php echo $nbparc; ?> +']" name="epre_parc_nbprix['+ <?php echo $nbparc; ?> +']" value="<?php if(isset($f->parc_nbprix[$nbparc])) echo $f->parc_nbprix[$nbparc]; ?>">';
content += '</fieldset>';

content += '<fieldset>';
content += '	<legend class="pull-left width-full">Codes promotions';
content += '	</legend>';

content += '<p class="text-center">';
content += '	<a class="btn btn-info m-r-5" href="javascript:;" onclick="affiche_modal(\'Gestion des codes promos du parcours\',0,\'https://www.ats-sport.com/<?php echo URL_DEV; ?>admin/creation_promo.php?epre_id=<?php echo $_GET['epre_id']; ?>&id_parcours=<?php echo $f->parc_id[$nbparc]; ?>\')" class="btn btn-danger m-b-15 m-l-5"><b>Générer des codes promo pour ce parcours</b></a>';
content += '</p>';

content += '</fieldset>';

content += '<fieldset>';
content += '			<legend class="pull-left width-full">Insérez ici les  informations transmises au coureur dans l’email de confirmation d’inscription</legend>';
content += '				<div class="row-margin-left m-l-10">';
content += '					<div class="panel-body panel-form m-b-20">';
content += '						<textarea name="info_complementaire_parcours['+ <?php echo $nbparc; ?> +']" class="textarea form-control" id="info_complementaire_parcours_wysihtml5_'+ <?php echo $nbparc; ?> +'" placeholder="Historique, dénivelé, ambiance, accès, douche, repas..." rows="12"><?php echo $f->info_complementaire_parcours[$nbparc]; ?></textarea>';
content += '					</div>';
content += '				</div>';
content += '</fieldset>';

content += '<fieldset>';
content += '	<legend class="pull-left width-full">Champs supplémentaires';

content += '	</legend>';
content +='		<p class="text-center">';
content +='			<a class="btn btn-info m-r-5" href="javascript:;" onclick="affiche_modal(\'QQuestion Quantité\',0,\'https://www.ats-sport.com/<?php echo URL_DEV; ?>admin/creation_champs.php?epre_id=<?php echo $_GET['epre_id']; ?>&id_parcours=<?php echo $f->parc_id[$nbparc]; ?>&champ=d_parcours\')" class="btn btn-danger m-b-15 m-l-5" data-toggle="tooltip" data-placement="top" data-original-title=""><b>Question Quantité</b></br>Ex: taille/tee-shirt</a>';
content +='			<a class="btn btn-primary m-r-5" href="javascript:;" onclick="affiche_modal(\'Question Commerce\',0,\'https://www.ats-sport.com/<?php echo URL_DEV; ?>admin/creation_champs.php?epre_id=<?php echo $_GET['epre_id']; ?>&id_parcours=<?php echo $f->parc_id[$nbparc]; ?>&champ=p_parcours\')" class="btn btn-danger m-b-15 m-l-5" data-toggle="tooltip" data-placement="top" data-original-title=""><b>Question Commerce</b></br>Ex: repas, hébergement</a>';
content +='			<a class="btn btn-default m-r-5" href="javascript:;" onclick="affiche_modal(\'Autres Questions\',0,\'https://www.ats-sport.com/<?php echo URL_DEV; ?>admin/creation_champs.php?epre_id=<?php echo $_GET['epre_id']; ?>&id_parcours=<?php echo $f->parc_id[$nbparc]; ?>&champ=q_parcours\')" class="btn btn-danger m-b-15 m-l-5" data-toggle="tooltip" data-placement="top" data-original-title=""><b>Autres Questions</b></br>Ex: profession, intérêts</a>';
content +='		</p>';
content += '</fieldset>';

content += '<fieldset>';
content += '	<legend class="pull-left width-full">Fichiers supplémentaires pour ce parcours</legend>';
content += '		<div class="col-sm-12">';
content += '			<div class="panel-body panel-form">';
content += '				<div class="form-group">';
content += '					<label class="control-label"><b>5</b> fichiers maximum. Formats autorisés : <b>.jpg, .gif , .png ou .pdf, 2Mo maximum</b></label>';
content += '					<input id="fichier_parcours_sup_<?php echo $nbparc; ?>" name="fichier_parcours_sup_<?php echo $nbparc; ?>[]" type="file" class="file" multiple >';
content += '				</div>';
content += '			</div>';
content += '		</div>';
content += '</fieldset>';

content += '<fieldset>';
content += '			<legend class="pull-left width-full">Description du parcours</legend>';
content += '				<div class="row-margin-left m-l-10">';
content += '					<div class="panel-body panel-form m-b-20">';
content += '						<textarea name="parcours_description['+ <?php echo $nbparc; ?> +']" class="textarea form-control" id="parcours_description_wysihtml5['+ <?php echo $nbparc; ?> +']" placeholder="Historique, dénivelé, ambiance, accès, douche, repas..." rows="12"><?php echo $f->parcours_description[$nbparc]; ?></textarea>';
content += '					</div>';
content += '				</div>';
content += '</fieldset>';

$('.add-parcours').closest('li').before('<li id="P' + <?php echo $nbparc; ?> +'"><a id="name_parcours_' + <?php echo $nbparc; ?> +'" href="#parcours_' + <?php echo $nbparc; ?> + '">parcours ' + <?php echo $nbparc; ?> + '</a> <span><i class="ion-close"></i></span></li>');
$('.tab-content').append('<div class="tab-pane" id="' + tabId + '">' + content + '</div>');
	
		//Activation des dates et heures des tarifs
<?php for ($nb=1;$nb<=12;$nb++) { ?>

		$('#date_timepicker_start_tarifs_'+ <?php echo $nbparc; ?> + '_' + <?php echo $nb; ?>).datetimepicker({
		format:'d/m/Y H:i',
		lang:'fr',
		step:15,
		onShow:function( ct ){
		this.setOptions({
			maxDate:getDate($('#date_timepicker_end_tarifs_'+ <?php echo $nbparc; ?> + '_' + <?php echo $nb; ?>).val())?getDate($('#date_timepicker_end_tarifs_'+ <?php echo $nbparc; ?> + '_' + <?php echo $nb; ?>).val()):false
		})
		},
		});
	
		$('#date_timepicker_end_tarifs_'+ <?php echo $nbparc; ?> + '_' + <?php echo $nb; ?>).datetimepicker({
		format:'d/m/Y H:i',
			lang:'fr',
			step:15,
		onShow:function( ct ){
		this.setOptions({
			minDate:getDate($('#date_timepicker_start_tarifs_'+ <?php echo $nbparc; ?> + '_' + <?php echo $nb; ?>).val())?getDate($('#date_timepicker_start_tarifs_'+ <?php echo $nbparc; ?> + '_' + <?php echo $nb; ?>).val()):false
		})
		},
	});
<?php } ?>	
	
    //var inputfpsupdate = new Array();		
	var inputfpsupdate_<?php echo $nbparc; ?> = $("#fichier_parcours_sup_<?php echo $nbparc; ?>");
	inputfpsupdate_<?php echo $nbparc; ?>.fileinput({
	uploadUrl:'submit_file.php',
	uploadAsync: false,
	showUpload: false, // hide upload button
	showRemove: false, // hide remove button
	allowedFileExtensions : ['jpg', 'png','gif','pdf'],

<?php if ($f->docs_parcours_id[$nbparc][0] != '') { ?>
		<?php if ($f->idEpreuve != '') { ?>
		
				initialPreview: [
					<?php for($i=0;$i<count($f->docs_parcours_rep[$nbparc]);$i++) { ?>
						"<img src='<?php echo $f->docs_parcours_rep[$nbparc][$i]; ?>' class='file-preview-image-120'>",
					<?php } ?>
				],
		<?php } ?>
		<?php if (($_GET['epre_button'] == 'Modifier la fiche de cette course')) { ?>
				initialPreviewConfig: [
					<?php for($i=0;$i<count($f->docs_parcours[$nbparc]);$i++) { ?>
						{caption: "<?php echo $f->docs_parcours_affichage[$nbparc][$i]; ?>", width: "120px", url: "<?php echo $f->url_del_docs_parcours[$nbparc][$i]; ?>", key: 1},
					<?php } ?>
				],
		<?php } elseif($f->idEpreuve != '') { ?>
				initialPreviewConfig: [
					<?php for($i=0;$i<count($f->docs_parcours[$nbparc]);$i++) { ?>
						{caption: "<?php echo $f->docs_parcours_affichage[$nbparc][$i]; ?>", width: "120px", key: 1},
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
});
 											<?php if ($nb_parcours > 5) { ?>
												$('a#name_parcours_' + <?php echo $nbparc; ?> ).html(' <i id="badge_parcours_<?php echo $nbparc; ?>" class="badge badge-primary" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo $f->parc_nom[$nbparc];?>">P<?php echo $nbparc; ?></i>');
											<?php } else { ?>
												$('a#name_parcours_' + <?php echo $nbparc; ?> ).text('<?php echo tronque_texte($f->parc_nom[$nbparc],$length_aff_parcours); ?>');
											<?php  } ?>	       
		
		
		
<?php } ?>
	var nb_li = $(".nav-tabs").children().length; //think about it ;)

<?php } ?>

function etat_change_checkbox(name) {

    $('#' + name).live('change', function() {
		if(($(this).prop('checked'))==true) { $('#div-'+name).show()} else {$('#div-'+name).hide() }});	
}
function etat_change_checkbox_button(name) {

    $('#' + name).live('change', function() 
	{
		if(($(this).prop('checked'))==false) { 
			if(($('#button-'+name).prop('checked'))==true) 
			{ 
				$('#button-'+name).click();
			}			 
		}
	}
);	
}
	//soumission du formulaire
	$('[data-parsley-validate="true"]').on('submit', function(e) {
    e.preventDefault();
    var targetUrl = $(this).attr('action');
	//alert(targetUrl);
	scriptCharset: "iso-8859-1",

    $.post(targetUrl, $(this).serialize()).done(function(data) {

		var returnData = data;

		url_return = 'creation_epreuve.php?epre_id='+data+'&epre_button=Modifier la fiche de cette course';
		
		$("#fermer_resultat_final").attr("href", url_return);
		
		$("#resultat").modal({
        open: true,
        modal: true,
		keyboard: false
		
		});
    });
});

function vider_champ (num_parc) {

var champ = ["dotation", "participation", "questiondiverse"];
champ.forEach(function(champ) {

    //copie_champ_create(num_parc,entry);
	$('#table_up_'+ champ +'\\['+ num_parc +'\\]').empty();
	$('#affichage_modal_'+ champ +'\\['+num_parc+'\\]').empty();
	$('#div_up_'+ champ +'\\['+ num_parc +'\\]').hide();
});	
	

}
function copie_champ (num_parc){

var champ = ["dotation", "participation", "questiondiverse"];
champ.forEach(function(entry) {

    copie_champ_create(num_parc,entry);
	});	
}
function copie_champ_create(num_parc,champ) 
{
	
	$('#table_up_'+ champ +'\\['+ num_parc +'\\]').empty();
	$('#affichage_modal_'+ champ +'\\['+num_parc+'\\]').empty();
	
	var cpt=1;
	var num=new Array();
	var copie_parc;
	$( ".nav-tabs li" ).each(function( index ) {
		num[cpt] = $(this).attr('id');
		num[cpt] = num[cpt].replace("P", "");
		
		if (num[cpt] == num_parc) {
			cpt = cpt - 1;
			copie_parc = num[cpt];
		return false;
		}
		
		cpt++;
	});

	var nb_tr = $("tr[id*='tr_"+ champ +"\\[" + copie_parc + "\\]']").length;

	if ($('#div_up_'+ champ +'\\['+ copie_parc +'\\]').css('display') != 'none') 
	{
		$('#div_up_'+ champ +'\\['+ num_parc +'\\]').show();

		var content = '';
	
		for (id=1;id<=nb_tr;id++)
		{
			content += '			<tr id="tr_'+ champ +'['+ num_parc +']_' + id + '">';
			content += '				<td id="td_ordre_'+ champ +'_'+ num_parc +'_' + id + '"><input size="1" type="text" value="'+ id +'" name="epre_parcours_input_ordre_' + champ + '['+ num_parc +']['+ id +']" id="epre_parcours_input_ordre_' + champ + '['+ num_parc +']['+ id +']"> </td>';
			content += '				<td id="td_nom_'+ champ +'_'+ num_parc +'_' + id + '">';
			content += $('#td_nom_'+ champ +'_'+ copie_parc +'_' + id).html()+'</td>';
			content += '				<td id="td_select_'+ champ +'_'+ num_parc +'_' + id + '">';
			content += '				</td><td id="td_action_'+ champ +'['+ num_parc +']_' + id + '"><a class="fa fa-2x fa-wrench m-r-5" onclick="editer_fiche_champ(\''+ champ +'\','+ num_parc +',' + id + ');" href="javascript:;"></a><a class="fa fa-2x fa-times" <i="" onclick="bouton_supprimer_champ(\''+ champ +'\','+ num_parc +',' + id + ');" href="javascript:;"></a>';
			content += '				</td>';
			content += '			</tr>';
		}

		$('#table_up_'+ champ +'\\['+ num_parc +'\\]').append(content);
		for (id=1;id<=nb_tr;id++)
		{
			var ddl = $('#epre_parcours_select_'+ champ +'\\['+ copie_parc +'\\]\\[' + id + '\\]').clone();
			var selectedValue = $("#epre_parcours_select_"+ champ +"\\["+ copie_parc +"\\]\\[" + id + "\\] option:selected").val();
			ddl.find("option[value = '" + selectedValue + "']").attr("selected", "selected");			
			$('#td_select_'+ champ +'_'+ num_parc +'_' + id).append(ddl);
			$('td#td_select_'+ champ +'_'+ num_parc +'_' + id + ' #epre_parcours_select_'+ champ +'\\['+ copie_parc +'\\]\\[' + id + '\\]').attr('id','epre_parcours_select_'+ champ +'['+ num_parc +'][' + id + ']');
			$('td#td_select_'+ champ +'_'+ num_parc +'_' + id + ' #epre_parcours_select_'+ champ +'\\['+ num_parc +'\\]\\[' + id + '\\]').attr('name','epre_parcours_select_'+ champ +'['+ num_parc +'][' + id + ']');
			$('td#td_select_'+ champ +'_'+ num_parc +'_' + id + ' #epre_parcours_select_'+ champ +'\\['+ num_parc +'\\]\\[' + id + '\\]').attr('onchange','Get_Champs_Sup(\''+ champ +'\','+ num_parc +',' + id + ')');

			Create_Modal(''+ champ +'',num_parc,id,1);
			
			$('#epre_parcours_input_ordre_'+ champ +'\\['+num_parc+'\\]\\[' + id + '\\]').val($('#epre_parcours_input_ordre_'+ champ +'\\['+copie_parc+'\\]\\[' + id + '\\]').val());
			$('#epre_parcours_nom_'+ champ +'\\['+num_parc+'\\]\\[' + id + '\\]').val($('#epre_parcours_nom_'+ champ +'\\['+copie_parc+'\\]\\[' + id + '\\]').val());
			$('#epre_parcours_label_'+ champ +'\\['+num_parc+'\\]\\[' + id + '\\]').val($('#epre_parcours_label_'+ champ +'\\['+copie_parc+'\\]\\[' + id + '\\]').val());
			
			if (champ=='dotation' || champ=='questiondiverse') {
				
				$('#epre_parcours_critere_'+ champ +'\\['+num_parc+'\\]\\[' + id + '\\]').val($('#epre_parcours_critere_'+ champ +'\\['+copie_parc+'\\]\\[' + id + '\\]').val());
				$('#epre_parcours_type_champ_'+ champ +'\\['+num_parc+'\\]\\[' + id + '\\]').val($('#epre_parcours_type_champ_'+ champ +'\\['+copie_parc+'\\]\\[' + id + '\\]').val());
			}
			else {
				$('input:radio[id=epre_parcours_type_champ_'+ champ +'\\['+num_parc+'\\]\\[' + id + '\\]]').filter('[value=' + $('input:radio[id=epre_parcours_type_champ_'+ champ +'\\['+copie_parc+'\\]\\[' + id + '\\]]:checked').val() +']').attr('checked', true);
				$('#epre_parcours_prix_'+ champ +'\\['+num_parc+'\\]\\[' + id + '\\]').val($('#epre_parcours_prix_'+ champ +'\\['+copie_parc+'\\]\\[' + id + '\\]').val());
				$('#epre_parcours_qte_'+ champ +'\\['+num_parc+'\\]\\[' + id + '\\]').val($('#epre_parcours_qte_'+ champ +'\\['+copie_parc+'\\]\\[' + id + '\\]').val());
			}
			$('input:radio[id=epre_parcours_critere_obligatoire_'+ champ +'\\['+num_parc+'\\]\\[' + id + '\\]]').filter('[value=' + $('input:radio[id=epre_parcours_critere_obligatoire_'+ champ +'\\['+copie_parc+'\\]\\[' + id + '\\]]:checked').val() +']').attr('checked', true);
			$('input:radio[id=epre_parcours_critere_obligatoire_'+ champ +'\\['+num_parc+'\\]\\[' + id + '\\]]').filter('[value=non]').attr('checked', true);
			$('#epre_parcours_date_butoir_'+ champ +'\\['+num_parc+'\\]\\[' + id + '\\]').val($('#epre_parcours_date_butoir_'+ champ +'\\['+copie_parc+'\\]\\[' + id + '\\]').val());
		}
		$('#epre_parc_nbchamps'+ champ +'\\[' + num_parc + '\\]').val(nb_tr);
	}
}
 
$('#form-parcours input').keypress(  
function(event){  
if(event.keyCode == 13){  
event.preventDefault();  
alert('Il faut effectuer toutes les étapes pour valider votre épreuve.');  
}  
}  
);  
 
function check_value_nb_bon_promo (numparc,value) {
	var value_depart=($('#code_promo_numero_depart\\['+numparc+'\\]').val())-1;
	var value_fin = $('#code_promo_numero_fin\\['+numparc+'\\]').val();
	diff = value_fin-value_depart;
	
	if (value == 0 ) 
	{
		$('#code_promo_affiche\\['+numparc+'\\]').hide();
		$('#code_promo_numero_depart\\['+numparc+'\\]').val('0');
		$('#code_promo_numero_fin\\['+numparc+'\\]').val('0');
	}
	else
	{
		if (value != diff) {
		$('#code_promo_numero_depart\\['+numparc+'\\]').val('0');
		$('#code_promo_numero_fin\\['+numparc+'\\]').val('0');
		}
		$('#code_promo_affiche\\['+numparc+'\\]').show();
	}
}
function check_value_numérotation_bon_promo (numparc) {
	var value_depart=($('#code_promo_numero_depart\\['+numparc+'\\]').val())-1;
	var value_fin = $('#code_promo_numero_fin\\['+numparc+'\\]').val();
    
	$('#code_promo_label'+numparc).val('');
	$('#code_promo_fois_utilisable\\['+numparc+'\\]').val((''));
	
	diff = value_fin-value_depart;
	if (diff  <=0 && value_fin != 0) {
		
		$('#code_promo_numero_depart\\['+numparc+'\\]').val('0');
		$('#code_promo_numero_fin\\['+numparc+'\\]').val('0');
		$('#code_promo_fois_utilisable\\['+numparc+'\\]').val(('0'));
	}
	else
	{
	
		$('#code_promo_fois_utilisable\\['+numparc+'\\]').val((diff));
	}

}	
	
	
$(document).ready(function() {
	App.init();
	$('[data-toggle="tooltip"]').tooltip();
	FormWizardValidation.init();
	FormSliderSwitcher.init("input[id*='email_'], input[id*='button-visible_liste_inscrit-'],input[id*='relais-dossard_equipe-'], input[id*='relais-'],input[id*='auto_parentale-'], input[id*='certif_medical-'],input[id*='button-certif_medical_-'],input[id*='age-'],input[id*='plus-de-tarif'],input[id*='plage-exclusion-'],input[id*='inscription-'],input[id*='affichage_code_promo'],input[id*='champ_supp']");
	FormWysihtml5.init();
	FormPlugins.init();
<?php for ($nb=1;$nb<=12;$nb++) { ?>

		$('#date_timepicker_start_tarifs_1_' + <?php echo $nb; ?>).datetimepicker({
		format:'d/m/Y H:i',
		lang:'fr',
		step:15,
		onShow:function( ct ){
		this.setOptions({
			minDate:new Date(),
			maxDate:getDate($('#date_timepicker_end_tarifs_1_' + <?php echo $nb; ?>).val())?getDate($('#date_timepicker_end_tarifs_1_' + <?php echo $nb; ?>).val()):false
		})
		},
		});
	
		$('#date_timepicker_end_tarifs_1_' + <?php echo $nb; ?>).datetimepicker({
		format:'d/m/Y H:i',
			lang:'fr',
			step:15,
		onShow:function( ct ){
		this.setOptions({
			minDate:getDate($('#date_timepicker_start_tarifs_1_' + <?php echo $nb; ?>).val())?getDate($('#date_timepicker_start_tarifs_1_' + <?php echo $nb; ?>).val()):false
		})
		},
	});
<?php } ?>

//modele parentale
		var input_auto_parentale = $("#modele_auto_parentale");
		input_auto_parentale.fileinput({
				overwriteInitial: false,
				uploadUrl:'submit_file.php',
				uploadAsync: false,
				dropZoneEnabled: false,
				showPreview: true,
				showUpload: false, // hide upload button
				showRemove: false, // hide remove button
				allowedFileExtensions : ['pdf'],
				maxFileSize: 2000,
				maxFileCount: 1,
				<?php if ($f->modele_auto_parentale_fichier_rep != '') { ?>
					<?php if ($f->idEpreuve != '') { ?>
					
							initialPreview: [
								"<img src='<?php echo $f->modele_auto_parentale_fichier_rep; ?>' class='file-preview-image'>",
							],
					<?php } ?>	
					<?php if (($_GET['epre_button'] == 'Modifier la fiche de cette course')) { ?>
							initialPreviewConfig: [
								{caption: "<?php echo $f->modele_auto_parentale_fichier_affichage; ?>", width: "120px", url: "<?php echo $f->url_del_modele_auto_parentale_fichier; ?>", key: 1},
							],
							uploadExtraData: function() {
								return {
									update: 1,
									idEpreuve: <?php echo $f->idEpreuve; ?>
								};
							},
					<?php } elseif($f->idEpreuve != '') { ?>
							initialPreviewConfig: [
								{caption: "<?php echo $f->modele_auto_parentale_fichier_affichage; ?>", width: "120px", key: 1},
							],
					<?php } ?>			
				<?php } ?>	
			}).on("filebatchselected", function(event, files) {
			// trigger upload method immediately after files are selected
			input_auto_parentale.fileinput("upload");
		});

//perso epreuve image de fond
		var input_image_de_fond = $("#epreuve_image_de_fond");
		input_image_de_fond.fileinput({
				overwriteInitial: false,
				uploadUrl:'submit_file.php',
				uploadAsync: false,
				dropZoneEnabled: false,
				showPreview: true,
				showUpload: false, // hide upload button
				showRemove: false, // hide remove button
				allowedFileExtensions : ['jpg'],
				maxFileSize: 2000,
				maxFileCount: 1,
				<?php if ($f->epreuve_image_de_fond_fichier_rep != '') { ?>
					<?php if ($f->idEpreuve != '') { ?>
					
							initialPreview: [
								"<img src='<?php echo $f->epreuve_image_de_fond_fichier_rep; ?>' class='file-preview-image'>",
							],
					<?php } ?>	
					<?php if (($_GET['epre_button'] == 'Modifier la fiche de cette course')) { ?>
							initialPreviewConfig: [
								{caption: "<?php echo $f->epreuve_image_de_fond_fichier_affichage; ?>", width: "120px", url: "<?php echo $f->url_del_epreuve_image_de_fond_fichier; ?>", key: 1},
							],
							uploadExtraData: function() {
								return {
									update: 1,
									idEpreuve: <?php echo $f->idEpreuve; ?>
								};
							},
					<?php } elseif($f->idEpreuve != '') { ?>
							initialPreviewConfig: [
								{caption: "<?php echo $f->epreuve_image_de_fond_fichier_affichage; ?>", width: "120px", key: 1},
							],
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
    if ( ((screen.width  >= 480) && (screen.height >= 800)) || ((screen.width  >= 800) && (screen.height >= 480)) || navigator.userAgent.match(/ipad/gi) ) {
        //alert('tablette');
		drop_zone = false ;
    } else {
        //alert('mobile');
		drop_zone = false ;
    }
} 

var input = $("#epre_photo");
input.fileinput({
		uploadUrl:'submit_file.php',
		uploadAsync: false,
		dropZoneEnabled: drop_zone,
		showUpload: false, // hide upload button
		showRemove: false, // hide remove button
		allowedFileExtensions : ['jpg', 'png','gif'],
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
			initialPreviewConfig: [
				{caption: "<?php echo $f->photo_epreuve_affichage; ?>", width: "120px", url: "<?php echo $f->url_del_photo_epreuve; ?>", key: 1},
			],
			uploadExtraData: function() {
				return {
					update: 1,
					idEpreuve: <?php echo $f->idEpreuve; ?>
				};
			},
	<?php } elseif($f->idEpreuve != '') { ?>
			initialPreviewConfig: [
				{caption: "<?php echo $f->photo_epreuve_affichage; ?>", width: "120px", key: 1},
			],
	<?php } ?>			
<?php } ?>	

    }).on("filebatchselected", function(event, files) {
   // trigger upload method immediately after files are selected
   input.fileinput("upload");
});


var input_fes = $("#fichier_epreuve_sup");
input_fes.fileinput({
		overwriteInitial: false,
		uploadUrl:'submit_file.php',
		uploadAsync: false,
		showUpload: false, // hide upload button
		showRemove: false, // hide remove button
		allowedFileExtensions : ['jpg', 'png','gif','pdf'],
        maxFileSize: 2000,
        maxFileCount: 10,
<?php if (count($f->docs_epreuve_rep) > 0) { ?>
		<?php if ($f->idEpreuve != '') { ?>
		
				initialPreview: [
					<?php for($i=0;$i<count($f->docs_epreuve_rep);$i++) { ?>
						"<img src='<?php echo $f->docs_epreuve_rep[$i]; ?>' class='file-preview-image-120'>",
					<?php } ?>
				],
		<?php } ?>	
		<?php if (($_GET['epre_button'] == 'Modifier la fiche de cette course')) { ?>
				initialPreviewConfig: [
					<?php for($i=0;$i<count($f->docs_epreuve);$i++) { ?>
						{caption: "<?php echo $f->docs_epreuve_affichage[$i]; ?>", width: "120px", url: "<?php echo $f->url_del_docs_epreuve[$i]; ?>", key: 1},
					<?php } ?>
				],
				uploadExtraData: function() {
					return {
						update: 1,
						idEpreuve: <?php echo $f->idEpreuve; ?>
					};
				},
		<?php } elseif($f->idEpreuve != '') { ?>
				initialPreviewConfig: [
					<?php for($i=0;$i<count($f->docs_epreuve);$i++) { ?>
						{caption: "<?php echo $f->docs_epreuve_affichage[$i]; ?>", width: "120px", key: 1},
								<?php } ?>
				],
		<?php } ?>
<?php } ?>	
    }).on("filebatchselected", function(event, files) {
   // trigger upload method immediately after files are selected
   input_fes.fileinput("upload");
});

var input_fps = $("#fichier_parcours_sup_1");
input_fps.fileinput({
		uploadUrl:'submit_file.php',
		uploadAsync: false,
		showUpload: false, // hide upload button
		showRemove: false, // hide remove button
		allowedFileExtensions : ['jpg', 'png','gif','pdf'],
		
<?php if ($f->docs_parcours_id[1][0] != '') { ?>
		<?php if ($f->idEpreuve != '') { ?>
		
				initialPreview: [
					<?php for($i=0;$i<count($f->docs_parcours_rep[1]);$i++) { ?>
						"<img src='<?php echo $f->docs_parcours_rep[1][$i]; ?>' class='file-preview-image-120'>",
					<?php } ?>
				],
		<?php } ?>
		<?php if (($_GET['epre_button'] == 'Modifier la fiche de cette course')) { ?>
				initialPreviewConfig: [
					<?php for($i=0;$i<count($f->docs_parcours[1]);$i++) { ?>
						{caption: "<?php echo $f->docs_parcours_affichage[1][$i]; ?>", width: "120px", url: "<?php echo $f->url_del_docs_parcours[1][$i]; ?>", key: 1},
					<?php } ?>
				],
		<?php } elseif($f->idEpreuve != '') { ?>
				initialPreviewConfig: [
					<?php for($i=0;$i<count($f->docs_parcours[1]);$i++) { ?>
						{caption: "<?php echo $f->docs_parcours_affichage[1][$i]; ?>", width: "120px", key: 1},
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

if ($('#aff_critere_ul_' + champ + '_'+numparc+'_' + id + ':visible').length == 1) {

	var pattern = /(((^[a-zA-Záàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ]+\([0-9]+\))|(^[a-zA-Záàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ]+\([\*]{1}\)))((;[a-zA-Záàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ]+\([0-9]+\))|(;[a-zA-Záàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ]+\([\*]{1}\)))*)/;
	if (pattern.exec(value)) { test = pattern.exec(value); $('#epre_parcours_critere_' + champ + '\\['+numparc+'\\]\\[' + id + '\\]').val(test[0]);return false;}
	else { alert('Séparez les critères par des ";" et indiquez la quantité disponible entre parenthèse (mettre une "*" pour illimité)'); return false; }
}
else
{

var pattern = /^(([a-zA-Z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ\/]+)(;[a-zA-Z0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœ\/]+)*)/;
if (!pattern.exec(value)) { alert('Séparez les critères par des ";"</br>Gestion des quantités non disponible pour ce type de champ');return false; }
}

}

function Create_Modal_Iframe (titre,id_parcours,url,height,width) {
height = 750;
content ='<div id="modal_champs_'+id_parcours+'" class="modal">';	

content +='				<div class="modal-dialog" style="width:1000px;"><a class="modal-close close" href="#"></a>';
content +='					<div class="modal-content" >';
content +='						<div class="modal-body" id="return_resultat" style="width:100%;height:'+(height-170)+'px">';
content +='	<div class="col-sm-12 col-centered">';
content +='<iframe src="'+url+'" scrolling="yes" frameBorder="0" width="100%" height="'+(height-200)+'px" allowfullscreen="allowfullscreen" ></iframe>';
content +='	</div>';
content +='						</div>';
content +='						<div class="modal-footer">';
content +='							<a data-dismiss="modal" class="btn btn-sm btn-default" href="javascript:;" ><strong>Revenir à la fiche epreuve</strong></a>';
content +='						</div>';
content +='					</div>';
content +='				</div>';
content +='</div>';	

$('#affichage_modal').append(content);	
}
function affiche_modal (titre,id_parcours,url) {	

	$('#affichage_modal').html('');
	
	Create_Modal_Iframe (titre,id_parcours,url,$(window).height(),$(window).width());
	
	$('#modal_champs_'+id_parcours).modal({
		open: true,
		modal: true,
	});	
}

function email_obligatoire(value,id)
{
	console.log(value+' - '+id);
	if (value===true) {
		
		$('#epre_inscr_email').attr('data-parsley-group', 'wizard-step-3');
		$('#epre_inscr_email').prop('required',true);
		$('#epre_inscr_email').val('<?php echo $f->inscr_email ?>');
		$('#epre_inscr_email').addClass('parsley-error');
	}
	else
	{
		$('#epre_inscr_email').attr('data-parsley-group', '');
		$('#epre_inscr_email').prop('required',false);
		$('#epre_inscr_email').val('');
		$('#parsley-id-'+id).hide();
		$('#epre_inscr_email').removeClass('parsley-error');
	}
}

function check_condition(value)
{
	if (value==true)
	{
		$('#enre_epreuve').show();
	}
	else
	{
		$('#enre_epreuve').hide();
	}	
	
}

function remove(id)
{
	$('#'+id).remove();
}

function send_resultats(idEpreuve,id)
{
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: 'includes/ajaxEnvoiResultats.php',
		data: 'idEpreuve='+idEpreuve+'&id='+id,
		success: function(data){
			if(data.envoi == 'ok')
				notification('Notification','l\'email a bien été envoyé',5000,'ok');
			else
				notification('Notification','l\'email n\'a pas été envoyé<br>Vérifiez l\'adresse email et si les résultats sont en ligne',5000,'ko')
		},
		error: function (xhr, ajaxOptions, thrownError){}
	});
}

function notification (title,text,time,image) {
	$.gritter.add({
		title: title,
		text: text,
		time:time,
		image: 'assets/plugins/gritter/images/'+image+'.png'
	});
	return false;
}

function show_nsi(value)
{
	if (value==false)
	{
		$('#epre_nsi_nouveau').show();
		$('#epre_nsi_nouveau_2').show();
		$('#epre_nsi_ancien').hide();
		$('#epre_nsi_ancien_2').hide();
	}
	else
	{
		$('#epre_nsi_nouveau').hide();
		$('#epre_nsi_nouveau_2').hide();
		$('#epre_nsi_ancien').show();
		$('#epre_nsi_ancien_2').show();
	}	
	
}
	</script>
</body>
</html>