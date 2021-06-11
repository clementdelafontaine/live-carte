<?php
$id_parcours='';
if (!empty($_POST['id_parcours'])) $id_parcours = '&id_parcours='.$_POST['id_parcours'];
$html='<iframe src="https://www.ats-sport.com/liste_des_inscrits.php?id_epreuve='.$_POST['id_epreuve'].$id_parcours.'&panel=iframe" width="100%" height="1200" frameBorder="0"></iframe>';
$comeback = array('html'=>$html);
$json_comeback = json_encode($comeback);
echo $json_comeback;	

?>