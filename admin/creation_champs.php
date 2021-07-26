<?php 

//echo "id:".$_SESSION['log_id'];
require("includes/connect_db.php");
require("includes/slashes.php");
require_once("includes/toolbox.php");
global $mysqli;
ini_set('session.cookie_secure', 'true');
ini_set('session.cookie_httponly', 'true');
ini_set('session.cookie_samesite', 'strict');
session_start();
connect_db();

//var_dump(session_get_cookie_params());
ini_set("display_errors", 0);
error_reporting(E_ALL);


require_once("includes/functions.php");

					//unset($_SESSION['mod_epre_id']);
					//unset($_SESSION['mod_epre_ids']);
					//unset($_SESSION['mod_epre_ids_code_promo']);
					unset($_SESSION['mod_epre_ids_dotation']);
					unset($_SESSION['mod_epre_ids_participation']);
					unset($_SESSION['mod_epre_ids_questiondiverse']);
					unset($_SESSION['mod_epre_ids_participation_commune']);
					unset($_SESSION['mod_epre_ids_dotation_commune']);
					unset($_SESSION['mod_epre_ids_questiondiverse_commune']);
//echo $_SESSION['unique_id_session'];

/*	
function dateen2fr($mydate,$wtime=0){

   if ($wtime == 0) {
		@list($date,$horaire)=explode(' ',$mydate);
		@list($annee,$mois,$jour)=explode('-',$date);
		@list($heure,$minute,$seconde)=explode(':',$horaire);
		//echo "</br>Date de départ : ".$mydate."</br> Date après : ".date('d/m/Y H:i',strtotime($mois."/".$jour."/".$annee." ".$heure.":".$minute));
		return @date('d/m/Y H:i',strtotime($mois."/".$jour."/".$annee." ".$heure.":".$minute));
   }
   else
   {
		@list($annee,$mois,$jour)=explode('-',$mydate);
		return @date('d/m/Y',strtotime($mois."/".$jour."/".$annee));
   }
   
}
*/
/*
if(!isset($_SESSION["log_log"]))
	{ ?>
<script>
    window.location = 'login_v2.php';
</script>		
<?php
	}
	*/

?>
<!DOCTYPE html> 
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="fr">
<!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<title>Ats Sport | Champs dynamiques</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<meta content="" name="description" />
	<meta content="" name="author" />
	
    <?php include ("includes/header_css_js_base.php"); ?>
	
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
<body style="overflow-x:hidden;background: #fff none repeat scroll 0 0;">
<?php
echo "totot".$_SESSION["log_id"]; 
		
		$f = new stdClass();
		$f->typeepreuve = array();
		$query  = "SELECT idTypeEpreuve, nomTypeEpreuve ";
		$query .= "FROM r_typeepreuve;";
		$result = $mysqli->query($query);
		array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
		while (($row=mysqli_fetch_array($result)) != FALSE)
			$f->typeepreuve[$row['idTypeEpreuve']] = $row['nomTypeEpreuve'];

		$f->typeparcours = array();
		$query  = "SELECT idTypeParcours, nomTypeParcours ";
		$query .= "FROM r_typeparcours;";
		$result = $mysqli->query($query);
		array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
		while (($row=mysqli_fetch_array($result)) != FALSE)
			$f->typeparcours[$row['idTypeParcours']] = $row['nomTypeParcours'];
	
		//JEFF
		//table dotation
		$f->dotationpreremplie = array();
		$query  = "SELECT idDotationPreRemplie, nom ";
		$query .= "FROM r_dotationpreremplie;";
		$result = $mysqli->query($query);
		array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
		while (($row=mysqli_fetch_array($result)) != FALSE)
			$f->dotationpreremplie[$row['idDotationPreRemplie']] = $row['nom'];
		
		//table participation
		$f->participationpreremplie = array();
		$query  = "SELECT idParticipationPreRemplie, nom ";
		$query .= "FROM r_participationpreremplie;";
		$result = $mysqli->query($query);
		array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
		while (($row=mysqli_fetch_array($result)) != FALSE)
			$f->participationpreremplie[$row['idParticipationPreRemplie']] = $row['nom'];
		
		//table QuestionDiverse
		$f->questiondiversepreremplie = array();
		$query  = "SELECT idQuestionDiversePreremplie, nom ";
		$query .= "FROM r_questiondiversepreremplie;";
		$result = $mysqli->query($query);
		array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
		while (($row=mysqli_fetch_array($result)) != FALSE)
			$f->questiondiversepreremplie[$row['idQuestionDiversePreremplie']] = $row['nom'];
		
		
		//table participation commune
		$f->participationpreremplie_commune = array();
		$query  = "SELECT idParticipationPreRemplieCommune, nom ";
		$query .= "FROM r_participationpreremplie_commune;";
		$result = $mysqli->query($query);
		//array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
		while (($row=mysqli_fetch_array($result)) != FALSE)
			$f->participationpreremplie_commune[$row['idParticipationPreRemplieCommune']] = $row['nom'];

		//table dotation commune
		$f->dotationpreremplie_commune = array();
		$query  = "SELECT idDotationPreRemplieCommune, nom ";
		$query .= "FROM r_dotationpreremplie_commune;";
		$result = $mysqli->query($query);
		//array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
		while (($row=mysqli_fetch_array($result)) != FALSE)
			$f->dotationpreremplie_commune[$row['idDotationPreRemplieCommune']] = $row['nom'];
		
		//table QuestionDiverse commune
		$f->questiondiversepreremplie_commune = array();
		$query  = "SELECT idQuestionDiversePreremplieCommune, nom ";
		$query .= "FROM r_questiondiversepreremplie_commune;";
		$result = $mysqli->query($query);
		//array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
		while (($row=mysqli_fetch_array($result)) != FALSE)
			$f->questiondiversepreremplie_commune[$row['idQuestionDiversePreremplieCommune']] = $row['nom'];
		
		//JEFF	
									
													
					

		$f->nom = "";
		$f->epre_date = date("d/m/Y");
		$f->epre_date_fin =date("d/m/Y", strtotime("+1 year"));
		$f->nbparc = 1;
		$f->selected_typeepreuve = -1;
		$f->payeur = "";
		$j = 1;
		$f->parc_nom[$j] = "";
		$f->parc_dossard[$j] = 0;
		$f->parc_dossardFin[$j] = 0;
		$f->parc_dossardExclus[$j] = '';
		$f->parc_ordre[$j] = 0;
		$f->relais[$j] = 0;
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
		//JEFF
		
		$f->code_promo_nom = array();
		$f->code_promo_label = array();
		$f->code_promo_numerotation_depart = array();
		$f->code_promo_numerotation_arrivee = array();
		$f->code_promo_nb_fois_utilisable = array();
		$f->code_promo_date_debut = array();
		$f->code_promo_date_fin = array();
		$f->code_promo_heure_debut = array();
		$f->code_promo_heure_fin = array();
		$f->code_promo_prix_reduction = array();
		
		$f->description_contact = "";
		$f->champssupdotation_nom = array();
		$f->champssupdotation_label = array();
		$f->champssupdotation_critere = array();
		$f->champssupdotation_unite = array();
		$f->champssupdotation_type_champ = array();
		$f->champssupdotation_date_butoir = array();
		$f->champssupdotation_information = array();
		$f->champssupdotation_obligatoire = array();
		$f->champssupdotation_ordre = array();

		$f->champssupparticipation_nom = array();
		$f->champssupparticipation_label = array();
		$f->champssupparticipation_prix = array();
		$f->champssupparticipation_qte = array();
		$f->champssupparticipation_unite = array();
		$f->champssupparticipation_date_butoir = array();
		$f->champssupdotation_date_butoir = array();
		$f->champssupparticipation_information = array();
		$f->champssupparticipation_ordre = array();
		
		$f->champssupquestiondiverse_nom = array();
		$f->champssupquestiondiverse_label = array();
		$f->champssupquestiondiverse_critere = array();
		$f->champssupquestiondiverse_unite = array();
		$f->champssupquestiondiverse_type_champ = array();
		$f->champssupquestiondiverse_date_butoir = array();
		$f->champssupquestiondiverse_information = array();
		$f->champssupquestiondiverse_obligatoire = array();
		$f->champssupquestiondiverse_ordre = array();

		

		$f->champssupparticipation_nom_commune = array();
		$f->champssupparticipation_label_commune = array();
		$f->champssupparticipation_prix_commune = array();
		$f->champssupparticipation_qte_commune = array();
		$f->champssupparticipation_unite_commune = array();
		$f->champssupparticipation_date_butoir_commune = array();
		$f->champssupparticipation_information_commune = array();
		$f->champssupparticipation_obligatoire_commune = array();
		$f->champssupparticipation_ordre_commune = array();
		
		$f->champssupdotation_nom_commune = array();
		$f->champssupdotation_label_commune = array();
		$f->champssupdotation_critere_commune = array();
		$f->champssupdotation_unite_commune = array();
		$f->champssupdotation_type_champ_commune = array();
		$f->champssupdotation_date_butoir_commune = array();
		$f->champssupdotation_information_commune = array();
		$f->champssupdotation_obligatoire_commune = array();
		$f->champssupdotation_ordre_commune = array();

		$f->champssupquestiondiverse_nom_commune = array();
		$f->champssupquestiondiverse_label_commune = array();
		$f->champssupquestiondiverse_critere_commune = array();
		$f->champssupquestiondiverse_unite_commune = array();
		$f->champssupquestiondiverse_type_champ_commune = array();
		$f->champssupquestiondiverse_date_butoir_commune = array();
		$f->champssupquestiondiverse_information_commune = array();
		$f->champssupquestiondiverse_obligatoire_commune = array();
		$f->champssupquestiondiverse_ordre_commune = array();
		
		
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
		//JEFF
		//MODIFICATION
		$id_parcours = $_GET['id_parcours'];
		$type_champ=$_GET['champ'];
		if (isset($_GET['epre_id']))
		{
			if (isset($_POST['epre_id']))
			{
				$epre_id = isset($_POST['epre_id'])?$_POST['epre_id']:"";
				$query  = "SELECT e.idTypeEpreuve, e.nomEpreuve, e.dateEpreuve, e.nombreParcours, e.departement, ";
				$query .= "e.idInternaute, e.nbParticipantsAttendus, e.nomStructureLegale, e.siteInternet, e.siteFacebook, e.siteTwitter,";
				$query .= "e.contactInscription, e.telInscription, e.emailInscription, e.dateDebutInscription, ";
				$query .= "e.dateFinInscription, e.description, e.reglement, e.ville, e.sitelieu, ";
				$query .= "e.referencer, e.urlImage, e.dateInscription, e.paiement_cb, e.payeur, e.devisChrono, e.administrateur, ";
				$query .= "i.loginInternaute, i.passInternaute, i.nomInternaute, i.prenomInternaute, i.telephone, i.emailInternaute ";
				$query .= "FROM r_epreuve as e JOIN r_internaute as i ON e.idInternaute = i.idInternaute ";
				$query .= "WHERE idEpreuve='".$epre_id."';";
				//echo $query;
			}
			else if (isset($_GET['epre_id']))
			{
				
				$epre_id = isset($_GET['epre_id'])?$_GET['epre_id']:"";
				$id_parcours = isset($_GET['id_parcours'])?$_GET['id_parcours']:"";
				
				$query  = "SELECT e.idTypeEpreuve, e.nomEpreuve, e.dateEpreuve, e.dateFinEpreuve, e.nombreParcours, e.departement, ";
				$query .= "e.idInternaute, e.nbParticipantsAttendus, e.nomStructureLegale, e.siteInternet, e.siteFacebook, e.siteTwitter,";
				$query .= "e.contactInscription, e.telInscription, e.emailInscription, e.emailinscription_recevoir, e.dateDebutInscription, ";
				$query .= "e.dateFinInscription, e.description, e.reglement, e.ville, e.sitelieu, ";
				$query .= "e.referencer, e.urlImage, e.dateInscription, e.paiement_cb, e.paiement_cheque, e.coordonnees_paiement_cheque,e.payeur, e.devisChrono, e.administrateur, ";
				$query .= "i.loginInternaute, i.passInternaute, i.nomInternaute, i.prenomInternaute, i.telephone, i.emailInternaute ";
				$query .= "FROM r_epreuve as e JOIN r_internaute as i ON e.idInternaute = i.idInternaute ";
				
				if($_SESSION['typeInternaute']=='admin') $query .= "WHERE e.idEpreuve='".$epre_id."' AND e.administrateur=".$_SESSION['log_id'].";";
elseif($_SESSION['typeInternaute']=='super_organisateur') $query .= "WHERE e.idEpreuve='".$epre_id."' AND e.super_organisateur=".$_SESSION['log_id'].";";
				else $query .= "WHERE e.idEpreuve='".$epre_id."' AND e.idInternaute=".$_SESSION['log_id'].";";
				//echo $query;
			}
		
			$result = $mysqli->query($query);
			array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
		
			if (($row=mysqli_fetch_array($result)) != FALSE)
			{
				$f->idEpreuve = $epre_id;
				
				
				
				$f->nom						= sql_to_form($row['nomEpreuve']);
				$f->epre_date				= sql_to_form(dateen2fr($row['dateEpreuve'],1));
				$f->epre_date_fin				= sql_to_form(dateen2fr($row['dateFinEpreuve'],1));
				$f->nbparc					= $row['nombreParcours'];
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
				//$f->inscr_fin 				= sql_to_form(date("d-m-Y",strtotime($row['dateFinInscription'])));
				$f->inscr_fin 				= sql_to_form(dateen2fr($row['dateFinInscription']));
				
				$f->inscr_fin_min			= $timeFin[1];
				$f->inscr_fin_heure			= $timeFin[0];
				$f->description				= sql_to_form($row['description']);
				//$f->reglement				= sql_to_form($row['reglement']);
				$f->ville					= sql_to_form($row['ville']);
				$f->siteetlieu				= sql_to_form($row['sitelieu']);
				$f->reference				= $row['referencer'];
				$f->urlImage				= $row['urlImage'];
				$f->paiement_cb				= $row['paiement_cb'];
				$f->paiement_cheque			= $row['paiement_cheque'];
				$f->epre_coordonnees_cheque = $row['coordonnees_paiement_cheque'];
				$f->payeur					= $row['payeur'];
				$f->devis_chrono			= $row['devisChrono'];
				$f->loginorga				= $row['loginInternaute'];
				$f->passorga				= $row['passInternaute'];
				$f->nomorga					= $row['nomInternaute'];
				$f->prenomorga				= $row['prenomInternaute'];
				$f->telorga					= $row['telephone'];
				$f->emailorga				= $row['emailInternaute'];
				
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
				



				
				//JEFF Table r_champssupdotation
				
				$tab_dotation_id = array();				
				$tab_dotation = array();
				$query  = "SELECT idChampsSupDotation, idEpreuveParcours, nom, label, critere, type_champ, date_butoir, obligatoire, ordre, information ";
				$query .= "FROM r_champssupdotation ";
				$query .= "WHERE idEpreuve='".$epre_id."' ";
				$query .= "AND idEpreuveParcours='".$id_parcours."' ";
				$query .= "ORDER BY ordre;";
				//echo $query;
				$result = $mysqli->query($query);
				array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
				while (($row=mysqli_fetch_array($result)) != FALSE)
				{
					$tab_dotation[$row['idEpreuveParcours']][$row['idChampsSupDotation']] = array(sql_to_form($row['nom']), $row['label'], $row['critere'], $row['type_champ'], $row['date_butoir'], $row['obligatoire'], $row['ordre'], $row['information']);
				}
				//JEFF Table r_champssupparticipation
				
				$tab_participation_id = array();				
				$tab_participation = array();
				$query  = "SELECT idChampsSupParticipation, idEpreuveParcours, nom, label, type_champ, prix, qte, date_butoir, obligatoire, ordre, information ";
				$query .= "FROM r_champssupparticipation ";
				$query .= "WHERE idEpreuve='".$epre_id."' ";
				$query .= "AND idEpreuveParcours='".$id_parcours."' ";
				$query .= "ORDER BY ordre;";
				//echo $query;
				$result = $mysqli->query($query);
				array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
				while (($row=mysqli_fetch_array($result)) != FALSE)
				{
					$tab_participation[$row['idEpreuveParcours']][$row['idChampsSupParticipation']] = array(sql_to_form($row['nom']), $row['label'], $row['type_champ'], $row['prix'], $row['qte'], $row['date_butoir'], $row['obligatoire'], $row['ordre'], $row['information']);
				}

				//JEFF Table r_champssupparticipation
				
				//JEFF Table r_champssupdotation
				
				$tab_questiondiverse_id = array();				
				$tab_questiondiverse = array();
				$query  = "SELECT 	idChampsSupQuestionDiverse, idEpreuveParcours, nom, label, critere, type_champ, date_butoir, obligatoire, ordre, unite, information ";
				$query .= "FROM r_champssupquestiondiverse ";
				$query .= "WHERE idEpreuve='".$epre_id."' ";
				$query .= "AND idEpreuveParcours='".$id_parcours."' ";
				$query .= "ORDER BY ordre;";
				//echo $query;
				$result = $mysqli->query($query);
				array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
				while (($row=mysqli_fetch_array($result)) != FALSE)
				{
					$tab_questiondiverse[$row['idEpreuveParcours']][$row['idChampsSupQuestionDiverse']] = array(sql_to_form($row['nom']), $row['label'], $row['critere'], $row['type_champ'], $row['date_butoir'], $row['obligatoire'], $row['ordre'], $row['unite'], $row['information']);
				}
				//JEFF Table r_champssupdotation
				

				
				
				$query  = "SELECT idEpreuveParcours, idTypeParcours, nomParcours, nbtarif, horaireDepart, dossardDeb, dossardFin, nbexclusion, dossards_exclus, ordre_affichage, relais, ageLimite, age, ParcoursDescription, certificatMedical, certificatMedicalObligatoire, autoParentale, infoParcoursInscription ";
				$query .= "FROM r_epreuveparcours ";
				$query .= "WHERE idEpreuve=".$epre_id." ";
				if (!empty($id_parcours)) {
					$query .= " AND idEpreuveParcours=".$id_parcours." ";
				}
				$query .= "ORDER BY idEpreuveParcours;";
				//$query .= " ORDER BY ordre_affichage;";
				//$query;
				$result = $mysqli->query($query);
				array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);


				//JEFF Table r_champssupparticipation_commune
				$query_participation_commune  = "SELECT idChampsSupParticipationCommune, nom, label, type_champ, prix, qte, date_butoir, obligatoire, ordre, information ";
				$query_participation_commune .= "FROM r_champssupparticipation_commune ";
				$query_participation_commune .= "WHERE idEpreuve='".$epre_id."' ";
				$query_participation_commune .= "ORDER BY ordre;";
				//echo $query;
				$result_participation_commune = $mysqli->query($query_participation_commune);
				//array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
				$jj=1;
				$tab_participation_commune_id = array();
				while (($row_p_c=mysqli_fetch_array($result_participation_commune)) != FALSE)
				{
					
						$tab_participation_commune_id[0][1][$jj] = $row_p_c['idChampsSupParticipationCommune'];
						//$f->champssupparticipation_id_commune[0][$jj] = $row['idChampsSupParticipationCommune'];
						$f->champssupparticipation_nom_commune[0][$jj] = $row_p_c['nom'];
						$f->champssupparticipation_label_commune[0][$jj] = $row_p_c['label'];
						$f->champssupparticipation_type_champ_commune[0][$jj] = $row_p_c['type_champ'];
						$f->champssupparticipation_prix_commune[0][$jj] = $row_p_c['prix'];
						$f->champssupparticipation_qte_commune[0][$jj] = $row_p_c['qte'];
						$f->champssupparticipation_date_butoir_commune[0][$jj] = dateen2fr($row_p_c['date_butoir'],1);
						$f->champssupparticipation_information_commune[0][$jj] = $row_p_c['information'];
						$f->champssupparticipation_obligatoire_commune[0][$jj] = $row_p_c['obligatoire'];
						$f->champssupparticipation_ordre_commune[0][$jj] = $row_p_c['ordre'];
				$jj++;
				}
				
				//JEFF Table r_champssupdotation_commune
				$query_dotation_commune  = "SELECT idChampsSupDotationCommune, nom, label, type_champ, critere, date_butoir, obligatoire, ordre, information ";
				$query_dotation_commune .= "FROM r_champssupdotation_commune ";
				$query_dotation_commune .= "WHERE idEpreuve='".$epre_id."' ";
				$query_dotation_commune .= "ORDER BY ordre;";
				//echo $query_participation_commune;
				$result_dotation_commune = $mysqli->query($query_dotation_commune);
				//array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
				$jj=1;
				$tab_dotation_commune_id = array();
				while (($row_d_c=mysqli_fetch_array($result_dotation_commune)) != FALSE)
				{
					
						$tab_dotation_commune_id[0][1][$jj] = $row_d_c['idChampsSupDotationCommune'];
						//$f->champssupparticipation_id_commune[0][$jj] = $row['idChampsSupParticipationCommune'];
						$f->champssupdotation_nom_commune[0][$jj] = $row_d_c['nom'];
						$f->champssupdotation_label_commune[0][$jj] = $row_d_c['label'];
						$f->champssupdotation_critere_commune[0][$jj] = $row_d_c['critere'];
						$f->champssupdotation_type_champ_commune[0][$jj] = $row_d_c['type_champ'];
						$f->champssupdotation_date_butoir_commune[0][$jj] = dateen2fr($row_d_c['date_butoir'],1);
						$f->champssupdotation_information_commune[0][$jj] = $row_d_c['information'];
						$f->champssupdotation_obligatoire_commune[0][$jj] = $row_d_c['obligatoire'];
						$f->champssupdotation_ordre_commune[0][$jj] = $row_d_c['ordre'];
				

				$jj++;
				}

				//JEFF Table r_champsquestiondiverse_commune
				$query_questiondiverse_commune  = "SELECT idChampsSupQuestionDiverseCommune, nom, label, type_champ, critere, date_butoir, obligatoire, ordre, information ";
				$query_questiondiverse_commune .= "FROM r_champssupquestiondiverse_commune ";
				$query_questiondiverse_commune .= "WHERE idEpreuve='".$epre_id."' ";
				$query_questiondiverse_commune .= "ORDER BY ordre;";
				//echo $query_questiondiverse_commune;
				$result_questiondiverse_commune = $mysqli->query($query_questiondiverse_commune);
				//array_push($p->query, (($result != FALSE)?"ok":"er")." : ".$query);
				$jj=1;
				$tab_questiondiverse_commune_id = array();
				while (($row_qd_c=mysqli_fetch_array($result_questiondiverse_commune)) != FALSE)
				{
					
						
						$tab_questiondiverse_commune_id[0][1][$jj] = $row_qd_c['idChampsSupQuestionDiverseCommune'];
						//$f->champssupparticipation_id_commune[0][$jj] = $row['idChampsSupParticipationCommune'];
						$f->champssupquestiondiverse_nom_commune[0][$jj] = $row_qd_c['nom'];
						$f->champssupquestiondiverse_label_commune[0][$jj] = $row_qd_c['label'];
						$f->champssupquestiondiverse_critere_commune[0][$jj] = $row_qd_c['critere'];
						$f->champssupquestiondiverse_type_champ_commune[0][$jj] = $row_qd_c['type_champ'];
						$f->champssupquestiondiverse_date_butoir_commune[0][$jj] = dateen2fr($row_qd_c['date_butoir'],1);
						$f->champssupquestiondiverse_information_commune[0][$jj] = $row_qd_c['information'];
						$f->champssupquestiondiverse_obligatoire_commune[0][$jj] = $row_qd_c['obligatoire'];
						$f->champssupquestiondiverse_ordre_commune[0][$jj] = $row_qd_c['ordre'];
		

				$jj++;
				}

				
				$j = 0;
				while (($row=mysqli_fetch_array($result)) != FALSE)
				{
					$j++;
					$tab_id[$j][0] 				= $row['idEpreuveParcours'];
					$tab_dotation_id[$j][0] 		= $row['idEpreuveParcours'];
					$tab_participation_id[$j][0] 		= $row['idEpreuveParcours'];
					$tab_questiondiverse_id[$j][0] 		= $row['idEpreuveParcours'];

					$jj = 0;
					///print_r($tab_dotation);
					//echo "acccc".$row['idEpreuveParcours'];
					foreach ($tab_dotation[$row['idEpreuveParcours']] as $k=>$i)
					{
						$jj++;
						//echo "eezaeazeazze";
						$tab_dotation_id[$j][1][$jj] = $k;
						$f->champssupdotation_nom[$j][$jj] = $i[0];
						$f->champssupdotation_label[$j][$jj] = $i[1];
						$f->champssupdotation_critere[$j][$jj] = $i[2];
						//$f->champssupdotation_unite[$j][$jj] = $i[6];
						$f->champssupdotation_type_champ[$j][$jj] = $i[3];
						$f->champssupdotation_date_butoir[$j][$jj] = dateen2fr($i[4],1);
						$f->champssupdotation_information[$j][$jj] = $i[7];
						$f->champssupdotation_obligatoire[$j][$jj] = $i[5];
						$f->champssupdotation_ordre[$j][$jj] = $i[6];
					}
					$jj = 0;
					//print_r($tab_dotation);
					//echo "acccc".$row['idEpreuveParcours'];
					foreach ($tab_participation[$row['idEpreuveParcours']] as $k=>$i)
					{
						$jj++;
						//echo "eezaeazeazze";
						$tab_participation_id[$j][1][$jj] = $k;
						$f->champssupparticipation_nom[$j][$jj] = $i[0];
						$f->champssupparticipation_label[$j][$jj] = $i[1];
						$f->champssupparticipation_type_champ[$j][$jj] = $i[2];
						$f->champssupparticipation_prix[$j][$jj] = $i[3];
						$f->champssupparticipation_qte[$j][$jj] = $i[4];
						$f->champssupparticipation_date_butoir[$j][$jj] = dateen2fr($i[5],1);
						$f->champssupparticipation_information[$j][$jj] = $i[8];
						$f->champssupparticipation_obligatoire[$j][$jj] = $i[6];
						$f->champssupparticipation_ordre[$j][$jj] = $i[7];
					}
					//print_r($f->champssupparticipation_nom[1]);
					$jj = 0;
					//print_r($tab_participation);
					//echo "acccc".$row['idEpreuveParcours'];
					foreach ($tab_questiondiverse[$row['idEpreuveParcours']] as $k=>$i)
					{
						$jj++;
						//echo "eezaeazeazze";
						$tab_questiondiverse_id[$j][1][$jj] = $k;
						$f->champssupquestiondiverse_nom[$j][$jj] = $i[0];
						$f->champssupquestiondiverse_label[$j][$jj] = $i[1];
						$f->champssupquestiondiverse_critere[$j][$jj] = $i[2];
						$f->champssupquestiondiverse_unite[$j][$jj] = $i[7];
						$f->champssupquestiondiverse_type_champ[$j][$jj] = $i[3];
						$f->champssupquestiondiverse_date_butoir[$j][$jj] = dateen2fr($i[4],1);
						$f->champssupquestiondiverse_information[$j][$jj] = $i[8];
						$f->champssupquestiondiverse_obligatoire[$j][$jj] = $i[5];
						$f->champssupquestiondiverse_ordre[$j][$jj] = $i[6];
					}
				}
				//print_r($f->parc_descprix);
				/*if ((isset($_POST['epre_button']) && $_POST['epre_button'] == 'Modifier la fiche de cette course') || 
					(isset($_GET['epre_button']) && $_GET['epre_button'] == 'Modifier la fiche de cette course') || 
					(isset($_GET['epre_button']) && $_GET['epre_button'] == '1'))
				{*/
					//$modif = true;
					//$_SESSION['mod_epre_id_champs'] = true;
					//$_SESSION['mod_epre_ids_champs'] = $tab_id;

					//echo "www : ";print_r($_SESSION['mod_epre_ids_participation_commune']);
					//echo "xxx : ";print_r($_SESSION['mod_epre_ids_dotation']);
				//}
				/*else
				{
					$modif = false;
					unset($_SESSION['mod_epre_id']);
					unset($_SESSION['mod_epre_ids']);
					unset($_SESSION['mod_epre_ids_code_promo']);
					unset($_SESSION['mod_epre_ids_dotation']);
					unset($_SESSION['mod_epre_ids_participation']);
					unset($_SESSION['mod_epre_ids_questiondiverse']);
				}*/
			}
			else
			{
				$p->centre = "<br /><br /><p class='txtLibre' style='text-align:center;font-size:14px;'>Vous n'avez pas accès à cette épreuve !</p>\n";
				return $p;
			}
		}
		

