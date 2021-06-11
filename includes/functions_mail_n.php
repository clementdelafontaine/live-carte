<?php 

global $mysqli;
/**
 * This example shows sending a message using a local sendmail binary.
 */

#4b9ee8
//require_once $_SERVER["DOCUMENT_ROOT"].'/assets/plugins/phpMailer/PHPMailerAutoload.php';
require_once ('/var/www/vhosts/ats-sport.com/httpdocs/assets/plugins/phpMailer/PHPMailerAutoload.php');
//require_once ('../assets/plugins/phpMailer/PHPMailerAutoload.php');

function banniere_accueil_v_mail($type)
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

function send_mail ($expediteur_email,$expediteur_nom,$destinataire_email, $destinataire_nom, $sujet,$corps) {
	global $mysqli;
	if ($corps[0]["webService_Atlas"]=='oui'){//On vérifie que l'epreuve soit aussi une epreuve Atlas
		$row_info = info_internaute_send_mail_test ($corps[0]['idInscriptionEpreuveInternaute'],0,'GRATUIT');

		
		if ($corps[0]["groupe"]!="Aucun"){// verification de l'existence d'un groupe dans l'inscription
			$query  = "SELECT * FROM k_AtlasGroupe WHERE nomGroupe='".$corps[0]['groupe']."' AND IDAtlas='".$corps[0]['IDAtlas']."';";
			$result = $mysqli->query($query);
			$row=mysqli_fetch_assoc($result);///On vérifie si le groupe existe deja dans notre bd 
			if ($row==null){//il existe pas ?
			$body=ajoutTeamAtlas($corps);//si non, on le rajoute chez Atlas
			$query_groupe  = "INSERT INTO k_AtlasGroupe  ";//et dans notre bd
			$query_groupe .= "(IDAtlas, idGroupeAtlas, nomGroupe)VALUES ";
			$query_groupe .= "('".$corps[0]["IDAtlas"]."', ";
			$query_groupe .= "".$body.", ";
			$query_groupe .= "'".$corps[0]["groupe"]."') ";
			
			$result_groupe = $mysqli->query($query_groupe);
			}
				$donnees=array();
			array_push($donnees,$row_info);
			$IDAtlas=$donnees[0]['IDAtlas'];
			$query_b = "INSERT INTO `k_Atlas`( `IDAtlas`, `idInternaute`, `idInscriptionInternaute`, `IDGroupeAtlas`, `nomGroupe`) ";//si oui, on rajoute juste dans notre bd l'user
		$query_b .= "VALUES ('".$IDAtlas."',".$donnees[0]['idInternaute'].",".$corps[0]['idInscriptionEpreuveInternaute'].",'".$row['idGroupeAtlas']."','".$corps[0]['groupe']."');";
		$result_b = $mysqli->query($query_b);
		$corps[0]['teamID']=$body;
		ajoutUserAtlas($corps);//et dans Atlas
			//INSERT INTO `k_AtlasGroupe`(`id`, `IDAtlas`, `idGroupeAtlas`, `nomGroupe`) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5])
			//insetion dans k_AtlasGroupe
		}else{//Il n'y a pas de team

		$donnees=array();
		array_push($donnees,$row_info);
		$IDAtlas=$donnees[0]['IDAtlas'];//alors on rajoute juste la personne dans notre bd et chez atlas
		$query_b = "INSERT INTO `k_Atlas`( `IDAtlas`, `idInternaute`, `idInscriptionInternaute`, `IDGroupeAtlas`, `nomGroupe`) ";
		$query_b .= "VALUES ('".$IDAtlas."',".$donnees[0]['idInternaute'].",".$corps[0]['idInscriptionEpreuveInternaute'].",null,null);";
		$result_b = $mysqli->query($query_b);
		//requete a Atlas
		$corps[0]['teamID']=null;
		ajoutUserAtlas($corps);
	}
	}
//Create a new PHPMailer instance
	$mail = new PHPMailer;
	// Set PHPMailer to use the sendmail transport
	$mail->isSendmail();
	$mail->CharSet = 'UTF-8';
	//Set who the message is to be sent from
	$mail->setFrom($expediteur_email, $expediteur_nom);
	//Set an alternative reply-to address
	$mail->addReplyTo($expediteur_email, $expediteur_nom);
	//Set who the message is to be sent to
	$mail->addAddress($destinataire_email, $destinataire_nom);
	
	//echo $corps[0]['idEpreuve']."-".$corps[0]['type_mail'];
	if ($corps[0]['type_mail'] == 'organisateur' ) {
		//$mail->AddBCC("contact@traildescalades.fr","contact@traildescalades.fr");
		//$mail->AddBCC("contact@ats-sport.com","contact@ats-sport.com");
	}
	$mail->AddBCC("webmaster@ats-sport.com","webmaster@ats-sport.com");
	$mail->AddBCC("webmaster@ats-sport.com","webmaster@ats-sport.com");
	//Set the subject line
	$mail->Subject =  $sujet;
	
	$mail->AddEmbeddedImage('../assets/img/logoATS-bleu2-small.png', 'LOGO-PC');
	//Read an HTML message body from an external file, convert referenced images to embedded,
	//convert HTML into a basic plain-text alternative body
	//$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
	$mail->msgHTML(mail_internaute_inscription($corps));
	//Replace the plain text body with one created manually
	//$mail->AltBody = 'This is a plain-text message body';
	//Attach an image file
	//$mail->addAttachment('images/phpmailer_mini.png');
	
	//send the message, check for errors

	if (!$mail->send()) {
		return $mail->ErrorInfo;
	} else {
		return "ok";
	} 


}

