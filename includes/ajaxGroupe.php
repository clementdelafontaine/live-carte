<?php 
	session_start();
	require_once("includes.php");
	require_once("functions.php");
	require_once('connect_db.php');
	connect_db();
	global $mysqli;
	if ($_POST['action']==1)
	{
		unset($_SESSION['idEpreuvePersoPre'],$_SESSION['groupe'],$_SESSION['paiement_indiv'],$_SESSION['groupe_mdp']);
		$json = array('etat' =>'DEL');
		
	}
	elseif ($_POST['action']==5)
	{
			//echo "xxx : ".$_POST['patronymeGroupe'];
			$nomGroupe = $_POST['nomGroupe'];
			$patronymeGroupe = $_POST['patronymeGroupe'];
			$emailGroupe = $_POST['emailGroupe'];
			$telGroupe = $_POST['telGroupe'];
			$mdpGroupe = $_POST['mdpGroupe'];
			$mdpGroupeAdmin = $_POST['mdpGroupeAdmin'];
			$id_epreuve = $_POST['idepreuve'];
			
			
			$date_fin_epreuve = $CMPCOD = extract_champ_epreuve('DateFinEpreuve',$id_epreuve);
			
			if ($_POST['active'][$key]==1) $_POST['active'][$key]='oui'; else $_POST['active'][$key]='non';
			if ($_POST['paiement_indiv'][$key]==1) $_POST['paiement_indiv'][$key]='oui'; else $_POST['paiement_indiv'][$key]='non';
			$query_liens  = "INSERT INTO r_epreuveperso_pre  ";
			$query_liens .= "(idEpreuve, groupe, codeActivation, dateDebut,dateFin,active,paiement_indiv,PatronymeResp,	email, telephone,passAdmin) VALUES ";
			$query_liens .= "(".$id_epreuve.", ";
			$query_liens .= "'".addslashes_form_to_sql($nomGroupe)."', ";
			$query_liens .= "'".addslashes_form_to_sql($mdpGroupe)."', ";
			$query_liens .= "NOW(), ";
			$query_liens .= "'".$date_fin_epreuve."', ";
			$query_liens .= "'oui', ";
			$query_liens .= "'oui', ";
			$query_liens .= "'".addslashes_form_to_sql($patronymeGroupe)."', ";
			$query_liens .= "'".addslashes_form_to_sql($emailGroupe)."', ";
			$query_liens .= "'".addslashes_form_to_sql($telGroupe)."', ";
			$query_liens .= "'".addslashes_form_to_sql($mdpGroupeAdmin)."') ";

			//echo $query_liens ;				
			$result = $mysqli->query($query_liens);
			
/*
			//requete a atlas
			//if ($IDAtlas!=null){//On vérifie si la course est bien atlasienne
				//enregistrement dans la base atlas
			$IDAtlas=extract_champ_epreuve('IDAtlas',$id_epreuve);//on récupere le raceID de l'epreuve
			
	$data = array(
			"raceID"=>$IDAtlas,"name"=>$_POST["nomGroupe"],"creationRequestedBy"=>"66763D92-630C-4C0A-AB6F-7E9403F791D8"

		);
		
		$data_string=json_encode($data);
		echo $data_string;
		$ch=curl_init();
		echo $ch;
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

		curl_setopt($ch, CURLOPT_URL, "https://public.sandbox.atlaslivetracking.com/api/Team");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER,
			array(
				'Content-Type:application/json',
				'Content-Length: ' . strlen($data_string)
			)
		);
		//echo $ch;
		
		//curl_setopt($ch, CURLOPT_CAINFO, "cacert.pem");
		//echo $ch;
		//echo"test4000000";
		$response=curl_exec($ch);
//$reponse=curl_getinfo($ch,CURLINFO_HTTP_CODE);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $header_size);
		$body = substr($response, $header_size);
		//echo "body:".$body;
		if (curl_errno($ch)) {
			echo curl_error($ch);
			die();
		}
		//echo $header;
		//echo $body;
		//echo "<script>console.log('Debug Objects: " . $header. "' );</script>";
//echo "reponse:".$reponse;
		//echo $ch;
		//echo "oui";
		curl_close($ch);
		//echo $ch;
		//enregistrement dans la base atlas
		//insertion dans k_AtlasGroupe
			$query_groupe  = "INSERT INTO k_AtlasGroupe  ";
			$query_groupe .= "(IDAtlas, idGroupeAtlas, nomGroupe)VALUES ";
			$query_groupe .= "(".$IDAtlas.", ";
			$query_groupe .= "".$body.", ";
			$query_groupe .= "'".addslashes_form_to_sql($nomGroupe)."') ";
			
			$result_groupe = $mysqli->query($query_groupe);
			//INSERT INTO `k_AtlasGroupe`(`id`, `IDAtlas`, `idGroupeAtlas`, `nomGroupe`) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5])
			//insetion dans k_AtlasGroupe
			//}*/
					$id = $mysqli->insert_id;
		
		
		
			if (isset($id)) {
				
				$query_ep ="SELECT * from r_epreuveperso_pre WHERE idEpreuvePersoPre = ".$id;

				$result_ep = $mysqli->query($query_ep);
				$row_ep=mysqli_fetch_row($result_ep);			
				
				$json = array('etat' =>'OK','groupe' => $row_ep[3], 'mdp_groupe_new' =>$row_ep[4]);
				$_SESSION['idEpreuvePersoPre']=$row_ep[0];
				$_SESSION['groupe']=$row_ep[3];
				$_SESSION['paiement_indiv']=$row_ep[8];
				$_SESSION['mdp_groupe_new']=$row_ep[4];
				
			}
			else
			{
				
				$json = array('etat' =>'KO');
			}	
		
		
		
	}
	else
	{	
		$id = $_POST['id'];
		$value = $_POST['value'];
		
		$query_ep ="SELECT * from r_epreuveperso_pre WHERE codeActivation = '".$value;
		$query_ep .="' AND idEpreuvePersoPre = ".$id;
		$result_ep = $mysqli->query($query_ep);
		$row_ep=mysqli_fetch_row($result_ep);
	
		if ($row_ep!= FALSE) { 
			
			$json = array('etat' =>'OK','groupe' => $row_ep[3]);
			$_SESSION['idEpreuvePersoPre']=$row_ep[0];
			$_SESSION['groupe']=$row_ep[3];
			$_SESSION['paiement_indiv']=$row_ep[8];
		}
		else
		{
			
			$json = array('etat' =>'KO');
		}
	}
	
	echo json_encode($json);
			

?>