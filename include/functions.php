<?php
	function zoekWaardeInArrayVanObjecten($optie, $zoekwaarde = NULL, $array = NULL, $sleutel = NULL)
	{
		// Help tonen
		If($optie == "help")
		{
			$help = "De functie 'zoekWaardeInArrayVanObjecten' gaat in een array zoeken of de waarde bestaat.<br>
			Je kan de functie op volgende 2 manieren oproepen:<br>
			<ol>
				<li>zoekWaardeInArrayVanObjecten('help')</li>
				<li>zoekWaardeInArrayVanObjecten(OPTIE, ZOEKWAARDE, ARRAY, SLEUTEL)</li>
			</ol>
			Dankzij de eerste mogelijkheid ben je hier geraakt.<br><br>
			Met de tweede mogelijkheid wordt er gezocht naar de combinatie 'SLEUTEL->ZOEKWAARDE' in ARRAY.<br>
			OPTIE bepaalt wat er wordt teruggestuurd.<br>
			<ul>
				<li>bool --> return = true/false</li>
				<li>sleutel --> return = waarde van die sleutel of false als het niet bestaat</li>
			</ul>";
			return $help;
		}
		
		// De functie
		$temp = "";
		foreach ( $array as $element )
		{
			if ( $zoekwaarde == $element->$sleutel )
			{
				$temp = $element;
			}
		}
		
		// Return bepalen
		if($temp != "")
		{
			switch ($optie)
			{
				case "bool":
					return true;
					break;
				default:
					return $temp->$optie;
			}
		}
		return false;
	}
	
	// Rechten uit database halen en plaatsen in $_SESSION
	function rechtenInlezenGroep($conn, $group)
	{
		// Gegevens ophalen
		$sql = "SELECT r.id, r.groep, p.naam AS platform, ro.naam AS rol, rn.naam AS niveau FROM siteRechten r LEFT JOIN siteRechtenNiveau rn ON rn.id = r.niveauID LEFT JOIN siteRol ro ON ro.id = r.rolID LEFT JOIN sitePlatform p ON p.id = r.platformID WHERE r.groep = '{$group}' ORDER BY r.niveauID ASC;";
		$results = $conn->query($sql);
		$aantalRijen = $conn->affected_rows;
		
		// Array vullen
		$arrRechten = [];
		// while ($row = $result->fetch_assoc())
		foreach($results as $row)
		{
			$arrRechten[] = $row;
		}
		
		// Return
		Return $arrRechten;
	}
	
	function rechtenInlezen($conn): void
	{
		// Variabele
		$tempArray1 = [];
		$tempArray2 = [];
		
		// Groepen overlopen
		(isset($_SESSION['groups'])) ? $arrGroups=$_SESSION['groups'] : $arrGroups=[];
		foreach ( $arrGroups as $element )
		{
			// Gegevens ophalen
			$tempArray2 = rechtenInlezenGroep($conn, $element->code);
			
			// Resultaten samenvoegen
			$tempArray1 = array_merge($tempArray1, $tempArray2);
		}
		
		// Resultaat
		$_SESSION['rechten'] = $tempArray1;
	}
	
	// Functie om input veilig te maken
	Function test_input($data)
	{
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}
	
	// Begin schooljaar opzoeken
	Function datumSJ($invoerDatum)
	{
		// Datums opbouwen
		$vorigJaar = date("Y-m-d",strtotime("last year",strtotime($invoerDatum)));
		$startKalenderjaar = date("Y-m-d",strtotime("01-01-".date("Y", strtotime($invoerDatum))));
		$eindeKalenderjaar = date("Y-m-d",strtotime("31-12-".date("Y", strtotime($invoerDatum))));
		
		switch (date("m"))
		{
			case "01":
			case "02":
			case "03":
			case "04":
			case "05":
			case "06":
			case "07":
				$startJaar = date("Y", strtotime($invoerDatum))-1;
				$eindJaar = date("Y", strtotime($invoerDatum));
				break;
			case "08":
				if(date("d")<="15")
				{
					$startJaar = date("Y", strtotime($invoerDatum))-1;
					$eindJaar = date("Y", strtotime($invoerDatum));
				}else{
					$startJaar = date("Y", strtotime($invoerDatum));
					$eindJaar = date("Y", strtotime($invoerDatum))+1;
				}
				break;
			case "09":
			case "10":
			case "11":
			case "12":
				$startJaar = date("Y", strtotime($invoerDatum));
				$eindJaar = date("Y", strtotime($invoerDatum))+1;
				break;
			default:
				break;
		}
		$startSchooljaar = date("Y-m-d",strtotime("16-08-".$startJaar));
		$eindeSchooljaar = date("Y-m-d",strtotime("15-08-".$eindJaar));
		
		// Return
		return array("startSJ"=>$startSchooljaar, "eindeSJ"=>$eindeSchooljaar, "startKJ"=>$startKalenderjaar, "eindeKJ"=>$eindeKalenderjaar, "vorigJaar"=>$vorigJaar);
	}
	
	// String to Array
	Function string2array($string)
	{
		// Remove the square brackets and single quotes
		$string = trim($string, "[]");
		$string = str_replace("'", "", $string);

		// Split the string into an array and return it
		return explode(', ', $string);
	}

    // Functie om internnummer te genereren op basis van naam
    Function generateInternnummer(string $voornaam, string $achternaam)
    {
        // Variabelen
		$parts = explode(" ", $achternaam);
        $last_name_part_count = count($parts);

        // Achternaam bestaat uit 2 delen
        if ($last_name_part_count == 2)
        {
            $internnummer = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 2) . substr($voornaam, 0, 3));
        } 
        // Achternaam bestaat uit 3 delen
        elseif ($last_name_part_count == 3)
        {
            $internnummer = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1) . substr($parts[2], 0, 1) . substr($voornaam, 0, 3));
        } 
        // Achternaam bestaat uit 1 deel of meer dan 3 delen
        else
        {
            $internnummer = strtoupper(substr($achternaam, 0, 3) . substr($voornaam, 0, 3));
        }

        return $internnummer;
    }
	
	// Clone elk object binnen de array --> zo kan je werken op een kopie
	function diepeKopieArray(array $array) : array
	{
		return array_map(function($item)
			{
				return clone $item;
			}, $array);
	}
	
	// Object binnen School\Structuur op actief zetten en ook alle bovenliggende objecten
	function zetGroepActiefRecursief (School\Structuur $groep, array $arrGroepen, bool $reset = false) : void
	{
		// Variabelen
		static $eersteKeer = true;
		
		// Reset uitvoeren
		if ($reset)
		{
			$eersteKeer = true;
		}
		
		// Alleen de eerste keer
		if ($eersteKeer)
		{
			// Aanpassen
			$groep->set_isChecked(true);
			
			// Variabelen aanpassen
			$eersteKeer = false;
		}
		
		// Deze groep actief zetten
		$groep->set_isActief(true);
		
		// Stoppen als je boven bent
		if ($groep->get_parent() != '')
		{
			// Volgend niveau aanpassen
			zetGroepActiefRecursief ($arrGroepen[$groep->get_parent()],$arrGroepen);
		}
	}
	
	function toonBoomstructuur (Persoon\Persoon $persoon = null, School\Structuur $groep, array $groepen, School\Campusfilter $filter, bool $reset = false) : string
	{
		// Variabelen
		static $eersteKeer = true;
		$output = '';
		$kopieGroep = clone $groep;	// Wijzigingen enkel binnen de functie gebruiken dankzij 'clone'
		$kinderen = $kopieGroep->get_kinderen();
		
		// Reset uitvoeren
		if ($reset)
		{
			$eersteKeer = true;
		}
		
		// Alleen de eerste keer uitvoeren
		if ($eersteKeer)
		{
			if(isset($_SESSION['profiel']))
			{
				if($_SESSION['profiel']['SYNCgroepenOPEN']==1)
				{
					// Alle groepen automatisch uit te klappen
					foreach($groepen as $group)
					{
						$group->set_isActief(true);
					}
				}
				else
				{
					// Niet alle groepen automatisch uit te klappen
					foreach($groepen as $group)
					{
						$group->set_isActief(false);
					}
				}
			}
			
			// Is er wel een gebruiker meegegeven?
			if ($persoon !== null)
			{
				// Variabelen
				$huidigeCampus = $filter->get_huidigeCampus();
				$first_letter = substr($huidigeCampus,0,1);
				$alleHuidigeGroepen = $persoon->getGroupInfo();
				
				// Wijzigingen enkel binnen de functie gebruiken dankzij 'clone'
				$groepen = diepeKopieArray($groepen);
				
				// Heeft de gebruiker wel groepen in deze campus?
				if (!empty($alleHuidigeGroepen[$first_letter."Groups"]))
				{
					// Huidige groepen en oudergroepen actief zetten
					$arrTemp = explode(', ', str_replace(['[', ']', "'"], '', $alleHuidigeGroepen[$first_letter."Groups"]));
					
					foreach ($arrTemp as $group)
					{
						if (isset($groepen[$group]))
						{
							zetGroepActiefRecursief($groepen[$group], $groepen, true);
						}
						else
						{
							$_SESSION['log']['errors'][] = "Groep $group bestaat niet in de array.";
						}
					}
				}
				// Heeft de gebruiker een klas?
				if (!empty($alleHuidigeGroepen['klas']))
				{
					// Huidige groepen en oudergroepen actief zetten
					$arrTemp = explode(', ', str_replace(['[', ']', "'"], '', $alleHuidigeGroepen['klas']));
					
					foreach ($arrTemp as $group)
					{
						if (isset($groepen[$group]))
						{
							zetGroepActiefRecursief($groepen[$group], $groepen, true);
						}
						else
						{
							$_SESSION['log']['errors'][] = "Groep $group bestaat niet in de array.";
						}
					}
				}
				// Heeft de gebruiker webuntis- of titularisgroepen?
				if ((!empty($alleHuidigeGroepen['wGroups'])) || (!empty($alleHuidigeGroepen['tituGroups'])))
				{
					// Huidige groepen en oudergroepen actief zetten
					$arrTemp1 = explode(', ', str_replace(['[', ']', "'"], '', $alleHuidigeGroepen['wGroups']));
					$arrTemp2 = explode(', ', str_replace(['[', ']', "'"], '', $alleHuidigeGroepen['tituGroups']));
					$arrTemp = array_merge($arrTemp1,$arrTemp2);
					
					foreach ($arrTemp as $group)
					{
						if (isset($groepen[$group]))
						{
							zetGroepActiefRecursief($groepen[$group], $groepen, true);
						}
						else
						{
							$_SESSION['log']['errors'][] = "Groep $group bestaat niet in de array.";
						}
					}
				}
			}
			
			// De bovenste groep tonen
			$checked = $groepen[$kopieGroep->get_code()]->get_isChecked() ? ' checked' : '';
			$output .= "<input type='checkbox' name='lbxGroepen[]' id='{$kopieGroep->get_code()}' value='{$kopieGroep->get_code()}'{$checked}>\n";
			$output .= "<label for='{$kopieGroep->get_code()}'>{$kopieGroep->get_naam()}</label>\n";
			
			// Variabelen aanpassen
			$eersteKeer = false;
		}
		
		// Structuur overlopen
		foreach ($kinderen as $kind)
		{
			// Is de groep actief en checked?
			$style = $groepen[$kind]->get_isActief() ? 'nestedGroup activeGroup' : 'nestedGroup';
			$checked = $groepen[$kind]->get_isChecked() ? ' checked' : '';

			// Zijn er subgroepen?
			// if (empty($kind))
			if (empty($groepen[$kind]->get_kinderen()))
			{
				// <li> maken --> geen subgroep
				$output .= "<li>\n";
				$output .= "<input type='checkbox' name='lbxGroepen[]' id='{$kind}' value='{$kind}'{$checked}>\n";
				$output .= "<label for='{$kind}'>{$groepen[$kind]->get_naam()}</label>\n";
				$output .= "</li>\n";
			}
			else
			{
				// <li> maken en <ul> starten --> wel subgroep
				$output .= "<li><span class='caretGroup' id='{$kind}'>\n";
				$output .= "<input type='checkbox' name='lbxGroepen[]' id='{$kind}' value='{$kind}'{$checked}>\n";
				$output .= "<label for='{$kind}'>{$groepen[$kind]->get_naam()}</label>\n";
				$output .= "</span>\n<ul class='{$style}'>\n";
				
				// <ul> vullen
				$output .= toonBoomstructuur ($persoon,$groepen[$kind],$groepen,$filter);
				
				// <ul> en <li> sluiten
				$output .= "</ul>\n</li>\n";
			}
		}
		
		// Return
		return $output;
	}
	
	function groepenOphalen(Database $conn, School\Campusfilter $filter, string $parent)
	{
		// Gegevens ophalen met meegegeven $parent
		$where = $filter->mysqlWhere ('groep', "(code LIKE 'b%' OR code LIKE 'p%' OR code LIKE 'r%') AND parent='{$parent}'");
		$conn->connect();
		$resultGeg = $conn->select(TBLSTRUCTUUR, ['code', 'naam', 'parent'], $where, "code ASC");
		$conn->disconnect();
		
		// Variabelen
		$arrGroepen = [];
		
		// Rijen overlopen
		foreach($resultGeg as $row)
		{
			// Testgegevens ophalen
			$conn->connect();
			$resultTest = $conn->select(TBLSTRUCTUUR,array('*'),"parent='".$row['code']."'");
			$aantalRijenTest = $conn->affected_rows;
			$conn->disconnect();
			
			if($aantalRijenTest==0)
			{
				// Bewaren
				$arrGroepen[$row['code']] = ['code'=>$row['code'],'naam'=>$row['naam']];
			}else{
				// Bewaren
				$arrGroepen[$row['code']] = ['code'=>$row['code'],'naam'=>$row['naam']];
				
				// Recursief zoeken
				$arrGroepen = array_merge($arrGroepen, groepenOphalen($conn,$filter,$row['code']));
			}
		}
		
		// Return
		return $arrGroepen;
	}
	
	Function groepenDatalist(Database $conn, School\Campusfilter $filter, string $parent)
	{
		// Variabele
		$arrGroepen = [];
		$strDatalist = '';
		
		// Gegevens ophalen met meegegeven $parent
		$arrGroepen = groepenOphalen($conn,$filter,$parent);
		
		// Array sorteren op $key
		ksort($arrGroepen);
		
		// Weergave opbouwen
		foreach($arrGroepen as $groep)
		{
			$strDatalist .= "<option value='{$groep['code']}'>{$groep['naam']}</option>\n";
		}
		
		// Return
		return $strDatalist;
	}
?>