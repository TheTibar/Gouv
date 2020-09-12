<?php

function curl_get_contents($url)
{
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  if(curl_exec($ch) === false)
	{
		echo(nl2br("Erreur Curl : " . curl_error($ch) . "\n"));
		curl_close($ch);
	}
	else
	{
		//echo(nl2br("L'opération s'est terminée sans aucune erreur \n"));
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
}


function curl_get_contents_AMF($url)
/*$agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';*/
{
  $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  
  curl_setopt($ch, CURLOPT_USERAGENT, $agent);
  
  if(curl_exec($ch) === false)
	{
		echo(nl2br("Erreur Curl : " . curl_error($ch) . "\n"));
		curl_close($ch);
	}
	else
	{
		//echo(nl2br("L'opération s'est terminée sans aucune erreur \n"));
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
}



 /*Récupération de la dernière page*/
 
 function getLastAnnuaireMairiePage() /*Permet de récupérer la dernière page de l'annuaire des mairies*/
 {
	 $rootURL = "https://lannuaire.service-public.fr/navigation/mairie?page=1";
	 /*Il faut récupérer la valeur du li qui se trouve juste avant le li "next"*/
	 //   //li[preceding-sibling::li[contains(., "...")] and following-sibling::li[contains(., "Suivant")]]
	 
	$content = curl_get_contents($rootURL);
	$dom = new DOMDocument;
	libxml_use_internal_errors(true);
	$dom->loadHTML($content);
	
	$xPath = new DOMXPath($dom);

	$lastPages = $xPath->evaluate('//li[preceding-sibling::li[contains(., "...")] and following-sibling::li[contains(., "Suivant")]]'); //Ok
	foreach($lastPages as $lastPage) {
		$pageNumber = $lastPage->nodeValue;
	}
	
	return $pageNumber;
 }
 
 
 function getLastPage($url)
 {
     $pageNumber = 1;
     $content = curl_get_contents($url);
     $dom = new DOMDocument;
     libxml_use_internal_errors(true);
     $dom->loadHTML($content);
     
     $xPath = new DOMXPath($dom);
     
     $lastPages = $xPath->evaluate('//li[preceding-sibling::li[contains(., "...")] and following-sibling::li[contains(., "Suivant")]]'); //Ok
     foreach($lastPages as $lastPage) {
         $pageNumber = $lastPage->nodeValue;
     }
     
     return $pageNumber;
 }


?>