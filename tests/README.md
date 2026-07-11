# Test suite notes

## PHPUnit

Unit tests live in `tests/phpunit/` and use WordPress stubs from `tests/support/wordpress-stubs.php`. They validate plugin-owned PHP behavior without booting WordPress.

Coverage includes viewer settings, magazine pages, PDF metadata, renderer settings resolution, data lifecycle migrations, and uninstall cleanup behavior.

## Playwright

End-to-end tests live in `tests/e2e/` and load static HTML fixtures from `tests/fixtures/`.

Each viewer test imports the production bundle built by Vite:

`plugin/magazine73/assets/dist/js/magazine73-viewer.js`

The admin fixture loads:

`plugin/magazine73/assets/dist/js/magazine73-editor.js`

These tests verify client-side initialization and control wiring. They are not substitutes for full WordPress integration testing in a running site.

## What Playwright covers

- Production bundle loading
- StPageFlip initialization and ready state
- Keyboard navigation
- Progressive loading overlay dismissal
- Resume reading prompt
- Thumbnails toggle
- Zoom control behavior
- Fullscreen control presence
- PDF download control presence
- Admin pages and PDF field bootstrapping

## What still requires manual WordPress testing

- Plugin activation and capabilities
- Media Library uploads and WebP validation in admin
- Public magazine URLs and theme template overrides
- Real fullscreen behavior across browsers
- Actual PDF downloads from WordPress attachment URLs
- Spanish translations in wp-admin and the front end
- Uninstall behavior inside a real WordPress database

See `docs/release/pre-release-checklist.md` for the manual acceptance checklist.
