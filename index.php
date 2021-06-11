<?php

require_once("includes/includes.php");   
require_once("includes/functions.php");
global $mysqli;
ini_set("display_errors", 0);
error_reporting(E_ALL);
$url_fichier = "admin/fichiers_epreuves/";
$evenements = evenements_a_venir( 8 );
$resultats = derniers_resultats( 8 );

if ($_GET['act']=='disconnect') {
	session_start();
	session_destroy();
	session_unset();
	unset($_SESSION);
}

?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="fr">
<!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<title>ATS-SPORT | Une seule application pour toutes vos inscriptions</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<meta content="spécialiste du dossard, impression de dossard, dossard personnalisé, location de solution de chronométrage, contact chronométreur, inscriptions en ligne, dossards, course à pied, trail, cyclisme, cyclosportive, vtt, triathlon, duathlon" name="description" />
	<meta content="" name="author" />
	
	<!-- ================== BEGIN BASE CSS STYLE ================== -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
	<link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
	<link href="assets/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
	<link href="assets/css/animate.min.css" rel="stylesheet" />
	<link href="assets/css/style_c.css" rel="stylesheet" />
	<link href="assets/css/style-responsive.css" rel="stylesheet" />
	<link href="assets/css/theme/blue.css" id="theme" rel="stylesheet" />
	<!-- ================== END BASE CSS STYLE ================== -->

	<!-- ================== BEGIN PAGE LEVEL STYLE ================== -->

	<!-- ================== END PAGE LEVEL STYLE ================== -->
	<!-- ================== BEGIN BASE JS ================== -->
	<!-- ================== END BASE JS ================== -->
