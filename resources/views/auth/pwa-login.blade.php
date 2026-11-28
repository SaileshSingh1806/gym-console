<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#0D0E10">
    <link rel="manifest" href="{{ $manifest }}">
    <link rel="icon" href="{{ $icon }}">
    <link rel="apple-touch-icon" href="{{ $icon }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <x-pwa-startup-images :tenant="$tenant" :role="$role" :version="substr(hash('sha256', json_encode(['static-icon-v4', $tenant->logo_url, $tenant->settings['pwa_icon_path'] ?? null, $tenant->updated_at?->timestamp])), 0, 20)" />
    <meta name="apple-mobile-web-app-title" content="{{ $name }}">
    <title>{{ $name }} — {{ ucfirst($role) }} sign in</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#0D0E10;color:#ECEDEF;font:15px/1.5 system-ui,sans-serif;color-scheme:dark}
        main{min-height:100vh;min-height:100dvh;display:grid;place-items:center;padding:32px 24px;padding: max(32px, env(safe-area-inset-top, 0px)) max(24px, env(safe-area-inset-right, 0px)) max(32px, env(safe-area-inset-bottom, 0px)) max(24px, env(safe-area-inset-left, 0px))}
        section{width:100%;max-width:380px}header{text-align:center;margin-bottom:32px}
        img{display:block;width:144px;height:144px;object-fit:contain;margin:0 auto 18px}
        h1{font-size:24px;line-height:1.25;margin:0 0 8px;font-weight:650}p{color:#A0A4AB;margin:0}
        form{display:grid;gap:18px}label{display:block;font-size:13px;font-weight:500;margin-bottom:7px}
        input:not([type=checkbox]){width:100%;min-height:48px;border:1px solid #373A41;border-radius:8px;background:#141518;color:#ECEDEF;padding:12px;font:inherit;font-size:16px}
        input:focus-visible,button:focus-visible{outline:2px solid #F5A524;outline-offset:2px}
        .remember{display:flex;align-items:center;gap:9px;font-weight:400;margin:0}
        input[type=checkbox]{accent-color:#F5A524;width:16px;height:16px}
        button{min-height:48px;border:0;border-radius:8px;background:#F5A524;color:#16130B;font:600 15px system-ui;cursor:pointer}
        .notice{border:1px solid #613636;border-radius:8px;color:#FCA5A5;padding:12px;margin-bottom:18px}
        footer{text-align:center;margin-top:24px;font-size:13px;color:#A0A4AB}
    </style>
</head>
<body>
<main>
    <section>
        <header>
            <img src="{{ $logo }}" alt="{{ $name }}">
            <h1>{{ $name }}</h1>
            <p>{{ ucfirst($role) }} sign in</p>
        </header>
        @if($errors->any())
            <div class="notice" role="alert">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('pwa.login.post', [$tenant->slug, $role]) }}">
            @csrf
            <div>
                <label for="email">{{ $role === 'member' ? 'Email, mobile or member ID' : 'Email or mobile number' }}</label>
                <input id="email" name="email" type="text" required autocomplete="username" autocapitalize="none" spellcheck="false" value="{{ old('email') }}">
            </div>
            <div>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
            </div>
            <label class="remember"><input name="remember" type="checkbox" value="1"> Keep me signed in</label>
            <button type="submit">Sign in</button>
        </form>
        <footer>Need help accessing your account? Contact your gym reception.</footer>
    </section>
</main>
<script>
    if ('serviceWorker' in navigator && window.isSecureContext) {
        navigator.serviceWorker.register('/pwa-worker.js', {scope:'/app/', updateViaCache:'none'}).catch(console.warn);
    }
</script>
</body>
</html>
