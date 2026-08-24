# Autoloader backup (DO NOT EDIT)

These are **pristine copies** of `vendor/autoload.php` + `vendor/composer/*.php`
generated from the full dependency set (see `.composer-backup/composer.json`).

## Why this exists

Huvanti deploys to Hostinger shared hosting via Git. Hostinger's auto-deploy
used to detect a root `composer.json` and run `composer install`, which
**regenerated the committed autoloader from whatever composer.json said**.
When composer.json had no `require` entries (the old "deployment stub"), the
regenerated autoloader mapped nothing, so `Illuminate\…` classes stopped
loading and every request died with a blank HTTP 500.

`public/index.php` and `install.php` now self-heal: before requiring
`vendor/autoload.php` each verifies the autoloader maps — both
`autoload_psr4.php` and `autoload_static.php` (the authoritative map the
runtime loader actually uses; checking only the psr4 map once let a damaged
static map slip through and crash with `Class Illuminate\Foundation\Application
not found`). If either file lost the `Illuminate\` mappings, the pristine
copies are restored from `bootstrap/autoload_backup/` (cached OPcache
bytecode is flushed too) and the boot continues. `install.php?repair=1`
can run the same restore from a browser.

## Maintenance

If you ever regenerate the autoloader locally (after adding a package via the
`.composer-backup/` workflow), refresh this backup too:

```bash
cp vendor/autoload.php bootstrap/autoload_backup/autoload.php
cp vendor/composer/ClassLoader.php vendor/composer/InstalledVersions.php \
   vendor/composer/autoload_classmap.php vendor/composer/autoload_files.php \
   vendor/composer/autoload_namespaces.php vendor/composer/autoload_psr4.php \
   vendor/composer/autoload_real.php vendor/composer/autoload_static.php \
   vendor/composer/installed.php vendor/composer/platform_check.php \
   bootstrap/autoload_backup/
```

`installed.json` is intentionally excluded — only the `composer` CLI uses it.
