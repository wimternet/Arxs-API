<?php
	// Programmeren zoals het hoort
	declare(strict_types=1);
	
	// Constants and classes
	require("../include/autoload.php");
	
	// Include
	require("../include/settings.php");
	
	// Initialise
	$arxsData = new Extern\Arxs ($arxs_token_url,$arxs_baseUrl,$arxs_api_key);
	
	
	
	/* Gebruikers aanmaken/verwijderen */
	
	// Haal de school op
	$tempSchool = $arxsData->getMasterdata();
	
	// Haal alle personeelsleden op
	// Bestaat de "nieuwe" Gebruiker
		// Nee --> aanmaken
			// Profielfoto?
	
	/* -------------------------------- */
	/* Gebruiker van rol veranderen */
	
	// Haal de school op
	$tempSchool = $arxsData->getMasterdata();
	
	// Rollen Ophalen
	$tempRol = $arxsData->getRoles();
	
	// Haal alle personeelsleden op
	// Zoek deze gebruiker
	
	// Gebruiker toevoegen aan de rol
	
	
	
	echo ("<h3>Scholengemeenschap</h3>\n");
	foreach ($tempSchool['legalStructure'] as $scholengemeenschap)
	{
		if ($scholengemeenschap['isDeleted'] != 1)
		{
			echo ("Naam: {$scholengemeenschap['name']}<br>\n");
			echo ("ID: {$scholengemeenschap['id']}<br>\n");
			echo ("Deleted: {$scholengemeenschap['isDeleted']}<br>\n");
			echo ("-----<br>\n");
		}
	}
	
	/*echo ("<h3>School</h3>\n");
	foreach ($tempSchool['branch'] as $school)
	{
		if ($school['isDeleted'] != 1)
		{
			echo ("Naam: {$school['name']}<br>\n");
			echo ("ID: {$school['id']}<br>\n");
			echo ("Deleted: {$school['isDeleted']}<br>\n");
			echo ("-----<br>\n");
		}
	}
	
	
	echo ("<h3>Rol</h3>\n");
	foreach ($tempRol as $rol)
	{
		if ($rol['isDeleted'] != 1)
		{
			echo ("Naam: {$rol['name']}<br>\n");
			echo ("ID: {$rol['id']}<br>\n");
			echo ("Deleted: {$rol['isDeleted']}<br>\n");
			echo ("-----<br>\n");
		}
	}*/
	
	$data = [
		"firstname" => "John",
		"surname" => "Doe",
		"userName" => "johndoe"/*,
		"emails" => [
			["isPreferred" => true, "email" => "john.doe@company.com"]
		]/*,
		"assignments" => [
			[
				"legalStructure" => ["id" => "8b706a54-edb0-c2b2-cd7f-08d732a72e73"],
				"branch" => ["id" => "5bc6ed4a-410a-cd5e-6fb4-08d732a83622"],
				"isPreferred" => true
			]
		] /*,
		 "attachmentInfo" => $attachmentInfo*/
	];
	$tempNieuwPersoneel = $arxsData->newEmployee($data);
	
	
	
	// echo ("<pre>\n");
	// print_r($tempRol);
	// echo ("</pre>\n");
	
	// $temp = $arxsData->getLocations();
	// echo ("<pre>\n");
	// print_r($temp);
	// echo ("</pre>\n");









/*
require_once 'client.php'; // Zorg dat dit je client bevat
$client = createClient();

// Ophalen van branches
$branches = $client->masterdata->branch->get();
$branch = array_filter($branches, fn($x) => $x->name === "Branch name 123");
$branch = reset($branch);
if (!$branch) {
    throw new Exception("Branch not found!");
}

// Ophalen van user roles
$userRoles = $client->masterdata->userRole->get();
$userRole = array_filter($userRoles, fn($x) => $x->name === "User");
$userRole = reset($userRole);
if (!$userRole) {
    throw new Exception("Userrole not found!");
}








// Upload afbeelding en verkrijg attachment info
$imageUrl = $client->shared->blob->uploadToCloudStorage("../assets/anonymous.png");
$attachmentInfo = $client->shared->mapImageUrlToAttachmentInfo($imageUrl);










// Gebruiker aanmaken
$data = [
    "firstname" => "John",
    "surname" => "Doe",
    "userName" => "johndoe",
    "emails" => [
        ["isPreferred" => true, "email" => "john.doe@company.com"]
    ],
    "assignments" => [
        [
            "legalStructure" => ["id" => $branch->legalStructure->id],
            "branch" => ["id" => $branch->id],
            "isPreferred" => true
        ]
    ],
    "attachmentInfo" => $attachmentInfo
];

$employeeId = $client->masterdata->employee->post($data);
echo "Employee Created: " . $employeeId . PHP_EOL;







// Gebruiker toevoegen aan rol
$client->masterdata->userRole->addUsers($userRole->id, [$employeeId]);
echo "Employee added to role." . PHP_EOL;
*/
?>