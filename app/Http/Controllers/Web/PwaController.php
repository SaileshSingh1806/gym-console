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
        $version = $this->version($tenant);
        $name = trim($tenant->settings['pwa_name'] ?? '') ?: $tenant->name;
        $short = trim($tenant->settings['pwa_short_name'] ?? '') ?: $name;
        $icons = [];
        foreach ([192, 512] as $size) {
            $icons[] = ['src' => route('pwa.icon', [$slug, $role, $size, 'any', 'v' => $version], false),
                'sizes' => "{$size}x{$size}", 'type' => 'image/png', 'purpose' => 'any'];
        }
        $icons[] = ['src' => route('pwa.icon', [$slug, $role, 512, 'maskable', 'v' => $version], false),
            'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'];

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
        return substr(hash('sha256', json_encode(['icon-v3', $tenant->logo_url, $tenant->settings['pwa_icon_path'] ?? null, $tenant->updated_at?->timestamp])), 0, 20);
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

    public function icon(string $slug, string $role, int $size, string $purpose)
    {
        abort_unless(in_array($size, [180, 192, 512], true) && ($purpose !== 'maskable' || $size === 512), 404);
        $tenant = $this->tenant($slug);
        $fallback = base_path('public/pwa/icons/'.$role.'-'.($purpose === 'maskable' ? 'maskable-' : '').$size.'.png');
        $headers = ['Content-Type' => 'image/png', 'Cache-Control' => 'public, max-age=300', 'X-Content-Type-Options' => 'nosniff'];
        $fallbackResponse = static function () use ($fallback, $headers) {
            abort_unless(is_file($fallback), 503, 'PWA fallback icon unavailable');
            return response(file_get_contents($fallback), 200, $headers);
        };
        try {
            $path = $this->localLogo($tenant);
            $disk = Storage::disk('public');
            if (!$path || !function_exists('imagecreatefromstring') || !$disk->exists($path) || $disk->size($path) > 10485760) {
                return $fallbackResponse();
            }
            $source = $disk->get($path);
            if (!is_string($source) || $source === '') return $fallbackResponse();
            $info = @getimagesizefromstring($source);
            if (!$info || $info[0] * $info[1] > 16000000 || !in_array($info['mime'], ['image/png', 'image/jpeg', 'image/webp'], true)) {
                return $fallbackResponse();
            }
            $image = @imagecreatefromstring($source);
            if (!$image) return $fallbackResponse();
            $canvas = imagecreatetruecolor($size, $size);
            if (!$canvas) return $fallbackResponse();
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 13, 14, 16));
            $limit = (int) floor($size * ($purpose === 'maskable' ? 0.65 : 0.9));
            $scale = min($limit / imagesx($image), $limit / imagesy($image), 1);
            $width = max(1, (int) round(imagesx($image) * $scale));
            $height = max(1, (int) round(imagesy($image) * $scale));
            if (!imagecopyresampled($canvas, $image, (int) (($size-$width)/2), (int) (($size-$height)/2), 0, 0, $width, $height, imagesx($image), imagesy($image))) return $fallbackResponse();
            ob_start();
            try {
                $ok = imagepng($canvas, null, 6);
                $png = ob_get_contents();
            } finally {
                ob_end_clean();
            }
            $actual = is_string($png) ? @getimagesizefromstring($png) : false;
            if (!$ok || !$actual || $actual[0] !== $size || $actual[1] !== $size || $actual['mime'] !== 'image/png') return $fallbackResponse();
            return response($png, 200, $headers);
        } catch (\Throwable $e) {
            report($e);
            return $fallbackResponse();
        }
    }

    public function launch(Request $request, string $slug, string $role)
    {
        $tenant = $this->tenant($slug);
        $user = $request->user();
        if (!$user) return redirect()->guest(route('login'));
        $allowed = $role === 'member' ? $user->isMember() : (!$user->isMember() && !$user->isGymOwner() && !$user->isSuperAdmin() && $user->isTrainer());
        abort_unless((int) $user->tenant_id === (int) $tenant->id && $allowed && $user->status === 'ACTIVE', 403);
        return redirect()->route($role === 'member' ? 'app.portal' : 'trainer.workspace');
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
            $settings['pwa_icon_path'] = $request->file('pwa_icon')->store('pwa-branding/'.$tenant->id, 'public');
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
