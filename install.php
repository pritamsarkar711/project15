<?php
/**
 * Huvanti Installer — single-file web installer for shared hosting.
 * Visit https://your-domain.com/install.php, fill in 3 short sections,
 * click install. Done.
 */

declare(strict_types=1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', '1');

const ROOT = __DIR__;

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

// Already installed?
if (file_exists($lockFile)) {
    http_response_code(403);
    echo view_page('Already installed', '
        <h1>Already installed</h1>
        <p class="sub">Huvanti is already set up on this server.</p>
        <div class="state state-ok">
            <h2>To reinstall</h2>
            <ul>
                <li>Delete <code>storage/app/installed.lock</code> via File Manager</li>
                <li>Drop all tables in your MySQL database</li>
                <li>Reload this page</li>
            </ul>
        </div>
        <a class="btn" href="/">Visit site</a>');
    exit;
}

$reqs = check_requirements();
$allReqPass = !in_array(false, array_column($reqs, 'ok'), true);

$errors = [];
$success = false;
$migLog = '';
$f = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$allReqPass) {
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
        if ($f['app_url'] === '' || !filter_var($f['app_url'], FILTER_VALIDATE_URL)) $errors[] = 'Site URL must be a full https:// URL';

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
                'APP_NAME="%s"' . "\n" .
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
                'VITE_APP_NAME="%s"' . "\n",
                $f['app_name'], $appKey, $f['app_url'],
                $f['db_host'], $f['db_port'], $f['db_name'], $f['db_user'], $f['db_pass'],
                $f['app_name']
            );
            if (file_put_contents(ROOT . '/.env', $env) === false) {
                $errors[] = 'Cannot write .env file — make the project root writable (chmod 755).';
            }
        }

        if (!$errors) {
            try {
                require ROOT . '/vendor/autoload.php';
                $app = require ROOT . '/bootstrap/app.php';
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

                @file_put_contents($lockFile, date('c') . ' installed by ' . $f['admin_email']);
                @chmod($lockFile, 0644);
                @unlink(__FILE__);
                $success = true;
            } catch (Throwable $e) {
                $errors[] = 'Migration/setup failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString();
            }
        }
    }
}

function check_requirements(): array {
    return [
        'PHP 8.1+' => ['ok' => version_compare(PHP_VERSION, '8.1.0', '>='), 'value' => PHP_VERSION],
        'pdo_mysql' => ['ok' => extension_loaded('pdo_mysql'), 'value' => extension_loaded('pdo_mysql') ? 'yes' : 'no'],
        'openssl' => ['ok' => extension_loaded('openssl'), 'value' => extension_loaded('openssl') ? 'yes' : 'no'],
        'mbstring' => ['ok' => extension_loaded('mbstring'), 'value' => extension_loaded('mbstring') ? 'yes' : 'no'],
        'tokenizer' => ['ok' => extension_loaded('tokenizer'), 'value' => extension_loaded('tokenizer') ? 'yes' : 'no'],
        'gd' => ['ok' => extension_loaded('gd'), 'value' => extension_loaded('gd') ? 'yes' : 'no'],
        'fileinfo' => ['ok' => extension_loaded('fileinfo'), 'value' => extension_loaded('fileinfo') ? 'yes' : 'no'],
        'vendor/autoload.php' => ['ok' => file_exists(ROOT . '/vendor/autoload.php'), 'value' => file_exists(ROOT . '/vendor/autoload.php') ? 'ok' : 'missing'],
        'storage/ writable' => ['ok' => is_writable(ROOT . '/storage'), 'value' => is_writable(ROOT . '/storage') ? 'yes' : 'no'],
        'bootstrap/cache/ writable' => ['ok' => is_writable(ROOT . '/bootstrap/cache'), 'value' => is_writable(ROOT . '/bootstrap/cache') ? 'yes' : 'no'],
        'root writable (.env)' => ['ok' => is_writable(ROOT), 'value' => is_writable(ROOT) ? 'yes' : 'no'],
    ];
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
        . '<div class="brand-tag">Installer</div></div>'
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
            <div class="brand-tag">Installer</div>
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
                <input type="password" id="db_pass" name="db_pass" value="<?= h($_POST['db_pass'] ?? '') ?>">
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
