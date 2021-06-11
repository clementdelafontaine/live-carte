<?php 
//clean sans le dernier require
ini_set('display_errors', 1);

require_once("/var/www/vhosts/ats-sport.com/httpdocs/temp/admin/assets/plugins/PHPExcel-1.8/Classes/PHPExcel.php");
require_once("/var/www/vhosts/ats-sport.com/httpdocs/temp/admin/assets/plugins/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php");
require_once("/var/www/vhosts/ats-sport.com/httpdocs/temp/admin/assets/plugins/tcpdf/tcpdf.php");
require_once("/var/www/vhosts/ats-sport.com/httpdocs/temp/admin/includes/functions_mail_julien.php");


/*
require_once("assets/plugins/PHPExcel-1.8/Classes/PHPExcel.php");
require_once("assets/plugins/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php");
require_once("assets/plugins/tcpdf/tcpdf.php");
require_once("functions_mail.php");
*/
//Convertion numéro de puce -> n° de dossard
//Prévoir cas des puces non encodées ou puces dans sac à dos voiture ex. 00BC44880EEEE455985568585

function serialToBib( $serial )
{
	$dossard = substr( $serial, 3 );		//On supprime le "[" et la lettre ou numéro en début de chaine
	$dossard = ltrim( $dossard, "0");		//On supprime les zéros entre la lettre le numéro de dossard
	$dossard = substr( $dossard, 0, -1 );

	return $dossard;
}

//Recherche du coureur associé à la puce
function runner( $idEpreuve, $dossard )
{
	//Grâce au numéro de la puce on recupère l'id du coureur + id du parcours concerné pour l'épreuve concernée
	$qrunner  ="SELECT i.idInternaute, iei.dossard ";
	$qrunner .="FROM r_internaute AS i JOIN r_inscriptionepreuveinternaute AS iei ON i.idInternaute = iei.idInternaute ";
	$qrunner .="WHERE iei.idEpreuve = ".$idEpreuve." AND iei.dossard = '".$dossard."' ";
	$qrunner .="ORDER BY iei.idInscriptionEpreuveInternaute DESC limit 1";

	$result = $mysqli->query( $qrunner );
	$runner = mysqli_fetch_assoc( $result );
			
	if( !$runner ) return false;
	else return $runner;
}

function insert_fichier_resultats( $idEpreuve, $lecteur, $file )
{
	if( !is_uploaded_file( $file['fichier']['tmp_name'] ) )
	{
		return display_alert( 'danger', '<strong>Erreur !</strong> Le fichier n\'a pas été correctement transmis. Veuillez réessayer.' );
	}
	else if( $file['fichier']['type'] != "text/plain" )
	{
		return display_alert( 'danger', '<strong>Erreur !</strong> Le fichier doit être au format <b>txt séparateur tabulation</b>' );
	}
	else
	{
		// nom du fichier destination :
		$nomfichier = $file["fichier"]["name"];
		$nomfichier = preg_replace("/#/", " ", $nomfichier);
		$nomfichier = preg_replace("/#/", "'", $nomfichier);
		$nomfichier = preg_replace("/#/", "é", $nomfichier);
		$nomfichier = preg_replace("/#/", "&", $nomfichier);
		$nomfichier = preg_replace("/#/", "(", $nomfichier);
		$nomfichier = preg_replace("/#/", ")", $nomfichier);
		$nomfichier = preg_replace("/#/", "è", $nomfichier);
		$nomfichier = preg_replace("/#/", "ç", $nomfichier);
		$nomfichier = preg_replace("/#/", "à", $nomfichier);
		$nomfichier = "resultats/".$nomfichier;
		if( move_uploaded_file( $file["fichier"]["tmp_name"], $nomfichier ) == FALSE )
		{
			return display_alert( 'danger', '<strong>Erreur !</strong> Le fichier n\'a pas été correctement transmis. Veuillez réessayer.' );
		}
		else if( ( $fichier = fopen( $nomfichier,"r" ) ) == FALSE )
		{
			return display_alert( 'danger', '<strong>Erreur !</strong> Le fichier ne peut pas être lu. Veuillez réessayer.' );
		}
		else
		{			
			$nb = 0;
			$tabPassage = array();
			$tabRebip = array();
			$config = explode( "-", $lecteur );

			//Temps de rebippage
			$qrebip  ="SELECT rebippage FROM chrono_lecteur ";
			$qrebip .="WHERE idEpreuve = ".$idEpreuve." AND lieu = '".$config[1]."'";

			$rrebip = $mysqli->query( $qrebip );
			$rebip = mysqli_fetch_assoc( $rrebip );

			while( !feof( $fichier ) ) // lecture ligne par ligne jusqu'à la fin du fichier
			{
				//Lecture de la ligne courante
				$line = explode( ":", fgets($fichier, "4096" ) );

				if( $line[0][0] == "[" ) $dossard = serialToBib( $line[0] ); //Format chronospeedway et raspberry = [30000001]:a20171201102839001
				else $dossard = $line[0]; //Format chronomaster = 1:a20171201102839001

				$horaire = date( "Y-m-d H:i:s", strtotime( substr( $line[1], 1, 14 ) ) );
									
				//Recherche du coureur
				$runner = runner( $idEpreuve, $dossard );

				//Nombre de passage par coureur pour le lieu donné
				if( isset( $tabPassage[$idEpreuve.$runner['dossard'].$config[1]] ) ) $tabPassage[$idEpreuve.$runner['dossard'].$config[1]]++;
				else $tabPassage[$idEpreuve.$runner['dossard'].$config[1]] = 1;

				//Rebippage ou non de la puce
				if( isset( $tabRebip[$idEpreuve.$runner['dossard'].$config[1]] ) )
				{
					$date1 = new DateTime( $tabRebip[$idEpreuve.$runner['dossard'].$config[1]] );
					$date2 = new DateTime( $horaire );

					$temps_rebip = $date2->format('U') - $date1->format('U');

					if( $temps_rebip >= $rebip['rebippage'] )
					{
						$tabRebip[$idEpreuve.$runner['dossard'].$config[1]] = $horaire;
						$rebippage = 0;
					}
					else
					{
						$rebippage = 1;
					}
				}
				else
				{
					$tabRebip[$idEpreuve.$runner['dossard'].$config[1]] = $horaire;
					$rebippage = 0;
				}
								
				//On insert le résultat si on a trouvé un coureur associé à la puce
				if( !empty( $runner['idInternaute'] ) )
				{
					//Insertion du résultat, champs fixe
					$qresultats  = "INSERT INTO chrono_resultats ";
					$qresultats .= "(idInternaute, dossard, idEpreuve, idLecteur, lieu, passage, horaire, status, type, indice, rebip ) VALUES ";
					$qresultats .= "(";
					$qresultats .= $runner['idInternaute'].",";
					$qresultats .= $runner['dossard'].",";
					$qresultats .= $idEpreuve.",";
					$qresultats .= $config[0].",";
					$qresultats .= "'".$config[1]."',";
					$qresultats .= $tabPassage[$idEpreuve.$runner['dossard'].$config[1]].",";
					$qresultats .= "'".$horaire."',";
					$qresultats .= "'OK',";
					$qresultats .= "'RSP',";
					$qresultats .= $nb.",";
					$qresultats .= $rebippage.") ON DUPLICATE KEY UPDATE horaire = '".$horaire."',rebip = ".$rebippage;
							
					$mysqli->query( $qresultats );

					$nb++;
				}
			}
			fclose($fichier);

			return display_alert( 'success', '<strong>'.sizeof( $tabPassage )." résultats ont été enregistrés</strong>" );
		}
	}
}

function delete_parcours_resultats( $idResultatsParcours )
{
	$qDelete = "DELETE FROM h_resultatsparcours WHERE idResultatsParcours=".$idResultatsParcours;
	$mysqli->query( $qDelete );
}

function delete_config_resultats( $idResultatsParcours )
{
	$qDelete = "DELETE FROM h_resultatsconfig WHERE idResultatsParcours=".$idResultatsParcours;
	$mysqli->query( $qDelete );
}

function insert_parcours_resultats( $idEpreuve, $idEpreuveParcours, $nomParcoursResultats, $type, $horaireDepart )
{
	$newRp  = "INSERT INTO h_resultatsparcours ";
	$newRp .= "(idEpreuve, idEpreuveParcours, nomParcoursResultats, type, horaireDepart";	
	$newRp .= ") VALUES (";
	$newRp .= addslashes( $idEpreuve ).", ";
	$newRp .= addslashes( $idEpreuveParcours ).", ";
	$newRp .= "'".addslashes( $nomParcoursResultats )."', ";
	$newRp .= "'".addslashes( $type )."', ";
	$newRp .= "'".addslashes( date( "Y-m-d H:i:s", strtotime( $horaireDepart ) ) )."')";		

	$mysqli->query($newRp);
	$idResultatsParcours = $mysqli->insert_id; 

	return $idResultatsParcours;
}

function insert_config_lecteur( $idEpreuve, $data )
{
	foreach( $data['lecteur'] as $lecteur )
	{
		$chrono_lecteur  = "INSERT INTO chrono_lecteur ";
		$chrono_lecteur .= "(idLecteur, idEpreuve, lieu, date_min, date_max, rebippage, distance_depart ";	
		$chrono_lecteur .= ") VALUES (";
		$chrono_lecteur .= $lecteur.", ";
		$chrono_lecteur .= addslashes( $idEpreuve ).", ";
		$chrono_lecteur .= "'".$data['lieu'][$lecteur]."', ";
		$chrono_lecteur .= "'".addslashes( date( "Y-m-d H:i:s", strtotime( $data['date_min'][$lecteur] ) ) )."', ";
		$chrono_lecteur .= "'".addslashes( date( "Y-m-d H:i:s", strtotime( $data['date_max'][$lecteur] ) ) )."', ";
		$chrono_lecteur .= $data['rebip'][$lecteur].", ";
		$chrono_lecteur .= $data['distance_depart'][$lecteur].") ";
		$chrono_lecteur .= "ON DUPLICATE KEY UPDATE lieu = '".$data['lieu'][$lecteur]."', ";
		$chrono_lecteur .= "date_min = '".addslashes( date( "Y-m-d H:i:s", strtotime( $data['date_min'][$lecteur] ) ) )."', ";
		$chrono_lecteur .= "date_max = '".addslashes( date( "Y-m-d H:i:s", strtotime( $data['date_max'][$lecteur] ) ) )."', ";
		$chrono_lecteur .= "rebippage = ".addslashes( $data['rebip'][$lecteur] ).", ";
		$chrono_lecteur .= "distance_depart = ".addslashes( $data['distance_depart'][$lecteur] );
		$mysqli->query($chrono_lecteur);

		$del_chrono_lecteur .= "DELETE FROM chrono_lecteur WHERE idEpreuve = ".$idEpreuve." AND idLecteur not in(".implode( ",", $data['lecteur']).")";
		$mysqli->query($del_chrono_lecteur);
	}
}

function insert_config_vague( $idEpreuve, $data )
{
	$chrono_vague = "INSERT INTO chrono_vague ";
	$chrono_vague .= "(vague, idEpreuve, date ";	
	$chrono_vague .= ") VALUES (";
	$chrono_vague .= $data["vague"].", ";
	$chrono_vague .= addslashes( $idEpreuve ).", ";
	$chrono_vague .= "'".addslashes( date( "Y-m-d H:i:s", strtotime( $data["date_vague"] ) ) )."') ";
	$chrono_vague .= "ON DUPLICATE KEY UPDATE date = '".addslashes( date( "Y-m-d H:i:s", strtotime( $data["date_vague"] ) ) )."'";

	$result = $mysqli->query($chrono_vague);

	if( $result ) return display_alert( 'success', 'Vague n° '.$data['vague'].' correctement ajoutée' );
	else return display_alert( 'danger', '<strong>Erreur !</strong> lors du traitement de la vague n° '.$data['vague'] );
}

function insert_config_resultats( $idEpreuve, $idResultatsParcours, $ip, $lieu, $date )
{
	$newRc = "INSERT INTO h_resultatsconfig ";
	$newRc .= "(idEpreuve, idResultatsParcours, ip, lieu, date ";	
	$newRc .= ") VALUES (";
	$newRc .= addslashes( $idEpreuve ).", ";
	$newRc .= addslashes( $idResultatsParcours ).", ";
	$newRc .= "'".addslashes( $ip )."', ";
	$newRc .= "'".addslashes( $lieu )."', ";
	$newRc .= "'".addslashes( date( "Y-m-d", strtotime( $date ) ) )."')";		

	$mysqli->query($newRc);
	$idResultatsParcours = $mysqli->insert_id;
}

function delete_resultats_lieu( $idEpreuve, $lieu )
{
	$qDelete = "DELETE FROM chrono_resultats WHERE idEpreuve = ".$idEpreuve." AND lieu like '".$lieu."'";
	$mysqli->query( $qDelete );
}

/*
	Fonction display_alert
	Affiche une alerte bootstrap selon son type 
	type peut prendre comme valeur : info - success - warning - danger
	message sera le texte affiché dans l'alert
	retourne l'alert ou false si le type n'existe pas
*/
function display_alert( $type, $message ) 
{
	if( $type == 'info' || $type == 'success' || $type == 'warning' || $type == 'danger' )
	{
		$alert ='
			<div class="alert alert-'.$type.' alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				'.$message.'
			</div>
		';

		return $alert;
	}
	else
	{
		return false;
	}
}


