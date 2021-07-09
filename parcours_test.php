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
	<link href="assets/css/carte.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
	<!-- ================== END BASE CSS STYLE ================== -->

	<!-- ================== BEGIN PAGE LEVEL STYLE ================== -->
	<!-- ================== END PAGE LEVEL STYLE ================== -->
</head>
<?php echo '<body data-spy="scroll" data-target="#header-navbar" data-offset="51">'; ?>
	
    <!-- begin #page-container -->
    <div id="page-container">
        
        <!-- Header-->
		<?php include( 'header.php' ); ?>

		<div class="col-md-12 col-sm-12" style="margin-top:75px;"><br><br></div>

		<div class="row " style="margin-top:100px;">
			<div class="col-sm-1 col-md-1">
			</div>
    
			<!-- begin #carte -->
			<div id="carte" class="content col-md-7 col-sm-7" data-scrollview="true">
				<!-- begin container -->
				<div id="mapid"></div>
				<!-- begin js idEpreuve info -->
				<div id='idEpreuve' style="display: none;"></div>
			</div>

			<div class="col-sm-1 col-md-1">
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
	<!-- ================== LEAFLET ================== -->
	<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.5.1/gpx.min.js"></script> -->
    <script src="assets/plugins/leaflet-gpx/gpx.js"></script>
	<script src="assets/js/carte.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/@turf/turf@5/turf.min.js"></script>
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

	<script>
		var url = window.location.href;
		var idEpreuve = url.split("/");
		idEpreuve = idEpreuve[4];
		var div = document.getElementById('idEpreuve');
		div.textContent = idEpreuve;
		if(document.readyState == "complete"){
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