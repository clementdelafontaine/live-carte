<?php
echo "test";
//require_once("connect_db.php");
//$mysqli=connect_db();
/*$raceID="3f07c6b1-73a8-47d5-b10d-8a2a142134de";
$ch=curl_init();
curl_setopt($ch, CURLOPT_URL, "https://public.sandbox.atlaslivetracking.com/api/Team/List?raceID=".$raceID."");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_CAINFO, "D:\cacert.pem");
$response=curl_exec($ch);//on recupere toutes les teams qui existent sous forme de tableau
//$reponse=curl_getinfo($ch,CURLINFO_HTTP_CODE);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$header = substr($response, 0, $header_size);
$body = substr($response, $header_size);
$bodyarray=explode(",", $body);//on separe la liste des teams obtenus par , 
/*echo gettype($body);
echo $body;
echo count($bodyarray);
echo $bodyarray[2];
$i=1;
$cpt=0;
$v=2;
$cpt1=0;
$bodyuni=array();
$bodyuniID=array();
while ($v<count($bodyarray)){//on enregistre ces string nécessaires dans une variables pour pouvoir les utiliser
	$bodyuniID[$cpt1]=explode(":",$bodyarray[$v]);//meme procede que le teamname mais pour les teamID
	$v=$v+7;
	$cpt1++;
}
while ($i<count($bodyarray)){
	$bodyuni[$cpt]=explode(":",$bodyarray[$i]);//on va reseparer le premier tableau de string obtenu en separant les string qui continnent ":" uniquement pour les teamName
	$i=$i+7;
	$cpt++;
}
/*echo $bodyuni[0][1];
echo $bodyuni[1][1];
echo count($bodyuni);*/
/*for ($i=1;$i<count($bodyarray);$i+6) {
	$bodyuni=explode(":", $bodyarray[$i]);
}
echo $bodyarray[6];*/
/*?>
<div>

	<form display:block margin-left:auto; margin-right:auto; action="./../inscriptionCourseAtlas.php" method="POST">

		<?php 
		/*<label ><b class="black-text">Nom </b></label>
		<input  type="text" placeholder="Entrer votre nom" name="name" required>

		<label ><b class="black-text">Prenom</b></label>
		<input  type="text" placeholder="Entrer votre prenom" name="prenom" required>

		<label><b >Email</b></label>
		<input  type="email" placeholder="Entrer votre email" name="email" required>
		<br>
		<label><b>Sexe</b></label>
		<SELECT name="sexe" size="1" style="display:block";>


			<OPTION> Homme</OPTION>
			<OPTION> Femme</OPTION>

		</SELECT>	
		<label><b>Voulez-vous intégrer un groupe?</b></label>
		<SELECT name="integreteam" size="1" style="display:block";>
			<option value="">None</option>
		  <?php
		 for ($i=0;$i<count($bodyuni);$i++){
		  	echo '<option value ='.$bodyuniID[$i][1].'>'.$bodyuni[$i][1].'</option>';
		  } 
		  ?>

		</SELECT>	
		?>
		<label><b >Ou creer un groupe?</b></label>
		<input  type="text" placeholder="Team" name="team">
		<br>
		<?php
		/*
		<label><b >Date de naissance</b></label>
		<input  type="date" placeholder="dd/mm/aaaa" name="birthdate" required>
		<br>
		<label ><b class="black-text">Age</b></label>
		<input  type="number" placeholder="Entrez votre age" name="age" required>
		?>
		<input type="submit" id='submit' value='SE CONNECTER' >/*
		</div>*/

	// test des requetes atlas
	/*
	require_once("includes.php");
	require_once("functions_n.php");
	require_once('connect_db.php');
	$row_info = info_internaute_send_mail_test (672676,0,'GRATUIT');
	connect_db();
	global $mysqli;
	$corps=array();
	array_push($corps,$row_info);
	if ($corps[0]["groupe"]!="Aucun"){
			$query  = "SELECT * FROM k_AtlasGroupe WHERE nomGroupe='".$corps[0]['groupe']."' AND IDAtlas='".$corps[0]['IDAtlas']."';";
			$result = $mysqli->query($query);
			$row=mysqli_fetch_assoc($result);
			echo $query;
			echo "groupe".$corps[0]['groupe'];
			echo "row :".$row["idGroupeAtlas"];
			if ($row==null){
			$body=ajoutTeamAtlas($corps);
			$query_groupe  = "INSERT INTO k_AtlasGroupe  ";
			$query_groupe .= "(IDAtlas, idGroupeAtlas, nomGroupe)VALUES ";
			$query_groupe .= "('".$corps[0]["IDAtlas"]."', ";
			$query_groupe .= "".$body.", ";
			$query_groupe .= "'".$corps[0]["groupe"]."') ";
			
			$result_groupe = $mysqli->query($query_groupe);
			}
			$donnees=array();
			array_push($donnees,$row_info);
			$IDAtlas=$donnees[0]['IDAtlas'];
			$query_b = "INSERT INTO `k_Atlas`( `IDAtlas`, `idInternaute`, `idInscriptionInternaute`, `IDGroupeAtlas`, `nomGroupe`) ";
		$query_b .= "VALUES ('".$IDAtlas."',".$corps[0]['idInternaute'].",".$corps[0]['idInscriptionEpreuveInternaute'].",'".$row['idGroupeAtlas']."','".$corps[0]['groupe']."');";
		echo $query_b;
		$result_b = $mysqli->query($query_b);
		$corps[0]['teamID']=$body;
		//ajoutUserAtlas($corps);
			//INSERT INTO `k_AtlasGroupe`(`id`, `IDAtlas`, `idGroupeAtlas`, `nomGroupe`) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5])
			//insetion dans k_AtlasGroupe
		}else{

		$donnees=array();
		array_push($donnees,$row_info);
		$IDAtlas=$donnees[0]['IDAtlas'];
		$query_b = "INSERT INTO `k_Atlas`( `IDAtlas`, `idInternaute`, `idInscriptionInternaute`, `IDGroupeAtlas`, `nomGroupe`) ";
		$query_b .= "VALUES ('".$IDAtlas."',".$donnees[0]['idInternaute'].",".$corps[0]['idInscriptionEpreuveInternaute'].",null,null);";
		$result_b = $mysqli->query($query_b);
		//requete a Atlas
		$corps[0]['teamID']=null;
		ajoutUserAtlas($corps);
	}
		//echo $ch;
		//fin de requete
		//fin de requete
	//echo $_GET["raceID"];
	//echo gettype(intval($_POST["idEpreuve"]));
	//$idEpreuve=intval($_POST["idEpreuve"])
	
	/*$IDAtlas=extract_champ_epreuve('nomEpreuve',7272);//on récupere le raceID de l'epreuve
	echo "id:".$IDAtlas;
		$query  = "SELECT IDAtlas";
		$query .= " FROM r_epreuve";
		$query .=" WHERE idEpreuve = ".$_POST["id_epreuve"]." ";
		$query .= $and;
		$result = $mysqli->query($query);
		$row=mysqli_fetch_row($result);
		echo "requete :".$row[0];
			
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
		echo $ch;
		
		//curl_setopt($ch, CURLOPT_CAINFO, "cacert.pem");
		echo $ch;
		echo"test4000000";
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
		echo $header;
		echo $body;
		//echo "<script>console.log('Debug Objects: " . $header. "' );</script>";
//echo "reponse:".$reponse;
		echo $ch;
		echo "oui";
		curl_close($ch);
		//echo $ch;*/

		//test requete atlas


		//test live
		/*require_once("includes.php");
		require_once('connect_db.php');
		connect_db();
		date_default_timezone_set('Europe/Berlin');
		$date=date ('Y\/m\/d H:i:s');
		$test="nom_2018-03-04_12-00-00-0";
		$string=explode("_", $test);
		//echo "date : ".$string[1];
		$temps=explode("-", $string[2]);
		//echo "heure: ".$temps[0].":".$temps[1].":".$temps[2];
		$vraitemps=$string[1]." ".$temps[0].":".$temps[1].":".$temps[2];
		echo $vraitemps;
		//echo $vraitemps;
		$dossard=$string[0];
		//echo "dossard : ".$dossard;
		$indice=$temps[3];
		//echo "indice :".$indice;
		$idSportLab=105;
		global $mysqli;
		/*$query  = "SELECT * FROM  `chrono_lecteur`";
		$query .=" WHERE idLecteur=".$idSportLab."";
		$query .=" AND date_min < '".$vraitemps."'
		AND date_max > '".$vraitemps."'";
		echo "avant query";
		$query_participant="INSERT INTO `k_classement_reduit`( `dossard`, `tempsUnix`)";
          $query_participant.=" VALUES ";
          $query_participant.="('1','41541554')";
          $result = $mysqli->query($query_participant);
		$data=mysqli_fetch_array($result);
		$filename="127-05464-+094-789";
		$dossard=01;
		$indice=0;
		$sportLab=105;
		require_once("includes.php");
		require_once('connect_db.php');
		connect_db();
			$query_lieu="SELECT e.nomEpreuve,e.idEpreuve ,C.lieu";
	$query_lieu.=" FROM r_epreuve AS e, chrono_lecteur AS C";
	$query_lieu.=" WHERE C.idEpreuve = e.idEpreuve";
	$query_lieu .= "AND e.live='oui'";
	echo $query_lieu;
	$result_lieu = $mysqli->query($query_lieu);
	while( $row_lieu=mysqli_fetch_array($result_lieu) ){
								echo "epreuve :".$row_lieu['idEpreuve']." & lieu".$row_lieu['lieu'];
							}
					
$query_lieu2="SELECT count(lieu) AS nblieu ";
$query_lieu2.="FROM chrono_lecteur ";
$query_lieu2.=" WHERE lieu='Arrivée'";
$query_lieu2.="AND idEpreuve= 4792";
$result_lieu2=$mysqli->query($query_lieu2);
$data_lieu2=mysqli_fetch_array($result_lieu2);
echo $data_lieu2['nblieu'];
		//$idEpreuve=$data["idEpreuve"];
		//echo "idEpreuve :".$idEpreuve;
		/*$query_b = "INSERT INTO `k_photo`( `nomPhoto`, `idEpreuve`, `dossard`, `indicePhoto`, `date`,`idSportLab`) ";
                $query_b .= "VALUES ('".$filename."',0,'".$dossard."','".$indice."','".$vraitemps."','".$sportLab."');";
                $result_b = $mysqli->query($query_b);
                echo $query_b;*/
                require_once("includes/includes.php");
				require_once('includes/connect_db.php');
				connect_db();
                $query_count="SELECT COUNT( * ) AS nbPhotos ";
                $query_count.="FROM k_photo ";
                $query_count.="WHERE idEpreuve = 4792";
                $result_count = $mysqli->query($query_count);
                $data_count=mysqli_fetch_array($result_count);
                echo $query_count;
                echo $data_count['nbPhotos'];
                if ($data_count['nbPhotos']>0){
                	echo "entré if";
                    include("./creationHTML.php?idEpreuve=4792");
                    echo "require numeral effectue";
                }else{
                	 require("creationHTML.php?idEpreuve=4792");
                	 echo "require effectué";
                }

		?>