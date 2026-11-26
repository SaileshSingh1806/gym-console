<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Attendance QR Standee - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b',
                            600: '#d97706',
                            950: '#0f172a'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        @media print {
            html, body {
                width: 210mm !important;
                height: 297mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }

            .standee-a4-page {
                width: 210mm !important;
                height: 297mm !important;
                max-height: 297mm !important;
                margin: 0 !important;
                padding: 12mm 14mm !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                overflow: hidden !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
            }
        }

        @media screen {
            .standee-a4-page {
                width: 100%;
                max-width: 650px;
                min-height: 920px;
            }
        }
    
        .standee-qr { width:min(256px,100%); aspect-ratio:1; background:#fff; border:1px solid #e2e8f0; margin:auto; display:grid; place-items:center; }
        #qrcode-render { width:100%; min-width:0; }
        #qrcode-render svg { display:block; width:100%; height:auto; }
        @media screen and (max-width:480px) {
            .standee-a4-page { min-height:0; padding:18px 12px; gap:16px; }
            .standee-a4-page .relative.inline-block { width:100%; max-width:300px; padding:12px; }
            .no-print.w-full { flex-wrap:wrap; gap:8px; }
            .standee-qr { width:100%; max-width:256px; }
        }
        @media print { .standee-qr { width:70mm; height:70mm; } }
    </style>
</head>
<body class="bg-slate-950 text-slate-900 min-h-screen flex flex-col items-center justify-start p-3 sm:p-8 font-sans antialiased">

    @php
        $rawLogo = $tenant->logo_url ?? null;
        $gymLogo = null;
        if (!empty($rawLogo)) {
            if (str_starts_with($rawLogo, 'http://') || str_starts_with($rawLogo, 'https://')) {
                $gymLogo = $rawLogo;
            } elseif (str_starts_with($rawLogo, '/storage/') || str_starts_with($rawLogo, 'storage/')) {
                $gymLogo = asset(ltrim($rawLogo, '/'));
            } else {
                $gymLogo = asset('storage/' . $rawLogo);
            }
        }

        $tenantSettings = is_array($tenant->settings) ? $tenant->settings : [];
        $gymAddress = $tenantSettings['address'] ?? ($tenant->mainBranch?->address ?? ($tenant->address ?? ''));
        $gymPhone = $tenant->phone ?? ($tenantSettings['phone'] ?? '');
        $gymEmail = $tenant->email ?? ($tenantSettings['email'] ?? '');
    @endphp

    <!-- Top Action Bar (Screen Only) -->
    <div class="no-print w-full max-w-[650px] flex items-center justify-between mb-5">
        <a href="{{ route('app.attendance.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors bg-slate-900/80 px-3.5 py-2 rounded-xl border border-slate-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Attendance</span>
        </a>

        <div class="flex items-center gap-2.5">
            <span class="text-[11px] font-bold text-amber-400/90 hidden sm:inline-flex items-center gap-1.5 bg-amber-500/10 px-3 py-1.5 rounded-lg border border-amber-500/20">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                A4 Poster Format
            </span>
            <button type="button" onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs transition-all shadow-lg shadow-amber-500/20 flex items-center gap-2 cursor-pointer active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Standee (A4)</span>
            </button>
        </div>
    </div>

    <!-- Professional Standee Poster (A4 Sheet Ratio) -->
    <div class="standee-a4-page bg-white rounded-3xl shadow-2xl border border-slate-200/80 overflow-hidden text-center p-8 sm:p-10 relative flex flex-col justify-between">
        
        <!-- Subtle Premium Border Frame Accent -->
        <div class="absolute inset-2 sm:inset-3 border-2 border-slate-900/10 rounded-2xl pointer-events-none"></div>

        <!-- 1. HEADER SECTION: BRANDING & GYM IDENTITY -->
        <div class="space-y-4 pt-1 z-10">
            <!-- Logo Display -->
            <div class="flex items-center justify-center">
                @if(!empty($gymLogo))
                    <img src="{{ $gymLogo }}" 
                         alt="{{ $tenant->name }}" 
                         class="h-16 sm:h-20 w-auto max-w-[240px] object-contain drop-shadow-sm"
                         onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-16 h-16 rounded-2xl bg-slate-950 text-amber-400 font-black text-2xl flex items-center justify-center shadow-md border-2 border-amber-500/40\'>{{ strtoupper(substr($tenant->name ?: 'G', 0, 2)) }}</div>';">
                @else
                    <div class="w-16 h-16 rounded-2xl bg-slate-950 text-amber-400 font-black text-2xl flex items-center justify-center shadow-md border-2 border-amber-500/40">
                        {{ strtoupper(substr($tenant->name ?: 'G', 0, 2)) }}
                    </div>
                @endif
            </div>

            <!-- Gym Name & Official Standee Badge -->
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-950 tracking-tight uppercase leading-tight font-serif">
                    {{ $tenant->name }}
                </h1>
                
                @if(!empty($gymAddress))
                    <p class="text-[11px] font-medium text-slate-500 mt-0.5 line-clamp-1 max-w-md mx-auto">
                        {{ $gymAddress }}
                    </p>
                @endif

                <div class="inline-flex items-center gap-2 mt-2.5 px-4 py-1 rounded-full bg-slate-900 text-amber-400 border border-slate-800 text-[10px] sm:text-xs font-black tracking-widest uppercase shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    Self Attendance Check-In Standee
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                </div>
            </div>
        </div>

        <!-- 2. HERO QR CODE SECTION -->
        <div class="my-4 sm:my-6 space-y-3 z-10">
            <!-- QR Frame Card -->
            <div class="relative inline-block mx-auto p-4 sm:p-5 rounded-3xl bg-slate-50 border-2 border-slate-200/90 shadow-md">
                
                <!-- Corner Target Accents (Viewfinder design) -->
                <div class="absolute top-2 left-2 w-3.5 h-3.5 border-t-2 border-l-2 border-slate-900"></div>
                <div class="absolute top-2 right-2 w-3.5 h-3.5 border-t-2 border-r-2 border-slate-900"></div>
                <div class="absolute bottom-2 left-2 w-3.5 h-3.5 border-b-2 border-l-2 border-slate-900"></div>
                <div class="absolute bottom-2 right-2 w-3.5 h-3.5 border-b-2 border-r-2 border-slate-900"></div>

                <!-- QR Container -->
                <div id="qrcode-wrapper" class="standee-qr">
                    <div id="qrcode-render">{!! $qrSvg !!}</div>
                </div>
                <a class="no-print text-xs underline block mt-2" href="{{ $checkinUrl }}">Open check-in link</a>

                <!-- Live Camera Indicator Badge -->
                <div class="mt-2.5 flex items-center justify-center gap-1.5 text-[11px] font-black text-slate-900 uppercase tracking-wider">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Point Any Camera &bull; Auto Check-In</span>
                </div>
            </div>

            <!-- Compatibility Tagline -->
            <p class="text-xs text-slate-600 font-medium max-w-sm mx-auto leading-relaxed">
                Works with <strong>iPhone Camera</strong>, <strong>Google Lens</strong>, <strong>Paytm</strong>, <strong>WhatsApp</strong>, or your <strong>Member Portal</strong>.
            </p>
        </div>

        <!-- 3. HOW IT WORKS: 3 BALANCED STEPS -->
        <div class="grid grid-cols-3 gap-2.5 sm:gap-3 py-3 border-t-2 border-b-2 border-slate-100 text-center z-10">
            <div class="p-2.5 sm:p-3 rounded-2xl bg-slate-50 border border-slate-200/60 shadow-xs">
                <div class="w-6 h-6 rounded-full bg-slate-900 text-amber-400 font-black text-xs flex items-center justify-center mx-auto mb-1">1</div>
                <div class="font-black text-slate-900 text-xs sm:text-sm">Open Camera</div>
                <p class="text-[10px] text-slate-500 mt-0.5 leading-tight">Or any QR scanner</p>
            </div>

            <div class="p-2.5 sm:p-3 rounded-2xl bg-slate-50 border border-slate-200/60 shadow-xs">
                <div class="w-6 h-6 rounded-full bg-slate-900 text-amber-400 font-black text-xs flex items-center justify-center mx-auto mb-1">2</div>
                <div class="font-black text-slate-900 text-xs sm:text-sm">Scan QR</div>
                <p class="text-[10px] text-slate-500 mt-0.5 leading-tight">Hold camera steady</p>
            </div>

            <div class="p-2.5 sm:p-3 rounded-2xl bg-slate-50 border border-slate-200/60 shadow-xs">
                <div class="w-6 h-6 rounded-full bg-emerald-600 text-white font-black text-xs flex items-center justify-center mx-auto mb-1">3</div>
                <div class="font-black text-slate-900 text-xs sm:text-sm">Marked!</div>
                <p class="text-[10px] text-emerald-600 font-semibold mt-0.5 leading-tight">Attendance logged</p>
            </div>
        </div>

        <!-- 4. GYM RULES / NOTICE PILL -->
        <div class="py-2 z-10">
            <div class="p-2.5 rounded-xl bg-amber-50/70 border border-amber-200 text-amber-900 text-[10px] sm:text-[11px] font-semibold flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                <span>Members are requested to carry clean workout footwear & gym towel at all times.</span>
            </div>
        </div>

        <!-- 5. FOOTER: CONTACT & AUTHENTICITY BADGE -->
        <div class="pt-2 text-[11px] text-slate-500 flex items-center justify-between border-t border-slate-100 z-10">
            <div class="text-left">
                @if(!empty($gymPhone))
                    <span class="font-bold text-slate-900 block leading-tight">Support: {{ $gymPhone }}</span>
                @else
                    <span class="font-medium text-slate-500 block leading-tight">Ask Front Desk for Assistance</span>
                @endif
                <span class="text-[9px] text-slate-400">Automated Attendance Terminal</span>
            </div>

            <div class="text-right flex items-center gap-1.5">
                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                <span class="font-extrabold text-slate-800 tracking-tight text-[11px]">Powered by <span class="text-slate-950 font-black">GymDeskHub</span></span>
            </div>
        </div>

    </div>

    <!-- Print Tip for Screen -->
    <div class="no-print mt-5 text-center text-xs text-slate-500">
        <p>💡 Tip: For best results, select <strong>A4 Paper</strong> and set Margins to <strong>None / Default</strong> in your print dialog.</p>
    </div>

</body>
</html>
