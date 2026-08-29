<?php

if (!function_exists('setting')) {
    function setting($key, $default = null) {
        return \App\Models\Setting::get($key, $default);
    }
}
if (!function_exists('site_name')) {
    function site_name() { return setting('site_name', config('app.name','Huvanti')); }
}

if (!function_exists('storage_image_url')) {
    /**
     * Resolve a stored upload path (e.g. "uploads/posts/abc.webp") to the
     * URL the browser must fetch.
     *
     * WHY THIS EXISTS: uploads are stored on the "public" disk as paths
     * WITHOUT a leading slash ("uploads/posts/abc.webp"). Printing such a
     * value raw inside src="..." makes the browser resolve it RELATIVE to
     * the current page, so a post at /blog/how-to-x requests
     * /blog/uploads/posts/abc.webp → 404 → the featured image never shows.
     * Every uploaded-image <img> on the site must go through this helper
     * (or the equivalent '/storage/'.$path prefix).
     *
     * Handles:
     *   - empty value            → null (caller decides on a fallback)
     *   - absolute URLs (http…)  → unchanged (external/legacy images)
     *   - paths already prefixed → unchanged ("/storage/…", "storage/…")
     *   - anything else          → "/storage/".$path (root-relative, works
     *                              with the RelativeAssetUrlGenerator setup)
     */
    function storage_image_url(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }
        if (preg_match('~^(https?:)?//~i', $path) || str_starts_with($path, 'data:')) {
            return $path;
        }
        if (str_starts_with($path, '/')) {
            // Already root-relative; normalise a double "/storage/storage" just in case.
            return str_starts_with($path, '/storage/') ? $path : $path;
        }
        if (str_starts_with($path, 'storage/')) {
            return '/'.$path;
        }
        return '/storage/'.$path;
    }
}

if (!function_exists('image_alt_text')) {
    /**
     * Build alt text from an uploaded image's FILE NAME.
     *
     * The site keeps the original file name of every upload (e.g.
     * "how-to-remove-pilling-from-clothes.webp"), so we can reuse it as the
     * image's alt text: "how to remove pilling from clothes". Falls back to
     * $fallback (typically the post title) when the file name carries no
     * usable words (e.g. "IMG_2043", "123").
     */
    function image_alt_text(?string $path, string $fallback = ''): string
    {
        $alt = '';
        $path = trim((string) $path);
        if ($path !== '') {
            // Strip any directory/URL part, then the extension.
            $name = pathinfo(parse_url($path, PHP_URL_PATH) ?: $path, PATHINFO_FILENAME);
            // Humanize: hyphens/underscores/dots → spaces, collapse whitespace.
            $name = str_replace(['-', '_', '.'], ' ', $name);
            $name = trim(preg_replace('/\s+/u', ' ', $name) ?? '');
            // Ignore meaningless camera/file names ("IMG 2043", "123", "image").
            $meaningful = preg_match('/[\p{L}]{3,}/u', $name) && !preg_match('/^(img|image|photo|picture|dsc|screenshot|untitled)[\s\d]*$/i', $name);
            $alt = $meaningful ? $name : '';
        }
        return $alt !== '' ? $alt : trim($fallback);
    }
}
