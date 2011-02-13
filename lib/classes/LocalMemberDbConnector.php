<?php

class LocalMemberDbConnector {
	private $db;
	
	private $contactCategories = array(
		1 => 'phone',
		2 => 'workphone',
		3 => 'fax',
		4 => 'mobile',
		5 => 'workmobile',
		6 => 'website',
		7 => 'aim',
		9 => 'workemail',
		10 => 'icq',
		14 => 'email'
	);
	
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
	
	private function findMember($whereClause=null) {
		$query =
			'SELECT'.
			' idMembre as id,'.
//			' creation as creationDate,'.
//			' droits as privilege,'.
			' civilite as title,'.
			' nom as lastname,'.
			' prenom as firstname,'.
			" CONCAT(prenom,' ',nom) as name,".
			' idRegion as region,'.
			' devise as motto '.
			'FROM Membre';
		
		if ($whereClause)
			$query.=" WHERE $whereClause";
		
		
		$foundMember = $this->db->getRow($query, array_slice(func_get_args(), 1));
		$foundMember = $this->fillMemberData($foundMember);
		return $foundMember;
	}
	
	public function findMemberByCredential($login, $password) {
		return $this->findMember('(idWeb=? AND passWeb=?) OR (idMembre=? AND password=?)', $login, $password, $login, $password);
	}
	
	public function findMemberById($memberId) {
		return $this->findMember('id=?', $memberId);
	}
	
	private function fillMemberData($memberData) {
		if ($memberData) {
			$memberId = $memberData['id'];
			
			$foundContacts = $this->findContacts($memberId);
			$foundAddress = $this->findAddress($memberId);
			
			if (!$foundContacts)
				$foundContacts = array();
			
			if ($foundAddress)
				array_push($foundContacts, array('type'=>'address','value'=>$foundAddress));
			
			if ($foundContacts)
				$memberData['contacts'] = $foundContacts;
		}
		
		return $memberData;
	}
	
	private function findAddress($memberId) {
		$result = null;
		
		$foundAddress = $this->db->getRow(
			'SELECT'.
			'	adresse1,'.
			'	adresse2,'.
			'	cp,'.
			'	ville,'.
			'	pays '.
			'FROM'.
			'	Adresse a,'.
			'	Ville v,'.
			'	Pays p '.
			'WHERE'.
			"	confidentiel=0 AND conf_web=0 AND annuaire=1 AND fin='0000-00-00' AND".
			'	a.idVille = v.idVille AND v.idPays = p.idPays AND'.
			'	a.idMembre=?',
			$memberId
		);
		
		if ($foundAddress) {
			$result = '';
			$result.= $foundAddress['adresse1']."\n";
			
			if ($foundAddress['adresse2'])
				$result.= $foundAddress['adresse2']."\n";
			
			$result.= $foundAddress['cp'].' '.$foundAddress['ville']."\n";
			$result.= $foundAddress['pays'];
		}
		
		// trim because it seems some countries have trailing spaces...
		return trim($result);
	}
	
	private function findContacts($memberId) {
		$contacts = $this->db->getList(
			'SELECT idCategorie AS type, adresse AS value '.
			'FROM Contact '.
			"WHERE suppression='0000-00-00' AND confidentiel=0 AND conf_web=0 AND idMembre=?",
			$memberId
		);
		
		// replace contact type codes with appropriate labels.
		for ($i=0; $i<count($contacts); $i++)
			$contacts[$i]['type'] = $this->contactCategories[$contacts[$i]['type']];
		
		return $contacts;
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