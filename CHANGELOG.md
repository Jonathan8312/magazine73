# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.3] - 2026-07-18

### Fixed

- Public magazine template no longer triggers the `header.php` deprecation on block themes such as Twenty Twenty-Five.

## [0.1.2] - 2026-07-18

### Fixed

- Public magazine template now references `\Magazine73\Magazine_Meta` and `\Magazine73\Magazine_Renderer`, preventing a fatal error on the single magazine page.

## [0.1.1] - 2026-07-18

### Fixed

- Magazine editor now loads admin assets and opens the Media Library from **Add or Upload Pages**.
- Admin stylesheet URLs from the Vite manifest resolve to real CSS files instead of broken paths.
- Bundled JavaScript i18n falls back safely when `wp.i18n` is unavailable (Playwright fixtures).

## [0.1.0] - 2026-07-11

### Added

- Magazine custom post type with admin list columns and metadata.
- WebP page management with natural filename sorting.
- Global viewer settings and per-magazine overrides.
- Shortcode and public magazine templates.
- StPageFlip 2.0.7 viewer integration with progressive page loading.
- Viewer controls for navigation, zoom, fullscreen, and thumbnails.
- Local reading progress without personal data.
- Optional PDF download support.
- Spanish (`es_ES`) translations and JavaScript i18n support.
- Data lifecycle migrations and optional uninstall cleanup.
- PHPUnit and Playwright test coverage.
- WordPress.org `readme.txt` and release packaging workflow.

### Fixed

- StPageFlip viewer initialization now calls `loadFromImages()` before waiting for the `init` event.
- Release workflow now gates ZIP publishing on the full validation suite and PHP 8.0 compatibility checks.

[0.1.3]: https://github.com/Jonathan8312/magazine73/releases/tag/v0.1.3
[0.1.2]: https://github.com/Jonathan8312/magazine73/releases/tag/v0.1.2
[0.1.1]: https://github.com/Jonathan8312/magazine73/releases/tag/v0.1.1
[0.1.0]: https://github.com/Jonathan8312/magazine73/releases/tag/v0.1.0
