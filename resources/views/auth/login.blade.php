@php
    $cookieMode = request()->cookie('crm_mode');
    $isDarkServer = ($cookieMode === 'night' || $cookieMode === 'dark' || $cookieMode === null);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white dark:bg-[#0b1120] {{ $isDarkServer ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sign In - Precision IT Systems ERP Operations</title>

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

    <x-crm-theme-init />
</head>

<body class="h-full antialiased font-sans text-slate-800 dark:text-slate-100 bg-white dark:bg-[#0b1120]"
      x-data="{ 
          portal: 'admin',
          showPassword: false,
          isDarkMode: document.documentElement.classList.contains('dark'),
          workerName: 'Raju Kumar',
          workerPhone: '9876543210',
          setRole(role) {
              this.portal = role;
              if (role === 'admin') {
                  this.setEmailAndPass('test@example.com', 'password');
              } else if (role === 'technician') {
                  this.setEmailAndPass('bob@example.com', 'password');
              } else if (role === 'staff') {
                  this.setEmailAndPass('alex@example.com', 'password');
              } else if (role === 'worker') {
                  this.workerName = 'Raju Kumar';
                  this.workerPhone = '9876543210';
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
              this.setEmailAndPass(email, pass);
              document.getElementById('loginForm').submit();
          }
      }">

    <div class="min-h-full flex flex-col lg:flex-row">

        {{-- LEFT COLUMN: CLEAN FORM --}}
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-6 sm:p-8 lg:p-10 xl:p-12 bg-white dark:bg-[#0f172a] min-h-screen lg:min-h-full">
            
            {{-- Brand Logo Header --}}
            <div class="flex items-center justify-between mb-4 lg:mb-6">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('logo.png') }}" alt="Precision IT Systems" class="crm-brand-logo h-12 w-12 object-contain shrink-0">
                    <div>
                        <span class="text-xl font-bold text-slate-900 dark:text-white tracking-tight leading-tight">Precision IT <span class="text-blue-600">Systems</span></span>
                        <span class="block text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">Operations ERP</span>
                    </div>
                </a>

                <div class="flex items-center gap-2">
                    <button @click="isDarkMode = window.crmToggleTheme()" 
                            type="button" 
                            class="inline-flex items-center justify-center w-8 h-8 rounded-[6px] border border-[#1e293b] dark:border-slate-600 bg-white dark:bg-slate-800 text-[#1e293b] dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer shrink-0 shadow-2xs" 
                            :title="isDarkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'" 
                            aria-label="Toggle dark/light mode">
                        <svg x-show="isDarkMode" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="5" fill="currentColor"/>
                            <line x1="12" y1="1" x2="12" y2="3"/>
                            <line x1="12" y1="21" x2="12" y2="23"/>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                            <line x1="1" y1="12" x2="3" y2="12"/>
                            <line x1="21" y1="12" x2="23" y2="12"/>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                        <svg x-show="!isDarkMode" class="w-4 h-4 text-[#1e293b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </button>
                    <span class="text-xs font-bold px-2 py-0.5 rounded border border-slate-300 dark:border-slate-600" :class="portal === 'worker' ? 'bg-amber-100 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300' : 'bg-blue-100 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300'" x-text="portal === 'worker' ? 'Daily Wages Portal' : 'Staff & Admin ERP'">
                        Staff &amp; Admin ERP
                    </span>
                </div>
            </div>

            {{-- Main Form Container --}}
            <div class="max-w-md w-full mx-auto my-auto py-2">
                
                <!-- Main Title -->
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight mb-1" x-text="portal === 'worker' ? 'Daily Wage Worker Sign In' : 'Operations Sign In'">
                    Operations Sign In
                </h1>
                <p class="text-xs text-slate-500 mb-5" x-text="portal === 'worker' ? 'Log in with your Name and Mobile Number to punch photo attendance & log cabling/camera work' : 'Sign in to access administration, technician dispatch and field operations'">
                    Sign in to access administration, technician dispatch and field operations
                </p>

                <!-- Role Selector Tabs -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-1 p-1 bg-slate-100 rounded-[4px] mb-5 text-xs border border-slate-200">
                    <button type="button" 
                            class="py-1.5 px-2 rounded-[4px] transition-colors text-center font-medium"
                            :class="portal === 'admin' ? 'bg-white text-blue-600 font-bold border border-slate-300 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            @click="setRole('admin')">
                        Admin
                    </button>
                    <button type="button" 
                            class="py-1.5 px-2 rounded-[4px] transition-colors text-center font-medium"
                            :class="portal === 'technician' ? 'bg-white text-blue-600 font-bold border border-slate-300 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            @click="setRole('technician')">
                        Technician
                    </button>
                    <button type="button" 
                            class="py-1.5 px-2 rounded-[4px] transition-colors text-center font-medium"
                            :class="portal === 'staff' ? 'bg-white text-blue-600 font-bold border border-slate-300 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            @click="setRole('staff')">
                        Employee
                    </button>
                    <button type="button" 
                            class="py-1.5 px-2 rounded-[4px] transition-colors text-center font-medium flex items-center justify-center gap-1"
                            :class="portal === 'worker' ? 'bg-blue-600 text-white font-bold border border-blue-700 shadow-sm' : 'text-amber-900 bg-amber-50 border border-amber-200 hover:bg-amber-100'"
                            @click="setRole('worker')">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span class="truncate">Daily Worker</span>
                    </button>
                </div>

                {{-- ========================================================= --}}
                {{-- TAB A: DAILY WAGES WORKER LOGIN FORM (NAME & PHONE ONLY)  --}}
                {{-- ========================================================= --}}
                <div x-show="portal === 'worker'" class="space-y-4">
                    <div class="p-3.5 bg-gradient-to-r from-amber-50 to-blue-50 border border-amber-200/80 rounded-[4px] text-xs text-slate-800">
                        <div class="font-bold flex items-center gap-1.5 text-blue-900 mb-1">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>No Password Needed for Daily Wage Workers</span>
                        </div>
                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            Sign in with your <strong>Full Name</strong> and <strong>Mobile Number</strong> to punch your daily photo attendance (selfie), log cabling meters pulled, and record installed cameras.
                        </p>
                    </div>

                    <form id="workerLoginForm" method="POST" action="{{ route('login.worker') }}" class="space-y-4">
                        @csrf

                        <!-- Worker Name -->
                        <div>
                            <label for="worker_name" class="block text-sm font-semibold text-slate-700 mb-1">
                                Worker Full Name <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <input id="worker_name" type="text" name="name" 
                                       x-model="workerName"
                                       required placeholder="e.g. Raju Kumar / Muthu" 
                                       class="w-full pl-9 pr-3 py-2 text-sm rounded-[4px] border border-slate-300 focus:outline-none focus:border-blue-600 text-slate-900 placeholder-slate-400 bg-white">
                            </div>
                        </div>

                        <!-- Worker Mobile Phone Number -->
                        <div>
                            <label for="worker_phone" class="block text-sm font-semibold text-slate-700 mb-1">
                                Mobile Phone Number <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                                <input id="worker_phone" type="tel" name="phone" 
                                       x-model="workerPhone"
                                       required placeholder="e.g. 9876543210" 
                                       class="w-full pl-9 pr-3 py-2 text-sm rounded-[4px] border border-slate-300 focus:outline-none focus:border-blue-600 text-slate-900 placeholder-slate-400 bg-white">
                            </div>
                            <span class="text-[11px] text-slate-500 block mt-1">10-digit mobile number linked to daily wage disbursements</span>
                        </div>

                        <div class="pt-2">
                            <button type="submit" 
                                    class="w-full px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-[4px] transition-colors flex items-center justify-center gap-2 cursor-pointer shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Sign In &amp; Open Attendance Portal</span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ========================================================= --}}
                {{-- TAB B: STANDARD STAFF / TECH / ADMIN EMAIL & PASSWORD     --}}
                {{-- ========================================================= --}}
                <div x-show="portal !== 'worker'">
                    <!-- Google OAuth Login Link -->
                    <a href="{{ route('auth.google', ['portal' => 'staff', 'mode' => 'login']) }}" 
                       class="w-full flex items-center justify-center space-x-2 py-2.5 px-4 rounded-[4px] border border-slate-300 hover:bg-slate-50 text-xs font-medium text-slate-700 transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        <span>Log in with Staff Workspace</span>
                    </a>

                    <!-- Divider: OR LOGIN WITH EMAIL -->
                    <div class="relative flex items-center justify-center my-5">
                        <div class="border-t border-slate-200 w-full"></div>
                        <span class="bg-white px-3 text-xs font-medium text-slate-400 uppercase tracking-wider whitespace-nowrap">
                            OR LOGIN WITH EMAIL
                        </span>
                        <div class="border-t border-slate-200 w-full"></div>
                    </div>

                    <!-- Email & Password Form -->
                    <form id="loginForm" method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        <!-- Email Address Field -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                            <input id="email" type="email" name="email" 
                                   value="{{ old('email', 'test@example.com') }}" 
                                   required autofocus autocomplete="username" 
                                   placeholder="test@example.com" 
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
                                <span>Log in to Operations ERP</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Demo Credentials Helper -->
                <div class="mt-5 p-3 bg-slate-50 border border-slate-200 rounded-[4px]">
                    <div class="text-xs font-semibold text-slate-500 mb-2 flex items-center justify-between">
                        <span>Demo Logins (Click to Autofill)</span>
                        <span class="text-slate-400 font-normal text-[11px]" x-text="portal === 'worker' ? 'Daily wages profile' : 'Password: password'"></span>
                    </div>

                    <!-- 4 Logins Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 text-xs">
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
                        <div class="p-2 bg-amber-50/80 rounded-[4px] border border-amber-300 cursor-pointer hover:border-blue-600 transition-colors text-center" @click="portal = 'worker'; workerName = 'Raju Kumar'; workerPhone = '9876543210'">
                            <span class="font-bold text-amber-900 block text-xs">Worker</span>
                            <span class="text-amber-700 font-mono text-[10px] block truncate">9876543210</span>
                        </div>
                    </div>
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
                    Operations Academy
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Telemetry and diagnostic resources for field technicians and dispatch operators.
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
                    <img src="{{ asset('images/staff_erp_illustration.jpg') }}" 
                         alt="Precision IT CCTV Enterprise ERP Operations"
                         class="w-full h-auto object-contain rounded-[4px] block select-none">
                </div>

                <!-- Status Ribbon Below -->
                <div class="w-full max-w-xl xl:max-w-2xl flex items-center justify-between mt-3 px-1 text-xs text-slate-600">
                    <div class="font-medium">
                        2,480+ Cameras Active • 99.9% Uptime
                    </div>

                    <div class="font-bold text-blue-600">
                        ERP v2.6 Enterprise
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
