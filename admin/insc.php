<?php




global $mysqli;
require("admin/includes/includes.php");

require_once("includes/secu.php"); 

require('admin/includes/functions.php');

require('admin/includes/functions_insc.php');

		$fp = fopen("logs_insc.txt","a");
		fputs($fp,"DATE : ".date("d/m/Y H-i-s")." : ".$_SERVER['REQUEST_URI']." - IP CLIENTS : ".get_ip()." \n");
		fputs($fp," : ----------- INSC.PHP --------------\n");

/*		
echo "SESSION PANIER :".$_SESSION['panier'];
print_r($_SESSION);
echo "AVANT : SESSION id_unique_paiement : ".$_SESSION['id_unique_paiement'];
echo "xxxxxxxxxxxxxxxx ".print_r($_SESSION['rieis']);
echo "yyyyyyyyyyyyyyy ".print_r($_SESSION['tarifs']);
*/
function gene_code_paiement($car) {
	global $mysqli;
	
	$string = "";
	$chaine = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	srand((double)microtime()*1000000);
	for($i=0; $i<$car; $i++) {
		$string .= $chaine[rand()%strlen($chaine)];
	}
	return $string;
}

function TestDate($date1, $date2) { 
global $mysqli;
	$datetime1 = new DateTime($date1);
	$datetime2 = new DateTime($date2);
	if ($datetime1 > $datetime2) {
		return 'ko'; 
	}

} 

function affichage_annee($id_epreuve,$id_parcours)
{
	global $mysqli;
	$annee_depart = extract_champ_epreuve('dateEpreuve',$id_epreuve);
	$annee = "eee</br>".substr($horaire_depart, 0, 4);
	$age_min = extract_champ_parcours('age',$id_parcours);
	$age_max = extract_champ_parcours('ageLimite',$id_parcours);
	if ($age_min < 18 && $age_max < 18) {
		return "Année ". ($annee_depart-$age_max). " à ". ($annee_depart-$age_min);
	}
	else
	{
		return "Av.  ".($annee_depart-$age_min);
		
	}
}

function ouverture_inscription($id_epreuve)
{
	global $mysqli;
	$dateDebutInscription = extract_champ_epreuve('dateDebutInscription',$id_epreuve);
    //echo "XXXXXX".$dateDebutInscription;
	list($year,$month,$day) = explode('-',$dateDebutInscription);
	$date1 = new DateTime($dateDebutInscription);
	$date2 = new DateTime(date('Y-m-d'));
	if( $date1 > $date2 )  
	{
		$champs=array();
		$champs['annee']= $year;
		$champs['mois']= $month;
		$champs['jour']= $day;
		//print_r($champs);
		return $champs;
	}
}

function fermeture_inscription($id_epreuve)
{
	global $mysqli;
	$inscriptionLigne = extract_champ_epreuve('paiement_cb',$id_epreuve);
    //echo "XXXXXX".$dateDebutInscription;
	return $inscriptionLigne;

}
/********
$dateDebutInscription = extract_champ_epreuve('dateDebutInscription',$_GET['id_epreuve']);
$dateFinInscription = extract_champ_epreuve('dateFinInscription',$_GET['id_epreuve']);
//echo $dateFinInscription;
$test = TestDate(date ('Y-m-d H:m:s'),$dateFinInscription);
if ($test =='ko') header('Location: index.php');
*/
	
//if (strtotime($dateFinInscription)<strtotime(date ('Y-m-d'))) echo "TOTO";

if (empty($_SESSION['id_unique_paiement'])) $_SESSION['id_unique_paiement'] = gene_code_paiement(10);
//echo "APRES : SESSION id_unique_paiement : ".$_SESSION['id_unique_paiement'];					
//echo "AAAAAA :".print_r($_SESSION);
//echo $_SESSION['nb_relais'];
function delete_equipe($idInternauteref, $idInternauteReferent,$id_epreuve)
{
	global $mysqli;	
		$idInternauteref." - ".$idInternauteReferent;
		
		$id_ctrl = extract_champ_r_internautereferent('InscritParidInternaute',$idInternauteReferent);
		$id_ctrl_2 = extract_champ_r_internautereferent('idInternauteref',$idInternauteReferent);
		if ($id_ctrl == $idInternauteref || $id_ctrl_2 == $idInternauteref)
		{
			
			$del_idInscriptionEpreuveInternautes= extract_champ_r_internautereferent('idInscriptionEpreuveInternautes',$idInternauteReferent);
			
			$values = explode('|',$del_idInscriptionEpreuveInternautes);
			foreach ($values as $idInscriptionEpreuveInternaute)
			{
				$id_tmp_epreuve_parcours=extract_champ_epreuve_internaute('idEpreuveParcours',$idInscriptionEpreuveInternaute);
				//echo "(0,".$idInscriptionEpreuveInternaute."-".$id_epreuve."-".$id_tmp_epreuve_parcours;
				delete_inscription_internaute (0,$idInscriptionEpreuveInternaute,$id_epreuve,$id_tmp_epreuve_parcours,'');
				//print_r($_SESSION['rieis']);
				$id_reg = array_search($idInscriptionEpreuveInternaute,$_SESSION['rieis']);
				unset($_SESSION['rieis'][$id_reg],$_SESSION['tarifs'][$id_reg]);

				//echo $idInscriptionEpreuveInternaute;

				//***unset($_SESSION['equipes_Idref'][$id_reg]);
				//$_SESSION['equipes_Idref'][] = $id_internaute_referent;
				//print_r($_SESSION['equipes_rieis']);
				$id_reg = array_search($idInscriptionEpreuveInternaute,$_SESSION['equipes_rieis']);
				unset($_SESSION['equipes_rieis'][$id_reg]);
				//$_SESSION['equipes_rieis'][] = $id_internaute_inscription_referent;
				//echo "DDDDDDDDDDD: ".$_SESSION['rieis'][$id_reg]." - ".$_SESSION['tarifs'][$id_reg];
				
			}
			
			$idInternaute=extract_champ_r_internautereferent('idInternauteref',$idInscriptionEpreuveInternaute);
			//print_r($_SESSION['equipes_Idref']);
			$id_reg = array_search($id_ctrl_2,$_SESSION['equipes_Idref']);			
			unset($_SESSION['equipes_Idref'][$id_reg]);
			//print_r($_SESSION['equipes']);
			$id_reg = array_search($idInternauteReferent,$_SESSION['equipes']);
			unset($_SESSION['equipes'][$id_reg]);
			
			
			$del_idRefInscriptionEpreuveInternaute= extract_champ_r_internautereferent('idInternauteInscriptionref',$idInternauteReferent);
			$id_tmp_epreuve_parcours=extract_champ_epreuve_internaute('idEpreuveParcours',$del_idRefInscriptionEpreuveInternaute);
			delete_inscription_internaute (0,$del_idRefInscriptionEpreuveInternaute,$id_epreuve,$id_tmp_epreuve_parcours,'');
			$id_reg = array_search($del_idRefInscriptionEpreuveInternaute,$_SESSION['rieis']);
			unset($_SESSION['rieis'][$id_reg],$_SESSION['tarifs'][$id_reg]);
			//echo "bbbbb: ".$_SESSION['rieis'][$id_reg]." - ".$_SESSION['tarifs'][$id_reg];
			$query = "DELETE from r_internautereferent WHERE idInternauteReferent = ".$idInternauteReferent;
			$mysqli->query($query);
			
			$query = "DELETE from r_insc_internautereferent WHERE idInternauteReferent = ".$idInternauteReferent;
			$mysqli->query($query);			
			

		}
	
	
	
}


$iframe='';
if (!empty($_GET['panel'])) $iframe = $_GET['panel'];
//echo "ABANT SESSION IDREUVE: ".$_SESSION['idEpreuve'];
//$id_epreuve = $_GET['id_epreuve'];
if (!empty($_SESSION['idEpreuve']))
{
	if ($_SESSION['idEpreuve'] != $_GET['id_epreuve']) 
	{	
		$array_check=array(1,2,3,4,'grp','opt','');
		$array_check_tp=array('s','r','');
		//echo "GET IDREUVE: ".$_GET['id_epreuve'];
		//echo "SESSION IDREUVE: ".$_SESSION['idEpreuve'];
		if (!empty($_SESSION['caddie']) || in_array($_GET['step'],$array_check) || in_array($_GET['step'],$array_check_tp))
		{
			header('Location: insc.php?id_epreuve='.$_SESSION['idEpreuve'].'&step=start');
		} elseif(empty($_SESSION['caddie']))
		{
			$_SESSION['idEpreuve'] = $_GET['id_epreuve'];
			header('Location: insc.php?id_epreuve='.$_SESSION['idEpreuve'].'&step=start');
		}
	}
	
}
else
{
	
	$_SESSION['idEpreuve'] = $_GET['id_epreuve'];
}



$mdp_perdu=0;

	//on evite le repost on rafraichissisement
	//echo "icic";
/*	
	if(!empty($_POST['login_perdu']) && !empty($_POST['email_perdu']))
	{
	
		$_SESSION['sauvegarde_login'] = $_POST['login_perdu'] ;
		$_SESSION['sauvegarde_email'] = $_POST['email_perdu'] ;
		$fichierActuel = $_SERVER['PHP_SELF'] ;
		if(!empty($_SERVER['QUERY_STRING']))
		{
			$fichierActuel .= '?' . $_SERVER['QUERY_STRING'] ;
		}
		header('Location: ' . $fichierActuel);
		exit;
	}
	// } Fin - Première partie
	// { Début - Seconde partie
	if(isset($_SESSION['sauvegarde_login']))
	{
		$_POST['login_perdu'] = $_SESSION['sauvegarde_login'] ;
		$_POST['email_perdu'] = $_SESSION['sauvegarde_email'] ;
		unset($_SESSION['sauvegarde_login'], $_SESSION['sauvegarde_email']);
	}
*/	
	//on evite le repost on rafraichissisement
	
if (isset($_POST['login_perdu']) && isset($_POST['email_perdu'])) {
	

	
	$send_mail='';
	$mdp_perdu=1;
	$query = "SELECT ri.idInternaute, ri.emailInternaute, ri.nomInternaute, ri.prenomInternaute, ri.nomInternaute, ri.clubInternaute, ri.naissanceInternaute, ri.sexeInternaute, ri.paysInternaute ";
	$query.="FROM r_internaute as ri ";
	$query.=" WHERE ri.emailInternaute = '".$_POST['email_perdu']."'";
	$query.=" AND ri.loginInternaute = '".$_POST['login_perdu']."'";
	//echo $query;
	$result = $mysqli->query($query);
	$row=mysqli_fetch_array($result);
	//echo $row['idInternaute'];
	if (isset($row['idInternaute'])) 
	{
		//echo "oka"; 
		require("includes/functions_mail_n.php");
		//echo "ok"; 
		$send_mail='ok';
		$donnees = array();
		$code = hhp(code_inscription(10));
								$query  = "INSERT INTO r_genepass ";
								$query .= "(hash, etat, idInternaute )";
							
								$query .= " VALUES (";
								$query .= "'".$code."', ";
								$query .= "'NON', ";
								//$query .= "'".date("Y-m-d H:i:s")."', ";
								$query .= "'".$row['idInternaute']."' ";
								$query .= ") ";
								//if ($besoin_certif != 0) $query .= ",'".$date_certficat."') "; else $query .= ") ";
								//echo $query;
								$result_query = $mysqli->query($query);		
			
			//$row_info = info_internaute_send_mail_test($idInscriptionEpreuveInternauteRef,0,'');
			$row_info['rmdp'] = 1;
			$row_info['rmdp_lien'] = "requestmdp.php?hash=".$code."&id=".$row['idInternaute'];
			array_push($donnees,$row_info);
			//print_r($donnees);
			$sujet = "Ats Sport - Mot de passe perdu ";
			
			$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport - Requête Mot de passe Perdu',$row['emailInternaute'], $row['nomInternaute']." ".$row['prenomInternaute'], $sujet,$donnees);			
			$mdp_perdu_etat = 1;
	}
	else { 
	
		//echo "ko";
		$mdp_perdu_etat = 2;
	
	}
	//***echo "mdp : ".$mdp_perdu."- mdp_type : ".$mdp_perdu_etat ;

}

$nom_internaute_perdu = '';
if (isset($_POST['ident_perdu'])) {

	$send_mail='';
	$mdp_perdu=1;
	$query = "SELECT ri.idInternaute, ri.loginInternaute ";
	$query.="FROM r_internaute as ri ";
	$query.=" WHERE ri.emailInternaute = '".$_POST['email_perdu']."'";
	$query.=" AND ri.nomInternaute = '".$_POST['nom_perdu']."'";
	$query.=" AND ri.naissanceInternaute = '".datefr2en($_POST['naissance_perdu'])."' ORDER BY idInternaute DESC LIMIT 1";
	//echo $query;
	$result = $mysqli->query($query);
	$row=mysqli_fetch_array($result);
	//echo $row['idInternaute'];
	if (isset($row['idInternaute'])) 
	{
		//echo "oka"; 
		//****require("includes/functions_mail_n.php");
		//echo "ok";
		$nom_internaute_perdu = $row['loginInternaute'];		
	}
	else { 
	
		//echo "ko";
		$ident_perdu_etat = 2;
	
	}
	//***echo "mdp : ".$mdp_perdu."- mdp_type : ".$mdp_perdu_etat ;

}	
		
$query="SELECT val FROM r_constant WHERE cle like 'conditionService'";
$result=$mysqli->query($query);
$condition=mysqli_fetch_array($result);

$info_type_parcours = info_type_parcours($_SESSION['idEpreuve']);	

if ($info_type_parcours['nb_solo'] ==0 && $info_type_parcours['nb_relais'] ==0) { header('Location: index.php'); } 
$array_check=array(1,2,3,4,'grp','opt','start','');
//print_r($array_check);
if (empty($_GET['id_epreuve'])) { header('Location: index.php'); }
if (empty($_GET['step']) && empty($_GET['tp'])) { header('Location: insc.php?id_epreuve='.$_SESSION['idEpreuve'].'&step=start'); } 
if (!in_array($_GET['step'],$array_check) ) { header('Location: insc.php?id_epreuve='.$_SESSION['idEpreuve'].'&step=start'); 
}

$cout_paiement_cb = extract_champ_epreuve('cout_paiement_cb',$_SESSION['idEpreuve']);

if ( extract_champ_epreuve('payeur',$_SESSION['idEpreuve'])== 'organisateur' ) 
{
	$paiement_frais_cb = 0;
	$frais_cb_orga_fixe = extract_champ_epreuve('cout_paiement_cb',$_SESSION['idEpreuve']);
}
else 
{
	$paiement_frais_cb = 1;
}

$id_parcours = $_GET['id_parcours'];
$select_parcours = $_GET['sel'];
$info_epreuve_perso = info_epreuve_perso($_SESSION['idEpreuve']);
//print_r($info_epreuve_perso);

//if (!empty($info_epreuve_perso['url_image_solo'])) echo "TOTO";
$url_fichier = "../../../admin/template_content_html/fichiers_epreuves/";
$licence_ffa_epreuve = 0;
	
		$query  = "SELECT * ";
		$query .= "FROM r_epreuve";
		$query .=" INNER JOIN r_typeepreuve ON r_epreuve.idTypeEpreuve = r_typeepreuve.idTypeEpreuve";
		$query .=" WHERE idEpreuve = ".$_SESSION['idEpreuve'];
		$result = $mysqli->query($query);
		$data_epreuve=mysqli_fetch_array($result);
		
		if ($data_epreuve['idTypeEpreuve'] == 1)
		{
			$type_epreuve = type_certificat(1);
			
		}
		elseif($data_epreuve['idTypeEpreuve'] == 2)
		{
			$type_epreuve = type_certificat(2);
		}
		elseif($data_epreuve['idTypeEpreuve'] == 3)
		{
			$type_epreuve = type_certificat(3);
			$licence_ffa_epreuve = 1;
		}
		elseif($data_epreuve['idTypeEpreuve'] == 4)
		{
			$type_epreuve = type_certificat(4);
		}


		
?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="fr">
<!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<title>ATS-SPORT | Chronométrage, inscriptions en ligne, dossards</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<meta content="chronométrage, chronométreur, inscriptions en ligne, dossards, course à pied, trail, cyclisme, cyclosportive, vtt, triathlon, duathlon" name="description" />
	<meta content="" name="author" />
	
	<!-- ================== BEGIN BASE CSS STYLE ================== -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:100,100italic,300,300italic,400,400italic,500,500italic,700,700italic,900,900italic" rel="stylesheet" type="text/css" />
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<link href="assets/plugins/jquery-ui/themes/base/minified/jquery-ui.min.css" rel="stylesheet" />
	<link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
	<link href="assets/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
	<link href="assets/css/animate.min.css" rel="stylesheet" />
	<link href="assets/css/style_c.css" rel="stylesheet" />
	<link href="assets/css/style-responsive.css" rel="stylesheet" />
	<link href="assets/css/theme/default.css" rel="stylesheet" id="theme" />
	<link href="admin/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.css" rel="stylesheet" />
	<link href="admin/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.css" rel="stylesheet" />
	<link href="assets/plugins/fancybox/jquery.fancybox.css" rel="stylesheet" />
	<!-- ================== END BASE CSS STYLE ================== -->

	<!-- ================== BEGIN PAGE LEVEL STYLE ================== -->

	<!-- ================== END PAGE LEVEL STYLE ================== -->
	<!-- ================== BEGIN BASE JS ================== -->
	<!-- ================== END BASE JS ================== -->

<style>
<?php

echo $info_epreuve_perso['css'];
?>
  <style type="text/css">
.wrapper_ct {
  text-align: center;
}
.time_ct {
  color: #fff;
  font-size: 6em;
}
.label_ct {
  font-size: 2em;
  display: block;
  color: #aaa;
} 
  </style>
