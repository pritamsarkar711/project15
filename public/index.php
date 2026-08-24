<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// ---------------------------------------------------------------------------
// Huvanti shared-hosting bootstrap (hardened).
//
// On Hostinger shared hosting the Composer autoloader inside vendor/ has been
// clobbered in the past by the host's auto-deploy running `composer install`
// against a dependency-less composer.json. The regenerated maps then knew
// nothing about Illuminate/Symfony and every request died with a blank
// HTTP 500 before Laravel could even boot (and before it could render its
// own error page). The site became impossible to debug from the outside.
//
// This front controller therefore:
//   1. Verifies the committed autoloader still maps Illuminate\ — and if not,
//      restores it from bootstrap/autoload_backup/ (pristine copies, see the
//      README in that directory). Self-healing, zero interaction needed.
//   2. If anything still fails before Laravel's exception handler can take
//      over, renders a readable diagnostic page instead of a blank 500, with
//      a checklist and a link to /install.php?repair=1.
// ---------------------------------------------------------------------------

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/**
 * Cheap, side-effect-free check that the on-disk autoloader still contains
 * the framework's class maps (i.e. composer never regenerated them from a
 * dependency-less composer.json). We inspect the file contents instead of
 * requiring it, because requiring a clobbered autoloader would define its
 * broken classes and make a restore-then-require impossible.
 */
function huvanti_autoloader_is_pristine(): bool
{
    $map = __DIR__.'/../vendor/composer/autoload_psr4.php';
    if (!is_file($map)) {
        return false;
    }

    $head = @file_get_contents($map, false, null, 0, 65536);
    if ($head === false) {
        return false;
    }

    return str_contains($head, "'Illuminate\\\\'");
}

/**
 * Copy the pristine autoloader files from bootstrap/autoload_backup/ back
 * over vendor/composer/ + vendor/autoload.php. Returns [copied, failed].
 */
function huvanti_restore_autoloader_backup(): array
{
    $backupDir = __DIR__.'/../bootstrap/autoload_backup';
    $composerDir = __DIR__.'/../vendor/composer';

    if (!is_dir($backupDir) || !is_dir(__DIR__.'/../vendor')) {
        return [[], ['backup directory or vendor/ missing']];
    }
    if (!is_dir($composerDir) && !@mkdir($composerDir, 0775, true)) {
        return [[], ['cannot create vendor/composer/']];
    }

    $copied = [];
    $failed = [];

    foreach (scandir($backupDir) ?: [] as $file) {
        if ($file === '.' || $file === '..' || !str_ends_with($file, '.php')) {
            continue;
        }
        $src = $backupDir.'/'.$file;
        $dst = $file === 'autoload.php'
            ? __DIR__.'/../vendor/autoload.php'
            : $composerDir.'/'.$file;

        if (@copy($src, $dst)) {
            @chmod($dst, 0644);
            $copied[] = $file;
        } else {
            $failed[] = $file;
        }
    }

    return [$copied, $failed];
}

/**
 * Render a human-readable failure page instead of the web server's blank
 * HTTP 500. Reaches the browser even when Laravel itself cannot boot.
 */
function huvanti_render_boot_failure_page(?Throwable $e, array $notes = []): void
{
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        header('X-Robots-Tag: noindex');
    }

    $h = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

    $checks = [
        'PHP version' => [
            'ok' => PHP_VERSION_ID >= 80300,
            'value' => PHP_VERSION.(PHP_VERSION_ID >= 80300 ? '' : ' — Huvanti needs PHP 8.3+ (hPanel → PHP Configuration)'),
        ],
        'vendor/autoload.php' => [
            'ok' => is_file(__DIR__.'/../vendor/autoload.php'),
            'value' => is_file(__DIR__.'/../vendor/autoload.php') ? 'present' : 'missing — re-deploy from Git',
        ],
        'vendor/laravel/framework/' => [
            'ok' => is_dir(__DIR__.'/../vendor/laravel/framework'),
            'value' => is_dir(__DIR__.'/../vendor/laravel/framework')
                ? 'present'
                : 'deleted (composer uninstall damage) — delete vendor/ + bootstrap/cache/* via File Manager, then Git → Deploy',
        ],
        '.env file' => [
            'ok' => is_file(__DIR__.'/../.env'),
            'value' => is_file(__DIR__.'/../.env') ? 'present' : 'missing — open /install.php',
        ],
        'APP_KEY set' => [
            'ok' => is_file(__DIR__.'/../.env')
                && (bool) preg_grep('/^APP_KEY=base64:./', file(__DIR__.'/../.env') ?: []),
            'value' => 'checked in .env',
        ],
        'storage/ writable' => [
            'ok' => is_writable(__DIR__.'/../storage'),
            'value' => is_writable(__DIR__.'/../storage') ? 'yes' : 'no — chmod 755/775 storage/ in File Manager',
        ],
        'bootstrap/cache/ writable' => [
            'ok' => is_writable(__DIR__.'/../bootstrap/cache'),
            'value' => is_writable(__DIR__.'/../bootstrap/cache') ? 'yes' : 'no — chmod 755/775 bootstrap/cache/ in File Manager',
        ],
        'Autoloader maps intact' => [
            'ok' => huvanti_autoloader_is_pristine(),
            'value' => huvanti_autoloader_is_pristine()
                ? 'yes'
                : 'no — reload this page (auto-restore) or use /install.php?repair=1',
        ],
    ];

    $rows = '';
    foreach ($checks as $label => $c) {
        $cls = $c['ok'] ? 'ok' : 'bad';
        $mark = $c['ok'] ? '&#10003;' : '&#10007;';
        $rows .= "<tr class=\"{$cls}\"><td>{$mark}</td><td>{$h($label)}</td><td>{$h($c['value'])}</td></tr>";
    }

    $notesHtml = '';
    if ($notes !== []) {
        $notesHtml = '<ul class="notes">';
        foreach ($notes as $note) {
            $notesHtml .= '<li>'.$h($note).'</li>';
        }
        $notesHtml .= '</ul>';
    }

    $errorBlock = '';
    if ($e !== null) {
        $errorBlock = '<details><summary>Error details</summary><pre>'
            .$h(get_class($e).': '.$e->getMessage()."\n".'in '.$e->getFile().':'.$e->getLine()."\n\n".$e->getTraceAsString())
            .'</pre></details>';
    }

    echo <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Server error · Huvanti</title>
