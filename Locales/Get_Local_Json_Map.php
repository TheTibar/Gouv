<?php
/**/
require_once __DIR__ . '/Classes/local_orga.php';
require_once __DIR__ . '/Classes/process.php';


$process = new Process();
//On récupère l'id du dernier process Local
$process->getCurrentProcess('L');
$current_L_process = $process->__get("current_process");

$process->getCurrentProcess('M');
$current_M_process = $process->__get("current_process");

echo(nl2br("Process local : " . $current_L_process . "\n"));
echo(nl2br("Process mairies : " . $current_M_process . "\n"));

$Local_Orga = new Local_Orga();
$Local_Orga->getJSONMap($current_L_process, $current_M_process);

?>