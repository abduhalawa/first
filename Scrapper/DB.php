<?php
class Dbconfig {
	protected $serverName;
	protected $userName;
	protected $passCode;
	protected $dbName;
	function Dbconfig() {
		$this->serverName = 'localhost';
		$this->userName = 'root';
		$this->passCode = 'root';
		$this->dbName = 'vtigercrm540copy';
	}
}
class Mysql extends Dbconfig {
	public $connectionString;
	public $dataSet;
	private $sqlQuery;
	protected $databaseName;
	protected $hostName;
	protected $userName;
	protected $passCode;
	function Mysql() {
		$this->connectionString = NULL;
		$this->sqlQuery = NULL;
		$this->dataSet = NULL;
		
		$dbPara = new Dbconfig ();
		$this->databaseName = $dbPara->dbName;
		$this->hostName = $dbPara->serverName;
		$this->userName = $dbPara->userName;
		$this->passCode = $dbPara->passCode;
		$dbPara = NULL;
	}
	function dbConnect() {
		$this->connectionString = mysqli_connect ( $this->serverName, $this->userName, $this->passCode );
		mysqli_select_db ( $this->connectionString, $this->databaseName );
		return $this->connectionString;
	}
	function dbDisconnect() {
		$this->connectionString = NULL;
		$this->sqlQuery = NULL;
		$this->dataSet = NULL;
		$this->databaseName = NULL;
		$this->hostName = NULL;
		$this->userName = NULL;
		$this->passCode = NULL;
	}

	function insertInto($values) {
		$this->sqlQuery = "    CREATE TABLE `keywords` (
      `ID` int(10) unsigned NOT NULL auto_increment,
	   `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
      `Hauptkeyword` varchar(255) NOT NULL default '',
      `URL` varchar(255) NOT NULL default '',
      PRIMARY KEY  (`ID`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
		
		 mysqli_query ( $this->connectionString, $this->sqlQuery );
		$this->sqlQuery = 'INSERT INTO keywords (Hauptkeyword,URL)VALUES ("' . $values ['Hauptkeyword'] . '" , "' . $values ['url'] . ' ")';
		
	
		mysqli_query ( $this->connectionString, $this->sqlQuery );
		return $this->sqlQuery;
		// $this -> sqlQuery = NULL;
	}

}

