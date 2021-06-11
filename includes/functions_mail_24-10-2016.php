<?php  

/**
 * This example shows sending a message using a local sendmail binary.
 */
#4b9ee8
require_once $_SERVER["DOCUMENT_ROOT"].'/assets/plugins/phpMailer/PHPMailerAutoload.php';


	
function send_mail ($expediteur_email,$expediteur_nom,$destinataire_email, $destinataire_nom, $sujet,$corps) {


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
	$mail->AddBCC("inscriptions@pointcourse.com","inscriptions@pointcourse.com");
	$mail->AddBCC("inscriptions@ats-sport.com","contact@ats-sport.com");
	//Set the subject line
	$mail->Subject =  $sujet;
	
	$mail->AddEmbeddedImage('../assets/img/logo-point-course-small.png', 'LOGO-PC');
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


$info_complementaires_parcours = '';

if (!empty($donnees[0]['infoParcoursInscription'])) { 

   $info_complementaires_parcours = '<tr>
                                        <td style="padding: 5px; border: 1px  solid #f59c1a;margin-bottom:10px">
											<p class="m-b-5">'.$donnees[0]['infoParcoursInscription'].'</p>
                                        </td>
                                     </tr>';
}
//print_r($donnees);
$code_promo = $aff_gratuit = '';
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

if ($donnees[0]['code_promo'] != 'Aucun') {
	
	$donnees[0]['cout'] = $donnees[0]['cout']-$donnees[0]['valeur_code_promo'];
	
	//$cout_inscription = '<s>'.$cout_inscription.' €</s>';		
	//echo floatval($donnees[0]['valeur_code_promo']);
	//echo floatval($donnees[0]['montant']) - 10.3;
	
	if (($donnees[0]['montant']-$donnees[0]['valeur_code_promo']) == 0) {
		$cpt_gratuit = 1;
		$aff_gratuit = '<strong> GRATUIT !</strong>';
	}
	else{
		
		$aff_gratuit = '<strong> ( Réduction de '.$donnees[0]['valeur_code_promo'].' € )</strong>';
	}

	$code_promo = '<li><strong>Code promo utilisé :</strong> <em>'.$donnees[0]['code_promo'].'</em></li>';

}

//SI GROUPE
$groupe = '';
if ($donnees[0]['groupe'] != 'Aucun') {
	$groupe = '<li class="text-danger"><strong>Groupe :</strong> <em>'.$donnees[0]['groupe'].'</em></li>';
}
$info_gratuit = '';
if ($donnees[0]['mode_paiement'] == 'GRATUIT') {


$cout_inscription = 0;
$aff_gratuit = '<strong> GRATUIT !</strong>';
$code_promo = '<li><strong>Code promo utilisé :</strong> <em>'.$donnees[0]['code_promo'].'</em></li>';

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
	if ($donnees[0]['participation'] <= 0) { $total_participation = 'Aucune'; } else { $total_participation = $donnees[0]['participation'].' €'; }
}
else
{
	
	$total_participation='0 €';
}

$info_referent='';

if (!empty($donnees[0]['referent'])) {
	    //A FAIRE INNER JOIN AVEC TABLE RIEI
		$query  = "SELECT ri.prenomInternaute, ri.nomInternaute FROM r_internaute as ri ";
		$query .= "INNER JOIN r_inscriptionepreuveinternaute as riei ON ri.idInternaute = riei.idInternaute";
		$query .=" WHERE ri.idInternaute = ".$donnees[0]['referent'];
		$query .=" AND riei.idEpreuve= ".$donnees[0]['idEpreuve'];
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
		$query .= " AND ri.idInternaute NOT IN (".$donnees[0]['referents'].")";		$query .= " AND rii.idEpreuve = ".$donnees[0]['idEpreuve'];
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
$aff_frais_coureur ='';

if ($donnees[0]['mode_paiement']!='GRATUIT')
{
	if ($qui_paye == 'organisateur') 
	{ 
		$somme_totale_des_inscriptions = $donnees[0]['cout']+$donnees[0]['participation'];
	}
	else
	{
	
		
		if (empty($donnees[0]['referent']) && $cpt_gratuit==0) {	
			$aff_frais_coureur = '<li><strong>Montant des frais d\'inscriptions : </strong> <em><strong>'.$frais_coureur.'</strong> € </em></li>';
			$somme_totale_des_inscriptions = $donnees[0]['cout'] + $frais_coureur + $donnees[0]['participation'];
		}
		else
		{
			$somme_totale_des_inscriptions = $donnees[0]['cout'] + $donnees[0]['participation'];
			
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
$lien_edition = 'http://www.pointcourse.com/inscriptions.php?id_epreuve='.$donnees[0]['idEpreuve'].'&id_parcours='.$donnees[0]['idParcours'].'&id='.$donnees[0]['idSession'].'&date_inscription='.$donnees[0]['dateInscription'].'&action=update&id_int='.$idEpInscInt;
$lien_inscrits = 'http://www.pointcourse.com/liste_des_inscrits.php?id_epreuve='.$donnees[0]['idEpreuve'];


if ($donnees[0]['certificatMedical'] == 'non')
{
	$color_certif='#ff5b57';
	$certif = 'Non fourni';
}
else if ($donnees[0]['certificatMedical'] == 'oui')
{
	$color_certif='#f59c1a';
	$certif = 'Fourni';
}
else
{
	$color_certif='#f59c1a';
	$certif = 'Pas de besoin';
}
	
if ($donnees[0]['autoParentale'] == 'non')
{
	$color_autop='#ff5b57';
	$autop = 'Non fourni';
}
else if ($donnees[0]['autoParentale'] == 'oui')
{
	$color_autop='#f59c1a';
	$autop = 'Fourni';
}
else
{
	$color_autop='#f59c1a';
	$autop = 'Pas de besoin';
}

$champ_aff= '';
$champ_dynamique = recup_champ_dotation_inscrit($donnees[0]['idEpreuve'], $donnees[0]['idParcours'], $donnees[0]['idInternaute'], $donnees[0]['idInscriptionEpreuveInternaute']);
$champ_dynamique_epreuve = recup_champ_dotation_inscrit_epreuve($donnees[0]['idEpreuve'], $donnees[0]['idInternaute'],$donnees[0]['idInscriptionEpreuveInternaute']);

if (!empty($champ_dynamique) || !empty($champ_dynamique_epreuve)) {
	$champ = '';

							
	foreach ($champ_dynamique as $j=>$value_champ) { 
	
		//if ($cpt==1) $class="info"; else $class="";
		if ($value_champ['type_champ']=='TEXTAREA') $value_champ['value'] = (tronque_texte($value_champ['value'],10));
		if ($value_champ['champ']=='participation') $value_champ['value'] = $value_champ['value']." unité(s) [ <b>".$value_champ['prix_total']." €</b> ]";
	
		//echo '<tr class=".$class.">';
        $champ .='<li>'.$value_champ['label'].' : <em>'.$value_champ['value'].'</em></li>';

	}
	foreach ($champ_dynamique_epreuve as $j=>$value_champ_epreuve) { 
	
		//if ($cpt==1) $class="info"; else $class="";
		//if ($value_champ_epreuve['type_champ']=='TEXTAREA') $value_champ['value'] = (tronque_texte($value_champ['value'],10));
		if ($value_champ_epreuve['champ']=='participation') $value_champ_epreuve['value'] = $value_champ_epreuve['value']." unité(s) [ <b>".$value_champ_epreuve['prix_total']." €</b> ]";
	
		//echo '<tr class=".$class.">';
        $champ_epreuve .='<li>'.$value_champ_epreuve['label'].' : <em>'.$value_champ_epreuve['value'].'</em></li>';

	}
	$champ_aff = '               
		<table class="row">
			<tr>
				<td class="wrapper">
					<table class="ten columns">
						<tr>
							<td>
								<h6>Vos réponses aux questions subsidiaires</h6>
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
}

							
$entete = ' 
			<table class="row">
                <tr>
                    <!-- begin wrapper -->
                    <td class="wrapper">
                        <table class="twelve columns">
                            <tr>
                                <td class="last">
                                    <h4>Inscription à l\'épreuve <em style="color:#f59c1a">'.$donnees[0]['nomEpreuve'].' - '.$donnees[0]['nomParcours'].'</em></h4>
                                    <h5 >Résumé de vos informations</h5>
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
	if ($donnees[0]['mode_paiement'] !='CHEQUE')
	{
		
		$num_dossard = '<h5> - Dossard N° : <em>'.$donnees[0]['numerotation'].'</h5></em>';
		
	}
	
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
else
{
	$sujet_email ='Confirmation d\'inscription';
	$codeSecurite = $donnees[0]['codeSecurite'];
	
}
if ($donnees[0]['paiement_CB'] == 'OK' ) {
	$sujet_lien_edition = 'EFFECTUER LE PAIEMENT EN LIGNE DE MON INSCRIPTION';
	//$codeSecurite = $donnees[0]['passInternaute'];
	$lien_edition_resend = 'http://www.pointcourse.com/liste_des_inscrits.php?id_epreuve='.$donnees[0]['idEpreuve'].'&id_parcours='.$donnees[0]['idParcours'].'&id='.$donnees[0]['idSession'].'&idInternaute='.$donnees[0]['idInternaute'].'&action=maj_paiement&id_int='.$donnees[0]['referents'];

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



$css= '	
<!DOCTYPE html>
<!--[if IE 8]> <html lang="fr" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="fr">
<!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<title>Point Course | '.$donnees[0]['nomEpreuve'].' - '.$donnees[0]['nomParcours'].'</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<meta content="Point Course - '.$donnees[0]['nomEpreuve'].' - '.$donnees[0]['nomParcours'].' name="description" />
	<meta content="ATS-SPORT" name="author" />
	
	<style type="text/css">
	    /******************************************************
        * INK RESPONSIVE EMAIL TEMPLATE: http://zurb.com/ink/ *
        ******************************************************/
        
        /* Client-specific Styles & Reset */
        
        #outlook a { 
          padding:0; 
        } 
        
        body{ 
          width:100% !important; 
          min-width: 100%;
          -webkit-text-size-adjust:100%; 
          -ms-text-size-adjust:100%; 
          margin:0; 
          padding:0;
        }
        
        .ExternalClass { 
          width:100%;
        } 
        
        .ExternalClass, 
        .ExternalClass p, 
        .ExternalClass span, 
        .ExternalClass font, 
        .ExternalClass td, 
        .ExternalClass div { 
          line-height: 100%; 
        } 
        
        #backgroundTable { 
          margin:0; 
          padding:0; 
          width:100% !important; 
          line-height: 100% !important; 
        }
        
        img { 
          outline:none; 
          text-decoration:none; 
          -ms-interpolation-mode: bicubic;
          width: auto;
          max-width: 100%; 
          float: left; 
          clear: both; 
          display: block;
        }
        
        center {
          width: 100%;
          min-width: 580px;
        }
        
        a img { 
          border: none;
        }
        
        p {
          margin: 0 0 0 10px;
        }
        
        table {
          border-spacing: 0;
          border-collapse: collapse;
        }
        
        td { 
          word-break: break-word;
          -webkit-hyphens: auto;
          -moz-hyphens: auto;
          hyphens: auto;
          border-collapse: collapse !important; 
        }
        
        table, tr, td {
          padding: 0;
          vertical-align: top;
          text-align: left;
        }
        
        hr {
          color: #d9d9d9; 
          background-color: #d9d9d9; 
          height: 1px; 
          border: none;
        }
        
        /* Responsive Grid */
        
        table.body {
          height: 100%;
          width: 100%;
        }
        
        table.container {
          width: 580px;
          margin: 0 auto;
          text-align: inherit;
        }
        
        table.row { 
          padding: 0px; 
          width: 100%;
          position: relative;
        }
        
        table.container table.row {
          display: block;
        }
        
        td.wrapper {
          padding: 10px 20px 0px 0px;
          position: relative;
        }
        
        table.columns,
        table.column {
          margin: 0 auto;
        }
        
        table.columns td,
        table.column td {
          padding: 0px 0px 10px; 
        }
        
        table.columns td.sub-columns,
        table.column td.sub-columns,
        table.columns td.sub-column,
        table.column td.sub-column {
          padding-right: 10px;
        }
        
        td.sub-column, td.sub-columns {
          min-width: 0px;
        }
        
        table.row td.last,
        table.container td.last {
          padding-right: 0px;
        }
        
        table.one { width: 30px; }
        table.two { width: 80px; }
        table.three { width: 130px; }
        table.four { width: 180px; }
        table.five { width: 230px; }
        table.six { width: 280px; }
        table.seven { width: 330px; }
        table.eight { width: 380px; }
        table.nine { width: 430px; }
        table.ten { width: 480px; }
        table.eleven { width: 530px; }
        table.twelve { width: 580px; }
        
        table.one center { min-width: 30px; }
        table.two center { min-width: 80px; }
        table.three center { min-width: 130px; }
        table.four center { min-width: 180px; }
        table.five center { min-width: 230px; }
        table.six center { min-width: 280px; }
        table.seven center { min-width: 330px; }
        table.eight center { min-width: 380px; }
        table.nine center { min-width: 430px; }
        table.ten center { min-width: 480px; }
        table.eleven center { min-width: 530px; }
        table.twelve center { min-width: 580px; }
        
        table.one .panel center { min-width: 10px; }
        table.two .panel center { min-width: 60px; }
        table.three .panel center { min-width: 110px; }
        table.four .panel center { min-width: 160px; }
        table.five .panel center { min-width: 210px; }
        table.six .panel center { min-width: 260px; }
        table.seven .panel center { min-width: 310px; }
        table.eight .panel center { min-width: 360px; }
        table.nine .panel center { min-width: 410px; }
        table.ten .panel center { min-width: 460px; }
        table.eleven .panel center { min-width: 510px; }
        table.twelve .panel center { min-width: 560px; }
        
        .body .columns td.one,
        .body .column td.one { width: 8.333333%; }
        .body .columns td.two,
        .body .column td.two { width: 16.666666%; }
        .body .columns td.three,
        .body .column td.three { width: 25%; }
        .body .columns td.four,
        .body .column td.four { width: 33.333333%; }
        .body .columns td.five,
        .body .column td.five { width: 41.666666%; }
        .body .columns td.six,
        .body .column td.six { width: 50%; }
        .body .columns td.seven,
        .body .column td.seven { width: 58.333333%; }
        .body .columns td.eight,
        .body .column td.eight { width: 66.666666%; }
        .body .columns td.nine,
        .body .column td.nine { width: 75%; }
        .body .columns td.ten,
        .body .column td.ten { width: 83.333333%; }
        .body .columns td.eleven,
        .body .column td.eleven { width: 91.666666%; }
        .body .columns td.twelve,
        .body .column td.twelve { width: 100%; }
        
        td.offset-by-one { padding-left: 50px; }
        td.offset-by-two { padding-left: 100px; }
        td.offset-by-three { padding-left: 150px; }
        td.offset-by-four { padding-left: 200px; }
        td.offset-by-five { padding-left: 250px; }
        td.offset-by-six { padding-left: 300px; }
        td.offset-by-seven { padding-left: 350px; }
        td.offset-by-eight { padding-left: 400px; }
        td.offset-by-nine { padding-left: 450px; }
        td.offset-by-ten { padding-left: 500px; }
        td.offset-by-eleven { padding-left: 550px; }
        
        td.expander {
          visibility: hidden;
          width: 0px;
          padding: 0 !important;
        }
        td.expander-logo {
		   vertical-align: middle;
        }
        td.expander-logo h2{
          color:#348fe2;
        }
        table.columns .text-pad,
        table.column .text-pad {
          padding-left: 10px;
          padding-right: 10px;
        }
        
        table.columns .left-text-pad,
        table.columns .text-pad-left,
        table.column .left-text-pad,
        table.column .text-pad-left {
          padding-left: 10px;
        }
        
        table.columns .right-text-pad,
        table.columns .text-pad-right,
        table.column .right-text-pad,
        table.column .text-pad-right {
          padding-right: 10px;
        }
        
        /* Block Grid */
        
        .block-grid {
          width: 100%;
          max-width: 580px;
        }
        
        .block-grid td {
          display: inline-block;
          padding:10px;
        }
        
        .two-up td {
          width:270px;
        }
        
        .three-up td {
          width:173px;
        }
        
        .four-up td {
          width:125px;
        }
        
        .five-up td {
          width:96px;
        }
        
        .six-up td {
          width:76px;
        }
        
        .seven-up td {
          width:62px;
        }
        
        .eight-up td {
          width:52px;
        }
        
        /* Alignment & Visibility Classes */
        
        table.center, td.center {
          text-align: center;
        }
        
        h1.center,
        h2.center,
        h3.center,
        h4.center,
        h5.center,
        h6.center {
          text-align: center;
        }
        
        span.center {
          display: block;
          width: 100%;
          text-align: center;
        }
        
        img.center {
          margin: 0 auto;
          float: none;
        }
        
        .show-for-small,
        .hide-for-desktop {
          display: none;
        }
        
        /* Typography */
        
        body, table.body, h1, h2, h3, h4, h5, h6, p, td { 
          color: #222222;
          font-family: "Helvetica", "Arial", sans-serif; 
          font-weight: normal; 
          padding:0; 
          margin: 0;
          text-align: left; 
          line-height: 1.3;
        }
        
        h1, h2, h3, h4, h5, h6 {
          word-break: normal;
        }
        
        h1 {font-size: 40px;}
        h2 {font-size: 36px;}
        h3 {font-size: 32px;}
        h4 {font-size: 28px;}
        h5 {font-size: 24px;}
        h6 {font-size: 20px;}
        body, table.body, p, td {font-size: 14px;line-height:19px;}
        
        p.lead, p.lede, p.leed {
          font-size: 18px;
          line-height:21px;
        }
        
        p { 
          margin-bottom: 10px;
        }
        
        small {
          font-size: 10px;
        }
        
        a {
          color: #4b9ee8; 
          text-decoration: none;
        }
        
        a:hover { 
          color: #fff !important;
        }
        
        a:active { 
          color: #4b9ee8 !important;
        }
        
        a:visited { 
          color: #4b9ee8 !important;
        }
        
        h1 a, 
        h2 a, 
        h3 a, 
        h4 a, 
        h5 a, 
        h6 a {
          color: #2ba6cb;
        }
        
        h1 a:active, 
        h2 a:active,  
        h3 a:active, 
        h4 a:active, 
        h5 a:active, 
        h6 a:active { 
          color: #2ba6cb !important; 
        } 
        
        h1 a:visited, 
        h2 a:visited,  
        h3 a:visited, 
        h4 a:visited, 
        h5 a:visited, 
        h6 a:visited { 
          color: #2ba6cb !important; 
        } 
        .info li em{
		color:#f59c1a;
		}
		.info-danger {
		color:#ff5b57;
		font-weight:bold;
		}
		.text-center {
		text-align:center;
		}
        /* Panels */
        
        .panel {
          background: #f2f2f2;
          border: 1px solid #d9d9d9;
          padding: 10px !important;
        }
        
        .sub-grid table {
          width: 100%;
        }
        
        .sub-grid td.sub-columns {
          padding-bottom: 0;
        }
        
        /* Buttons */
        
        table.button,
        table.tiny-button,
        table.small-button,
        table.medium-button,
        table.large-button {
          width: 100%;
          overflow: hidden;
        }
        
        table.button td,
        table.tiny-button td,
        table.small-button td,
        table.medium-button td,
        table.large-button td {
          display: block;
          width: auto !important;
          text-align: center;
          background: #2ba6cb;
          border: 1px solid #2284a1;
          color: #ffffff;
          padding: 8px 0;
        }
        
        table.tiny-button td {
          padding: 5px 0 4px;
        }
        
        table.small-button td {
          padding: 8px 0 7px;
        }
        
        table.medium-button td {
          padding: 12px 0 10px;
        }
        
        table.large-button td {
          padding: 21px 0 18px;
        }
        
        table.button td a,
        table.tiny-button td a,
        table.small-button td a,
        table.medium-button td a,
        table.large-button td a {
          font-weight: bold;
          text-decoration: none;
          font-family: Helvetica, Arial, sans-serif;
          color: #ffffff;
          font-size: 16px;
        }
        
        table.tiny-button td a {
          font-size: 12px;
          font-weight: normal;
        }
        
        table.small-button td a {
          font-size: 16px;
        }
        
        table.medium-button td a {
          font-size: 20px;
        }
        
        table.large-button td a {
          font-size: 24px;
        }
        
        table.button:hover td,
        table.button:visited td,
        table.button:active td {
          background: #2795b6 !important;
        }
        
        table.button:hover td a,
        table.button:visited td a,
        table.button:active td a {
          color: #fff !important;
        }
        
        table.button:hover td,
        table.tiny-button:hover td,
        table.small-button:hover td,
        table.medium-button:hover td,
        table.large-button:hover td {
          background: #2795b6 !important;
        }
        
        table.button:hover td a,
        table.button:active td a,
        table.button td a:visited,
        table.tiny-button:hover td a,
        table.tiny-button:active td a,
        table.tiny-button td a:visited,
        table.small-button:hover td a,
        table.small-button:active td a,
        table.small-button td a:visited,
        table.medium-button:hover td a,
        table.medium-button:active td a,
        table.medium-button td a:visited,
        table.large-button:hover td a,
        table.large-button:active td a,
        table.large-button td a:visited {
          color: #ffffff !important; 
        }
        
        table.secondary td {
          background: #e9e9e9;
          border-color: #d0d0d0;
          color: #555;
        }
        
        table.secondary td a {
          color: #555;
        }
        
        table.secondary:hover td {
          background: #d0d0d0 !important;
          color: #555;
        }
        
        table.secondary:hover td a,
        table.secondary td a:visited,
        table.secondary:active td a {
          color: #555 !important;
        }
        
        table.success td {
          background: #5da423;
          border-color: #457a1a;
        }
        
        table.success:hover td {
          background: #457a1a !important;
        }
        
        table.alert td {
          background: #c60f13;
          border-color: #970b0e;
        }
        
        table.alert:hover td {
          background: #970b0e !important;
        }
        
        table.radius td {
          -webkit-border-radius: 3px;
          -moz-border-radius: 3px;
          border-radius: 3px;
        }
        
        table.round td {
          -webkit-border-radius: 500px;
          -moz-border-radius: 500px;
          border-radius: 500px;
        }
        
        /* Outlook First */
        
        body.outlook p {
          display: inline !important;
        }
        
        /*  Media Queries */
        
        @media only screen and (max-width: 600px) {
        
          table[class="body"] img {
            width: auto !important;
            height: auto !important;
          }
        
          table[class="body"] center {
            min-width: 0 !important;
          }
        
          table[class="body"] .container {
            width: 95% !important;
          }
        
          table[class="body"] .row {
            width: 100% !important;
            display: block !important;
          }
        
          table[class="body"] .wrapper {
            display: block !important;
            padding-right: 0 !important;
          }
        
          table[class="body"] .columns,
          table[class="body"] .column {
            table-layout: fixed !important;
            float: none !important;
            width: 100% !important;
            padding-right: 0px !important;
            padding-left: 0px !important;
            display: block !important;
          }
        
          table[class="body"] .wrapper.first .columns,
          table[class="body"] .wrapper.first .column {
            display: table !important;
          }
        
          table[class="body"] table.columns td,
          table[class="body"] table.column td {
            width: 100% !important;
          }
        
          table[class="body"] .columns td.one,
          table[class="body"] .column td.one { width: 8.333333% !important; }
          table[class="body"] .columns td.two,
          table[class="body"] .column td.two { width: 16.666666% !important; }
          table[class="body"] .columns td.three,
          table[class="body"] .column td.three { width: 25% !important; }
          table[class="body"] .columns td.four,
          table[class="body"] .column td.four { width: 33.333333% !important; }
          table[class="body"] .columns td.five,
          table[class="body"] .column td.five { width: 41.666666% !important; }
          table[class="body"] .columns td.six,
          table[class="body"] .column td.six { width: 50% !important; }
          table[class="body"] .columns td.seven,
          table[class="body"] .column td.seven { width: 58.333333% !important; }
          table[class="body"] .columns td.eight,
          table[class="body"] .column td.eight { width: 66.666666% !important; }
          table[class="body"] .columns td.nine,
          table[class="body"] .column td.nine { width: 75% !important; }
          table[class="body"] .columns td.ten,
          table[class="body"] .column td.ten { width: 83.333333% !important; }
          table[class="body"] .columns td.eleven,
          table[class="body"] .column td.eleven { width: 91.666666% !important; }
          table[class="body"] .columns td.twelve,
          table[class="body"] .column td.twelve { width: 100% !important; }
        
          table[class="body"] td.offset-by-one,
          table[class="body"] td.offset-by-two,
          table[class="body"] td.offset-by-three,
          table[class="body"] td.offset-by-four,
          table[class="body"] td.offset-by-five,
          table[class="body"] td.offset-by-six,
          table[class="body"] td.offset-by-seven,
          table[class="body"] td.offset-by-eight,
          table[class="body"] td.offset-by-nine,
          table[class="body"] td.offset-by-ten,
          table[class="body"] td.offset-by-eleven {
            padding-left: 0 !important;
          }
        
          table[class="body"] table.columns td.expander {
            width: 1px !important;
          }
        
          table[class="body"] .right-text-pad,
          table[class="body"] .text-pad-right {
            padding-left: 10px !important;
          }
        
          table[class="body"] .left-text-pad,
          table[class="body"] .text-pad-left {
            padding-right: 10px !important;
          }
        
          table[class="body"] .hide-for-small,
          table[class="body"] .show-for-desktop {
            display: none !important;
          }
        
          table[class="body"] .show-for-small,
          table[class="body"] .hide-for-desktop {
            display: inherit !important;
          }
        }
	</style>
	<style type="text/css">
	    /********************************
        * CUSTOM STYLING - SYSTEM EMAIL *
        ********************************/
	    body {
	        background: #d9e0e7;
	    }
	    a:hover,
	    a:focus {
	        text-decoration: underline;
	    }
	    
	    /* Typography */
	    
	    body, table.body, p, td {
	        font-size: 12px;
	    }
	    h1, h2, h3, h4 {
	        margin: 5px 0 10px;
	    }
	    h5, h6 {
	        margin: 5px 0;
	    }
	    
	    h1 { font-size: 36px; }
	    h2 { font-size: 30px; }
	    h3 { font-size: 24px; }
	    h4 { font-size: 18px; }
	    h5 { font-size: 14px; }
	    h6 { font-size: 12px; }
	    
	    /* Predefined Class */
	    
	    h1.last,
	    h2.last,
	    h3.last,
	    h4.last,
	    h5.last,
	    h6.last,
	    p.last {
	        margin-bottom: 0;
	    }
	    td.last {
	        padding-bottom: 0 !important;
	    }
	    td.wrapper {
	        padding: 15px 15px 0 15px;
	    }
		td.wrapper-logo {
			padding: 5px 15px 0 15px;
		}
	    table.columns td, table.column td {
	        padding: 0 0 15px 0;
	    }
	    table.columns-wrapper-logo td, table.column-wrapper-logo td {
	        padding: 0 15px 5px 0;
	    }
	    .header,
	    .footer {
	        background: #ffffff;
	    }
	    .content.dark-theme {
	        background: #2d353c;
	    }
	    .content.dark-theme .panel {
	        background: #fff2e3;
	        border: none;
	    }
	    .content.dark-theme h1, 
	    .content.dark-theme h2,
	    .content.dark-theme h3, 
	    .content.dark-theme h4, 
	    .content.dark-theme h5, 
	    .content.dark-theme h6,
	    .content.dark-theme .highlight {
	        color: #4b9ee8 !important;
	    } 
	    .content.dark-theme a, a {
	        color: #4b9ee8;;
	    }
	    .content.dark-theme p, 
	    .content.dark-theme td {
	        color: #a8acb1 !important;
	    }
	    .divider {
	        height: 1px;
	        width: 100%;
	        background: #000;
	        margin-top: 5px;
	    }
	    .text-right {
	        text-align: right;
	    }
	    .valign-middle {
	        vertical-align: middle;
	    }
	    .m-t-0 { margin-top: 0px !important; }
	    .m-t-5 { margin-top: 5px !important; }
	    .m-t-10 { margin-top: 10px !important; }
	    .m-t-15 { margin-top: 15px !important; }
	    .m-b-0 { margin-bottom: 0px !important; }
	    .m-b-5 { margin-bottom: 5px !important; }
	    .m-b-10 { margin-bottom: 10px !important; }
	    .m-b-15 { margin-bottom: 15px !important; }
	    .p-t-0 { padding-top: 0px !important; }
	    .p-t-5 { padding-top: 5px !important; }
	    .p-t-10 { padding-top: 10px !important; }
	    .p-t-15 { padding-top: 15px !important; }
	    
	    /* Button */
	    .btn a,
	    .button a {
	        color: #fff !important;
	        font-weight: normal !important;
	        text-decoration: none !important;
	    }
	    table.btn td,
	    table.button td {
	        vertical-align: middle !important;
	        padding: 6px 18px !important;
	        background: #00acac !important;
	        border-color: #00acac !important;
	    }
	    table.btn:hover td, 
	    table.button:hover td,
	    table.btn:visited td, 
	    table.button:visited td,
	    table.btn:active td,
	    table.button:active td {
	        background: #008a8a !important;
	        border-color: #008a8a !important;
	    }
	    
	    /* Button Orange */
	    
	    table.btn.orange td,
	    table.button.orange td {
	        background: #f59c1a !important;
	        border-color: #f59c1a !important;
	    }
	    table.btn.orange:hover td, 
	    table.button.orange:hover td,
	    table.btn.orange:visited td, 
	    table.button.orange:visited td,
	    table.btn.orange:active td,
	    table.button.orange:active td {
	        background: #c47d15 !important;
	        border-color: #c47d15 !important;
	    }
	    
	    /* Button Blue */
	    
	    table.btn.blue td,
	    table.button.blue td {
	        background: #348fe2 !important;
	        border-color: #348fe2 !important;
	    }
	    table.btn.blue:hover td, 
	    table.button.blue:hover td,
	    table.btn.blue:visited td, 
	    table.button.blue:visited td,
	    table.btn.blue:active td,
	    table.button.blue:active td {
	        background: #2a72b5 !important;
	        border-color: #2a72b5 !important;
	    }
		
		table.btn.blue p{
		color: #fff !important;
		text-align: center;
		}
		
	    /* Button Blue_ats */
	    
	    table.btn.blue_ats td,
	    table.button.blue_ats td {
	        background: #348fe2 !important;
	        border-color: #348fe2 !important;
	    }
		
		table.btn.blue_ats p{
		color: #fff !important;
		text-align: center;
		}
		table.btn h4{
		color: #fff !important;
		font-size: 18px;
		}
		
	    /* Button Red */
	    
	    table.btn.red td,
	    table.button.red td {
	        background: #ff5b57 !important;
	        border-color: #ff5b57 !important;
	    }
	    table.btn.red:hover td, 
	    table.button.red:hover td,
	    table.btn.red:visited td, 
	    table.button.red:visited td,
	    table.btn.red:active td,
	    table.button.red:active td {
	        background: #cc4946 !important;
	        border-color: #cc4946 !important;
	    }
	    
	    /* Button White */
	    
	    table.btn.white td a,
	    table.button.white td a {
	        color: #333 !important;
	    }
	    table.btn.white td,
	    table.button.white td {
	        background: #ffffff !important;
	        border-color: #ffffff !important;
	    }
	    table.btn.white:hover td, 
	    table.button.white:hover td,
	    table.btn.white:visited td, 
	    table.button.white:visited td,
	    table.btn.white:active td,
	    table.button.white:active td {
	        background: #e2e7eb !important;
	        border-color: #e2e7eb !important;
	    }
	    
	    /* Button Grey */
	    
	    table.btn.grey td,
	    table.button.grey td {
	        background: #348fe2 !important;
	        border-color: #348fe2 !important;
	    }
	    table.btn.grey:hover td, 
	    table.button.grey:hover td,
	    table.btn.grey:visited td, 
	    table.button.grey:visited td,
	    table.btn.grey:active td,
	    table.button.grey:active td {
	        background: #929ba1 !important;
	        border-color: #929ba1 !important;
	    }
	    
		table.btn.orange p{
		color: #fff !important;
		text-align: center;
		}
		
	    @media only screen and (max-width: 600px) {
	        .body .container.content {
	            width: 100% !important;
	        }
	        table[class="body"] .wrapper {
	            padding-right: 15px !important;
	        }
	        .text-right {
	            text-align: left !important;
	        }
	    }
	</style>
</head>';

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
													
                                                        <a href="http://www.pointcourse.com"><img src="cid:LOGO-PC" width="40px" height="50px"/></a>
                                                    </td>
                                                    <td class="expander-logo"><h2>Point Course</h2></td>
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
                                                        <li><strong>Nom :</strong> <em>'.$donnees[0]['nomInternaute'].'</em></li>
														<li><strong>Prénom :</strong> <em>'.$donnees[0]['prenomInternaute'].'</li>
                                                        <li><strong>Date de Naissance :</strong>  <em>'.dateen2fr($donnees[0]['naissanceInternaute'],1).'</em> </li>
                                                        <li><strong>Adresse :</strong> <em>'.$donnees[0]['adresseInternaute'].'</em></li>
                                                        <li><strong>Code postal :</strong> <em>'.$donnees[0]['cpInternaute'].'</em></li>
														<li><strong>Ville :</strong> <em>'.$donnees[0]['villeInternaute'].'</em></li>
														<li><strong>Pays :</strong> <em>'.$donnees[0]['paysInternaute'].'</em></li>
														<li><strong>Email :</strong> <em>'.$donnees[0]['emailInternaute'].'</em></li>

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
														<li><strong>Catégorie :</strong>  <em>'.$cat_code.'</em></li>
                                                        <li><strong>Club :</strong> <em>'.$donnees[0]['clubInternaute'].'</em></li>
														<li><strong>Certificat Médical ou licence :</strong> <em class="info-danger" style="color:'.$color_certif.'">'.$certif.'</em></li>
														<li><strong>Autorisation parentale :</strong> <em class="info-danger" style="color:'.$color_autop.'">'.$autop.'</em></li>
														<li><strong>Mode de Paiement :</strong> <em>'.$donnees[0]['mode_paiement'].'</em></li>
														<li><strong>Montant de l\'inscription :</strong> <em>'.$cout_inscription.' € </em></li>
														<li><strong>Montant des participations : </strong> <em>'.$total_participation.'</em></li>
														'.$aff_frais_coureur.'
														<li><strong>Total de votre engagement : </strong> <em><strong>'.$somme_totale_des_inscriptions.$aff_gratuit.'</strong></em></li>
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
																	<a href="'.$lien_inscrits.'"><b>Voir la liste des inscrits</b></a>
																</td>
															</tr>
														</tbody>
													</table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="last">
                                                    <p class="m-b-5">Vous pouvez éditer vos informations (ajout certificat médical / effectuer un paiement etc ...) en cliquant sur le lien ci-dessous : </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="">
													<table class="btn orange" align="center">
                                                        <tbody>
															<tr>
																<td>
																	<a href="'.$lien_edition.'"><b>Editer mon inscription</b></a>
																</td>
															</tr>
														</tbody>
													</table>
                                                </td>
                                            </tr>
											
											<table align="center">				
															<tr >
																<td>
																	<p><h5>Code de sécurité : <strong><em class="info-danger">'.$codeSecurite.'</em></strong></h5></p>
																</td>
															</tr>
											</table>
											<table align="center">				
															<tr >
																<td>
																	<p><i>Ceci est un mail générique - ne pas y répondre</i></p>
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
                                                        &copy; Point Course 2016 - Powered by ATS-SPORT.
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
if ($donnees[0]['resendmail'] != 1 ){
	return $css.$entete_email.$corps.$corps2.$corps3;
}
else
{
	return $css.$entete_email.$pieces_manquante.$corps2.$corps3;
}

}
?>