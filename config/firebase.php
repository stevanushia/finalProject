<?php

return [
    'credentials' => env('FIREBASE_CREDENTIALS', base_path('firebase_credentials.json')),
    'database_url' => env('FIREBASE_DATABASE_URL'),
];
