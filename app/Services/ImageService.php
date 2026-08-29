<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * GD-based image optimization: validates, resizes to max 1600px wide,
 * converts to WebP (quality 78) and stores on the public disk.
 * Typical result: 90-95%+ smaller than the original upload.
 *
 * Shared-hosting hardening (the fix for "upload shows in preview but never
 * appears on the site"):
 *   - The Laravel "public" disk is configured with throw => false, which
 *     means a failed write returns false SILENTLY. The admin sees
 *     "Settings updated" while the file never landed on disk.
 *   - This service therefore VERIFIES every write and additionally writes
 *     the file to BOTH canonical locations:
 *       1. storage/app/public/<relative>  (Laravel disk, used by the
 *          /storage/{path} fallback route)
 *       2. public/storage/<relative>      (the physical web root, served
 *          directly by Apache; identical to #1 when the symlink works,
 *          a second real copy when public/storage is a real directory)
 *     If both writes fail, a clear exception is thrown so the admin sees
 *     an actionable message instead of silent data loss.
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
     * The ORIGINAL file name is preserved: "Nabil Rahman.jpg" is stored as
     * "Nabil Rahman.webp" (spaces become hyphens for URL safety, the
     * extension reflects the optimised format, a numeric suffix is added
     * only when a file with the same name already exists). Random uniqid
     * names are gone — authors and admins can recognise their uploads by
     * name, and the file name doubles as SEO-friendly alt text on render.
     *
     * @param bool $allowVector When true, SVG / ICO files are accepted and
     *                          stored without re-encoding (GD can't process
     *                          them). Used for logos & favicons, where SVG is
     *                          the natural format. SVGs are sanitised first.
     * @return string relative path on public disk, e.g. "uploads/posts/my-crochet-article.webp"
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
            throw new \InvalidArgumentException('Image could not be processed. The file may be corrupted.');
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

        // Encode to WebP; if this GD build lacks WebP support (or encoding
        // fails), fall back to JPEG so the upload still succeeds.
        $tmp = tempnam(sys_get_temp_dir(), 'img');
        $ext = 'webp';
        $encoded = function_exists('imagewebp') ? @imagewebp($src, $tmp, self::QUALITY) : false;
        if (!$encoded || !is_file($tmp) || filesize($tmp) === 0) {
            $ext = 'jpg';
            $encoded = @imagejpeg($src, $tmp, self::QUALITY);
        }
        imagedestroy($src);
        if (!$encoded || !is_file($tmp) || filesize($tmp) === 0) {
            if (is_file($tmp)) { @unlink($tmp); }
            throw new \InvalidArgumentException('The image could not be processed by the server (GD encoding failed).');
        }

        $contents = file_get_contents($tmp);
        @unlink($tmp);
        if ($contents === false || $contents === '') {
            throw new \InvalidArgumentException('The image could not be read from the server temp folder.');
        }

        $relative = trim($dir, '/') . '/' . $this->uniqueStoredName($file, $dir, $ext);
        $this->persist($relative, $contents);

        return $relative;
    }

    /**
     * Build a collision-free, URL-safe stored file name that KEEPS the
     * original upload's name.
     *
     *   "Nabil Rahman.jpg"   → "Nabil-Rahman.webp"
     *   "my article (v2).png" → "my-article-v2.webp"
     *   "নাবিল রহমান.jpg"     → "নাবিল-রহমান.webp" (unicode kept)
     *
     * Rules:
     *   - whitespace runs collapse to a single hyphen (URL-safe, readable)
     *   - characters unsafe in URLs/paths are stripped; unicode LETTERS and
     *     digits from every script are kept (multibyte-safe)
     *   - the extension comes from the actual re-encoded format ($ext), not
     *     the original one — GD converts everything to WebP/JPEG
     *   - if the target name already exists in EITHER storage location, a
     *     -2 / -3 / … suffix is appended so an upload can never overwrite
     *     an earlier file
     *   - a name that empties out entirely falls back to "image-<random>"
     */
    protected function uniqueStoredName(UploadedFile $file, string $dir, string $ext): string
    {
        $original = pathinfo($file->getClientOriginalName() ?: '', PATHINFO_FILENAME);

        // Strip URL/path-unsafe characters but KEEP unicode letters/digits
        // (Bengali, Arabic, accented Latin…). Spaces, hyphens, underscores
        // and dots are normalised to single hyphens.
        $name = preg_replace('/[^\\p{L}\\p{N}._\-\s]+/u', '', (string) $original) ?? '';
        $name = preg_replace('/[._\-\s]+/u', '-', trim($name)) ?? '';
        $name = trim($name, '-');
        $name = mb_substr($name, 0, 80);
        $name = trim($name, '-');

        if ($name === '') {
            $name = 'image-' . strtolower(\Illuminate\Support\Str::random(6));
        }

        $dir = trim($dir, '/');
        $candidate = $name . '.' . $ext;
        $i = 1;
        while ($this->existsOnDisk($dir . '/' . $candidate)) {
            $candidate = $name . '-' . (++$i) . '.' . $ext;
        }

        return $candidate;
    }

    /** Does this relative path already exist in either storage location? */
    protected function existsOnDisk(string $relative): bool
    {
        try {
            if (Storage::disk('public')->exists($relative)) {
                return true;
            }
        } catch (\Throwable $e) {
            // fall through to the physical check
        }
        return is_file(public_path('storage/' . $relative))
            || is_file(storage_path('app/public/' . $relative));
    }

    /**
     * Store SVG / ICO uploads untouched (GD cannot re-encode them, and they
     * are already compact). SVG content is sanitised to strip scripts and
     * event handlers before it is written to disk. The original file name is
     * kept (see uniqueStoredName()).
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

        $relative = trim($dir, '/') . '/' . $this->uniqueStoredName($file, $dir, $ext);
        $this->persist($relative, $contents);

        return $relative;
    }

    /**
     * Write the file to BOTH serving locations and verify at least one
     * physically landed on disk. Throws a clear, actionable error when the
     * server refuses both writes (permissions / ownership problems).
     */
    protected function persist(string $relative, string $contents): void
    {
        // Location A: the Laravel "public" disk (storage/app/public/...).
        // Disk has throw => false, so a silent false is a real possibility —
        // verify the file physically exists with the right size afterwards.
        $okA = false;
        try {
            Storage::disk('public')->put($relative, $contents);
            $fileA = storage_path('app/public/' . $relative);
            $okA = is_file($fileA) && (int) @filesize($fileA) === strlen($contents);
        } catch (\Throwable $e) {
            $okA = false;
        }

        // Location B: the physical web root (public/storage/...). When the
        // public/storage symlink works this is the same file as A (harmless
        // overwrite); when it is a real directory or missing it guarantees
        // the web server can serve /storage/<relative> directly.
        $okB = false;
        try {
            $fileB = public_path('storage/' . $relative);
            $dirB = dirname($fileB);
            if (!is_dir($dirB)) {
                @mkdir($dirB, 0755, true);
            }
            if (is_dir($dirB)) {
                $okB = (@file_put_contents($fileB, $contents) !== false)
                    && is_file($fileB)
                    && (int) @filesize($fileB) === strlen($contents);
            }
        } catch (\Throwable $e) {
            $okB = false;
        }

        if (!$okA && !$okB) {
            throw new \InvalidArgumentException(
                'The file was uploaded but the server refused to save it. '
                . 'Please make sure the folders "storage/app/public" and "public/storage" exist and are '
                . 'writable (permissions 755 or 775), then upload again.'
            );
        }
    }

    /**
     * Delete a previously stored upload from BOTH locations. Safe to call
     * with null / empty values.
     */
    public function delete(?string $relative): void
    {
        $relative = trim((string) $relative);
        if ($relative === '') {
            return;
        }
        try {
            Storage::disk('public')->delete($relative);
        } catch (\Throwable $e) {
            // ignore — the physical unlink below is the safety net
        }
        @unlink(public_path('storage/' . $relative));
        @unlink(storage_path('app/public/' . $relative));
    }
}
