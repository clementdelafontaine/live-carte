<?php

require_once("includes.php");
require_once('functions.php');
require_once ('/var/www/vhosts/ats-sport.com/httpdocs/assets/plugins/phpMailer/PHPMailerAutoload.php');

function send_mail ($expediteur_email,$expediteur_nom,$destinataire_email, $destinataire_nom, $sujet,$corps) {
global $mysqli;

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
	//$mail->AddBCC("webmaster@ats-sport.com","webmaster@ats-sport.com");
	//$mail->AddBCC("webmaster@ats-sport.com","webmaster@ats-sport.com");
	//Set the subject line
	$mail->Subject =  $sujet;
	
	$mail->AddEmbeddedImage('../assets/img/logoATS-bleu2-small.png', 'LOGO-PC');
	//Read an HTML message body from an external file, convert referenced images to embedded,
	//convert HTML into a basic plain-text alternative body
	//$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
	$mail->msgHTML($corps);
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
	
$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport webmaster','webmaster@ats-sport.com', 'webmaster', $_POST['objet'],$_POST['corps']);	
//$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			

$aff= array('comeback'=>'OK');
echo json_encode($aff);	

?>