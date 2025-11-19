<!-- Page Header-->
    <header class="section page-header rd-navbar-dark">
        <!-- RD Navbar-->
        <div class="rd-navbar-wrap">
            <nav class="rd-navbar rd-navbar-classic" data-layout="rd-navbar-fixed" data-sm-layout="rd-navbar-fixed"
                data-md-layout="rd-navbar-fixed" data-md-device-layout="rd-navbar-fixed"
                data-lg-layout="rd-navbar-fixed" data-lg-device-layout="rd-navbar-fixed"
                data-xl-layout="rd-navbar-static" data-xl-device-layout="rd-navbar-static"
                data-xxl-layout="rd-navbar-static" data-xxl-device-layout="rd-navbar-static"
                data-lg-stick-up-offset="166px" data-xl-stick-up-offset="166px" data-xxl-stick-up-offset="166px"
                data-lg-stick-up="true" data-xl-stick-up="true" data-xxl-stick-up="true">
                <div class="rd-navbar-panel">
                    <!-- RD Navbar Toggle-->
                    <button class="rd-navbar-toggle" data-rd-navbar-toggle=".rd-navbar-main"><span></span></button>
                    <!-- RD Navbar Panel-->
                    <div class="rd-navbar-panel-inner container">
                        <div class="rd-navbar-collapse rd-navbar-panel-item rd-navbar-panel-item-left">
                            <!-- Owl Carousel-->
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
                                        <!-- Post Inline-->
                                        <article class="post-inline">
                                            <time class="post-inline-time" datetime="2020">April 15, 2020</time>
                                            <p class="post-inline-title">Sportland vs Dream Team</p>
                                        </article>
                                        <!-- Post Inline-->
                                        <article class="post-inline">
                                            <time class="post-inline-time" datetime="2020">April 15, 2020</time>
                                            <p class="post-inline-title">Sportland vs Real Madrid</p>
                                        </article>
                                        <!-- Post Inline-->
                                        <article class="post-inline">
                                            <time class="post-inline-time" datetime="2020">April 15, 2020</time>
                                            <p class="post-inline-title">Sportland vs Barcelona</p>
                                        </article>
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
                                            @auth
                                                <a class="dropdown-item" href="{{ route('profile') }}">My Profile</a>
                                            @else
                                                <a class="dropdown-item" href="{{ route('login') }}">Login</a>
                                            @endauth
                                        </li>
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
                        <div class="rd-navbar-main-container container">
                            <!-- RD Navbar Brand-->
                            <div class="rd-navbar-brand"><a class="brand" href="./"><img class="brand-logo "
                                        src="{{ asset('assets/images/main-logo.png') }}" alt="" width="95"
                                        height="126" /></a>
                            </div>
                            <!-- RD Navbar List-->
                            <ul class="rd-navbar-list">
                                <li class="rd-navbar-list-item"><a class="rd-navbar-list-link" href="#"><img
                                            src="{{ asset('assets/images/partners-1-inverse-75x42.png') }}" alt="" width="75"
                                            height="42" /></a></li>
                                <li class="rd-navbar-list-item"><a class="rd-navbar-list-link" href="#"><img
                                            src="{{ asset('assets/images/partners-2-inverse-88x45.png') }}" alt="" width="88"
                                            height="45" /></a></li>
                                <li class="rd-navbar-list-item"><a class="rd-navbar-list-link" href="#"><img
                                            src="{{ asset('assets/images/partners-3-inverse-79x52.png') }}" alt="" width="79"
                                            height="52" /></a></li>
                            </ul>
                            <!-- RD Navbar Search-->
                            <div class="rd-navbar-search">
                                <button class="rd-navbar-search-toggle"
                                    data-rd-navbar-toggle=".rd-navbar-search"><span></span></button>
                                <form class="rd-search" action="#" data-search-live="rd-search-results-live"
                                    method="GET">
                                    <div class="form-wrap">
                                        <label class="form-label" for="rd-navbar-search-form-input">Enter your
                                            search request
                                            here...</label>
                                        <input class="rd-navbar-search-form-input form-input"
                                            id="rd-navbar-search-form-input" type="text" name="s"
                                            autocomplete="off">
                                        <div class="rd-search-results-live" id="rd-search-results-live"></div>
                                    </div>
                                    <button class="rd-search-form-submit fl-budicons-launch-search81"
                                        type="submit"></button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="rd-navbar-main-bottom rd-navbar-darker">
                        <div class="rd-navbar-main-container container">
                            <!-- RD Navbar Nav-->
                            <ul class="rd-navbar-nav">
                                <li class="rd-nav-item {{ request()->is('/') ? 'active' : '' }}"><a class="rd-nav-link" href="/">Home</a>
                                </li>
                                <li class="rd-nav-item {{ Route::is('game.list') || Route::is('game.overview.specific') ? 'active' : '' }}"><a class="rd-nav-link"
                                        href="{{ route('game.list') }}">Game overview</a>
                                </li>
                                <li class="rd-nav-item"><a class="rd-nav-link"
                                        href="{{ route('tournaments.index') }}">Tournament</a>
                                </li>
                                <li class="rd-nav-item"><a class="rd-nav-link"
                                        href="{{ route('subscription.show') }}">Subscription</a>
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