function mail_internaute_inscription ($donnees) {
	global $mysqli;
	//*****$fp = fopen("function_mail.txt","a");
	//*****fputs($fp,date("d/m/Y His")." : ".$_SERVER['REQUEST_URI']."\n");

/*
            [0] => 117016
            [idInternaute] => 117016
            [1] => 110974
            [idInscriptionEpreuveInternaute] => 110974
            [2] => 117016
            [idInternauteReferent] => 117016
            [3] => 3140
            [idEpreuve] => 3140
            [4] => 5307
            [idParcours] => 5307
            [5] => 7877
            [idEpreuveParcoursTarif] => 7877
            [6] => ami
            [nomInternaute] => ami
            [7] => test
            [prenomInternaute] => test
            [8] => jfchauveau@espritweb.fr
            [emailInternaute] => jfchauveau@espritweb.fr
            [9] => 2015-08-17 07:10:21
            [dateInscription] => 2015-08-17 07:10:21
            [10] => non
            [certificatMedical] => non
            [11] => d84ebf0a9da635bc9ced19be43ea71c2
            [idSession] => d84ebf0a9da635bc9ced19be43ea71c2
            [12] => IoLNEF
            [codeSecurite] => IoLNEF
            [13] => TRAIL DES CALADES
            [nomEpreuve] => TRAIL DES CALADES
            [14] => Trail 16km 500D+
            [nomParcours] => Trail 16km 500D+
*/


            $tab_banniere = banniere_accueil_v_mail('banniere_pied_inscription');


            $req_mdp = 0;
            if (!empty($donnees[0]['rmdp_lien'])) {
            	$lien_request_mdp = 'http://www.ats-sport.com/'.$donnees[0]['rmdp_lien'];
            	$req_mdp = 1;
            }

            $lng_inscrit = extract_champ_internaute('langage', $donnees[0]['idInternaute']);
            $lng_trad= mail_langage($lng_inscrit);

            $info_complementaires_parcours = '';

            if (!empty($donnees[0]['infoParcoursInscription'])) { 

            	$info_complementaires_parcours = '<tr>
            	<td style="color:black; padding:5px; border:1px  solid red; margin-bottom:10px">
            	<p align="center"><b><u>Informations</u></b></p>
            	<p class="x_m-b-5">'.$donnees[0]['infoParcoursInscription'].'</p>
            	</td>
            	</tr>';
            }
//print_r($donnees);
            $code_promo = $code_promov2 = $aff_gratuit = '';
            $qui_paye = extract_champ_epreuve('payeur', $donnees[0]['idEpreuve']);
/*
if ($qui_paye == 'organisateur') 
{ 
	$donnees[0]['frais_cb']=0; 
	$donnees[0]['frais_cheque']=0; 
}
 */
if ($donnees[0]['mode_paiement'] == 'CB') 
{ 
	$frais_coureur =  $donnees[0]['frais_cb'];
}
else
{
	$frais_coureur =  $donnees[0]['frais_cheque'];
}

//$donnees[0]['valeur_code_promo'] = 5;
//$donnees[0]['code_promo'] = 'XCFLEL3';
//echo "donnees[0]['frais_cb'] : ".$donnees[0]['frais_cb'];
//echo "cout inscription : ".$donnees[0]['cout'];
//echo '<br>montant: '.$donnees[0]['montant'];

/*
if ($qui_paye == 'organisateur') 
{ 
	$cout_inscription = $donnees[0]['cout'];
}
else
{
	$cout_inscription = $donnees[0]['cout'] + $frais_coureur;
}
*/
$cout_inscription = $donnees[0]['cout'];

if (!empty($donnees[0]['valeur_code_promo'])) { 
	
	$donnees[0]['cout'] = $donnees[0]['cout']-$donnees[0]['valeur_code_promo'];
	$code_promov2='<tr style="width:90%!important">
	<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
	Code promo utilisé
	</td>
	<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
	'.$donnees[0]['code_promo'].'
	</td>
	</tr>';
	//$cout_inscription = '<s>'.$cout_inscription.' €</s>';		
	//echo floatval($donnees[0]['valeur_code_promo']);
	//echo floatval($donnees[0]['montant']) - 10.3;
	/*
	if (($donnees[0]['montant']-$donnees[0]['valeur_code_promo']) == 0) {
		$cpt_gratuit = 1;
		$aff_gratuit = '<strong> GRATUIT !</strong>';
	}
	else{
		
		$aff_gratuit = '<strong> ( Réduction de '.$donnees[0]['valeur_code_promo'].' € )</strong>';
	}

	$code_promo = '<li><strong>Code promo utilisé :</strong> <em>'.$donnees[0]['code_promo'].'</em></li>';
	*/
}

if ($donnees[0]['place_promo'] ==1) {
	
	//$donnees[0]['cout'] = $donnees[0]['cout']-$donnees[0]['valeur_place_promo'];
	
	//$cout_inscription = '<s>'.$cout_inscription.' €</s>';		
	//echo floatval($donnees[0]['valeur_code_promo']);
	//echo floatval($donnees[0]['montant']) - 10.3;
	
	$aff_gratuit = '<strong> ( Réduction de '.$donnees[0]['valeur_place_promo'].' € )</strong>';


	$code_promo = '<li><strong>Promotion : </strong> <em>Réduction premières places !</em></li>';

	$aff_reduc_place_promo = '
	<tr style="width:90%!important">
	<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
	Réduction premières places 
	</td>
	<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
	'.$donnees[0]['valeur_place_promo'].'  €
	</td>
	</tr>';
	
}

//SI GROUPE
$groupe = '';
if ($donnees[0]['groupe'] != 'Aucun') {
	$groupe = '<li class="text-danger"><strong>Groupe :</strong> <em>'.$donnees[0]['groupe'].'</em></li>';
}
$info_gratuit = '';
if ($donnees[0]['mode_paiement'] == 'GRATUIT') {


	$cout_inscription = 0;
	$aff_gratuit = '<strong> '.$lng_trad['gratuit'].' !</strong>';
	$code_promo = '<li><strong>'.$lng_trad['code_promo'].' :</strong> <em>'.$donnees[0]['code_promo'].'</em></li>';

	if ($donnees[0]['mode_gratuit'] == 'OK') {

		$info_gratuit ='
		<table class="row">
		<tr>
		<!-- begin wrapper -->
		<td class="wrapper">
		<table class="twelve columns" align="center">
		<tr>
		<td class="" align="center">
		<table class="btn blue_ats" align="center">
		<tbody>
		<tr>
		<td >
		<span style="font-size:14px;color:#fff">Votre inscription vous a été offerte par l\'organisateur</span>
		</td>
		</tr>
		</tbody>
		</table>
		</td>
		</tr>
		</table>
		</td>
		<!-- end wrapper -->
		</tr>
		</table>';
	}
	
}

$info_autre = '';
if ($donnees[0]['mode_autre'] == 'OK') {
	
	$info_autre ='
	<table class="row">
	<tr>
	<!-- begin wrapper -->
	<td class="wrapper">
	<table class="twelve columns" align="center">
	<tr>
	<td class="" align="center">
	<table class="btn blue_ats" align="center">
	<tbody>
	<tr>
	<td >
	<span style="font-size:14px;color:#fff">Votre inscription a été enregistrée par l\'organisateur</span>
	</td>
	</tr>
	</tbody>
	</table>
	</td>
	</tr>
	</table>
	</td>
	<!-- end wrapper -->
	</tr>
	</table>';
}
/*** LOGS ****/
		//*****fputs($fp," : donnees[0]['mode_paiement'] : ".$donnees[0]['mode_paiement']."\n");
/*** LOGS ****/

/*** LOGS ****/
		//*****fputs($fp," : donnees[0]['cout'] : ".$donnees[0]['cout']."\n");
/*** LOGS ****/

/*** LOGS ****/
		//*****fputs($fp," : donnees[0]['frais_cb'] : ".$donnees[0]['frais_cb']."\n");
/*** LOGS ****/

/*** LOGS ****/
		//*****fputs($fp," : donnees[0]['frais_cheque'] : ".$donnees[0]['frais_cheque']."\n");
/*** LOGS ****/

/*** LOGS ****/
		//*****fputs($fp," : donnees[0]['valeur_code_promo'] : ".$donnees[0]['valeur_code_promo']."\n");
/*** LOGS ****/	

/*** LOGS ****/
		//*****fputs($fp," : donnees[0]['code_promo'] : ".$donnees[0]['code_promo']."\n");
/*** LOGS ****/	

/*** LOGS ****/
		//*****fputs($fp," : donnees[0]['montant'] : ".$donnees[0]['montant']."\n");
/*** LOGS ****/

/*** LOGS ****/
		//*****fputs($fp," : donnees[0]['participation'] : ".$donnees[0]['participation']."\n");
/*** LOGS ****/

/*** LOGS ****/
		//*****fputs($fp," : donnees[0]['referent'] : (ou id_internaute) ".$donnees[0]['referent']."\n");
/*** LOGS ****/

/*** LOGS ****/
		//*****fputs($fp," : donnees[0]['referents'] : ".$donnees[0]['referents']."\n");
/*** LOGS ****/
//cout participation
//$total_participation = ($donnees[0]['montant_inscription'] - $donnees[0]['montant'])." €";
if ($donnees[0]['mode_paiement']!='GRATUIT')
{
	if ($donnees[0]['participation'] <= 0) { $total_participation = '0 €'; } else { $total_participation = $donnees[0]['participation']." €" ; }
}
else
{
	
	$total_participation='0 €';
}

if($donnees[0]['montant_total_coureurs'] > 0)
{
	$montant_total_coureurs ='<tr style="width:90%!important">
	<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
	Montant des inscriptions supplémentaires 
	</td>
	<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
	'.$donnees[0]['montant_total_coureurs'].' € 
	</td>
	</tr>';	
	
}
$lien_compte_news='';

if (isset($donnees[0]['nomReferent_paiement'])) {
	
	
	$patronyme_referent_paiement = '
	<tr style="width:90%!important">
	<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
	Votre inscription a été payée par  
	</td>
	<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
	'.$donnees[0]['prenomReferent_paiement'].' '.$donnees[0]['nomReferent_paiement'].'
	</td>
	</tr>

	';
	
	

}

if (isset($donnees[0]['nomReferent'])) {
	
	
	$patronyme_referent = '
	<table align="left" style="width:90%!important; height:100%; border-collapse:collapse; border-style:solid; border-width:1px; border-color:#17ABE7">
	<thead style="width:90%!important; height:100%">
	<tr style="background-color:#17ABE7; color:#ffffff; width:90%!important">
	<th style="padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:5px">
	Information </th>
	<th style="padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:3px">
	Personne</th>
	</tr>
	</thead>
	<tbody style="width:90%!important; height:100%">
	<tr style="width:90%!important">
	<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
	Votre référent est    
	</td>
	<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
	'.$donnees[0]['prenomReferent'].' '.$donnees[0]['nomReferent'].'
	</td>
	</tr>
	'.$patronyme_referent_paiement.'
	</tbody>
	</table><p></p>
	';

}

elseif (isset($patronyme_referent_paiement) && empty($donnees[0]['patronymes_inscrits']))
{
	
	
	$patronyme_referent = '
	<table align="left" style="width:90%!important; height:100%; border-collapse:collapse; border-style:solid; border-width:1px; border-color:#17ABE7">
	<thead style="width:90%!important; height:100%">
	<tr style="background-color:#17ABE7; color:#ffffff; width:90%!important">
	<th style="padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:5px">
	Information </th>
	<th style="padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:3px">
	Personne</th>
	</tr>
	</thead>
	<tbody style="width:90%!important; height:100%">
	'.$patronyme_referent_paiement.'
	</tbody>
	</table><p></p>
	';	
	
	
	
}


if (empty($donnees[0]['clubInternaute'])) $donnees[0]['clubInternaute'] = '<i>Non renseigné</i>';

if (isset($donnees[0]['ref'])) {
	
	$lien_compte_news = '?a=new&ref='.$donnees[0]['ref'].'&id_ref='.$donnees[0]['idInternaute'];
}

if ($donnees[0]['groupe'] != 'Aucun')
{
	
	$groupe = '<li><strong>Groupe : </strong><em class="info-warning">'.$donnees[0]['groupe'].'</em> </li>';
	
	
}

if (isset($donnees[0]['patronymes_inscrits_all']))
{
	
	$patronymes_inscrits_all = '

	<tr style="width:90%!important">
	<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
	Vous avez payé les inscriptions de :
	</td>
	<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
	'.$donnees[0]['patronymes_inscrits_all'].'
	</td>
	</tr>

	';	
	
	
	
}

if (isset($donnees[0]['patronymes_inscrits']))
{
	
	$patronymes_inscrits = '
	<table align="left" style="width:90%!important; height:100%; border-collapse:collapse; border-style:solid; border-width:1px; border-color:#17ABE7">
	<thead style="width:90%!important; height:100%">
	<tr style="background-color:#17ABE7; color:#ffffff; width:90%!important">
	<th style="padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:5px">
	Information </th>
	<th style="padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:3px">
	Personne(s)</th>
	</tr>
	</thead>
	<tbody style="width:90%!important; height:100%">
	<tr style="width:90%!important">
	<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
	Vous êtes inscrit avec  
	</td>
	<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
	'.$donnees[0]['patronymes_inscrits'].'
	</td>
	</tr>
	'.$patronyme_referent_paiement.'
	'.$patronymes_inscrits_all.'
	</tbody>
	</table><p></p>
	';	
	
	
	
}
elseif (isset($patronymes_inscrits_all))
{
	
	$patronymes_inscrits = '
	<table align="left" style="width:90%!important; height:100%; border-collapse:collapse; border-style:solid; border-width:1px; border-color:#17ABE7">
	<thead style="width:90%!important; height:100%">
	<tr style="background-color:#17ABE7; color:#ffffff; width:90%!important">
	<th style="padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:5px">
	Information </th>
	<th style="padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:3px">
	Personne(s)</th>
	</tr>
	</thead>
	<tbody style="width:90%!important; height:100%">
	'.$patronymes_inscrits_all.'
	</tbody>
	</table><p></p>	';
	
	
	
	
}

$info_referent='';

if (!empty($donnees[0]['referent'])) {
	    //A FAIRE INNER JOIN AVEC TABLE RIEI
	$query  = "SELECT ri.prenomInternaute, ri.nomInternaute FROM r_internaute as ri ";
	$query .= "INNER JOIN r_inscriptionepreuveinternaute as riei ON ri.idInternaute = riei.idInternaute";
	$query .=" WHERE ri.idInternaute = ".$donnees[0]['referent'];
	$query .=" AND riei.idEpreuve= ".$donnees[0]['idEpreuve'];
		//echo $query;
		//$temp_query = $query;
	$result = $mysqli->query($query);
	$data=mysqli_fetch_array($result);

	$info_referent_cheque = '';
	if ($donnees[0]['mode_paiement'] == 'CHEQUE') {
		$info_referent_cheque='<p> <em class="info-danger">Merci de faire régler la somme totale des inscriptions par la personne ci-dessus.</em></p>';
	}

	$info_referent ='
	<table class="row">
	<tbody>
	<tr>
	<td class="wrapper">
	<table class="twelve columns">
	<tbody>
	<tr>
	<td class="last">
	<p> Votre inscription a été effectuée par <h5><strong>'.$data['prenomInternaute'].' '.$data['nomInternaute'].$temp_query .'</strong></h5></p>
	'.$info_referent_cheque.'
	</td>
	</tr>
	</tbody>
	</table>
	</td>
	</tr>
	</tbody>
	</table>';	

}


$info_referents=$tmp_aff='';
$prix_value = 0;
$prix_total_participants = 0;
$total_des_engagements = '';
//echo "referents_mail".$donnees[0]['referents'];
if (!empty($donnees[0]['referents']) && $donnees[0]['insc_orga'] !=1) {
	
	$query  = "SELECT rii.cout, rii.participation, rii.frais_cb, rii.frais_cheque, ri.prenomInternaute, ri.nomInternaute ";
	$query .= "FROM r_internaute as ri ";
	$query .= "INNER JOIN r_insc_internaute_temp as rii ON 	ri.idInternaute = rii.idInternaute ";
	$query .=" WHERE idInternauteReferent = ".$donnees[0]['referents'];
	$query .= " AND ri.idInternaute NOT IN (".$donnees[0]['referents'].")";
	$query .= " AND rii.idInscriptionEpreuveInternaute > ".$donnees[0]['idInscriptionEpreuveInternaute']." ";
	$query .= " AND rii.idEpreuve = ".$donnees[0]['idEpreuve'];
	$result = $mysqli->query($query);
	
	while (($data=mysqli_fetch_array($result)) != FALSE)
	{
		/*
		if ($qui_paye == 'organisateur') 
		{ 
			$data['frais_cb']=0; 
			$data['frais_cheque']=0; 
		}
		
		if ($donnees[0]['mode_paiement'] == 'CB') 
		{ 
			$frais_coureur =  $data['frais_cb'];
		}
		else
		{
			$frais_coureur =  $data['frais_cheque'];
		}	
		*/
		if ($data['participation'] != 0) {
			$prix_value = $data['cout']+$data['participation'];
			$prix = ($data['cout']).' €  + '.$data['participation'].' € de participation = '.($prix_value).' €';
			$prix_total_participants = $prix_total_participants + $prix_value;
		}
		else {  
			$prix = ($data['cout']).' € '; 
			$prix_value = $data['cout'];
			$prix_total_participants = $prix_total_participants + $prix_value;
		}
		
		$tmp_aff .= '<li> <em> <strong>'.$data['prenomInternaute'].' '.$data['nomInternaute'].'</strong></em> - Cout : <strong>'.$prix.'</strong></li>';
		
		
	}
//echo $data['cout'] ."#A#". $data['participation'] ."#B#". $donnees[0]['participation'] ."#C#". $donnees[0]['montant'] ."#D#". $donnees[0]['valeur_code_promo'];
	//$cout_participation_en_plus = $scout_inscription  + $prix_value;
	
	
	
	$info_referents_cheque = '';
	if ($donnees[0]['mode_paiement'] == 'CHEQUE') {
		$info_referents_cheque='<p> <em class="info-danger">La somme totale des inscriptions doit être réglé en un seul chèque</em></p>';
	}
	$info_referents ='
	<table class="row">
	<tbody>
	<tr>
	<td class="wrapper">
	<table class="twelve columns">
	<tbody>
	<tr>
	<td class="last">
	<p> Vous avez aussi inscrit : </p>
	<ul>
	'.$tmp_aff.'
	</ul>
	'.$info_referents_cheque.'
	</td>
	</tr>
	</tbody>
	</table>
	</td>
	</tr>
	</tbody>
	</table>';	

}

//OPTION PLUS
$aff_option_plus='';
if ($donnees[0]['nomOptionPlus'] !='')
{
	$aff_option_plus = '
	<tr style="width:90%!important">
	<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
	Option supplémentaire [ <i>'.$donnees[0]['nomOptionPlus'].'</i> ] 
	</td>
	<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
	'.$donnees[0]['Prix_OptionPlus'].'  €
	</td>
	</tr>';

	/*
	$aff_entete_option_plus = '<h5>Option supplémentaire : <strong><em style="color:#f59c1a">'.$donnees[0]['nom_option_plus'].'</em></strong></h5>';
	if ($donnees[0]['mode_paiement'] == 'GRATUIT') $donnees[0]['prix_option_plus'] = 0;
	$aff_resum_option_plus = '<li><strong>Montant option supp : </strong> <em><strong>'.$donnees[0]['prix_option_plus'].'</strong> € </em></li>';
	$aff_option_plus = '<tr style="width:90%!important">
							<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
								'.$donnees[0]['nom_option_plus'].' 
							</td>
							<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
							'.$donnees[0]['prix_option_plus'].' € 
							</td>
						</tr>';
						*/
					}	
//OPTION PLUS


					$aff_frais_coureur ='';

					if ($donnees[0]['mode_paiement']!='GRATUIT')
					{
						if ($qui_paye == 'organisateur') 
						{ 
		//OPTION PLUS
							$somme_totale_des_inscriptions = $donnees[0]['cout']+$donnees[0]['participation']+$donnees[0]['prix_option_plus'];
		//OPTION PLUS
						}
						else
						{


							if (empty($donnees[0]['referent']) && $cpt_gratuit==0) {	
								$aff_frais_coureur = '<li><strong>Montant des frais d\'inscriptions : </strong> <em><strong>'.$frais_coureur.'</strong> € </em></li>';
			//OPTION PLUS
								$somme_totale_des_inscriptions = $donnees[0]['cout'] + $frais_coureur + $donnees[0]['participation']+$donnees[0]['prix_option_plus'];
			//OPTION PLUS
							}
							else
							{
			//OPTION PLUS
								$somme_totale_des_inscriptions = $donnees[0]['cout'] + $donnees[0]['participation']+$donnees[0]['prix_option_plus'];
			//OPTION PLUS
							}

						}
						$somme_totale_des_inscriptions .= ' € ';

		//echo '<br>somme_totale_des_inscriptions: '.$somme_totale_des_inscriptions ;
						if ($prix_value > 0) {
							$total_des_engagements='<li><strong>Total des engagements : </strong> <em><strong>'.($somme_totale_des_inscriptions + $prix_total_participants).' € </strong>('.$somme_totale_des_inscriptions.' € + '.$prix_total_participants.' €)</em></li>';
						}
		//echo '<br>total_des_engagements: '.$total_des_engagements ;

					}
					else
					{
						$total_des_engagements='';

					}	
					$info_cheque = '';
					if ($donnees[0]['mode_paiement'] == 'CHEQUE' && $info_referent =='') {

						$query  = "SELECT coordonnees_paiement_cheque,dateFinInscription,nomStructureLegale ";
						$query .= "FROM r_epreuve";
						$query .=" WHERE idEpreuve = ".$donnees[0]['idEpreuve'];
						$result = $mysqli->query($query);
						$data_epreuve=mysqli_fetch_array($result);

						if ($donnees[0]['cheque_recu'] == 'OK') {

							$num_info_cheque = '';
							if (!empty($donnees[0]['info_cheque'])) $num_info_cheque = '<p><i> Information sur votre chèque : <strong>'.$donnees[0]['info_cheque'].' </strong></i></p>';
							$info_cheque ='
							<table class="row">
							<tr>
							<!-- begin wrapper -->
							<td class="wrapper">
							<table class="twelve columns" align="center">
							<tr>
							<td class="" align="center">
							<table class="btn blue_ats" align="center">
							<tbody>
							<tr>
							<td >
							<span style="font-size:14px;color:#fff">Votre inscription a été enregistrée par l\'organisateur</span>
							'.$num_info_cheque.'
							</td>
							</tr>
							</tbody>
							</table>
							</td>
							</tr>
							</table>
							</td>
							<!-- end wrapper -->
							</tr>
							</table>';

						}
						else
						{


							$info_cheque ='
							<table class="row">
							<tr>
							<!-- begin wrapper -->
							<td class="wrapper">
							<table class="twelve columns" align="center">
							<tr>
							<td class="" align="center">
							<table class="btn blue_ats" align="center">
							<tbody>
							<tr>
							<td >
							<p>Merci de renvoyer le chèque de <span class="text-danger">'.($somme_totale_des_inscriptions + $prix_total_participants).' € </span> à : </p>
							<p><strong>'.$data_epreuve['coordonnees_paiement_cheque'].'</strong></p>
							<p>avant le <strong>'.str_replace(" "," à ", dateen2fr($data_epreuve['dateFinInscription'],1)).'</strong></p>
							<p>A l\'ordre de : <strong>'.$data_epreuve['nomStructureLegale'].'</strong></p>
							<p>Merci d\'indiquer la référence suivante avec votre chèque : <strong>'.$donnees[0]['reference_cheque'].'</strong></p>
							</td>
							</tr>
							</tbody>
							</table>
							</td>
							</tr>
							</table>
							</td>
							<!-- end wrapper -->
							</tr>
							</table>';										

						}	
					}


/*$annee_naissance = substr($donnees[0]['naissanceInternaute'], 0, 4);
$categorie=array();
$categorie = calcul_categorie($annee_naissance,'code'); */
$cat_code = $donnees[0]['categorie'];

//equipe
if($donnees[0]['equipe'] != 'Aucune') $equipe = '<li><strong>Equipe :</strong>  <em>'.$donnees[0]['equipe'].'</em></li>';
if (!empty($donnees[0]['referents'])) $idEpInscInt = $donnees[0]['idInscriptionEpreuveInternaute']; else $idEpInscInt = $donnees[0]['idInscriptionEpreuveInternaute'];
$lien_edition = 'https://www.ats-sport.com/inscriptions.php?id_epreuve='.$donnees[0]['idEpreuve'].'&id_parcours='.$donnees[0]['idParcours'].'&id='.$donnees[0]['idSession'].'&date_inscription='.$donnees[0]['dateInscription'].'&action=update&id_int='.$idEpInscInt;
$lien_inscrits = 'https://www.ats-sport.com/liste_des_inscrits.php?id_epreuve='.$donnees[0]['idEpreuve'];
if ($donnees[0]['mode_paiement']=='CB' && empty($donnees[0]['referent'])) 
{	
	$lien_recu='                           
	<tr>
	<td class="">
	<table class="btn info" align="center">
	<tbody>
	<tr>
	<td>
	<a href="https://www.ats-sport.com/recu.php?id_epreuve='.$donnees[0]['idEpreuve'].'&id='.$donnees[0]['idInscriptionEpreuveInternaute'].'&ids='.$donnees[0]['idSession'].'"><b>Editer un reçu de l\'inscription</b></a>
	</td>
	</tr>
	</tbody>
	</table>
	</td>
	</tr>';
}
$lien_recu_7162='';
if ($donnees[0]['idEpreuve']==7162)
{

	$lien_recu_7162='                           
	<tr>
	<td class="">
	<table class="btn info" align="center">
	<tbody>
	<tr>
	<td>
	<a href="https://www.ats-sport.com/recu_dossard.php?&id='.$donnees[0]['idInscriptionEpreuveInternaute'].'&ids='.$donnees[0]['idSession'].'" style="vertical-align:middle; white-space:nowrap; text-align:center; margin-right:5px!important; color:#fff; background:#c83349; border-color:#000; border-radius:2px; padding:5px 5px; font-size:18px; line-height:1.3333333; display:inline-block; margin-bottom:0; font-weight:400"><b>Générer mon dossard</b></a>
	</td>
	</tr>
	</tbody>
	</table>
	</td>
	</tr>';	
	
	
	$lien_recu_7162=' <tr>
	<td align="center">
	<p><a href="https://www.ats-sport.com/recu_dossard.php?&id='.$donnees[0]['idInscriptionEpreuveInternaute'].'&ids='.$donnees[0]['idSession'].'" style="vertical-align:middle; white-space:nowrap; text-align:center; margin-right:5px!important; color:#fff; background:#c83349; border-color:#000; border-radius:2px; padding:5px 5px; font-size:18px; line-height:1.3333333; display:inline-block; margin-bottom:0; font-weight:400"><b>Cliquer pour générer mon dossard</b></a></p>
	</td>
	<tr>';	
	
	
	
}
//https://www.ats-sport.com/recu.php?id_epreuve=4762&id=418578&ids=562020e5b204dd0f663c2bd6e34e9cdb

if ($donnees[0]['certificatMedical'] == 'aucun')
{
	$color_certif='#ff5b57';
	$certif = $lng_trad['non_fourni'];
}
else if ($donnees[0]['certificatMedical'] == 'oui' || $donnees[0]['certificatMedical'] == 'non')
{
	$color_certif='#f59c1a';
	$certif = $lng_trad['fourni'];
}
else if ($donnees[0]['certificatMedical'] == 'ffa')
{
	$color_certif='#f59c1a';
	$certif = $lng_trad['verifie_ffa'];
}
else
{
	$color_certif='#f59c1a';
	$certif = $lng_trad['pas_de_besoin'];
}

if ($donnees[0]['autoParentale'] == 'non')
{
	$color_autop='#ff5b57';
	$autop = $lng_trad['non_fourni'];
}
else if ($donnees[0]['autoParentale'] == 'oui')
{
	$color_autop='#f59c1a';
	$autop = $lng_trad['fourni'];
}
else
{
	$color_autop='#f59c1a';
	$autop = $lng_trad['pas_de_besoin'];
}

$champ_aff= '';
$champ_dynamique = recup_champ_dotation_inscrit($donnees[0]['idEpreuve'], $donnees[0]['idParcours'], $donnees[0]['idInternaute'], $donnees[0]['idInscriptionEpreuveInternaute'],$lng_inscrit);
$champ_dynamique_epreuve = recup_champ_dotation_inscrit_epreuve($donnees[0]['idEpreuve'], $donnees[0]['idInternaute'],$donnees[0]['idInscriptionEpreuveInternaute'],$lng_inscrit);

if (!empty($champ_dynamique) || !empty($champ_dynamique_epreuve)) {
	
	$champ_participation = $champ_dotation = $champ_questiondiverse = '';
	$champ_participation_epreuve = $champ_dotation_epreuve = $champ_questiondiverse_epreuve = '';

	foreach ($champ_dynamique as $j=>$value_champ) { 

		//if ($cpt==1) $class="info"; else $class="";
		
		
		if ($value_champ['champ']=='participation') {
			
			$champ_participation .='<tr style="width:90%!important">
			<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
			'.$value_champ['label'].'
			</td>
			<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
			<em>'.$value_champ['value'].'</em>
			</td>
			<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
			<em>'.$value_champ['prix_total'].' € </em>
			</td>
			</tr>';		
			
			
		}
		
		if ($value_champ['champ']=='dotation') {
			
			$champ_dotation .='<tr style="width:90%!important">
			<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
			'.$value_champ['label'].'
			</td>
			<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
			<em>'.$value_champ['value'].'</em>
			</td>
			</tr>';		
			
			
		}

		if ($value_champ['champ']=='questiondiverse') {
			
			if ($value_champ['type_champ']=='TEXTAREA') $value_champ['value'] = (tronque_texte($value_champ['value'],10));
			$champ_questiondiverse .='<tr style="width:90%!important">
			<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
			'.$value_champ['label'].'
			</td>
			<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
			<em>'.$value_champ['value'].'</em>
			</td>
			</tr>';		
			
			
		}			
		//if ($value_champ['champ']=='participation') $value_champ['value'] = $value_champ['value']." ".$lng_trad['unite']." [ <b>".$value_champ['prix_total']." €</b> ]";
		//echo '<tr class=".$class.">';
        //$champ .='<li>'.$value_champ['label'].' : <em>'.$value_champ['value'].'</em></li>';
        /*
		$champ .='<tr style="width:90%!important">
							<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
								'.$value_champ['label'].'
							</td>
							<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
								<em>'.$value_champ['value'].'</em>
							</td>
							<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
								<em>'.$value_champ['prix_total'].' € </em>
							</td>
						</tr>';
		*/
					}

					foreach ($champ_dynamique_epreuve as $j=>$value_champ_epreuve) { 

		//if ($cpt==1) $class="info"; else $class="";
		//if ($value_champ_epreuve['type_champ']=='TEXTAREA') $value_champ['value'] = (tronque_texte($value_champ['value'],10));
		//if ($value_champ_epreuve['champ']=='participation') $value_champ_epreuve['value'] = $value_champ_epreuve['value']." ".$lng_trad['unite']." [ <b>".$value_champ_epreuve['prix_total']." €</b> ]";
						if ($value_champ_epreuve['champ']=='participation') {

							$champ_participation_epreuve .='<tr style="width:90%!important">
							<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
							'.$value_champ_epreuve['label'].'
							</td>
							<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
							<em>'.$value_champ_epreuve['value'].'</em>
							</td>
							<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
							<em>'.$value_champ_epreuve['prix_total'].' € </em>
							</td>
							</tr>';		


						}

						if ($value_champ_epreuve['champ']=='dotation') {

							$champ_dotation_epreuve .='<tr style="width:90%!important">
							<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
							'.$value_champ_epreuve['label'].'
							</td>
							<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
							<em>'.$value_champ_epreuve['value'].'</em>
							</td>
							</tr>';		


						}

						if ($value_champ_epreuve['champ']=='questiondiverse') {

							if ($value_champ_epreuve['type_champ']=='TEXTAREA') $value_champ_epreuve['value'] = (tronque_texte($value_champ_epreuve['value'],10));

							$champ_questiondiverse_epreuve .='<tr style="width:90%!important">
							<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
							'.$value_champ_epreuve['label'].'
							</td>
							<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
							<em>'.$value_champ_epreuve['value'].'</em>
							</td>
							</tr>';		


						}		



		//echo '<tr class=".$class.">';
        /*
		$champ_epreuve .='<tr style="width:90%!important">
							<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
								'.$value_champ_epreuve['label'].'
							</td>
							<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
								<em>'.$value_champ_epreuve['value'].'</em>
							</td>
							<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
								<em>'.$value_champ_epreuve['prix_total'].' € </em>
							</td>
						</tr>';
		*/

					}

					if (!empty($champ_participation) || !empty($champ_participation_epreuve)) 
					{
						$champ_aff_participation = '
						<table align="left" style="margin-top:20px;width:90%!important; height:100%; border-collapse:collapse; border-style:solid; border-width:1px; border-color:#17ABE7">
						<thead style="width:90%!important; height:100%">
						<tr style="background-color:#17ABE7; color:#ffffff; width:90%!important">
						<th style="padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:5px">
						Synthèse des participations tarifées sélectionnées</th>
						<th style="padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:3px">
						Nombre</th>
						<th style="padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:3px">
						Prix</th>
						</tr>
						</thead>
						<tbody style="width:90%!important; height:100%">
						'.$champ_participation.'
						'.$champ_participation_epreuve.'
						</tbody>
						</table>';



					}
					if (!empty($champ_dotation) || !empty($champ_questiondiverse) || !empty($champ_dotation_epreuve) || !empty($champ_questiondiverse_epreuve)) 
					{
						$champ_aff_dotation = '
						<table align="left" style="margin-top:20px;width:90%!important; height:100%; border-collapse:collapse; border-style:solid; border-width:1px; border-color:#17ABE7">
						<thead style="width:90%!important; height:100%">
						<tr style="background-color:#17ABE7; color:#ffffff; width:90%!important">
						<th style="padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:5px">
						Synthèse des options supplémentaires</th>
						<th style="padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:3px">
						Nombre/valeur</th>
						</tr>
						</thead>
						<tbody style="width:90%!important; height:100%">
						'.$champ_dotation.'
						'.$champ_questiondiverse.'
						'.$champ_dotation_epreuve.'
						'.$champ_questiondiverse_epreuve.'
						</tbody>
						</table>';



					}

					$champ_aff = $champ_aff_participation.$champ_aff_dotation;



	/*														
	$champ_aff = '               
		<table class="row">
			<tr>
				<td class="wrapper">
					<table class="ten columns">
						<tr>
							<td>
								<h6>'.$lng_trad['question_subs'].'</h6>
								<p>
									<ul class="info">'.$champ.'</ul> 
									<ul class="info">'.$champ_epreuve.'</ul> 
								</p>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>';
		*/
	}


	$entete = ' 
	<table class="row">
	<tr>
	<!-- begin wrapper -->
	<td class="wrapper">
	<table class="twelve columns">
	<tr>
	<td class="last">
	<h4>'.$lng_trad['inscription_epreuve'].' <em style="color:#f59c1a">'.$donnees[0]['nomEpreuve'].' - '.$donnees[0]['nomParcours'].' </em>[ '.dateen2fr($donnees[0]['horaireDepart'],1).' ]</h4>
	'.$aff_entete_option_plus.'
	<h5 >'.$lng_trad['resume_information'].'</h5>
	</td>
	</tr>
	</table>

	</td>
	<!-- end wrapper -->
	</tr>
	</table>
	';

	$info_agent = '';
	if ($donnees[0]['type_mail'] == 'organisateur') {

		$num_dossard = '';
		$info_agent = ' - '.$donnees[0]['info_diverses'];
	/*****
	if ($donnees[0]['mode_paiement'] !='CHEQUE')
	{
		
		$num_dossard = '<h5> - Dossard N° : <em>'.$donnees[0]['numerotation'].'</h5></em>';
		
	}
	*****/
	$entete = '
	<table class="row">
	<tbody>
	<tr>
	<td class="wrapper">
	<table class="twelve columns">
	<tbody>
	<tr>
	<td class="last">
	<h5>Une personne vient de s\'inscire à l\'épreuve :<em style="color:#f59c1a">'.$donnees[0]['nomEpreuve'].' - '.$donnees[0]['nomParcours'].'</em></h5>
	<h5>Résumé des informations ci-dessous</h5>'.$num_dossard.' 
	</td>
	</tr>
	</tbody>
	</table>
	</td>
	</tr>
	</tbody>
	</table>';
}

if ($donnees[0]['resendmail'] == 1 ) {
	
	if ($donnees[0]['etat_certif'] =='warning') {

		$html_certif = '<li><strong>Certificat médical / license :</strong> <em>Fourni mais non vérifié par l\'organisateur</em></li>';
		
	}
	elseif ($donnees[0]['etat_certif'] =='danger')
	{
		$html_certif = '<li><strong>Certificat médical / license :</strong> <em class="info-danger">Manquant</em></li>';

	}
	else
	{
		$html_certif = '<li><strong>Certificat médical / license :</strong> <em class="text-success">Validé</em></li>';
	}
	
	if ($donnees[0]['paiement'] ==0) {
		
		$html_paiement = '<li><strong>Paiement de l\inscription :</strong> <em class="text-success">Payé</em></li>';
	}
	else
	{
		$html_paiement = '<li><strong>Paiement de l\inscription :</strong> <em class="text-danger">A payer</em></li>';
	}
	
	$pieces_manquante = '
	<table class="six columns">
	<tr>
	<td>
	<p> 
	<ul class="info">
	'.$html_certif.'
	'.$html_paiement.'
	</ul>  
	</td>
	</tr>
	</table>
	';
	$sujet_email ='Mise à jour de votre inscription';
	$codeSecurite = $donnees[0]['passInternaute'];
}
else if ($donnees[0]['insc'] == 1 )
{
	
	$sujet_email ='Inscription sur le site d\'ATS-SPORT';
}
else if ($donnees[0]['fusion'] == 1 )
{
	
	$sujet_email ='Fusion de résultat';
}
else if ($req_mdp==1) {
	
	$sujet_email ='Mot de passe perdu';
}
else
{
	$sujet_email ='Confirmation d\'inscription';
	$codeSecurite = $donnees[0]['codeSecurite'];
	
}
if ($donnees[0]['paiement_CB'] == 'OK' ) {
	$sujet_lien_edition = 'EFFECTUER LE PAIEMENT EN LIGNE DE MON INSCRIPTION';
	//$codeSecurite = $donnees[0]['passInternaute'];
	$lien_edition_resend = 'http://www.ats-sport.com/liste_des_inscrits.php?id_epreuve='.$donnees[0]['idEpreuve'].'&id_parcours='.$donnees[0]['idParcours'].'&id='.$donnees[0]['idSession'].'&idInternaute='.$donnees[0]['idInternaute'].'&action=maj_paiement&id_int='.$donnees[0]['referents'];

	$info_CB ='
	<table class="row">
	<tr>
	<!-- begin wrapper -->
	<td class="wrapper">
	<table class="twelve columns" align="center">
	<tr>
	<td class="" align="center">
	<table class="btn blue_ats" align="center">
	<tbody>
	<tr>
	<td align="center">
	<span style="font-size:14px;color:#fff"><strong>Votre inscription a été pré-enregistrée par l\'organisateur</strong></span>
	<p></i>Merci de finaliser votre inscription en effectuant le paiement en Carte Bleue avec le lien ci-dessous</i></p>
	</td>
	</tr>
	</tbody>
	</table>
	</td>
	</tr>
	<tr>
	<td class="" align="center">
	<table class="btn red" align="center">
	<tbody>
	<tr>
	<td>
	<a href="'.$lien_edition_resend.'">'.$sujet_lien_edition.'</a>
	</td>
	</tr>
	</tbody>
	</table>
	</td>
	</tr>
	</td>
	</tr>
	</table>
	</td>
	<!-- end wrapper -->
	</tr>
	</table>';
	//$sujet_email ='Mise à jour de votre inscription';

}
$new_email ='
<!DOCTYPE html>
<!--[if IE 8]> <html lang="fr" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="fr">
<!--<![endif]-->
<head>

<meta charset="utf-8" />
<title>ATS-SPORT | '.$donnees[0]['nomEpreuve'].' - '.$donnees[0]['nomParcours'].'</title>
<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
<meta content="ATS-SPORT - '.$donnees[0]['nomEpreuve'].' - '.$donnees[0]['nomParcours'].'" name="description" />
<meta content="ATS-SPORT" name="author" />
<meta http-equiv="content-type" content="text/html;">

</head>

<body style="color:#000000; background-color: #FFFFFF;">
<div>
<table cellspacing="0" cellpadding="0" border="0" style="color:#333; background:#fff; padding:0; margin:0; width:100%; font:15px \'Helvetica Neue\',Arial,Helvetica">
<tbody>
<tr width="100%">
<td style="background:#f0f0f0; font:15px \'Helvetica Neue\',Arial,Helvetica">
<table style="border:none; padding:0 18px; margin:50px auto; width:500px">
<tbody>
<tr width="100%" height="57">
<td valign="top" align="left" style="border-top-left-radius:4px; border-top-right-radius:4px; background:#17ABE7; padding:12px 18px; text-align:center">
<img src="https://www.ats-sport.com/images/logo/ats-sport/Specialiste-Chronometrage-sportif.jpg" title="ATS-SPORT" width="75"> </td>
</tr>
<tr width="100%">
<td valign="top" align="left" style="background:#595959; padding:12px 18px; text-align:center;color:white">
'.$sujet_email.'
</td>
</tr>
<tr width="100%">
<td style="border-bottom-left-radius:4px; border-bottom-right-radius:4px; background:#fff; padding:18px">
<table style="width:100%; margin:0; padding:0">
<tbody>
'.$info_complementaires_parcours.'
<tr style="margin:0; padding:0">
<td style="margin:0; padding:0; padding-left:20px">
<p>Bonjour '.$donnees[0]['prenomInternaute'].' '.$donnees[0]['nomInternaute'].',</p>
<p>Votre inscription sous la référence <em>'.$donnees[0]['idInscriptionEpreuveInternaute'].'</em> à bien été pris en compte.</p>
<p>Résumé de vos informations :</p>
<ul>
<li><strong>'.$lng_trad['prenom'].' '.$lng_trad['nom'].' : </strong><em>'.$donnees[0]['prenomInternaute'].'</em> <em>'.$donnees[0]['nomInternaute'].'</em> </li>
<li><strong>'.$lng_trad['date_de_naissance'].' : </strong><em>'.dateen2fr($donnees[0]['naissanceInternaute'],1).'</em> </li>
<li><strong>'.$lng_trad['adresse'].' : </strong><em>'.$donnees[0]['adresseInternaute'].'</em> <em>'.$donnees[0]['cpInternaute'].'</em> <em>'.$donnees[0]['villeInternaute'].'</em> - <em>'.$donnees[0]['paysInternaute'].'</em> </li>
<li><strong>'.$lng_trad['email'].' : </strong><em>'.$donnees[0]['emailInternaute'].'</em> </li>
<li><strong>'.$lng_trad['club'].' : </strong><em>'.$donnees[0]['clubInternaute'].'</em></li>
<li><strong>'.$lng_trad['categorie'].' : </strong><em>'.$cat_code.'</em> </li>
<li><strong>'.$lng_trad['licence'].' : </strong><em class="info-danger" style="color:'.$color_certif.'">'.$certif.'</em> </li>
'.$groupe.'

</ul>
<p></p>
<p>les détails de l\'inscription :</p>
<p></p>
'.$patronyme_referent.'
'.$patronymes_inscrits.'

<table align="left" style="width:90%!important; height:100%; border-collapse:collapse; border-style:solid; border-width:1px; border-color:#17ABE7">
<thead style="width:90%!important; height:100%">
<tr style="background-color:#17ABE7; color:#ffffff; width:90%!important">
<th style="padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:5px">
Nom de l\'épreuve : '.$donnees[0]['nomEpreuve'].'</th>
<th style="padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:3px">
Prix</th>
</tr>
</thead>
<tbody style="width:90%!important; height:100%">
<tr style="width:90%!important">
<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
Inscription - '.$donnees[0]['nomParcours'].' - 
</td>
<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
'.$donnees[0]['tarif'].' € 
</td>
</tr>
<tr style="width:90%!important">
<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
'.$lng_trad['montant_participation'].' 
</td>
<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
'.$total_participation.' 
</td>
</tr>
'.$aff_option_plus.'
'.$aff_reduc_place_promo.'
'.$montant_total_coureurs.'
<tr style="width:90%!important">
<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
Frais Inscription en ligne 
</td>
<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px">
'.$donnees[0]['frais_cb'].' € 
</td>
</tr>

'.$code_promov2.'
<tr style="background-color:#17ABE7; width:100%!important; color:#fff">
<td style="border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px; font-weight:bold">
Total engagé
</td>
<td style="text-align:right; border-style:solid; border-width:1px; border-color:#ADADAD; padding-top:3px; padding-bottom:3px; padding-left:3px; padding-right:5px; font-weight:bold">
'.$donnees[0]['paiement_montant'].' €
</td>
</tr>
</tbody>
</table>
'.$champ_aff.'
<p></p>

</td>
</tr>
<tr>
<td align="center">
<p><a href="https://www.ats-sport.com/moncompte.php'.$lien_compte_news.'" style="touch-action: manipulation;cursor: pointer;vertical-align: middle;white-space: nowrap; text-align: center;margin-right: 5px!important; color: #fff; background: #2196F3; border-color: #2196F3; border-radius: 2px;transition: all .2s ease-in-out;    padding: 10px 16px;font-size: 18px;    line-height: 1.3333333;display: inline-block;margin-bottom: 0;    font-weight: 400;" class="btn btn-primary btn-lg m-r-5">Editer mon compte</a></p>
</td>
</tr>
'.$lien_recu_7162.'
<tr>
<td align="center">
<p><a href="https://www.ats-sport.com/recu.php?id_epreuve='.$donnees[0]['idEpreuve'].'&id='.$donnees[0]['idInscriptionEpreuveInternaute'].'&ids='.$donnees[0]['idSession'].'"><b>Editer un reçu de l\'inscription</b></a></p>
</td>
<tr>

<td>
<p style="font:15px/1.25em \'Helvetica Neue\',Arial,Helvetica; color:#333">Sportivement,<br>
l\'équipe ATS-SPORT</p>
</td>
</tr>
</tbody>
</table>
<a href="'.$tab_banniere['url_lien'].'" target="_blank" rel="noopener noreferrer"><img src="https://www.ats-sport.com/images/bannieres/'.$tab_banniere['url_image'].'" alt="'.$tab_banniere['information'].'" style="max-width:800px"></a> 
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</div>
</body>
</html>';


$email_inscription ='
<!DOCTYPE html>
<!--[if IE 8]> <html lang="fr" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="fr">
<!--<![endif]-->
<head>

<meta charset="utf-8" />
<title>ATS-SPORT | La référence des inscriptions en ligne</title>
<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
<meta content="spécialiste du dossard, impression de dossard, dossard personnalisé, chronométrage, chronométreur, inscriptions en ligne, dossards, course à pied, trail, cyclisme, cyclosportive, vtt, triathlon, duathlon" name="description" />
<meta content="ATS-SPORT" name="author" />
<meta http-equiv="content-type" content="text/html;">

</head>

<body style="color:#000000; background-color: #FFFFFF;">
<div>
<table cellspacing="0" cellpadding="0" border="0" style="color:#333; background:#fff; padding:0; margin:0; width:100%; font:15px \'Helvetica Neue\',Arial,Helvetica">
<tbody>
<tr width="100%">
<td style="background:#f0f0f0; font:15px \'Helvetica Neue\',Arial,Helvetica">
<table style="border:none; padding:0 18px; margin:50px auto; width:500px">
<tbody>
<tr width="100%" height="57">
<td valign="top" align="left" style="border-top-left-radius:4px; border-top-right-radius:4px; background:#17ABE7; padding:12px 18px; text-align:center">
<img src="https://www.ats-sport.com/images/logo/ats-sport/Specialiste-Chronometrage-sportif.jpg" title="ATS-SPORT" width="75"> </td>
</tr>
<tr width="100%">
<td valign="top" align="left" style="background:#595959; padding:12px 18px; text-align:center;color:white">
'.$sujet_email.'
</td>
</tr>
<tr width="100%">
<td style="border-bottom-left-radius:4px; border-bottom-right-radius:4px; background:#fff; padding:18px">
<table style="width:100%; margin:0; padding:0">
<tbody>
<tr style="margin:0; padding:0">
<td style="margin:0; padding:0; padding-left:20px">
<p>Bonjour '.$donnees[0]['prenomInternaute'].' '.$donnees[0]['nomInternaute'].',</p>
<p>Bienvenue sur ATS-SPORT et merci de nous avoir rejoint. Vous pouvez maintenant vous connecter avec le mot de passe que vous avez choisi lors de votre inscription. </p>
<p>Résumé de vos informations :</p>
<ul>
<li><strong> Compte de connexion : </strong><em>'.$donnees[0]['login'].'</em> </li>
<li><strong> Mot de passe : </strong><em>'.$donnees[0]['mdp_temp'].'</em> </li>
<li><strong>'.$lng_trad['prenom'].' '.$lng_trad['nom'].' : </strong><em>'.$donnees[0]['prenomInternaute'].'</em> <em>'.$donnees[0]['nomInternaute'].'</em> </li>
<li><strong>'.$lng_trad['date_de_naissance'].' : </strong><em>'.dateen2fr($donnees[0]['naissanceInternaute'],1).'</em> </li>
<li><strong>'.$lng_trad['email'].' : </strong><em>'.$donnees[0]['emailInternaute'].'</em> </li>
</ul>
<p></p>
<p><a href="https://www.ats-sport.com/moncompte.php" style="                touch-action: manipulation;cursor: pointer;vertical-align: middle;white-space: nowrap; text-align: center;margin-right: 5px!important; color: #fff; background: #2196F3; border-color: #2196F3; border-radius: 2px;transition: all .2s ease-in-out;    padding: 10px 16px;font-size: 18px;    line-height: 1.3333333;display: inline-block;margin-bottom: 0;    font-weight: 400;" class="btn btn-primary btn-lg m-r-5">Me connecter</a></p>
<p></p>
</td>
</tr>
<tr>
<td>
<p style="font:15px/1.25em \'Helvetica Neue\',Arial,Helvetica; color:#333">Sportivement,<br>
l\'équipe ATS-SPORT</p>
</td>
</tr>
</tbody>
</table>
<a href="https://shop.ats-sport.com/fr/" target="_blank" rel="noopener noreferrer"><img src="https://www.ats-sport.com/images/banniere/specialiste-dossard-sportif.jpg" alt="Boutique de l\'organisateur ATS-SPORT"></a> 
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</div>
</body>
</html>';

$email_fusion ='
<!DOCTYPE html>
<!--[if IE 8]> <html lang="fr" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="fr">
<!--<![endif]-->
<head>

<meta charset="utf-8" />
<title>ATS-SPORT | La référence des inscriptions en ligne</title>
<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
<meta content="spécialiste du dossard, impression de dossard, dossard personnalisé, chronométrage, chronométreur, inscriptions en ligne, dossards, course à pied, trail, cyclisme, cyclosportive, vtt, triathlon, duathlon" name="description" />
<meta content="ATS-SPORT" name="author" />
<meta http-equiv="content-type" content="text/html;">

</head>

<body style="color:#000000; background-color: #FFFFFF;">
<div>
<table cellspacing="0" cellpadding="0" border="0" style="color:#333; background:#fff; padding:0; margin:0; width:100%; font:15px \'Helvetica Neue\',Arial,Helvetica">
<tbody>
<tr width="100%">
<td style="background:#f0f0f0; font:15px \'Helvetica Neue\',Arial,Helvetica">
<table style="border:none; padding:0 18px; margin:50px auto; width:500px">
<tbody>
<tr width="100%" height="57">
<td valign="top" align="left" style="border-top-left-radius:4px; border-top-right-radius:4px; background:#17ABE7; padding:12px 18px; text-align:center">
<img src="https://www.ats-sport.com/images/logo/ats-sport/Specialiste-Chronometrage-sportif.jpg" title="ATS-SPORT" width="75"> </td>
</tr>
<tr width="100%">
<td valign="top" align="left" style="background:#595959; padding:12px 18px; text-align:center;color:white">
'.$sujet_email.'
</td>
</tr>
<tr width="100%">
<td style="border-bottom-left-radius:4px; border-bottom-right-radius:4px; background:#fff; padding:18px">
<table style="width:100%; margin:0; padding:0">
<tbody>
<tr style="margin:0; padding:0">
<td style="margin:0; padding:0; padding-left:20px">
<p>Bonjour '.$donnees[0]['prenomInternaute'].' '.$donnees[0]['nomInternaute'].',</p>
<p>Une demande de fusion de résultat à été demandée le '.date('d-m-Y').' à '.date('H:m').'</br> Si cette demande ne vient pas de vous, veuillez ignorer et contacter ATS SPORT à webmaster@ats-sport.com</p>
<p></p>
<p><a href="https://www.ats-sport.com/mesresultats.php?idf='.$donnees[0]['id_fusion'].'" style=" touch-action: manipulation;cursor: pointer;vertical-align: middle;white-space: nowrap; text-align: center;margin-right: 5px!important; color: #fff; background: #2196F3; border-color: #2196F3; border-radius: 2px;transition: all .2s ease-in-out;    padding: 10px 16px;font-size: 18px;    line-height: 1.3333333;display: inline-block;margin-bottom: 0;    font-weight: 400;" class="btn btn-primary btn-lg m-r-5">Fusionner mes résultats sur un seul compte ATS SPORT</a></p>
<p></p>
</td>
</tr>
<tr>
<td>
<p style="font:15px/1.25em \'Helvetica Neue\',Arial,Helvetica; color:#333">Sportivement,<br>
l\'équipe ATS-SPORT</p>
</td>
</tr>
</tbody>
</table>
<a href="https://shop.ats-sport.com/fr/" target="_blank" rel="noopener noreferrer"><img src="https://www.ats-sport.com/images/banniere/specialiste-dossard-sportif.jpg" alt="Boutique de l\'organisateur ATS-SPORT"></a> 
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</div>
</body>
</html>';


$email_req_mdp ='
<!DOCTYPE html>
<!--[if IE 8]> <html lang="fr" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="fr">
<!--<![endif]-->
<head>

<meta charset="utf-8" />
<title>ATS-SPORT | La référence des inscriptions en ligne</title>
<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
<meta content="spécialiste du dossard, impression de dossard, dossard personnalisé, chronométrage, chronométreur, inscriptions en ligne, dossards, course à pied, trail, cyclisme, cyclosportive, vtt, triathlon, duathlon" name="description" />
<meta content="ATS-SPORT" name="author" />
<meta http-equiv="content-type" content="text/html;">

</head>

<body style="color:#000000; background-color: #FFFFFF;">
<div>
<table cellspacing="0" cellpadding="0" border="0" style="color:#333; background:#fff; padding:0; margin:0; width:100%; font:15px \'Helvetica Neue\',Arial,Helvetica">
<tbody>
<tr width="100%">
<td style="background:#f0f0f0; font:15px \'Helvetica Neue\',Arial,Helvetica">
<table style="border:none; padding:0 18px; margin:50px auto; width:500px">
<tbody>
<tr width="100%" height="57">
<td valign="top" align="left" style="border-top-left-radius:4px; border-top-right-radius:4px; background:#17ABE7; padding:12px 18px; text-align:center">
<img src="https://www.ats-sport.com/images/logo/ats-sport/Specialiste-Chronometrage-sportif.jpg" title="ATS-SPORT" width="75"> </td>
</tr>
<tr width="100%">
<td valign="top" align="left" style="background:#595959; padding:12px 18px; text-align:center;color:white">
'.$sujet_email.'
</td>
</tr>
<tr width="100%">
<td style="border-bottom-left-radius:4px; border-bottom-right-radius:4px; background:#fff; padding:18px">
<table style="width:100%; margin:0; padding:0">
<tbody>
<tr style="margin:0; padding:0">
<td style="margin:0; padding:0; padding-left:20px">
<p>Bonjour '.$donnees[0]['prenomInternaute'].' '.$donnees[0]['nomInternaute'].',</p>
<p>Une demande de changement de mot de passe à été demandée le '.date('d-m-Y').' à '.date('H:m').'</br> Si cette demande ne vient pas de vous, veuillez ignorer et contacter ATS SPORT à webmaster@ats-sport.com</p>
<p></p>
<p><a href="'.$lien_request_mdp.'" style=" touch-action: manipulation;cursor: pointer;vertical-align: middle;white-space: nowrap; text-align: center;margin-right: 5px!important; color: #fff; background: #2196F3; border-color: #2196F3; border-radius: 2px;transition: all .2s ease-in-out;    padding: 10px 16px;font-size: 18px;    line-height: 1.3333333;display: inline-block;margin-bottom: 0;    font-weight: 400;" class="btn btn-primary btn-lg m-r-5">Cliquer ici pour changer votre mot de passe</a></p>
<p></p>
</td>
</tr>
<tr>
<td>
<p style="font:15px/1.25em \'Helvetica Neue\',Arial,Helvetica; color:#333">Sportivement,<br>
l\'équipe ATS-SPORT</p>
</td>
</tr>
</tbody>
</table>
<a href="https://shop.ats-sport.com/fr/" target="_blank" rel="noopener noreferrer"><img src="https://www.ats-sport.com/images/banniere/specialiste-dossard-sportif.jpg" alt="Boutique de l\'organisateur ATS-SPORT"></a> 
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</div>
</body>
</html>';

$css= '	
<!DOCTYPE html>
<!--[if IE 8]> <html lang="fr" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="fr">
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<title>ATS-SPORT | '.$donnees[0]['nomEpreuve'].' - '.$donnees[0]['nomParcours'].'</title>
<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
<meta content="ATS-SPORT - '.$donnees[0]['nomEpreuve'].' - '.$donnees[0]['nomParcours'].' name="description" />
<meta content="ATS-SPORT" name="author" />

';

$entete_email = '<body>
<!-- begin page body -->
<table class="body">
<tr>
<td class="center" align="center" valign="top">
<center>
<!-- begin page header -->
<table class="row header">
<tr>
<td class="center" align="center">
<center>
<!-- begin container -->
<table class="container">
<tr>
<td class="wrapper-logo">
<!-- begin six columns -->
<table class="six columns-wrapper-logo">
<tr>
<td >

<a href="http://www.ats-sport.com"><img src="cid:LOGO-PC" width="40px" height="50px"/></a>
</td>
<td class="expander-logo"><h2>ATS SPORT</h2></td>
</tr>
</table>
<!-- end six columns -->
</td>
<td class="wrapper">
<!-- begin six columns -->
<table class="six columns">
<tr>
<td class="text-right valign-middle">
<!-- <span class="template-label">'.$sujet_email.'</span> /-->
</td>
<td class="expander"></td>
</tr>
</table>
<!-- end six columns -->
</td>
</tr>
</table>
<!-- end container -->
</center>
</td>
</tr>
</table>
<!-- end page header -->
<!-- begin page container -->
<table class="container content dark-theme">
<tr>
<td>

<!-- begin divider -->
'.$entete.'

<table class="divider"></table>
';
$corps = '



'.$info_referent.$info_referents.'
<table class="row">
<td class="wrapper">
<!-- begin twelve columns -->
<table class="six columns">
<tr>
<td>
<p> 
<ul class="info">
<li><strong>'.$lng_trad['nom'].' :</strong> <em>'.$donnees[0]['nomInternaute'].'</em></li>
<li><strong>'.$lng_trad['prenom'].' :</strong> <em>'.$donnees[0]['prenomInternaute'].'</li>
<li><strong>'.$lng_trad['date_de_naissance'].' :</strong>  <em>'.dateen2fr($donnees[0]['naissanceInternaute'],1).'</em> </li>
<li><strong>'.$lng_trad['adresse'].' :</strong> <em>'.$donnees[0]['adresseInternaute'].'</em></li>
<li><strong>'.$lng_trad['code_postal'].' :</strong> <em>'.$donnees[0]['cpInternaute'].'</em></li>
<li><strong>'.$lng_trad['ville'].' :</strong> <em>'.$donnees[0]['villeInternaute'].'</em></li>
<li><strong>'.$lng_trad['pays'].' :</strong> <em>'.$donnees[0]['paysInternaute'].'</em></li>
<li><strong>'.$lng_trad['email'].' :</strong> <em>'.$donnees[0]['emailInternaute'].'</em></li>

</ul>  
</td>
</tr>
</table>

<!-- end twelve columns -->

</td>
<td class="wrapper">
<!-- begin twelve columns -->
<table class="six columns">
<tr>
<td>
<p> 

<ul class="info">
'.$equipe.'
<li><strong>'.$lng_trad['categorie'].' :</strong>  <em>'.$cat_code.'</em></li>
<li><strong>'.$lng_trad['club'].' :</strong> <em>'.$donnees[0]['clubInternaute'].'</em></li>
<li><strong>'.$lng_trad['licence'].' :</strong> <em class="info-danger" style="color:'.$color_certif.'">'.$certif.'</em></li>
<li><strong>'.$lng_trad['auto_parentale'].' :</strong> <em class="info-danger" style="color:'.$color_autop.'">'.$autop.'</em></li>
<li><strong>'.$lng_trad['mode_paiement'].' :</strong> <em>'.$donnees[0]['mode_paiement'].'</em></li>
<li><strong>'.$lng_trad['montant_inscription'].' :</strong> <em>'.$cout_inscription.' € </em></li>
<li><strong>'.$lng_trad['montant_participation'].' : </strong> <em>'.$total_participation.'</em></li>
'.$aff_resum_option_plus.'
'.$aff_frais_coureur.'
<li><strong>'.$lng_trad['total_engagement'].' : </strong> <em><strong>'.$somme_totale_des_inscriptions.$aff_gratuit.'</strong></em></li>
'.$total_des_engagements.'
'.$code_promo.'
'.$groupe.'

</ul> 

</p>

</td>
</tr>
</table>
</td>
</tr>
</table>

'.$info_cheque.'
'.$info_gratuit.'
'.$info_autre.'
'.$info_CB.'

'.$champ_aff;




$corps2 = '
<!-- begin row -->
<table class="row">
<tr>
<!-- begin wrapper -->
<td class="wrapper">
<table class="twelve columns">
'.$info_complementaires_parcours.'

<tr>
<td class="">
<table class="btn primary" align="center">
<tbody>
<tr>
<td>
<a href="'.$lien_inscrits.'"><b>'.$lng_trad['voir_liste_inscrits'].'</b></a>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td class="last">
<p class="m-b-5">'.$lng_trad['editer_info_inscription'].' : </p>
</td>
</tr>

<tr>
<td class="">
<table class="btn orange" align="center">
<tbody>
<tr>
<td>
<a href="'.$lien_edition.'"><b>'.$lng_trad['editer_mon_inscription'].'</b></a>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
'.$lien_recu_7162.'
'.$lien_recu.'

<table align="center">				
<tr >
<td>
<p><h5>'.$lng_trad['code_securite'].' : <strong><em class="info-danger">'.$codeSecurite.'</em></strong></h5></p>
</td>
</tr>
</table>
<table align="center">				
<tr >
<td>
<p><i>'.$lng_trad['mail_generique'].'</i></p>
<p><i>'.$info_agent.'</i></p>
</td>
</tr>
</table>
</table>
</td>
<!-- end wrapper -->
</tr>
</table>
<!-- end row -->';
$corps3 = '
</td>
</tr>
</table>
<!-- end page container -->

<!-- begin page footer -->
<table class="row footer">
<tr>
<td class="center" align="center">
<center>
<!-- begin container -->
<table class="container">
<tr>
<td class="wrapper">
<table class="six columns">
<tr>
<td>
&copy; ATS-SPORT 2018
</td>
<td class="expander"></td>
</tr>
</table>
</td><!--
<td class="wrapper">
<table class="six columns">
<tr>
<td class="wrapper text-right valign-middle">
<a href="javascript:;">About Us</a>
&nbsp; 
<a href="javascript:;">Privacy Policy</a>
&nbsp; 
<a href="javascript:;">Terms of Use</a>
</td>
<td class="expander"></td>
</tr>
</table>
</td> /-->
</tr>
</table>
<!-- end container -->
</center>
</td>
</tr>
</table>
<!-- end page footer -->
</center>
</td>
</tr>
</table>
<!-- end page body -->
</body>
</html>
';
if ($req_mdp==1) {

	return $email_req_mdp;
}
else if ($donnees[0]['insc'] == 1 )
{
	//return $css.$entete_email.$pieces_manquante.$corps2.$corps3;
	return $email_inscription;
}
else if ($donnees[0]['fusion'] == 1 )
{
	return $email_fusion;
}
else if ($donnees[0]['resendmail'] != 1 ){
	//return $css.$entete_email.$corps.$corps2.$corps3;
	return $new_email;
}
else
{
	//return $css.$entete_email.$pieces_manquante.$corps2.$corps3;
	return $new_email;
}

}

