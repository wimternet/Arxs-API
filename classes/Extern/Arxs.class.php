<?php
	// Namespace
	namespace Extern;
	
	// Class
	Class Arxs
	{
		// Properties
			// API
		private string $api_key;
		private string $bearer_token;
		private string $token_url;
		private string $base_url;
			// Andere
		protected array $structuur = [];	// Vertaalgegevens
		protected array $data = [];			// Al opgehaalde gegevens
		
		// Construct
		public function __construct(string $token_url, string $base_url, string $key, string $bearer_token = '')
		{
			// Gegevens ontvangen
			$this->token_url = $token_url;
			$this->base_url = $base_url;
			$this->api_key = $key;
			$this->bearer_token = $bearer_token;
			
			// JWT token opvragen als het nog niet is gebeurd
			if (empty($this->bearer_token))
			{
				$tempURL = $this->token_url . $this->api_key;
				
				$tempJSON = @file_get_contents($tempURL);
				if ($tempJSON === false)
				{
					throw new Exception("Kon JWT-token niet ophalen van: {$tempURL}");
				}
				
				$decoded = json_decode($tempJSON, true);
				if ($decoded === null)
				{
					throw new Exception("Ongeldige JSON ontvangen van: $url");
				}
				
				$this->bearer_token = $decoded;
			}
		}
		
		// Methods
		private function fetchFromApi(string $path)
		{
			// Vraag
			$url = $this->base_url . $path;
			$options = [
				'http' => [
					'method' => 'GET',
					'header' => "Authorization: Bearer {$this->bearer_token}\r\n"
				]
			];
			
			// Antwoord
			$context = stream_context_create($options);
			$response = file_get_contents($url, false, $context);
			
			// Controle
			if ($response === FALSE)
			{
				throw new Exception("Failed to retrieve $path");
			}
			
			// Versturen
			return json_decode($response, true);
		}
		
		private function postToApi(string $path, $body)
		{
			echo ("1<br>\n");
			
			
			// Vraag
			$url = $this->base_url . $path;
			$options = [
				"http" => [
					"method" => "POST",
					"header" => "Authorization: Bearer {$this->bearer_token}\r\n" .
								"Content-Type: application/json\r\n",
					"content" => json_encode($body)
				]
			];
			
			echo ("2<br>\n");
			echo ("{$url}<br>\n");
			echo ("<pre>\n");
			print_r ($options);
			echo ("</pre>\n");
			echo ("2b<br>\n");

			// Antwoord
			$context = stream_context_create($options);
			$response = file_get_contents($url, false, $context);
			
			echo ("3<br>\n");
			echo ("{$context}<br>\n");
			echo ("{$response}<br>\n");

			// Controle
			if ($response === false)
			{
				throw new Exception("Failed to POST: {$path}");
			}

			// Versturen
			return json_decode($response, true);
		}
		
		private function loadMasterData() : void
		{
			// Gegevens ophalen en binnen het object bewaren
			$this->data['legalStructure'] = $this->fetchFromApi("/api/masterdata/legalstructure");
			$this->data['branch'] = $this->fetchFromApi("/api/masterdata/branch");
		}
		
		public function getMasterdata() : array
		{
			// Gegevens ophalen
			$this->loadMasterData();
			
			// Antwoord sturen
			return [
				'legalStructure' => $this->data['legalStructure'],
				'branch' => $this->data['branch']
			];

		}
		
		public function getRoles(string $id = '')
		{
			if ($id == '')
			{
				// Alle personeelsleden ophalen
				return $this->fetchFromApi("/api/masterdata/userrole");
			}
			else
			{
				// Enkel personeelslid met id = $id ophalen
				return $this->fetchFromApi("/api/masterdata/userrole/{$id}");
			}
		}
		
		public function getLocations(string $id = '')
		{
			if ($id == '')
			{
				// Alle lokalen ophalen
				return $this->fetchFromApi("/api/assetmanagement/location");
			}
			else
			{
				// Lokaal met id = $id ophalen
				return $this->fetchFromApi("/api/assetmanagement/location/{$id}");
			}
		}
		
		public function getEmployees(string $id = '')
		{
			if ($id == '')
			{
				// Alle personeelsleden ophalen
				return $this->fetchFromApi("/api/masterdata/employee");
			}
			else
			{
				// Enkel personeelslid met id = $id ophalen
				return $this->fetchFromApi("/api/masterdata/employee/{$id}");
			}
		}
		
		public function newEmployee($data)
		{
			return $this->postToApi("/api/masterdata/employee", $data);
		}
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
	}
?>