?>
    <div class="row">
        <!-- begin col-12 -->
        <div class="col-md-12"> 
            <!-- begin panel -->
            <div class="panel panel-inverse">
               <!-- <div class="panel-heading">
                    <h2 class="panel-title" id="nom_epreuve"><?php if ($f->nom !='') echo $f->nom; else echo "Nouvelle épreuve"; ?></h2>
                </div> /-->
                <div class="panel-body">
					<form action="champs_submit.php?epre_id=<?php echo $_GET['epre_id']; ?>&epre_button=Modifier la fiche de cette course&modif=<?php echo $_SESSION['mod_epre_id']; ?>" method="POST" data-parsley-validate="true" name="form-wizard" class="form-horizontal" id="form-parcours" enctype="multipart/form-data">

					<input type="hidden" name="idEpreuve" value="<?php echo $_GET['epre_id']; ?>">
												<!--	
												<SELECT class="form-control input-inline input-xs" name="select_parcours[1]" id="select_parcours_1" onchange="url_redirect_new(<?php echo $_GET['epre_id']; ?>,this.value)">
													<?php 
													

													$info_parcours=info_parcours($_GET['epre_id'],0,1);
													if (empty($_GET['id_parcours'])) { ?> <OPTION VALUE="0;0"> Choisir ... </OPTION> <?php }
													while (($row_select_parcours=mysqli_fetch_array($info_parcours)) != FALSE)
													{ 
													
													?>
														
														<OPTION VALUE="<?php echo $row_select_parcours['idEpreuveParcours']; ?>" <?php if (!empty($_GET['id_parcours']) && $id_parcours == $row_select_parcours['idEpreuveParcours'] ) echo "selected"; ?> <?php echo $aff_selected_parcours_disabled ; ?> ><?php echo stripslashes(htmlspecialchars($row_select_parcours['nomParcours'], ENT_QUOTES))." (<i> ".stripslashes(htmlspecialchars($row_select_parcours['desctarif'], ENT_QUOTES))."</i> ) ".$aff_selected_parcours_complet; ?> </OPTION>';
						
													<?php } ?>
												</SELECT>
											//-->
								<!-- <div class="panel panel-inverse panel-with-tabs" data-sortable-id="ui-unlimited-tabs-4"> /-->
								
									
										
											<input type="hidden" id="id_table_parcours[1]" name="id_table_parcours[1]" value="<?php echo $tab_id[1][0]; ?>" />

								

											<div id="info_enregistrement" style="display:none"><p><span  class="btn btn-success btn-sm" ><strong>Enregistrement effectué !</strong></span></p></div>
											<div id="info_enregistrement_en_cours" style="display:none"><p><span  class="btn btn-warning btn-sm" ><strong>Enregistrement en cours</strong></span></p></div>
											
											<fieldset>
													<?php if ($type_champ=='d_epreuve' || $type_champ=='tous' ) { $_SESSION['mod_epre_ids_dotation_commune'] = $tab_dotation_commune_id;?>
													<div class="form-group">
														<div class="panel panel-ats panel-info col-sm-12">
															<div class="panel-heading panel-heading-ats p-b-20">
																<div class="panel-heading-btn">

																		<!-- <button class="btn btn-success btn-xs" id="buttonplus_dotation[0]" type="button" data-toggle="tooltip" data-placement="top" data-original-title="Ajouter un champ" onclick="bouton_ajouter_champ('dotation',1)";><i class="fa fa-2x fa-plus-circle"></i></button> /-->
																		<!-- <a id="buttonplus_dotation[0]" class="btn btn-success btn-xs" href="javascript:;" data-toggle="tooltip" data-placement="top" data-original-title="Ajouter un champ" onclick="bouton_ajouter_champ('dotation',1)"><i class="fa fa-2x fa-plus-circle"></i></a> /-->
																		<!-- <button class="btn btn-danger btn-xs" id="buttonmoins" type="button" data-toggle="tooltip" data-placement="top" data-original-title="Supprimer un champ"><i class="fa fa-minus-square"></i></button> /-->

																</div>
																<h3 class="panel-title">Champs de type Dotation uniquement pour l'épreuve</h3>
															</div>
															<?php //echo "xxxxx".count($f->champssupdotation_nom[0]); ?>
															
															<div id="div_up_dotation[0]" class="panel-body panel-body-ats" <?php if (count($f->champssupdotation_nom_commune[0])) "style='display:visible'"; else echo "style=\"display:none\""; ?>>
																
																<table class="table table-striped">
																	<thead>
																		<tr>
																			<th>Ordre</th>
																			<th>Nom</th>
																			<th>Type</th>
																			<th>Action</th>
																		</tr>
																	</thead>
																	<tbody id="table_up_dotation[0]">
																	<?php $perso = 1 ; if (count($f->champssupdotation_nom_commune[0])) { ?>
																	<?php 	$arr_length = count($f->champssupdotation_nom_commune[0]);
																	for($id=1;$id<($arr_length+1);$id++)
																	{ ?>	
																	
																		<tr id="tr_dotation[0]_<?php echo $id; ?>">
																			<td id="td_ordre_dotation_0_<?php echo $id; ?>"> <input size="1" type="text" value="<?php if($f->champssupdotation_ordre_commune[0][$id]) { echo $f->champssupdotation_ordre_commune[0][$id];} else { echo $id; }?>" name="epre_parcours_input_ordre_dotation[0][<?php echo $id; ?>]" id="epre_parcours_input_ordre_dotation[0][<?php echo $id; ?>]"> </td>
																			<td id="td_nom_dotation_0_<?php echo $id; ?>"><?php echo $f->champssupdotation_nom_commune[0][$id]; ?></td>
																			<td id="td_select_dotation_0_<?php echo $id; ?>">
																				<SELECT name="epre_parcours_select_dotation[0][<?php echo $id; ?>]" id="epre_parcours_select_dotation[0][<?php echo $id; ?>]" class="form-control input-panel-heading" onchange="Get_Champs_Sup('dotation',0,<?php echo $id; ?>)">
																				<OPTION VALUE="0"> Votre sélection </OPTION>
																				<?php 	foreach ($f->dotationpreremplie_commune as $k=>$i) { ?>
																				<OPTION VALUE="<?php echo $k; ?>"<?php if($i == $f->champssupdotation_nom_commune[0][$id]) { echo " SELECTED"; $perso = 0; } else { echo ""; }?>><?php echo $i; ?></OPTION>
																				<?php } ?>
																				<OPTION VALUE="-1" <?php if($perso==1) echo " SELECTED"; else echo "";?>> Personnalisée </OPTION>
																				</select>
																				
																			<td id="td_action_dotation[0]_<?php echo $id; ?>"><a class="fa fa-2x fa-wrench m-r-5" onclick="editer_fiche_champ('dotation',0,<?php echo $id; ?>);" href="javascript:;"></a><a class="fa fa-2x fa-times text-danger" onclick="bouton_supprimer_champ('dotation',0,<?php echo $id; ?>);" href="javascript:;"></a>
																			</td>
																		</tr>
																	<?php } ?>
																	<?php } ?>
																	</tbody>
																</table>
															
															</div>
															<a id="buttonplus_dotation_commune" class="btn btn-success btn-xs" href="javascript:;" data-toggle="tooltip" data-placement="top" data-original-title="Ajouter un champ" onclick="bouton_ajouter_champ('dotation',0)"><i class="fa fa-2x fa-plus-circle"></i></a>
															<input type="hidden" id="epre_parc_nbchampsdotation[0]" name="epre_parc_nbchampsdotation[0]" value="<?php echo $arr_length; ?>">	
														</div>
													</div>	
													<div id="affichage_modal_dotation[0]">
													<?php	$arr_length = count($f->champssupdotation_nom_commune[0]);
													for($id=1;$id<($arr_length+1);$id++)
														{ ?>
															<?php if ($f->champssupdotation_nom_commune[0][$id] != '') { ?>
																<div class="modal" id="epre_parcours_dotation_modal[0][<?php echo $id; ?>]" style="display: none;" aria-hidden="true">	
																	<div class="modal-dialog">
																		<div class="modal-content">
																			<div style="text-align: right;" class="modal-header">
																				Création champ de type Dotation supplémentaire
																			</div>
																			<div class="modal-body">
																				<div class="form-group">
																					<label class="col-sm-3 control-label">Référence</label>
																					<div class="col-sm-9">
																						<input type="text" onchange="$('#td_nom_dotation_0_<?php echo $id; ?>').html(this.value);" maxlength="50" value="<?php echo $f->champssupdotation_nom_commune[0][$id]; ?>" id="epre_parcours_nom_dotation[0][<?php echo $id; ?>]" name="epre_parcours_nom_dotation[0][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																					</div>
																				</div>
																				<div class="form-group">
																					<label class="col-sm-3 control-label">Question posée</label>
																					<div class="col-sm-9">
																						<input type="text" maxlength="1000" value="<?php echo $f->champssupdotation_label_commune[0][$id]; ?>" id="epre_parcours_label_dotation[0][<?php echo $id; ?>]" name="epre_parcours_label_dotation[0][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																					</div>
																				</div>
																				<div class="form-group" id="aff_critere_dotation_0_<?php echo $id; ?>">
																					<label class="col-sm-3 control-label">critere</label>
																					<div class="col-sm-9">
																						<input type="text" placeholder="ex : XS(100);S(50);XL(200)" value="<?php echo $f->champssupdotation_critere_commune[0][$id]; ?>" id="epre_parcours_critere_dotation[0][<?php echo $id; ?>]" name="epre_parcours_critere_dotation[0][<?php echo $id; ?>]" class="form-control input-inline input-xs" onchange="check_pattern(this.value,'dotation',1,<?php echo $id; ?>);">
																						<ul class="list-unstyled" id="aff_critere_ul_dotation_0_<?php echo $id; ?>">
																							<li>Séparez les critères par des ";" et indiquez la quantité disponible entre parenthèse (mettre une "*" pour illimité)</li>
																						</ul>
																					</div>
																				</div>
																				<div class="form-group">
																				<label class="col-sm-3 control-label">Type de Champ </label>
																					<div class="col-sm-4">
																						<select class="form-control" id="epre_parcours_type_champ_dotation[0][<?php echo $id; ?>]" name="epre_parcours_type_champ_dotation[0][<?php echo $id; ?>]" onchange="affichage_critere_ou_pas(this.value,'dotation',0,<?php echo $id; ?>);">
																							<option value="SELECT"<?php if($f->champssupdotation_type_champ_commune[0][$id]=='SELECT') echo " SELECTED"; else echo "";?> > SELECT </option>
																							<option value="RADIO" <?php if($f->champssupdotation_type_champ_commune[0][$id]=='RADIO') echo " SELECTED"; else echo "";?>> RADIO </option>
																							<option value="CASE" <?php if($f->champssupdotation_type_champ_commune[0][$id]=='CASE') echo " SELECTED"; else echo "";?>> CASE A COCHER </option>
																							<option value="INPUT" <?php if($f->champssupdotation_type_champ_commune[0][$id]=='INPUT') echo " SELECTED"; else echo "";?>> INPUT </option>
																							<option value="TEXTAREA" <?php if($f->champssupdotation_type_champ_commune[0][$id]=='TEXTAREA') echo " SELECTED"; else echo "";?>> TEXTAREA </option>
																						</select>
																					</div>
																				</div>
																				<div class="form-group"><label class="col-sm-3 control-label">Date limite</label>
																					<div class="col-md-9">
																						<div class="input-group">
																							<input type="text" style="z-index:100000" class="form-control input-inline input-xs" value="<?php echo $f->champssupdotation_date_butoir_commune[0][$id]; ?>" id="epre_parcours_date_butoir_dotation[0][<?php echo $id; ?>]" name="epre_parcours_date_butoir_dotation[0][<?php echo $id; ?>]">
																						</div>
																					</div>
																				</div>  
																				<div class="form-group"><label class="col-sm-3 control-label">Informations</label>
																					<div class="col-md-9">
																						<div class="input-group">
																							<textarea style="z-index:100000" class="form-control m-l-15" id="epre_parcours_information_dotation[0][<?php echo $id; ?>]" name="epre_parcours_information_dotation[0][<?php echo $id; ?>]"><?php echo $f->champssupdotation_information_commune[0][$id]; ?></textarea>
																						</div>
																					</div>
																						<ul class="list-unstyled" id="aff_critere_ul_information_0_<?php echo $id; ?>">
																							<li>Information sur le champ qui seront affichées sur la fiche d'inscription</li>
																						</ul>
																				</div> 																			
																				<div class="form-group">
																				<label class="col-sm-3 control-label">Obligatoire?</label>
																					<div class="col-md-9">
																						<label class="radio-inline">
																							<input type="radio" value="non" name="epre_parcours_critere_obligatoire_dotation[0][<?php echo $id; ?>]" id="epre_parcours_critere_obligatoire_dotation[0][<?php echo $id; ?>]" <?php if($f->champssupdotation_obligatoire_commune[0][$id] == "non") echo " CHECKED"; else echo "";?>> Non
																							<input class="m-l-3" type="radio" value="oui" name="epre_parcours_critere_obligatoire_dotation[0][<?php echo $id; ?>]" id="epre_parcours_critere_obligatoire_dotation[0][<?php echo $id; ?>]" <?php if($f->champssupdotation_obligatoire_commune[0][$id] == "oui") echo " CHECKED"; else echo "";?>> <span class="m-l-20">Oui </span>
																						</label>
																					</div>
																				</div>
																				
																			</div>
																			<div class="modal-footer"><a data-dismiss="modal" id="fermer_resultat" class="btn btn-primary" href="#">Valider</a>
																			</div>
																		</div>
																	</div>
																</div>
																<?php	} ?>
														<?php	} ?>
													</div>
													<?php } ?>
											
											
											
											
											
											
											
											
											
											
											
											
											
											
											
											
											
											
											
											
											
											
											<?php if ($type_champ=='p_epreuve' || $type_champ=='tous' ) { $_SESSION['mod_epre_ids_participation_commune'] = $tab_participation_commune_id;?>
											
												<!--<legend class="pull-left width-full">Options
												<div class="btn-group pull-right" id="div-champ_supp_action_0">
													<button class="btn btn-success btn-xs" type="button" data-toggle="tooltip" data-placement="top" data-original-title="Copier les champs du parcours précédent" onclick="copie_champ(0);";><i class="fa fa-2x fa-copy"></i></button>
													<button class="btn btn-success btn-xs" type="button" data-toggle="tooltip" data-placement="top" data-original-title="vider tous les champs" onclick="vider_champ(0);";><i class="fa fa-2x fa-eraser"></i></button>
													<button class="btn btn-success btn-xs" type="button" data-toggle="tooltip" data-placement="top" data-original-title="Visualisation" onclick="voir_exemple_champ(0);";><i class="fa fa-2x fa-search"></i></button>
												</div>
												
												</legend> /-->
												<div id="div-champ_supp_0" >
																	
													<div class="form-group">				
														<div class="panel panel-ats panel-info col-sm-12">
															<div class="panel-heading panel-heading-ats p-b-20">
																<div class="panel-heading-btn">
																	<div class="btn-group pull-right">
																		<!-- <button class="btn btn-success btn-xs" id="buttonplus_participation_commune" type="button" data-toggle="tooltip" data-placement="top" data-original-title="Ajouter un champ" onclick="bouton_ajouter_champ('participation',1)";><i class="fa fa-plus-square-o"></i></button> /-->
																		<!-- <a id="buttonplus_participation_commune" class="btn btn-success btn-xs" href="javascript:;" data-toggle="tooltip" data-placement="top" data-original-title="Ajouter un champ" onclick="bouton_ajouter_champ('participation',0)"><i class="fa fa-2x fa-plus-circle"></i></a> /-->
																		<!-- <button class="btn btn-danger btn-xs" id="buttonmoins" type="button" data-toggle="tooltip" data-placement="top" data-original-title="Supprimer un champ"><i class="fa fa-minus-square"></i></button> /-->
																	</div>
																</div>
																<h4 class="panel-title">Champs de type Participation commun à l'épreuve</h4>
															</div>
															<?php //echo "xxxxx".count($f->champssupparticipation_nom_commune); ?>
															
															<div id="div_up_participation[0]" class="panel-body panel-body-ats" <?php if (count($f->champssupparticipation_nom_commune[0])) "style='display:visible'"; else echo "style=\"display:none\""; ?>>
																<table class="table table-striped">
																	<thead>
																		<tr>
																			<th>Ordre</th>
																			<th>Nom</th>
																			<th>Type</th>
																			<th>Action</th>
																		</tr>
																	</thead>
																	<tbody id="table_up_participation[0]">
																	<?php $perso = 1 ; if (count($f->champssupparticipation_nom_commune[0])) { ?>
																	<?php 	$arr_length = count($f->champssupparticipation_nom_commune[0]);
																	for($id=1;$id<($arr_length+1);$id++)
																	{ ?>	
																		
																		<tr id="tr_participation[0]_<?php echo $id; ?>">
																			<td id="td_ordre_participation_0_<?php echo $id; ?>"> <input size="1" type="text" value="<?php if($f->champssupparticipation_ordre_commune[0][$id]) { echo $f->champssupparticipation_ordre_commune[0][$id];} else { echo $id; }?>" name="epre_parcours_input_ordre_participation[0][<?php echo $id; ?>]" id="epre_parcours_input_ordre_participation[0][<?php echo $id; ?>]"> </td>
																			<td id="td_nom_participation_0_<?php echo $id; ?>"><?php echo $f->champssupparticipation_nom_commune[0][$id]; ?></td>
																			<td>
																				<SELECT name="epre_parcours_select_participation[0][<?php echo $id; ?>]" id="epre_parcours_select_participation[0][<?php echo $id; ?>]" class="form-control input-panel-heading" onchange="Get_Champs_Sup('participation',0,<?php echo $id; ?>)">
																				<OPTION VALUE="0"> Votre sélection </OPTION>
																				<?php 	foreach ($f->participationpreremplie_commune as $k=>$i) { ?>
																				<OPTION VALUE="<?php echo $k; ?>"<?php if($i == $f->champssupparticipation_nom_commune[0][$id]) { echo " SELECTED"; $perso = 0; } else { echo ""; }?>><?php echo $i; ?></OPTION>
																				<?php } ?>
																				<OPTION VALUE="-1" <?php if($perso==1) echo " SELECTED"; else echo "";?>> Personnalisée </OPTION>
																				</select>
																				
																			<td id="td_action_participation_0_<?php echo $id; ?>"><a class="fa fa-2x fa-wrench m-r-5" onclick="editer_fiche_champ('participation',0,<?php echo $id; ?>);" href="javascript:;"></a><a class="fa fa-2x fa-times text-danger" onclick="bouton_supprimer_champ('participation',0,<?php echo $id; ?>);" href="javascript:;"></a>
																			</td>
																		</tr>
																	<?php } ?>
																	<?php } ?>
																	</tbody>
																</table>
																
															</div>
															<a id="buttonplus_participation_commune" class="btn btn-success btn-xs" href="javascript:;" data-toggle="tooltip" data-placement="top" data-original-title="Ajouter un champ" onclick="bouton_ajouter_champ('participation',0)"><i class="fa fa-2x fa-plus-circle"></i></a>
															<input type="hidden" id="epre_parc_nbchampsparticipation[0]" name="epre_parc_nbchampsparticipation[0]" value="<?php echo $arr_length; ?>">	
														</div>
													</div>
														
																<div id="affichage_modal_participation[0]">
																	<?php	$arr_length = count($f->champssupparticipation_nom_commune[0]);
																	for($id=1;$id<($arr_length+1);$id++)
																		{ ?>
																			<?php if ($f->champssupparticipation_nom_commune[0][$id] != '') { ?>
																				<div class="modal" id="epre_parcours_participation_modal[0][<?php echo $id; ?>]" style="display: none;" aria-hidden="true">	
																					<div class="modal-dialog">
																						<div class="modal-content">
																							<div style="text-align: right;" class="modal-header">
																								Paramétrage du champs
																							</div>
																							<div class="modal-body">
																								<div class="form-group">
																									<label class="col-sm-3 control-label">Référence</label>
																									<div class="col-sm-9">
																										<input type="text" onchange="$('#td_nom_participation_0_<?php echo $id; ?>').html(this.value);" maxlength="50" value="<?php echo $f->champssupparticipation_nom_commune[0][$id]; ?>" id="epre_parcours_nom_participation[0][<?php echo $id; ?>]" name="epre_parcours_nom_participation[0][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																									</div>
																								</div>
																								<div class="form-group">
																									<label class="col-sm-3 control-label">Question posée</label>
																									<div class="col-sm-9">
																										<input type="text" maxlength="1000" value="<?php echo $f->champssupparticipation_label_commune[0][$id]; ?>" id="epre_parcours_label_participation[0][<?php echo $id; ?>]" name="epre_parcours_label_participation[0][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																									</div>
																								</div>
																								<div class="form-group">
																									<label class="col-sm-3 control-label">Prix (en €)</label>
																									<div class="col-sm-9">
																										<input type="text" placeholder="ex : 10" maxlength="5" value="<?php echo $f->champssupparticipation_prix_commune[0][$id]; ?>" id="epre_parcours_prix_participation[0][<?php echo $id; ?>]" name="epre_parcours_prix_participation[0][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																										<ul class="list-unstyled">
																											<li>Indiquez le prix par <b>unité et/ou participant</b></li>
																										</ul>
																									</div>
																								</div>
																								<div class="form-group">
																									<label class="col-sm-3 control-label">Quantité</label>
																									<div class="col-sm-9">
																										<input type="text" placeholder="ex : 200" maxlength="5" value="<?php echo $f->champssupparticipation_qte_commune[0][$id]; ?>" id="epre_parcours_qte_participation[0][<?php echo $id; ?>]" name="epre_parcours_qte_participation[0][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																										<ul class="list-unstyled">
																											<li>Indiquez la quantité disponible</b></li>
																										</ul>
																									</div>
																								</div>

																								<div class="form-group">
																								<label class="col-sm-3 control-label">Choix au participant</label>
																											<div class="col-md-9">
																												<label class="radio-inline">
																													<input  type="radio" value="SELECT" name="epre_parcours_type_champ_participation[0][<?php echo $id; ?>]" id="epre_parcours_type_champ_participation[0][<?php echo $id; ?>]" <?php if($f->champssupparticipation_type_champ_commune[0][$id] == "SELECT") echo " CHECKED";?>> Choix multiple (select)
																													<input class="m-l-3" type="radio" value="RADIO" name="epre_parcours_type_champ_participation[0][<?php echo $id; ?>]" id="epre_parcours_type_champ_participation[0][<?php echo $id; ?>]" <?php if($f->champssupparticipation_type_champ_commune[0][$id] == "RADIO") echo " CHECKED";?>> <span class="m-l-20">Choix unique (Oui/non)	</span>
																											</label>
																											</div>
																								</div>
																								<div class="form-group">
																								<label class="col-sm-3 control-label">Date limite</label>
																									<div class="col-md-9">
																										<div class="input-group">
																											<input type="text" style="z-index:100000" class="form-control input-inline input-xs m-l-15" value="<?php echo $f->champssupparticipation_date_butoir_commune[0][$id]; ?>" id="epre_parcours_date_butoir_participation[0][<?php echo $id; ?>]" name="epre_parcours_date_butoir_participation[0][<?php echo $id; ?>]">
																										</div>
																									</div>
																								</div>
																								<div class="form-group"><label class="col-sm-3 control-label">Informations</label>
																									<div class="col-md-9">
																										<div class="input-group">
																											<textarea style="z-index:100000" class="form-control m-l-15" id="epre_parcours_information_participation[0][<?php echo $id; ?>]" name="epre_parcours_information_participation[0][<?php echo $id; ?>]"><?php echo $f->champssupparticipation_information_commune[0][$id]; ?></textarea>
																										</div>
																									</div>
																										<ul class="list-unstyled" id="aff_critere_ul_information_0_<?php echo $id; ?>">
																											<li>Information sur le champ qui seront affichées sur la fiche d'inscription</li>
																										</ul>
																								</div> 																								
																								<div class="form-group">
																								<label class="col-sm-3 control-label">Obligatoire?</label>
																									<div class="col-md-9">
																										<label class="radio-inline">
																											<input type="radio" value="non" name="epre_parcours_critere_obligatoire_participation[0][<?php echo $id; ?>]" id="epre_parcours_critere_obligatoire_participation[0]<?php echo $id; ?>]" <?php if($f->champssupparticipation_obligatoire_commune[0][$id] == "non") echo " CHECKED"; else echo "";?>> Non
																											<input class="m-l-3" type="radio" value="oui" name="epre_parcours_critere_obligatoire_participation[0][<?php echo $id; ?>]" id="epre_parcours_critere_obligatoire_participation[0][<?php echo $id; ?>]" <?php if($f->champssupparticipation_obligatoire_commune[0][$id] == "oui") echo " CHECKED"; else echo "";?>> <span class="m-l-20">Oui </span>
																										</label>
																									</div>
																								</div>
																							</div>
																							<div class="modal-footer"><a data-dismiss="modal" id="fermer_resultat" class="btn btn-primary" href="#">Valider</a>
																							</div>
																						</div>
																					</div>
																				</div>
																				<?php } ?>
																		<?php	} ?>
																	</div>
	
																	<div id="affichage_modal_exemple_0">
																			<div class="modal" id="affichage_modal_exemple_0" aria-hidden="true">	
																				<div class="modal-dialog">
																					<div class="modal-content">
																						<div style="text-align: right;" class="modal-header">
																							Paramétrage du champs
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
																	</div>
												</div>
													
											<?php } ?>
											
											
													<?php if ($type_champ=='q_epreuve' || $type_champ=='tous' ) { $_SESSION['mod_epre_ids_questiondiverse_commune'] = $tab_questiondiverse_commune_id;?>
													<div class="form-group">
													<div class="panel panel-ats panel-info col-sm-12">
														<div class="panel-heading panel-heading-ats p-b-20">
															<div class="panel-heading-btn">
																<div class="btn-group pull-right">
																	<!-- <button class="btn btn-success btn-xs" id="buttonplus_questiondiverse[0]" type="button" data-toggle="tooltip" data-placement="top" data-original-title="Ajouter un champ" onclick="bouton_ajouter_champ('questiondiverse',1)";><i class="fa fa-plus-square-o"></i></button>
																	 <button class="btn btn-danger btn-xs" id="buttonmoins" type="button" data-toggle="tooltip" data-placement="top" data-original-title="Supprimer un champ"><i class="fa fa-minus-square"></i></button> /-->
																	<!-- <a id="buttonplus_questiondiverse[0]" class="btn btn-success btn-xs" href="javascript:;" data-toggle="tooltip" data-placement="top" data-original-title="Ajouter un champ" onclick="bouton_ajouter_champ('questiondiverse',1)"><i class="fa fa-2x fa-plus-circle"></i></a> /-->
																</div>
															</div>
															<h4 class="panel-title">Champs de type questiondiverse uniquement pour l'épreuve</h4>
														</div>
														<?php //echo "xxxxx".count($f->champssupquestiondiverse_nom[0]); ?>
														
														<div id="div_up_questiondiverse[0]" class="panel-body panel-body-ats" <?php if (count($f->champssupquestiondiverse_nom_commune[0])) "style='display:visible'"; else echo "style=\"display:none\""; ?>>
															<table class="table table-striped">
																<thead>
																	<tr>
																		<th>Ordre</th>
																		<th>Nom</th>
																		<th>Type</th>
																		<th>Action</th>
																	</tr>
																</thead>
																<tbody id="table_up_questiondiverse[0]">
																<?php $perso = 1 ; if (count($f->champssupquestiondiverse_nom_commune[0])) { ?>
																<?php 	$arr_length = count($f->champssupquestiondiverse_nom_commune[0]);
																for($id=1;$id<($arr_length+1);$id++)
																{ ?>	
																
																	<tr id="tr_questiondiverse[0]_<?php echo $id; ?>">
																		<td id="td_ordre_questiondiverse_0_<?php echo $id; ?>"> <input size="1" type="text" value="<?php if($f->champssupquestiondiverse_ordre_commune[0][$id]) { echo $f->champssupquestiondiverse_ordre_commune[0][$id];} else { echo $id; }?>" name="epre_parcours_input_ordre_questiondiverse[0][<?php echo $id; ?>]" id="epre_parcours_input_ordre_questiondiverse[0][<?php echo $id; ?>]"> </td>
																		<td id="td_nom_questiondiverse_0_<?php echo $id; ?>"><?php echo $f->champssupquestiondiverse_nom_commune[0][$id]; ?></td>
																		<td>
																			<SELECT name="epre_parcours_select_questiondiverse[0][<?php echo $id; ?>]" id="epre_parcours_select_questiondiverse[0][<?php echo $id; ?>]" class="form-control input-panel-heading" onchange="Get_Champs_Sup('questiondiverse',0,<?php echo $id; ?>)">
																			<OPTION VALUE="0"> Votre sélection </OPTION>
																			<?php 	foreach ($f->questiondiversepreremplie_commune as $k=>$i) { ?>
																			<OPTION VALUE="<?php echo $k; ?>"<?php if($i == $f->champssupquestiondiverse_nom_commune[0][$id]) { echo " SELECTED"; $perso = 0; } else { echo ""; }?>><?php echo $i; ?></OPTION>
																			<?php } ?>
																			<OPTION VALUE="-1" <?php if($perso==1) echo " SELECTED"; else echo "";?>> Personnalisée </OPTION>
																			</select>
																			
																		<td id="td_action_questiondiverse[0]_<?php echo $id; ?>"><a class="fa fa-2x fa-wrench m-r-5" onclick="editer_fiche_champ('questiondiverse',0,<?php echo $id; ?>);" href="javascript:;"></a><a class="fa fa-2x fa-times text-danger"  onclick="bouton_supprimer_champ('questiondiverse',0,<?php echo $id; ?>);" href="javascript:;"></a>
																		</td>
																	</tr>
																<?php } ?>
																<?php } ?>
																</tbody>
															</table>
														
														</div>
													<a id="buttonplus_questiondiverse_commune" class="btn btn-success btn-xs" href="javascript:;" data-toggle="tooltip" data-placement="top" data-original-title="Ajouter un champ" onclick="bouton_ajouter_champ('questiondiverse',0)"><i class="fa fa-2x fa-plus-circle"></i></a>
															<input type="hidden" id="epre_parc_nbchampsquestiondiverse[0]" name="epre_parc_nbchampsquestiondiverse[0]" value="<?php echo $arr_length; ?>">	
													</div>
													</div>
														
													<div id="affichage_modal_questiondiverse[0]">
													<?php	$arr_length = count($f->champssupquestiondiverse_nom_commune[0]);
													for($id=1;$id<($arr_length+1);$id++)
														{ ?>
															<?php if ($f->champssupquestiondiverse_nom_commune[0][$id] != '') { ?>
																<div class="modal" id="epre_parcours_questiondiverse_modal[0][<?php echo $id; ?>]" style="display: none;" aria-hidden="true">	
																	<div class="modal-dialog">
																		<div class="modal-content">
																			<div style="text-align: right;" class="modal-header">
																				Paramétrage du champs
																			</div>
																			<div class="modal-body">
																				<div class="form-group">
																					<label class="col-sm-3 control-label">Référence</label>
																					<div class="col-sm-9">
																						<input type="text" onchange="$('#td_nom_questiondiverse_0_<?php echo $id; ?>').html(this.value);" maxlength="50" value="<?php echo $f->champssupquestiondiverse_nom_commune[0][$id]; ?>" id="epre_parcours_nom_questiondiverse[0][<?php echo $id; ?>]" name="epre_parcours_nom_questiondiverse[0][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																					</div>
																				</div>
																				<div class="form-group">
																					<label class="col-sm-3 control-label">Question posée</label>
																					<div class="col-sm-9">
																						<input type="text" maxlength="1000" value="<?php echo $f->champssupquestiondiverse_label_commune[0][$id]; ?>" id="epre_parcours_label_questiondiverse[0][<?php echo $id; ?>]" name="epre_parcours_label_questiondiverse[0][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																					</div>
																				</div>
																				<div class="form-group" id="aff_critere_questiondiverse_0_<?php echo $id; ?>">
																					<label class="col-sm-3 control-label">critere</label>
																					<div class="col-sm-9">
																						<input type="text" placeholder="ex : XS(100);S(50);XL(200)" value="<?php echo $f->champssupquestiondiverse_critere_commune[0][$id]; ?>" id="epre_parcours_critere_questiondiverse[0][<?php echo $id; ?>]" name="epre_parcours_critere_questiondiverse[0][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																						<ul class="list-unstyled">
																							<li>Séparez les critères par des ";"</li>
																						</ul>
																					</div>
																				</div>
																				<div class="form-group">
																				<label class="col-sm-3 control-label">Type de Champ </label>
																					<div class="col-sm-4">
																						<select class="form-control" id="epre_parcours_type_champ_questiondiverse[0][<?php echo $id; ?>]" name="epre_parcours_type_champ_questiondiverse[0][<?php echo $id; ?>]" onchange="affichage_critere_ou_pas(this.value,'questiondiverse',0,<?php echo $id; ?>);">
																							<option value="SELECT"<?php if($f->champssupquestiondiverse_type_champ_commune[0][$id]=='SELECT') echo " SELECTED"; else echo "";?> > SELECT </option>
																							<option value="RADIO" <?php if($f->champssupquestiondiverse_type_champ_commune[0][$id]=='RADIO') echo " SELECTED"; else echo "";?>> RADIO </option>
																							<option value="CASE" <?php if($f->champssupquestiondiverse_type_champ_commune[0][$id]=='CASE') echo " SELECTED"; else echo "";?>> CASE A COCHER </option>
																							<option value="INPUT" <?php if($f->champssupquestiondiverse_type_champ_commune[0][$id]=='INPUT') echo " SELECTED"; else echo "";?>> INPUT </option>
																							<option value="TEXTAREA" <?php if($f->champssupquestiondiverse_type_champ_commune[0][$id]=='TEXTAREA') echo " SELECTED"; else echo "";?>> TEXTAREA </option>
																							<option value="FILE" <?php if($f->champssupquestiondiverse_type_champ_commune[0][$id]=='FILE') echo " SELECTED"; else echo "";?>> FILE </option>
																						</select>
																					</div>
																				</div>
																				<div class="form-group"><label class="col-sm-3 control-label">Date limite</label>
																					<div class="input-group">
																						<input type="text" style="z-index:100000" class="form-control input-inline input-xs m-l-15" value="<?php echo $f->champssupquestiondiverse_date_butoir_commune[0][$id]; ?>" id="epre_parcours_date_butoir_questiondiverse[0][<?php echo $id; ?>]" name="epre_parcours_date_butoir_questiondiverse[0][<?php echo $id; ?>]">
																					</div>
																				</div>
																				<div class="form-group"><label class="col-sm-3 control-label">Informations</label>
																					<div class="col-md-9">
																						<div class="input-group">
																							<textarea style="z-index:100000" class="form-control m-l-15" id="epre_parcours_information_questiondiverse[0][<?php echo $id; ?>]" name="epre_parcours_information_questiondiverse[0][<?php echo $id; ?>]"><?php echo $f->champssupquestiondiverse_information_commune[0][$id]; ?></textarea>
																						</div>
																					</div>
																						<ul class="list-unstyled" id="aff_critere_ul_information_0_<?php echo $id; ?>">
																							<li>Information sur le champ qui seront affichées sur la fiche d'inscription</li>
																						</ul>
																				</div> 																				
																				<div class="form-group">
																				<label class="col-sm-3 control-label">Obligatoire?</label>
																					<div class="col-md-9">
																						<label class="radio-inline">
																							<input type="radio" value="non" name="epre_parcours_critere_obligatoire_questiondiverse[0][<?php echo $id; ?>]" id="epre_parcours_critere_obligatoire_questiondiverse[0][<?php echo $id; ?>]" <?php if($f->champssupquestiondiverse_obligatoire_commune[0][$id] == "non") echo " CHECKED"; else echo "";?>> Non
																							<input class="m-l-3" type="radio" value="oui" name="epre_parcours_critere_obligatoire_questiondiverse[0][<?php echo $id; ?>]" id="epre_parcours_critere_obligatoire_questiondiverse[0][<?php echo $id; ?>]" <?php if($f->champssupquestiondiverse_obligatoire_commune[0][$id] == "oui") echo " CHECKED"; else echo "";?>> <span class="m-l-20"> Oui</span>
																						</label>
																					</div>
																				</div>
																				
																			</div>
																			<div class="modal-footer"><a data-dismiss="modal" id="fermer_resultat" class="btn btn-primary" href="#">Valider</a>
																			</div>
																		</div>
																	</div>
																</div>
																<?php	} ?>
														<?php	} ?>
													</div>
		
													<div id="affichage_modal_exemple[1]">
															<div class="modal" id="affichage_modal_exemple[1]" aria-hidden="true">	
																<div class="modal-dialog">
																	<div class="modal-content">
																		<div style="text-align: right;" class="modal-header">
																			header
																		</div>
																		<div class="modal-body">
																			<fieldset > Exemple des champs prédéfinis-</fieldset><hr>
																			<div id="exemple_champs_param[1]"></div>
																		</div>
																		<div class="modal-footer"><a data-dismiss="modal" id="fermer_resultat" class="btn btn-primary" href="#">OK</a>
																		</div>
																	</div>
																</div>
															</div>
													</div>
												<?php } ?>											
											
											
											
											
											
											
											
											
											
											
											
											
											
											
											</fieldset>	
											
											
											
											
											
											
											
											
											
											
											
											
											
											
											
											<fieldset>
												<div id="div-champ_supp_1">
													<?php if ($type_champ=='d_parcours' || $type_champ=='tous' ) { $_SESSION['mod_epre_ids_dotation'] = $tab_dotation_id;?>
													<div class="form-group">
														<div class="panel panel-ats panel-info col-sm-12">
															<div class="panel-heading panel-heading-ats p-b-20">
																<div class="panel-heading-btn">

																		<!-- <button class="btn btn-success btn-xs" id="buttonplus_dotation[1]" type="button" data-toggle="tooltip" data-placement="top" data-original-title="Ajouter un champ" onclick="bouton_ajouter_champ('dotation',1)";><i class="fa fa-2x fa-plus-circle"></i></button> /-->
																		<!-- <a id="buttonplus_dotation[1]" class="btn btn-success btn-xs" href="javascript:;" data-toggle="tooltip" data-placement="top" data-original-title="Ajouter un champ" onclick="bouton_ajouter_champ('dotation',1)"><i class="fa fa-2x fa-plus-circle"></i></a> /-->
																		<!-- <button class="btn btn-danger btn-xs" id="buttonmoins" type="button" data-toggle="tooltip" data-placement="top" data-original-title="Supprimer un champ"><i class="fa fa-minus-square"></i></button> /-->

																</div>
																<h3 class="panel-title">Champs de type Dotation uniquement pour le parcours</h3>
															</div>
															<?php //echo "xxxxx".count($f->champssupdotation_nom[1]); ?>
															
															<div id="div_up_dotation[1]" class="panel-body panel-body-ats" <?php if (count($f->champssupdotation_nom[1])) "style='display:visible'"; else echo "style=\"display:none\""; ?>>
																
																<table class="table table-striped">
																	<thead>
																		<tr>
																			<th>Ordre</th>
																			<th>Nom</th>
																			<th>Type</th>
																			<th>Action</th>
																		</tr>
																	</thead>
																	<tbody id="table_up_dotation[1]">
																	<?php $perso = 1 ; if (count($f->champssupdotation_nom[1])) { ?>
																	<?php 	$arr_length = count($f->champssupdotation_nom[1]);
																	for($id=1;$id<($arr_length+1);$id++)
																	{ ?>	
																	
																		<tr id="tr_dotation[1]_<?php echo $id; ?>">
																			<td id="td_ordre_dotation_1_<?php echo $id; ?>"> <input size="1" type="text" value="<?php if($f->champssupdotation_ordre[1][$id]) { echo $f->champssupdotation_ordre[1][$id];} else { echo $id; }?>" name="epre_parcours_input_ordre_dotation[1][<?php echo $id; ?>]" id="epre_parcours_input_ordre_dotation[1][<?php echo $id; ?>]"> </td>
																			<td id="td_nom_dotation_1_<?php echo $id; ?>"><?php echo $f->champssupdotation_nom[1][$id]; ?></td>
																			<td id="td_select_dotation_1_<?php echo $id; ?>">
																				<SELECT name="epre_parcours_select_dotation[1][<?php echo $id; ?>]" id="epre_parcours_select_dotation[1][<?php echo $id; ?>]" class="form-control input-panel-heading" onchange="Get_Champs_Sup('dotation',1,<?php echo $id; ?>)">
																				<OPTION VALUE="0"> Votre sélection </OPTION>
																				<?php 	foreach ($f->dotationpreremplie as $k=>$i) { ?>
																				<OPTION VALUE="<?php echo $k; ?>"<?php if($i == $f->champssupdotation_nom[1][$id]) { echo " SELECTED"; $perso = 0; } else { echo ""; }?>><?php echo $i; ?></OPTION>
																				<?php } ?>
																				<OPTION VALUE="-1" <?php if($perso==1) echo " SELECTED"; else echo "";?>> Personnalisée </OPTION>
																				</select>
																				
																			<td id="td_action_dotation[1]_<?php echo $id; ?>"><a class="fa fa-2x fa-wrench m-r-5" onclick="editer_fiche_champ('dotation',1,<?php echo $id; ?>);" href="javascript:;"></a><a class="fa fa-2x fa-times" <i="" onclick="bouton_supprimer_champ('dotation',1,<?php echo $id; ?>);" href="javascript:;"></a>
																			</td>
																		</tr>
																	<?php } ?>
																	<?php } ?>
																	</tbody>
																</table>
															
															</div>
															<a id="buttonplus_dotation[1]" class="btn btn-success btn-xs" href="javascript:;" data-toggle="tooltip" data-placement="top" data-original-title="Ajouter un champ" onclick="bouton_ajouter_champ('dotation',1)"><i class="fa fa-2x fa-plus-circle"></i></a>
																<input type="hidden" id="epre_parc_nbchampsdotation[1]" name="epre_parc_nbchampsdotation[1]" value="<?php echo $arr_length; ?>">	
														</div>
													</div>	
													<div id="affichage_modal_dotation[1]">
													<?php	$arr_length = count($f->champssupdotation_nom[1]);
													for($id=1;$id<($arr_length+1);$id++)
														{ ?>
															<?php if ($f->champssupdotation_nom[1][$id] != '') { ?>
																<div class="modal" id="epre_parcours_dotation_modal[1][<?php echo $id; ?>]" style="display: none;" aria-hidden="true">	
																	<div class="modal-dialog">
																		<div class="modal-content">
																			<div style="text-align: right;" class="modal-header">
																				Création champ de type Dotation supplémentaire
																			</div>
																			<div class="modal-body">
																				<div class="form-group">
																					<label class="col-sm-3 control-label">Référence</label>
																					<div class="col-sm-9">
																						<input type="text" onchange="$('#td_nom_dotation_1_<?php echo $id; ?>').html(this.value);" maxlength="50" value="<?php echo $f->champssupdotation_nom[1][$id]; ?>" id="epre_parcours_nom_dotation[1][<?php echo $id; ?>]" name="epre_parcours_nom_dotation[1][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																					</div>
																				</div>
																				<div class="form-group">
																					<label class="col-sm-3 control-label">Question posée</label>
																					<div class="col-sm-9">
																						<input type="text" maxlength="1000" value="<?php echo $f->champssupdotation_label[1][$id]; ?>" id="epre_parcours_label_dotation[1][<?php echo $id; ?>]" name="epre_parcours_label_dotation[1][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																					</div>
																				</div>
																				<div class="form-group" id="aff_critere_dotation_1_<?php echo $id; ?>">
																					<label class="col-sm-3 control-label">critere</label>
																					<div class="col-sm-9">
																						<input type="text" placeholder="ex : XS(100);S(50);XL(200)" value="<?php echo $f->champssupdotation_critere[1][$id]; ?>" id="epre_parcours_critere_dotation[1][<?php echo $id; ?>]" name="epre_parcours_critere_dotation[1][<?php echo $id; ?>]" class="form-control input-inline input-xs" onchange="check_pattern(this.value,'dotation',1,<?php echo $id; ?>);">
																						<ul class="list-unstyled" id="aff_critere_ul_dotation_1_<?php echo $id; ?>">
																							<li>Séparez les critères par des ";" et indiquez la quantité disponible entre parenthèse (mettre une "*" pour illimité)</li>
																						</ul>
																					</div>
																				</div>
																				<div class="form-group">
																				<label class="col-sm-3 control-label">Type de Champ </label>
																					<div class="col-sm-4">
																						<select class="form-control" id="epre_parcours_type_champ_dotation[1][<?php echo $id; ?>]" name="epre_parcours_type_champ_dotation[1][<?php echo $id; ?>]" onchange="affichage_critere_ou_pas(this.value,'dotation',1,<?php echo $id; ?>);">
																							<option value="SELECT"<?php if($f->champssupdotation_type_champ[1][$id]=='SELECT') echo " SELECTED"; else echo "";?> > SELECT </option>
																							<option value="RADIO" <?php if($f->champssupdotation_type_champ[1][$id]=='RADIO') echo " SELECTED"; else echo "";?>> RADIO </option>
																							<option value="CASE" <?php if($f->champssupdotation_type_champ[1][$id]=='CASE') echo " SELECTED"; else echo "";?>> CASE A COCHER </option>
																							<option value="INPUT" <?php if($f->champssupdotation_type_champ[1][$id]=='INPUT') echo " SELECTED"; else echo "";?>> INPUT </option>
																							<option value="TEXTAREA" <?php if($f->champssupdotation_type_champ[1][$id]=='TEXTAREA') echo " SELECTED"; else echo "";?>> TEXTAREA </option>
																						</select>
																					</div>
																				</div>
																				<div class="form-group"><label class="col-sm-3 control-label">Date limite</label>
																					<div class="col-md-9">
																						<div class="input-group">
																							<input type="text" style="z-index:100000" class="form-control input-inline input-xs" value="<?php echo $f->champssupdotation_date_butoir[1][$id]; ?>" id="epre_parcours_date_butoir_dotation[1][<?php echo $id; ?>]" name="epre_parcours_date_butoir_dotation[1][<?php echo $id; ?>]">
																						</div>
																					</div>
																				</div>  
																				<div class="form-group"><label class="col-sm-3 control-label">Informations</label>
																					<div class="col-md-9">
																						<div class="input-group">
																							<textarea style="z-index:100000" class="form-control m-l-15" id="epre_parcours_information_dotation[1][<?php echo $id; ?>]" name="epre_parcours_information_dotation[1][<?php echo $id; ?>]"><?php echo $f->champssupdotation_information[1][$id]; ?></textarea>
																						</div>
																					</div>
																						<ul class="list-unstyled" id="aff_critere_ul_information_0_<?php echo $id; ?>">
																							<li>Information sur le champ qui seront affichées sur la fiche d'inscription</li>
																						</ul>
																				</div> 
																				<div class="form-group">
																				<label class="col-sm-3 control-label">Obligatoire?</label>
																					<div class="col-md-9">
																						<label class="radio-inline">
																							<input type="radio" value="non" name="epre_parcours_critere_obligatoire_dotation[1][<?php echo $id; ?>]" id="epre_parcours_critere_obligatoire_dotation[1][<?php echo $id; ?>]" <?php if($f->champssupdotation_obligatoire[1][$id] == "non") echo " CHECKED"; else echo "";?>> Non
																							<input class="m-l-3" type="radio" value="oui" name="epre_parcours_critere_obligatoire_dotation[1][<?php echo $id; ?>]" id="epre_parcours_critere_obligatoire_dotation[1][<?php echo $id; ?>]" <?php if($f->champssupdotation_obligatoire[1][$id] == "oui") echo " CHECKED"; else echo "";?>> <span class="m-l-20">Oui </span>
																						</label>
																					</div>
																				</div>
																				
																			</div>
																			<div class="modal-footer"><a data-dismiss="modal" id="fermer_resultat" class="btn btn-primary" href="#">Valider</a>
																			</div>
																		</div>
																	</div>
																</div>
																<?php	} ?>
														<?php	} ?>
													</div>
													<?php } ?>
													<?php if ($type_champ=='p_parcours' || $type_champ=='tous' ) { $_SESSION['mod_epre_ids_participation'] = $tab_participation_id;?>
													<div class="form-group">				
													<div class="panel panel-ats panel-info col-sm-12">
														<div class="panel-heading panel-heading-ats p-b-20">
															<div class="panel-heading-btn">
																<div class="btn-group pull-right">
																	<!-- <button class="btn btn-success btn-xs" id="buttonplus_participation[1]" type="button" data-toggle="tooltip" data-placement="top" data-original-title="Ajouter un champ" onclick="bouton_ajouter_champ('participation',1)";><i class="fa fa-plus-square-o"></i></button> /-->
																	<!-- <a id="buttonplus_participation[1]" class="btn btn-success btn-xs" href="javascript:;" data-toggle="tooltip" data-placement="top" data-original-title="Ajouter un champ" onclick="bouton_ajouter_champ('participation',1)"><i class="fa fa-2x fa-plus-circle"></i></a> /-->
																	<!-- <button class="btn btn-danger btn-xs" id="buttonmoins" type="button" data-toggle="tooltip" data-placement="top" data-original-title="Supprimer un champ"><i class="fa fa-minus-square"></i></button> /-->
																</div>
															</div>
															<h4 class="panel-title">Champs de type participation uniquement pour le parcours</h4>
														</div>
														<?php //echo "xxxxx".count($f->champssupparticipation_nom[1]); ?>
														
														<div id="div_up_participation[1]" class="panel-body panel-body-ats" <?php if (count($f->champssupparticipation_nom[1])) "style='display:visible'"; else echo "style=\"display:none\""; ?>>
															<table class="table table-striped">
																<thead>
																	<tr>
																		<th>Ordre</th>
																		<th>Nom</th>
																		<th>Type</th>
																		<th>Action</th>
																	</tr>
																</thead>
																<tbody id="table_up_participation[1]">
																<?php $perso = 1 ; if (count($f->champssupparticipation_nom[1])) { ?>
																<?php 	$arr_length = count($f->champssupparticipation_nom[1]);
																for($id=1;$id<($arr_length+1);$id++)
																{ ?>	
																
																	<tr id="tr_participation[1]_<?php echo $id; ?>">
																		<td id="td_ordre_participation_1_<?php echo $id; ?>"> <input size="1" type="text" value="<?php if($f->champssupparticipation_ordre[1][$id]) { echo $f->champssupparticipation_ordre[1][$id];} else { echo $id; }?>" name="epre_parcours_input_ordre_participation[1][<?php echo $id; ?>]" id="epre_parcours_input_ordre_participation[1][<?php echo $id; ?>]"> </td>
																		<td id="td_nom_participation_1_<?php echo $id; ?>"><?php echo $f->champssupparticipation_nom[1][$id]; ?></td>
																		<td>
																			<SELECT name="epre_parcours_select_participation[1][<?php echo $id; ?>]" id="epre_parcours_select_participation[1][<?php echo $id; ?>]" class="form-control input-panel-heading" onchange="Get_Champs_Sup('participation',1,<?php echo $id; ?>)">
																			<OPTION VALUE="0"> Votre sélection </OPTION>
																			<?php 	foreach ($f->participationpreremplie as $k=>$i) { ?>
																			<OPTION VALUE="<?php echo $k; ?>"<?php if($i == $f->champssupparticipation_nom[1][$id]) { echo " SELECTED"; $perso = 0; } else { echo ""; }?>><?php echo $i; ?></OPTION>
																			<?php } ?>
																			<OPTION VALUE="-1" <?php if($perso==1) echo " SELECTED"; else echo "";?>> Personnalisée </OPTION>
																			</select>
																			
																		<td id="td_action_participation[1]_<?php echo $id; ?>"><a class="fa fa-2x fa-wrench m-r-5" onclick="editer_fiche_champ('participation',1,<?php echo $id; ?>);" href="javascript:;"></a><a class="fa fa-2x fa-times" <i="" onclick="bouton_supprimer_champ('participation',1,<?php echo $id; ?>);" href="javascript:;"></a>
																		</td>
																	</tr>
																<?php } ?>
																<?php } ?>
																</tbody>
															</table>
															
														</div>
													<a id="buttonplus_participation[1]" class="btn btn-success btn-xs" href="javascript:;" data-toggle="tooltip" data-placement="top" data-original-title="Ajouter un champ" onclick="bouton_ajouter_champ('participation',1)"><i class="fa fa-2x fa-plus-circle"></i></a>
															<input type="hidden" id="epre_parc_nbchampsparticipation[1]" name="epre_parc_nbchampsparticipation[1]" value="<?php echo $arr_length; ?>">	
													</div>
													</div>
														
													<div id="affichage_modal_participation[1]">
													<?php	$arr_length = count($f->champssupparticipation_nom[1]);
													for($id=1;$id<($arr_length+1);$id++)
														{ ?>
															<?php if ($f->champssupparticipation_nom[1][$id] != '') { ?>
																<div class="modal" id="epre_parcours_participation_modal[1][<?php echo $id; ?>]" style="display: none;" aria-hidden="true">	
																	<div class="modal-dialog">
																		<div class="modal-content">
																			<div style="text-align: right;" class="modal-header">
																				header
																			</div>
																			<div class="modal-body">
																				<div class="form-group">
																					<label class="col-sm-3 control-label">Référence</label>
																					<div class="col-sm-9">
																						<input type="text" onchange="$('#td_nom_participation_1_<?php echo $id; ?>').html(this.value);" maxlength="50" value="<?php echo $f->champssupparticipation_nom[1][$id]; ?>" id="epre_parcours_nom_participation[1][<?php echo $id; ?>]" name="epre_parcours_nom_participation[1][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																					</div>
																				</div>
																				<div class="form-group">
																					<label class="col-sm-3 control-label">Question posée</label>
																					<div class="col-sm-9">
																						<input type="text" maxlength="1000" value="<?php echo $f->champssupparticipation_label[1][$id]; ?>" id="epre_parcours_label_participation[1][<?php echo $id; ?>]" name="epre_parcours_label_participation[1][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																					</div>
																				</div>
																				<div class="form-group">
																					<label class="col-sm-3 control-label">Prix (en €)</label>
																					<div class="col-sm-9">
																						<input type="text" placeholder="ex : 10" maxlength="5" value="<?php echo $f->champssupparticipation_prix[1][$id]; ?>" id="epre_parcours_prix_participation[1][<?php echo $id; ?>]" name="epre_parcours_prix_participation[1][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																						<ul class="list-unstyled">
																							<li>Indiquez le prix par <b>unité et/ou participant</b></li>
																						</ul>
																					</div>
																				</div>
																				<div class="form-group">
																					<label class="col-sm-3 control-label">Quantité</label>
																					<div class="col-sm-4">
																						<input type="text" placeholder="ex : 200" maxlength="5" value="<?php echo $f->champssupparticipation_qte[1][$id]; ?>" id="epre_parcours_qte_participation[1][<?php echo $id; ?>]" name="epre_parcours_qte_participation[1][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																						<ul class="list-unstyled">
																							<li>Indiquez la quantité disponible</b></li>
																						</ul>
																					</div>
																				</div>
																				<div class="form-group">
																				<label class="col-sm-3 control-label">Choix au participant</label>
																							<div class="col-md-9">
																								<label class="radio-inline">
																									<input type="radio" value="SELECT" name="epre_parcours_type_champ_participation[1][<?php echo $id; ?>]" id="epre_parcours_type_champ_participation[1][<?php echo $id; ?>]" <?php if($f->champssupparticipation_type_champ[1][$id] == "SELECT") echo " CHECKED"; else echo "";?>> Choix multiple (select)
																									<input class="m-l-3" type="radio" value="RADIO" name="epre_parcours_type_champ_participation[1][<?php echo $id; ?>]" id="epre_parcours_type_champ_participation[1][<?php echo $id; ?>]" <?php if($f->champssupparticipation_type_champ[1][$id] == "RADIO") echo " CHECKED"; else echo "";?>>  <span class="m-l-20">Choix unique (Oui/non)	</span>
																								</label>
																							</div>
																				</div>
																				<div class="form-group"><label class="col-sm-3 control-label">Date limite</label>
																					<div class="col-md-9">
																						<div class="input-group">
																							<input type="text" style="z-index:100000" class="form-control input-inline input-xs" value="<?php echo $f->champssupparticipation_date_butoir[1][$id]; ?>" id="epre_parcours_date_butoir_participation[1][<?php echo $id; ?>]" name="epre_parcours_date_butoir_participation[1][<?php echo $id; ?>]">
																						</div>
																					</div>
																				</div>  	
																				<div class="form-group"><label class="col-sm-3 control-label">Informations</label>
																					<div class="col-md-9">
																						<div class="input-group">
																							<textarea style="z-index:100000" class="form-control m-l-15" id="epre_parcours_information_participation[1][<?php echo $id; ?>]" name="epre_parcours_information_participation[1][<?php echo $id; ?>]"><?php echo $f->champssupparticipation_information[1][$id]; ?></textarea>
																						</div>
																					</div>
																						<ul class="list-unstyled" id="aff_critere_ul_information_0_<?php echo $id; ?>">
																							<li>Information sur le champ qui seront affichées sur la fiche d'inscription</li>
																						</ul>
																				</div>																				
																				<div class="form-group">
																				<label class="col-sm-3 control-label">Obligatoire?</label>
																					<div class="col-md-9">
																						<label class="radio-inline">
																							<input type="radio" value="non" name="epre_parcours_critere_obligatoire_participation[1][<?php echo $id; ?>]" id="epre_parcours_critere_obligatoire_participation[1][<?php echo $id; ?>]" <?php if($f->champssupparticipation_obligatoire[1][$id] == "non") echo " CHECKED"; else echo "";?>> Non
																							<input class="m-l-3" type="radio" value="oui" name="epre_parcours_critere_obligatoire_participation[1][<?php echo $id; ?>]" id="epre_parcours_critere_obligatoire_participation[1][<?php echo $id; ?>]" <?php if($f->champssupparticipation_obligatoire[1][$id] == "oui") echo " CHECKED"; else echo "";?>>  <span class="m-l-20"> Oui</span>
																						</label>
																					</div>
																				</div>
																				
																			</div>
																			<div class="modal-footer"><a data-dismiss="modal" id="fermer_resultat" class="btn btn-primary" href="#">Valider</a>
																			</div>
																		</div>
																	</div>
																</div>
																<?php } ?>
														<?php	} ?>
													</div>
													<?php } ?>
													
													<?php if ($type_champ=='q_parcours' || $type_champ=='tous' ) { $_SESSION['mod_epre_ids_questiondiverse'] = $tab_questiondiverse_id;?>
													<div class="form-group">
													<div class="panel panel-ats panel-info col-sm-12">
														<div class="panel-heading panel-heading-ats p-b-20">
															<div class="panel-heading-btn">
																<div class="btn-group pull-right">
																	<!-- <button class="btn btn-success btn-xs" id="buttonplus_questiondiverse[1]" type="button" data-toggle="tooltip" data-placement="top" data-original-title="Ajouter un champ" onclick="bouton_ajouter_champ('questiondiverse',1)";><i class="fa fa-plus-square-o"></i></button>
																	 <button class="btn btn-danger btn-xs" id="buttonmoins" type="button" data-toggle="tooltip" data-placement="top" data-original-title="Supprimer un champ"><i class="fa fa-minus-square"></i></button> /-->
																	<!-- <a id="buttonplus_questiondiverse[1]" class="btn btn-success btn-xs" href="javascript:;" data-toggle="tooltip" data-placement="top" data-original-title="Ajouter un champ" onclick="bouton_ajouter_champ('questiondiverse',1)"><i class="fa fa-2x fa-plus-circle"></i></a> /-->
																</div>
															</div>
															<h4 class="panel-title">Champs de type questiondiverse uniquement pour le parcours</h4>
														</div>
														<?php //echo "xxxxx".count($f->champssupquestiondiverse_nom[1]); ?>
														
														<div id="div_up_questiondiverse[1]" class="panel-body panel-body-ats" <?php if (count($f->champssupquestiondiverse_nom[1])) "style='display:visible'"; else echo "style=\"display:none\""; ?>>
															<table class="table table-striped">
																<thead>
																	<tr>
																		<th>Ordre</th>
																		<th>Nom</th>
																		<th>Type</th>
																		<th>Action</th>
																	</tr>
																</thead>
																<tbody id="table_up_questiondiverse[1]">
																<?php $perso = 1 ; if (count($f->champssupquestiondiverse_nom[1])) { ?>
																<?php 	$arr_length = count($f->champssupquestiondiverse_nom[1]);
																for($id=1;$id<($arr_length+1);$id++)
																{ ?>	
																
																	<tr id="tr_questiondiverse[1]_<?php echo $id; ?>">
																		<td id="td_ordre_questiondiverse_1_<?php echo $id; ?>"> <input size="1" type="text" value="<?php if($f->champssupquestiondiverse_ordre[1][$id]) { echo $f->champssupquestiondiverse_ordre[1][$id];} else { echo $id; }?>" name="epre_parcours_input_ordre_questiondiverse[1][<?php echo $id; ?>]" id="epre_parcours_input_ordre_questiondiverse[1][<?php echo $id; ?>]"> </td>
																		<td id="td_nom_questiondiverse_1_<?php echo $id; ?>"><?php echo $f->champssupquestiondiverse_nom[1][$id]; ?></td>
																		<td>
																			<SELECT name="epre_parcours_select_questiondiverse[1][<?php echo $id; ?>]" id="epre_parcours_select_questiondiverse[1][<?php echo $id; ?>]" class="form-control input-panel-heading" onchange="Get_Champs_Sup('questiondiverse',1,<?php echo $id; ?>)">
																			<OPTION VALUE="0"> Votre sélection </OPTION>
																			<?php 	foreach ($f->questiondiversepreremplie as $k=>$i) { ?>
																			<OPTION VALUE="<?php echo $k; ?>"<?php if($i == $f->champssupquestiondiverse_nom[1][$id]) { echo " SELECTED"; $perso = 0; } else { echo ""; }?>><?php echo $i; ?></OPTION>
																			<?php } ?>
																			<OPTION VALUE="-1" <?php if($perso==1) echo " SELECTED"; else echo "";?>> Personnalisée </OPTION>
																			</select>
																			
																		<td id="td_action_questiondiverse[1]_<?php echo $id; ?>"><a class="fa fa-2x fa-wrench m-r-5" onclick="editer_fiche_champ('questiondiverse',1,<?php echo $id; ?>);" href="javascript:;"></a><a class="fa fa-2x fa-times" <i="" onclick="bouton_supprimer_champ('questiondiverse',1,<?php echo $id; ?>);" href="javascript:;"></a>
																		</td>
																	</tr>
																<?php } ?>
																<?php } ?>
																</tbody>
															</table>
														
														</div>
													<a id="buttonplus_questiondiverse[1]" class="btn btn-success btn-xs" href="javascript:;" data-toggle="tooltip" data-placement="top" data-original-title="Ajouter un champ" onclick="bouton_ajouter_champ('questiondiverse',1)"><i class="fa fa-2x fa-plus-circle"></i></a>
															<input type="hidden" id="epre_parc_nbchampsquestiondiverse[1]" name="epre_parc_nbchampsquestiondiverse[1]" value="<?php echo $arr_length; ?>">	
													</div>
													</div>
														
													<div id="affichage_modal_questiondiverse[1]">
													<?php	$arr_length = count($f->champssupquestiondiverse_nom[1]);
													for($id=1;$id<($arr_length+1);$id++)
														{ ?>
															<?php if ($f->champssupquestiondiverse_nom[1][$id] != '') { ?>
																<div class="modal" id="epre_parcours_questiondiverse_modal[1][<?php echo $id; ?>]" style="display: none;" aria-hidden="true">	
																	<div class="modal-dialog">
																		<div class="modal-content">
																			<div style="text-align: right;" class="modal-header">
																				header
																			</div>
																			<div class="modal-body">
																				<div class="form-group">
																					<label class="col-sm-3 control-label">Référence</label>
																					<div class="col-sm-9">
																						<input type="text" onchange="$('#td_nom_questiondiverse_1_<?php echo $id; ?>').html(this.value);" maxlength="50" value="<?php echo $f->champssupquestiondiverse_nom[1][$id]; ?>" id="epre_parcours_nom_questiondiverse[1][<?php echo $id; ?>]" name="epre_parcours_nom_questiondiverse[1][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																					</div>
																				</div>
																				<div class="form-group">
																					<label class="col-sm-3 control-label">Question posée</label>
																					<div class="col-sm-9">
																						<input type="text" maxlength="1000" value="<?php echo $f->champssupquestiondiverse_label[1][$id]; ?>" id="epre_parcours_label_questiondiverse[1][<?php echo $id; ?>]" name="epre_parcours_label_questiondiverse[1][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																					</div>
																				</div>
																				<div class="form-group" id="aff_critere_questiondiverse_1_<?php echo $id; ?>">
																					<label class="col-sm-3 control-label">critere</label>
																					<div class="col-sm-9">
																						<input type="text" placeholder="ex : XS(100);S(50);XL(200)" value="<?php echo $f->champssupquestiondiverse_critere[1][$id]; ?>" id="epre_parcours_critere_questiondiverse[1][<?php echo $id; ?>]" name="epre_parcours_critere_questiondiverse[1][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																						<ul class="list-unstyled">
																							<li>Séparez les critères par des ";"</li>
																						</ul>
																					</div>
																				</div>
																				<div <?php if (isset($f->champssupquestiondiverse_unite[1][$id])) echo 'style="display:visible"'; else echo 'style="display:none"'; ?> class="form-group" id="aff_unite_questiondiverse_1_<?php echo $id; ?>">
																					<label class="col-sm-3 control-label">Nombre de fichier</label>
																					<div class="col-sm-9">
																						<input type="text" placeholder="ex : 1" value="<?php echo $f->champssupquestiondiverse_unite[1][$id]; ?>" id="epre_parcours_unite_questiondiverse[1][<?php echo $id; ?>]" name="epre_parcours_unite_questiondiverse[1][<?php echo $id; ?>]" class="form-control input-inline input-xs">
																					</div>
																				</div>
																				<div class="form-group">
																				<label class="col-sm-3 control-label">Type de Champ </label>
																					<div class="col-sm-4">
																						<select class="form-control" id="epre_parcours_type_champ_questiondiverse[1][<?php echo $id; ?>]" name="epre_parcours_type_champ_questiondiverse[1][<?php echo $id; ?>]" onchange="affichage_critere_ou_pas(this.value,'questiondiverse',1,<?php echo $id; ?>);">
																							<option value="SELECT"<?php if($f->champssupquestiondiverse_type_champ[1][$id]=='SELECT') echo " SELECTED"; else echo "";?> > SELECT </option>
																							<option value="RADIO" <?php if($f->champssupquestiondiverse_type_champ[1][$id]=='RADIO') echo " SELECTED"; else echo "";?>> RADIO </option>
																							<option value="CASE" <?php if($f->champssupquestiondiverse_type_champ[1][$id]=='CASE') echo " SELECTED"; else echo "";?>> CASE A COCHER </option>
																							<option value="INPUT" <?php if($f->champssupquestiondiverse_type_champ[1][$id]=='INPUT') echo " SELECTED"; else echo "";?>> INPUT </option>
																							<option value="TEXTAREA" <?php if($f->champssupquestiondiverse_type_champ[1][$id]=='TEXTAREA') echo " SELECTED"; else echo "";?>> TEXTAREA </option>
																							<option value="FILE" <?php if($f->champssupquestiondiverse_type_champ[1][$id]=='FILE') echo " SELECTED"; else echo "";?>> FILE </option>
																						</select>
																					</div>
																				</div>
																				<div class="form-group"><label class="col-sm-3 control-label">Date limite</label>
																					<div class="input-group">
																						<input type="text" style="z-index:100000" class="form-control input-inline input-xs" value="<?php echo $f->champssupquestiondiverse_date_butoir[1][$id]; ?>" id="epre_parcours_date_butoir_questiondiverse[1][<?php echo $id; ?>]" name="epre_parcours_date_butoir_questiondiverse[1][<?php echo $id; ?>]">
																					</div>
																				</div>
																				<div class="form-group"><label class="col-sm-3 control-label">Informations</label>
																					<div class="col-md-9">
																						<div class="input-group">
																							<textarea style="z-index:100000" class="form-control m-l-15" id="epre_parcours_information_questiondiverse[1][<?php echo $id; ?>]" name="epre_parcours_information_questiondiverse[1][<?php echo $id; ?>]"><?php echo $f->champssupquestiondiverse_information[1][$id]; ?></textarea>
																						</div>
																					</div>
																						<ul class="list-unstyled" id="aff_critere_ul_information_0_<?php echo $id; ?>">
																							<li>Information sur le champ qui seront affichées sur la fiche d'inscription</li>
																						</ul>
																				</div>																					
																				<div class="form-group">
																				<label class="col-sm-3 control-label">Obligatoire?</label>
																					<div class="col-md-9">
																						<label class="radio-inline">
																							<input type="radio" value="non" name="epre_parcours_critere_obligatoire_questiondiverse[1][<?php echo $id; ?>]" id="epre_parcours_critere_obligatoire_questiondiverse[1][<?php echo $id; ?>]" <?php if($f->champssupquestiondiverse_obligatoire[1][$id] == "non") echo " CHECKED"; else echo "";?>> Non
																							<input class="m-l-3" type="radio" value="oui" name="epre_parcours_critere_obligatoire_questiondiverse[1][<?php echo $id; ?>]" id="epre_parcours_critere_obligatoire_questiondiverse[1][<?php echo $id; ?>]" <?php if($f->champssupquestiondiverse_obligatoire[1][$id] == "oui") echo " CHECKED"; else echo "";?>> <span class="m-l-20"> Oui</span>
																						</label>
																					</div>
																				</div>
																				
																			</div>
																			<div class="modal-footer"><a data-dismiss="modal" id="fermer_resultat" class="btn btn-primary" href="#">Valider</a>
																			</div>
																		</div>
																	</div>
																</div>
																<?php	} ?>
														<?php	} ?>
													</div>
		
													<div id="affichage_modal_exemple[1]">
															<div class="modal" id="affichage_modal_exemple[1]" aria-hidden="true">	
																<div class="modal-dialog">
																	<div class="modal-content">
																		<div style="text-align: right;" class="modal-header">
																			header
																		</div>
																		<div class="modal-body">
																			<fieldset > Exemple des champs prédéfinis-</fieldset><hr>
																			<div id="exemple_champs_param[1]"></div>
																		</div>
																		<div class="modal-footer"><a data-dismiss="modal" id="fermer_resultat" class="btn btn-primary" href="#">OK</a>
																		</div>
																	</div>
																</div>
															</div>
													</div>
												<?php } ?>
											</div>
											</fieldset>


										
									
								
                             <!-- end wizard 3/-->
                           
                            
                        
						
						<button class="btn btn-danger btn-lg" type="submit"><strong>Enregistrer</strong></button>
						<input type="hidden" id="epre_nbparc" name="epre_nbparc" value="<?php if(isset($f->nbparc)) echo $f->nbparc; ?>">
						<input type="hidden" id="id_table_parcours" name="id_table_parcours" value="<?php echo $tab_id[1][0]; ?>" />
					</form>
				</div>
			</div>
		</div>
	</div>

            <!-- end row -->

		
        <!-- begin theme-panel -->
		 <?php //include ("includes/panel.php"); ?>
        <!-- end theme-panel -->
		
		<!-- begin scroll to top btn -->
		<a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top fade" data-click="scroll-top"><i class="fa fa-angle-up"></i></a>
		<!-- end scroll to top btn -->

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
	<script src="assets/js/form-wysiwyg.demo.js"></script>
	<!-- <script src="assets/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script> /-->
	<!-- <script src="assets/plugins/bootstrap-wizard/js/bwizard.js"></script> /-->
	<!-- ================== END PAGE LEVEL JS ================== -->
	
	<script>

	