function send_mail_contact( $data )
{
	global $mysqli;
	//Data (POST) : nom* - prénom* - email* - objet - évènement - message
	$nom 		= utf8_decode( $data['nom'] );
	$prenom 	= utf8_decode( $data['prenom'] );
	$email 		= utf8_decode( $data['email'] );
	$objet		= utf8_decode( $data['objet'] );
	$evenement	= utf8_decode( $data['evenement'] );
	$message 	= utf8_decode( $data['message'] );
	
	//Type de mail et méthode d'envoi
	$mail = new PHPmailer();
	$mail->IsMail(); 
	$mail->IsHTML(true);

	//Adresse email
	$mail->From = $email;
	$mail->FromName = $prenom." ".$nom;
	$mail->AddAddress( "contact@ats-sport.com" );
	$mail->AddAddress( "hugo@ats-sport.com" );

	$mail->Subject = $objet;

	$mail->Body .= 'Concerne : '.$evenement."<br><br />";
	$mail->Body .= $message;

	if( !empty( $message ) )
	{
		if(!$mail->Send())
		{
			echo "Votre message n'a pas pu être envoyé, merci de réessayer ou de nous contacter au 04 67 45 41 10";
		} 
		else
		{ 
			return true;
		}	
	}
	else
	{
		return "Veuillez svp saisir votre demande";
	}
}

