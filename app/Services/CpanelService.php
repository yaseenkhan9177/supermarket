<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\Log;

class CpanelService
{
    protected $domain;
    protected $cpanelUser;
    protected $apiToken;
    protected $dbUser;

    public function __construct()
    {
        $this->domain = env('CPANEL_DOMAIN');
        $this->cpanelUser = env('CPANEL_USERNAME');
        $this->apiToken = env('CPANEL_API_TOKEN');
        $this->dbUser = env('CPANEL_DB_USER');
    }

    public function provisionTenantDatabase($databaseName)
    {
        try {
            // 1. Create the database in cPanel
            $this->makeCpanelRequest('/execute/Mysql/create_database', [
                'name' => $databaseName
            ]);

            // 2. Assign the default DB user with ALL PRIVILEGES
            $this->makeCpanelRequest('/execute/Mysql/set_privileges_on_database', [
                'user' => $this->dbUser,
                'database' => $databaseName,
                'privileges' => 'ALL PRIVILEGES'
            ]);

            return $databaseName;
        } catch (Exception $e) {
            Log::error('cPanel DB Creation Failed: ' . $e->getMessage());
            throw new Exception("Failed to provision server resources. " . $e->getMessage());
        }
    }

    private function makeCpanelRequest($endpoint, $parameters)
    {
        $response = Http::withHeaders([
            'Authorization' => 'cpanel ' . $this->cpanelUser . ':' . $this->apiToken
        ])->get($this->domain . $endpoint, $parameters);

        if (!$response->successful() || isset($response->json()['errors'])) {
            $error = $response->json()['errors'][0] ?? 'Unknown cPanel API Error';
            throw new Exception($error);
        }

        return $response->json();
    }
}
