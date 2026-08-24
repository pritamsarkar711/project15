<?php
/**
 * Huvanti Installer — single-file web installer for shared hosting.
 * Visit https://your-domain.com/install.php, fill in 3 short sections,
 * click install. Done.
 */

declare(strict_types=1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
// Keep unhandled warnings, stack traces and absolute paths out of this public
// response. Handled setup failures render below; full details go to PHP's log.
ini_set('display_errors', '0');
ini_set('log_errors', '1');

const ROOT = __DIR__;
const INSTALL_DEPLOYMENT = '2026-08-24-hostinger-launch-v2';

header('Cache-Control: no-store, max-age=0');
header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-Huvanti-Deploy: ' . INSTALL_DEPLOYMENT);

// Stateless double-submit CSRF protection works before Laravel or its session
// database has been installed.
$installerCsrf = is_string($_COOKIE['huvanti_installer_csrf'] ?? null)
    ? $_COOKIE['huvanti_installer_csrf']
    : '';
if (!preg_match('/^[a-f0-9]{64}$/', $installerCsrf)) {
    $installerCsrf = bin2hex(random_bytes(32));
    setcookie('huvanti_installer_csrf', $installerCsrf, [
        'expires' => 0,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

// Material Design 3 + shadcn palette (matches reference repo).
const PRIMARY = '#1565C0';        // Blue 800
const PRIMARY_HOVER = '#0D47A1';  // Blue 900
const SUCCESS = '#00897B';        // Teal 600
const ERROR = '#D4183D';          // Material Red 600
const BG = '#FAFAFA';             // page background
const SURFACE = '#FFFFFF';        // cards
const SURFACE_VAR = '#F3F3F5';    // inputs
const ON_SURFACE = '#1C1B1F';
const ON_SURFACE_MUTED = '#6E6D78';
const BORDER = 'rgba(0,0,0,0.10)';

const INSTALLER_CSS = '
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap");
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{font-size:16px}
body{font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;
  background:' . BG . ';color:' . ON_SURFACE . ';line-height:1.6;
  -webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;
  min-height:100vh;display:flex;flex-direction:column}
.page{max-width:520px;margin:0 auto;padding:32px 20px;flex:1;width:100%}
.brand{display:flex;align-items:center;gap:10px;margin-bottom:20px}
.brand-mark{width:32px;height:32px;border-radius:8px;background:' . PRIMARY . ';
  color:#fff;display:flex;align-items:center;justify-content:center;
  font-weight:700;font-size:18px;letter-spacing:-0.02em;font-family:Inter,sans-serif}
.brand-name{font-weight:600;font-size:16px;color:' . ON_SURFACE . ';letter-spacing:-0.01em}
.brand-tag{font-size:11px;color:' . ON_SURFACE_MUTED . ';font-weight:500;letter-spacing:0.02em}
.card{background:' . SURFACE . ';border:1px solid ' . BORDER . ';border-radius:12px;
  padding:24px;margin-bottom:16px;
  box-shadow:0 2px 8px rgba(0,0,0,0.08)}
.foot{text-align:center;font-size:12px;color:' . ON_SURFACE_MUTED . ';padding:12px 0 4px}
h1{font-size:24px;font-weight:700;color:' . ON_SURFACE . ';letter-spacing:-0.01em;line-height:1.3;margin-bottom:6px}
.sub{font-size:14px;color:' . ON_SURFACE_MUTED . ';margin-bottom:20px;line-height:1.6}
h2{font-size:13px;font-weight:600;color:' . ON_SURFACE_MUTED . ';text-transform:uppercase;
  letter-spacing:0.08em;margin:24px 0 12px;padding-bottom:8px;border-bottom:1px solid ' . BORDER . '}
h2:first-of-type{margin-top:0}
.req{background:' . SURFACE_VAR . ';border:1px solid ' . BORDER . ';border-radius:8px;
  padding:4px 16px;margin-bottom:20px}
.req .row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;
  font-size:14px;border-bottom:1px solid ' . BORDER . '}
.req .row:last-child{border-bottom:0}
.req .row .v{color:' . ON_SURFACE_MUTED . ';font-size:12px;font-family:Roboto Mono,monospace}
.req .row.bad{color:' . ERROR . '}
.req .row.ok .v::before{content:"\2713  ";color:' . SUCCESS . ';font-weight:700}
.req .row.bad .v::before{content:"\2717  ";color:' . ERROR . ';font-weight:700}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.field{display:block}
.field+.field{margin-top:12px}
.grid .field+.field{margin-top:0}
label{display:block;font-size:14px;font-weight:500;color:' . ON_SURFACE . ';
  margin-bottom:6px;line-height:1.5}
input{width:100%;min-height:48px;padding:10px 14px;
  border:1.5px solid ' . BORDER . ';border-radius:8px;font-size:16px;
  font-family:inherit;background:' . SURFACE_VAR . ';color:' . ON_SURFACE . ';
  transition:border-color .15s,box-shadow .15s,background .15s;line-height:1.5}
input:focus{outline:none;border-color:' . PRIMARY . ';
  box-shadow:0 0 0 3px rgba(21,101,192,0.16);background:' . SURFACE . '}
input::placeholder{color:#9CA3AF}
input[type=password]{font-family:Roboto Mono,monospace;letter-spacing:0.02em}
.btn{display:inline-block;width:100%;min-height:48px;padding:0 24px;
  background:' . PRIMARY . ';color:#fff;border:0;border-radius:8px;
  font-family:Inter,sans-serif;font-size:16px;font-weight:600;line-height:1.5;
  cursor:pointer;margin-top:24px;text-decoration:none;text-align:center;
  transition:background .15s,transform .05s,box-shadow .15s;
  display:flex;align-items:center;justify-content:center;gap:8px}
.btn:hover{background:' . PRIMARY_HOVER . '}
.btn:active{transform:scale(0.99)}
.btn:disabled{opacity:0.4;cursor:not-allowed;background:#9CA3AF}
.errors{background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;
  padding:12px 16px;margin-bottom:20px;border-radius:8px;font-size:14px;line-height:1.6}
.errors ul{margin:8px 0 0;padding-left:24px}
.errors ul li{margin:4px 0}
.state{padding:18px;border-radius:8px;margin-bottom:20px;border:1px solid}
.state-ok{background:#E8F5E9;border-color:#BBF7D0}
.state-ok h2{color:#14532D;border-color:#BBF7D0}
.state-ok a{color:#0D47A1}
.state-ok ul{margin:8px 0 0;padding-left:24px;font-size:14px;line-height:1.8}
.state-ok ul li{margin:2px 0}
.state-ok code{background:rgba(0,0,0,0.06);padding:2px 6px;border-radius:4px;
  font-family:Roboto Mono,monospace;font-size:12px}
.mig-log{background:#1C1B1F;color:#E6E1E5;padding:14px 16px;
  font-family:Roboto Mono,monospace;font-size:12px;white-space:pre-wrap;
  max-height:240px;overflow:auto;border-radius:8px;margin-top:12px;line-height:1.5}
@media(max-width:520px){.grid{grid-template-columns:1fr}}
@media(prefers-reduced-motion:reduce){
  *,*::before,*::after{transition:none !important;animation:none !important}
}
';

$lockFile = ROOT . '/storage/app/installed.lock';

// Repair actions moved to the standalone doctor, which is authenticated and
// includes its own recovery data. Keep this compatibility URL informational;
// never expose migration or filesystem actions from the public installer.
if (isset($_GET['repair'])) {
    http_response_code(303);
    header('Location: doctor.php');
    exit;
}

// Already installed?
if (file_exists($lockFile)) {
    http_response_code(403);
    echo view_page('Already installed', '
        <h1>Already installed</h1>
        <p class="sub">Huvanti is already set up on this server.</p>
        <div class="state state-ok">
            <h2>Site showing an error?</h2>
            <ul>
                <li>Open the authenticated <a href="doctor.php">Huvanti Doctor</a> —
                    it fixes broken autoloaders and applies pending database
                    migrations without SSH. Unlock it with APP_KEY from .env.</li>
            </ul>
            <h2>To reinstall</h2>
            <ul>
                <li>Delete <code>storage/app/installed.lock</code> via File Manager</li>
                <li>Drop all tables in your MySQL database</li>
                <li>Reload this page</li>
            </ul>
        </div>
        <a class="btn" href="/">Visit site</a>
        <a class="btn" style="background:#fff;color:' . PRIMARY . ';border:1.5px solid ' . PRIMARY . '" href="doctor.php">Open Huvanti Doctor</a>');
    exit;
}

// Hostinger's obsolete deployment stub could regenerate only Composer's maps
// while leaving Laravel's package files in place. Heal that exact failure on
// first load so installation is not blocked behind a separate repair screen.
if (huvanti_framework_files_ok()
    && (!is_file(ROOT . '/vendor/autoload.php') || !huvanti_autoload_maps_ok())) {
    huvanti_restore_autoload();
}

$reqs = check_requirements();
$allReqPass = !in_array(false, array_column($reqs, 'ok'), true);

$errors = [];
$success = false;
$migLog = '';
$f = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedCsrf = is_string($_POST['csrf'] ?? null) ? $_POST['csrf'] : '';
    if ($postedCsrf === '' || !hash_equals($installerCsrf, $postedCsrf)) {
        $errors[] = 'The installer security token expired. Reload this page and submit the form again.';
    } elseif (!$allReqPass) {
        $errors[] = 'Fix the red items in the checklist first.';
    } else {
        $f = [
            'db_host' => trim($_POST['db_host'] ?? 'localhost'),
            'db_port' => (int)($_POST['db_port'] ?? 3306),
            'db_name' => trim($_POST['db_name'] ?? ''),
            'db_user' => trim($_POST['db_user'] ?? ''),
            'db_pass' => (string)($_POST['db_pass'] ?? ''),
            'app_url' => rtrim(trim($_POST['app_url'] ?? ''), '/'),
            'app_name' => trim($_POST['app_name'] ?? 'Huvanti'),
            'admin_name' => trim($_POST['admin_name'] ?? ''),
            'admin_email' => trim($_POST['admin_email'] ?? ''),
            'admin_pass' => (string)($_POST['admin_pass'] ?? ''),
        ];
        if ($f['db_name'] === '') $errors[] = 'Database name is required';
        if ($f['db_user'] === '') $errors[] = 'Database username is required';
        if ($f['admin_name'] === '') $errors[] = 'Admin name is required';
        if (!filter_var($f['admin_email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Admin email is invalid';
        if (strlen($f['admin_pass']) < 8) $errors[] = 'Admin password must be at least 8 characters';
        if ($f['db_port'] < 1 || $f['db_port'] > 65535) $errors[] = 'Database port must be between 1 and 65535';
        if (!preg_match('/^[A-Za-z0-9_.:\\[\\]-]+$/', $f['db_host'])) $errors[] = 'Database host contains invalid characters';
        if (str_contains($f['db_name'], ';')) $errors[] = 'Database name contains invalid characters';
        if ($f['app_url'] === ''
            || !filter_var($f['app_url'], FILTER_VALIDATE_URL)
            || strtolower((string) parse_url($f['app_url'], PHP_URL_SCHEME)) !== 'https') {
            $errors[] = 'Site URL must be a full https:// URL';
        }
        foreach ($f as $name => $value) {
            if (is_string($value) && preg_match('/[\\x00-\\x1F\\x7F]/', $value)) {
                $errors[] = 'A submitted field contains an invalid control character (' . $name . ')';
            }
            if (is_string($value) && strlen($value) > 1000) {
                $errors[] = 'A submitted field is too long (' . $name . ')';
            }
        }

        // Test DB connection BEFORE writing .env.
        $pdo = null;
        if (!$errors) {
            try {
                $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $f['db_host'], $f['db_port'], $f['db_name']);
                $pdo = new PDO($dsn, $f['db_user'], $f['db_pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                $pdo->exec("SET NAMES utf8mb4");
                $pdo->exec("SET time_zone='+00:00'");
            } catch (PDOException $e) {
                $errors[] = 'Database connection failed: ' . $e->getMessage();
            }
        }

        if (!$errors) {
            $appKey = 'base64:' . base64_encode(random_bytes(32));
            $env = sprintf(
                'APP_NAME=%s' . "\n" .
                'APP_ENV=production' . "\n" .
                'APP_KEY=%s' . "\n" .
                'APP_DEBUG=false' . "\n" .
                'APP_URL=%s' . "\n\n" .
                'APP_TIMEZONE=UTC' . "\n" .
                'APP_LOCALE=en' . "\n\n" .
                'LOG_CHANNEL=stack' . "\n" .
                'LOG_STACK=single' . "\n" .
                'LOG_DEPRECATIONS_CHANNEL=null' . "\n" .
                'LOG_LEVEL=error' . "\n\n" .
                'DB_CONNECTION=mysql' . "\n" .
                'DB_HOST=%s' . "\n" .
                'DB_PORT=%d' . "\n" .
                'DB_DATABASE=%s' . "\n" .
                'DB_USERNAME=%s' . "\n" .
                'DB_PASSWORD=%s' . "\n\n" .
                'SESSION_DRIVER=database' . "\n" .
                'SESSION_LIFETIME=120' . "\n" .
                'SESSION_ENCRYPT=false' . "\n\n" .
                'BROADCAST_CONNECTION=log' . "\n" .
                'FILESYSTEM_DISK=public' . "\n" .
                'QUEUE_CONNECTION=database' . "\n\n" .
                'CACHE_STORE=database' . "\n\n" .
                'VITE_APP_NAME=%s' . "\n",
                huvanti_env_quote($f['app_name']), $appKey, huvanti_env_quote($f['app_url']),
                huvanti_env_quote($f['db_host']), $f['db_port'], huvanti_env_quote($f['db_name']),
                huvanti_env_quote($f['db_user']), huvanti_env_quote($f['db_pass']),
                huvanti_env_quote($f['app_name'])
            );
            if (!huvanti_atomic_write(ROOT . '/.env', $env, 0600)) {
                $errors[] = 'Cannot safely write .env — make the project root writable (chmod 755).';
            }
        }

        if (!$errors) {
            try {
                // Self-heals a damaged autoloader BEFORE loading it, verifies
                // the framework classes are really loadable, and throws an
                // actionable RuntimeException instead of a raw
                // "Class Illuminate\Foundation\Application not found" crash.
                $app = huvanti_boot_laravel();
                $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
                Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                $migLog .= Illuminate\Support\Facades\Artisan::output();
                Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
                $migLog .= Illuminate\Support\Facades\Artisan::output();

                // Update the seeded admin user to match the user's input
                // (preserves FK relations from seeded posts).
                $hash = password_hash($f['admin_pass'], PASSWORD_BCRYPT, ['cost' => 10]);
                $existingAdmin = $pdo->query("SELECT id FROM users WHERE role='admin' ORDER BY id ASC LIMIT 1")->fetch();
                if ($existingAdmin) {
                    // If user's email collides with a non-admin account, drop it first.
                    $checkConflict = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
                    $checkConflict->execute([$f['admin_email'], $existingAdmin['id']]);
                    if ($conflict = $checkConflict->fetch()) {
                        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$conflict['id']]);
                    }
                    $upd = $pdo->prepare("UPDATE users SET name=?, email=?, password=?, role='admin', email_verified_at=NOW(), two_factor_enabled=0, google2fa_secret=NULL WHERE id=?");
                    $upd->execute([$f['admin_name'], $f['admin_email'], $hash, $existingAdmin['id']]);
                } else {
                    $ins = $pdo->prepare("INSERT INTO users (name, email, password, role, email_verified_at, two_factor_enabled, created_at, updated_at) VALUES (?, ?, ?, 'admin', NOW(), 0, NOW(), NOW())");
                    $ins->execute([$f['admin_name'], $f['admin_email'], $hash]);
                }

                // Persist site_name + tagline in settings table using prepared statements
                // (NOT $pdo->quote() — it returns the value already wrapped in single quotes,
                // and wrapping it again caused: SQLSTATE[42000]: 1064 near 'Huvanti'').
                $stmt = $pdo->prepare(
                    "INSERT INTO settings (`key`,`value`,`type`,`group`,`created_at`,`updated_at`)
                     VALUES (?, ?, 'text', 'general', NOW(), NOW())
                     ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW()"
                );
                $stmt->execute(['site_name', $f['app_name']]);
                $stmt->execute(['site_tagline', 'Explore Ideas. Inspire Life.']);

                if (!huvanti_atomic_write(
                    $lockFile,
                    date('c') . ' installed by ' . $f['admin_email'] . "\n",
                    0644
                )) {
                    throw new RuntimeException('Setup finished, but the installer lock could not be written.');
                }
                @unlink(__FILE__);
                $success = true;
            } catch (Throwable $e) {
                error_log((string) $e);
                $errors[] = 'Migration/setup failed (' . get_class($e) . '). Open the authenticated '
                    . 'doctor.php for diagnostics. Full details were written to the server error log.';
            }
        }
    }
}

/** Atomically publish a sensitive installer file in its destination directory. */
function huvanti_atomic_write(string $destination, string $content, int $mode): bool {
    try {
        $temporary = $destination . '.install-' . bin2hex(random_bytes(4)) . '.tmp';
    } catch (Throwable $e) {
        error_log((string) $e);
        return false;
    }
    if (@file_put_contents($temporary, $content, LOCK_EX) !== strlen($content)) {
        @unlink($temporary);
        return false;
    }
    @chmod($temporary, $mode);
    if (!@rename($temporary, $destination)) {
        @unlink($temporary);
        return false;
    }
    return true;
}

/** Quote a submitted value for phpdotenv without interpolation or comments. */
function huvanti_env_quote(string $value): string {
    // Validation above rejects line/control characters. Escaping in this order
    // preserves literal backslashes, quotes and dollar signs in DB passwords.
    return '"' . str_replace(
        ['\\', '"', '$'],
        ['\\\\', '\\"', '\\$'],
        $value
    ) . '"';
}

function check_requirements(): array {
    return [
        'Deployment release' => ['ok' => true, 'value' => INSTALL_DEPLOYMENT],
        'PHP 8.3+' => ['ok' => version_compare(PHP_VERSION, '8.3.0', '>='), 'value' => PHP_VERSION],
        'pdo_mysql' => ['ok' => extension_loaded('pdo_mysql'), 'value' => extension_loaded('pdo_mysql') ? 'yes' : 'no'],
        'openssl' => ['ok' => extension_loaded('openssl'), 'value' => extension_loaded('openssl') ? 'yes' : 'no'],
        'mbstring' => ['ok' => extension_loaded('mbstring'), 'value' => extension_loaded('mbstring') ? 'yes' : 'no'],
        'tokenizer' => ['ok' => extension_loaded('tokenizer'), 'value' => extension_loaded('tokenizer') ? 'yes' : 'no'],
        'gd' => ['ok' => extension_loaded('gd'), 'value' => extension_loaded('gd') ? 'yes' : 'no'],
        'fileinfo' => ['ok' => extension_loaded('fileinfo'), 'value' => extension_loaded('fileinfo') ? 'yes' : 'no'],
        'vendor/autoload.php' => ['ok' => file_exists(ROOT . '/vendor/autoload.php'), 'value' => file_exists(ROOT . '/vendor/autoload.php') ? 'ok' : 'missing'],
        'autoload maps Illuminate' => ['ok' => huvanti_autoload_maps_ok(), 'value' => huvanti_autoload_maps_ok() ? 'ok' : 'clobbered — upload/open doctor.php'],
        'Laravel framework files' => ['ok' => huvanti_framework_files_ok(), 'value' => huvanti_framework_files_ok() ? 'ok' : 'missing — delete vendor/, Git → Deploy'],
        'storage/ writable' => ['ok' => is_writable(ROOT . '/storage'), 'value' => is_writable(ROOT . '/storage') ? 'yes' : 'no'],
        'bootstrap/cache/ writable' => ['ok' => is_writable(ROOT . '/bootstrap/cache'), 'value' => is_writable(ROOT . '/bootstrap/cache') ? 'yes' : 'no'],
        'root writable (.env)' => ['ok' => is_writable(ROOT), 'value' => is_writable(ROOT) ? 'yes' : 'no'],
    ];
}

/**
 * True when the on-disk autoloader still maps the framework namespaces.
 * Inspects file content only (never requires it) so a clobbered autoloader
 * can't cause side effects here.
 *
 * BOTH map files are verified on purpose: Composer's runtime loader
 * (autoload_real.php) is fed by autoload_static.php — the authoritative map —
 * while autoload_psr4.php is only the fallback. Checking just the psr4 map
 * once let a damaged static map pass as "healthy", and the installer then
 * died with "Class Illuminate\Foundation\Application not found" even though
 * every checklist row was green.
 */
function huvanti_autoload_maps_ok(): bool {
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

/**
 * True when the Laravel framework's own entry class physically exists in
 * vendor/. is_dir() alone is not enough — a partially-failed Git deploy can
 * leave vendor/laravel/framework/ present but gutted, which also ends in
 * "Class Illuminate\Foundation\Application not found".
 */
function huvanti_framework_files_ok(): bool {
    return is_file(ROOT . '/vendor/laravel/framework/src/Illuminate/Foundation/Application.php');
}

/**
 * Drop a file's compiled bytecode from OPcache (when enabled) so the next
 * require() re-reads it from disk. Needed because some shared hosts run with
 * opcache.validate_timestamps=off — copying a fixed autoloader over a broken
 * one has no effect while the stale bytecode stays cached.
 */
function huvanti_opcache_forget(string $file): void {
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate($file, true);
    }
}

/**
 * Copy pristine autoloader files from bootstrap/autoload_backup/ back over
 * vendor/composer/ + vendor/autoload.php. Returns [copied[], failed[]].
 */
function huvanti_restore_autoload(): array {
    $backupDir = ROOT . '/bootstrap/autoload_backup';
    $composerDir = ROOT . '/vendor/composer';

    if (!is_dir($backupDir)) {
        return [[], ['bootstrap/autoload_backup/ is missing — re-deploy the repo from Git first']];
    }
    if (!is_dir(ROOT . '/vendor') && !@mkdir(ROOT . '/vendor', 0775, true)) {
        return [[], ['cannot create vendor/']];
    }
    if (!is_dir($composerDir) && !@mkdir($composerDir, 0775, true)) {
        return [[], ['cannot create vendor/composer/']];
    }

    $files = [
        'ClassLoader.php', 'InstalledVersions.php',
        'autoload_classmap.php', 'autoload_files.php', 'autoload_namespaces.php',
        'autoload_psr4.php', 'autoload_real.php', 'autoload_static.php',
        'installed.php', 'platform_check.php',
        'autoload.php', // publish the entry point last
    ];
    $copied = [];
    $failed = [];
    foreach ($files as $file) {
        if ($file === 'autoload.php' && $failed !== []) {
            $failed[] = 'autoload.php (not published because a dependency failed)';
            continue;
        }
        $destination = $file === 'autoload.php'
            ? ROOT . '/vendor/autoload.php'
            : $composerDir . '/' . $file;
        $content = @file_get_contents($backupDir . '/' . $file);
        $temporary = $destination . '.restore-' . bin2hex(random_bytes(4)) . '.tmp';
        if ($content !== false
            && @file_put_contents($temporary, $content, LOCK_EX) === strlen($content)
            && @rename($temporary, $destination)) {
            @chmod($destination, 0644);
            huvanti_opcache_forget($destination);
            $copied[] = $file;
        } else {
            @unlink($temporary);
            $failed[] = $file;
        }
    }
    if (function_exists('opcache_reset')) {
        @opcache_reset();
    }
    return [$copied, $failed];
}

/**
 * Load vendor/autoload.php (self-healing first) and boot Laravel. Returns
 * the Application instance from bootstrap/app.php, or throws a RuntimeException
 * with an actionable message — never a bare class-not-found crash.
 */
function huvanti_boot_laravel() {
    if (!is_file(ROOT . '/vendor/autoload.php')) {
        throw new RuntimeException(
            'vendor/autoload.php is missing — the Git deploy did not copy vendor/. '
            . 'In hPanel, run Git → Deploy again (vendor/ is committed to the repo), then retry.'
        );
    }

    // Self-heal a clobbered autoloader BEFORE requiring it — the same
    // strategy as public/index.php. A damaged autoloader must never actually
    // be loaded, or its broken classes stick for the rest of the request.
    if (!huvanti_autoload_maps_ok()) {
        [, $failed] = huvanti_restore_autoload();
        if ($failed || !huvanti_autoload_maps_ok()) {
            throw new RuntimeException(
                'The Composer autoloader in vendor/composer/ is damaged and the automatic '
                . 'restore from bootstrap/autoload_backup/ failed ('
                . ($failed !== [] ? implode(', ', $failed) : 'the restored maps still miss the Illuminate\\ mappings')
                . '). Upload/open doctor.php and run "Restore autoloader", or re-deploy the current main branch.'
            );
        }
    }

    // If OPcache is serving stale bytecode for the autoloader (hosts with
    // validate_timestamps=off), forget those entries so require() below
    // re-reads the pristine files from disk.
    huvanti_opcache_forget(ROOT . '/vendor/autoload.php');
    foreach (['autoload_real.php', 'autoload_static.php', 'autoload_psr4.php', 'platform_check.php'] as $name) {
        huvanti_opcache_forget(ROOT . '/vendor/composer/' . $name);
    }

    // Fail fast (before any class loading) when the framework entry class is
    // physically gone — a gutted vendor/ tree must not trigger noisy autoloader
    // include-warnings on the installer page.
    if (!huvanti_framework_files_ok()) {
        throw new RuntimeException(
            'The Laravel framework files are missing from vendor/. Delete the vendor/ folder '
            . 'via hPanel → File Manager, then run Git → Deploy to restore the committed copy, '
            . 'and run the installer again.'
        );
    }

    require ROOT . '/vendor/autoload.php';

    if (!class_exists(Illuminate\Foundation\Application::class)) {
        // Maps fine + file present, yet the class will not load: the
        // running PHP process is almost certainly serving stale OPcache.
        throw new RuntimeException(
            'The autoloader maps are intact and the framework files exist, but Illuminate '
            . 'classes still do not load — stale OPcache is the likely cause. Open '
            . 'doctor.php and run "Restore autoloader" (it also flushes cached bytecode), '
            . 'or wait a minute and retry.'
        );
    }

    return require ROOT . '/bootstrap/app.php';
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function view_page($title, $body): string {
    return '<!doctype html><html lang="en"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">'
        . '<meta name="robots" content="noindex,nofollow">'
        . '<title>' . h($title) . ' · Huvanti</title>'
        . '<style>' . INSTALLER_CSS . '</style></head><body>'
        . '<div class="page"><header class="brand">'
        . '<div class="brand-mark">H</div>'
        . '<div><div class="brand-name">Huvanti</div>'
        . '<div class="brand-tag">Installer · ' . INSTALL_DEPLOYMENT . '</div></div>'
        . '</header><main class="card">' . $body . '</main>'
        . '<footer class="foot">© ' . date('Y') . ' Huvanti</footer></div></body></html>';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<title>Install · Huvanti</title>
<style><?= INSTALLER_CSS ?></style>
</head>
<body>
<div class="page">
    <header class="brand">
        <div class="brand-mark">H</div>
        <div>
            <div class="brand-name">Huvanti</div>
            <div class="brand-tag">Installer · <?= h(INSTALL_DEPLOYMENT) ?></div>
        </div>
    </header>

    <main class="card">
        <h1>Install Huvanti</h1>
        <p class="sub">Fill in the form below and click install. Takes ~10 seconds.</p>

        <?php if ($errors): ?>
        <div class="errors">
            <strong>Please fix the following:</strong>
            <ul><?php foreach ($errors as $e) echo '<li>' . h($e) . '</li>'; ?></ul>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="state state-ok">
            <h2>Installation complete</h2>
            <ul>
                <li>Site: <a href="<?= h($f['app_url']) ?>"><?= h($f['app_url']) ?></a></li>
                <li>Admin: <a href="<?= h($f['app_url']) ?>/manage"><?= h($f['app_url']) ?>/manage</a></li>
                <li>Login: <strong><?= h($f['admin_email']) ?></strong></li>
            </ul>
        </div>
        <?php if ($migLog): ?>
        <h2>Setup log</h2>
        <div class="mig-log"><?= h($migLog) ?></div>
        <?php endif; ?>
        <a class="btn" href="<?= h($f['app_url']) ?>">Visit your site</a>
        <?php else: ?>

        <h2>Server check</h2>
        <div class="req">
        <?php foreach ($reqs as $name => $info): ?>
        <div class="row <?= $info['ok'] ? 'ok' : 'bad' ?>"><?= h($name) ?><span class="v"><?= h($info['value']) ?></span></div>
        <?php endforeach; ?>
        </div>

        <?php if (!$allReqPass): ?>
        <div class="errors">Fix the red items above, then reload this page.</div>
        <?php endif; ?>

        <form method="post" action="">
        <input type="hidden" name="csrf" value="<?= h($installerCsrf) ?>">
        <h2>Database</h2>
        <div class="grid">
            <div class="field">
                <label for="db_host">Host</label>
                <input type="text" id="db_host" name="db_host" value="<?= h($_POST['db_host'] ?? 'localhost') ?>" required>
            </div>
            <div class="field">
                <label for="db_port">Port</label>
                <input type="number" id="db_port" name="db_port" value="<?= h($_POST['db_port'] ?? 3306) ?>" required>
            </div>
            <div class="field">
                <label for="db_name">Database name</label>
                <input type="text" id="db_name" name="db_name" value="<?= h($_POST['db_name'] ?? '') ?>" required placeholder="u123_huvanti">
            </div>
            <div class="field">
                <label for="db_user">Username</label>
                <input type="text" id="db_user" name="db_user" value="<?= h($_POST['db_user'] ?? '') ?>" required>
            </div>
            <div class="field" style="grid-column:1/-1">
                <label for="db_pass">Password</label>
                <input type="password" id="db_pass" name="db_pass" value="" autocomplete="new-password">
            </div>
        </div>

        <h2>Site</h2>
        <div class="grid">
            <div class="field">
                <label for="app_url">Site URL</label>
                <input type="url" id="app_url" name="app_url" value="<?= h($_POST['app_url'] ?? ('https://' . ($_SERVER['HTTP_HOST'] ?? 'your-domain.com'))) ?>" required>
            </div>
            <div class="field">
                <label for="app_name">Site name</label>
                <input type="text" id="app_name" name="app_name" value="<?= h($_POST['app_name'] ?? 'Huvanti') ?>" required>
            </div>
        </div>

        <h2>Admin</h2>
        <div class="grid">
            <div class="field">
                <label for="admin_name">Name</label>
                <input type="text" id="admin_name" name="admin_name" value="<?= h($_POST['admin_name'] ?? 'Admin') ?>" required>
            </div>
            <div class="field">
                <label for="admin_email">Email</label>
                <input type="email" id="admin_email" name="admin_email" value="<?= h($_POST['admin_email'] ?? '') ?>" required placeholder="you@example.com">
            </div>
            <div class="field" style="grid-column:1/-1">
                <label for="admin_pass">Password</label>
                <input type="password" id="admin_pass" name="admin_pass" required placeholder="Min 8 characters">
            </div>
        </div>

        <button class="btn" type="submit" <?= $allReqPass ? '' : 'disabled' ?>>Install Huvanti</button>
        </form>
        <?php endif; ?>
    </main>

    <footer class="foot">© <?= date('Y') ?> Huvanti</footer>
</div>
</body>
</html>
