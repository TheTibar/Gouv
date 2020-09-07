<?php

/* */
require_once __DIR__ . '/Classes/mairie.php';
require_once __DIR__ . '/Classes/process.php';
include_once('fonctions.php');


/* à réactiver pour le passage en prod */
//On crée un process
$process = new Process();


//On récupère l'id du nouveau process
$process->getCurrentProcess();
$current_process = $process->__get("current_process");
echo(nl2br("Process actuel : " . $current_process . "\n"));


/* */

if (isset($_GET['start']) && isset($_GET['nbrow'])) // On a un level maximum
{	$start = $_GET['start'];	$nbRow = $_GET['nbrow'];
	echo(nl2br("Début : " . $start . ", nombre de lignes : " . $nbRow . "\n"));
}
else // Il manque des paramètres, on avertit le visiteur
{	$start = -1;	$nbRow = -1;
	echo(nl2br("Début : " . $start . ", nombre de lignes : " . $nbRow . "\n"));
}


/* */

$Mairie = new Mairie();
if($start == -1 && $nbRow == -1) {
    $result = $Mairie->getAllUrlWithoutMayor($current_process);
} else {
	$result = $Mairie->getLimitedUrlWithoutMayor($start, $nbRow);
}


//var_dump($result);$nbCom = count($result);


/* */	
for($j = 0; $j < $nbCom; $j++) {
	$currentURL = $result[$j]["url"];	$currentInseeCode = $result[$j]["insee_code"];	//https://www.amf.asso.fr/annuaire-communes-intercommunalites?refer=commune&dep_n_id=971&insee=97113
	$amfURL = "https://www.amf.asso.fr/annuaire-communes-intercommunalites?refer=commune&dep_n_id=";
	
	/* Remplacé par un case when
	if(substr($currentInseeCode, 0, 2) == "97" || substr($currentInseeCode, 0, 2) == "98") {
		$codeDep = substr($currentInseeCode, 0, 3);
	} else {
		$codeDep = substr($currentInseeCode, 0, 2);
	}
	
	$amfURL . $codeDep . "&insee=" . $currentInseeCode;
	*/
	
	if(substr($currentInseeCode, 0, 3) == "978" || substr($currentInseeCode, 0, 3) == "977") {
		$amfURL = "";
	}
	else {
		switch(substr($currentInseeCode, 0, 2)) {
		case "97" :
			$codeDep = substr($currentInseeCode, 0, 3);
			$amfURL = $amfURL . $codeDep . "&insee=" . $currentInseeCode;
		case "98" :
			$codeDep = substr($currentInseeCode, 0, 3);
			$amfURL = $amfURL . $codeDep . "&insee=" . $currentInseeCode;
		case "20" :
			$codeDep = substr($currentInseeCode, 0, 3);
			$amfURL = $amfURL . $codeDep . "&insee=" . $currentInseeCode;
		default :
			$codeDep = substr($currentInseeCode, 0, 2);
			$amfURL = $amfURL . $codeDep . "&insee=" . $currentInseeCode;
		}
	}
	
	if($amfURL <> "") {
		echo(nl2br("Etape " . $j . " sur " . $nbCom . ", ville AMF : " . $amfURL . ", état : "));

		
		$content = curl_get_contents_AMF($amfURL);
		$dom = new DOMDocument;
		libxml_use_internal_errors(true);
		//$dom->loadHTML($content);
		$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
		//var_dump($content);
		
		$xPath = new DOMXPath($dom);
		
		$nameTags = $xPath->evaluate('//div[starts-with(string(), "Nom du Maire")]//strong'); //Ok
		//var_dump($nameTags);
		foreach($nameTags as $nameTag) {
			$maire = $nameTag->nodeValue;
		}
		
		// //div[starts-with(string(), "Nombre")]//strong
		$habTags = $xPath->evaluate('//div[starts-with(string(), "Nombre")]//strong'); //Ok
		foreach($habTags as $habTag) {
			$hab = $habTag->nodeValue;
			$hab = intval(preg_replace('/[^0-9]/', '', $hab));
		}
		
		/* 		*/
		$res = $Mairie->updateMairieDetail($currentInseeCode, $maire, $hab);
		if($res) {
			echo(nl2br("OK \n"));
		} else {
			echo(nl2br("KO \n"));
		}

		echo(nl2br("Nom : " . $maire . " (Ville : " . $currentInseeCode . ", Nb hab : " . $hab . ") \n"));
		
	} else {
		echo(nl2br("Etape " . $j . " sur " . $nbCom . ", ville AMF : " . $currentInseeCode . ", état : HORS SCOPE \n"));
	}}


?>