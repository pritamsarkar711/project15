<?php

/** Smoke tests for installer serialization and atomic writes. */

declare(strict_types=1);

$failed = false;

function installer_test_check(bool $condition, string $message): void
{
    global $failed;
    echo ($condition ? '  PASS  ' : '  FAIL  ') . $message . PHP_EOL;
    $failed = $failed || !$condition;
}

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'huvanti.test';
ob_start();
require dirname(__DIR__) . '/install.php';
ob_end_clean();

require_once dirname(__DIR__) . '/vendor/autoload.php';

$original = 'pa\\ss"word#${NOT_AN_ENV_VAR}';
$encoded = huvanti_env_quote($original);
$entries = (new Dotenv\Parser\Parser())->parse('VALUE=' . $encoded);
$value = $entries[0]->getValue()->get();
installer_test_check($value->getChars() === $original, 'dotenv quoting round-trips special characters');
installer_test_check($value->getVars() === [], 'dotenv quoting disables variable interpolation');

$temp = sys_get_temp_dir() . '/huvanti-install-' . bin2hex(random_bytes(6));
try {
    installer_test_check(huvanti_atomic_write($temp, "sensitive\n", 0600), 'atomic installer write succeeds');
    installer_test_check(file_get_contents($temp) === "sensitive\n", 'atomic installer write preserves content');
    installer_test_check((fileperms($temp) & 0777) === 0600, 'atomic installer write applies restrictive mode');
} finally {
    @unlink($temp);
}

exit($failed ? 1 : 0);
