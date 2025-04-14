<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirebaseController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('pages.home');
});

Route::get('/template', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
});

Route::middleware('auth')->group(function () {
    // Route::get('/', function () {
    //     return view('pages.home');
    // });
    // Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});


Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::post('/firebase-login', [AuthController::class, 'firebaseLogin']);







Route::get('/firebase/store', [FirebaseController::class, 'storeData']);
Route::get('/firebase/get', [FirebaseController::class, 'getData']);

