<?php

class EzPDO extends PDO {

	public function __construct($dbname) {
		require(__DIR__.'/../../conf/database.php');
		
		if (!isset($databases))
			throw new Exception("Database configuration is missing. Please check conf folder.");
		
		if (!isset($databases[$dbname]))
			throw new Exception("Unknown database: $dbname");
		
		$dbconf = $databases[$dbname];
		
		parent::__construct('mysql:host='.$dbconf['host'].';dbname='.$dbconf['database'].';charset='.$dbconf['charset'], $dbconf['user'], $dbconf['password']);
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	
	}

	public function prepare($sql, $options=NULL) {
		$statement = parent::prepare($sql);
		
		if(strpos(strtoupper($sql), 'SELECT') === 0)
			$statement->setFetchMode(PDO::FETCH_ASSOC);
		
		return $statement;
	}
	
	public function getRow($sqlQuery) {
		$stm = $this->prepare($sqlQuery);
		
		if ($stm->execute(array_slice(func_get_args(), 1)))
			return $stm->fetch();
		
		return null;
	}
		
	public function getList($sqlQuery) {
		$stm = $this->prepare($sqlQuery);
		
		if ($stm->execute(array_slice(func_get_args(), 1)))
			return $stm->fetchAll();
		
		return null;
	}
}

