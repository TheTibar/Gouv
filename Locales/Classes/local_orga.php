<?php
//use Exception;
include_once dirname(__FILE__) . '/db_connect.php';

class Local_Orga {

    private $id;
    private $theme;
    private $remote_id;
    private $level;
    private $link;
    private $label;
	private $father_id;
	private $person;
	private $childUrls = [];
    
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
  
    public function createLocalOrga($theme, $id, $remote_id, $level, $link, $label, $father_id, $currentProcess) {
        $instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
        
        //$user_token = mysqli_real_escape_string($mysql_db_conn, $user_token);
        $theme = mysqli_real_escape_string($conn, $theme);
        $id = mysqli_real_escape_string($conn, $id);
        $remote_id = mysqli_real_escape_string($conn, $remote_id);
		$level = mysqli_real_escape_string($conn, $level);
		$link = mysqli_real_escape_string($conn, $link);
		$label = mysqli_real_escape_string($conn, $label);
		$father_id = mysqli_real_escape_string($conn, $father_id);
		$currentProcess  = mysqli_real_escape_string($conn, $currentProcess);

        $sql = "INSERT INTO gouv_local_orga (theme, id, remote_id, level, link, label, father_id, process_id) 
                VALUES ('$theme', $id, $remote_id, $level, '$link', '$label', $father_id, $currentProcess)";
        
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
	
    public function getChildUrls($currentProcess) {
	    $instance = \ConnectDB::getInstance();
	    $conn = $instance->getConnection();
	    
	    $currentProcess  = mysqli_real_escape_string($conn, $currentProcess);
	    
	    $sql = "SELECT GLO.gouv_local_orga_id as local_orga_id, GLO.link as link
                FROM gouv_local_orga GLO
                WHERE GLO.process_id = $currentProcess
                AND GLO.LEVEL = 2
                AND NOT EXISTS (
					SELECT 1 
                    FROM gouv_local_orga_detail GLOD 
                    WHERE GLO.process_id = GLOD.process_id 
						AND GLO.gouv_local_orga_id = GLOD.gouv_local_orga_id)";
	    
	    //echo($sql);
	    
	    if ($sql_result = mysqli_query($conn, $sql))
	    {
	        while ($line = mysqli_fetch_assoc($sql_result))
	        {
	            $result[] = $line;
	        }
	        $this->childUrls = $result;
	        return true;
	    } else {
	        echo("getChildUrls error");
	        return false;
	    }
	}
	
	public function createCentralPoint($current_L_process) {
	    
	    $current_L_process  = mysqli_real_escape_string($conn, $current_L_process);
	    $sql = "INSERT INTO gouv_local_orga
                (theme, id, remote_id, link, label, father_id, process_id)
                VALUES
                ('Point central', 99999, 99999, -1, 'Racine', 'Point central', 0, $current_L_process)";
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
	
	public function updateFirstLevel($current_L_process) {
	    
	    $current_L_process  = mysqli_real_escape_string($conn, $current_L_process);
	    $sql = "UPDATE gouv_local_orga \n
                SET father_id = 99999 \n
                WHERE father_id = 0 
                    AND level = 0 
                    AND process_id = $current_L_process";
	    
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
	
	public function createLocalOrgaDetail($currentId, $region, $departement, $long, $lat, $email, $website, $currentProcess) {
	    //echo(nl2br("Local orga id : " . $currentId . ", région : " . $region . ", département : " . $departement . ", long : " . $long . ", lat : " . $lat . ", email : " . $email . ", website : " . $website . "\n"));
	    
	    $instance = \ConnectDB::getInstance();
	    $conn = $instance->getConnection();
	    
	    $currentId  = mysqli_real_escape_string($conn, $currentId);
	    $region  = mysqli_real_escape_string($conn, $region);
	    $departement  = mysqli_real_escape_string($conn, $departement);
	    $long  = mysqli_real_escape_string($conn, $long);
	    $lat  = mysqli_real_escape_string($conn, $lat);
	    $email  = mysqli_real_escape_string($conn, $email);
	    $website  = mysqli_real_escape_string($conn, $website);
	    $currentProcess  = mysqli_real_escape_string($conn, $currentProcess);
	    
	    $sql = "INSERT INTO gouv_local_orga_detail (gouv_local_orga_id, region, departement, longit, latit, email, website, process_id)
                VALUES ($currentId, '$region', '$departement', $long, $lat, '$email', '$website', $currentProcess)";
	    
	    //echo(nl2br($sql . "\n"));
	    
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
	
	public function get3dJSON($current_L_process, $current_M_process) {
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
		
		
		$result = [];
		$orga = [];
		$links = [];
		/*Récupère toutes les organisation locales, y-compris les mairies*/
		$sql = "SELECT distinct label as user, link as description, gouv_local_orga_id as id 
				FROM gouv_local_orga GOL
				WHERE GOL.process_id = $current_L_process
                UNION ALL
                SELECT distinct city_name as user, commune_url as description, concat(process_id, '_', detail_id) as id 
                FROM gouv_mairie_detail
                where process_id = $current_M_process";

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
		/*Crée tous les liens entre le père et les fils, y compris pour les mairies*/
		$sql = "SELECT GOLF.gouv_local_orga_id as source, GOLC.gouv_local_orga_id as target
				FROM gouv_local_orga GOLF
				INNER JOIN gouv_local_orga GOLC on GOLC.remote_id = GOLF.father_id 
					AND GOLC.level = (GOLF.level - 1)
					AND GOLC.process_id = GOLF.process_id
				WHERE GOLF.process_id = $current_L_process
				UNION ALL
                SELECT DISTINCT GLO.gouv_local_orga_id as source, concat(GMD.process_id, '_', GMD.detail_id) as target 
                FROM gouv_local_orga GLO
                INNER JOIN gouv_mairie_detail GMD on 1=1
                WHERE GLO.process_id = $current_L_process 
                    AND GMD.process_id = $current_M_process 
                    AND GLO.level = 1
                    AND GLO.label = 'Mairie'";
		
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
		
		if (file_put_contents("graph/data/local_data_node_links_L_" . $current_L_process . "_M_" . $current_M_process . ".json", $result))
			echo(nl2br("\n JSON file created successfully... \n"));
		else 
			echo(nl2br("\n Error creating json file... \n"));
		
	}
	
	public function getJSONMap($current_L_process, $current_M_process) {
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
		
		
		$result = [];
		/*Récupère toutes les données Locales (et les mairies dans les tables Mairie*/
		$sql = "SELECT 
                    GLO.theme as theme,
    				GLO.label as description,
                    'Point' AS type,
                    GLOD.longit as x,
                    GLOD.latit as y
                FROM gouv_local_orga GLO
                INNER JOIN gouv_local_orga_detail GLOD on GLOD.gouv_local_orga_id = GLO.gouv_local_orga_id
                	AND GLOD.process_id = GLO.process_id
                WHERE 1 = 1
                	AND GLOD.latit <> 0
                    AND GLOD.longit <> 0
                    AND GLO.level = 2
                    AND GLO.process_id = $current_L_process
                UNION ALL
                SELECT 
                    'Administration locale' as theme,
                    city_name as description,
                    'Point' AS type,
                    GMD.longitude as x,
                    GMD.latitude as y
                FROM gouv_mairie_detail GMD
                WHERE 1 = 1
                    AND GMD.longitude <> 0
                    AND GMD.latitude <> 0
                    AND GMD.process_id = $current_M_process
";
		


		if ($sql_result = mysqli_query($conn, $sql))
		{
			while ($line = mysqli_fetch_assoc($sql_result))
			{
				$result[] = $line;
			}
		} else {
			echo("getting map json error");
		}
		
		$result = json_encode($result, JSON_UNESCAPED_UNICODE);

		if (file_put_contents("graph/data/local_data_map_L_" . $current_L_process . "_M_" . $current_M_process . ".json", $result))
			echo(nl2br("\n JSON file created successfully... \n"));
		else 
			echo(nl2br("\n Error creating json file... \n"));

		
	}
}

?>