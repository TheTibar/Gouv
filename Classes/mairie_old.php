<?php
//use Exception;
include_once dirname(__FILE__) . '/db_connect.php';

class Mairie {

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
  
    public function createRegion($link, $process_id) {
        $instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
        
        //$user_token = mysqli_real_escape_string($mysql_db_conn, $user_token);
		$link = mysqli_real_escape_string($conn, $link);
		$process_id = mysqli_real_escape_string($conn, $process_id);

        $sql = "INSERT INTO gouv_mairie_region (region_url, process_id) 
                VALUES ('$link', $process_id)";
        
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
	
	
    public function createDepartement($link, $region_url, $process_id) {
        $instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
        
        //$user_token = mysqli_real_escape_string($mysql_db_conn, $user_token);
		$link = mysqli_real_escape_string($conn, $link);
		$region_url = mysqli_real_escape_string($conn, $region_url);
		$process_id = mysqli_real_escape_string($conn, $process_id);

        $sql = "INSERT INTO gouv_mairie_departement (departement_url, region_url, process_id) 
                VALUES ('$link', '$region_url', $process_id)";
        
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
	
	
    public function createCommune($link, $departement_url, $process_id) {
        $instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
        
        //$user_token = mysqli_real_escape_string($mysql_db_conn, $user_token);
		$link = mysqli_real_escape_string($conn, $link);
		$departement_url = mysqli_real_escape_string($conn, $departement_url);
		$process_id = mysqli_real_escape_string($conn, $process_id);

        $sql = "INSERT INTO gouv_mairie_commune (commune_url, departement_url, process_id) 
                VALUES ('$link', '$departement_url', $process_id)";
        
		//echo($sql);
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

	public function getUrlByDepartement($depId) {
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
        
        //$user_token = mysqli_real_escape_string($mysql_db_conn, $user_token);
		$depId = mysqli_real_escape_string($conn, $depId);
		//$sql = "test";

		$sql = "SELECT commune_url FROM gouv_mairie_commune
				WHERE departement_url LIKE '%$depId%'";

		if ($sql_result = mysqli_query($conn, $sql))
		{
			while ($line = mysqli_fetch_assoc($sql_result))
			{
				$result[] = $line;
			}
		} else {
			echo("getting orga links error");
		}
		
		return $result;
	}
	
    public function createMairieDetail($commune_url, $maire, $email, $habitants, $process_id) {
        $instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
        
        //$user_token = mysqli_real_escape_string($mysql_db_conn, $user_token);
		$commune_url = mysqli_real_escape_string($conn, $commune_url);
		$maire = mysqli_real_escape_string($conn, $maire);
		$email = mysqli_real_escape_string($conn, $email);
		$habitants = mysqli_real_escape_string($conn, $habitants);
		$process_id = mysqli_real_escape_string($conn, $process_id);

        $sql = "INSERT INTO gouv_mairie_detail (commune_url, maire, email, habitants, process_id) 
                VALUES ('$commune_url', '$maire', '$email', $habitants, $process_id)";
        
		//echo($sql);
        try
        {
            if (mysqli_query($conn, $sql))
            {
                //echo("0");
                return true; //Cr�ation OK
            }
            else
            {
                //echo("-1");
                return false; //Cr�ation KO
            }
        }
        catch (Exception $e)
        {
            echo ("Erreur : " . $e);
        }
    }
	
	public function resetDetail($depId) {
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();

		$current_process = mysqli_real_escape_string($conn, $current_process);	

		$sql = "delete from gouv_mairie_detail where commune_url in (select commune_url from gouv_mairie_commune COM where COM.departement_url like '%$depId%')";
		
		//echo($sql);
		//echo("<br>");
		
		try {
            if (mysqli_query($conn, $sql))
            {
				echo(nl2br("Reset details successfull \n"));
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
	
	
}

?>