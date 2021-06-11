<?php 


/*
####################################################################################################

- Crée le 01/10/2014 par Hugo : refactoring numérotation automatique

####################################################################################################
*/
//error_reporting(E_ALL & ~E_STRICT);

function numerotation($parcours,$epreuve,$iei,$idInscriptionEpreuveInternaute_parent=0)
{
	echo "idInscriptionEpreuveInternaute_parent : ".$idInscriptionEpreuveInternaute_parent."\n";
	$dossard_equipe = extract_champ_parcours('dossard_equipe',$parcours);
	//echo "dossard equipe: ".$dossard_equipe;
	$dossard_affecte = array();
								
	if ($dossard_equipe=='non') {	
	$query  = "SELECT dossard FROM r_inscriptionepreuveinternaute 
			   WHERE idEpreuveParcours = ".$parcours." AND paiement_type NOT IN('SUPPRESSION') ORDER BY dossard ASC";	
	//echo $query ;
	}
	else {
		$query = "SELECT * FROM r_inscriptionepreuveinternaute as riei
				INNER JOIN r_internautereferent AS rir ON riei.idInternaute = rir.idInternauteref
				INNER JOIN r_insc_internautereferent AS riir ON rir.idInternauteReferent = riir.idInternauteReferent
               WHERE riei.idEpreuveParcours = ".$parcours."
               AND riei.paiement_type NOT IN('SUPPRESSION') ORDER BY riei.dossard ASC";
  
		if ($idInscriptionEpreuveInternaute_parent==0) 
		{
				$query  = "SELECT dossard FROM r_inscriptionepreuveinternaute 
			   WHERE idEpreuveParcours = ".$parcours." AND paiement_type NOT IN('SUPPRESSION') ORDER BY dossard ASC";	
		}
		else
		{
			$query = "SELECT dossard FROM r_inscriptionepreuveinternaute WHERE idInscriptionEpreuveInternaute = ".$idInscriptionEpreuveInternaute_parent;
			$result = $mysqli->query($query);
			$row = mysqli_fetch_array($result);
			$query  = "UPDATE r_inscriptionepreuveinternaute SET dossard = ".$row['dossard']." WHERE idEpreuve = ".$epreuve." and idEpreuveParcours = ".$parcours." AND idInscriptionEpreuveInternaute = ".$iei."";
			//echo $query;
			$mysqli->query($query);
			return $row['dossard'];
			exit();			
		}
		//echo $query;
 
			   
	}
	//echo $query;
	$result = $mysqli->query($query);
	while($row = mysqli_fetch_array($result)) array_push($dossard_affecte,intval($row['dossard']));
							
	//Récupération du 1er dossard à attribuer sur le parcours
	$query = "SELECT dossardDeb, dossardFin, nbexclusion, dossards_exclus FROM r_epreuveparcours WHERE idEpreuve = ".$epreuve." and idEpreuveParcours = ".$parcours."";
	//echo $query;
	$result = $mysqli->query($query);
	$borne = mysqli_fetch_array($result);
	$dossardmin = $borne['dossardDeb'];
	$dossardmax = $borne['dossardFin'];	
	$doss = $dossardmin;
	
	$nbexclusion = $borne['nbexclusion'];
	$plage_exclusion = explode(":",$borne['dossards_exclus']);
	for($j=0; $j<$nbexclusion; $j++)
	{
		$exclus = explode("-",$plage_exclusion[$j]);
		for($e=$exclus[0]; $e<=$exclus[1]; $e++)
			array_push($dossard_affecte,intval($e));
	}
	sort($dossard_affecte);
	//print_r($dossard_affecte);				
	if(empty($dossard_affecte))
	{				
		//si la numérotation n'est pas initialisée, on attribue 0
		if($dossardmin == 0) $doss = 0;
		else $doss = $dossardmin;
								
		$query  = "UPDATE r_inscriptionepreuveinternaute SET dossard = ".$doss." WHERE idEpreuve = ".$epreuve." and idEpreuveParcours = ".$parcours." AND idInscriptionEpreuveInternaute = ".$iei."";
		$mysqli->query($query);
	}
	else
	{							
		
		if($dossardmin == 0)
		{
			$query  = "UPDATE r_inscriptionepreuveinternaute SET dossard = 0 WHERE idEpreuve = ".$epreuve." and idEpreuveParcours = ".$parcours." AND idInscriptionEpreuveInternaute = ".$iei."";
			$mysqli->query($query);
		}else{
			
			
			while(in_array($doss,$dossard_affecte)) $doss += 1;
				//echo $doss;					
			//attribution du prochain dossard disponible
			$query  = "UPDATE r_inscriptionepreuveinternaute SET dossard = ".$doss." WHERE idEpreuve = ".$epreuve." and idEpreuveParcours = ".$parcours." AND idInscriptionEpreuveInternaute = ".$iei."";
			$mysqli->query($query);
		}
	}
	return $doss;
}

