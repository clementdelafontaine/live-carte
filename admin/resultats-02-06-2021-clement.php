<?php 
//define("URL_DEV", '/dev/');
ini_set("display_errors", 0);
error_reporting(E_ALL);


require_once("includes/includes.php");

require_once("includes/functions.php");	

require_once("includes/functions_hugo.php");	

global $mysqli;
if (!function_exists ('banniere_accueil'))
{
function banniere_accueil($type)
{
	
	global $mysqli;
	$tab_banniere = array();
	$query  = "SELECT idBanniere, label, url_image, url_lien, information ";
	$query .= "FROM r_bannieres ";
	$query .=" WHERE type = '".$type."'";
	$query .=" AND NOW() BETWEEN dateDebut AND dateFin ";
	$query .=" AND active = 'oui' ";
	$query .= " ORDER BY idBanniere ASC;";
	//echo $query;
	
	$result = $mysqli->query($query);
	$row=mysqli_fetch_array($result);
	$tab_banniere['idBanniere'] = $row['idBanniere'];
	$tab_banniere['label'] = $row['label'];
	$tab_banniere['url_image'] = $row['url_image'];
	$tab_banniere['information'] = $row['information'];
	if (empty($row['url_lien'])) $tab_banniere['url_lien']='javascript:;'; else $tab_banniere['url_lien'] = $row['url_lien'];
	
	if (!empty($row['idBanniere']))
	{
		$query  = "UPDATE r_bannieres SET ";
		$query .= " nb_impression = nb_impression + 1 "; //nb_truc=nb_truc+
		$query .= " WHERE idBanniere=". $row['idBanniere'];
		$result_query = $mysqli->query($query);	
	}
	return $tab_banniere;
}
}

if(!isset($_SESSION["log_log"]))
{		
	if(isset($_POST['login']) && isset($_POST['pass']))
	{
		login_internaute ($_POST['login'],$_POST['pass']);
	}
	else
	{
		header('Location: login_v2.php');
	}	
}

if( isset( $_GET['id_epreuve'] ) )
{
	$idEpreuve = $_GET['id_epreuve'];
}
else
{
	header('Location: liste_epreuves.php');
}
	
$admin = '';
if ($_SESSION["typeInternaute"] == 'admin' || $_SESSION["typeInternaute"] == 'super_organisateur') $admin=1;

$_SESSION['url_en_cours'] = $_SERVER['REQUEST_URI'];	

if( isset( $_POST['ajout_temps'] ) )
{
	$retour = insert_fichier_resultats( $idEpreuve, $_POST['lieux'], $_FILES );
}

if( isset( $_POST['ajout_lecteur'] ) )
{
	insert_config_lecteur( $idEpreuve, $_POST );
}

if( isset( $_POST['ajout_vague'] ) )
{
	$retour_vague = insert_config_vague( $idEpreuve, $_POST );
}

if( isset( $_POST['supprime_lieu'] ) )
{
	delete_resultats_lieu( $idEpreuve, $_POST['supprime_lieu'] );
}	

?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<title>Ats Sport | Résultats de l'épreuve</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<meta content="" name="description" />
	<meta content="" name="author" />
	
	<!-- ================== begin CSS DE BASE INCLUS DANS TOUTES LES PAGES ================== -->
	<?php require_once ("includes/header_css_js_base.php");?>
	<!-- ================== end CSS DE BASE INCLUS DANS TOUTES LES PAGES ================== -->
	
	<!-- ================== begin CSS EN PLUS SELON LES FONCTIONNALITES CHOISIES EN PLUS ================== -->
	<link href="assets/plugins/datetimepicker-master/jquery.datetimepicker.css" rel="stylesheet" />
	<link href="assets/plugins/switchery/switchery.min.css" rel="stylesheet" />

	<!-- ================== end CSS EN PLUS SELON LES FONCTIONNALITES CHOISIES EN PLUS ================== -->

	<!-- ###########################################################################################################  -->

	<!-- ================== begin SCIPTS JS EN PLUS SI BESOIN DE LES METTRE AVANT LE CODE  EN PLUS ================== -->
	
	<!-- <script src="assets/plugins/morris/raphael.min.js"></script> -->

	<!-- ================== end SCIPTS JS EN PLUS SI BESOIN DE LES METTRE AVANT LE CODE  EN PLUS ================== -->		
