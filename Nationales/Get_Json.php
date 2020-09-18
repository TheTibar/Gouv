<?php
/**/
require_once __DIR__ . '/Classes/orga.php';
require_once __DIR__ . '/Classes/process.php';

$Orga = new Orga();

$process = new Process();
//On récupère l'id du process en cours
$process->getCurrentProcess('N');
$current_process = $process->__get("current_process");

echo(nl2br("Mise à jour du father_id du niveau 0 avec le remote_id du PM \n"));
$Orga->createFirstLevel($current_process);

echo(nl2br("Suppression des boucles infinies PARENT -> ENFANT -> PARENT \n"));
$Orga->removeInfiniteLoops($current_process);
/**/

echo("Process actuel : " . $current_process);


$Orga->get3dJSON($current_process);

?>