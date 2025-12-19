<header class="section page-header rd-navbar-dark">
    <div class="rd-navbar-wrap">
        <nav class="rd-navbar rd-navbar-classic" data-layout="rd-navbar-fixed" data-sm-layout="rd-navbar-fixed"
            data-md-layout="rd-navbar-fixed" data-md-device-layout="rd-navbar-fixed"
            data-lg-layout="rd-navbar-fixed" data-lg-device-layout="rd-navbar-fixed"
            data-xl-layout="rd-navbar-static" data-xl-device-layout="rd-navbar-static"
            data-xxl-layout="rd-navbar-static" data-xxl-device-layout="rd-navbar-static"
            data-lg-stick-up-offset="166px" data-xl-stick-up-offset="166px" data-xxl-stick-up-offset="166px"
            data-lg-stick-up="true" data-xl-stick-up="true" data-xxl-stick-up="true">
            <div class="rd-navbar-panel">
                <button class="rd-navbar-toggle" data-rd-navbar-toggle=".rd-navbar-main"><span></span></button>
                <div class="rd-navbar-panel-inner container">
                    <div class="rd-navbar-collapse rd-navbar-panel-item rd-navbar-panel-item-left">
                        <div class="owl-carousel-navbar owl-carousel-inline-outer">
                            <div class="owl-inline-nav">
                                <button class="owl-arrow owl-arrow-prev"></button>
                                <button class="owl-arrow owl-arrow-next"></button>
                            </div>
                            <div class="owl-carousel-inline-wrap">
                                <div class="owl-carousel owl-carousel-inline" data-items="1" data-dots="false"
                                    data-nav="true" data-autoplay="true" data-autoplay-speed="3200"
                                    data-stage-padding="0" data-loop="true" data-margin="10"
                                    data-mouse-drag="false" data-touch-drag="false"
                                    data-nav-custom=".owl-carousel-navbar">
                                    
                                    {{-- DYNAMIC CONTENT --}}
                                    @if(isset($upcomingTournaments) && count($upcomingTournaments) > 0)
                                        @foreach($upcomingTournaments as $t)
                                            <article class="post-inline">
                                                <time class="post-inline-time" datetime="{{ date('Y', $t['startDate']/1000) }}">
                                                    {{ date('F d, Y', $t['startDate']/1000) }}
                                                </time>
                                                <p class="post-inline-title">
                                                    <a href="{{ route('tournaments.show', $t['id']) }}">{{ $t['name'] }}</a>
                                                </p>
                                            </article>
                                        @endforeach
                                    @else
                                        {{-- FALLBACK (Shown if no tournaments or on pages without data) --}}
                                        <article class="post-inline">
                                            <time class="post-inline-time" datetime="{{ date('Y') }}">{{ date('F d, Y') }}</time>
                                            <p class="post-inline-title">Welcome to CourtSide Stats</p>
                                        </article>
                                        <article class="post-inline">
                                            <time class="post-inline-time" datetime="{{ date('Y') }}">{{ date('F d, Y') }}</time>
                                            <p class="post-inline-title">Track your games in real-time</p>
                                        </article>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="rd-navbar-panel-item rd-navbar-panel-item-right">
                        <ul class="list-inline list-inline-bordered">
                            @auth
                            <li class="dropdown">
                                <a class="link link-icon link-icon-left link-classic dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                                    <span class="icon fl-bigmug-line-user144"></span>
                                    <span class="link-icon-text">{{ Auth::user()->name }}</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile') }}">My Profile</a>
                                    </li>
                                    
                                    @if(session('firebase_is_admin') === true)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.dashboard') }}" style="color: #ffc107; font-weight: bold;">
                                            <i class="fa fa-shield me-2"></i> Admin Panel
                                        </a>
                                    </li>
                                    @endif

                                    <li><a class="dropdown-item" href="">Create Team</a></li>
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item" style="width: 100%; text-align: left;">
                                                Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                            @else
                            <li>
                                <a class="link link-icon link-icon-left link-classic" href="{{ route('login') }}">
                                    <span class="icon fl-bigmug-line-login12"></span>
                                    <span class="link-icon-text">Login</span>
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </div>
                    
                    <div class="rd-navbar-collapse-toggle rd-navbar-fixed-element-1"
                        data-rd-navbar-toggle=".rd-navbar-collapse"><span></span></div>
                </div>
            </div>
            
            <div class="rd-navbar-main">
                <div class="rd-navbar-main-top">
                    {{-- ADDED: d-flex justify-content-center to center the logo --}}
                    <div class="rd-navbar-main-container container d-flex justify-content-center align-items-center">
                        
                        <div class="rd-navbar-brand">
                            <a class="brand" href="./">
                                <img class="brand-logo" src="{{ asset('assets/images/main-logo.png') }}" alt="" width="95" height="126" />
                            </a>
                        </div>
                        
                    </div>
                </div>
                
                <div class="rd-navbar-main-bottom rd-navbar-darker">
                    <div class="rd-navbar-main-container container">
                        <ul class="rd-navbar-nav">
                            <li class="rd-nav-item {{ request()->is('/') ? 'active' : '' }}"><a class="rd-nav-link" href="/">Home</a></li>
                            <li class="rd-nav-item {{ Route::is('game.list') ? 'active' : '' }}"><a class="rd-nav-link" href="{{ route('game.list') }}">Game overview</a></li>
                            <li class="rd-nav-item"><a class="rd-nav-link" href="{{ route('tournaments.index') }}">Tournament</a></li>
                            <li class="rd-nav-item"><a class="rd-nav-link" href="{{ route('subscription.show') }}">Subscription</a></li>
                            {{-- <li class="rd-nav-item {{ Route::is('teams.index') ? 'active' : '' }}">
                                <a class="rd-nav-link" href="{{ route('teams.index') }}">My Teams</a>
                            </li> --}}
                            <li class="rd-nav-item {{ Route::is('teams.*') ? 'active' : '' }}">
                                <a class="rd-nav-link" href="#">Teams</a>
                                {{-- The class 'rd-menu rd-navbar-dropdown' is standard for this template's dropdowns --}}
                                <ul class="rd-menu rd-navbar-dropdown">
                                    <li class="rd-dropdown-item">
                                        <a class="rd-dropdown-link" href="{{ route('teams.index') }}">Team List</a>
                                    </li>
                                    {{-- <li class="rd-dropdown-item">
                                        <a class="rd-dropdown-link" href="">My Teams</a>
                                    </li> --}}
                                </ul>
                            </li>
                        </ul>
                        <div class="rd-navbar-main-element">
                            <ul class="list-inline list-inline-sm">
                                <li><a class="icon icon-xs icon-light fa fa-facebook" href="#"></a></li>
                                <li><a class="icon icon-xs icon-light fa fa-twitter" href="#"></a></li>
                                <li><a class="icon icon-xs icon-light fa fa-google-plus" href="#"></a></li>
                                <li><a class="icon icon-xs icon-light fa fa-instagram" href="#"></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</header>