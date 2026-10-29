<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Set New Password - {{ request()->getPort() == 8001 ? 'Precision IT Systems ERP Operations' : 'Precision IT Systems Customer Portal' }}</title>

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
</head>

<body class="h-full antialiased font-sans text-slate-800 bg-white"
      x-data="{ showPassword: false }">

    <div class="min-h-full flex flex-col lg:flex-row">

        {{-- LEFT COLUMN: CLEAN FORM --}}
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-6 sm:p-8 lg:p-10 xl:p-12 bg-white min-h-screen lg:min-h-full">
            
            {{-- Brand Logo Header --}}
            <div class="flex items-center justify-between mb-4 lg:mb-6">
                <a href="{{ request()->getPort() == 8001 ? route('login') : route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('logo.png') }}?v={{ filemtime(public_path('logo.png')) }}" alt="Precision IT Systems" class="h-9 w-auto max-w-[180px] object-contain shrink-0">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider pl-2 border-l border-slate-300">Operations</span>
                </a>

                <a href="{{ route('login') }}" class="text-xs font-medium text-slate-600 hover:text-blue-600 hover:underline">
                    ← Back to Sign In
                </a>
            </div>

            {{-- Main Form Container --}}
            <div class="max-w-md w-full mx-auto my-auto py-4">
                
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight mb-1">
                    Set New Password
                </h1>
                <p class="text-xs text-slate-500 mb-5 leading-relaxed">
                    Create a strong new password with at least 8 characters.
                </p>

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="mb-4 p-3 rounded-[4px] bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold space-y-1">
                        @foreach ($errors->all() as $error)
                            <div>• {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <!-- Password Reset Form -->
                <form method="POST" action="{{ route('password.store') }}" class="space-y-3.5">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                        <input id="email" type="email" name="email" 
                               value="{{ old('email', $request->email) }}" 
                               required autofocus autocomplete="username" 
                               class="w-full px-3 py-2 text-sm rounded-[4px] border border-slate-300 focus:outline-none focus:border-blue-600 text-slate-900 placeholder-slate-400 bg-white">
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
                        <input id="password" :type="showPassword ? 'text' : 'password'" name="password" 
                               required autocomplete="new-password" 
                               placeholder="At least 8 characters" 
                               class="w-full px-3 py-2 text-sm rounded-[4px] border border-slate-300 focus:outline-none focus:border-blue-600 text-slate-900 placeholder-slate-400 bg-white">
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm New Password</label>
                        <input id="password_confirmation" :type="showPassword ? 'text' : 'password'" name="password_confirmation" 
                               required autocomplete="new-password" 
                               placeholder="Re-type new password" 
                               class="w-full px-3 py-2 text-sm rounded-[4px] border border-slate-300 focus:outline-none focus:border-blue-600 text-slate-900 placeholder-slate-400 bg-white">
                    </div>

                    <!-- Show Password Checkbox -->
                    <div class="flex items-center">
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
                            <span>Reset Password</span>
                        </button>
                    </div>
                </form>

            </div>

            {{-- Left Footer Copyright --}}
            <div class="text-xs text-slate-400 text-center lg:text-left mt-6">
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
                        Secure Password Recovery • Encrypted Token
                    </div>

                    <div class="font-bold text-blue-600">
                        Auth Shield Active
                    </div>
                </div>
            </div>

            {{-- Bottom Right Tagline --}}
            <div class="flex items-center justify-between text-xs text-slate-500 pt-4">
                <span>Enterprise Security Operations</span>
                <span class="font-mono text-xs">Role-based secure access</span>
            </div>

        </div>

    </div>

</body>
</html>
