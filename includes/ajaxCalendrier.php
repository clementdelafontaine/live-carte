<?php 
/*
ini_set("display_errors", 1);
error_reporting(E_ALL);
*/
require_once("functions.php");
require_once('connect_db.php');
session_start();
connect_db();
global $mysqli;
		function diff_date_include($datedeb,$datefin)
			{
				global $mysqli;
				$datedeb = dateen2fr($datedeb,1);
				$datefin = dateen2fr($datefin,1);	
				$dfin = explode("/", $datefin);
				$djour = explode("/", $datedeb); 
				$date2 = $dfin[2].$dfin[1].$dfin[0]; 
				$date1 = $djour[2].$djour[1].$djour[0];
				//echo $date1." > ".$date2."(".$datefin.")----";
				if ($date1>$date2) return 1; else return 0;	
			
			}
 if ($_SESSION["typeInternaute"] == 'admin' || $_SESSION["typeInternaute"] == 'super_organisateur') $admin=1;

$type_epreuve = $_GET['type_epreuve'];
$filter = $_GET['filter'];
$nb_jour=$_GET['jours'];
$check_doublon=$_GET['dbl'];
$paiement_filter=$_GET['paiement'];
$query_relais = "SELECT sum(relais) as nb_relais FROM r_epreuveparcours WHERE idEpreuve = ".$_GET['id_epreuve'];
$result_relais = $mysqli->query($query_relais);
$row_relais = mysqli_fetch_array($result_relais);
$nb_relais = $row_relais['nb_relais'];
$groupe='';

	$query_parcours ="SELECT idEpreuveParcours FROM r_epreuveparcours WHERE idEpreuve = ".$_GET['id_epreuve']." ";
	$result_parcours = $mysqli->query($query_parcours);
	$nb_parcours = mysqli_num_rows($result_parcours);
	
if (!empty($_GET['id_parcours'])) {
	$inscription_perso = inscription_perso($_GET['id_epreuve'],$_GET['id_parcours']);
	$groupe=$inscription_perso['groupe'];
}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Easy set variables
	 */
	
	/* Array of database columns which should be read and sent back to DataTables. Use a space where
	 * you want to insert a non-database field (for example a counter or static image)
	 */
	//$aColumns = array( 'riei.idInscriptionEpreuveInternaute', 'ri.loginInternaute', 'nomInternaute', 'prenomInternaute', 'sexeInternaute', 'emailInternaute' );
