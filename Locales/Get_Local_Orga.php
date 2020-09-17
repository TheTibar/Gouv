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
$process->createProcess('L');

//On récupère l'id du nouveau process
$process->getCurrentProcess('L');
$currentProcess = $process->__get("current_process");
echo(nl2br("Process actuel : " . $currentProcess . "\n"));


$urlRoot = 'https://lannuaire.service-public.fr/';
$level = 0;
$filter_url = "https://lannuaire.service-public.fr/";



$rootThemesArray = getRootThemes($urlRoot);
echo(nl2br("rootThemesArray : " . count($rootThemesArray) . "\n"));
echo(buildTable($rootThemesArray));
writeDb($rootThemesArray, $currentProcess);


$urlRootArray = getRootUrls($rootThemesArray);
echo(nl2br("urlRootArray : " . count($urlRootArray) . "\n"));
echo(buildTable($urlRootArray));

writeDb($urlRootArray, $currentProcess);

/**/
$orgaByRoot = getOrgaByRoot($urlRootArray);
echo(nl2br("orgaByRoot : " . count($orgaByRoot) . "\n"));
//echo(buildTable($orgaByRoot));

writeDb($orgaByRoot, $currentProcess);



function getRootThemes($urlRoot)
{
    //$localOrga = new Local_Orga();
    $content = curl_get_contents($urlRoot);
    $themes = [];
    
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
    
    $xPath = new DOMXPath($dom);
    
    $anchorTags = $xPath->evaluate('//h3');
    $urlTags = $xPath->evaluate('//h3/a/@href');
    $i = 1;
    foreach ($anchorTags as $anchorTag) {
        $theme = $anchorTag->nodeValue;
        $themes[] = array("theme"=>$theme, "id"=>$i, "remote_id" => $i, "level" => 0, "link" => "", "label" => $theme, "father_id" => 0);
        $i = $i + 1;
    }
    $i = 1;
    
    foreach ($urlTags as $urlTag) {
        $url = $urlTag->nodeValue;
        $urls[] = array("id"=>$i, "link" => $url);
        $i = $i + 1;
    }
    
    for($i = 0; $i < count($themes); $i++)
    {
        $themes[$i]["link"] = $urls[$i]["link"];
    }
    return $themes;
}

function getRootUrls($rootThemesArray) 
{
    $localOrga = new Local_Orga();
    
    $links = [];
    $label = [];
    $url = [];
    


    for($i = 1; $i < count($rootThemesArray); $i++)
    {
        echo(nl2br("currentUrl : " . $rootThemesArray[$i]["link"] . "\n"));
        $content = curl_get_contents($rootThemesArray[$i]["link"]);
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
        
        $xPath = new DOMXPath($dom);
        
        
        //echo(nl2br("father_id : " . $rootThemesArray[$i]["remote_id"] . ", fiche-item-" . $rootThemesArray[$i]["id"] . "\n"));
        //il faut récupérer : la valeur du thème : $resultRoot[$i]["label"]
        //la valeur des champs a //div[@id="fiche-item-2"]//a
        //la valeur des champs href //div[@id="fiche-item-2"]//a/@href
        $theme = $rootThemesArray[$i]["theme"];
        $father_id = $rootThemesArray[$i]["remote_id"];
        //echo(nl2br($theme));
        $eval_a = '//div[@id="fiche-item-' . $rootThemesArray[$i]["id"] .'"]//a';
        
        $anchorTags = $xPath->evaluate($eval_a);
        
        foreach ($anchorTags as $anchorTag) {
            $label[] = array("theme" => $theme, "label" => strstr($anchorTag->nodeValue, " (", TRUE), "father_id" => $father_id);
        }
        //var_dump($label);
        //echo(nl2br("\n"));
        
        $eval_href = '//div[@id="fiche-item-' . $rootThemesArray[$i]["id"] .'"]//a/@href';
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
        $links[] = array("theme"=>$label[$k]["theme"], "id"=>$k, "remote_id" => $k, "level" => 1, "link"=>$url[$k]["url"], "label"=>$label[$k]["label"], "father_id" => $label[$k]["father_id"]);
        //echo(nl2br("theme : " . $label[$k]["theme"] . ", label : " . $label[$k]["label"] . ", url : " . $url[$k]["url"] . "\n"));
    }

    
    return($links);
}