/*
	Fonction insert_rider
	Insère un coureur en base dans le cas où :
		- inscrit le jour de la course
*/
function insert_rider( $idEpreuve, $idEpreuveParcours, $dossard, $nom, $prenom, $sexe, $club, $naissance = '0000-00-00', $email = '' )
{
	$newRider  = "INSERT INTO r_internaute ";
	$newRider .= "(loginInternaute, passInternaute, validation, dateInscription, nomInternaute, prenomInternaute, ";
	$newRider .= "sexeInternaute, naissanceInternaute, emailInternaute, clubInternaute, adresseInternaute, cpInternaute, villeInternaute, ";
	$newRider .= "paysInternaute, typeInternaute, telephone, date_epreuve  ";	
	$newRider .= ") VALUES (";
	$newRider .= "'ATS-".addslashes( substr( $nom, 0, 3 ).substr( $prenom, 0, 3 ).date( "YmdHis" ) )."', ";
	$newRider .= "'@".addslashes( substr( $nom, 0, 2)."_".substr( $prenom, 0, 2) )."', ";
	$newRider .= "'oui', ";
	$newRider .= "'".date( "Y-m-d H:i:s" )."', ";
	$newRider .= "UPPER('".$nom."'), ";
	$newRider .= "UPPER('".$prenom."'), ";
	$newRider .= "UPPER('".$sexe."'), ";
	$newRider .= "'".date( "Y-m-d", strtotime( $naissance ) )."', ";
	$newRider .= "'".$email."', ";
	$newRider .= "'".$club."', ";
	$newRider .= "'', ";
	$newRider .= "'', ";
	$newRider .= "'', ";
	$newRider .= "'', ";
	$newRider .= "'coureur', ";
	$newRider .= "'', ";
	$newRider .= "'0000-00-00')";		

	$mysqli->query($newRider);
	$idInternaute = $mysqli->insert_id;

	$newIei  = "INSERT INTO r_inscriptionepreuveinternaute ";
	$newIei .= "(idEpreuveParcoursTarif, idEpreuveParcours, idEpreuve, idInternaute, paiement_date, date_insc, paiement_type, observation, dossard, new) ";
	$newIei .= "VALUES (";
	$newIei .= "0, ";
	$newIei .= $idEpreuveParcours.", ";
	$newIei .= $idEpreuve.", ";
	$newIei .= $idInternaute.", ";
	$newIei .= "'".date("Y-m-d H:i:s")."', ";
	$newIei .= "'".date("Y-m-d H:i:s")."', ";
	$newIei .= "'AUTRE', ";
	$newIei .= "'Saisie sur place', ";
	$newIei .= $dossard.", ";
	$newIei .= "'non') ";
											
	$mysqli->query($newIei);

	return $idInternaute;
}


/*
	Fonction search_rider
	cherche un coureur dans la base en vu de lui lier un résultat
	return idInternaute si le coureur existe
	retourne false si non trouvé
*/
function search_rider( $idEpreuve, $dossard, $nom, $prenom )
{
	$rider  = "SELECT i.idInternaute FROM r_internaute AS i JOIN r_inscriptionepreuveinternaute AS iei ON i.idInternaute = iei.idInternaute ";
	$rider .= "WHERE iei.idEpreuve=".$idEpreuve." ";
	$rider .= "AND iei.dossard=".$dossard." ";
	$rider .= "AND i.nomInternaute='".$nom."' ";
	$rider .= "AND i.prenomInternaute='".$prenom."' ";

	$result_rider = $mysqli->query( $rider );
	$info_rider = mysqli_fetch_assoc( $result_rider );

	return $info_rider["idInternaute"];
}

/*
	Fonction export_excel
	enregistre le fichier résultat au format excel
*/
function export_excel( $epreuve, $parcours, $fichier, $champs_non_affiche )
{
	rewind($fichier);

	$classeur = new PHPExcel;
	$classeur->getProperties()->setCreator("ats-sport.com");
	PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);

	$nomExportExcel = NomFichierValide( $epreuve."_".$parcours );

	$header = '';
	$data ='';

	$colonne=0;
	$ligne=5;

	$feuille = $classeur->createSheet(0);
	$classeur->setActiveSheetIndex(0);
	$feuille=$classeur->getActiveSheet();
	$feuille->setTitle('Résultats');

	//Lecture des entêtes
	fgets( $fichier , "4096" ); //1ère ligne ignorée
	$head = fgets( $fichier, "4096" );
	$head = rtrim( $head ,"\n\r\t\0" );
	$header=explode( "\t", $head );
	$reverse_header = array();


	//Style appliqué au fichier
	$styleArray = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		),
		'alignment' => array(
	       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	    )
	);

	$styleArray2 = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		),
		'alignment' => array(
	       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
	    )
	);

	//Entêtes résultats
	$indice_entete = 0;
	foreach( $header as $key => $value )
	{
		if( !in_array( $key, $champs_non_affiche ) )
		{
			$feuille->setCellValueByColumnAndRow( $indice_entete, $ligne, $value );
			$limits = $feuille->getHighestRowAndColumn();
			$indice_entete++;
		}
	}
	$feuille->getStyle('A1:'.$limits["column"].$limits["row"].'')->getFont()->setBold(true);

	//Entêtes fichier
	$feuille->mergeCells($pRange = 'A1:'.$feuille->getHighestColumn().($ligne-1).'');
	$feuille->setCellValue( 'A1' , $epreuve."\n".$parcours );
	$feuille->getStyle('A1')->applyFromArray($styleArray2);
	$feuille->getStyle('A1')->getFont()->setSize(16);
	$feuille->getStyle('A1')->getAlignment()->setWrapText(true);

	$objDrawing = new PHPExcel_Worksheet_Drawing();
	$objDrawing->setName('Logo');
	$objDrawing->setDescription('Logo');
	$logo = './images/logoATS-bleu2.jpg'; // Provide path to your logo file
	$objDrawing->setPath($logo);
	$objDrawing->setCoordinates('J2');
	$objDrawing->setHeight(60); // logo height
	$objDrawing->setWorksheet($feuille);

	$ligne++;

	// lecture et copie ligne par ligne jusqu'à la fin du fichier
	while( !feof( $fichier ) )
	{
		//Lecture de la ligne courante
		$line = fgets( $fichier, "4096" );
		$line = rtrim( $line ,"\n\r\t\0" );
		$row = explode( "\t", $line );

		$indice_ligne = 0;
		foreach( $row as $key => $value )
		{
			if( !in_array( $key, $champs_non_affiche ) )
			{
				$feuille->setCellValueByColumnAndRow( $indice_ligne, $ligne, utf8_encode( $row[$key] ) );
				$indice_ligne++;
			}
		}

		$ligne++;
	}

	$limits = $feuille->getHighestRowAndColumn();

	$feuille->getStyle('A5:'.$limits["column"].$limits["row"].'')->applyFromArray($styleArray);
	unset($styleArray);

	$classeur->setActiveSheetIndex(0);

	if ($_GET['type'] =='xls5') 
	{
	    header('Content-Type: application/vnd.ms-excel');
	    header('Content-Disposition: attachment;filename="'.$nomEpreuve.'.xls"');
	    header('Cache-Control: max-age=0');
	    $writer = PHPExcel_IOFactory::createWriter($classeur, 'Excel5');
	}
	elseif ($_GET['type'] =='csv')
	{	
		header('Content-Type: text/csv');
	    header('Content-Disposition: attachment;filename="'.$nomEpreuve.'.csv"');
	    header('Cache-Control: max-age=0');

	    $writer = PHPExcel_IOFactory::createWriter($classeur, 'CSV');
	    $writer->setDelimiter(';');
	    $writer->setEnclosure('');
	    $writer->setLineEnding("\r\n");
	    $writer->setSheetIndex(0);
	}
	else
	{	
		header('Content-Type: application/vnd.ms-excel');
	    header('Content-Disposition: attachment;filename="'.$nomEpreuve.'.xls"');
	    header('Cache-Control: max-age=0');
	    $writer = PHPExcel_IOFactory::createWriter($classeur, 'Excel5');
	}
	$writer->save( "../resultats/".$nomExportExcel.".xls" );

	return $nomExportExcel.".xls";
}

/*
	Params :
	$epreuve = nom de l'épreuve
	$date = date de l'épreuve
	$ville = ville de l'épreuve
	$parcours = nom du parcours
	$fichier = fichier résultat type chronomaster
	$champs = entête du fichier résultat. ex. $tab[0] = 'NOM' => $tab['NOM'] = 0. les indices et valeurs ont été inversés
*/
function export_logica_txt( $epreuve, $date, $ville, $parcours, $fichier, $champs, $champs2, $champs3 )
{
	rewind( $fichier );
	$nomExportLogica = NomFichierValide( "logica_".$epreuve."_".$parcours.".txt" );
	copy( "../resultats/logica_2019.txt" , "../resultats/".$nomExportLogica );

	// on ignore la premiere ligne du fichier de résultats
	fgets( $fichier, "4096" );
	// on ignore la ligne des champs du fichier de résultats
	fgets( $fichier, "4096" );

	// on ouvre le fichier dans lequel on veut écrire
	$fichier_logica=fopen( "../resultats/".$nomExportLogica, "a" );

	$epreuve = utf8_decode($epreuve);
	$parcours = utf8_decode($parcours);
	$ville = utf8_decode($ville);

	$tabPays = array();
	$qPays = "SELECT alpha_ffa, nom_fr_fr FROM pays";
	$rPays = $mysqli->query( $qPays );
	while( $pays = mysqli_fetch_assoc( $rPays ) )
	{
		$tabPays[$pays['nom_fr_fr']] = $pays['alpha_ffa'];
	}

	while ( !feof( $fichier ) ) // lecture ligne par ligne tant qu'on ne rencontre pas le code: fin de fichier(feof)
	{
		$tab = explode( "\t", fgets( $fichier, "4096" ) );
		if( !empty($tab) && sizeof($tab) > 1 )
		{
			$dossard = isset( $champs['DOSSARD'] ) ? trim( $tab[$champs['DOSSARD']], "\n\r\t\0" ):"";
			$licence = isset( $champs3['LICENCE'] ) ? trim( $tab[$champs3['LICENCE']], "\n\r\t\0" ):"";
			$nom = isset( $champs['NOM'] ) ? trim( $tab[$champs['NOM']], "\n\r\t\0" ):"";
			$prenom = isset( $champs['PRENOM'] ) ? trim( $tab[$champs['PRENOM']], "\n\r\t\0" ):"";
			$naissance = isset( $champs3['NAISSANCE'] ) ? $tab[$champs3['NAISSANCE']] :"";
			$club = isset( $champs['CLUB'] ) ? trim( $tab[$champs['CLUB']], "\n\r\t\0" ):"";
			$cat = isset( $champs['CAT'] ) ? trim( $tab[$champs['CAT']], "\n\r\t\0" ):"";
			$sexe = isset( $champs['SEXE'] ) ? trim( $tab[$champs['SEXE']], "\n\r\t\0" ):"";
			$place = $tab[0];
			$pays = (isset( $champs2['PAYS'] ) && $tab[$champs2['PAYS']] != '' ) ? trim( $tabPays[ucfirst( strtolower( $tab[$champs2['PAYS']] ) )], "\n\r\t\0" ):"FRA";
			//$distance = isset( $champs3['PARCOURS'] ) ? trim( $tab[$champs3['PARCOURS']], "\n\r\t\0" ):"";
			//$codeappel = isset( $champs3['codeappel'] ) ? trim( $tab[$champs3['codeappel']], "\n\r\t\0" ):"";
			//$typelicence = isset( $champs3['Type licence'] ) ? trim( $tab[$champs3['Type licence']], "\n\r\t\0" ):"";

			if( isset( $champs['TEMPS'] ) && date( "H", strtotime( $tab[$champs['TEMPS']] ) ) == "00" )
				$temps =  date( "is", strtotime( $tab[$champs['TEMPS']] ) );
			else
				$temps =  date( "His", strtotime( $tab[$champs['TEMPS']] ) );

			if( isset( $champs['TEMPS REEL'] ) && date( "H", strtotime( $tab[$champs2['TEMPS REEL']] ) ) == "00" )
				$tempsreel =  date( "is", strtotime( $tab[$champs2['TEMPS REEL']] ) );
			else
				$tempsreel =  date( "His", strtotime( $tab[$champs2['TEMPS REEL']] ) );


			fwrite( $fichier_logica, $dossard."\t".$licence."\t".$nom."\t".$prenom."\t".$pays."\t\t".$club."\t\t".$naissance."\t".$cat."\t".$sexe."\t\t".$place."\t".$temps."\t".$tempsreel."\n" );
			//fwrite( $fichier_logica, $dossard."\t".$licence."\t".$nom."\t".$prenom."\t".$pays."\t\t\t".$club."\t\t\t\t\t\t\t\t".$naissance."\t".$cat."\t".$sexe."\t\t\t".$epreuve."\t".$parcours."\t".$codeappel."\t".$distance."\t\t".$place."\t".$temps."\t\t".$ville."\t".$epreuve."\t".$date."\t\t\t\t\t\t".$typelicence."\t\t\t\t\t".$tempsreel."\n" );
		}
	}

	fclose( $fichier_logica );
	return $nomExportLogica;
}

