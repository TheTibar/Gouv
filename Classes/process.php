<?php
include_once dirname(__FILE__) . '/db_connect.php';

class Process {
	
	private $current_process;
	
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
			return "ok";
        }
    } 
	
	public function createProcess()
	{
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
		
		$sql = "INSERT INTO gouv_process(process_id) VALUES (NULL)";
		
		try {
            if (mysqli_query($conn, $sql))
            {
              return 1;
            } else
            {
                return 0;
            }
        } catch (Exception $e) {
            echo ("Erreur : " . $e);
        }
	}
	
	public function getCurrentProcess()
	{
		$instance = \ConnectDB::getInstance();
        $conn = $instance->getConnection();
		$sql = "SELECT max(process_id) as current_process FROM gouv_process";
		
		try
		{
			if ($result = mysqli_query($conn, $sql))
			{
				$this->current_process = mysqli_fetch_row($result)[0];
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