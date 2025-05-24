@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<section class="section section-lg bg-default d-flex align-items-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm rounded-4 p-4">
                    <div class="text-center">
                        <a href="/">
                            <img src="{{ asset('assets/images/main-logo-dark.png') }}" alt="" width="95" height="126">
                        </a>
                    </div>
                    <h4 class="text-center mb-4">Login</h4>

                    <div class="form-group mb-3">
                        <input type="email" id="login_email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="form-group mb-4">
                        <input type="password" id="login_password" class="form-control" placeholder="Password" required>
                    </div>
                    <button onclick="loginWithEmail()" class="btn btn-primary w-100" id="loginBtn">Login</button>

                    <div class="text-center mt-3">or</div>

                    <button onclick="googleLogin()" class="btn btn-danger w-100 mt-2" id="googleBtn">
                        <img class="image" data-alt-override="false" alt="G" srcset="
                            https://www.gstatic.com/marketing-cms/assets/images/d5/dc/cfe9ce8b4425b410b49b7f2dd3f3/g.webp=s48-fcrop64=1,00000000ffffffff-rw 1x,
                            https://www.gstatic.com/marketing-cms/assets/images/d5/dc/cfe9ce8b4425b410b49b7f2dd3f3/g.webp=s96-fcrop64=1,00000000ffffffff-rw 2x
                        " width="30" height="30" loading="lazy" src="https://www.gstatic.com/marketing-cms/assets/images/d5/dc/cfe9ce8b4425b410b49b7f2dd3f3/g.webp=s48-fcrop64=1,00000000ffffffff-rw"> 
                        &nbsp;&nbsp;Login with Google
                    </button>

                    <p class="text-center mt-4">
                        Don't have an account? <a href="{{ route('register') }}">Register!</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-auth-compat.js"></script>
<script>
    const firebaseConfig = {
        apiKey: "AIzaSyB2o8f5gcls6IxHPKaTMZVDdqtXur6gGWw",
        authDomain: "project-ta-df552.firebaseapp.com",
        databaseURL: "https://project-ta-df552-default-rtdb.firebaseio.com",
        projectId: "project-ta-df552",
        storageBucket: "project-ta-df552.firebasestorage.app",
        messagingSenderId: "205264169507",
        appId: "1:205264169507:web:3f024ab34abde7dc8c1f96",
        measurementId: "G-MZZ72360VC"
    };

    firebase.initializeApp(firebaseConfig);

    function showSpinner() {
        document.getElementById('loadingSpinner').style.display = 'flex';
    }

    function hideSpinner() {
        document.getElementById('loadingSpinner').style.display = 'none';
    }

    function disableButton(buttonId) {
        document.getElementById(buttonId).disabled = true;
    }

    function enableButton(buttonId) {
        document.getElementById(buttonId).disabled = false;
    }

    function showError(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                text: message
            });
        } else {
            alert('Login failed: ' + message);
        }
    }

    function showSuccess(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: message,
                timer: 1500,
                showConfirmButton: false
            });
        }
    }

    async function sendTokenToBackend(token) {
        try {
            console.log('Sending token to backend...');
            
            const response = await fetch('/firebase-login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ token })
            });

            console.log('Backend response status:', response.status);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Backend error response:', errorText);
                throw new Error(`Server error: ${response.status}`);
            }

            const data = await response.json();
            console.log('Backend response data:', data);

            if (data.error) {
                throw new Error(data.message || 'Authentication failed');
            }

            if (data.status === 'success') {
                showSuccess('Login successful! Redirecting...');
                setTimeout(() => {
                    window.location.href = data.redirect_url || '/';
                }, 1500);
                return true;
            }

            throw new Error('Unexpected response from server');

        } catch (error) {
            console.error('Backend authentication error:', error);
            throw error;
        }
    }

    function loginWithEmail() {
    const email = document.getElementById('login_email').value;
    const password = document.getElementById('login_password').value;

    if (!email || !password) {
        showError('Please enter both email and password');
        return;
    }

    showSpinner();
    disableButton('loginBtn');

    firebase.auth().signInWithEmailAndPassword(email, password)
        .then(userCredential => {
            console.log('Firebase email login successful:', userCredential.user);
            return userCredential.user.getIdToken();
        })
        .then(token => {
            console.log('Got Firebase ID token');
            return sendTokenToBackend(token);
        })
        .then(() => {
            // Success is handled in sendTokenToBackend
            hideSpinner();
            enableButton('loginBtn');
        })
        .catch(error => {
            console.error('Login error:', error);
            hideSpinner();
            enableButton('loginBtn');
            
            let errorMessage = 'Login failed. Please try again.';
            if (error.code) {
                switch (error.code) {
                    case 'auth/user-not-found':
                        errorMessage = 'No account found with this email.';
                        break;
                    case 'auth/wrong-password':
                        errorMessage = 'Incorrect password.';
                        break;
                    case 'auth/invalid-email':
                        errorMessage = 'Invalid email address.';
                        break;
                    case 'auth/too-many-requests':
                        errorMessage = 'Too many failed attempts. Please try again later.';
                        break;
                    default:
                        errorMessage = error.message;
                }
            }
            showError(errorMessage);
        });
}

