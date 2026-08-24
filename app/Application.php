<?php

namespace App;

use Illuminate\Foundation\Application as BaseApplication;

/**
 * Huvanti application container.
 *
 * The only reason this subclass exists is getNamespace(). The stock Laravel
 * implementation reads composer.json from the project root at runtime and
 * throws "Unable to detect application namespace." when the file is missing.
 *
 * On Hostinger shared hosting we deliberately ship WITHOUT a root
 * composer.json: the host's Git auto-deploy detects that file and runs
 * `composer install`, which regenerated the committed vendor/ autoloader
 * from whatever composer.json said — and once produced dependency-less maps
 * that killed every request with a blank HTTP 500. No composer.json at the
 * root means the auto-deploy never invokes Composer and the committed
 * vendor/ stays exactly as it is in git.
 *
 * Returning the namespace directly keeps all callers (Blade component class
 * guessing, console command discovery, model factories, artisan generators)
 * working without the file. If the PSR-4 mapping in
 * .composer-backup/composer.json ever changes, update this constant too.
 */
class Application extends BaseApplication
{
    public function getNamespace(): string
    {
        return $this->namespace ??= 'App\\';
    }
}
