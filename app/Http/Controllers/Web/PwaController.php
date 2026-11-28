<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PwaController extends Controller
{
    private function tenant(string $slug): Tenant
    {
        return Tenant::where('slug', $slug)->firstOrFail();
    }

    public function manifest(string $slug, string $role)
    {
        $tenant = $this->tenant($slug);
        $name = trim($tenant->settings['pwa_name'] ?? '') ?: $tenant->name;
        $short = trim($tenant->settings['pwa_short_name'] ?? '') ?: $name;
        $icons = [];
        foreach ([[192, 'any'], [512, 'any'], [512, 'maskable']] as [$size, $purpose]) {
            $icons[] = [
                'src' => $this->installIconUrl($tenant, $role, $size, $purpose),
                'sizes' => "{$size}x{$size}",
                'type' => 'image/png',
                'purpose' => $purpose,
            ];
        }

        return response()->json([
            'id' => '/pwa/tenant/'.$tenant->id.'/'.$role,
            'name' => $name.' '.ucfirst($role),
            'short_name' => Str::limit($short, 24, '').' '.ucfirst($role),
            'start_url' => route('pwa.launch', [$slug, $role], false),
            'scope' => '/app/',
            'display' => 'standalone',
            'background_color' => '#0D0E10',
            'theme_color' => '#0D0E10',
            'icons' => $icons,
        ], 200, ['Content-Type' => 'application/manifest+json', 'Cache-Control' => 'no-cache']);
    }

    private function version(Tenant $tenant): string
    {
        return substr(hash('sha256', json_encode(['static-icon-v4', $tenant->logo_url, $tenant->settings['pwa_icon_path'] ?? null, $tenant->updated_at?->timestamp])), 0, 20);
    }

    private function localLogo(Tenant $tenant): ?string
    {
        $dedicated = $tenant->settings['pwa_icon_path'] ?? null;
        if (is_string($dedicated) && Str::startsWith($dedicated, 'pwa-branding/'.$tenant->id.'/')) {
            return $dedicated;
        }
        $logo = (string) $tenant->logo_url;
        $path = parse_url($logo, PHP_URL_PATH);
        if (!is_string($path)) return null;
        // Never fetch an arbitrary external URL from the server.
        $host = parse_url($logo, PHP_URL_HOST);
        if ($host && strcasecmp($host, (string) parse_url(config('app.url'), PHP_URL_HOST)) !== 0 && strcasecmp($host, request()->getHost()) !== 0) return null;
        $path = ltrim($path, '/');
        if (Str::startsWith($path, 'storage/')) $path = substr($path, 8);
        if (!Str::startsWith($path, 'logos/') || str_contains($path, '..') || str_contains($path, chr(0))) return null;
        return $path;
    }

    /**
     * Generate a public, versioned PNG once. The browser downloads a real static
     * image from the same storage path used for gym logos, not a PHP image route.
     */
    private function installIconUrl(Tenant $tenant, string $role, int $size, string $purpose): string
    {
        $filename = $role.'-'.($purpose === 'maskable' ? 'maskable-' : '').$size.'.png';
        $fallback = '/pwa/icons/'.$filename;
        try {
            $disk = Storage::disk('public');
            $path = $this->localLogo($tenant);
            if (!$path || !$disk->exists($path) || !function_exists('imagecreatefromstring') || $disk->size($path) > 10485760) {
                return $fallback;
            }

            $target = 'pwa-generated/'.$tenant->id.'/'.$this->version($tenant).'/'.$filename;
            if ($disk->exists($target)) {
                return '/storage/'.$target;
            }

            $source = $disk->get($path);
            if (!is_string($source) || $source === '') return $fallback;
            $info = @getimagesizefromstring($source);
            if (!$info || $info[0] * $info[1] > 16777216 || !in_array($info['mime'], ['image/png', 'image/jpeg', 'image/webp'], true)) return $fallback;
            $image = @imagecreatefromstring($source);
            if (!$image) return $fallback;
            $canvas = imagecreatetruecolor($size, $size);
            if (!$canvas) return $fallback;
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 13, 14, 16));
            $limit = (int) floor($size * ($purpose === 'maskable' ? 0.65 : 0.9));
            $scale = min($limit / imagesx($image), $limit / imagesy($image), 1);
            $width = max(1, (int) round(imagesx($image) * $scale));
            $height = max(1, (int) round(imagesy($image) * $scale));
            if (!imagecopyresampled($canvas, $image, (int) (($size-$width)/2), (int) (($size-$height)/2), 0, 0, $width, $height, imagesx($image), imagesy($image))) return $fallback;
            ob_start();
            try {
                $ok = imagepng($canvas, null, 6);
                $png = ob_get_contents();
            } finally {
                ob_end_clean();
            }
            $actual = is_string($png) ? @getimagesizefromstring($png) : false;
            if (!$ok || !$actual || $actual[0] !== $size || $actual[1] !== $size || $actual['mime'] !== 'image/png') return $fallback;
            if (!$disk->put($target, $png, ['visibility' => 'public'])) return $fallback;
            return '/storage/'.$target;
        } catch (\Throwable $e) {
            report($e);
            return $fallback;
        }
    }

    public function icon(string $slug, string $role, int $size, string $purpose)
    {
        abort_unless(in_array($size, [180, 192, 512], true) && ($purpose !== 'maskable' || $size === 512), 404);
        $url = $this->installIconUrl($this->tenant($slug), $role, $size, $purpose);
        return redirect($url)->header('Cache-Control', 'no-store');
    }


    /**
     * iOS needs a full-resolution PNG for its native launch screen.
     * Only known screen sizes are accepted; cache is isolated by tenant and branding version.
     */
    public function startup(string $slug, string $role, int $width, int $height)
    {
        $sizes = [[1320,2868],[1206,2622],[1290,2796],[1179,2556],[1170,2532],[1125,2436],[1284,2778],[1242,2688],[828,1792],[750,1334],[1242,2208]];
        $portrait = [min($width, $height), max($width, $height)];
        abort_unless(in_array($portrait, $sizes, true), 404);
        $tenant = $this->tenant($slug);
        $disk = Storage::disk('public');
        $target = 'pwa-generated/'.$tenant->id.'/'.$this->version($tenant).'/startup-v1-'.$role.'-'.$width.'x'.$height.'.png';

        abort_unless(function_exists('imagecreatetruecolor') && function_exists('imagecreatefromstring'), 503);
        if (!$disk->exists($target)) {
            $source = $this->localLogo($tenant);
            $bytes = $source && $disk->exists($source) && $disk->size($source) <= 10485760 ? $disk->get($source) : null;
            $info = is_string($bytes) ? @getimagesizefromstring($bytes) : false;
            if (!$info || $info[0] * $info[1] > 16777216 || !in_array($info['mime'], ['image/png','image/jpeg','image/webp'], true)) {
                $bytes = @file_get_contents(public_path('pwa/icons/'.$role.'-512.png'));
            }
            $logo = is_string($bytes) ? @imagecreatefromstring($bytes) : false;
            abort_unless($logo, 503);
            $canvas = imagecreatetruecolor($width, $height);
            abort_unless($canvas, 503);
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 13, 14, 16));
            imagealphablending($canvas, true);
            $limit = (int) round(min($width, $height) * 0.36);
            $scale = min($limit / imagesx($logo), $limit / imagesy($logo));
            $w = max(1, (int) round(imagesx($logo) * $scale));
            $h = max(1, (int) round(imagesy($logo) * $scale));
            abort_unless(imagecopyresampled($canvas, $logo, (int) (($width-$w)/2), (int) (($height-$h)/2), 0, 0, $w, $h, imagesx($logo), imagesy($logo)), 503);
            ob_start();
            try {
                $ok = imagepng($canvas, null, 6);
                $png = ob_get_contents();
            } finally {
                ob_end_clean();
            }
            abort_unless($ok && is_string($png) && $disk->put($target, $png, ['visibility' => 'public']), 503);
        }

        return response($disk->get($target), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function redirectLegacyLogin(string $slug, string $role)
    {
        return redirect()->route('pwa.login', [$slug, $role]);
    }

    public function redirectLegacyLaunch(string $slug, string $role)
    {
        return redirect()->route('pwa.launch', [$slug, $role]);
    }

    public function launch(Request $request, string $slug, string $role)
    {
        $tenant = $this->tenant($slug);
        $user = $request->user();
        if (!$user) return redirect()->route('pwa.login', [$slug, $role]);
        $allowed = $role === 'member' ? $user->isMember() : (!$user->isMember() && !$user->isGymOwner() && !$user->isSuperAdmin() && $user->isTrainer());
        abort_unless((int) $user->tenant_id === (int) $tenant->id && $allowed && $user->status === 'ACTIVE', 403);
        return redirect()->route($role === 'member' ? 'app.portal' : 'trainer.workspace');
    }

    public function showLogin(Request $request, string $slug, string $role)
    {
        $tenant = $this->tenant($slug);
        if ($request->user()) return redirect()->route('pwa.launch', [$slug, $role]);
        $name = trim($tenant->settings['pwa_name'] ?? '') ?: $tenant->name;
        $path = $this->localLogo($tenant);
        $logo = $path ? '/storage/'.$path : $this->installIconUrl($tenant, $role, 512, 'any');
        $manifest = route('pwa.manifest', [$slug, $role, 'v' => $this->version($tenant)], false);
        $icon = $this->installIconUrl($tenant, $role, 180, 'any');
        return response()->view('auth.pwa-login', compact('tenant', 'role', 'name', 'logo', 'manifest', 'icon'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function login(Request $request, string $slug, string $role)
    {
        $tenant = $this->tenant($slug);
        $data = $request->validate(['email' => 'required|string|max:190', 'password' => 'required|string|max:1024', 'remember' => 'nullable']);
        $input = trim($data['email']);
        $digits = preg_replace('/[^0-9]/', '', $input);
        $users = \App\Models\User::where('tenant_id', $tenant->id)->where('role', $role)->where('status', 'ACTIVE')
            ->where(function ($query) use ($input, $digits, $tenant, $role) {
                $query->where('email', $input)->orWhere('phone', $input);
                if (strlen($digits) >= 10) $query->orWhere('phone', 'like', '%'.substr($digits, -10));
                if ($role === 'member') {
                    $query->orWhereIn('id', \App\Models\Member::where('tenant_id', $tenant->id)->where('member_code', $input)->whereNotNull('user_id')->select('user_id'));
                }
            })->get();
        foreach ($users as $user) {
            if (\Illuminate\Support\Facades\Hash::check($data['password'], $user->password)) {
                \Illuminate\Support\Facades\Auth::login($user, $request->boolean('remember'));
                $request->session()->regenerate();
                $request->session()->forget('url.intended');
                return redirect()->route('pwa.launch', [$slug, $role]);
            }
        }
        return back()->withErrors(['email' => 'Invalid credentials for this gym. Contact your gym reception if you need account access.'])->onlyInput('email');
    }

    public function saveBranding(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->isGymOwner() && $user->tenant_id, 403);
        $tenant = $user->tenant;
        abort_unless($tenant, 403);
        $data = $request->validate([
            'pwa_name' => 'nullable|string|max:80',
            'pwa_short_name' => 'nullable|string|max:24',
            'pwa_icon' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:10240|dimensions:min_width=512,min_height=512,max_width=4096,max_height=4096',
            'remove_pwa_icon' => 'nullable|boolean',
        ]);
        $settings = $tenant->settings ?? [];
        $settings['pwa_name'] = trim($data['pwa_name'] ?? '') ?: $tenant->name;
        $settings['pwa_short_name'] = trim($data['pwa_short_name'] ?? '') ?: $settings['pwa_name'];
        $old = $settings['pwa_icon_path'] ?? null;
        if ($request->hasFile('pwa_icon')) {
            // Preserve original bytes; no WebP conversion or lossy compression.
            $stored = $request->file('pwa_icon')->store('pwa-branding/'.$tenant->id, 'public');
            if (!$stored) return back()->withErrors(['pwa_icon' => 'Could not save app logo. Please try again.'])->with('active_tab', 'member_app');
            $settings['pwa_icon_path'] = $stored;
        } elseif ($request->boolean('remove_pwa_icon')) {
            unset($settings['pwa_icon_path']);
        }
        $tenant->settings = $settings;
        $tenant->save();
        if ($old && $old !== ($settings['pwa_icon_path'] ?? null) && Str::startsWith($old, 'pwa-branding/'.$tenant->id.'/')) {
            Storage::disk('public')->delete($old);
        }
        return back()->with('success', 'App branding updated.')->with('active_tab', 'member_app');
    }
}
