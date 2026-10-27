@php
    $cookieMode = request()->cookie('crm_mode');
    $isDarkServer = ($cookieMode === 'night' || $cookieMode === 'dark' || $cookieMode === null);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full {{ $isDarkServer ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sign In - Precision IT Systems ERP Operations</title>

    <!-- Favicon / App Icon -->
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}?v={{ filemtime(public_path('logo.png')) }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ filemtime(public_path('favicon.ico')) }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts & Styles via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Tailwind CSS & Alpine.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <x-crm-theme-init />

    <style>
        :root {
            --crm-accent: #2563eb !important;
            --crm-accent-hover: #1d4ed8 !important;
            --brand-blue: #2563eb !important;
            --brand-blue-hover: #1d4ed8 !important;
        }

        * { font-family: 'Inter', system-ui, -apple-system, sans-serif; }

        /* ══════════════════════════════════════════════════════════════
           LEFT PANEL — always dark regardless of theme mode
           ══════════════════════════════════════════════════════════════ */
        .login-hero-panel {
            background: linear-gradient(160deg, #0c1222 0%, #162036 50%, #0c1222 100%);
            position: relative;
            overflow: hidden;
        }
        .login-hero-panel::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background:
                radial-gradient(ellipse at 25% 40%, rgba(37, 99, 235, 0.10) 0%, transparent 55%),
                radial-gradient(ellipse at 75% 25%, rgba(99, 102, 241, 0.07) 0%, transparent 50%),
                radial-gradient(ellipse at 40% 80%, rgba(14, 165, 233, 0.06) 0%, transparent 50%);
            animation: heroShift 25s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes heroShift {
            0%, 100% { transform: translate(0, 0); }
            33%  { transform: translate(2%, -1.5%); }
            66%  { transform: translate(-1%, 1%); }
        }

        /* Floating orbs */
        .orb { position: absolute; border-radius: 50%; filter: blur(90px); opacity: 0.35; animation: orbFloat 18s ease-in-out infinite; pointer-events: none; }
        .orb-1 { width: 280px; height: 280px; background: rgba(37, 99, 235, 0.18); top: 8%; left: 5%; }
        .orb-2 { width: 220px; height: 220px; background: rgba(99, 102, 241, 0.14); bottom: 12%; right: 8%; animation-delay: -6s; }
        .orb-3 { width: 180px; height: 180px; background: rgba(14, 165, 233, 0.10); top: 55%; left: 35%; animation-delay: -12s; }
        @keyframes orbFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25%  { transform: translate(25px, -18px) scale(1.04); }
            50%  { transform: translate(-15px, 25px) scale(0.96); }
            75%  { transform: translate(18px, 15px) scale(1.02); }
        }

        /* Subtle grid */
        .grid-overlay {
            position: absolute; inset: 0; pointer-events: none;
            background-image:
                linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
            background-size: 56px 56px;
            mask-image: radial-gradient(ellipse at center, black 25%, transparent 72%);
            -webkit-mask-image: radial-gradient(ellipse at center, black 25%, transparent 72%);
        }

        /* Glass card (left panel illustration wrapper) */
        .glass-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        /* Stat pills on left panel */
        .stat-pill {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.10);
            backdrop-filter: blur(8px);
            transition: all 0.3s ease;
        }
        .stat-pill:hover {
            background: rgba(255, 255, 255, 0.10);
            border-color: rgba(37, 99, 235, 0.35);
            transform: translateY(-2px);
        }

        /* Live pulse dot */
        .live-dot { width: 6px; height: 6px; background: #22c55e; border-radius: 50%; position: relative; flex-shrink: 0; }
        .live-dot::after { content: ''; position: absolute; inset: -3px; border-radius: 50%; background: rgba(34, 197, 94, 0.3); animation: livePulse 2s ease-in-out infinite; }
        @keyframes livePulse { 0%, 100% { transform: scale(1); opacity: 0.6; } 50% { transform: scale(1.6); opacity: 0; } }


        /* ══════════════════════════════════════════════════════════════
           RIGHT PANEL — adapts to light / dark
           All rules use !important to override crm-theme-init injections
           ══════════════════════════════════════════════════════════════ */

        /* --- LIGHT MODE --- */
        html:not(.dark) .form-panel { background: #f8fafc !important; }
        html:not(.dark) .form-panel .fp-heading  { color: #0f172a !important; }
        html:not(.dark) .form-panel .fp-subtext  { color: #64748b !important; }
        html:not(.dark) .form-panel .fp-label    { color: #334155 !important; }
        html:not(.dark) .form-panel .fp-muted    { color: #94a3b8 !important; }
        html:not(.dark) .form-panel .fp-footer   { color: #94a3b8 !important; }

        html:not(.dark) .login-input {
            background: #ffffff !important;
            border: 1.5px solid #e2e8f0 !important;
            color: #0f172a !important;
        }
        html:not(.dark) .login-input:focus {
            background: #ffffff !important;
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10) !important;
        }
        html:not(.dark) .login-input::placeholder { color: #94a3b8 !important; }

        html:not(.dark) .tab-bar { background: #f1f5f9 !important; border: 1.5px solid #e2e8f0 !important; }
        html:not(.dark) .tab-btn { color: #64748b !important; font-weight: 600 !important; }
        html:not(.dark) .tab-btn:hover { color: #0f172a !important; background: rgba(255,255,255,0.7) !important; }
        html:not(.dark) .tab-btn.tab-btn-active,
        html:not(.dark) .tab-btn.worker-tab-active { 
            background: #2563eb !important; 
            color: #ffffff !important; 
            font-weight: 700 !important; 
            border: 1px solid #1d4ed8 !important; 
            box-shadow: 0 1px 4px rgba(37,99,235,0.3) !important; 
        }
        html:not(.dark) .tab-btn.tab-btn-active svg,
        html:not(.dark) .tab-btn.worker-tab-active svg {
            stroke: #ffffff !important;
            color: #ffffff !important;
        }

        /* Google Workspace button in Light Mode */
        html:not(.dark) .google-btn { 
            background: #ffffff !important; 
            border: 1.5px solid #cbd5e1 !important; 
        }
        html:not(.dark) .google-btn,
        html:not(.dark) .google-btn span { 
            color: #0f172a !important; 
            font-weight: 600 !important; 
        }
        html:not(.dark) .google-btn:hover { 
            background: #f8fafc !important; 
            border-color: #94a3b8 !important; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.06) !important; 
        }

        html:not(.dark) .divider-line { border-color: #e2e8f0 !important; }
        html:not(.dark) .divider-text { color: #94a3b8 !important; background: #f8fafc !important; }

        html:not(.dark) .demo-wrapper { background: #ffffff !important; border: 1.5px solid #e2e8f0 !important; }
        html:not(.dark) .demo-header { background: #f8fafc !important; border-bottom: 1px solid #e2e8f0 !important; }
        html:not(.dark) .demo-card { border-right: 1px solid #f1f5f9 !important; }
        html:not(.dark) .demo-card:last-child { border-right: none !important; }
        html:not(.dark) .demo-card:hover { background: #f0f5ff !important; }
        html:not(.dark) .demo-card .dc-name { color: #1e293b !important; }
        html:not(.dark) .demo-card .dc-email { color: #94a3b8 !important; }
        html:not(.dark) .demo-card-worker:hover { background: #fffbeb !important; }
        html:not(.dark) .demo-card-worker .dc-name { color: #92400e !important; }
        html:not(.dark) .demo-card-worker .dc-email { color: #d97706 !important; }

        html:not(.dark) .info-banner { background: linear-gradient(135deg, #eff6ff 0%, #eef2ff 100%) !important; border: 1px solid #bfdbfe !important; }
        html:not(.dark) .info-banner .ib-icon-wrap { background: #dbeafe !important; }
        html:not(.dark) .info-banner .ib-icon { color: #2563eb !important; }
        html:not(.dark) .info-banner .ib-title { color: #1e3a8a !important; }
        html:not(.dark) .info-banner .ib-text { color: #475569 !important; }

        html:not(.dark) .theme-toggle { background: #ffffff !important; border: 1.5px solid #cbd5e1 !important; color: #0f172a !important; }
        html:not(.dark) .theme-toggle:hover { background: #f1f5f9 !important; border-color: #94a3b8 !important; }
        html:not(.dark) .theme-toggle svg { color: #0f172a !important; stroke: #0f172a !important; }
        html:not(.dark) .portal-badge-staff { background: #eff6ff !important; border: 1.5px solid #bfdbfe !important; color: #1d4ed8 !important; font-weight: 700 !important; }
        html:not(.dark) .portal-badge-worker { background: #fffbeb !important; border: 1.5px solid #fde68a !important; color: #92400e !important; font-weight: 700 !important; }

        html:not(.dark) .chk-label { color: #64748b !important; }
        html:not(.dark) .forgot-link { color: #2563eb !important; font-weight: 600 !important; }
        html:not(.dark) .forgot-link:hover { color: #1d4ed8 !important; text-decoration: underline !important; }
        html:not(.dark) .login-alert-error { background: #fef2f2 !important; border: 1px solid #fecaca !important; color: #b91c1c !important; }
        html:not(.dark) .login-alert-success { background: #f0fdf4 !important; border: 1px solid #bbf7d0 !important; color: #15803d !important; }

        /* Icons in Light Mode — Black */
        html:not(.dark) .demo-card .dc-icon { color: #000000 !important; stroke: #000000 !important; }
        html:not(.dark) .demo-card .dc-icon-wrap { background: #e2e8f0 !important; border: 1px solid #cbd5e1 !important; }
        html:not(.dark) .demo-card:hover .dc-icon-wrap { background: #cbd5e1 !important; border-color: #94a3b8 !important; }
        html:not(.dark) .input-icon { color: #000000 !important; stroke: #000000 !important; }
        html:not(.dark) .pwd-toggle { color: #000000 !important; stroke: #000000 !important; }
        html:not(.dark) .tab-btn svg { color: #000000 !important; stroke: #000000 !important; }


        /* --- DARK MODE --- */
        html.dark .form-panel { background: #0f172a !important; }
        html.dark .form-panel .fp-heading  { color: #ffffff !important; }
        html.dark .form-panel .fp-subtext  { color: #94a3b8 !important; }
        html.dark .form-panel .fp-label    { color: #e2e8f0 !important; }
        html.dark .form-panel .fp-muted    { color: #94a3b8 !important; }
        html.dark .form-panel .fp-footer   { color: #64748b !important; }

        html.dark .login-input {
            background: #1e293b !important;
            border: 1.5px solid #334155 !important;
            color: #ffffff !important;
        }
        html.dark .login-input:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
            background: #1e293b !important;
        }
        html.dark .login-input::placeholder { color: #64748b !important; }

        /* Role Navigation Tabs in Dark Mode: Crisp, high-contrast, beautiful */
        html.dark .tab-bar { background: #0b1329 !important; border: 1.5px solid #1e293b !important; }
        html.dark .tab-btn { color: #cbd5e1 !important; font-weight: 600 !important; }
        html.dark .tab-btn:hover { color: #ffffff !important; background: #1e293b !important; }
        html.dark .tab-btn.tab-btn-active,
        html.dark .tab-btn.worker-tab-active { 
            background: #2563eb !important; 
            color: #ffffff !important; 
            font-weight: 700 !important; 
            border: 1px solid #3b82f6 !important; 
            box-shadow: 0 2px 10px rgba(37,99,235,0.45) !important; 
        }
        html.dark .tab-btn.tab-btn-active svg,
        html.dark .tab-btn.worker-tab-active svg {
            stroke: #ffffff !important;
            color: #ffffff !important;
        }

        /* Google Workspace button in Dark Mode */
        html.dark .google-btn { background: #1e293b !important; border: 1.5px solid #3b4b68 !important; }
        html.dark .google-btn,
        html.dark .google-btn span { color: #ffffff !important; font-weight: 600 !important; }
        html.dark .google-btn:hover { background: #28374f !important; border-color: #60a5fa !important; box-shadow: 0 2px 10px rgba(0,0,0,0.3) !important; }

        html.dark .divider-line { border-color: #334155 !important; }
        html.dark .divider-text { color: #94a3b8 !important; background: #0f172a !important; }

        html.dark .demo-wrapper { background: #1e293b !important; border: 1.5px solid #334155 !important; }
        html.dark .demo-header { background: #162032 !important; border-bottom: 1px solid #334155 !important; }
        html.dark .demo-card { border-right: 1px solid #293548 !important; }
        html.dark .demo-card:last-child { border-right: none !important; }
        html.dark .demo-card:hover { background: rgba(37, 99, 235, 0.15) !important; }
        html.dark .demo-card .dc-name { color: #f8fafc !important; }
        html.dark .demo-card .dc-email { color: #94a3b8 !important; }
        html.dark .demo-card-worker:hover { background: rgba(217, 119, 6, 0.15) !important; }
        html.dark .demo-card-worker .dc-name { color: #fcd34d !important; }
        html.dark .demo-card-worker .dc-email { color: #f59e0b !important; }

        html.dark .info-banner { background: linear-gradient(135deg, rgba(30,58,138,0.25) 0%, rgba(49,46,129,0.2) 100%) !important; border: 1px solid rgba(59,130,246,0.35) !important; }
        html.dark .info-banner .ib-icon-wrap { background: rgba(37, 99, 235, 0.25) !important; }
        html.dark .info-banner .ib-icon { color: #93c5fd !important; }
        html.dark .info-banner .ib-title { color: #bfdbfe !important; }
        html.dark .info-banner .ib-text { color: #cbd5e1 !important; }

        /* Theme Toggle & Badges in Dark Mode */
        html.dark .theme-toggle { background: #1e293b !important; border: 1.5px solid #475569 !important; color: #fbbf24 !important; }
        html.dark .theme-toggle:hover { background: #263450 !important; border-color: #fbbf24 !important; }
        html.dark .theme-toggle svg { color: #fbbf24 !important; stroke: #fbbf24 !important; }
        html.dark .theme-toggle svg circle { fill: #fbbf24 !important; stroke: #fbbf24 !important; }

        html.dark .portal-badge-staff { 
            background: rgba(30, 58, 138, 0.45) !important; 
            border: 1.5px solid #3b82f6 !important; 
            color: #bfdbfe !important; 
            font-weight: 700 !important; 
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.25) !important; 
        }
        html.dark .portal-badge-worker { 
            background: rgba(146, 64, 14, 0.45) !important; 
            border: 1.5px solid #f59e0b !important; 
            color: #fde68a !important; 
            font-weight: 700 !important; 
            box-shadow: 0 0 10px rgba(245, 158, 11, 0.25) !important; 
        }

        html.dark .chk-label { color: #cbd5e1 !important; }
        html.dark .forgot-link { color: #93c5fd !important; font-weight: 600 !important; }
        html.dark .forgot-link:hover { color: #bfdbfe !important; text-decoration: underline !important; }
        html.dark .login-alert-error { background: rgba(153, 27, 27, 0.25) !important; border: 1px solid rgba(248, 113, 113, 0.3) !important; color: #fca5a5 !important; }
        html.dark .login-alert-success { background: rgba(22, 101, 52, 0.25) !important; border: 1px solid rgba(74, 222, 128, 0.3) !important; color: #86efac !important; }

        /* Icons in Dark Mode — White / Light Slate */
        html.dark .demo-card .dc-icon { color: #ffffff !important; stroke: #ffffff !important; }
        html.dark .demo-card .dc-icon-wrap { background: #334155 !important; border: 1px solid #475569 !important; }
        html.dark .demo-card:hover .dc-icon-wrap { background: #475569 !important; border-color: #64748b !important; }
        html.dark .input-icon { color: #94a3b8 !important; stroke: #94a3b8 !important; }
        html.dark .pwd-toggle { color: #94a3b8 !important; stroke: #94a3b8 !important; }
        html.dark .pwd-toggle:hover { color: #ffffff !important; stroke: #ffffff !important; }
        html.dark .pwd-toggle svg { stroke: #94a3b8 !important; color: #94a3b8 !important; }
        html.dark .pwd-toggle:hover svg { stroke: #ffffff !important; color: #ffffff !important; }
        html.dark .tab-btn svg { color: #cbd5e1 !important; stroke: #cbd5e1 !important; }


        /* ══════════════════════════════════════════════════════════════
           SHARED COMPONENTS
           ══════════════════════════════════════════════════════════════ */
        .login-input { transition: all 0.2s ease; outline: none; }
        .login-input-with-icon { padding-left: 2.75rem !important; }

        .btn-primary-login {
            background: #2563eb; color: #fff; font-weight: 700;
            transition: all 0.25s ease; position: relative; overflow: hidden;
        }
        .btn-primary-login:hover {
            background: #1d4ed8; transform: translateY(-1px);
            box-shadow: 0 8px 24px -4px rgba(37, 99, 235, 0.4) !important;
        }
        .btn-primary-login:active { transform: translateY(0); }
        .btn-primary-login::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.12) 0%, transparent 50%);
            pointer-events: none;
        }

        .worker-tab-active { background: #2563eb !important; color: #fff !important; border: 1px solid #1d4ed8 !important; }

        .demo-card, .google-btn, .tab-btn { transition: all 0.2s ease; }
        .demo-card:hover { transform: translateY(-1px); }
        .google-btn:hover { transform: translateY(-1px); }

        /* Entrance animation */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .anim { animation: fadeInUp 0.45s ease-out forwards; opacity: 0; }
        .anim-d1 { animation-delay: 0.08s; }
        .anim-d2 { animation-delay: 0.16s; }
        .anim-d3 { animation-delay: 0.24s; }
        .anim-d4 { animation-delay: 0.32s; }
        .anim-d5 { animation-delay: 0.40s; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    </style>
</head>

<body class="h-full antialiased"
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

    <div class="min-h-screen flex flex-col lg:flex-row">

        {{-- ═══════════════════════════════════════════════════════════
             LEFT PANEL — Brand showcase (always dark aesthetic)
             ═══════════════════════════════════════════════════════════ --}}
        <div class="hidden lg:flex w-1/2 login-hero-panel flex-col justify-between relative min-h-screen select-none">
            
            <div class="orb orb-1"></div>
            <div class="orb orb-2"></div>
            <div class="orb orb-3"></div>
            <div class="grid-overlay"></div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-between h-full p-8 lg:p-10 xl:p-14">
                
                <!-- Top: Brand -->
                <div class="anim">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-blue-600/20 border border-blue-500/20 flex items-center justify-center group-hover:bg-blue-600/30 transition-colors duration-300">
                            <img src="{{ asset('logo.png') }}" alt="Precision IT Systems" class="crm-brand-logo h-7 w-7 object-contain">
                        </div>
                        <div>
                            <span class="text-lg font-bold text-white tracking-tight">Precision IT <span class="text-blue-400">Systems</span></span>
                            <span class="block text-[10px] font-semibold text-slate-500 tracking-widest uppercase mt-0">Operations ERP</span>
                        </div>
                    </a>
                </div>

                <!-- Center: Headline + Illustration -->
                <div class="flex-1 flex flex-col justify-center py-8 max-w-lg">
                    
                    <div class="anim anim-d1">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full stat-pill text-xs text-emerald-300 font-medium mb-6">
                            <div class="live-dot"></div>
                            <span>All Systems Operational</span>
                        </div>
                    </div>

                    <h2 class="text-3xl xl:text-[2.5rem] font-extrabold text-white leading-[1.15] tracking-tight anim anim-d2">
                        Enterprise CCTV<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-sky-300 to-cyan-400">
                            Operations Hub
                        </span>
                    </h2>

                    <p class="text-[13px] text-slate-400 mt-4 leading-relaxed max-w-sm anim anim-d3">
                        Manage installations, dispatch technicians, track inventory, and monitor field operations — all from one unified platform.
                    </p>

                    <!-- Illustration -->
                    <div class="mt-8 anim anim-d4">
                        <div class="glass-card rounded-xl p-2.5 max-w-md">
                            <img src="{{ asset('images/staff_erp_illustration.jpg') }}" 
                                 alt="Precision IT CCTV Enterprise ERP Operations"
                                 class="w-full h-auto object-contain rounded-lg block">
                        </div>
                    </div>
                </div>

                <!-- Bottom: Stats + Security -->
                <div class="anim anim-d5">
                    <div class="flex flex-wrap gap-2.5 mb-5">
                        <div class="stat-pill rounded-lg px-4 py-2.5 text-center min-w-[95px]">
                            <div class="text-lg font-extrabold text-white leading-tight">2,480+</div>
                            <div class="text-[9px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Cameras Live</div>
                        </div>
                        <div class="stat-pill rounded-lg px-4 py-2.5 text-center min-w-[95px]">
                            <div class="text-lg font-extrabold text-white leading-tight">99.9%</div>
                            <div class="text-[9px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Uptime</div>
                        </div>
                        <div class="stat-pill rounded-lg px-4 py-2.5 text-center min-w-[95px]">
                            <div class="text-lg font-extrabold text-white leading-tight">v2.6</div>
                            <div class="text-[9px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Enterprise</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-[11px] text-slate-500">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>256-Bit SSL Encrypted &middot; SOC-2 Certified</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════
             RIGHT PANEL — Login form (light / dark adaptive)
             ═══════════════════════════════════════════════════════════ --}}
        <div class="w-full lg:w-1/2 flex flex-col form-panel min-h-screen">

            <!-- Top Bar -->
            <div class="flex items-center justify-between px-6 sm:px-8 lg:px-10 xl:px-12 pt-6 sm:pt-8">
                <!-- Mobile logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 lg:hidden">
                    <img src="{{ asset('logo.png') }}" alt="Precision IT Systems" class="crm-brand-logo h-9 w-9 object-contain">
                    <span class="text-base font-bold fp-heading tracking-tight">Precision IT <span class="text-blue-600">Systems</span></span>
                </a>
                <div class="hidden lg:block"></div>

                <div class="flex items-center gap-2.5">
                    <button @click="isDarkMode = window.crmToggleTheme()" 
                            type="button" 
                            class="theme-toggle inline-flex items-center justify-center w-9 h-9 rounded-lg transition-all duration-200 cursor-pointer" 
                            :title="isDarkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'" 
                            aria-label="Toggle dark/light mode">
                        <svg x-show="isDarkMode" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="5" fill="currentColor"/>
                            <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                            <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                        <svg x-show="!isDarkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </button>

                    <span class="text-[11px] font-semibold px-2.5 py-1 rounded-md transition-colors duration-200"
                          :class="portal === 'worker' ? 'portal-badge-worker' : 'portal-badge-staff'" 
                          x-text="portal === 'worker' ? '⚡ Daily Wage Worker' : '🔐 Staff & Admin ERP'">
                        Staff &amp; Admin ERP
                    </span>
                </div>
            </div>

            <!-- Main Form Container -->
            <div class="flex-1 flex items-center justify-center px-6 sm:px-8 lg:px-10 xl:px-12 py-6">
                <div class="w-full max-w-[420px]">
                    
                    <!-- Heading -->
                    <div class="mb-7 anim">
                        <h1 class="text-2xl font-extrabold fp-heading tracking-tight" 
                            x-text="portal === 'worker' ? 'Daily Wage Worker Sign In' : 'Operations Sign In'">
                            Operations Sign In
                        </h1>
                        <p class="text-sm fp-subtext mt-1.5" 
                           x-text="portal === 'worker' ? 'Sign in to punch attendance & log field work' : 'Sign in to your Operations ERP account'">
                            Sign in to your Operations ERP account
                        </p>
                    </div>

                    <!-- Status & Validation Alerts -->
                    @if (session('status'))
                        <div class="mb-5 p-3 rounded-lg login-alert-success text-xs font-semibold flex items-center gap-2 anim">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-5 p-3 rounded-lg login-alert-error text-xs font-semibold space-y-1.5 anim">
                            @foreach ($errors->all() as $error)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>{{ $error }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Role Tabs -->
                    <div class="anim anim-d1">
                        <div class="tab-bar grid grid-cols-4 gap-1 p-1 rounded-xl mb-6">
                            <button type="button" 
                                    class="tab-btn py-2 px-2 rounded-lg text-xs font-semibold text-center cursor-pointer"
                                    :class="portal === 'admin' ? 'tab-btn-active' : ''"
                                    @click="setRole('admin')">
                                Admin
                            </button>
                            <button type="button" 
                                    class="tab-btn py-2 px-2 rounded-lg text-xs font-semibold text-center cursor-pointer"
                                    :class="portal === 'technician' ? 'tab-btn-active' : ''"
                                    @click="setRole('technician')">
                                Technician
                            </button>
                            <button type="button" 
                                    class="tab-btn py-2 px-2 rounded-lg text-xs font-semibold text-center cursor-pointer"
                                    :class="portal === 'staff' ? 'tab-btn-active' : ''"
                                    @click="setRole('staff')">
                                Employee
                            </button>
                            <button type="button" 
                                    class="tab-btn py-2 px-2 rounded-lg text-xs font-semibold text-center cursor-pointer flex items-center justify-center gap-1"
                                    :class="portal === 'worker' ? 'worker-tab-active' : ''"
                                    @click="setRole('worker')"
                                    title="Daily Worker">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span class="truncate">Daily Worker</span>
                            </button>
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════════ --}}
                    {{-- WORKER LOGIN (Name + Phone)                --}}
                    {{-- ═══════════════════════════════════════════ --}}
                    <div x-show="portal === 'worker'" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="space-y-5">
                        
                        <div class="info-banner p-4 rounded-xl">
                            <div class="flex items-start gap-3">
                                <div class="ib-icon-wrap w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="ib-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                                <div>
                                    <div class="ib-title text-xs font-bold">Daily Wage Worker Portal</div>
                                    <p class="ib-text text-[11px] leading-relaxed mt-0.5">
                                        Sign in with your <strong>Worker Full Name</strong> and <strong>Mobile Phone Number</strong> to punch photo attendance & log field work.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <form id="workerLoginForm" method="POST" action="/login/worker" class="space-y-4">
                            @csrf
                            <div>
                                <label for="worker_name" class="fp-label block text-xs font-semibold mb-1.5">
                                    Worker Full Name <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 flex items-center pointer-events-none z-10">
                                        <svg class="w-4 h-4 input-icon transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <input id="worker_name" type="text" name="name" x-model="workerName"
                                           required placeholder="e.g. Raju Kumar" 
                                           style="padding-left: 2.75rem !important;"
                                           class="login-input login-input-with-icon w-full pr-4 py-2.5 text-sm rounded-lg">
                                </div>
                            </div>
                            <div>
                                <label for="worker_phone" class="fp-label block text-xs font-semibold mb-1.5">
                                    Mobile Phone Number <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 flex items-center pointer-events-none z-10">
                                        <svg class="w-4 h-4 input-icon transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    </div>
                                    <input id="worker_phone" type="tel" name="phone" x-model="workerPhone"
                                           required placeholder="e.g. 9876543210" 
                                           style="padding-left: 2.75rem !important;"
                                           class="login-input login-input-with-icon w-full pr-4 py-2.5 text-sm rounded-lg">
                                </div>
                                <span class="fp-muted text-[11px] block mt-1">10-digit mobile number linked to daily wage disbursements</span>
                            </div>
                            <div class="pt-1">
                                <button type="submit" class="btn-primary-login w-full py-2.5 text-sm rounded-lg flex items-center justify-center gap-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Sign In &amp; Open Attendance</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- ═══════════════════════════════════════════ --}}
                    {{-- STAFF / ADMIN LOGIN (Email + Password)     --}}
                    {{-- ═══════════════════════════════════════════ --}}
                    <div x-show="portal !== 'worker'" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        
                        <!-- Google OAuth -->
                        <div class="anim anim-d2">
                            <a href="{{ route('auth.google', ['portal' => 'staff', 'mode' => 'login']) }}" 
                               class="google-btn w-full flex items-center justify-center gap-2.5 py-2.5 px-4 rounded-lg text-sm font-medium cursor-pointer">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                                </svg>
                                <span>Continue with Google Workspace</span>
                            </a>
                        </div>

                        <!-- Divider -->
                        <div class="relative flex items-center my-6 anim anim-d2">
                            <div class="flex-1 border-t divider-line"></div>
                            <span class="divider-text px-4 text-[11px] font-medium uppercase tracking-widest">or</span>
                            <div class="flex-1 border-t divider-line"></div>
                        </div>

                        <!-- Login Form -->
                        <form id="loginForm" method="POST" action="{{ route('login') }}" class="space-y-4 anim anim-d3">
                            @csrf
                            <div>
                                <label for="email" class="fp-label block text-xs font-semibold mb-1.5">Email Address</label>
                                <input id="email" type="email" name="email" 
                                       value="{{ old('email', 'test@example.com') }}" 
                                       required autofocus autocomplete="username" 
                                       placeholder="you@company.com" 
                                       class="login-input w-full px-4 py-2.5 text-sm rounded-lg">
                            </div>
                            <div>
                                <label for="password" class="fp-label block text-xs font-semibold mb-1.5">Password</label>
                                <div class="relative">
                                    <input id="password" :type="showPassword ? 'text' : 'password'" name="password" 
                                           value="password" required autocomplete="current-password" 
                                           placeholder="••••••••" 
                                           class="login-input w-full px-4 py-2.5 text-sm rounded-lg pr-11">
                                    <button type="button" @click="showPassword = !showPassword" 
                                            class="pwd-toggle absolute right-3 top-1/2 -translate-y-1/2 transition-colors cursor-pointer"
                                            title="Toggle password visibility">
                                        <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <label for="remember_me" class="flex items-center gap-2 cursor-pointer select-none">
                                    <input id="remember_me" type="checkbox" name="remember" 
                                           class="w-3.5 h-3.5 rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-0 focus:ring-offset-0 cursor-pointer">
                                    <span class="chk-label text-xs">Remember me</span>
                                </label>
                                <a href="{{ route('password.request') }}" class="forgot-link text-xs font-medium transition-colors">
                                    Forgot password?
                                </a>
                            </div>
                            <div class="pt-1">
                                <button type="submit" class="btn-primary-login w-full py-2.5 text-sm rounded-lg flex items-center justify-center gap-2 cursor-pointer">
                                    <span>Sign in to Operations ERP</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Demo Credentials -->
                    <div class="mt-7 anim anim-d4">
                        <div class="demo-wrapper rounded-xl overflow-hidden">
                            <div class="demo-header px-4 py-2.5 flex items-center justify-between">
                                <span class="text-[11px] font-bold fp-muted uppercase tracking-wider">Demo Logins</span>
                                <span class="text-[10px] fp-muted font-medium" x-text="portal === 'worker' ? 'Daily wages profile' : 'Click to autofill'"></span>
                            </div>
                            <div class="grid grid-cols-4 gap-0">
                                <div class="demo-card p-3 cursor-pointer text-center" 
                                     @click="portal = 'admin'; setEmailAndPass('test@example.com', 'password')">
                                    <div class="dc-icon-wrap w-7 h-7 rounded-full flex items-center justify-center mx-auto mb-1.5 transition-all">
                                        <svg class="dc-icon w-3.5 h-3.5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    </div>
                                    <span class="dc-name font-bold block text-[11px]">Admin</span>
                                    <span class="dc-email text-[9px] block truncate mt-0.5 font-mono">test@example.c…</span>
                                </div>
                                <div class="demo-card p-3 cursor-pointer text-center" 
                                     @click="portal = 'technician'; setEmailAndPass('bob@example.com', 'password')">
                                    <div class="dc-icon-wrap w-7 h-7 rounded-full flex items-center justify-center mx-auto mb-1.5 transition-all">
                                        <svg class="dc-icon w-3.5 h-3.5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <span class="dc-name font-bold block text-[11px]">Technician</span>
                                    <span class="dc-email text-[9px] block truncate mt-0.5 font-mono">bob@example.c…</span>
                                </div>
                                <div class="demo-card p-3 cursor-pointer text-center" 
                                     @click="portal = 'staff'; setEmailAndPass('alex@example.com', 'password')">
                                    <div class="dc-icon-wrap w-7 h-7 rounded-full flex items-center justify-center mx-auto mb-1.5 transition-all">
                                        <svg class="dc-icon w-3.5 h-3.5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <span class="dc-name font-bold block text-[11px]">Staff</span>
                                    <span class="dc-email text-[9px] block truncate mt-0.5 font-mono">alex@example.c…</span>
                                </div>
                                <div class="demo-card demo-card-worker p-3 cursor-pointer text-center" 
                                     @click="portal = 'worker'; workerName = 'Raju Kumar'; workerPhone = '9876543210'">
                                    <div class="dc-icon-wrap w-7 h-7 rounded-full flex items-center justify-center mx-auto mb-1.5 transition-all">
                                        <svg class="dc-icon w-3.5 h-3.5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    </div>
                                    <span class="dc-name font-bold block text-[11px]">Worker</span>
                                    <span class="dc-email text-[9px] block truncate mt-0.5 font-mono">9876543210</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 sm:px-8 lg:px-10 xl:px-12 pb-6 text-center">
                <p class="fp-footer text-[11px]">
                    &copy; {{ date('Y') }} Precision IT Systems. All rights reserved.
                </p>
            </div>
        </div>

    </div>

</body>
</html>
