# Pre-release checklist

Use this checklist before tagging `v0.1.0` on `main`. Automated repository checks run on every pull request to `develop`; the release workflow repeats the full validation suite before publishing a ZIP.

## Automated validation in CI

The following jobs must pass on the release candidate commit:

- Composer validate and audit
- PHP syntax (PHP 8.0)
- PHP 8.0 syntax and PHPCompatibility (`php-compatibility.yml`)
- PHPCS and PHPCompatibilityWP
- PHPStan
- PHPUnit (PHP 8.1–8.4)
- Vite build
- Playwright (Chromium, Firefox, WebKit)

## Local commands

```bash
composer validate --strict --no-check-publish
composer install
composer audit --locked --no-interaction
find plugin/magazine73 -name '*.php' -print0 | xargs -0 -n1 php -l
composer phpcs
composer phpstan
composer test
npm ci
npm run build
npm run test:e2e
bash bin/package-release.sh 0.1.0
bash bin/verify-release-zip.sh magazine73-0.1.0.zip
```

## Version synchronization

Before release, confirm these values all match `0.1.0`:

- `plugin/magazine73/magazine73.php` header `Version`
- `MAGAZINE73_VERSION` constant
- `plugin/magazine73/readme.txt` `Stable tag`
- `CHANGELOG.md` latest entry
- Git tag prefix `v0.1.0`
- Output ZIP filename `magazine73-0.1.0.zip`

## WordPress Plugin Check

Plugin Check is not executed in CI because it requires a full WordPress runtime. Run it locally before release:

```bash
wp plugin install plugin-check --activate
wp plugin activate magazine73
wp plugin check magazine73 --checks=all
```

If Plugin Check is unavailable, review `readme.txt` manually and verify activation, admin screens, shortcode rendering, and uninstall behavior in a local WordPress 6.6+ site with PHP 8.0+.

Do not claim Plugin Check passed unless it was actually executed.

## Playwright fixture scope

Playwright tests load the production bundles from `plugin/magazine73/assets/dist/` through static HTML fixtures in `tests/fixtures/`. They verify viewer initialization, navigation, zoom, thumbnails, fullscreen control presence, progressive loading completion, resume prompt behavior, and admin panel bootstrapping.

These tests are not full WordPress integration tests. They do not boot WordPress, authenticate users, or exercise Media Library uploads.

## Manual WordPress acceptance testing

Perform these checks in a local WordPress 6.6+ environment before release:

1. Activate Magazine73 on a clean site.
2. Create a magazine draft, upload WebP pages, and confirm natural filename sorting.
3. Configure global viewer settings and per-magazine overrides.
4. Publish the magazine and verify the public URL and `[magazine73]` shortcode.
5. Test desktop two-page and mobile one-page viewer layouts.
6. Verify progressive loading, resume prompt, zoom, thumbnails, fullscreen, and keyboard navigation.
7. Attach an optional PDF and confirm the download control appears only when enabled.
8. Switch admin language to Spanish and confirm translated strings in PHP and JavaScript UI.
9. Confirm uninstall leaves Media Library attachments untouched by default.
10. Enable **Delete plugin data on uninstall**, reinstall, and verify only Magazine73 posts, metadata, and plugin options are removed.

## Packaging verification

The release ZIP must include runtime PHP, built assets, templates, translations, `readme.txt`, `license.txt`, `uninstall.php`, and third-party notices.

It must exclude GitHub metadata, tests, docs, Docker files, `AGENTS.md`, source-only frontend files, `node_modules`, `vendor`, Composer development files, Playwright files, PHPUnit files, Cursor files, caches, and secrets.

Use `bash bin/verify-release-zip.sh magazine73-0.1.0.zip` after packaging.
