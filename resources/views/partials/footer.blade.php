<footer class="section footer-classic footer-classic-dark context-dark">
    <div class="footer-classic-main">
        <div class="container">
            <div class="row row-50 justify-content-between">
                
                <div class="col-lg-4 col-md-5">
                    <div class="brand-wrapper mb-4">
                        <a class="brand brand-md" href="/">
                            <img class="brand-logo" src="{{ asset('assets/images/main-logo.png') }}" alt="Logo" width="80" height="106" />
                        </a>
                    </div>
                    <p class="text-white-50" style="max-width: 350px;">
                        The ultimate platform for basketball analytics. Track games in real-time, manage tournaments, and analyze player performance like a pro.
                    </p>
                    <div class="group-sm group-middle mt-4">
                        <a class="button button-sm button-primary-outline" href="{{ route('game.list') }}">Start Tracking</a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-3 col-sm-6">
                    <h5 class="footer-title fw-bold mb-3 text-white">Product</h5>
                    <ul class="list-unstyled footer-list">
                        <li class="mb-2"><a href="{{ route('home') }}" class="text-white-50 text-decoration-none hover-white">Home</a></li>
                        <li class="mb-2"><a href="{{ route('game.list') }}" class="text-white-50 text-decoration-none hover-white">Live Games</a></li>
                        <li class="mb-2"><a href="{{ route('tournaments.index') }}" class="text-white-50 text-decoration-none hover-white">Tournaments</a></li>
                        <li class="mb-2"><a href="{{ route('subscription.show') }}" class="text-white-50 text-decoration-none hover-white">Pricing & Plans</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <h5 class="footer-title fw-bold mb-3 text-white">Account</h5>
                    <ul class="list-unstyled footer-list">
                        @auth
                            <li class="mb-2"><a href="{{ route('profile') }}" class="text-white-50 text-decoration-none hover-white">My Profile</a></li>
                            @if(session('firebase_is_admin'))
                                <li class="mb-2"><a href="{{ route('admin.dashboard') }}" class="text-warning text-decoration-none fw-bold">Admin Panel</a></li>
                            @endif
                            <li class="mt-3">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-link p-0 text-white-50 text-decoration-none hover-white">Logout</button>
                                </form>
                            </li>
                        @else
                            <li class="mb-2"><a href="{{ route('login') }}" class="text-white-50 text-decoration-none hover-white">Login</a></li>
                            <li class="mb-2"><a href="{{ route('register') }}" class="text-white-50 text-decoration-none hover-white">Register</a></li>
                        @endauth
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <div class="footer-classic-aside footer-classic-darken">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <p class="rights mb-0">
                    <span>&copy;&nbsp;</span><span class="copyright-year"></span>
                    <span>&nbsp;</span><span>CourtSide Stats</span><span>.&nbsp;</span>
                    <span class="text-white-50">All Rights Reserved.</span>
                </p>
                
                <p class="mb-0 small text-white-50">
                    Designed by <a href="https://www.linkedin.com/in/stevanushia" target="_blank" class="text-white fw-bold text-decoration-none">Stevanus Hia</a>
                </p>
            </div>
        </div>
    </div>
</footer>

<style>
    /* Simple hover effect for footer links */
    .hover-white:hover {
        color: #fff !important;
        text-decoration: underline !important;
    }
</style>