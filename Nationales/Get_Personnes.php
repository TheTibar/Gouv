<?php
/**/
require_once __DIR__ . '/Classes/orga.php';
require_once __DIR__ . '/Classes/process.php';

//ob_start();
echo(nl2br("require once OK \n"));
//On crée un process
$process = new Process();

//On récupère l'id du process en cours
$process->getCurrentProcess();
$current_process = $process->__get("current_process"); //#TODO_PROCESS
echo(nl2br("Process actuel : " . $current_process . "\n"));




//On récupère la liste des liens pour lesquels on souhaite récupérer les données "Personne"
$Orga = new Orga();
$Orga->getAllLinks($current_process);
$linkList = $Orga->__get("person");
echo(nl2br("Récupération liste Ok \n"));

//On supprime les personnes existantes pour le process en cours : 
$Orga->resetPerson($current_process);
echo(nl2br("Reset Personnes Ok \n"));

//var_dump($linkList);
echo(nl2br("Count linkList : " . count($linkList) . "\n"));


for($cpt=0; $cpt < count($linkList); $cpt++)
//for($cpt=0; $cpt < 10; $cpt++) //pour les tests
{
	$url = $linkList[$cpt]["link"];
	echo(nl2br("i : " . $cpt . ", url : " . $url . "\n"));

	$persons = getPersonData($url);

	foreach($persons as $person)
	{
		$name = $person["name"];
		$remoteId = $person["remoteId"];
		$role = $person["jobTitle"];
		echo(nl2br("Rôle : " . $role . " nom : " . $name . " (remoteId : " . $remoteId . ")" . "\n"));
		$Orga->savePerson($remoteId, $name, $role, $current_process);
	}

}



//$url = "https://lannuaire.service-public.fr/gouvernement/cabinet-ministeriel_165689";
//$result = getPersonData($url);

//var_dump($result);

//echo(buildTable($result));

function curl_get_contents($url)
{
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  
  
  if(curl_exec($ch) === false)
	{
		echo(nl2br("CURL KO : " . curl_error($ch) . "\n"));
	}
	else
	{
		$data = curl_exec($ch);
	}
  
  
  curl_close($ch);
  return $data;
}


function getPersonData($url)
{
	$remoteId = getRemoteId($url);
	//les expressions xPaths à utiliser pour récupérer la fonction et le nom / prénom sont
	//ul[@class="sat-responsable"] //renvoie la liste des éléments du bloc "responsable"
	//ul[@class="sat-responsable"]//p[@itemprop="jobTitle"] //renvoie la fonction
	//ul[@class="sat-responsable"]//p[@itemprop="name"] //renvoie le nom

	echo(nl2br("url : " . $url . "\n"));
	$content = curl_get_contents($url);
	//echo("taille content : " . strlen($content));
	$dom = new DOMDocument;
	libxml_use_internal_errors(true);
	$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
	
	$xPath = new DOMXPath($dom);
	
	$anchorTags = $xPath->evaluate('//ul[@class="sat-responsable"]//p[@itemprop="jobTitle"]'); //Ok
	var_dump($anchorTags);

	foreach ($anchorTags as $anchorTag) {
		$jobTitles[] = array("remoteId"=>$remoteId, "jobTitle"=>$anchorTag->nodeValue, "name"=>"");
	}
	
	$anchorTags = $xPath->evaluate('//ul[@class="sat-responsable"]//p[@itemprop="name"]'); //Ok
	//var_dump($anchorTags);

	foreach ($anchorTags as $anchorTag) 
	{
		$array_name = explode(",", $anchorTag->nodeValue);
		$name = $array_name[0];
		$names[] = $name;
	}
	
	for($i=0; $i<count($jobTitles); $i++) {
		$jobTitles[$i]["name"] = $names[$i];
	}

	return $jobTitles;
}


function getRemoteId($url)
{
	$remoteId = substr($url, strrpos($url, "_") + 1);
	return $remoteId;
}


?>