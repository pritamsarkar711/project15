<?php
/**
 * Huvanti Doctor — standalone single-file diagnostic + repair tool.
 * -------------------------------------------------------------------------
 * PURPOSE
 *   Visiting https://your-domain.com/doctor.php shows exactly WHY the site is
 *   broken (blank 500 / "Class Illuminate\Foundation\Application not found")
 *   and can fix the most common Hostinger causes right from the browser — no
 *   SSH, no Composer, no full re-deploy needed.
 *
 *   It is self-contained: it does NOT depend on install.php, public/index.php,
 *   or any other repo file being current. Upload just THIS one file.
 *
 * HOW TO USE
 *   1. Upload doctor.php to your site root (public_html/) via File Manager.
 *   2. Open https://your-domain.com/doctor.php in your browser.
 *   3. Read the red items + follow the "What to do now" box.
 *   4. Use the buttons to auto-repair where possible.
 *   5. DELETE doctor.php when finished (there is a button, or do it in File
 *      Manager). Leaving a repair tool online is a (minor) security risk.
 *
 * SECURITY
 *   This file performs no changes unless you click a button (it is read-only
 *   on first load). It never sends data anywhere. Delete it when you're done.
 */

declare(strict_types=1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
@ini_set('display_errors', '1');

const ROOT = __DIR__;

const PRIMARY = '#1565C0';
const PRIMARY_HOVER = '#0D47A1';
const SUCCESS = '#00897B';
const ERROR_C = '#D4183D';
const BG = '#FAFAFA';
const SURFACE = '#FFFFFF';
const SURFACE_VAR = '#F3F3F5';
const ON_SURFACE = '#1C1B1F';
const ON_MUTED = '#6E6D78';
const BORDER = 'rgba(0,0,0,0.10)';

// --- Helpers ---------------------------------------------------------------

function doc_h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/** Both map files must still reference the Illuminate\\ prefix. */
function doc_autoload_maps_ok(): bool {
    foreach (['autoload_psr4.php', 'autoload_static.php'] as $name) {
        $map = ROOT . '/vendor/composer/' . $name;
        if (!is_file($map)) {
            return false;
        }
        $head = @file_get_contents($map, false, null, 0, 65536);
        if ($head === false || !str_contains($head, "'Illuminate\\\\'")) {
            return false;
        }
    }
    return true;
}

/** The framework entry class must physically exist on disk. */
function doc_framework_ok(): bool {
    return is_file(ROOT . '/vendor/laravel/framework/src/Illuminate/Foundation/Application.php');
}

function doc_opcache_forget(string $file): void {
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate($file, true);
    }
}

/** Restore the autoloader from bootstrap/autoload_backup/. Returns [copied[], failed[]]. */
function doc_restore_autoload(): array {
    $backupDir = ROOT . '/bootstrap/autoload_backup';
    $composerDir = ROOT . '/vendor/composer';

    if (!is_dir($backupDir)) {
        return [[], ['bootstrap/autoload_backup/ is missing — a fresh re-deploy is required']];
    }
    if (!is_dir(ROOT . '/vendor') && !@mkdir(ROOT . '/vendor', 0775, true)) {
        return [[], ['cannot create vendor/']];
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
        $dst = $file === 'autoload.php'
            ? ROOT . '/vendor/autoload.php'
            : $composerDir . '/' . $file;
        if (@copy($backupDir . '/' . $file, $dst)) {
            @chmod($dst, 0644);
            doc_opcache_forget($dst);
            $copied[] = $file;
        } else {
            $failed[] = $file;
        }
    }
    return [$copied, $failed];
}

/**
 * Safely probe whether Laravel can boot. Returns ['ok' => bool, 'msg' => string].
 * Restores the autoloader first if it looks damaged, and isolates the test so a
 * fatal during boot is caught rather than killing the whole page.
 */
