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

    /** Raster formats GD can decode + re-encode. */
    protected const ALLOWED_MIMES = ['image/jpeg','image/png','image/gif','image/webp','image/bmp'];

    /** Vector / icon formats GD cannot process — stored as-is. */
    protected const RAW_MIMES = ['image/svg+xml','image/x-icon','image/vnd.microsoft.icon'];

    /**
     * Validate, optimise and store an uploaded image on the public disk.
     *
     * @param bool $allowVector When true, SVG / ICO files are accepted and
     *                          stored without re-encoding (GD can't process
     *                          them). Used for logos & favicons, where SVG is
     *                          the natural format. SVGs are sanitised first.
     * @return string relative path on public disk, e.g. "uploads/posts/65f1a2b3c4d5e.webp"
     * @throws \InvalidArgumentException with a user-friendly message
     */
    public function optimizeAndStore(UploadedFile $file, string $dir = 'uploads', bool $allowVector = false): string
    {
        if (!$file->isValid()) {
            throw new \InvalidArgumentException('Invalid file upload.');
        }
        $mime = $file->getMimeType();

        // SVG / ICO — keep original bytes (already tiny), after sanitising SVGs.
        if ($allowVector && in_array($mime, self::RAW_MIMES, true)) {
            return $this->storeRaw($file, $dir);
        }

        if (!in_array($mime, self::ALLOWED_MIMES, true)) {
            $allowed = 'JPG, PNG, GIF, WebP, BMP' . ($allowVector ? ', SVG, ICO' : '');
            throw new \InvalidArgumentException(
                'Unsupported image type "' . $mime . '". Allowed: ' . $allowed . '.'
            );
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

    /**
     * Store SVG / ICO uploads untouched (GD cannot re-encode them, and they
     * are already compact). SVG content is sanitised to strip scripts and
     * event handlers before it is written to disk.
     */
    protected function storeRaw(UploadedFile $file, string $dir): string
    {
        $contents = file_get_contents($file->getRealPath());
        if ($contents === false || $contents === '') {
            throw new \InvalidArgumentException('The uploaded file could not be read.');
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: '');
        $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'bin';

        if ($ext === 'svg') {
            // Strip <script> blocks, inline event handlers and javascript: URLs.
            $contents = preg_replace('#<script(\s[^>]*)?>.*?</script>#is', '', $contents) ?? $contents;
            $contents = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $contents) ?? $contents;
            $contents = preg_replace('/javascript\s*:/i', '', $contents) ?? $contents;
        }

        $relative = trim($dir, '/') . '/' . uniqid('', true) . '.' . $ext;
        Storage::disk('public')->makeDirectory(trim($dir, '/'));
        Storage::disk('public')->put($relative, $contents);

        return $relative;
    }
}
