<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? 'Gym Console Notification' }}</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #0f172a; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        @media screen and (max-width: 600px) {
            .mobile-padding { padding-left: 16px !important; padding-right: 16px !important; }
            .mobile-stack { display: block !important; width: 100% !important; }
            .mobile-center { text-align: center !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #0b0f19; color: #f8fafc;">

    <!-- Wrapper Table -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed; background-color: #0b0f19;">
        <tr>
            <td align="center" style="padding: 30px 15px 40px 15px;" class="mobile-padding">
                
                <!-- Main Container (600px max) -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #111827; border-radius: 20px; border: 1px solid #1e293b; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);">
                    
                    <!-- Top Gradient Header Brand Bar -->
                    @php
                        $isPlatform = $isPlatformMail ?? false;
                        $globalAppName = \App\Models\Setting::getGlobal('app_name', config('app.name', 'Gym Console'));
                        $globalLogo = \App\Models\Setting::getGlobal('logo_url');
                        $globalSupportEmail = \App\Models\Setting::getGlobal('support_email', 'support@gymconsole.com');
                        $globalSupportPhone = \App\Models\Setting::getGlobal('support_phone', '+91 9974995179');
                        $globalFooterCopyright = $footerCopyright ?? \App\Models\Setting::getGlobal('footer_copyright', '© 2026 ' . $globalAppName . '. All rights reserved.');

                        if ($isPlatform) {
                            $rawLogo = $brandLogo ?? $globalLogo;
                            $resolvedBrandName = $brandName ?? $globalAppName;
                            $resolvedSupportEmail = $supportEmail ?? $globalSupportEmail;
                            $resolvedSupportPhone = $supportPhone ?? $globalSupportPhone;
                        } else {
                            $rawLogo = $brandLogo ?? ($tenant->logo_url ?? ($ticket->tenant->logo_url ?? $globalLogo));
                            $resolvedBrandName = $brandName ?? ($tenant->name ?? ($ticket->tenant->name ?? $globalAppName));
                            $resolvedSupportEmail = $supportEmail ?? ($tenant->email ?? ($ticket->tenant->email ?? $globalSupportEmail));
                            $resolvedSupportPhone = $supportPhone ?? ($tenant->phone ?? ($ticket->tenant->phone ?? $globalSupportPhone));
                        }

                        $resolvedBrandLogo = null;
                        if (!empty($rawLogo)) {
                            $rawLogo = trim($rawLogo);
                            if (str_starts_with($rawLogo, 'http://') || str_starts_with($rawLogo, 'https://')) {
                                $resolvedBrandLogo = $rawLogo;
                            } elseif (str_starts_with($rawLogo, '/')) {
                                $resolvedBrandLogo = url($rawLogo);
                            } else {
                                $resolvedBrandLogo = asset($rawLogo);
                            }
                        }

                        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$resolvedSupportPhone);
                        $resolvedWhatsAppUrl = !empty($cleanPhone) 
                            ? 'https://wa.me/' . $cleanPhone . '?text=' . urlencode('Hi ' . $resolvedBrandName . ' Support, I have a query regarding my gym portal.') 
                            : null;
                    @endphp
                    <tr>
                        <td style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #db2777 100%); padding: 26px 32px; text-align: center;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        @if(!empty($resolvedBrandLogo))
                                            <!-- Gym / Platform Brand Logo (Displayed in place of text name) -->
                                            <div style="display: inline-block;">
                                                <img src="{{ $resolvedBrandLogo }}" 
                                                     alt="{{ $resolvedBrandName }}" 
                                                     style="max-height: 55px; max-width: 220px; object-fit: contain; border-radius: 10px; background-color: rgba(255, 255, 255, 0.95); padding: 6px 12px; display: inline-block; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);" />
                                            </div>
                                        @else
                                            <!-- Fallback Text Brand Name when logo is not uploaded -->
                                            <div style="display: inline-block; background-color: rgba(255, 255, 255, 0.15); padding: 7px 16px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.25);">
                                                <span style="font-size: 17px; font-weight: 900; color: #ffffff; letter-spacing: 0.5px; text-transform: uppercase;">
                                                    ⚡ {{ $resolvedBrandName }}
                                                </span>
                                            </div>
                                        @endif
                                        @if(isset($headerSubtitle))
                                            <p style="margin: 10px 0 0 0; font-size: 12px; color: rgba(255, 255, 255, 0.9); font-weight: 500;">
                                                {{ $headerSubtitle }}
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Main Email Body Content -->
                    <tr>
                        <td style="padding: 36px 32px;" class="mobile-padding">
                            @yield('content')
                        </td>
                    </tr>

                    <!-- Footer Bar -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 26px 32px; border-top: 1px solid #1e293b; text-align: center;">
                            <p style="margin: 0; font-size: 12px; font-weight: 700; color: #cbd5e1; line-height: 18px;">
                                © 2026 {{ $resolvedBrandName }}. All rights reserved.
                            </p>
                            <p style="margin: 6px 0 14px 0; font-size: 11px; color: #64748b; line-height: 16px;">
                                This is an automated system notification regarding your gym management account. If you need assistance, please contact our team.
                            </p>

                            <!-- Support Email & WhatsApp Links -->
                            <table border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 0 auto;">
                                <tr>
                                    @if(!empty($resolvedSupportEmail))
                                        <td align="center" style="padding: 4px 8px;">
                                            <a href="mailto:{{ $resolvedSupportEmail }}" style="color: #818cf8; text-decoration: none; font-size: 11px; font-weight: 600;">
                                                ✉️ {{ $resolvedSupportEmail }}
                                            </a>
                                        </td>
                                    @endif
                                    @if(!empty($resolvedWhatsAppUrl))
                                        <td align="center" style="padding: 4px 8px;">
                                            <a href="{{ $resolvedWhatsAppUrl }}" target="_blank" style="color: #22c55e; text-decoration: none; font-size: 11px; font-weight: 700;">
                                                💬 WhatsApp: {{ $resolvedSupportPhone }}
                                            </a>
                                        </td>
                                    @endif
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>

