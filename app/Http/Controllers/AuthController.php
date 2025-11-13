<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;

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
            // Redirect to home, or use intended if you have specific logic
            return redirect('/'); 
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

        // Redirect to home after registration
        return redirect('/'); 
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function firebaseLogin(Request $request)
    {
        try {
            Log::info('Firebase Login Request:', [
                'has_token' => $request->has('token'),
                'token_length' => $request->token ? strlen($request->token) : 0,
            ]);

            if (!$request->has('token') || empty($request->token)) {
                Log::warning('Firebase token missing');
                return response()->json([
                    'error' => true,
                    'message' => 'Firebase token is required',
                ], 400);
            }

            $factory = (new Factory)
                ->withServiceAccount(base_path('firebase_credentials.json'))
                ->withDatabaseUri('https://project-ta-df552-default-rtdb.firebaseio.com');   
            
            $auth = $factory->createAuth();
            
            // Create a database instance as well
            $database = $factory->createDatabase(); 

            // Verify the Firebase ID token
            $verifiedIdToken = $auth->verifyIdToken($request->token);
            $uid = $verifiedIdToken->claims()->get('sub');
            
            // Get user information from Firebase Auth
            $firebaseUser = $auth->getUser($uid);
            
            // --- NEW SYNC LOGIC ---
            // 1. Get user data from Realtime Database
            $rtdbUser = $database->getReference('users/' . $uid)->getValue();
            $rtdbName = $rtdbUser['displayName'] ?? null;

            // 2. Get user data from Firebase Auth
            $authName = $firebaseUser->displayName ?? $firebaseUser->email;

            // 3. Decide which name is the "best" or "freshest"
            // We'll prefer the Realtime Database name since that's what your app uses
            $finalName = $rtdbName ?? $authName;
            // --- END NEW SYNC LOGIC ---

            Log::info('Firebase User Data:', [
                'uid' => $uid,
                'email' => $firebaseUser->email,
                'authName' => $authName,
                'rtdbName' => $rtdbName,
                'finalName' => $finalName
            ]);

            // Find or create user in your local database
            $user = User::firstOrCreate(
                ['email' => $firebaseUser->email],
                [
                    'name' => $finalName, // Use the "finalName" on creation
                    'password' => Hash::make(uniqid()), 
                    'email_verified_at' => $firebaseUser->emailVerified ? now() : null,
                ]
            );

            // Sync name for EXISTING users
            // If the user was *found* (not created) but their name is out of sync, update it
            if ($user->wasRecentlyCreated === false && $user->name !== $finalName) {
                $user->name = $finalName;
                $user->save();
                Log::info('Local user name synced on login.', ['user_id' => $user->id, 'new_name' => $finalName]);
            }
            
            Log::info('User found/created:', [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);

            // Login the user using Laravel's authentication
            Auth::login($user, true); 
            
            $request->session()->regenerate();
            
            session([
                'login_confirm' => 'yes',
                'firebase_login' => true,
                'firebase_uid' => $uid,
            ]);

            $request->session()->save(); 
            
            Log::info('Session after Firebase login:', [
                'auth_check' => Auth::check(),
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged in',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name, // This will now be the correct, synced name
                    'email' => $user->email,
                ],
                'redirect_url' => '/'
            ]);
            
        } catch (AuthException $e) {
            Log::error('Firebase Auth Exception:', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return response()->json([
                'error' => true,
                'message' => 'Invalid or expired Firebase token: ' . $e->getMessage(),
            ], 401);
            
        } catch (FirebaseException $e) {
            Log::error('Firebase Exception:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'error' => true,
                'message' => 'Firebase service error: ' . $e->getMessage(),
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Firebase Login Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'error' => true,
                'message' => 'Authentication failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}