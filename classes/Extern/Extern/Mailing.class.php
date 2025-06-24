<?php
	/*
		Versie:
			1.0 - start van de klasse - Wim Calders
			1.1 - Connectie met SOAP wordt binnen de klasse opgestart - Wim Calders
			1.2 - Mail sturen voor een toiletkaartje - Wim Calders
	*/
	
	/*
		- Bijlagen zijn nog niet geprogrammeerd voor Smartschool.
		- E-mails zenden is nog niet toegevoegd.
	*/
	
	// Namespace
	namespace Extern;
	
	// Import other class
	use Database;
	use SoapClient;
	
	// Class
	Class Mailing
	{
		// Properties
		private array $ontvangers;
		private string $afzender;
		private string $onderwerp;
		private string $bericht;	// HTML
		private $bijlage;
		private $clientSOAP;
		private bool $setMail = false;
		private bool $setSmartschool = false;
		
		// Methods
		protected function adressenControleren(array $ontvangers, array $afzenders)
		{
			// Variabelen
			$geldigVoorSmartschool = false;
			$geldigVoorMail = false;
			
			// Overlopen
			foreach ($ontvangers as $item)
			{
				// E-mailades?
				if (filter_var($item, FILTER_VALIDATE_EMAIL))
				{
					// Geldig e-mailadres
					$geldigVoorMail = true;
				}
				
				// Gebruikersnaam van Smartschool?
				if ((strlen($item) == 3) or (strlen($item) == 5) or (strlen($item) == 6))
				{
					// De lengte klopt al
					$geldigVoorSmartschool = true;
				}
			}
			foreach ($afzenders as $item)
			{
				// E-mailades?
				if (filter_var($item, FILTER_VALIDATE_EMAIL))
				{
					// Geldig e-mailadres
					$geldigVoorMail = true;
				}
				
				// Gebruikersnaam van Smartschool?
				// if ((strlen($item) == 3) or (strlen($item) == 5) or (strlen($item) == 6))
				if (!filter_var($item, FILTER_VALIDATE_EMAIL))
				{
					// De lengte klopt al
					$geldigVoorSmartschool = true;
				}
			}
			
			// Conclusie
			switch (true)
			{
				// Adressen zijn niet goed
				case (($geldigVoorMail == false) && ($geldigVoorSmartschool == false)):
					return "Ongeldige afzenders";
				// Mailfunctie opzetten
				case (($geldigVoorMail == true) && ($geldigVoorSmartschool == false)):
					$this->setMail = true;
					$this->setSmartschool = false;
					return "Mail";
				// Smartschoolfunctie opzetten
				case (($geldigVoorMail == false) && ($geldigVoorSmartschool == true)):
					$this->setMail = false;
					$this->setSmartschool = true;
					return "Smartschool";
				// Combinatie is niet bruikbaar
				case (($geldigVoorMail == true) && ($geldigVoorSmartschool == true)):
					return "Ongeldige combinatie";
			}
			return "Ongeldige status";
		}
		
		public function voorbereiding(array $ontvangers, string $afzender, string $onderwerp, string $bericht, $bijlage = '')
		{
			// Adressen opslaan
			$arrAfzender = explode(",",$afzender);
			if(($this->adressenControleren($ontvangers,$arrAfzender) == "Mail") or ($this->adressenControleren($ontvangers,$arrAfzender) == "Smartschool"))
			{
				$this->ontvangers = $ontvangers;
				$this->afzender = $afzender;
			}
			
			// De rest opslaan
			$this->onderwerp = $onderwerp;
			$this->bericht = $bericht;
		}
		
		private function connectSOAP()
		{
			// Connecteren met Smartschool
			$this->clientSOAP = new SoapClient('https://'.$_SESSION['platform'].'/Webservices/V3?wsdl', ['cache_wsdl' => WSDL_CACHE_NONE]);
		}
		
		public function zendSMSbericht($webservicesPwd)
		{
			// Controle
			if($this->setSmartschool and !($this->setMail))
			{
				// Connecteren met Smartschool
				$this->connectSOAP();
				
				// Ontvangers overlopen
				foreach($this->ontvangers as $strOntvangers)
				{
					// Is het een groep?
					if(!empty($_SESSION['structuur']) && array_key_exists($strOntvangers,$_SESSION['structuur']))
					{
						// Leden ophalen
						$leden = $_SESSION['structuur'][$strOntvangers]->getMembers();
						print_r ($leden);
						
						// Individuele mails sturen
						foreach($leden as $persoon)
						{
							// Verzenden
							$wsresult = $this->clientSOAP->sendMsg($webservicesPwd,$persoon,$this->onderwerp,$this->bericht,$this->afzender);
						}
					}
					else
					{
						// Verzenden
						$wsresult = $this->clientSOAP->sendMsg($webservicesPwd,$strOntvangers,$this->onderwerp,$this->bericht,$this->afzender);
					}
				}
				
				// Return
				return "Ok<br>\n";
			}
			else
			{
				// Return
				return "Dit object is niet klaar om een Smartschoolbericht te sturen. Gelieve de geadresseerden nog eens na te kijken.<br>\n";
			}
			
			// Return
			return "Ongeldige status<br>\n";
		}
		
		// Special methods
		public function zendEHBOziekenhuis(string $bezoekID, string $campus, string $accesscode, Database $conn)
		{
			// Variabelen
			$return = 'Niets verstuurd<br>\n';
			
			// Gegevens ophalen
			$resultBezoek = $conn->select('tblEHBObezoek',null,"id='{$bezoekID}'");
			$rowBezoek = $resultBezoek[0];
			
			// Enkel de eerste keer een mail sturen
			If($rowBezoek['ziekenhuisMail']=='0')
			{
				// Reden ophalen
				$sqlReden = "SELECT r.naam FROM EHBOredenBezoek rb LEFT JOIN tblEHBOreden r ON r.id=rb.redenID WHERE rb.bezoekID={$bezoekID}";
				$resultReden = $conn->query($sqlReden);
				$aantalRijen = $conn->affected_rows;
				
				$reden = '';
				$i = 0;
				
				foreach ($resultReden as $rowReden)
				{
					// Teller
					$i++;
					
					$reden .= $rowReden['naam'];
					If( ($aantalRijen > 1) && ($i < $aantalRijen) )
					{
						$reden .= ', ';
					}
				}
				
				// Ziekenhuisvervooer voorbereiden
				If($rowBezoek['ziekenhuisVervoer']=="ziekenhuis-andere")
				{
					$vervoer = $rowBezoek['ziekenhuisAndere'];
				}Else{
					$vervoer = $rowBezoek['ziekenhuisVervoer'];
				}
				
				// Ontvangers selecteren
				If(empty($rowBezoek['klas']))
				{
					$strToDirectie = DIRECTIE[$campus]['personeel'];
				}Else{
					$strToDirectie = DIRECTIE[$campus][$rowBezoek['klas'][1]];
				}
				$strToDirectie .= "," . DIRECTIE[$campus]['preventie'];
				$toDirectie = explode(',', $strToDirectie);
				
				// Het onderwerp
				$msgSubject = "Persoon naar ziekenhuis";
				
				// Het bericht
				$msgBody = "Beste<br><br>\n";
				$msgBody .= "<b><u>Opname ziekenhuis:</u></b><br>\n";
				$msgBody .= "<b>Naam:</b> {$rowBezoek['naam']}<br>\n";
				$msgBody .= "<b>Klas:</b> {$rowBezoek['klas']}<br>\n";
				$msgBody .= "<b>Redenen:</b> {$reden}<br><br>\n";
				$msgBody .= "<b>Binnengekomen op:</b> {$rowBezoek['datum']} {$rowBezoek['van']}<br>\n";
				$msgBody .= "<b>Locatie van het voorval:</b> {$rowBezoek['plaats']}<br><br>\n";
				$msgBody .= "<b>Naar ziekenhuis op:</b> {$rowBezoek['datum']} {$rowBezoek['tot']}<br>\n";
				$msgBody .= "<b>Gebracht door:</b> {$vervoer}<br><br>\n";
				$msgBody .= "<b>Verzorger:</b> {$rowBezoek['verzorger']}<br>";
				$msgBody .= "<b>Bijkomende info:</b> {$rowBezoek['opmerking']}<br><br>";
				$msgBody .= "M.v.g.<br><br><br><br>";
				$msgBody .= "Het EHBO-portaal";
				
				// Verzenden
				$this->voorbereiding($toDirectie, 'no-reply', $msgSubject, $msgBody);
				$return = $this->zendSMSbericht($accesscode);
				
				// Tabel updaten
				$sql = "UPDATE tblEHBObezoek SET ziekenhuisMail=1 WHERE id='{$bezoekID}'";
				$result = $conn->query($sql);
			}
			
			// Iets tonen
			return $return;
		}
		
		public function zendEHBOtoilet(string $bezoekID, string $campus, string $accesscode, Database $conn)
		{
			// Variabelen
			$return = 'Niets verstuurd<br>\n';
			
			// Gegevens ophalen
			$resultBezoek = $conn->select('tblEHBObezoek',null,"id='{$bezoekID}'");
			$rowBezoek = $resultBezoek[0];
			
			// Enkel de eerste keer een mail sturen
			If($rowBezoek['toiletMail']=='0')
			{
				// Ontvangers selecteren
				$strOntvangers = EHBOMAIL[$campus]['TOILET'];
				$arrOntvangers = explode(',', $strOntvangers);
				
				// Het onderwerp
				$msgSubject = "Persoon naar TIME-OUT";
				
				// Het bericht
				$msgBody = "Beste<br><br>\n";
				$msgBody .= "<b><u>TIME-OUT:</u></b><br>\n";
				$msgBody .= "<b>Naam:</b> {$rowBezoek['naam']}<br>\n";
				$msgBody .= "<b>Klas:</b> {$rowBezoek['klas']}<br><br>\n";
				$msgBody .= "<b>Binnengekomen op:</b> {$rowBezoek['datum']} {$rowBezoek['van']}<br>\n";
				$msgBody .= "<b>Locatie van het voorval:</b> {$rowBezoek['plaats']}<br><br>\n";
				$msgBody .= "<b>Invuller:</b> {$rowBezoek['verzorger']}<br>";
				$msgBody .= "<b>Bijkomende info:</b> {$rowBezoek['opmerking']}<br><br>";
				$msgBody .= "M.v.g.<br><br><br><br>";
				$msgBody .= "Het EHBO-portaal";
				
				// Verzenden
				$this->voorbereiding($arrOntvangers, 'no-reply', $msgSubject, $msgBody);
				$return = $this->zendSMSbericht($accesscode);
				
				// Tabel updaten
				$sql = "UPDATE tblEHBObezoek SET toiletMail=1 WHERE id='{$bezoekID}'";
				$result = $conn->query($sql);
			}
			
			// Iets tonen
			return $return;
		}
		
		public function zendEHBOtimeout(string $bezoekID, string $campus, string $accesscode, Database $conn)
		{
			// Variabelen
			$return = 'Niets verstuurd<br>\n';
			
			// Gegevens ophalen
			$resultBezoek = $conn->select('tblEHBObezoek',null,"id='{$bezoekID}'");
			$rowBezoek = $resultBezoek[0];
			
			// Enkel de eerste keer een mail sturen
			If($rowBezoek['timeoutMail']=='0')
			{
				// Ontvangers selecteren
				$strOntvangers = EHBOMAIL[$campus]['TIMEOUT'];
				$arrOntvangers = explode(',', $strOntvangers);
				
				// Het onderwerp
				$msgSubject = "Persoon naar TIME-OUT";
				
				// Het bericht
				$msgBody = "Beste<br><br>\n";
				$msgBody .= "<b><u>TIME-OUT:</u></b><br>\n";
				$msgBody .= "<b>Naam:</b> {$rowBezoek['naam']}<br>\n";
				$msgBody .= "<b>Klas:</b> {$rowBezoek['klas']}<br><br>\n";
				$msgBody .= "<b>Binnengekomen op:</b> {$rowBezoek['datum']} {$rowBezoek['van']}<br>\n";
				$msgBody .= "<b>Locatie van het voorval:</b> {$rowBezoek['plaats']}<br><br>\n";
				$msgBody .= "<b>Invuller:</b> {$rowBezoek['verzorger']}<br>";
				$msgBody .= "<b>Bijkomende info:</b> {$rowBezoek['opmerking']}<br><br>";
				$msgBody .= "M.v.g.<br><br><br><br>";
				$msgBody .= "Het EHBO-portaal";
				
				// Verzenden
				$this->voorbereiding($arrOntvangers, 'no-reply', $msgSubject, $msgBody);
				$return = $this->zendSMSbericht($accesscode);
				
				// Tabel updaten
				$sql = "UPDATE tblEHBObezoek SET timeoutMail=1 WHERE id='{$bezoekID}'";
				$result = $conn->query($sql);
			}
			
			// Iets tonen
			return $return;
		}
		
		public function zendEHBOokan(string $bezoekID, string $campus, string $accesscode, Database $conn)
		{
			// Variabelen
			$return = 'Niets verstuurd<br>\n';
			
			// Gegevens ophalen
			$resultBezoek = $conn->select('tblEHBObezoek',null,"id='{$bezoekID}'");
			$rowBezoek = $resultBezoek[0];
			
			// Enkel de eerste keer een mail sturen
			If($rowBezoek['okanMail']=='0')
			{
				// Ontvangers selecteren
				$strOntvangers = EHBOMAIL[$campus]['OKAN'];
				$arrOntvangers = explode(',', $strOntvangers);
				
				// Het onderwerp
				$msgSubject = "Persoon naar ziekenboeg";
				
				// Het bericht
				$msgBody = "Beste<br><br>\n";
				$msgBody .= "<b>Naam:</b> {$rowBezoek['naam']}<br>\n";
				$msgBody .= "<b>Klas:</b> {$rowBezoek['klas']}<br><br>\n";
				$msgBody .= "<b>Redenen:</b> {$reden}<br><br>\n";
				$msgBody .= "<b>Binnengekomen op:</b> {$rowBezoek['datum']} {$rowBezoek['van']}<br>\n";
				$msgBody .= "<b>Locatie van het voorval:</b> {$rowBezoek['plaats']}<br><br>\n";
				$msgBody .= "<b>Bijkomende info:</b> {$rowBezoek['opmerking']}<br><br>";
				$msgBody .= "M.v.g.<br><br><br><br>";
				$msgBody .= "Het EHBO-portaal";
				
				// Verzenden
				$this->voorbereiding($arrOntvangers, 'no-reply', $msgSubject, $msgBody);
				$return = $this->zendSMSbericht($accesscode);
				
				// Tabel updaten
				$sql = "UPDATE tblEHBObezoek SET okanMail=1 WHERE id='{$bezoekID}'";
				$result = $conn->query($sql);
			}
			
			// Iets tonen
			return $return;
		}
	}
?>