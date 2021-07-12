<?php
$site = "https://www.ats-sport.com/temp/";
echo $site.'includes/includes.php';
require_once($site.'includes/includes.php');   
require_once($site.'includes/functions.php');
global $mysqli;
ini_set("display_errors", 0);
error_reporting(E_ALL);
$url_fichier = $site.'admin/fichiers_epreuves/';
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
	<?php
		$css = '<link href="'.$site.'assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
			<link href="'.$site.'assets/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
			<link href="'.$site.'assets/css/animate.min.css" rel="stylesheet" />
			<link href="'.$site.'assets/css/style_c.css" rel="stylesheet" />
			<link href="'.$site.'assets/css/style-responsive.css" rel="stylesheet" />
			<link href="'.$site.'assets/css/theme/blue.css" id="theme" rel="stylesheet" />
			<link href="'.$site.'assets/css/carte.css" rel="stylesheet" />';
		echo $css;
	?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
	<!-- ================== END BASE CSS STYLE ================== -->

	<!-- ================== BEGIN PAGE LEVEL STYLE ================== -->
	<!-- ================== END PAGE LEVEL STYLE ================== -->
</head>
<body data-spy="scroll" data-target="#header-navbar" data-offset="51">
	
    <!-- begin #page-container -->
    <div id="page-container">
        
        <!-- Header-->
		<?php include( $site.'header.php' ); ?>

		<div class="col-md-12 col-sm-12" style="margin-top:75px;"><br><br></div>

		<div class="row " style="margin-top:100px;">
			<div class="col-sm-1 col-md-1">
			</div>
    
			<!-- begin #carte -->
			<div id="carte" class="content col-md-7 col-sm-7" data-scrollview="true">
				<!-- begin container -->
				<div id="mapid"></div>
				<!-- begin js idEpreuve info -->
				<div id='idEpreuve' style='display: none;'></div>
			</div>

			<div class="col-sm-1 col-md-1">
			</div>

			<div class="col-md-4 col-sm-3">
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
							<?php
				                echo '<img src="'.$site.'assets/img/quote-bg.jpg" alt="Quote" />';
							?>
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
        <?php include( $side.'footer.php' ) ?>

    </div>
    <!-- end page-container -->

    <!-- ================== BEGIN BASE JS ================== -->
	<?php
	$js = '<script src="'.$site.'assets/plugins/jquery/jquery-1.9.1.min.js"></script>
		<script src="'.$site.'assets/plugins/jquery/jquery-migrate-1.1.0.min.js"></script>
		<script src="'.$site.'assets/plugins/jquery-ui/ui/minified/jquery-ui.min.js"></script>
		<script src="'.$site.'assets/plugins/bootstrap/js/bootstrap.min.js"></script>
		<script src="'.$site.'assets/plugins/isotope/jquery.isotope.min.js"></script>
		<script src="'.$site.'assets/plugins/lightbox/js/lightbox-2.6.min.js"></script>
		<script type="text/javascript" src="'.$site.'2017/js/jquery.cycle.all.js"></script>
		<!-- ================== LEAFLET ================== -->
		<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
		<script src="'.$site.'assets/js/carte.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/@turf/turf@5/turf.min.js"></script>
		<!--[if lt IE 9]>
			<script src="'.$site.'assets/crossbrowserjs/html5shiv.js"></script>
			<script src="'.$site.'assets/crossbrowserjs/respond.min.js"></script>
			<script src="'.$site.'assets/crossbrowserjs/excanvas.min.js"></script>
		<![endif]-->
		<script src="'.$site.'assets/plugins/jquery-cookie/jquery.cookie.js"></script>
		<script src="'.$site.'assets/plugins/scrollMonitor/scrollMonitor.js"></script>
		<script src="'.$site.'assets/js/apps.js"></script>';
	echo $js;
	?>
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

	<script>
		var url = window.location.href;
		var urlidEpreuve = url.split("/");
		idEpreuve = urlidEpreuve[4];
		console.log('idEpreuve : '+idEpreuve);
		var div = document.getElementById('idEpreuve');
		div.textContent = idEpreuve;
		if(document.readyState == "interactive"){
			initCarte();
		}
	</script>

	<!-- <?php
		//Si pas d'idEpreuve, on affiche une carte vide
		if (!isset($_GET['idEpreuve'])){
			header("HTTP/1.0 400 Bad Request");
			// Ne pas afficher de carte ou afficher une carte par défaut
			?>
			<script>
				$(document).ready(function() {
					carteDeBase();
				});
			</script><?php
		} else if (!ctype_digit($_GET['idEpreuve'])){		
			header("HTTP/1.0 404 Not Found");
			exit;
		} else {
			$idEpreuve = ($_GET['idEpreuve']);
			// Passage du nom de l'épreuve vers javascript
			echo '<div id="idEpreuve" style="display: none;">';
				echo htmlspecialchars($idEpreuve);
			echo '</div>';?>
			<script>
				$(document).ready(function() {
					initCarte();
				});
			</script><?php
			}
		?> -->

</body>
</html>