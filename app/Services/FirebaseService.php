<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Database;

class FirebaseService
{
    protected $auth;
    protected $database;

    public function __construct()
    {
        $credentialsPath = base_path('firebase_credentials.json'); // HARDCODED FOR TESTING
        if (!file_exists($credentialsPath)) {
            throw new \Exception("Firebase credentials file not found at: " . $credentialsPath);
        }

        $factory = (new Factory)
            ->withServiceAccount($credentialsPath)
            ->withDatabaseUri(config('firebase.database_url'));

        // ✅ Initialize both Auth and Database services
        $this->auth = $factory->createAuth();
        $this->database = $factory->createDatabase();
    }

    public function getAuth(): Auth
    {
        return $this->auth;
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }
}


