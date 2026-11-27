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
