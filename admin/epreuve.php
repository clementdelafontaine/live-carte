<?php 

require_once("includes/includes.php");   
require_once("includes/functions.php");
global $mysqli;
$id_epreuve = $_GET['id_epreuve'];
$red_epreuve = check_epreuve_redirection($id_epreuve);
$url_fichier = "admin/fichiers_epreuves/";
$inscriptionLigne=extract_champ_epreuve('paiement_cb',$id_epreuve);
$nsi=extract_champ_epreuve('nsi',$id_epreuve);
// Compteur de clics
$clic = "UPDATE r_epreuve SET clics = (clics+1) WHERE idEpreuve='".$id_epreuve."'";
$mysqli->query($clic);

$query  = "SELECT * ";
$query .= "FROM r_epreuve";
$query .=" INNER JOIN r_typeepreuve ON r_epreuve.idTypeEpreuve = r_typeepreuve.idTypeEpreuve";
$query .=" WHERE idEpreuve = ".$id_epreuve;
$result = $mysqli->query($query);
$data_epreuve=mysqli_fetch_array($result);
		
$query  = "SELECT * ";
$query .= "FROM r_epreuvefichier";
$query .=" WHERE idEpreuve = ".$id_epreuve." AND type = 'photo_epreuve' ORDER BY idEpreuveFichier DESC LIMIT 1";
$result = $mysqli->query($query);
$photo_epreuve=mysqli_fetch_array($result);
		
$query  = "SELECT * ";
$query .= "FROM r_epreuvefichier";
$query .=" WHERE idEpreuve = ".$id_epreuve." AND type = 'docs_epreuve'";
$query .=" ORDER by date ASC";
$result_docs = $mysqli->query($query);
$num_docs = mysqli_num_rows($result_docs);

$query  = "SELECT * ";
$query .= "FROM r_epreuveparcours";
$query .=" INNER JOIN r_typeparcours ON r_epreuveparcours.idTypeParcours = r_typeparcours.idTypeParcours";
$query .=" WHERE idEpreuve = ".$id_epreuve;
$query .=" ORDER by ordre_affichage ASC";
$result_parcours = $mysqli->query($query);
		
$query  = "SELECT * ";
$query .= "FROM r_epreuveparcours";
$query .=" INNER JOIN r_epreuveparcourstarif ON r_epreuveparcours.idEpreuveParcours = r_epreuveparcourstarif.idEpreuveParcours";
$query .=" INNER JOIN r_epreuveparcourstarifpromo ON r_epreuveparcours.idEpreuveParcours = r_epreuveparcourstarifpromo.idEpreuveParcours";
$query .=" INNER JOIN r_epreuvefichier ON r_epreuveparcours.idEpreuveParcours = r_epreuvefichier.idEpreuveParcours";
$query .=" WHERE r_epreuveparcours.idEpreuve = ".$id_epreuve;
//$result_parcours = $mysqli->query($query);
//$data_parcours=mysqli_fetch_array($result);

$query  = "SELECT * ";
$query .= "FROM `r_inscriptionepreuveinternaute` ";
$query .= " WHERE `idEpreuve` = ".$id_epreuve." AND `paiement_type` IN ('ORG','CB','CHQ','AUTRE','GRATUIT','SUR_PLACE') ORDER BY paiement_date DESC LIMIT 1";

$result = $mysqli->query($query);
$inscrit_epreuve=mysqli_fetch_array($result);
		
function tarifs($id_parcours)
{
global $mysqli;	
	$query  = "SELECT * ";
	$query .= "FROM r_epreuveparcourstarif";
	$query .=" WHERE idEpreuveParcours = ".$id_parcours." AND dateFinTarif >= '".date("Y-m-d H:i:s")."' ";
	$query .=" ORDER by dateDebutTarif ASC";
	$result_tarifs = $mysqli->query($query);
	return $result_tarifs;
}

