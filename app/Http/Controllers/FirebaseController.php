<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\Request;

class FirebaseController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase->getDatabase();
    }

    public function storeData()
    {
        $this->firebase->getReference('users/1')->set([
            'name' => 'John Doe',
            'email' => 'johndoe@example.com'
        ]);

        return response()->json(['message' => 'Data stored successfully']);
    }

    public function getData()
    {
        $data = $this->firebase->getReference('users/1')->getValue();
        return response()->json($data);
    }
}
