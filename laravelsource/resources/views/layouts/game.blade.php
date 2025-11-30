<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>@yield('title', 'The Last Stand: Dead Zone')</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Security-Policy" content="default-src * 'unsafe-inline' 'unsafe-eval' data: blob:;">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <base href="{{ config('app.url') }}/">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ config('app.url') }}/assets/favicon.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="{{ config('app.url') }}/assets/favicon.ico" />

    <!-- Styles -->
    <link href="{{ config('app.url') }}/assets/css/screen.css" rel="stylesheet" type="text/css" />

    <!-- SWFObject for Flash -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/swfobject/2.2/swfobject.min.js"
        integrity="sha512-INjccm+ffMBD7roophHluNrqwX0TLzZSEUPX2omxJP78ho8HbymItbcdh3HvgznbxeBhwcuqd6BnkBvdXeb1pg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>

    <!-- Game Scripts -->
    <script type="text/javascript" src="{{ config('app.url') }}/assets/js/game.js"></script>
    <script id="publishingnetwork" type="text/javascript" async src="{{ config('app.url') }}/assets/js/PublishingNetwork.js"></script>
</head>
<body>
    @yield('content')
</body>
</html>
