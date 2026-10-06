<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Software License Activation</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Instant Theme Detection -->
    <script>
        (function() {
            const saved = localStorage.getItem('lkms_theme');
            if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        code, .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="h-full flex items-center justify-center bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased p-4 relative transition-colors duration-150">

    <!-- Top Right Theme Switcher -->
    <div class="absolute top-6 right-6">
        <button id="themeToggleBtn" type="button" aria-label="Toggle Theme" class="p-2.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-800 transition shadow-xs cursor-pointer">
            <svg id="themeIconSun" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <svg id="themeIconMoon" class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
        </button>
    </div>

    <div class="w-full max-w-lg">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-blue-600 text-white mb-4 shadow-sm">
                <img src="{{ asset('images/logo.svg') }}" alt="LKMS Logo" class="w-9 h-9">
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Software License Activation</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1 font-medium">Authoritative Verification via Central License Authority</p>
        </div>

        <!-- Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 shadow-xl transition-colors">
            
            <!-- Detected Domain Badge -->
            <div class="mb-6 p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <span class="text-xs uppercase font-bold tracking-wider text-slate-500 block">Host Domain</span>
                    <span class="text-sm font-mono font-bold text-slate-900 dark:text-white mt-0.5 block">{{ $domain }}</span>
                </div>
                <div class="flex items-center space-x-1.5 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-xs font-bold text-emerald-700 dark:text-emerald-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Ready</span>
                </div>
            </div>

            <!-- Error Alerts -->
            @if($errors->any())
                <div class="mb-5 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <div class="flex items-start space-x-2">
                            <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('lkms.activate.submit') }}" class="space-y-5">
                @csrf

                <!-- License Key Input -->
                <div>
                    <label for="license_key" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        License Key
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        </div>
                        <input type="text" id="license_key" name="license_key" value="{{ old('license_key') }}" required autofocus placeholder="LKMS-LIVE-XXXX-XXXX-XXXX-XXXX" class="w-full pl-11 pr-4 py-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-base font-mono text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600 uppercase tracking-wider transition">
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Enter the license key issued by your software authority.</p>
                </div>

                <!-- Submit Button (Solid Blue - NO gradient) -->
                <button type="submit" class="w-full py-3.5 px-4 rounded-xl text-base font-bold text-white bg-blue-600 hover:bg-blue-700 transition shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Verify & Activate License</span>
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-mono">
                <span>Cryptographic RSA-2048</span>
                <span>Hardware Bound</span>
            </div>
        </div>
    </div>

    <!-- Theme Switcher Script -->
    <script>
        const themeBtn = document.getElementById('themeToggleBtn');
        if (themeBtn) {
            themeBtn.addEventListener('click', function() {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('lkms_theme', isDark ? 'dark' : 'light');
            });
        }
    </script>
</body>
</html>
