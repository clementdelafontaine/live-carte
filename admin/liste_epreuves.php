<?php 
//define("URL_DEV", '/dev/');


require_once("includes/includes.php");
require_once("includes/functions.php");	
//echo "log : ". $_SESSION["log_log"]."-".isset($_POST['login']);
require_once("includes/requete_stats_admin.php");
global $mysqli;

if(!isset($_SESSION["log_log"]))
	{ 
	if ($_POST['pass']=='') { header('Location: login_v2.php?act=disconnect'); }	
	if(isset($_POST['login']) && isset($_POST['pass']))
	{
		
		login_internaute ($_POST['login'],$_POST['pass']);
	}else
	{
		header('Location: login_v2.php?act=disconnect'); 
	}	
		//header('Location: login_v2.php');

}


//print_r($_SESSION);
	
 $admin = '';
 if ($_SESSION["typeInternaute"] == 'admin' || $_SESSION["typeInternaute"] == 'super_organisateur') $admin=1;
 
	global $_GET;

	if(isset($_GET['pass'])) $pass_recu = $_GET['pass'];		
	if(isset($_GET['mail'])) $mail_recu = $_GET['mail'];

	$query  = "SELECT e.idEpreuve, e.nomEpreuve, e.dateEpreuve, e.DateFinEpreuve, e.idInternaute, e.contactInscription, e.administrateur, e.emailInscription, e.nbParticipantsAttendus, e.paiement_cb, e.paiement_cheque, e.dateModification, ";
	$query .= "i.idInternaute, i.loginInternaute, i.passInternaute, i.emailInternaute  ";

	$query .= "FROM r_epreuve AS e, r_internaute AS i ";
		
	if($_SESSION["typeInternaute"] != "virtuelorganisateur" )
	{
		if(isset($_SESSION["typeInternaute"]) && $_SESSION["typeInternaute"] == "admin" )
		{
			$query .= "WHERE i.idInternaute = e.idInternaute AND (e.idInternaute = ".$_SESSION["log_id"]." OR e.administrateur = ".$_SESSION["log_id"].") ";
			
		}
		elseif(isset($_SESSION["typeInternaute"]) && $_SESSION["typeInternaute"] == "super_organisateur" )
		{
			
			$query .= "WHERE i.idInternaute = e.idInternaute AND (e.idInternaute = ".$_SESSION["log_id"]." OR e.super_organisateur = ".$_SESSION["log_id"].") ";
		}
		else
		{
			$query .= "WHERE i.idInternaute = e.idInternaute ";
			if (isset($_SESSION["log_id"])) $query .= "AND e.idInternaute = ".$_SESSION["log_id"]." ";
		}
	}
	else
	{
		
		$query .= "WHERE i.idInternaute = e.idInternaute AND e.idEpreuve >= 4000 ";
	}
	if (isset ($_GET['pass']) && isset ($_GET['mail'])){
		$query .= "AND i.emailInternaute ='".$mail_recu."' ";
		$query .= "AND i.passInternaute ='".$pass_recu."' ";
	}
	
	if ($_GET['date']=='encours') {
		$query .=" AND e.DateFinEpreuve >= (NOW() - INTERVAL 5 DAY) AND e.dateDebutInscription <= nOW() ";
	}
	elseif ($_GET['date']=='finie')
	{
		$query .=" AND e.DateFinEpreuve <= NOW() ";
	}
	elseif ($_GET['date']=='avenir')
	{
		$query .=" AND e.DateFinEpreuve  >= NOW() ";
	}
	elseif ($_GET['date']=='365')
	{
		$query .=" AND  (e.dateEpreuve BETWEEN (NOW() - INTERVAL 365 DAY) AND now())";
	}
	//echo $query;
	$query .= "ORDER BY e.idEpreuve DESC";
	$result = $mysqli->query($query);
	$nb_epreuve = mysqli_num_rows($result);

$_SESSION['url_en_cours'] = $_SERVER['REQUEST_URI'];	

	
	
	function tarifs($id_parcours) {		
	global $mysqli;
	$date_et_heure_du_jour = strtotime(date('Y-m-d H:i:s'));
			
			$query  = "SELECT * ";
			$query .= "FROM r_epreuveparcourstarif";
			$query .=" WHERE idEpreuveParcours = ".$id_parcours;
			$result_tarifs = $mysqli->query($query);
			
			while ($row_tarifs=mysqli_fetch_array($result_tarifs))
			{ 
				//echo strtotime($row_tarifs['dateDebutTarif'])." ".$date_et_heure_du_jour." ".strtotime($row_tarifs['dateFinTarif'])." ";
				if ( strtotime($row_tarifs['dateDebutTarif']) < $date_et_heure_du_jour AND $date_et_heure_du_jour < strtotime($row_tarifs['dateFinTarif'])) { 
					$tarif_promo['tarif'] =  $row_tarifs['tarif'];
					$tarif_promo['id_tarif'] =  $row_tarifs['idEpreuveParcoursTarif'] ;
							
				}											
			}			
			
			return $tarif_promo;
	}
	
if ($admin==1) {
	$query_so = '';
	if($_SESSION["typeInternaute"] == "super_organisateur" )
		{
			
			$query_so = " AND re.super_organisateur = ".$_SESSION["log_id"];
		}	
	$query_epreuve_liste ="SELECT re.idEpreuve, re.nomEpreuve, re.DateFinEpreuve, (SELECT count(*) 
							FROM r_inscriptionepreuveinternaute 
							WHERE idEpreuve = re.idEpreuve AND paiement_date IS NOT NULL) nb_inscrit 
							 FROM r_epreuve as re 
							WHERE re.DateFinEpreuve > NOW() 
							".$query_so."
							ORDER BY re.nomEpreuve ASC  ";
	//echo $query_epreuve_liste;
	$result_epreuve_liste  = $mysqli->query($query_epreuve_liste);
	
	$query_remboursement = "SELECT riei.remboursement_type, riei.observation, ri.nomInternaute, ri.prenomInternaute, rire.idInscriptionRemboursement, riei.paiement_type, riei.paiement_date, re.payeur, re.idEpreuve, re.nomEpreuve, rep.idEpreuveParcours,rep.nomParcours, rire.idInscriptionEpreuveInternaute, reference,rire.remboursement, rire.frais_remboursement, rire.remboursement_effectue, rire.remboursement_date FROM r_inscriptionremboursement as rire
		INNER JOIN r_epreuve as re ON rire.idEpreuve = re.idEpreuve
		INNER JOIN r_epreuveparcours as rep ON rire.idEpreuveParcours = rep.idEpreuveParcours
		INNER JOIN r_inscriptionepreuveinternaute as riei ON rire.idInscriptionEpreuveInternaute = riei.idInscriptionEpreuveInternaute
		INNER JOIN r_internaute as ri ON riei.idInternaute = ri.idInternaute
		WHERE rire.remboursement_effectue = 'non'
		".$query_so."
		AND (riei.remboursement_type IS NULL OR riei.remboursement_type = 'COMPLET')
		ORDER BY re.idEpreuve,riei.paiement_date ASC
		";
	//echo $query_remboursement;
	$result_remboursement = $mysqli->query($query_remboursement);
	$nb = mysqli_num_rows($result_remboursement);

	$query_remboursement_partiel = "SELECT riei.remboursement_type, riei.observation, ri.nomInternaute, ri.prenomInternaute, rire.idInscriptionRemboursement, riei.paiement_type, riei.paiement_date, re.payeur, re.idEpreuve, re.nomEpreuve, rep.idEpreuveParcours,rep.nomParcours, rire.idInscriptionEpreuveInternaute, reference,rire.remboursement, rire.frais_remboursement, rire.remboursement_effectue, rire.remboursement_date FROM r_inscriptionremboursement as rire
		INNER JOIN r_epreuve as re ON rire.idEpreuve = re.idEpreuve
		INNER JOIN r_epreuveparcours as rep ON rire.idEpreuveParcours = rep.idEpreuveParcours
		INNER JOIN r_inscriptionepreuveinternaute as riei ON rire.idInscriptionEpreuveInternaute = riei.idInscriptionEpreuveInternaute
		INNER JOIN r_internaute as ri ON riei.idInternaute = ri.idInternaute
		WHERE rire.remboursement_effectue = 'non'
		".$query_so."
		AND riei.remboursement_type = 'PARTIEL'
		ORDER BY re.idEpreuve,riei.paiement_date ASC
		";
	$result_remboursement_partiel = $mysqli->query($query_remboursement_partiel);
	$nb_partiel = mysqli_num_rows($result_remboursement_partiel);
	//echo $query_remboursement_partiel ;
	
	$query_remboursement_effectue = "SELECT riei.remboursement_type, riei.observation, ri.nomInternaute, ri.prenomInternaute, rire.idInscriptionRemboursement, riei.paiement_type, riei.paiement_date, re.payeur, re.idEpreuve, re.nomEpreuve, rep.idEpreuveParcours,rep.nomParcours, rire.idInscriptionEpreuveInternaute, reference,rire.remboursement, rire.frais_remboursement, rire.remboursement_effectue, rire.remboursement_date FROM r_inscriptionremboursement as rire
	INNER JOIN r_epreuve as re ON rire.idEpreuve = re.idEpreuve
	INNER JOIN r_epreuveparcours as rep ON rire.idEpreuveParcours = rep.idEpreuveParcours
	INNER JOIN r_inscriptionepreuveinternaute as riei ON rire.idInscriptionEpreuveInternaute = riei.idInscriptionEpreuveInternaute
	INNER JOIN r_internaute as ri ON riei.idInternaute = ri.idInternaute
	WHERE rire.remboursement_effectue = 'oui'
	".$query_so."
	ORDER BY rire.remboursement_date DESC LIMIT 100
	";
	$result_remboursement_effectue  = $mysqli->query($query_remboursement_effectue );
	$nb_effectue  = mysqli_num_rows($result_remboursement_effectue );
}	


