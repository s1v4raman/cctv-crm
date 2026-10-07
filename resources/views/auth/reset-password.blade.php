<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Set New Password - {{ request()->getPort() == 8001 ? 'Precision IT Systems ERP Operations' : 'Precision IT Systems Customer Portal' }}</title>

    <!-- Signature Brand Colors Permanently Locked (Independent from CRM user themes) -->
    <style>
        :root {
            --crm-accent: #2563eb !important;
            --crm-accent-hover: #1d4ed8 !important;
            --crm-accent-shadow: rgba(37, 99, 235, 0.25) !important;
            --brand-blue: #2563eb !important;
            --brand-blue-hover: #1d4ed8 !important;
        }
    </style>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Scripts & Styles via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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

<body class="h-full antialiased font-sans text-slate-800 bg-white"
      x-data="{ showPassword: false }">

    <div class="min-h-full flex flex-col lg:flex-row">

        {{-- ========================================================================= --}}
        {{-- LEFT COLUMN: CLEAN WHITE FORM                                            --}}
        {{-- ========================================================================= --}}
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-6 sm:p-8 lg:p-10 xl:p-12 bg-white min-h-screen lg:min-h-full">
            
            {{-- Brand Logo Header --}}
            <div class="flex items-center justify-between mb-4 lg:mb-6">
                <a href="{{ request()->getPort() == 8001 ? route('login') : route('home') }}" class="flex items-center gap-3.5 group">
                    <img src="{{ asset('logo.png') }}" alt="Precision IT Systems" class="crm-brand-logo h-14 w-14 sm:h-16 sm:w-16 object-contain shrink-0 drop-shadow-xs transition-transform group-hover:scale-105">
                    <div>
                        <span class="text-xl sm:text-2xl font-black font-heading text-slate-900 tracking-tight leading-tight">Precision IT <span class="crm-brand-accent-text text-blue-600">Systems</span></span>
                        <span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 font-mono mt-0.5">Surveillance &amp; Operations CRM</span>
                    </div>
                </a>

                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 transition-colors">
                    <span>← Back to Sign In</span>
                </a>
            </div>

            {{-- Main Form Container --}}
            <div class="max-w-md w-full mx-auto my-auto py-4">
                
                <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 font-heading tracking-tight mb-2">
                    Set New Password
                </h1>
                <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                    Please create a strong new password with at least 8 characters to secure your account.
                </p>

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold space-y-1">
                        @foreach ($errors->all() as $error)
                            <div>• {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <!-- Password Reset Form -->
                <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Email Address</label>
                        <input id="email" type="email" name="email" 
                               value="{{ old('email', $request->email) }}" 
                               required autofocus autocomplete="username" 
                               class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-slate-900 placeholder-slate-400 bg-white transition-all shadow-xs">
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">New Password</label>
                        <input id="password" :type="showPassword ? 'text' : 'password'" name="password" 
                               required autocomplete="new-password" 
                               placeholder="At least 8 characters" 
                               class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-slate-900 placeholder-slate-400 bg-white transition-all shadow-xs">
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1.5">Confirm New Password</label>
                        <input id="password_confirmation" :type="showPassword ? 'text' : 'password'" name="password_confirmation" 
                               required autocomplete="new-password" 
                               placeholder="Re-type new password" 
                               class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-slate-900 placeholder-slate-400 bg-white transition-all shadow-xs">
                    </div>

                    <!-- Show Password Checkbox -->
                    <div class="flex items-center">
                        <label for="toggle_pass" class="flex items-center space-x-2 cursor-pointer select-none">
                            <input id="toggle_pass" type="checkbox" x-model="showPassword" 
                                   class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            <span class="text-xs text-slate-600">Show Passwords</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full py-3.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-lg shadow-blue-500/25 flex items-center justify-center space-x-2 transition-all hover:scale-[1.01] active:scale-[0.99]">
                            <span>Reset Password</span>
                            <span class="text-base leading-none">→</span>
                        </button>
                    </div>
                </form>

            </div>

            {{-- Left Footer Copyright --}}
            <div class="text-xs text-slate-400 text-center lg:text-left mt-6">
                &copy; {{ date('Y') }} Precision IT Systems. All rights reserved.
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
                    <a href="https://wa.me/919677257774" target="_blank"
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
