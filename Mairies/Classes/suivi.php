<?php
//use Exception;
include_once dirname(__FILE__) . '/db_connect.php';

class Suivi {
	
	private $last_page;
	private $current_page;
	
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
	
	//setLastPage
	public function setLastPage($lastPage) {
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
        
        //$user_token = mysqli_real_escape_string($mysql_db_conn, $user_token);
		$lastPage = mysqli_real_escape_string($conn, $lastPage);
		
		$sql = "UPDATE gouv_mairie_suivi SET suivi_value = $lastPage
				WHERE suivi_key = 'LAST_PAGE'";
				
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
	
	//setCurrentPage
	public function setCurrentPage($currentPage) {
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
        
        //$user_token = mysqli_real_escape_string($mysql_db_conn, $user_token);
		$currentPage = mysqli_real_escape_string($conn, $currentPage);
		
		$sql = "UPDATE gouv_mairie_suivi SET suivi_value = $currentPage
				WHERE suivi_key = 'CURRENT_PAGE'";
				
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
	
	public function getLastPage() {
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
		
		$sql = "SELECT suivi_value 
				FROM gouv_mairie_suivi 
				WHERE suivi_key = 'LAST_PAGE'";
				
		try
		{
			if ($result = mysqli_query($conn, $sql))
			{
				$this->last_page = mysqli_fetch_row($result)[0];
			}
			else
			{
				echo("error getting current process");
			}
		}
		catch (Exception $e)
		{
			return ($e->getMessage());
		}
		
	}
	
	public function getCurrentPage() {
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
		
		$sql = "SELECT suivi_value 
				FROM gouv_mairie_suivi 
				WHERE suivi_key = 'CURRENT_PAGE'";
				
		try
		{
			if ($result = mysqli_query($conn, $sql))
			{
				$this->current_page = mysqli_fetch_row($result)[0];
			}
			else
			{
				echo("error getting current process");
			}
		}
		catch (Exception $e)
		{
			return ($e->getMessage());
		}
		
	}
}	
	
?>