<?php

require_once __DIR__ . '/Classes/mairie.php';
require_once __DIR__ . '/Classes/process.php';
require_once __DIR__ . '/Classes/suivi.php';
include_once('fonctions.php');


/* à réactiver pour le passage en prod */
//On récupère le process
$process = new Process();

//On récupère l'id du nouveau process
$process->getCurrentProcess();
$current_process = $process->__get("current_process"); //#TODO_PROCESS
echo(nl2br("Process actuel : " . $current_process . "\n"));

$Mairie = new Mairie();
$links = [];



$Suivi = new Suivi();

$Suivi->getCurrentPage();
$currentPage = $Suivi->__get("current_page");
/*
$maxPage = $currentPage + 3;
*/
$Suivi->getLastPage();
$maxPage = $Suivi->__get("last_page");



//Dans cette page, on initialise la récupération des données. Comme cela prend plus d'une heure, il faut relancer le même type de process plus tard pour compléter
//c'est la page Get_Next_Mairies_Annuaire_Officiel.php qui s'en occupe, et que l'on peut appeler plusieurs fois sans risque d'écraser des données.

for($i = $currentPage; $i <= $maxPage; $i++) { //pour les tests, on peut remplacer $maxPage par une petite valeur
	echo(nl2br("Page " . $i ." sur " . $maxPage . " : "));
	$urlRoot = "https://lannuaire.service-public.fr/navigation/mairie?page=" . $i;
	
	$result = getComLinks($urlRoot, $current_process);
	$Suivi->setCurrentPage($i);
}

function getComLinks($urlRoot, $current_process)
{
	$Mairie = new Mairie();
	$links = [];

	echo(nl2br("(" . date('Y-m-d H:i:s') . ") " . "currentURL : " . $urlRoot . "\n"));
	$content = curl_get_contents($urlRoot); //3 secondes environ par page...
	$dom = new DOMDocument;
	libxml_use_internal_errors(true);
	//echo(nl2br("(" . date('Y-m-d H:i:s') . ") " . "loadHTML \n"));
	$dom->loadHTML($content);
	
	$xPath = new DOMXPath($dom);
	$anchorTags = $xPath->evaluate('//ul[@class="list-arrow list-orga"]//a/@href'); //Ok
	foreach ($anchorTags as $anchorTag) {
		$comURL = $anchorTag->nodeValue;
		$comURLSplit = explode("/", $comURL);
		$region = $comURLSplit[3];
		$departement = $comURLSplit[4];
		$mairie = $comURLSplit[5];
		$mairieSplit = explode("-", $mairie);
		$insee_code = $mairieSplit[1];
		$order_number = intval($mairieSplit[2]);
		$links[] = array("insee"=>$insee_code, "order_number"=>$order_number, "comURL"=>$comURL, "region"=>$region, "departement"=>$departement);
		$Mairie->createInseeData($insee_code, $order_number, $current_process, $comURL, $region, $departement);
	}
	return $links;
}
?>



