</head>
<body data-spy="scroll" data-target="#header-navbar" data-offset="51">
    <!-- begin #page-container -->
    <div id="page-container">
        
        <!-- Header-->
		<?php include( 'header.php' ); ?>

		<div class="col-md-12 col-sm-12" style="margin-top:75px;"><br><br></div>


		<div class="row">
			<div class="col-md-1 col-sm-1">
			</div>

		    <div class="col-md-8 col-sm-8">
		        <div id="sp90" style="margin:auto;">
				<?php 
	$query  = "SELECT label, url_image, url_lien, ordre  ";
	$query .= "FROM r_bannieres ";
	$query .=" WHERE type = 'banniere_page_accueil'";
	$query .=" AND NOW() BETWEEN dateDebut AND dateFin ";
	$query .=" AND active = 'oui' ";
	$query .= " ORDER BY ordre ASC;";
	//echo $query;
	$result = $mysqli->query($query);
					
					while (($row=mysqli_fetch_array($result)) != FALSE)
					{	
						if (!empty($row['url_lien']))
						{
							echo '<li><a href="'.$row['url_lien'].'" target="_blank"> <img title="'.$row['label'].'" alt="'.$row['label'].'"	src="images/bannieres/'.$row['url_image'].'" class="img-responsive" ></a></li>';
						}
						else
						{
							echo '<li><img title="'.$row['label'].'"	alt="'.$row['label'].'"	 	src="images/bannieres/'.$row['url_image'].'" class="img-responsive" ></li>';						
						}
					}
				
				
				
				
				
				
				?>							
					</div>
			      			
		    </div>
		    <div class="col-md-3 col-sm-3">
		    	<!-- begin #facebook -->
					<div id="fb-root" ></div>
						<script>(function(d, s, id) {
	 						var js, fjs = d.getElementsByTagName(s)[0];
 								if (d.getElementById(id)) return;
 								js = d.createElement(s); js.id = id;
 								js.src = 'https://connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v2.11';
 								fjs.parentNode.insertBefore(js, fjs);
								}(document, 'script', 'facebook-jssdk'));
						</script>
					<div class="fb-page img-responsive"  data-href="https://www.facebook.com/ats.sport" 
						data-hide-cover="false" data-show-facepile="true">
							<blockquote class="fb-xfbml-parse-ignore" cite="https://www.facebook.com/ats.sport">
								<a href="https://www.facebook.com/ats.sport">ATS-Sport</a>
							</blockquote>
					</div>
			</div>
		</div>
		    
		<!--Ajout d'un bandeau Ajouter votre épreuve -->

		<div class="col-md-12 col-sm-12">
			<div class="alert alert-dark" role="alert">
				<p class="text-center"><a href = "new_event.php" >Cliquez ici, pour ajouter votre épreuve au calendrier</a></p> 
			</div>
		</div>

        <!-- begin #epreuves -->
        <div id="epreuves" class="content" data-scrollview="true">
            <!-- begin container -->
            <div class="container" data-animation="true" data-animation-type="fadeInDown">
            	<div class="row">
                	<div class="col-md-6 col-sm-6">
                		<h2 class="content-title">Vos prochaines courses</h2>
                		<?php
                		foreach( $evenements as $evenement )
                		{
	                		$ev  = '<div class="media">';
							$ev .= '	<div class="media-left">';
							$ev .= '		<a href="epreuve.php?id_epreuve='.$evenement['idEpreuve'].'"><img class="media-object" style="width:100px;" src="'.$url_fichier.( $evenement['nomFichier'] ? $evenement['nomFichier'] : "pasdephoto.jpg" ).'" title="'.$evenement['nomEpreuve'].'" alt="'.$evenement['nomEpreuve'].'"></a>';
							$ev .= '	</div>';
							$ev .= '	<div class="media-body">';
							$ev .= '		<h4 class="media-heading"><a href="epreuve.php?id_epreuve='.$evenement['idEpreuve'].'" style="text-decoration:none;color:#242A30;">'.$evenement['nomEpreuve'].'</a>';
							if ($evenement['Live']=='oui'){
								$ev.="<a  href='./pageLive/".$evenement['idEpreuve']."/Live-".$evenement['nomEpreuve']."-accueil'>| <span class='textLive'>Live</span></a>";
							}
							$ev.='</h4>';
							$ev .= '		<h6 class="media-heading">'.date( "d/m/Y" ,strtotime( $evenement['dateEpreuve'] ) ).'</h6>';
							$ev .= '		<h6 class="media-heading">'.$evenement['villeEpreuve'].' ('.$evenement['departementEpreuve'].')</h6>';
							if( date( "Y-m-d" ) >= $evenement['dateEpreuve'] && date( "Y-m-d" ) <= $evenement['dateFinEpreuve'] && $evenement['live'] )
							    	$ev .= '<h6 class="media-heading"><a href="https://www.ats-sport.com/liveResults/Resultats/direct/'.$evenement['idEpreuve'].'" target=_blank>Cliquez pour voir le direct</a> <img src="images/en_direct.gif" alt="En direct" title="En direct" style="width:12px;text-decoration:blink;" /></h6>';
							else
								$ev .= '		<h6 class="media-heading"><a href="epreuve.php?id_epreuve='.$evenement['idEpreuve'].'">cliquez pour + d\'infos</a></h6>';
							$ev .= '	</div>';
							$ev .= '</div>';
							echo $ev;
						}
						?>
                	</div>
	               	<div class="col-md-6 col-sm-12">
	              		<h2 class="content-title">Vos derniers résultats</h2>
	              		<?php
                		foreach( $resultats as $resultat )
                		{
	                		echo '<div class="media">
								<div class="media-left">
									<a href="resultats.php?id_epreuve='.$resultat['idEpreuve'].'"><img class="media-object" style="width:100px;" src="'.$url_fichier.( $resultat['nomFichier'] ? $resultat['nomFichier'] : "pasdephoto.jpg" ).'" title="'.$resultat['nomEpreuve'].'" alt="'.$resultat['nomEpreuve'].'"></a>
								</div>
							 	<div class="media-body">
							    	<h4 class="media-heading"><a href="resultats.php?id_epreuve='.$resultat['idEpreuve'].'" style="text-decoration:none;color:#242A30;">'.$resultat['nomEpreuve'].'</a></h4>
							    	<h6 class="media-heading">'.date( "d/m/Y" ,strtotime( $resultat['dateEpreuve'] ) ).'</h6>
							    	<h6 class="media-heading">'.$resultat['nb'].' coureurs classés</h6>
							    	<!--<h6 class="media-heading"><a href="https://www.ats-sport.com/liveResults/Resultats/direct/'.$resultat['idEpreuve'].'" target=_blank>Cliquez pour voir les résultats</a></h6>-->
							  		<h6 class="media-heading"><a href="resultats.php?id_epreuve='.$resultat['idEpreuve'].'" style="text-decoration:none;">Cliquez pour voir les résultats</a></h6>
							  	</div>
							</div>';
						}
						?>
	               	</div>
                </div>
            </div>
        </div>
        <!-- end epreuves -->

         <!-- begin #label -->
        <div id="label" class="content" data-scrollview="true">
            <!-- begin container -->
            <div class="container-fluid" data-animation="true" data-animation-type="fadeInDown">
                <!-- begin row -->
                <div class="row">
                    <div class="col-md-12 col-sm-12 label">
                    	<img src="images/bann_label.jpg" alt="Label Fédération Française de Triathlon - Fédération Française d'Athlétisme" style="width:50%"/>
					</div>
				</div>
			</div>
		</div>
		<!-- end label -->

        <!-- begin #bouton -->
        <div id="bouton" class="content" data-scrollview="true">
            <!-- begin container -->
            <div class="container-fluid" data-animation="true" data-animation-type="fadeInDown">
                <!-- begin row -->
                <div class="row">
                    <div class="col-md-12 col-sm-12 bouton">
				       <a href="newsletter.php" class="btn btn-lg btn-info">
							<i class="fa fa-envelope fa-2x pull-left"></i>
							S'abonner<br> à nos newsletters<br>
						</a>
						<a href="newsletter.php" class="btn btn-lg btn-success">
							<i class="fa fa-trophy fa-2x pull-left"></i>
							Recevoir<br> les résultats des courses<br>
						</a>
						<a href="recrutement.php" class="btn btn-lg btn-inverse">
							<i class="fa fa-users fa-2x pull-left"></i>
							Devenir<br> bénévole / Chronométreur<br>
						</a>
					</div>
				</div>
			</div>
		</div>
		<!-- end bouton -->

        <!-- begin #accroche -->
        <div id="accroche" class="content" data-scrollview="true">
            <!-- begin container -->
            <div class="container-fluid" data-animation="true" data-animation-type="fadeInDown">
                <!-- begin row -->
                <div class="row">
                    <div class="col-md-12 col-sm-12">
             			<!-- begin #quote -->
				        <div id="quote" class="content bg-black-darker has-bg" data-scrollview="true">
				            <!-- begin content-bg -->
				            <div class="content-bg">
				                <img src="assets/img/quote-bg.jpg" alt="Quote" />
				            </div>
				            <!-- end content-bg -->
				            <!-- begin container -->
				            <div class="container-fluid" data-animation="true" data-animation-type="fadeInLeft">
				                <!-- begin row -->
				                <div class="row">
				                    <!-- begin col-12 -->
				                    <div class="col-md-12 quote">
				                        <i class="fa fa-quote-left"></i> Chronométreur des Championnats du Monde cycliste <br />
				                        <span class="text-theme">UCI Gran Fondo</span> 2017 à Albi !  
				                        <i class="fa fa-quote-right"></i>
				                        <small>Chronométrage, inscriptions en ligne, dossards</small>
				                    </div>
				                    <!-- end col-12 -->
				                </div>
				                <!-- end row -->
				            </div>
				            <!-- end container -->
				        </div>
				        <!-- end #quote -->
                    </div>
				</div>
				<!-- end row -->
			</div>
		</div>

        <!-- footer -->
        <?php include( 'footer.php' ) ?>

    </div>
    <!-- end page-container -->
	
	<!-- ================== BEGIN BASE JS ================== -->
	<script src="assets/plugins/jquery/jquery-1.9.1.min.js"></script>
	<script src="assets/plugins/jquery/jquery-migrate-1.1.0.min.js"></script>
	<script src="assets/plugins/jquery-ui/ui/minified/jquery-ui.min.js"></script>
	<script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
	<script src="assets/plugins/isotope/jquery.isotope.min.js"></script>
  	<script src="assets/plugins/lightbox/js/lightbox-2.6.min.js"></script>
  	<script type="text/javascript" src="2017/js/jquery.cycle.all.js"></script>

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
        $('#sp90').cycle({ 
            fx:     'scrollLeft', 
            timeout: 5000, 
            before:  onBefore, 
            after:   onAfter
        });
        function onBefore() { 
            $('#output').html("Scrolling image:<br>" + this.src); 
        } 
        function onAfter() { 
            $('#output').html("Scroll complete for:<br>" + this.src).append('<h3>' + this.alt + '</h3>'); 
        } 
	
		$(document).ready(function() {
		    App.init();
		});
		
	</script>

</body>
</html>