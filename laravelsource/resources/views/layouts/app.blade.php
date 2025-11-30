<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Deadzone Revive')</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/js/all.min.js"
        integrity="sha512-6BTOlkauINO65nLhXhthZMtepgJSghyimIalb+crKRPhvhmsCdnIuGcVbR5/aQY2A+260iC1OPy1oCdB6pSSwQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
        crossorigin="anonymous"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Polyfills for older browsers -->
    <script nomodule src="https://cdn.jsdelivr.net/npm/promise-polyfill@8/dist/polyfill.min.js"></script>
    <script nomodule src="https://cdn.jsdelivr.net/npm/whatwg-fetch@3/dist/fetch.umd.js"></script>

    @stack('head-scripts')

    <style>
        /* Animations */
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.5; }
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(220, 38, 38, 0.3), 0 0 40px rgba(220, 38, 38, 0.1); }
            50% { box-shadow: 0 0 30px rgba(220, 38, 38, 0.5), 0 0 60px rgba(220, 38, 38, 0.2); }
        }

        @keyframes scanline {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(100%); }
        }

        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        /* Background */
        .bg-animated {
            background: linear-gradient(45deg, #000000, #1a0000, #000000, #0a0000);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }

        .bg-dots {
            background-image: radial-gradient(circle, rgb(220 38 38 / 77%) 1px, transparent 1px);
            background-size: 30px 30px;
            animation: pulse 8s ease-in-out infinite;
        }

        /* Form Container */
        .form-container {
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid rgba(220, 38, 38, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }

        /* Input Fields */
        .input-field {
            background: rgba(0, 0, 0, 0.5) !important;
            border: 1px solid rgba(75, 85, 99, 0.5) !important;
            transition: all 0.3s ease;
            position: relative;
        }

        .input-field:focus {
            background: rgba(0, 0, 0, 0.7) !important;
            border-color: #dc2626 !important;
            box-shadow: 0 0 20px rgba(220, 38, 38, 0.3),
                        inset 0 0 20px rgba(220, 38, 38, 0.05);
        }

        .input-field:hover {
            border-color: rgba(220, 38, 38, 0.5) !important;
        }

        /* Button Styles */
        .btn-primary {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.4);
            transition: all 0.3s ease;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Social Buttons */
        .social-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .social-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(109, 109, 109, 0.1);
            transform: translate(-50%, -50%);
            transition: width 0.5s ease, height 0.5s ease;
        }

        .social-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .social-btn:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        /* Logo Animation */
        .title-underline {
            position: relative;
            display: inline-block;
        }

        .title-underline::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: #dc2626;
            border-radius: 2px;
        }

        /* Alert Styles */
        .alert {
            position: relative;
            overflow: hidden;
            border-left: 4px solid;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Checkbox Custom */
        .custom-checkbox {
            position: relative;
            cursor: pointer;
        }

        .custom-checkbox input:checked + .checkbox-box::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(1);
            color: white;
            font-weight: bold;
            animation: checkPop 0.3s ease-out;
        }

        @keyframes checkPop {
            0% { transform: translate(-50%, -50%) scale(0); }
            50% { transform: translate(-50%, -50%) scale(1.2); }
            100% { transform: translate(-50%, -50%) scale(1); }
        }

        /* Browser Compatibility */
        @supports not (animation: gradientShift 15s ease infinite) {
            .bg-animated { background: #000000; }
        }

        @media all and (-ms-high-contrast: none), (-ms-high-contrast: active) {
            .bg-animated { background: #000000; }
            .form-container { background: rgba(0, 0, 0, 0.9); }
        }

        /* Flexbox Prefixes for older browsers */
        .flex {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
        }

        .items-center {
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
        }

        .justify-center {
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
        }

        .justify-between {
            -webkit-box-pack: justify;
            -ms-flex-pack: justify;
            justify-content: space-between;
        }

        /* Border Radius Prefixes */
        input, button, .form-container {
            -webkit-border-radius: 0.5rem;
            -moz-border-radius: 0.5rem;
            border-radius: 0.5rem;
        }

        /* Transition Prefixes */
        input, button, a {
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            -o-transition: all 0.3s ease;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="text-white min-h-screen">
    @yield('content')

    <script type="text/javascript">
        window.API_BASE_URL = "{{ config('app.api_base_url', env('API_BASE_URL')) }}";
    </script>

    @stack('scripts')
</body>
</html>
