<?php
/**/
require_once __DIR__ . '/Classes/orga.php';
require_once __DIR__ . '/Classes/process.php';

echo("ne pas oublier de mettre à jour le niveau 0 avec cette requête : update gouv_orga
set father_id = 172210
where father_id = 0
and remote_id <> 172210;");
/**/
$process = new Process();
//On récupère l'id du process en cours
$process->getCurrentProcess(); //#TODO_PROCESS
$current_process = $process->__get("current_process");  //#TODO_PROCESS
echo("Process actuel : " . $current_process);

$Orga = new Orga();
$Orga->get3dJSON($current_process);

?>