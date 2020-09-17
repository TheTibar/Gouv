<?php
/**/
require_once __DIR__ . '/Classes/local_orga.php';
require_once __DIR__ . '/Classes/process.php';

$process = new Process();
//On récupère l'id du nouveau process
$process->getCurrentProcess('L');
$current_L_process = $process->__get("current_process");

$process->getCurrentProcess('M');
$current_M_process = $process->__get("current_process");

echo(nl2br("Process locales actuel : " . $current_L_process . "\n"));
echo(nl2br("Process mairies actuel : " . $current_M_process . "\n"));

echo(nl2br("Création du niveau -1 et mise à jour du father_id du niveau 0 avec ce niveau -1"));

$Local_Orga = new Local_Orga();
//createCentralPoint($current_L_process)
echo(nl2br("Création du point central \n"));
$Local_Orga->createCentralPoint($current_L_process);

//updateFirstLevel($current_L_process)
echo(nl2br("Mise à jour du niveau 0 \n"));
$Local_Orga->updateFirstLevel($current_L_process);
/**/
echo(nl2br("Récupération des données dans un json \n"));
$Local_Orga->get3dJSON($current_L_process, $current_M_process);

?>