//********************** FONCTION MODAL POUR CHAMP SUPPLEMENTAIRE ***********************

function Create_Modal (champ,numparc,id,cp) {

content ='<div id="epre_parcours_' + champ + '_modal['+numparc+'][' + id + ']" class="modal">';
content +='	<div class="modal-dialog">';
content +='	 	<div class="modal-content">';
content +='    		<div class="modal-header" style="text-align: right;">';
content +='				header';
content +='			</div>';
content +='			<div class="modal-body">';
content +='				<div class="form-group">';
content +='					<label class="col-sm-3 control-label">Référence</label>';
content +='					<div class="col-sm-9">';
content +='						<input type="text" class="form-control input-sm" name="epre_parcours_nom_' + champ + '['+numparc+'][' + id + ']" id="epre_parcours_nom_' + champ + '['+numparc+'][' + id + ']" value="" maxlength="50" onchange="$(\'#td_nom_' + champ + '_'+numparc+'_' + id + '\').html(\'<i>\'+this.value+\'</i>\');">';	
content +='					</div>';
content +='				</div>';
content +='				<div class="form-group">';
content +='					<label class="col-sm-3 control-label">Question posée</label>';
content +='					<div class="col-sm-9">';
content +='						<input type="text" class="form-control input-sm" name="epre_parcours_label_' + champ + '['+numparc+'][' + id + ']" id="epre_parcours_label_' + champ + '['+numparc+'][' + id + ']" value="" maxlength=250>';
content +='					</div>';
content +='				</div>';
if (champ == 'participation') {
	content +='				<div class="form-group">';
	content +='					<label class="col-sm-3 control-label">Prix (en €)</label>';
	content +='					<div class="col-sm-9">';
	content +='						<input type="text" class="form-control input-sm" name="epre_parcours_prix_' + champ + '['+numparc+'][' + id + ']" id="epre_parcours_prix_' + champ + '['+numparc+'][' + id + ']" value="" maxlength="50" placeholder="ex : 10">';
	content +='							<ul class="list-unstyled"><li>Indiquez le prix par <b>unité et/ou participant</b></li></ul>';
	content +='					</div>';
	content +='				</div>';
	content +='				<div class="form-group">';
	content +='					<label class="col-sm-3 control-label">Quantité</label>';
	content +='					<div class="col-sm-9">';
	content +='						<input type="text" class="form-control input-sm" name="epre_parcours_qte_' + champ + '['+numparc+'][' + id + ']" id="epre_parcours_qte_' + champ + '['+numparc+'][' + id + ']" value="" maxlength="50" placeholder="ex : 10">';
	content +='							<ul class="list-unstyled"><li>Indiquez la quantité disponible</li></ul>';
	content +='					</div>';
	content +='				</div>';
	
	content +='				<div class="form-group">';
	content +='					<label class="col-sm-3 control-label">Choix au participant</label>';
	content +='				<div class="col-md-9">';
	content +='					<label class="radio-inline">';
	content +='						<INPUT type="radio" id="epre_parcours_type_champ_' + champ + '['+numparc+'][' + id + ']" name="epre_parcours_type_champ_' + champ + '['+numparc+'][' + id + ']"  value="SELECT"  checked> Choix multiple (select)';
	content +='					</label>';
	content +='					<label class="radio-inline">';
	content +='						<INPUT type="radio" id="epre_parcours_type_champ_' + champ + '['+numparc+'][' + id + ']" name="epre_parcours_type_champ_' + champ + '['+numparc+'][' + id + ']" value="RADIO" > Choix unique (oui/non)';
	content +='					</label>';
	content +='					</div>';
	content +='				</div>';
	
}
if (champ == 'questiondiverse') {

	content +='				<div class="form-group" id="aff_critere_' + champ + '_'+numparc+'_' + id + '">';
	content +='					<label class="col-sm-3 control-label">Critere</label>';
	content +='					<div class="col-sm-9">';
	content +='						<input type="text" class="form-control input-sm" name="epre_parcours_critere_' + champ + '['+numparc+'][' + id + ']" id="epre_parcours_critere_' + champ + '['+numparc+'][' + id + ']" value="" maxlength="1000" placeholder="ex : option1;option2;option3">';
	content +='							<ul class="list-unstyled" id="aff_critere_ul_' + champ + '_'+numparc+'_' + id + '"><li>Séparez les critères par des ";"</li></ul>';
	//content +='							<ul class="list-unstyled" id="aff_critere_ul_radio_case_' + champ + '_'+numparc+'_' + id + '" style="display:none"><li>Séparez les critères par des ";" Gestion des quantités non disponible pour ce type de champ</li></ul>';	
	content +='					</div>';
	content +='				</div>';
	
	content +='	<div style="display:none" class="form-group" id="aff_unite_' + champ + '_'+numparc+'_' + id + '">';
	content +='		<label class="col-sm-3 control-label">Nombre de fichier</label>';
	content +='		<div class="col-sm-9">';
	content +='			<input type="text" placeholder="ex : 1" value="" id="epre_parcours_unite_' + champ + '['+numparc+'][' + id + ']"  name="epre_parcours_unite_' + champ + '['+numparc+'][' + id + ']" class="form-control input-inline input-xs">';
	content +='		</div>';
	content +='	</div>';

}
else if (champ == 'dotation') {

	content +='				<div class="form-group" id="aff_critere_' + champ + '_'+numparc+'_' + id + '">';
	content +='					<label class="col-sm-3 control-label">Critere</label>';
	content +='					<div class="col-sm-9">';
	content +='						<input type="text" class="form-control input-sm" name="epre_parcours_critere_' + champ + '['+numparc+'][' + id + ']" id="epre_parcours_critere_' + champ + '['+numparc+'][' + id + ']" value="" maxlength="1000" placeholder="ex : XS(100);S(50);L(*);XL(200) ou XL;L;M;S" onchange="check_pattern(this.value,\''+champ+'\','+numparc+',' + id + ');">';
	content +='							<ul class="list-unstyled" id="aff_critere_ul_' + champ + '_'+numparc+'_' + id + '"><li>Séparez les critères par des ";" et indiquez la quantité disponible entre parenthèse (mettre une "*" pour illimité)</li></ul>';
	content +='							<ul class="list-unstyled" id="aff_critere_ul_radio_case_' + champ + '_'+numparc+'_' + id + '" style="display:none"><li>Séparez les critères par des ";" Gestion des quantités non disponible pour ce type de champ</li></ul>';	
	content +='					</div>';
	content +='				</div>';

}

if (champ != 'participation') {
	content +='				<div class="form-group">';
	content +='					<label class="col-sm-3 control-label">Type de Champ </label>';
	content +='					<div class="col-sm-4">';
	content +='						<SELECT name="epre_parcours_type_champ_' + champ + '['+numparc+'][' + id + ']" id="epre_parcours_type_champ_' + champ + '['+numparc+'][' + id + ']" class="form-control" onchange="affichage_critere_ou_pas(this.value,\''+champ+'\','+numparc+',' + id + ');">';
	content +='							<OPTION VALUE="SELECT"> SELECT </OPTION>';
	content +='							<OPTION VALUE="RADIO"> RADIO </OPTION>';
	content +='							<OPTION VALUE="CASE"> CASE A COCHER </OPTION>';
	content +='							<OPTION VALUE="INPUT"> INPUT </OPTION>';
	content +='							<OPTION VALUE="TEXTAREA"> TEXTAREA </OPTION>';
	if (champ == 'questiondiverse') {
		content +='							<OPTION VALUE="FILE"> FILE </OPTION>';
	}
	content +='						</SELECT>';
	content +='					</div>';
	content +='				</div>';
}

content +='				<div class="form-group"><label class="col-sm-3 control-label">Date limite</label>';
content +='					<div class="col-sm-9">';
content +='						<div class="input-group">';
content +='							<input name="epre_parcours_date_butoir_' + champ + '['+numparc+'][' + id + ']" id="epre_parcours_date_butoir_' + champ + '['+numparc+'][' + id + ']" value="" type="text"  class="form-control input-sm" style="z-index:100000">';
content +='						</div>';
content +='					</div>';
content +='				</div> ';

content +='	<div class="form-group"><label class="col-sm-3 control-label">Informations</label> ';
content +='		<div class="col-md-9"> ';
content +='			<div class="input-group"> ';
content +='				<textarea style="z-index:100000" class="form-control m-l-15" value="" id="epre_parcours_information_' + champ + '['+numparc+'][' + id + ']" name="epre_parcours_information_' + champ + '['+numparc+'][' + id + ']"></textarea> ';
content +='			</div> ';
content +='		</div> ';
content +='		<ul class="list-unstyled" id="aff_critere_ul_information_0"';
content +='			<li>Information sur le champ qui seront affichées sur la fiche d\'inscription</li> ';
content +='		</ul> ';
content +='	</div>  ';
																				
																				
content +='				<div class="form-group"><label class="col-sm-3 control-label">Obligatoire?</label>';
content +='					<div class="col-sm-9">';
content +='						<label class="radio-inline">';
content +='							<INPUT type="radio" id="epre_parcours_critere_obligatoire_' + champ + '['+numparc+'][' + id + ']" name="epre_parcours_critere_obligatoire_' + champ + '['+numparc+'][' + id + ']" value="non"> Non';
content +='						</label>';
content +='						<label class="radio-inline">';
content +='							<INPUT type="radio" id="epre_parcours_critere_obligatoire_' + champ + '['+numparc+'][' + id + ']" name="epre_parcours_critere_obligatoire_' + champ + '['+numparc+'][' + id + ']" value="oui"> Oui';
content +='						</label>';
content +='					</div>';
content +='				</div>';

content +='			</div>';
content +='			<div class="modal-footer">';
content +='				<a href="#" class="btn btn-primary" id="fermer_resultat" data-dismiss="modal">Valider</a>';
content +='			</div>';
content +='		</div>';
content +='	</div>';
content +='	</div>';

$('#affichage_modal_' + champ + '\\['+numparc+'\\]').append(content);

	FormPlugins.init();
	
	$("input[id*='epre_parcours_date_butoir_']").datetimepicker({
		format:'d/m/Y',
		lang:'fr',
		timepicker:false,
		minDate:new Date()
		
 });
}