</style>
</head>
<body data-spy="scroll" data-target="#header-navbar" data-offset="51">
<?php if($_GET['panel'] != 'iframe') { $panel = 0;?>
    <!-- begin #page-container -->

	<!-- Header-->
		<?php include( 'header.php' ); ?>
 <div class="content bg-image">
<?php } else { $panel=1;  }?>
 	<div class="row">
			<?php if ($_GET['step']=='start') { ?>
				<div class="col-md-3"></div>
					<div class="panel panel-primary col-md-6" style="max-width: 900px;;margin:auto">
						<?php if ($panel == 0) { ?>
						<div class="breadcrumb flat no-nums hidden-xs">
							<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>" class="active"><i class="fa fa-1x fa-home"></i></a>
							<a href="javascript:;">Parcours</a>
							<a href="javascript:;">Connexion</a>
							<a href="javascript:;">Formulaire</a>
							<a href="javascript:;">Panier</a>
							<a href="javascript:;">Validation</a>
						</div>
						<?php } ?>
						<div class="panel-heading">
		
							<h4 class="panel-title" style="font-size:2em"><?php echo $data_epreuve['nomEpreuve']; ?></h4>
						</div>
						<div class="panel-body" style="border-width:0px 1px 1px 1px;border-style:solid solid solid solid;border-color:#ccc #ccc #ccc #ccc;background:#fff">				
							<div class="row">
								<div class="col-sm-12">
									<div class="panel panel-success" style="border: 1px solid #ccc; margin-bottom:0px">

										<!-- <div class="panel-body text-black" style="background:#e2e7eb;background:white url('images/parcours_solo.jpg') no-repeat center center;"> /-->
										<div class="panel-body text-black">
											<fieldset>
												<?php if ($info_type_parcours['nb_solo'] > 0 ) { ?>
												<p class="text-center" style="border:1px solid #bababa;text-align:center;-webkit-box-shadow:5px 5px 10px -10px #8d8d8d ;">											
													<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&tp=s<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" > <?php if (!empty($info_epreuve_perso['url_image_solo'])) { ?><img style="max-width: 100%;" src="<?php echo $info_epreuve_perso['url_image_solo']; ?>"><?php } else { ?><img src="images/parcours_solo.jpg" width="250px"><?php } ?></a>
												</p>
												<hr>
												<?php } ?>
												<?php if ($info_type_parcours['nb_relais'] > 0 ) { ?>
												<p class="text-center" style="border:1px solid #bababa;text-align:center;-webkit-box-shadow:5px 5px 10px -10px #8d8d8d ;">											
													<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&tp=r<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" >  <?php if (!empty($info_epreuve_perso['url_image_relais'])) { ?><img style="max-width: 100%;" src="<?php echo $info_epreuve_perso['url_image_relais']; ?>"><?php } else { ?><img src="images/parcours_relais.jpg" width="250px"><?php } ?></a>
												</p>
												<?php } ?>
												<hr>
												<p class="text-center" style="border:1px solid #bababa;text-align:center;-webkit-box-shadow:5px 5px 10px -10px #8d8d8d ;">											
													<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=grp<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" > <img src="images/groupe.png" width="250px"></br>Je rejoins un groupe </a>
												</p>
											</fieldset>
										</div>
									</div>
								</div>
							</div>
								
						</div>
					</div>
				<div class="col-md-3"></div>
			<?php } elseif ($_GET['step'] == 'grp') { 
				//unset($_SESSION['option_plus']);
				
				if (isset($_SESSION['idEpreuvePersoPre']) && empty($_SESSION['mdp_groupe_new']))
				{
					$aff_groupe="visible"; $aff_select_groupe="none"; $aff_new_groupe="none";
				}
				elseif (isset($_SESSION['idEpreuvePersoPre']) && isset($_SESSION['mdp_groupe_new']))
				{
					$aff_groupe="none";$aff_groupe_new="visible"; $aff_select_groupe="none"; $aff_new_groupe="none";
				}
				else
				{
					$aff_groupe="none"; $aff_select_groupe="visible"; $aff_new_groupe="none"; $aff_groupe_new="none";
				}
				$query_ep="SELECT idEpreuvePersoPre, groupe from r_epreuveperso_pre WHERE idEpreuve = ".$_SESSION['idEpreuve'];
				$query_ep .=" AND active='oui' AND (NOW() BETWEEN `dateDebut` AND `dateFin`)";
				$result_ep = $mysqli->query($query_ep);
	
		
		?>
				<div class="col-md-3"></div>
					<div class="panel panel-primary col-md-6" style="max-width: 900px;">
						<?php if ($panel == 0) { ?>
							<div class="breadcrumb flat no-nums hidden-xs">
								<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>" ><i class="fa fa-1x fa-home"></i></a>
								<a href="javascript:;" class="active">Groupe</a>
								<a href="javascript:;">Connexion</a>
								<a href="javascript:;">Formulaire</a>
								<a href="javascript:;">Panier</a>
								<a href="javascript:;">Validation</a>
							</div>
						<?php } ?>
						<div class="panel-heading">
		
							<h4 class="panel-title"><?php echo $data_epreuve['nomEpreuve']; ?> - Inscription Groupe</h4>
						</div>
						<div class="panel-body" style="border-width:0px 1px 1px 1px;border-style:solid solid solid solid;border-color:#ccc #ccc #ccc #ccc;background:#fff">				
							<div class="row">					
								<div id="affichage_groupe" style="display:<?php echo $aff_select_groupe; ?>">
									<table class="table " border="0">
										<thead>
											<tr >
												<th>Nom du groupe</th>
												<th>Mot de passe d'inscription</th>
												<th>Validation</th>
											</tr>
										</thead>
										<tbody>
										
										<?php  
											while($row_ep = mysqli_fetch_array($result_ep)) {	?>
											<tr>
		
	
											<td>										
															<div class="form-group row m-b-15">
																<label class="col-sm-12 col-form-label"><?php echo $row_ep['groupe']; ?></label>
											</td>
											<td>
																<div class="col-sm-12">
																	<input id="groupe_<?php echo $row_ep['idEpreuvePersoPre']; ?>" type="password" class="form-control" placeholder="Entrer le code d'activation">
																	<span id="grp_error_<?php echo $row_ep['idEpreuvePersoPre']; ?>" class="text-danger" style="display:none;font-size:12px">Le mot de passe ne corresponds pas</span>
																	<span id="grp_error_empty_<?php echo $row_ep['idEpreuvePersoPre']; ?>" class="text-danger" style="display:none;font-size:12px">Le champ ne peut etre vide</span>
																</div>
																
											</td>
											<td>
																<div class="col-sm-12">
																	<button onclick="check_groupe(<?php echo $row_ep['idEpreuvePersoPre']; ?>,0,<?php echo $_SESSION['idEpreuve']; ?>);" class="btn btn-primary">Valider</button>
																</div>
															</div>
														
											</td>		
	
											</tr>
										<?php } ?>																	
	
									</tr>
										</tbody>
									</table>
									<?php if ($_SESSION['idEpreuve'] == 6547) { 
									$email_organisateur = recup_mail_organisateur_epreuve($_SESSION['idEpreuve']);
									?>
											
										<a class="btn btn-success" href="mailto:<?php echo $email_organisateur; ?>?subject=Triathlon%20du%20Salagou%202020%20-%20Demande%20nouveau%20groupe &body=Merci%20de%20nous%20indiquer%20ce-dessous%20le%20nom%20du%20groupe%20et%20le%20nombre%20de%20coureur%20attendus: ">Créer un nouveau groupe - Contacter l'organisateur</a>
									<?php } else { ?>

										<a href="javascript:;" class="btn btn-success" onclick="check_groupe(0,2,<?php echo $_SESSION['idEpreuve']; ?>);">Créer un nouveau groupe</a>
									<?php  } ?>
									<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=start<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" class="btn btn-info pull-right" onclick="check_groupe(0,1,<?php echo $_SESSION['idEpreuve']; ?>);">Revenir à la page d'accueil de l'épreuve</a>
								</div>
								<div class="col-sm-12" id="groupe_ok" style="display:<?php echo $aff_groupe; ?>">
									<div class="panel panel-success" >

										<!-- <div class="panel-body text-black" style="background:#e2e7eb;background:white url('images/parcours_solo.jpg') no-repeat center center;"> /-->
										<div class="panel-body text-black" style="background:white ;">
											<fieldset>
												<legend class="m-b-15">Vous êtes désormais associé au groupe <b><span id="nom_groupe" class="text-primary" style="font-size:28px"><?php echo $_SESSION['groupe']; ?></span></b>
												</br>Continuer votre inscription en sélectionnant le type de parcours ci-dessous.
												</legend>
													<p class="text-center" style="border:1px solid #bababa;text-align:center;-webkit-box-shadow:5px 5px 10px -10px #8d8d8d ;">											
														<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&tp=s<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" > <?php if (!empty($info_epreuve_perso['url_image_solo'])) { ?><img style="max-width: 100%;" src="<?php echo $info_epreuve_perso['url_image_solo']; ?>"><?php } else { ?><img src="images/parcours_solo.jpg" width="250px"><?php } ?></a>
													</p>
													<hr>
													<?php if ($info_type_parcours['nb_relais'] > 0 ) { ?>
														<p class="text-center" style="border:1px solid #bababa;text-align:center;-webkit-box-shadow:5px 5px 10px -10px #8d8d8d ;">											
															<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&tp=r<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" >  <?php if (!empty($info_epreuve_perso['url_image_relais'])) { ?><img style="max-width: 100%;" src="<?php echo $info_epreuve_perso['url_image_relais']; ?>"><?php } else { ?><img src="images/parcours_relais.jpg" width="250px"><?php } ?></a>
														</p>
													<?php } ?>
													<hr>
											</fieldset>
										</div>
										<button class="btn btn-danger" onclick="check_groupe(0,1,<?php echo $_SESSION['idEpreuve']; ?>);"> Annuler ma participation au groupe</button>
									</div>
								</div>
								
								<div class="col-sm-12" id="groupe_ok_new" style="display:<?php echo $aff_groupe_new; ?>">
									<div class="panel panel-success" >

										<!-- <div class="panel-body text-black" style="background:#e2e7eb;background:white url('images/parcours_solo.jpg') no-repeat center center;"> /-->
										<div class="panel-body text-black" style="background:white ;">
											<fieldset>
												<legend class="m-b-15">Vous avez crée le groupe <b><span id="nom_groupe_new" class="text-primary" style="font-size:28px"><?php echo $_SESSION['groupe']; ?></span></b> et vous y êtes associé
												</br>Le mot de passe du groupe est :  <b><span id="mdp_groupe_new" class="text-primary" style="font-size:28px"><?php echo $_SESSION['mdp_groupe_new']; ?></span></b>
												</br>Continuer votre inscription en sélectionnant le type de parcours ci-dessous.
												</legend>
													<p class="text-center" style="border:1px solid #bababa;text-align:center;-webkit-box-shadow:5px 5px 10px -10px #8d8d8d ;">											
														<a  href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&tp=s<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" > <?php if (!empty($info_epreuve_perso['url_image_solo'])) { ?><img style="max-width: 100%;" src="<?php echo $info_epreuve_perso['url_image_solo']; ?>"><?php } else { ?><img src="images/parcours_solo.jpg" width="250px"><?php } ?></a>
													</p>
													<hr>
													<?php if ($info_type_parcours['nb_relais'] > 0 ) { ?>
														<p class="text-center" style="border:1px solid #bababa;text-align:center;-webkit-box-shadow:5px 5px 10px -10px #8d8d8d ;">											
															<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&tp=r<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" >  <?php if (!empty($info_epreuve_perso['url_image_relais'])) { ?><img style="max-width: 100%;" src="<?php echo $info_epreuve_perso['url_image_relais']; ?>"><?php } else { ?><img src="images/parcours_relais.jpg" width="250px"><?php } ?></a>
														</p>
													<?php } ?>
													<hr>
											</fieldset>
										</div>
										<button class="btn btn-danger" onclick="check_groupe(0,1,<?php echo $_SESSION['idEpreuve']; ?>);"> Annuler ma participation au groupe</button>
									</div>
								</div>
								
								<div class="col-sm-12" id="groupe_new" style="display:<?php echo $aff_new_groupe; ?>">
									<div class="panel panel-success" >
											<label class="control-label">Formulaire pour la création d'un groupe </label>
										<hr>
										<!-- <div class="panel-body text-black" style="background:#e2e7eb;background:white url('images/parcours_solo.jpg') no-repeat center center;"> /-->
										<div class="panel-body text-black" style="background:white ;">

												<label class="control-label">Nom du groupe <span class="text-danger">*</span></label>
												<div class="row m-b-15">
													<div class="col-md-12">
														<input name="groupe_nom" id="groupe_nom" value="" type="text" class="form-control" placeholder="Ex: Les barjots" required />
													</div>
												</div>
												<label class="control-label">Nom et prénom du responsable <span class="text-danger">*</span></label>
												<div class="row m-b-15">
													<div class="col-md-12">
														<input name="groupe_patronyme" id="groupe_patronyme" value="" type="text" class="form-control" placeholder="Ex: Durand Martin" required />
													</div>
												</div>
												<label class="control-label">Email <span class="text-danger">*</span></label>
												<div class="row m-b-15">
													<div class="col-md-12">
														<input value="" name="groupe_email" id="groupe_email" type="email" class="form-control" placeholder="ex: email@domaine.com" required />
													</div>
												</div>
												<label class="control-label">Email confirmation <span class="text-danger">*</span></label>
												<div class="row m-b-15">
													<div class="col-md-12">
														<input value="" id="groupe_email_conf" type="text" class="form-control" placeholder="ex: email@domaine.com" required onchange="confirm_champs('groupe_email');" />
													</div>
												</div>
												<label class="control-label">Téléphone<span class="text-danger">*</span></label>
												<div class="row m-b-15">
													<div class="col-md-12">
														<input value="" name="groupe_tel" id="groupe_tel" type="text" class="form-control" placeholder="ex: 0102030405" required />
													</div>
												</div>
												<label class="control-label">Mot de passe du groupe<span class="text-danger">*</span></label>
												<div class="row m-b-15">
													<div class="col-md-12">
														<input value="" name="groupe_mdp" id="groupe_mdp" type="text" class="form-control" placeholder="" required />
													</div>
												</div>
												<label class="control-label">confirmation du mot de passe du groupe<span class="text-danger">*</span></label>
												<div class="row m-b-15">
													<div class="col-md-12">
														<input value="" name="groupe_mdp_conf" id="groupe_mdp_conf" type="text" class="form-control" placeholder="" required onchange="confirm_champs('groupe_mdp');" />
													</div>
												</div>
												<hr>
												<label class="control-label">mot de passe administateur <span class="text-danger">*</span></label>
												<div class="row m-b-15">
													<div class="col-md-12">
														<input value="" name="groupe_mdp_admin" id="groupe_mdp_admin" type="password" class="form-control" placeholder="" required />
													</div>
												</div>	
												<button class="btn btn-success" onclick="check_groupe(0,5,<?php echo $_SESSION['idEpreuve']; ?>);"> Créer mon groupe</button>	<a href="javascript:;" class="btn btn-danger" onclick="check_groupe(0,3,<?php echo $_SESSION['idEpreuve']; ?>);"> Annuler la création du groupe</a>											

										</div>
										
									</div>
								</div>
								
							</div>

						</div>
					</div>
				<div class="col-md-3"></div>
			<?php } elseif ($_GET['step'] == 'opt') { 
				//unset($_SESSION['option_plus']);
				//echo "xxx".$_SESSION['tarifs']."xxx";
				//if (empty($_SESSION['tarifs'])) { header('Location: insc.php?id_epreuve='.$_SESSION['idEpreuve'].'&step=start'); } 
				
				if (count($_GET['edit_reg']) >0 || $_GET['r'] == 1 ) 
				{

					
					//$tarif=extract_champ_epreuve_internaute('idEpreuveParcoursTarif',$_SESSION['rieis'][$_GET['edit_reg']]);
					//$prix_engagement = extract_champ_tarif('tarif',$tarif);
					//$param_t = $tarif;
					//list($idOptionPlus,$Prix_OptionPlus,$information)= explode('|', $_SESSION['option_plus']);
					if ($_GET['r']==1)
					{
						$update=1;
						$idInscriptionEpreuveInternaute = extract_champ_r_internautereferent('idInternauteInscriptionref',$_GET['id']);
						$idOptionPlus_update = extract_champ_epreuve_internaute ('idOptionPlus', $idInscriptionEpreuveInternaute);
						$id_parcours_tmp = extract_champ_epreuve_internaute('idEpreuveParcours', $idInscriptionEpreuveInternaute);
						unset($_SESSION['option_plus']);
						
						//echo "idInscriptionEpreuveInternaute-r : ".$idInscriptionEpreuveInternaute;
						//echo "idOptionPlus_update-r".$idOptionPlus_update;							
						$lien = "&r=1&id=".$_GET['id'];
					}
					else
					{
						$update=1;
						$idInscriptionEpreuveInternaute = $_SESSION['rieis'][$_GET['edit_reg']];
						$idOptionPlus_update = extract_champ_epreuve_internaute ('idOptionPlus', $idInscriptionEpreuveInternaute);
						$id_parcours_tmp = extract_champ_epreuve_internaute('idEpreuveParcours', $idInscriptionEpreuveInternaute);
						unset($_SESSION['option_plus']);
						
						//echo "idInscriptionEpreuveInternaute : ".$idInscriptionEpreuveInternaute;
						//echo "idOptionPlus_update".$idOptionPlus_update;						
						
						$lien = "&edit_reg=".$_GET['edit_reg'];
					}

					//https://www.ats-sport.com/https://www.ats-sport.com/formulaire.php?id_epreuve=4247&r=1&id=24331*$
					
				}
				else 
				{
					$edit_reg = '';
					if (isset($_SESSION['option_plus']))  { header('Location: insc.php?id_epreuve='.$_SESSION['idEpreuve'].'&step=start'); }
					if ($_POST['relais']==1)
					{
						
						//print_r($_POST['relais_nbplace']);
						$seg = explode("_",$_POST['relais_nbplace']);
						//echo "nb : ".$seg[0]." - Id_tarif : ".$seg[1]." - tarif : ".$seg[2]."</br>";	
						$tarif  = $seg[1];
						$nb_relais = $seg[0];
						if (empty($_SESSION['tarifs']))  $_SESSION['tarifs'] = $tarif;
						$_SESSION['nb_relais'] = $nb_relais;
						$id_parcours_tmp = extract_champ_tarif('idEpreuveParcours', $_SESSION['tarifs']);
					}
					else
					{
						$first = TRUE;
						foreach ($_POST['nbplace'] as $value)
						{
							$seg = explode("_",$value);
							//echo "nb : ".$seg[0]." - Id_tarif : ".$seg[1]." - tarif : ".$seg[2]."</br>";		
							if ($seg[0]>0)
							{
								
								for ($i=0;$i<$seg[0];$i++)
								{
									if ($first==TRUE)
									{
										$tarif .=$seg[1];
										$first = FALSE;
									}
									else
									{
										$tarif .=",".$seg[1];
									}
								}
							}
							
							
						}
						if (empty($_SESSION['tarifs']))  $_SESSION['tarifs'] = $_POST['tarif_select'];
						$id_parcours_tmp = extract_champ_tarif('idEpreuveParcours', $_SESSION['tarifs']);
						//echo "id_parcours_tmp : ".$id_parcours_tmp;
					}
					
					//echo "x : ".$_SESSION['tarifs'];
					
					$_SESSION['new_user'] = extract_champ_internaute('etat',$_SESSION['log_id']);
					//echo "ID : ".$_SESSION['log_id']."-".$_SESSION['new_user'];						
				}
				
				
				
				$option_plus=options_plus_ie($_SESSION['idEpreuve'],$id_parcours_tmp);
				//print_r($option_plus);
				if (mysqli_num_rows($option_plus) == 0)  {  echo "<script> window.location.replace('insc.php?id_epreuve=".$_SESSION['idEpreuve']."&step=start') </script>"; }
		
		?>
				<div class="col-md-3"></div>
					<div class="panel panel-primary col-md-6" style="max-width: 900px;">
						<?php if ($panel == 0) { ?>
							<div class="breadcrumb flat no-nums hidden-xs">
								<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>" ><i class="fa fa-1x fa-home"></i></a>
								<a href="javascript:;" class="active">Options</a>
								<a href="javascript:;">Connexion</a>
								<a href="javascript:;">Formulaire</a>
								<a href="javascript:;">Panier</a>
								<a href="javascript:;">Validation</a>
							</div>
						<?php } ?>
						<div class="panel-heading">
		
							<h4 class="panel-title"><?php echo $data_epreuve['nomEpreuve']; ?> - Choissisez votre option</h4>
						</div>
						<div class="panel-body" style="border-width:0px 1px 1px 1px;border-style:solid solid solid solid;border-color:#ccc #ccc #ccc #ccc;background:#fff">				
							<div class="row">
								
									<table class="table " border="0" style="text-align:center;">
										<tbody>
										
										<?php if (mysqli_num_rows($option_plus) > 0) { 
											while($row_option_plus = mysqli_fetch_array($option_plus)) {	?>
											<tr>
											<td style="border-top:0px solid #fff;border-bottom:0px solid #fff">
												<?php if (isset($_SESSION['log_id'])) { ?>
													
													<form action="https://www.ats-sport.com/formulaire.php?id_epreuve=<?php echo $_SESSION['idEpreuve'].$lien; ?><?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" method="POST" id="insc_form" class="margin-bottom-0" enctype="multipart/form-data">
												<?php } else { ?>
													<form action="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=2<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" method="POST" id="insc_form" class="margin-bottom-0" enctype="multipart/form-data">
													
																									
												<?php } ?>
													<table style="border:1px solid #bababa;text-align:center;-webkit-box-shadow:5px 5px 10px -10px #8d8d8d ;" class="table m-b-0" width="33%" <?php if ($idOptionPlus_update == $row_option_plus['idOptionPlus']) echo 'style="border: 2px solid red;"'; else echo 'border="0"'; ?>>
														
														<thead >
															<tr>
																<th style="padding:10px;font-size: 24px; text-align:center;border-top:0px solid #fff;border-bottom: 0px solid #fff"><?php echo $row_option_plus['label']; ?> <span class="text-primary"><b><?php echo (($row_option_plus['prix']>0)? "(+" : (($row_option_plus['prix']<0)? "(-" :"")."").(($row_option_plus['prix']!=0)? $row_option_plus['prix']." €)":""); ?></b></span></th>
															</tr>
														</thead>
														<tbody>
														<?php if (!empty($row_option_plus['url_image'])) { ?>
															<tr>
																<td style="padding:20px;border-top:0px solid #fff">
																	
																		<img src="<?php echo "images/".$row_option_plus['url_image']; ?>" style="width: auto;height:auto" >
																		

																</td>
															</tr>
														<?php } ?>
															<tr>
																<td><?php echo $row_option_plus['information']; ?></td>
															</tr>
															<tr><td style="border-top:0px solid #fff;padding:20px;">
																<button class="btn btn-primary">
																Je choisis cette option
																</button>
																</td>
															</tr>
														</tbody>
													</table>
																	<input type="hidden" name="select_option_plus" value="<?php echo $row_option_plus['idOptionPlus'] ;?>|<?php echo $row_option_plus['prix'] ;?>">
																	<input type="hidden" name="relais_nbplace" value="<?php echo $_POST['relais_nbplace']; ?>">
																	<input type="hidden" name="relais" value="<?php echo $_POST['relais']; ?>">
												</form>
											</td>
											</tr>
										<?php } ?>																	
									<?php } ?>
									
										</tbody>
									</table>
								
							</div>
						</div>
					</div>
				<div class="col-md-3"></div>
			<?php } elseif ($_GET['step'] <1) { unset($_SESSION['tarifs'],$_SESSION['option_plus']); ?>
            <!-- begin container -->
				<div class="col-md-3"></div>
				<div class="panel panel-primary col-md-6" style="max-width: 900px;">
					<!--<div class="panel panel-inverse" data-sortable-id="index-1" style="background-color:#0e5887">-->
					<?php if ($panel == 0) { ?>
						<div class="breadcrumb flat no-nums hidden-xs">
							<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>" ><i class="fa fa-1x fa-home"></i></a>
							<a href="javascript:;" class="active">Parcours</a>
							<a href="javascript:;">Connexion</a>
							<a href="javascript:;">Formulaire</a>
							<a href="javascript:;">Panier</a>
							<a href="javascript:;">Validation</a>
						</div>
					<?php } ?>
						<div class="panel-heading">

							<h4 class="panel-title"><?php echo $data_epreuve['nomEpreuve']; ?></h4>
						</div>
					
					<div class="panel-body bg-white" >
						<div class="row">
						<?php if ($_GET['tp']=='s') 
						{ ?>	
							
							<?php $info_parcours=info_parcours_n($_SESSION['idEpreuve'],'',0); 
							if (mysqli_num_rows($info_parcours)!=0) 
							{
							?>
							
								<!--<div class="table-responsiv">/-->
									<table class="table table-striped m-b-0 " border="0">
										<thead>
											<tr >
												<th>Parcours</th>
												<th width="15%" style="vertical-align: middle;text-align: center;">Tarif</th>
												<th width="7%">Votre sélection</th>
											</tr><tr><th colspan="3"></br></th></tr>
										</thead>
										<tbody >
					
											<?php 
												$color = 1;
														
														$nb_dossard_disponible =1;
														$nbParticipantsAttendus = extract_champ_epreuve('nbParticipantsAttendus',$_SESSION['idEpreuve']);
														$query_nb_dossard_parcours = "select idEpreuveParcours from r_epreuveparcours WHERE idEpreuve = ".$_SESSION['idEpreuve']." AND relais= 0";
														$result_nb_dossard_parcours = $mysqli->query($query_nb_dossard_parcours);
														$nb_dossard_disponible_array = array();
														while (($row_nb_dossard_parcours=mysqli_fetch_array($result_nb_dossard_parcours)) != FALSE)
														{
															//if ($row_nb_dossard_parcours['idEpreuveParcours']==15609) {
																$nb_info_dossard_array = nombre_dossard_v2($row_nb_dossard_parcours['idEpreuveParcours']);
																//print_r($nb_info_dossard_array);
																$nb_dossard_disponible_array[$row_nb_dossard_parcours['idEpreuveParcours']] = $nb_info_dossard_array;
															//}
														}	
														//print_r($nb_info_dossard_array);
														//print_r($nb_dossard_disponible_array);
												$aff_place_non_limitee = extract_champ_epreuve('insc_aff_place_restante',$_SESSION['idEpreuve']);	
												while (($row_select_parcours=mysqli_fetch_array($info_parcours)) != FALSE)
												{ 	
													$nom_tarif=extract_champ_tarif('desctarif',$row_select_parcours['idEpreuveParcoursTarif']);
													$option_plus=options_plus_ie($_SESSION['idEpreuve'],$row_select_parcours['idEpreuveParcours']); 
													if ($color==1) $c_html = 'blue';
													if ($color==2) $c_html = 'blue';
													$pct = 0;
													
													//echo $row_select_parcours['nb_dossard']." - ".$row_select_parcours['nb_dossard_pris']." - ".$row_select_parcours['reduction'];
													$tarif_reduc_place = 0;
													$tar = tarif_reduc_place($row_select_parcours['idEpreuveParcoursTarif']);
													//print_r($tar);
													$tarif_reduc_place = $tar['reduction'];
													
														 
														

													?>				
							
													<tr style="-webkit-box-shadow:5px 5px 10px -10px #8d8d8d ;">
														<td style="padding: 20px;">
															
																<h4><strong><?php echo $row_select_parcours['nomParcours']; ?></strong> - <i><?php echo $row_select_parcours['desctarif']; ?> </i></h4>
																<h6 style="padding-right: 50px" class="hidden-xs"><?php echo $row_select_parcours['ParcoursDescription']; ?></h6>
																<?php if ($nb_dossard_disponible_array[$row_select_parcours['idEpreuveParcours']]['nb_dossard_disponible'] != 99999999 && $aff_place_non_limitee !='oui') { ?>
																	<span class="label label-info m-r-5" style="font-size:12px"><?php echo $nb_dossard_disponible_array[$row_select_parcours['idEpreuveParcours']]['nb_dossard_parcours'];?> places </span>
																	<?php //$nb_dossard_disponible_array[$row_select_parcours['idEpreuveParcours']]['nb_dossard_attribue'] = 300;
																 
																	$pct = $nb_dossard_disponible_array[$row_select_parcours['idEpreuveParcours']]['nb_dossard_attribue']/$nb_dossard_disponible_array[$row_select_parcours['idEpreuveParcours']]['nb_dossard_parcours']*100;
																	
																	if ($pct > 75 && $pct <= 90 ) { ?>
																	<span class="label label-warning m-r-5" style="font-size:12px"><b><?php echo $nb_dossard_disponible_array[$row_select_parcours['idEpreuveParcours']]['nb_dossard_disponible'];?></b> restantes</span>
																		<?php } elseif ($pct > 90) { ?>
																			<span class="label label-danger m-r-5" style="font-size:12px"><b><?php echo $nb_dossard_disponible_array[$row_select_parcours['idEpreuveParcours']]['nb_dossard_disponible'];?></b> restantes</span>
																		<?php }  ?>
																	<?php } 
																		else { ?> <span class="label label-info" style="font-size:12px"> Places non limitées</span> <?php } ?>
																<span class="label label-info" style="font-size:12px"> <a style="font-size:12px;" href = "liste_des_inscrits.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&id_parcours=<?php echo $row_select_parcours['idEpreuveParcours']; ?>" target="_blank" class="label label-info" data-toggle="popover" title="Information" data-content="liste des inscrits sur ce parcours" >Liste engagées</a></span>
																<span class="m-l-5 label label-inverse" style="font-size:12px"> <?php echo affichage_annee($_SESSION['idEpreuve'],$row_select_parcours['idEpreuveParcours']); ?> </span>
														</td>
														<td style="vertical-align: middle;text-align: center;">
															<p class="lead" style="text-align: center;margin-left: auto; margin-right: auto;display: block;">
																<?php if ($tarif_reduc_place > 0) { ?>
																	<s><strong><?php echo $row_select_parcours['tarif'];?> €</strong></s>
																	<strong><span class="text-success"><?php echo $row_select_parcours['tarif']-$tarif_reduc_place;?> €</span></strong>
																<?php } else { ?>
																	<strong><?php echo $row_select_parcours['tarif'];?> €</strong>
																<?php } ?>
															</p>
														</td>
														<!--
														<td style="vertical-align: middle;text-align: center;">
															<div class="form-group m-b-10">
																<select onchange="change_tarif();" id="nbplace_<?php echo $row_select_parcours['idEpreuveParcours']; ?>_<?php echo $row_select_parcours['tarif']; ?>" name="nbplace[]" class="form-control form-control-lg">
																<?php for ($i=0;$i<10;$i++) { ?>
																	<OPTION VALUE="<?php echo $i."_".$row_select_parcours['idEpreuveParcoursTarif']."_".$row_select_parcours['tarif']; ?>" ><?php echo $i; ?> </OPTION> 
																<?php } ?>
																</select>
															</div>
			
														</td>
														
														/-->
														<td style="vertical-align: middle;text-align: center;" >
															<div class="form-group m-b-10">
															<?php if (mysqli_num_rows($option_plus) > 0) { ?>
																<form action="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=opt<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" method="POST" id="insc_form" class="margin-bottom-0" enctype="multipart/form-data">
															
															<?php } elseif (isset($_SESSION['log_id'])) { ?>
																
																<form action="https://www.ats-sport.com/formulaire.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?><?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" method="POST" id="insc_form" class="margin-bottom-0" enctype="multipart/form-data">
															<?php } else { ?>
																<form action="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=2<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" method="POST" id="insc_form" class="margin-bottom-0" enctype="multipart/form-data">
																
																												
															<?php } ?>
																<input type="hidden" id="prix_total" name="prix_total" value="0">
																<input type="hidden" name="tarif_select" value="<?php echo $row_select_parcours['idEpreuveParcoursTarif']; ?>">
																<!-- <input type="hidden" id="select_option_plus" name="select_option_plus" value=""> /-->
	
																
																<?php if ($nb_dossard_disponible_array[$row_select_parcours['idEpreuveParcours']]['nb_dossard_disponible'] <=0 ) { ?>
																	 <span class="label label-danger" style="font-size:1.5em">COMPLET !</span>
																	<!-- <div id="aff_check_place" style="display:none" class="alert-danger" style="margin-top: 10px;"><strong> Etape suivante ></div>/-->
																<?php } else { ?>
																	  <input id="button_step_<?php echo $row_select_parcours['idEpreuveParcours']; ?>" class="btn btn-info btn-lg" style="font-weight:bold" type="submit" value="Sélectionner" <?php echo $option_aff_select; ?>>
																<?php } ?>
															</form>
															</div>
														</td>
														
													</tr>
													<?php if ($color==2) $color=1; else $color++;
												} ?>
												
										</tbody>
									</table>
									
	
										<!--
										<div class="col-sm-4 text-right pull-right" id="aff_montant">
												
											<span id="div-aff_code_promo" class="e">
											<div id="aff_code_promo_1">
												</div>
											</span>
											<legend class=""> 
											Montant total : <span class="badge badge-danger prix-ats m-r-40" id="prix_total_affichage_bas">0 €</span>
											<input type="hidden" id="prix_total" name="prix_total" value="0">
											</legend>
											<div id="aff_bouton_inscription" class="text-center">
												<p><input id="button_step" class="btn btn-success btn-lg p-r-5" style="font-weight:bold" type="submit" value="Etape suivante >" disabled></p>
	
											</div>
										</div>
										/-->	
								<!-- </div> /-->
					<?php   } ?>
							<!-- relais /-->
			<?php   	} 
						elseif ($_GET['tp']=='r') 
						{ ?>	
							<?php $info_parcours=info_parcours_n($_SESSION['idEpreuve'],'',2,$select_parcours);
								  
							if (mysqli_num_rows($info_parcours)!=0) {
								
								
							?>
													<!-- <?php if (mysqli_num_rows($option_plus) > 0) { ?>
														<form action="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=opt<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" method="POST" id="insc_form" class="margin-bottom-0" enctype="multipart/form-data">
													
													<?php } elseif (isset($_SESSION['log_id'])) { ?>
														
														<form action="https://www.ats-sport.com/formulaire.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?><?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" method="POST" id="insc_form_relais" class="margin-bottom-0" enctype="multipart/form-data">

													 <?php } else { ?>

														<form action="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=2&relais=1" method="POST" id="insc_form_relais" class="margin-bottom-0" enctype="multipart/form-data">
																										 
													 <?php } ?> /-->
													 
						


							
								<table class="table table-striped m-b-0">
									<thead>
										<tr >
											<!-- <th width="2%">Choix</th> /-->
											<th>Parcours RELAIS</th>
											<th width="15%" style="vertical-align: middle;text-align: center;">Tarif</th>
											<th width="7%">Participants</th>
											<th width="7%"> Votre sélection </th>
										</tr><tr><th colspan="3"></br></th></tr>
									</thead>
									<tbody>
				
										<?php 
											
											$nb=1;
											$color = 1;
											$first=TRUE;
														$nb_dossard_disponible =1;
														$nbParticipantsAttendus = extract_champ_epreuve('nbParticipantsAttendus',$_SESSION['idEpreuve']);
														$query_nb_dossard_parcours = "select idEpreuveParcours from r_epreuveparcours WHERE idEpreuve = ".$_SESSION['idEpreuve']." AND relais > 0";
														$result_nb_dossard_parcours = $mysqli->query($query_nb_dossard_parcours);
														$nb_dossard_disponible_array = array();
														while (($row_nb_dossard_parcours=mysqli_fetch_array($result_nb_dossard_parcours)) != FALSE)
														{
															//if ($row_nb_dossard_parcours['idEpreuveParcours']==15609) {
																$nb_info_dossard_array = nombre_dossard_v2($row_nb_dossard_parcours['idEpreuveParcours']);
																$nb_dossard_disponible_array[$row_nb_dossard_parcours['idEpreuveParcours']] = $nb_info_dossard_array;
															//}
														}	
														//print_r($nb_info_dossard_array);
														//print_r($nb_dossard_disponible_array);
											$aff_place_non_limitee = extract_champ_epreuve('insc_aff_place_restante',$_SESSION['idEpreuve']);
											while (($row_select_parcours=mysqli_fetch_array($info_parcours)) != FALSE)
											{ 	
												$option_plus=options_plus_ie($_SESSION['idEpreuve'],$row_select_parcours['idEpreuveParcours']);
												//print_r($row_select_parcours);
												if ($row_select_parcours['relais'] > 1) 
												{
													$nom_tarif=extract_champ_tarif('desctarif',$row_select_parcours['idEpreuveParcoursTarif']);
													if ($color==1) $c_html = 'blue';
													if ($color==2) $c_html = 'blue';
													$nb_relais_unique = 0;
													if ($row_select_parcours['min_relais']==0)
													{
														$nb_min_relais = $row_select_parcours['relais'];
														$nb_max_relais = $row_select_parcours['relais'];														
														$nb_relais_unique = 0;
													}
													else
													{
														$nb_min_relais = $row_select_parcours['min_relais'];
														$nb_max_relais = $row_select_parcours['relais'];
														$nb_relais_unique = 0;
														
													}
													?>				
							
													<tr>
															<?php if (mysqli_num_rows($option_plus) > 0) { ?>
																<form action="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=opt<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" method="POST" id="insc_form" class="margin-bottom-0" enctype="multipart/form-data">
															
															<?php } elseif (isset($_SESSION['log_id'])) { ?>
																
																<form action="https://www.ats-sport.com/formulaire.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?><?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" method="POST" id="insc_form" class="margin-bottom-0" enctype="multipart/form-data">
															<?php } else { ?>
																<form action="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=2<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" method="POST" id="insc_form" class="margin-bottom-0" enctype="multipart/form-data">
																
																												
															<?php } ?>
														<!--
														<td>
																<!--
																<input onchange="change_etat_relais(this.id);" id="radio_relais_<?php echo $row_select_parcours['idEpreuveParcours']; ?>_<?php echo $row_select_parcours['tarif']; ?>" type="radio" name="optionsRadios" value="1" <?php if ($nb==1) { echo "checked"; } ?>>  
																
															
														</td>
														/-->
														<td>
																<strong><?php echo $row_select_parcours['nomParcours']; ?></strong> - <i><?php echo $row_select_parcours['desctarif']; ?> </i></h4>
																<h6 style="padding-right: 50px"><?php echo $row_select_parcours['ParcoursDescription']; ?></h6> 
																<?php if ($nb_dossard_disponible_array[$row_select_parcours['idEpreuveParcours']]['nb_dossard_disponible'] != 99999999 && $aff_place_non_limitee !='oui') { ?>
																	<span class="label label-info m-r-5" style="font-size:12px"><?php echo $nb_dossard_disponible_array[$row_select_parcours['idEpreuveParcours']]['nb_dossard_parcours'];?> places </span>
																	<?php //$nb_dossard_disponible_array[$row_select_parcours['idEpreuveParcours']]['nb_dossard_attribue'] = 300;
																 
																	$pct = $nb_dossard_disponible_array[$row_select_parcours['idEpreuveParcours']]['nb_dossard_attribue']/$nb_dossard_disponible_array[$row_select_parcours['idEpreuveParcours']]['nb_dossard_parcours']*100;
																	if ($pct > 75 && $pct <= 90 ) { ?>
																	<span class="label label-warning m-r-5" style="font-size:12px"><b><?php echo $nb_dossard_disponible_array[$row_select_parcours['idEpreuveParcours']]['nb_dossard_disponible'];?></b> restantes</span>
																	<?php } elseif ($pct > 90) { ?>
																		<span class="label label-danger m-r-5" style="font-size:12px"><b><?php echo $nb_dossard_disponible_array[$row_select_parcours['idEpreuveParcours']]['nb_dossard_disponible'];?></b> restantes</span>
																	<?php }  ?>
																<?php } else { ?> <span class="label label-info" style="font-size:12px"> Places non limitées pour l'instant </span> <?php } ?>
																<span class="label label-info" style="font-size:12px"> <a style="font-size:12px;" href = "liste_des_inscrits.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&id_parcours=<?php echo $row_select_parcours['idEpreuveParcours']; ?>" target="_blank" class="label label-info" >Liste engagées </a></span>
																<span class="m-l-5 label label-inverse" style="font-size:12px"> <?php echo affichage_annee($_SESSION['idEpreuve'],$row_select_parcours['idEpreuveParcours']); ?> </span>															
														</td>
														<td style="vertical-align: middle;text-align: center;">
															<p class="lead" style="text-align: center;margin-left: auto; margin-right: auto;display: block;">
																<strong><?php echo $row_select_parcours['tarif'];?> €</strong>
															</p>
														</td>
														<td style="vertical-align: middle;text-align: center;">
															<div class="form-group m-b-10">
																<?php 
																	if ($nb_relais_unique==0)
																	{ ?>
																		<select onchange="change_tarif_relais();" id="relais_nbplace_<?php echo $row_select_parcours['idEpreuveParcours']; ?>_<?php echo $row_select_parcours['tarif']; ?>" name="relais_nbplace" class="form-control form-control-lg" >
																		<?php 
		
																				for ($i=$nb_min_relais;$i<=$nb_max_relais;$i++) { ?>
																			
																				<OPTION VALUE="<?php echo $i."_".$row_select_parcours['idEpreuveParcoursTarif']."_".$row_select_parcours['tarif']; ?>" ><?php echo $i; ?> </OPTION> 
																			
																		<?php } ?>
		
																		</select> 
																<?php } else { ?>
																		
																				<span class="text-inverse"><?php echo $row_select_parcours['relais']; ?></span>
																				<input type="hidden" id="relais_nbplace_<?php echo $row_select_parcours['idEpreuveParcours']; ?>_<?php echo $row_select_parcours['tarif']; ?>" name="relais_nbplace" value="<?php echo $row_select_parcours['relais']; ?>">
																	<?php } ?>
															</div>
														<!-- <input type="hidden" id="prix_total" name="prix_total" value="0">
														<input type="hidden" name="tarif_select" value="<?php echo $row_select_parcours['idEpreuveParcoursTarif']; ?>"> /-->
														</td>
														<td style="vertical-align: middle;text-align: center;" >
															<div class="form-group m-b-10">

																<input type="hidden" id="prix_total" name="prix_total" value="0">
																<!-- <input type="hidden" name="tarif_select" value="<?php echo $row_select_parcours['idEpreuveParcoursTarif']; ?>">  /-->
																<!-- <input type="hidden" id="select_option_plus" name="select_option_plus" value=""> /-->
	
																
																	
																	<input id="button_step_<?php echo $row_select_parcours['idEpreuveParcours']; ?>" class="btn btn-info btn-lg" style="font-weight:bold" type="submit" value="Sélectionner" <?php echo $option_aff_select; ?>>
																	<!-- <div id="aff_check_place" style="display:none" class="alert-danger" style="margin-top: 10px;"><strong> Etape suivante ></div>/-->
															<input type="hidden" id="relais" name="relais" value="1">
															</form>
															</div>
														</td>
													</tr>
											<?php } ?>	
											<?php if ($color==2) $color=1; else $color++;
											$nb++;
											} ?>
									</tbody>
								</table>
										<!--	
										<span id="div-aff_code_promo" class="e">
											<div id="aff_code_promo_1"></div>
										</span>
										<legend class=""> 
											Montant total : <span class="badge badge-danger prix-ats m-r-40" id="prix_total_affichage_bas_relais">0 €</span>
											<input type="hidden" id="prix_total_relais" name="prix_total" value="0">
											<input type="hidden" id="relais" name="relais" value="1">
										</legend>
										<div id="aff_bouton_inscription" class="text-center">
											<p><input id="button_step_relais" class="btn btn-success btn-lg p-r-5" style="font-weight:bold" type="submit" value="Etape suivante >"></p>
										</div>
										/-->
	
							
							
						<?php } ?>
			<?php } else { header('Location: insc.php?id_epreuve='.$_SESSION['idEpreuve'].'&step=start'); } ?> 

									
						</div>
					</div>
				</div>
					<div class="col-md-3"></div>	
			<?php  } elseif ($_GET['step'] ==2 && empty($_SESSION['log_id'])) { ?>
		<?php 
		
		
		
		
		
		if ($_POST['relais']==1)
		{
			
			//print_r($_POST['relais_nbplace']);
			$seg = explode("_",$_POST['relais_nbplace']);
			//echo "nb : ".$seg[0]." - Id_tarif : ".$seg[1]." - tarif : ".$seg[2]."</br>";	
			$tarif  = $seg[1];
			$nb_relais = $seg[0];
			if (empty($_SESSION['tarifs']))  $_SESSION['tarifs'] = $tarif;
			$_SESSION['nb_relais'] = $nb_relais;
		}
		else
		{
			$first = TRUE;
			foreach ($_POST['nbplace'] as $value)
			{
				$seg = explode("_",$value);
				//echo "nb : ".$seg[0]." - Id_tarif : ".$seg[1]." - tarif : ".$seg[2]."</br>";		
				if ($seg[0]>0)
				{
					
					for ($i=0;$i<$seg[0];$i++)
					{
						if ($first==TRUE)
						{
							$tarif .=$seg[1];
							$first = FALSE;
						}
						else
						{
							$tarif .=",".$seg[1];
						}
					}
				}
				
				
			}
			if (empty($_SESSION['tarifs']))  $_SESSION['tarifs'] = $_POST['tarif_select'];
			if (empty($_SESSION['option_plus']))  $_SESSION['option_plus'] = $_POST['select_option_plus'];
		}
		//echo $tarif;
		
		//echo "x : ".$_SESSION['tarifs'];
		//echo $_SESSION['nb_relais'] = $nb_relais;
		//echo $_SESSION['new_user'] = 'nouveau';
		//echo "ID : ".$_SESSION['log_id']."- nouveau";
		 $_SESSION['rieis'] = array();
		 $_SESSION['idInternautes'] = array();
		?>
            <!-- begin container -->
		<div class="col-md-3"></div>
		<div class="panel panel-primary col-md-6">
					<?php if ($panel == 0) { ?>	
						<div class="breadcrumb flat no-nums hidden-xs">
							<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>" ><i class="fa fa-1x fa-home"></i></a>
							<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&tp=s">Parcours</a>
							<a href="javascript:;" class="active">Connexion</a>
							<a href="javascript:;">Formulaire</a>
							<a href="javascript:;">Panier</a>
							<a href="javascript:;">Validation</a>
						</div>
					<?php } ?>
		<!--<div class="panel panel-inverse" data-sortable-id="index-1" style="background-color:#0e5887">-->
			<div class="panel-heading">

				<h4 class="panel-title"><?php echo $data_epreuve['nomEpreuve']; ?></h4>
			</div>
			<div class="panel-body bg-white">
            <!-- end news-feed -->
				<!-- begin right-content -->
				<div class="right-content"  id="login" style="display:none">
					<!-- begin login-header -->
									<?php  if ($mdp_perdu==1) { ?>
										<?php if ($mdp_perdu_etat==1) { ?>
											<div class="alert alert-success fade in m-b-15">
												
												Un email vous à été envoyé pour changer votre mot de passe
												<span class="close" data-dismiss="alert">×</span>
											</div>
										<?php } else if ($mdp_perdu_etat==2) { ?>
											<div class="alert alert-danger fade in m-b-15">
												
												Une erreur s'est produite, mauvais compte et/ou email
												<span class="close" data-dismiss="alert">×</span>
											</div>
										<?php } ?>
									<?php 
								
									
									} ?>
					<legend id="legend_ident_off">Veuillez vous identifer</legend>
					<legend id="legend_ident_on" style="display:none">Connexion réussie !</legend>
					<!-- end login-header -->
					<!-- begin login-content -->
					<div class="login-content" >
							<span id="info_connexion">
								<div class="form-group m-b-15">
									<input type="text" <?php if (!empty($nom_internaute_perdu)) { ?> value="<?php echo $nom_internaute_perdu; ?>" <?php } else { ?> value="<?php echo $_SESSION["loginInternaute"]; ?>" <?php } ?> name="connex_email" id="connex_email" class="form-control input-lg" placeholder="Votre identifiant (ex : MARTI610874)" required />
								</div>
								<div class="form-group m-b-15">
									<input type="password" name="connex_pass" id="connex_pass" class="form-control input-lg" placeholder="mot de passe" required />
								</div>
								
								<div class="login-buttons">
									<input type="submit" class="btn btn-info btn-block btn-lg" id="connex_email_suivant" value="Suivant">
									<a type="button" class="btn btn-info btn-block btn-lg" href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?><?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" >Retour</a>
								</div>
												<?php if (!empty($nom_internaute_perdu)) { ?>
															<div class="form-group" id="aff_alert_noident_2">
																<label class="control-label text-success" ><b>Votre identifiant a été retrouvé et s'affiche ci-dessus. Veuillez indiquer votre mot de passe </b></label>
															</div>
												<?php } elseif ($ident_perdu_etat==2) { ?>
															<div class="form-group" id="aff_alert_noident_2">
																<label class="control-label text-danger" >Aucun identifiant n'a été trouvé avec ces informations</label>
															</div>
												<?php } ?>
															<div class="form-group" id="aff_alert_noident" style="display:none">
																<label class="control-label text-danger" >Identifiant et/ou mot de passe incorrect</label>
															</div>
										
										<div class="m-t-20 text-inverse">
											S'incrire sans compte ? Cliquer <a href="javascript:;" onclick="show_register(1)" >ICI</a>
											</br>
											<a href="#modal-dialog_ident" data-toggle="modal">Identifiant oublié  ?</a>
											<a href="#modal-dialog_mdp" data-toggle="modal">Mot de passe oublié ?</a>
										</div>
							</span>
					
							
										<div class="form-group" id="ident_ok" style="display:none">
											<label class="col-md-12 control-label" id="aff_ident_ok" ></label>
										</div>

							
										<form action="https://www.ats-sport.com/temp/formulaire.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?><?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" method="POST" id="insc_form" class="margin-bottom-0" enctype="multipart/form-data">
											<input type="hidden" id="prix_total" name="prix_total" value="<?php echo $_POST['prix_total']; ?>">
											<input type="hidden" id="param_t" name="param_t" value="<?php echo $_SESSION['tarifs']; ?>">
											<input type="hidden" id="relais" name="relais" value="<?php echo $_POST['relais'] ?>">											
											<input type="submit" class="btn btn-info btn-block btn-lg" id="connex_email_cont" style="display:none" value="Continuer l'inscription">
										</form>						
					</div>
					<!-- end login-content -->
				</div>

                <div class="register-content" id="register">
                    <form action="index.html" method="POST" class="margin-bottom-0">
                        <div class="register-buttons">
                            <a href="javascript:;" class="btn btn-info btn-block btn-lg" onclick="show_register(0)" >Je possède un compte ATS-SPORT ? </br>Je clique ICI</a>
                        </div>
						<hr>
						<span class="text-center" >Ou je renseigne mes informations ci-dessous</span>
						<hr>
                        <label class="control-label">Nom <span class="text-danger">*</span></label>
                        <div class="row row-space-10">
                            <div class="col-md-6 m-b-15">
                                <input value ="" type="text" class="form-control" placeholder="Nom de famille" id="insc_nom" name="insc_nom" required />
                            </div>
                            <div class="col-md-6 m-b-15">
                                <input value ="" type="text" class="form-control" placeholder="Prénom" name="insc_prenom" id="insc_prenom" required />
                            </div>
                        </div>
                        <label class="control-label">Email <span class="text-danger">*</span></label>
                        <div class="row m-b-15">
                            <div class="col-md-12">
                                <input value ="adresse@email.com" onchange="check_email(this.value,1);" type="text" class="form-control" placeholder="Adresse email" name="insc_email" id="insc_email" required />
								<span class="" id="span-pass_nominal"></span>
                            </div>
                        </div>
                        <label class="control-label">Confirmer votre adresse email <span class="text-danger">*</span></label>
                        <div class="row m-b-15">
                            <div class="col-md-12">
                                <input value ="" onchange="check_email(this.value,2);" type="text" class="form-control" placeholder="reconfirmer votre email" id="insc_email_reconfirm" required />
								<span id="span-pass_check"></span>
                            </div>
                        </div>
                        <label class="control-label">Sexe <span class="text-danger">*</span></label>
                        <div class="row m-b-15">
                            <div class="col-md-12" >
							<select class="form-control" name="insc_sexe" id="insc_sexe">
                                <option value="M">Homme</option>
								<option value="F">Femme</option> 
                            </select>
              
                            </div>
                        </div>
                        <label class="control-label">Date de naissance <span class="text-danger">*</span></label>
                        <div class="row m-b-15">
                            <div class="col-md-12">
								<input value ="" id="datepicker-default" type="text" class="form-control" placeholder="JJ/MM/AAAA" name="insc_naissance" required />
                            </div>
                        </div>
						
                        <div class="checkbox ">
                            <label>
                                <input id="inscription_libre" type="checkbox" /><h5>Je souhaite créer un compte ATS-SPORT</h5></a>
                            </label>
                        </div>
						<span id="mot_de_passe" style="display:none">
							<label class="control-label">
							Mot de passe <span class="text-danger">* <br/><span style="font-size:10px" class="text-danger"><i>Le mot de passe doit contenir des miniscules, majuscules, chiffres et minimum 8 caractères</i></span></label>
							<div class="row m-b-15">
								<div class="col-md-12" id="div-pass_nominal">
									<input value ="" type="password" class="form-control" placeholder="Mot de passe" name="insc_pass" id="insc_pass" Onchange="Check(this.value);" />
								</div>
							</div>
							<label class="control-label">Confirmez votre mot de passe<span class="text-danger">*</span></label>
							<div class="row m-b-15">
								<div class="col-md-12" id="div-pass_check">
									<input value ="" onchange="check_pass(this.value);" type="password" class="form-control" placeholder="Mot de passe à confirmer" name="insc_pass_confirm" id="insc_pass_confirm"  />
								</div>
							</div>                       
                        </span>
                        <div class="checkbox m-b-30">
                            <label>
                                <input id="reglement_pp_check" type="checkbox" required />J'accepte le réglement intérieur de <a class="reglement_pp_modal text-success" href="#reglement_pp"><strong>Ats Sport</strong></a>.
                            </label>
                        </div>
						<div class="col-sm-12" id="reglement_pp" style="display:none"><?php echo $condition['val']; ?></div>
                        <div class="register-buttons">
                            <input type="submit" class="btn btn-info btn-block btn-lg" id="connex_sign_up" value="S'enregistrer">
                        </div>
                        <div class="m-t-20 text-inverse">
                            Déjà membre ? Cliquer <a href="#javascript:;" onclick="show_login();">ICI</a> pour se connecter.
                        </div>
                    </form>
                </div>
				<!-- end right-container -->
			

			</div>
		</div>		
		<div class="col-md-3"></div>	
			<?php  } elseif ($_GET['step'] ==2 && isset($_SESSION['log_id'])) { ?>
		<?php
		
		if ($_POST['relais']==1)
		{
			
			//print_r($_POST['relais_nbplace']);
			$seg = explode("_",$_POST['relais_nbplace']);
			//echo "nb : ".$seg[0]." - Id_tarif : ".$seg[1]." - tarif : ".$seg[2]."</br>";	
			$tarif  = $seg[1];
			$nb_relais = $seg[0];
			if (empty($_SESSION['tarifs']))  $_SESSION['tarifs'] = $tarif;
			$_SESSION['nb_relais'] = $nb_relais;
		}
		else
		{
			$first = TRUE;
			foreach ($_POST['nbplace'] as $value)
			{
				$seg = explode("_",$value);
				//echo "nb : ".$seg[0]." - Id_tarif : ".$seg[1]." - tarif : ".$seg[2]."</br>";		
				if ($seg[0]>0)
				{
					
					for ($i=0;$i<$seg[0];$i++)
					{
						if ($first==TRUE)
						{
							$tarif .=$seg[1];
							$first = FALSE;
						}
						else
						{
							$tarif .=",".$seg[1];
						}
					}
				}
				
				
			}
			if (empty($_SESSION['tarifs']))  $_SESSION['tarifs'] = $_POST['tarif_select'];
			if (empty($_SESSION['option_plus']))  $_SESSION['option_plus'] = $_POST['select_option_plus'];
		}
		
		//echo "x : ".$_SESSION['tarifs'];
		
		$_SESSION['new_user'] = extract_champ_internaute('etat',$_SESSION['log_id']);
		//echo "ID : ".$_SESSION['log_id']."-".$_SESSION['new_user'];
		?>			<div class="col-md-3"></div>
				<?php if ($panel == 0) { ?>
					<div class="panel panel-primary col-md-6" >
						<div class="breadcrumb flat no-nums hidden-xs">
							<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>" ><i class="fa fa-1x fa-home"></i></a>
							<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&tp=s">Parcours</a>
							<a href="javascript:;" class="active">Connexion</a>
							<a href="javascript:;">Formulaire</a>
							<a href="javascript:;">Panier</a>
							<a href="javascript:;">Validation</a>
						</div>
				<?php }?>
					<!--<div class="panel panel-inverse" data-sortable-id="index-1" style="background-color:#0e5887">-->
						<div class="panel-heading">

							<h4 class="panel-title"><?php echo $data_epreuve['nomEpreuve']; ?></h4>
						</div>
						<div class="panel-body bg-white">				
							
										
												<div class="right-content"  id="login">
													<!-- begin login-header -->
													<?php if ($_SESSION["user_no_compte"]==0) { ?>
														<legend id="legend_ident_on">Connexion réussie !</legend>
													<?php } ?>

													<!-- end login-header -->
													<!-- begin login-content -->
													<div class="login-content">
														<div class="form-group" id="ident_ok" >
															<label class="col-md-12 control-label" id="aff_ident_ok" ><legend>Bonjour <?php echo $_SESSION["prenomInternaute"]; ?> <?php echo $_SESSION["nomInternaute"]; ?></legend></label>
														</div>
								
															
																		<form action="https://www.ats-sport.com/formulaire.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?><?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" method="POST" id="insc_form" class="margin-bottom-0" enctype="multipart/form-data">
																			<input type="hidden" id="prix_total" name="prix_total" value="<?php echo $_POST['prix_total']; ?>">
																			<input type="hidden" id="param_t" name="param_t" value="<?php echo $_SESSION['tarifs']; ?>">
																			<input type="hidden" id="relais" name="relais" value="<?php echo $_POST['relais'] ?>">
																			
																			<input type="submit" class="btn btn-info btn-block btn-lg" id="connex_email_cont" value="Continuer l'inscription">
																		</form>						
													</div>
													<!-- end login-content -->
												</div>									

							
			
						</div>
					</div>		
					<div class="col-md-3"></div>
			
			<?php  } elseif ($_GET['step'] ==3 && isset($_SESSION['log_id'])) { ?>
		<?php
/*		
echo "xxxxxxxxxxxxxxxx ".print_r($_SESSION['rieis']);
echo "yyyyyyyyyyyyyyy ".print_r($_SESSION['tarifs']);
		//unset($_SESSION['id_ref_relais']);
ini_set("display_errors", 1);
error_reporting(E_ERROR);
*/
		//echo "XXXXXXXXXXXXXXX".$_SESSION['log_id'];
		//exit();
		if (empty($_SESSION['log_id'])) { header('Location: insc.php?id_epreuve='.$_SESSION['idEpreuve'].'&step=start'); }
//GESTION DU PANIER
		$type_certificat_bdd = type_epreuve($_SESSION['idEpreuve']);
		$query_select_certificat_fichier = $type_certificat_bdd['type_nom_bdd'];
		$query_select_peremption = $type_certificat_bdd['nom_date_nom_bdd'];
		if (isset($_SESSION['id_ref_temp'])) 
		{
			
			$query_del = "DELETE FROM r_internautereferent WHERE idInternauteReferent = ".$_SESSION['id_ref_temp'];
			$mysqli->query($query_del);
			
			$query_del = "DELETE FROM r_insc_internautereferent WHERE idInternauteReferent = ".$_SESSION['id_ref_temp'];
			$mysqli->query($query_del);			
			unset($_SESSION['id_ref_temp']);
		}
//echo "UPDATE :  ".$_POST['update'];
$update_ok = 0;
if ($_POST['update']==1) {
	//echo "je suis dans UPDATE";
	//print_r($_POST);
	require('admin/includes/functions_insc_update.php');
	//echo "totot";
	//header('Location: moncompte.php');
	//echo "taraea";
//header('Location: '.basename($_SERVER['REQUEST_URI']));

}
				//$_SESSION['equipes'] = array();
				//$_SESSION['equipes_Idref'] = array();
				//$_SESSION['equipes_rieis'] = array();
//GESTION des équipes

			//if ($_POST['relais'] > 0) 
			//{
			
			if ($update==0 && $_POST['relais'] > 0 ) {
				

				
				if ($paiement_frais_cb==1) { $somme_total = $_SESSION['somme_frais_cb'] + $_SESSION['panier']; } else {  $somme_total = $_SESSION['panier']; }
				$all_internaute_tmp = array();
				$all_parcours_tmp = array();
				
				foreach ($last_inscriptions as $riei)
				{
					$id_internaute_tmp = extract_champ_epreuve_internaute('idInternaute',$riei);
					$id_parcours_tmp = extract_champ_epreuve_internaute('idEpreuveParcours',$riei);
					
					array_push($all_internaute_tmp,$id_internaute_tmp);
					array_push($all_parcours_tmp,$id_parcours_tmp);
					$_SESSION['equipes_rieis'][] = $riei;
					
				}
				
				$id_internaute_referent = extract_champ_epreuve_internaute('idInternaute',$last_inscriptions[0]);
				$id_internaute_inscription_referent = $last_inscriptions[0];
				
				$nomInternaute = extract_champ_internaute('nomInternaute',$all_internaute_tmp[0]);
				$prenomInternaute = extract_champ_internaute('prenomInternaute',$all_internaute_tmp[0]);
				
				$NomRespEquipe = $nomInternaute." ".$prenomInternaute;
				$TelRespEquipe = extract_champ_internaute('telephone',$all_internaute_tmp[0]);
				
				$email_internaute = extract_champ_internaute('emailInternaute',$all_internaute_tmp[0]);
				
					//$removed = array_shift($hobbies);
					$all_id_inscription_internaute_tmp = $last_inscriptions;
					//print_r($all_id_inscription_internaute_tmp);
					//$all_id_inscription_internaute_tmp = array_shift($all_id_inscription_internaute_tmp);
					unset($all_id_inscription_internaute_tmp[0]);
					//print_r($all_id_inscription_internaute_tmp);
					$all_id_inscription_internaute = implode('|', $all_id_inscription_internaute_tmp);
					//echo $all_id_inscription_internaute;
					
					unset($all_internaute_tmp[0]);
					$all_id_internaute = implode('|', $all_internaute_tmp);
					
					unset($all_parcours_tmp[0]);
					$all_id_parcours = implode('|', $all_parcours_tmp);
					
				$equipe = $_POST['insc_nom_equipe'];
				
				//$lastid_epreuve_inscription
				if($_POST['relais'] > 0) { $relais = 'oui'; }
						
				if (!empty($equipe)) $NomEquipe = addslashes($equipe); else $NomEquipe = 'Aucune';
							
							if (!empty($_SESSION['id_unique_paiement'])) $code_paiement = $_SESSION['id_unique_paiement'] ; else $code_paiement = NULL;
							
							if ($id_internaute_referent==$_SESSION['log_id']) $InscritParidInternaute = 'NULL'; else $InscritParidInternaute = $_SESSION['log_id'];
							$query_referent = "INSERT INTO r_internautereferent ";
							$query_referent .= "(idInternauteref, idInternauteInscriptionref, idInternautes, idInscriptionEpreuveInternautes,relais,idEpreuve,NomEquipe,NomRespEquipe,TelRespEquipe,InscritParidInternaute,id_unique_paiement) VALUES ";
							$query_referent .="(".$id_internaute_referent.", ".$id_internaute_inscription_referent.", '".$all_id_internaute."','".$all_id_inscription_internaute."','".$relais."',".$_SESSION['idEpreuve'].", '".addslashes_form_to_sql($NomEquipe)."', UPPER('".addslashes_form_to_sql($NomRespEquipe)."'), '".addslashes_form_to_sql($TelRespEquipe)."', ".$InscritParidInternaute.",'".$code_paiement."' )";
							$result_query = $mysqli->query($query_referent);
							$id_ref_temp_e = $mysqli->insert_id;
							//$_SESSION['id_ref_temp'] = $id_ref_temp;
							
							/*** LOGS ***/
							fputs($fp," (update==0 && _POST['relais'] > 0 )  :  INSERT INTO r_internautereferent : ".$query_referent." \n");
							/*** LOGS ***/
							
							//echo $query_referent;
							$_SESSION['id_ref_relais'][] = $id_referent = $mysqli->insert_id;
							//if (!empty($equipe)) $query_temp .= "'".addslashes($equipe)."',"; else $query_temp .= "'Aucune',";
							//f($payeur == 'coureur') $prix_total_multi = ($_POST['prix_total'] + $frais_cb_total); else $prix_total_multi = $_POST['prix_total'];
							
							$frais_cb_ref = round(calcul_frais_cb(($cout_tarif + $cout_option_m),$montant_inscription_equipe,$cout_paiement_cb,$cout_participation),2);
							
							$query_referent = "INSERT INTO r_insc_internautereferent ";
							$query_referent .= "(idInternauteReferent, idEpreuve, idEpreuveParcours, frais_cb, montant) VALUES ";
							$query_referent .="(".$id_referent.",".$_SESSION['idEpreuve'].",'".$all_id_parcours."',".$frais_cb_ref.",".$montant_inscription_equipe.")";
							//***echo $query_referent;
							$result_query = $mysqli->query($query_referent);
							/*** LOGS ***/
							fputs($fp," (update==0 && _POST['relais'] > 0 )  :  INSERT INTO r_insc_internautereferent : ".$query_referent." \n");
							/*** LOGS ***/
							//$id_ref_bp = 'M-'.$id_referent;
				$_SESSION['equipes'][] = $id_ref_temp_e;
				$_SESSION['equipes_Idref'][] = $id_internaute_referent;
				$_SESSION['equipes_rieis'][] = $id_internaute_inscription_referent;


			}
			//print_r($_SESSION['id_ref_relais']);

//GESTION DU PANIER
			if (empty($_SESSION['tarifs']))  $_SESSION['tarifs'] = $_POST['tarif_select'];
	
	//echo "azeaez:".$_SESSION['option_plus'];
	if (empty($_SESSION['option_plus'])) $_SESSION['option_plus'] = $_POST['select_option_plus'];
	$Prix_OptionPlus = 0;
	list($idOptionPlus,$Prix_OptionPlus)= explode('|', $_SESSION['option_plus']);
	//echo $idOptionPlus."---".$Prix_OptionPlus."---".$information;
	
			/*
			echo "x : ".$_SESSION['tarifs'];
			echo $_SESSION['nb_relais'] = $nb_relais;
			echo $_SESSION['new_user'] = extract_champ_internaute('etat',$_SESSION['log_id']);
			echo "ID : ".$_SESSION['log_id']."-".$_SESSION['new_user'];
			*/
			
				//$_SESSION['rieis'] = array_push($lastid_epreuve_inscription);
				//echo "xxx :".$lastid_epreuve_inscription;
			//if (isset($lastid_epreuve_inscription)) $_SESSION['rieis'][] = $lastid_epreuve_inscription;
			
			if (isset($_GET['del_reg'])) 
			{ 
				echo "je suis dans le del";
				if ($_GET['del_reg']=='o')
				{
					
					foreach ($_SESSION['rieis'] as $id)
					{
						
						
						$id_tmp_epreuve_parcours=extract_champ_epreuve_internaute('idEpreuveParcours',$id);
						unset($_SESSION['info_caddie']);
						delete_inscription_internaute (0,$id,$_SESSION['idEpreuve'],$id_tmp_epreuve_parcours,'');						
						
					}
					//***unset($_SESSION['rieis'],$_SESSION['tarifs'],$_SESSION['info_caddie'], $_SESSION['id_bp_temp'], $_SESSION['equipes'], $_SESSION['equipes_Idref'], $_SESSION['equipes_rieis']);
					unset($_SESSION['panier'],$_SESSION['tarifs'],$_SESSION['idEpreuvePersoPre'],$_SESSION['paiement_indiv'],$_SESSION['groupe'],$_SESSION['somme_frais_cb'],$_SESSION['rieis']);
					unset($_SESSION['option_plus'],$_SESSION['idEpreuve'],$_SESSION['nb_relais'],$_SESSION['new_user'],$_SESSION['info_caddie'],$_SESSION['id_ref_temp'],$_SESSION['unique_id_session']);
					unset($_SESSION['id_internaute_referent'],$_SESSION['nom_Internaute'],$_SESSION['prenom_Internaute']);
					unset($_SESSION['bp_paiement_autre_multiple'],$_SESSION['autre_personne'],$_SESSION['equipes'],$_SESSION['equipes_rieis'],$_SESSION['equipe_participation'],$_SESSION['equipe_tarif_et_option']);
					unset($_SESSION['equipes_Idref'],$_SESSION['nb_inscrit'],$_SESSION['idInternautes'],$_SESSION['id_unique_paiement'],$_SESSION['nb_inscription_solo']);
					$_SESSION['idInternautes'] = $_SESSION['equipes'] = array();
				}
				else
				{
					//ECHO "ICIC ".$_GET['r']; 
					
					
					
					if ($_GET['r']==1) 
					{
						
						if ($_GET['del_reg']=='e')
						{
							//echo "errzerzerezrzer";
							delete_equipe($_SESSION['log_id'], $_GET['id'], $_SESSION['idEpreuve']);
							$_SESSION['info_caddie']=($_SESSION['info_caddie']-1);
							if (empty($_SESSION['info_caddie']))  { 
							
							
								unset($_SESSION['panier'],$_SESSION['tarifs'],$_SESSION['idEpreuvePersoPre'],$_SESSION['paiement_indiv'],$_SESSION['groupe'],$_SESSION['somme_frais_cb'],$_SESSION['rieis']);
								unset($_SESSION['option_plus'],$_SESSION['idEpreuve'],$_SESSION['nb_relais'],$_SESSION['new_user'],$_SESSION['info_caddie'],$_SESSION['id_ref_temp'],$_SESSION['unique_id_session']);
								unset($_SESSION['id_internaute_referent'],$_SESSION['nom_Internaute'],$_SESSION['prenom_Internaute']);
								unset($_SESSION['bp_paiement_autre_multiple'],$_SESSION['autre_personne'],$_SESSION['equipes'],$_SESSION['equipes_rieis'],$_SESSION['equipe_participation'],$_SESSION['equipe_tarif_et_option']);
								unset($_SESSION['equipes_Idref'],$_SESSION['nb_inscrit'],$_SESSION['idInternautes'],$_SESSION['id_unique_paiement'],$_SESSION['nb_inscription_solo']);
								$_SESSION['idInternautes'] = $_SESSION['equipes'] = array();
							}
						}
						else
						{
							
							$id_a_trouver = $_SESSION['rieis'][$_GET['del_reg']];
							$id_tmp_epreuve_parcours=extract_champ_epreuve_internaute('idEpreuveParcours',$id_a_trouver);
							delete_inscription_internaute (0,$id_a_trouver,$_SESSION['idEpreuve'],$id_tmp_epreuve_parcours,'');
							unset($_SESSION['rieis'][$_GET['del_reg']],$_SESSION['tarifs'][$_GET['del_reg']],$_SESSION['idInternautes'][$_GET['del_reg']]);
							
							$del_idInscriptionEpreuveInternautes= extract_champ_r_internautereferent('idInscriptionEpreuveInternautes',$_GET['id']);
							$values = explode('|',$del_idInscriptionEpreuveInternautes);
							
							$new_idInscriptionEpreuveInternautes = array();
							foreach ($values as $idInscriptionEpreuveInternaute)
							{
								
								if ($idInscriptionEpreuveInternaute != $id_a_trouver) $new_idInscriptionEpreuveInternautes[] = $idInscriptionEpreuveInternaute;
								
							} 							
							$all_id_inscription_internaute = implode('|', $new_idInscriptionEpreuveInternautes);
							$query="UPDATE r_internautereferent SET idInscriptionEpreuveInternautes = '".$all_id_inscription_internaute."' WHERE idInternauteReferent = ".$_GET['id'];
							$result_query = $mysqli->query($query);
							/*** LOGS ***/
							fputs($fp," _GET['r']==1:  UPDATE r_internautereferent : ".$query." \n");
							/*** LOGS ***/
							
						}
						
					}
					else
					{
						
						$id_tmp_epreuve_parcours=extract_champ_epreuve_internaute('idEpreuveParcours',$_SESSION['rieis'][$_GET['del_reg']]);
						delete_inscription_internaute (0,$_SESSION['rieis'][$_GET['del_reg']],$_SESSION['idEpreuve'],$id_tmp_epreuve_parcours,'');
						unset($_SESSION['rieis'][$_GET['del_reg']],$_SESSION['tarifs'][$_GET['del_reg']],$_SESSION['idInternautes'][$_GET['del_reg']]);
						$_SESSION['info_caddie']=($_SESSION['info_caddie']-1); $_SESSION['nb_inscription_solo'] = ($_SESSION['nb_inscription_solo']-1);
							if (empty($_SESSION['info_caddie']))  { 
							
							
								unset($_SESSION['panier'],$_SESSION['tarifs'],$_SESSION['idEpreuvePersoPre'],$_SESSION['paiement_indiv'],$_SESSION['groupe'],$_SESSION['somme_frais_cb'],$_SESSION['rieis']);
								unset($_SESSION['option_plus'],$_SESSION['idEpreuve'],$_SESSION['nb_relais'],$_SESSION['new_user'],$_SESSION['info_caddie'],$_SESSION['id_ref_temp'],$_SESSION['unique_id_session']);
								unset($_SESSION['id_internaute_referent'],$_SESSION['nom_Internaute'],$_SESSION['prenom_Internaute']);
								unset($_SESSION['bp_paiement_autre_multiple'],$_SESSION['autre_personne'],$_SESSION['equipes'],$_SESSION['equipes_rieis'],$_SESSION['equipe_participation'],$_SESSION['equipe_tarif_et_option']);
								unset($_SESSION['equipes_Idref'],$_SESSION['nb_inscrit'],$_SESSION['idInternautes'],$_SESSION['id_unique_paiement'],$_SESSION['nb_inscription_solo']);
								$_SESSION['idInternautes'] = $_SESSION['equipes'] = array();
								
							}
						
					}

					
					//delete_inscription_internaute (0,$_SESSION['rieis'][$_GET['del_reg']],$_SESSION['idEpreuve'],$id_tmp_epreuve_parcours,'') {
					//unset($_SESSION['tarifs'][$_GET['del_reg']]);
				}
			//echo "info_caddie REG : ".$_SESSION['info_caddie'];
			}
			
		
		//print_r($_SESSION['rieis']);
		//echo "AVANT - ".count($_SESSION['rieis'])." - IC IC IC :".print_r($_SESSION['rieis']);
		if (count($_SESSION['rieis']) > 0) { 
		//echo "IC IC IC :".print_r($_SESSION['rieis']);
		?>
					<div class="col-md-3"></div>
					<div class="panel panel-primary col-md-6" style="max-width: 768px;">
					<?php if ($panel == 0) { ?>	
						<div class="breadcrumb flat no-nums hidden-xs">
							<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>" ><i class="fa fa-1x fa-home"></i></a>
							<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&tp=s">Parcours</a>
							<a href="javascript:;" >Connexion</a>
							<a href="javascript:;">Formulaire</a>
							<a href="javascript:;" class="active">Panier</a>
							<a href="javascript:;">Validation</a>
						</div>
					<?php  } ?>
							<div class="panel-heading">
								<h4 class="panel-title"><?php echo $data_epreuve['nomEpreuve']; ?></h4>
							</div>
							<div class="panel-body bg-white">				
								<div class="row">
									<div class="col-sm-8">
										<?php
											$insc_solo = $_SESSION['rieis'];
											$cpt=0;
											$nb_equipe = 0;
											$frais_cb_coureur_r = array();
											$tarif_total_r=0;
											$total_participation_r=0;
											$somme_frais_cb_r=0;
											$somme_total_r = 0;
											$somme_total_assurance = 0;
											$cout_assurance_user_r = 0;
											$_SESSION['equipe_participation'] =0;
											$_SESSION['equipe_tarif_et_option'] =0;
											$_SESSION['somme_frais_cb'] =0;
											foreach ($_SESSION['rieis'] as $riei)
											{
												$total_participation = 0;
												$idInternauteReferent = extract_champ_r_idInternauteInscriptionref('idInternauteReferent',$riei);
												//echo "C'est une equipe si :".$riei."----".$idInternauteReferent."#####";
												//echo "XXXXX: ". $_SESSION['relais']." XXXXX";
												//if ($idInternauteReferent && !empty($_SESSION['relais']))
												if ($idInternauteReferent) 
												{	
													$id_ref_idInscriptionEpreuveInternaute = $riei;
													//echo "idInternauteReferent".$idInternauteReferent;
													
													
													
													$cout_equipe = extract_champ_r_insc_internautereferent('montant',$idInternauteReferent);
													//$cout_equipe = extract_champ_r_insc_internautereferent('montant',$idInternauteReferent);
													
													
													$equipe_array = select_equipe($_SESSION['idEpreuve'],0,$idInternauteReferent);
													$cout_tarif = extract_champ_tarif('tarif',$equipe_array[0]['idEpreuveParcoursTarif']);
													//print_r($equipe_array);
													$nom_parcours=extract_champ_parcours('nomParcours',extract_champ_epreuve_internaute('idEpreuveParcours',$equipe_array[0]['idInscriptionEpreuveInternaute']));
													$equipe_explode = explode("|",$equipe_array[0]['idInscriptionEpreuveInternautes']);
													
													//*** ASSURANCE ANNULATION ****/
													//echo $equipe_array[0]['idEpreuveParcours'];
													$assurance_annulation=assurance_annulation($_SESSION['idEpreuve'],$equipe_array[0]['idEpreuveParcours']);
													//print_r($assurance_annulation);
													$assurance = 0;
													$id_assurance_annulation = 0;
													$type_assurance_annulation='';
													
																									//CHECK CODE PROMO
													$exist_code_promo = exist_code_promo($_SESSION['idEpreuve'],$equipe_array[0]['idEpreuveParcours']);
													//CHECK CODE PROMO
													//ECHO "ICIC : ".$row['idEpreuveParcours'];
													$besoin_certif = besoin_certificat_medical($equipe_array[0]['idEpreuveParcours']);
													$besoin_auto_parentale = besoin_auto_parentale_parcours($equipe_array[0]['idEpreuveParcours']);
													if ($assurance_annulation['active'] =='oui') 
													{
														$assurance = 1;
														$type_assurance_annulation = $assurance_annulation['type'];
														$id_assurance_annulation = $assurance_annulation['idAssuranceAnnulation'];
														
														$id_assurance_annulation = extract_champ_assurance_annulation('idAssuranceAnnulation',$id_ref_idInscriptionEpreuveInternaute);
														
														if (empty($id_assurance_annulation))
														{
															$id_assurance_annulation = $assurance_annulation['idAssuranceAnnulation'];
														}
														else
														{
															$check_btn_assurance = 'checked';
															$cout_assurance_user_r += extract_champ_assurance_annulation('montant',$id_ref_idInscriptionEpreuveInternaute);
														}
														
														$aff_message_assu = "";
														//echo "XXXXXXXXXXXXXXXXXX:".$assurance_annulation['fixe'];
														if (empty($assurance_annulation['fixe'])) 
														{
															if ($assurance_annulation['type']=='tarif')
															{
																$aff_message_assu = "( <b><span class='text-danger'>+".$assurance_annulation['pourcentage']."%</span></b> sur le tarif d'inscription )";
															}
															elseif ($assurance_annulation['type']=='participation_parcours' || $assurance_annulation['type']=='participation_epreuve')
															{
																$aff_message_assu = "( <b><span class='text-danger'>+".$assurance_annulation['pourcentage']."%</span></b> sur les participations engagées )";
															}
															elseif ($assurance_annulation['type']=='option_supp')
															{
																$aff_message_assu = "( <b><span class='text-danger'>+".$assurance_annulation['pourcentage']."%</span></b> sur les options supplémentaires )";
															}
															elseif ($assurance_annulation['type']=='total')
															{
																$aff_message_assu = "( <b><span class='text-danger'>+".$assurance_annulation['pourcentage']."%</span></b> sur la totalité de l'inscription )";
															}

														}
														else
														{
															$aff_message_assu = "( <b><span class='text-danger'>+".$assurance_annulation['fixe']." € </span></b> )";
														}
													}
													
													?>
																		<table class="table" ><thead><th>Parcours</th><th>Equipe</th><th>Cout</th></thead>
																		<tbody>
																		<tr>
																			<td width="45%"><h5><?php echo $nom_parcours; ?></h5></td>
																			<td width="45%"><h5><?php echo $equipe_array[0]['NomEquipe']; ?></h5></td>
																			<td width="10%"><h5><?php echo $cout_tarif; ?> €</h5></td>
																		</tr>
																		
																		
																		<!-- Parcours : <b><?php echo $nom_parcours; ?> </b>- Equipe : <b><?php echo $equipe_array[0]['NomEquipe']; ?></b> - Cout : <b><?php echo $cout_equipe; ?> € </b> /-->
																		

																		<tr>
																		<td colspan="3">
																		<ul class="media-list media-list-with-divider" style="padding-top:5px">
																		<?php
																		$cout_paiement_cb = extract_champ_epreuve('cout_paiement_cb',$_SESSION['idEpreuve']);
																		
																		
																		$i=0;
																		$total_option_plus = 0;
																		$_SESSION['tarifs']=array();
																		foreach ($equipe_explode as $ri) {
																			
																			$query = "SELECT ri.idInternaute, ri.prenomInternaute, ri.nomInternaute, ri.sexeInternaute, ri.emailInternaute, ri.naissanceInternaute, ri.clubInternaute, ri.villeInternaute, ri.index_telephone, ri.telephone, ri.typeInternaute, ri.organisateur, ri.coureur, ri.fournisseur, ri.avatar, ri.adresseInternaute, ri.cpInternaute, ri.villeLatitude, ri.villeLongitude, ri.paysInternaute, ri.natInternaute, ri.".$query_select_certificat_fichier." as fichier_cert, ri.".$query_select_peremption." as peremption_cert,riei.date_insc, ";
																			$query .= " riei.date_insc, riei.idInscriptionEpreuveInternaute, riei.paiement_type, riei.montant_inscription, riei.paiement_montant, riei.equipe, riei.idEpreuveParcoursTarif,";
																			$query .=" riei.paiement_date, riei.frais_cb, riei.frais_cheque, riei.dossard, riei.commentaire, riei.observation, riei.verif_certif, riei.idEpreuveParcours,";
																			$query .=" riei.verif_auto_parentale, riei.info_cheque, riei.relance_certif, riei.relance_certif_date, riei.idEpreuveParcoursTarifPromo,riei.label_code_promo, riei.montant_code_promo,";
																			$query .=" riei.participation, riei.categorie, riei.relance_paiement, riei.relance_paiement_date, riei.inscription_par, riei.modifie_par, riei.modifie_date,";
																			$query .=" riei.verif_certif_ffa, riei.num_licence_ffa, riei.type_licence_ffa, riei.date_fin_licence_ffa, riei.num_club_ffa,";
																			//OPTION Plus et ASSURANCE
																			$query .=" riei.idOptionPlus,riei.Prix_OptionPlus,riei.assurance,";
																			//OPTION plus et ASSURANCE 	
																			$query .=" rib.remboursement, rib.frais_remboursement, rib.remboursement_effectue, rib.remboursement_date, riei.surcout, riei.frais_surcout, riei.surcout_effectue,rept.tarif, rept.idEpreuveParcoursTarif, riei.equipe, riei.groupe, riei.idGroupe, riei.num_licence_ffa ";
																			$query .=" FROM r_internaute as ri ";
																			$query .="INNER JOIN r_inscriptionepreuveinternaute as riei ON ri.idInternaute = riei.idInternaute ";
																			$query .="INNER JOIN r_epreuveparcourstarif as rept ON riei.idEpreuveParcoursTarif = rept.idEpreuveParcoursTarif ";
																			$query .="INNER JOIN r_epreuve ON riei.idEpreuve = r_epreuve.idEpreuve ";
																			$query .="INNER JOIN r_epreuveparcours ON riei.idEpreuveParcours = r_epreuveparcours.idEpreuveParcours ";
																			$query .="LEFT OUTER JOIN r_inscriptionremboursement as rib ON riei.idInscriptionRemboursement = rib.idInscriptionRemboursement ";
																			$query .="WHERE riei.idEpreuve = ".$_SESSION['idEpreuve']. " ";
																			//$query .="AND riei.idEpreuveParcours = ".$. " ";
																			//$query .="AND riei.date_insc = '".$date_insc . "' ";
																			//$query .="AND ri.validation = 'non' ";
																			//if (!empty($whereIn)) 
																			$query .="AND riei.idInscriptionEpreuveInternaute IN (".$ri.") "; 
																			//$query .=" ORDER BY idInscriptionEpreuveInternaute DESC ";
																			//else $query .="AND riei.id_session = '".md5($_GET['id']). "' ";
																			//echo $query;
																			$result_ri = $mysqli->query($query);
																			$nb_result_ri = mysqli_num_rows($result_ri);											
																			$row=mysqli_fetch_array($result_ri);
																			//$_SESSION['info_caddie']=$nb_result_ri;
																			
																			$tarif_en_cours = extract_champ_tarif('tarif',$row['idEpreuveParcoursTarif']);
																			//$_SESSION['tarifs'][] = $row['idEpreuveParcoursTarif'];
																			if ($i==0) 
																			{
																				$total_cout = $tarif_en_cours+ $row['Prix_OptionPlus'];
																				$total_option_plus = $row['Prix_OptionPlus'];
																				$total_participation = $row['participation'];
																				//$frais_cb_coureur_m[] = round(calcul_frais_cb($total_cout,$row['montant_inscription'],$cout_paiement_cb,$total_participation),2);
																			}
																			else
																			{
																				$total_participation += $row['participation'];
																				
																			}
																			
																			//$frais_cb_coureur_m[] = round(calcul_frais_cb(($tarif_en_cours+ $row['Prix_OptionPlus']),$row['montant_inscription'],$cout_paiement_cb,$row['participation']),2);
																			
																		//if ($i > 0) 
																			//echo "xxxxxxxxxxxxxxxxxxx :".$ref_relais = extract_champ_r_internautereferent('idInternauteInscriptionref',$_SESSION['id_ref_relais'][0]);
																		//if (extract_champ_r_internautereferent('idInternauteReferent',$_SESSION['id_ref_relais'][0])==$_SESSION['id_ref_relais'][0]) { $equipe_en_cous=1;?>
																			<!-- Equipe : <?php echo extract_champ_r_internautereferent('NomEquipe',$_SESSION['id_ref_relais'][0]); ?><br> /-->
																			
																		<?php  //}?>
																		<?php $id_reg = array_search($row['idInscriptionEpreuveInternaute'],$_SESSION['rieis']); ?>
										
																			
																			<li class="media media-sm" style="border-top:none;padding-top:0px;">
																				<a class="media-left" href="javascript:;">
																					<img src="admin/assets/img/course_a_pied.jpg" alt="" class="media-object rounded-corner">
																				</a>
																				<div class="media-body">
																					<h5 class="media-heading text-primary"><span class="small"><?php echo $row['nomInternaute']." ".$row['prenomInternaute']; ?><span class="small"><i> (<?php echo $row['emailInternaute']; ?></i>) </span></h5>
																																
																					<!-- <p>Parcours : <b><?php echo extract_champ_parcours('nomParcours',$row['idEpreuveParcours']); ?></b><br> /-->
																				
																					<!-- <b><?php echo ($row['participation']+$row['Prix_OptionPlus']) ;?> € </b>d'options /-->
															
															
																			<dl class="dl-horizontal">
																				<!-- <dt>Tarif du parcours</dt>
																				<dd><b><?php echo $tarif_en_cours; ?> € </b></dd> /-->
																				<?php if ($row['participation'] > 0) { ?>
																					<dt>Participation</dt>
																					<dd><b> <?php echo ($row['participation']) ;?> € </b></dd>
																				<?php } ?>
																				<?php if ($row['idOptionPlus'] > 0) { 
																					$nom_option = extract_champ_options_plus('label',$row['idOptionPlus']);
																				?>																
																					<dt><b><?php echo $nom_option; ?></b></dt>
																					<dd><b><?php echo ($row['Prix_OptionPlus']) ;?> € </b> [ <a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=opt&r=1&id=<?php echo $idInternauteReferent; ?><?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" >modifier</a>]</dd>
																				<?php } ?>
																				<?php
																				if ($besoin_certif==1) 
																				{
																					if ($row['verif_certif']=='non') 
																					{ 
																					?>
																							<dt>Certif. médical / licence </dt>
																							<dd class="text-warning"><b> Fourni </b></dd>
																					<?php 
																					}
																					elseif ($row['verif_certif']=='oui') 
																					{ 
																					?>
																							<dt>Certif. médical / licence </dt>
																							<dd class="text-success"><b> Validé(e)</b></dd>
																					<?php 
																					}
																					else
																					{
																					?>
																							<dt>Certif. médical / licence </dt>
																							<dd class="text-danger"><b> Non Fourni </b></dd>
																					<?php 																		
																						
																					}
																				}
																				else
																				{
																					?>
																							<dt>Certif. médical / licence </dt>
																							<dd class="text-success"><b> Pas de besoin </b></dd>
																					<?php 																	
																					
																				}
																				
																				if ($besoin_auto_parentale==1) 
																				{
																					if ($row['verif_auto_parentale']=='non') 
																					{ 
																					?>
																							<dt>Auto parentale </dt>
																							<dd class="text-warning"><b> Fourni </b></dd>
																					<?php 
																					}
																					else
																					{
																					?>
																							<dt>Auto parentale </dt>
																							<dd class="text-danger"><b> Non Fourni </b></dd>
																					<?php 																		
																						
																					}
																				}
																				else
																				{
																					?>
																							<dt>Auto parentale </dt>
																							<dd class="text-success"><b> Pas de besoin </b></dd>
																					<?php 																	
																					
																				}
																				?>
																				<?php if ($row['groupe'] != 'Aucun') { ?>
																					<dt>Groupe</dt>
																					<dd><b class="text-info"> <?php echo ($row['groupe']) ;?> </b></dd>
																				<?php } ?>																
																			</dl>


															
																					<br/>
																					<?php if ( $id_reg >0) { ?>
																						<!-- <a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=3&del_reg=<?php echo $id_reg; ?>&r=1&id=<?php echo $idInternauteReferent; ?>" class="btn btn-xs btn-danger">Supprimer</a> /-->
																					<?php } ?>
																					</p>
																				</div>
																			</li>
																			<?php 
																			//if ($equipe_en_cours==1) { $montant_total = $row['montant_inscription']; } else { $montant_total += $row['montant_inscription']; }
																			$i++; 
																			$cpt++;
																				// Search
																				$pos = array_search($ri, $insc_solo);
																				if (isset($pos)) unset($insc_solo[$pos]);
																		
																		}
																		if ($_SESSION['nb_relais'] != $i) $erreur_relais = 1; else $erreur_relais = 0;
																		?>	
																		</ul>
																		</td></tr>
																						<?php if ($assurance==1) { ?>
																						<tr>
																						<td colspan="3">
																							<input type="checkbox" id="assurance_annulation_<?php echo $id_ref_idInscriptionEpreuveInternaute ; ?>" name="assurance_annulation" onchange="calcul_frais_annulation(this.id,<?php echo $id_ref_idInscriptionEpreuveInternaute ; ?>,'<?php echo $type_assurance_annulation; ?>',<?php echo $assurance_annulation['pourcentage']; ?>,<?php echo $assurance_annulation['fixe']; ?>,<?php echo $cout_equipe; ?>,<?php echo $cout_tarif; ?>,<?php echo $id_assurance_annulation; ?>)" <?php echo $check_btn_assurance; ?>> <i>Je m'assure</i> - <span class="text-warning"><?php echo $aff_message_assu; ?> <a href="javascript:;" data-toggle="popover" title="Information importante" data-content="<?php echo $assurance_annulation['informations']; ?>" id="option_plus_information_<?php echo $row['idInscriptionEpreuveInternaute'] ; ?>"><i class="fa fa fa-exclamation-circle"/></i></a></span>
																						</td>
																						</tr>
																						<?php } ?>
																						<tr>
																						<td colspan="3">
																																			
																							<input onchange="vli(<?php echo $row['idInscriptionEpreuveInternaute'] ; ?>);" type="checkbox" id="reglement_cnil_vli">Je ne souhaite pas voir mon nom sur la liste des inscrits
																						</td>
																						</tr>
													
																		<tr>
																		<td > 
																					<a href="https://www.ats-sport.com/formulaire.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&r=1&id=<?php echo $idInternauteReferent; ?><?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" class="btn btn-xs btn-primary m-r-5">Modifier l'équipe</a></td>
																					<td ><a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=3&r=1&del_reg=e&id=<?php echo $idInternauteReferent; ?><?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" class="btn btn-xs btn-danger">Supprimer l'équipe</a></td>
																					<td >&nbsp;</td>
																		</table><hr>
																		
															<?php
													$somme_frais_cb_r += round(calcul_frais_cb(($cout_tarif+$total_option_plus),$tarif_total_r,$cout_paiement_cb,$total_participation),2);
													$nb_equipe++;
													$tarif_total_r += $cout_tarif+$total_option_plus;
													$somme_total_assurance += $cout_assurance_user_r;
												}
																		
																		
																		
																		$total_participation_r +=$total_participation;
													/*					
																		$somme_frais_cb = round(calcul_frais_cb($total_cout,$cout_tarif,$cout_paiement_cb,$total_participation),2);
																		echo "cout_tarif :".$cout_tarif." - montant_inscription : ". $row['montant_inscription']." - participation :".$total_participation." - somme_frais_cb :".$somme_frais_cb ;
																		$_SESSION['panier'] = $montant_total;
																		//print_r($_SESSION['tarifs']);
																		//print_r($frais_cb_coureur_m);
																		//$somme_frais_cb = array_sum($frais_cb_coureur_m);
																		//****$_SESSION['somme_frais_cb'] = $somme_frais_cb;
																		
																		//if ($paiement_frais_cb==1) { $somme_total = $somme_frais_cb + $_SESSION['panier']; } else {  $somme_total = $_SESSION['panier']; }
																		

																		
																		
																			$query="UPDATE r_insc_internautereferent SET frais_cb = '".$somme_frais_cb."',  montant = '".$somme_total."' WHERE idInternauteReferent = ".$_SESSION['id_ref_relais'][0];
																			$result = $mysqli->query($query);		
																			
																			
																			
																		
																		echo "SESSION FRAIS CB : ".$_SESSION['somme_frais_cb'];
												//echo "i : ".$i." - nb_equipe : ".$nb_equipe." _SESSION['info_caddie'] : ".$_SESSION['info_caddie'];
												echo "equipe : ".$_SESSION['info_caddie']=$nb_equipe;
												//echo " AFTER _SESSION['info_caddie'] : ".$_SESSION['info_caddie'];
												//print_r($equipe_array);
												*/
												$_SESSION['info_caddie'] = $nb_equipe;
												$_SESSION['equipe_participation'] = $total_participation_r;
												$_SESSION['equipe_tarif_et_option'] = $tarif_total_r;
											}
											
											//TOTAL TOUTES EQUIPES
											
											//echo "tarif_total_r : ".$tarif_total_r;
											//echo "total participation : ".$total_participation_r;
											$total_cout_equipe = $tarif_total_r + $total_participation_r;
											
											
											$somme_total_r = $tarif_total_r + $total_participation_r;
											/*
											if ($paiement_frais_cb==1) 
											{
												$somme_total_r = $somme_frais_cb_r + $total_cout_equipe; 
												$_SESSION['somme_frais_cb'] = $somme_frais_cb_r;
											} 
											else 
											{  
												$somme_total_r = $total_cout_equipe; 
												$_SESSION['somme_frais_cb'] = $frais_cb_orga_fixe;
											}
											
											*/
											$_SESSION['panier'] = $somme_total_r;
											//echo "PANIER : ".$_SESSION['panier'];
											$_SESSION['somme_frais_cb'] = $somme_frais_cb_r;
											//echo "SESSION somme_frais_cb :".$_SESSION['somme_frais_cb'] ;
											//echo "SOMME PANIER : ".$_SESSION['panier'];
											//echo "SOMME TOTAL : ".$somme_total_r;
											
											//INSCRIPTION SOLO
											$cpt=0;
											
											$type_certificat_bdd = type_epreuve($_SESSION['idEpreuve']);
											$query_select_certificat_fichier = $type_certificat_bdd['type_nom_bdd'];
											$query_select_peremption = $type_certificat_bdd['nom_date_nom_bdd'];		
											
											$whereIn = implode(',', $insc_solo);
											
											$query = "SELECT ri.idInternaute, ri.prenomInternaute, ri.nomInternaute, ri.sexeInternaute, ri.emailInternaute, ri.naissanceInternaute, ri.clubInternaute, ri.villeInternaute, ri.index_telephone, ri.telephone, ri.typeInternaute, ri.organisateur, ri.coureur, ri.fournisseur, ri.avatar, ri.adresseInternaute, ri.cpInternaute, ri.villeLatitude, ri.villeLongitude, ri.paysInternaute, ri.natInternaute, ri.".$query_select_certificat_fichier." as fichier_cert, ri.".$query_select_peremption." as peremption_cert,riei.date_insc, ";
											$query .= " riei.idEpreuveParcours, riei.date_insc, riei.idInscriptionEpreuveInternaute, riei.paiement_type, riei.montant_inscription, riei.paiement_montant, riei.equipe, riei.idEpreuveParcoursTarif,";
											$query .=" riei.paiement_date, riei.frais_cb, riei.frais_cheque, riei.dossard, riei.commentaire, riei.observation, riei.verif_certif, riei.idEpreuveParcours,";
											$query .=" riei.verif_auto_parentale, riei.info_cheque, riei.relance_certif, riei.relance_certif_date, riei.idEpreuveParcoursTarifPromo,riei.label_code_promo, riei.montant_code_promo,";
											$query .=" riei.participation, riei.categorie, riei.relance_paiement, riei.relance_paiement_date, riei.inscription_par, riei.modifie_par, riei.modifie_date,";
											$query .=" riei.verif_certif_ffa, riei.num_licence_ffa, riei.type_licence_ffa, riei.date_fin_licence_ffa, riei.num_club_ffa,";
											//OPTION Plus et ASSURANCE
											$query .=" riei.idOptionPlus,riei.Prix_OptionPlus,riei.assurance,";
											//OPTION plus et ASSURANCE 	
											$query .=" rib.remboursement, rib.frais_remboursement, rib.remboursement_effectue, rib.remboursement_date, riei.surcout, riei.frais_surcout, riei.surcout_effectue,rept.tarif, rept.idEpreuveParcoursTarif, riei.equipe, riei.groupe, riei.idGroupe, riei.num_licence_ffa ";
											$query .=" FROM r_internaute as ri ";
											$query .="INNER JOIN r_inscriptionepreuveinternaute as riei ON ri.idInternaute = riei.idInternaute ";
											$query .="INNER JOIN r_epreuveparcourstarif as rept ON riei.idEpreuveParcoursTarif = rept.idEpreuveParcoursTarif ";
											$query .="INNER JOIN r_epreuve ON riei.idEpreuve = r_epreuve.idEpreuve ";
											$query .="INNER JOIN r_epreuveparcours ON riei.idEpreuveParcours = r_epreuveparcours.idEpreuveParcours ";
											$query .="LEFT OUTER JOIN r_inscriptionremboursement as rib ON riei.idInscriptionRemboursement = rib.idInscriptionRemboursement ";
											$query .="WHERE riei.idEpreuve = ".$_SESSION['idEpreuve']. " ";
											//$query .="AND riei.idEpreuveParcours = ".$. " ";
											//$query .="AND riei.date_insc = '".$date_insc . "' ";
											//$query .="AND ri.validation = 'non' ";
											//if (!empty($whereIn)) 
											$query .="AND riei.idInscriptionEpreuveInternaute IN (".$whereIn.") "; 
											//$query .=" ORDER BY idInscriptionEpreuveInternaute DESC ";
											//else $query .="AND riei.id_session = '".md5($_GET['id']). "' ";
											//echo $query;
											$result = $mysqli->query($query);
											$nb_result = mysqli_num_rows($result);
											$_SESSION['info_caddie']+=$nb_result;
											//echo $_SESSION['idEpreuve'];
								
											//$data_epreuve_update=mysqli_fetch_array($result);
																	/*
																	$frais_cb_coureur_m[$j] = round(calcul_frais_cb(($_POST['tarif_en_cours_'][$j] + $Prix_OptionPlus),$_POST['prix_total'],$cout_paiement_cb,$_POST['participation_en_cours_'][$j]),2);
																	
																	$frais_cheque_un_coureur = $frais_cheque = $cout_paiement_cheque = 0;
									
																if ($j >1 && $_POST['relais'] > 0) {
																
																	$tarif_temp = 0;
																	$frais_cb_coureur_m[$j] = round(calcul_frais_cb(0,$_POST['prix_total'],$cout_paiement_cb,$_POST['participation_en_cours_'][$j]),2);
																}
																*/
											?>			
	

						<!--<div class="panel panel-inverse" data-sortable-id="index-1" style="background-color:#0e5887">-->
										<?php if (count($insc_solo)>0) { ?>
											<ul class="media-list media-list-with-divider" style="padding-top:0">
											<li class="media media-sm" style="border-top:none;padding-top:0">Inscription(s) SOLO
											<?php if ($_SESSION['autre_personne']==1) { ?>
												<!-- <b><?php echo $_SESSION['nomInternaute']." ".$_SESSION['prenomInternaute']; ?></b> vous avez inscrit : </li> //-->
												 vous avez inscrit : 
											
											
											<?php } ?></li>
											<?php
											//echo "XXXXXXXXXXXXXXXXXXXXXXXXXX:".$_SESSION['idEpreuvePersoPre']."-".$_SESSION['paiement_indiv'];
											//print_r($_SESSION);
											//print_r($_SESSION['rieis']);
											//echo "SOMME TOTA ASSURANCE : ".$somme_total_assurance;
											$cout_paiement_cb = extract_champ_epreuve('cout_paiement_cb',$_SESSION['idEpreuve']);
											
											$frais_cb_coureur_m = array();
											//$i=0;
											$_SESSION['tarifs']=array();
											$first=TRUE;
											$all_assurance = 0;
											$total_participation_s = 0;
											$somme_frais_cb = 0;
											while (($row=mysqli_fetch_array($result)) != FALSE)	{
												
												 if ($first==TRUE) {
													 $all_parcours = $row['idEpreuveParcours'];
													 $first=FALSE;
												 } else
												 {
													 $all_parcours .= ",".$row['idEpreuveParcours'];
													 
												 }
												
												//CHECK CODE PROMO
												$exist_code_promo = exist_code_promo($_SESSION['idEpreuve'],$row['idEpreuveParcours']);
												//CHECK CODE PROMO
												
												$besoin_certif = besoin_certificat_medical($row['idEpreuveParcours']);
												$besoin_auto_parentale = besoin_auto_parentale_parcours($row['idEpreuveParcours']);
												
												//*** ASSURANCE ANNULATION ****/
												$assurance_annulation=assurance_annulation($_SESSION['idEpreuve'],$row['idEpreuveParcours']);
												//print_r($assurance_annulation);
												//echo "panier : ".$_SESSION['panier'];
												$assurance = 0;
												$id_assurance_annulation = 0;
												$type_assurance_annulation='';
												if ($assurance_annulation['active'] =='oui') 
												{
													
													$assurance = 1;
													$check_btn_assurance ='';
													$cout_assurance_user_s = 0;
													$type_assurance_annulation = $assurance_annulation['type'];
													$id_assurance_annulation = extract_champ_assurance_annulation('idAssuranceAnnulation',$row['idInscriptionEpreuveInternaute']);
													
													if (empty($id_assurance_annulation))
													{
														$id_assurance_annulation = $assurance_annulation['idAssuranceAnnulation'];
													}
													else
													{
														$check_btn_assurance = 'checked';
														$cout_assurance_user_s += extract_champ_assurance_annulation('montant',$row['idInscriptionEpreuveInternaute']);
													}
														$aff_message_assu = "";
														//echo "XXXXXXXXXXXXXXXXXX:".$assurance_annulation['fixe'];
													if (empty($assurance_annulation['fixe'])) 
													{
														if ($assurance_annulation['type']=='tarif')
														{
															$aff_message_assu = "( <b><span class='text-danger'>+".$assurance_annulation['pourcentage']."%</span></b> sur le tarif d'inscription )";
														}
														elseif ($assurance_annulation['type']=='participation_parcours' || $assurance_annulation['type']=='participation_epreuve')
														{
															$aff_message_assu = "( <b><span class='text-danger'>+".$assurance_annulation['pourcentage']."%</span></b> sur les participations engagées )";
														}
														elseif ($assurance_annulation['type']=='option_supp')
														{
															$aff_message_assu = "( <b><span class='text-danger'>+".$assurance_annulation['pourcentage']."%</span></b> sur les options supplémentaires )";
														}
														elseif ($assurance_annulation['type']=='total')
														{
															$aff_message_assu = "( <b><span class='text-danger'>+".$assurance_annulation['pourcentage']."%</span></b> sur la totalité de l'inscription )";
														}
													}
													else
													{
														$aff_message_assu = "( <b><span class='text-danger'>+".$assurance_annulation['fixe']." € </span></b> )";
													}
												$all_assurance=1;
												}
											
											$tarif_en_cours = extract_champ_tarif('tarif',$row['idEpreuveParcoursTarif']);

											$tar = tarif_reduc_place($row['idEpreuveParcoursTarif']);
											//print_r($tar);
											$tarif_reduc_promo = $tar['reduction'];	
											
											$_SESSION['tarifs'][] = $row['idEpreuveParcoursTarif'];
											$frais_cb_coureur_m[] = round(calcul_frais_cb(($tarif_en_cours+ $row['Prix_OptionPlus']),$row['montant_inscription'],$cout_paiement_cb,$row['participation']),2);
											?>
			
												
												<li class="media media-sm" style="border-top:none;padding-top:0">
													<a class="media-left" href="javascript:;">
														<img src="admin/assets/img/course_a_pied.jpg" alt="" class="media-object rounded-corner">
													</a>
													<div class="media-body">
													<?php $id_reg = array_search($row['idInscriptionEpreuveInternaute'],$_SESSION['rieis']); ?>
														<h5 class="media-heading text-primary"><?php echo $row['nomInternaute']." ".$row['prenomInternaute']; ?><span class="small"><i> (<?php echo $row['emailInternaute']; ?></i>) </span></h5>
																									
														<p>Parcours : <b><?php echo extract_champ_parcours('nomParcours',$row['idEpreuveParcours']); ?></b><br>
														<?php if ($row['label_code_promo'] != '' && $row['montant_code_promo'] > 0) { ?>
															Montant inscription : <b><?php if ($row['montant_inscription'] ==0) { ?> <i><b><span class="text-success">Gratuit</span></b></i> <?php } else { ?><b><?php echo $row['montant_inscription']; ?> € <?php } ?> - Code promo utilisé <span class="badge badge-info badge-square"><?php echo $row['label_code_promo']; ?></span></b>
														<?php } else { ?>
															Montant inscription : <b><?php if ($row['montant_inscription'] ==0) { ?> <i><b><span class="text-success">Gratuit</span></b></i> <?php } else { ?><b><?php echo $row['montant_inscription']; ?> € <?php } ?></b> 
														<?php } ?>

										

															
															<dl class="dl-horizontal">
																<dt>Tarif du parcours</dt>
																<dd><b><?php echo $tarif_en_cours; ?> € </b></dd>
																<?php if ($tarif_reduc_promo > 0) { ?>
																	<dt>Réduction premier dossard</dt>
																	<dd><b><span class="text-success"><?php echo $tarif_reduc_promo; ?> € </span></b></dd>
																<?php } ?>
																<?php if ($row['participation'] > 0) { ?>
																	<dt>Participation</dt>
																	<dd><b> <?php echo ($row['participation']) ;?> € </b></dd>
																<?php } ?>
																<?php if ($row['idOptionPlus'] > 0) { 
																	$nom_option = extract_champ_options_plus('label',$row['idOptionPlus']);
																?>																
																	<dt><b><?php echo $nom_option; ?></b></dt>
																	<dd><b><?php echo ($row['Prix_OptionPlus']) ;?> € </b> [ <a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=opt&edit_reg=<?php echo $id_reg; ?><?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" >modifier</a>]</dd>
																<?php } ?>
																<?php
																if ($besoin_certif==1) 
																{
																	if ($row['verif_certif']=='non') 
																	{ 
																	?>
																			<dt>Certif. médical / licence </dt>
																			<dd class="text-warning"><b> Fourni </b></dd>
																	<?php 
																	}
																	elseif ($row['verif_certif']=='oui') 
																	{ 
																	?>
																			<dt>Certif. médical / licence </dt>
																			<dd class="text-success"><b> Validé(e)</b></dd>
																	<?php 
																	}
																	else
																	{
																	?>
																			<dt>Certif. médical / licence </dt>
																			<dd class="text-danger"><b> Non Fourni </b></dd>
																	<?php 																		
																		
																	}
																}
																else
																{
																	?>
																			<dt>Certif. médical / licence </dt>
																			<dd class="text-success"><b> Pas de besoin </b></dd>
																	<?php 																	
																	
																}
																
																if ($besoin_auto_parentale==1) 
																{
																	if ($row['verif_auto_parentale']=='non') 
																	{ 
																	?>
																			<dt>Auto parentale </dt>
																			<dd class="text-warning"><b> Fourni </b></dd>
																	<?php 
																	}
																	else
																	{
																	?>
																			<dt>Auto parentale </dt>
																			<dd class="text-danger"><b> Non Fourni </b></dd>
																	<?php 																		
																		
																	}
																}
																else
																{
																	?>
																			<dt>Auto parentale </dt>
																			<dd class="text-success"><b> Pas de besoin </b></dd>
																	<?php 																	
																	
																}
																?>
																<?php if ($row['groupe'] != 'Aucun') { ?>
																	<dt>Groupe</dt>
																	<dd><b class="text-info"> <?php echo ($row['groupe']) ;?> </b></dd>
																<?php } ?>																
															</dl>
	
														
														<a href="https://www.ats-sport.com/formulaire.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&edit_reg=<?php echo $id_reg; ?><?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" class="btn btn-xs btn-primary m-r-5">Modifier</a>
														<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=3&del_reg=<?php echo $id_reg; ?><?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" class="btn btn-xs btn-danger m-r-5">Supprimer</a>
														<?php if ( count($_SESSION['rieis']) == 1 ) { ?>
															
														<?php } elseif ($id_reg > 0) { ?>
																	<!-- <a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=3&del_reg=<?php echo $id_reg; ?>" class="btn btn-xs btn-danger m-r-5">Supprimer</a> /-->
														<?php } ?>
														<?php if ($row['label_code_promo'] != '' && $row['montant_code_promo'] > 0) { ?>
															<a href="javascript:;" onclick ="check_code_promo(2,<?php echo $row['idInscriptionEpreuveInternaute']; ?>,<?php echo $_SESSION['idEpreuve']; ?>,<?php echo $row['idEpreuveParcours']; ?>)"class="btn btn-inverse btn-xs m-r-5" data-toggle="modal">Supprimer mon code promo</a>
														<?php } else { ?>
															<?php if (!empty($exist_code_promo)) { ?>
																<a style="margin-top:0px" href="#modal-code_promo_<?php echo $cpt; ?>"class="btn btn-info btn-xs m-r-5" data-toggle="modal">J'ai un code promo</a>
															<?php } ?>
														<?php } ?>
														<?php if ($assurance==1) { ?>
														
															<br/>
															<input style="margin-top:10px" type="checkbox" id="assurance_annulation_<?php echo $row['idInscriptionEpreuveInternaute'] ; ?>" name="assurance_annulation" onchange="calcul_frais_annulation(this.id,<?php echo $row['idInscriptionEpreuveInternaute'] ; ?>,'<?php echo $type_assurance_annulation; ?>',<?php echo $assurance_annulation['pourcentage']; ?>,<?php echo $assurance_annulation['fixe']; ?>,<?php echo $row['montant_inscription']; ?>,<?php echo $row['tarif']; ?>,<?php echo $id_assurance_annulation; ?>)" <?php echo $check_btn_assurance; ?>> <i>Je m'assure</i> - <span class="text-warning"><?php echo $aff_message_assu; ?> <a href="javascript:;" data-toggle="popover" title="Information importante" data-content="<?php echo $assurance_annulation['informations']; ?>" id="option_plus_information_<?php echo $row['idInscriptionEpreuveInternaute'] ; ?>"><i class="fa fa fa-exclamation-circle"/></i></a></span>  
														<?php } ?>
																																				<tr>
															<br/>
																												
																<input type="checkbox" onchange="vli(<?php echo $row['idInscriptionEpreuveInternaute'] ; ?>);" id="reglement_cnil_vli"> Je ne souhaite pas voir mon nom sur la liste des inscrits

														</p>
															<!-- MODAL CODE PROMO /-->
															<div class="modal" id="modal-code_promo_<?php echo $cpt; ?>" style="display: none;">
																<div class="modal-dialog">
																	<div class="modal-content">
																		<div class="modal-header">
																			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
																			<h4 class="modal-title">Vous avez un code promotionnel - Entrez le ici </h4>
																		</div>
																		<div class="modal-body">
																			<fieldset>
																				<div class="form-group">
																					<input type="text" class="form-control" id="code_promo_<?php echo $row['idInscriptionEpreuveInternaute']; ?>" placeholder="Ex : IAKOAP">
																					<span id="check_promo_return" style="font-size:10px;display:none" class="text-danger"><i>Ce code promo n'est pas valide</i></span>
																				</div>
																				<button onclick ="check_code_promo(1,<?php echo $row['idInscriptionEpreuveInternaute']; ?>,<?php echo $_SESSION['idEpreuve']; ?>,<?php echo $row['idEpreuveParcours']; ?>)" id="btn_code_promo" class="btn btn-sm btn-primary m-r-5">Valider</button>
																			</fieldset>	
																		</div>
																		<div class="modal-footer">
																			<a href="javascript:;" class="btn btn-sm btn-white" data-dismiss="modal">Annuler</a>
																		</div>
																	</div>
																</div>
															</div>
															<!-- MODAL CODE PROMO /-->
															
													</div>
												</li>
											<?php 
												//if ($equipe_en_cours==1) { $montant_total = $row['montant_inscription']; } else { $montant_total += $row['montant_inscription']; }

											
										?>	
										</ul>
										<?php 
													$montant_total_s += $row['montant_inscription'];
												$total_participation_s += $row['participation'];
												$somme_total_assurance += $cout_assurance_user_s;
												$somme_frais_cb_total_panier += round(calcul_frais_cb($row['montant_inscription']-$row['participation'],$montant_total_s,$cout_paiement_cb,$total_participation_s),2);
												$cpt++; 
										
										} 

											
											}

										//echo "	montant_total_s :".$montant_total_s;
										//echo "	montant_total_+ option :".($montant_total_s-$total_participation_s);
										//echo "  total_participation_s : ".$total_participation_s;
										
											$_SESSION['info_caddie'] = $cpt + $nb_equipe;
											$_SESSION['panier'] += $montant_total_s;
											//echo "SOMME PANIER AVANT SOLO : ".$_SESSION['panier'];
											//echo "tout parcours :".$all_parcours;
											
											$_SESSION['equipe_participation'] += $total_participation_s;
											$_SESSION['equipe_tarif_et_option'] += ($montant_total_s-$total_participation_s);
											
											$_SESSION['panier'] = $somme_total_r + $montant_total_s+$somme_total_assurance;
											//print_r($_SESSION['tarifs']);
											//print_r($frais_cb_coureur_m);
											//echo "somme_frais_cb_r : ".$somme_frais_cb_r;
											//echo "SOMME TOTAL ASSURANCE : ".$somme_total_assurance;
											//echo "PANIER  : ".$_SESSION['panier'];											
											if ($somme_frais_cb_r == 0)
											{
												//***ECHO "somme_frais_cb_r = 0";
												//$somme_frais_cb_total_panier = round(calcul_frais_cb($_SESSION['equipe_tarif_et_option'],$tarif_total_r,$cout_paiement_cb,$_SESSION['equipe_participation']),2);
												//$somme_frais_cb = array_sum($frais_cb_coureur_m);
												if ($paiement_frais_cb==1) 
												{
													$somme_total = $somme_frais_cb_total_panier + $_SESSION['panier']; 
													$_SESSION['somme_frais_cb'] = $somme_frais_cb_total_panier;
												} 
												else 
												{  
													$somme_total = $_SESSION['panier']; 
													$_SESSION['somme_frais_cb'] = $frais_cb_orga_fixe;
												}
	
												if ($update==0 && $_POST['relais'] > 0) {
													$query="UPDATE r_insc_internautereferent SET frais_cb = '".$somme_frais_cb_total_panier."',  montant = '".$somme_total."' WHERE idInternauteReferent = ".$_SESSION['id_ref_relais'][0];
													$result = $mysqli->query($query);	
													
													/*** LOGS ***/
													fputs($fp," update==0 && _POST['relais'] > 0:  UPDATE r_insc_internautereferent : ".$query." \n");
													/*** LOGS ***/
												}
	
											}
											else
											{
												//***ECHO "somme_frais_cb_r >0 ";
												$somme_frais_cb_total_panier_r = round(calcul_frais_cb($_SESSION['equipe_tarif_et_option'],$tarif_total_r,$cout_paiement_cb,$_SESSION['equipe_participation']),2);											
												$somme_frais_cb = array_sum($frais_cb_coureur_m) + $somme_frais_cb_r;
												//$total_tarif = $montant_total_s + $total_cout_equipe;
												//$total_participation = $total_participation_s  + $total_participation__r;
												
												//echo "total tout : ".$total_tout;
												//echo "total_participation : ".$total_participation;
												
												//$somme_frais_cb = round(calcul_frais_cb($total_cout,$tarif_total_r,$cout_paiement_cb,$total_participation),2);
												
												if ($paiement_frais_cb==1) 
												{
													$somme_total = $somme_frais_cb + $_SESSION['panier']; 
													$_SESSION['somme_frais_cb'] = $somme_frais_cb_total_panier_r + $somme_frais_cb_total_panier;
												} 
												else 
												{  
													$somme_total = $_SESSION['panier']; 
													$_SESSION['somme_frais_cb'] = $frais_cb_orga_fixe;
												}	
												
												
												
												
												
											}
											
											//echo "SOMME PANIER APRES SOLO : ".$_SESSION['panier'];									
										
										
										?>
									</div>
									
									<div class="col-sm-4"></div>
		
										<div class="col-sm-8">
											<div class="form-group">
												<div class="input-group">
															<span class="input-group-addon">
																<input type="checkbox" id="reglement_epreuve_check">
															</span>
															<span class="form-control">J'accepte le réglement de l'épreuve<?php echo recup_reglement_epreuve($_SESSION['idEpreuve']);	?></span>
												</div>
											</div>
											<!--
											<div class="form-group">
												<div class="input-group">
															<span class="input-group-addon">
																<input type="checkbox" id="reglement_pp_check">
															</span>
															<span class="form-control">J'accepte le réglement intérieur de Ats Sport <!--<a class="reglement_pp_modal btn btn-primary btn-xs pull-right" href="#reglement_pp" >Réglement Ats Sport</a></span>
															<div class="col-sm-12" id="reglement_pp" style="display:none"><?php echo $condition['val']; ?></div>
												</div>
											</div>
											-->
											<div class="form-group">
												<div class="input-group">
													<span class="input-group-addon">
														<input onclick="newsletter(<?php echo $_SESSION['log_id']; ?>,'<?php echo $type_epreuve; ?>',0);" type="checkbox" id="reglement_cnil_check">
													</span>
													<span class="form-control">Je souhaite m'inscrire à la newsletter ATS-SPORT<br>
				
														<!--<a class="reglement_pp_cnil btn btn-primary btn-xs pull-right" href="#reglement_cnil" >Conformité CNIL et législation</a>--></span>
														<!--<div class="col-sm-12" id="reglement_cnil" style="display:none">D'après l'article L34-5 du Code des postes et des communications électroniques, 
																seul l'opt in actif est autorisé pour les envois de messages électroniques à des particuliers.</br>
																La CNIL indique que l'opt in passif doit être réservé aux adresses professionnelles.
														</div>-->
												</div>
											</div>
											<!--
											<div class="form-group">
												<div class="input-group">
													<span class="input-group-addon">
														<input onclick="newsletter(<?php echo $_SESSION['log_id']; ?>,'<?php echo $type_epreuve; ?>');" type="checkbox" id="reglement_cnil_vli">
													</span>
													<span class="form-control">Je ne souhaite pas voir mon nom sur la liste des inscrits<br>
				

												</div>
											</div>
											/-->
												<p>
													<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=3&del_reg=o<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" class="btn btn-xs btn-danger m-r-5"><i class="fa fa-trash-o"></i> Vider le panier </a>
													<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?><?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" class="btn btn-warning"><i class="fa fa-user"></i> Ajouter un ou des participants</a>
													<!-- <a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>" class="btn btn-light-green"><i class="fa fa-eur"></i> Payer </a> /-->
												</p>
										
										</div>
										<div class="col-sm-4">
											<div style="vertical-align: middle;">
												<table class="table" >
													<tbody>
													<?php if ($paiement_frais_cb==1) { ?>
														<tr>
															<td style="border-top:0px;"><h5>Sous total: </td></h5>
															<td style="border-top:0px;"><h5><b><?php echo ($_SESSION['panier']-$somme_total_assurance); ?>€</b> </h5></td>
														</tr>
														
														<tr>
															<td><h6>Frais bancaire: </h6></td>
										
															<td><h6><b><?php echo $_SESSION['somme_frais_cb']; ?>€</b></h6></td> 
														</tr>
														<?php if ($all_assurance==1) { 
														
														?>
															<tr>
																<td><h6>Frais d'assurance: </h6></td>
																<td><span class="semi-bold text-danger" id="aff_frais_assurance"><?php echo $somme_total_assurance ; ?> € </span></td>
															<tr>
															
														<?php 
														
														
														} ?>
													<?php } ?>
															<!--
															<tr>
																<td><h6>Code Réduc: </h6></td>
																<td>
																<input type="text" size="5" data-toggle="tooltip" data-placement="top" data-original-title="Utilisable que pour une seule inscription" id="code_promo_1" name="code_promo" class="form-control" onchange="check_code_promo(this.value,<?php echo $_SESSION['idEpreuve']; ?>,1);" onkeyup="$('#bouton_code_promo_1').show();">
																</br><button id="bouton_code_promo_1" class="btn btn-sm btn-primary " type="button" onclick="check_code_promo($('#code_promo_1').val(),<?php echo $_SESSION['idEpreuve']; ?>,1,'<?php echo $all_parcours; ?>');">Envoyer </button></td>
															</tr>
															//-->
															<td><h3>Total: </h3></td>
															<td><h3><b><span id="aff_prix_total"><?php echo $somme_total; ?></span>€</b></h3></td>
															</br>

														</tr>
														
														<tr> 
															<td colspan="2" id="bouton_payer" style="border-top:0px;display:none">
																<a  href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=4<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" class="btn btn-success pull-right" style="font-size:24px"><i class="fa fa-eur"></i> 
																	Payer 
																</a>
															</td>
															<td colspan="2" id="bouton_payer_info" style="border-top:0px;">
																<i>Veuillez accepter le reglement pour payer</i>
															</td>
														</tr>
													</tbody>
												</table>
											</div>							
			
										</div>
		
								</div>
								<div class="right-content"  id="login">
									<!-- begin login-header 
									<legend id="legend_ident_on">Résumé de mon panier</legend>-->
									<!-- end login-header -->
									<!-- begin login-content -->
									<div class="login-content">
								
											
											
														<form action="https://www.ats-sport.com/formulaire.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?><?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" method="POST" id="insc_form" class="margin-bottom-0" enctype="multipart/form-data">
															<input type="hidden" id="prix_total" name="prix_total" value="<?php echo $somme_total; ?>">
															<input type="hidden" id="param_t" name="param_t" value="<?php echo $_SESSION['tarifs']; ?>">
															<input type="hidden" id="relais" name="relais" value="<?php echo $_POST['relais'] ?>">
															<input type="hidden" id="total_assurance" name="total_assurance" value="<?php echo $somme_total_assurance ; ?>">
															<input type="hidden" id="frais_cb" value="<?php echo $somme_frais_cb; ?>">
															<input type="hidden" id="valeur_en_cours_code_promo_1" value="0">				
															
															<!-- <input type="submit" class="btn btn-info btn-block btn-lg" id="connex_email_cont" value="Continuer l'inscription"> /-->
														</form>
							
									</div>
									<!-- end login-content -->
								</div>									
	
								
				
							</div>
					</div>		
						<div class="col-md-3"></div>

					<?php  } else {  ?>
						<div class="col-md-3"></div>
	
						<div class="panel panel-primary col-md-6" style="max-width: 768px;">
								<div class="breadcrumb flat no-nums hidden-xs">
									<a href="insc.php?id_epreuve=<?php echo $_GET['id_epreuve']; ?>" ><i class="fa fa-1x fa-home"></i></a>
									<a href="insc.php?id_epreuve=<?php echo $_GET['id_epreuve']; ?>&tp=s">Parcours</a>
									<a href="javascript:;" >Connexion</a>
									<a href="javascript:;">Formulaire</a>
									<a href="javascript:;" class="active">Panier</a>
									<a href="javascript:;">Validation</a>
								</div>	
						<!--<div class="panel panel-inverse" data-sortable-id="index-1" style="background-color:#0e5887">-->
								<div class="panel-heading">
		
									<h4 class="panel-title"><?php echo $data_epreuve['nomEpreuve']; ?></h4>
								</div>
								<div class="panel-body bg-white">				
									<div class="row" > <p class="text-center"><b>Panier Vide</b></p></div>
									<div class="row"><p class="text-center"><a href="insc.php?id_epreuve=<?php echo $_GET['id_epreuve']; ?>&step=start<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" class="btn btn-danger"><i class="fa fa-reply"></i> Retour à la sélection des parcours </a></p></div>
								</div>
						</div>
						<div class="col-md-3"></div>
						<?php  } ?>
						
				<!-- ***************************** STEP 4 ********************************* -->
				
				<?php  } elseif ($_GET['step'] == 4 && isset($_SESSION['log_id'])) { 
				
						//echo "XXX:".print_r($_SESSION['rieis']);echo "xxxx";
						//echo count($_SESSION['rieis']);
						$organisateur ='non'; $simul = 0;
						if ($_SESSION['log_organisateur']=='oui') { $organisateur = 'oui'; $simul=1; }
						if (count($_SESSION['rieis'])==0) {  echo "<script> window.location.replace('insc.php?id_epreuve=".$_SESSION['idEpreuve']."&step=start') </script>"; }
				?>
				
				
				
				<?php
					require_once("includes/numerotation.php");
					//require_once("../libs/class.paiement.php");
					require_once("etransaction/class.paiement_n.php");
		//assurance

				//echo "AUTRE PERSONNE :".$_SESSION['autre_personne']; 
				
				if (count($_SESSION['equipes'])>0) $_SESSION['bp_paiement_autre_multiple'] = 1; else $_SESSION['bp_paiement_autre_multiple'] = 0;
				//echo implode(",", $_SESSION['equipes']);
				//echo "bp_paiement_autre_multiple : ".$_SESSION['bp_paiement_autre_multiple'];
				
				/*
				if ($_SESSION['bp_paiement_autre_multiple']==1)
				{
					echo $queryupiei=" UPDATE r_insc_internautereferent
					SET frais_cb = 0 
					WHERE idInternauteReferent IN (".implode(",", $_SESSION['equipes']).")" ;
					//***$resultupiei = $mysqli->query($queryupiei);					

				}
				*/				

				$_SESSION['somme_total'] = 0;
				//echo "ASSURANCE : ".$_POST['total_assurance'];
				
				//if (!empty($_POST['total_assurance'])) $_SESSION['panier'] += $_POST['total_assurance'];
				if ($paiement_frais_cb==1) { $_SESSION['somme_total'] = $_SESSION['somme_frais_cb'] + $_SESSION['panier']; } else {  $_SESSION['somme_total'] = $_SESSION['panier']; }
				
				//echo $nb_inscrit;
				
				$all_internaute_tmp = array();
				$all_parcours_tmp = array();
				$cpt_riei = 0;
				
				$rieis_tmp = array();
				$rieis_tmp = $_SESSION['rieis'];
				//echo "equipes_rieis : ".print_r($_SESSION['equipes_rieis']);
				foreach ($_SESSION['equipes_rieis'] as $rieis)
				{
					
					//****echo "R  :".$rieis." R ";
					 //$id_reg = array_search($rieis,$rieis_tmp);
					 //echo "X :".$id_reg." X ";
					//if (!empty($id_reg)) { echo "ici"; unset($rieis_tmp[$id_reg]); }
					
					if (($key = array_search($rieis, $rieis_tmp)) !== false) {  unset($rieis_tmp[$key]); }
					
				}
				//echo print_r($_SESSION['rieis'])."RESTANT : ".print_r($rieis_tmp);
				$_SESSION['nb_inscrit'] = count($rieis_tmp);
				foreach ($rieis_tmp as $riei)
				{
					$id_internaute_tmp = extract_champ_epreuve_internaute('idInternaute',$riei);
					$id_parcours_tmp = extract_champ_epreuve_internaute('idEpreuveParcours',$riei);
					
					array_push($all_internaute_tmp,$id_internaute_tmp);
					array_push($all_parcours_tmp,$id_parcours_tmp);
					
					if (isset($_SESSION['idEpreuvePersoPre']) && $_SESSION['paiement_indiv'] =='non')
					{
						if ($cpt_riei==0)
						{
							$query="UPDATE r_inscriptionepreuveinternaute SET frais_cb=0, paiement_montant =  ".$_SESSION['panier'].", paiement_type = 'AUTRE', paiement_date=NOW() WHERE idInscriptionEpreuveInternaute = ".$riei;
							$result_query = $mysqli->query($query);
							
							/*** LOGS ***/
							fputs($fp," _SESSION['idEpreuvePersoPre']) && _SESSION['paiement_indiv'] =='non' && cpt_riei==0 :  UPDATE r_inscriptionepreuveinternaute : ".$query." \n");
							/*** LOGS ***/							
						}
						else
						{
							
							$query="UPDATE r_inscriptionepreuveinternaute SET frais_cb=0, paiement_montant =  0, paiement_type = 'AUTRE', paiement_date=NOW() WHERE idInscriptionEpreuveInternaute = ".$riei;
							$result_query = $mysqli->query($query);
							
							/*** LOGS ***/
							fputs($fp," _SESSION['idEpreuvePersoPre']) && _SESSION['paiement_indiv'] =='non' && cpt_riei==0 | ELSE  :  UPDATE r_inscriptionepreuveinternaute : ".$query." \n");
							/*** LOGS ***/							
						}
						


					}
					$cpt_riei++;	
				}

					$rieis_tmp= array_values($rieis_tmp);
					//print_r($rieis_tmp);
					$_SESSION['id_internaute_referent_equipe_solo'] = extract_champ_epreuve_internaute('idInternaute',$rieis_tmp[0]);
					$_SESSION['id_internaute_inscription_referent'] = $rieis_tmp[0];
					
					$nomInternaute = extract_champ_internaute('nomInternaute',$all_internaute_tmp[0]);
					$prenomInternaute = extract_champ_internaute('prenomInternaute',$all_internaute_tmp[0]);
					
					$_SESSION['NomRespEquipe'] = $nomInternaute." ".$prenomInternaute;
					$_SESSION['TelRespEquipe'] = extract_champ_internaute('telephone',$all_internaute_tmp[0]);
					
					$email_internaute = extract_champ_internaute('emailInternaute',$all_internaute_tmp[0]);

				
					//$removed = array_shift($hobbies);
					$all_id_inscription_internaute_tmp = $rieis_tmp;
					//print_r($all_id_inscription_internaute_tmp);
					//$all_id_inscription_internaute_tmp = array_shift($all_id_inscription_internaute_tmp);
					unset($all_id_inscription_internaute_tmp[0]);
					//print_r($all_id_inscription_internaute_tmp);
					$_SESSION['all_id_inscription_internaute'] = implode('|', $all_id_inscription_internaute_tmp);
					//echo $all_id_inscription_internaute;
					
					unset($all_internaute_tmp[0]);
					$_SESSION['all_id_internaute'] = implode('|', $all_internaute_tmp);
					
					unset($all_parcours_tmp[0]);
					$_SESSION['all_id_parcours'] = implode('|', $all_parcours_tmp);
					
					
					$equipe = '';
					
					if ($_SESSION['panier'] == 0) $insc_gratuite = 1; else $insc_gratuite = 0;
					
					//Echo "NB iscrits : ".$_SESSION['nb_inscrit'];
					
					if ($_SESSION['nb_inscrit'] > 1) 
					{ 
						//ECHO "MULTIPLE";
						//$nb_inscrit ='1';
						//$lastid_epreuve_inscription
						if($_POST['relais'] > 0) $relais = 'oui'; else $relais = 'non';
						
						if (!empty($equipe)) $NomEquipe = addslashes($equipe); else $NomEquipe = 'Aucune';
						
						//echo "id_ref_temp :".$_SESSION['id_ref_temp'];
						//unset($_SESSION['id_ref_temp']);
						if (empty($_SESSION['id_ref_temp'])) {
							
						
						
						
							if ($_SESSION['id_internaute_referent_equipe_solo'] != $_SESSION['log_id']) 
							{ 
								$_SESSION['InscritParidInternaute'] = $_SESSION['log_id'];
								$_SESSION['bp_paiement_autre_multiple'] = 1;
							}
							else
							{
								$_SESSION['InscritParidInternaute'] = 'NULL';
								
							}
							

							
							if ($_SESSION['autre_personne']==1) 
							{ 
								$_SESSION['id_ref_bp'] = 'M-'.$_SESSION['log_id'];
							}
							else
							{
								$_SESSION['id_ref_bp'] = 'M-'.$id_referent;
								
							}
							
							$frais_cb_tmp = 0;
							
							if ($_SESSION['bp_paiement_autre_multiple'] == 0 ) 
							{
								$frais_cb_tmp = $_SESSION['somme_frais_cb'];
								
							}
						
							$query2 = "UPDATE r_inscriptionepreuveinternaute SET
							frais_cb = ".$frais_cb_tmp." ,
							WHERE idInscriptionEpreuveInternaute = ".$_SESSION['id_internaute_inscription_referent'];
							//***$result = $mysqli->query($query2);
							//$id_ref_bp = 'M-'.$id_referent;
						}

						
						//$id_referent = $mysqli->insert_id;
						/*
						$query_referent  = "UPDATE r_inscriptionepreuveinternaute SET ";
						$query_referent .= "relais = ".$_POST['relais'].", ";
						$query_referent .= "idei_referent_relais = ".$lastid_epreuve_inscription.", ";
						$query_referent .= "id_admin_relais = ".$id_referent." ";
						$query_referent .= " WHERE idInscriptionEpreuveInternaute=".$lastid_epreuve_inscription;
						$result_referent = $mysqli->query($query_referent);
						*/
						/*
						if ($cout_paiement_cb==0) {
							$total = ($_SESSION['somme_frais_cb'] + $_SESSION['panier']);
						}
						else
						{
							$total = $_SESSION['panier'];
						}
						*/
						$iframe = '';
						$_SESSION['id_ref_bp'] = "M-T".$_SESSION['id_internaute_referent_equipe_solo'];
						$_SESSION['id_ref_bp'] = 'A-'.$_SESSION['log_id']."-".$_SESSION['id_unique_paiement'];
						
						if ($_SESSION['autre_personne']==1) $email_internaute = $_SESSION['emailInternaute'];
								
								$_SESSION['id_internaute_referent'] = $_SESSION['log_id'];
								$_SESSION['email_internaute'] = $_SESSION['emailInternaute'];
								$_SESSION['nom_Internaute']=$_SESSION['nomInternaute'];
								$_SESSION['prenom_Internaute']=$_SESSION['prenomInternaute'];
								
						//ECHO "ICI";
						//***if ($insc_gratuite==0) $html_ca = create_form_ca_n("M-T".$_SESSION['id_internaute_referent_equipe_solo'], round($_SESSION['somme_total'], 2), $email_internaute,$_SESSION['idEpreuve'],$iframe);
						//if ($insc_gratuite==0) $html_ca = create_form_ca_n("M-".$id_referent, 0.5, $email_internaute,$_SESSION['idEpreuve'],$iframe);
					}
					elseif ($_SESSION['nb_inscrit']==1)
					{
						//ECHO "UNIQUE";
						if ($_SESSION['autre_personne']==1) 
						{ 
							$email_internaute = $_SESSION['emailInternaute'];
							//$id_referent = $_SESSION['log_id'];
						}
						
						$_SESSION['id_ref_bp'] = $id_referent = $_SESSION['id_internaute_inscription_referent'];
						//$id_ref_bp = $id_internaute_inscription_referent;

						//if ($cout_paiement_cb==0) { $total = ($_SESSION['somme_frais_cb'] + $_SESSION['panier']);} else { $total = $_SESSION['panier'];}
						$iframe = '';
							
						$frais_cb_tmp = 0;

						if ($_SESSION['bp_paiement_autre_multiple'] == 0 ) 
						{
							$frais_cb_tmp = $_SESSION['somme_frais_cb'];
							
						} 
							//echo "id_internaute_referent_equipe_solo : ".$_SESSION['id_internaute_referent_equipe_solo'];
							if ($_SESSION['id_internaute_referent_equipe_solo'] == $_SESSION['log_id']) 
							{ 
								$_SESSION['id_internaute_referent'] = $_SESSION['log_id'];
								$_SESSION['email_internaute'] = $_SESSION['emailInternaute'];
								$_SESSION['nom_Internaute']=$_SESSION['nomInternaute'];
								$_SESSION['prenom_Internaute']=$_SESSION['prenomInternaute'];
							}

							
						    $query2 = "UPDATE r_inscriptionepreuveinternaute SET
							frais_cb = ".$frais_cb_tmp."
							WHERE idInscriptionEpreuveInternaute = ".$_SESSION['id_internaute_inscription_referent'];
							$result = $mysqli->query($query2);	
							
							/*** LOGS ***/
							fputs($fp," _SESSION['bp_paiement_autre_multiple'] == 0  :  UPDATE r_inscriptionepreuveinternaute : ".$query2." \n");
							/*** LOGS ***/								
						
						//if ($insc_gratuite==0) $html_ca = create_form_ca_n($_SESSION['id_internaute_inscription_referent'], round($_SESSION['somme_total'], 2), $email_internaute,$_SESSION['idEpreuve'],$iframe);
						
					}
					elseif ($_SESSION['nb_inscrit']==0)
					{
						
						//ECHO "MULTIPLE EQUIPES UNQUEMENT";
						//$nb_inscrit ='1';
						//$lastid_epreuve_inscription
						if($_POST['relais'] > 0) $relais = 'oui'; else $relais = 'non';
						
						if (!empty($equipe)) $NomEquipe = addslashes($equipe); else $NomEquipe = 'Aucune';
						
						//echo "id_ref_temp :".$_SESSION['id_ref_temp'];
						//unset($_SESSION['id_ref_temp']);
						if (empty($_SESSION['id_ref_temp'])) {
							
						
						
						
							if ($_SESSION['id_internaute_referent_equipe_solo'] != $_SESSION['log_id']) 
							{ 
								$_SESSION['InscritParidInternaute'] = $_SESSION['log_id'];
								$_SESSION['bp_paiement_autre_multiple'] = 1;
							}
							else
							{
								$_SESSION['InscritParidInternaute'] = 'NULL';
								
							}
							

							/*
							if ($_SESSION['autre_personne']==1) 
							{ 
								$_SESSION['id_ref_bp'] = 'M-'.$_SESSION['log_id'];
							}
							else
							{
								$_SESSION['id_ref_bp'] = 'M-'.$id_referent;
								
							}
							*/
							$frais_cb_tmp = 0;
							
							if ($_SESSION['bp_paiement_autre_multiple'] == 0 ) 
							{
								$frais_cb_tmp = $_SESSION['somme_frais_cb'];
								
							}
							
							$_SESSION['id_ref_bp'] = 'M-'.$_SESSION['log_id']."-".$_SESSION['id_unique_paiement'];
							
							$query2 = "UPDATE r_inscriptionepreuveinternaute SET
							
							frais_cb = ".$frais_cb_tmp." ,
							WHERE idInscriptionEpreuveInternaute = ".$_SESSION['id_internaute_inscription_referent'];
	
							/*** LOGS ***/
							fputs($fp," (empty(_SESSION['id_ref_temp']))  :  UPDATE r_inscriptionepreuveinternaute SET : ".$query2." \n");
							/*** LOGS ***/
							
							//***$result = $mysqli->query($query2);
							//$id_ref_bp = 'M-'.$id_referent;
						}						
						
						
						
						
						
						
						//if ($insc_gratuite==0) $html_ca = create_form_ca_n($_SESSION['id_internaute_inscription_referent'], round($_SESSION['somme_total'], 2), $email_internaute,$_SESSION['idEpreuve'],$iframe);
					}
					//echo "bp_paiement_autre_multiple : ".$_SESSION['bp_paiement_autre_multiple'];


						if ($insc_gratuite==0) 
						{
						

							
							if ($_SESSION['autre_personne']==1) 
							{ 
								$_SESSION['email_internaute'] = $_SESSION['emailInternaute'];
								$_SESSION['id_internaute_referent'] = $_SESSION['log_id'];
								$_SESSION['nom_Internaute']=$_SESSION['nomInternaute'];
								$_SESSION['prenom_Internaute']=$_SESSION['prenomInternaute'];
								$email_internaute = $_SESSION['emailInternaute'];
							}
							
							
							if ($_SESSION['bp_paiement_autre_multiple']==1)
							{
								//ECHO "bp_paiement_autre_multiplexxxx";
								$_SESSION['id_ref_bp'] = 'A-'.$_SESSION['log_id']."-".$_SESSION['id_unique_paiement'];
								$_SESSION['id_internaute_referent'] = $_SESSION['log_id'];
								$_SESSION['email_internaute'] = $_SESSION['emailInternaute'];
								$_SESSION['nom_Internaute']=$_SESSION['nomInternaute'];
								$_SESSION['prenom_Internaute']=$_SESSION['prenomInternaute'];
								$email_internaute = $_SESSION['emailInternaute'];
							}
							
							$html_ca = create_form_ca_n($_SESSION['id_ref_bp'], round($_SESSION['somme_total'], 2), $email_internaute,$_SESSION['idEpreuve'],$iframe,$simul);
							
							if ($simul===1)
							{
								$bpaiements  = "INSERT INTO b_paiements (reference, montant, frais_cb, date, id_referant, id_epreuve, statut, nomInternaute, prenomInternaute, emailInternaute) ";
								$bpaiements .= "VALUE ('".$_SESSION['id_ref_bp']."', ".round($_SESSION['somme_total'], 2).", ".round($_SESSION['somme_frais_cb'], 2).", NOW(), ".$_SESSION['id_internaute_referent'].", ".$_SESSION['idEpreuve'].", 'ATTENTE','".addslashes_form_to_sql($_SESSION['nom_Internaute'])."','".addslashes_form_to_sql($_SESSION['prenom_Internaute'])."','".$_SESSION['email_internaute']."')";  
								$result = $mysqli->query($bpaiements);	
								/*** LOGS ***/
								fputs($fp," (simul===1 )  :  INSERT INSERT INTO b_paiements : ".$bpaiements." \n");
								/*** LOGS ***/								
							}
							/*
							$bpaiements  = "INSERT INTO b_paiements (reference, montant, frais_cb, date, id_referant, id_epreuve, statut, nomInternaute, prenomInternaute, emailInternaute) ";
							echo $bpaiements .= "VALUE ('".$_SESSION['id_ref_bp']."', ".round($_SESSION['somme_total'], 2).", ".round($_SESSION['somme_frais_cb'], 2).", NOW(), ".$_SESSION['id_internaute_referent'].", ".$_SESSION['idEpreuve'].", 'ATTENTE','".addslashes_form_to_sql($_SESSION['nom_Internaute'])."','".addslashes_form_to_sql($_SESSION['prenom_Internaute'])."','".$_SESSION['email_internaute']."')";  
							$result = $mysqli->query($bpaiements);
							//echo $bpaiements;
							*/
						}
						
						
					if (isset($_SESSION['idEpreuvePersoPre']) && $_SESSION['paiement_indiv'] =='non' && $_SESSION['nb_inscrit']==1)
					{
						$query="UPDATE r_inscriptionepreuveinternaute SET frais_cb=0, paiement_montant =  ".$_SESSION['panier'].", paiement_type = 'AUTRE', paiement_date=NOW() WHERE idInscriptionEpreuveInternaute = ".$id_referent;
						$result_query = $mysqli->query($query);
							
							/*** LOGS ***/
							fputs($fp," (isset(_SESSION['idEpreuvePersoPre']) && _SESSION['paiement_indiv'] =='non' && _SESSION['nb_inscrit']==1  :  NON APPLIQUE !!! UPDATE r_inscriptionepreuveinternaute SET : ".$query." \n");
							/*** LOGS ***/
						//ini_set("display_errors", 1);
						//error_reporting(E_ALL);
						require_once("etransaction/class.paiement_n.php");
						require_once("includes/numerotation.php");
						//require("includes/slashes.php");
						//require("includes/functions_n.php");
						require_once("includes/functions_mail_n.php");						
						
						$donnees = array();
						$row_info = info_internaute_send_mail_test ($reference,(($_GET['Montant']/100)),'CB');
						//print_r($row_info);
						$row_info['frais_cb']= extract_champ_b_paiements('frais_cb',$reference);
	
						//temp
						//***$row_info['emailInternaute']= 'jf@chauveau.nom.fr';
						//temp
						
						//OPTION PLUS			
						if ($row_info['idOptionPlus'] != '')
						{
							$query_option_plus = "SELECT label FROM r_options_plus WHERE idOptionPlus = ".$row_info['idOptionPlus'];
							$result_option_plus = $mysqli->query($query_option_plus);
							$row_option_plus= mysqli_fetch_row($result_option_plus);
							$row_info['nom_option_plus'] = $row_option_plus[0];
							$row_info['prix_option_plus'] = $row_info['Prix_OptionPlus'];
						}
						//OPTION PLUS
						
						if (!empty($row_info['InscritParidInternaute']))
						{
							$row_info['nomReferent']=extract_champ_internaute('nomInternaute',$row_info['InscritParidInternaute']);
							$row_info['prenomReferent']=extract_champ_internaute('prenomInternaute',$row_info['InscritParidInternaute']);					
							
						}						
						
						array_push($donnees,$row_info);
						
						$sujet = "Ats Sport - Inscription à l'épreuve ".$row_info['nomEpreuve']." - ".$row_info['nomParcours'];
						$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
					
						/*** if($affected == 0) send_mail_etat_paiement('individuel',$query2,$query,addslashes($reference)); ****/
						$dossard_iei = extract_champ_id_epreuve_internaute('dossard',$row_info['idInscriptionEpreuveInternaute']);
						if ($dossard_iei == 0) $dossard = numerotation($row_info['idParcours'],$row_info['idEpreuve'],$row_info['idInscriptionEpreuveInternaute']); else $dossard = $dossard_iei;
						
						if ($row_info['place_promo'] ==1) {
							maj_tarif_reduc_place($row_info['idEpreuveParcoursTarif'],'update',$row_info['idInscriptionEpreuveInternaute']);
						}
						
						if ($row_info['code_promo'] != 'Aucun') {
					
							//mise_a_jour_code_promo ($row_info['idEpreuve'],$row_info['idParcours'],$row_info['code_promo'],$row_info['idInscriptionEpreuveInternaute']);
							mise_a_jour_code_promo ($row_info['idEpreuve'],$row_info['idParcours'],$row_info['code_promo'],$row_info['valeur_code_promo'],$row_info['idEpreuveParcoursTarifPromo'], $row_info['idInscriptionEpreuveInternaute']);
						}
						if ($insc_gratuite==0)
						{
							$query = "UPDATE b_paiements SET montant = ".($_GET['Montant']/100).", paiement_date = NOW(), statut='VALIDEE' WHERE reference = '".addslashes($reference)."'";
							$mysqli->query($query);
							/*** LOGS ***/
							fputs($fp," (insc_gratuite==0)  :  UPDATE b_paiements : ".$query." \n");
							/*** LOGS ***/
						}
						$email_organisateur = recup_mail_organisateur_epreuve($row_info['idEpreuve']);
						
						if (!empty($email_organisateur)) {
		
							$row_info['numerotation'] = $dossard;
							$row_info['type_mail'] = 'organisateur';			
							$donnees[0]=$row_info;
							$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$email_organisateur, $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);	
						}
						//$result = $mysqli->query($query);
					}
					else
					{
						if ($insc_gratuite==0) 
						{
						

							/*
							if ($_SESSION['autre_personne']==1) 
							{ 
								$_SESSION['email_internaute'] = $_SESSION['emailInternaute'];
								$_SESSION['id_internaute_referent'] = $_SESSION['log_id'];
								$_SESSION['nom_Internaute']=$_SESSION['nomInternaute'];
								$_SESSION['prenom_Internaute']=$_SESSION['prenomInternaute'];
							}
							
							
							if ($_SESSION['bp_paiement_autre_multiple']==1)
							{
								$_SESSION['id_ref_bp'] = 'A-'.$_SESSION['log_id']."-".$_SESSION['id_unique_paiement'];
								$_SESSION['id_internaute_referent'] = $_SESSION['log_id'];
								$_SESSION['email_internaute'] = $_SESSION['emailInternaute'];
								$_SESSION['nom_Internaute']=$_SESSION['nomInternaute'];
								$_SESSION['prenom_Internaute']=$_SESSION['prenomInternaute'];
							}
							else
							{
								$_SESSION['email_internaute'] = $_SESSION['emailInternaute'];
								$_SESSION['id_internaute_referent'] = $_SESSION['log_id'];
								$_SESSION['nom_Internaute']=$_SESSION['nomInternaute'];
								$_SESSION['prenom_Internaute']=$_SESSION['prenomInternaute'];								
								
								
							}
							*/
							/*
							$bpaiements  = "INSERT INTO b_paiements (reference, montant, frais_cb, date, id_referant, id_epreuve, statut, nomInternaute, prenomInternaute, emailInternaute) ";
							echo $bpaiements .= "VALUE ('".$_SESSION['id_ref_bp']."', ".round($_SESSION['somme_total'], 2).", ".round($_SESSION['somme_frais_cb'], 2).", NOW(), ".$_SESSION['id_internaute_referent'].", ".$_SESSION['idEpreuve'].", 'ATTENTE','".addslashes_form_to_sql($_SESSION['nom_Internaute'])."','".addslashes_form_to_sql($_SESSION['prenom_Internaute'])."','".$_SESSION['email_internaute']."')";  
							//*** $result = $mysqli->query($bpaiements);
							//echo $bpaiements;
							*/
						}
						//***$result = $mysqli->query($bpaiements);
						//echo "id_bp_temp : ".$_SESSION['id_bp_temp'] = $mysqli->insert_id;
						//$_SESSION['id_bp_temp']=1;
					}	
					//}
?>					
				
				
				<div class="col-md-3"></div>
					<div class="panel panel-primary col-md-6" style="max-width: 900px;">
					<?php if ($panel == 0) { ?>	
						<div class="breadcrumb flat no-nums hidden-xs">
							<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>" ><i class="fa fa-1x fa-home"></i></a>
							<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&tp=s">Parcours</a>
							<a href="javascript:;" >Connexion</a>
							<a href="javascript:;">Formulaire</a>
							<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=3">Panier</a>
							<a href="javascript:;" class="active">Validation</a>
						</div>
					<?php } ?>
						<div class="panel-heading">
		
							<h4 class="panel-title"><?php echo $data_epreuve['nomEpreuve']; ?></h4>
						</div>
						<div class="panel-body bg-white">				
							<div class="row">
						<?php if (isset($_SESSION['idEpreuvePersoPre']) && $_SESSION['paiement_indiv'] =='non') { ?>
							<div class="alert alert-info fade in">
								<span class="close" data-dismiss="alert">×</span>
								<i class="fa fa-info-circle fa-2x pull-left"></i>
								<p>Ce formulaire est une pré-inscription pour le groupe <b><?php echo $_SESSION['groupe']; ?></b> - Le paiement devra être effectué par le club ou l'association.</p>
							</div>
						<?php } elseif (isset($_SESSION['idEpreuvePersoPre']) && $_SESSION['paiement_indiv'] =='oui') { ?>
							<div class="alert alert-info fade in">
								<span class="close" data-dismiss="alert">×</span>
								<i class="fa fa-info-circle fa-2x pull-left"></i>
								<p>Ce formulaire est une inscription pour le groupe <b><?php echo $_SESSION['groupe']; ?></b> - Le paiement est individuel.</p>
							</div>						
						<?php } ?>
								<div class="col-sm-12">
									<div class="row">
									<?php if (isset($_SESSION['idEpreuvePersoPre']) && $_SESSION['paiement_indiv'] =='non') { ?>
										<div class="col-sm-12">
											<div class="panel panel-orange" >
												<div class="panel-heading" style="padding:5px">
						
													<h6 class="panel-title" style="font-size:16px">Inscription dans le groupe <b><?php echo $_SESSION['groupe']; ?></b></h6>
												</div>
												<div class="panel-body text-black" style="background:#e2e7eb">

												<div class="row row-centered">
													<div class="col-sm-12 col-centered">
														<div class="panel panel-info">
															<div class="panel-heading">
																<h4 class="panel-title"> Votre inscription est bien enregistré !</h4>
																
															</div>
															<div class="panel-body bg-black">
																<p class="text-center">
																	<span style="font-weight:bold;font-size:1.2em" class=""></span>
																		</p><p class="note note-info text-center" style="font-weight:bold;font-size:1.1em"> 
																			<span class="text-danger">Votre dossier d'inscription a bien été enregistré !</span>
																		<br> <a href="inscriptions.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&amp;&amp;CodeActivation=A41JB5P">Enregistrer un nouveau dossier en cliquant ICI</a> 											</p>
							
																<p></p>
															</div>
														</div>
													</div>
												</div>

												</div>
											</div>
										</div>								
									
									
									<?php } else  { 
									
										if ($insc_gratuite==0) 
										{
										?>
											<div class="col-sm-12">
												<div class="panel panel-primary" >
													<div class="panel-heading" style="padding:5px">
							
														<h6 class="panel-title" style="font-size:16px">Paiement par CB</h6>
													</div>
	
													<div class="panel-body text-black" style="background:#e2e7eb">
														<p>											
															
																<table class="table">
																	<tbody>
																	<?php if ($paiement_frais_cb==1) { ?>
																		<tr>
																			<td style="border-top:0px;" align="right" width="50%"><h3>Sous total : </td></h3>
																			<td style="border-top:0px;" align="left" width="50%"><h3><b><?php echo $_SESSION['panier']; ?> €</b> </h3></td>
																		</tr>
																		
																		<tr>
																			<td style="border-top:0px;" align="right" width="50%"><h3>Frais bancaires<span class="text-danger"> *</span> : </h3></td>
																			<td style="border-top:0px;" align="left" width="50%"><h3><b><?php echo $_SESSION['somme_frais_cb']; ?> €</b></h3></td> 
																		</tr>
																		<tr>
																		<?php } ?>
																			<td style="border-top:0px;" align="right" width="50%"><h1>Total : </h1></td>
																			<td style="border-top:0px;" align="left" width="50%"><h1 class="text-danger"><b><?php echo $_SESSION['somme_total']; ?> €</b></h1></td> 
																		</tr>
																		<tr> <td colspan="2" id="bouton_payer" style="border-top:0px;display:none"><a  href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=4<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" class="btn btn-success pull-right"><i class="fa fa-eur"></i> Payer </a></td>
																		</tr>
																	</tbody>
																</table>
																
														</p>
														<p> <?php echo $html_ca; ?> </p>
														<?php if ($paiement_frais_cb==1) { ?>
															<p><span class="text-danger">*<small>frais bancaires relatifs à la gestion des inscriptions en ligne</small></p>
														<?php } ?>
													</div>
												</div>
											</div>
										<?php } else {?>
											
											<div class="col-sm-12">
												<div class="panel panel-primary" >
													<div class="panel-heading" style="padding:5px">
							
														<h6 class="panel-title" style="font-size:16px">Inscription(s) gratuite(s)</h6>
													</div>
	
													<div class="panel-body text-black" style="background:#e2e7eb">
														
															
																<table class="table table-centered " id="info_gratuite">
																	<tbody>
																		<tr>
																			<td>
																				<input type="submit" class="btn btn-danger active btn-lg" style="font-weight:bold" value="Valider votre inscription ou vos inscriptions en cliquant ici" onclick="submit_paiement(<?php echo $_SESSION['idEpreuve']; ?>,'<?php echo $iframe; ?>',1,0)">
																			</td>
																		</tr>
																	</tbody>
																</table>
																<table class="table table-centered " id="info_gratuite_confirme" style="display:none">
																	<tbody>
																		<tr>
																			<td>
																				<button class="btn btn-succes active btn-lg" style="font-weight:bold" >Enregistrement(s) effectué(s) !</button>
																			</td>
																		<tr>
																			<td>
																				<a href="moncompte.php" class="btn btn-info active btn-lg" style="font-weight:bold" >Editer mon compte</a>
																			</td>
																		</tr>
																		<tr>
																			<td>
																				<a href="liste_des_inscrits.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>" class="btn btn-info active btn-lg" style="font-weight:bold" >Liste des inscrits</a>
																			</td>
																		</tr>
																		</tr>
																	</tbody>
																</table>															
														
													</div>
												</div>
											</div>										
										
										
										<?php } ?>
										<!--
										<div class="col-sm-6">
											<div class="panel panel-orange" >
												<div class="panel-heading" style="padding:5px">
						
													<h6 class="panel-title" style="font-size:16px">Paiement par chèque</h6>
												</div>
												<div class="panel-body text-black" style="background:#e2e7eb">
													<p><?php //print_r($_SESSION['rieis']); ?></p>
												</div>
											</div>
										</div>
										/-->
										<?php } ?>
									</div>
									
								</div>
							</div>
						</div>
					</div>
				<div class="col-md-3"></div>
				<?php  } else { echo "<script> window.location.replace('insc.php?id_epreuve=".$_SESSION['idEpreuve']."&step=start') </script>"; }?>

	</div>				
</div>
							<div class="modal fade" id="modal-dialog_mdp">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
											<h4 class="modal-title">Mot de passe oublié ?</h4>
										</div>
										<div class="modal-body">
										<form action="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=2<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" method="POST">
											<fieldset>
												<div class="form-group">
													<label for="exampleInputPassword1">Votre compte de connexion</label>
													<input class="form-control" name="login_perdu" placeholder="ex: MDB_45" type="text">
												</div>
												<div class="form-group">
													<label for="exampleInputEmail1">Votre adresse email</label>
													<input class="form-control" name="email_perdu" placeholder="ex: monemail@domaine.com" type="email">
												</div>
												<button type="submit" class="btn btn-sm btn-primary m-r-5">Envoyer</button>
											</fieldset>
										</form>
										</div>
										<div class="modal-footer">
											<a href="javascript:;" class="btn btn-sm btn-white" data-dismiss="modal">Fermer</a>
										</div>
									</div>
								</div>
							</div>

							<div class="modal fade" id="modal-dialog_ident">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
											<h4 class="modal-title">Identifiant oublié ?</h4>
										</div>
										<div class="modal-body">
										<form action="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>&step=2<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>" method="POST">
											<fieldset>
												<div class="form-group">
													<label for="exampleInputPassword1">Votre nom</label>
													<input class="form-control" name="nom_perdu" placeholder="ex: DURAND" type="text">
												</div>
												<div class="form-group">
													<label for="exampleInputPassword1">Votre date de naissance</label>
													<input class="form-control" name="naissance_perdu" placeholder="ex: 01/12/1970" type="text">
												</div>
												<div class="form-group">
													<label for="exampleInputEmail1">Votre adresse email</label>
													<input class="form-control" name="email_perdu" placeholder="ex: monemail@domaine.com" type="email">
												</div>
												<button type="submit" class="btn btn-sm btn-primary m-r-5">Envoyer</button>
												<input class="form-control" name="ident_perdu" value="1" type="hidden">
											</fieldset>
										</form>
										</div>
										<div class="modal-footer">
											<a href="javascript:;" class="btn btn-sm btn-white" data-dismiss="modal">Fermer</a>
										</div>
									</div>
								</div>
							</div>							
							
							<div class="modal fade" id="modal-dialog_simulation">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
											<h4 class="modal-title">SIMULATION D'INSCRIPTION</h4>
										</div>
										<div class="modal-body">
												La simulation a bien été effectuée.
										</div>
										<div class="modal-footer">
											<a href="insc.php?id_epreuve=<?php echo $_SESSION['idEpreuve']; ?>" class="btn btn-sm btn-white" data-dismiss="modal">Revenir à l'accueil</a>
										</div>
									</div>
								</div>
							</div>
							
							<div class="modal fade" id="modal-countdown" >
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
															<h4 class="modal-title">LES INSCRIPTIONS SERONT OUVERTES DANS</h4>
										</div>
										<div class="modal-body" style ="font-family: Lato, sans-serif;  background-color: #000;">
											<div class="h-100">
												<div id="countdown" class="row h-100 justify-content-center align-items-center"></div>
											</div>
										</div>
										<div class="modal-footer">
											<!-- <a href="index.php" class="btn btn-sm btn-white" data-dismiss="modal">Revenir à l'accueil</a> /-->
										</div>
									</div>
								</div>
							</div>
							
							<div class="modal fade" id="modal-inscription_non_ouverte" >
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
															<h4 class="modal-title">INFORMATION</h4>
										</div>
										<div class="modal-body" style ="color:#fff;font-family: Lato, sans-serif;  background-color: #000;">
											<h1>CETTE EPREUVE N'EST PAS OUVERTE AUX INSCRIPTIONS EN LIGNE<h1>
										</div>
										<div class="modal-footer">
											<!-- <a href="index.php" class="btn btn-sm btn-white" data-dismiss="modal">Revenir à l'accueil</a> /-->
										</div>
									</div>
								</div>
							</div>
<!--
<div class="theme-panel"> 
<button class="theme-collapse-btn" style="left: -150px;width: 150px;height: 35px;top: -110px;"><span style="font-size:14px;line-height:0">Total: </span><strong><span class="" id="prix_total_affichage_cote" style="line-height:0"><?php if (empty($_POST['prix_total'])) echo "0"; else echo $_POST['prix_total']; ?> €</strong></span></button>
</div>
/-->
        <!-- footer -->
		<?php if ($panel == 0) include( 'footer.php' ); else include ( 'footer-iframe-v2.php' ) ;?>
        


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
	<script src="admin/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
	<script src="admin/assets/plugins/bootstrap-datepicker/locales/bootstrap-datepicker.fr.min.js"></script>
	<script src="assets/plugins/fancybox/jquery.fancybox.pack.js"></script>
	<script src="assets/plugins/countdown/js/countdown.jquery.js"></script>
	<script src="assets/js/apps.js"></script>
	<!-- ================== END BASE JS ================== -->

	<script> 
<?php 
$ai = fermeture_inscription($_SESSION['idEpreuve']);
if ($ai==0)  { ?>;
		$('#modal-inscription_non_ouverte').modal({backdrop: 'static', keyboard: false});
<?php } 
 $oi = ouverture_inscription ($_SESSION['idEpreuve']);



//echo "CCC: ".print_r($oi);
if (!empty($oi))  { ?>;

        $('#countdown').countdown({
            year: <?php echo $oi['annee']; ?>, month: <?php echo $oi['mois']; ?>, day: <?php echo $oi['jour']; ?>,
        });
		$('#modal-countdown').modal({backdrop: 'static', keyboard: false});
<?php } ?>

<?php if ($update_ok==1 || isset($_GET['del_reg'])) { 

?>

//console.log('<?php echo basename($_SERVER["REQUEST_URI"]); ?>');

window.location.href = "insc.php?id_epreuve="+<?php echo $_SESSION['idEpreuve']; ?>+"&step=3<?php if($_GET['panel'] == 'iframe') echo "&panel=iframe"; ?>";


<?php } ?>	
        /*
		$('#sp90').cycle({ 
            fx:     'scrollLeft', 
            timeout: 5000, 
            before:  onBefore, 
            after:   onAfter
        });
		*/
        function onBefore() { 
            $('#output').html("Scrolling image:<br>" + this.src); 
        } 
        function onAfter() { 
            $('#output').html("Scroll complete for:<br>" + this.src).append('<h3>' + this.alt + '</h3>'); 
        } 
	
		$(document).ready(function() {
		    App.init();


		});

	    $("#datepicker-default").datepicker({
        todayHighlight: true,
		language: 'fr'
    });
	
$('#connex_email_back').click(function (e) {
e.preventDefault();

	$('#aff_alert_noident').hide();
	$('#connex_email_back').hide();
	$('#aff_connex_email').show();
	$('#aff_connex_pass').hide();
	$('#connex_email_cont').hide();
	//$('#connex_email').show();
	$('#aff_ident_ok').hide();
	$('#connex_email').show();
	
});

$('#connex_email_suivant').click(function (e) {
e.preventDefault();
//console.log('connex_email');
if (!$('#connex_email').val()) return;

if ($('#aff_connex_email').is(":visible"))
{
	$('#connex_email_back').show();
	$('#aff_connex_email').hide();
	$('#aff_connex_pass').show();

}
else
{
	//console.log('ident');
	login = $('#connex_email').val();
    pass = 	$('#connex_pass').val();
		var cpt=0;
		var targetUrl = 'includes/ajaxconnex.php';
			$.post(targetUrl,
			
			{
			login: login,
			pass : pass
			}		
			
			).done(function(data) {
		var dataObj = JSON.parse(data);
		
		if (dataObj.connect =='oui' ) {
		
		//console.log('Cette personne existe');
		$('#aff_connex_pass').hide();
		$('#aff_alert_noident').hide();
		$('#info_connexion').hide();
		$('#legend_ident_off').hide();
		if (dataObj.user_no_compte==0) $('#legend_ident_on').show();
		$('#aff_ident_ok').show();
		$('#ident_ok').show();
		$('#aff_ident_ok').html('<legend>Bonjour ' + dataObj.prenom + ' ' + dataObj.nom + '</legend>');
		$('#connex_email_cont').show();
		$('#connex_email').hide();
		window.top.window.scrollTo(0,0);
	
		}
		else
		{
			$('#aff_alert_noident_2').hide();
			$('#aff_alert_noident').show();
		}
	
	
	
	});
}	



});	

$('#reglement_epreuve_check').click(function (e) {
//e.preventDefault();
		//bouton_payer
		//console.log('click');
		if ($('#reglement_epreuve_check').is(':checked') == true  ) { 
			$('#bouton_payer').show();
			$('#bouton_payer_info').hide();
		}
		else
		{
			$('#bouton_payer').hide();
			$('#bouton_payer_info').show();
			console.log('hide');
		}
		/*
		if ($('#reglement_pp_check').is(':checked') == true ) { 
			alert("Merci d'accepter le réglement intérieur de Ats Sport."); return false;
		}
		*/
});

$('#reglement_pp_check').click(function (e) {
//e.preventDefault();
		//bouton_payer
		console.log('click');
		if ($('#reglement_epreuve_check').is(':checked') == true && $('#reglement_pp_check').is(':checked') == true ) { 
			$('#bouton_payer').show(); //console.log('show');
		}
		else
		{
			$('#bouton_payer').hide();
			//console.log('hide');
		}
		/*
		if ($('#reglement_pp_check').is(':checked') == true ) { 
			alert("Merci d'accepter le réglement intérieur de Ats Sport."); return false;
		}
		*/
});
//insc_libre

$('#inscription_libre').click(function (e) {
//e.preventDefault();
		//bouton_payer
		console.log('click');
		if ($('#inscription_libre').is(':checked') == true ) { 
			$('#mot_de_passe').show(); //console.log('show');
			$("#insc_pass").prop('required',true);
			$("#insc_pass_confirm").prop('required',true);
			$("#connex_sign_up").val("S'inscrire");

		}
		else
		{
			$('#mot_de_passe').hide();
			$("#insc_pass").prop('required',false);
			$("#insc_pass_confirm").prop('required',false);
			$("#connex_sign_up").val("S'enregistrer");
			//console.log('hide');
		}
		/*
		if ($('#reglement_pp_check').is(':checked') == true ) { 
			alert("Merci d'accepter le réglement intérieur de Ats Sport."); return false;
		}
		*/
});


$('#connex_sign_up').click(function (e) {
e.preventDefault();


/*
if (!$('#insc_email').val()) return;
if (!$('#insc_email_confirm').val()) return;
*/

/*
if ($('#aff_connex_email').is(":visible"))
{
	$('#connex_email_back').show();
	$('#aff_connex_email').hide();
	$('#aff_connex_pass').show();

}
else
{
	*/
	
	var nom = $('#insc_nom').val();
    var prenom = $('#insc_prenom').val();
	var sexe = $('#insc_sexe').val();
	var date_naissance = $('#datepicker-default').val();
	var email = $('#insc_email').val();
    var pass = 	$('#insc_pass').val();
	var confirm_pass = 	$('#insc_pass_confirm').val();
	var new_user = 1;

	if (nom=='' || prenom == '' || date_naissance =='' || email == '' ) {
					alert("Merci de remplir tous les champs"); return false;
		}
	
	if($("#insc_pass").prop('required')){
		console.log("requirex");
		if (pass == '' ) {
					alert("Merci de mettre un mot de passe"); return false;
		}
		
	}

		
		if ($('#reglement_pp_check').is(':checked') == false ) { 
			alert("Merci d'accepter le réglement intérieur de Ats Sport."); return false;
		}
		
		var cpt=0;
		var targetUrl = 'includes/ajaxconnex.php';
			$.post(targetUrl,
			
			{
			nom: nom,
			prenom : prenom,
			date_naissance : date_naissance,
			sexe : sexe,
			email : email,
			pass : pass,
			new_user : new_user
			}		
			
			).done(function(data) {
		var dataObj = JSON.parse(data);
		
		if (dataObj.connect =='oui' ) {
		
		//console.log('Cette personne existe');
		$("#login").show();
		$("#register").hide();
		$('#aff_connex_pass').hide();
		$('#aff_alert_noident').hide();
		$('#info_connexion').hide();
		$('#legend_ident_off').hide();
		if (dataObj.user_no_compte==0) $('#legend_ident_on').show();
		$('#aff_ident_ok').show();
		$('#ident_ok').show();
		$('#aff_ident_ok').html('<legend>Bonjour ' + dataObj.prenom + ' ' + dataObj.nom + '</legend>');
		$('#connex_email_cont').show();
		$('#connex_email').hide();
		window.top.window.scrollTo(0,0);
	
		}
		else
		{
			//console.log('Cette personne n\'existe pas');
	$("#login").show();
	$("#register").hide();		
		
		$('#aff_connex_pass').hide();
		$('#aff_alert_noident').hide();
		$('#info_connexion').hide();

		$('#legend_ident_off').hide();
		if (dataObj.user_no_compte==0) $('#legend_ident_on').show();
		$('#aff_ident_ok').show();
		$('#ident_ok').show();
		$('#aff_ident_ok').html('<legend>Bonjour ' + dataObj.prenom + ' ' + dataObj.nom + '</legend>');
		$('#connex_email_cont').show();
		$('#connex_email').hide();
		window.top.window.scrollTo(0,0);
		}
	
	
	
	});
//}	



});	

function option_plus (id_parcours)
{
	
	var value_option_plus = $('#select_option_'+id_parcours).val();
	//console.log(value_option_plus);
	if (value_option_plus==0)
	{
		
		$("#button_step_"+id_parcours).prop('disabled', true);
		$('#select_option_plus').val('');
	}
	else
	{
		$("#button_step_"+id_parcours).prop('disabled', false);
		$('#select_option_plus').val(value_option_plus);
		
	}
	
	
	
	
	
	
}
function change_tarif()
{
	var total = 0;
	var total_nb=0;
	/*var tarif_en_cours = $('prix_total').val();
	var res = id.split("_");
	var tarif_value = parseFloat(res[2]);
	
	var nb = parseInt(value);
	var tarif_calcul = nb * tarif_value;
	console.log (id + "-" + res[1] + " - " + tarif_value + "-" + nb + "-" + tarif_calcul);
	*/
	
$("select[id^='nbplace_']" ).each(function(){
 var id = this.id;
 var res = id.split("_");
 var tarif_value = parseFloat(res[2]);
 var nb = parseInt($('#'+ id).val());
 var tarif_calcul = nb * tarif_value;
//console.log(id + " - " + tarif_value + " - "+nb);
 
 total = total + tarif_calcul;
 total_nb = total_nb + nb;
});
console.log(total);
if (total_nb !=0) { $("#button_step").prop('disabled', false); $('#insc_form_relais').hide(); } else { $('#insc_form_relais').show(); $("#button_step").prop('disabled', true);}
 $('#prix_total').val(total);
 $('#prix_total_affichage_bas').html(total+' €');
  $('#prix_total_affichage_cote').html(total+' €');
}

function change_tarif_relais()
{
	var total = 0;
	var total_nb=0;
	/*var tarif_en_cours = $('prix_total').val();
	var res = id.split("_");
	var tarif_value = parseFloat(res[2]);
	
	var nb = parseInt(value);
	var tarif_calcul = nb * tarif_value;
	console.log (id + "-" + res[1] + " - " + tarif_value + "-" + nb + "-" + tarif_calcul);
	*/
	
$("select[id^='relais_nbplace_']" ).each(function(){
 var id = this.id;
 var res = id.split("_");
 var tarif_value = parseFloat(res[3]);
 var nb = parseInt($('#'+ id).val());
 if (nb >0) nb = 1; else nb = 0;
 var tarif_calcul = nb * tarif_value;
 console.log(id + " - " + tarif_value + " - "+nb);
 
 total = total + tarif_calcul;
 total_nb = total_nb + nb;
	
});
//if (total_nb !=0) { $('#insc_form').hide(); $("#button_step_relais").prop('disabled', false);} else { $('#insc_form').show(); $("#button_step_relais").prop('disabled', true);}
 $('#prix_total_relais').val(total);
 $('#prix_total_affichage_bas_relais').html(total+' €');
  $('#prix_total_affichage_cote').html(total+' €');
}
function change_etat_relais(id)
{
	$("select[id^='relais_nbplace_']" ).each(function(){
	
		var id_relais = this.id;
		$("#"+id_relais).prop('disabled', true);
	
	});
	var res = id.split("_");
	//console.log(res[2]);
	$("#relais_nbplace_"+res[2]+"_"+res[3]).prop('disabled', false);
	
}
function check_place()
{

var total = 0;	
	$("select[id^='nbplace_']" ).each(function(){
	 var id = this.id;
	var nb = parseInt($('#'+ id).val());

	total = total + nb;
	
});	

if (total==0) { $('#aff_check_place').show(); return false; }
	
}
function show_register(id)
{
	if (id==0) {
		
		$("#login").show();
		$("#register").hide();
		$("#insc_email").val('');
	}
	else
	{
		$("#login").hide();
		$("#register").show();
		$("#insc_email").val('');
	}		
		
	
}
function show_login()
{
	$("#login").show();
	$("#register").hide();
}
	$(".reglement_pp_modal").fancybox({
		maxWidth	: 800,
		maxHeight	: 600,
		fitToView	: false,
		width		: '70%',
		height		: '70%',
		autoSize	: false,
		closeClick	: false,
		openEffect	: 'none',
		closeEffect	: 'none'
	});
if ($(".fancybox-pdf").length) {
	
	$(".fancybox-pdf").fancybox({
	'width': 800, // or whatever you want
	'height': 600, // or whatever you want
	'type': 'iframe'
});
}
if ($(".fancybox").length) {
	
	$(".fancybox").fancybox({
		'transitionIn'	:	'elastic',
		'transitionOut'	:	'elastic',
		'speedIn'		:	100, 
		'speedOut'		:	100, 
		'overlayShow'	:	false
	});

}
function Check(password)
{
	//alert(password);
	var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
	var okRegex = new RegExp("(?=.{6,}).*", "g");
	var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
        if (okRegex.test(password) === false) {
            // If ok regex doesn't match the password
			$('#div-pass_nominal').addClass('has-error has-feedback');
			$('#span-pass_nominal').addClass('fa fa-times form-control-feedback');
			$('#insc_pass').val('');
               alert('Le mot de passe doit comporter au moins 8 caractères');
			   return false;
		}
		else
		{
			$('#div-pass_nominal').removeClass('has-error has-feedback').addClass('has-success has-feedback');
			$('#span-pass_nominal').removeClass('fa fa-times form-control-feedback').addClass('fa fa-check form-control-feedback');
			
		}
        if (mediumRegex.test(password) === false) {
			$('#div-pass_nominal').addClass('has-error has-feedback');
			$('#span-pass_nominal').addClass('fa fa-times form-control-feedback');
			$('#insc_pass').val('');
               alert('Le mot de passe doit contenir des miniscules,majuscules et des chiffres');
			   return false;
		}
		else
		{
			
			$('#div-pass_nominal').removeClass('has-error has-feedback').addClass('has-success has-feedback');
			$('#span-pass_nominal').removeClass('fa fa-times form-control-feedback').addClass('fa fa-check form-control-feedback');
			
		}
		return true;
}

function calcul_frais_annulation(id,id_ref_pc,type,pourcentage,fixe,montant_inscription, tarif, idAssuranceAnnulation)
{
	

	<?php if ($relais=='oui' && $type_assurance_annulation=='participation_epreuve') { ?>
		var total_participation_parcours = parseFloat($('#total_all_participation_epreuve').val());
	<?php } elseif ($relais=='oui' && $type_assurance_annulation=='participation_parcours') { ?>
		var total_participation_parcours = parseFloat($('#total_all_participation_parcours').val());
	<?php } else { ?>
		//var total_participation_parcours = parseFloat($('#total_'+type+'_'+id_ref_pc).val());
		var total_participation_parcours = (parseFloat($('#prix_total').val()) - parseFloat($('#frais_cb').val()));
		var total_participation_parcours = montant_inscription;
		var total_participation_parcours = tarif;
		//console.log('#total_'+type+'_'+id_ref_pc + ' ---- type : '+type + 'id_ref_pc : '+id_ref_pc + '3 : '+total_participation_parcours);
	<?php } ?>
	//console.log ('pourcentage : ' + pourcentage + 'total_participation_parcours : ' + total_participation_parcours);
	
	//console.log(calcul_pourcentage);
	var prix_total = parseFloat($('#prix_total').val());
	var prix_total_cheque = parseFloat($('#prix_total_cheque').val());
	var cout_assurance_actuel = parseFloat($('#total_assurance').val());
	
	if($('#'+id).prop("checked") == true)
	{
		if (fixe>0)
		{
			var calcul_pourcentage =  fixe;
		}
		else
		{
			var calcul_pourcentage =  roundDecimal((total_participation_parcours*(1+(pourcentage)/100)-total_participation_parcours),2);
		}
		//var prix_total_base = parseFloat($('#prix_total_base').val());
		$('#prix_total').val(prix_total+calcul_pourcentage);
		$('#aff_prix_total').html(prix_total+calcul_pourcentage);
		$('#total_assurance').val(cout_assurance_actuel+calcul_pourcentage);
		$('#aff_frais_assurance').html('+ '+(cout_assurance_actuel+calcul_pourcentage)+' €');
		$('#prix_total_cheque').val(prix_total_cheque+calcul_pourcentage);
		$('#aff_montant_cheque').html((prix_total_cheque+calcul_pourcentage)+' €');
		$('#cout_assurance_'+id_ref_pc).val(calcul_pourcentage);

				var targetUrl = 'includes/ajaxAssurance.php?tarif='+ calcul_pourcentage + '&idAssurance='+idAssuranceAnnulation+'&id='+id_ref_pc+'&action=i'; 
				$.post(targetUrl).done(function(data) {
					var dataObj = JSON.parse(data);
				});
					
	}
	else if($('#'+id). prop("checked") == false)
	{

		if (fixe > 0)
		{
			var calcul_pourcentage =  fixe;
		}
		else
		{
			var calcul_pourcentage =  roundDecimal((total_participation_parcours*(1+(pourcentage)/100)-total_participation_parcours),2);
		}
		
		var prix_total_base = parseFloat($('#prix_total_base').val());
		var cout_assurance_actuel = parseFloat($('#total_assurance').val());
		$('#prix_total').val(prix_total-calcul_pourcentage);
		$('#aff_prix_total').html(prix_total-calcul_pourcentage);
		$('#total_assurance').val(roundDecimal(cout_assurance_actuel-calcul_pourcentage),2);
		$('#aff_frais_assurance').html('+ '+(roundDecimal(cout_assurance_actuel-calcul_pourcentage,2))+' €');
		$('#prix_total_cheque').val(prix_total_cheque-calcul_pourcentage);
		$('#aff_montant_cheque').html((prix_total_cheque-calcul_pourcentage)+' €');
		$('#cout_assurance_'+id_ref_pc).val(cout_assurance_actuel-calcul_pourcentage);
	
				var targetUrl = 'includes/ajaxAssurance.php?tarif='+ calcul_pourcentage + '&idAssurance='+idAssuranceAnnulation+'&id='+id_ref_pc+'&action=d'; 
				$.post(targetUrl).done(function(data) {
					var dataObj = JSON.parse(data);
				});
		
	}
}
function roundDecimal(nombre, precision){
    var precision = precision || 2;
    var tmp = Math.pow(10, precision);
    return Math.round( nombre*tmp )/tmp;
}

function check_code_promo(action,idrefpc,id_epreuve,id_parcours) {
	
		if (action==1) 
		{
			var code_promo = $('#code_promo_'+idrefpc).val();
			if (code_promo =='') return false;
		}
		else
		{
			
			var code_promo = '';
		}
		
		var error = 0;			

				var cpt=0;
				var targetUrl = 'includes/ajaxCodePromo_new.php'; 
				$.post(targetUrl,
				{
					action:action,
					code_promo:code_promo,
					idrefpc: idrefpc,
					id_epreuve: id_epreuve,
					id_parcours: id_parcours

				}
				
				
				).done(function(data) {
					var dataObj = JSON.parse(data);
			
					if (dataObj.etat == 'OK' ) 
					{
						
						$('#check_promo_return').hide();
						if (dataObj.action == 1) 
						{
							alert('Votre code promotionnel est activé !');
						}
						else if (dataObj.action == 2) 
						{
							alert('Votre code promotionnel a été supprimé');
						}
						
						window.location.reload(true);

					}
					else
					{
						$('#check_promo_return').show();
						

	
					}
									//$('#code_promo_'+nb_insc).attr('readonly');
									//***$('#code_promo_'+nb_insc).prop('readonly', true);
	
									
									//.remove()
								


						//alert(dataObj.etat);
				});
			//}
	

}

function check_groupe(id,action,idepreuve)
{
	
	if (action==2)
	{
		$('#affichage_groupe').hide();
		$('#groupe_ok').hide();
		$('#groupe_new').show();
		return true;
		
		
	}
	else if (action==3)
	{
		$('#affichage_groupe').show();
		$('#groupe_ok').hide();
		$('#groupe_new').hide();
		$('#groupe_ok_new').hide();
		return true;
		
		
	}
	else if (action==5)
	{
			var targetUrl = 'includes/ajaxGroupe.php';
			
			var nomGroupe = $('#groupe_nom').val();
			var patronymeGroupe = $('#groupe_patronyme').val();
			var emailGroupe = $('#groupe_email').val();
			var telGroupe = $('#groupe_tel').val();
			var mdpGroupe = $('#groupe_mdp').val();
			var mdpGroupeAdmin = $('#groupe_mdp_admin').val();
			
			//alert (patronymeGroupe);
			$.post(targetUrl,
			
			{
				action:action,
				id: id,
				idepreuve: idepreuve,
				nomGroupe: nomGroupe,
				patronymeGroupe: patronymeGroupe,
				emailGroupe: emailGroupe,
				telGroupe: telGroupe,
				mdpGroupe: mdpGroupe,
				mdpGroupeAdmin: mdpGroupeAdmin

			}		
			
			).done(function(data) {
				
			var dataObj = JSON.parse(data);
			
			if (dataObj.etat == 'OK' ) {
				
					//console.log('OK');
					$('#grp_error_'+id).hide();
					$('#grp_error_empty_'+id).hide();
					$('#nom_groupe_new').html('<b>'+dataObj.groupe+'</b>');
					$('#mdp_groupe_new').html('<b>'+dataObj.mdp_groupe_new+'</b>');
					$('#affichage_groupe').hide();
					$('#groupe_new').hide();
					$('#groupe_ok_new').show();
					window.top.window.scrollTo(0,0);
					return true;
			}
			else if (dataObj.etat == 'DEL')
			{
				
					$('#grp_error_'+id).hide();
					$('#grp_error_empty_'+id).hide();
					$('#nom_groupe').html('');
					$('#affichage_groupe').show();
					$('#groupe_ok').hide();
					window.top.window.scrollTo(0,0);
					return false;
				
			}
			else
			{
					$('#grp_error_'+id).show();
					//console.log('KO');
					window.top.window.scrollTo(0,0);
					return false;
			}
	});		
		

	}
	else
	{
		
		
		var value = $('#groupe_'+id).val();
		if (!value && action ==0)
		{
			$('#grp_error_empty_'+id).show();
			return false;
			
		}
		
		var targetUrl = 'includes/ajaxGroupe.php'; 
				$.post(targetUrl,
				
				{
					action:action,
					id: id,
					value:value
				}		
				
				).done(function(data) {
					
				var dataObj = JSON.parse(data);
				
				if (dataObj.etat == 'OK' ) {
					
						//console.log('OK');
						$('#grp_error_'+id).hide();
						$('#grp_error_empty_'+id).hide();
						$('#nom_groupe').html('<b>'+dataObj.groupe+'</b>');
						$('#affichage_groupe').hide();
						$('#groupe_ok').show();
						window.top.window.scrollTo(0,0);
						return true;
				}
				else if (dataObj.etat == 'DEL')
				{
					
						$('#grp_error_'+id).hide();
						$('#grp_error_empty_'+id).hide();
						$('#nom_groupe').html('');
						$('#affichage_groupe').show();
						$('#groupe_ok').hide();
						$('#aff_groupe_new').hide();
						$('#groupe_ok_new').hide();
						window.top.window.scrollTo(0,0);
						return false;
					
				}
				else
				{
						$('#grp_error_'+id).show();
						//console.log('KO');
						window.top.window.scrollTo(0,0);
						return false;
				}
		});
	}
	
}

function submit_paiement (id_epreuve,iframe,gratuit,simul) {

if (iframe != '') {
	url = "http://www.ats-sport.com/retour_paiement.php?id_epreuve="+id_epreuve+"&panel="+iframe;
	$('#top_page').focus();
	setTimeout(function(){document.location.href = url},500);
}
var Ref = $('#PBX_CMD').val();
var Montant = $('#PBX_TOTAL').val();
	var targetUrl = 'includes/ajaxSession.php'; 
	$.post(targetUrl,
				
				{
					gratuit:gratuit,
					simul:simul,
					Ref:Ref,
					Montant:Montant
				}		
				
				).done(function(data) {
	var dataObj = JSON.parse(data);
	
	if (dataObj.etat == 'OK' ) {
		//console.log('OK');
			if (simul == 1) 
			{
				//alert("roro");
				//$('#modal-dialog_simulation').modal
				$('#modal-dialog_simulation').modal('show');
				
				
			}
			else if (gratuit == 0) 
			{
				//console.log('OK');
				$('form#form_paiement').submit();
			}

			else
			{
				$('#info_gratuite').hide();	
				$('#info_gratuite_confirme').show();
				$('#info_caddie').hide();
			}
			return true;
	}
	else
	{
			console.log('KO');
			return false;
	}
	});
	




}

function confirm_champs(name) {
	
		$("#"+name+"_conf").bind("cut copy paste",function(e) {
		e.preventDefault();
	});	
		console.log("#"+name+"_conf");

	
	if ($("#"+name).val() != $("#"+name+"_conf").val())
	{
		$("#"+name+"_conf").val('');
		if (name=='groupe_email') alert("La confirmation des deux emails ne correspondent pas - Veuillez vérifier."); else alert("La confirmation des deux mots de passe ne correspondent pas - Veuillez vérifier.");
		//***$("#insc_email_conf_"+id).focus();
	}

}

function check_email(value,action) 
{
	if (action==1)
	{
		if (validateEmail(value)) { return true; } else { alert('L\'email ne semble pas conforme - veuillez vérifier s\'il vous plait'); return false; }
	}
	else
	{
		var pass = 	$('#insc_email').val();
		//console.log(pass);
		if ( pass != value) {
			alert("La confirmation de l'email ne correspondent pas. Veuillez vérifier s'il vous plait."); 
			$('#insc_email_reconfirm').val('');
			$('#insc_email_reconfirm').focus();
			return false;
		}	
	}
}

	
function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

function check_pass(value)
{

		if ($('#insc_pass').val() != value ) { 
			alert("Les mots de passe ne sont pas identiques. Veuillez vérifier s'il vous plait."); 
			$('#insc_pass_confirm').val(''); 
			return false;
		}
	
}

function newsletter(idInternaute,type_epreuve) {
	
	var chk = $('#reglement_cnil_check:checked').val();
	if (chk =='on') { var etat ='oui' } else { var etat='non'; }
	//if (etat=='non') if (confirm("êtes vous sur de passer ce dossard en non retiré ?") == false) return false;
	
	//console.log(etat);
	var targetUrl = 'includes/ajaxCnil.php';
	
		$.post(targetUrl,
        
		{
			idInternaute : idInternaute,
			etat : etat,
			type_epreuve : type_epreuve

			
        }		
		
		).done(function(data) {
		
		//var dataObj = JSON.parse(data);
		/*
		if( dataObj.nb_dossard_retire != null) $('#nb_dossard_retire').html( dataObj.nb_dossard_retire );
		
		if (dataObj.comeback == 'OK' ) 
		{
		//console.log(dataObj.comeback);
			
			if (etat=='oui') {
				var html = '<button onclick="dossard_retire('+idInscriptionEpreuveInternaute+',\'non\');" class="btn btn-success btn-lg" href="javascript:;">RETIRE</button>';
				$('#dossard_retire_'+ idInscriptionEpreuveInternaute).html(html);
			}
			else {
				var html = '<button onclick="dossard_retire('+idInscriptionEpreuveInternaute+',\'oui\');" class="btn btn-danger btn-lg" href="javascript:;">NON RETIRE</button>';
				$('#dossard_retire_'+ idInscriptionEpreuveInternaute).html(html);
			}
		
		}
		else 
		{
			notification('Notification','Une erreur est survenue à la suppression du fichier',5000,'ko');
		}
		*/
		
		});
		

					
	
	
}

function vli(id) {
	
	var chk = $('#reglement_cnil_vli:checked').val();
	if (chk =='on') { var etat ='oui' } else { var etat='non'; }
	//if (etat=='non') if (confirm("êtes vous sur de passer ce dossard en non retiré ?") == false) return false;
	
	//console.log(etat);
	var targetUrl = 'includes/ajaxCnil.php';
	
		$.post(targetUrl,
        
		{
			id : id,
			etat : etat
        }		
		
		).done(function(data) {
		
		//var dataObj = JSON.parse(data);
		/*
		if( dataObj.nb_dossard_retire != null) $('#nb_dossard_retire').html( dataObj.nb_dossard_retire );
		
		if (dataObj.comeback == 'OK' ) 
		{
		//console.log(dataObj.comeback);
			
			if (etat=='oui') {
				var html = '<button onclick="dossard_retire('+idInscriptionEpreuveInternaute+',\'non\');" class="btn btn-success btn-lg" href="javascript:;">RETIRE</button>';
				$('#dossard_retire_'+ idInscriptionEpreuveInternaute).html(html);
			}
			else {
				var html = '<button onclick="dossard_retire('+idInscriptionEpreuveInternaute+',\'oui\');" class="btn btn-danger btn-lg" href="javascript:;">NON RETIRE</button>';
				$('#dossard_retire_'+ idInscriptionEpreuveInternaute).html(html);
			}
		
		}
		else 
		{
			notification('Notification','Une erreur est survenue à la suppression du fichier',5000,'ko');
		}
		*/
		
		});
		

					
	
	
}
	</script>
</body>
</html>