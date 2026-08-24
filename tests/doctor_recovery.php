<?php

/** Smoke-test the one-file doctor against a directory with no backup/vendor maps. */

declare(strict_types=1);

$repo = dirname(__DIR__);
$temp = sys_get_temp_dir() . '/huvanti-doctor-' . bin2hex(random_bytes(6));
$failed = false;

function doctor_test_check(bool $condition, string $message): void
{
    global $failed;
    echo ($condition ? '  PASS  ' : '  FAIL  ') . $message . PHP_EOL;
    $failed = $failed || !$condition;
}

function doctor_test_remove(string $path): void
{
    if (!is_dir($path)) {
        @unlink($path);
        return;
    }
    foreach (scandir($path) ?: [] as $entry) {
        if ($entry !== '.' && $entry !== '..') {
            doctor_test_remove($path . '/' . $entry);
        }
    }
    @rmdir($path);
}

try {
    mkdir($temp, 0775, true);
    copy($repo . '/doctor.php', $temp . '/doctor.php');

    define('HUVANTI_DOCTOR_LIBRARY_ONLY', true);
    require $temp . '/doctor.php';

    [$copied, $errors] = doc_restore_autoload();
    doctor_test_check($errors === [], 'embedded recovery reports no write errors');
    doctor_test_check(count($copied) === 11, 'embedded recovery restores all 11 Composer files');
    doctor_test_check(doc_autoload_maps_ok(), 'restored maps contain the Illuminate namespace');

    foreach (glob($repo . '/bootstrap/autoload_backup/*.php') ?: [] as $expected) {
        $name = basename($expected);
        $actual = $name === 'autoload.php'
            ? $temp . '/vendor/autoload.php'
            : $temp . '/vendor/composer/' . $name;
        doctor_test_check(
            is_file($actual) && hash_file('sha256', $expected) === hash_file('sha256', $actual),
            $name . ' matches the pristine backup'
        );
    }
} catch (Throwable $e) {
    doctor_test_check(false, get_class($e) . ': ' . $e->getMessage());
} finally {
    doctor_test_remove($temp);
}

exit($failed ? 1 : 0);