function send_mail_recrutement( $data, $file )
{
	global $mysqli;
	//Data (POST) : nom* - prénom* - sexe* - naissance* - email* - téléphone - adresse* - cp* - ville* - pays* - poste* - cv* - présentation*
	$nom 			= $data['nom'];
	$prenom 		= $data['prenom'];
	$sexe 			= $data['sexe'];
	$naissance 		= $data['naissance'];
	$email 			= $data['email'];
	$telephone		= $data['telephone'];
	$adresse		= $data['adresse'];
	$cp 			= $data['cp'];
	$ville 			= $data['ville'];
	$pays 			= $data['pays'];
	$poste 			= $data['poste'];
	$zone 			= $data['zone'];
	$presentation	= $data['presentation'];

	//Gestion du cv
	$uploaddir = 'tmp/cv/';
	$uploadfile = $uploaddir . basename($file['cv']['name']);

	if( $file['cv']['name'] != '' && $file['cv']['size'] > 0 )
	{
		if( $file['cv']['size'] > 2100000 ) return "Le fichier est trop volumineux";
		if( !move_uploaded_file( $file['cv']['tmp_name'], $uploadfile ) ) return "Le Fichier ne peut être envoyé";
	}
	
	//Type de mail et méthode d'envoi
	$mail = new PHPmailer();
	$mail->IsMail(); 
	$mail->IsHTML(true);

	//Adresse email
	$mail->From = $email;
	$mail->FromName = $prenom." ".$nom;
	$mail->AddAddress( "contact@ats-sport.com" );

	$mail->Subject = "Recrutement ATS-SPORT";

	$message  = "<html>";
	$message .= "<body>";
	$message .= "<b>".$prenom." ".$nom."</b> souhaiterait intégrer l'équipe d'ATS-SPORT en tant que <b>".$poste."</b><br><br>";
	$message .= "Téléphone : ".$telephone."<br>";
	$message .= "Adresse : ".$adresse." ".$cp." ".$ville." ".$pays."<br><br>";
	$message .= "Zone d'intervention : ".$zone."<br><br>";
	$message .= $presentation."<br>";
	$message .= "<body>";
	$message .= "<body>";
	$message .= "</body>";
	$message .= "</html>";

	$file = '/path/to/file/file.zip';

	$mail->AddAttachment( $uploadfile );

	$mail->Body = utf8_decode( $message );

	if(!$mail->Send())
	{
		return "Votre message n'a pu être envoyé, veuillez réessayer ultérieurement";
	} 
	else
	{ 
		return "ok";
	}	
}

