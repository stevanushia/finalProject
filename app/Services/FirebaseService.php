<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class FirebaseService
{
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

        $this->database = $factory->createDatabase();
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }
}
