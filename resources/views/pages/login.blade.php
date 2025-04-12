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
                    <button onclick="loginWithEmail()" class="btn btn-primary w-100">Login</button>

                    <div class="text-center mt-3">or</div>

                    <button onclick="googleLogin()" class="btn btn-danger w-100 mt-2">Login with Google</button>

                    <p class="text-center mt-4">
                        Don’t have an account? <a href="{{ route('register') }}">Register!</a>
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

    function loginWithEmail() {
        const email = document.getElementById('login_email').value;
        const password = document.getElementById('login_password').value;

        firebase.auth().signInWithEmailAndPassword(email, password)
            .then(userCredential => userCredential.user.getIdToken())
            .then(token => {
                return fetch('/firebase-login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ token })
                });
            })
            .then(async response => {
                    const contentType = response.headers.get('content-type');
                    const data = contentType.includes('application/json')
                        ? await response.json()
                        : await response.text();

                    console.log('Response:', data);
                    window.location.href = '/';
                })
            // .then(() => window.location.href = '/');
    }

    document.addEventListener('DOMContentLoaded', function () {
        window.googleLogin = function () {
            const csrf = document.querySelector('meta[name="csrf-token"]');
            const token = csrf ? csrf.getAttribute('content') : null;

            if (!token) {
                alert('CSRF token not found!');
                return;
            }

            const provider = new firebase.auth.GoogleAuthProvider();
            firebase.auth().signInWithPopup(provider)
                .then(result => result.user.getIdToken())
                .then(firebaseToken => {
                    return fetch('/firebase-login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ token: firebaseToken })
                    });
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type');
                    const data = contentType.includes('application/json')
                        ? await response.json()
                        : await response.text();

                    console.log('Response:', data);
                    // window.location.href = '/';
                })
                .catch(error => {
                    console.error('Login failed:', error);
                    alert('Login failed: ' + error.message);
                });
        };
    });

</script>
@endpush