//$('#buttonplus_dotation').click(function (e) {
	//e.preventDefault();
function bouton_ajouter_champ(champ, numparc) {
	$('#div_up_' + champ + '\\['+numparc+'\\]').show();
    //var id = (($("#table_up_dotation").children(). length)+1); //think about it ;)
	//alert(champ+''+numparc);
	var name_rt = $('#table_up_' + champ + '\\['+numparc+'\\] tr:last').attr('id');
	if (name_rt ===undefined) { id=1; } 
	else {	var id = name_rt.replace('tr_' + champ + '['+numparc+']_','');	id++; }
	
	content_type = '<SELECT name="epre_parcours_select_' + champ + '['+numparc+']['+ id +']" id="epre_parcours_select_' + champ + '['+numparc+']['+ id +']" class="form-control input-panel-heading" onchange="Get_Champs_Sup(\''+ champ +'\','+numparc+','+ id +')";>';
	content_type +='<OPTION VALUE="0"> Votre sélection </OPTION>';
	if (champ =='dotation' && numparc >0) {
		<?php 	foreach ($f->dotationpreremplie as $k=>$i) { ?>
		content_type +='<OPTION VALUE="<?php echo $k; ?>"<?php if($f->dotationpreremplie == $k) echo " SELECTED"; else echo "";?>><?php echo $i; ?></OPTION>';
		<?php } ?> 
	}
	else if (champ =='dotation' && numparc ==0) {
		<?php 	foreach ($f->dotationpreremplie_commune as $k=>$i) { ?>
		content_type +='<OPTION VALUE="<?php echo $k; ?>"<?php if($f->dotationpreremplie_commune == $k) echo " SELECTED"; else echo "";?>><?php echo $i; ?></OPTION>';
		<?php } ?> 
	}
	else if (champ =='participation' && numparc >0) {
		<?php 	foreach ($f->participationpreremplie as $k=>$i) { ?>
		content_type +='<OPTION VALUE="<?php echo $k; ?>"<?php if($f->participationpreremplie == $k) echo " SELECTED"; else echo "";?>><?php echo $i; ?></OPTION>';
		<?php } ?> 
	}
	else if (champ =='participation' && numparc ==0) {
		<?php 	foreach ($f->participationpreremplie_commune as $k=>$i) { ?>
		content_type +='<OPTION VALUE="<?php echo $k; ?>"<?php if($f->participationpreremplie_commune == $k) echo " SELECTED"; else echo "";?>><?php echo $i; ?></OPTION>';
		<?php } ?> 
	}
	else if (champ =='questiondiverse' && numparc >0) {
		<?php 	foreach ($f->questiondiversepreremplie as $k=>$i) { ?>
		content_type +='<OPTION VALUE="<?php echo $k; ?>"<?php if($f->questiondiversepreremplie == $k) echo " SELECTED"; else echo "";?>><?php echo $i; ?></OPTION>';
		<?php } ?> 
	}
	else if (champ =='questiondiverse' && numparc ==0) {
		<?php 	foreach ($f->questiondiversepreremplie_commune as $k=>$i) { ?>
		content_type +='<OPTION VALUE="<?php echo $k; ?>"<?php if($f->questiondiversepreremplie_commune == $k) echo " SELECTED"; else echo "";?>><?php echo $i; ?></OPTION>';
		<?php } ?> 
	}
	
	content_type +='<OPTION VALUE="-1"> Personnalisée </OPTION>';
	content_type +='</SELECT>';


	
    var TrId = 'tr_' + champ + '['+numparc+']_' + id;
		
	content = '<td id="td_ordre_' + champ + '_'+numparc+'_'+ id +'"> <input size="1" type="text" value="'+ id +'" name="epre_parcours_input_ordre_' + champ + '['+numparc+']['+ id +']" id="epre_parcours_input_ordre_' + champ + '['+numparc+']['+ id +']"> </td>';
	content += '<td id="td_nom_' + champ + '_'+numparc+'_'+ id +'"> <i>A définir</i></td><td>' + content_type + '</td><td id="td_action_' + champ + '['+numparc+']_'+ id +'"><a href="javascript:;" onclick="bouton_supprimer_champ(\''+ champ +'\','+ numparc +',' + id + ');"<i class="fa fa-2x fa-times"></i></a></td>';
	$('#table_up_' + champ + '\\['+numparc+'\\]').append('<tr id="' + TrId + '">' + content + '</tr>');
	
	//on compte le nb de champ pour l'insertion dans la base
	$('#epre_parc_nbchamps' + champ + '\\['+numparc+'\\]').val($("tr[id*='tr_" + champ + "\\["+numparc+"\\]_']").length);
}


