<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        $plans = Plan::with('features')->where('is_active', true)->orderBy('sort_order')->get();

        return view('public.home', compact('plans'));
    }

    public function features(): View
    {
        return view('public.features');
    }

    public function pricing(): View
    {
        $plans = Plan::with('features')->where('is_active', true)->orderBy('sort_order')->get();

        return view('public.pricing', compact('plans'));
    }

    public function about(): View
    {
        return view('public.about');
    }

    public function contact(): View
    {
        return view('public.contact');
    }

    public function sitemap(): \Illuminate\Http\Response
    {
        $urls = [
            ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily', 'lastmod' => now()->toDateString()],
            ['loc' => route('features'), 'priority' => '0.9', 'changefreq' => 'weekly', 'lastmod' => now()->toDateString()],
            ['loc' => route('pricing'), 'priority' => '0.9', 'changefreq' => 'weekly', 'lastmod' => now()->toDateString()],
            ['loc' => route('about'), 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => now()->toDateString()],
            ['loc' => route('contact'), 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => now()->toDateString()],
            ['loc' => route('login'), 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => now()->toDateString()],
            ['loc' => route('register'), 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => now()->toDateString()],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars($u['loc']) . "</loc>\n";
            $xml .= "        <lastmod>{$u['lastmod']}</lastmod>\n";
            $xml .= "        <changefreq>{$u['changefreq']}</changefreq>\n";
            $xml .= "        <priority>{$u['priority']}</priority>\n";
            $xml .= "    </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    public function serveStorageFile(string $path)
    {
        $path = str_replace(['..', "\0"], '', $path);
        $fullPath = storage_path('app/public/'.$path);

        if (! file_exists($fullPath) || is_dir($fullPath)) {
            abort(404);
        }

        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    public function gymCheckin(string $slug, Request $request, AttendanceService $attendanceService): View|RedirectResponse
    {
        $tenant = Tenant::where('slug', $slug)
            ->orWhere('id', is_numeric($slug) ? (int) $slug : 0)
            ->firstOrFail();

        $user = auth()->user();
        $member = null;
        $attendance = null;
        $status = null;
        $message = null;

        if ($user) {
            $cleanUserPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
            $suffix10 = strlen($cleanUserPhone) >= 10 ? substr($cleanUserPhone, -10) : $cleanUserPhone;

            $member = Member::where('tenant_id', $tenant->id)
                ->where(function ($q) use ($user, $suffix10) {
                    $q->where('user_id', $user->id)
                      ->orWhere('email', $user->email);
                    if (! empty($suffix10)) {
                        $q->orWhere('phone', $user->phone)
                          ->orWhere('phone', 'like', "%{$suffix10}");
                    }
                })->first();

            if ($member) {
                if (! $member->isMembershipActive()) {
                    $status = 'expired';
                    $message = "Membership Expired: Your membership at {$tenant->name} has expired. Please renew at the reception.";
                } else {
                    $attendance = $attendanceService->checkIn($member, 'qr_scan');
                    $status = 'success';
                    $message = "Welcome, {$member->full_name}! Check-in recorded at ".now()->format('h:i A').'.';
                }
            }
        }

        return view('public.gym_checkin', compact('tenant', 'member', 'attendance', 'status', 'message', 'slug'));
    }

    public function processGymCheckin(string $slug, Request $request, AttendanceService $attendanceService): View|RedirectResponse
    {
        $tenant = Tenant::where('slug', $slug)
            ->orWhere('id', is_numeric($slug) ? (int) $slug : 0)
            ->firstOrFail();

        $input = trim($request->input('identifier', ''));
        $action = $request->input('action', 'check_in');

        if (empty($input)) {
            return back()->with('error', 'Please enter your Mobile Number or Member ID.');
        }

        $cleanDigits = preg_replace('/[^0-9]/', '', $input);
        $suffix10 = strlen($cleanDigits) >= 10 ? substr($cleanDigits, -10) : $cleanDigits;

        $member = Member::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($input, $suffix10) {
                $q->where('member_code', $input)
                  ->orWhere('email', $input);
                if (! empty($suffix10)) {
                    $q->orWhere('phone', $input)
                      ->orWhere('phone', 'like', "%{$suffix10}");
                }
            })->first();

        if (! $member) {
            return back()->with('error', "No member profile found for '{$input}' at {$tenant->name}. Please contact reception.")->withInput();
        }

        if ($action === 'check_out') {
            $record = $attendanceService->checkOut($member);
            if (! $record) {
                return back()->with('error', "No active check-in found today for {$member->full_name} to check out.");
            }

            return view('public.gym_checkin', [
                'tenant' => $tenant,
                'member' => $member,
                'attendance' => $record,
                'status' => 'checkout',
                'message' => "Goodbye {$member->first_name}! Check-out recorded at ".now()->format('h:i A').'.',
                'slug' => $slug,
            ]);
        }

        if (! $member->isMembershipActive()) {
            return view('public.gym_checkin', [
                'tenant' => $tenant,
                'member' => $member,
                'status' => 'expired',
                'message' => "Membership Inactive: {$member->full_name}, your membership at {$tenant->name} has expired or is inactive. Please renew at the front desk.",
                'slug' => $slug,
            ]);
        }

        $record = $attendanceService->checkIn($member, 'qr_scan');

        return view('public.gym_checkin', [
            'tenant' => $tenant,
            'member' => $member,
            'attendance' => $record,
            'status' => 'success',
            'message' => "Welcome, {$member->full_name}! Check-in recorded at ".now()->format('h:i A').'.',
            'slug' => $slug,
        ]);
    }
}
