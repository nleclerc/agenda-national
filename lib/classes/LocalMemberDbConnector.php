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
	
	/**
	 * Finds user data necessary for main site compatible authentication.
	 * 
	 * @param string $login
	 * @param string $password
	 */
	public function findMemberAuthData($remoteDbConnector, $login, $password) {
		$foundMember = $this->db->getRow(
			'SELECT'.
			' idMembre as id,'.
			' idRegion as region,'.
			' droits as privilege,'.
			' nom as lastname,'.
			' prenom as firstname '.
			'FROM Membre '.
			'WHERE (idWeb=? AND passWeb=?) OR (idMembre=? AND password=?)',
			$login, $password, $login, $password
		);
		
		if ($foundMember) {
			// Local data found, proceeding normaly.
			$foundMember['id'] = intval($foundMember['id']);
			$foundMember['privilege'] = intval($foundMember['privilege']);
			
			$memberId = $foundMember['id'];
			
			$primaryEmail = $this->findMemberPrimaryEmail($memberId);
			
			if (!$primaryEmail)
				$primaryEmail = '';
			
			$foundMember['email'] = $primaryEmail;
			$foundMember['subscriptionTerm'] = $this->findMemberSubscriptionTerm($memberId);
		} else {
			$fetchedMember = $this->fetchAndSaveRemoteMemberData($remoteDbConnector, $login, $password);
			
			if ($fetchedMember)
				$foundMember = $fetchedMember;
			else {
				// Do nothing, either data is fetched or exception is raised.
			}
		}
		
		return $foundMember;
	}
	
	private function findMemberPrimaryEmail($memberId) {
		$result = $this->db->getRow(
			'SELECT adresse FROM Contact WHERE (idCategorie = 14 OR idCategorie = 9) AND idMembre = ?',
			$memberId
		);
		
		if ($result)
			return $result['adresse'];
		
		return '';
	}
	
	private function findMemberSubscriptionTerm($memberId) {
		$result = $this->db->getRow(
			'SELECT fin FROM Cotisation WHERE idMembre = ? ORDER BY fin DESC',
			$memberId
		);
		
		if ($result)
			return $result['fin'];
		
		throw new Exception("Ce membre n'a pas de cotisation : $memberId");
	}
	
	private function fetchAndSaveRemoteMemberData($remoteDbConnector, $login, $password) {
		$fetchedData = $remoteDbConnector->fetchMember($login, $password);
		$this->db->execute(
			'UPDATE Membre SET idWeb = ?, passWeb = ?, password = ? WHERE idMembre = ?',
			$login, $password, $password, $fetchedData['id']
		);
		
		return $fetchedData;
	}
	
	public function findMemberShortDataBatch($memberIdArray) {
		$result = array();
		
		foreach($memberIdArray as $memberId)
			array_push($result, $this->findMemberShortData($memberId));
		
		return $result;
	}
	
	public function findMemberShortData($memberId) {
		$foundData = $this->findMemberPublicData($memberId);
		
		if ($foundData) {
			$result = array(
				'id' => $foundData['id'],
				'name' => $foundData['name']
			);
			
			for ($i=0; $i<$foundData['contacts'] && !isset($result['email']); $i++)
				if ($foundData['contacts'][$i]['type'] == 'email')
					$result['email'] = $foundData['contacts'][$i]['value'];
			
			return $result;
		}
		
		return null;
	}
	
	public function findMemberPublicData($memberId) {
		$query =
			'SELECT'.
			' idMembre as id,'.
			' civilite as title,'.
			' nom as lastname,'.
			' prenom as firstname,'.
			" CONCAT(prenom,' ',nom) as name,".
			' idRegion as region,'.
			' devise as motto '.
			'FROM Membre '.
			'WHERE idMembre=?';
		
		$foundMember = $this->db->getRow($query, $memberId);
		
		if ($foundMember) {
			$foundMember = $this->fillMemberData($foundMember);
		}
		
		return $foundMember;
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
			
			$foundInterests = $this->findInterests($memberId);
			if ($foundInterests)
				$memberData['interests'] = $foundInterests;
			
			$foundLanguages = $this->findLanguages($memberId);
			if ($foundLanguages)
				$memberData['languages'] = $foundLanguages;
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
	
	private function findInterests($memberId) {
		$result = $this->db->getList(
			'SELECT '.
			'	i.description as name, '.
			'	c.competence as skill, '.
			'	n.Niveau_interet as level '.
			'FROM '.
			'	Interet_membre j, '.
			'	Interet i, '.
			'	Competence c, '.
			'	Niveau_interet n '.
			'WHERE '.
			'	j.idInteret = i.idInteret AND '.
			'	j.competence = c.idCompetence AND '.
			'	j.niveau_interet = n.idNiveau_interet AND '.
			"	suppression = '0000-00-00' AND ".
			'	idMembre = ?',
			$memberId
		);
		
		return $result;
	}
		
	private function findLanguages($memberId) {
		$result = $this->db->getList(
			'SELECT '.
			'	l.langue as name, '.
			'	n.niveau as level '.
			'FROM '.
			'	Langue_membre j, '.
			'	Langue l, '.
			'	Niveau_langue n '.
			'WHERE '.
			'	j.idLangue = l.idLangue AND '.
			'	j.idNiveau = n.idNiveau AND '.
			"	j.suppression = '0000-00-00' AND ".
			'	j.idMembre = ?',
			$memberId
		);
		
		return $result;
	}
}