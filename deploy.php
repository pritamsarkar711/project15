<?php
/**
 * Huvanti Deploy Helper — visit this page after a Git pull on Hostinger.
 * It clears all Laravel caches, runs pending migrations, and restores
 * the Composer autoloader from backup. No SSH needed.
 *
 * Security: requires the APP_KEY from .env (same as doctor.php).
 */

declare(strict_types=1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

const ROOT = __DIR__;

header('Cache-Control: no-store, max-age=0');
header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');

// ----- CSRF-like gate: require ?key=<APP_KEY> or cookie -----
$envFile = ROOT . '/.env';
$appKey = '';
if (is_file($envFile)) {
    foreach (file($envFile) ?: [] as $line) {
        if (preg_match('/^APP_KEY=base64:(.+)/i', trim($line), $m)) {
            $appKey = $m[1];
            break;
        }
    }
}

$token = $_GET['key'] ?? ($_COOKIE['huvanti_deploy_key'] ?? '');
$authenticated = ($appKey !== '' && hash_equals($appKey, $token));

// Auto-auth via cookie if submitted via form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deploy_key'])) {
    if ($appKey !== '' && hash_equals($appKey, $_POST['deploy_key'])) {
        $authenticated = true;
        setcookie('huvanti_deploy_key', $_POST['deploy_key'], [
            'expires' => time() + 86400,
            'path' => '/',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    }
}

if (!$authenticated) {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Deploy · Huvanti</title>
    <style>*,*::before,*::after{box-sizing:border-box}body{font-family:Inter,-apple-system,BlinkMacSystemFont,sans-serif;background:#FAFAFA;color:#1C1B1F;margin:0;padding:32px 20px}
    .page{max-width:480px;margin:0 auto;background:#fff;border:1px solid rgba(0,0,0,.1);border-radius:12px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,.08)}
    h1{font-size:20px;margin:0 0 6px}.sub{color:#6E6D78;font-size:14px;margin:0 0 20px}
    input{width:100%;min-height:44px;padding:10px 14px;border:1.5px solid rgba(0,0,0,.1);border-radius:8px;font-size:14px;font-family:inherit;background:#F3F3F5;margin-bottom:12px}
    input:focus{outline:none;border-color:#1565C0;box-shadow:0 0 0 3px rgba(21,101,192,.16);background:#fff}
    .btn{display:inline-block;width:100%;min-height:44px;padding:0 24px;background:#1565C0;color:#fff;border:0;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer}
    .btn:hover{background:#0D47A1}</style></head><body>
    <div class="page"><h1>Huvanti Deploy Helper</h1>
    <p class="sub">Enter your APP_KEY to proceed. Find it in <code>.env</code>.</p>
    <form method="POST"><input type="password" name="deploy_key" placeholder="Paste APP_KEY here (base64:...)">
    <button type="submit" class="btn">Authenticate</button></form>
    <p style="margin-top:16px;font-size:12px;color:#6E6D78">After Git deploy on Hostinger, this page clears view cache, config cache, and runs pending migrations.</p>
    </div></body></html>';
    exit;
}

// ----- Authenticated: perform deploy actions -----
$actions = [];

// 1. Restore autoloader from backup
$backupDir = ROOT . '/bootstrap/autoload_backup';
$composerDir = ROOT . '/vendor/composer';
if (is_dir($backupDir) && is_dir($composerDir)) {
    $files = ['ClassLoader.php', 'InstalledVersions.php', 'autoload_classmap.php',
        'autoload_files.php', 'autoload_namespaces.php', 'autoload_psr4.php',
        'autoload_real.php', 'autoload_static.php', 'installed.php', 'platform_check.php'];
    $restored = 0;
    foreach ($files as $file) {
        $src = $backupDir . '/' . $file;
        $dst = $composerDir . '/' . $file;
        $content = @file_get_contents($src);
        if ($content !== false && @file_put_contents($dst, $content, LOCK_EX)) {
            @chmod($dst, 0644);
            $restored++;
        }
    }
    // Also restore vendor/autoload.php
    $content = @file_get_contents($backupDir . '/autoload.php');
    if ($content !== false) {
        @file_put_contents(ROOT . '/vendor/autoload.php', $content, LOCK_EX);
    }
    if (function_exists('opcache_reset')) @opcache_reset();
    $actions[] = "<span style=\"color:#00897B\">Autoloader restored</span> ($restored files from bootstrap/autoload_backup/)";
} else {
    $actions[] = '<span style="color:#D4183D">Autoloader backup not found</span>';
}

// 2. Clear view cache
$viewCacheDir = ROOT . '/storage/framework/views';
$viewCount = 0;
if (is_dir($viewCacheDir)) {
    foreach (glob($viewCacheDir . '/*.php') as $f) {
        if (@unlink($f)) $viewCount++;
    }
}
$actions[] = "<span style=\"color:#00897B\">View cache cleared</span> ($viewCount compiled templates deleted)";

// 3. Clear ALL application cache (settings, config, everything)
$cacheDir = ROOT . '/storage/framework/cache';
$cacheCount = 0;
if (is_dir($cacheDir . '/data')) {
    foreach (glob($cacheDir . '/data/*') as $f) {
        if (is_file($f) && @unlink($f)) $cacheCount++;
    }
}
$actions[] = "<span style=\"color:#00897B\">Application cache cleared</span> ($cacheCount files — includes settings cache)";

// 4. Create storage symlink (public/storage → storage/app/public)
$symlinkTarget = ROOT . '/storage/app/public';
$symlinkLink = ROOT . '/public/storage';
if (!is_link($symlinkLink) && !is_dir($symlinkLink)) {
    if (function_exists('symlink') && @symlink($symlinkTarget, $symlinkLink)) {
        $actions[] = '<span style="color:#00897B">Storage symlink created</span>';
    } else {
        $actions[] = '<span style="color:#F57C00">Storage symlink skipped</span> (symlink disabled — PHP fallback route handles /storage/*)';
    }
} else {
    $actions[] = '<span style="color:#6E6D78">Storage symlink exists</span>';
}

// 5. Write deploy version file (triggers auto-clear on next request too)
$versionFile = ROOT . '/storage/framework/.huvanti_deploy_version';
@file_put_contents($versionFile, 'v14-2026-08-25-panel-reactions', LOCK_EX);

// 6. Run pending migrations (requires Laravel to boot).
//    `migrate --force` is IDEMPOTENT — when nothing is pending Laravel replies
//    "Nothing to migrate" and no SQL runs, so we ALWAYS call it. (The previous
//    logic only migrated when the review_status column was missing, which
//    silently skipped every later migration — e.g. the new categories.)
$migrationOutput = '';
try {
    require ROOT . '/vendor/autoload.php';
    $app = require ROOT . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    $migrationOutput = trim(\Illuminate\Support\Facades\Artisan::output());
    if ($migrationOutput === '' || str_contains($migrationOutput, 'Nothing to migrate')) {
        $actions[] = "<span style=\"color:#6E6D78\">Database up to date</span> (no pending migrations)";
    } else {
        $actions[] = "<span style=\"color:#00897B\">Migrations ran</span> (" . htmlspecialchars($migrationOutput) . ")";
    }

    // Also clear config and route cache via Artisan
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    $actions[] = "<span style=\"color:#00897B\">Config & route cache cleared</span>";
} catch (\Throwable $e) {
    $actions[] = '<span style="color:#D4183D">Could not run migrations</span> — ' . htmlspecialchars(substr($e->getMessage(), 0, 120));
}

if (function_exists('opcache_reset')) @opcache_reset();

// ----- Render result page -----
$rows = '';
foreach ($actions as $a) {
    $rows .= '<li style="margin-bottom:8px">' . $a . '</li>';
}

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Deploy Complete · Huvanti</title>
<style>*,*::before,*::after{box-sizing:border-box}body{font-family:Inter,-apple-system,BlinkMacSystemFont,sans-serif;background:#FAFAFA;color:#1C1B1F;margin:0;padding:32px 20px}
.page{max-width:520px;margin:0 auto;background:#fff;border:1px solid rgba(0,0,0,.1);border-radius:12px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,.08)}
h1{font-size:22px;margin:0 0 4px}.sub{color:#6E6D78;font-size:14px;margin:0 0 20px}
ul{padding-left:20px;margin:0 0 24px}li{font-size:14px;line-height:1.6}
.btn{display:inline-block;padding:10px 24px;background:#1565C0;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;font-size:14px;margin-right:8px}
.btn.secondary{background:#fff;color:#1565C0;border:1.5px solid #1565C0}
</style></head><body>
<div class="page">
<h1>Deploy Complete</h1>
<p class="sub">All caches cleared and migrations are up to date.</p>
<ul>' . $rows . '</ul>
<a class="btn" href="/">Visit your site</a>
<a class="btn secondary" href="/manage">Admin panel</a>
</div></body></html>';
