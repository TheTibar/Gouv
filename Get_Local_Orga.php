<?php
require_once __DIR__ . '/Classes/local_orga.php';
require_once __DIR__ . '/Classes/process.php';
include_once('fonctions.php');

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
$currentProcess = $process->__get("current_process");
echo(nl2br("Process actuel : " . $currentProcess . "\n"));


$urlRoot = 'https://lannuaire.service-public.fr/themes?theme=administration-locale#administration-locale';
$level = 0;
$filter_url = "https://lannuaire.service-public.fr/";

$resultRoot = getRootThemes($urlRoot);
//echo(nl2br("resultRoot : " . count($resultRoot) . "\n"));
//echo(buildTable($resultRoot));

$urlRoot = getRootUrls($urlRoot, $resultRoot);
//echo(nl2br("resultRoot : " . count($urlRoot) . "\n"));
//echo(buildTable($urlRoot));

writeDb($urlRoot, $currentProcess);

getOrgaByRoot($urlRoot, $currentProcess);




function getRootThemes($urlRoot)
{
    $localOrga = new Local_Orga();
    $content = curl_get_contents($urlRoot);
    $themes = [];
    
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
    
    $xPath = new DOMXPath($dom);
    
    $anchorTags = $xPath->evaluate('//div[@class="fiche-item"]//h2');
    $i = 1;
    /*
    echo("anchor_tags : ");
    var_dump($anchorTags);
    echo(nl2br("\n"));
   */
    foreach ($anchorTags as $anchorTag) {
        $theme = $anchorTag->nodeValue;
        $themes[] = array("id"=>$i, "theme"=>$theme);
        $i = $i + 1;
    }
    return $themes;
}

function getRootUrls($urlRoot, $resultRoot) 
{
    $localOrga = new Local_Orga();
    $content = curl_get_contents($urlRoot);
    $links = [];
    $label = [];
    $url = [];
    
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
    
    $xPath = new DOMXPath($dom);

    for($i = 1; $i < count($resultRoot); $i++)
    {
        //echo(nl2br("fiche-item-" . $resultRoot[$i]["id"] . "\n"));
        //il faut récupérer : la valeur du thème : $resultRoot[$i]["label"]
        //la valeur des champs a //div[@id="fiche-item-2"]//a
        //la valeur des champs href //div[@id="fiche-item-2"]//a/@href
        $theme = $resultRoot[$i]["theme"];
        //echo(nl2br($theme));
        $eval_a = '//div[@id="fiche-item-' . $resultRoot[$i]["id"] .'"]//a';
        
        $anchorTags = $xPath->evaluate($eval_a);
        
        foreach ($anchorTags as $anchorTag) {
            $label[] = array("theme" => $theme, "label" => strstr($anchorTag->nodeValue, " (", TRUE));
        }
        
        $eval_href = '//div[@id="fiche-item-' . $resultRoot[$i]["id"] .'"]//a/@href';
        //echo($eval_href);
        
        $anchorTags = $xPath->evaluate($eval_href);
        
        foreach ($anchorTags as $anchorTag) {
            $url[] = array("url" => $anchorTag->nodeValue);
        }
    }
    /*
    echo(nl2br("label \n"));
    var_dump($label);
    echo(nl2br("url \n"));
    var_dump($url);
*/
    for($k = 0; $k < count($label); $k++)
    {
        $links[] = array("theme"=>$label[$k]["theme"], "id"=>$k, "remote_id" => $k, "level" => 0, "link"=>$url[$k]["url"], "label"=>$label[$k]["label"], "father_id" => 0);
        //echo(nl2br("theme : " . $label[$k]["theme"] . ", label : " . $label[$k]["label"] . ", url : " . $url[$k]["url"] . "\n"));
    }

    
    return($links);
}

function writeDb($urlRoot, $currentProcess)
{
    $Local_Orga = new Local_Orga();
    for($i = 0; $i < count($urlRoot); $i++)
    {
        $theme = $urlRoot[$i]["theme"];
        $id = $urlRoot[$i]["id"];
        $remote_id = $urlRoot[$i]["remote_id"];
        $level = $urlRoot[$i]["level"];
        $link = $urlRoot[$i]["link"];
        $label = $urlRoot[$i]["label"];
        $father_id = $urlRoot[$i]["father_id"];
        $Local_Orga->createLocalOrga($theme, $id, $remote_id, $level, $link, $label, $father_id, $currentProcess);
    }
}

function getOrgaByRoot($urlRoot, $currentProcess)
{
    //for($i = 0; $i < count($urlRoot); $i++)
    $children = [];
    for($i = 0; $i < 10; $i++)
    {
        //1 on ouvre la page $i
        $url = $urlRoot[$i]["link"];
        $content = curl_get_contents($url);
        
        $label = $urlRoot[$i]["label"];
        $father_id = $urlRoot[$i]["remote_id"];
        
        //2 on récupère le nombre de pages
        $pageNumber = getLastPage($url);
        echo(nl2br($url . " (nombre de pages : " . $pageNumber . ") \n"));
        
        //3 on récupère les urls de la page //ul[@class="list-arrow list-orga"]//a
        if($urlRoot[$i]["label"] <> 'Mairie') //on élimine les 1200 pages des mairies
        {
            for($j = 1; $j <=$pageNumber; $j++)
            {
                $currentUrl = $url . "?page=" . $j;
                echo(nl2br($currentUrl . "\n"));
                
                
                /*
                 * ici, récupérer valeur de a et de //a/@href de chaque $currentUrl
                 * récupérer également longitude et latitude
                 * récupérer l'email et l'url du site
                 * splitter l'url pour obtenir la région et le département
                 * https://lannuaire.service-public.fr/region/departement/
                 * 
                 */
                
                
                
                
                
            }
        }
    }
}


//vient du fichier d'origine


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

function getNextLevelData($url, $fatherId, $remoteFatherId, $level, $filter_url, $current_process)
{
	//attention, on peut avoir des class "col-seconde" ou des class "annuaire"
	$Orga = new Orga();
	$i = 0;
	$content = curl_get_contents($url);
	$dom = new DOMDocument;
	$links = [];
	
	libxml_use_internal_errors(true);
	$dom->loadHTML($content);
	
	$xPath = new DOMXPath($dom);

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