$aColumns = array( 'te.nomTypeEpreuve,e.dateEpreuve,e.ville,e.nomEpreuve,rep.nomParcours,e.idEpreuve,   e.DateFinEpreuve, e.departement, e.idInternaute, e.contactInscription, e.administrateur, e.emailInscription, e.nbParticipantsAttendus,e.dateFinInscription,e.dateDebutInscription
');

	
$champ_desire = array('idEpreuve','nomTypeEpreuve','dateEpreuve','ville','nomEpreuve','departement', 'nomParcours','nbParticipantsAttendus','dateFinInscription','dateDebutInscription');

$champ_desire_v2 = array('nomTypeEpreuve','dateEpreuve','ville','nomEpreuve','nomParcours');


/*
else 
{
$champ_desire = array('riei.idInscriptionEpreuveInternaute','riei.date_insc','ri.nomInternaute','ri.sexeInternaute','riei.paiement_type','riei.verif_certif',
'riei.verif_auto_parentale','riei.dossard','riei.observation');

$champ_desire_v2 = array('idInscriptionEpreuveInternaute','date_insc','nomInternaute','sexeInternaute','paiement_type','verif_certif',
'verif_auto_parentale','dossard','observation');
}
*/
$champ_recherche = array('e.nomEpreuve','e.ville');
/*
if ($nb_relais > 0)
{	
	array_push($champ_desire,'riei.equipe');
	array_push($champ_desire_v2,'equipe');
	array_push($champ_recherche,'riei.equipe');
	//print_r($champ_recherche);
}
if (!empty($groupe))
{	
	array_push($champ_desire,'riei.groupe');
	array_push($champ_desire_v2,'groupe');
}

if (empty($_GET['id_parcours']) && $nb_parcours > 1 ) 
{	
	array_push($champ_desire,'rep.nomParcours');
	array_push($champ_desire_v2,'nomParcours');
}
*/
	/*
	$query  = "SELECT e.idEpreuve, e.nomEpreuve, e.dateEpreuve, e.DateFinEpreuve, e.departement,e.ville, e.idInternaute, e.contactInscription, e.administrateur, e.emailInscription, e.nbParticipantsAttendus,";
	$query .= "i.idInternaute, i.loginInternaute, i.passInternaute, i.emailInternaute  ";
    $query .= "FROM r_epreuve AS e, r_internaute AS i ";
	$query .= "WHERE i.idInternaute = e.idInternaute AND (e.idInternaute = ".$_SESSION["log_id"]." OR e.administrateur = ".$_SESSION["log_id"].") ";
    $query .=" AND e.dateDebutInscription  >= NOW() ";
	*/
//print_r($champ_desire);

	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "e.idEpreuve";
	
	/* DB table to use */
	$sTable = " r_epreuve AS e 
				INNER JOIN r_epreuveparcours as rep ON e.idEpreuve = rep.idEpreuve 
				INNER JOIN r_typeepreuve as te ON e.idTypeEpreuve = te.idTypeEpreuve ";
	/*
	
	$sTable = " r_internaute as ri INNER JOIN r_inscriptionepreuveinternaute as riei ON ri.idInternaute = riei.idInternaute ";
	$sTable.="INNER JOIN r_epreuve as re ON riei.idEpreuve = re.idEpreuve ";
	$sTable.="INNER JOIN r_epreuveparcours as rep ON riei.idEpreuveParcours = rep.idEpreuveParcours ";
	*/
	
/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
	{
		$sLimit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
			mysql_real_escape_string( $_GET['iDisplayLength'] );
	}
	
	
	/*
	 * Ordering
	 */
	if ( isset( $_GET['iSortCol_0'] ) )
	{
		$sOrder = "ORDER BY  ";
		for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
		{
			if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
			{
				$sOrder .= $champ_desire_v2[ intval( $_GET['iSortCol_'.$i] ) ]."
				 	".mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
			}
		}
		
		$sOrder = substr_replace( $sOrder, "", -2 );
		if ( $sOrder == "ORDER BY" )
		{
			$sOrder = "";
		}
	}

	
	/* 
	 * Filtering
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
	 */
	$sWhere = "";
	if ( $_GET['sSearch'] != "" )
	{
		//print_r($aColumns);
		//print_r($champ_recherche);
		$sWhere = "WHERE (";
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			//echo "## (".$i.") [".$champ_desire[$i]."] ##";
			//echo "(".$i.") [".$aColumns[$i]."]";
			if ($key = array_search($aColumns[$i],$champ_recherche)) {
			
				//echo "#".$champ_recherche[$key]."#";
				$sWhere .= $champ_recherche[$key]." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
			
			}			
			
		}
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ')';
	}
	
	/* Individual column filtering */
	for ( $i=0 ; $i<count($aColumns) ; $i++ )
	{
		if ( $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
		{
			if ( $sWhere == "" )
			{
				$sWhere = "WHERE ";
				
			}
			else
			{
				$sWhere .= " AND ";
			}
			$sWhere .= $champ_desire[$i]." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$i])."%' ";
		}
	}
	/*		
	if ( $sWhere == "" )
	{
		$sWhere .= " WHERE riei.idEpreuve = ".$_GET['id_epreuve'];
		//$sWhere.="   ";
	}
	else
	{
		$sWhere .= " AND riei.idEpreuve = ".$_GET['id_epreuve'];
	}
	if ( $_GET['id_parcours'] != "" )
	{
		$sWhere .= " AND riei.idEpreuveParcours = ".$_GET['id_parcours'];
	}
	*/
	//$sWhere .= " WHERE e.dateDebutInscription  >= NOW() ";
	$sWhere .= " WHERE e.visible_calendrier='1' AND e.DateFinEpreuve > Now() ";
	/*
	if ($nb_jour == 'dci') {
	
		//echo $_SESSION["dateConsultation"];
		//$query.="AND riei.date_insc >= '".$_SESSION["dateConsultation"]."' ";
		$sWhere .=" AND riei.new = 'oui' ";
	}
	elseif ($nb_jour == 'dc') {
	
		//echo $_SESSION["dateConsultation"];
		//$query.="AND riei.date_insc >= '".$_SESSION["dateConsultation"]."' ";
		$sWhere .=" AND riei.new = 'non' ";
	}
	elseif ($nb_jour == 'tous') {
	
		//echo $_SESSION["dateConsultation"];
		//$query.="AND riei.date_insc >= '".$_SESSION["dateConsultation"]."' ";
	}
	elseif ($nb_jour>0){
		
		$sWhere .=" AND riei.date_insc BETWEEN NOW() - INTERVAL ".$nb_jour." DAY AND NOW() ";
		
	}

	if (empty($paiement_filter)) {
		//$sWhere .= " AND paiement_type NOT IN ('ATTENTE','ATTENTE CHQ','STAFF','ORGANISATEUR','SUPPRESSION','REMBOURSE','A REMBOURSER') AND paiement_date IS NOT NULL ";
		if ($check_doublon != 1) {
			if ($nb_jour == '*tous*') {
				$sWhere .= " ";
			} 
			else {
				$sWhere .= " AND paiement_type IN ('CB','CHQ','GRATUIT','AUTRE') AND paiement_date IS NOT NULL ";
			}
		}

	}
	else
	{
		if ($paiement_filter=='ATTENTE') $paiement_filter="'ATTENTE','ATTENTE CHQ','RELAIS_ATTENTE'"; else $paiement_filter="'".$paiement_filter."'";

		$sWhere .= " AND paiement_type IN (".$paiement_filter.") ";
	}
	
	if ($check_doublon == 1) {
		$sWhere .=" 
				AND riei.idInternaute IN 
				( 
				SELECT idInternaute FROM r_inscriptionepreuveinternaute WHERE idEpreuve = ".$_GET['id_epreuve']." 
				GROUP BY idInternaute    HAVING COUNT(idInternaute)>1 
				)";
	}
*/	
	/*
	 * SQL queries
	 * Get data to display
	 */
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable
		$sWhere
		$sOrder
		$sLimit
	";
	//echo $sQuery;
	$rResult = $mysqli->query( $sQuery ) or die(mysqil_error());
	$iTotal = mysqli_num_rows($rResult);
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	";
	$rResultFilterTotal = $mysqli->query( $sQuery ) or die(mysqil_error());
	$aResultFilterTotal = mysqli_fetch_array($rResultFilterTotal);
	$iFilteredTotal = $aResultFilterTotal[0];
	
	/* Total data set length */
	$sQuery = "
		SELECT COUNT(".$sIndexColumn.")
		FROM   $sTable 
		WHERE e.DateFinEpreuve > Now() AND e.visible_calendrier='1' ";
	$rResultTotal = $mysqli->query( $sQuery ) or die(mysqil_error());
	$aResultTotal = mysqli_fetch_array($rResultTotal);
	//$iTotal = $aResultTotal[0];
	
	
	/*
	 * Output
	 */
	$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => array()
	);
	//echo count($aColumns);
	$prenom = '';
	//$query_update_certificat_fichier = type_epreuve($_GET['id_epreuve']);
	
	while ( $aRow = mysqli_fetch_array( $rResult ) )
	{
		
		
		$aRow['ville'] = $aRow['departement']."-".$aRow['ville'];
		//$aRow['nomEpreuve'] = $aRow['idEpreuve']."-".$aRow['nomEpreuve']." <i>[".$aRow['nbParticipantsAttendus']." particpants max.]</i>";
		$aRow['nomEpreuve'] = $aRow['dateEpreuve']." - ".$aRow['nomEpreuve']." <i>[".$aRow['nbParticipantsAttendus']." particpants max.]</i>";
		/*
		$nb_relais_tmp = extract_champ_parcours('relais',$aRow['idEpreuveParcours']);
		$inscription_perso = inscription_perso($id_epreuve,$row_parcours['idEpreuveParcours']);
		if (!empty($inscription_perso['groupe'])) $groupe =1;
		$nb_relais +=$nb_relais_tmp;
		
		//echo $aRow['relais'];
		$certif_parcours = $aRow['certificatMedical'];
		$autoParentale_parcours = $aRow['autoParentale'];
		$paiement_type = $aRow['paiement_type'];
		
		
		//print_r($aRow)."</br>";
		$nom= $aRow['nomInternaute'];
		$idInternaute = $aRow['idInternaute'];
		$idInscriptionEpreuveInternaute= $aRow['idInscriptionEpreuveInternaute'];
		$aRow['date_insc']=dateen2fr($aRow['date_insc'],0,'d/m/Y');
		//echo $aRrow['relais'];
		//print_r($aRow);
		//echo count($aColumns);
		$action='';
		*/
		$row = array();
		/*
		$ref_name='';
		$id_relais = internaute_inscription_multiple ($_GET['id_epreuve'],$aRow['idEpreuveParcours'],$idInternaute);
		//if($aRow['idInternaute'] == 188045) print_r($id_relais);	
		//print_r($id_relais);										
		$chk_id_ref = internaute_ref($aRow['idInscriptionEpreuveInternaute'],$_GET['id_epreuve']);
		//echo $aRow['idInscriptionEpreuveInternaute'];
		//if ($aRow['idInscriptionEpreuveInternaute']==214161) print_r($chk_id_ref);	
		$id_ref = $chk_id_ref['idRef'];
		$noms_id_ref = $chk_id_ref['noms_Internautes'];
		if (!empty($id_ref))
		{
			//$ref_name= '<i data-toggle="tooltip" data-placement="top" data-original-title="Inscription(s) de '.$noms_id_ref.'" class="fa fa-users text-primary"></i>';
			$ref_name = '<span class="label label-primary" data-toggle="tooltip" data-placement="top" data-original-title="Inscription(s) de '.$noms_id_ref.'" >REF</span>';
		}
		else
		{
			if (!empty($id_relais['Ref'])) { 
				//$ref_name = '<i class="fa fa-user text-default" data-original-title="Inscrit par '.$id_relais['Ref'].'" data-placement="top" data-toggle="tooltip"></i> ';
				$ref_name = '<span class="label label-default" data-toggle="tooltip" data-placement="top" data-original-title="Inscrit par '.$id_relais['Ref'].' (Ref_PC : '.$id_relais['idInternauteInscriptionRef'].')" >ENF</span>';				
			}
			//else { $ref_name= '<i class="fa fa-user text-warning"></i>'; }
			
		
		}
		
		
		
		 if ($admin==1) { 
			 $ref_paiement_multiple = '';
			 if ($idInternaute == $id_ref) { 
				if ($chk_id_ref['idRefEtat'] == 'KO') { $color_ref_bp="danger"; }
				  elseif ($chk_id_ref['idRefEtat'] == 'OK' && ($chk_id_ref['idRefStatut'] == 'ATTENTE' || $chk_id_ref['idRefStatut'] == 'REFUSEE' || $chk_id_ref['idRefStatut'] == 'ANNULEE')) { $color_ref_bp="warning"; }
				  else { $color_ref_bp="success"; } 
					$ref_paiement_multiple  = '</br><span class="text-'.$color_ref_bp.' text-center" data-original-title='.$chk_id_ref['idRefMontant'].' € - '.$chk_id_ref['idRefStatut'].'" data-placement="top" data-toggle="tooltip"><strong>M-'.$chk_id_ref['idRefPaiement'].'</strong></span>';
			 }
			
			$aRow['idInternaute'] = '<span data-original-title="'.strip_tags(str_replace("</br>"," # ",$aRow['info_diverses'])).'" data-placement="top" data-toggle="tooltip">'.$aRow['idInternaute'].''.$id_multi.'</span>'.$ref_paiement_multiple;
		
		//$aRow['idIInternaute'] = $aRow['idIInternaute'].$ref_paiement_multiple.$tools_tip;
		}
		
		
		if ($aRow['inscription_par']=='organisateur')
		{
			$color_nom='warning';
		}
		elseif ($aRow['inscription_par']=='admin')
		{
			$color_nom='danger';
		}
		elseif ($aRow['inscription_par']=='super_organisateur')
		{
			$color_nom='inverse';
		}
		else
		{
			$color_nom='primary';
		}
		
		if ($aRow['new']=='oui') 
		{
			
			$etat_inscription_aff ='<span id="aff_etat_dossier_'.$idInscriptionEpreuveInternaute.'"><a onclick="validation(3,\'non\','.$idInscriptionEpreuveInternaute.',0,'.$idInternaute.');notification(\'Notification\',\'Le participant est vérifié\',5000,\'ok\')" href="javascript:;"><i data-toggle="tooltip" data-placement="top" data-original-title="Cliquez ici pour passer le participant en vérifié" class="text-danger fa fa-1x fa-star"></i></a> <span style="display:none">dangerh</span></span>';
		}
		else
		{
			$etat_inscription_aff ='<span id="aff_etat_dossier_'.$idInscriptionEpreuveInternaute.'"><a onclick="validation(3,\'oui\','.$idInscriptionEpreuveInternaute.',0,'.$idInternaute.');notification(\'Notification\',\'Le participant n\\\'est pas vérifié\',5000,\'ok\')" href="javascript:;"><i data-toggle="tooltip" data-placement="top" data-original-title="Cliquez ici pour passer le participant en NON vérifié" class="text-success fa fa-1x fa-check-square-o"></i></a><span style="display:none">success</span></span>';
		
			//$etat_inscription_aff ='<i class="text-success fa fa-1x fa-check-square-o" data-original-title="Inscription le '.substr(dateen2fr($row['date_insc']),0,-3).'" data-placement="top" data-toggle="tooltip"><span style="display:none">success</span></i>';
			
		}
			

		$aRow['nomInternaute'] = $etat_inscription_aff.' <span class="text-'.$color_nom.'"><strong>'.strtoupper($aRow['nomInternaute']).'</strong></span> '.strtoupper($aRow['prenomInternaute']).'  '.$ref_name;
		//$aRow['nomInternaute'] ='<span class="text-primary"><strong>'.$aRow['nomInternaute'].'</strong></span> '.$aRow['prenomInternaute'].'';
		
		
		
		//echo $aRow['sexeInternaute'];
		if($aRow['sexeInternaute']=='M')
		{
				//echo "xxx";
				$aRow['sexeInternaute'] = '<strong><i class="fa fa-1x fa-male text-default"></i></strong>';
				//echo $aRow['sexeInternaute'];
				//echo "yyy";
		}
		else
		{
			$aRow['sexeInternaute'] = '<strong><i class="fa fa-1x fa-female text-danger"></i></strong>';
		
		}
		
		
		
		
		
		$color_paiment= 'success';
		$paiement_etat =1;
		if ($idInternaute == $id_ref) $color_paiment= 'primary';
		if ($aRow['paiement_type'] == 'CHQ') { $html_aRow_paiement = '<span class="text-'.$color_paiment.'"><strong>CHQ</strong></span>';}
		elseif ($aRow['paiement_type'] == 'CB') { $html_aRow_paiement = '<span class="text-'.$color_paiment.'"><strong>CB</strong></span>';}
		elseif ($aRow['paiement_type'] == 'GRATUIT') { $html_aRow_paiement = '<span class="text-'.$color_paiment.'"><strong>GRATUIT</strong></span>';}
		//if ($row['paiement_type'] == 'ATTENTE') { $icon = 'fa-cc-visa'; $aff_icon = 'GRATUIT'; $paiement = '<span class="text-'.$color_paiment.'"><strong>GRATUIT</strong></span>';}
		elseif ($aRow['paiement_type'] == 'AUTRE') { $html_aRow_paiement = '<span class="text-'.$color_paiment.'"><strong>AUTRE</strong></span>';}
		elseif ($aRow['paiement_type'] == 'A REMBOURSER') { $html_aRow_paiement = '<span class="text-warning"><strong>A REMBOURSER</strong></span>';}
		elseif ($aRow['paiement_type'] == 'REMBOURSE') { $html_aRow_paiement = '<span class="text-danger"><strong>REMBOURSE</strong></span>';}
		elseif ($aRow['paiement_type'] == 'STAFF') { $html_aRow_paiement = '<span class="text-danger"><strong>STAFF</strong></span>';}
		elseif ($aRow['paiement_type'] == 'ORGANISATEUR') { $html_aRow_paiement = '<span class="text-info"><strong>ORGANISATEUR</strong></span>';}
		else {
			if (empty($id_relais)) {	
				$html_aRow_paiement ='<a href="javascript:;" id="paiement_type--inscription--'.$idInscriptionEpreuveInternaute.'" data-type="select" data-pk="'.$_GET['id_epreuve']."--".$aRow['idEpreuveParcours'].'" data-value="'.$aRow['paiement_type'].'" data-source="includes/ajaxListes.php?filter=type_paiement" >'.$aRow['paiement_type'].'</a>';
			} else {  
											
				$html_aRow_paiement = '<span class="text-default"><strong>'.$aRow['paiement_type'].'</strong></span>';	
			}	
				$paiement_etat =0;
		
		}
		
		if ($paiement_type == 'CHQ' || $paiement_type == 'ATTENTE CHQ') $aff_a_cheque='visible'; else $aff_a_cheque='none';
		if ($paiement_type == 'ATTENTE') $aff_a_attente='visible'; else $aff_a_attente='none';
		
		$html_paiement_chq_relance ='';
		if(($aRow['paiement_type'] =='ATTENTE' || $aRow['paiement_type'] =='ATTENTE CHQ' )  && $aRow['paiement_date'] == '') {
			if ($aRow['relance_paiement']==0 && (empty($id_relais))) { 
				//$html_paiement_chq_relance = '<a href="javascript:;" style="display:'.$aff_a_cheque.'" id="a-info_cheque--'.$idInscriptionEpreuveInternaute.'" onclick="check_type_paiement('.$idInscriptionEpreuveInternaute.')" data-original-title="'.$aRow['info_cheque'].'" data-placement="top" data-toggle="tooltip"><i class="fa fa-info"></i></a>';
				//$html_paiement_chq_relance .= '<a href="javascript:;" style="display:'.$aff_a_cheque.'" id="a-info_attente--'.$idInscriptionEpreuveInternaute.'" data-original-title="'.$paiement_status.'" data-placement="top" data-toggle="tooltip"><i class="fa fa-info"></i></a>';
				$html_paiement_chq_relance .= '<span id="relance_paiement_'.$idInscriptionEpreuveInternaute.'"> <a href="javascript:;" onclick="if(!confirm(\'Confirmez vous l\\\'envoi de la demande de paiement ?\')) return;validation(0,\'oui\','.$idInscriptionEpreuveInternaute.', '.$paiement_etat.', '.$idInternaute.');"><i class="text-danger fa fa-repeat" data-original-title="Relancer le paiement ?" data-placement="top" data-toggle="tooltip"></i></a></span>';
			 } else {  
				 if (empty($row['paiement_date'])) { 

					 if (empty($id_relais)) { 
						$html_paiement_chq_relance = '<span id="relance_paiement_'.$idInscriptionEpreuveInternaute.'"><i class="text-danger fa fa-spinner" data-original-title="Relance effectuée le '.dateen2fr($aRow['relance_paiement_date']).'" data-placement="top" data-toggle="tooltip"></i></span><span style="display:none">RELANCE</span>';
					 } else { 
						$html_paiement_chq_relance = '<span id="relance_paiement_'.$idInscriptionEpreuveInternaute.'"><i class="text-danger fa fa-info-circle" data-original-title="Le paiement doit être effectué par '.$id_relais['Ref'].'" data-placement="top" data-toggle="tooltip"></i></span><span style="display:none">RELANCE</span>';
					 } 
				
				 } 
			 } 
		 } 		
		
		$aRow['paiement_type'] ='<div id="td_paiement--'.$idInscriptionEpreuveInternaute.'">'.$html_aRow_paiement.' '.$html_paiement_chq_relance.'</div>';
		if ($certif_parcours == 1) 
		{
			$certif = certif_medical_existe ($aRow['nomInternaute']." ".$aRow['prenomInternaute'], $idInscriptionEpreuveInternaute,$idInternaute,$query_update_certificat_fichier['type_nom_bdd'],$type_retour='liste',$aRow['verif_certif']);
			
			
			if ($certif =='warning') {
				
				//$html_certif = '<strong><i class="text-warning fa fa-1x fa-file-pdf-o" data-original-title="Fourni mais non vérifié par l\'organisateur" data-placement="top" data-toggle="tooltip"></i></strong>';
				$html_certif = certif_medical_existe ($nom." ".$aRow['prenomInternaute'], $idInscriptionEpreuveInternaute,$idInternaute,$query_update_certificat_fichier['type_nom_bdd']);
				$html_certif_controle = '<a href="javascript:;" onclick="validation(1,\'oui\','.$idInscriptionEpreuveInternaute.','.$paiement_etat.','.$idInternaute.');notification(\'Notification\',\'le certificat a été validé\',5000,\'ok\')"><i class="text-danger fa fa-1x fa-times-circle" data-original-title="Valider le certificat / la license ?" data-placement="top" data-toggle="tooltip"></i></a><span style="display:none">CAV</span>';
			
			}
			elseif ($certif =='danger')
			{
				$html_certif =' ';
				//$html_certif ='<i class="text-danger fa fa-1x fa-file-pdf-o" data-original-title="Non fourni" data-placement="top" data-toggle="tooltip"></i> ';
				//$html_certif .= '<a onclick="affiche_modal_relance('.$aRow['idInternaute'].');" class="btn btn-primary btn-xs m-r-5" href="javascript:;">Relance ?</a>';
				$html_certif_controle = '<a href="javascript:;" onclick="validation(1,\'oui\','.$idInscriptionEpreuveInternaute.','.$paiement_etat.','.$idInternaute.');notification(\'Notification\',\'le certificat a été validé\',5000,\'ok\')"><i class="text-danger fa fa-1x fa-times-circle" data-original-title="Valider le certificat / la license ?" data-placement="top" data-toggle="tooltip"></i></a>';
				$html_certif_controle .= ' <a href="javascript:;" onclick="affiche_modal_certif('.$idInternaute.','.$idInscriptionEpreuveInternaute.','.$_GET['id_epreuve'].','.$aRow['idEpreuveParcours'].');"><i class="text-danger fa fa-1x fa-plus" data-original-title="Insérer le certificat / la license ?" data-placement="top" data-toggle="tooltip"></i></a><span style="display:none">CA</span>';
				if ($aRow['relance_certif'] > 0) {
					
					if ($aRow['relance_certif']==2) 
						$relance_certif = "automatique à 3 mois de l'épreuve,"; 
					elseif ($aRow['relance_certif']==3) 
						$relance_certif = "automatique à 2 mois de l'épreuve,"; 
					elseif ($aRow['relance_certif']==4) 
						$relance_certif = "automatique à 1 mois de l'épreuve,";
					elseif ($aRow['relance_certif']==5) 
						$relance_certif = "automatique à 15 jours de l'épreuve,";
					elseif ($aRow['relance_certif']==6) 
						$relance_certif = "automatique à 7 jours de l'épreuve,";
					elseif ($aRow['relance_certif']==7) 
						$relance_certif = "automatique à 3 jours de l'épreuve,"; 			
					else $relance_certif = "manuelle";
					
					$info_relance_certif = "Relance ".$relance_certif." à la date du ".dateen2fr($aRow['relance_certif_date']);
					$html_certif_controle .='<span id="relance_certif_'.$idInscriptionEpreuveInternaute.'"><a href="javascript:;" onclick="if(!confirm(\'Confirmez vous l\\\'envoi de la demande de relance du certificat ?\')) return;validation(1,\'pas_de_certif|mail\','.$idInscriptionEpreuveInternaute.','.$paiement_etat.','.$idInternaute.')"><i class="text-danger fa fa-1x fa-spinner" data-original-title="'.$info_relance_certif.' - Cliquez pour relancer manuellement la demande" data-placement="top" data-toggle="tooltip"></i></a></span><span style="display:none">RELANCE_CERTIF</span> ';
					
				} else
				{
					$html_certif_controle .= ' <a href="javascript:;" onclick="if(!confirm(\'Confirmez vous l\\\'envoi de la demande de relance du certificat ?\')) return;validation(1,\'pas_de_certif|mail\','.$idInscriptionEpreuveInternaute.','.$paiement_etat.','.$idInternaute.')"><i class="text-danger fa fa-1x fa-repeat" data-original-title="Relancer pour le certificat ?" data-placement="top" data-toggle="tooltip"></i></a>';
				}
			}
			else
			{
				//$html_certif = '<i class="text-success fa fa-1x fa-file-pdf-o" data-original-title="Certificat validé" data-placement="top" data-toggle="tooltip"></i>';
				$html_certif = certif_medical_existe ($nom." ".$aRow['prenomInternaute'], $idInscriptionEpreuveInternaute,$idInternaute,$query_update_certificat_fichier['type_nom_bdd']);
				
				$html_certif_controle = '<a href="javascript:;" onclick="validation(1,\'non\','.$idInscriptionEpreuveInternaute.','.$paiement_etat.','.$idInternaute.');notification(\'Notification\',\'le certificat a été refusé\',5000,\'ko\')"><i class="text-success fa fa-1x fa-check" data-original-title="Invalider le certificat / la license ?" data-placement="top" data-toggle="tooltip"></i></a><span style="display:none">CV</span>';
				
			}
		}
		else
		{
			$certif ='success';
			$html_certif_controle = '';
			$html_certif = '<i class="text-danger fa fa-1x fa-ban" data-original-title="Pas de certificat pour ce parcours" data-placement="top" data-toggle="tooltip"></i>';
		}

		$aRow['verif_certif'] = '<span id="certif_fichier_'.$idInscriptionEpreuveInternaute.'">'.$html_certif.'</span><span id="certif_controle_'.$idInscriptionEpreuveInternaute.'">'.$html_certif_controle.'</span>';
		
		
		if ($autoParentale_parcours == 1) 
			{
				$autoParentale = auto_parentale_existe ($nom." ".$aRow['prenomInternaute'],$idInternaute,$idInscriptionEpreuveInternaute,$aRow['idEpreuveParcours'],$type_retour='liste',$aRow['verif_auto_parentale']);
				
				//if ($aRow['idInternaute'] == 1272 ) echo "xxxxxx".$autoParentale;
				if ($autoParentale =='warning') {
					
					//$html_certif = '<strong><i class="text-warning fa fa-1x fa-file-pdf-o" data-original-title="Fourni mais non vérifié par l\'organisateur" data-placement="top" data-toggle="tooltip"></i></strong>';
					$html_autoParentale= auto_parentale_existe ($nom." ".$aRow['prenomInternaute'],$idInternaute,$idInscriptionEpreuveInternaute,$aRow['idEpreuveParcours']);
					$html_autoParentale_controle = '<a href="javascript:;" onclick="validation(2,\'oui\','.$idInscriptionEpreuveInternaute.','.$paiement_etat.','.$idInternaute.');notification(\'Notification\',\'l\\\'autorisation parentale a été validée\',5000,\'ok\')"><i class="text-danger fa fa-1x fa-times-circle" data-original-title="Valider l\'autorisation parentale ?" data-placement="top" data-toggle="tooltip"></i></a><span style="display:none">CAV</span>';
					//if ($aRow['idInternaute'] == 1272 ) echo "xxxxxx".$html_autoParentale;
				}
				elseif ($autoParentale=='danger')
				{
					$html_autoParentale =' ';
					//$html_certif ='<i class="text-danger fa fa-1x fa-file-pdf-o" data-original-title="Non fourni" data-placement="top" data-toggle="tooltip"></i> ';
					//$html_certif .= '<a onclick="affiche_modal_relance('.$aRow['idInternaute'].');" class="btn btn-primary btn-xs m-r-5" href="javascript:;">Relance ?</a>';
					$html_autoParentale_controle = '<a href="javascript:;" onclick="validation(2,\'oui\','.$idInscriptionEpreuveInternaute.','.$paiement_etat.','.$idInternaute.');notification(\'Notification\',\'l\\\'autorisation parentale a été validée\',5000,\'ok\')"><i class="text-danger fa fa-1x fa-times-circle" data-original-title="Valider l\'autorisation parentale ?" data-placement="top" data-toggle="tooltip"></i></a>';
					$html_autoParentale_controle .= ' <a href="javascript:;" onclick="affiche_modal_autoParentale('.$idInternaute.','.$idInscriptionEpreuveInternaute.','.$_GET['id_epreuve'].','.$aRow['idEpreuveParcours'].');"><i class="text-danger fa fa-1x fa-plus" data-original-title="Insérer l\'autorisation parentale ?" data-placement="top" data-toggle="tooltip"></i></a><span style="display:none">CA</span>';
					//***$html_autoParentale_controle .= '<span id="relance_certif_'.$idInscriptionEpreuveInternaute.'> <a href="javascript:;" onclick="if(!confirm(\'Confirmez vous l\'envoi de la demande de relance du certificat ?\')) return;validation(1,\'supp_certif|mail\','.$idInscriptionEpreuveInternaute.','.$paiement_etat.')"><i class="text-danger fa fa-repeat" data-original-title="Relancer pour le certificat ?" data-placement="top" data-toggle="tooltip"></i></a></span>';
				}
				else
				{
					//$html_certif = '<i class="text-success fa fa-1x fa-file-pdf-o" data-original-title="Certificat validé" data-placement="top" data-toggle="tooltip"></i>';
					$html_autoParentale= auto_parentale_existe ($nom." ".$aRow['prenomInternaute'],$idInternaute,$idInscriptionEpreuveInternaute,$aRow['idEpreuveParcours'],'fichier');
					
					$html_autoParentale_controle = '<a href="javascript:;" onclick="validation(2,\'non\','.$idInscriptionEpreuveInternaute.','.$paiement_etat.','.$idInternaute.');notification(\'Notification\',\'l\\\'autorisation parentale a été refusée\',5000,\'ko\')"><i class="text-success fa fa-1x fa-check" data-original-title="Invalider l\'autorisation parentale ?" data-placement="top" data-toggle="tooltip"></i></a><span style="display:none">CV</span>';
					
				}
			}
			else
			{
				$html_autoParentale ='<i data-toggle="tooltip" data-placement="top" data-original-title="pas d\'autorisation parentale sur ce parcours" class="text-danger fa fa-1x fa-ban"></i>';
				$html_autoParentale_controle = '';
				//$autoParentale = 'success';
			}

		$aRow['verif_auto_parentale'] = '<span id="autoParentale_fichier_'.$idInscriptionEpreuveInternaute.'">'.$html_autoParentale.'</span><span id="autoParentale_controle_'.$idInscriptionEpreuveInternaute.'">'.$html_autoParentale_controle.'</span>';
		
		if ($aRow['dossard'] == 0) 
		{
			$aRow['dossard'] = '<span id="aff_dossard_'.$idInscriptionEpreuveInternaute.'"><a href="javascript:;" id="dossard--inscription--'.$idInscriptionEpreuveInternaute.'" data-inputclass="input_size_number" data-type="number" data-pk="'.$_GET['id_epreuve']."--".$aRow['idEpreuveParcours'].'" data-title="Modifier le dossard"><strong><i class="text-danger fa fa-ban" data-original-title="Dossard non attribué" data-placement="top" data-toggle="tooltip"></i></strong></a></span>';
		}
		else
		{
			$aRow['dossard'] = '<span id="aff_dossard_'.$idInscriptionEpreuveInternaute.'"><a href="javascript:;" id="dossard--inscription--'.$idInscriptionEpreuveInternaute.'" data-inputclass="input_size_number" data-type="number" data-pk="'.$_GET['id_epreuve']."--".$aRow['idEpreuveParcours'].'" data-title="Modifier le dossard"><strong>'.$aRow['dossard'].'</strong></a></span>';
		}
		
		if ($nb_relais>0) {

			if ($idInternaute == $id_ref)
				{	
					$aRow['equipe'] = "<span class='text-primary'><strong>".$aRow['equipe']."</strong></span>";
				}
				else
				{
					$aRow['equipe'] = "<span class='text-default'>".$aRow['equipe']."</span>"; 
				}

		}
		if (!empty($groupe)) {

			if ($idInternaute == $id_ref)
				{	
					$aRow['groupe'] = "<span class='text-primary'><strong>".$aRow['groupe']."</strong></span>";
				}
				else
				{
					$aRow['groupe'] = "<span class='text-default'>".$aRow['groupe']."</span>"; 
				}

		}
		if (empty($_GET['id_parcours']))
		{
			if ($aRow['relais'] > 0) { $aRow['nomParcours'] = '<span class="text-warning">'.$aRow['nomParcours'].'</span>'; } else { $aRow['nomParcours'] = '<span class="text-default">'.$aRow['nomParcours'].'</span>'; }
		}
		$aRow['observation'] = '<a href="javascript:;" id="observation--inscription--'.$idInscriptionEpreuveInternaute.'" data-type="textarea" data-pk="'.$_GET['id_epreuve']."--".$aRow['idEpreuveParcours'].'" data-placeholder="Observations" data-original-title="Observations">'.$aRow['observation'].'</a>';
		*/
		/*$aRow['dateEpreuve'] = dateen2fr($aRow['dateEpreuve'],1);
		$joursem = array('Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam');
		$mois_fr = Array("", "Jan", "Fév", "Mar", "Avr", "Mai", "Jui", "Juil", "Aou", 
        "Sep", "Oct", "Nov", "Déc");
		list($jour, $mois, $annee) = explode('/', $aRow['dateEpreuve']);
		$timestamp = mktime (0, 0, 0, $mois, $jour, $annee);
		$aRow['dateEpreuve'] =$joursem[date("w",$timestamp)]." ".ltrim($jour, '0')." ".$mois_fr[$mois];
		*/
		
		$aRow['dateEpreuve']= dateen2fr($aRow['dateEpreuve'],1);
		for ( $i=0 ; $i<count($champ_desire_v2) ; $i++ )
		{

			

			
			//echo $aRow[$champ_desire[$i]]."</br>";
				$row[] = $aRow[$champ_desire_v2[$i]];
		

			

			//if (in_array($aColumns[$i],$champ_desire)) {
			


				if($champ_desire[$i]=='riei.idInscriptionEpreuveInternaute')
				{
					

					
				}
				
				//$row[] = $aRow[$i];

			

			
			
			//}
			/*
			if ( $aColumns[$i] == "date_insc" )
			{
				
				//$row[] = ($aRow[ $aColumns[$i] ]=="0") ? '-' : $aRow[ $aColumns[$i] ];
			}
			else if ( $aColumns[$i] != ' ' )
			{
				
				//echo $aColumns[$i]."</br>";
				//echo $aRow[ $aColumns[$i] ]."---";
				$row[] = $aRow[$i];
				//print_r($row);
			}
			*/
		}
		
		/*
		$action='
		<a class="" onclick="affiche_modal(\'Modification du participant\','.$_GET['id_epreuve'].',\'profile.php?id_epreuve='.$_GET['id_epreuve'].'&amp;id_parcours='.$aRow["idEpreuveParcours"].'&amp;action=update_aff&amp;id_int='.$aRow["idInscriptionEpreuveInternaute"].'&amp;panel=iframe\')" href="javascript:;"><i data-toggle="tooltip" data-placement="top" data-original-title="Informations complémentaires du participant" class="fa fa-1x fa-user"></i></a>
		
		<a onclick="reedition('.$aRow["idInternaute"].','.$aRow["idInscriptionEpreuveInternaute"].','.$_GET['id_epreuve'].','.$aRow["idEpreuveParcours"].',\''.$aRow["prenomInternaute"].'\',\''.$nom.'\',\'reedition\')" href="javascript:;"> <i class="text-danger fa fa-1x fa-external-link" data-original-title="Renvoyer la confirmation d\'inscription au participant" data-placement="top" data-toggle="tooltip"></i></a>
		<a class="text-primary fa fa-1x fa-envelope" data-original-title="Envoyer un email au participant" data-placement="top" data-toggle="tooltip" <i="" href="mailto:'.$aRow["emailInternaute"].'"></a>
		';
		if (strip_tags($aRow["paiement_type"]) != 'CB')
		{
			$action .='<a onclick="modif_inscription_internaute('.$aRow["idInternaute"].','.$aRow["idInscriptionEpreuveInternaute"].','.$_GET['id_epreuve'].','.$aRow["idEpreuveParcours"].',\''.$aRow["prenomInternaute"].'\',\''.$nom.'\',\'supp\')" href="javascript:;"> <i class="text-danger fa fa-1x fa-trash-o" data-original-title="Supprimer" data-placement="top" data-toggle="tooltip"></i></a>';
		
		}
		*/
		
		/*
		$action ='';
		
		//echo  '- ID : '.$aRow['idInternaute'].'- REf: '.$id_ref.' - Equipe : '.$aRrow['equipe'].'- Relais : '.$aRrow['relais'];
		 if ( ($idInternaute == $id_ref) && ($aRow['equipe'] != 'Aucune') && ($aRow['relais'] > 0))  { 
			$action .='<a href="javascript:;" onclick="affiche_modal(\'Edition du relais\','.$_GET['id_epreuve'].',\'relais_creation.php?id_epreuve='.$_GET['id_epreuve'].'&id_parcours='.$aRow["idEpreuveParcours"].'&id='.$idInscriptionEpreuveInternaute.'&panel=iframe\',710,510)" class="" ><i class="text-warning fa fa-1x fa-users" data-original-title="Edition du relais" data-placement="top" data-toggle="tooltip"></i></a>';
		 } 
		 //if (!empty($admin)) { 
			$action .='<a href="javascript:;" onclick="affiche_modal(\'Modification d\\\'un participant\','.$_GET['id_epreuve'].',\'profile.php?id_epreuve='.$_GET['id_epreuve'].'&id_parcours='.$aRow["idEpreuveParcours"].'&action=update_aff&id_int='.$idInscriptionEpreuveInternaute.'&panel=iframe\',750,1000)" class="" ><i class="fa fa-1x fa-user" data-original-title="Informations complémentaires du participant" data-placement="top" data-toggle="tooltip"></i></a>';
		 //} 
			
		if ($paiement_type=='A REMBOURSER') { 
			$action .='<span id="aff_remboursement_'.$idInscriptionEpreuveInternaute.'"><a href="javascript:;" onclick="modif_inscription_internaute('.$idInternaute.','.$idInscriptionEpreuveInternaute.','.$_GET['id_epreuve'].','.$aRow['idEpreuveParcours'].',\''.addslashes_form_to_sql($nom).'\',\''.$aRow['prenomInternaute'].'\',\'annulearembourser\')"> <s data-toggle="tooltip" data-placement="top" data-original-title="Annuler remboursement complet" class="text-warning fa fa-1x fa-money"></s></a></span>';												
			
		} else if ($paiement_type =='CB'){ 
			$action .= '<span id="aff_remboursement_'.$idInscriptionEpreuveInternaute.'"><a href="javascript:;" onclick="modif_inscription_internaute('.$idInternaute.','.$idInscriptionEpreuveInternaute.','.$_GET['id_epreuve'].','.$aRow['idEpreuveParcours'].',\''.addslashes_form_to_sql($nom).'\',\''.$aRow['prenomInternaute'].'\',\'arembourser\')"> <i data-toggle="tooltip" data-placement="top" data-original-title="Demander le remboursement complet" class="text-success fa fa-1x fa-money"></i></a></span>';
		} 
		
		 if ($admin==1 && $paiement_type == 'A REMBOURSER') { 
			$action .= '<a href="javascript:;" onclick="modif_inscription_internaute('.$idInternaute.','.$idInscriptionEpreuveInternaute.','.$_GET['id_epreuve'].','.$aRow["idEpreuveParcours"].',\''.addslashes_form_to_sql($nom).'\',\''.addslashes_form_to_sql($aRow['prenomInternaute']).'\',\'rembourse\')"> <span data-toggle="tooltip" data-placement="top" data-original-title="Remboursement effectué ?"><i  class="text-danger fa fa-euro"></i><i  class="text-success fa fa-1x fa-check"></i></span></a>';
		 } 
		
		 if ($idInternaute == $id_ref && $paiement_type != 'CB') {
		 //if (strip_tags($aRow['paiement_type']) == 'ATTENTE' || strip_tags($aRow['paiement_type']) == 'ATTENTE CHQ' || strip_tags($aRow['paiement_type']) == 'SUPPRESSION' || strip_tags($aRow['paiement_type']) == 'GRATUIT' || strip_tags($aRow['paiement_type']) == 'CHQ' || strip_tags($aRow['paiement_type']) == 'AUTRE' || strip_tags($aRow['paiement_type']) == 'STAFF' || strip_tags($aRow['paiement_type']) == 'RELAIS_ATTENTE') { 	
			$action .='<a href="javascript:;" onclick="modif_inscription_internaute('.$idInternaute.','.$idInscriptionEpreuveInternaute.','.$_GET['id_epreuve'].','.$aRow["idEpreuveParcours"].',\''.addslashes_form_to_sql($nom).'\',\''.addslashes_form_to_sql($aRow['prenomInternaute']).'\',\'supp\')"> <i data-toggle="tooltip" data-placement="top" data-original-title="Supprimer" class="text-danger fa fa-1x fa-trash-o"></i></a>';
		 }
		 elseif ($paiement_type != 'CB') {
		 //if (strip_tags($aRow['paiement_type']) == 'ATTENTE' || strip_tags($aRow['paiement_type']) == 'ATTENTE CHQ' || strip_tags($aRow['paiement_type']) == 'SUPPRESSION' || strip_tags($aRow['paiement_type']) == 'GRATUIT' || strip_tags($aRow['paiement_type']) == 'CHQ' || strip_tags($aRow['paiement_type']) == 'AUTRE' || strip_tags($aRow['paiement_type']) == 'STAFF' || strip_tags($aRow['paiement_type']) == 'RELAIS_ATTENTE') { 	
			$action .='<a href="javascript:;" onclick="modif_inscription_internaute('.$idInternaute.','.$idInscriptionEpreuveInternaute.','.$_GET['id_epreuve'].','.$aRow["idEpreuveParcours"].',\''.addslashes_form_to_sql($nom).'\',\''.addslashes_form_to_sql($aRow['prenomInternaute']).'\',\'supp\')"> <i data-toggle="tooltip" data-placement="top" data-original-title="Supprimer" class="text-danger fa fa-1x fa-trash-o"></i></a>';
		 }

		 if ($paiement_type == 'CB') { 	
			$action .='<span id="aff_annule_'.$idInscriptionEpreuveInternaute.'"><a href="javascript:;" onclick="modif_inscription_internaute('.$idInternaute.','.$idInscriptionEpreuveInternaute.','.$_GET['id_epreuve'].','.$aRow['idEpreuveParcours'].',\''.addslashes_form_to_sql($nom).'\',\''.addslashes_form_to_sql($aRow['prenomInternaute']).'\',\'annule\')"> <i data-toggle="tooltip" data-placement="top" data-original-title="Annule sa participation (sans remboursement)" class="text-danger fa fa-1x fa-remove"></i></a></span>';
		 }
				 
		 if ($paiement_type == 'CB' || $paiement_type == 'CHQ' || $paiement_type == 'GRATUIT' || $paiement_type == 'AUTRE') { 	
			$action .='<a href="javascript:;" onclick="reedition('.$idInternaute.','.$idInscriptionEpreuveInternaute.','.$_GET['id_epreuve'].','.$aRow["idEpreuveParcours"].',\''.addslashes_form_to_sql($nom).'\',\''.addslashes_form_to_sql($row['prenomInternaute']).'\',\'supp\')"> <i data-toggle="tooltip" data-placement="top" data-original-title="Renvoyer la confirmation d\\\'inscription au participant" class="text-danger fa fa-1x fa-external-link"></i></a>';
		 } 
		*/
		$action ='';
		//if ($aRow['idEpreuve']==4372) {
			//echo $aRow['dateDebutInscription'];
			$insc_end = diff_date_include(date('Y-m-d'),$aRow['dateFinInscription']);
			//$insc_end = diff_date($aRow['dateDebutInscription'],$aRow['dateFinInscription']);
			if ($insc_end==0)
			{
				$insc_start = diff_date_include(date('Y-m-d'),$aRow['dateDebutInscription']);
				if ($insc_start==1)
				{
					$action ='<span class="text-success">EN COURS</span>';
				}
				else
				{
					$action ='<span class="text-warning">BIENTOT</span>';
				}
			}
			else
			{
				
				$action ='<span class="text-danger">NON DEFINI</span>';
			}
		//}
		//$action =' xxxx';
	
		
		
		$row[]=$action;

		//$row[]='toto';
		//print_r($row);
		$output['aaData'][] = $row;
	}
	//print_r($output);
	echo json_encode( $output );
?>	
