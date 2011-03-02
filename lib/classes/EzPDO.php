<?php

class EzPDO extends PDO {

	public function __construct($dbname) {
		require(__DIR__.'/../../conf/database.php');
		
		if (!isset($databases))
			throw new Exception("Database configuration is missing. Please check conf folder.");
		
		if (!isset($databases[$dbname]))
			throw new Exception("Unknown database: $dbname");
		
		$dbconf = $databases[$dbname];
		
		parent::__construct('mysql:host='.$dbconf['host'].';dbname='.$dbconf['database'], $dbconf['user'], $dbconf['password']);
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	
		$this->exec('SET CHARACTER SET '.$dbconf['charset']);
	}

	public function prepare($sql, $options=NULL) {
		$statement = parent::prepare($sql);
		
		if(preg_match('/^\s*select\s/i', $sql))
			$statement->setFetchMode(PDO::FETCH_ASSOC);
		
		return $statement;
	}
	
	public function getRow($sqlQuery, $parms=null) {
		$stm = $this->prepare($sqlQuery);
		
		$actualParms = null;
		
		if (is_array($parms))
			$actualParms = $parms;
		else
			$actualParms = array_slice(func_get_args(), 1);
		
		if ($stm->execute($actualParms))
			return $stm->fetch();
		
		return null;
	}
		
	public function getList($sqlQuery, $parms=null) {
		$stm = $this->prepare($sqlQuery);
		
		$actualParms = null;
		
		if (is_array($parms))
			$actualParms = $parms;
		else
			$actualParms = array_slice(func_get_args(), 1);
		
		if ($stm->execute($actualParms))
			return $stm->fetchAll();
		
		return null;
	}
		
	public function execute($sqlQuery, $parms=null) {
		$stm = $this->prepare($sqlQuery);
		
		$actualParms = null;
		
		if (is_array($parms))
			$actualParms = $parms;
		else
			$actualParms = array_slice(func_get_args(), 1);
		
		return $stm->execute($actualParms);
	}
	
	public function insertInto($tableName, $values) {
		// ensure $values is an array in case we receive an object.
		$values = (array)$values;
		
		$valueNames = array_keys($values);
		
		if (count($valueNames) == 0)
			throw new Exception('No values to insert into table `'.$tableName.'`.');
		
		$query = "INSERT INTO `$tableName` (`";
		$query.= implode('`, `', $valueNames);
		$query.= '`) VALUES (:';
		$query.= implode(', :', $valueNames);
		$query.= ')';
		
		$parms = array();
		
		foreach($values as $key => $value)
			$parms[":$key"] = $value;
		
		return $this->execute($query, $parms);
	}
}

