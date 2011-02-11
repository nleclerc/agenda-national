<?php

class LocalMemberDbConnector {
	private $db;
	
	public function __construct() {
		$this->db = new EzPDO('member');
	}
	
	public function hasRunningSubscription($idMembre) {
		$idMembre = intval($idMembre);
		$currentDate = date('Y-m-d');
		$foundSubscription = $this->db->getRow(
			'SELECT * FROM Cotisation WHERE idMembre = ? AND debut <= ? AND fin >= ?  ORDER BY fin DESC',
			$idMembre, $currentDate, $currentDate
		);
		
		return $foundSubscription != false;
	}
	
	public function findMember($login, $password) {
		$foundMember = $this->db->getRow(
			'SELECT * FROM Membre WHERE ((idWeb = ? AND passWeb = ?) OR (idMembre = ? AND password = ?))',
			$login, $password,
			intval($login), $password
		);
		
		return $foundMember;
	}

	public function findOrFetchMember($remoteDbConnector, $login, $password){
		$foundMember = $this->findMember($login, $password);
		
		if (!$foundMember){
			$fetchedData = $remoteDbConnector->fetchMember($login, $password);
			$this->db->execute(
				'UPDATE Membre SET idWeb = ?, passWeb = ?, password = ? WHERE idMembre = ?',
				$login, $password, $password, $fetchedData['id']
			);
			
			$foundMember = findMember($login, $password);
		}
		
		return $foundMember;
	}
}