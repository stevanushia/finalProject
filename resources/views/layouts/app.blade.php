<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>
<body>
    <div class="preloader loaded">
        <div class="preloader-body">
            <div class="preloader-item"></div>
        </div>
    </div>
    <!-- Page-->
    <div class="page">
    @include('partials.header')

    <main>
        @yield('content') {{-- This will be replaced by actual page content --}}
    </main>

    @include('partials.footer')
    <!-- Modal Video-->
        <div class="modal modal-video fade" id="modal1" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button class="close" type="button" data-dismiss="" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>modal
                    </div>
                    <div class="modal-body">
                        <div class="embed-responsive embed-responsive-16by9">
                            <iframe class="embed-responsive-item" width="560" height="315"
                                data-src="https://www.youtube.com/embed/uSzNA2_y46c"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Global Mailform Output-->
    <div class="snackbars" id="form-output-global"></div>

    {{-- Add any extra scripts here --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/core.min.js') }}"></script>
    <script src="{{ asset('assets/js/script.js') }}"></script>
    @stack('scripts')
    
    <!-- Make sure jQuery and your template scripts are loaded -->
    <script>\
        // Ensure preloader is hidden on page load if template script fails
        $(document).ready(function() {
            // Add loaded class to preloader if it doesn't have it
            setTimeout(function() {
                $('.preloader').addClass('loaded');
            }, 100);
        });
        
        $(window).on('load', function() {
            // Force hide preloader after page load
            $('.preloader').addClass('loaded');
        });
    </script>
</body>
</html>