function doc_probe_boot(): array {
    if (!is_file(ROOT . '/vendor/autoload.php')) {
        return ['ok' => false, 'msg' => 'vendor/autoload.php is missing.'];
    }
    if (!doc_framework_ok()) {
        return ['ok' => false, 'msg' => 'Laravel framework files are missing from vendor/.'];
    }

    // Self-heal a damaged autoloader before testing.
    if (!doc_autoload_maps_ok()) {
        [$copied, $failed] = doc_restore_autoload();
        if ($failed || !doc_autoload_maps_ok()) {
            return ['ok' => false, 'msg' => 'Autoloader maps are damaged and could not be restored from bootstrap/autoload_backup/.'];
        }
    }

    doc_opcache_forget(ROOT . '/vendor/autoload.php');

    try {
        require ROOT . '/vendor/autoload.php';
    } catch (Throwable $e) {
        return ['ok' => false, 'msg' => 'Requiring vendor/autoload.php failed: ' . $e->getMessage()];
    }

    if (!class_exists('Illuminate\Foundation\Application')) {
        return ['ok' => false, 'msg' => 'Autoloader loaded but Illuminate\Foundation\Application is still not found — stale OPcache is the likely cause (wait ~1 min and retry, or ask Hostinger to flush OPcache).'];
    }

    if (!is_file(ROOT . '/.env')) {
        return ['ok' => false, 'msg' => '.env is missing — run the installer (install.php) or create it from .env.example.'];
    }

    try {
        $app = require ROOT . '/bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        return ['ok' => true, 'msg' => 'Laravel boots successfully.'];
    } catch (Throwable $e) {
        return ['ok' => false, 'msg' => get_class($e) . ': ' . $e->getMessage()];
    }
}

// --- Run a POST action -----------------------------------------------------

$messages = [];
$actionErrors = [];
$migLog = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'restore_autoload') {
        [$copied, $failed] = doc_restore_autoload();
        if ($failed) {
            $actionErrors[] = 'Restore failed: ' . implode(', ', $failed);
        } elseif (!doc_autoload_maps_ok()) {
            $actionErrors[] = 'Files copied but maps still look wrong — a full re-deploy is needed.';
        } else {
            $messages[] = 'Autoloader restored (' . count($copied) . ' files copied from bootstrap/autoload_backup/, OPcache flushed). Reload the homepage.';
            if (!doc_framework_ok()) {
                $actionErrors[] = 'Maps are restored, but framework files are still missing from vendor/ — delete vendor/ in File Manager and re-deploy from Git.';
            }
        }
    }

    if ($action === 'migrate') {
        $probe = doc_probe_boot();
        if (!$probe['ok']) {
            $actionErrors[] = 'Cannot migrate — Laravel will not boot: ' . $probe['msg'];
        } else {
            try {
                $app = require ROOT . '/bootstrap/app.php';
                $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
                Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                $migLog = Illuminate\Support\Facades\Artisan::output();
                $messages[] = 'Pending migrations applied.';
            } catch (Throwable $e) {
                $actionErrors[] = 'Migration failed: ' . $e->getMessage();
            }
        }
    }

    if ($action === 'clear_caches') {
        foreach ([
            ROOT . '/bootstrap/cache/config.php',
            ROOT . '/bootstrap/cache/routes.php',
            ROOT . '/bootstrap/cache/events.php',
            ROOT . '/bootstrap/cache/compiled.php',
        ] as $f) {
            if (is_file($f)) {
                @unlink($f);
            }
        }
        // Clear framework view cache.
        foreach (glob(ROOT . '/storage/framework/views/*.php') ?: [] as $f) {
            @unlink($f);
        }
        $messages[] = 'Cleared bootstrap/cache compiled files + view cache.';
    }

    if ($action === 'delete_self') {
        // Best-effort self-delete, then redirect to homepage.
        @unlink(__FILE__);
        header('Location: /');
        exit;
    }
}

// --- Diagnostics -----------------------------------------------------------

$reqs = [];

$reqs['PHP 8.3+'] = [
    'ok' => PHP_VERSION_ID >= 80300,
    'value' => PHP_VERSION . (PHP_VERSION_ID >= 80300 ? '' : ' — set PHP 8.3 in hPanel → PHP Configuration'),
];
foreach (['pdo_mysql', 'openssl', 'mbstring', 'tokenizer', 'gd', 'fileinfo', 'curl'] as $ext) {
    $reqs['ext: ' . $ext] = [
        'ok' => extension_loaded($ext),
        'value' => extension_loaded($ext) ? 'yes' : 'MISSING — enable in hPanel → PHP Configuration',
    ];
}

