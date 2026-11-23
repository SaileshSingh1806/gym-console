<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    <title>{{ $title ?? ($tenant->name . ' | Member agreement') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display:none!important}
        * { box-sizing:border-box}
        body { margin:0; background:#f3f2ef; color:#20242b; font:14px/1.65 Inter,system-ui,sans-serif}
        button,input,textarea { font:inherit}
        button,a,input,textarea { touch-action:manipulation}
        button,a {  -webkit-tap-highlight-color:transparent}
        button { cursor:pointer}
        button:disabled { opacity:.45; cursor:not-allowed}
        a { color:inherit}
        h1,h2,h3,p { margin:0}
        h1 { font-size:clamp(25px,4vw,34px); line-height:1.2; letter-spacing:-1px; font-weight:600}
        h2 { font-size:21px; letter-spacing:-.5px; font-weight:600}
        h3 { font-size:15px; font-weight:600}
        .muted { color:#69717b}
        .eyebrow { font-size:11px; text-transform:uppercase; letter-spacing:1.6px; font-weight:600; color:#68707a}
        .brandbar { border-bottom:1px solid #deded9; background:#fff}
        .brandinner { max-width:1000px; margin:auto; padding:18px 28px; display:flex; align-items:center; justify-content:space-between; gap:16px}
        .brand { display:flex; align-items:center; gap:12px; font-weight:600}
        .brand img { width:36px; height:36px; object-fit:contain}
        .brandmark { width:34px; height:34px; background:#242830; color:#fff; display:grid; place-items:center; border-radius:4px}
        .page { max-width:920px; margin:40px auto; padding:0 24px}
        .pageintro { margin-bottom:26px; display:flex; justify-content:space-between; align-items:end; gap:16px}
        .pageintro p { margin-top:10px; max-width:560px}
        .sheet { background:white; border:1px solid #deded9; box-shadow:0 4px 20px #18202b05; border-top:3px solid #b58a3c}
        .sheethead { padding:28px 36px; border-bottom:1px solid #e5e5e0}
        .member { display:flex; align-items:center; gap:18px}
        .portrait { width:76px; height:88px; object-fit:cover; border-radius:4px; background:#ecece7; flex-shrink:0}
        .initials { display:grid; place-items:center; font-size:26px; color:#72766e}
        .member h2 { margin:3px 0}
        .membermeta { display:flex; gap:6px 20px; flex-wrap:wrap; font-size:12px; color:#69717b}
        .steps { display:grid; grid-template-columns:repeat(4,1fr); border-bottom:1px solid #e5e5e0; padding:0 36px; gap:16px}
        .step { padding:16px 0; border-bottom:2px solid transparent; color:#777d85; font-size:12px}
        .step.active { color:#20242b; border-color:#b58a3c; font-weight:600}
        .step span { margin-right:6px; color:#8a7250; font-variant-numeric:tabular-nums}
        .document { padding:32px 36px 36px}
        .sectionintro { margin-bottom:26px}
        .sectionintro p { margin-top:8px; color:#69717b}
        .facts { display:grid; grid-template-columns:1fr 1fr; gap:22px 32px; margin:24px 0 30px}
        .facts dt { font-size:12px; color:#69717b}
        .facts dd { margin:4px 0 0; font-weight:500; overflow-wrap:anywhere}
        .notice { background:#f7f6f2; border-left:2px solid #b58a3c; padding:14px 16px; margin:20px 0; color:#5c6066; font-size:13px}
        .check { display:flex; align-items:flex-start; gap:11px; font-size:13px; cursor:pointer}
        .check input { width:17px; height:17px; flex-shrink:0; margin-top:3px; accent-color:#333b45}
        .actions { margin-top:28px; padding-top:22px; border-top:1px solid #e5e5e0; display:flex; justify-content:space-between; align-items:center; gap:16px}
        .primary,.secondary { display:inline-flex; justify-content:center; align-items:center; min-height:46px; padding:11px 20px; border-radius:5px; font-size:13px; font-weight:600; text-decoration:none; border:1px solid #252b33}
        .primary { background:#252b33; color:#fff}
        .primary:hover { background:#39414b}
        .secondary { background:white; color:#414954; border-color:#d8dadc}
        .question { margin:0; padding:22px 0; border:0; border-bottom:1px solid #e5e5e0; min-width:0}
        .question legend { float:left; width:100%; font-size:14px; font-weight:500; line-height:1.6; margin-bottom:12px}
        .question legend span { color:#96999d; font-size:12px; margin-right:8px}
        .answers { clear:both; display:flex; gap:10px}
        .answer { display:flex; align-items:center; gap:9px; padding:9px 18px; border:1px solid #dadcde; border-radius:4px; cursor:pointer; min-width:85px}
        .answer:has(input:checked) { border-color:#515963; background:#f3f4f4}
        .answer input { accent-color:#333b45; margin:0; width:16px; height:16px}
        .question textarea { width:100%; margin-top:12px; padding:12px; border:1px solid #d8dadc; border-radius:4px; resize:vertical; background:#fafaf9; min-height:80px}
        .terms { margin:24px 0}
        .terms h3 { margin-bottom:10px}
        .terms p { margin:8px 0; color:#555d65; font-size:13px; line-height:1.85}
        .signature { display:block; width:100%; height:190px; border:1px solid #d8dadc; border-radius:4px; background:#fdfdfb; touch-action:none; cursor:crosshair; margin:16px 0 8px}
        .signaturetools { display:flex; justify-content:space-between; font-size:12px; color:#69717b}
        .textbutton { border:0; background:none; text-decoration:underline; color:#414954; padding:4px}
        .alert { padding:14px 16px; border:1px solid #e0b6ae; background:#fff5f2; color:#8c3528; margin:0 0 22px; font-size:13px}
        .complete { padding:16px 0}
        .complete h2 { font-size:26px}
        .receipt { margin:24px 0; padding:20px 0; border-block:1px solid #e5e5e0; overflow-wrap:anywhere}
        .footer { max-width:920px; margin:24px auto 36px; padding:0 24px; display:flex; justify-content:space-between; gap:16px; font-size:11px; color:#7a7f84}
        :focus-visible { outline:2px solid #996d25; outline-offset:4px}
         @media(max-width:600px) { .brandinner { padding:14px 18px}
        .page { margin:24px auto; padding:0 12px}
        .pageintro { padding:0 6px}
        .pageintro>.eyebrow { display:none}
        .sheethead { padding:22px 20px}
        .document { padding:25px 20px}
        .steps { padding:0 20px; gap:9px}
        .step { font-size:10px}
        .step span { display:block; margin:0}
        .facts { gap:20px}
        .actions { flex-wrap:wrap}
        .actions .primary { flex:1}
        .membermeta { gap:3px 10px}
        .portrait { width:64px; height:76px}
        .footer { padding:0 18px; font-size:10px}
        .brandinner>.muted { font-size:11px}
        }
        @media(prefers-reduced-motion:no-preference) { button,a { transition:background .15s ease}
        }
        .sheet { border-radius:12px; overflow:hidden; box-shadow:0 12px 40px #18202b08; }
        .sheethead { background:#faf9f6; }
        .portrait { border-radius:8px; border:1px solid #dfdfd8; }
        .document { padding-top:28px; }
        .sectionintro h2 { font-size:24px; margin-top:5px; }
        .sectionintro p { font-size:14px; }
        .health-progress { padding:17px 20px; background:#f7f7f4; border:1px solid #e8e8e2; border-radius:8px; margin-bottom:12px; }
        .health-progress>div { display:flex; justify-content:space-between; gap:12px; font-size:12px; }
        .health-progress span { color:#657065; font-variant-numeric:tabular-nums; }
        .health-progress progress { display:block; appearance:none; border:0; width:100%; height:5px; margin-top:12px; border-radius:8px; overflow:hidden; background:#e4e5df; }
        progress::-webkit-progress-bar { background:#e4e5df; }
        progress::-webkit-progress-value { background:#9b793d; }
        progress::-moz-progress-bar { background:#9b793d; }
        .question { padding:24px 4px; }
        .question legend { font-size:15px; line-height:1.7; }
        .question legend span { display:inline-grid; place-items:center; width:27px; height:27px; border-radius:5px; background:#f1f2ef; color:#68716b; font-size:11px; font-weight:600; }
        .question.answered legend span { color:#765822; background:#f6edda; }
        .answer { min-width:104px; min-height:46px; justify-content:center; border-radius:6px; background:#fff; font-weight:500; }
        .answer:has(input:checked) { background:#252d36; border-color:#252d36; color:white; box-shadow:0 2px 5px #252d3612; }
        .answer input { accent-color:#bc9651; }
        .declaration { padding:18px; background:#f6f5ef; border:1px solid #e6e1d4; border-radius:7px; font-size:14px; line-height:1.7; }
        .health-actions { flex-wrap:wrap; background:white; }
        .save-hint { flex:1; font-size:12px; color:#68716b; }
        .primary { border-radius:7px; min-height:48px; padding-inline:24px; }
        .notice { border-radius:0 6px 6px 0; }
        @media(max-width:600px) {
            .document { padding:24px 20px; }
            .question legend { font-size:15px; }
            .health-progress { padding:14px; }
            .health-progress>div { font-size:11px; }
            .save-hint { flex-basis:100%; }
            .health-actions { gap:12px; }
            .sectionintro h2 { font-size:23px; }
        }
        .terms-index { display:grid; gap:0; border:1px solid #e2e4e3; border-radius:8px; padding:18px 20px; background:#f8f9f7; margin-bottom:30px; }
        .terms-index>.eyebrow { margin-bottom:10px; }
        .terms-index a { padding:9px 0; font-size:12px; line-height:1.5; text-decoration:none; display:flex; gap:12px; }
        .terms-index a:hover { color:#8a651f; text-decoration:underline; }
        .terms-index a span { color:#8a7351; font-variant-numeric:tabular-nums; }
        .terms { margin:0 0 28px; border:1px solid #e1e4e2; border-radius:9px; overflow:hidden; scroll-margin-top:24px; }
        .terms-heading { display:flex; align-items:center; gap:13px; padding:19px 22px; background:#f6f7f5; border-bottom:1px solid #e1e4e2; }
        .terms-number { display:grid; place-items:center; width:30px; height:30px; flex-shrink:0; background:#252d36; color:#fff; border-radius:5px; font-size:11px; }
        .terms h3 { margin:0; font-size:16px; line-height:1.5; letter-spacing:-.2px; }
        .terms-clauses { margin:0; padding:8px 22px 14px; list-style:none; }
        .terms-clauses li { position:relative; padding:15px 0 15px 20px; border-bottom:1px solid #eceeeb; color:#47515b; font-size:14px; line-height:1.85; }
        .terms-clauses li:last-child { border-bottom:0; }
        .terms-clauses li::before { content:''; position:absolute; left:0; top:25px; width:5px; height:5px; border-radius:50%; background:#a48a5a; }
        .terms-clauses .policy-emphasis { margin:8px -8px; padding:14px 16px; background:#fff8e9; border:1px solid #e9d6ac; border-radius:6px; color:#654c21; }
        .terms-clauses .policy-emphasis::before { display:none; }
        @media(max-width:600px) { .terms-heading { padding:16px; } .terms-clauses { padding:6px 16px 10px; } .terms h3 { font-size:15px; } .terms-index { padding:16px; } }
        @media print { .brandbar,.footer,.steps,.actions { display:none}
        .page { margin:0; max-width:none}
        .sheet { box-shadow:none; border:0}
        body { background:white}
        }
    </style>
</head>
<body>
    <header class="brandbar"><div class="brandinner"><div class="brand">
        @if($tenant->logo_url)<img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}">@else<span class="brandmark">{{ mb_substr($tenant->name, 0, 1) }}</span>@endif
        <span>{{ $tenant->name }}</span></div><span class="muted">Member services</span></div></header>
    <main class="page">{{ $slot }}</main>
    <footer class="footer"><span>{{ $tenant->name }} &middot; Membership records</span><span>Powered by GymDeskHub</span></footer>
</body>
</html>