?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<title>Ats Sport | Liste de Mes Epreuves</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<meta content="" name="description" />
	<meta content="" name="author" />
	
	<!-- ================== begin CSS DE BASE INCLUS DANS TOUTES LES PAGES ================== -->
	<?php include ("includes/header_css_js_base.php"); ?>
	<!-- ================== end CSS DE BASE INCLUS DANS TOUTES LES PAGES ================== -->
	
	<!-- ================== begin CSS EN PLUS SELON LES FONCTIONNALITES CHOISIES EN PLUS ================== -->
	
	<!-- ex : <link href="assets/plugins/bootstrap-datepicker/css/datepicker.css" rel="stylesheet" /> /-->
	   		<link rel="stylesheet" href="assets/plugins/jquery-auto-google/jquery.autocomplete.css">
	<!-- ================== end CSS EN PLUS SELON LES FONCTIONNALITES CHOISIES EN PLUS ================== -->

	<!-- ================== begin SCIPTS JS EN PLUS SI BESOIN DE LES METTRE AVANT LE CODE  EN PLUS ================== -->
	
	<!-- ex : <script src="assets/js/form-plugins.demo.js"></script> /-->
	
	<!-- ================== end SCIPTS JS EN PLUS SI BESOIN DE LES METTRE AVANT LE CODE  EN PLUS ================== -->		