function numerotation_no_sql($parcours)
{
	$dossard_affecte = array();
	$return = array();
									
	$query  = "SELECT dossard FROM r_inscriptionepreuveinternaute 
			   WHERE idEpreuveParcours = ".$parcours." AND paiement_type NOT IN('SUPPRESSION') ORDER BY dossard ASC";	
																
	$result = $mysqli->query($query);
	while($row = mysqli_fetch_array($result)) array_push($dossard_affecte,intval($row['dossard']));
								
	//Récupération du 1er dossard à attribuer sur le parcours
	$query = "SELECT dossardDeb, dossardFin, nbexclusion, dossards_exclus FROM r_epreuveparcours WHERE idEpreuveParcours = ".$parcours."";
	$result = $mysqli->query($query);
	$borne = mysqli_fetch_array($result);
	$dossardmin = $borne['dossardDeb'];
	$dossardmax = $borne['dossardFin'];
	$doss = $dossardmin;
	
	$nbexclusion = $borne['nbexclusion'];
	$plage_exclusion = explode(":",$borne['dossards_exclus']);
	for($j=0; $j<$nbexclusion; $j++)
	{
		$exclus = explode("-",$plage_exclusion[$j]);
		for($e=$exclus[0]; $e<=$exclus[1]; $e++)
			array_push($dossard_affecte,intval($e));
	}
	sort($dossard_affecte);
							
	if(empty($dossard_affecte)) $doss = $dossardmin;
	else while(in_array($doss,$dossard_affecte)) $doss += 1;		

	$return['doss'] = $doss;
	$return['dossardmin'] = $dossardmin;
	$return['dossardmax'] = $dossardmax;
	
	return $return;
}
function numerotation_no_update($parcours,$epreuve,$iei)
{
	$dossard_affecte = array();
								
	$query  = "SELECT dossard FROM r_inscriptionepreuveinternaute 
			   WHERE idEpreuveParcours = ".$parcours." AND paiement_type NOT IN('SUPPRESSION') ORDER BY dossard ASC";	
	//echo $query ;														
	$result = $mysqli->query($query);
	while($row = mysqli_fetch_array($result)) array_push($dossard_affecte,intval($row['dossard']));
							
	//Récupération du 1er dossard à attribuer sur le parcours
	$query = "SELECT dossardDeb, dossardFin, nbexclusion, dossards_exclus FROM r_epreuveparcours WHERE idEpreuve = ".$epreuve." and idEpreuveParcours = ".$parcours."";
	//echo $query;
	$result = $mysqli->query($query);
	$borne = mysqli_fetch_array($result);
	$dossardmin = $borne['dossardDeb'];
	$dossardmax = $borne['dossardFin'];	
	$doss = $dossardmin;
	
	$nbexclusion = $borne['nbexclusion'];
	$plage_exclusion = explode(":",$borne['dossards_exclus']);
	for($j=0; $j<$nbexclusion; $j++)
	{
		$exclus = explode("-",$plage_exclusion[$j]);
		for($e=$exclus[0]; $e<=$exclus[1]; $e++)
			array_push($dossard_affecte,intval($e));
	}
	sort($dossard_affecte);
	//print_r($dossard_affecte);				
	if(empty($dossard_affecte))
	{				
		//si la numérotation n'est pas initialisée, on attribue 0
		if($dossardmin == 0) $doss = 0;
		else $doss = $dossardmin;
								
		//$query  = "UPDATE r_inscriptionepreuveinternaute SET dossard = ".$doss." WHERE idEpreuve = ".$epreuve." and idEpreuveParcours = ".$parcours." AND idInscriptionEpreuveInternaute = ".$iei."";
		//$mysqli->query($query);
	}
	else
	{							
		
		if($dossardmin == 0)
		{
			//$query  = "UPDATE r_inscriptionepreuveinternaute SET dossard = 0 WHERE idEpreuve = ".$epreuve." and idEpreuveParcours = ".$parcours." AND idInscriptionEpreuveInternaute = ".$iei."";
			//$mysqli->query($query);
		}else{
			
			
			while(in_array($doss,$dossard_affecte)) $doss += 1;
				//echo $doss;					
			//attribution du prochain dossard disponible
			//$query  = "UPDATE r_inscriptionepreuveinternaute SET dossard = ".$doss." WHERE idEpreuve = ".$epreuve." and idEpreuveParcours = ".$parcours." AND idInscriptionEpreuveInternaute = ".$iei."";
			//$mysqli->query($query);
		}
	}
	return $doss;
}
?>
