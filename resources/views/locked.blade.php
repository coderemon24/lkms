<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Locked - Valid License Required</title>
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
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-rose-600 text-white mb-4 shadow-sm">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Software Access Locked</h1>
            <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 mt-1 font-medium max-w-md mx-auto">
                Application execution is restricted by the central license authority.
            </p>
        </div>

        <!-- Main Authority Custom Notice Card -->
        @if(!empty($details['lock_message']))
        <div class="mb-6 p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-200">
            <div class="flex items-start space-x-3.5">
                <div class="p-2 rounded-xl bg-amber-200/80 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400 block mb-1">Notice from Central Authority</span>
                    <p class="text-slate-900 dark:text-slate-100 text-sm leading-relaxed font-semibold whitespace-pre-line">{{ $details['lock_message'] }}</p>

                    @if(!empty($details['support_contact']))
                    <div class="mt-3 pt-3 border-t border-amber-200 dark:border-amber-800/80 flex items-center space-x-2 text-xs text-amber-800 dark:text-amber-300">
                        <svg class="w-4 h-4 shrink-0 text-amber-700 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>Support Contact: <strong class="text-slate-900 dark:text-white select-all font-mono">{{ $details['support_contact'] }}</strong></span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Card with Status Details -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-7 shadow-xl transition-colors">
            <div class="space-y-3.5 text-sm">
                <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Host Domain</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $domain }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Authorization Status</span>
                    <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider {{ ($details['status'] ?? '') === 'active' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800' }}">
                        {{ $details['status'] ?? 'Unactivated' }}
                    </span>
                </div>
                @if(!empty($details['expires_at']))
                <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Expiration</span>
                    <span class="font-mono text-slate-800 dark:text-slate-200">{{ $details['expires_at'] }}</span>
                </div>
                @endif
                @if(!empty($details['last_synced_at']))
                <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Last Authority Sync</span>
                    <span class="font-mono text-slate-500 dark:text-slate-400 text-xs">{{ $details['last_synced_at'] }}</span>
                </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="mt-7 space-y-3">
                <!-- Sync / Recheck with Server (Solid Blue) -->
                <a href="{{ route('lkms.locked', ['sync' => 1]) }}" class="w-full py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 transition shadow-xs flex items-center justify-center space-x-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Re-check / Sync Status with Server</span>
                </a>

                <!-- Enter New License Key (Solid Outline) -->
                <a href="{{ route('lkms.activate', ['force' => 1]) }}" class="w-full py-3 px-4 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white bg-slate-50 dark:bg-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition flex items-center justify-center space-x-2 cursor-pointer">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    <span>Enter New License Key</span>
                </a>
            </div>

            <div class="mt-6 pt-5 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-mono">
                <span>LKMS Cryptographic Enforcement</span>
                <span>Port 443 Encrypted</span>
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
