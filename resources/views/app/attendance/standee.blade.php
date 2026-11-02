<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Attendance QR Standee - {{ $tenant->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-card {
                border: 2px solid #000 !important;
                box-shadow: none !important;
                margin: 0 auto !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col items-center justify-center p-4 sm:p-8">

    <!-- Top Action Bar -->
    <div class="no-print w-full max-w-md flex items-center justify-between mb-6">
        <a href="{{ route('app.attendance.index') }}" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-white transition-colors font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Attendance</span>
        </a>

        <button type="button" onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:brightness-110 text-slate-950 font-black text-xs transition-all shadow-lg shadow-orange-500/20 flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Print Standee Poster</span>
        </button>
    </div>

    <!-- Standee Printable Card (A4 Aspect Ratio Ready) -->
    <div class="print-card w-full max-w-md bg-white text-slate-900 rounded-3xl border border-slate-200 shadow-2xl overflow-hidden text-center p-8 sm:p-10 space-y-6">
        
        <!-- Gym Header -->
        <div class="space-y-3 border-b-2 border-slate-100 pb-6">
            @if(!empty($tenant->logo_url))
                <img src="{{ asset('storage/' . $tenant->logo_url) }}" alt="{{ $tenant->name }}" class="h-16 sm:h-20 w-auto max-w-[220px] mx-auto object-contain">
            @else
                <div class="w-16 h-16 rounded-2xl bg-amber-500 text-slate-950 font-black text-2xl flex items-center justify-center mx-auto shadow-md">
                    {{ strtoupper(substr($tenant->name ?: 'G', 0, 2)) }}
                </div>
            @endif

            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-950 tracking-tight uppercase">
                    {{ $tenant->name }}
                </h1>
                <p class="text-xs text-slate-500 font-semibold tracking-wide uppercase mt-1">
                    Self Attendance Check-In Standee
                </p>
            </div>
        </div>

        <!-- QR Code Standee Hero -->
        <div class="py-2 space-y-4">
            <span class="inline-block px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-black uppercase tracking-wider">
                📷 Scan to Check In
            </span>

            <div class="p-4 bg-slate-50 rounded-2xl border-2 border-slate-200 inline-block shadow-inner">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data={{ urlencode($checkinUrl) }}" 
                     alt="Gym Checkin QR Code" 
                     class="w-56 h-56 mx-auto rounded-lg">
            </div>

            <p class="text-xs text-slate-600 font-medium max-w-xs mx-auto leading-relaxed">
                Scan with any mobile camera (iPhone Camera, Google Lens, Paytm, WhatsApp) or through your <strong>GymDeskHub Member Portal</strong>.
            </p>
        </div>

        <!-- 3 Simple Steps -->
        <div class="grid grid-cols-3 gap-2 pt-4 border-t-2 border-slate-100 text-center text-[11px]">
            <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                <span class="block text-base font-black text-amber-500">1</span>
                <span class="font-bold text-slate-700">Open Camera</span>
            </div>
            <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                <span class="block text-base font-black text-amber-500">2</span>
                <span class="font-bold text-slate-700">Scan QR</span>
            </div>
            <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                <span class="block text-base font-black text-emerald-600">3</span>
                <span class="font-bold text-slate-700">Marked!</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="pt-3 text-[10px] text-slate-400 flex items-center justify-between">
            <span>{{ $tenant->phone ?? 'Ask Reception for assistance' }}</span>
            <span class="font-bold text-slate-600">Powered by GymDeskHub</span>
        </div>
    </div>

    <!-- Print Tip for Screen -->
    <div class="no-print mt-6 text-center text-xs text-slate-400">
        <p>💡 Tip: Place this printed poster on your reception desk or turnstile entrance for automatic member attendance.</p>
    </div>

</body>
</html>