function export_FFTRI( $epreuve, $parcours, $fichier, $champs, $champs2, $champs3 )
{
	rewind($fichier);

	$classeur = new PHPExcel;
	$classeur->getProperties()->setCreator("ats-sport.com");
	PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);

	$nomExportExcel = NomFichierValide( "FFTRI_".$epreuve."_".$parcours );

	$header = '';
	$data ='';

	$colonne=0;
	$ligne=1;

	$feuille = $classeur->createSheet(0);
	$classeur->setActiveSheetIndex(0);
	$feuille=$classeur->getActiveSheet();
	$feuille->setTitle('Résultats FFTRI');

	//Lecture des entêtes
	fgets( $fichier , "4096" ); //1ère ligne ignorée
	fgets( $fichier, "4096" );	//2ème ligne ignorée

	//Style appliqué au fichier
	$styleArray = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		),
		'alignment' => array(
	       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	    )
	);

	$styleArray2 = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		),
		'alignment' => array(
	       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
	    )
	);

	$tabPays = array();
	$qPays = "SELECT alpha_ffa, nom_fr_fr FROM pays";
	$rPays = $mysqli->query( $qPays );
	while( $pays = mysqli_fetch_assoc( $rPays ) )
	{
		$tabPays[$pays['nom_fr_fr']] = $pays['alpha_ffa'];
	}

	//Entêtes résultats
	$indice_entete = 0;
	$header = array( 'Rang', 'N°', 'Nom', 'Prénom', 'Sexe', 'Nat.', 'Rang /Cat.', 'Cat.', 'U23', 'N° Licence', 'Club', 'Discipline #1', 'Transition #1', 'Discipline #2', 'Transition #2', 'Discipline #3', 'Total' );
	foreach( $header as $key => $value )
	{
		$feuille->setCellValueByColumnAndRow( $indice_entete, $ligne, $value );
		$limits = $feuille->getHighestRowAndColumn();
		$indice_entete++;
	}

	$feuille->getStyle('A1:'.$limits["column"].$limits["row"].'')->getFont()->setBold(true);
	$feuille->getStyle('A1:'.$limits["column"].$limits["row"].'')->applyFromArray( 
	    array( 'fill' => 
	        array( 'type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => 
	            array('rgb' => '474747') 
	        ) 
	    ) 
	);

	$feuille->getStyle('A1:'.$limits["column"].$limits["row"].'')->applyFromArray(array('font' => array('bold' => true,'color' => array('rgb' => 'FFFFFF'))));

	$ligne++;

	// lecture et copie ligne par ligne jusqu'à la fin du fichier
	while( !feof( $fichier ) )
	{
		$tab = explode( "\t", fgets( $fichier, "4096" ) );
		if( !empty($tab) && sizeof($tab) > 1 )
		{
			$dossard = isset( $champs['DOSSARD'] ) ? trim( $tab[$champs['DOSSARD']], "\n\r\t\0" ):"";
			$licence = isset( $champs3['LICENCE'] ) ? trim( $tab[$champs3['LICENCE']], "\n\r\t\0" ):"";
			$nom = isset( $champs['NOM'] ) ? trim( $tab[$champs['NOM']], "\n\r\t\0" ):"";
			$prenom = isset( $champs['PRENOM'] ) ? trim( $tab[$champs['PRENOM']], "\n\r\t\0" ):"";
			$naissance = isset( $champs3['NAISSANCE'] ) ? $tab[$champs3['NAISSANCE']] :"";
			$club = isset( $champs['CLUB'] ) ? trim( $tab[$champs['CLUB']], "\n\r\t\0" ):"";
			$cat = isset( $champs['CAT'] ) ? trim( $tab[$champs['CAT']], "\n\r\t\0" ):"";
			$plcat = isset( $champs['PL CAT'] ) ? trim( $tab[$champs['PL CAT']], "\n\r\t\0" ):"";
			$sexe = isset( $champs['SEXE'] ) ? trim( $tab[$champs['SEXE']], "\n\r\t\0" ):"";
			$place = $tab[0];
			$pays = (isset( $champs2['PAYS'] ) && $tab[$champs2['PAYS']] != '' ) ? trim( $tabPays[ucfirst( strtolower( $tab[$champs2['PAYS']] ) )], "\n\r\t\0" ):"FRA";
			$naissance_ = explode("/",$naissance);
			$u23 = ( date("Y",strtotime( $naissance_[2].'-'.$naissance_[1].'-'.$naissance_[0] )) >= 1996 ? "*" : "" );
			$discipline1 = isset( $champs2['Discipline#1'] ) ? trim( $tab[$champs2['Discipline#1']], "\n\r\t\0" ):"";
			$discipline2 = isset( $champs2['Discipline#2'] ) ? trim( $tab[$champs2['Discipline#2']], "\n\r\t\0" ):"";
			$discipline3 = isset( $champs2['Discipline#3'] ) ? trim( $tab[$champs2['Discipline#3']], "\n\r\t\0" ):"";
			$transition1 = isset( $champs2['Transition#1'] ) ? trim( $tab[$champs2['Transition#1']], "\n\r\t\0" ):"";
			$transition2 = isset( $champs2['Transition#2'] ) ? trim( $tab[$champs2['Transition#2']], "\n\r\t\0" ):"";
			$temps =  date( "H:i:s", strtotime( $tab[$champs['TEMPS']] ) );

			$feuille->setCellValueByColumnAndRow( 0, $ligne, utf8_encode( $place ) );
			$feuille->setCellValueByColumnAndRow( 1, $ligne, utf8_encode( $dossard ) );
			$feuille->setCellValueByColumnAndRow( 2, $ligne, utf8_encode( $nom ) );
			$feuille->setCellValueByColumnAndRow( 3, $ligne, utf8_encode( $prenom ) );
			$feuille->setCellValueByColumnAndRow( 4, $ligne, utf8_encode( $sexe ) );
			$feuille->setCellValueByColumnAndRow( 5, $ligne, utf8_encode( $pays ) );
			$feuille->setCellValueByColumnAndRow( 6, $ligne, utf8_encode( $plcat ) );
			$feuille->setCellValueByColumnAndRow( 7, $ligne, utf8_encode( $cat ) );
			$feuille->setCellValueByColumnAndRow( 8, $ligne, utf8_encode( $u23 ) );
			$feuille->setCellValueByColumnAndRow( 9, $ligne, utf8_encode( $licence ) );
			$feuille->setCellValueByColumnAndRow( 10, $ligne, utf8_encode( $club ) );
			$feuille->setCellValueByColumnAndRow( 11, $ligne, utf8_encode( $discipline1 ) );
			$feuille->setCellValueByColumnAndRow( 12, $ligne, utf8_encode( $transition1 ) );
			$feuille->setCellValueByColumnAndRow( 13, $ligne, utf8_encode( $discipline2 ) );
			$feuille->setCellValueByColumnAndRow( 14, $ligne, utf8_encode( $transition2 ) );
			$feuille->setCellValueByColumnAndRow( 15, $ligne, utf8_encode( $discipline3 ) );
			$feuille->setCellValueByColumnAndRow( 16, $ligne, utf8_encode( $temps ) );
		}

		$ligne++;
	}

	$limits = $feuille->getHighestRowAndColumn();

	$feuille->getStyle('A1:'.$limits["column"].$limits["row"].'')->applyFromArray($styleArray);
	unset($styleArray);

	$classeur->setActiveSheetIndex(0);

	if ($_GET['type'] =='xls5') 
	{
	    header('Content-Type: application/vnd.ms-excel');
	    header('Content-Disposition: attachment;filename="'.$nomEpreuve.'.xls"');
	    header('Cache-Control: max-age=0');
	    $writer = PHPExcel_IOFactory::createWriter($classeur, 'Excel5');
	}
	elseif ($_GET['type'] =='csv')
	{	
		header('Content-Type: text/csv');
	    header('Content-Disposition: attachment;filename="'.$nomEpreuve.'.csv"');
	    header('Cache-Control: max-age=0');

	    $writer = PHPExcel_IOFactory::createWriter($classeur, 'CSV');
	    $writer->setDelimiter(';');
	    $writer->setEnclosure('');
	    $writer->setLineEnding("\r\n");
	    $writer->setSheetIndex(0);
	}
	else
	{	
		header('Content-Type: application/vnd.ms-excel');
	    header('Content-Disposition: attachment;filename="'.$nomEpreuve.'.xls"');
	    header('Cache-Control: max-age=0');
	    $writer = PHPExcel_IOFactory::createWriter($classeur, 'Excel5');
	}
	$writer->save( "../resultats/".$nomExportExcel.".xls" );

	return $nomExportExcel.".xls";
}

function export_FFTRI_categories( $epreuve, $parcours, $fichier, $champs, $champs2, $champs3 )
{
	rewind($fichier);

	$classeur = new PHPExcel;
	$classeur->getProperties()->setCreator("ats-sport.com");
	PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);

	$nomExportExcel = NomFichierValide( "FFTRI_".$epreuve."_".$parcours."_categories" );

	$header = '';
	$data ='';

	$colonne=0;
	$ligne=1;

	$feuille = $classeur->createSheet(0);
	$classeur->setActiveSheetIndex(0);
	$feuille=$classeur->getActiveSheet();
	$feuille->setTitle('Résultats FFTRI catégories');

	//Lecture des entêtes
	fgets( $fichier , "4096" ); //1ère ligne ignorée
	fgets( $fichier, "4096" );	//2ème ligne ignorée

	//Style appliqué au fichier
	$styleArray = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		),
		'alignment' => array(
	       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	    )
	);

	$styleArray2 = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		),
		'alignment' => array(
	       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
	    )
	);

	$tabPays = array();
	$qPays = "SELECT alpha_ffa, nom_fr_fr FROM pays";
	$rPays = $mysqli->query( $qPays );
	while( $pays = mysqli_fetch_assoc( $rPays ) )
	{
		$tabPays[$pays['nom_fr_fr']] = $pays['alpha_ffa'];
	}

	//Entêtes résultats
	$indice_entete = 0;
	$header = array( 'Rang', 'N°', 'Nom', 'Prénom', 'Sexe', 'Nat.', 'Rang /Cat.', 'Cat.', 'U23', 'N° Licence', 'Club', 'Discipline #1', 'Transition #1', 'Discipline #2', 'Transition #2', 'Discipline #3', 'Total' );
	foreach( $header as $key => $value )
	{
		$feuille->setCellValueByColumnAndRow( $indice_entete, $ligne, $value );
		$limits = $feuille->getHighestRowAndColumn();
		$indice_entete++;
	}

	$feuille->getStyle('A1:'.$limits["column"].$limits["row"].'')->getFont()->setBold(true);
	$feuille->getStyle('A1:'.$limits["column"].$limits["row"].'')->applyFromArray( 
	    array( 'fill' => 
	        array( 'type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => 
	            array('rgb' => '474747') 
	        ) 
	    ) 
	);

	$feuille->getStyle('A1:'.$limits["column"].$limits["row"].'')->applyFromArray(array('font' => array('bold' => true,'color' => array('rgb' => 'FFFFFF'))));

	$ligne++;

	$tabCategories = array();

	// lecture et copie ligne par ligne jusqu'à la fin du fichier
	while( !feof( $fichier ) )
	{
		$tab = explode( "\t", fgets( $fichier, "4096" ) );
		if( !empty($tab) && sizeof($tab) > 1 )
		{
			$dossard = isset( $champs['DOSSARD'] ) ? trim( $tab[$champs['DOSSARD']], "\n\r\t\0" ):"";
			$licence = isset( $champs3['LICENCE'] ) ? trim( $tab[$champs3['LICENCE']], "\n\r\t\0" ):"";
			$nom = isset( $champs['NOM'] ) ? trim( $tab[$champs['NOM']], "\n\r\t\0" ):"";
			$prenom = isset( $champs['PRENOM'] ) ? trim( $tab[$champs['PRENOM']], "\n\r\t\0" ):"";
			$naissance = isset( $champs3['NAISSANCE'] ) ? $tab[$champs3['NAISSANCE']] :"";
			$club = isset( $champs['CLUB'] ) ? trim( $tab[$champs['CLUB']], "\n\r\t\0" ):"";
			$cat = isset( $champs['CAT'] ) ? trim( $tab[$champs['CAT']], "\n\r\t\0" ):"";
			$plcat = isset( $champs['PL CAT'] ) ? trim( $tab[$champs['PL CAT']], "\n\r\t\0" ):"";
			$sexe = isset( $champs['SEXE'] ) ? trim( $tab[$champs['SEXE']], "\n\r\t\0" ):"";
			$place = $tab[0];
			$pays = (isset( $champs2['PAYS'] ) && $tab[$champs2['PAYS']] != '' ) ? trim( $tabPays[ucfirst( strtolower( $tab[$champs2['PAYS']] ) )], "\n\r\t\0" ):"FRA";
			$naissance_ = explode("/",$naissance);
			$u23 = ( date("Y",strtotime( $naissance_[2].'-'.$naissance_[1].'-'.$naissance_[0] )) >= 1996 ? "*" : "" );
			$discipline1 = isset( $champs2['Discipline#1'] ) ? trim( $tab[$champs2['Discipline#1']], "\n\r\t\0" ):"";
			$discipline2 = isset( $champs2['Discipline#2'] ) ? trim( $tab[$champs2['Discipline#2']], "\n\r\t\0" ):"";
			$discipline3 = isset( $champs2['Discipline#3'] ) ? trim( $tab[$champs2['Discipline#3']], "\n\r\t\0" ):"";
			$transition1 = isset( $champs2['Transition#1'] ) ? trim( $tab[$champs2['Transition#1']], "\n\r\t\0" ):"";
			$transition2 = isset( $champs2['Transition#2'] ) ? trim( $tab[$champs2['Transition#2']], "\n\r\t\0" ):"";
			$temps =  date( "H:i:s", strtotime( $tab[$champs['TEMPS']] ) );

			if( empty( $tabCategories[$cat] ) )
			{
				$tabCategories[$cat] = array();
			}

			$resultat = array();

			array_push($resultat,utf8_encode( $place ));
			array_push($resultat,utf8_encode( $dossard ));
			array_push($resultat,utf8_encode( $nom ));
			array_push($resultat,utf8_encode( $prenom ));
			array_push($resultat,utf8_encode( $sexe ));
			array_push($resultat,utf8_encode( $pays ));
			array_push($resultat,utf8_encode( $plcat ));
			array_push($resultat,utf8_encode( $cat ));
			array_push($resultat,utf8_encode( $u23 ));
			array_push($resultat,utf8_encode( $licence ));
			array_push($resultat,utf8_encode( $club ));
			array_push($resultat,utf8_encode( $discipline1 ));
			array_push($resultat,utf8_encode( $transition1 ));
			array_push($resultat,utf8_encode( $discipline2 ));
			array_push($resultat,utf8_encode( $transition2 ));
			array_push($resultat,utf8_encode( $discipline3 ));
			array_push($resultat,utf8_encode( $temps ));

			array_push($tabCategories[$cat],$resultat);
		}
	}

	foreach( $tabCategories as $cat => $coureur )
	{
		$feuille->setCellValueByColumnAndRow( 0, $ligne, utf8_encode( $cat ) );
		$feuille->mergeCellsByColumnAndRow( 0, $ligne, 16, $ligne );
		$feuille->getStyleByColumnAndRow( 0, $ligne, 16, $ligne )->applyFromArray($styleArray2);

		$ligne++;

		foreach ($coureur as $c) 
		{
			$feuille->getStyleByColumnAndRow( 0, $ligne, 16, $ligne )->applyFromArray($styleArray);
			$feuille->setCellValueByColumnAndRow( 0, $ligne, utf8_encode( $c[0] ) );
			$feuille->setCellValueByColumnAndRow( 1, $ligne, utf8_encode( $c[1] ) );
			$feuille->setCellValueByColumnAndRow( 2, $ligne, utf8_encode( $c[2] ) );
			$feuille->setCellValueByColumnAndRow( 3, $ligne, utf8_encode( $c[3] ) );
			$feuille->setCellValueByColumnAndRow( 4, $ligne, utf8_encode( $c[4] ) );
			$feuille->setCellValueByColumnAndRow( 5, $ligne, utf8_encode( $c[5] ) );
			$feuille->setCellValueByColumnAndRow( 6, $ligne, utf8_encode( $c[6] ) );
			$feuille->setCellValueByColumnAndRow( 7, $ligne, utf8_encode( $c[7] ) );
			$feuille->setCellValueByColumnAndRow( 8, $ligne, utf8_encode( $c[8] ) );
			$feuille->setCellValueByColumnAndRow( 9, $ligne, utf8_encode( $c[9] ) );
			$feuille->setCellValueByColumnAndRow( 10, $ligne, utf8_encode( $c[10] ) );
			$feuille->setCellValueByColumnAndRow( 11, $ligne, utf8_encode( $c[11] ) );
			$feuille->setCellValueByColumnAndRow( 12, $ligne, utf8_encode( $c[12] ) );
			$feuille->setCellValueByColumnAndRow( 13, $ligne, utf8_encode( $c[13] ) );
			$feuille->setCellValueByColumnAndRow( 14, $ligne, utf8_encode( $c[14] ) );
			$feuille->setCellValueByColumnAndRow( 15, $ligne, utf8_encode( $c[15] ) );
			$feuille->setCellValueByColumnAndRow( 16, $ligne, utf8_encode( $c[16] ) );

			$ligne++;
		}
	}
	
	$limits = $feuille->getHighestRowAndColumn();

	unset($styleArray);
	unset($styleArray2);

	$classeur->setActiveSheetIndex(0);

	if ($_GET['type'] =='xls5') 
	{
	    header('Content-Type: application/vnd.ms-excel');
	    header('Content-Disposition: attachment;filename="'.$nomEpreuve.'.xls"');
	    header('Cache-Control: max-age=0');
	    $writer = PHPExcel_IOFactory::createWriter($classeur, 'Excel5');
	}
	elseif ($_GET['type'] =='csv')
	{	
		header('Content-Type: text/csv');
	    header('Content-Disposition: attachment;filename="'.$nomEpreuve.'.csv"');
	    header('Cache-Control: max-age=0');

	    $writer = PHPExcel_IOFactory::createWriter($classeur, 'CSV');
	    $writer->setDelimiter(';');
	    $writer->setEnclosure('');
	    $writer->setLineEnding("\r\n");
	    $writer->setSheetIndex(0);
	}
	else
	{	
		header('Content-Type: application/vnd.ms-excel');
	    header('Content-Disposition: attachment;filename="'.$nomEpreuve.'.xls"');
	    header('Cache-Control: max-age=0');
	    $writer = PHPExcel_IOFactory::createWriter($classeur, 'Excel5');
	}
	$writer->save( "../resultats/".$nomExportExcel.".xls" );

	return $nomExportExcel.".xls";
}

