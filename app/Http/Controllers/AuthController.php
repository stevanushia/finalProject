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

    public function firebaseLogin(Request $request)
    {
        try {
            Log::info('Firebase Login Request:', [
                'has_token' => $request->has('token'),
                'token_length' => $request->token ? strlen($request->token) : 0,
            ]);

            // Validate that token is provided
            if (!$request->has('token') || empty($request->token)) {
                Log::warning('Firebase token missing');
                return response()->json([
                    'error' => true,
                    'message' => 'Firebase token is required',
                ], 400);
            }

            $factory = (new Factory)->withServiceAccount(base_path('firebase_credentials.json'));
            $auth = $factory->createAuth();

            // Verify the Firebase ID token
            $verifiedIdToken = $auth->verifyIdToken($request->token);
            $uid = $verifiedIdToken->claims()->get('sub');
            
            // Get user information from Firebase
            $firebaseUser = $auth->getUser($uid);
            
            Log::info('Firebase User Data:', [
                'uid' => $uid,
                'email' => $firebaseUser->email,
                'displayName' => $firebaseUser->displayName,
                'emailVerified' => $firebaseUser->emailVerified,
            ]);

            // Find or create user in your database
            $user = User::firstOrCreate(
                ['email' => $firebaseUser->email],
                [
                    'name' => $firebaseUser->displayName ?? $firebaseUser->email,
                    'password' => Hash::make(uniqid()), // Random password since they're using Firebase auth
                    'email_verified_at' => $firebaseUser->emailVerified ? now() : null,
                ]
            );

            Log::info('User found/created:', [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);

            // Login the user using Laravel's authentication
            Auth::login($user, true); // The 'true' parameter enables "remember me"
            
            // Regenerate session for security
            $request->session()->regenerate();
            
            // Set additional session data
            session([
                'login_confirm' => 'yes',
                'firebase_login' => true,
                'firebase_uid' => $uid,
            ]);

            $request->session()->save(); // 👈 forces session to persist

            
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
                    'name' => $user->name,
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