function send_mail_admin_devis_epreuve( $data )
{
	global $mysqli;
	//Type de mail et méthode d'envoi
	$mail = new PHPmailer();
	$mail->IsMail(); 
	$mail->IsHTML(true);

	//Adresse email
	$mail->From = $data['email'];
	$mail->FromName = $data['prenom']." ".$data['nom'];
	$mail->AddAddress( "contact@ats-sport.com" );
	$mail->AddAddress( "hugo@ats-sport.com" );
	$mail->AddAddress( "sylvain@ats-sport.com" );

	$mail->Subject = "[ATS-SPORT.COM] : Demande de devis";

	$email  = "<html><body>";
	$email .= "<table border='2' width='100%'>";
	$email .= "<TR><TD colspan='2' align='center'><b>Infos relatives à l'organisation</b></TD></TR>";
	$email .= "<TR><TD align='center' >Souhaite être contacté par téléphone</TD><TD align='center'>".(($data['contact_tel'] == 'oui') ?'oui':'non')."</TD></TR>";
	$email .= "<TR><TD align='center'>Nom de l'organisateur</TD><TD align='center'>".stripslashes( $data['nom'] )."</TD></TR>";
	$email .= "<TR><TD align='center'>Prenom de l'organisateur</TD><TD align='center'>".stripslashes( $data['prenom'] )."</TD></TR>";
	$email .= "<TR><TD align='center'>Email de l'organisateur</TD><TD align='center'>".stripslashes( $data['email'] )."</TD></TR>";
	$email .= "<TR><TD align='center'>Téléphone de l'organisateur</TD><TD align='center'>".stripslashes( $data['telephone'] )."</TD></TR>";
	$email .= "<TR><TD align='center'>Pays de l'organisateur</TD><TD align='center'>".stripslashes( $data['pays'] )."</TD></TR>";
	$email .= "<TR><TD colspan='2' align='center'><b>Info relatives à l'épreuve</b></TD></TR>";
	$email .= "<TR><TD align='center'>Nom de l'épreuve</TD><TD align='center'>".stripslashes( $data['nomepreuve'] )."</TD></TR>";
	$email .= "<TR><TD align='center'>Type d'épreuve</TD><TD align='center'>".stripslashes( $data['type_epreuve'] )."</TD></TR>";
	$email .= "<TR><TD align='center'>Date de l'épreuve</TD><TD align='center'>".$data['date']."</TD></TR>";
	$email .= "<TR><TD align='center'>Ville</TD><TD align='center'>".stripslashes( $data['ville'] )."</TD></TR>";
	$email .= "<TR><TD align='center'>Département</TD><TD align='center'>".stripslashes( $data['departement'] )."</TD></TR>";
	$email .= "<TR><TD align='center'>Nombre de participant</TD><TD align='center'>".stripslashes( $data['participation'] )."</TD></TR>";
	$email .= "<TR><TD align='center'>Nom de l'asso/Club</TD><TD align='center'>".stripslashes( $data['structure'] )."</TD></TR>";
	$email .= "<TR><TD align='center'>Site web</TD><TD align='center'>".stripslashes( $data['site'] )."</TD></TR>";
	$email .= "<TR><TD align='center'>Description l'épreuve</TD><TD align='center'>".stripslashes( $data['description'] )."</TD></TR>";
	$email .= "<TR><TD colspan='2' align='center'><b>Info relatives au devis</b></TD></TR>";
	$email .= "<TR><TD align='center'>Devis Chronométrage</TD><TD align='center'>".(($data['chrono'] == "oui") ?"oui":"non")."</TD></TR>";
	$email .= "<TR><TD align='center'>Devis Inscription</TD><TD align='center'>".(($data['inscriptions'] == "oui") ?"oui":"non")."</TD></TR>";
	$email .= "<TR><TD align='center'>Devis dossard</TD><TD align='center'>".(($data['dossards'] == "oui") ?"oui":"non")."</TD></TR>";
	$email .= "<TR><TD align='center'>Devis newsletter</TD><TD align='center'>".(($data['newsletters'] == "oui") ?"oui":"non")."</TD></TR>";
	$email .= "<TR><TD align='center'>Devis spécifique</TD><TD align='center'>".stripslashes( $data['autres_besoins'] )."</TD></TR>";
	$email .= "<TR><TD align='center'>Commentaires</TD><TD align='center'>".stripslashes( $data['commentaires'] )."</TD></TR>";
	$email .= "</table>";
	$email .= "</body></html>";

	$mail->Body = utf8_decode( $email );
	
	if(!$mail->Send())
	{
		return false;
	} 
	else
	{ 
		return true;
	}
}