function fichiers_parcours($id_parcours)
{
global $mysqli;	
	$query  = "SELECT * ";
	$query .= "FROM r_epreuvefichier";
	$query .=" WHERE idEpreuveParcours = ".$id_parcours." AND type = 'docs_parcours'";
	$query .=" ORDER by nom_fichier_affichage ASC";
	$result_fichiers_parcours = $mysqli->query($query);
	if (mysqli_num_rows($result_fichiers_parcours) > 0) return $result_fichiers_parcours; else return '';
}

$nom_epreuve = extract_champ_epreuve("nomEpreuve",$id_epreuve);	

$inscription_en_cours = extract_champ_epreuve ('idEpreuve',$id_epreuve,"AND dateFinInscription > NOW()");

$info_epreuve_perso = info_epreuve_perso($id_epreuve);

$ilyadesinscrits = 0;
if ($inscrit_epreuve['idInternaute']!='')
{
	$ilyadesinscrits=1;
	
}

?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="fr">
<!--<![endif]-->
<head>

	<meta charset="utf-8" />
	<title>ATS-SPORT | <?php echo $nom_epreuve; ?></title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<meta content="chronométrage, chronométreur, inscriptions en ligne, dossards, course à pied, trail, cyclisme, cyclosportive, vtt, triathlon, duathlon" name="description" />
	<meta content="" name="author" />
	
	<!-- ================== BEGIN BASE CSS STYLE ================== -->
	<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
	<link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
	<link href="assets/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
	<link href="assets/css/animate.min.css" rel="stylesheet" />
	<link href="assets/css/style_c.css" rel="stylesheet" />
	<link href="assets/css/style-responsive.css" rel="stylesheet" />
	<link href="assets/css/theme/blue.css" id="theme" rel="stylesheet" />
	<!-- ================== END BASE CSS STYLE ================== -->
	<!-- ================== BEGIN PAGE LEVEL STYLE ================== -->
  	<link href="assets/plugins/isotope/isotope.css" rel="stylesheet" />
  	<link href="assets/plugins/lightbox/css/lightbox.css" rel="stylesheet" />
	<link href="assets/plugins/bootstrap-datepicker/css/datepicker.css" rel="stylesheet" />
	<link href="assets/plugins/bootstrap-datepicker/css/datepicker3.css" rel="stylesheet" />
	<link href="assets/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet" />
	<link href="assets/plugins/datetimepicker-master/jquery.datetimepicker.css" rel="stylesheet" />
	<!-- ================== END PAGE LEVEL STYLE ================== -->
	<!-- ================== BEGIN BASE JS ================== -->

	<!--<script src="assets/plugins/pace/pace.min.js"></script> /-->
	<!-- ================== END BASE JS ================== -->