function export_csv_FFTRI( $epreuve, $parcours, $cle_manifestation, $cle_epreuve, $fichier, $champs, $champs2, $champs3 )
{
	rewind($fichier);

	$classeur = new PHPExcel;
	$classeur->getProperties()->setCreator("ats-sport.com");
	PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);

	$nomExportCsv = NomFichierValide( "ESPACEFFTRI_".$epreuve."_".$parcours );

	$data ='';

	$colonne=0;
	$ligne=1;

	$feuille = $classeur->createSheet(0);
	$classeur->setActiveSheetIndex(0);
	$feuille=$classeur->getActiveSheet();
	$feuille->setTitle('Résultats FFTRI');

	//Lecture des entêtes
	fgets( $fichier , "4096" ); //1ère ligne ignorée
	fgets( $fichier, "4096" );	//2ème ligne ignorée

	//Style appliqué au fichier
	$styleArray = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		),
		'alignment' => array(
	       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	    )
	);

	$styleArray2 = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		),
		'alignment' => array(
	       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
	    )
	);

	$limits = $feuille->getHighestRowAndColumn();
	$feuille->getStyle('A1:'.$limits["column"].$limits["row"].'')->getFont()->setBold(true);

	$cle_prestataire = 29;
	$pass_competition = "";

	// lecture et copie ligne par ligne jusqu'à la fin du fichier
	while( !feof( $fichier ) )
	{
		$tab = explode( "\t", fgets( $fichier, "4096" ) );
		if( !empty($tab) && sizeof($tab) > 1 )
		{
			$dossard = isset( $champs['DOSSARD'] ) ? trim( $tab[$champs['DOSSARD']], "\n\r\t\0" ):"";
			$licence = isset( $champs3['LICENCE'] ) ? trim( $tab[$champs3['LICENCE']], "\n\r\t\0" ):"";
			$nom = isset( $champs['NOM'] ) ? trim( $tab[$champs['NOM']], "\n\r\t\0" ):"";
			$prenom = isset( $champs['PRENOM'] ) ? trim( $tab[$champs['PRENOM']], "\n\r\t\0" ):"";
			$naissance = isset( $champs3['NAISSANCE'] ) ? $tab[$champs3['NAISSANCE']] :"";
			$club = isset( $champs['CLUB'] ) ? trim( $tab[$champs['CLUB']], "\n\r\t\0" ):"";
			$cat = isset( $champs['CAT'] ) ? trim( $tab[$champs['CAT']], "\n\r\t\0" ):"";
			$plcat = isset( $champs['PL CAT'] ) ? trim( $tab[$champs['PL CAT']], "\n\r\t\0" ):"";
			$plsexe = isset( $champs2['/sx'] ) ? trim( $tab[$champs2['/sx']], "\n\r\t\0" ):"";
			$status = ( ( $plcat == "DNF" || $plcat == "DSQ" || $plcat == "DNS" ) ? $plcat : "Finisher" );
			$sexe = isset( $champs['SEXE'] ) ? trim( $tab[$champs['SEXE']], "\n\r\t\0" ):"";
			$parcours = isset( $champs3['PARCOURS'] ) ? trim( $tab[$champs3['PARCOURS']], "\n\r\t\0" ):"";
			$place = $tab[0];
			$naissance_ = explode("/",$naissance);
			$u23 = ( date("Y",strtotime( $naissance_[2].'-'.$naissance_[1].'-'.$naissance_[0] )) >= 1996 ? "*" : "" );
			$discipline1 = isset( $champs2['Discipline#1'] ) ? trim( $tab[$champs2['Discipline#1']], "\n\r\t\0" ):"00:00:00";
			$discipline2 = isset( $champs2['Discipline#2'] ) ? trim( $tab[$champs2['Discipline#2']], "\n\r\t\0" ):"00:00:00";
			$discipline3 = isset( $champs2['Discipline#3'] ) ? trim( $tab[$champs2['Discipline#3']], "\n\r\t\0" ):"00:00:00";
			$transition1 = isset( $champs2['Transition#1'] ) ? trim( $tab[$champs2['Transition#1']], "\n\r\t\0" ):"00:00:00";
			$transition2 = isset( $champs2['Transition#2'] ) ? trim( $tab[$champs2['Transition#2']], "\n\r\t\0" ):"00:00:00";
			$temps =  date( "H:i:s", strtotime( $tab[$champs['TEMPS']] ) );

			$feuille->setCellValueByColumnAndRow( 0, $ligne, utf8_encode( $cle_manifestation ) );
			$feuille->setCellValueByColumnAndRow( 1, $ligne, utf8_encode( $cle_epreuve ) );
			$feuille->setCellValueByColumnAndRow( 2, $ligne, utf8_encode( $licence ) );
			$feuille->setCellValueByColumnAndRow( 3, $ligne, utf8_encode( $pass_competition ) );
			$feuille->setCellValueByColumnAndRow( 4, $ligne, utf8_encode( $dossard ) );
			$feuille->setCellValueByColumnAndRow( 5, $ligne, utf8_encode( $status ) );
			$feuille->setCellValueByColumnAndRow( 6, $ligne, utf8_encode( $nom ) );
			$feuille->setCellValueByColumnAndRow( 7, $ligne, utf8_encode( $prenom ) );
			$feuille->setCellValueByColumnAndRow( 8, $ligne, utf8_encode( $sexe ) );
			$feuille->setCellValueByColumnAndRow( 9, $ligne, utf8_encode( $naissance ) );
			$feuille->setCellValueByColumnAndRow( 10, $ligne, utf8_encode( $cat ) );
			$feuille->setCellValueByColumnAndRow( 11, $ligne, utf8_encode( "" ) );	//Clé club
			$feuille->setCellValueByColumnAndRow( 12, $ligne, utf8_encode( $cle_prestataire ) );
			$feuille->setCellValueByColumnAndRow( 13, $ligne, utf8_encode( "" ) );	//Rang club
			$feuille->setCellValueByColumnAndRow( 14, $ligne, utf8_encode( "" ) );	//Point club
			$feuille->setCellValueByColumnAndRow( 15, $ligne, utf8_encode( $place ) );
			$feuille->setCellValueByColumnAndRow( 16, $ligne, utf8_encode( $temps ) );
			$feuille->setCellValueByColumnAndRow( 17, $ligne, utf8_encode( $plsexe ) );
			$feuille->setCellValueByColumnAndRow( 18, $ligne, utf8_encode( $plcat ) );
			$feuille->setCellValueByColumnAndRow( 19, $ligne, utf8_encode( $parcours ) );
			$feuille->setCellValueByColumnAndRow( 20, $ligne, utf8_encode( "SWIM" ) );
			$feuille->setCellValueByColumnAndRow( 21, $ligne, utf8_encode( "RACE" ) );
			$feuille->setCellValueByColumnAndRow( 22, $ligne, utf8_encode( date( "His", strtotime( $discipline1 ) ) ) );
			$feuille->setCellValueByColumnAndRow( 23, $ligne, utf8_encode( "" ) );
			$feuille->setCellValueByColumnAndRow( 24, $ligne, utf8_encode( "SWIM" ) );
			$feuille->setCellValueByColumnAndRow( 25, $ligne, utf8_encode( "TRANSITION" ) );
			$feuille->setCellValueByColumnAndRow( 26, $ligne, utf8_encode( date( "His", strtotime( $transition1 ) ) ) );
			$feuille->setCellValueByColumnAndRow( 27, $ligne, utf8_encode( "" ) );
			$feuille->setCellValueByColumnAndRow( 28, $ligne, utf8_encode( "BIKE" ) );
			$feuille->setCellValueByColumnAndRow( 29, $ligne, utf8_encode( "RACE" ) );
			$feuille->setCellValueByColumnAndRow( 30, $ligne, utf8_encode( date( "His", strtotime( $discipline2 ) ) ) );
			$feuille->setCellValueByColumnAndRow( 31, $ligne, utf8_encode( "" ) );
			$feuille->setCellValueByColumnAndRow( 32, $ligne, utf8_encode( "BIKE" ) );
			$feuille->setCellValueByColumnAndRow( 33, $ligne, utf8_encode( "TRANSITION" ) );
			$feuille->setCellValueByColumnAndRow( 34, $ligne, utf8_encode( date( "His", strtotime( $transition2 ) ) ) );
			$feuille->setCellValueByColumnAndRow( 35, $ligne, utf8_encode( "" ) );
			$feuille->setCellValueByColumnAndRow( 36, $ligne, utf8_encode( "RUN" ) );
			$feuille->setCellValueByColumnAndRow( 37, $ligne, utf8_encode( "RACE" ) );
			$feuille->setCellValueByColumnAndRow( 38, $ligne, utf8_encode( date( "His", strtotime( $discipline3 ) ) ) );
			$feuille->setCellValueByColumnAndRow( 39, $ligne, utf8_encode( "" ) );
		}

		$ligne++;
	}

	$limits = $feuille->getHighestRowAndColumn();

	$feuille->getStyle('A1:'.$limits["column"].$limits["row"].'')->applyFromArray($styleArray);
	unset($styleArray);

	$classeur->setActiveSheetIndex(0);

	header('Content-Type: text/csv');
	header('Content-Disposition: attachment;filename="'.$nomEpreuve.'.csv"');
	header('Cache-Control: max-age=0');

	$writer = PHPExcel_IOFactory::createWriter($classeur, 'CSV');
	$writer->setDelimiter(';');
	$writer->setEnclosure('');
	$writer->setLineEnding("\r\n");
	$writer->setSheetIndex(0);
	
	$writer->save( "../resultats/".$nomExportCsv.".csv" );

	return $nomExportCsv.".csv";
}


