<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Reset Password - {{ request()->getPort() == 8001 ? 'SecureVision ERP Operations' : 'PathSoft Customer Portal' }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS & Alpine.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        heading: ['"Outfit"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>

<body class="h-full antialiased font-sans text-slate-800 bg-white">

    <div class="min-h-full flex flex-col lg:flex-row">

        {{-- ========================================================================= --}}
        {{-- LEFT COLUMN: CLEAN WHITE FORM                                            --}}
        {{-- ========================================================================= --}}
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-6 sm:p-8 lg:p-10 xl:p-12 bg-white min-h-screen lg:min-h-full">
            
            {{-- Brand Logo Header --}}
            <div class="flex items-center justify-between mb-4 lg:mb-6">
                <a href="{{ request()->getPort() == 8001 ? route('login') : route('home') }}" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/25 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xl font-extrabold font-heading text-slate-900 tracking-tight">Path<span class="text-blue-600">Soft</span></span>
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">SecureVision CRM</span>
                    </div>
                </a>

                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 transition-colors">
                    <span>← Back to Sign In</span>
                </a>
            </div>

            {{-- Main Form Container --}}
            <div class="max-w-md w-full mx-auto my-auto py-4">
                
                <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 font-heading tracking-tight mb-2">
                    Forgot Password?
                </h1>
                <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                    No problem. Enter your registered email address and we will email you a password reset link to create a new one.
                </p>

                <!-- Session Status Notification -->
                @if (session('status'))
                    <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold flex items-center gap-2">
                        <span class="text-base">✓</span>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold space-y-1">
                        @foreach ($errors->all() as $error)
                            <div>• {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <!-- Password Reset Form -->
                <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-800 mb-1.5">Email Address</label>
                        <input id="email" type="email" name="email" 
                               value="{{ old('email') }}" 
                               required autofocus autocomplete="username" 
                               placeholder="Enter your registered email" 
                               class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-slate-900 placeholder-slate-400 bg-white transition-all shadow-xs">
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full py-3.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-lg shadow-blue-500/25 flex items-center justify-center space-x-2 transition-all hover:scale-[1.01] active:scale-[0.99]">
                            <span>Email Password Reset Link</span>
                            <span class="text-base leading-none">→</span>
                        </button>
                    </div>
                </form>

                <!-- Return to Login Link -->
                <div class="text-center mt-6 text-xs text-slate-500">
                    <span>Remember your credentials? </span>
                    <a href="{{ route('login') }}" class="text-pink-600 font-bold hover:underline">Log in now</a>
                </div>

            </div>

            {{-- Left Footer Copyright --}}
            <div class="text-xs text-slate-400 text-center lg:text-left mt-6">
                &copy; {{ date('Y') }} PathSoft CCTV & Surveillance. All rights reserved.
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- RIGHT COLUMN: SOFT BLUE BACKGROUND + ILLUSTRATION + ACADEMY CARD           --}}
        {{-- ========================================================================= --}}
        <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-[#eff4fc] via-[#f0f3fa] to-[#e6eef9] p-8 lg:p-10 xl:p-12 flex-col justify-between relative overflow-hidden border-l border-slate-100 min-h-screen lg:min-h-full">
            
            {{-- Top Right Academy Widget Card --}}
            <div class="self-end max-w-sm text-right space-y-2 z-10">
                <div class="flex items-center justify-end space-x-2 text-slate-900 font-bold text-sm font-heading">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span>{{ request()->getPort() == 8001 ? 'Operations Academy' : 'Customer Academy' }}</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    @if(request()->getPort() == 8001)
                        We've got tips, telemetry and diagnostic tools to keep your security fleet running 24/7.
                    @else
                        We've got tips and tools to keep your business and premises safe while you're out of the office.
                    @endif
                </p>
                <div>
                    <a href="https://wa.me/916380920970" target="_blank"
                       class="inline-block px-4 py-1.5 rounded-lg border border-slate-300 hover:border-slate-800 hover:bg-white text-[11px] font-bold text-slate-700 tracking-wider uppercase transition-all shadow-xs">
                        START ACADEMY
                    </a>
                </div>
            </div>

            {{-- Center Illustration Container (Full Uncropped Display) --}}
            <div class="my-auto py-3 flex flex-col items-center justify-center relative z-10 w-full">
                <div class="absolute w-96 h-96 bg-blue-300/20 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Full Uncropped Image Card -->
                <div class="relative w-full max-w-xl xl:max-w-2xl rounded-3xl overflow-hidden shadow-xl border border-white/80 bg-white/95 backdrop-blur-md p-2.5 sm:p-3 group transition-all duration-300 hover:shadow-2xl">
                    @if(request()->getPort() == 8001)
                        <img src="{{ asset('images/staff_erp_illustration.jpg') }}" 
                             alt="PathSoft CCTV Enterprise ERP Operations"
                             class="w-full h-auto object-contain rounded-2xl block select-none">
                    @else
                        <img src="{{ asset('images/customer_login_illustration.jpg') }}" 
                             alt="PathSoft 24/7 Customer Support & Help Desk"
                             class="w-full h-auto max-h-[480px] object-contain mx-auto rounded-2xl block select-none">
                    @endif
                </div>

                <!-- Status Ribbon Below -->
                <div class="w-full max-w-xl xl:max-w-2xl flex items-center justify-between mt-3 px-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/90 backdrop-blur-md border border-slate-200/80 shadow-xs text-xs font-bold text-slate-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Secure Password Recovery • Encrypted Token</span>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/90 backdrop-blur-md border border-slate-200/80 shadow-xs text-xs font-bold text-emerald-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Auth Shield Active</span>
                    </div>
                </div>
            </div>

            {{-- Bottom Right Tagline --}}
            <div class="flex items-center justify-between text-xs text-slate-400 z-10 pt-4">
                <span>Certified Enterprise Security Operations</span>
                <span class="font-mono text-[10px]">ISO 27001 • CCTV Telemetry</span>
            </div>

        </div>

    </div>

</body>
</html>
