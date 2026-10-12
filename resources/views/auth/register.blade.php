<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Create Account - {{ request()->getPort() == 8001 ? 'Precision IT Systems ERP Operations' : 'Precision IT Systems Customer Portal' }}</title>

    <style>
        :root {
            --crm-accent: #2563eb !important;
            --crm-accent-hover: #1d4ed8 !important;
            --brand-blue: #2563eb !important;
            --brand-blue-hover: #1d4ed8 !important;
        }
        * { box-shadow: none !important; }
    </style>

    <!-- Scripts & Styles via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Tailwind CSS & Alpine.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="h-full antialiased font-sans text-slate-800 bg-white"
      x-data="{ showPassword: false }">

    <div class="min-h-full flex flex-col lg:flex-row">

        {{-- LEFT COLUMN: CLEAN FORM --}}
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-6 sm:p-8 lg:p-10 xl:p-12 bg-white min-h-screen lg:min-h-full">
            
            {{-- Brand Logo Header --}}
            <div class="flex items-center justify-between mb-4 lg:mb-6">
                <a href="{{ request()->getPort() == 8001 ? route('login') : route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('logo.png') }}" alt="Precision IT Systems" class="crm-brand-logo h-12 w-12 object-contain shrink-0">
                    <div>
                        <span class="text-xl font-bold text-slate-900 tracking-tight leading-tight">Precision IT <span class="text-blue-600">Systems</span></span>
                        <span class="block text-xs font-medium text-slate-500 mt-0.5">Surveillance &amp; Operations CRM</span>
                    </div>
                </a>

                @if(request()->getPort() == 8001)
                    <span class="text-xs font-bold text-blue-700">
                        Staff ERP (8001)
                    </span>
                @else
                    <a href="{{ route('home') }}" class="text-xs font-medium text-slate-600 hover:text-blue-600 hover:underline">
                        ← Back to Store
                    </a>
                @endif
            </div>

            {{-- Main Form Container --}}
            <div class="max-w-md w-full mx-auto my-auto py-2">
                
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight mb-1">
                    Create Account
                </h1>
                <p class="text-xs text-slate-500 mb-5">
                    Track CCTV cameras, warranties, quotations and maintenance tickets.
                </p>

                <!-- Session / Error Alerts -->
                @if (session('status'))
                    <div class="mb-4 p-3 rounded-[4px] bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-3 rounded-[4px] bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
                        @foreach ($errors->all() as $error)
                            <div>• {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <!-- Google OAuth Sign Up Link -->
                <a href="{{ route('auth.google', ['portal' => 'customer', 'mode' => 'register']) }}" 
                   class="w-full flex items-center justify-center space-x-2 py-2.5 px-4 rounded-[4px] border border-slate-300 hover:bg-slate-50 text-xs font-medium text-slate-700 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                    <span>Sign up with Google</span>
                </a>

                <!-- Divider: OR REGISTER WITH EMAIL -->
                <div class="relative flex items-center justify-center my-4">
                    <div class="border-t border-slate-200 w-full"></div>
                    <span class="bg-white px-3 text-xs font-medium text-slate-400 uppercase tracking-wider whitespace-nowrap">
                        OR REGISTER WITH EMAIL
                    </span>
                    <div class="border-t border-slate-200 w-full"></div>
                </div>

                <!-- Registration Form -->
                <form method="POST" action="{{ route('register') }}" class="space-y-3">
                    @csrf

                    <!-- Full Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Full Name *</label>
                        <input id="name" type="text" name="name" 
                               value="{{ old('name') }}" 
                               required autofocus autocomplete="name" 
                               placeholder="e.g. John Doe" 
                               class="w-full px-3 py-2 text-sm rounded-[4px] border border-slate-300 focus:outline-none focus:border-blue-600 text-slate-900 placeholder-slate-400 bg-white">
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address *</label>
                        <input id="email" type="email" name="email" 
                               value="{{ old('email') }}" 
                               required autocomplete="username" 
                               placeholder="name@example.com" 
                               class="w-full px-3 py-2 text-sm rounded-[4px] border border-slate-300 focus:outline-none focus:border-blue-600 text-slate-900 placeholder-slate-400 bg-white">
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Mobile / Phone <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <input id="phone" type="text" name="phone" 
                               value="{{ old('phone') }}" 
                               placeholder="e.g. +91 98765 43210" 
                               class="w-full px-3 py-2 text-sm rounded-[4px] border border-slate-300 focus:outline-none focus:border-blue-600 text-slate-900 placeholder-slate-400 bg-white">
                    </div>

                    <!-- Password Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password *</label>
                            <input id="password" :type="showPassword ? 'text' : 'password'" name="password" 
                                   required autocomplete="new-password" 
                                   placeholder="At least 8 chars" 
                                   class="w-full px-3 py-2 text-sm rounded-[4px] border border-slate-300 focus:outline-none focus:border-blue-600 text-slate-900 placeholder-slate-400 bg-white">
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm Password *</label>
                            <input id="password_confirmation" :type="showPassword ? 'text' : 'password'" name="password_confirmation" 
                                   required autocomplete="new-password" 
                                   placeholder="Re-type password" 
                                   class="w-full px-3 py-2 text-sm rounded-[4px] border border-slate-300 focus:outline-none focus:border-blue-600 text-slate-900 placeholder-slate-400 bg-white">
                        </div>
                    </div>

                    <!-- Show Password Checkbox -->
                    <div class="flex items-center pt-0.5">
                        <label for="toggle_pass" class="flex items-center space-x-2 cursor-pointer select-none">
                            <input id="toggle_pass" type="checkbox" x-model="showPassword" 
                                   class="w-4 h-4 rounded-[2px] border-slate-300 text-blue-600 focus:ring-0 cursor-pointer">
                            <span class="text-xs text-slate-600">Show Passwords</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2 flex justify-end">
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-[4px] transition-colors cursor-pointer">
                            <span>Create Account</span>
                        </button>
                    </div>
                </form>

                <!-- Bottom Helper Link -->
                <div class="text-center mt-4 text-xs text-slate-500">
                    <span>Already have an account? </span>
                    <a href="{{ route('login') }}" class="text-blue-600 font-bold hover:underline">Log in</a>
                </div>

            </div>

            {{-- Left Footer Copyright --}}
            <div class="text-xs text-slate-400 text-center lg:text-left mt-4">
                &copy; {{ date('Y') }} Precision IT Systems. All rights reserved.
            </div>
        </div>

        {{-- RIGHT COLUMN: CLEAN SLATE WORKSPACE CARD --}}
        <div class="hidden lg:flex w-1/2 bg-slate-50 p-8 lg:p-10 xl:p-12 flex-col justify-between relative border-l border-slate-200 min-h-screen lg:min-h-full">
            
            {{-- Top Right Academy Card --}}
            <div class="self-end max-w-sm text-right space-y-1.5">
                <div class="text-slate-900 font-bold text-sm">
                    {{ request()->getPort() == 8001 ? 'Operations Academy' : 'Customer Academy' }}
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    @if(request()->getPort() == 8001)
                        Telemetry and diagnostic resources for field technicians and dispatch operators.
                    @else
                        Guides and tools to manage your surveillance system and service requests.
                    @endif
                </p>
                <div>
                    <a href="https://wa.me/919677257774" target="_blank"
                       class="inline-block px-3 py-1 rounded-[4px] border border-slate-300 hover:bg-white text-xs font-semibold text-slate-700 transition-colors">
                        Academy Support
                    </a>
                </div>
            </div>

            {{-- Center Illustration Container --}}
            <div class="my-auto py-3 flex flex-col items-center justify-center w-full">
                <div class="w-full max-w-xl xl:max-w-2xl rounded-[4px] border border-slate-300 bg-white p-2">
                    @if(request()->getPort() == 8001)
                        <img src="{{ asset('images/staff_erp_illustration.jpg') }}" 
                             alt="Precision IT CCTV Enterprise ERP Operations"
                             class="w-full h-auto object-contain rounded-[4px] block select-none">
                    @else
                        <img src="{{ asset('images/customer_login_illustration.jpg') }}" 
                             alt="Precision IT 24/7 Customer Support & Help Desk"
                             class="w-full h-auto max-h-[480px] object-contain mx-auto rounded-[4px] block select-none">
                    @endif
                </div>

                <!-- Status Ribbon Below -->
                <div class="w-full max-w-xl xl:max-w-2xl flex items-center justify-between mt-3 px-1 text-xs text-slate-600">
                    <div class="font-medium">
                        {{ request()->getPort() == 8001 ? '2,480+ Cameras Active • 99.9% Uptime' : '24/7 Customer Care • Instant Support' }}
                    </div>

                    <div class="font-bold text-blue-600">
                        {{ request()->getPort() == 8001 ? 'Telemetry Live' : 'Support Online' }}
                    </div>
                </div>
            </div>

            {{-- Bottom Right Tagline --}}
            <div class="flex items-center justify-between text-xs text-slate-500 pt-4">
                <span>Enterprise Security Operations</span>
                <span class="font-mono text-xs">ISO 27001 Certified</span>
            </div>

        </div>

    </div>

</body>
</html>
