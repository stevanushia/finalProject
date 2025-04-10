<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('pages.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/home');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ]);
    }

    public function showRegisterForm()
    {
        return view('pages.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect('/home');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    // public function firebaseLogin(Request $request)
    // {
    //     $factory = (new Factory)->withServiceAccount(config('firebase.credentials'));
    //     $auth = $factory->createAuth();

    //     $verifiedIdToken = $auth->verifyIdToken($request->token);
    //     $firebaseUser = $auth->getUser($verifiedIdToken->claims()->get('sub'));

    //     $user = User::firstOrCreate(
    //         ['email' => $firebaseUser->email],
    //         ['name' => $firebaseUser->displayName ?? $firebaseUser->email]
    //     );
    //     dd($user);

    //     Auth::login($user);

    //     return response()->json(['status' => 'logged_in']);
    // }
    public function firebaseLogin(Request $request)
    {
        try {
            Log::info('Firebase Login Request:', $request->all());

            $factory = (new Factory)->withServiceAccount(base_path('firebase_credentials.json'));
            $auth = $factory->createAuth();
    
            $verifiedIdToken = $auth->verifyIdToken($request->token);
            $firebaseUser = $auth->getUser($verifiedIdToken->claims()->get('sub'));
    
            $user = User::firstOrCreate(
                ['email' => $firebaseUser->email],
                ['name' => $firebaseUser->displayName ?? $firebaseUser->email]
            );
    
            Auth::guard('web')->login($user);
            session(['login_confirm' => 'yes']);
            Log::info('Session after login:', session()->all());

            return response()->json(['status' => 'logged_in']);
        } catch (\Throwable $e) {
            Log::error('Firebase Login Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
    
            return response()->json([
                'error' => true,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }
    

}