</head>
<body data-spy="scroll" data-target="#header-navbar" data-offset="51">

    <!-- begin #page-container -->
    <div id="page-container" class="">
        <!-- begin #header -->
			<?php
			if( !$red_epreuve ) 
			{ 
				include( 'header.php' );
			} else { 
			?>
			    <div id="header" class="header navbar navbar-transparent navbar-fixed-top">
          		<!-- begin container -->
					<div style="background: #1ea0e6 none repeat scroll 0 0;margin: 0 auto;padding: 20px 40px 35px;position: relative;text-align: center;">
						<div>
							<span>
								<h1 class="text-default">Cette épreuve est terminée ! </h1></br><h3>veuillez cliquer sur le bouton ci-dessous afin d'être redirigé sur l'épreuve en cours.</h3>
							</span>
						</div>
						<a href="epreuve.php?id_epreuve=<?php echo $red_epreuve['id_epreuve']; ?>" id="add-sticky-with-callbacks" class="btn btn-danger btn-lg m-r-5" style="font-size:28px"><?php echo $red_epreuve['nom']; ?></a>
					</div>
				</div>
			<?php } ?>
            <!-- end container -->
        <!-- end #header -->
        
        <!-- begin #home -->
        <div id="home" class="content has-bg home">
            <!-- begin content-bg -->
            <div class="content-bg">
			<?php if (isset($info_epreuve_perso['url_image'])) { ?>
				<img src="<?php echo $info_epreuve_perso['url_image']; ?>" alt="Home" />
			<?php } else { ?>
                <img src="assets/img/home-bg4.jpg" alt="Home" />
			<?php } ?>
            </div>
            <!-- end content-bg -->
            <!-- begin container -->
            <div class="container home-content">
                <h2><?php echo $data_epreuve['nomEpreuve']; ?></h2>
                <p>
				<?php
				if($photo_epreuve['nom_fichier'] <> '') 
				{
				?>
					<a href="<?php echo $url_fichier.$photo_epreuve['nom_fichier']; ?>" data-lightbox="image_epreuve" ><img src="<?php echo $url_fichier.$photo_epreuve['nom_fichier']; ?>" class="img_epreuve" id="img-home" alt="<?php echo $data_epreuve['nomEpreuve']; ?>"></a>
				<?php
				}
				//Affiche par défaut Course pédestre
				elseif($data_epreuve['idTypeEpreuve'] == '3' OR $data_epreuve['idTypeEpreuve'] == '1')
				{
				?>
					<a href="" data-lightbox="image_epreuve" ><img src="https://www.ats-sport.com/images/defaut_image_epreuve_occitanie.png" class="img_epreuve" id="img-home" alt="<?php echo $data_epreuve['nomEpreuve']; ?>"></a>
				<?php	
				}
				?>
				</p>
            
			</div>
            <!-- end container -->
        </div>
        <!-- end #home -->
        
        <!-- begin #about -->
        <div id="about" class="content" data-scrollview="true">
            <!-- begin container -->
            <div class="container" data-animation="true" data-animation-type="fadeInDown">
                <h2 class="content-title"><?php echo $data_epreuve['nomEpreuve']; ?></h2>
				<h4 class="content-desc"> Epreuve de <span class="title text-theme"><?php echo $data_epreuve['nomTypeEpreuve']; ?></span>, organisée par <span class="title text-theme"><?php echo $data_epreuve['nomStructureLegale']; ?></span>, du <span class="title text-theme"><?php echo date('d/m/Y',strtotime($data_epreuve['dateEpreuve'])); ?></span> au <span class="title text-theme"><?php echo date('d/m/Y',strtotime($data_epreuve['DateFinEpreuve'])); ?></span>
				</br> Lieu : <span class="title text-theme"><?php echo $data_epreuve['ville']." (".$data_epreuve['departement'].")"; ?></span> 
				</br> Condition d'accès : <span class="title text-theme"><?php echo $data_epreuve['sitelieu']; ?> </span>
				</br> Nombre de parcours : <span class="title text-theme"><?php echo $data_epreuve['nombreParcours']; ?> </span>
				</br> Nombre de participants : <span class="title text-theme"><?php echo $data_epreuve['nbParticipantsAttendus']; ?> </span></h4>
                <!-- begin row -->
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <!-- begin about -->
                        <div class="content-title">
                            <h2>Les parcours</h2>
                			<div class="panel-group" id="faq">
                    <!-- begin panel -->
					<?php $cpt=TRUE; ?>
					<?php while (($row=mysqli_fetch_array($result_parcours)) != FALSE)
					{ ?>
                    <div class="panel panel-inverse">
                        <div class="panel-heading">
                            <h2 class="panel-title">
                                <a data-toggle="collapse" href="#parcours-<?php echo $row['idEpreuveParcours']; ?>"><?php echo $row['nomParcours']; ?></a>
                            </h2>
                        </div>
                       <?php if ($cpt==TRUE) { $in = 'in'; $cpt=FALSE;} else { $in = ''; }?>
						<div id="parcours-<?php echo $row['idEpreuveParcours']; ?>" class="panel-collapse collapse<?php echo  $in; ?>">
                            <div class="panel-body">								
								<p>
								<h4>Date et heure : <span class="title text-theme"><?php echo date('d/m/Y H:i',strtotime($row['horaireDepart'])); ?></span> 
								</br>Format <span class="title text-theme"><?php echo  $row['nomTypeParcours']; ?></span> <?php if ($row['relais']==1) { ?>, en <span class="title text-theme">relais</span> <?php } if ($row['age']!=0) { ?>, âge minimum de <span class="title text-theme"><?php echo $row['age'] ; ?> ans </span><?php } ?>
								</h4>
								<?php if(!empty($row['ParcoursDescription'])) { ?><h4><em>Description</em></h4>
                                    <p style="text-align:justify"><?php echo $row['ParcoursDescription']; ?></p>
								<?php	}	
								
									$tab_tarif = array();
									$tab_tarif = tarifs($row['idEpreuveParcours']);
									$somme_tarifs = 0;
		
								?>
												<div class="row">

                    <div class="col-md-8 col-sm-8 milestone-col">
								<h5>Tarifs</h5>
								<div class="email-content">
										<table class="table table-email">
											<tbody>
											<?php while (($row_tarifs=mysqli_fetch_array($tab_tarif)) != FALSE)
											{ $somme_tarifs += $row_tarifs['tarif']; ?>
												<tr>
													<td class="email-select">  <b><?php echo $row_tarifs['desctarif']; ?></b></td>
													<td class="email-sender">  Du <b><?php echo date('d/m/Y à H:i',strtotime($row_tarifs['dateDebutTarif'])); ?></b></td>
													<td class="email-sender">  Au <strong><span class="text-primary"><?php echo date('d/m/Y à H:i',strtotime($row_tarifs['dateFinTarif'])); ?></span></strong></td>
													<td class="email-subject">
														<strong><span class="text-danger"><?php if( $row_tarifs['tarif'] > 0 ) echo $row_tarifs['tarif']." €" ; ?></span></strong>
													</td>
												</tr>
											<?php
											
											}
												if ($somme_tarifs == 0 && $photo_epreuve['nom_fichier'] == '')
												{ 
												?>
												<tr>
													<td colspan="4" class="email-select" align="center">  <a href="mailto:contact@ats-sport.comm?subject=+ d'info sur la course <?php echo $data_epreuve['nomEpreuve']; ?> du <?php echo $data_epreuve['nomEpreuve']; ?> &body=Bonjour,%0D%0A%0D%0AVeuillez prendre en note les éléments suivants:%0D%0A%0D%0A%0D%0APar ailleurs, veuillez me contacter (oui/non) par téléphone ou mail aux coordonnées suivante :%0D%0A%0D%0AMerci ;-))" class="btn btn-warning btn-lg m-l-10 pull-left" target="_blank"> >> Vous êtes l'Organisateur? Cliquez pour diffuser + d'informations <<</td>
												</tr>
												<?php
												}												
												?>
												
												
											</tbody>
										</table>
								</div>
					</div>
								<?php		
								
									$tab_fichiers_parcours= array();
									$tab_fichiers_parcours = fichiers_parcours($row['idEpreuveParcours']);
								if (!empty($tab_fichiers_parcours)) { 
								?>
								
								<div class="col-md-4 col-sm-4 milestone-col">
											<h5></h5>
											<h5>Documents</h5>
											<div class="email-content">
													<table class="table table-email">
														<tbody>
														<?php while (($row_fichiers_parcours=mysqli_fetch_array($tab_fichiers_parcours)) != FALSE)
														{ ?>
															<tr>
																<?php if (substr ($row_fichiers_parcours['nom_fichier'], -3) == 'pdf') { ?>
																<td class="email-select"><i class="fa fa-file-pdf-o"></i></td>
																<?php } else { ?>
																<td class="email-select"><i class="fa fa-file-photo-o"></i></td>
																<?php } ?>
																<td class="email-subject">  <b><?php if (strlen($row_fichiers_parcours['nom_fichier_affichage'])>50) $row_fichiers_parcours['nom_fichier_affichage']=substr($row_fichiers_parcours['nom_fichier_affichage'], 0, 50)."...";  
			
																echo '<a href="'.$url_fichier.$row_fichiers_parcours['nom_fichier'].'"';
																if (substr ($row_fichiers_parcours['nom_fichier'], -3) != 'pdf') { echo 'data-lightbox="gallery-group-'.$row['idEpreuveParcours'].'"'; }
																else { echo 'target="_blank"'; }
																
																echo '>'.$row_fichiers_parcours['nom_fichier_affichage'].'</a>'; ?></b></td>
		
															</tr>
														<?php } ?>
														</tbody>
													</table>
											</div>
								</div>
								<?php } ?>
								</div>
				

                                </p>
								
								<p><?php 
								
									if (($somme_tarifs!=0 ||  $id_epreuve == 6579 || $id_epreuve == 6552 || $id_epreuve == 7218 || $id_epreuve == 7247 || $id_epreuve == 7345) && $inscriptionLigne==1) 
								{ //MAJ Bud pour le festibike parcours à 0 € = bientôt disponible!??
											$url_relais = '';
										//A modifier pour épreuves gratuites
										//echo 'id_epreuve : '.$id_epreuve;
										if ($row['relais'] > 0 ) $url_relais ='_relais';
										$nb_relais_bdd_min = extract_champ_parcours('min_relais',$row['idEpreuveParcours']);
										?>
		
													
										<?php if ($id_epreuve==5745) { ?>
											<a href="inscriptions_cyfac.php?id_epreuve=<?php echo $id_epreuve; ?>&id_parcours=<?php echo $row['idEpreuveParcours']; ?>" class="btn btn-success btn-lg pull-left"><strong>Inscrivez-vous !</strong></a></p>
										<?php } else if ($id_epreuve==6399) { ?>
											<!-- <span class="btn btn-warning btn-lg m-l-10 pull-left">Bientôt disponible !</span> //-->
											<a href="insc.php?id_epreuve=<?php echo $id_epreuve; ?>" class="btn btn-success btn-lg pull-left"><strong>Inscrivez-vous !</strong></a></p>								
										<?php } else { ?>
											
											<?php if ($nb_relais_bdd_min >0) { ?>
												<?php if ($nsi=='non') { ?>		
													<?php for ($rc=$nb_relais_bdd_min;$rc<=$row['relais'];$rc++) { ?>
														<a class="text-white btn btn-success btn-lg pull-left m-r-5 m-b-5" href="inscriptions_relais.php?id_epreuve=<?php echo $id_epreuve; ?>&id_parcours=<?php echo $row['idEpreuveParcours']; ?>&nb_relais=<?php echo $rc; ?>"> J'inscris <?php echo $rc; ?> coureurs</a>
													<?php } ?>
												<?php } else { ?>
												
													<a href="insc.php?id_epreuve=<?php echo $id_epreuve; ?>" class="btn btn-success btn-lg pull-left m-r-10"><strong>Inscrivez-vous !</strong></a></p>	
												
												<?php } ?>
												
											<?php } else { ?>
												<?php if ($nsi=='non') { ?>
													<a href="inscriptions<?php echo  $url_relais; ?>.php?id_epreuve=<?php echo $id_epreuve; ?>&id_parcours=<?php echo $row['idEpreuveParcours']; ?>" class="btn btn-success btn-lg pull-left m-r-10"><strong>Inscrivez-vous !</strong></a></p>
												<?php } else { ?>
												
													<a href="insc.php?id_epreuve=<?php echo $id_epreuve; ?>" class="btn btn-success btn-lg pull-left m-r-10"><strong>Inscrivez-vous !</strong></a></p>												
												<?php }?>
											<?php } ?>											
										<?php } ?>
						  <?php } else 
						  { ?>
									
								<?php if (empty($inscription_en_cours)) 
								{ ?>
									<span class="btn btn-danger btn-lg m-l-10 pull-left">Inscriptions terminées !</span>
								<?php } elseif($photo_epreuve['nom_fichier'] != '')
										{ ?>
									<span class="btn btn-warning btn-lg m-l-10 pull-left">Bientôt disponible !</span>
									<?php } ?>									
							<?php
								}
							
							if ($ilyadesinscrits)
							{
							?>
							
									<a href="liste_des_inscrits.php?id_epreuve=<?php echo $id_epreuve; ?>" class="btn btn-primary btn-lg pull-left" target="_blank"><strong>Liste des inscrits</strong></a>
                            <?php
							}
							?>
							</div>
                        </div>
                    </div>
					<?php 
										
					}
					?>
                    <!-- end panel -->
                    <!-- end panel -->
				</div>           
                        </div>
                        <!-- end about -->
                    </div>
				</div>
				<div class="row">
                    <div class="col-md-12 col-sm-12">
                        <!-- begin about -->
                        <div class="content-title">
                            <h3>Description de l'épreuve</h3>
                            <p style="text-align:justify;">
                                <?php echo $data_epreuve['description']; ?>
                            </p>
                            <p>
                                <?php //echo $data_epreuve['description']; ?>
                            </p>
                        </div>
                        <!-- end about -->
                    </div>
				</div>
                <div class="row">
                    <!-- begin col-4 -->

                    <!-- end col-4 -->
                    <!-- begin col-4 -->
                    <div class="col-md-12 col-sm-12 text-center">
                        <h3>En savoir plus sur l'épreuve</h3>
                        <div>
                            <?php if (!empty($data_epreuve['siteInternet'])) { ?><a href="<?php echo $data_epreuve['siteInternet']; ?>" target="_blank"><img src="images/website-logo.png" style="max-width:100px"></a><?php } ?>
							<?php if (!empty($data_epreuve['siteFacebook'])) { ?></br><h5><a href="<?php echo $data_epreuve['siteFacebook']; ?>" target="_blank"><img src="images/facebook-logo.jpg" style="max-width:100px"></a><?php } ?>
							<?php if (!empty($data_epreuve['siteTwitter'])) { ?> </br><h5><a href="<?php echo $data_epreuve['siteTwitter']; ?>" target="_blank"><img src="images/twitter-logo.png" style="max-width:100px"></a><?php } ?>
							</div>
						</div>
					
                    <!-- end col-4 -->
                    <!-- begin col-4 -->
					<?php if ($num_docs >0) { ?>
                    <div class="col-md-12 col-sm-12 text-center">
                        <h3>Documents de l'épreuve</h3>
				
							<div class="email-content">
									<table class="table table-email">
										<tbody>
																<?php while (($row_docs=mysqli_fetch_array($result_docs)) != FALSE)
									{ ?>
											<tr>
												<?php if (substr ($row['nom_fichier'], -3) == 'pdf') { ?>
												<td class="email-select"><i class="fa fa-file-pdf-o"></i></td>
												<?php } else { ?>
												<td class="email-select"><i class="fa fa-file-photo-o"></i></td>
												<?php } ?>
												<td class="email-subject">
													<?php if (strlen($row_docs['nom_fichier_affichage'])>50) $row_docs['nom_fichier_affichage']=substr($row_docs['nom_fichier_affichage'], 0, 50)."...";  
													
													echo '<a href="'.$url_fichier.$row_docs['nom_fichier'].'"';
													if (substr ($row_docs['nom_fichier'], -3) != 'pdf') { echo 'data-lightbox="gallery-grou-'.$row_docs['idEpreuveParcours'].'"'; }
													else { echo 'target="_blank"'; }
													echo '>'.$row_docs['nom_fichier_affichage'].'</a>';?>
												</td>
											</tr>
										<?php } ?>
										</tbody>
									</table>
							</div>
                        <!-- end skills -->
                    </div>
					<?php } ?>
                    <!-- end col-4 -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end #about -->
    
        <!-- begin #milestone -->
        <div id="milestone" class="content bg-black-darker has-bg" data-scrollview="true">
            <!-- begin content-bg -->
            <div class="content-bg">
                <img src="assets/img/home-1920-500.jpg" alt="Milestone" />
            </div>
        </div>
        <!-- end #milestone -->

        <!-- begin FAQs -->
															<div class="modal" id="TCGU">
														<div class="modal-dialog" style="width:800px">
															<div class="modal-content">
																<div class="modal-header" style="text-align: right;">
																	<a href="#" class="btn btn-primary" data-dismiss="modal">Fermer</a>
																</div>
																<div class="modal-body">
																
																
