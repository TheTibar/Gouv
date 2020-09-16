<?php
/**/
require_once __DIR__ . '/Classes/local_orga.php';
require_once __DIR__ . '/Classes/process.php';


$process = new Process();
//On récupère l'id du nouveau process
$process->getCurrentProcess();
$current_process = $process->__get("current_process"); //#TODO_PROCESS

echo(nl2br("Process actuel : " . $current_process . "\n"));

$Local_Orga = new Local_Orga();
$Local_Orga->getJSONMap($current_process);

?>