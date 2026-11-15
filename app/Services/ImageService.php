<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Validates, resizes and converts uploads to WebP (480 / 800 / 1280 / 1600 px wide) using PHP's GD.
 * Files live in storage/app/public/uploads (public via the storage symlink).
 * Returns a Media row whose "path" is a base like storage/uploads/abc123 that <x-photo> turns into a responsive <img>.
 */
class ImageService
{
    public const WIDTHS = [480, 800, 1280, 1600];

    public const MAX_KB = 8192;

    public function store(UploadedFile $file, ?string $alt = null): Media
    {
        $source = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if (! $source) {
            throw new \RuntimeException('That file is not a readable image.');
        }

        $source = $this->orient($source, $file->getRealPath());
        $origW = imagesx($source);
        $origH = imagesy($source);
        $name = Str::lower(Str::random(20));

        // Never upscale: sizes wider than the original reuse the original width.
        foreach (self::WIDTHS as $w) {
            $tw = min($w, $origW);
            $th = (int) round($origH * $tw / $origW);
            $canvas = imagecreatetruecolor($tw, $th);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $tw, $th, $origW, $origH);

            ob_start();
            imagewebp($canvas, null, 80);
            Storage::disk('public')->put("uploads/$name-$w.webp", (string) ob_get_clean());
            imagedestroy($canvas);
        }
        imagedestroy($source);

        return Media::create([
            'path' => "storage/uploads/$name",
            'alt' => $alt,
            'width' => $origW,
            'height' => $origH,
            'variants' => self::WIDTHS,
        ]);
    }

    public function delete(Media $media): void
    {
        $base = Str::after($media->path, 'storage/');
        foreach (self::WIDTHS as $w) {
            Storage::disk('public')->delete("$base-$w.webp");
        }
        $media->delete();
    }

    /** Respect the camera's rotation flag on JPEGs. */
    private function orient(\GdImage $img, string $path): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $img;
        }
        $exif = @exif_read_data($path);
        $angle = match ($exif['Orientation'] ?? 1) {
            3 => 180, 6 => -90, 8 => 90, default => 0,
        };

        return $angle ? (imagerotate($img, $angle, 0) ?: $img) : $img;
    }
}
