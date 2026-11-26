<?php

namespace Tests\Unit;

use App\Services\AttendanceQrService;
use Tests\TestCase;

class AttendanceQrServiceTest extends TestCase
{
    public function test_server_rendered_qr_has_white_quiet_zone_and_dark_modules(): void
    {
        $service = new AttendanceQrService();
        $svg = $service->svg('https://gymdeskhub.com/gym/checkin/armour-24-7-gym');
        $this->assertStringContainsString('shape-rendering="crispEdges"', $svg);
        $this->assertStringContainsString('M4 4h1v1h-1z', $svg);
        $this->assertStringContainsString('fill="#fff"', $svg);
        $this->assertStringContainsString('fill="#000"', $svg);
        $this->assertSame($svg, $service->svg('https://gymdeskhub.com/gym/checkin/armour-24-7-gym'));
        $this->assertNotSame($svg, $service->svg('https://gymdeskhub.com/gym/checkin/another-gym'));
    }
}
