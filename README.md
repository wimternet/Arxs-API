# Arxs-API (PHP Implementation)
> Inspired by [@arxssoftware/api.arxs.be-samples](https://github.com/arxssoftware/api.arxs.be-samples)

This repository provides a PHP implementation for interacting with the [Arxs public API](https://api.arxs.be/swagger/index.html), including examples for user, school, and role management.  
It is based on the Node.js sample logic and workflows found in `@arxssoftware/api.arxs.be-samples`.

---

## About

- **Language:** PHP
- **Purpose:** Easily connect, authenticate, and manage entities with the Arxs API, using patterns similar to the official Node.js samples.
- **Status:** In development; API may undergo breaking changes (see the [API spec](https://api.arxs.be/swagger/index.html)).

---

## Authentication

To access the API, you must include a JWT token as a Bearer token in the Authorization header.

**How to obtain a JWT token:**
1. If you have the 'Admin' role, generate an API key from your user profile page.
2. Request a JWT token via:  
   `GET https://identity.arxs.be/api/authenticate/token/{apiKey}`
3. The response will include a JWT token for use in API calls.  
   Add it to your Authorization header:  
   `Authorization: Bearer {jwt-token}`

API credentials are configured in `include/settings.php`:
```php
$arxs_api_key = "YOUR_API_KEY";
$arxs_token_url = "https://identity.arxs.be/api/authenticate/token/";
$arxs_baseUrl = "https://api.arxs.be";
```

---

## Features

- **Connects to Arxs identity and API endpoints**
- **Manages users, user roles, school structures**
- **Uploads attachments/images and links them to entities**
- **Recursively manages group and school structure states**
- **Configurable via simple PHP settings**
- **Examples for:**
  - Creating a task request
  - Creating and assigning an employee record

---

## Usage Example: Create Employee & Assign Role

```php
$arxsData = new Extern\Arxs($arxs_token_url, $arxs_baseUrl, $arxs_api_key);

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

$newEmployeeId = $arxsData->newEmployee($data);
// Add employee to a userRole, similar to the Node.js sample
$arxsData->addUserRole($userRoleId, $newEmployeeId);
```

---

## File Structure

- `index.php` – Main logic, sample code for entity management
- `include/settings.php` – API credentials and settings
- `include/autoload.php` – Autoloads PHP classes
- `include/functions.php` – Utility functions for data and group management

---

## References

- [Node.js samples](https://github.com/arxssoftware/api.arxs.be-samples)
- [API Swagger spec](https://api.arxs.be/swagger/index.html)

---

## Contributing

Pull requests and issues are welcome!  
See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

---

## License

MIT

---

Maintainer: [@wimternet](https://github.com/wimternet)

For questions or help, [open an issue](https://github.com/wimternet/Arxs-API/issues).
