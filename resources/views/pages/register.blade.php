@extends('layouts.auth')

@section('title', 'Register')

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
                    <h4 class="text-center mb-4">Register</h4>
                    <div class="form-group mb-3">
                        <input type="text" id="register_name" class="form-control" placeholder="Full Name" required>
                    </div>
                    <div class="form-group mb-3">
                        <input type="email" id="register_email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="form-group mb-3">
                        <input type="password" id="register_password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="form-group mb-4">
                        <input type="password" id="register_confirm" class="form-control" placeholder="Confirm Password" required>
                    </div>
                    <button onclick="registerWithEmail()" class="btn btn-primary w-100" id="registerBtn">Register</button>

                    <div class="text-center mt-3">or</div>

                    <button onclick="googleRegister()" class="btn btn-danger w-100 mt-2" id="googleBtn"><img class="image" data-alt-override="false" alt="G" srcset="
                        https://www.gstatic.com/marketing-cms/assets/images/d5/dc/cfe9ce8b4425b410b49b7f2dd3f3/g.webp=s48-fcrop64=1,00000000ffffffff-rw 1x,
                        https://www.gstatic.com/marketing-cms/assets/images/d5/dc/cfe9ce8b4425b410b49b7f2dd3f3/g.webp=s96-fcrop64=1,00000000ffffffff-rw 2x
                      " width="30" height="30" loading="lazy" src="https://www.gstatic.com/marketing-cms/assets/images/d5/dc/cfe9ce8b4425b410b49b7f2dd3f3/g.webp=s48-fcrop64=1,00000000ffffffff-rw"> &nbsp;&nbsp;Sign Up with Google</button>

                    <p class="text-center mt-4">
                        Already have an account? <a href="{{ route('login') }}">Login!</a>
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
    firebase.initializeApp{
        apiKey: "AIzaSyB2o8f5gcls6IxHPKaTMZVDdqtXur6gGWw",
        authDomain: "project-ta-df552.firebaseapp.com",
        databaseURL: "https://project-ta-df552-default-rtdb.firebaseio.com",
        projectId: "project-ta-df552",
        storageBucket: "project-ta-df552.appspot.com",
        messagingSenderId: "205264169507",
        appId: "1:205264169507:web:3f024ab34abde7dc8c1f96",
        measurementId: "G-MZZ72360VC"
    };

    function registerWithEmail() {
        const email = document.getElementById('register_email').value;
        const password = document.getElementById('register_password').value;
        const confirm = document.getElementById('register_confirm').value;
        const name = document.getElementById('register_name').value;

        if (password !== confirm) {
            alert('Passwords do not match');
            return;
        }
        // 🔥 Show spinner right after clicking
        document.getElementById('loadingSpinner').style.display = 'flex';
        document.getElementById('registerBtn').disabled = true;

        firebase.auth().createUserWithEmailAndPassword(email, password)
            .then(userCredential => {
                return userCredential.user.updateProfile({ displayName: name })
                    .then(() => userCredential.user.getIdToken());
            })
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

                console.log('Registration Response:', data);
                document.getElementById('loadingSpinner').style.display = 'flex';

                setTimeout(() => {
                    window.location.href = '/';
                }, 200);
            })
            .catch(error => {
                document.getElementById('loadingSpinner').style.display = 'none';
                alert('Register failed: ' + error.message);
                
                document.getElementById('loadingSpinner').style.display = 'none';
                document.getElementById('registerBtn').disabled = false;
            });
    }

    window.googleRegister = function () {
        const csrf = document.querySelector('meta[name="csrf-token"]');
        const token = csrf ? csrf.getAttribute('content') : null;

        if (!token) {
            alert('CSRF token not found!');
            return;
        }

        
        document.getElementById('loadingSpinner').style.display = 'flex';
        document.getElementById('googleBtn').disabled = true;

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

                console.log('Google Register Response:', data);
                document.getElementById('loadingSpinner').style.display = 'flex';
                

                setTimeout(() => {
                    window.location.href = '/';
                }, 200);
            })
            .catch(error => {
                document.getElementById('loadingSpinner').style.display = 'none';
                alert('Register failed: ' + error.message);
                document.getElementById('loadingSpinner').style.display = 'none';
                document.getElementById('googleBtn').disabled = false;
            });
    };
    </script>
@endpush

