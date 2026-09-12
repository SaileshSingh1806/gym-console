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
                        $resolvedBrandLogo = $brandLogo ?? ($tenant->logo_url ?? null);
                        $resolvedBrandName = $brandName ?? ($tenant->name ?? config('app.name', 'Gym Console'));
                        $resolvedSupportEmail = $supportEmail ?? ($tenant->email ?? null);
                    @endphp
                    <tr>
                        <td style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #db2777 100%); padding: 26px 32px; text-align: center;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        @if(!empty($resolvedBrandLogo))
                                            <div style="margin-bottom: 12px;">
                                                <img src="{{ $resolvedBrandLogo }}" alt="{{ $resolvedBrandName }}" style="max-height: 55px; max-width: 190px; object-fit: contain; border-radius: 10px; background-color: rgba(255, 255, 255, 0.95); padding: 5px 10px; display: inline-block; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);" />
                                            </div>
                                        @endif
                                        <div style="display: inline-block; background-color: rgba(255, 255, 255, 0.15); padding: 7px 16px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.25);">
                                            <span style="font-size: 17px; font-weight: 900; color: #ffffff; letter-spacing: 0.5px; text-transform: uppercase;">
                                                ⚡ {{ $resolvedBrandName }}
                                            </span>
                                        </div>
                                        @if(isset($headerSubtitle))
                                            <p style="margin: 8px 0 0 0; font-size: 12px; color: rgba(255, 255, 255, 0.9); font-weight: 500;">
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
                        <td style="background-color: #0f172a; padding: 24px 32px; border-top: 1px solid #1e293b; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8; line-height: 18px;">
                                © {{ date('Y') }} <strong>{{ $resolvedBrandName }}</strong>. All rights reserved.
                            </p>
                            <p style="margin: 6px 0 0 0; font-size: 11px; color: #64748b; line-height: 16px;">
                                This is an automated system notification regarding your gym management account. If you need assistance, contact our support team.
                            </p>
                            @if(!empty($resolvedSupportEmail))
                                <p style="margin: 8px 0 0 0; font-size: 11px;">
                                    <a href="mailto:{{ $resolvedSupportEmail }}" style="color: #818cf8; text-decoration: none; font-weight: 600;">Contact: {{ $resolvedSupportEmail }}</a>
                                </p>
                            @endif
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>