<style>
*,*::before,*::after{box-sizing:border-box}
body{font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;
background:#FAFAFA;color:#1C1B1F;line-height:1.6;margin:0;padding:32px 20px}
.page{max-width:640px;margin:0 auto;background:#fff;border:1px solid rgba(0,0,0,.1);
border-radius:12px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,.08)}
h1{font-size:22px;margin:0 0 6px}
.sub{color:#6E6D78;font-size:14px;margin:0 0 20px}
table{width:100%;border-collapse:collapse;font-size:14px}
td{padding:8px 6px;border-bottom:1px solid rgba(0,0,0,.06);vertical-align:top}
tr.ok td:first-child{color:#00897B;font-weight:700}
tr.bad td:first-child{color:#D4183D;font-weight:700}
tr.bad td:nth-child(2){font-weight:600}
ul.notes{background:#FFF8E1;border:1px solid #FFE082;border-radius:8px;
padding:12px 16px 12px 32px;font-size:14px}
.btn{display:inline-block;margin-top:20px;padding:12px 24px;background:#1565C0;
color:#fff;text-decoration:none;border-radius:8px;font-weight:600}
.btn.secondary{background:#fff;color:#1565C0;border:1.5px solid #1565C0;margin-left:8px}
details{margin-top:16px;font-size:13px}
pre{background:#1C1B1F;color:#E6E1E5;padding:14px;border-radius:8px;overflow:auto;
font-size:12px;white-space:pre-wrap}
</style>
</head>
<body>
<div class="page">
<h1>Huvanti hit a server error</h1>
<p class="sub">Laravel could not start, so this page is shown instead of a blank
HTTP 500. Work through the checklist below — red items are the problem.</p>
{$errorBlock}
<table>{$rows}</table>
{$notesHtml}
<a class="btn" href="/install.php?repair=1">Open repair console</a>
<a class="btn secondary" href="/">Retry homepage</a>
</div>
</body>
</html>
HTML;
}

try {
    $autoload = __DIR__.'/../vendor/autoload.php';

    if (!is_file($autoload)) {
        throw new RuntimeException(
            'vendor/autoload.php is missing — the Git deploy did not copy vendor/. '
            .'In hPanel, re-run Git → Deploy (vendor/ is committed to the repo).'
        );
    }

    // Self-heal a clobbered autoloader BEFORE requiring it (see file header).
    if (!huvanti_autoloader_is_pristine()) {
        [$copied, $failed] = huvanti_restore_autoloader_backup();
        if ($failed !== [] || !huvanti_autoloader_is_pristine()) {
            huvanti_render_boot_failure_page(new RuntimeException(
                'The Composer autoloader in vendor/composer/ is damaged and the '
                .'automatic restore from bootstrap/autoload_backup/ failed.'
            ), array_merge(
                ['Autoloader restore failed for: '.($failed !== [] ? implode(', ', $failed) : 'unknown reason')],
                $copied !== [] ? ['Files restored before failing: '.implode(', ', $copied)] : []
            ));
            exit(1);
        }
    }

    // Register the Composer autoloader...
    require $autoload;

    if (!class_exists(Application::class)) {
        // Maps looked fine but the framework classes are gone — composer
        // "uninstalled" the vendored packages. Nothing PHP can do here.
        huvanti_render_boot_failure_page(new RuntimeException(
            'The autoloader maps look intact but the Laravel framework files '
            .'are missing from vendor/. Delete the vendor/ folder via hPanel → '
            .'File Manager, then Git → Deploy to restore it.'
        ));
        exit(1);
    }

    // Bootstrap Laravel and handle the request...
    /** @var Application $app */
    $app = require_once __DIR__.'/../bootstrap/app.php';

    $app->handleRequest(Request::capture());
} catch (Throwable $e) {
    // Anything that blew up before Laravel's own exception handler took over.
    error_log((string) $e);
    huvanti_render_boot_failure_page($e);
    exit(1);
}