function send_mail_resultats ($expediteur_email ,$destinataire_email, $sujet, $corps, $pieces_jointes)
{
	global $mysqli;
	if( empty( $pieces_jointes ) ) return false;
	//Create a new PHPMailer instance
	$mail = new PHPMailer;
	// Set PHPMailer to use the sendmail transport
	$mail->isSendmail();
	$mail->CharSet = 'UTF-8';
	//Set who the message is to be sent from
	$mail->setFrom($expediteur_email);
	//Set an alternative reply-to address
	$mail->addReplyTo($expediteur_email);
	//Set who the message is to be sent to
	$mail->addAddress($destinataire_email);
	
	//Set the subject line
	$mail->Subject = $sujet;
	
	$mail->AddEmbeddedImage('/var/www/vhosts/ats-sport.com/httpdocs/assets/img/logoATS-bleu2-small.png', 'LOGO-PC');
	//Read an HTML message body from an external file, convert referenced images to embedded,
	//convert HTML into a basic plain-text alternative body
	//$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
	$mail->msgHTML(mail_resultats($corps));
	//Replace the plain text body with one created manually
	//$mail->AltBody = 'This is a plain-text message body';
	//Attach an image file
	//$mail->addAttachment('images/phpmailer_mini.png');
	foreach( $pieces_jointes as $pj )
		$mail->addAttachment('/var/www/vhosts/ats-sport.com/httpdocs/resultats/'.$pj);
	
	//send the message, check for errors

	/*if (!$mail->send()) {
		return $mail->ErrorInfo;
	} else {
		return "ok";
	} */

	if (!$mail->send()) {
		return false;
	} else {
		return true;
	}

}

