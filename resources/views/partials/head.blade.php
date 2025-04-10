<!-- Site Title -->
<title>@yield('title', 'Default Title')</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" href="{{ asset('assets/images/favicon.ico') }}" type="image/x-icon">

<!-- Stylesheets -->
<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/fonts.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
