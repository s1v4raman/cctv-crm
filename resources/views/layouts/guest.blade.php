<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#f5f5f5]">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Precision IT Systems') }}</title>

        <!-- Favicon / App Icon -->
        <link rel="icon" type="image/png" href="{{ asset('logo.png') }}?v={{ filemtime(public_path('logo.png')) }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ filemtime(public_path('favicon.ico')) }}">
        <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">

        <style>
            :root {
                --crm-accent: #2563eb !important;
                --crm-accent-hover: #1d4ed8 !important;
                --brand-blue: #2563eb !important;
                --brand-blue-hover: #1d4ed8 !important;
            }
            html, body {
                font-family: Arial, sans-serif !important;
                background-color: #f5f5f5 !important;
                color: #222222 !important;
                font-size: 14px !important;
                line-height: 1.4 !important;
            }
            * { box-shadow: none !important; }
            h1, h2 {
                font-family: Arial, sans-serif !important;
                font-size: 24px !important;
                font-weight: bold !important;
                color: #222222 !important;
            }
            h3 {
                font-family: Arial, sans-serif !important;
                font-size: 18px !important;
                font-weight: bold !important;
                color: #222222 !important;
            }
            label {
                font-family: Arial, sans-serif !important;
                font-size: 14px !important;
                font-weight: normal !important;
                color: #555555 !important;
                text-transform: none !important;
            }
            input {
                font-family: Arial, sans-serif !important;
                font-size: 14px !important;
                border: 1px solid #cccccc !important;
                border-radius: 4px !important;
            }
            input:focus {
                border-color: #2563eb !important;
                outline: none !important;
            }
            button, .btn-primary {
                font-family: Arial, sans-serif !important;
                font-size: 14px !important;
                font-weight: normal !important;
                background-color: #2563eb !important;
                border: 1px solid #1d4ed8 !important;
                border-radius: 4px !important;
                color: #ffffff !important;
            }
            button:hover, .btn-primary:hover {
                background-color: #1d4ed8 !important;
            }
        </style>

        <!-- Tailwind CSS & Alpine.js CDN Guard -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Scripts & Styles via Vite -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#f5f5f5] text-[#222222] min-h-screen flex flex-col justify-between antialiased selection:bg-blue-600 selection:text-white">
        <!-- Minimal Brand Header -->
        <header class="border-b border-[#dddddd] bg-white px-6 py-3.5 flex justify-between items-center">
            <a href="/" class="flex items-center gap-3">
                <img src="{{ asset('logo.png') }}" alt="Precision IT Systems" class="crm-brand-logo h-10 w-10 object-contain">
                <div>
                    <span class="font-bold text-base text-[#222222] tracking-tight">Precision IT <span class="text-[#2563eb]">Systems</span></span>
                    <span class="block text-xs text-[#555555]">Security Operations</span>
                </div>
            </a>
            <a href="/" class="text-xs text-[#555555] hover:text-[#2563eb] hover:underline flex items-center gap-1 font-normal">
                <span>← Back to Storefront</span>
            </a>
        </header>

        <main class="flex-1 flex flex-col justify-center items-center py-10 px-4 sm:px-6 lg:px-8">
            <div class="w-full sm:max-w-md bg-white border border-[#dddddd] p-6 rounded-[4px]">
                {{ $slot }}
            </div>
        </main>

        <footer class="border-t border-[#dddddd] bg-white py-4 text-center text-xs text-[#555555]">
            © {{ date('Y') }} {{ config('app.name', 'Precision IT Systems') }}. All rights reserved.
        </footer>
    </body>
</html>
