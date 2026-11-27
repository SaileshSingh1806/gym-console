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

    public function submitContact(Request $request): RedirectResponse
    {
        if ($request->filled('website')) {
            abort(422);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'gym_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9 ()-]{10,20}$/'],
            'city' => ['required', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:1500'],
        ]);

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "GymDeskHub website inquiry\n\n" .
                "Name: {$data['name']}\nGym: {$data['gym_name']}\nPhone: {$data['phone']}\nCity: {$data['city']}\n\n{$data['message']}",
                fn ($mail) => $mail->to('sales@gymdeskhub.in')->subject('GymDeskHub website inquiry')
            );
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withInput()->with('contact_error', 'We could not send your inquiry. Please try again or contact us directly on WhatsApp.');
        }

        return redirect()->route('contact')->with('contact_success', 'Your inquiry has been sent.');
    }

    public function terms(): View
    {
        return view('public.terms');
    }

    public function privacy(): View
    {
        return view('public.privacy');
    }

    public function refundPolicy(): View
    {
        return view('public.refund-policy');
    }

    public function shippingPolicy(): View
    {
        return view('public.shipping-policy');
    }

    public function sitemap(): \Illuminate\Http\Response
    {
        $urls = [
            ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily', 'lastmod' => now()->toDateString()],
            ['loc' => route('features'), 'priority' => '0.9', 'changefreq' => 'weekly', 'lastmod' => now()->toDateString()],
            ['loc' => route('pricing'), 'priority' => '0.9', 'changefreq' => 'weekly', 'lastmod' => now()->toDateString()],
            ['loc' => route('about'), 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => now()->toDateString()],
            ['loc' => route('contact'), 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => now()->toDateString()],
            ['loc' => route('terms'), 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => now()->toDateString()],
            ['loc' => route('privacy'), 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => now()->toDateString()],
            ['loc' => route('refund-policy'), 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => now()->toDateString()],
            ['loc' => route('shipping-policy'), 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => now()->toDateString()],
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

        // A public QR page must never record attendance merely by being opened.
        $member = null;
        $attendance = null;
        $status = null;
        $message = null;

        return view('public.gym_checkin', compact('tenant', 'member', 'attendance', 'status', 'message', 'slug'));
    }

    public function processGymCheckin(string $slug, Request $request, AttendanceService $attendanceService): View|RedirectResponse
    {
        $tenant = Tenant::where('slug', $slug)
            ->orWhere('id', is_numeric($slug) ? (int) $slug : 0)
            ->firstOrFail();

        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'action' => ['nullable', 'in:check_in,check_out'],
        ]);

        $input = trim($validated['identifier']);
        $action = $validated['action'] ?? 'check_in';

        if ($input === '') {
            return back()->with('error', 'Please enter your mobile number or member ID.');
        }

        // Member codes always take precedence, including numeric codes.
        $members = Member::where('tenant_id', $tenant->id)
            ->where('member_code', $input)
            ->limit(2)->get();

        // Only complete phone numbers may enter the phone lookup path.
        // Never extract a partial number from an alphanumeric member code.
        if ($members->isEmpty() && preg_match('/^[+0-9 ()-]+$/D', $input)) {
            $digits = preg_replace('/[^0-9]/', '', $input);

            if (strlen($digits) >= 10 && strlen($digits) <= 15) {
                $phones = [$input, $digits, '+'.$digits];

                // Indian local and international representations are equivalent.
                if ($tenant->currency === 'INR') {
                    $local = strlen($digits) === 12 && str_starts_with($digits, '91')
                        ? substr($digits, 2)
                        : $digits;
                    if (strlen($local) === 10) {
                        $phones = array_merge($phones, [$local, '91'.$local, '+91'.$local, '+91 '.$local]);
                    }
                }

                $members = Member::where('tenant_id', $tenant->id)
                    ->whereIn('phone', array_unique($phones))
                    ->limit(2)->get();
            }
        } elseif ($members->isEmpty() && filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $members = Member::where('tenant_id', $tenant->id)
                ->where('email', $input)
                ->limit(2)->get();
        }

        if ($members->count() > 1) {
            return back()->with('error', 'Multiple member profiles match. Please contact reception or use your unique member ID.')->withInput();
        }

        $member = $members->first();

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

        $record = $attendanceService->checkIn($member, 'qr');

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
