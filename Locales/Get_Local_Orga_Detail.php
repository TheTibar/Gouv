<?php
require_once __DIR__ . '/Classes/local_orga.php';
require_once __DIR__ . '/Classes/process.php';
include_once('fonctions.php');


//On récupère le process en cours
$process = new Process(); 

//On récupère l'id du nouveau process
$process->getCurrentProcess('L');
$currentProcess = $process->__get("current_process");
echo(nl2br("Process actuel : " . $currentProcess . "\n"));


$local_orga = new Local_Orga();
if($local_orga->getChildUrls($currentProcess)) {
    $childUrls = $local_orga->__get("childUrls");
    getDetailByUrl($childUrls, $currentProcess); 
}
else {
    echo("Error getting child urls");
}


function getDetailByUrl($childUrls, $currentProcess) 
{
    $detail = [];
    for($i = 0; $i < count($childUrls); $i++) 
    //for($i = 0; $i < 20; $i++) 
    {
        $currentUrl = $childUrls[$i]["link"];
        $currentId = $childUrls[$i]["local_orga_id"];
        echo(nl2br($currentUrl . "\n"));

        $region = explode('/', $currentUrl)[3];
        $departement = explode('/', $currentUrl)[4];
        
        $content = curl_get_contents($currentUrl);
        
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
        
        $xPath = new DOMXPath($dom);
        
        $long = 0;
        $lat = 0;
        
        $coordTags = $xPath->evaluate('//script[contains(., "longitude")]'); //Ok
        foreach($coordTags as $coorTag) {
            $coord = $coorTag->nodeValue;
            $deb = strpos($coord, "{");
            $fin = strrpos($coord, "}");
            $coord = urldecode(substr($coord, $deb, $fin - $deb + 1));
            
            $array_in = ['ignKey', 'ignMap', 'initPoint', 'longitude', 'latitude', 'name', 'description', 'tokenForSearchOnAddress'];
            $array_out = ['"ignKey"', '"ignMap"', '"initPoint"', '"longitude"', '"latitude"', '"name"', '"description"', '"tokenForSearchOnAddress"'];
            
            $coord = str_replace($array_in, $array_out, $coord);
            
            $coordArray = json_decode($coord, true);
            $long = isset($coordArray["initPoint"]["longitude"]) ? $coordArray["initPoint"]["longitude"] : 0;
            $lat = isset($coordArray["initPoint"]["latitude"]) ? $coordArray["initPoint"]["latitude"] : 0;
        }	
        
        $emailTags = $xPath->evaluate('//a[@class="send-mail"]'); //Ok
        foreach($emailTags as $emailTag) {
            $email = $emailTag->nodeValue;
        }

        $websiteTags = $xPath->evaluate('//a[@id="websites"]/@href'); //Ok
        foreach($websiteTags as $websiteTag) {
            $website = $websiteTag->nodeValue;
        }

        //echo(nl2br("Local orga id : " . $currentId . ", région : " . $region . ", département : " . $departement . ", long : " . $long . ", lat : " . $lat . ", email : " . $email . ", website : " . $website . "\n"));
        $local_orga = new Local_Orga();
        $local_orga->createLocalOrgaDetail($currentId, $region, $departement, $long, $lat, $email, $website, $currentProcess);
    }
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









