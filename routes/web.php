<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirebaseController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\GameOverviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TournamentController;

Route::get('/', function () {
    return view('pages.home');
});

Route::get('/template', function () {
    return view('welcome');
});

// Subscription page - accessible to everyone (guests can view)
Route::get('/subscription', [SubscriptionController::class, 'show'])->name('subscription.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
});

Route::middleware('auth')->group(function () {
    // Statistics routes
    Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
    Route::get('/stats', [StatisticsController::class, 'index'])->name('stats'); // Alternative route
    
    // Game Overview routes
    Route::get('/games', [GameOverviewController::class, 'listGames'])->name('game.list');
    Route::get('/game/{gameId}/overview', [GameOverviewController::class, 'index'])->name('game.overview.specific');
    Route::delete('/games/{gameId}/delete', [GameOverviewController::class, 'deleteGame'])->name('games.delete');    

    // Profile route
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');

    // Subscription actions - require login
    Route::post('/subscription/payment', [SubscriptionController::class, 'createPayment'])->name('subscription.payment');
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');

// Tournament Routes
    Route::get('/tournaments', [TournamentController::class, 'index'])->name('tournaments.index');
    Route::get('/tournaments/create', [TournamentController::class, 'create'])->name('tournaments.create');
    Route::post('/tournaments', [TournamentController::class, 'store'])->name('tournaments.store');
    Route::get('/tournaments/{id}', [TournamentController::class, 'show'])->name('tournaments.show');
    
    // UPDATE MATCH (Existing)
    Route::post('/tournaments/{tournamentId}/match/{matchId}', [TournamentController::class, 'updateMatch'])->name('tournaments.match.update');
    
    // DELETE TOURNAMENT (New)
    Route::delete('/tournaments/{id}', [TournamentController::class, 'destroy'])->name('tournaments.destroy');
});

Route::get('/create-team', function () {
    return view('pages.create-team');
});

// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/firebase-login', [AuthController::class, 'firebaseLogin']);

// Midtrans notification webhook - NO AUTH, NO CSRF (called by Midtrans servers)
Route::post('/midtrans/notification', [SubscriptionController::class, 'handleNotification'])->name('midtrans.notification');

// Firebase test routes
Route::get('/firebase/store', [FirebaseController::class, 'storeData']);
Route::get('/firebase/get', [FirebaseController::class, 'getData']);