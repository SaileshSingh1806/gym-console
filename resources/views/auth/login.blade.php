<x-guest-layout :hide-nav="true" :hide-footer="true" :noindex="true" title="Sign in — {{ $platformSettings['app_name'] ?? 'GymDeskHub' }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        .gdh-login { --surface:#151b23; --canvas:#10151c; --line:#303b48; --text:#e8edf2; --muted:#a5b2c0; --accent:#a8c9e5; font-family:Inter,system-ui,sans-serif; color:var(--text); background:var(--canvas); min-height:100vh; min-height:100dvh; display:grid; grid-template-rows:auto 1fr auto; padding:0 clamp(20px,5vw,72px); font-size:14px; line-height:1.5; color-scheme:dark; }
        .gdh-login * { box-sizing:border-box; }
        .gdh-login a { color:var(--accent); text-decoration:none; }
        .gdh-login a:hover { text-decoration:underline; text-underline-offset:3px; }
        .gdh-login :focus-visible { outline:2px solid var(--accent); outline-offset:3px; }
        .gdh-login-header { display:flex; align-items:center; justify-content:space-between; gap:16px; min-height:76px; border-bottom:1px solid var(--line); }
        .gdh-login-header img { display:block; width:auto; height:32px; max-width:180px; object-fit:contain; }
        .gdh-login-header a, .gdh-login-footer { font-size:13px; }
        .gdh-login-content { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); width:100%; max-width:1000px; margin:auto; padding:48px 0; align-items:center; }
        .gdh-login-intro { padding:24px clamp(24px,5vw,72px) 24px 0; border-right:1px solid var(--line); }
        .gdh-login-intro h2 { font-size:clamp(28px,3vw,40px); font-weight:600; line-height:1.18; letter-spacing:-.04em; margin:14px 0; max-width:390px; }
        .gdh-login-intro p { color:var(--muted); max-width:340px; font-size:15px; line-height:1.7; }
        .gdh-login-kicker { font-size:12px; font-weight:600; color:var(--accent); }
        .gdh-login-form-area { width:100%; max-width:420px; margin:auto; padding-left:clamp(20px,4vw,54px); }
        .gdh-login h1 { font-size:28px; line-height:1.25; font-weight:600; letter-spacing:-.035em; margin:0 0 8px; }
        .gdh-login-description { color:var(--muted); margin-bottom:28px; }
        .gdh-login-form { display:grid; gap:18px; }
        .gdh-login-label { font-size:13px; font-weight:500; display:block; margin-bottom:7px; }
        .gdh-login-field { display:block; width:100%; min-height:46px; border:1px solid var(--line); border-radius:7px; background:var(--surface); color:var(--text); padding:11px 13px; font:inherit; }
        .gdh-login-field::placeholder { color:#8795a5; }
        .gdh-login-field:focus { border-color:var(--accent); outline:1px solid var(--accent); outline-offset:0; }
        .gdh-login-password { position:relative; }
        .gdh-login-password input { padding-right:66px; }
        .gdh-login-show { position:absolute; right:5px; top:5px; bottom:5px; padding:0 10px; border-radius:5px; color:var(--muted); font-size:12px; font-weight:500; cursor:pointer; }
        .gdh-login-show:hover { background:#232d38; color:var(--text); }
        .gdh-login-submit { width:100%; min-height:46px; border-radius:7px; background:var(--accent); color:#182938; font-weight:600; font-size:14px; cursor:pointer; }
        .gdh-login-submit:hover:not(:disabled) { background:#bfd8ec; }
        .gdh-login-submit:disabled { opacity:.65; cursor:wait; }
        .gdh-login-notice { border:1px solid var(--line); border-left:3px solid currentColor; border-radius:6px; padding:11px 13px; margin-bottom:18px; font-size:13px; }
        .gdh-login-footer { display:flex; justify-content:space-between; flex-wrap:wrap; gap:12px; border-top:1px solid var(--line); padding:20px 0; color:var(--muted); }
        @media(max-width:767px) {
            .gdh-login-content { grid-template-columns:1fr; padding:40px 0; }
            .gdh-login-intro { display:none; }
            .gdh-login-form-area { padding:0; max-width:400px; }
            .gdh-login-header { min-height:66px; }
            .gdh-login-header img { height:28px; max-width:155px; }
            .gdh-login-field { font-size:16px; }
        }
    </style>
    <div class="gdh-login">
        <header class="gdh-login-header">
            <a href="{{ route('home') }}" aria-label="Back to website">
                <img src="{{ asset('images/gymdeskhub_horizontal_dark.png') }}" alt="{{ $platformSettings['app_name'] ?? 'GymDeskHub' }}">
            </a>
            <a href="{{ route('contact') }}">Contact support</a>
        </header>
        <div class="gdh-login-content">
            <aside class="gdh-login-intro">
                <span class="gdh-login-kicker">Gym management software</span>
                <h2>Everything you need to run your gym.</h2>
                <p>Manage memberships, attendance and billing in one workspace.</p>
            </aside>
            <section class="gdh-login-form-area" aria-labelledby="login-title" x-data="{ showPassword: false, submitting: false }">
                <h1 id="login-title">Sign in</h1>
                <p class="gdh-login-description">Enter your account details to continue.</p>
                @if ($errors->any())
                    <div class="gdh-login-notice text-rose-300" role="alert">{{ $errors->first() }}</div>
                @endif
                @if (session('status'))
                    <div class="gdh-login-notice text-emerald-300" role="status">{{ session('status') }}</div>
                @endif
                <form action="{{ route('login.post') }}" method="POST" @submit="submitting = true" class="gdh-login-form">
                    @csrf
                    <div>
                        <label for="email" class="gdh-login-label">Email, mobile number or member ID</label>
                        <input type="text" id="email" name="email" value="{{ old('email') }}" required autocomplete="username" autocapitalize="none" spellcheck="false" class="gdh-login-field" placeholder="Enter your email, mobile or ID">
                    </div>
                    <div>
                        <div class="flex items-baseline justify-between gap-2">
                            <label for="password" class="gdh-login-label">Password</label>
                            <a href="{{ route('contact') }}" class="text-xs">Forgot password?</a>
                        </div>
                        <div class="gdh-login-password">
                            <input type="password" :type="showPassword ? 'text' : 'password'" id="password" name="password" required autocomplete="current-password" class="gdh-login-field" placeholder="Enter your password">
                            <button type="button" @click="showPassword = !showPassword" :aria-pressed="showPassword" aria-controls="password" :aria-label="showPassword ? 'Hide password' : 'Show password'" class="gdh-login-show"><span x-text="showPassword ? 'Hide' : 'Show'">Show</span></button>
                        </div>
                    </div>
                    <label class="flex items-center gap-2.5 cursor-pointer select-none text-sm">
                        <input type="checkbox" name="remember" @checked(old('remember')) class="w-4 h-4 rounded" style="accent-color:var(--accent)">
                        <span>Keep me signed in</span>
                    </label>
                    <button type="submit" :disabled="submitting" :aria-busy="submitting" class="gdh-login-submit">
                        <span x-text="submitting ? 'Signing in…' : 'Sign in'">Sign in</span>
                    </button>
                </form>
                <p class="mt-6 pt-5 border-t text-sm" style="border-color:var(--line);color:var(--muted)">New to GymDeskHub? <a href="{{ route('register') }}" class="font-medium">Create an account</a></p>
            </section>
        </div>
        <footer class="gdh-login-footer">
            <span>{{ $platformSettings['footer_copyright'] ?? ('© ' . date('Y') . ' ' . ($platformSettings['app_name'] ?? 'GymDeskHub')) }}</span>
            <a href="{{ route('home') }}">Back to website</a>
        </footer>
    </div>
</x-guest-layout>
