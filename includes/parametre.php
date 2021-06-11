<?PHP
	global $parametre;
	$parametre['mail'] 					= new stdClass();
	$parametre['mailperdu'] 			= new stdClass();
	$parametre['mailinscr'] 			= new stdClass();
	$parametre['mailepreuve'] 			= new stdClass();
	$parametre['mailforminter'] 		= new stdClass();
	$parametre['mailformorg'] 			= new stdClass();
	$parametre['mailing'] 				= new stdClass();
	$parametre['mailforcb'] 			= new stdClass();
	$parametre['mailforcertif'] 		= new stdClass();
	$parametre['mailadmin'] 			= new stdClass();
	$parametre['mailadminepreuve'] 		= new stdClass();
	$parametre['mailadmindevisepreuve'] = new stdClass();
	$parametre['mailadminresultats']	= new stdClass();
	$parametre['mailadminformulaire'] 	= new stdClass();
	$parametre['image_default'] 		= new stdClass();
	$parametre['url_site']  			= new stdClass();
	$parametre['accueil'] 				= new stdClass();
	
	
// MAIL
	$parametre['mail']->smtp_server = "smtp.wanadoo.fr";
	$parametre['mail']->smtp_port = "25";

	$parametre['mailperdu']->from = "contact@ats-sport.com";
	$parametre['mailperdu']->objet = "[ATS-SPORT.COM] : Mot de passe perdu";

	$parametre['mailinscr']->from = "contact@ats-sport.com";
	$parametre['mailinscr']->objet = "[ATS-SPORT.COM] : Vos identifiants";

	$parametre['mailepreuve']->from = "contact@ats-sport.com";
	$parametre['mailepreuve']->objet = "[ATS-SPORT.COM] : Nouvelle epreuve";

	$parametre['mailforminter']->from = "contact@ats-sport.com";
	$parametre['mailforminter']->objet = "[ATS-SPORT.COM] : Inscription à une épreuve";

	$parametre['mailformorg']->from = "contact@ats-sport.com";
	$parametre['mailformorg']->objet = "[ATS-SPORT.COM] : Nouvelle inscription";
	
	$parametre['mailing']->from = "contact@ats-sport.com";
	$parametre['mailing']->objet = "Place d'honneur ou podium?";
	
	$parametre['mailforcb']->from = "contact@ats-sport.com";
	$parametre['mailforcb']->objet = "Confirmation de paiement";
	
	$parametre['mailforcertif']->objet = "Validation de votre certificat";
	

// MAIL ADMIN
	$parametre['mailadmin']->addresse = "contact@ats-sport.com";

	$parametre['mailadminepreuve']->from = "contact@ats-sport.com";
	$parametre['mailadminepreuve']->objet = "[ATS-SPORT.COM] : Nouvelle epreuve";

	$parametre['mailadmindevisepreuve']->from = "contact@ats-sport.com";
	$parametre['mailadmindevisepreuve']->objet = "[ATS-SPORT.COM] : Demande de devis";
	
	$parametre['mailadminresultats']->from = "contact@ats-sport.com";
	$parametre['mailadminresultats']->objet = "[ATS-SPORT.COM] : Nouveaux résultats";

	$parametre['mailadminformulaire']->from = "contact@ats-sport.com";
	$parametre['mailadminformulaire']->objet = "[ATS-SPORT.COM] : Nouveau formulaire";


// IMAGES PAR DEFAUT
	$parametre['image_default']->parcours = "images_typeepreuve/pas-de-photo.png";
	$parametre['image_default']->epreuve = "images_typeepreuve/pas-de-photo.png";


// URL
	$parametre['url_site'] = "http://www.ats-sport.com"; // SANS LE DERNIER '/' !!!


// ACCUEIL
	$parametre['accueil']->nombre_epreuve_par_page = 7;
	$parametre['accueil']->nombre_resultat_par_page = 20;
?>