<div class="widget-content form-container">

    
    <form id="registration_form" class="form-horizontal" enctype="multipart/form-data" novalidate="novalidate" method="post" action="">
                                <fieldset>
                                    <legend class="pull-left width-full">Inscription au parcours xxxxxx</legend>
								<div class="panel panel-inverse panel-with-tabs">
									<div class="panel-heading p-0">
										<div class="panel-heading-btn m-r-10 m-t-10">
										</div>
										<div class="tab-overflow">
											<ul class="nav nav-tabs nav-tabs-inverse">
												<li class="active" id="P1"><a href="#parcours_1" data-toggle="tab" id="name_parcours_1"><?php if (isset($_GET['epre_id'])) echo $f->parc_nom[1]; else echo "Parcours N°1";?></a></li>
												<li id="ajouter_parcours" id="P2"><a href="#" class="add-parcours">Parcours +</a>
											</ul>
										</div>
									</div>
									<div class="tab-content" id="divTab">
										<div class="tab-pane fade active in" id="parcours_1">
											<input type="hidden" id="id_table_parcours[1]" name="id_table_parcours[1]" value="<?php echo $tab_id[1][0]; ?>" />
																				<div class="form-group">
										<label class="col-sm-3 control-label" >Nom</label>
										<div class="col-sm-9">
											<input type="text" class="form-control" name="insc_nom[1]" value="<?php echo $f->nom[1]; ?>" id="insc_nom[1]" maxlength="150" required>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label">Prénom</label>
										<div class="col-sm-9">
											<input type="text" class="form-control" name="insc_prenom[1]" value="<?php echo $f->prenom[1]; ?>" id="insc_nom[1]" maxlength="150" required>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Sexe</label>
										<div class="col-md-9">
											<label class="radio-inline">
												<input type="radio" name="insc_sexe" value="M" checked="">
												Homme
											</label>
											<label class="radio-inline">
												<input type="radio" name="insc_sexe" value="F">
												Femme
											</label>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-3 control-label">Date de naissance</label>
										<div class="col-md-9">
											<div class="input-group">
												<input id="naissance_1" name="naissance[1]" value="<?php echo $f->naissance[1]; ?>" type="text"  class="form-control" />
												<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label">Club</label>
										<div class="col-sm-9">
											<input name="insc_club[1]" id="jquery-autocomplete-club" class="form-control txt-auto" value="<?php echo $f->club; ?>" placeholder="entrez le nom de votre club"/>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label">Email valide </label>
										<div class="col-sm-9">
											<INPUT class="form-control" type="text" name="insc_email[1]" id="insc_email[1]" value="<?php echo $f->email[1]; ?>" maxlength="150">
										</div>
									</div>
									
									<div class="form-group">
										<label class="col-sm-3 control-label">Adresse complète </label>
										<div class="col-sm-9">
											<INPUT class="form-control" type="text" name="insc_adresse[1]" id="insc_adresse[1]" value="<?php echo $f->insc_adresse[1]; ?>" maxlength="150">
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label">Ville</label>
										<div class="col-sm-9">
											<input name="epre_ville" id="jquery-autocomplete-ville" class="form-control txt-auto" value="<?php echo $f->ville; ?>" placeholder="entrez le nom de votre ville"/>
											<input type="hidden" id="epre_departement" name="epre_departement" class="form-control txt-auto" value="<?php echo $f->departement; ?>"/>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3control-label">Pays</label>
										<div class="col-sm-9">
											<input name="insc_pays" id="jquery-autocomplete-pays" class="form-control txt-auto" value="<?php echo $f->ville; ?>" placeholder="entrez le nom de votre ville"/>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label">Téléphone</label>
										<div class="col-sm-9">
											<input type="text" class="form-control" name="insc_telephone[1]" value="<?php echo $f->telephone[1]; ?>" id="insc_telephone[1]" maxlength="150" required>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-12">
											<div class="panel panel-default">
												<div class="panel-body">
													<TEXTAREA class="textarea form-control" id="wysihtml5" placeholder="Informations supplémentaires" name="epre_inscr_contact" cols="10" rows="15"><?php echo $f->inscr_contact; ?></TEXTAREA>
												</div> <!-- End Panel-body /-->
											</div> <!-- End Panel-Default /-->
										</div>
									</div>

										</div>
									</div>
								</div>
								
                                </fieldset>
    <div class="form-actions">
        <input type="submit" value="Etape suivante" class="btn btn-primary" name="submit_pay">
    </div>
    </form></div>
																</div>
																<div class="modal-footer">
																	<a href="#" class="btn btn-primary" data-dismiss="modal">Fermer</a>
																</div>
															</div><!-- /.modal-content -->
														</div><!-- /.modal-dialog -->
													</div>
        
        <!-- begin #footer -->
        <?php include( 'footer.php' ) ?>
        <!-- end #footer -->
    </div>
    <!-- end #page-container -->
	
	<!-- ================== BEGIN BASE JS ================== -->
	<script src="assets/plugins/jquery/jquery-1.9.1.min.js"></script>
	<script src="assets/plugins/jquery/jquery-migrate-1.1.0.min.js"></script>
	<script src="assets/plugins/jquery-ui/ui/minified/jquery-ui.min.js"></script>
	<script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
	<script src="assets/plugins/isotope/jquery.isotope.min.js"></script>
  	<script src="assets/plugins/lightbox/js/lightbox-2.6.min.js"></script>
	<script src="assets/plugins/datetimepicker-master/jquery.datetimepicker.js" type="text/javascript"></script>
	<!--[if lt IE 9]>
		<script src="assets/crossbrowserjs/html5shiv.js"></script>
		<script src="assets/crossbrowserjs/respond.min.js"></script>
		<script src="assets/crossbrowserjs/excanvas.min.js"></script>
	<![endif]-->
	<script src="assets/plugins/jquery-cookie/jquery.cookie.js"></script>
	<script src="assets/plugins/scrollMonitor/scrollMonitor.js"></script>
	<script src="assets/js/apps.js"></script>
	<!-- ================== END BASE JS ================== -->
	
	<script> 
	//Activation date et heure de départ de la course
	$('#naissance_1').datetimepicker({
		format:'d/m/Y',
		lang:'fr',
		timepicker:false
 	});
	
	$(document).ready(function() {
	    App.init();
	});
		
	$('#jquery-autocomplete-ville').autocomplete({
		source : 'ajaxVille.php',
		autoFocus: true,
		minLength: 3,
		dataType: "json",
		select:function(event,ui) {
			$("#epre_departement").val(ui.item.id);
		}
  	});
	</script>
</body>
</html>
