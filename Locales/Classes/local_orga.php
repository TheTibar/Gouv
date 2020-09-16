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
	
	public function getJSONMap($current_process) {
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
		
		
		$result = [];
		
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
                AND GLO.process_id = $current_process";


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

		if (file_put_contents("graph/data/data_map_" . $current_process . ".json", $result))
			echo(nl2br("\n JSON file created successfully... \n"));
		else 
			echo(nl2br("\n Oops! Error creating json file... \n"));

		
	}
}

?>