function voir_exemple_champ(numparc) {
	
	//nb_champ = $('#epre_parcours_nom_dotation\\[' + numparc + '\\]\\[\\]').length;
	
	
	
	//alert("champ" + nb);
	$('#exemple_dotation\\['+ numparc +'\\]').empty();
	$('#exemple_participation\\['+ numparc +'\\]').empty();
	$('#exemple_questiondiverse\\['+ numparc +'\\]').empty();
	$('#exemple_champs_param\\['+ numparc +'\\]').empty();
	

	var content = '';
	var etoile = '';	
	
	var champ = ["dotation", "participation", "questiondiverse" ];
	for(var nb= 0; nb < champ.length; nb++)
	{
     var content = '';
	 nb_type_champ = $("input[id*='epre_parcours_nom_"+champ[nb]+"\\[" +numparc +"\\]']").length;
		
		for(var nb_champ=1;nb_champ<=nb_type_champ;nb_champ++)
		{
		
			if ($('#epre_parcours_nom_'+champ[nb]+'\\[' + numparc + '\\]\\[' + nb_champ + '\\]').val() != '') 
			{
			
				obligatoire = $('input[type=radio][id=epre_parcours_critere_obligatoire_'+champ[nb]+'\\[' + numparc + '\\]\\[' + nb_champ + '\\]]:checked').attr('value');
			
				if (champ[nb] != 'participation') {
					type_champ = $('#epre_parcours_type_champ_'+champ[nb]+'\\[' + numparc + '\\]\\[' + nb_champ + '\\]').val();
					//alert($('#epre_parcours_type_champ_dotation\\[1\\]\\[1\\]').val());
					//alert("bloucle : " + nb_champ + " - " + type_champ + " - " + champ[nb]);
					critere = $('#epre_parcours_critere_'+champ[nb]+'\\[' + numparc + '\\]\\[' + nb_champ + '\\]').val();
					//alert(critere);
					//$('input:radio[id=epre_parcours_critere_obligatoire_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]]').filter('[value=non]').attr('checked', true);
					//alert(obligatoire);
					//$('input[type=radio][name=#epre_parcours_critere_obligatoire_dotation\\[' + numparc + '\\]\\[1\\]]:checked').attr('value');
					//XS(100);S(50);L;XL(200)
					var criteres = critere.split(';');
				}
				else
				{
				
					type_champ = 'INPUT';
				}
		
				//if ($("#chk1").is(":checked")){
				if (obligatoire == 'oui') { etoile = '*'; }
				//alert('epre_parcours_critere_dotation\\[' + numparc + '\\]\\[1\\]');
				//alert($('#epre_parcours_critere_dotation\\[' + numparc + '\\]\\[1\\]').val());
				//alert($('#epre_parcours_type_champ_dotation\\[' + numparc + '\\]\\[1\\]').val());
				//epre_parcours_label_dotation
				//epre_parcours_critere_obligatoire_dotation
				if (type_champ == 'INPUT') 
				{
				content += '<div class="form-group">';
				content += '	<label class="col-md-6 control-label">' + $('#epre_parcours_label_'+champ[nb]+'\\[' + numparc + '\\]\\[' + nb_champ + '\\]').val() + " " + etoile + '</label>';

				
				content += '	<div class="col-md-6">';
				if (champ[nb] == 'participation') { 
					
					content += '<input type="text" size="3">';
					content += ' ('+$('#epre_parcours_prix_'+champ[nb]+'\\[' + numparc + '\\]\\[' + nb_champ + '\\]').val() + ' € / unité)';
					content += ' <ul class="list-unstyled">'; 
					content += ' <li>Indiquez le nombre de quantité souhaité </li>';
					content += '</ul>';
				}	
				else
				{
					content += '<input type="text">';
				}
				
				content += '	</div>';
				content += '</div>';
						
				}
			
				if (type_champ == 'TEXTAREA') 
				{
					content += '<div class="form-group">';
					content += '	<label class="col-md-6 control-label">' + $('#epre_parcours_label_'+champ[nb]+'\\[' + numparc + '\\]\\[' + nb_champ + '\\]').val() + " " + etoile + '</label>';
					content += '	<div class="col-md-6">';
					content += '		<TEXTAREA></TEXTAREA>';
					content += '	</div>';
					content += '</div>';
							
				}
				
				if (type_champ == 'SELECT') 
				{
					//alert('select');
					content += '<div class="form-group">';
					content += '	<label class="col-md-6 control-label">' + $('#epre_parcours_label_'+champ[nb]+'\\[' + numparc + '\\]\\[' + nb_champ + '\\]').val() + " " + etoile + '</label>';
					content += '	<div class="col-md-6">';
					content += '		<select>';
					for(var i=0;i<criteres .length;i++)
					{
						var champ_critere=criteres[i].replace(/\(.[^(]*\)/g,'');
						content += '<option>' + champ_critere + '</option>';
					}
					content += '</select>';
			
					content += '	</div>';
					content += '</div>';
							
				}
				if (type_champ == 'CASE') 
				{
					content += '<div class="form-group">';
					content += '	<label class="col-md-6 control-label">' + $('#epre_parcours_label_'+champ[nb]+'\\[' + numparc + '\\]\\[' + nb_champ + '\\]').val() + " " + etoile + '</label>';
					content += '	<div class="col-md-6">';
					for(var i=0;i<criteres .length;i++)
					{
						var champ_critere=criteres[i].replace(/\(.[^(]*\)/g,'');
						content += '<input type="checkbox">  ' + champ_critere + '</br>';
					}
			
					content += '	</div>';
					content += '</div>';
							
				}
				if (type_champ == 'RADIO') 
				{
					content += '<div class="form-group">';
					content += '	<label class="col-md-6 control-label">' + $('#epre_parcours_label_'+champ[nb]+'\\[' + numparc + '\\]\\[' + nb_champ + '\\]').val() + " " + etoile + '</label>';
					content += '	<div class="col-md-6">';
					for(var i=0;i<criteres .length;i++)
					{
						var champ_critere=criteres[i].replace(/\(.[^(]*\)/g,'');
						content += '<input type="radio" name="unique">  ' + champ_critere + '</br>';
					}
			
					content += '	</div>';
					content += '</div>';
							
				}
				if (type_champ == 'FILE') 
				{
				content += '<div class="form-group">';
				content += '	<label class="col-md-6 control-label">' + $('#epre_parcours_label_'+champ[nb]+'\\[' + numparc + '\\]\\[' + nb_champ + '\\]').val() + " " + etoile + '</label>';

				
					content += '<input type="file">';
					content += ' <ul class="list-unstyled">'; 
					content += ' <li>(.jpg|.png|.gif|.pdf) uniquement</li>';
					content += '</ul>';

				
				content += '	</div>';
				content += '</div>';
						
				}
			}
		}
	//alert(content);
	$('#exemple_champs_param\\['+ numparc +'\\]').append(content);
	}
	
	
	$('#affichage_modal_exemple\\[' + numparc + '\\]').modal({
		open: true,
		modal: true,
	});

}


function bouton_supprimer_champ(champ,numparc,id) {

	var TrId = '#tr_' + champ + '\\['+numparc+'\\]_' + (id);
	$('#epre_parcours_' + champ + '_modal\\['+numparc+'\\]\\[' + (id) + '\\]').remove();
	$(TrId).remove();
	//alert($("tr[id*='tr_dotation']").length);
	
	//on masque le dessous si plus de champs 
	var name_rt = $('#table_up_' + champ + '\\['+numparc+'\\] tr:last').attr('id');
	
	if (name_rt ===undefined) { $('#div_up_' + champ + '\\['+numparc+'\\]').hide();  $('#epre_parc_nbchamps' + champ + '\\['+numparc+'\\]').val(0)} 
	
	//on compte le nb de champ pour l'insertion dans la base
	$('#epre_parc_nbchamps' + champ + '\\['+numparc+'\\]').val($("tr[id*='tr_" + champ + "\\["+numparc+"\\]_']").length);
}

function editer_fiche_champ(champ,numparc,id) {

	//alert('#epre_parcours_dotation_modal\\['+numparc+'\\]\\[' + id + '\\]');
	$('#epre_parcours_' + champ + '_modal\\['+numparc+'\\]\\[' + id + '\\]').modal({
		open: true,
		modal: true,
	});
}

function affichage_critere_ou_pas(valeur,champ,id_parcours,id) {
	
	
	if (valeur == "TEXTAREA" || valeur == "INPUT" || valeur == "FILE") { 
		
		$('#aff_critere_' + champ + '_'+ id_parcours +'_'+ id).hide();
		$('#aff_unite_' + champ + '_'+ id_parcours +'_'+ id).hide(); 		
		if (valeur=='FILE')
		{
			$('#aff_unite_' + champ + '_'+ id_parcours +'_'+ id).show(); 
			
		}
		
		}
	else if (valeur == "RADIO" || valeur == "CASE") { 
		
		$('#aff_critere_ul_' + champ + '_'+ id_parcours +'_'+ id).hide(); 
		$('#aff_critere_ul_radio_case_' + champ + '_'+ id_parcours +'_'+ id).show();
		$('#aff_unite_' + champ + '_'+ id_parcours +'_'+ id).hide(); 
		
		}
	else
	{
		$('#aff_critere_' + champ + '_'+ id_parcours +'_'+ id).show();
		$('#aff_critere_ul_' + champ + '_'+ id_parcours +'_'+ id).show();
		$('#aff_critere_ul_radio_case_' + champ + '_'+ id_parcours +'_'+ id).hide();
		$('#aff_unite_' + champ + '_'+ id_parcours +'_'+ id).hide(); 
	}
}

function Get_Champs_Sup(champ,numparc,id) {
    //alert(champ+numparc+id);
	
	//alert($('#epre_parcours_dotation_modal\\['+numparc+'\\]\\[' + id + '\\]').length);
	//si le modal n'existe pas, on le crée
	if ($('#epre_parcours_'+ champ +'_modal\\['+numparc+'\\]\\[' + id + '\\]').length == 0 ) {
		Create_Modal(champ,numparc,id,0);
	//alert($('#epre_parcours_type_champ_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]').val());
	//ajout mode edition
	var td_action_html = $('#td_action_'+ champ +'\\['+numparc+'\\]_'+ id).html();
	var add_mode_edition = "<a href='javascript:;' onclick=\"editer_fiche_champ('"+ champ +"',"+ numparc +"," + id + ");\"<i class='fa fa-2x fa-wrench m-r-5'></i></a>";
	$('#td_action_'+ champ +'\\['+numparc+'\\]_'+ id).html(add_mode_edition + td_action_html);
	
	}
	
	//modal personnalisée

	if ($('#epre_parcours_select_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]').val()==-1) {
		
		//$('#epre_parcours_dotation_modal').show();
		
		
		$('#epre_parcours_nom_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]').val('');
		$('#td_nom_dotation_'+numparc+'_' + id ).html('<i>A définir</i>');
		$('#epre_parcours_label_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]').val('');
		$('#epre_parcours_critere_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]').val('');
		if (champ != 'participation') $('#epre_parcours_type_champ_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]').val('SELECT');
		$('input:radio[id=epre_parcours_critere_obligatoire_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]]').filter('[value=non]').attr('checked', true);
		$('#epre_parcours_date_butoir_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]').val($('#date_timepicker_end').val());
		
	
		//$('#epre_parcours_dotation_modal\\[' + id + '\\]').dialog({modal:true,autoOpen:false}); //init dialog
		$('#epre_parcours_'+ champ +'_modal\\['+numparc+'\\]\\[' + id + '\\]').modal({
			open: true,
			modal: true,
		});
	
	}
	else if ($('#epre_parcours_select_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]').val()==0)
	{
		//suppression du mode edition
		$('#td_action_'+ champ +'\\['+numparc+'\\]_'+ id).html('<a href="javascript:;" onclick="bouton_supprimer_champ(\''+ champ +'\', '+ numparc +',' + id + ');"<i class="fa fa-2x fa-times"></i></a>');		
		
		$('#epre_parcours_nom_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]').val('');
		//suppression du modal
		$('#epre_parcours_'+ champ +'_modal\\['+numparc+'\\]\\[' + (id) + '\\]').remove();
		$('#td_nom_'+ champ +'_'+numparc+'_' + id ).html('<i>A définir</i>');
	}
	
	else {
		
	
		//affichage_modal;
		//$('#epre_parcours_dotation_modal').show();
		var targetUrl = 'includes/ajaxChampsSup.php?type='+ champ + '&numparc=' + numparc;

		$.post(targetUrl, $('#epre_parcours_select_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]').serialize()).done(function(data) {
		var dataObj = JSON.parse(data);
		//alert(dataObj.nom);
		$('#epre_parcours_nom_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]').val(dataObj.nom);
		$('#td_nom_'+ champ +'_'+numparc+'_' + id ).html(dataObj.nom);
		$('#epre_parcours_label_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]').val(dataObj.label);
		$('#epre_parcours_critere_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]').val(dataObj.critere);
		if (champ != 'participation')  {
			$('#epre_parcours_type_champ_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]').val(dataObj.type_champ);
		}
		else
		{
			$('input:radio[id=epre_parcours_type_champ_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]]').filter('[value=' + dataObj.type_champ +']').attr('checked', true);
		}
		$('input:radio[id=epre_parcours_critere_obligatoire_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]]').filter('[value=' + dataObj.obligatoire +']').attr('checked', true);
		$('#epre_parcours_date_butoir_'+ champ +'\\['+numparc+'\\]\\[' + id + '\\]').val($('#date_timepicker_end').val());
		$('#epre_parcours_'+ champ +'_modal\\['+numparc+'\\]\\[' + id + '\\]').modal({
			open: true,
			modal: true,
		});
		});
	}
}

//********************** FONCTION MODAL POUR CHAMP SUPPLEMENTAIRE ***********************


	//soumission du formulaire
	$('[data-parsley-validate="true"]').on('submit', function(e) {
		
	
    e.preventDefault();
	$('#info_enregistrement_en_cours').show();
    var targetUrl = $(this).attr('action');
	//alert(targetUrl);
	scriptCharset: "iso-8859-1",
	
    $.post(targetUrl, $(this).serialize()).success(function(data) {
        // if json format data returned
        var returnData = jQuery.parseJSON(data);
                /*$( "#progressbar" ).progressbar({
                    value: 100
                });*/
				
		//console.log(returnData.comeback);
		//$('#info_enregistrement').show();
		if (returnData.comeback =='OK') {
			//console.log(data);
			$('#info_enregistrement_en_cours').hide();
			$('#info_enregistrement').show();
			location.reload();
		}

		url_return = 'creation_epreuve.php?epre_id='+data;
		
		$("#fermer_resultat_final").attr("href", url_return);
		
		$("#resultat").modal({
        open: true,
        modal: true,
		keyboard: false
		
		//buttons: { "Accept": function() {window.location.reload(true);window.location.href = url_return;$(this).dialog("close");}}
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
	
	//alert('num_parc_actuel : '+num_parc+' - Num parc -1 '+$('#id_precedent\\['+ num_parc +'\\]').val());
	var cpt=1;
	var num=new Array();
	var copie_parc;
	$( ".nav-tabs li" ).each(function( index ) {
		num[cpt] = $(this).attr('id');
		num[cpt] = num[cpt].replace("P", "");
		//alert(num[cpt]);
		
		if (num[cpt] == num_parc) {
			cpt = cpt - 1;
			copie_parc = num[cpt];
		return false;
		}
		
		//alert( index + ": " + num );
		cpt++;
	});
	//alert (copie_parc);

	//var copie_parc = (num_parc -1);
	//var copie_parc = $('#id_precedent\\['+ num_parc +'\\]').val();
	var nb_tr = $("tr[id*='tr_"+ champ +"\\[" + copie_parc + "\\]']").length;

	if ($('#div_up_'+ champ +'\\['+ copie_parc +'\\]').css('display') != 'none') 
	{
		$('#div_up_'+ champ +'\\['+ num_parc +'\\]').show();

		var content = '';
//	content = '<td id="td_ordre_' + champ + '_'+numparc+'_'+ id +'"> <input size="1" type="text" value="'+ id +'" name="epre_parcours_input_ordre_' + champ + '['+numparc+']['+ id +']" id="epre_parcours_input_ordre_' + champ + '['+numparc+']['+ id +']"> </td>';
	
		for (id=1;id<=nb_tr;id++)
		{
			
			content += '			<tr id="tr_'+ champ +'['+ num_parc +']_' + id + '">';
			content += '				<td id="td_ordre_'+ champ +'_'+ num_parc +'_' + id + '"><input size="1" type="text" value="'+ id +'" name="epre_parcours_input_ordre_' + champ + '['+ num_parc +']['+ id +']" id="epre_parcours_input_ordre_' + champ + '['+ num_parc +']['+ id +']"> </td>';
			content += '				<td id="td_nom_'+ champ +'_'+ num_parc +'_' + id + '">';
			content += $('#td_nom_'+ champ +'_'+ copie_parc +'_' + id).html()+'</td>';
			content += '				<td id="td_select_'+ champ +'_'+ num_parc +'_' + id + '">';
			//content += ddl;
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
			//$('td#td_select_'+ champ +'_'+ num_parc +'_' + id + ' #epre_parcours_input_ordre_'+ champ +'\\['+ copie_parc +'\\]\\[' + id + '\\]').attr('id','epre_parcours_input_ordre_'+ champ +'['+ num_parc +'][' + id + ']');
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
			//alert($('input:radio[id=epre_parcours_critere_obligatoire_'+ champ +'\\['+copie_parc+'\\]\\[' + id + '\\]]:checked').val());
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
 


	
	
$(document).ready(function() {
	App.init();
	//TreeView.init();
	FormWizardValidation.init();

	FormPlugins.init();

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

function url_redirect_new(id_epreuve,id_parcours) {
		
		url = 'creation_champs.php?file=epre&epre_id='+id_epreuve+'&id_parcours='+id_parcours+'&panel=iframe';
		window.location = url;
}

/*
$("#fichier_epreuve_sup").fileinput({
		uploadUrl:'submit_file.php',
		uploadAsync: false,
		allowedFileExtensions : ['jpg', 'png','gif','pdf'],
        maxFileSize: 2000,
        maxFileCount: 10
	});
*/

	</script>
</body>
</html>
