<?php 

	require_once('includes/functions.php');
	session_start();
	require_once('includes/connect_db.php');
	connect_db();
	

	$idEpreuve = $_GET['id_epreuve'];
	
	$id_internaute = extract_champ_epreuve("idInternaute",$idEpreuve);
	if (($_SESSION["typeInternaute"] != 'admin' && $id_internaute != $_SESSION["log_id"]) || empty($idEpreuve))
	{
		header('Location: liste_epreuves.php');  
	
	}
    
	$nom_epreuve = NomFichierValide(extract_champ_epreuve("nomEpreuve",$idEpreuve));
	
	/*$rep_fichiers_inscription  ="../fichiers_insc".DIRECTORY_SEPARATOR;
	$file = 'mytable.sql';
	$query  ='SELECT * INTO OUTFILE '.$file.' FROM r_epreuve as re, r_epreuveparcours, r_champssupdotation, r_champssupparticipation, r_champssupquestiondiverse, r_epreuveparcourstarif, r_epreuveparcourstarifpromo ';
	echo $query .='WHERE re.idEpreuve = '.$idEpreuve;
	$result = $mysqli->query($query);
	exit();*/
	
// create backup
//////////////////////////////////////

$backup_file_structure = $nom_epreuve.'-STRUCTURE-'.time().'.sql';
$backup_file_donnees = $nom_epreuve.'-DONNEES-'.time().'.sql';
$backup_file_total = $nom_epreuve.'-STRUCTURE-ET-DONNEES-'.time().'.sql';
// get backup
//structure d'une epreuve
$mybackup_structure = backup_tables("localhost","ATSPC","Pc489a!3eax45*aze","ATSPC","r_epreuve,r_epreuveparcours,r_champssupdotation,r_champssupparticipation,r_champssupquestiondiverse,r_champssupparticipation_commune,r_epreuveparcourstarif,r_epreuveparcourstarifpromo,r_insc_champ_separator,r_inscriptionepreuveinternaute,r_insc_internautereferent",$idEpreuve,$_GET['ud']);

//***$handle = fopen($backup_file_structure ,'w+');
//***fwrite($handle,$mybackup_structure);

//Données d'une épreuve


$mybackup_donnees = backup_tables("localhost","ATSPC","Pc489a!3eax45*aze","ATSPC","b_paiements,r_epreuvefichier,r_insc_champssupdotation,r_insc_champssupparticipation,r_insc_champssupquestiondiverse,r_insc_champssupparticipation_commune,r_insc_internautereferent,r_insc_internaute_temp,r_internautereferent,r_inscriptionepreuveinternaute",$idEpreuve,$_GET['ud']);

//***$handle = fopen($backup_file_donnees,'w+');
//***fwrite($handle,$mybackup_donnees);



$backup_total = $mybackup_structure."\n--------=============== DONNEES =================-----------\n".$mybackup_donnees;
	



header('Content-type: application/text');
header('Content-Disposition: attachment; filename='.$backup_file_total);

echo $backup_total;
exit();
	

	
?>