<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirebaseController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/firebase/store', [FirebaseController::class, 'storeData']);
Route::get('/firebase/get', [FirebaseController::class, 'getData']);