/*
	Fonction export_pdf
	enregistre le fichier résultat au format pdf
*/
function export_pdf( $typeEpreuve, $epreuve, $parcours, $fichier, $champs_non_affiche )
{
	rewind($fichier);

	if($typeEpreuve == 1) $format = 'A3';
	else $format = 'A4';

	$nomExportPdf = NomFichierValide( $epreuve."_".$parcours );
	// Extend the TCPDF class to create custom Header and Footer
	class MYPDF extends TCPDF {

		private $epreuve;
		private $parcours;

		public function __construct( $epreuve_, $parcours_, $format_ ) {
			$this->epreuve = $epreuve_;
			$this->parcours = $parcours_;
			parent::__construct('L', PDF_UNIT, $format_, true, 'UTF-8', false);
	    }

	    //Page header
	    public function Header() {
	        // Logo
	        $this->Image('images/logoATS-bleu2.jpg', '', '', 15, '', 'JPG', '', 'T', false, 300, 'L', false, false, 0, false, false, false);
	         $this->SetFont('', '', 14);
	       	$this->writeHTMLCell('', '', '', 5, "<b>".$this->epreuve."<br />".$this->parcours."</b>", 0, 0, false, true, 'C', true);
	    }

	    // Page footer
	    public function Footer() {
	        // Position at 15 mm from bottom
	        $this->SetY(-15);
	        // Set font
	        $this->SetFont('helvetica', 'I', 8);
	        // Page number
	        $this->Cell(0, 5, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	    }
	}

	// create new PDF document
	$pdf = new MYPDF( $epreuve, $parcours, $format );

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('ATS-SPORT');
	$pdf->SetTitle('Résultats');
	$pdf->SetSubject('Résultats');
	$pdf->SetKeywords('Résultats, PDF');

	// set default header data
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	$pdf->SetMargins(1, 20, 1);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// set some language-dependent strings (optional)
	if (@file_exists(dirname(__FILE__).'/lang/fr.php')) {
	    require_once(dirname(__FILE__).'/lang/fr.php');
	    $pdf->setLanguageArray($l);
	}

	// ---------------------------------------------------------

	// add a page
	$pdf->AddPage();

	//Lecture des entêtes
	fgets( $fichier , "4096" ); //1ère ligne ignorée
	$head = fgets( $fichier, "4096" );
	$head = rtrim( $head ,"\n\r\t\0" );
	$header=explode( "\t", $head );

	// Colors, line width and bold font
	$pdf->SetFillColor( 18, 117, 182 );
	$pdf->SetTextColor( 255 );
	$pdf->SetDrawColor( 18, 117, 182 );
	$pdf->SetLineWidth( 0.1 );

	// Header
	$pdf->SetFont( '', '', 7 );
	$w = array();
	$num_headers = count( $header );
	for( $i = 0; $i < $num_headers; ++$i )
	{
	    if( $header[$i] == "PLACE" ||  $header[$i] == "DOSSARD" )
	    	$w[$i] = 14;
	   	else if( $header[$i] == "SEXE" || $header[$i] == "CAT" || $header[$i] == "/sx" || $header[$i] == "/cat" || $header[$i] == "Vit." )
	   		$w[$i] = 7;
	   	else if( $header[$i] == "NOM" ||  $header[$i] == "PRENOM" || $header[$i] == "CLUB" )
	   		$w[$i] = 40;
	   	else if( $header[$i] == '' )
	   			$w[$i] = 12;
	   	else
	   		$w[$i] = 20;

	   	if( !in_array($i, $champs_non_affiche) )
	   		$pdf->Cell( $w[$i], 5, $header[$i], 1, 0, 'C', 1 );
	}
	$pdf->Ln();
	// Color and font restoration
	$pdf->SetFillColor( 225, 225, 225 );
	$pdf->SetTextColor( 0 );
	$pdf->SetFont( '', '', 7 );
	// Data
	$fill = 0;
	$numberOfLine = 1;
	while( !feof( $fichier ) )
	{
		$line = fgets( $fichier, "4096" );
		$line = rtrim( $line ,"\n\r\t\0" );
		$row = explode( "\t", $line );
		if( !empty($row) )
		{
			foreach( $row as $key => $value )
			{	
				if( !in_array($key, $champs_non_affiche) )
					$pdf->Cell( $w[$key], 4, utf8_encode( $value ), 'LR', 0, 'C', $fill, '', 1 );
			}
		    $pdf->Ln();
		    $fill=!$fill;

		    if($typeEpreuve == 1) $flag = 61;
			else $flag = 39;

		    if($numberOfLine % $flag == 0 && $numberOfLine >= $flag)
		    {
		    	$pdf->SetFillColor( 18, 117, 182 );
				$pdf->SetTextColor( 255 );
				$pdf->SetDrawColor( 18, 117, 182 );
		    	for( $i = 0; $i < $num_headers; ++$i )
				{
				    if( $header[$i] == "PLACE" ||  $header[$i] == "DOSSARD" )
				    	$w[$i] = 14;
				   	else if( $header[$i] == "SEXE" || $header[$i] == "CAT" || $header[$i] == "/sx" || $header[$i] == "/cat" || $header[$i] == "Vit." )
				   		$w[$i] = 7;
				   	else if( $header[$i] == "NOM" ||  $header[$i] == "PRENOM" || $header[$i] == "CLUB" )
				   		$w[$i] = 40;
				   	else if( $header[$i] == '' )
	   					$w[$i] = 12;
				   	else
				   		$w[$i] = 20;

				   	if( !in_array($i, $champs_non_affiche) )
				   		$pdf->Cell( $w[$i], 5, $header[$i], 1, 0, 'C', 1, '', 1 );
				}
				$pdf->Ln();

				$pdf->SetFillColor( 225, 225, 225 );
				$pdf->SetTextColor( 0 );
				$pdf->SetFont( '', '', 7 );
		    }
		    $numberOfLine++;
		}
	}
	// ---------------------------------------------------------

	//Close and output PDF document
	$pdf->Output( $_SERVER['DOCUMENT_ROOT']."resultats/".$nomExportPdf.".pdf", 'F' );

	return $nomExportPdf.".pdf";
}

/*
	Fonction export_excel
	enregistre le fichier résultat au format excel
*/
function export_excel_label_or( $epreuve, $parcours, $idEpreuve )
{
	$classeur = new PHPExcel;
	$classeur->getProperties()->setCreator("ats-sport.com");
	PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);

	$nomExportExcel = NomFichierValide( $epreuve."_".$parcours.'_label_or' );

	$data ='';

	$colonne=0;
	$ligne=1;

	$feuille = $classeur->createSheet(0);
	$classeur->setActiveSheetIndex(0);
	$feuille=$classeur->getActiveSheet();
	$feuille->setTitle('Résultats');

	//Style appliqué au fichier
	$styleArray = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		),
		'alignment' => array(
	       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	    )
	);

	$styleArray2 = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		),
		'alignment' => array(
	       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
	    )
	);

	$entete = array( "Nom", "Prénom", "Sexe", "Année de naissance", "Catégorie", "Club/Team", "Place Catégorie", "Points Epreuve", "Scratch" );

	//Entêtes résultats
	foreach( $entete as $key => $value )
	{
		$feuille->setCellValueByColumnAndRow( $key, $ligne, $value );
		$limits = $feuille->getHighestRowAndColumn();
	}
	$feuille->getStyle('A1:'.$limits["column"].$limits["row"].'')->getFont()->setBold(true);

	//Entêtes fichier
	/*$feuille->mergeCells($pRange = 'A1:'.$feuille->getHighestColumn().($ligne-1).'');
	$feuille->setCellValue( 'A1' , $epreuve."\n".$parcours );
	$feuille->getStyle('A1')->applyFromArray($styleArray2);
	$feuille->getStyle('A1')->getFont()->setSize(16);
	$feuille->getStyle('A1')->getAlignment()->setWrapText(true);

	$objDrawing = new PHPExcel_Worksheet_Drawing();
	$objDrawing->setName('Logo');
	$objDrawing->setDescription('Logo');
	$logo = './images/logoATS-bleu2.jpg'; // Provide path to your logo file
	$objDrawing->setPath($logo);
	$objDrawing->setCoordinates('J2');
	$objDrawing->setHeight(60); // logo height
	$objDrawing->setWorksheet($feuille);*/

	$ligne++;

	$query   = "SELECT nomConcurrent, prenomConcurrent, sexeConcurrent, naissanceConcurrent, cat_label_or, place_cat_label_or, points_label_or, classementConcurrent, clubConcurrent ";
	$query  .= "FROM r_resultats ";
	$query  .= "WHERE idEpreuve = ".$idEpreuve." AND points_label_or > 0 ";
	$query  .= "ORDER BY cat_label_or DESC, place_cat_label_or";

	$result = $mysqli->query( $query );

	while( $row=mysqli_fetch_assoc( $result ) )
	{
		$feuille->setCellValueByColumnAndRow( 0, $ligne, strtoupper( $row["nomConcurrent"] ) );
		$feuille->setCellValueByColumnAndRow( 1, $ligne, strtoupper( $row["prenomConcurrent"] ) );
		$feuille->setCellValueByColumnAndRow( 2, $ligne, $row["sexeConcurrent"] );
		$feuille->setCellValueByColumnAndRow( 3, $ligne, $row["naissanceConcurrent"] );
		$feuille->setCellValueByColumnAndRow( 4, $ligne, $row["cat_label_or"] );
		$feuille->setCellValueByColumnAndRow( 5, $ligne, $row["clubConcurrent"] );
		$feuille->setCellValueByColumnAndRow( 6, $ligne, $row["place_cat_label_or"] );
		$feuille->setCellValueByColumnAndRow( 7, $ligne, $row["points_label_or"] );
		$feuille->setCellValueByColumnAndRow( 8, $ligne, $row["classementConcurrent"] );

		$ligne++;
	}

	$limits = $feuille->getHighestRowAndColumn();

	$feuille->getStyle('A1:'.$limits["column"].$limits["row"].'')->applyFromArray($styleArray);
	unset($styleArray);

	$classeur->setActiveSheetIndex(0);

	if ($_GET['type'] =='xls5') 
	{
	    header('Content-Type: application/vnd.ms-excel');
	    header('Content-Disposition: attachment;filename="'.$nomEpreuve.'.xls"');
	    header('Cache-Control: max-age=0');
	    $writer = PHPExcel_IOFactory::createWriter($classeur, 'Excel5');
	}
	elseif ($_GET['type'] =='csv')
	{	
		header('Content-Type: text/csv');
	    header('Content-Disposition: attachment;filename="'.$nomEpreuve.'.csv"');
	    header('Cache-Control: max-age=0');

	    $writer = PHPExcel_IOFactory::createWriter($classeur, 'CSV');
	    $writer->setDelimiter(';');
	    $writer->setEnclosure('');
	    $writer->setLineEnding("\r\n");
	    $writer->setSheetIndex(0);
	}
	else
	{	
		header('Content-Type: application/vnd.ms-excel');
	    header('Content-Disposition: attachment;filename="'.$nomEpreuve.'.xls"');
	    header('Cache-Control: max-age=0');
	    $writer = PHPExcel_IOFactory::createWriter($classeur, 'Excel5');
	}
	$writer->save( "../resultats/".$nomExportExcel.".xls" );

	return $nomExportExcel.".xls";
}

/*
	Fonction export_excel_label_or
	enregistre le fichier trophée label d'or général au format excel
*/
function export_excel_label_or_general()
{
	$classeur = new PHPExcel;
	$classeur->getProperties()->setCreator("ats-sport.com");
	PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);

	$nomExportExcel = NomFichierValide( 'Classement_general_label_or' );

	$data ='';

	$colonne=0;
	$ligne=1;

	$feuille = $classeur->createSheet(0);
	$classeur->setActiveSheetIndex(0);
	$feuille=$classeur->getActiveSheet();
	$feuille->setTitle('Résultats');

	//Style appliqué au fichier
	$styleArray = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		),
		'alignment' => array(
	       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	    )
	);

	$styleArray2 = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)
		),
		'alignment' => array(
	       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
	    )
	);

	$entete = array( "Scratch", "Nom", "Prénom", "Catégorie", "Nb d'épreuves", "Points", "Bonus", "Global", "Place Cat", "Club/Team" );

	//Entêtes résultats
	foreach( $entete as $key => $value )
	{
		$feuille->setCellValueByColumnAndRow( $key, $ligne, $value );
		$limits = $feuille->getHighestRowAndColumn();
	}
	$feuille->getStyle('A1:'.$limits["column"].$limits["row"].'')->getFont()->setBold(true);

	$ligne++;

	$bonus = array( 0,0,20,45,70,95,120,150,150,150,150,150,150,150,150,150 );
	$pl_cat_label_or = array(
        "M1" => 1, "M2" => 1, "M3" => 1, "M4" => 1, "M5" => 1, "M6" => 1, "M7" => 1, "M8" => 1, "M9" => 1,
        "F1" => 1, "F2" => 1, "F3" => 1, "F4" => 1, "F5" => 1, "F6" => 1, "F7" => 1, "F8" => 1, "F9" => 1, "H" => 1
    );

	/*$query   = "select lower( concat( trim( nomConcurrent ), trim( prenomConcurrent ) ) ) AS id,
				count(*) as nbEpreuve, cat_label_or, sum(points_label_or) as points, nomConcurrent, prenomConcurrent, clubConcurrent, naissanceConcurrent
				FROM r_resultats
				WHERE points_label_or > 0
				GROUP BY id
				ORDER BY cat_label_or, points DESC, naissanceConcurrent";*/

	$query   = "select lower( concat( trim( nomConcurrent ), trim( prenomConcurrent ), trim( naissanceConcurrent ) ) ) AS id,
				count(*) as nbEpreuve, cat_label_or, sum(points_label_or) as points, nomConcurrent, prenomConcurrent, clubConcurrent, naissanceConcurrent
				FROM r_resultats
				WHERE place_cat_label_or > 0
				GROUP BY id
				ORDER BY points DESC, naissanceConcurrent";
	$scratch = 1;

	$result = $mysqli->query( $query );

	while( $row=mysqli_fetch_assoc( $result ) )
	{
		$feuille->setCellValueByColumnAndRow( 0, $ligne, $scratch );
		$feuille->setCellValueByColumnAndRow( 1, $ligne, strtoupper( $row["nomConcurrent"] ) );
		$feuille->setCellValueByColumnAndRow( 2, $ligne, strtoupper( $row["prenomConcurrent"] ) );
		$feuille->setCellValueByColumnAndRow( 3, $ligne, $row["cat_label_or"] );
		$feuille->setCellValueByColumnAndRow( 4, $ligne, $row["nbEpreuve"] );
		$feuille->setCellValueByColumnAndRow( 5, $ligne, $row["points"] );
		$feuille->setCellValueByColumnAndRow( 6, $ligne, $bonus[$row["nbEpreuve"]] );
		$feuille->setCellValueByColumnAndRow( 7, $ligne, ( $row["points"] + $bonus[$row["nbEpreuve"]] ) );
		$feuille->setCellValueByColumnAndRow( 8, $ligne, $pl_cat_label_or[$row["cat_label_or"]] );
		$feuille->setCellValueByColumnAndRow( 9, $ligne, $row["clubConcurrent"] );

		$ligne++;
		$pl_cat_label_or[$row["cat_label_or"]] += 1;
		$scratch++;
	}

	$limits = $feuille->getHighestRowAndColumn();

	$feuille->getStyle('A1:'.$limits["column"].$limits["row"].'')->applyFromArray($styleArray);
	unset($styleArray);

	$classeur->setActiveSheetIndex(0);

	if ($_GET['type'] =='xls5') 
	{
	    header('Content-Type: application/vnd.ms-excel');
	    header('Content-Disposition: attachment;filename="'.$nomEpreuve.'.xls"');
	    header('Cache-Control: max-age=0');
	    $writer = PHPExcel_IOFactory::createWriter($classeur, 'Excel5');
	}
	elseif ($_GET['type'] =='csv')
	{	
		header('Content-Type: text/csv');
	    header('Content-Disposition: attachment;filename="'.$nomEpreuve.'.csv"');
	    header('Cache-Control: max-age=0');

	    $writer = PHPExcel_IOFactory::createWriter($classeur, 'CSV');
	    $writer->setDelimiter(';');
	    $writer->setEnclosure('');
	    $writer->setLineEnding("\r\n");
	    $writer->setSheetIndex(0);
	}
	else
	{	
		header('Content-Type: application/vnd.ms-excel');
	    header('Content-Disposition: attachment;filename="'.$nomEpreuve.'.xls"');
	    header('Cache-Control: max-age=0');
	    $writer = PHPExcel_IOFactory::createWriter($classeur, 'Excel5');
	}
	$writer->save( "../resultats/".$nomExportExcel.".xls" );

	return $nomExportExcel.".xls";
}

