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
 * A root composer.json and composer.lock ARE tracked today so Hostinger's
 * Git auto-deploy runs Composer with the correct dependency list (see the
 * .gitignore note). This override remains as belt and braces: on a damaged
 * checkout where the manifest was deleted, clobbered or replaced by the old
 * dependency-less deployment stub, the app still boots and the recovery
 * flows documented in HOSTINGER_DEPLOYMENT.md can repair vendor/ instead of
 * dying with a blank HTTP 500.
 *
 * Returning the namespace directly keeps all callers (Blade component class
 * guessing, console command discovery, model factories, artisan generators)
 * working without the file. If the PSR-4 mapping in composer.json ever
 * changes, keep .composer-backup/composer.json in sync with it.
 */
class Application extends BaseApplication
{
    public function getNamespace(): string
    {
        return $this->namespace ??= 'App\\';
    }
}
