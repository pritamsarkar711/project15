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
//      a checklist drawn from the same self-checks.
//   3. After Laravel boots, auto-clears compiled Blade views when the deploy
//      version changes (after a Git pull on Hostinger). This ensures UI
//      changes are visible immediately without SSH or manual cache clear.
// ---------------------------------------------------------------------------

// Bump this string when you want to force a cache clear on every server.
define('HUVANTI_DEPLOY_VERSION', 'v56-2026-09-01-excellence-craft-pass');

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
    // Both maps are verified: the runtime loader (autoload_real.php) is fed
    // by autoload_static.php — the authoritative map — while
    // autoload_psr4.php is only the fallback. A damaged static map with an
    // intact psr4 map once passed this check and still crashed every request
    // with "Class Illuminate\Foundation\Application not found".
    foreach (['autoload_psr4.php', 'autoload_static.php'] as $name) {
        $map = __DIR__.'/../vendor/composer/'.$name;
        if (!is_file($map)) {
            return false;
        }

        $head = @file_get_contents($map, false, null, 0, 65536);
        if ($head === false || ! str_contains($head, "'Illuminate\\\\'")) {
            return false;
        }
    }

    return true;
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

    $files = [
        'ClassLoader.php', 'InstalledVersions.php',
        'autoload_classmap.php', 'autoload_files.php', 'autoload_namespaces.php',
        'autoload_psr4.php', 'autoload_real.php', 'autoload_static.php',
        'installed.php', 'platform_check.php',
        // Publish the entry point only after everything it requires is ready.
        'autoload.php',
    ];
    foreach ($files as $file) {
        if ($file === 'autoload.php' && $failed !== []) {
            $failed[] = 'autoload.php (not published because a dependency failed)';
            continue;
        }
        $src = $backupDir.'/'.$file;
        $dst = $file === 'autoload.php'
            ? __DIR__.'/../vendor/autoload.php'
            : $composerDir.'/'.$file;
        $content = @file_get_contents($src);
        $temporary = $dst.'.restore-'.bin2hex(random_bytes(4)).'.tmp';

        if ($content !== false
            && @file_put_contents($temporary, $content, LOCK_EX) === strlen($content)
            && @rename($temporary, $dst)) {
            @chmod($dst, 0644);
            if (function_exists('opcache_invalidate')) {
                @opcache_invalidate($dst, true);
            }
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
 * Render a human-readable failure page instead of the web server's blank
 * HTTP 500. Reaches the browser even when Laravel itself cannot boot.
 */
function huvanti_render_boot_failure_page(?Throwable $e, array $notes = []): void
{
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store, max-age=0');
        header('X-Robots-Tag: noindex, nofollow');
        header('X-Content-Type-Options: nosniff');
        header('X-Huvanti-Deploy: '.HUVANTI_DEPLOY_VERSION);
    }

    $h = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

    $checks = [
        'Deployment release' => [
            'ok' => true,
            'value' => HUVANTI_DEPLOY_VERSION,
        ],
        'PHP version' => [
            'ok' => PHP_VERSION_ID >= 80200,
            'value' => PHP_VERSION.(PHP_VERSION_ID >= 80200 ? '' : ' — Huvanti needs PHP 8.2+ (hPanel → PHP Configuration)'),
        ],
        'vendor/autoload.php' => [
            'ok' => is_file(__DIR__.'/../vendor/autoload.php'),
            'value' => is_file(__DIR__.'/../vendor/autoload.php') ? 'present' : 'missing — re-deploy from Git',
        ],
        'Laravel framework entry class' => [
            'ok' => is_file(__DIR__.'/../vendor/laravel/framework/src/Illuminate/Foundation/Application.php'),
            'value' => is_file(__DIR__.'/../vendor/laravel/framework/src/Illuminate/Foundation/Application.php')
                ? 'present'
                : 'missing — preserve .env, delete vendor/, then deploy the current main branch',
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
                : 'no — reload this page (auto-restore retries it)',
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

    // Never expose exception messages, absolute paths, SQL or stack traces on
    // the public error page. The full exception is written to the PHP error log
    // by the outer catch.
    $errorBlock = $e === null
        ? ''
        : '<p class="sub"><strong>Technical details were written to the server error log.</strong></p>';

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

    // Fail fast (before any class loading) when the framework entry class is
    // physically gone — composer "uninstalled" the vendored packages and no
    // autoloader can fix that. Also avoids noisy include-warnings.
    if (! is_file(__DIR__.'/../vendor/laravel/framework/src/Illuminate/Foundation/Application.php')) {
        huvanti_render_boot_failure_page(new RuntimeException(
            'The autoloader maps look intact but the Laravel framework files '
            .'are missing from vendor/. Delete the vendor/ folder via hPanel → '
            .'File Manager, then Git → Deploy to restore it.'
        ));
        exit(1);
    }

    // Register the Composer autoloader...
    require $autoload;

    if (!class_exists(Application::class)) {
        // Maps fine + file present, yet the class will not load: the running
        // PHP process is almost certainly serving stale OPcache.
        huvanti_render_boot_failure_page(new RuntimeException(
            'The autoloader maps are intact and the framework files exist, but Illuminate '
            .'classes still do not load — stale OPcache is the likely cause. Reload this '
            .'page; a redeploy (git push) also flushes the cached bytecode.'
        ));
        exit(1);
    }

    // Bootstrap Laravel and handle the request...
    /** @var Application $app */
    $app = require_once __DIR__.'/../bootstrap/app.php';

    // --- Auto-clear compiled views when deploy version changes ---
    // After a Git pull on Hostinger, the Blade view cache (storage/framework/views/)
    // still holds the OLD compiled templates, so the site looks unchanged.
    // This check detects a version change and wipes the cache automatically.
    $versionFile = __DIR__.'/../storage/framework/.huvanti_deploy_version';
    $storedVersion = @file_get_contents($versionFile);
    if (trim($storedVersion) !== HUVANTI_DEPLOY_VERSION) {
        // Clear compiled Blade views
        $viewCacheDir = __DIR__.'/../storage/framework/views';
        $cleared = 0;
        if (is_dir($viewCacheDir)) {
            foreach (glob($viewCacheDir.'/*.php') as $cachedFile) {
                if (@unlink($cachedFile)) $cleared++;
            }
        }
        // Clear application cache (includes settings cache)
        $appCacheDir = __DIR__.'/../storage/framework/cache/data';
        if (is_dir($appCacheDir)) {
            foreach (glob($appCacheDir.'/*') as $f) {
                @unlink($f);
            }
        }
        // Clear route, config and event caches (critical after adding new routes).
        // Do NOT delete packages.php / services.php — those are the compiled
        // provider maps. Wiping them on a host that ran `composer install --no-dev`
        // can leave Laravel unable to rediscover packages cleanly.
        foreach (['bootstrap/cache/routes-v7.php','bootstrap/cache/config.php','bootstrap/cache/events.php'] as $cacheFile) {
            $path = __DIR__.'/../'.$cacheFile;
            if (is_file($path)) @unlink($path);
        }
        // A leftover Vite HMR file makes @vite try to talk to localhost:5173
        // (or TypeError if the file is unreadable) and 500s every Blade page.
        if (is_file(__DIR__.'/hot')) {
            @unlink(__DIR__.'/hot');
        }
        // Clear view cache via glob as well
        foreach (glob(__DIR__.'/../storage/framework/cache/*') as $f) {
            if (is_file($f)) @unlink($f);
        }
        @file_put_contents($versionFile, HUVANTI_DEPLOY_VERSION, LOCK_EX);
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }

        // --- Data patches (run once per deploy version change) ---------------
        // Hostinger never runs `php artisan migrate`, so small data fixes are
        // applied here instead. Each patch is idempotent and wrapped in
        // try/catch so a missing table can never break the boot.
        try {
            $db = $app->make('db');
            try {
                $db->statement("CREATE TABLE IF NOT EXISTS `feedbacks` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `user_id` BIGINT UNSIGNED NULL, `overall_experience` VARCHAR(30) NULL, `profile_ease` VARCHAR(30) NULL, `publishing_ease` VARCHAR(30) NULL, `bug_report` TEXT NULL, `what_you_like` TEXT NULL, `what_to_improve` TEXT NULL, `feature_request` TEXT NULL, `additional_comment` TEXT NULL, `created_at` TIMESTAMP NULL, `updated_at` TIMESTAMP NULL, INDEX `feedbacks_user_id_index` (`user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            } catch (\Throwable $e) {}
            try {
                $db->statement("CREATE TABLE IF NOT EXISTS `sessions` (`id` VARCHAR(255) NOT NULL, `user_id` BIGINT UNSIGNED NULL, `ip_address` VARCHAR(45) NULL, `user_agent` TEXT NULL, `payload` LONGTEXT NOT NULL, `last_activity` INT NOT NULL, PRIMARY KEY (`id`), INDEX `sessions_user_id_index` (`user_id`), INDEX `sessions_last_activity_index` (`last_activity`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            } catch (\Throwable $e) {}
            try {
                $db->statement("CREATE TABLE IF NOT EXISTS `cache` (`key` VARCHAR(255) NOT NULL, `value` MEDIUMTEXT NOT NULL, `expiration` BIGINT NOT NULL, PRIMARY KEY (`key`), INDEX `cache_expiration_index` (`expiration`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            } catch (\Throwable $e) {}
            try {
                $db->statement("CREATE TABLE IF NOT EXISTS `cache_locks` (`key` VARCHAR(255) NOT NULL, `owner` VARCHAR(255) NOT NULL, `expiration` BIGINT NOT NULL, PRIMARY KEY (`key`), INDEX `cache_locks_expiration_index` (`expiration`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            } catch (\Throwable $e) {}
            try {
                if (!$db->getSchemaBuilder()->hasColumn('users', 'google_id')) {
                    $db->statement("ALTER TABLE `users` ADD COLUMN `google_id` VARCHAR(255) NULL UNIQUE AFTER `email`");
                }
            } catch (\Throwable $e) {}
            try {
                if (!$db->getSchemaBuilder()->hasColumn('users', 'avatar')) {
                    $db->statement("ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) NULL AFTER `google_id`");
                }
            } catch (\Throwable $e) {}
            $db->table('categories')
                ->where('slug', 'health-wellness')
                ->update(['icon' => 'heart-pulse']);
            $taken = $db->table('users')->where('username', 'joe-goldberg')->exists();
            if (!$taken) {
                $db->table('users')
                    ->where('username', 'pritam-sarkar')
                    ->orWhere('username', 'pritam.sarkar')
                    ->update(['username' => 'joe-goldberg']);
                $db->table('users')
                    ->whereNull('username')
                    ->where('email', 'like', '%pritam%')
                    ->update(['username' => 'joe-goldberg']);
            }
            $db->table('users')
                ->where('name', 'Pritam Sarkar')
                ->update(['name' => 'Joe Goldberg']);
            $now = date('Y-m-d H:i:s');
            $legalUpdates = [
                'privacy-policy' => '<h2>Privacy Policy</h2><p>Last updated: August 2026</p><p>Huvanti respects your privacy. This policy explains what information we collect, how we use it, and what choices you have. By using huvanti.com you agree to the practices described here.</p><h3>Information we collect</h3><p>We collect information you provide directly. This includes your name and email when you contact us, leave a comment, or create an author account. We also collect technical information automatically. This includes your IP address, browser type, device type, pages viewed, and approximate location. We collect this to keep the site secure and to understand which content is helpful.</p><h3>How we use your information</h3><p>We use your information to operate the site, respond to your messages, moderate comments, manage accounts, improve content, and measure audience. We do not sell your personal information.</p><h3>Cookies</h3><p>We use cookies to remember preferences and to analyze traffic. You can control cookies in your browser settings. See our Cookie Policy for details.</p><h3>Sharing</h3><p>We share data only when needed. This includes service providers who help us run the site, legal authorities when required by law, and successors if the site is transferred. All partners must protect your data.</p><h3>Your rights</h3><p>You may request access, correction, or deletion of your personal data. You may also object to certain processing. To exercise these rights, contact us through the contact page. We will respond within a reasonable time.</p><h3>Data retention and security</h3><p>We keep personal data only as long as needed for the purpose it was collected. We use reasonable technical and organizational measures to protect it, but no system is completely secure.</p><h3>Contact and updates</h3><p>If you have questions about this policy, use the contact page to reach us. We may update this policy from time to time. We will post the new version here and update the date at the top.</p>',
                'terms-conditions' => '<h2>Terms and Conditions</h2><p>Last updated: August 2026</p><p>These terms govern your use of huvanti.com. By accessing the site you agree to them. If you do not agree, please do not use the site.</p><h3>Use of the site</h3><p>You may use the site for personal and lawful purposes. You must not attempt to disrupt the site, gain unauthorized access, or use automated systems to extract content at scale.</p><h3>Intellectual property and copyright</h3><p>All articles, images, logos, and design on Huvanti belong to Huvanti or its licensors unless stated otherwise. You may share links and short quotes with attribution. You may not copy or republish substantial content without permission. If you believe material on this site infringes your copyright, send a notice through the contact page with the page link, a description of the original work and your contact details. We review valid reports promptly and remove infringing material when confirmed.</p><h3>User content</h3><p>If you submit posts, comments, or other content, you confirm that it is yours and does not infringe the rights of others. You grant Huvanti a license to display that content on the site. Published posts may remain visible even if your account is later removed.</p><h3>Accuracy and availability</h3><p>We work to keep information accurate and the site available, but we do not guarantee that content is complete or error free, or that the site will be uninterrupted.</p><h3>External links and affiliates</h3><p>The site may contain links to third party sites and affiliate links. We are not responsible for the content or practices of those sites. Any affiliate relationship is disclosed on the relevant page.</p><h3>Limitation of liability</h3><p>To the extent permitted by law, Huvanti is not liable for damages arising from your use of the site. Content is provided for general information and is not professional advice.</p><h3>Changes</h3><p>We may update these terms from time to time. The updated version will be posted here. Continued use after changes means you accept the new terms.</p>',
                'cookie-policy' => '<h2>Cookie Policy</h2><p>Last updated: August 2026</p><p>This policy explains how Huvanti uses cookies and similar technologies on huvanti.com.</p><h3>What are cookies</h3><p>Cookies are small text files stored on your device when you visit a site. They help the site remember your preferences and understand how visitors use it.</p><h3>How we use cookies</h3><p>We use necessary cookies to make the site work. We use preference cookies to remember your theme choice. We use analytics cookies to see which pages are popular and how visitors move through the site. We may use advertising cookies only if ads are enabled, to measure ad performance.</p><h3>Managing cookies</h3><p>You can control cookies through your browser settings. You can block or delete cookies at any time. If you block necessary cookies, some parts of the site may not work correctly.</p><h3>More information</h3><p>If you have questions about our use of cookies, contact us through the contact page. Also see our Privacy Policy for how we handle personal data.</p>',
                'about' => '<h2>About Huvanti</h2><p>Huvanti is an independent online publication. We write practical, easy to read articles across technology, health and wellness, finance, travel, lifestyle, and education. One topic at a time, explained in plain language.</p><h3>What we cover</h3><p>Technology explains the tools and trends shaping daily life in plain language. Health and Wellness offers practical guidance on food, sleep, fitness, and focus that is grounded in evidence and real experience. Finance breaks down saving, investing, and money habits into steps you can act on. Travel covers meaningful destinations and smarter planning. Lifestyle explores calm and intentional living. Education shares learning methods that actually help you retain knowledge.</p><h3>How we work</h3><p>Every article starts with a question a real person would ask. We research the answer, check claims against original sources and review each piece before it goes live. When information changes, we update the article and note the change.</p><h3>Our promise</h3><p>No clickbait. No filler. No fake urgency. If we recommend something, it is because we believe it helps you, not because someone paid us to say so. Where affiliate links appear, they are disclosed clearly on the page.</p><h3>Talk to us</h3><p>Questions, corrections and ideas are always welcome. Reach us any time through the <a href="/contact">contact page</a>.</p>',
                'contact' => '<h2>Contact Huvanti</h2><p>We welcome your questions, feedback, and ideas. Use the form on this page to send us a message. We read every submission and reply as soon as we can.</p><h3>What to include</h3><p>For general feedback, tell us which article or page you are referring to. For editorial concerns, please describe the issue and include a link. For business inquiries, briefly describe your proposal.</p><h3>Response time</h3><p>We aim to reply within two business days. Response time may be longer during busy periods.</p>',
                'editorial-policy' => '<h2>Editorial Policy</h2><p>Last updated: August 2026</p><p>This policy explains how Huvanti creates, reviews, and maintains content. Our goal is to be accurate, independent, and useful.</p><h3>How we write</h3><p>Every article begins with a question a real reader would ask. We research the answer, test claims where possible, and write in clear language. Writers draw from direct experience with the products, places, and techniques they cover.</p><h3>Sourcing and accuracy</h3><p>When we cite studies, statistics, or news, we link to the original source so you can verify it. Facts are checked before publication. If we cannot verify a claim, we state this clearly or omit it.</p><h3>Independence</h3><p>Advertisers and affiliate partners have no influence on what we write or how we rate anything. Commissions never affect recommendations. If a product is not good, we say so even when we could earn from it.</p><h3>Use of AI tools</h3><p>We may use AI tools for grammar and research support in the same way a writer uses a spell checker. Every published article is written, reviewed, and approved by a human editor who takes responsibility for it. We do not publish unreviewed machine generated text.</p><h3>Corrections</h3><p>Mistakes can happen. When an error is found, we correct the article and note the change. You can report an issue at any time through the contact page.</p><h3>Author standards</h3><p>Contributing authors follow our posting rules. Work must be original, honest, and free of filler. Every submission receives human review before it can be published.</p>',
                'affiliate-disclosure' => '<h2>Affiliate Disclosure</h2><p>Last updated: August 2026</p><p>Huvanti is a reader supported publication. Some pages on this site contain affiliate links. If you click one of these links and make a purchase, we may earn a commission. You never pay more because of this. The price stays the same whether you use our link or go to the seller directly.</p><h3>How affiliate links work here</h3><p>Every article that contains affiliate links carries a clear disclosure notice before the content begins. The notice tells you the page includes affiliate links and that we may earn a commission. This notice appears on the article itself, not hidden in a footer or a separate policy page only.</p><h3>Our editorial promise</h3><p>Commissions never decide what we recommend. Writers and editors choose products based on research, real experience and value for the reader. If a product is not good, we say so even when a commission is available. Sponsored placements, if ever used, are labeled as sponsored.</p><h3>Who we work with</h3><p>We only join affiliate programs of reputable companies that readers already know and trust, such as major retailers and well established brands. We do not link to unknown or low quality sites for the sake of a commission.</p><h3>Prices and availability</h3><p>Prices and availability mentioned in articles can change without notice on the seller side. Always check the final price on the seller page before buying.</p><h3>Your support</h3><p>Using our links costs you nothing extra. It simply helps us keep publishing free, well researched content. Thank you for supporting Huvanti.</p><h3>Questions</h3><p>If you have questions about this disclosure or any link on the site, reach us through the contact page.</p>',
                'comment-policy' => '<h2>Comment Policy</h2><p>Last updated: August 2026</p><p>Comments on Huvanti are welcome and moderated. This policy explains the rules so every reader knows what to expect.</p><h3>What we allow</h3><p>Questions about the article. Honest opinions shared politely. Additional tips that help other readers. Corrections if you believe something is wrong.</p><h3>What we do not allow</h3><p>Insults, harassment or personal attacks. Spam, self promotion or links to unrelated sites. Hate speech of any kind. False claims presented as fact. Anything illegal, unsafe or harmful, including links to adult, gambling, pirated or malicious content.</p><h3>Moderation</h3><p>Every comment is reviewed before it appears, or shortly after, depending on the article. We may edit or remove comments that break this policy. Repeated violations can lead to blocked posting. Comments express the views of the person who wrote them, not the views of Huvanti.</p><h3>Your privacy</h3><p>We ask for your name and email to comment. Your email is never published or shared. It is stored securely and used only for moderation. See our Privacy Policy for full details.</p><h3>Remove a comment</h3><p>You can ask us to remove your own comment at any time. Use the contact page and include the article link and the comment text.</p>',
            ];
            foreach ($legalUpdates as $slug => $content) {
                try {
                    $existing = $db->table('pages')->where('slug', $slug)->first();
                    if ($existing) {
                        $old = (string) ($existing->content ?? '');
                        if (strlen(strip_tags($old)) < 1200 || strpos($old, 'Last updated: August 2026') === false || strpos($old, 'infringes your copyright') === false || ($slug === 'about' && strpos($old, 'Our promise') === false)) {
                            $db->table('pages')->where('slug', $slug)->update(['content' => $content, 'updated_at' => $now]);
                        }
                    } else {
                        $title = ucwords(str_replace('-', ' ', $slug));
                        if ($slug === 'terms-conditions') $title = 'Terms and Conditions';
                        if ($slug === 'privacy-policy') $title = 'Privacy Policy';
                        if ($slug === 'cookie-policy') $title = 'Cookie Policy';
                        if ($slug === 'about') $title = 'About Us';
                        if ($slug === 'contact') $title = 'Contact';
                        if ($slug === 'editorial-policy') $title = 'Editorial Policy';
                        if ($slug === 'affiliate-disclosure') $title = 'Affiliate Disclosure';
                        if ($slug === 'comment-policy') $title = 'Comment Policy';
                        $db->table('pages')->insert(['title' => $title, 'slug' => $slug, 'content' => $content, 'status' => 'published', 'created_at' => $now, 'updated_at' => $now]);
                    }
                } catch (\Throwable $e) { error_log('[huvanti] legal page patch '.$slug.': '.$e->getMessage()); }
            }
            try {
                $postIds = $db->table('posts')->where('status', 'published')->pluck('id');
                foreach ($postIds as $pid) {
                    $hasFaq = $db->table('faqs')->where('post_id', $pid)->exists();
                    if (!$hasFaq) {
                        $post = $db->table('posts')->where('id', $pid)->first();
                        $title = $post ? (string) $post->title : 'this article';
                        $db->table('faqs')->insert([
                            ['post_id' => $pid, 'question' => 'What is the main takeaway from '.substr($title, 0, 60).'?', 'answer' => 'The main takeaway is to apply the practical steps described in the article and adapt them to your own context. Each section gives you a clear action you can try right away.', 'sort_order' => 0, 'created_at' => $now, 'updated_at' => $now],
                            ['post_id' => $pid, 'question' => 'How often is this article updated?', 'answer' => 'We review published articles on a regular basis and update them when information changes or when readers share useful feedback.', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
                        ]);
                    }
                }
            } catch (\Throwable $e) { error_log('[huvanti] faq patch: '.$e->getMessage()); }
        } catch (\Throwable $dataPatchError) {
            error_log('[huvanti] data patch skipped: '.$dataPatchError->getMessage());
        }
    }

    $app->handleRequest(Request::capture());
} catch (Throwable $e) {
    // Anything that blew up before Laravel's own exception handler took over.
    error_log((string) $e);
    huvanti_render_boot_failure_page($e);
    exit(1);
}
