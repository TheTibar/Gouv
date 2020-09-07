<?php
require_once __DIR__ . '/Classes/orga.php';
require_once __DIR__ . '/Classes/process.php';

//Tester les xpath sur http://xpather.com/
//dendogram zoomable : http://bl.ocks.org/robschmuecker/7880033
//explications, outils et paramétres : https://github.com/robschmuecker/d3-hierarchy

if (isset($_GET['max_level'])) // On a un level maximum
{
	echo 'Level max : ' . $_GET['max_level'];
	echo("<br>");
	$max_level = $_GET['max_level'];
}
else // Il manque des paramètres, on avertit le visiteur
{
	echo 'Level max par défaut : 1';
	echo("<br>");
	$max_level = 20;
}

//On crée un process
$process = new Process();
$process->createProcess();

//On récupère l'id du nouveau process
$process->getCurrentProcess();
$current_process = $process->__get("current_process");
echo("Process actuel : " . $current_process);
echo("<br>");

$urlRoot = 'https://lannuaire.service-public.fr/navigation/ministeres/';
$level = 0;
$id = "resultats";
$filter_url = "https://lannuaire.service-public.fr/";

$resultRoot = getRootLinks($id, $urlRoot, $level, $filter_url, $current_process);
$result = $resultRoot;
echo("resultRoot : " . count($result));
//echo("<br>");
//echo(buildTable($resultRoot));

/*Faire itérer tant que le level désiré ($max_level) n'est pas atteint*/
do
{
	echo("Level en cours : " . $level);
	echo("<br>");
	//1 - on récupère la liste des liens en cours
	$fatherLinks = array_filter($result, function ($var) use($level) {
		return ($var["level"] == $level);
	});
	echo("fatherLinks");
	echo("<br>");
	$fatherLinks = array_values($fatherLinks); //permet de renuméroter le tableau
	//var_dump($fatherLinks);

	//2 - On définit leurs enfants comme le niveau + 1 des liens en cours
	$level = $level + 1;
	echo("Level des enfants : " . $level);
	echo("<br>");
	//3 - Les données intéressantes peuvent se trouver dans les classes "col-second" ou "annuaire"
	$classes = ["col-second", "annuaire"];

	//4 - Pour chaque lien de fatherLinks, on va chercher les enfants
	echo("Nombre de parents à ce niveau : " . count($fatherLinks));
	echo("<br>");
	
	for($cpt=0; $cpt<count($fatherLinks); $cpt++) 
	//for($cpt=0; $cpt<3; $cpt++) //permet de tester sur uniquement quelques lignes parent
	{
		echo("i : " . $cpt . " link : " . $fatherLinks[$cpt]["link"] . " id : " . $fatherLinks[$cpt]["id"] . " remoteId : " . $fatherLinks[$cpt]["remoteId"]);
		echo("<br>");
		$resultChild = getNextLevelData($classes, $fatherLinks[$cpt]["link"], $fatherLinks[$cpt]["id"], $fatherLinks[$cpt]["remoteId"], $level, $filter_url, $current_process);
		//var_dump($result2);
		//echo("resultChild");
		//echo("<br>");
		//echo(buildTable($resultChild));
		$result = array_merge($result, $resultChild);
		echo("count after merge : " . count($result));
		echo("<br>");
		//echo(buildTable($result));
	}

//5 - On continue tant que count($fatherLinks) > 0 et que $level < 2 
}while ($level <= $max_level);

echo("On sort de la boucle DO au level : " . $level);

function curl_get_contents($url)
{
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}

function getRootLinks($id, $urlRoot, $level, $filter_url, $current_process)
{
	$Orga = new Orga();
	$content = curl_get_contents($urlRoot);
	$links = [];

	$dom = new DOMDocument;
	libxml_use_internal_errors(true);
	$dom->loadHTML($content);

	$xPath = new DOMXPath($dom);
	//Partie Ok sauf personnes
	$anchorTags = $xPath->evaluate('//div[@id="' . $id . '"]//a/@href'); //Ok
	$i = 0;
	
	foreach ($anchorTags as $anchorTag) {
		if (substr($anchorTag->nodeValue, 0, strlen($filter_url)) === $filter_url) {
			$remoteId = getRemoteId($anchorTag->nodeValue);
			$link = $anchorTag->nodeValue;
			$label = getTitleContent($anchorTag->nodeValue);
			$links[] = array("id"=>$i, "remoteId"=>$remoteId,  "level"=>$level, "link"=>$link, "label"=>$label, "father"=>0);
			$Orga->createOrga($i, $remoteId, $level, $link, $label, 0, $current_process);
			$i = $i + 1;
			
		}
	}
	return $links;
}

function getRemoteId($url)
{
	$remoteId = substr($url, strrpos($url, "_") + 1);
	return $remoteId;
}

function getTitleContent($url)
{
	//echo("url Title Content : " . $url);

	$content = curl_get_contents($url);
	
	$dom = new DOMDocument;
	libxml_use_internal_errors(true);
	$dom->loadHTML($content);

	foreach ($dom->getElementsByTagName('h1') as $node) {
		$label = utf8_decode($node->nodeValue);
	}
	return $label;
	
	

}

function dumpNode($nodes)
{
	foreach($nodes as $node) {
		echo $node->nodeValue, PHP_EOL;
	}
}

function getNextLevelData($classes, $url, $fatherId, $remoteFatherId, $level, $filter_url, $current_process)
{
	echo("Entrée dans getNextLevelData");
	echo("<br>");
	//attention, on peut avoir des class "col-seconde" ou des class "annuaire"
	$Orga = new Orga();
	$i = 0;
	$content = curl_get_contents($url);
	$dom = new DOMDocument;
	$links = [];
	
	libxml_use_internal_errors(true);
	$dom->loadHTML($content);
	
	$xPath = new DOMXPath($dom);
	
	foreach($classes as $class) {
		echo("Class xPath en cours : " . $class);
		echo("<br>");
		$anchorTags = $xPath->evaluate('//div[@class="' . $class . '"]//a/@href'); //Ok
		
		foreach ($anchorTags as $anchorTag) {
			if (substr($anchorTag->nodeValue, 0, strlen($filter_url)) === $filter_url) {
				$remoteId = getRemoteId($anchorTag->nodeValue);
				$link = $anchorTag->nodeValue;
				$label = getTitleContent($anchorTag->nodeValue);
				$links[] = array("id"=>$i, "remoteId"=>$remoteId,  "level"=>$level, "link"=>$link, "label"=>$label, "father"=>$remoteFatherId);
				$Orga->createOrga($i, $remoteId, $level, $link, $label, $remoteFatherId, $current_process);
				$i = $i + 1;
			}
		}
	}
	echo("Sortie de getNextLevelData");
	echo("<br>");
	
	return $links;

}


function buildTable($array)
{
	echo("Entrée dans buildTable");
	echo("<br>");
    // start table
    $html = '<table>';
    // header row
    $html .= '<tr>';
    foreach($array[0] as $key=>$value){
            $html .= '<th>' . htmlspecialchars($key) . '</th>';
        }
    $html .= '</tr>';

    // data rows
    foreach( $array as $key=>$value){
        $html .= '<tr>';
        foreach($value as $key2=>$value2){
            $html .= '<td>' . htmlspecialchars($value2) . '</td>';
        }
        $html .= '</tr>';
    }

    // finish table and return it

    $html .= '</table>';
	echo("Sortie de buildTable");
	echo("<br>");
    return $html;
}

?>









