<?php

class RemoteMemberDbConnector {
	
	private $client = null;
	
	public function __construct() {
		require(__DIR__.'/../../conf/database.php');
		
		$connectionInfo = $databases['remoteMember'];
		
		try {
			$client = new soapClient('http://'.$connectionInfo['host'].':80/4DWSDL',array('trace'=>1,'encoding'=>$connectionInfo['charset']));
			$client->__setLocation('http://'.$connectionInfo['host'].':80/4DSOAP');
			$this->client = $client;
		} catch(Exception $e) {
			throw new Exception('Echec de la connexion 4D : '.$e->getMessage());
		}
	}
	
	public function fetchMember($login, $password) {
		$rawResult = $this->client->ws_xml_acces($login, $password);
		
		if(is_soap_fault($rawResult))
			throw new Exception('Echec de la connexion au serveur 4D : '.$rawResult->faultcode.' :: '.$rawResult->faultstring);
		
		$doc = new SimpleXMLElement(utf8_encode($rawResult));
		
		// the message test might be pointless
		if ($doc->Erreur != 1 || $doc->Message == 'N/A')
			throw new Exception('Erreur de Login ou/et Mot de passe ou cotisation arrivée à échéance.');
		
		$foundMember = $doc->membre;
		
		if ($foundMember->ref < 1)
			throw new Exception('Problème de numéro de membre, veuillez contactez l\'admistrateur.');
		
		$memberData = $this->parseMemberName($foundMember->nom);
		
		$memberData['id'] = (int)$foundMember->ref;
		$memberData['region'] = (string)$foundMember->region;
		$memberData['email'] = (string)$foundMember->courriel;
		$memberData['subscriptionTerm'] = preg_replace('%(\d{2})/(\d{2})/(\d{2})%', '$1-$2-20$3', $foundMember->echeance);
		$memberData['privilege'] = (int)$foundMember->droits;
		
		return $memberData;
	}

	/**
	 * Looks for the first all caps word to split first and last name.
	 * 
	 * @param string $fullname
	 */
	private function parseMemberName($fullname) {
		$firstnameTokens = array();
		
		$tokens = explode(' ', $fullname);
		
		while (count($tokens) && $tokens[0] != mb_strtoupper($tokens[0])) {
			array_push($firstnameTokens, array_shift($tokens));
		}
		
		return array(
			'firstname' => implode(' ', $firstnameTokens),
			'lastname' => implode(' ', $tokens)
		);
	}
}