# Issue closure comments

GitHub issues #3 through #15 were implemented in pull requests merged into `develop`. Use the comments below when closing each issue manually. Final local WordPress acceptance testing is tracked separately in `docs/release/pre-release-checklist.md`.

## #3 — Repository automation and CI foundation

Completed in PR #17 (`cursor/repository-automation-ci-bdb8`).

Automated validation on `develop`: PHPCS, PHPStan, PHP syntax, Composer audit, Vite build, and CodeQL.

Final local WordPress acceptance testing is tracked separately before `v0.1.0`.

## #4 — Magazine metadata and admin list columns

Completed in PR #19 (`feature/magazine-metadata`).

Automated validation on `develop`: PHPCS, PHPStan, PHP syntax, and PHPUnit coverage for related metadata flows where applicable.

Final local WordPress acceptance testing is tracked separately before `v0.1.0`.

## #5 — WebP magazine page management

Completed in PR #20 (`feature/webp-page-management`).

Automated validation on `develop`: PHPUnit `MagazinePagesTest`, PHPCS, PHPStan, and Vite build.

Final local WordPress acceptance testing is tracked separately before `v0.1.0`.

## #6 — Global viewer settings and per-magazine overrides

Completed in PR #22 (`feature/viewer-settings`).

Automated validation on `develop`: PHPUnit `ViewerSettingsTest`, PHPCS, PHPStan, and Vite build.

Final local WordPress acceptance testing is tracked separately before `v0.1.0`.

## #7 — Shortcode and public magazine templates

Completed in PR #23 (`feature/shortcode-public-template`).

Automated validation on `develop`: PHPCS, PHPStan, PHP syntax, and Vite build.

Final local WordPress acceptance testing is tracked separately before `v0.1.0`.

## #8 — StPageFlip viewer integration

Completed in PR #24 (`feature/stpageflip-viewer`).

Automated validation on `develop`: PHPCS, PHPStan, Playwright viewer fixture tests, and Vite build.

Final local WordPress acceptance testing is tracked separately before `v0.1.0`.

## #9 — Viewer controls, zoom, thumbnails, and fullscreen

Completed in PR #25 (`feature/viewer-controls`).

Automated validation on `develop`: Playwright viewer fixture coverage for controls, PHPCS, PHPStan, and Vite build.

Final local WordPress acceptance testing is tracked separately before `v0.1.0`.

## #10 — Progressive page loading and reading progress

Completed in PR #26 (`cursor/progressive-loading-progress-bdb8`).

Automated validation on `develop`: Playwright loading and resume prompt coverage, PHPCS, PHPStan, and Vite build.

Final local WordPress acceptance testing is tracked separately before `v0.1.0`.

## #11 — Optional PDF download support

Completed in PR #27 (`cursor/pdf-download-bdb8`).

Automated validation on `develop`: PHPUnit `MagazinePdfTest`, Playwright admin fixture coverage, PHPCS, PHPStan, and Vite build.

Final local WordPress acceptance testing is tracked separately before `v0.1.0`.

## #12 — Vite and modular frontend assets

Completed in PR #18 (`chore/vite-assets`) and PR #21 (`chore/vite-assets`, WordPress 6.6 minimum and script modules).

Automated validation on `develop`: Vite build, PHPCS, PHPStan, and Playwright bundle loading checks.

Final local WordPress acceptance testing is tracked separately before `v0.1.0`.

## #13 — Internationalization and Spanish translations

Completed in PR #28 (`cursor/internationalization-spanish-bdb8`).

Automated validation on `develop`: PHPCS, PHPStan, Vite build, and translation artifact presence in the release ZIP verification script.

Final local WordPress acceptance testing is tracked separately before `v0.1.0`.

## #14 — Data migrations and uninstall controls

Completed in PR #29 (`cursor/data-lifecycle-bdb8`).

Automated validation on `develop`: PHPUnit `DataLifecycleTest` and `UninstallerTest`, PHPCS, and PHPStan.

Final local WordPress acceptance testing is tracked separately before `v0.1.0`.

## #15 — Automated tests and WordPress.org release packaging

Completed in PR #30 (`cursor/mvp-release-readiness-bdb8`) and follow-up audit fixes.

Automated validation on `develop`: PHPUnit, Playwright, release packaging scripts, PHP 8.0 compatibility workflow, and gated release workflow.

Final local WordPress acceptance testing is tracked separately before `v0.1.0`.
