<?php

/**
 * Self-heal smoke tests for the Huvanti installer + front controller.
 *
 * These exercise the exact failure modes seen in production on shared
 * hosting: clobbered Composer autoloader maps, a gutted vendor/ tree and
 * the resulting "Class Illuminate\Foundation\Application not found".
 * They run on plain PHP — no database needed.
 *
 * Usage (from the repo root, one scenario per process):
 *   php tests/selfheal.php requirements
 *   php tests/selfheal.php pristine
 *   php tests/selfheal.php psr4-clobber
 *   php tests/selfheal.php static-clobber
 *   php tests/selfheal.php both-clobber
 *   php tests/selfheal.php framework-missing
 *   php tests/selfheal.php autoload-missing
 *
 * Destructive scenarios heal/undo themselves, but resetting with
 * `git checkout -- vendor/` between runs is never a bad idea.
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$scenarios = [
    'requirements',
    'pristine',
    'psr4-clobber',
    'static-clobber',
    'both-clobber',
    'framework-missing',
    'autoload-missing',
];

$scenario = $argv[1] ?? '';
if (!in_array($scenario, $scenarios, true)) {
    fwrite(STDERR, 'Usage: php tests/selfheal.php <' . implode('|', $scenarios) . ">\n");
    exit(2);
}

$root = dirname(__DIR__);
$failures = 0;

function check(string $name, bool $ok): void
{
    global $failures;
    echo ($ok ? '  PASS  ' : '  FAIL  ') . $name . "\n";
    if (! $ok) {
        $failures++;
    }
}

/** Strip the 'Illuminate\' PSR-4 keys from an autoloader map on disk. */
function clobber(string $file): void
{
    global $root;
    $path = $root . '/' . $file;
    $content = file_get_contents($path);
    if ($content === false || ! str_contains($content, "'Illuminate\\\\'")) {
        fwrite(STDERR, "expected an Illuminate\\ mapping inside {$file} — aborting\n");
        exit(2);
    }
    file_put_contents($path, str_replace("'Illuminate\\\\'", "'Clobbered\\\\'", $content));
}

// Load install.php's helper functions. Under GET it only renders the
// installer form (discarded below), so this is side-effect free.
$_SERVER['REQUEST_METHOD'] = 'GET';
ob_start();
require $root . '/install.php';
ob_end_clean();

echo "scenario: {$scenario}\n";

switch ($scenario) {
    case 'requirements':
        foreach (check_requirements() as $label => $r) {
            check("requirement ok: {$label} ({$r['value']})", $r['ok']);
        }
        break;

    case 'pristine':
        check('maps ok on a pristine tree', huvanti_autoload_maps_ok());
        check('framework files ok on a pristine tree', huvanti_framework_files_ok());
        try {
            $app = huvanti_boot_laravel();
            check('boots the Application', $app instanceof Illuminate\Foundation\Application);
            $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
            check('console kernel bootstraps (needed for migrate/seed)', true);
        } catch (Throwable $e) {
            check('boot on pristine tree (' . get_class($e) . ': ' . $e->getMessage() . ')', false);
        }
        break;

    case 'psr4-clobber':
        clobber('vendor/composer/autoload_psr4.php');
        check('maps detect a clobbered psr4 map', ! huvanti_autoload_maps_ok());
        try {
            $app = huvanti_boot_laravel();
            check('boot self-heals a clobbered psr4 map', $app instanceof Illuminate\Foundation\Application);
            check('maps ok again after healing', huvanti_autoload_maps_ok());
        } catch (Throwable $e) {
            check('boot after psr4 clobber (' . get_class($e) . ': ' . $e->getMessage() . ')', false);
        }
        break;

    case 'static-clobber':
        // The case the OLD health check missed: the runtime map
        // (autoload_static.php) is damaged while the fallback psr4 map
        // still looks fine — previously ended in "Class ... not found".
        clobber('vendor/composer/autoload_static.php');
        check('maps detect a clobbered static map (old check missed this)', ! huvanti_autoload_maps_ok());
        try {
            $app = huvanti_boot_laravel();
            check('boot self-heals a clobbered static map', $app instanceof Illuminate\Foundation\Application);
            check('maps ok again after healing', huvanti_autoload_maps_ok());
        } catch (Throwable $e) {
            check('boot after static clobber (' . get_class($e) . ': ' . $e->getMessage() . ')', false);
        }
        break;

    case 'both-clobber':
        clobber('vendor/composer/autoload_psr4.php');
        clobber('vendor/composer/autoload_static.php');
        check('maps detect a fully clobbered autoloader', ! huvanti_autoload_maps_ok());
        try {
            $app = huvanti_boot_laravel();
            check('boot self-heals a fully clobbered autoloader', $app instanceof Illuminate\Foundation\Application);
        } catch (Throwable $e) {
            check('boot after full clobber (' . get_class($e) . ': ' . $e->getMessage() . ')', false);
        }
        break;

    case 'framework-missing':
        $file = $root . '/vendor/laravel/framework/src/Illuminate/Foundation/Application.php';
        rename($file, $file . '.bak');
        try {
            check('framework check flags the gutted vendor/', ! huvanti_framework_files_ok());
            try {
                huvanti_boot_laravel();
                check('boot throws instead of crashing', false);
            } catch (RuntimeException $e) {
                check('actionable RuntimeException (not a raw class-not-found)', str_contains($e->getMessage(), 'File Manager'));
            }
        } finally {
            rename($file . '.bak', $file);
        }
        break;

    case 'autoload-missing':
        $file = $root . '/vendor/autoload.php';
        rename($file, $file . '.bak');
        try {
            try {
                huvanti_boot_laravel();
                check('boot throws instead of a fatal require', false);
            } catch (RuntimeException $e) {
                check('explains how to fix a missing vendor/autoload.php', str_contains($e->getMessage(), 'Git'));
            }
        } finally {
            rename($file . '.bak', $file);
        }
        break;
}

exit($failures === 0 ? 0 : 1);
