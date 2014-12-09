<?php

//Basic usage:
//$var = new SqlSrvWrapper();
//$var->bind("Server","Database","User","Password");
//$err = $var->connect();
//if (!$err) { echo $var->get_error(); }
//$result = $var->query("SELECT STATEMENT");
//if ($!result) { echo $var->get_error(); }
//$var->query("NON SELECT STATEMENT",false);


class SqlSrvWrapper {
	private $sql; 
	private $server; 
	private $db;
	private $uid;
	private $pw;
	private $error;
	function __construct($c = null, $d=null, $u=null, $p=null, $s=null)
	{
		$this->sql = $s;
		$this->server = $c;
		$this->db = $d;
		$this->uid = $u;
		$this->pw = $p;
	}
	public function get_error()
	{
		return $this->error;
	}
	private function bind_errors()
	{
		//Fill $error with contents of sqlsrv_errors
		$error_array = sqlsrv_errors();
		$this->error = "";
		$indx = 1;
		foreach ($error_array as $e)
		{
			//format errors as [ [number] - error message ]
			$this->error .= "[ [$indx] - " . $e['message'] . " ] ";
			$indx += 1;
		}
		$indx -= 1;
		$this->error = "$indx total errors: $this->error";
	}
	public function bind_existing($s)
	{
		//bind an already defined ms-sql instance to our class
		$this->sql = $s;
	}
	public function bind($srv,$d,$u,$p)
	{
		//bind our connection variables
		$this->server = $srv;
		$this->db = $d;
		$this->uid = $u;
		$this->pw = $p;
	}
	public function close()
	{	
		//close the connection and reset sql var
		if ($this->sql === null) return;
		sqlsrv_close($this->sql);
		$this->sql === null;
	}
	public function query($q = null, $ret = true)
	{
		//returns a multidimensional array of data from your query
		//for queries that do not require a return array, set $ret to false;
		if ($this->sql === null)
		{
			$this->error = "No active SQL connection. Use connect() first.";
			return false;
		}
		if ($q === null)
		{
			$this->error = "No query string provided.";
			return false;
		}
		$result = sqlsrv_query($this->sql, $q);
		if (!$result)
		{
			$this->bind_errors();
			return false;
		}
		if (!$ret) return true;
		$result_array = array();
		//this converts our result array into a more user friendly array
		while ($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC))
		{
			array_push($result_array,$row);
		}
		return $result_array;
	}
	public function connect()
	{
		//connect to our database
		$er = false;
		if ($this->server === null)
		{
			$er = true;
			$this->error = "No SQL server name specified.";
		}
		if ($this->db === null)
		{
			$er = true;
			$this->error = "No database name specified.";
		}
		if ($this->uid === null)
		{
			$er = true;
			$this->error = "No UID specified.";
		}
		if ($this->pw === null)
		{
			$er = true;
			$this->error = "No password specified.";
		}
		if ($er) return false;
		//set up our connection string array and connect
		$connectionstring = array ("Database" => $this->db, "UID" => $this->uid, "PWD"=>$this->pw );
		$this->sql = sqlsrv_connect($this->server,$connectionstring);
		if (!$this->sql)
		{
			$this->bind_errors();
			return false;
		}
		return true;
	}
}
?>