</head>
<body>
	<!-- begin #page-loader -->
	<div id="page-loader" class="fade in"><span class="spinner"></span></div>
	<!-- end #page-loader -->
	
	<!-- begin #page-container -->
	 <div id="page-container" class="fade page-sidebar-fixed page-header-fixed gradient-enabled">
	<!--<div id="" class="">  /-->
		<!-- begin #header (Bandeau du haut)-->
		<?php include ("includes/header.php"); ?>
		<!-- end #header (Bandeau du haut)-->
		
		<!-- begin #sidebar (menu) -->
		<?php include ("includes/sidebar.php"); ?>
		<!-- end #sidebar (menu) -->
		
		<!-- begin #content -->
		<div id="content" class="content">
			
			<!-- begin breadcrumb (fil d'ariane)-->
			<ol class="breadcrumb pull-right">
				<li><a href="javascript:;">Accueil</a></li>
				<li class="active">Liste de mes épreuves</li>
			</ol>
			<!-- end breadcrumb (fil d'ariane) -->
			
			<!-- begin page-header -->
			<h1 class="page-header">LISTE DE MES EPREUVES <small> <?php echo $nb_epreuve; ?> épreuves sélectionnées</small></h1>
			<!-- end page-header -->
			
			<!-- begin row -->
			<div class="row">
			    <!-- begin col-12 -->

				
			<?php if ($admin==1) { ?>	
				<div class="col-md-9">				
					<div class="panel panel-inverse">
											<div class="panel-heading">
												<h4 class="panel-title">Accès rapide épreuves (ADMIN)</h4>
											</div>
											<div class="panel-body">
												<SELECT class="form-control input-inline input-sm" name="select_epreuve" id="select_epreuve" onchange="url_redirect_new(this.value);">
													<OPTION VALUE="0;0"> Choisir ... </OPTION> <?php
													while (($row_epreuve_liste=mysqli_fetch_array($result_epreuve_liste)) != FALSE)
													{ 
														
													?>
														
														<OPTION VALUE="<?php echo $row_epreuve_liste['idEpreuve']; ?>" <?php if ($_GET['id_epreuve'] == $row_epreuve_liste['idEpreuve']) echo "selected"; ?>><?php echo "<span class='text-danger'>".stripslashes(htmlspecialchars($row_epreuve_liste['nomEpreuve'], ENT_QUOTES))."</span> ( ".$row_epreuve_liste['idEpreuve']." ) [ Date epreuve : ".dateen2fr($row_epreuve_liste['DateFinEpreuve'],1)." ] [ Inscrits: ".$row_epreuve_liste['nb_inscrit']." ]" ; ?> </OPTION>
													
													<?php } ?>
												</SELECT>
											</div>
					</div>
				</div>
				<div class="col-md-3">	
					
					<div class="panel panel-danger">
											<div class="panel-heading">
												<h4 class="panel-title">Actions rapides (ADMIN) </h4>
											</div>
											<div class="panel-body">
												<a data-toggle="modal" class="btn btn-xs btn-success" href="#remboursement_effectue"><i data-toggle="tooltip" data-placement="top" data-original-title="Demandes de remboursement effectué" class="fa fa-2x fa-money"></i></a>	
												<a data-toggle="modal" class="btn btn-xs btn-danger" href="#remboursement_a_faire"><i data-toggle="tooltip" data-placement="top" data-original-title="Demandes de remboursement à effectuer" class="fa fa-2x fa-money"></i></a>	
												<a data-toggle="modal" class="btn btn-xs btn-warning" href="#remboursement_a_faire_partiel"><i data-toggle="tooltip" data-placement="top" data-original-title="Demandes de remboursement partiel à effectuer" class="fa fa-2x fa-money"></i></a>	
												<a data-toggle="modal" class="btn btn-xs btn-info" href="#stats_admin"><i data-toggle="tooltip" data-placement="top" data-original-title="Statistiques ats-sport" class="fa fa-2x fa-bar-chart-o"></i></a>
												<a data-toggle="tooltip" class="btn btn-xs btn-info" href="newsletter.php" data-original-title="Newsletter"><i class="fa fa-2x fa-envelope"></i></a>
											</div>
					</div>
				</div>	
			<?php } ?>
				<div class="col-md-12">
					<div class="panel panel-inverse">
											<div class="panel-heading">
												<h4 class="panel-title">Recherche rapide d'un participant</h4>
											</div>
											<div class="panel-body">
								<input placeholder="Entrer son nom et/ou prénom" size="50" type="text" class="form-control input-inline input-sm" name="insc_nom[1]" value="" id="insc_nom_1" maxlength="150">
							<img id="loading" style="display:none" src="https://www.exitcertified.com/commonFiles/images/loadingIndicator_2.gif" />
											</div>
					</div>
				</div>

			    <div class="col-md-12">
			        <!-- begin panel -->
                    <div class="panel panel-inverse" data-sortable-id="table-basic-7">
                        <div class="panel-heading">
                            <div class="panel-heading-btn">
                                <!--<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"><i class="fa fa-repeat"></i></a>
                                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
                                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-danger" data-click="panel-remove"><i class="fa fa-times"></i></a> /-->
                            </div>
                            <h4 class="panel-title">Epreuves</h4>
                        </div>
                        <div class="panel-body">
							<div class="table-responsive">
								<table class="table table-striped table-bordered">
									<thead>
										<tr>
											<th>#</th>
											<th>Nom de l'épreuve</th>
											<th>Vérifications</th>
											<th class="text-center">Date de départ</th>
											<th class="text-center">Date de fin</th>
											<th class="text-center">Action</th>
										</tr>
									</thead>
									<tbody>
									<?php $first = TRUE;
									$cpt=1;
											while (($row=mysqli_fetch_array($result)) != FALSE){
												$unique_edition_epreuve_id = md5(uniqid());
												$query_update_certificat_fichier = type_epreuve($row['idEpreuve']);
												$nb_certif_a_verifier = nb_inscrit_certif_a_valide($row['idEpreuve'],$id_parcours,$query_update_certificat_fichier['type_nom_bdd'],'tous'); 
												$nb_auto_parentale_a_verifier = nb_inscrit_paiement_valide_autoparentale_a_valider($row['idEpreuve'],$id_parcours,'tous'); 
												
												if ((isset ($_GET['pass']) && isset ($_GET['mail'])) || isset ($_SESSION["log_id"])){
												$query_p ='SELECT idEpreuveParcours, nomParcours, relais FROM r_epreuveparcours WHERE idEpreuve = '.$row['idEpreuve'];
												$result_p = $mysqli->query($query_p);
												$qui_paye = extract_champ_epreuve ('payeur', $row['idEpreuve']);

												?>
												<tr id="tr_<?php echo $row['idEpreuve'] ?>">
													<td><?php echo $row['idEpreuve'] ?></td>
													<td><strong><?php echo stripslashes_bdd_to_scr($row['nomEpreuve']); ?></strong> [ <?php echo $row['nbParticipantsAttendus']; ?> Participants max. ]<?php if (!empty($qui_paye)) { ?></br>Frais d'inscription à la charge du/de : <i><?php echo $qui_paye; ?></i><?php } ?>
													</br> email organisateur : <?php echo $row['emailInscription']; ?>
													</br> Type de paiement : <?php if ($row['paiement_cb']==0) { echo "<span class='text-danger'><strong>Epreuve non ouverte</strong></span>"; } else { if ($row['paiement_cb']==1) echo "CB" ; if ($row['paiement_cheque']==1) echo " / CHEQUE"; }?>
														<?php if (!empty($admin) && $_GET['date'] != 'finie' && $_GET['date'] != '365'  ) { ?>
														</br><input value="(admin : voir modif fiche épreuve)" type="button" class="btn btn-success btn-xs" onclick="show_modif_orga(<?php echo $row['idEpreuve'] ?>,<?php echo $row['idInternaute'] ?>,<?php echo $row['dateModification'] ?>)">

													<?php } ?>												
</br>
													</br>
													<?php if ($admin==1) { ?><div class="alert alert-danger fade in m-b-15"<?php if (check_champs_separateur($row['idEpreuve'])) echo 'style="display:none"'; echo 'style="display:visible"'; ?>>	<strong>ERREUR !</strong> Il manque l'épreuve <?php echo $row['idEpreuve'];?>	dans la table <b>r_insc_champ_separator</b> - contacter le <a href="mailto:webmaster@ats-sport.com">webmaster@ats-sport.com</a> <span class="close" data-dismiss="alert">×</span></div><?php } ?>
			
													</td>
													<td>
														<div class="widget-small widget-stats-small bg-blue pull-right" data-original-title="Vert : Vérifié / Orange : A vérifier / Rouge : Absente" data-placement="top" data-toggle="tooltip">
															<div class="stats-icon stats-icon-lg"><i class="fa fa-file-pdf-o fa-fw"></i></div>
															<div class="stats-title text-center"><strong>AUTO. PARENTALE</strong></div>
															
															<div class="stats-number label label-success"><?php echo nb_inscrit_total($row['idEpreuve'],$id_parcours,'tous','non','oui'); ?></div>							
															
															<?php $nb_auto_parentale_a_verifier = nb_inscrit_paiement_valide_autoparentale_a_valider($row['idEpreuve'],$id_parcours,'tous'); 
															if ($nb_auto_parentale_a_verifier !=0)
															{ ?>
																<a href="javascript:;" onclick="affiche_modal_v2('',<?php echo $row['idEpreuve']; ?>,'autoparentale.php?id_epreuve=<?php echo $row['idEpreuve']; if (!empty($id_parcours)) echo '&id_parcours='.$id_parcours;?>',900,1000)"><div class="stats-number label label-warning" style="color:#000;font-weight: bold"><?php echo $nb_auto_parentale_a_verifier; ?></div></a>
																<!-- <a href="autoparentale.php?id_epreuve=<?php echo $row['idEpreuve']; if (!empty($id_parcours)) echo '&id_parcours='.$id_parcours;?>"><div class="stats-number label label-warning" style="color:#000;font-weight: bold"><?php echo $nb_auto_parentale_a_verifier; ?></div></a> /-->
															<?php } else { ?>
																<div class="stats-number label label-warning"><?php echo $nb_auto_parentale_a_verifier; ?></div>
															<?php } ?>							
															
															<div class="stats-number label label-danger"><?php echo nb_inscrit_paiement_valide_autoparentale_absent($row['idEpreuve'],$id_parcours,'tous'); ?></div>
															
														</div>
								
								
													
								
													
														<div class="widget-small widget-stats-small bg-blue pull-right" data-original-title="Vert :  Vérifié / Orange : A vérifier / Rouge : Absent" data-placement="top" data-toggle="tooltip">
															<div class="stats-icon stats-icon-lg"><i class="fa fa-file-pdf-o fa-fw"></i></div>
															<div class="stats-title text-center"><strong>CERTIFICATS</strong></div>
															<div class="stats-number label label-success" ><?php echo nb_inscrit_total($row['idEpreuve'],$id_parcours,'tous','oui','non'); ?></div>
															<?php $nb_certif_a_verifier = nb_inscrit_certif_a_valide($row['idEpreuve'],$id_parcours,$query_update_certificat_fichier['type_nom_bdd'],'tous'); 
															if ($nb_certif_a_verifier !=0)
															{ ?>
																<a href="javascript:;" onclick="affiche_modal_v2('',<?php echo $row['idEpreuve']; ?>,'certif.php?id_epreuve=<?php echo $row['idEpreuve']; if (!empty($id_parcours)) echo '&id_parcours='.$id_parcours;?>',900,1000)"><div class="stats-number label label-warning" style="font-weight: bold"><?php echo $nb_certif_a_verifier; ?></div></a>
																<!-- <a href="certif.php?id_epreuve=<?php echo $row['idEpreuve']; if (!empty($id_parcours)) echo '&id_parcours='.$id_parcours;?>"><div class="stats-number label label-warning" style="font-weight: bold"><?php echo $nb_certif_a_verifier; ?></div></a> /-->
															<?php } else { ?>
																<div class="stats-number label label-warning" ><?php echo $nb_certif_a_verifier; ?></div>
															<?php } ?>
															<div class="stats-number label label-danger" ><?php echo nb_inscrit_paiement_valide_certif_absent($row['idEpreuve'],$id_parcours,$query_update_certificat_fichier['type_nom_bdd'],'tous'); ?></div>
								
														</div>



														<div class="widget-small widget-stats-small bg-blue pull-right" data-original-title="Vert :  Vérifié / Rouge : non vérifié / Noir : Total inscrit paiement OK" data-placement="top" data-toggle="tooltip">
															<div class="stats-icon stats-icon-lg"><i class="fa fa-users fa-fw"></i></div>
															<div class="stats-title text-center"><strong>INSCRITS</strong></div>
															<a id="I_DC" href="liste_des_inscrits_v2.php?id_epreuve=<?php echo $row['idEpreuve']; if (!empty($id_parcours)) echo '&id_parcours='.$id_parcours;?>&jours=dc"><div class="stats-number label label-success" ><?php echo $nb_inscrit_total = nb_inscrit_paiement_valide($row['idEpreuve'],$id_parcours,'"CB","CHQ","GRATUIT","AUTRE"','dc'); ?></div></a>
															<?php $nb_internaute_a_verifier = nb_inscrit_paiement_valide($row['idEpreuve'],$id_parcours,'"CB","CHQ","GRATUIT","AUTRE"','dci'); 
															if ($nb_internaute_a_verifier !=0)
															{ ?>
																<a href="liste_des_inscrits_v2.php?id_epreuve=<?php echo $row['idEpreuve']; if (!empty($id_parcours)) echo '&id_parcours='.$id_parcours;?>&jours=dci"><div class="stats-number label label-danger" style="font-weight: bold"><?php echo $nb_internaute_a_verifier; ?></div></a>
															<?php } ?>
															<div class="stats-number label label-inverse" ><?php echo nb_inscrit_total($row['idEpreuve'],$id_parcours,'tous','non','non'); ?></div>
								
														</div>												
													
													</td>
													<td class="text-center"><?php echo utf8_decode(date('d/m/Y',strtotime($row['dateEpreuve']))); ?></td>
													<td class="text-center"><?php echo utf8_decode(date('d/m/Y',strtotime($row['DateFinEpreuve']))); ?></td>
													<td class="text-center"><a data-toggle="tooltip" data-placement="top" data-original-title="Modifier l'épreuve" href="#" onclick="form_epre_submit(<?php echo $row['idEpreuve'] ?>, '<?php echo $unique_edition_epreuve_id; ?>');"><i class="fa fa-2x fa-wrench m-r-10"></a></i>
													<!-- <a data-toggle="tooltip" data-placement="top" data-original-title="Liste des inscrits" href="javascript:;" onclick="affiche_modal('Liste des inscrits  ', <?php echo $row['idEpreuve'] ?>,'http://www.ats-sport.com/<?php echo URL_DEV; ?>liste_des_inscrits.php?id_epreuve=<?php echo $row['idEpreuve']; ?>&panel=iframe')"><i class="fa fa-2x fa-bars m-r-10"></a></i>  /-->
													<?php if (!empty($admin)) { ?>
													<a data-toggle="tooltip" data-placement="top" data-original-title="Gestion des utilisateurs" href="utilisateur.php?id_epreuve=<?php echo $row['idEpreuve'] ?>" ><i class="fa fa-2x fa-user m-r-10"></i></a>
													<?php } ?>
													<a data-toggle="tooltip" data-placement="top" data-original-title="Gestion des inscriptions" href="liste_des_inscrits_v2.php?id_epreuve=<?php echo $row['idEpreuve'] ?>&jours=tous" ><i class="fa fa-2x fa-users m-r-10"></i></a>
													<a data-toggle="tooltip" data-placement="top" data-original-title="Synthèse Champs Dynamiques" href="javascript:;" onclick="affiche_modal('Synthese champs Dynamique ',<?php echo $row['idEpreuve'] ?>,'https://www.ats-sport.com/<?php echo URL_DEV; ?>/admin/resume_champs.php?id_epreuve=<?php echo $row['idEpreuve']; ?>&panel=iframe')"><i class="fa fa-2x fa-list m-r-10"></a></i>
													<a data-toggle="tooltip" data-placement="top" data-original-title="Voir la fiche épreuve" href="../epreuve.php?id_epreuve=<?php echo $row['idEpreuve']; ?>" target="_blank"><i class="fa fa-2x fa-search-plus m-r-10"></a></i>
													<?php if (!empty($admin) || $_SESSION['typeInternaute'] == 'super_organisateur' ) { ?>
														<a data-toggle="tooltip" data-placement="top" data-original-title="Exporter en XLS - Fichier chrono" href="export_chrono.php?id_epreuve=<?php echo $row['idEpreuve']; ?>"><i class="fa fa-2x fa-file-text m-r-10"></i></a>
													<?php } ?>
													<a data-toggle="tooltip" data-placement="top" data-original-title="Exporter en XLS - Liste des engagés" href="export_liste_engages.php?id_epreuve=<?php echo $row['idEpreuve']; ?>"><i class="fa fa-2x fa-file-text-o m-r-10"></i></a>
													<a data-toggle="tooltip" data-placement="top" data-original-title="Exporter en XLS - Retrait des dossards" href="../retrait_des_dossards.php?id_epreuve=<?php echo $row['idEpreuve']; ?>"><i class="fa fa-2x fa-file-text-o m-r-10"></i></a>
													<a data-toggle="tooltip" data-placement="top" data-original-title="Exporter en XLS - Fichier inscriptions" href="export_epreuve.php?id_epreuve=<?php echo $row['idEpreuve']; ?>"><i class="fa fa-2x fa-file-excel-o m-r-10"></i></a>
													<a data-toggle="tooltip" data-placement="top" data-original-title="Exporter en XLS - Fichier Comptabilité" href="export_epreuve.php?id_epreuve=<?php echo $row['idEpreuve']; ?>&type=compta"><i class="fa fa-2x fa-eur m-r-10"></i></a>
													
													<a data-toggle="tooltip" data-placement="top" data-original-title="Statistiques" href="statistiques.php?id_epreuve=<?php echo $row['idEpreuve']; ?>"><i class="fa fa-2x fa-bar-chart-o"></i></a>

													<?php if (!empty($admin) || $_SESSION['typeInternaute'] == 'super_organisateur' ) { ?>
														<a data-toggle="tooltip" data-placement="top" data-original-title="Résultats de l'épreuve" href="resultats.php?id_epreuve=<?php echo $row['idEpreuve']; ?>" ><i class="fa fa-2x fa-trophy m-r-10"></i></a>
													<?php } ?>	
													<?php if (!empty($admin)) { ?>
														<a data-toggle="tooltip" data-placement="top" data-original-title="Exporter en SQL" href="export_epreuve_SQL.php?id_epreuve=<?php echo $row['idEpreuve']; ?>"><i class="fa fa-2x fa-file-archive-o"></i></a>
													   	<a data-toggle="tooltip" data-placement="top" data-original-title="Copier" href="copie_epreuve.php?id_epreuve=<?php echo $row['idEpreuve']; ?>" ><i class="fa fa-2x fa-copy"></i></a>
													    <a data-toggle="tooltip" data-placement="top" data-original-title="Supprimer" href="javascript:;" onclick="modif_epreuve(<?php echo $row['idEpreuve'] ?>,'<?php echo $row['nomEpreuve']?>')"><i class="fa fa-2x fa-times"></i></a>
														
													<?php } ?>
														<a data-toggle="tooltip" data-placement="top" data-original-title="Gestion des liens groupes" href="liens_groupe.php?id_epreuve=<?php echo $row['idEpreuve']; ?>&action=edit" ><i class="fa fa-2x fa-navicon m-r-10"></a></i>
													</td>
												</tr>
												
												<tr id="tr_<?php echo $row_p['idEpreuveParcours']; ?>" style="padding-left:10%">
													
													<td> <span style="vertical-lr">Parcours associés</span></td>
													<td colspan="4">

														
														<ul class="list-unstyled m-l-25">
														<?php while (($row_p=mysqli_fetch_array($result_p)) != FALSE){ 
															//$aff_tarifs = tarifs($row_p['idEpreuveParcours']);
														?>
															<li>
															<?php echo $row_p['idEpreuveParcours']; ?> 
															- <?php echo stripslashes_bdd_to_scr($row_p['nomParcours']); if ($row_p['relais'] > 0) echo " - <i><b>En équipe</b></i> ";?> - 
															<!-- [ <?php echo $aff_tarifs['id_tarif']; ?> - Tarif en cours : <strong><i><?php echo $aff_tarifs['tarif']; ?> € </i></strong>] /-->
															 <a data-toggle="tooltip" data-placement="top" data-original-title="Gestion des inscriptions du parcours <?php echo stripslashes_bdd_to_scr($row_p['nomParcours']); ?>" href="liste_des_inscrits_v2.php?id_epreuve=<?php echo $row['idEpreuve'] ?>&id_parcours=<?php echo $row_p['idEpreuveParcours'] ?>&jours=tous" > ( <?php echo nb_inscrit_total($row['idEpreuve'],$row_p['idEpreuveParcours']); ?> inscrits )</a>
															<!-- [ <a href="javascript:;" onclick="affiche_modal('Liste des inscrits  ', <?php echo $row_p['idEpreuveParcours']; ?>,'http://www.ats-sport.com/<?php echo URL_DEV; ?>liste_des_inscrits.php?id_epreuve=<?php echo $row['idEpreuve']; ?>&id_parcours=<?php echo $row_p['idEpreuveParcours']; ?>&panel=iframe')"> Liste des inscrits </a> ]
															[ <a href="javascript:;" onclick="affiche_modal('Inscription d\'un participant  ',<?php echo $row_p['idEpreuveParcours']; ?>,'http://www.ats-sport.com/<?php echo URL_DEV; ?>inscriptions_admin.php?id_epreuve=<?php echo $row['idEpreuve']; ?>&id_parcours=<?php echo $row_p['idEpreuveParcours']; ?>&panel=iframe')"> Fiche inscription </a> ]
															[ <a href="liste_des_inscrits.php?id_epreuve=<?php echo $row['idEpreuve']; ?>&id_parcours=<?php echo $row_p['idEpreuveParcours']; ?>"> Gestion des inscriptions </a> ] /-->
															
															
															
															</li>
											
														<?php } ?>
														</ul>
														
													</td>
												</tr>
									<?php 	$first = FALSE;
									$cpt++;
												}
											}?>
									</tbody>
								</table>
								<div id="affichage_modal"></div>
								
									<form id='form_epre' method='get' action='creation_epreuve.php'>
										<INPUT type='text' name='file' value='epre' style='display:none;'>
										<INPUT type='text' id='epre_id' name='epre_id' value='' style='display:none;'>
										<INPUT type='text' id='eeid' name='eeid' value='' style='display:none;'>
										<INPUT type='text' name='epre_button' value='Modifier la fiche de cette course' style='display:none;'>
									</form>
									<form id='form_epre_affichage' method='post' action='../epreuves.php'>
										<INPUT type='text' name='file' value='epre' style='display:none;'>
										<INPUT type='text' id='epre_id_aff' name='epre_id_aff' value='' style='display:none;'>
										<INPUT type='text' name='epre_button' value='Modifier la fiche de cette course' style='display:none;'>
									</form>
							</div>
						</div>
					</div>
                    <!-- end panel -->
			    </div>
			    <!-- end col-12 -->
			</div>
			<!-- end row -->
		</div>
		<!-- end #content -->
		
        <!-- begin theme-panel -->
		<?php //include ("includes/panel.php"); ?>
        <!-- end theme-panel -->
		
		<!-- begin scroll to top btn -->
		<a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top fade" data-click="scroll-top"><i class="fa fa-angle-up"></i></a>
		<!-- end scroll to top btn -->
	</div>
	<!-- end page container -->
	<div class="modal" id="stats_admin">
									<div class="modal-dialog" style="width:70%">
										<div class="modal-content">
											<div class="modal-header" style="text-align: right;">
												<strong>STATISTIQUES ats-sport (UNIQUEMENT ADMIN)</strong>
											</div>
											<div class="modal-body">
												<div id="pie_paiement_type" style="height:400px;"></div>
												<div id="pie_epreuve_type" style="height:400px;"></div>
											</div>
											<div class="modal-footer">
												<a data-dismiss="modal" class="btn btn-sm btn-white" href="javascript:;">Fermer</a>
											</div>
										</div><!-- /.modal-content -->
									</div><!-- /.modal-dialog -->
								</div>

								<div class="modal" id="remboursement_a_faire">
									<div class="modal-dialog" style="width:90%">
										<div class="modal-content">
											<div class="modal-header" style="text-align: right;">
												<strong>ETAT DES REMBOURSEMENT (UNIQUEMENT ADMIN)</strong>
											</div>
											<div class="modal-body" id="return_resultat">
											

												<table class="table text-center">
													<thead >
														<tr >
															<th class="text-center">Epreuve/parcours</th>
															<th class="text-center">Référence</th>
															<th class="text-center">Internaute</th>
															<th>Somme à remb.</th>
															<?php if ($admin==1) { ?>
																<th>FB</th>
															<?php } ?>
															<th>Date du paiement</th>
															<th>Effectué ?</th>
															<th>Date de remb.</th>
															<th>Observation</th>
															<th>Action</th>
														</tr>
													</thead>
													<tbody>
													<?php while (($row_remboursement=mysqli_fetch_array($result_remboursement)) != FALSE)
													{ 
									
														if ($row_remboursement['remboursement_effectue']=='non') 
														{
																$query_m  = "SELECT montant FROM b_paiements WHERE reference = ".$row_remboursement['idInscriptionEpreuveInternaute'];
																$result_m = $mysqli->query($query_m);
																$row_m=mysqli_fetch_row($result_m);
																$montant_bp_paiement = $row_m[0];
																
																$find_multiple = stripos($row_remboursement['reference'], "M-");
																$new_ref = '';
															
															if ($find_multiple !== false) {
																	//echo "ICI";
																	$bodytag = str_replace("M-", "", $row_remboursement['reference']);
																	
																	$query_m  = "SELECT idInternauteref, id_unique_paiement  FROM r_internautereferent WHERE idInternauteReferent = ".$bodytag; 
																	$result_m = $mysqli->query($query_m);
																	$row_m=mysqli_fetch_row($result_m);
																	
																	//if ($row_m[1] != '') { $row_m[1] = str_replace("R-", "", $row_m[1]) ; $new_ref="</br> ( A-".$row_m[0]."-".$row_m[1]." ) "; $new_ref_bp = "A-".$row_m[0]."-".$row_m[1]; } else { $new_ref_bp = $row_remboursement['reference'];   } 
															
																	$query_m2  = 'SELECT reference, montant FROM b_paiements WHERE reference LIKE "%'.$row_m[1].'%" AND id_epreuve = '.$row_remboursement['idEpreuve']; 
																	$result_m2 = $mysqli->query($query_m2);
																	$row_m2=mysqli_fetch_row($result_m2);
																	$montant_bp_paiement = $row_m2[1];
																	
																	if ($row_m[1] != '') { $new_ref="</br> (".$row_m2[0].") "; $new_ref_bp = $row_m2[0]; } else { $new_ref_bp = $row_remboursement['reference'];   } 
																	
																	
															}		
															$color='text-danger'; ?>
												
														<tr class="<?php echo $color; ?>" id="tr_remboursement_<?php echo $row_remboursement['idInscriptionRemboursement']; ?>">
															
															<td><?php echo $row_remboursement['nomEpreuve']; ?> / <?php echo $row_remboursement['nomParcours']; ?> ( IdIP/IdEpPar : <?php echo $row_remboursement['idEpreuve']; ?>/<?php echo $row_remboursement['idEpreuveParcours']; ?> )</td>
															<td><strong><?php echo $row_remboursement['reference'].$new_ref; ?></strong></td>
															<td><strong><?php echo $row_remboursement['nomInternaute']; ?>  <?php echo $row_remboursement['prenomInternaute']; ?> </strong></td>
															
															<!-- <td><strong><?php if ($row_remboursement['payeur']=='coureur') echo ($row_remboursement['remboursement']-$row_remboursement['frais_remboursement']); else echo $row_remboursement['remboursement']; ?> € <span class="text-primary">[ <?php echo $montant_bp_paiement; ?> € ] </span></strong> //--> <?php //if ($row_remboursement['paiement_type']=='A REMBOURSER' || $row_remboursement['paiement_type']=='REMBOURSE') echo '<strong>(C)</strong>';else echo '<strong>(P)</strong>'; ?> </td>
															<td><strong><?php echo ($row_remboursement['remboursement']-$row_remboursement['frais_remboursement']); ?> € <span class="text-primary">[ <?php echo $montant_bp_paiement; ?> € (FrC) / <?php echo $row_remboursement['remboursement']; ?> € (FrS)] </span></strong>
															<?php if ($admin==1) { ?>
																<td><strong><?php echo $row_remboursement['frais_remboursement']; ?> € (<?php if ($row_remboursement['payeur']=='coureur') echo '<strong>C</strong>';else echo '<strong>O</strong>'; ?>)</strong></td>
															<?php } ?>
															<td id="td_date_paiement_<?php echo $row_remboursement['idInscriptionRemboursement']; ?>"><strong><?php echo strtoupper($row_remboursement['paiement_date']); ?></strong></td>
															<td id="td_effectue_<?php echo $row_remboursement['idInscriptionRemboursement']; ?>"><strong><?php echo strtoupper($row_remboursement['remboursement_effectue']); ?></strong></td>
															<td id="td_date_<?php echo $row_remboursement['idInscriptionRemboursement']; ?>"><strong><?php if (empty($row_remboursement['remboursement_date'])) echo "-------"; else echo dateen2fr($row_remboursement['remboursement_date'],0,'d-m-y H:i'); ?></strong></td>
															<td id="td_observation_<?php echo $row_remboursement['idInscriptionRemboursement']; ?>"><strong><?php echo strtoupper($row_remboursement['observation']); ?></strong></td>
															<td id="td_action_<?php echo $row_remboursement['idInscriptionRemboursement']; ?>">
																<?php if ($row_remboursement['remboursement_effectue']=='non') { ?>
																	<a href="javascript:;" onclick="modif_inscription_internaute(<?php echo $row_remboursement['idInscriptionRemboursement']; ?>,<?php echo $row_remboursement['idEpreuve']; ?>,<?php echo $row_remboursement['idEpreuveParcours']; ?>,'supp_remboursement')"> <i class="text-danger fa fa-1x fa-trash-o" data-original-title="Supprimé ?" data-placement="top" data-toggle="tooltip"></i></a>
																	<a href="javascript:;" onclick="modif_inscription_internaute(<?php echo $row_remboursement['idInscriptionRemboursement']; ?>,<?php echo $row_remboursement['idEpreuve']; ?>,<?php echo $row_remboursement['idEpreuveParcours']; ?>,'valide_remboursement')"> <i class="text-success fa fa-1x fa-check" data-original-title="Effectué ?" data-placement="top" data-toggle="tooltip"></i></a>																
																<?php } else { ?>
																------------
																<?php } ?>
															</td>
														</tr>
														
														<?php } ?>	
													<?php } ?>
													</tbody>
												</table>
												
												
											
											
											</div>
											<div class="modal-footer">
												<a data-dismiss="modal" class="btn btn-sm btn-white" href="javascript:;">Fermer</a>
												
											</div>
										</div><!-- /.modal-content -->
									</div><!-- /.modal-dialog -->
								</div>
								
								<div class="modal" id="remboursement_a_faire_partiel">
									<div class="modal-dialog" style="width:90%">
										<div class="modal-content">
											<div class="modal-header" style="text-align: right;">
												<strong>ETAT DES REMBOURSEMENT PARTIEL (UNIQUEMENT ADMIN)</strong>
											</div>
											<div class="modal-body" id="return_resultat">
											

												<table class="table text-center">
													<thead >
														<tr >
															<th class="text-center">Epreuve/parcours</th>
															<th class="text-center">Référence</th>
															<th class="text-center">Internaute</th>
															<th>Somme à remb.</th>
															<?php if ($admin==1) { ?>
																<th>FB</th>
															<?php } ?>
															<th>Date du paiement</th>
															<th>Effectué ?</th>
															<th>Date de remb.</th>
															<th>Observation</th>
															<th>Action</th>
														</tr>
													</thead>
													<tbody>
													<?php while (($row_remboursement=mysqli_fetch_array($result_remboursement_partiel)) != FALSE)
													{ 
									
														if ($row_remboursement['remboursement_effectue']=='non') {
																$query_m  = "SELECT montant FROM b_paiements WHERE reference = ".$row_remboursement['idInscriptionEpreuveInternaute'];
																$result_m = $mysqli->query($query_m);
																$row_m=mysqli_fetch_row($result_m);
																$montant_bp_paiement = $row_m[0];
																
																$find_multiple = stripos($row_remboursement['reference'], "M-");
																$new_ref = '';
														if ($find_multiple !== false) {
																
																	$bodytag = str_replace("M-", "", $row_remboursement['reference']);
																	
																	$query_m  = "SELECT idInternauteref, id_unique_paiement  FROM r_internautereferent WHERE idInternauteReferent = ".$bodytag; 
																	$result_m = $mysqli->query($query_m);
																	$row_m=mysqli_fetch_row($result_m);
																	
																	//if ($row_m[1] != '') { $row_m[1] = str_replace("R-", "", $row_m[1]) ; $new_ref="</br> ( A-".$row_m[0]."-".$row_m[1]." ) "; $new_ref_bp = "A-".$row_m[0]."-".$row_m[1]; } else { $new_ref_bp = $row_remboursement['reference'];   } 
															
																	$query_m2  = 'SELECT reference, montant FROM b_paiements WHERE reference LIKE "%'.$row_m[1].'%" AND id_epreuve = '.$row_remboursement['idEpreuve']; 
																	$result_m2 = $mysqli->query($query_m2);
																	$row_m2=mysqli_fetch_row($result_m2);
																	$montant_bp_paiement = $row_m2[1];
																	
																	if ($row_m[1] != '') { $new_ref="</br> (".$row_m2[0].") "; $new_ref_bp = $row_m2[0]; } else { $new_ref_bp = $row_remboursement['reference'];   } 
																		
																
														}		
															$color='text-danger'; ?>
												
														<tr class="<?php echo $color; ?>" id="tr_remboursement_<?php echo $row_remboursement['idInscriptionRemboursement']; ?>">
															
															<td><?php echo $row_remboursement['nomEpreuve']; ?> / <?php echo $row_remboursement['nomParcours']; ?> ( IdIP/IdEpPar : <?php echo $row_remboursement['idEpreuve']; ?>/<?php echo $row_remboursement['idEpreuveParcours']; ?> )</td>
															<td><strong><?php echo $row_remboursement['reference'].$new_ref; ?></strong></td>
															<td><strong><?php echo $row_remboursement['nomInternaute']; ?>  <?php echo $row_remboursement['prenomInternaute']; ?> </strong></td>
															
															<td><strong><?php if ($row_remboursement['payeur']=='coureur') echo ($row_remboursement['remboursement']-$row_remboursement['frais_remboursement']); else echo $row_remboursement['remboursement']; ?> € <span class="text-primary">[ <?php echo $montant_bp_paiement; ?> € ] </span></strong> <?php //if ($row_remboursement['paiement_type']=='A REMBOURSER' || $row_remboursement['paiement_type']=='REMBOURSE') echo '<strong>(C)</strong>';else echo '<strong>(P)</strong>'; ?> </td>
															<?php if ($admin==1) { ?>
																<td><strong><?php echo $row_remboursement['frais_remboursement']; ?> € (<?php if ($row_remboursement['payeur']=='coureur') echo '<strong>C</strong>';else echo '<strong>O</strong>'; ?>)</strong></td>
															<?php } ?>
															<td id="td_date_paiement_<?php echo $row_remboursement['idInscriptionRemboursement']; ?>"><strong><?php echo strtoupper($row_remboursement['paiement_date']); ?></strong></td>
															<td id="td_effectue_<?php echo $row_remboursement['idInscriptionRemboursement']; ?>"><strong><?php echo strtoupper($row_remboursement['remboursement_effectue']); ?></strong></td>
															<td id="td_date_<?php echo $row_remboursement['idInscriptionRemboursement']; ?>"><strong><?php if (empty($row_remboursement['remboursement_date'])) echo "-------"; else echo dateen2fr($row_remboursement['remboursement_date'],0,'d-m-y H:i'); ?></strong></td>
															<td id="td_observation_<?php echo $row_remboursement['idInscriptionRemboursement']; ?>"><strong><?php echo strtoupper($row_remboursement['observation']); ?></strong></td>
															<td id="td_action_<?php echo $row_remboursement['idInscriptionRemboursement']; ?>">
																<?php if ($row_remboursement['remboursement_effectue']=='non') { ?>
																	<a href="javascript:;" onclick="modif_inscription_internaute(<?php echo $row_remboursement['idInscriptionRemboursement']; ?>,<?php echo $row_remboursement['idEpreuve']; ?>,<?php echo $row_remboursement['idEpreuveParcours']; ?>,'supp_remboursement')"> <i class="text-danger fa fa-1x fa-trash-o" data-original-title="Supprimé ?" data-placement="top" data-toggle="tooltip"></i></a>
																	<a href="javascript:;" onclick="modif_inscription_internaute(<?php echo $row_remboursement['idInscriptionRemboursement']; ?>,<?php echo $row_remboursement['idEpreuve']; ?>,<?php echo $row_remboursement['idEpreuveParcours']; ?>,'valide_remboursement')"> <i class="text-success fa fa-1x fa-check" data-original-title="Effectué ?" data-placement="top" data-toggle="tooltip"></i></a>																
																<?php } else { ?>
																------------
																<?php } ?>
															</td>
														</tr>
														
														<?php } ?>	
													<?php } ?>
													</tbody>
												</table>
												
												
											
											
											</div>
											<div class="modal-footer">
												<a data-dismiss="modal" class="btn btn-sm btn-white" href="javascript:;">Fermer</a>
												
											</div>
										</div><!-- /.modal-content -->
									</div><!-- /.modal-dialog -->
								</div>

								
								<div class="modal" id="remboursement_effectue">
									<div class="modal-dialog" style="width:90%">
										<div class="modal-content">
											<div class="modal-header" style="text-align: right;">
												<strong>ETAT DES REMBOURSEMENTS EFFECTUES(UNIQUEMENT ADMIN)</strong>
											</div>
											<div class="modal-body" id="return_resultat">
											

												<table class="table text-center">
													<thead >
														<tr >
															<th class="text-center">Epreuve/parcours</th>
															<th class="text-center">Référence</th>
															<th class="text-center">Internaute</th>
															<th>Montant</th>
															<?php if ($admin==1) { ?>
																<th>FB</th>
															<?php } ?>
															<th>Date du paiement</th>
															<th>Effectué ?</th>
															<th>Date de remb.</th>
															<th>Observation</th>
															<th>Action</th>
														</tr>
													</thead>
													<tbody>
													<?php while (($row_remboursement=mysqli_fetch_array($result_remboursement_effectue )) != FALSE)
													{ 
														
															$color="text-success"; ?>
												
														<tr class="<?php echo $color; ?>" id="tr_remboursement_<?php echo $row_remboursement['idInscriptionRemboursement']; ?>">
															
															<td><?php echo $row_remboursement['nomEpreuve']; ?> / <?php echo $row_remboursement['nomParcours']; ?> ( IdIP/IdEpPar : <?php echo $row_remboursement['idEpreuve']; ?>/<?php echo $row_remboursement['idEpreuveParcours']; ?> )</td>
															<td><strong><?php echo $row_remboursement['reference']; ?></strong></td>
															<td><strong><?php echo $row_remboursement['nomInternaute']; ?>  <?php echo $row_remboursement['prenomInternaute']; ?> </strong></td>
															
															<td><strong><?php echo $row_remboursement['remboursement']; ?> € </strong>( <?php if ($row_remboursement['paiement_type']=='A REMBOURSER' || $row_remboursement['paiement_type']=='REMBOURSE') echo '<strong><span data-original-title="Frais coureur" data-placement="top" data-toggle="tooltip">C</span></strong>';else echo '<strong><span data-original-title="Frais organisateur" data-placement="top" data-toggle="tooltip">O</span></strong>'; ?> - <?php if ($row_remboursement['remboursement_type']=='PARTIEL') echo '<strong><span data-original-title="Remboursement partiel" data-placement="top" data-toggle="tooltip">P</span></strong>';else echo '<strong><span data-original-title="Remboursement complet" data-placement="top" data-toggle="tooltip">C</span></strong>'; ?> )</td>
															<?php if ($admin==1) { ?>
																<td><strong><?php echo $row_remboursement['frais_remboursement']; ?> € ( <?php if ($row_remboursement['payeur']=='coureur') echo '<strong>C</strong>';else echo '<strong>O</strong>'; ?> )</strong></td>
															<?php } ?>
															<td id="td_date_paiement_<?php echo $row_remboursement['idInscriptionRemboursement']; ?>"><strong><?php echo strtoupper($row_remboursement['paiement_date']); ?></strong></td>
															<td id="td_effectue_<?php echo $row_remboursement['idInscriptionRemboursement']; ?>"><strong><?php echo strtoupper($row_remboursement['remboursement_effectue']); ?></strong></td>
															<td id="td_date_<?php echo $row_remboursement['idInscriptionRemboursement']; ?>"><strong><?php if (empty($row_remboursement['remboursement_date'])) echo "-------"; else echo dateen2fr($row_remboursement['remboursement_date'],0,'d-m-y H:i'); ?></strong></td>
															<td id="td_observation_<?php echo $row_remboursement['idInscriptionRemboursement']; ?>"><strong><?php echo strtoupper($row_remboursement['observation']); ?></strong></td>
															<td id="td_action_<?php echo $row_remboursement['idInscriptionRemboursement']; ?>">
																<?php if ($row_remboursement['remboursement_effectue']=='non') { ?>
																	<a href="javascript:;" onclick="modif_inscription_internaute(<?php echo $row_remboursement['idInscriptionRemboursement']; ?>,<?php echo $row_remboursement['idEpreuve']; ?>,<?php echo $row_remboursement['idEpreuveParcours']; ?>,'supp_remboursement')"> <i class="text-danger fa fa-1x fa-trash-o" data-original-title="Supprimé ?" data-placement="top" data-toggle="tooltip"></i></a>
																	<a href="javascript:;" onclick="modif_inscription_internaute(<?php echo $row_remboursement['idInscriptionRemboursement']; ?>,<?php echo $row_remboursement['idEpreuve']; ?>,<?php echo $row_remboursement['idEpreuveParcours']; ?>,'valide_remboursement')"> <i class="text-success fa fa-1x fa-check" data-original-title="Effectué ?" data-placement="top" data-toggle="tooltip"></i></a>																
																<?php } else { ?>
																------------
																<?php } ?>
															</td>
														</tr>

													<?php } ?>
													</tbody>
												</table>
												
												
											
											
											</div>
											<div class="modal-footer">
												<a data-dismiss="modal" class="btn btn-sm btn-white" href="javascript:;">Fermer</a>
												
											</div>
										</div><!-- /.modal-content -->
									</div><!-- /.modal-dialog -->
								</div>
	<!-- ================== BEGIN BASE JS ================== -->
		<?php include ("includes/footer_js_base.php"); ?>
	<!-- ================== END BASE JS ================== -->
	
	<!-- ================== BEGIN PAGE LEVEL JS ================== -->
		<script src="assets/plugins/jquery-auto-google/jquery.autocomplete.js" type="text/javascript"></script>
		<script src="assets/plugins/Highcharts/js/highcharts.js"></script>
		<script src="assets/js/draw_stats_admin.js"></script>

		<script type="text/javascript">



		$(document).ready(function() {
			App.init();
			
			//TreeView.init();
		});
	function form_epre_submit(idEpreuve,eeid){
				$('#epre_id').val(idEpreuve);
				$('#eeid').val(eeid);
				$('#form_epre').submit();
			}
	function form_epre_affichage_submit(idEpreuve){
				$('#epre_id_aff').val(idEpreuve);
				$('#form_epre_affichage').submit();
			}
			
	$(function() {
    $('#insc_nom_1').keyup(function() {
        this.value = this.value.toLocaleUpperCase();
    });
});
			
function modif_epreuve(idEpreuve,nomEpreuve) {
	

	if (!confirm("Confirmer vous la suppression de l'épreuve (cela entrainera aussi la suppression des inscriptions) ?")) {
return false

}
	var targetUrl = 'includes/ajaxModifEpreuve.php';
	
		$.post(targetUrl,
        
		{

			idEpreuve: idEpreuve,
		
        }		
		
		).done(function(data) {
		
		var dataObj = JSON.parse(data);
		
		if (dataObj.comeback == 'ok' ) {
		
		notification('Notification','L\'épreuve <strong>'+nomEpreuve+'</strong> a bien supprimée !',5000,'ok');
		$('#tr_'+ idEpreuve).remove();
		//$('#aff_reglement_paiement').hide();
			//$('#aff_info_paiement_cheque').show();
		//alert('Le certificat ou la license a été validé.');
		//location.reload();
		/*var form= document.createElement('form');
				form.method= 'post';
				form.action= 'inscriptions.php?id_epreuve='+$('#id_epreuve_update').val()+'&id_parcours='+$('#id_parcours_update').val();
					var input= document.createElement('input');
					input.type= 'hidden';
					input.name= 'action';
					input.value= 'update_aff';
					form.appendChild(input);
				document.body.appendChild(form);
				form.submit();
				return false; */
		}else {
			
				//location.reload();alert('Erreur sur la validation du certificat ou de la license.');
			//$('#info_connexion-alert').show();
			
		}
				
		
		});
		

					
	
	
}

function notification (title,text,time,image) {
	//$('#add-without-image').click(function(){
		$.gritter.add({
			title: title,
			text: text,
			time:time,
			image: 'assets/plugins/gritter/images/'+image+'.png'
		});
		return false;
	//});
}

function Create_Modal (titre,id_parcours,url,height,width) {
content ='<div id="modal_' + id_parcours + '" class="modal">';	

content +='				<div class="modal-dialog" style="width:80%;"><a class="modal-close close" href="#"></a>';
content +='					<div class="modal-content" >';
content +='						<div class="modal-header" style="text-align: right;width:100%">';
content +='<button aria-hidden="true" data-dismiss="modal" class="close" type="button">X</button>';
content +='							<h4 class="modal-title text-left">'+titre+'</h4>';
content +='						</div>';
content +='						<div class="modal-body" id="return_resultat" style="width:100%;height:'+(height-200)+'px">';

/*
content +='	<div class="col-sm-12 col-centered">';
content +='		<div class="panel panel-info">';
content +='			<div class="panel-heading">';
content +='				<h4 class="panel-title">Liste des inscrits</h4>';
content +='			</div>';
content +='		</div>';
content +='	</div>';
*/
content +='	<div class="col-sm-12 col-centered">';
//content +='		<div class="panel panel-info">';
//content +='			<div class="panel-body bg-black"> ';
content +='<iframe src="'+url+'" scrolling="yes" frameBorder="0" width="100%" height="'+(height-200)+'px" allowfullscreen="allowfullscreen" ></iframe>';
//content +='			</div>';
//content +='		</div>';
content +='	</div>';

content +='						</div>';
content +='						<div class="modal-footer">';
content +='							<a data-dismiss="modal" class="btn btn-sm btn-white" href="javascript:;">Annuler</a>';
///content +='							<a class="btn btn-sm btn-success" href="#" onclick="maj_info_participant(' + id_internaute + ',$(\'#insc_date_certificat_' + id_internaute + '\' ).val(),<?php echo $id_epreuve; ?>,<?php echo $id_parcours; ?>,\'<?php echo $id_session; ?>\',\'CB\')">Envoyer</a>';
content +='						</div>';
content +='					</div>';
content +='				</div>';
content +='</div>';	


$('#affichage_modal').append(content);	
}
function affiche_modal (titre,id_parcours,url) {	

	//$('#modal_' + id_parcours).html('');
	$('#affichage_modal').html('');
	//console.log($(window).height());
	
	Create_Modal (titre,id_parcours,url,$(window).height(),$(window).width());
	
	$('#modal_' + id_parcours).modal({
		open: true,
		modal: true,
	});	
}

function export_sql_epreuve(idEpreuve,nomEpreuve) {
	
	var targetUrl = 'includes/ajaxExportEpreuve.php';
	
		$.post(targetUrl,
        
		{

			idEpreuve: idEpreuve,
		
        }		
		
		).done(function(data) {
		
		var dataObj = JSON.parse(data);
		
		if (dataObj.comeback == 'ok' ) {
		
		notification('Notification','L\'épreuve <strong>'+nomEpreuve+'</strong> a bien exportée !',5000,'ok');
		$('#tr_'+ idEpreuve).remove();
		//$('#aff_reglement_paiement').hide();
			//$('#aff_info_paiement_cheque').show();
		//alert('Le certificat ou la license a été validé.');
		//location.reload();
		/*var form= document.createElement('form');
				form.method= 'post';
				form.action= 'inscriptions.php?id_epreuve='+$('#id_epreuve_update').val()+'&id_parcours='+$('#id_parcours_update').val();
					var input= document.createElement('input');
					input.type= 'hidden';
					input.name= 'action';
					input.value= 'update_aff';
					form.appendChild(input);
				document.body.appendChild(form);
				form.submit();
				return false; */
		}else {
			
				//location.reload();alert('Erreur sur la validation du certificat ou de la license.');
			//$('#info_connexion-alert').show();
			
		}
				
		
		});
		

					
	
	
}
$('#insc_nom_1').autocomplete({
valueKey:'title',
dropdownWidth:'auto',
minLength: 3,
source:[{
	url:"../includes/ajaxRecherchePersonne.php?id_epreuve=0&action=fast&q=%QUERY%",
	//url:"http://xdsoft.net/component/jquery_plugins/?task=demodata&q=%QUERY%",
	type:'remote',
	getValue:function(item){
	return item.value
	},
	ajax:{
		dataType : 'jsonp'	
	}
}]}).on('selected.xdsoft',function(e,datum){

		if (datum.value) {
							
							affiche_modal('Modification d\'un participant',0,'https://www.ats-sport.com/<?php echo URL_DEV; ?>/admin/profile.php?id_epreuve='+datum.idEpreuve+'&id_parcours='+datum.idEpreuveParcours+'&action=update_aff&id_int='+datum.idInscriptionEpreuveInternaute+'&panel=iframe');
						}
});


		/*
			$('#insc_nom_1').autocomplete({
		      			source : '../includes/ajaxRecherchePersonne.php?id_epreuve=0&action=fast',
						autoFocus: true,
						minLength: 3,
		      			dataType: "json",
						select:function(event,ui) {
							//alert(ui.item.test);
						if (ui.item.value) {
							affiche_modal('Modification d\'un participant',0,'http://www.ats-sport.com/<?php echo URL_DEV; ?>/admin/profile.php?id_epreuve='+ui.item.idEpreuve+'&id_parcours='+ui.item.idEpreuveParcours+'&action=update_aff&id_int='+ui.item.idInscriptionEpreuveInternaute+'&panel=iframe');
						}

						 }
  	
		      });
		*/	  
function Create_Modal (titre,id_parcours,url,height,width) {
height = 750;
content ='<div id="modal_' + id_parcours + '" class="modal">';	

content +='				<div class="modal-dialog" style="width:1000px;"><a class="modal-close close" href="#"></a>';
content +='					<div class="modal-content" >';
/*
content +='						<div class="modal-header" style="text-align: right;width:100%">';
content +='							<button aria-hidden="true" data-dismiss="modal" class="close" type="button">X</button>';
content +='							<h4 class="modal-title text-left">'+titre+'</h4>';
content +='						</div>';
*/
content +='						<div class="modal-body" id="return_resultat" style="width:100%;height:'+(height-170)+'px">';

/*
content +='	<div class="col-sm-12 col-centered">';
content +='		<div class="panel panel-info">';
content +='			<div class="panel-heading">';
content +='				<h4 class="panel-title">Liste des inscrits</h4>';
content +='			</div>';
content +='		</div>';
content +='	</div>';
*/
content +='	<div class="col-sm-12 col-centered">';
//content +='		<div class="panel panel-info">';
//content +='			<div class="panel-body bg-black"> ';
content +='<iframe src="'+url+'" scrolling="yes" frameBorder="0" width="100%" height="'+(height-200)+'px" allowfullscreen="allowfullscreen" ></iframe>';
//content +='			</div>';
//content +='		</div>';
content +='	</div>';

content +='						</div>';
content +='						<div class="modal-footer">';
content +='							<a data-dismiss="modal" class="btn btn-sm btn-primary" href="javascript:;" ><strong>Revenir à la liste SANS rechargement</strong></a>';
content +='							<a data-dismiss="modal" class="btn btn-sm btn-default" href="javascript:;" onclick="window.location.reload(true);"><strong>Revenir à la liste AVEC rechargement</strong></a>';
///content +='							<a class="btn btn-sm btn-success" href="#" onclick="maj_info_participant(' + id_internaute + ',$(\'#insc_date_certificat_' + id_internaute + '\' ).val(),<?php echo $id_epreuve; ?>,<?php echo $id_parcours; ?>,\'<?php echo $id_session; ?>\',\'CB\')">Envoyer</a>';
content +='						</div>';
content +='					</div>';
content +='				</div>';
content +='</div>';	


$('#affichage_modal').append(content);	
}
function affiche_modal (titre,id_parcours,url) {	

	//$('#modal_' + id_parcours).html('');
	$('#affichage_modal').html('');
	//console.log($(window).height());
	
	Create_Modal (titre,id_parcours,url,$(window).height(),$(window).width());
	
	$('#modal_' + id_parcours).modal({
		open: true,
		modal: true,
	});	
}
function url_redirect_new(id_epreuve) {
		
		url = 'liste_des_inscrits_v2.php?id_epreuve='+id_epreuve+'&jours=tous';
		//url = 'profile.php?id_epreuve='+id_epreuve+'&panel='+iframe;
		window.location = url;
}
function modif_inscription_internaute(idInscriptionRemboursement,id_epreuve,id_parcours,action) {
	
	if (action=='supp_remboursement') if(!confirm("Confirmez vous la suppression de ce remboursement ?")) return;
	//if (action=='arembourser') if(!confirm("*** INFORMATION *** Chaque demande de remboursement par CB est facturé 3% de la somme à rembourser. Confirmez vous le remboursement complet de l\'inscription de l\'internaute "+nom+" "+prenom+" ?")) return;
	//if (action=='annulearembourser') if(!confirm("Confirmez vous l\'annulation de la demande de l\'inscription de l\'internaute "+nom+" "+prenom+" ?")) return;
	//alert(date_certificat);
	//check_date = CheckDate(date_certificat);
	//check_date = CheckDate($('#insc_date_certificat_' + id_internaute).val());
	//if (check_date != 1 ) return false;
		
	
	var targetUrl = 'includes/AjaxModifInternaute.php';
	
		$.post(targetUrl,
        
		{
			action : action,
			id_epreuve: id_epreuve,
			id_parcours : id_parcours,
			idInscriptionRemboursement : idInscriptionRemboursement

        }		
		
		).done(function(data) {
		
		var dataObj = JSON.parse(data);
		
		if (dataObj.comeback == 'OK' ) {
			
			
			if (action=='supp_remboursement')	
			{
				$('#tr_remboursement_'+dataObj.id).remove();
				notification('Notification','le remboursement a été annulé',5000,'ok');
			}
			if (action=='valide_remboursement') 
			{	
				$('#td_action_'+dataObj.id).html('');
				$('#td_effectue_'+dataObj.id).html('oui');
				$('#td_date_'+dataObj.id).html(dataObj.date);
				//$('#tr_remboursement_'+dataObj.id).toggleClass('text-success');
				$('#tr_remboursement_'+dataObj.id).removeClass('text-danger');
				$('#tr_remboursement_'+dataObj.id).addClass('text-success'); 
				notification('Notification','le remboursement a été validé',5000,'ok');
			
			}

		
		}else {
			

			alert('KO');

			
		}
				
		
		});
				
	
	
}

function show_modif_orga(idEpreuve,idInternaute,dateModification) {
	
console.log(idEpreuve+" - "+idInternaute+" - "+dateModification);
		
	
	var targetUrl = 'includes/AjaxShowModif.php';
	
		$.post(targetUrl,
        
		{
			idEpreuve : idEpreuve,
			idInternaute: idInternaute,
			dateModification : dateModification,
			//id_internaute_modif_epreuve : id_internaute_modif_epreuve

        }		
		
		).done(function(data) {
		
		var dataObj = JSON.parse(data);
		
		if (dataObj.comeback == 'OK' ) {

				alert("Modifié par "+dataObj.login+"("+dataObj.id_internaute_modif_epreuve+") à la date du : "+dataObj.dateModification);


		
		}
				
		
		});
				
	
	
}

function Create_Modal_v2 (titre,id_parcours,url,height,width) {
//height = 750;
content ='<div id="modal_' + id_parcours + '" class="modal">';	

content +='				<div class="modal-dialog" style="width:'+width+'px;"><a class="modal-close close" href="#"></a>';
content +='					<div class="modal-content" >';
/*
content +='						<div class="modal-header" style="text-align: right;width:100%">';
content +='							<button aria-hidden="true" data-dismiss="modal" class="close" type="button">X</button>';
content +='							<h4 class="modal-title text-left">'+titre+'</h4>';
content +='						</div>';
*/
content +='						<div class="modal-body" id="return_resultat" style="width:100%;height:'+(height-170)+'px">';

/*
content +='	<div class="col-sm-12 col-centered">';
content +='		<div class="panel panel-info">';
content +='			<div class="panel-heading">';
content +='				<h4 class="panel-title">Liste des inscrits</h4>';
content +='			</div>';
content +='		</div>';
content +='	</div>';
*/
content +='	<div class="col-sm-12 col-centered">';
//content +='		<div class="panel panel-info">';
//content +='			<div class="panel-body bg-black"> ';
content +='<iframe src="'+url+'" scrolling="yes" frameBorder="0" width="100%" height="'+(height-200)+'px" allowfullscreen="allowfullscreen" ></iframe>';
//content +='			</div>';
//content +='		</div>';
content +='	</div>';

content +='						</div>';
content +='						<div class="modal-footer">';
content +='							<a data-dismiss="modal" class="btn btn-sm btn-default" href="javascript:;" onclick="window.location.reload(true);"><strong>Fermer</strong></a>';
//content +='							<a data-dismiss="modal" class="btn btn-sm btn-primary" href="javascript:;" ><strong>Revenir à la liste SANS rechargement</strong></a>';
//content +='							<a data-dismiss="modal" class="btn btn-sm btn-default" href="javascript:;" onclick="window.location.reload(true);"><strong>Revenir à la liste AVEC rechargement</strong></a>';
///content +='							<a class="btn btn-sm btn-success" href="#" onclick="maj_info_participant(' + id_internaute + ',$(\'#insc_date_certificat_' + id_internaute + '\' ).val(),<?php echo $id_epreuve; ?>,<?php echo $id_parcours; ?>,\'<?php echo $id_session; ?>\',\'CB\')">Envoyer</a>';
content +='						</div>';
content +='					</div>';
content +='				</div>';
content +='</div>';	


$('#affichage_modal').append(content);	
}

function affiche_modal_v2 (titre,id_parcours,url,height,width) {	
	//$('#modal_' + id_parcours).html('');
	$('#affichage_modal').html('');
	//console.log(height);
	var height_aff = $(window).height();
	Create_Modal_v2 (titre,id_parcours,url,height_aff,$(window).width());
	
	$('#modal_' + id_parcours).modal({
		open: true,
		modal: true,
	});	
}
	</script>
</body>
</html>