window.googleLogin = function () {
    const csrf = document.querySelector('meta[name="csrf-token"]');
    const token = csrf ? csrf.getAttribute('content') : null;

    if (!token) {
        showError('CSRF token not found! Please refresh the page.');
        return;
    }

    showSpinner();
    disableButton('googleBtn');

    const provider = new firebase.auth.GoogleAuthProvider();
    provider.addScope('email');
    provider.addScope('profile');

    firebase.auth().signInWithPopup(provider)
        .then(result => {
            console.log('Firebase Google login successful:', result.user);
            return result.user.getIdToken();
        })
        .then(firebaseToken => {
            console.log('Got Firebase ID token from Google login');
            return sendTokenToBackend(firebaseToken);
        })
        .then(() => {
            // Success is handled in sendTokenToBackend
            hideSpinner();
            enableButton('googleBtn');
        })
        .catch(error => {
            console.error('Google login error:', error);
            hideSpinner();
            enableButton('googleBtn');
            
            let errorMessage = 'Google login failed. Please try again.';
            if (error.code) {
                switch (error.code) {
                    case 'auth/popup-closed-by-user':
                        errorMessage = 'Sign-in cancelled by user.';
                        break;
                    case 'auth/popup-blocked':
                        errorMessage = 'Popup was blocked. Please allow popups and try again.';
                        break;
                    case 'auth/network-request-failed':
                        errorMessage = 'Network error. Please check your connection.';
                        break;
                    case 'auth/account-exists-with-different-credential':
                        errorMessage = 'An account already exists with this email using a different sign-in method.';
                        break;
                    default:
                        errorMessage = error.message;
                }
            }
            showError(errorMessage);
        });
};

    window.googleLogin = function () {
        const csrf = document.querySelector('meta[name="csrf-token"]');
        const token = csrf ? csrf.getAttribute('content') : null;

        if (!token) {
            showError('CSRF token not found! Please refresh the page.');
            return;
        }

        showSpinner();
        disableButton('googleBtn');

        const provider = new firebase.auth.GoogleAuthProvider();
        provider.addScope('email');
        provider.addScope('profile');

        firebase.auth().signInWithPopup(provider)
            .then(result => {
                console.log('Firebase Google login successful:', result.user);
                return result.user.getIdToken();
            })
            .then(firebaseToken => {
                console.log('Got Firebase ID token from Google login');
                return sendTokenToBackend(firebaseToken);
            })
            .catch(error => {
                console.error('Google login error:', error);
                hideSpinner();
                enableButton('googleBtn');
                
                let errorMessage = 'Google login failed. Please try again.';
                if (error.code) {
                    switch (error.code) {
                        case 'auth/popup-closed-by-user':
                            errorMessage = 'Sign-in cancelled by user.';
                            break;
                        case 'auth/popup-blocked':
                            errorMessage = 'Popup was blocked. Please allow popups and try again.';
                            break;
                        case 'auth/network-request-failed':
                            errorMessage = 'Network error. Please check your connection.';
                            break;
                        case 'auth/account-exists-with-different-credential':
                            errorMessage = 'An account already exists with this email using a different sign-in method.';
                            break;
                        default:
                            errorMessage = error.message;
                    }
                }
                showError(errorMessage);
            });
    };
</script>
@endpush