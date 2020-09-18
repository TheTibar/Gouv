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
	
	public function get3dJSON($current_process) { //pour la fonction large graph utilisée actuellement
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
		
		
		$result = [];
		$orga = [];
		$links = [];
		
		$sql = "SET group_concat_max_len = 2048"; //ajout person
		mysqli_query($conn, $sql); //ajout person
		
		
		//$sql = "SELECT distinct label as user, link as description, remote_id as id from gouv_orga where process_id = $current_process"; //commentaire person
		/**/
		$sql = "SELECT distinct label as user, link as description, coalesce(drv.person, '') as person, remote_id as id 
				FROM gouv_orga GOR 
				LEFT OUTER JOIN 
				( 
					SELECT 
						orga_remote_id, 
						process_id, 
						GROUP_CONCAT(CONCAT(role, ' : ', name) SEPARATOR ';') as person 
					FROM gouv_person GP 
					GROUP BY orga_remote_id 
				) drv on drv.orga_remote_id = GOR.remote_id and drv.process_id = GOR.process_id 
				WHERE GOR.process_id = $current_process";
		
		//echo($sql);		

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
			echo("error getting orga json");
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
		
		//var_dump($nodes_json);
		
		if (file_put_contents("graph/data/data_node_links_person" . $current_process . ".json", $nodes_json))
			echo(nl2br("\n JSON file created successfully... \n"));
		else 
			echo(nl2br("\n Oops! Error creating json file... \n"));
		
	}

	public function getFlatArray($current_process) { //pour les 3 graph de d3graph
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
				
		//echo($sql);
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
			echo(nl2br("\n JSON file created successfully... \n"));
		else 
			echo(nl2br("\n Oops! Error creating json file... \n"));
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
		
		//echo($sql);
		//echo("<br>");
		
		try {
            if (mysqli_query($conn, $sql))
            {
				echo(nl2br("Reset person successfull \n"));
				return 0;
            } 
			else
            {
				echo(nl2br("Error: " . $sql . "\n" . mysqli_error($conn) . "\n"));
				return 1;
            }
        } catch (Exception $e) {
            echo ("Erreur : " . $e);
			return 1;
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

		
		try {
            if (mysqli_query($conn, $sql))
            {
				echo(nl2br("SAVE OK \n"));
            } 
			else
            {
				echo(nl2br("SAVE KO: " . $sql . "\n" . mysqli_error($conn) . "\n"));
            }
        } catch (Exception $e) {
            echo ("Erreur : " . $e);
        }
	}
	
	public function createFirstLevel($current_process) {
	    $instance = \ConnectDB::getInstance();
	    $conn = $instance->getConnection();
	    
	    $current_process = mysqli_real_escape_string($conn, $current_process);
	    
	    $sql = "UPDATE gouv_orga AS go1 
                INNER JOIN (
                	SELECT go2.remote_id as n_father 
                    FROM gouv_orga go2 
                    WHERE go2.label = 'Premier ministre'
                    ) AS drv1
                SET go1.father_id = drv1.n_father
                WHERE 1 = 1
                	AND go1.label <> 'Premier ministre'
                    AND go1.father_id = 0
                	AND go1.process_id = $current_process";
	    
	    
	    try {
	        if (mysqli_query($conn, $sql))
	        {
	            echo(nl2br("SAVE OK \n"));
	        }
	        else
	        {
	            echo(nl2br("SAVE KO: " . $sql . "\n" . mysqli_error($conn) . "\n"));
	        }
	    } catch (Exception $e) {
	        echo ("Erreur : " . $e);
	    }
	    
	}
	
	public function removeInfiniteLoops($current_process) {
	    $instance = \ConnectDB::getInstance();
	    $conn = $instance->getConnection();
	    
	    $current_process = mysqli_real_escape_string($conn, $current_process);
	    
	    $sql = "DELETE 
                FROM gouv_orga
                WHERE remote_id = father_id
                    AND process_id = $current_process";
	    
	    try {
	        if (mysqli_query($conn, $sql))
	        {
	            echo(nl2br("DELETE OK \n"));
	        }
	        else
	        {
	            echo(nl2br("DELETE KO: " . $sql . "\n" . mysqli_error($conn) . "\n"));
	        }
	    } catch (Exception $e) {
	        echo ("Erreur : " . $e);
	    }
	}
	
}

?>