$reqs['vendor/autoload.php'] = [
    'ok' => is_file(ROOT . '/vendor/autoload.php'),
    'value' => is_file(ROOT . '/vendor/autoload.php') ? 'present' : 'MISSING — re-deploy from Git',
];
$reqs['Autoloader maps intact'] = [
    'ok' => doc_autoload_maps_ok(),
    'value' => doc_autoload_maps_ok() ? 'yes' : 'CLOBBERED — use "Restore autoloader" below',
];
$reqs['Laravel framework files'] = [
    'ok' => doc_framework_ok(),
    'value' => doc_framework_ok() ? 'present' : 'MISSING — delete vendor/, re-deploy from Git',
];
$reqs['bootstrap/autoload_backup/'] = [
    'ok' => is_file(ROOT . '/bootstrap/autoload_backup/autoload_psr4.php'),
    'value' => is_file(ROOT . '/bootstrap/autoload_backup/autoload_psr4.php') ? 'present (auto-restore possible)' : 'MISSING — full re-deploy required',
];
$reqs['.env file'] = [
    'ok' => is_file(ROOT . '/.env'),
    'value' => is_file(ROOT . '/.env') ? 'present' : 'MISSING — run install.php',
];
$envHasKey = false;
if (is_file(ROOT . '/.env')) {
    $envHasKey = (bool) preg_grep('/^APP_KEY=base64:./', file(ROOT . '/.env') ?: []);
}
$reqs['APP_KEY set'] = [
    'ok' => $envHasKey,
    'value' => $envHasKey ? 'yes' : 'no / invalid',
];
$reqs['storage/ writable'] = [
    'ok' => is_writable(ROOT . '/storage'),
    'value' => is_writable(ROOT . '/storage') ? 'yes' : 'NO — chmod 755/775',
];
$reqs['bootstrap/cache/ writable'] = [
    'ok' => is_writable(ROOT . '/bootstrap/cache'),
    'value' => is_writable(ROOT . '/bootstrap/cache') ? 'yes' : 'NO — chmod 755/775',
];
$reqs['root writable (.env)'] = [
    'ok' => is_writable(ROOT),
    'value' => is_writable(ROOT) ? 'yes' : 'NO — chmod 755',
];

// DB connection test (only if .env looks configured).
$dbMsg = 'not tested (no .env)';
$dbOk = false;
if (is_file(ROOT . '/.env')) {
    $env = parse_ini_file(ROOT . '/.env', false, INI_SCANNER_RAW) ?: [];
    $conn = $env['DB_CONNECTION'] ?? '';
    if (($conn === 'mysql') && !empty($env['DB_HOST']) && !empty($env['DB_DATABASE']) && !empty($env['DB_USERNAME'])) {
        try {
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $env['DB_HOST'], (int)($env['DB_PORT'] ?? 3306), $env['DB_DATABASE']);
            $pdo = new PDO($dsn, $env['DB_USERNAME'], (string)($env['DB_PASSWORD'] ?? ''), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
            $dbOk = true;
            $dbMsg = 'connected to ' . $env['DB_DATABASE'];
        } catch (Throwable $e) {
            $dbMsg = 'FAILED: ' . $e->getMessage();
        }
    } else {
        $dbMsg = 'DB_CONNECTION not mysql or DB_* not set';
    }
}
$reqs['MySQL connection'] = ['ok' => $dbOk, 'value' => $dbMsg];

// Has the database actually been migrated? (A failed earlier install can leave
// a valid .env but NO tables, which still 500s the homepage because
// SESSION_DRIVER/CACHE_STORE point at database tables that don't exist.)
$migrated = false;
$migMsg = 'not tested';
if ($dbOk) {
    try {
        $pdo = new PDO(sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $env['DB_HOST'], (int)($env['DB_PORT'] ?? 3306), $env['DB_DATABASE']),
            $env['DB_USERNAME'], (string)($env['DB_PASSWORD'] ?? ''),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $count = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = " . $pdo->quote($env['DB_DATABASE']))->fetchColumn();
        if ($count === 0) {
            $migMsg = 'database is empty — run migrations (button ②) or install.php';
        } else {
            $hasMigrations = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = " . $pdo->quote($env['DB_DATABASE']) . " AND table_name = 'migrations'")->fetchColumn();
            if ($hasMigrations) {
                $ran = (int) $pdo->query("SELECT COUNT(*) FROM migrations")->fetchColumn();
                $migrated = $ran > 0;
                $migMsg = $ran . ' migrations recorded';
            } else {
                $migMsg = $count . ' tables but no migrations table — DB needs a clean reset';
            }
        }
    } catch (Throwable $e) {
        $migMsg = 'could not inspect schema: ' . $e->getMessage();
    }
}
$reqs['Database migrated'] = ['ok' => $migrated, 'value' => $migMsg];

// The definitive test: can Laravel actually boot?
$boot = doc_probe_boot();
$reqs['Laravel boots'] = ['ok' => $boot['ok'], 'value' => $boot['msg']];

// --- Decision tree ---------------------------------------------------------

