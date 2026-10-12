@php
    if (request()->routeIs('customer.login') || request()->is('customer*') || request()->input('type') === 'customer' || request()->input('portal') === 'customer') {
        $isStaffPortal = false;
    } elseif (request()->routeIs('staff.login', 'admin.login') || request()->is('staff*', 'admin*') || request()->input('type') === 'staff' || request()->input('portal') === 'staff' || request()->getPort() == 8001) {
        $isStaffPortal = true;
    } else {
        $isStaffPortal = (request()->getPort() == 8001);
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sign In - {{ $isStaffPortal ? 'Precision IT Systems ERP Operations' : 'Precision IT Systems Customer Portal' }}</title>

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
      x-data="{ 
          isStaff: {{ $isStaffPortal ? 'true' : 'false' }},
          portal: '{{ $isStaffPortal ? 'admin' : 'customer' }}',
          showPassword: false,
          setStaff(val) {
              this.isStaff = val;
              if (val) {
                  this.portal = 'admin';
                  this.setEmailAndPass('test@example.com', 'password');
              } else {
                  this.portal = 'customer';
                  this.setEmailAndPass('customer@example.com', 'password');
              }
          },
          setEmailAndPass(email, pass) {
              const emailEl = document.getElementById('email');
              const passEl = document.getElementById('password');
              if (emailEl) emailEl.value = email;
              if (passEl) passEl.value = pass;
          },
          quickSubmitRole(email, pass, role) {
              this.portal = role;
              this.isStaff = (role !== 'customer');
              this.setEmailAndPass(email, pass);
              document.getElementById('loginForm').submit();
          }
      }">

    <div class="min-h-full flex flex-col lg:flex-row">

        {{-- LEFT COLUMN: CLEAN FORM --}}
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-6 sm:p-8 lg:p-10 xl:p-12 bg-white min-h-screen lg:min-h-full">
            
            {{-- Brand Logo Header --}}
            <div class="flex items-center justify-between mb-4 lg:mb-6">
                <a href="{{ $isStaffPortal ? route('login') : route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('logo.png') }}" alt="Precision IT Systems" class="crm-brand-logo h-12 w-12 object-contain shrink-0">
                    <div>
                        <span class="text-xl font-bold text-slate-900 tracking-tight leading-tight">Precision IT <span class="text-blue-600">Systems</span></span>
                        <span class="block text-xs font-medium text-slate-500 mt-0.5" x-text="isStaff ? 'Operations ERP' : 'Customer Surveillance Portal'">
                            {{ $isStaffPortal ? 'Operations ERP' : 'Customer Surveillance Portal' }}
                        </span>
                    </div>
                </a>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold"
                          :class="isStaff ? 'text-blue-700' : 'text-slate-700'"
                          x-text="isStaff ? 'Staff & Admin ERP' : 'Customer Portal'">
                    </span>
                </div>
            </div>

            {{-- Main Form Container --}}
            <div class="max-w-md w-full mx-auto my-auto py-2">
                
                {{-- Portal Selector Bar --}}
                <div class="flex p-1 bg-slate-100 rounded-[4px] mb-5 text-xs font-medium border border-slate-200">
                    <button type="button" 
                            @click="setStaff(false)"
                            class="flex-1 py-2 rounded-[4px] transition-colors text-center flex items-center justify-center gap-1.5"
                            :class="!isStaff ? 'bg-white text-blue-600 font-bold border border-slate-300' : 'text-slate-600 hover:text-slate-900'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Customer Portal</span>
                    </button>
                    <button type="button" 
                            @click="setStaff(true)"
                            class="flex-1 py-2 rounded-[4px] transition-colors text-center flex items-center justify-center gap-1.5"
                            :class="isStaff ? 'bg-white text-blue-600 font-bold border border-slate-300' : 'text-slate-600 hover:text-slate-900'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Staff & Admin ERP</span>
                    </button>
                </div>

                <!-- Main Title -->
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight mb-1">
                    @if($isStaffPortal)
                        <span x-text="isStaff ? 'Operations Sign In' : 'Customer Portal'">Operations Sign In</span>
                    @else
                        <span x-text="isStaff ? 'Operations Access' : 'Customer Sign In'">Customer Sign In</span>
                    @endif
                </h1>
                <p class="text-xs text-slate-500 mb-5"
                   x-text="isStaff ? 'Sign in to access administration, technician dispatch and field operations' : 'Sign in to view your camera surveillance status, service tickets and invoices'">
                    @if($isStaffPortal)
                        Sign in to access administration, technician dispatch and field operations
                    @else
                        Sign in to view your camera surveillance status, service tickets and invoices
                    @endif
                </p>

                <!-- Role Selector Tabs (When Staff Mode Active) -->
                <div x-show="isStaff" x-cloak class="flex p-1 bg-slate-100 rounded-[4px] mb-5 text-xs border border-slate-200">
                    <button type="button" 
                            class="flex-1 py-1.5 rounded-[4px] transition-colors text-center font-medium"
                            :class="portal === 'admin' ? 'bg-white text-blue-600 font-bold border border-slate-300' : 'text-slate-600 hover:text-slate-900'"
                            @click="portal = 'admin'; setEmailAndPass('test@example.com', 'password')">
                        Admin
                    </button>
                    <button type="button" 
                            class="flex-1 py-1.5 rounded-[4px] transition-colors text-center font-medium"
                            :class="portal === 'technician' ? 'bg-white text-blue-600 font-bold border border-slate-300' : 'text-slate-600 hover:text-slate-900'"
                            @click="portal = 'technician'; setEmailAndPass('bob@example.com', 'password')">
                        Technician
                    </button>
                    <button type="button" 
                            class="flex-1 py-1.5 rounded-[4px] transition-colors text-center font-medium"
                            :class="portal === 'staff' ? 'bg-white text-blue-600 font-bold border border-slate-300' : 'text-slate-600 hover:text-slate-900'"
                            @click="portal = 'staff'; setEmailAndPass('alex@example.com', 'password')">
                        Employee
                    </button>
                </div>

                <!-- Google OAuth Login Link -->
                <a href="{{ route('auth.google', ['portal' => $isStaffPortal ? 'staff' : 'customer', 'mode' => 'login']) }}" 
                   class="w-full flex items-center justify-center space-x-2 py-2.5 px-4 rounded-[4px] border border-slate-300 hover:bg-slate-50 text-xs font-medium text-slate-700 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                    <span>{{ $isStaffPortal ? 'Log in with Staff Workspace' : 'Log in with Google' }}</span>
                </a>

                <!-- Divider: OR LOGIN WITH EMAIL -->
                <div class="relative flex items-center justify-center my-5">
                    <div class="border-t border-slate-200 w-full"></div>
                    <span class="bg-white px-3 text-xs font-medium text-slate-400 uppercase tracking-wider whitespace-nowrap">
                        OR LOGIN WITH EMAIL
                    </span>
                    <div class="border-t border-slate-200 w-full"></div>
                </div>

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

                <!-- Email & Password Form -->
                <form id="loginForm" method="POST" action="{{ request()->is('staff/login', 'staff') ? route('staff.login.post') : route('login') }}" class="space-y-4">
                    @csrf

                    <!-- Email Address Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                        <input id="email" type="email" name="email" 
                               value="{{ old('email', $isStaffPortal ? 'test@example.com' : 'customer@example.com') }}" 
                               required autofocus autocomplete="username" 
                               placeholder="{{ $isStaffPortal ? 'test@example.com' : 'customer@example.com' }}" 
                               class="w-full px-3 py-2 text-sm rounded-[4px] border border-slate-300 focus:outline-none focus:border-blue-600 text-slate-900 placeholder-slate-400 bg-white">
                    </div>

                    <!-- Password Field with Show/Hide Eye Toggle -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                        <div class="relative">
                            <input id="password" :type="showPassword ? 'text' : 'password'" name="password" 
                                   value="password" required autocomplete="current-password" 
                                   placeholder="Password" 
                                   class="w-full px-3 py-2 text-sm rounded-[4px] border border-slate-300 focus:outline-none focus:border-blue-600 text-slate-900 placeholder-slate-400 bg-white pr-10">
                            <button type="button" @click="showPassword = !showPassword" 
                                    class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 focus:outline-none"
                                    title="Toggle password visibility">
                                <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="flex items-center space-x-2 cursor-pointer select-none">
                            <input id="remember_me" type="checkbox" name="remember" 
                                   class="w-4 h-4 rounded-[2px] border-slate-300 text-blue-600 focus:ring-0 cursor-pointer">
                            <span class="text-xs text-slate-600">Keep me logged in</span>
                        </label>

                        <a href="{{ route('password.request') }}" class="text-xs text-blue-600 hover:underline">
                            Forgot your password?
                        </a>
                    </div>

                    <!-- Primary Log in Button -->
                    <div class="pt-2 flex justify-end">
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-[4px] transition-colors cursor-pointer">
                            <span x-text="isStaff ? 'Log in to Operations ERP' : 'Log in to Customer Portal'">{{ $isStaffPortal ? 'Log in to Operations ERP' : 'Log in to Customer Portal' }}</span>
                        </button>
                    </div>
                </form>

                <div x-show="!isStaff" class="text-center mt-4 text-xs text-slate-500">
                    <span>New customer? </span>
                    <a href="{{ route('register') }}" class="text-blue-600 hover:underline font-bold">
                        Create an account / Sign up
                    </a>
                </div>

                <!-- Demo Credentials Helper -->
                <div class="mt-5 p-3 bg-slate-50 border border-slate-200 rounded-[4px]">
                    <div class="text-xs font-semibold text-slate-500 mb-2 flex items-center justify-between">
                        <span x-text="isStaff ? 'Staff Demo Logins' : 'Customer Demo Logins'">{{ $isStaffPortal ? 'Staff Demo Logins' : 'Customer Demo Logins' }}</span>
                        <span class="text-slate-400 font-normal">click to autofill</span>
                    </div>

                    <!-- Staff Logins -->
                    <div x-show="isStaff" x-cloak class="grid grid-cols-3 gap-1.5 text-xs">
                        <div class="p-2 bg-white rounded-[4px] border border-slate-200 cursor-pointer hover:border-blue-600 transition-colors text-center" @click="portal = 'admin'; setEmailAndPass('test@example.com', 'password')">
                            <span class="font-bold text-slate-800 block text-xs">Admin</span>
                            <span class="text-slate-500 font-mono text-[10px] block truncate">test@example.com</span>
                        </div>
                        <div class="p-2 bg-white rounded-[4px] border border-slate-200 cursor-pointer hover:border-blue-600 transition-colors text-center" @click="portal = 'technician'; setEmailAndPass('bob@example.com', 'password')">
                            <span class="font-bold text-slate-800 block text-xs">Technician</span>
                            <span class="text-slate-500 font-mono text-[10px] block truncate">bob@example.com</span>
                        </div>
                        <div class="p-2 bg-white rounded-[4px] border border-slate-200 cursor-pointer hover:border-blue-600 transition-colors text-center" @click="portal = 'staff'; setEmailAndPass('alex@example.com', 'password')">
                            <span class="font-bold text-slate-800 block text-xs">Staff</span>
                            <span class="text-slate-500 font-mono text-[10px] block truncate">alex@example.com</span>
                        </div>
                    </div>

                    <!-- Customer Logins -->
                    <div x-show="!isStaff" class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2 bg-white rounded-[4px] border border-slate-200 cursor-pointer hover:border-blue-600 transition-colors" @click="portal = 'customer'; setEmailAndPass('customer@example.com', 'password')">
                            <span class="font-bold text-slate-800 block text-xs">Demo Customer</span>
                            <span class="text-slate-500 font-mono text-[10px]">customer@example.com</span>
                        </div>
                        <div class="p-2 bg-white rounded-[4px] border border-slate-200 cursor-pointer hover:border-blue-600 transition-colors" @click="portal = 'customer'; setEmailAndPass('srinithish.p@example.com', 'password')">
                            <span class="font-bold text-slate-800 block text-xs">Srinithish P</span>
                            <span class="text-slate-500 font-mono text-[10px]">srinithish.p@example.com</span>
                        </div>
                    </div>

                    <div class="text-[11px] text-center text-slate-500 mt-2">Password for all accounts: <strong class="text-slate-700 font-mono">password</strong></div>
                </div>

                <!-- Portal Switcher Links -->
                <div class="text-center mt-5 text-xs text-slate-500 pt-3 border-t border-slate-200">
                    @if($isStaffPortal)
                        <span>Looking for Customer Portal? </span>
                        <a href="{{ route('customer.login') }}" class="text-blue-600 hover:underline font-bold">
                            Switch to Customer Login
                        </a>
                    @else
                        <span>Looking for Staff & Admin ERP? </span>
                        <a href="{{ route('staff.login') }}" class="text-blue-600 hover:underline font-bold">
                            Switch to Staff / Technician Login
                        </a>
                    @endif
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
                    {{ $isStaffPortal ? 'Operations Academy' : 'Customer Academy' }}
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    @if($isStaffPortal)
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
                    @if($isStaffPortal)
                        <img src="{{ asset('images/staff_erp_illustration.jpg') }}" 
                             alt="Precision IT CCTV Enterprise ERP Operations"
                             class="w-full h-auto object-contain rounded-[4px] block select-none">
                    @else
                        <img src="{{ asset('images/customer_login_illustration.jpg') }}" 
                             alt="Precision IT 24/7 Customer Support & Help Desk"
                             class="w-full h-auto max-h-[380px] xl:max-h-[420px] object-contain mx-auto rounded-[4px] block select-none">
                    @endif
                </div>

                <!-- Status Ribbon Below -->
                <div class="w-full max-w-xl xl:max-w-2xl flex items-center justify-between mt-3 px-1 text-xs text-slate-600">
                    <div class="font-medium">
                        {{ $isStaffPortal ? '2,480+ Cameras Active • 99.9% Uptime' : '24/7 Customer Care • Instant Support' }}
                    </div>

                    <div class="font-bold text-blue-600">
                        {{ $isStaffPortal ? 'ERP v2.6 Enterprise' : 'Secure Client Portal' }}
                    </div>
                </div>

            </div>

            {{-- Right Footer Security Status --}}
            <div class="flex items-center justify-between text-xs text-slate-500">
                <div>
                    256-Bit SSL Encrypted &amp; SOC-2 Certified
                </div>
                <div>Precision IT Systems Infrastructure</div>
            </div>
        </div>

    </div>

</body>
</html>
