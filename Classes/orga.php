<?php
//use Exception;
include_once dirname(__FILE__) . '/db_connect.php';
include_once dirname(__FILE__) . '/flat_tree.php';

class Orga {

    private $id;
    private $remote_id;
    private $level;
    private $link;
    private $label;
	private $father_id;
	private $person;
    
    public function test()
    {
        var_dump(get_object_vars($this));
    }
    
    public function export()
    {
        return get_object_vars($this);
    }
    
    public function __construct()
    {}
    
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    } 
  
    public function createOrga($id, $remote_id, $level, $link, $label, $father_id, $current_process) {
        $instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
        
        //$user_token = mysqli_real_escape_string($mysql_db_conn, $user_token);
        $id = mysqli_real_escape_string($conn, $id);
        $remote_id = mysqli_real_escape_string($conn, $remote_id);
		$level = mysqli_real_escape_string($conn, $level);
		$link = mysqli_real_escape_string($conn, $link);
		$label = mysqli_real_escape_string($conn, $label);
		$father_id = mysqli_real_escape_string($conn, $father_id);

        $sql = "INSERT INTO gouv_orga (id, remote_id, level, link, label, father_id, process_id) 
                VALUES ($id, $remote_id, $level, '$link', '$label', $father_id, $current_process)";
        
        //echo $sql;
        
        try
        {
            if (mysqli_query($conn, $sql))
            {
                //echo("0");
                return 0; //Cr�ation OK
            }
            else
            {
                //echo("-1");
                return -1; //Cr�ation KO
            }
        }
        catch (Exception $e)
        {
            echo ("Erreur : " . $e);
        }
    }
	
	public function getSubOrgaByLevel($level) {
		$level = mysqli_real_escape_string($conn, $level);
	}
	
	public function getJSON($current_process) {
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
		
		
		$result = [];
		$orga = [];
		$links = [];
		
		$sql = "SELECT label as name, link as artist, remote_id as id, 1 as playcount from gouv_orga where process_id = $current_process";

		/*Format nodes
			"nodes": [
				{
					"name": "node 1",
					"artist": "artist name",
					"id": "unique_id_1",
					"playcount": 123
				},
				{
					"name": "node 2",
				# ...
				}
			],
		*/

		if ($sql_result = mysqli_query($conn, $sql))
		{
			while ($line = mysqli_fetch_assoc($sql_result))
			{
				$orga[] = $line;
			}
		} else {
			echo("getting orga json error");
		}
		
		$sql = "SELECT father_id as source, remote_id as target from gouv_orga where father_id <> 0 and process_id = $current_process";
		
		/* Format links
			"links": [
				{
				  "source": "unique_id_1",
				  "target": "unique_id_2"
				},
				{
				  # ...
				}
		*/
		
		if ($sql_result = mysqli_query($conn, $sql))
		{
			while ($line = mysqli_fetch_assoc($sql_result))
			{
				$links[] = $line;
			}
		} else {
			echo("getting links json error");
		}
		
		$result['nodes'] = $orga;
		$result['links'] = $links;
		$nodes_json = json_encode($result, JSON_UNESCAPED_UNICODE);
		
		if (file_put_contents("networkgraph/interactive_network_demo/data/data_node_links" . $current_process . ".json", $nodes_json))
			echo "JSON file created successfully...";
		else 
			echo "Oops! Error creating json file...";
		
	}

	public function get3dJSON($current_process) {
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
		
		
		$result = [];
		$orga = [];
		$links = [];
		
		$sql = "SELECT distinct label as user, link as description, remote_id as id from gouv_orga where process_id = $current_process";

		/*Format nodes
		  "nodes": [
			{
			  "id": "4062045",
			  "user": "mbostock",
			  "description": "Force-Directed Graph"
			},
			{
			  "id": "1341021",
			  "user": "mbostock",
			  "description": "Parallel Coordinates"
			},
			...
			]
		*/

		if ($sql_result = mysqli_query($conn, $sql))
		{
			while ($line = mysqli_fetch_assoc($sql_result))
			{
				$orga[] = $line;
			}
		} else {
			echo("getting orga json error");
		}
		
		$sql = "SELECT father_id as source, remote_id as target from gouv_orga where father_id <> 0 and process_id = $current_process";
		
		/* Format links
			  "links": [
				{
				  "source": "950642",
				  "target": "4062045"
				},
				{
				  "source": "1341281",
				  "target": "1341021"
				},
				...
				]
		*/
		
		if ($sql_result = mysqli_query($conn, $sql))
		{
			while ($line = mysqli_fetch_assoc($sql_result))
			{
				$links[] = $line;
			}
		} else {
			echo("getting links json error");
		}
		
		$result['nodes'] = $orga;
		$result['links'] = $links;
		$nodes_json = json_encode($result, JSON_UNESCAPED_UNICODE);
		
		if (file_put_contents("networkgraph/3d-force-graph-master/example/datasets/data_node_links" . $current_process . ".json", $nodes_json))
			echo "JSON file created successfully...";
		else 
			echo "Oops! Error creating json file...";
		
	}
	
	public function getTreeJSON($current_process) { //pas loin de fonctionner mais pas nickel

		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
		
		$result = [];
		
		$sql = "SELECT remote_id as id, label as name, link as link, father_id as parent, '' as relation, '' as color, '' as size from gouv_orga 
				where process_id = $current_process";
				
		echo($sql);
		/**/
		if ($sql_result = mysqli_query($conn, $sql))
		{
			while ($line = mysqli_fetch_assoc($sql_result))
			{
				$result[] = $line;
			}
		} else {
			echo("getting links json error");
		}
		
		$FlatTree = new FlatToTreeConverter();
		$result = $FlatTree->convert($result, "id", "parent", "children");

		$nodes_json = json_encode($result, JSON_UNESCAPED_UNICODE);
		
		if (file_put_contents("networkgraph/interactive_network_demo/data/flat_data" . $current_process . ".json", $nodes_json))
			echo "JSON file created successfully...";
		else 
			echo "Oops! Error creating json file...";
		
	}

	public function getFlatJSON($current_process) {
		/* Format flat : 
		var data = [
		 { "name" : "ABC", "parent":"DEF", "relation": "ghi", "depth": 1 },
		 { "name" : "DEF", "parent":"null", "relation": "null", "depth": 0 },
		 { "name" : "new_name", "parent":"ABC", "relation": "rel", "depth": 2 },
		 { "name" : "new_name2", "parent":"ABC", "relation": "foo", "depth": 2 },
		 { "name" : "Foo", "parent":"DEF", "relation": "rel", "depth": 2 },
		 { "name" : "Bar", "parent":"null", "relation": "rel", "depth": 2 }
		];
		*/
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
		
		$result = [];
		
		$sql = "SELECT remote_id as id, label as name, link as link, father_id as parent, '' as relation, '' as color, '' as size from gouv_orga 
				where process_id = $current_process";
				
		echo($sql);
		/**/
		if ($sql_result = mysqli_query($conn, $sql))
		{
			while ($line = mysqli_fetch_assoc($sql_result))
			{
				$result[] = $line;
			}
		} else {
			echo("getting links json error");
		}

		$nodes_json = json_encode($result, JSON_UNESCAPED_UNICODE);
		
		if (file_put_contents("networkgraph/interactive_network_demo/data/flat_data" . $current_process . ".json", $nodes_json))
			echo "JSON file created successfully...";
		else 
			echo "Oops! Error creating json file...";
		
	}

	public function getFlatArray($current_process) {
		/* Format flat : 
		var data = [
		 { "name" : "ABC", "parent":"DEF", "relation": "ghi", "depth": 1 },
		 { "name" : "DEF", "parent":"null", "relation": "null", "depth": 0 },
		 { "name" : "new_name", "parent":"ABC", "relation": "rel", "depth": 2 },
		 { "name" : "new_name2", "parent":"ABC", "relation": "foo", "depth": 2 },
		 { "name" : "Foo", "parent":"DEF", "relation": "rel", "depth": 2 },
		 { "name" : "Bar", "parent":"null", "relation": "rel", "depth": 2 }
		];
		*/
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
		
		$result = [];
		
		$sql = "SELECT remote_id as id, label as name, link as link, father_id as parent, '' as relation, '' as color, '' as size from gouv_orga 
				where process_id = $current_process";
				
		echo($sql);
		/**/
		if ($sql_result = mysqli_query($conn, $sql))
		{
			while ($line = mysqli_fetch_assoc($sql_result))
			{
				$result[] = $line;
			}
		} else {
			echo("getting links json error");
		}

		return $result;
		
	}
	
	public function writeToServer($path, $json) {
		if (file_put_contents($path, $json))
			echo "JSON file created successfully...";
		else 
			echo "Oops! Error creating json file...";
	}

	public function getAllLinks($current_process) {
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
		
		$current_process = mysqli_real_escape_string($conn, $current_process);
		
		$sql = "SELECT DISTINCT link from gouv_orga where process_id = $current_process";
		
		//echo($sql);
		
		if ($sql_result = mysqli_query($conn, $sql))
		{
			while ($line = mysqli_fetch_assoc($sql_result))
			{
				$result[] = $line;
			}
			$this->person = $result;
		} else {
			echo("getting orga links error");
		}
		
	}

	public function resetPerson($current_process) {
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();

		$current_process = mysqli_real_escape_string($conn, $current_process);	

		$sql = "DELETE FROM gouv_person
				WHERE process_id = $current_process";
		
		echo($sql);
		echo("<br>");
		
		try {
            if (mysqli_query($conn, $sql))
            {
				echo "Reset person successfull";
				echo("<br>");
            } 
			else
            {
				echo "Error: " . $sql . "<br>" . mysqli_error($conn);
				echo("<br>");
            }
        } catch (Exception $e) {
            echo ("Erreur : " . $e);
        }
		
	}

	public function savePerson($remoteId, $name, $role, $current_process) {
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
		
		$remoteId = mysqli_real_escape_string($conn, $remoteId);
		$name = mysqli_real_escape_string($conn, $name);
		$role = mysqli_real_escape_string($conn, $role);
		$current_process = mysqli_real_escape_string($conn, $current_process);
		
		$sql = "INSERT INTO gouv_person(orga_remote_id, name, role, process_id) 
				VALUES ($remoteId, '$name', '$role', $current_process)";
		
		echo($sql);
		echo("<br>");
		
		try {
            if (mysqli_query($conn, $sql))
            {
				echo "New record created successfully";
				echo("<br>");
            } 
			else
            {
				echo "Error: " . $sql . "<br>" . mysqli_error($conn);
				echo("<br>");
            }
        } catch (Exception $e) {
            echo ("Erreur : " . $e);
        }
	}
}

?>