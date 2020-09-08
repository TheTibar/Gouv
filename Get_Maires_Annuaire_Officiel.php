<?php
/**/
require_once __DIR__ . '/Classes/mairie.php';
require_once __DIR__ . '/Classes/process.php';
include_once('fonctions.php');

/* à réactiver pour le passage en prod */
//On crée un process
$process = new Process();

//On récupère l'id du process en cours
$process->getCurrentProcess();
$current_process = $process->__get("current_process");
echo(nl2br("Process actuel : " . $current_process . "\n"));

if (isset($_GET['start']) && isset($_GET['nbrow'])) // On a une plage définie
{	$start = $_GET['start'];	$nbRow = $_GET['nbrow'];
	echo(nl2br("Début : " . $start . ", nombre de lignes : " . $nbRow . "\n"));
}
else // Il manque des paramètres, on avertit le visiteur
{	$start = -1;	$nbRow = -1;
	echo(nl2br("On récupère toutes les lignes \n"));
}


/**/

$Mairie = new Mairie();
if($start == -1 && $nbRow == -1) {
	$result = $Mairie->getAllUrl($current_process); //récupère toutes les urls qui n'ont pas d'email
} else {
	$result = $Mairie->getLimitedUrl($start, $nbRow, $current_process);
}

//var_dump($result);$nbCom = count($result);

/**/
for($j = 0; $j < $nbCom; $j++) {
	$currentURL = $result[$j]["url"];	$currentInseeCode = $result[$j]["insee_code"];	
	
	echo(nl2br("Etape " . $j . " sur " . $nbCom . ", ville : " . $currentURL . ", état : "));
	
	$content = curl_get_contents($currentURL);
	$dom = new DOMDocument;
	libxml_use_internal_errors(true);
	//$dom->loadHTML($content);
	$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
	
	$xPath = new DOMXPath($dom);


	$emailTags = $xPath->evaluate('//a[@class="send-mail"]'); //Ok
	foreach($emailTags as $emailTag) {
		$email = $emailTag->nodeValue;
	}		
		$long = 0;	$lat = 0;
	$coordTags = $xPath->evaluate('//script[contains(., "longitude")]'); //Ok
	foreach($coordTags as $coorTag) {
		$coord = $coorTag->nodeValue;		$deb = strpos($coord, "{");		$fin = strrpos($coord, "}");		$coord = urldecode(substr($coord, $deb, $fin - $deb + 1));		//echo(nl2br("Coord avant remplacement : " . $coord));		$array_in = ['ignKey', 'ignMap', 'initPoint', 'longitude', 'latitude', 'name', 'description', 'tokenForSearchOnAddress'];		$array_out = ['"ignKey"', '"ignMap"', '"initPoint"', '"longitude"', '"latitude"', '"name"', '"description"', '"tokenForSearchOnAddress"'];				$coord = str_replace($array_in, $array_out, $coord);		//echo(nl2br("Coord après remplacement : " . $coord));				$coordArray = json_decode($coord, true);		$long = isset($coordArray["initPoint"]["longitude"]) ? $coordArray["initPoint"]["longitude"] : 0;		$lat = isset($coordArray["initPoint"]["latitude"]) ? $coordArray["initPoint"]["latitude"] : 0;	}		
			//var_dump($coord);	/**/	$cityNames = $xPath->evaluate('//h1'); //Ok
	foreach($cityNames as $cityName) {
		$name = $cityName->nodeValue;
	}	$hab = 0;
	$mayor = "";
	
	echo(nl2br("Ville : " . $name . ", email : " . $email . " [long, lat] : [" . $long . ", " . $lat . "] \n"));	
	$res = $Mairie->createMairieDetail($currentInseeCode, $currentURL, $name, $mayor, $email, $hab, $long, $lat, $current_process);
	if($res) {
		echo(nl2br("OK \n"));
	} else {
		echo(nl2br("KO \n"));
	}
	
	//echo(nl2br("Nom : " . $name . ", email : " . $email . ", nb_hab : " . $hab . "\n"));
}


?>