</head>
<body>
	<!-- begin #page-loader -->
	<div id="page-loader" class="fade in"><span class="spinner"></span></div>
	<!-- end #page-loader -->
	
	<!-- begin #page-container -->
	<div id="page-container" class="fade page-sidebar-fixed page-header-fixed gradient-enabled">
		<!-- begin #header (Bandeau du haut)-->
		<?php 	require_once ("includes/header.php"); 
		?>
		<!-- end #header (Bandeau du haut)-->
		
		<!-- begin #sidebar (menu) -->
		<?php require_once ("includes/sidebar.php"); ?>
		<!-- end #sidebar (menu) -->
		
		<!-- begin #content -->
		<div id="content" class="content">
			
			<!-- begin breadcrumb (fil d'ariane)-->
			<ol class="breadcrumb pull-right">
				<li><a href="javascript:;">Accueil</a></li>
				<li>Liste de mes épreuves</li>
				<li class="active">Résultats</li>
			</ol>
			<!-- end breadcrumb (fil d'ariane) -->
			
			<!-- begin page-header -->
			<?php
			
				$qEpreuve = "SELECT nomEpreuve FROM r_epreuve WHERE idEpreuve=".$idEpreuve;
				$rEpreuve = $mysqli->query( $qEpreuve );
				$epreuve = mysqli_fetch_array( $rEpreuve );
			?>
			<h1 class="page-header">RÉSULTATS DE L'ÉPREUVE / <?php echo $epreuve['nomEpreuve']; ?></h1>

			<!-- Modal n°2 Aide à l'ajout des résultats -->
			<div class="modal fade" id="ModalHelpResults" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			  <div class="modal-dialog modal-m" role="document">
				<div class="modal-content">
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">Aide à l'ajout des résultats</h4>
				  </div>
				  <div class="modal-body">
					<h5 class="text-info">Merci de respecter ces prérogatives</h5>
					<u>Pré-requis</u>
					<p>
						Le fichier doit être au format <b>txt séparateur tabulation</b><br /><br />

						La 1<sup>ère</sup> ligne doit contenir le nom du classement ex. 10 Km<br />
						La 2<sup>nd</sup> ligne doit comporter, au minimum, les entêtes suivantes : <br />
						<ul>
							<li>PLACE <i style="color:rgb(125,125,125);">(obligatoirement en 1<sup>ère</sup> colonne)</i></li>
							<li>DOSSARD</li>
							<li>NOM</li>
							<li>PRENOM</li>
						<ul>	
						<br />
						Télécharger <a href="/admin/images/modele_ajout_resultats.txt" download>le modèle</a>
					</p>
				  </div>
				</div>
			  </div>
			</div>
			<!-------------------------->
			<!-- end page-header -->
			<!-- begin row -->
			<div class="row">
			    <!-- begin col-6/12 -->
			    <div class="col-md-12 ui-sortable">
			        <!-- begin panel -->
			   		<div class="panel panel-inverse">
                        <div class="panel-heading">
                            <h4 class="panel-title">Ajouter des résultats</h4>
                        </div>
						<div class="panel-body">
							<?php
							
								$rParcours = select_parcours($idEpreuve);
							?>
							<form method="POST" action="resultats_ajout_submit.php" enctype="multipart/form-data">
							<div class="col-md-2">
			                    <div class="form-group">
								    <label for="liste_parcours">Choisissez un parcours</label>
									<select class="form-control" id="liste_parcours" name="parcours">
									<?php
										while( $parcours=mysqli_fetch_assoc( $rParcours) )
										{
											echo '<option value="'.$parcours['idEpreuveParcours'].'">'.$parcours['nomParcours'].'</option>';
										}	
									?>				
									</select>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
							    	<label for="fichier_resultats">Fichier à insérer</label>
							    	<input type="file" id="fichier_resultats" name="fichier_resultats" accept=".txt">
							    	<input type="text" name="idEpreuve" id="idEpreuve" value="<?php echo $idEpreuve; ?>" style="display:none;" >
							  	</div>
							</div>
							<br />
							<div class="col-md-1" style="margin-top:4px;">
								<div class="form-group">
							    	<button type="submit" class="btn btn-primary" name="ajout_resultats">Importer</button>
							  	</div>
							</div>
							</form>
						</div>
					</div>
				</div>
			</div>	
			
			
			<!-- Modal n°2 Aide à l'ajout d'un fichier d'inscription -->
			<div class="modal fade" id="ModalHelpResults" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			  <div class="modal-dialog modal-m" role="document">
				<div class="modal-content">
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">Aide à l'ajout des résultats</h4>
				  </div>
				  <div class="modal-body">
					<h5 class="text-info">Merci de respecter ces prérogatives</h5>
					<u>Pré-requis</u>
					<p>
						Le fichier doit être au format <b>txt séparateur tabulation</b><br /><br />

						La 1<sup>ère</sup> ligne doit contenir le nom du classement ex. 10 Km<br />
						La 2<sup>nd</sup> ligne doit comporter, au minimum, les entêtes suivantes : <br />
						<ul>
							<li>PLACE <i style="color:rgb(125,125,125);">(obligatoirement en 1<sup>ère</sup> colonne)</i></li>
							<li>DOSSARD</li>
							<li>NOM</li>
							<li>PRENOM</li>
						<ul>	
						<br />
						Télécharger <a href="/admin/images/modele_ajout_resultats.txt" download>le modèle</a>
					</p>
				  </div>
				</div>
			  </div>
			</div>
			<!-------------------------->
			<!-- end page-header -->
			<!-- begin row -->
			<div class="row">
			    <!-- begin col-6/12 -->
			    <div class="col-md-12 ui-sortable">
			        <!-- begin panel -->
			   		<div class="panel panel-inverse">
                        <div class="panel-heading">
                            <h4 class="panel-title">Ajouter du fichier Export Chrono ATS-Sport.txt</h4>
                        </div>
						<div class="panel-body">
							<form method="POST" action="insert_fichier_inscriptions-clement.php" enctype="multipart/form-data">
							<div class="col-md-3">
								<div class="form-group">
							    	<label for="fichier_inscrits">Fichier à insérer</label>
							    	<input type="file" id="fichier_inscrits" name="fichier_inscrits" accept=".txt">
							    	<input type="text" name="idEpreuve" id="idEpreuve" value="<?php echo $idEpreuve; ?>" style="display:none;" >
							  	</div>
							</div>
							<br />
							<div class="col-md-1" style="margin-top:4px;">
								<div class="form-group">
							    	<button type="submit" class="btn btn-primary" name="ajout_inscrits">Importer</button>
							  	</div>
							</div>
							</form>
						</div>
					</div>
				</div>
			</div>	
			
			
			<!-- begin row -->
			<div class="row">
			    <!-- begin col-6/12 -->
			    <div class="col-md-12 ui-sortable">
			        <!-- begin panel -->
			   		<div class="panel panel-inverse">
                        <div class="panel-heading">
                            <h4 class="panel-title">Gestionnaire de résultats en direct</h4>
                        </div>
						<div class="panel-body">
							<fieldset>
							<legend>Configurer les contrôles intermédiaires</legend>
							</fieldset>
							<?php
								$lecteurs = array();
								$qLecteur = "SELECT * FROM chrono_lecteur WHERE idEpreuve = ".$idEpreuve." ORDER BY idLecteur";
								$rLecteur = $mysqli->query( $qLecteur );
								while( ( $lect=mysqli_fetch_array( $rLecteur ) ) != FALSE )
								{
									$lecteurs[$lect["idLecteur"]]["lieu"] = $lect["lieu"];
									$lecteurs[$lect["idLecteur"]]["date_min"] = $lect["date_min"];
									$lecteurs[$lect["idLecteur"]]["date_max"] = $lect["date_max"];
									$lecteurs[$lect["idLecteur"]]["rebippage"] = $lect["rebippage"];
									$lecteurs[$lect["idLecteur"]]["distance_depart"] = $lect["distance_depart"];
								}
							?>
							<form method="POST" action="" enctype="multipart/form-data">
							<?php
								for( $id = 100; $id <= 120; $id++ )
								{
									$actif = ( empty( $lecteurs[$id] ) ? false : true );
									echo '
										<div class="row">
											<div class="col-md-2">
											    <div class="form-check form-check-inline">
													<input type="checkbox" data-render="switchery" data-theme="blue" data-switchery="true" id="lecteur-'.$id.'" name="lecteur[]" value="'.$id.'" '.( $actif ? 'checked':"" ).' style="margin-top:12px;"> '.$id.'
												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<input type="text" name="lieu['.$id.']" class="form-control" value="'.( $actif ? $lecteurs[$id]['lieu']:"" ).'" placeholder="Lieu" />
												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<input type="text" name="date_min['.$id.']" class="form-control date_lecteur" value="'.( $actif ? date( "d-m-Y H:i:s", strtotime( $lecteurs[$id]['date_min'] ) ):"" ).'" placeholder="Date début (jj-mm-aaaa hh:ii:ss)" pattern="[0-9][0-9]-[0-9][0-9]-[0-9][0-9][0-9][0-9]\s[0-9][0-9]:[0-9][0-9]:[0-9][0-9]" />

												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<input type="text" name="date_max['.$id.']" class="form-control date_lecteur" value="'.( $actif ? date( "d-m-Y H:i:s", strtotime( $lecteurs[$id]['date_max'] ) ):"" ).'" placeholder="Date fin (jj-mm-aaaa hh:ii:ss)" pattern="[0-9][0-9]-[0-9][0-9]-[0-9][0-9][0-9][0-9]\s[0-9][0-9]:[0-9][0-9]:[0-9][0-9]" />
												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<input type="text" name="rebip['.$id.']" class="form-control" value="'.( $actif ? $lecteurs[$id]['rebippage']:"" ).'" placeholder="Rebippage (secondes)" />
												</div>
											</div>
											<div class="col-md-2">
												<div class="form-group">
													<input type="text" name="distance_depart['.$id.']" class="form-control" value="'.( $actif ? $lecteurs[$id]['distance_depart']:"" ).'" placeholder="Distance au départ" />
												</div>
											</div>
										</div>';
								}	
							?>	
							<div class="row">
								<div class="col-md-2">
									<div class="form-group">
										<button type="submit" class="btn btn-primary" name="ajout_lecteur">Valider</button>
									</div>
								</div>
							</div>			
							</form>
						</div>
						<div class="panel-body">
							<fieldset>
								<legend>Importer des temps de passage</legend>
							</fieldset>
							<?php
								$qLieux = "SELECT * FROM chrono_lecteur WHERE idEpreuve=".$idEpreuve;
								$rLieux = $mysqli->query( $qLieux );
							?>
							<form method="POST" action="" enctype="multipart/form-data">
							<div class="col-md-2">
			                    <div class="form-group">
								    <label for="liste_lieux">Choisissez un lieu</label>
									<select class="form-control" id="liste_lieux" name="lieux">
										<option value="0-ARRIVEE">ARRIVEE</option>
									<?php
										while( ( $lieux=mysqli_fetch_array( $rLieux ) ) != FALSE )
										{
											echo '<option value="'.$lieux['idLecteur'].'-'.$lieux['lieu'].'">'.$lieux['lieu'].'</option>';
										}	
									?>				
									</select>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
							    	<label for="fichier">Fichier à insérer</label>
							    	<input type="file" id="fichier" name="fichier" accept=".txt">
							  	</div>
							</div>
							<br />
							<div class="col-md-1" style="margin-top:4px;">
								<div class="form-group">
							    	<button type="submit" class="btn btn-primary" name="ajout_temps">Importer</button>
							  	</div>
							</div>
							</form>
						</div>
						<div class="panel-body">
							<div class="col-md-6">
								<?php
									if( isset( $retour ) ) echo $retour;
								?>
							</div>
						</div>
						<div class="panel-body">
							<fieldset>
								<legend>Supprimer des temps de passage</legend>
							</fieldset>
							<?php
								$qDelLieux = "SELECT count(lieu) as nb_passage_par_lieu, count(distinct(dossard)) as nb_dossard, lieu FROM chrono_resultats WHERE idEpreuve=".$idEpreuve." GROUP BY lieu";
								$rDelLieux = $mysqli->query( $qDelLieux );
							?>
							<div class="col-md-6">
			                    <div class="form-group">
									<?php
										while( ( $delLieux=mysqli_fetch_array( $rDelLieux ) ) != FALSE )
										{
											$l = str_replace( " ", "", $delLieux['lieu'] );
											echo "<p><form method='post' action='' id='".$l."'>
											<input type='text' name='supprime_lieu' value='".$delLieux['lieu']."' style='display:none;' />
											<span class='glyphicon glyphicon-trash' aria-hidden='true' style='cursor:pointer;' onClick='submitForm(\"".$l."\")'></span>
											".$delLieux['lieu']." - ".$delLieux['nb_dossard']." coureurs</form></p>";
										}	
									?>				
								</div>
							</div>
							<br />
						</div>
						<div class="panel-body">
							<fieldset>
								<legend>Vagues de départ</legend>
							</fieldset>
							<?php
								$qVagues = "SELECT * FROM chrono_vague WHERE idEpreuve=".$idEpreuve;
								$rVagues = $mysqli->query( $qVagues );
							?>
							<form method="POST" action="" enctype="multipart/form-data" class="form-inline">
							<div class="col-md-2">
			                    <div class="form-group">
									Vague n° <select class="form-control" id="vague" name="vague">
									<?php
										for( $i=1; $i<=12; $i++ )
										{
											echo '<option value="'.$i.'">'.$i.'</option>';
										}	
									?>				
									</select>
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<input type="text" name="date_vague" class="form-control date_vague" id="date_vague" placeholder="jj-mm-aaaa hh:mm:ss" pattern="[0-9][0-9]-[0-9][0-9]-[0-9][0-9][0-9][0-9]\s[0-9][0-9]:[0-9][0-9]:[0-9][0-9]" required />
								</div>
							</div>
							<div class="col-md-1">
								<div class="form-group">
							    	<button type="submit" class="btn btn-primary" name="ajout_vague">Valider</button>
							  	</div>
							</div>
							</form>
						</div>
						<div class="panel-body">
							<div class="col-md-3">
								<?php
									if( isset( $retour_vague ) ) echo $retour_vague;
								?>
							</div>
						</div>
					</div>
                    <!-- end panel -->
			    </div>
			</div>		
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
	<!-- ================== BEGIN BASE JS ================== -->
		<?php require_once ("includes/footer_js_base.php"); ?>
		<script src="assets/plugins/datetimepicker-master/jquery.datetimepicker.js" type="text/javascript"></script>
		<script src="assets/js/form-slider-switcher.demo.js"></script>
		<script src="assets/plugins/switchery/switchery.min.js"></script>
	<!-- ================== END BASE JS ================== -->
	
	<!-- ================== BEGIN PAGE LEVEL JS ================== -->

	<script>
		function submitForm( id )
		{
			$('#'+id).submit();
		}

		$(document).ready(function() {
			App.init();
			FormSliderSwitcher.init("input[id*='lecteur-']");
			$('#date_vague, .date_lecteur').datetimepicker({
					step:15,
					format:'d-m-Y H:i:s'
			});
		});
	</script>

	<!-- ================== END PAGE LEVEL JS ================== -->

</body>
</html>