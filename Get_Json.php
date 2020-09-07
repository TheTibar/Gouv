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
//On récupère l'id du nouveau process
$process->getCurrentProcess();
$current_process = $process->__get("current_process");
echo("Process actuel : " . $current_process);

$Orga = new Orga();
$Orga->get3dJSON($current_process);


/* 
//Pour générer un JSON hierarchique (attention, la fonction encapsule dans [] alors que les programmes appelant n'ont pas besoin de ce niveau
$result = $Orga->getFlatArray($current_process);

//var_dump($result);

// Transform the data
$outputTree = transformTree($result, 0);

$nodes_json = json_encode($outputTree, JSON_UNESCAPED_UNICODE);
$Orga->writeToServer("networkgraph/interactive_network_demo/data/flat_data" . $current_process . ".json", $nodes_json);


function transformTree($treeArray, $parentId = null)
{
    $output = [];

    // Read through all nodes of the tree
    foreach ($treeArray as $node) {

        // If the node parent is same as parent passed in argument
        if ($node['parent'] == $parentId) {

            // Get all the children for that node, using recursive method
            $children = transformTree($treeArray, $node['id']);

            // If children are found, add it to the node children array
            if ($children) {
                $node['children'] = $children;
            }

            // Add the main node with/without children to the main output
            $output[] = $node;

            // Remove the node from main array to avoid duplicate reading, speed up the process
            unset($node);
        }
    }
    return $output;
}
*/
?>