function writeDb($urlRootArray, $currentProcess)
{
    $Local_Orga = new Local_Orga();
    for($i = 0; $i < count($urlRootArray); $i++)
    {
        $theme = $urlRootArray[$i]["theme"];
        $id = $urlRootArray[$i]["id"];
        $remote_id = $urlRootArray[$i]["remote_id"];
        $level = $urlRootArray[$i]["level"];
        $link = $urlRootArray[$i]["link"];
        $label = $urlRootArray[$i]["label"];
        $father_id = $urlRootArray[$i]["father_id"];
        $Local_Orga->createLocalOrga($theme, $id, $remote_id, $level, $link, $label, $father_id, $currentProcess);
    }
}

function getOrgaByRoot($urlRootArray)
{
    
    $children = [];
    $label = [];
    $url = [];
    for($i = 0; $i < count($urlRootArray); $i++)
    //for($i = 0; $i < 3; $i++)
    {
        //1 on ouvre la page $i
        $urlRoot = $urlRootArray[$i]["link"];
        $theme = $urlRootArray[$i]["theme"];
        
        $content = curl_get_contents($urlRoot);
        
        //$label = $urlRootArray[$i]["label"];
        $father_id = $urlRootArray[$i]["remote_id"];
        
        //2 on récupère le nombre de pages
        $pageNumber = getLastPage($urlRoot);
        //echo(nl2br($urlRoot . " (nombre de pages : " . $pageNumber . ") \n"));
        
        //3 on récupère les urls de la page //ul[@class="list-arrow list-orga"]//a
        if($urlRootArray[$i]["label"] <> 'Mairie') //on élimine les 1200 pages des mairies
        {
            
            for($j = 1; $j <=$pageNumber; $j++)
            {
                $currentUrl = $urlRoot . "?page=" . $j;
                //echo(nl2br($currentUrl . "\n"));
                
                $content = curl_get_contents($currentUrl);
                
                $dom = new DOMDocument();
                libxml_use_internal_errors(true);
                $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
                
                $xPath = new DOMXPath($dom);
                
                /*
                 * ici, récupérer valeur de 
                 *      a : //ul[@class="list-arrow list-orga"]//li//a
                 *      href : //ul[@class="list-arrow list-orga"]//li//a/@href 
                 * de chaque $currentUrl
                 */

                
                $eval_a = '//ul[@class="list-arrow list-orga"]//li//a';
                
                $anchorTags = $xPath->evaluate($eval_a);
                
                foreach ($anchorTags as $anchorTag) {
                    $label[] = array("theme" => $theme, "label" => $anchorTag->nodeValue, "father_id" => $father_id);
                }
                
                $eval_href = '//ul[@class="list-arrow list-orga"]//li//a/@href';
                //echo($eval_href);
                
                $anchorTags = $xPath->evaluate($eval_href);
                
                foreach ($anchorTags as $anchorTag) {
                    $url[] = array("url" => $anchorTag->nodeValue);
                }
            }
        }
    }
    for($k = 0; $k < count($label); $k++)
    {
        $links[] = array("theme"=>$label[$k]["theme"], "id"=>$k, "remote_id" => $k, "level" => 2, "link"=>$url[$k]["url"], "label"=>$label[$k]["label"], "father_id" => $label[$k]["father_id"]);
        //echo(nl2br("theme : " . $label[$k]["theme"] . ", label : " . $label[$k]["label"] . ", url : " . $url[$k]["url"] . "\n"));
    }
    
    return($links);
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









