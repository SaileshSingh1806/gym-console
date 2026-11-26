<?php

namespace App\Services;

final class AttendanceQrService
{
    /**
     * Server-render the original tenant check-in URL as a vector QR image.
     * Four white modules on every side provide the required quiet zone.
     */
    public function svg(string $url): string
    {
        require_once app_path('Support/QrEncoder/qrcode.php');

        $qr = \App\Support\QrEncoder\QRCode::getMinimumQRCode(
            $url, \App\Support\QrEncoder\QR_ERROR_CORRECT_LEVEL_M
        );
        $count = $qr->getModuleCount();
        $size = $count + 8;
        $path = '';
        for ($row = 0; $row < $count; $row++) {
            for ($col = 0; $col < $count; $col++) {
                if ($qr->isDark($row, $col)) {
                    $path .= 'M'.($col + 4).' '.($row + 4).'h1v1h-1z';
                }
            }
        }

        // Only numeric module coordinates enter the SVG; no user input is interpolated.
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$size.' '.$size.'" shape-rendering="crispEdges" role="img" aria-label="Gym check-in QR code"><rect width="'.$size.'" height="'.$size.'" fill="#fff"/><path d="'.$path.'" fill="#000"/></svg>';
    }
}
