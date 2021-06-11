<?php 

session_start();
	require_once('connect_db.php');
	connect_db();

		
		//if(isset($_SESSION['log_root']))
		//{
			if ( $_GET['type'] == 'ville' ) {
				$term = $_GET['term'];
				$term = str_replace(" ","-",$_GET['term']);
				//$query  = "SELECT ville_nom_reel, ville_code_postal, ville_departement, ville_latitude_deg,ville_longitude_deg  FROM `villes_france_free` "; 
				$query  = "SELECT ville_nom_reel, ville_code_postal, ville_departement FROM `villes_france_free` "; 
				$query .= "WHERE ville_nom_reel like '".$term."%' ";
			
				$result = $mysqli->query($query);
				$ville_row = array();
				$ville = array();
				$cpt = 0;
				while($row = mysqli_fetch_array($result))
				{
					
					$ville_row['value'] = $row['ville_nom_reel'];
					if (isset($_GET['rt'])) {
						$ville_row['id'] = substr($row['ville_code_postal'],0,5);
						$ville_row['label'] = $row['ville_nom_reel']." ( ".substr($row['ville_code_postal'],0,5)." )";
					} else {
						$ville_row['id'] = $row['ville_code_postal']; 
						$ville_row['label'] = $row['ville_nom_reel'];
						//$ville_row['ville_latitude_deg'] = mb_convert_encoding($row['ville_latitude_deg'], "UTF-8", "Windows-1252");
						//$ville_row['ville_longitude_deg'] = mb_convert_encoding($row['ville_longitude_deg'], "UTF-8", "Windows-1252");
					}
					$cpt++; 
					array_push($ville, $ville_row);
				}
				if($cpt == 0) {
					$ville_row['label'] = 'Pas de proposition - Champ libre';
					$ville_row['value'] = '';
					$ville_row['id'] = '0';
					array_push($ville, $ville_row);
				}
				//$data = array('ville'=>$ville);
				$json = json_encode($ville); 
			}
			elseif ( $_GET['type'] == 'cp' ) {
				$term = $_GET['term'];
				$term = str_replace(" ","-",$_GET['term']);
				//$query  = "SELECT ville_nom_reel, ville_code_postal, ville_departement, ville_latitude_deg,ville_longitude_deg  FROM `villes_france_free` "; 
				$query  = "SELECT ville_nom_reel, ville_code_postal, ville_departement FROM `villes_france_free` "; 
				$query .= "WHERE ville_code_postal like '".$term."%' ";
			
				$result = $mysqli->query($query);
				$ville_row = array();
				$ville = array();
				$cpt = 0;
				while($row = mysqli_fetch_array($result))
				{
					
					$ville_row['value'] = $row['ville_nom_reel'];
					if (isset($_GET['rt'])) {
						$ville_row['id'] = substr($row['ville_code_postal'],0,5);
						$ville_row['label'] = $row['ville_nom_reel']." ( ".substr($row['ville_code_postal'],0,5)." )";
					} else {
						$ville_row['id'] = $row['ville_code_postal']; 
						$ville_row['label'] = $row['ville_nom_reel'];
						//$ville_row['ville_latitude_deg'] = mb_convert_encoding($row['ville_latitude_deg'], "UTF-8", "Windows-1252");
						//$ville_row['ville_longitude_deg'] = mb_convert_encoding($row['ville_longitude_deg'], "UTF-8", "Windows-1252");
					}
					$cpt++; 
					array_push($ville, $ville_row);
				}
				if($cpt == 0) {
					$ville_row['label'] = 'Pas de proposition - Champ libre';
					$ville_row['value'] = '';
					$ville_row['id'] = '0';
					array_push($ville, $ville_row);
				}
				//$data = array('ville'=>$ville);
				$json = json_encode($ville); 
			}
			elseif ( $_GET['type'] == 'club' )
			{
				$term = $_GET['term'];
				$query  = "SELECT nomclub FROM r_club_internaute ";
				$query .= "WHERE nomclub like '".$term."%' GROUP BY nomclub";
				$result = $mysqli->query($query);
				$club_row = array();
				$club = array();
				$cpt = 0;
				while($row = mysqli_fetch_array($result))
				{
					$club_row['value'] = $row['nomclub'];
					$cpt++;
					array_push($club, $club_row);
				}
				if($cpt == 0) {
					$club_row['label'] = 'Pas de proposition - Champ libre';
					$club_row['value'] = '';
					$club_row['id'] = '0';
					array_push($club, $club_row);
				}
				$json = json_encode($club);
			}
			
			else
			{
				$term = $_GET['term'];
				$query  = "SELECT nom_fr_fr FROM `pays` ";
				$query .= "WHERE nom_fr_fr like '".$term."%'";
				$result = $mysqli->query($query);
				$pays_row = array();
				$pays = array();
				$cpt = 0;
				while($row = mysqli_fetch_array($result))
				{
					$pays_row['value'] = $row['nom_fr_fr'];
					$cpt++;
					array_push($pays, $pays_row);
				}
				if($cpt == 0) {
					$pays_row['label'] = 'Pas de proposition - Champ libre';
					$pays_row['value'] = '';
					$pays_row['id'] = '0';
					array_push($pays, $pays_row);
				}
				$json = json_encode($pays);
			}
			print_r($json);
			
			//echo json_encode($ville);
		//}
?>