$mapsBroken = !doc_autoload_maps_ok();
$frameworkMissing = !doc_framework_ok();
$vendorMissing = !is_file(ROOT . '/vendor/autoload.php');
$backupMissing = !is_file(ROOT . '/bootstrap/autoload_backup/autoload_psr4.php');

$guidance = [];
if ($vendorMissing || $frameworkMissing || ($mapsBroken && $backupMissing)) {
    $guidance[] = 'Your vendor/ folder is incomplete or missing. The fastest fix is a FRESH re-deploy: in hPanel → File Manager, delete the entire vendor/ folder, then run Git → Deploy again (or upload a fresh ZIP). vendor/ + bootstrap/autoload_backup/ are committed to the repo.';
}
if ($mapsBroken && !$backupMissing) {
    $guidance[] = 'Your autoloader maps are clobbered but the backup is present. Click "Restore autoloader" below — it copies the good maps back automatically.';
}
if (PHP_VERSION_ID < 80300) {
    $guidance[] = 'Your PHP is too old. Set PHP 8.3 in hPanel → Websites → your site → Advanced → PHP Configuration.';
}
if (!$dbOk && is_file(ROOT . '/.env')) {
    $guidance[] = 'MySQL connection failed. In hPanel → MySQL Databases, double-check the database name, username, password, and that the user has ALL PRIVILEGES. Then re-run install.php.';
}
if (!is_file(ROOT . '/.env')) {
    $guidance[] = 'No .env file yet. After fixing the autoloader, open install.php and run the installer once.';
}
if ($dbOk && !$migrated) {
    $guidance[] = 'Your database connects but has not been migrated. Click "Run migrations" (button ②) below. Without the sessions/cache tables the homepage will keep 500-ing even after the autoloader is fixed.';
}
if ($boot['ok'] && !$actionErrors) {
    $guidance[] = 'Everything looks healthy. Your site should load now — open the homepage in a new tab.';
}

// --- Render ----------------------------------------------------------------

$rows = '';
$redCount = 0;
foreach ($reqs as $label => $r) {
    if (!$r['ok']) {
        $redCount++;
    }
    $cls = $r['ok'] ? 'ok' : 'bad';
    $rows .= '<div class="row ' . $cls . '"><span>' . doc_h($label) . '</span><span class="v">' . doc_h($r['value']) . '</span></div>';
}

$banner = $boot['ok']
    ? '<div class="state-ok"><strong>✓ Good news — Laravel can boot.</strong> The error you saw was an autoloader problem that is now resolved (or about to be). Open your homepage.</div>'
    : '<div class="state-bad"><strong>✗ Laravel cannot boot.</strong> That is exactly what causes the blank HTTP 500 and "Class Illuminate\Foundation\Application not found". Fix the red items below, top to bottom.</div>';

$guidanceHtml = '';
if ($guidance) {
    $guidanceHtml = '<div class="guide"><h2>What to do now</h2><ol>';
    foreach ($guidance as $g) {
        $guidanceHtml .= '<li>' . doc_h($g) . '</li>';
    }
    $guidanceHtml .= '</ol></div>';
}

$actionErrorsHtml = '';
if ($actionErrors) {
    $actionErrorsHtml = '<div class="errors"><ul>';
    foreach ($actionErrors as $e) {
        $actionErrorsHtml .= '<li>' . nl2br(doc_h($e)) . '</li>';
    }
    $actionErrorsHtml .= '</ul></div>';
}
$messagesHtml = '';
if ($messages) {
    $messagesHtml = '<div class="state-ok"><ul>';
    foreach ($messages as $m) {
        $messagesHtml .= '<li>' . nl2br(doc_h($m)) . '</li>';
    }
    $messagesHtml .= '</ul></div>';
}

$migLogHtml = $migLog !== '' ? '<h2>Migration output</h2><pre class="mig-log">' . doc_h($migLog) . '</pre>' : '';

// Conditionally show the restore button.
$canRestore = !$mapsBroken || !$backupMissing;

?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Doctor · Huvanti</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;
  background:<?=BG?>;color:<?=ON_SURFACE?>;line-height:1.6;-webkit-font-smoothing:antialiased;min-height:100vh}