////////////////////////////////////////////////////////////////////////////////////////Fonction d'importation du de calendrier depuis interafec admin//////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////modifiée le 08/05/2020 >>> Ajout du controle de l'existant pour éviter les doublons//////////////////////////
function insert_fichier_epreuves( $data, $file )
{
	if( !is_uploaded_file( $file['fichier']['tmp_name'] ) )
	{
		return display_alert( 'danger', '<strong>Erreur !</strong> Le fichier n\'a pas été correctement transmis. Veuillez réessayer.' );
	}
	else if( $file['fichier']['type'] != "text/plain" )
	{
		return display_alert( 'danger', '<strong>Erreur !</strong> Le fichier doit être au format <b>txt séparateur tabulation</b>' );
	}
	else
	{
		// nom du fichier destination :
		$nomfichier = $file["fichier"]["name"];
		$nomfichier = preg_replace("/#/", " ", $nomfichier);
		$nomfichier = preg_replace("/#/", "'", $nomfichier);
		$nomfichier = preg_replace("/#/", "é", $nomfichier);
		$nomfichier = preg_replace("/#/", "&", $nomfichier);
		$nomfichier = preg_replace("/#/", "(", $nomfichier);
		$nomfichier = preg_replace("/#/", ")", $nomfichier);
		$nomfichier = preg_replace("/#/", "è", $nomfichier);
		$nomfichier = preg_replace("/#/", "ç", $nomfichier);
		$nomfichier = preg_replace("/#/", "à", $nomfichier);
		$nomfichier = "resultats/".$nomfichier;
		if( move_uploaded_file( $file["fichier"]["tmp_name"], $nomfichier ) == FALSE )
		{
			return display_alert( 'danger', '<strong>Erreur !</strong> Le fichier n\'a pas été correctement transmis. Veuillez réessayer.' );
		}
		else if( ( $fichier = fopen( $nomfichier,"r" ) ) == FALSE )
		{
			return display_alert( 'danger', '<strong>Erreur !</strong> Le fichier ne peut pas être lu. Veuillez réessayer.' );
		}
		else
		{	
			/*
				=============================================================

				$line[0]  = Date début
				$line[1]  = Date fin
				$line[2]  = Nom Course
				$line[3]  = Type épreuve
				$line[4]  = Département
				$line[5]  = Ville
				$line[6]  = Nombre de parcours
				$line[7]  = Parcours 1
				$line[8]  = Type parcours 1
				$line[9]  = Horaire 1
				$line[10] = Tarif 1
				$line[11] = Parcours 2
				$line[12] = Type parcours 2
				$line[13] = Horaire 2
				$line[14] = Tarif 2
				$line[15] = Parcours 3
				$line[16] = Type parcours 3
				$line[17] = Horaire 3
				$line[18] = Tarif 3
				$line[19] = Parcours 4
				$line[20] = Type parcours 4
				$line[21] = Horaire 4
				$line[22] = Tarif 4
				$line[23] = Parcours 5
				$line[24] = Type parcours 5
				$line[25] = Horaire 5
				$line[26] = Tarif 5
				$line[27] = Parcours 6
				$line[28] = Type parcours 6
				$line[29] = Horaire 6
				$line[30] = Tarif 6
				$line[31] = Parcours 7
				$line[32] = Type parcours 7
				$line[33] = Horaire 7
				$line[34] = Tarif 7
				$line[35] = Parcours 8
				$line[36] = Type parcours 8
				$line[37] = Horaire 8
				$line[38] = Tarif 8
				$line[39] = Organisateur
				$line[40] = Téléphone
				$line[41] = Mail
				$line[42] = WWW
				$line[43] = compte organisateur oui/non
				$line[44] = nom orga
				$line[45] = prenom orga
				$line[46] = sexe orga
				$line[47] = description

				=============================================================
			*/	
			$tabIndiceParcours = array(7,11,15,19,23,27,31,35);
			fgets($fichier, "4096" );

			$nb = 0;
			while( !feof( $fichier ) ) // lecture ligne par ligne jusqu'à la fin du fichier
			{
				//Lecture de la ligne courante
				$line = explode( "\t", fgets($fichier, "4096" ) );

				//Echappement des caractères spéciaux comme ' " é à etc
				$line = array_map( "addslashes", $line );
				$line = array_map( "utf8_encode", $line );

				if( !empty( $line[0] ) )
				{
					//première étape on regarde l'existant
					
					
					
					
					//Insertion du résultat, champs fixe
					$qepreuves  = "INSERT INTO r_epreuve ";
					$qepreuves .= "(idTypeEpreuve, dateEpreuve, DateFinEpreuve, nomEpreuve, nombreParcours, departement, idInternaute, valide, nomStructureLegale, ";
					$qepreuves .= "ville, telInscription, contactInscription, emailInscription, siteInternet, dateDebutInscription, dateFinInscription, description, dateInscription, nbParticipantsAttendus, administrateur) ";
					$qepreuves .= "VALUES(";
					$qepreuves .= $line[3].",";			//idTypeEpreuve
					$qepreuves .= "'".date( "Y-m-d", strtotime( str_replace( "/", "-", $line[0] ) ) )."',";	//dateDebutEpreuve
					$qepreuves .= "'".date( "Y-m-d", strtotime( str_replace( "/", "-", $line[1] ) ) )."',";	//dateFinEpreuve
					$qepreuves .= "'".$line[2]."',";	//nomEpreuve
					$qepreuves .= $line[6].",";			//NombreParcours
					$qepreuves .= $line[4].",";			//Departement
					$qepreuves .= "185009,";			//idInternaute (organisateur)
					$qepreuves .= "'oui',";				//valide
					$qepreuves .= "'".$line[39]."',";	//contactInscription		
					$qepreuves .= "'".$line[5]."',";	//ville
					$qepreuves .= "'".$line[40]."',";	//telInscription
					$qepreuves .= "'".$line[39]."',";	//contactInscription
					$qepreuves .= "'".$line[41]."',";	//emailInscription
					$qepreuves .= "'".$line[42]."',";	//siteInternet
					$qepreuves .= "'".date( "Y-m-d", strtotime( str_replace( "/", "-", $line[0] ) ) )."',";			//dateDebutEpreuve
					$qepreuves .= "'".date( "Y-m-d H:i:s", strtotime( str_replace( "/", "-", $line[0] ) ) )."',";	//dateFinEpreuve
					$qepreuves .= "'".$line[47]."',";	//description
					$qepreuves .= "'".date( "Y-m-d" )."',";	//date création épreuve
					$qepreuves .= "500,";					//nbParticipantsAttendus
					$qepreuves .= "185009)";				//idInternaute (administrateur)
				
					$result = $mysqli->query( $qepreuves );
					if(!$result) return display_alert( 'danger', '<strong> Erreur : '.mysqil_error() );

					$idEpreuve = $mysqli->insert_id;

					$separator_fonction = code_separator(5);
					$separator_champ = code_separator(5);
					$separator_parcours = code_separator(5);

					//Création du compte organisateur si demandé
					if( strtolower( $line[34] ) == 'oui' )
					{
						add_user( $idEpreuve, $line[44].$idEpreuve, $line[45].rand(1000,9999), $line[44], $line[45], $line[46], $line[41], 'organisateur', false );						
					}
									
					//Création des séparateurs pour inscriptions en ligne
					$query_separator  = "INSERT INTO r_insc_champ_separator ";
					$query_separator .= "(idEpreuve, value_fonction, value_champ, value_parcours) VALUES (";
					$query_separator .= "".$idEpreuve.", ";
					$query_separator .= " '".$separator_fonction."', ";
					$query_separator .= "'".$separator_champ."', ";
					$query_separator .= "'".$separator_parcours."') ";
					$mysqli->query($query_separator);

					$nb++;

					for( $p=0; $p<$line[6]; $p++ )
					{
						$qparcours  = "INSERT INTO r_epreuveparcours ";
						$qparcours .= "(idEpreuve, idTypeParcours, nomParcours, nbtarif, horaireDepart,age) VALUES(";
						$qparcours .= $idEpreuve.",";
						$qparcours .= $line[$tabIndiceParcours[$p]+1].",";
						$qparcours .= "'".$line[$tabIndiceParcours[$p]]."',";
						$qparcours .= "1,";
						$qparcours .= "'".date( "Y-m-d H:i:s", strtotime( str_replace( "/", "-", $line[0] ).' '.$line[$tabIndiceParcours[$p]+2] ) )."',";
						$qparcours .= "0)";

						$result2 = $mysqli->query( $qparcours );
						if(!$result2) return display_alert( 'danger', '<strong> Erreur : '.mysqil_error() );

						$idEpreuveParcours = $mysqli->insert_id;

						if( !empty( $line[$tabIndiceParcours[$p]+3] ) )
						{
							$qtarif  = "INSERT INTO r_epreuveparcourstarif ";
							$qtarif .= "(idEpreuve, idEpreuveParcours, desctarif, tarif, dateDebutTarif, dateFinTarif) VALUES(";
							$qtarif .= $idEpreuve.",";
							$qtarif .= $idEpreuveParcours.",";
							$qtarif .= "'Tarif web',";
							$qtarif .= $line[$tabIndiceParcours[$p]+3].",";
							$qtarif .= "'".date( "Y-m-d H:i:s" )."',";
							$qtarif .= "'".date( "Y-m-d H:i:s", strtotime( str_replace( "/", "-", $line[0] ).' '.$line[$tabIndiceParcours[$p]+2] ) )."')";

							$result3 = $mysqli->query( $qtarif );
							if(!$result3) return display_alert( 'danger', '<strong> Erreur : '.mysqil_error() );
						}
					}
				}
				//Si tout s'est bien passé et que l'email existe, envoi d'un message à l'organisateur
				if( !empty( $line[41] ) )
				{	
			$message  = "<html><head></head>"."\r\n";
			$message .= "<br>"."\r\n";
			$message .= "   <table width='100%' cellspacing='0' cellpadding='0' border='0'>"."\r\n";
			$message .= "    <tbody>"."\r\n";
			$message .= "      <tr>"."\r\n";
			$message .= "        <td style='border-collapse:collapse;background-color:#ffffff' valign='top' align='center'>"."\r\n";
			$message .= "          <table  style='border-collapse:collapse;background-color:#ffffff;border-spacing:0;mso-table-lspace:0;mso-table-rspace:0;max-width:660px;width:100%' width='660' cellspacing='0' cellpadding='0' border='0'>"."\r\n";
			$message .= "            <tbody>"."\r\n";
			$message .= "              <tr>"."\r\n";
			$message .= "                <td  style='border-collapse:collapse;background-color:#ffffff!important' bgcolor='#ffffff' align='center'>"."\r\n";
			$message .= "                  <table style='border-collapse:collapse;border-spacing:0;mso-table-lspace:0;mso-table-rspace:0' width='100%' cellspacing='0' cellpadding='0' border='0'>"."\r\n";
			$message .= "                    <tbody>"."\r\n";
			$message .= "                      <tr>"."\r\n";
			$message .= "                        <td style='border-collapse:collapse;padding-left:0;padding-right:0'>"."\r\n";
			$message .= "                          <table  style='border-collapse:collapse;border-spacing:0;mso-table-lspace:0;mso-table-rspace:0;table-layout:fixed;margin-left:auto;margin-right:auto;padding-left:0;padding-right:0' width='100%' cellspacing='0' cellpadding='0' border='0'>"."\r\n";
			$message .= "                            <tbody>"."\r\n";
			$message .= "                              <tr>"."\r\n";
			$message .= "                                <td  style='border-collapse:collapse' valign='top' align='center'>"."\r\n";
			$message .= "								<a href='https://www.ats-sport.com' style='color:#dd2d2d;text-decoration:underline' moz-do-not-send='true'> <img src='https://www.ats-sport.com/images/entete_mailing_ATS.png' style='height:auto;max-width:100%;-ms-interpolation-mode:bicubic;border:0;display:block;outline:none;text-align:center;width:100%' moz-do-not-send='true' width='660'></a>"."\r\n";
			$message .= "                                </td>"."\r\n";
			$message .= "                              </tr>"."\r\n";
			$message .= "                            </tbody>"."\r\n";
			$message .= "                          </table>"."\r\n";
			$message .= "                        </td>"."\r\n";
			$message .= "                      </tr>"."\r\n";
			$message .= "                    </tbody>"."\r\n";
			$message .= "                  </table>"."\r\n";
			$message .= "                </td>"."\r\n";
			$message .= "              </tr>"."\r\n";
			$message .= "              <tr>"."\r\n";
			$message .= "                <td  style='border-collapse:collapse;background-color:#e7e7e7!important' bgcolor='#e7e7e7' align='center'>"."\r\n";
			$message .= "                  <table style='border-collapse:collapse;border-spacing:0;mso-table-lspace:0;mso-table-rspace:0' width='100%' cellspacing='0' cellpadding='0' border='0'>"."\r\n";
			$message .= "                    <tbody>"."\r\n";
			$message .= "                      <tr>"."\r\n";
			$message .= "                        <td style='border-collapse:collapse;padding-left:0;padding-right:0'>"."\r\n";
			$message .= "                          <table  style='border-collapse:collapse;border-spacing:0;mso-table-lspace:0;mso-table-rspace:0;table-layout:fixed;margin-left:auto;margin-right:auto;padding-left:0;padding-right:0' width='100%' cellspacing='0' cellpadding='0' border='0'>"."\r\n";
			$message .= "                            <tbody>"."\r\n";
			$message .= "                              <tr>"."\r\n";
			$message .= "                                <td  style='border-collapse:collapse;padding-top:10px;padding-bottom:10px;padding-left:20px;padding-right:20px;word-break:break-word;word-wrap:break-word' valign='top'>"."\r\n";
			$message .= "                                  <h2 style='margin:0 0 7.2px;color:#222222;font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;font-size:24px;line-height:38.4px;text-align:center;padding:0;font-style:normal;font-weight:normal'><span style='color: #3598db;'>Vous avez été référencé sur notre calendrier</span></h2>"."\r\n";
			$message .= "                                  <table style='border-collapse:collapse;border-spacing:0;mso-table-lspace:0;mso-table-rspace:0' width='100%' cellpadding='0'>"."\r\n";
			$message .= "                                    <tbody>"."\r\n";
			$message .= "                                      <tr>"."\r\n";
			$message .= "                                        <td  style='border-collapse:collapse;color:#000000;font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;font-size:15px;line-height:24px;word-break:break-word;word-wrap:break-word;text-align:left'>"."\r\n";
			$message .= "                                          Bonjour,</br></br> La période de confinement COVID-19 a perturbé bon nombre d'organisation durant les mois de mars à juillet, et nous souhaitions faire un petit point sur le calendrier des courses en Occitanie pour les mois à venir...</br></br>"."\r\n";
			$message .= "                                          <strong>Nous avons référencé votre épreuve dans notre calendrier sportif 2020.</strong><br>"."\r\n";
			$message .= "                                          <br>"."\r\n";
			$message .= "                                        </td>"."\r\n";
			$message .= "                                      </tr>"."\r\n";
			$message .= "                                    </tbody>"."\r\n";
			$message .= "                                  </table>"."\r\n";
			$message .= "                                  <table style='border-collapse:collapse;border-spacing:0;mso-table-lspace:0;mso-table-rspace:0' width='100%' cellpadding='0'>"."\r\n";
			$message .= "                                    <tbody>"."\r\n";
			$message .= "                                      <tr>"."\r\n";
			$message .= "                                        <td  style='border-collapse:collapse;color:#000000;font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;font-size:15px;line-height:24px;word-break:break-word;word-wrap:break-word;text-align:left'>"."\r\n";
			$message .= "                                          Nom de l'épreuve :  <a href='http://www.ats-sport.com/epreuve.php?id_epreuve=".$idEpreuve."'>".$line[2]."</a><br>"."\r\n";
			$message .= "                                          Date : ".$line[0]."<br>"."\r\n";
			$message .= "                                          Lieu : ".$line[5]."<br>"."\r\n";
			$message .= "                                          Tel : ".$line[40]."<br>"."\r\n";
			$message .= "                                          <br>"."\r\n";
			$message .= "                                          <strong>Merci de nous tenir informés par retour de mail, des <u>dates de report éventuelles</u> à contact@ats-sport.com.</strong></br>"."\r\n";
			//$message .= "                                          <strong>Merci de nous tenir informer par retour en cas de modification via l'adresse suivante : contact@ats-sport.com.</strong></br>"."\r\n";
			$message .= "                                        </td>"."\r\n";
			$message .= "                                      </tr>"."\r\n";
			$message .= "                                    </tbody>"."\r\n";
			$message .= "                                  </table>"."\r\n";
			$message .= "                                </td>"."\r\n";
			$message .= "                              </tr>"."\r\n";
			$message .= "                              <tr>"."\r\n";
			$message .= "                                <td  style='border-collapse:collapse;padding-top:10px;padding-bottom:10px;padding-left:20px;padding-right:20px' valign='top'>"."\r\n";
			$message .= "                                  <div>"."\r\n";
			$message .= "                                    <style='border-collapse:collapse;border-spacing:0;mso-table-lspace:0;mso-table-rspace:0' width='100%' cellspacing='0' cellpadding='0' border='0'>"."\r\n";
			$message .= "                                      <tbody>"."\r\n";
			$message .= "                                        <tr>"."\r\n";
			$message .= "                                          <td style='border-collapse:collapse;text-align:center'>"."\r\n";
			$message .= "                  								<a  href='http://www.ats-sport.com/epreuve.php?id_epreuve=".$idEpreuve."' style='color:#ffffff;text-decoration:none !important;display:inline-block;-webkit-text-size-adjust:none;mso-hide:all;text-align:center;background-color:#0074a2;border-color:#0074a2;border-width:0px;border-radius:40px;border-style:solid;width:288px;line-height:28px;font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;font-size:16px;font-weight:bold' moz-do-not-send='true'>"."\r\n";
			$message .= "                                              Cliquez pour voir votre épreuve</a>"."\r\n";
			$message .= "											</td>"."\r\n";
			$message .= "                                        </tr>"."\r\n";
			$message .= "                                      </tbody>"."\r\n";
			$message .= "                                    </table>"."\r\n";
			$message .= "                                  </div>"."\r\n";
			$message .= "                                </td>"."\r\n";
			$message .= "                              </tr>"."\r\n";
			$message .= "                              <tr>"."\r\n";
			$message .= "                                <td  style='border-collapse:collapse' valign='top' height='40'><br>"."\r\n";
			$message .= "                                </td>"."\r\n";
			$message .= "                              </tr>"."\r\n";
			$message .= "                              <tr>"."\r\n";
			$message .= "                                <td  style='border-collapse:collapse' valign='top' align='center'>"."\r\n";
			$message .= "                                <a href='https://www.ats-sport.com/new_event.php' ><img src='https://www.ats-sport.com/images/bannieres/lancez_vos_inscriptions_sur_ats-sport-2020-04-03-15-12-13-e.png' alt='lancez vos inscriptions sur ats-sport' style='height:auto;max-width:100%;-ms-interpolation-mode:bicubic;border:0;display:block;outline:none;text-align:center;width:90%' moz-do-not-send='true' width='660'></a>"."\r\n";
			$message .= "                                </br></br><a href='https://pointcourse.com/index.php/product/systeme-de-chronometrage/'><img src='https://www.ats-sport.com/images/bannieres/location-banniere-ATS-2020-04-27-15-29-50-e.png' alt='location chronométrage' style='height:auto;max-width:100%;-ms-interpolation-mode:bicubic;border:0;display:block;outline:none;text-align:center' moz-do-not-send='true' width='620'></a>"."\r\n";
			$message .= "                                </td>"."\r\n";
			$message .= "                              </tr>"."\r\n";
			$message .= "                              <tr>"."\r\n";
			$message .= "                                <td style='border-collapse:collapse;padding-top:10px;padding-bottom:10px;padding-left:20px;padding-right:20px;word-break:break-word;word-wrap:break-word' valign='top'>"."\r\n";
			$message .= "                                  <table style='border-collapse:collapse;border-spacing:0;mso-table-lspace:0;mso-table-rspace:0' width='100%' cellpadding='0'>"."\r\n";
			$message .= "                                    <tbody>"."\r\n";
			$message .= "                                      <tr>"."\r\n";
			$message .= "                                        <td  align='center' style='border-collapse:collapse;color:#000000;font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;font-size:11px;line-height:24px;word-break:break-word;word-wrap:break-word;text-align:center'>"."\r\n";
			$message .= "                                          <span style='color: #808080;'><strong>ATS SPORT </BR>7 rue de la chicanette</BR>34570 Pignan</br>France</strong></span>"."\r\n";
			$message .= "                                        </td>"."\r\n";
			$message .= "                                      </tr>"."\r\n";
			$message .= "                                    </tbody>"."\r\n";
			$message .= "                                  </table>"."\r\n";
			$message .= "                                </td>"."\r\n";
			$message .= "                              </tr>"."\r\n";
			$message .= "                              <tr>"."\r\n";
			$message .= "                                <td style='border-collapse:collapse;padding:5.5px 20px 5.5px 20px' valign='top'"."\r\n";
			$message .= "                                  <table style='border-collapse:collapse;border-spacing:0;mso-table-lspace:0;mso-table-rspace:0' width='100%' cellspacing='0' cellpadding='0' border='0'>"."\r\n";
			$message .= "                                    <tbody>"."\r\n";
			$message .= "                                      <tr>"."\r\n";
			$message .= "                                        <td style='border-collapse:collapse;border-top-width:1px;border-top-style:solid;border-top-color:#aaaaaa'>"."\r\n";
			$message .= "                                          <br>"."\r\n";
			$message .= "                                        </td>"."\r\n";
			$message .= "                                      </tr>"."\r\n";
			$message .= "                                    </tbody>"."\r\n";
			$message .= "                                  </table>"."\r\n";
			$message .= "                                </td>"."\r\n";
			$message .= "                              </tr>"."\r\n";
			$message .= "                              <tr>"."\r\n";
			$message .= "                                <td "."\r\n";
			$message .= "                                  style='border-collapse:collapse' valign='top' height='20'><br>"."\r\n";
			$message .= "                                </td>"."\r\n";
			$message .= "                              </tr>"."\r\n";
			$message .= "                              <tr>"."\r\n";
			$message .= "                                <td style='border-collapse:collapse;padding-top:10px;padding-bottom:10px;padding-left:20px;padding-right:20px' valign='top' align='center'>"."\r\n";
			$message .= "									<a href='https://www.facebook.com/ats.sport' style='color:#dd2d2d;text-decoration:none!important' moz-do-not-send='true'><img src='https://pointcourse.com/wp-content/plugins/mailpoet/assets/img/newsletter_editor/social-icons/02-grey/Facebook.png?mailpoet_version=3.0.0-rc.2.0.0' style='width:32px;height:32px;-ms-interpolation-mode:bicubic;border:0;display:inline;outline:none;' alt='facebook' moz-do-not-send='true' width='32' height='32'></a>"."\r\n";
			$message .= "									<a href='https://www.youtube.com/channel/UC1G6g069ChrEZLwN6IDb84Q' style='color:#dd2d2d;text-decoration:none!important' moz-do-not-send='true'> <img src='https://pointcourse.com/wp-content/plugins/mailpoet/assets/img/newsletter_editor/social-icons/02-grey/Youtube.png?mailpoet_version=3.46.12' style='width:32px;height:32px;-ms-interpolation-mode:bicubic;border:0;display:inline;outline:none;' alt='youtube' moz-do-not-send='true' width='32' height='32'></a>  </td>"."\r\n";
			$message .= "                              </tr>"."\r\n";
			$message .= "                            </tbody>"."\r\n";
			$message .= "                          </table>"."\r\n";
			$message .= "                        </td>"."\r\n";
			$message .= "                      </tr>"."\r\n";
			$message .= "                    </tbody>"."\r\n";
			$message .= "                  </table>"."\r\n";
			$message .= "                </td>"."\r\n";
			$message .= "              </tr>"."\r\n";
			$message .= "            </tbody>"."\r\n";
			$message .= "          </table>"."\r\n";
			$message .= "		</td>"."\r\n";
			$message .= "      </tr>"."\r\n";
			$message .= "    </tbody>"."\r\n";
			$message .= "  </table>"."\r\n";
			$message .= "</div>"."\r\n";
			$message .= "</html>"."\r\n";
			//Ancien message
					//$message  = "<html><head></head>"."\r\n";
					// $message .= "<body>"."\r\n";
					// $message .= "Bonjour,<br /><br />"."\r\n";
					// $message .= "La période de confinement COVID-19 a perturbé bon nombre d'organisation durant les mois de mars à mai,<br />et nous souhaitions faire un petit point sur le calendrier des courses à venir en occitanie...<br />"."\r\n";
					// $message .= "Nous avons référencé votre épreuve dans notre calendrier sportif ".date("Y", strtotime( str_replace( "/", "-", $line[0] ) ) ).".<br /><br />"."\r\n"; 
					// $message .= "Nom de l'épreuve : <a href='http://www.ats-sport.com/epreuve.php?id_epreuve=".$idEpreuve."'>".$line[2]."</a><br />"."\r\n";
					// $message .= "Date : ".$line[0]."<br />"."\r\n";
					//avec CP $message .= "Lieu : ".$line[5]."(".$line[4].")"."<br />"."\r\n";
					// $message .= "Lieu : ".$line[5]."<br />"."\r\n";
					// $message .= "Contact téléphone : ".$line[40]."<br />"."\r\n";
					// $message .= "Description : ".$line[47]."<br />"."\r\n";
					//$message .= "Parcours : "."<br />"."\r\n";
					
					//for( $p=0; $p<$line[6]; $p++ )
					//	$message .= "-".$line[$tabIndiceParcours[$p]]." ".$line[$tabIndiceParcours[$p]+3]."&euro; départ ".date( "H:i", strtotime( $line[$tabIndiceParcours[$p]+2] ) )."<br />"."\r\n";

					// $message .= "<br />\r\n";
					// $message .= "<b>> <u>Merci de nous informer par retour de mail de l'exactitude des informations ci dessus.</u></b><br />"."\r\n";
					// $message .= "<br />\r\n";
					// $message .= "<br />\r\n";
					// $message .= "<br />"."\r\n";
					// $message .= "Sportivement,<br /><br /><br />"."\r\n";
					// $message .= "<CENTER><a href='https://www.ats-sport.com/'>ATS-Sport - le E-ticket de l'exploit Sportif en Occitanie en Occitanie</a><br />"."\r\n";
					// $message .= "<a href='https://www.ats-sport.com/new_event.php'><img src= 'https://www.ats-sport.com/images/bannieres/lancez_vos_inscriptions_sur_ats-sport-2020-04-03-15-12-13-e.png' title='Promo on sort du COVID >> Vos dossards offerts'></a>";
					// $message .= "<br /><br />";
					// $message .= "<a href='https://pointcourse.com/'>Point Course - La logistique aux événements Sportifs Running, Cyclosport et Triathlon</a><br />"."\r\n";
					// $message .= "<a href='https://pointcourse.com/'><img src= 'https://www.ats-sport.com/images/bannieres/location-banniere-ATS-2020-04-27-15-29-50-e.png' title='Devenez votre propre chronométreur en quelques clics!!'></a>";
					// $message .= "</CENTER></body>"."\r\n";
					//$message .= "</html>"."\r\n";

					$email = sendmail( $line[41], $data["expediteur"], "Calendrier sportif ATS-SPORT", utf8_decode( $message ) );
				
				}
			}
			fclose($fichier);

			return display_alert( 'success', '<strong>'.$nb." épreuves ont été importées</strong>" );
		}
	}
}

