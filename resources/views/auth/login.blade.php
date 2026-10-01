@php
    if (request()->is('customer/login', 'customer') || request()->input('type') === 'customer') {
        $isStaffPortal = false;
    } elseif (request()->is('staff/login', 'staff', 'admin/login', 'admin') || request()->input('type') === 'staff' || request()->getPort() == 8001) {
        $isStaffPortal = true;
    } else {
        // Default on port 8000 / normal URL is Customer Portal
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
                    },
                    colors: {
                        brand: {
                            blue: '#2563eb',
                            blueHover: '#1d4ed8',
                            lightBg: '#eff4fc',
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="h-full antialiased font-sans text-slate-800 bg-white"
      x-data="{ 
          portal: '{{ $isStaffPortal ? 'admin' : 'customer' }}',
          showPassword: false,
          setEmailAndPass(email, pass) {
              document.getElementById('email').value = email;
              document.getElementById('password').value = pass;
          },
          quickSubmitRole(email, pass, role) {
              this.portal = role;
              this.setEmailAndPass(email, pass);
              document.getElementById('loginForm').submit();
          }
      }">

    <div class="min-h-full flex flex-col lg:flex-row">

        {{-- ========================================================================= --}}
        {{-- LEFT COLUMN: ROYAL BLUE THEMED CLEAN WHITE FORM                           --}}
        {{-- ========================================================================= --}}
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-6 sm:p-8 lg:p-10 xl:p-12 bg-white min-h-screen lg:min-h-full">
            
            {{-- Brand Logo Header --}}
            <div class="flex items-center justify-between mb-4 lg:mb-6">
                <a href="{{ $isStaffPortal ? route('login') : route('home') }}" class="flex items-center space-x-3 group">
                    <img src="{{ asset('logo.png') }}" alt="Precision IT Systems" class="h-10 w-auto object-contain">
                    <div>
                        <span class="text-xl font-extrabold font-heading text-slate-900 tracking-tight">Precision IT <span class="text-blue-600">Systems</span></span>
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            {{ $isStaffPortal ? 'Operations ERP Suite' : 'Customer Surveillance Portal' }}
                        </span>
                    </div>
                </a>

                @if($isStaffPortal)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                        <span>Staff & Admin ERP</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                        <span>Customer Portal</span>
                    </span>
                @endif
            </div>

            {{-- Main Form Container --}}
            <div class="max-w-md w-full mx-auto my-auto py-2">
                
                <!-- Main Bold Title -->
                <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 font-heading tracking-tight mb-2">
                    {{ $isStaffPortal ? 'Operations Sign In' : 'Customer Sign In' }}
                </h1>
                <p class="text-xs text-slate-500 mb-6">
                    @if($isStaffPortal)
                        Sign in to access administration, technician dispatch & field operations
                    @else
                        Sign in to view your camera surveillance status, service tickets & invoices
                    @endif
                </p>

                <!-- Role Selector Tabs (Only on Staff/Admin Login) -->
                @if($isStaffPortal)
                    <div class="flex p-1 bg-slate-100 rounded-xl mb-5 text-xs">
                        <button type="button" 
                                class="flex-1 py-2 rounded-lg font-bold transition-all text-center"
                                :class="portal === 'admin' ? 'bg-white text-blue-600 shadow-sm font-extrabold' : 'text-slate-500 hover:text-slate-900'"
                                @click="portal = 'admin'; setEmailAndPass('test@example.com', 'password')">
                            Admin
                        </button>
                        <button type="button" 
                                class="flex-1 py-2 rounded-lg font-bold transition-all text-center"
                                :class="portal === 'technician' ? 'bg-white text-blue-600 shadow-sm font-extrabold' : 'text-slate-500 hover:text-slate-900'"
                                @click="portal = 'technician'; setEmailAndPass('bob@example.com', 'password')">
                            Technician
                        </button>
                        <button type="button" 
                                class="flex-1 py-2 rounded-lg font-bold transition-all text-center"
                                :class="portal === 'staff' ? 'bg-white text-blue-600 shadow-sm font-extrabold' : 'text-slate-500 hover:text-slate-900'"
                                @click="portal = 'staff'; setEmailAndPass('alex@example.com', 'password')">
                            Employee
                        </button>
                    </div>
                @endif

                <!-- Google OAuth Login Link -->
                <a href="{{ route('auth.google', ['portal' => $isStaffPortal ? 'staff' : 'customer', 'mode' => 'login']) }}" 
                   class="w-full flex items-center justify-center space-x-3 py-3 px-4 rounded-xl border border-slate-200 hover:bg-slate-50 hover:border-slate-300 shadow-xs text-xs font-bold text-slate-700 transition-all">
                    <!-- Multicolored Google 'G' Icon -->
                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                    <span>{{ $isStaffPortal ? 'Log in with Staff Workspace' : 'Log in with Google' }}</span>
                </a>

                <!-- Divider: OR LOGIN WITH EMAIL -->
                <div class="relative flex items-center justify-center my-6">
                    <div class="border-t border-slate-200 w-full"></div>
                    <span class="bg-white px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest whitespace-nowrap">
                        OR LOGIN WITH EMAIL
                    </span>
                    <div class="border-t border-slate-200 w-full"></div>
                </div>

                <!-- Session / Error Alerts -->
                @if (session('status'))
                    <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold flex items-center gap-2">
                        <span>✓</span>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold space-y-1">
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
                        <label for="email" class="block text-xs font-bold text-slate-800 mb-1.5">Email Address</label>
                        <input id="email" type="email" name="email" 
                               value="{{ old('email', $isStaffPortal ? 'test@example.com' : 'customer@example.com') }}" 
                               required autofocus autocomplete="username" 
                               placeholder="{{ $isStaffPortal ? 'Staff Email (e.g. test@example.com)' : 'Customer Email (e.g. customer@example.com)' }}" 
                               class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-slate-900 placeholder-slate-400 bg-white transition-all shadow-xs">
                    </div>

                    <!-- Password Field with Show/Hide Eye Toggle -->
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-800 mb-1.5">Password</label>
                        <div class="relative">
                            <input id="password" :type="showPassword ? 'text' : 'password'" name="password" 
                                   value="password" required autocomplete="current-password" 
                                   placeholder="Password" 
                                   class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-slate-900 placeholder-slate-400 bg-white pr-10 transition-all shadow-xs">
                            <button type="button" @click="showPassword = !showPassword" 
                                    class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 focus:outline-none"
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
                                   class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            <span class="text-xs text-slate-600">Keep me logged in</span>
                        </label>

                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-pink-600 hover:text-pink-700 hover:underline">
                            Forgot your password?
                        </a>
                    </div>

                    <!-- Royal Blue Primary Log in Button -->
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full py-3.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-sm shadow-lg shadow-blue-500/25 flex items-center justify-center space-x-2 transition-all hover:scale-[1.01] active:scale-[0.99]">
                            <span>{{ $isStaffPortal ? 'Log in to Operations ERP' : 'Log in to Customer Portal' }}</span>
                            <span class="text-base leading-none">→</span>
                        </button>
                    </div>
                </form>

                @if(!$isStaffPortal)
                    <div class="text-center mt-3.5 text-xs text-slate-500">
                        <span>New customer? </span>
                        <a href="{{ route('register') }}" class="text-blue-600 font-bold hover:underline">
                            Create an account / Sign up →
                        </a>
                    </div>
                @endif

                <!-- Demo Credentials Quick Helper -->
                <div class="mt-5 p-3 bg-slate-50 border border-slate-200 rounded-2xl">
                    <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2 flex items-center justify-between">
                        <span>{{ $isStaffPortal ? 'Staff Demo Logins' : 'Customer Demo Logins' }}</span>
                        <span class="text-blue-600 font-semibold lowercase">click to autofill</span>
                    </div>

                    @if($isStaffPortal)
                        <div class="grid grid-cols-3 gap-1.5 text-[11px]">
                            <div class="p-1.5 bg-white rounded-lg border border-slate-100 cursor-pointer hover:border-blue-300 hover:bg-blue-50/40 transition text-center" @click="portal = 'admin'; setEmailAndPass('test@example.com', 'password')">
                                <span class="font-bold text-slate-800 block text-[10px]">Admin</span>
                                <span class="text-slate-500 font-mono text-[9px] block truncate">test@example.com</span>
                            </div>
                            <div class="p-1.5 bg-white rounded-lg border border-slate-100 cursor-pointer hover:border-blue-300 hover:bg-blue-50/40 transition text-center" @click="portal = 'technician'; setEmailAndPass('bob@example.com', 'password')">
                                <span class="font-bold text-slate-800 block text-[10px]">Technician</span>
                                <span class="text-slate-500 font-mono text-[9px] block truncate">bob@example.com</span>
                            </div>
                            <div class="p-1.5 bg-white rounded-lg border border-slate-100 cursor-pointer hover:border-blue-300 hover:bg-blue-50/40 transition text-center" @click="portal = 'staff'; setEmailAndPass('alex@example.com', 'password')">
                                <span class="font-bold text-slate-800 block text-[10px]">Staff</span>
                                <span class="text-slate-500 font-mono text-[9px] block truncate">alex@example.com</span>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div class="p-2 bg-white rounded-xl border border-slate-100 cursor-pointer hover:border-blue-400 hover:bg-blue-50/40 transition shadow-xs" @click="portal = 'customer'; setEmailAndPass('customer@example.com', 'password')">
                                <span class="font-bold text-blue-900 block text-xs">Demo Customer</span>
                                <span class="text-slate-500 font-mono text-[10px]">customer@example.com</span>
                            </div>
                            <div class="p-2 bg-white rounded-xl border border-slate-100 cursor-pointer hover:border-blue-400 hover:bg-blue-50/40 transition shadow-xs" @click="portal = 'customer'; setEmailAndPass('srinithish.p@example.com', 'password')">
                                <span class="font-bold text-blue-900 block text-xs">Srinithish P</span>
                                <span class="text-slate-500 font-mono text-[10px]">srinithish.p@example.com</span>
                            </div>
                        </div>
                    @endif

                    <div class="text-[10px] text-center text-slate-400 mt-1.5">Password for all accounts: <strong class="text-slate-700 font-mono">password</strong></div>
                </div>

                <!-- Portal Switcher Links -->
                <div class="text-center mt-5 text-xs text-slate-500 pt-3 border-t border-slate-100">
                    @if($isStaffPortal)
                        <span>Looking for Customer Portal? </span>
                        <a href="{{ route('customer.login') }}" class="text-blue-600 font-bold hover:underline">
                            Switch to Customer Login →
                        </a>
                    @else
                        <span>Looking for Staff & Admin ERP? </span>
                        <a href="{{ route('staff.login') }}" class="text-blue-600 font-bold hover:underline">
                            Switch to Staff / Technician Login →
                        </a>
                    @endif
                </div>

            </div>

            {{-- Left Footer Copyright --}}
            <div class="text-xs text-slate-400 text-center lg:text-left mt-4">
                &copy; {{ date('Y') }} Precision IT Systems. All rights reserved.
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- RIGHT COLUMN: ROYAL BLUE SOFT GRADIENT & WORKSPACE ILLUSTRATION           --}}
        {{-- ========================================================================= --}}
        <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-[#eff4fc] via-[#f0f3fa] to-[#e6eef9] p-8 lg:p-10 xl:p-12 flex-col justify-between relative overflow-hidden border-l border-slate-100 min-h-screen lg:min-h-full">
            
            {{-- Top Right Academy Widget Card --}}
            <div class="self-end max-w-sm text-right space-y-2 z-10">
                <div class="flex items-center justify-end space-x-2 text-slate-900 font-bold text-sm font-heading">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span>{{ $isStaffPortal ? 'Operations Academy' : 'Customer Academy' }}</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    @if($isStaffPortal)
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

            {{-- Center Illustration Container --}}
            <div class="my-auto py-3 flex flex-col items-center justify-center relative z-10 w-full">
                
                {{-- Glowing soft backdrop behind illustration --}}
                <div class="absolute w-96 h-96 bg-blue-300/20 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Full Uncropped Image Card -->
                <div class="relative w-full max-w-xl xl:max-w-2xl rounded-3xl overflow-hidden shadow-xl border border-white/80 bg-white/95 backdrop-blur-md p-2.5 sm:p-3 group transition-all duration-300 hover:shadow-2xl">
                    @if($isStaffPortal)
                        <img src="{{ asset('images/staff_erp_illustration.jpg') }}" 
                             alt="PathSoft CCTV Enterprise ERP Operations"
                             class="w-full h-auto object-contain rounded-2xl block select-none">
                    @else
                        <img src="{{ asset('images/customer_login_illustration.jpg') }}" 
                             alt="PathSoft 24/7 Customer Support & Help Desk"
                             class="w-full h-auto max-h-[380px] xl:max-h-[420px] object-contain mx-auto rounded-2xl block select-none">
                    @endif
                </div>

                <!-- Status Ribbon Below -->
                <div class="w-full max-w-xl xl:max-w-2xl flex items-center justify-between mt-3 px-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/90 backdrop-blur-md border border-slate-200/80 shadow-xs text-xs font-bold text-slate-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>{{ $isStaffPortal ? '2,480+ Cameras Active • 99.9% Uptime SLA' : '24/7 Customer Care • Instant Support' }}</span>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/90 backdrop-blur-md border border-slate-200/80 shadow-xs text-xs font-bold text-blue-600">
                        <span>{{ $isStaffPortal ? 'ERP v2.6 Enterprise' : 'Secure Client Portal' }}</span>
                    </div>
                </div>

            </div>

            {{-- Right Footer Security Status --}}
            <div class="flex items-center justify-between text-xs text-slate-400 z-10">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>256-Bit SSL Encrypted & SOC-2 Certified</span>
                </div>
                <span>Precision IT Systems Infrastructure</span>
            </div>
        </div>

    </div>

</body>
</html>