.page{max-width:640px;margin:0 auto;padding:32px 20px}
.card{background:<?=SURFACE?>;border:1px solid <?=BORDER?>;border-radius:12px;padding:24px;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,.08)}
h1{font-size:22px;font-weight:700;margin-bottom:4px}
.sub{color:<?=ON_MUTED?>;font-size:14px;margin-bottom:18px}
h2{font-size:13px;font-weight:600;color:<?=ON_MUTED?>;text-transform:uppercase;letter-spacing:.08em;margin:22px 0 10px;padding-bottom:8px;border-bottom:1px solid <?=BORDER?>}
h2:first-of-type{margin-top:0}
.banner{padding:14px 16px;border-radius:8px;margin-bottom:16px;font-size:14px;border:1px solid}
.state-ok{background:#E8F5E9;border-color:#BBF7D0;color:#14532D}
.state-bad{background:#FEF2F2;border-color:#FECACA;color:#991B1B}
.req{background:<?=SURFACE_VAR?>;border:1px solid <?=BORDER?>;border-radius:8px;padding:4px 16px;margin-bottom:8px}
.req .row{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;padding:9px 0;font-size:13.5px;border-bottom:1px solid <?=BORDER?>}
.req .row:last-child{border-bottom:0}
.req .row .v{color:<?=ON_MUTED?>;font-size:12px;font-family:Roboto Mono,monospace;text-align:right;max-width:60%}
.req .row.ok .v::before{content:"\2713  ";color:<?=SUCCESS?>;font-weight:700}
.req .row.bad .v::before{content:"\2717  ";color:<?=ERROR_C?>;font-weight:700}
.req .row.bad{color:<?=ERROR_C?>}
.guide{background:#FFF8E1;border:1px solid #FFE082;border-radius:8px;padding:4px 18px 14px}
.guide ol{margin:8px 0 0;padding-left:22px}
.guide li{margin:6px 0;font-size:14px}
.errors{background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;padding:12px 16px;margin-bottom:16px;border-radius:8px;font-size:14px}
.errors ul{margin:8px 0 0;padding-left:24px}
.btn{display:inline-block;width:100%;min-height:46px;padding:0 20px;background:<?=PRIMARY?>;color:#fff;border:0;border-radius:8px;
  font-family:inherit;font-size:15px;font-weight:600;cursor:pointer;margin-top:8px;text-decoration:none;
  display:flex;align-items:center;justify-content:center;gap:8px}
.btn:hover{background:<?=PRIMARY_HOVER?>}
.btn.ghost{background:#fff;color:<?=PRIMARY?>;border:1.5px solid <?=PRIMARY?>}
.btn.danger{background:#fff;color:<?=ERROR_C?>;border:1.5px solid <?=ERROR_C?>}
.btnrow{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:8px}
.mig-log{background:#1C1B1F;color:#E6E1E5;padding:14px;font-family:Roboto Mono,monospace;font-size:12px;white-space:pre-wrap;max-height:260px;overflow:auto;border-radius:8px;margin-top:8px}
.warn{background:#FFF3E0;border:1px solid #FFB74D;color:#8a5a00;padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:16px}
@media(max-width:560px){.btnrow{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="page">
  <div class="card">
    <h1>Huvanti Doctor</h1>
    <p class="sub">Standalone diagnostic + repair. This page tells you exactly why the site is
      showing a blank 500 or "Class Illuminate\Foundation\Application not found", and can fix the
      common Hostinger causes with one click.</p>

    <?= $banner ?>

    <?= $messagesHtml ?>
    <?= $actionErrorsHtml ?>

    <div class="warn"><strong>Reminder:</strong> delete <code>doctor.php</code> when you're done
      (button at the bottom, or File Manager). Don't leave repair tools online.</div>

    <h2>Health check</h2>
    <div class="req"><?= $rows ?></div>

    <?= $guidanceHtml ?>

    <?= $migLogHtml ?>

    <h2>Repair actions</h2>
    <form method="post">
      <input type="hidden" name="action" value="restore_autoload">
      <button class="btn" type="submit">① Restore the Composer autoloader</button>
    </form>
    <p class="sub" style="margin-top:6px">Copies the good autoloader maps from
      <code>bootstrap/autoload_backup/</code> back over <code>vendor/composer/</code>. This is the
      fix for the blank 500 / "Class not found" error. Safe to run any time.</p>

    <div class="btnrow">
      <form method="post">
        <input type="hidden" name="action" value="migrate">
        <button class="btn ghost" type="submit">② Run migrations</button>
      </form>
      <form method="post">
        <input type="hidden" name="action" value="clear_caches">
        <button class="btn ghost" type="submit">③ Clear caches</button>
      </form>
    </div>

    <form method="post" onsubmit="return confirm('Delete doctor.php now? You can always upload it again.')">
      <input type="hidden" name="action" value="delete_self">
      <button class="btn danger" type="submit">Delete this file (doctor.php)</button>
    </form>
  </div>
</div>
</body>
</html>
