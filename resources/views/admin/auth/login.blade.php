<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <link href="{{ \App\Support\SiteFont::googleUrl() }}" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <script>
        (function(){
            var t = localStorage.getItem('huvanti-admin-theme') || 'light';
            if(t === 'dark'){ document.documentElement.classList.add('dark'); }
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body class="min-h-screen bg-slate-100 dark:bg-[#0f172a] flex items-center justify-center p-4" style="font-family:{{ \App\Support\SiteFont::cssStack() }}">
    <div class="w-full max-w-[400px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-7 sm:p-8 text-slate-900 dark:text-slate-100">
        <div class="text-center mb-7">
            <div class="w-12 h-12 bg-[#0C3B2E] flex items-center justify-center mx-auto text-white" aria-hidden="true">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1 1 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>
            </div>
            <h1 class="font-extrabold text-2xl mt-3">Admin</h1>
        </div>

        @if($errors->any())
            <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 px-4 py-3 text-sm mb-4">{{ $errors->first() }}</div>
        @endif
        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 px-4 py-3 text-sm mb-4">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}" autocomplete="username"
                    class="mt-1 w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 outline-none text-sm placeholder:text-slate-400">
            </div>
            <div>
                <label class="text-sm font-medium">Password</label>
                <input type="password" name="password" required autocomplete="current-password"
                    class="mt-1 w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 outline-none text-sm placeholder:text-slate-400">
            </div>
            @if(session('show_2fa') || old('two_factor_code') || $errors->has('two_factor_code'))
                <div>
                    <label class="text-sm font-medium">2FA Code</label>
                    <input type="text" name="two_factor_code" inputmode="numeric" maxlength="6" value="{{ old('two_factor_code') }}" autocomplete="one-time-code"
                        class="mt-1 w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm font-mono tracking-[0.3em] text-center placeholder:tracking-normal placeholder:font-sans" placeholder="6-digit code from your app">
                </div>
            @endif
            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                <input type="checkbox" name="remember" class="border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500"> Remember me
            </label>
            <button type="submit" class="w-full h-12 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Sign In</button>
        </form>

        <div class="text-center mt-6"><a href="/" class="text-sm text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">← Back to site</a></div>
    </div>
</body>
</html>
