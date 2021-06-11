<?php 
	require_once('functions.php');
	//require_once('connect_db.php');
	//connect_db();
global $mysqli;
$num_lic = $_GET['num'];
$id_epreuve = $_GET['id_epreuve'];


$ch = curl_init();

// Set url
curl_setopt($ch, CURLOPT_URL, 'https://licence-api.espacetri.fftri.com/api/test-licence/13');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "X-USER: POINTCOURSE",
  "Authorization: 91fa0913fc2a474f51bec9b82d27e9c0",
  "Content-Type: application/json",
  "Accept: application/json",
 ]
);
// Create body
$json_array = [
            "licence" => $num_lic
        ];
$body = json_encode($json_array);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
$resp = curl_exec($ch);
//OK - licence found and available for season
curl_close($ch);



$champs=array();
if (strpos($resp, 'OK') !== false) {
    $champs['MSG_RETOUR'] = 'OK';
}
else
{
	$champs['MSG_RETOUR'] = 'KO';
}
$json = json_encode($champs);
print_r($json);
?>