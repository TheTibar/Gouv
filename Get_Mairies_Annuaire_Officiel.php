<?php//error_reporting(E_ALL);
/**/
require_once __DIR__ . '/Classes/mairie.php';require_once __DIR__ . '/Classes/process.php';require_once __DIR__ . '/Classes/suivi.php';include_once('fonctions.php');
/* à réactiver pour le passage en prod *///On crée un process$process = new Process();$process->createProcess();
//On récupère l'id du nouveau process$process->getCurrentProcess();$current_process = $process->__get("current_process");echo(nl2br("Process actuel : " . $current_process . "\n"));$Mairie = new Mairie();$links = [];$maxPage = getLastAnnuaireMairiePage(); //dans la page fonctions.php$Suivi = new Suivi();$Suivi->setLastPage($maxPage);$Suivi->setCurrentPage(1);//Dans cette page, on initialise la récupération des données. Comme cela prend plus d'une heure, il faut relancer le même type de process plus tard pour compléter//c'est la page Get_Next_Mairies_Annuaire_Officiel.php qui s'en occupe, et que l'on peut appeler plusieurs fois sans risque d'écraser des données./**/for($i = 1; $i < $maxPage; $i++) { //pour les tests, on peut remplacer $maxPage par une petite valeur	echo(nl2br("Page " . $i ." sur " . $maxPage . " : "));	$urlRoot = "https://lannuaire.service-public.fr/navigation/mairie?page=" . $i;		$result = getComLinks($urlRoot, $current_process);	$Suivi->setCurrentPage($i);	//echo(buildTable($result));}
//$resultCom = getComLinks($resultDep, $current_process);//echo(nl2br("resultCom : " . count($resultCom) . "\n"));//echo(buildTable($resultCom));//echo(nl2br("\n"));/**/
function getComLinks($urlRoot, $current_process){	//echo(nl2br("Entree dans getComLinks \n"));	$Mairie = new Mairie();	$links = [];	echo(nl2br("currentURL : " . $urlRoot . "\n"));
	$content = curl_get_contents($urlRoot);	$dom = new DOMDocument;	libxml_use_internal_errors(true);	$dom->loadHTML($content);		$xPath = new DOMXPath($dom);	$anchorTags = $xPath->evaluate('//ul[@class="list-arrow list-orga"]//a/@href'); //Ok		//var_dump($anchorTags);	foreach ($anchorTags as $anchorTag) {		$comURL = $anchorTag->nodeValue;		//echo(nl2br("URL : " . $comURL . "\n"));		$comURLSplit = explode("/", $comURL);		//echo(nl2br("comURLSplit : \n"));		//var_dump($comURLSplit);		$region = $comURLSplit[3];		//echo(nl2br("Région : " . $region . "\n"));		$departement = $comURLSplit[4];		//echo(nl2br("Département : " . $departement . "\n"));		$mairie = $comURLSplit[5];		//echo(nl2br("Mairie : " . $mairie . "\n"));		$mairieSplit = explode("-", $mairie);		$insee_code = $mairieSplit[1];		$links[] = array("insee"=>$insee_code, "comURL"=>$comURL, "region"=>$region, "departement"=>$departement);		$Mairie->createInseeData($insee_code, $current_process, $comURL, $region, $departement);	}
	//echo(nl2br("sortie de getComLinks \n"));	return $links;}function buildTable($array){	echo("Entrée dans buildTable");	echo("<br>");    // start table    $html = '<table>';    // header row    $html .= '<tr>';    foreach($array[0] as $key=>$value){            $html .= '<th>' . htmlspecialchars($key) . '</th>';        }    $html .= '</tr>';    // data rows    foreach( $array as $key=>$value){        $html .= '<tr>';        foreach($value as $key2=>$value2){            $html .= '<td>' . htmlspecialchars($value2) . '</td>';        }        $html .= '</tr>';    }    // finish table and return it    $html .= '</table>';	echo("Sortie de buildTable");	echo("<br>");    return $html;}?>