function mail_resultats($data)
{
	global $mysqli;
	$header = '	
	<!DOCTYPE html>
	<!--[if IE 8]> <html lang="fr" class="ie8"> <![endif]-->
	<!--[if !IE]><!-->
	<html lang="fr">
	<!--<![endif]-->
	<head>
	<meta charset="utf-8" />
	<title>ATS-SPORT | '.$data['nomEpreuve'].'</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<meta content="ATS-SPORT - '.$data['nomEpreuve'].'" name="description" />
	<meta content="ATS-SPORT" name="author" />
	<meta http-equiv="content-type" content="text/html;">
	</head>
	';
	$body='
	<body style="color:#000000; background-color: #FFFFFF;">
	<div>
	<table style="color: #333; background: #fff; padding: 0; margin:0; width: 100%; font: 15px \'Helvetica Neue\', Arial, Helvetica;" cellspacing="0" cellpadding="0" border="0">
	<tbody>
	<tr width="100%">
	<td style="background: #f0f0f0; font: 15px \'Helvetica Neue\',Arial, Helvetica;">
	<table style="border: none; padding: 0 18px; margin: 50px auto; width: 500px;">
	<tbody>
	<tr width="100%" height="57">
	<td style="border-top-left-radius: 4px; border-top-right-radius: 4px; background: #17ABE7; padding: 12px 18px; text-align: center;" valign="top" align="left"> 
	<img src="https://www.ats-sport.com/images/logo/ats-sport/Specialiste-Chronometrage-sportif.jpg" title="ATS-SPORT" moz-do-not-send="true" width="75"> 
	</td>
	</tr>
	<tr width="100%">
	<td style="border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; background:#fff; padding: 18px;">';
	$body .= 										$data['typeEnvoi']($data);
	$footer ='									</td>
	</tr>
	</tbody>
	</table>
	</td>
	</tr>
	</tbody>
	</table>
	</div>
	</body>
	</html>';

	return $header.$body.$footer;
}

function immediat($data)
{
	global $mysqli;
	$parcours = '<ul style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">';
	foreach( $data['parcours'] as $p )
	{
		$parcours .= '<li>'.$p.'</li>';
	}
	$parcours .= '</ul>';

	$body  ='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">Bonjour, </p>';
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">vous trouverez ci-joint les <b><u>résultats provisoires</u></b> de l\'épreuve '.$data['nomEpreuve'].' du '.date("d/m/Y",strtotime( $data['dateEpreuve'] ) ).' à '.date("H:i").'</p>';
	$body .= $parcours;
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;"><strong><u>Les résultats officiels vous seront envoyés dans h-12 heures</u></strong>></p><br>';
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">Sportivement,<br>l\'équipe ATS-SPORT</p>';
	$body .='<a href="https://shop.ats-sport.com/fr/"><img src="https://www.ats-sport.com/images/banniere/specialiste-dossard-sportif.jpg" alt="Boutique de l\'organisateur ATS-SPORT" /></a>';

	return $body;
}

function un_jour($data)
{
	global $mysqli;
	$parcours = '<ul style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">';
	foreach( $data['parcours'] as $p )
	{
		$parcours .= '<li>'.$p.'</li>';
	}
	$parcours .= '</ul>';

	$body  ='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">Bonjour, </p>';
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">vous trouverez ci-joint les résultats de l\'épreuve '.$data['nomEpreuve'].' du '.date("d/m/Y",strtotime( $data['dateEpreuve'] ) ).' à '.date("H:i").'</p>';
	$body .= $parcours;
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">Sportivement,<br>l\'équipe ATS-SPORT</p>';
	$body .='<a href="https://shop.ats-sport.com/fr/"><img src="https://www.ats-sport.com/images/banniere/specialiste-dossard-sportif.jpg" alt="Boutique de l\'organisateur ATS-SPORT" /></a>';

	return $body;
}

function deux_jours($data)
{
	global $mysqli;
	$parcours = '<ul style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">';
	foreach( $data['parcours'] as $p )
	{
		$parcours .= '<li>'.$p.'</li>';
	}
	$parcours .= '</ul>';

	$body  ='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">Bonjour, </p>';
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">vous trouverez ci-joint les résultats de l\'épreuve '.$data['nomEpreuve'].' du '.date("d/m/Y",strtotime( $data['dateEpreuve'] ) ).' à '.date("H:i").'</p>';
	$body .= $parcours;
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">Sportivement,<br>l\'équipe ATS-SPORT</p>';
	$body .='<a href="https://shop.ats-sport.com/fr/"><img src="https://www.ats-sport.com/images/banniere/specialiste-dossard-sportif.jpg" alt="Boutique de l\'organisateur ATS-SPORT" /></a>';

	return $body;
}

function send_mail_organisateur($expediteur_email ,$destinataire_email, $sujet, $corps)
{
	global $mysqli;
	//Create a new PHPMailer instance
	$mail = new PHPMailer;
	// Set PHPMailer to use the sendmail transport
	$mail->isSendmail();
	$mail->CharSet = 'UTF-8';
	//Set who the message is to be sent from
	$mail->setFrom($expediteur_email);
	//Set an alternative reply-to address
	$mail->addReplyTo($expediteur_email);
	//Set who the message is to be sent to
	$mail->addAddress($destinataire_email);
	$mail->AddBCC("hugo@ats-sport.com");
	
	//Set the subject line
	$mail->Subject = $sujet;
	
	//Read an HTML message body from an external file, convert referenced images to embedded,
	//convert HTML into a basic plain-text alternative body
	//$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
	$mail->msgHTML(mail_organisateur($corps));
	//Replace the plain text body with one created manually
	//$mail->AltBody = 'This is a plain-text message body';
	//Attach an image file
	if( !empty($corps['liste_engages']) )
		$mail->addAttachment('/var/www/vhosts/ats-sport.com/httpdocs/tmp/'.$corps['liste_engages']);
	
	//send the message, check for errors
	if (!$mail->send()) {
		return false;
	} else {
		return true;
	}
}

function mail_organisateur($data)
{
	global $mysqli;
	$header = '	
	<!DOCTYPE html>
	<!--[if IE 8]> <html lang="fr" class="ie8"> <![endif]-->
	<!--[if !IE]><!-->
	<html lang="fr">
	<!--<![endif]-->
	<head>
	<meta charset="utf-8" />
	<title>ATS-SPORT | '.$data['nomEpreuve'].'</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<meta content="ATS-SPORT - '.$data['nomEpreuve'].'" name="description" />
	<meta content="ATS-SPORT" name="author" />
	<meta http-equiv="content-type" content="text/html;">
	</head>
	';
	$body='
	<body style="color:#000000; background-color: #FFFFFF;">
	<div>
	<table style="color: #333; background: #fff; padding: 0; margin:0; width: 100%; font: 15px \'Helvetica Neue\', Arial, Helvetica;" cellspacing="0" cellpadding="0" border="0">
	<tbody>
	<tr width="100%">
	<td style="background: #f0f0f0; font: 15px \'Helvetica Neue\',Arial, Helvetica;">
	<table style="border: none; padding: 0 18px; margin: 50px auto; width: 500px;">
	<tbody>
	<tr width="100%" height="57">
	<td style="border-top-left-radius: 4px; border-top-right-radius: 4px; background: #17ABE7; padding: 12px 18px; text-align: center;" valign="top" align="left"> 
	<img src="https://www.ats-sport.com/images/logo/ats-sport/Specialiste-Chronometrage-sportif.jpg" title="ATS-SPORT" moz-do-not-send="true" width="75"> 
	</td>
	</tr>
	<tr width="100%">
	<td style="border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; background:#fff; padding: 18px;">';
	$body .= 										$data['evenement']($data);
	$footer ='									</td>
	</tr>
	</tbody>
	</table>
	</td>
	</tr>
	</tbody>
	</table>
	</div>
	</body>
	</html>';

	return $header.$body.$footer;
}

