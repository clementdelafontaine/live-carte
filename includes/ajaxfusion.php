<?php 

require_once('includes.php');

require_once('functions.php');

require_once("functions_mail_n.php"); //Retiré pour la version locale

global $mysqli;
$id_fusion = md5(uniqid());
$id = $_SESSION["log_id"];
$nonce_valide = false;
    
	if (isset($_POST['nonce'])) {   // le nonce était, ici, stocké dans un champ caché du formulaire

        $valeur = $_POST['nonce'];

        $nonce_valide = Cri_Nonce:: verifier_nonce($valeur, 'enregistrer', $_SESSION['unique_id_session'], $mysqli);
		//echo "xxxx:".$nonce_valide;

    }
    if (empty($nonce_valide)) {

        //echo " pas bon";
		echo json_encode(array('fusion' =>'non'));
		exit();

    }


//$query_up = "UPDATE r_internaute SET id_fusion = '".$id_fusion."' WHERE idInternaute = ".$_SESSION["log_id"];
$query_up = "UPDATE r_internaute SET id_fusion = '".$id_fusion."' WHERE idInternaute = ".$id;
$mysqli->query($query_up);
								
								$donnees = array();
								$row_info = array();
								
								//$row_info = info_internaute_send_mail ($reference,(($_GET['Montant']/100)),'CB');
								//$row_info['emailInternaute']= 'jf@chauveau.nom.fr';
								$row_info['emailInternaute']= $_SESSION["emailInternaute"];
								$row_info['nomInternaute']= $_SESSION["nomInternaute"];
								$row_info['prenomInternaute']= $_SESSION["prenomInternaute"];
								$row_info['naissanceInternaute'] = dateen2fr($_SESSION["naissanceInternaute"]);
								$row_info['login'] = $_SESSION["log_log"];
								$row_info['idInternaute'] = $_SESSION["log_id"];
								$row_info['id_fusion'] = $id_fusion;
								$row_info['fusion']= 1;
								array_push($donnees,$row_info);
								//print_r($donnees);
								$sujet = "ATS SPORT - Une demande de fusion de résultats à été demandée";
								$mail_send = send_mail ('webmaster@ats-sport.com','Ats Sport Inscription',$row_info['emailInternaute'], $row_info['nomInternaute']." ".$row_info['prenomInternaute'], $sujet,$donnees);			
								echo json_encode(array('fusion' =>'oui'));
							
							//mailing
							//***inscription_mailing($id_internaute,$type_certificat_bdd['idTypeEpreuve']);


?>
