<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * GD-based image optimization: validates, resizes to max 1600px wide,
 * converts to WebP (quality 78) and stores on the public disk.
 * Typical result: 90-95%+ smaller than the original upload.
 */
class ImageService
{
    public const MAX_WIDTH = 1600;
    public const QUALITY = 78;

    protected const ALLOWED_MIMES = ['image/jpeg','image/png','image/gif','image/webp','image/bmp'];

    /**
     * @return string relative path on public disk, e.g. "uploads/posts/65f1a2b3c4d5e.webp"
     * @throws \InvalidArgumentException
     */
    public function optimizeAndStore(UploadedFile $file, string $dir = 'uploads'): string
    {
        if (!$file->isValid()) {
            throw new \InvalidArgumentException('Invalid file upload.');
        }
        $mime = $file->getMimeType();
        if (!in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new \InvalidArgumentException('Unsupported image type. Allowed: JPG, PNG, GIF, WebP, BMP.');
        }

        $path = $file->getRealPath();
        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png'  => @imagecreatefrompng($path),
            'image/gif'  => @imagecreatefromgif($path),
            'image/webp' => @imagecreatefromwebp($path),
            'image/bmp'  => @imagecreatefrombmp($path),
        };
        if (!$src) {
            throw new \InvalidArgumentException('Image could not be processed.');
        }

        $w = imagesx($src);
        $h = imagesy($src);

        $needsRebuild = $w > self::MAX_WIDTH || !imageistruecolor($src);
        if ($needsRebuild) {
            $nw = min($w, self::MAX_WIDTH);
            $nh = (int) round($h * ($nw / $w));
            $dst = imagecreatetruecolor($nw, $nh);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $transparent);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($src);
            $src = $dst;
        } else {
            imagealphablending($src, false);
            imagesavealpha($src, true);
        }

        $tmp = tempnam(sys_get_temp_dir(), 'webp');
        imagewebp($src, $tmp, self::QUALITY);
        imagedestroy($src);

        $relative = trim($dir, '/').'/'.uniqid('', true).'.webp';
        Storage::disk('public')->makeDirectory(trim($dir, '/'));
        try {
            Storage::disk('public')->put($relative, file_get_contents($tmp));
        } finally {
            if (file_exists($tmp)) {
                @unlink($tmp);
            }
        }

        return $relative;
    }
}