//div principale
function ouverture_inscriptions($data)
{
	global $mysqli;
	$body  ='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">Bonjour, </p>';
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">les inscriptions en ligne de votre épreuve <b>'.$data['nomEpreuve'].'</b> du '.date("d/m/Y",strtotime( $data['dateEpreuve'] ) ).' sont ouvertes sur ats-sport.com</p>';
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">Connectez-vous dès maintenant sur votre espace organisateur <a href="https://www.ats-sport.com/admin/login_v2.php">www.ats-sport.com/admin/login_v2.php</a> pour gérer vos listes d\'engagés</p>';
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;"><strong>Lien vers le formulaire d\'inscriptions : </strong></p>';
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">Lien direct : https://www.ats-sport.com/inscriptions.php?id_epreuve='.$data['idEpreuve'].'<br>';
	$body .='	Intégrer à votre site web : <span><</span>iframe src="https://www.ats-sport.com/inscriptions.php?id_epreuve='.$data['idEpreuve'].'&panel=iframe" width="900"height="1200"></iframe></code></p>';
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;"><strong>Lien vers la liste des engagés : </strong></p>';
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">Lien direct : https://www.ats-sport.com/liste_des_inscrits.php?id_epreuve='.$data['idEpreuve'].'<br>';
	$body .='	Intégrer à votre site web : <span><</span>iframe src="https://www.ats-sport.com/liste_des_inscrits.php?id_epreuve='.$data['idEpreuve'].'&panel=iframe" width="900"height="1200"></iframe></code></p>';
	if( !$data['reglement'] )
		$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">Nous n\'avons pas reçu le <u>réglement de l\'épreuve</u>, merci de l\'envoyer à sylvain@ats-sport.com dès que possible.</p><br>';

	if( $data['chrono_ats_sport'] )
	{
		$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;"><strong>Résultats de l\'épreuve : </strong></p>';
		$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">';
		$body .='	En direct pendant la course : <a href="https://www.ats-sport.com/liveResults/Resultats/direct/'.$data['idEpreuve'].'">https://www.ats-sport.com/liveResults/Resultats/direct/'.$data['idEpreuve'].'</a><br>';
		$body .='	Après la course : <a href="https://www.ats-sport.com/resultats.php?id_epreuve='.$data['idEpreuve'].'">https://www.ats-sport.com/resultats.php?id_epreuve='.$data['idEpreuve'].'</a>';
		$body .='</p><br>';
	}
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333; margin-bottom:0;">Sportivement,<br>l\'équipe ATS-SPORT<br><br></p>';
	$body .='<a href="https://shop.ats-sport.com/fr/"><img src="https://www.ats-sport.com/images/banniere/specialiste-dossard-sportif.jpg" alt="Boutique de l\'organisateur ATS-SPORT" /></a>';

	return $body;
}

//div principale
function fermeture_inscriptions($data)
{
	global $mysqli;
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">Bonjour, </p>';
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">les inscriptions en ligne de votre épreuve <b>'.$data['nomEpreuve'].'</b> du '.date("d/m/Y",strtotime( $data['dateEpreuve'] ) ).' sont terminées sur ats-sport.com</p>';
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">Connectez-vous dès maintenant sur votre espace organisateur <a href="https://www.ats-sport.com/admin/login_v2.php">www.ats-sport.com/admin/login_v2.php</a> pour : </p>';
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">';
	$body .='	<ul style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">';
	$body .='		<li>Télécharger vos listes d\'engagés</li>';
	$body .='		<li>Vérifier les derniers certificats médicaux</li>';
	$body .='	</ul>';
	$body .='</p>';
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">Si vous proposez des inscriptions sur place, veillez à prévoir des bulletins d\'inscriptions papiers avec les éléments suivants : ';
	$body .='	<ul style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">';
	$body .='		<li>Course / épreuve</li>';
	$body .='		<li>n° de dossard</li>';
	$body .='		<li>nom</li>';
	$body .='		<li>prénom</li>';
	$body .='		<li>sexe</li>';
	$body .='		<li>date de naissance</li>';
	$body .='	</ul>';
	$body .='</p>';
	if( $data['chrono_ats_sport'] )
	{
		$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">Résultats de l\'épreuve : ';
		$body .='<ul style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">';
		$body .='	<li>en direct pendant la course : <a href="https://www.ats-sport.com/liveResults/Resultats/direct/'.$data['idEpreuve'].'">https://www.ats-sport.com/liveResults/Resultats/direct/'.$data['idEpreuve'].'</a></li>';
		$body .='		<li>après la course : <a href="https://www.ats-sport.com/resultats.php?id_epreuve='.$data['idEpreuve'].'">https://www.ats-sport.com/resultats.php?id_epreuve='.$data['idEpreuve'].'</a></li>';
		$body .='	</ul>';
		$body .='</p><br>';
	}
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">Sportivement,<br>l\'équipe ATS-SPORT<br></p>';
	$body .='<a href="https://shop.ats-sport.com/fr/"><img src="https://www.ats-sport.com/images/banniere/specialiste-dossard-sportif.jpg" alt="Boutique de l\'organisateur ATS-SPORT" /></a>';

	return $body;
}

//div principale
function resultats($data)
{
	global $mysqli;
	$body  ='<div>';
	$body .='	<p>Bonjour, </p>';
	$body .='	<p>vous trouverez ci-dessous les liens vers les résultats de l\'épreuve</p>';
	$body .='	<p><ul>';
	$body .='		<li>en direct pendant la course : <a href="https://www.ats-sport.com/liveResults/Resultats/direct/'.$data['idEpreuve'].'">https://www.ats-sport.com/liveResults/Resultats/direct/'.$data['idEpreuve'].'</a></li>';
	$body .='		<li>après la course : <a href="https://www.ats-sport.com/resultats.php?id_epreuve='.$data['idEpreuve'].'">https://www.ats-sport.com/resultats.php?id_epreuve='.$data['idEpreuve'].'</a></li>';
	$body .='	</ul></p><br>';
	$body .='	<p>Sportivement,</p>';
	$body .='	<p>l\'équipe ATS-SPORT</p>';
	$body .='	<a href="https://shop.ats-sport.com/fr/"><img src="https://www.ats-sport.com/images/banniere/specialiste-dossard-sportif.jpg" alt="Boutique de l\'organisateur ATS-SPORT" /></a>';
	$body .='</div>';

	return $body;
}

function send_mail_interne_ats($expediteur_email ,$destinataire_email, $sujet, $corps)
{
	global $mysqli;
	//Create a new PHPMailer instance
	$mail = new PHPMailer;
	// Set PHPMailer to use the sendmail transport
	$mail->isSendmail();
	$mail->CharSet = 'UTF-8';
	//Set who the message is to be sent from
	$mail->setFrom($expediteur_email);
	//Set an alternative reply-to address
	$mail->addReplyTo($destinataire_email);
	//Set who the message is to be sent to
	$mail->addAddress($destinataire_email);

	$mail->AddBCC("hugo@ats-sport.com");
	
	//Set the subject line
	$mail->Subject = $sujet;
	
	//Read an HTML message body from an external file, convert referenced images to embedded,
	//convert HTML into a basic plain-text alternative body
	//$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
	$mail->msgHTML(mail_interne_ats($corps));
	//Replace the plain text body with one created manually
	//$mail->AltBody = 'This is a plain-text message body';
	//Attach an image file
	
	//send the message, check for errors
	if (!$mail->send()) {
		return false;
	} else {
		return true;
	}
}

function mail_interne_ats($data)
{
	global $mysqli;
	$header = '	
	<!DOCTYPE html>
	<!--[if IE 8]> <html lang="fr" class="ie8"> <![endif]-->
	<!--[if !IE]><!-->
	<html lang="fr">
	<!--<![endif]-->
	<head>
	<meta charset="utf-8" />
	<title>ATS-SPORT | '.$data['nomEpreuve'].'</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<meta content="ATS-SPORT - '.$data['nomEpreuve'].'" name="description" />
	<meta content="ATS-SPORT" name="author" />
	<meta http-equiv="content-type" content="text/html;">
	</head>
	';
	$body='
	<body style="color:#000000; background-color: #FFFFFF;">
	<div>
	<table style="color: #333; background: #fff; padding: 0; margin:0; width: 100%; font: 15px \'Helvetica Neue\', Arial, Helvetica;" cellspacing="0" cellpadding="0" border="0">
	<tbody>
	<tr width="100%">
	<td style="background: #f0f0f0; font: 15px \'Helvetica Neue\',Arial, Helvetica;">
	<table style="border: none; padding: 0 18px; margin: 50px auto; width: 500px;">
	<tbody>
	<tr width="100%" height="57">
	<td style="border-top-left-radius: 4px; border-top-right-radius: 4px; background: #17ABE7; padding: 12px 18px; text-align: center;" valign="top" align="left"> 
	<img src="https://www.ats-sport.com/images/logo/ats-sport/Specialiste-Chronometrage-sportif.jpg" title="ATS-SPORT" moz-do-not-send="true" width="75"> 
	</td>
	</tr>
	<tr width="100%">
	<td style="border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; background:#fff; padding: 18px;">';
	$body .= 										$data['evenement']($data);
	$footer ='									</td>
	</tr>
	</tbody>
	</table>
	</td>
	</tr>
	</tbody>
	</table>
	</div>
	</body>
	</html>';

	return $header.$body.$footer;
}

//div principale
function facturation_apres_epreuve($data)
{
	global $mysqli;
	$periode = array( 'epreuve'=>'après l\'épreuve', 'trimestriel' => 'tous les 3 mois', 'mensuel' => 'tous les mois' );

	$body  ='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;"><strong>Rappel épreuve terminée</strong></p>';
	$body .='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">';
	$body .='	<strong>Epreuve : </strong> '.$data['nomEpreuve'].' (id='.$data['idEpreuve'].')<br>';
	$body .='   <strong>Date : </strong> '.$data['dateEpreuve'].'<br>';
	$body .='   <strong>Périodicité : </strong> '.$periode[$data['periode_reversement_inscriptions']];
	$body .=' </p>';
	$body .=' <p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;color: #939393; margin-bottom:0;">Service comptabilité ATS-SPORT</p>';

	return $body;
}

function facturation_trimestriel($data)
{
	global $mysqli;
	$periode = array( 'epreuve'=>'après l\'épreuve', 'trimestriel' => 'tous les 3 mois', 'mensuel' => 'tous les mois' );

	$body  ='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">';
	$body .='	<strong>Epreuve : </strong> '.$data['nomEpreuve'].' (id='.$data['idEpreuve'].')<br>';
	$body .='   <strong>Date : </strong> '.$data['dateEpreuve'].'<br>';
	$body .='   <strong>Périodicité : </strong> '.$periode[$data['periode_reversement_inscriptions']];
	$body .=' </p>';
	$body .=' <p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;color: #939393; margin-bottom:0;">Service comptabilité ATS-SPORT</p>';

	return $body;
}

function facturation_mensuel($data)
{
	global $mysqli;
	$periode = array( 'epreuve'=>'après l\'épreuve', 'trimestriel' => 'tous les 3 mois', 'mensuel' => 'tous les mois' );
	
	$body  ='<p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;">';
	$body .='	<strong>Epreuve : </strong> '.$data['nomEpreuve'].' (id='.$data['idEpreuve'].')<br>';
	$body .='   <strong>Date : </strong> '.$data['dateEpreuve'].'<br>';
	$body .='   <strong>Périodicité : </strong> '.$periode[$data['periode_reversement_inscriptions']];
	$body .=' </p>';
	$body .=' <p style="font: 15px/1.25em \'Helvetica Neue\',Arial, Helvetica; color: #333;color: #939393; margin-bottom:0;">Service comptabilité ATS-SPORT</p>';

	return $body;
}
?>