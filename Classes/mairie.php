<?php
//use Exception;
include_once dirname(__FILE__) . '/db_connect.php';

class Mairie {
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
 	
    public function createMairieDetail($insee_code, $currentURL, $name, $mayor, $email, $hab, $long, $lat, $current_process) {
        $instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
        
        //$user_token = mysqli_real_escape_string($mysql_db_conn, $user_token);		$insee_code = mysqli_real_escape_string($conn, $insee_code);
		$currentURL = mysqli_real_escape_string($conn, $currentURL);
		$mayor = mysqli_real_escape_string($conn, $mayor);
		$name = mysqli_real_escape_string($conn, $name);
		$email = mysqli_real_escape_string($conn, $email);
		$hab = mysqli_real_escape_string($conn, $hab);
		$long = mysqli_real_escape_string($conn, $long);		$lat = mysqli_real_escape_string($conn, $lat);		$current_process = mysqli_real_escape_string($conn, $current_process);
		/*On commence par supprimer si le code insee existe déjà pour le process actuel */		$sql = "DELETE FROM gouv_mairie_detail 				WHERE insee_code = '$insee_code'
                    AND process_id = $current_process ";				//echo($sql);		//echo("<br>");				try {            if (mysqli_query($conn, $sql))            {
				$sql = "INSERT INTO gouv_mairie_detail (insee_code, city_name, commune_url, maire, email, habitants, longitude, latitude, process_id) 
						VALUES ('$insee_code', '$name', '$currentURL', '$mayor', '$email', $hab, $long, $lat, $current_process)";
				
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
				}			} 			else            {				echo(nl2br("Erreur de suppression : " . $sql . "\n" . mysqli_error($conn) . "\n"));				return false;            }        } catch (Exception $e) {            echo ("Erreur : " . $e);			return 1;        }
    }		public function createInseeData($insee_code, $process_id, $url, $region, $departement) {		$instance = \ConnectDB::getInstance();        $conn = $instance->getConnection();				$insee_code = mysqli_real_escape_string($conn, $insee_code);		$process_id = mysqli_real_escape_string($conn, $process_id);		$url = mysqli_real_escape_string($conn, $url);		$region = mysqli_real_escape_string($conn, $region);		$departement = mysqli_real_escape_string($conn, $departement);		/*On commence par supprimer si le code insee existe déjà pour ce processus*/		$sql = "DELETE FROM gouv_mairie_insee 				WHERE insee_code = '$insee_code'
                AND process_id = $process_id";				//echo($sql);		//echo("<br>");				try {            if (mysqli_query($conn, $sql))            {				$sql = "INSERT INTO gouv_mairie_insee (insee_code, process_id, url, region, departement) 						VALUES ('$insee_code', $process_id, '$url', '$region', '$departement')";								//echo($sql);				try				{					if (mysqli_query($conn, $sql))					{						//echo("0");						return true; //Cr�ation OK					}					else					{						//echo("-1");						return false; //Cr�ation KO					}				}				catch (Exception $e)				{					echo ("Erreur : " . $e);				}            } 			else            {				echo(nl2br("Erreur de suppression : " . $sql . "\n" . mysqli_error($conn) . "\n"));				return false;            }        } catch (Exception $e) {            echo ("Erreur : " . $e);			return 1;        }	}		public function getLimitedUrl($start, $nbRow, $current_process) {		$instance = \ConnectDB::getInstance();        $conn = $instance->getConnection();		
        $current_process = mysqli_real_escape_string($conn, $current_process);
        		$result = [];				$sql = "SELECT 
					INS.url as url, 
					INS.insee_code as insee_code
				FROM gouv_mairie_insee INS
				LEFT OUTER JOIN gouv_mairie_detail DET ON DET.insee_code = INS.insee_code 
                    AND DET.process_id = INS.process_id
				WHERE DET.email is null
                    AND INS.process_id = $current_process 				ORDER BY insee_id				LIMIT $start, $nbRow";						//echo($sql);		/**/		if ($sql_result = mysqli_query($conn, $sql))		{			while ($line = mysqli_fetch_assoc($sql_result))			{				$result[] = $line;			}		} else {			echo("getting links json error");		}		return $result;			}

	public function getAllUrl($current_process) {
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
        
        $current_process = mysqli_real_escape_string($conn, $current_process);
		
		$result = [];
		
		$sql = "SELECT 
					INS.url as url, 
					INS.insee_code as insee_code
				FROM gouv_mairie_insee INS
				LEFT OUTER JOIN gouv_mairie_detail DET ON DET.insee_code = INS.insee_code 
                    AND DET.process_id = INS.process_id
				WHERE DET.email is null
                    AND INS.process_id = $current_process"; 
				
		//echo($sql);
		/**/
		if ($sql_result = mysqli_query($conn, $sql))
		{
			while ($line = mysqli_fetch_assoc($sql_result))
			{
				$result[] = $line;
			}
		} else {
			echo("getting url error");
		}
		return $result;
		
	}

	public function getAllUrlWithoutMayor($current_process) {
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
        
        $current_process = mysqli_real_escape_string($conn, $current_process);
		
		$result = [];
		
		$sql = "SELECT 
					INS.url as url, 
					INS.insee_code as insee_code
				FROM gouv_mairie_insee INS
				INNER JOIN gouv_mairie_detail DET ON DET.insee_code = INS.insee_code AND DET.process_id = INS.process_id
				WHERE DET.maire is null or DET.maire = ''
                    AND INS.process_id = $current_process";
				
		//echo($sql);
		/**/
		if ($sql_result = mysqli_query($conn, $sql))
		{
			while ($line = mysqli_fetch_assoc($sql_result))
			{
				$result[] = $line;
			}
		} else {
			echo(nl2br("Error getting url without mayor \n"));
		}

		return $result;
		
	}
	
	public function getLimitedUrlWithoutMayor($start, $nbRow) {
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
		
		$result = [];
		
		$sql = "SELECT 
					INS.url as url, 
					INS.insee_code as insee_code
				FROM gouv_mairie_insee INS
				INNER JOIN gouv_mairie_detail DET ON DET.insee_code = INS.insee_code AND DET.process_id = INS.process_id
				WHERE DET.maire is null or DET.maire = ''
                    AND INS.process_id = $current_process
				ORDER BY INS.insee_id
				LIMIT $start, $nbRow";
				
		//echo($sql);
		/**/
		if ($sql_result = mysqli_query($conn, $sql))
		{
			while ($line = mysqli_fetch_assoc($sql_result))
			{
				$result[] = $line;
			}
		} else {
			echo(nl2br("Error getting url without mayor \n"));
		}

		return $result;
		
	}	
	public function updateMairieDetail($insee_code, $maire, $hab) {
        $instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
        
        //$user_token = mysqli_real_escape_string($mysql_db_conn, $user_token);
		$insee_code = mysqli_real_escape_string($conn, $insee_code);
		$maire = mysqli_real_escape_string($conn, $maire);

		$sql = "UPDATE gouv_mairie_detail
				SET maire = '$maire', habitants = $hab
				WHERE insee_code = '$insee_code'";
		
		echo($sql);
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
}

?>