function sendmail( $destinataire, $expediteur, $objet, $message )
{
	$headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'Bcc: '.$expediteur.'' . "\r\n";
	$headers .= 'From: '.$expediteur.'' . "\r\n";
	$headers .= 'Reply-To: '.$expediteur.'' . "\r\n";
	$headers .= 'Disposition-Notification-To: '.$expediteur.'' . "\r\n";
	$headers .= 'X-Mailer: PHP/' . phpversion();

    mail($destinataire, $objet, $message, $headers);
}

function valider_epreuves( $data )
{
	foreach( $data['epreuve'] as $idEpreuve )
	{
		$valider_epreuves = "UPDATE r_epreuve SET valide = 'oui' WHERE idEpreuve = ".$idEpreuve;
		$mysqli->query($valider_epreuves);
	}
}

function select_parcours($id_epreuve, $id_parcours = '')
{				
	$query  = "SELECT * FROM r_epreuveparcours WHERE idEpreuve = ".$id_epreuve." ";
	if( $id_parcours != '' ) $query .= "AND idEpreuveParcours = ".$id_parcours;
	$result_select_parcours = $mysqli->query($query);
	return $result_select_parcours;
}

function copie_epreuve( $host, $user, $pass, $name, $tables = '*', $idEpreuve )
{
  //$data = "\n/*---------------------------------------------------------------".
  //        "\n  SQL DB BACKUP ".date("d.m.Y H:i")." ".
  //        "\n  HOST: {$host}".
  //        "\n  DATABASE: {$name}".
  //        "\n  TABLES: {$tables}".
  //        "\n  ---------------------------------------------------------------*/\n";
  $link = mysql_connect($host,$user,$pass);
  mysql_select_db($name,$link);
  $mysqli->query( "SET NAMES `utf8` COLLATE `utf8_general_ci`" , $link ); // Unicode

  if($tables == '*') //get all of the tables
  {
    $tables = array();
    $result = $mysqli->query("SHOW TABLES");
    while($row = mysqli_fetch_row($result))
    {
      $tables[] = $row[0];
    }
  }else{
    $tables = is_array($tables) ? $tables : explode(',',$tables);
  }

  foreach($tables as $table)
  {
    //$data.= "\n/*---------------------------------------------------------------".
    //        "\n  TABLE: `{$table}`".
    //        "\n  ---------------------------------------------------------------*/\n";           
    //$data.= "DROP TABLE IF EXISTS `{$table}`;\n";
    /*$res = $mysqli->query("SHOW CREATE TABLE `{$table}`", $link);
    $row = mysqli_fetch_row($res);
    $data.= $row[1].";\n";*/

    $result = $mysqli->query("SELECT * FROM `{$table}` WHERE idEpreuve = ".$idEpreuve, $link);
    $num_rows = mysqli_num_rows($result);    

    if($num_rows>0)
    {
      $vals = Array(); $z=0;
      for($i=0; $i<$num_rows; $i++)
      {
        $items = mysqli_fetch_row($result);
        $vals[$z]="(";
        for($j=0; $j<count($items); $j++)
        {
          if (isset($items[$j])) { $vals[$z].= "'".mysql_real_escape_string( $items[$j], $link )."'"; } else { $vals[$z].= "NULL"; }
          if ($j<(count($items)-1)){ $vals[$z].= ","; }
        }
        $vals[$z].= ")"; $z++;
      }
      $data.= "INSERT INTO `{$table}` VALUES ";      
      $data .= "  ".implode(";\nINSERT INTO `{$table}` VALUES ", $vals).";\n";
    }
  }
  mysqli_close( $link );
  return $data;
}

