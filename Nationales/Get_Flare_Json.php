<?php
/**/
require_once __DIR__ . '/Classes/orga.php';
require_once __DIR__ . '/Classes/process.php';

/**/
$process = new Process();
//On récupère l'id du nouveau process
$process->getCurrentProcess('N');
$current_process = $process->__get("current_process");

echo(nl2br("\n Process actuel : " . $current_process . "\n"));

//Pour générer un JSON hierarchique
$Orga = new Orga();
$result = $Orga->getFlatArray($current_process);

//var_dump($result);

// Transform the data
$outputTree = transformTree($result, 0);
$outputTree = $outputTree[0];

$nodes_json = json_encode($outputTree, JSON_UNESCAPED_UNICODE);

//var_dump($nodes_json);

$path = "/../d3graph/data/";
$filename = "flare_" . $current_process . ".json";

$Orga->writeToServer($path, $filename, $nodes_json);


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

?>