<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizerService
{
    /**
     * Optimize an image (file, base64 data URL, or path) and store it.
     * Resizes oversized camera images to a max bounding box and converts to high-efficiency WebP.
     * Provides ~70-90% file size reduction with crystal-clear visual quality.
     *
     * @param  UploadedFile|string  $input
     * @param  string  $directory  Target storage directory (e.g. 'members/photos')
     * @param  int  $maxDimension  Maximum width or height in pixels (default 1200)
     * @param  int  $quality  Compression quality 1-100 (default 82)
     * @param  string  $disk  Storage disk (default 'public')
     * @return string|null The saved relative path, or null on failure
     */
    public function optimizeAndStore(
        UploadedFile|string $input,
        string $directory = 'members/photos',
        int $maxDimension = 1200,
        int $quality = 82,
        string $disk = 'public'
    ): ?string {
        if (empty($input)) {
            return null;
        }

        $rawBinary = null;
        $originalExt = 'jpg';

        // 1. Resolve raw binary data
        if ($input instanceof UploadedFile) {
            $mime = (string) $input->getMimeType();
            $originalExt = $input->getClientOriginalExtension() ?: 'jpg';

            // If not an image (e.g. PDF document), store normally without attempting image GD processing
            if (! str_starts_with($mime, 'image/')) {
                return $input->store($directory, $disk);
            }

            $rawBinary = file_get_contents($input->getRealPath());
        } elseif (is_string($input)) {
            if (str_starts_with($input, 'data:image')) {
                // Base64 data URL from webcam / canvas
                $parts = explode(';', $input);
                $dataPart = $parts[1] ?? '';
                $dataSub = explode(',', $dataPart);
                $rawBinary = isset($dataSub[1]) ? base64_decode($dataSub[1]) : null;
            } elseif (file_exists($input)) {
                $rawBinary = file_get_contents($input);
            } else {
                // Attempt raw base64 decode
                $rawBinary = base64_decode($input, true) ?: $input;
            }
        }

        if (empty($rawBinary)) {
            return null;
        }

        // If GD extension is not available, save raw binary
        if (! function_exists('imagecreatefromstring')) {
            $fallbackName = $directory.'/'.Str::random(24).'.'.$originalExt;
            Storage::disk($disk)->put($fallbackName, $rawBinary);

            return $fallbackName;
        }

        // 2. Load into GD Image resource
        $image = @imagecreatefromstring($rawBinary);
        if (! $image) {
            // Fallback: save as raw
            $fallbackName = $directory.'/'.Str::random(24).'.'.$originalExt;
            Storage::disk($disk)->put($fallbackName, $rawBinary);

            return $fallbackName;
        }

        // 3. Fix EXIF orientation (mobile phone photos) if source is a file with EXIF
        if ($input instanceof UploadedFile && function_exists('exif_read_data')) {
            try {
                $exif = @exif_read_data($input->getRealPath());
                if (! empty($exif['Orientation'])) {
                    $image = match ((int) $exif['Orientation']) {
                        3 => imagerotate($image, 180, 0),
                        6 => imagerotate($image, -90, 0),
                        8 => imagerotate($image, 90, 0),
                        default => $image,
                    };
                }
            } catch (\Throwable $e) {
                // Ignore EXIF errors
            }
        }

        // 4. Calculate optimal dimensions without upscaling
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
            $ratio = min($maxDimension / $origWidth, $maxDimension / $origHeight);
            $newWidth = (int) round($origWidth * $ratio);
            $newHeight = (int) round($origHeight * $ratio);

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
            $image = $resized;
        } else {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        // 5. Output buffer to WebP (falls back to JPEG if WebP not supported)
        ob_start();
        $useWebp = function_exists('imagewebp');
        if ($useWebp) {
            imagewebp($image, null, $quality);
            $ext = 'webp';
        } else {
            imagejpeg($image, null, $quality);
            $ext = 'jpg';
        }
        $compressedData = ob_get_clean();

        // 6. Save compressed file to storage disk
        $filename = $directory.'/'.Str::random(24).'.'.$ext;
        Storage::disk($disk)->put($filename, $compressedData);

        return $filename;
    }
}