function send_newsletter($to,$objet,$mailcontent,$dateenvoi,$expediteur,$mode,$piecejointe)
{	
	require_once '../../2017/Mail.php';
	require_once '../../2017/mail_mime/Mail/mime.php';
	include '../../2017/config_emailing_manuel.php';
	include '../../2017/config_newsletter.php';

	global $mysqli;

	//=====Déclaration des messages au format texte et au format HTML.
	$message_html = $mailcontent;

	$mysqli->query("delete from emailing_historique where objet like '".$objet."'");

	$sauvegarde  = "INSERT INTO emailing_historique (objet,texte,date) VALUES(";
	$sauvegarde .= "'".addslashes( $objet )."','".addslashes( $message_html )."',NOW())";
	
	$mysqli->query( $sauvegarde );
	
	echo is_null($db_options);
	$file_denvoi_de_mail = new Mail_Queue($db_options, $mail_options);

	//echo $file_denvoi_de_mail;

	if($expediteur == '') $from = 'noreply@ats-sport.com';
	else $from = $expediteur;
	$destinataire= array();
	$entetes = array( 'From'    => $from,
					  'Bcc'      => $to,
					  'Subject' => ( $mode == 'reel' ? utf8_decode( $objet ) : '[TEST] '.utf8_decode( $objet ) ) 
					);
					
	$mime = new Mail_mime();
	
	//Ajout du contenu de l'email
	$mime->setHTMLBody( utf8_decode( $message_html ) );
	
	//Ajout de la pièce jointe s'il y en a une
	$path = '../../2017/news/piece_jointe/';
	if($piecejointe != '') $mime->addAttachment($path.$piecejointe,'application/octet-stream','',true,'base64');
	
	$corps = $mime->get();
	$entetes = $mime->headers($entetes);
	
	// put(expéditeur,destinataire,entete,corps,délais d'envoi(en heure),suppression après envoi (o = non 1 = oui))
	/* $file_denvoi_de_mail->put($from,$to,$entetes,$corps,$dateenvoi,1);
	
	$nombre_max_de_mails = 1;

	//Envoi effectif des messages 
	$file_denvoi_de_mail->sendMailsInQueue($nombre_max_de_mails); */
}

function send_newsletter_test($to,$objet,$mailcontent,$dateenvoi,$expediteur,$mode,$piecejointe)
{	
	require_once '../../2017/Mail.php';
	require_once '../../2017/mail_mime/Mail/mime.php';
	include '../../2017/config_emailing_manuel.php';

	//=====Déclaration des messages au format texte et au format HTML.
	$message_html = $mailcontent;

	$mysqli->query("delete from emailing_historique where objet like '".$objet."'");

	$sauvegarde  = "INSERT INTO emailing_historique (objet,texte,date) VALUES(";
	$sauvegarde .= "'".addslashes( $objet )."','".addslashes( $message_html )."',NOW())";
	$mysqli->query( $sauvegarde );
	
	$file_denvoi_de_mail = new Mail_Queue($db_options, $mail_options);

	if($expediteur == '') $from = 'noreply@ats-sport.com';
	else $from = $expediteur;
	$destinataire= array();
	$entetes = array( 'From'    => $from,
					  'To'      => $to,
					  'Subject' => ( $mode == 'reel' ? utf8_decode( $objet ) : '[TEST] '.utf8_decode( $objet ) ) 
					);
					
	$mime = new Mail_mime();
	
	//Ajout du contenu de l'email
	$mime->setHTMLBody( utf8_decode( $message_html ) );
	
	//Ajout de la pièce jointe s'il y en a une
	$path = '../../2017/news/piece_jointe/';
	if($piecejointe != '') $mime->addAttachment($path.$piecejointe,'application/octet-stream','',true,'base64');
	
	$corps = $mime->get();
	$entetes = $mime->headers($entetes);
	
	// put(expéditeur,destinataire,entete,corps,délais d'envoi(en heure),suppression après envoi (o = non 1 = oui))
	$file_denvoi_de_mail->put($from,$to,$entetes,$corps,$dateenvoi,1);
	
	$nombre_max_de_mails = 1;

	/* Envoi effectif des messages */
	//$file_denvoi_de_mail->sendMailsInQueue($nombre_max_de_mails);
}

function add_user( $idEpreuve, $login, $password, $lastname, $firstname, $sex, $email, $type, $ar = false )
{
	$user  = "INSERT INTO r_internaute ";
	$user .= "(loginInternaute, passInternaute, validation, dateInscription, nomInternaute, prenomInternaute, sexeInternaute, emailInternaute, typeInternaute) VALUES (";
	$user .= "'".addslashes( $login )."', ";
	$user .= "'".hhp( $password )."', ";
	$user .= "'oui', ";
	$user .= "'".date( "Y-m-d H:i:s" )."', ";
	$user .= "UPPER('".addslashes( $lastname )."'), ";
	$user .= "UPPER('".addslashes( $firstname )."'), ";
	$user .= "UPPER('".addslashes( $sex )."'), ";
	$user .= "'".addslashes( $email )."', ";
	$user .= "'".addslashes( $type )."')";		

	$mysqli->query($user);
	$idInternaute = $mysqli->insert_id;

	switch( $type )
	{
		case "organisateur" : 
			$epreuve  = "UPDATE r_epreuve SET idInternaute = ".$idInternaute." WHERE idEpreuve = ".$idEpreuve;	
			break;
		case "super_organisateur" :
			$epreuve  = "UPDATE r_epreuve SET super_organisateur = ".$idInternaute." WHERE idEpreuve = ".$idEpreuve;	
			break;
		case "chronometreur" :
			$epreuve  = "UPDATE r_epreuve SET chronometreur = ".$idInternaute." WHERE idEpreuve = ".$idEpreuve;	
			break;
	}

	$mysqli->query($epreuve);

	if( $ar ) send_mail_user("noreply@ats-sport.com" ,$email, "Accès à votre espace organisateur ATS-SPORT", array("prenom"=>$firstname,"identifiant"=>$login,"mdp"=>$password));
}

function add_existing_user( $idEpreuve, $idInternaute, $type )
{
	switch( $type )
	{
		case "organisateur" : 
			$epreuve  = "UPDATE r_epreuve SET idInternaute = ".$idInternaute." WHERE idEpreuve = ".$idEpreuve;	
			break;
		case "super_organisateur" :
			$epreuve  = "UPDATE r_epreuve SET super_organisateur = ".$idInternaute." WHERE idEpreuve = ".$idEpreuve;	
			break;
		case "chronometreur" :
			$epreuve  = "UPDATE r_epreuve SET chronometreur = ".$idInternaute." WHERE idEpreuve = ".$idEpreuve;	
			break;
	}

	$mysqli->query($epreuve);
}

function insert_admin_annonces( $titre, $contenu, $date, $type )
{
	$annonce  = "INSERT INTO admin_annonces ";
	$annonce .= "(titre, annonce, date, type) VALUES (";
	$annonce .= "'".addslashes( $titre )."', ";
	$annonce .= "'".addslashes( $contenu )."', ";
	$annonce .= "'".date("Y-m-d H:i:s",strtotime($date))."', ";
	$annonce .= "'".addslashes( $type )."')";		

	$retour = $mysqli->query($annonce);

	if( !$retour ) return display_alert( 'danger', '<strong>une erreur s\'est produite lors de l\'enregistrement de l\'annonce</strong>' );
	else return display_alert( 'success', '<strong>L\'annonce a bien été enregistrée</strong>' );

}

?>