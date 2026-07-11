# Magazine73 — AI Development Instructions

## Project

Magazine73 is a WordPress plugin for creating and publishing digital magazines with a page-flip viewer.

## Core requirements

- WordPress 6.6 or newer.
- PHP 8.0 or newer.
- Source language and fallback language: English.
- Text domain: `magazine73`.
- Author: Jonathan Torres.
- Author website: <https://73software.com>
- Public repository: <https://github.com/Jonathan8312/magazine73>
- Main PHP namespace: `Magazine73`.

## Architecture

- Use modular classes separated by responsibility.
- Load classes with explicit `require_once`.
- Do not introduce Composer unless explicitly approved.
- Keep the main plugin file minimal.
- Store reusable PHP templates in `/templates`.
- Allow themes to override templates through:

  `wp-content/themes/{active-theme}/magazine73/`

- CSS and JavaScript must remain controlled by the plugin.

## WordPress standards

- Follow WordPress Coding Standards.
- Use WordPress APIs instead of custom replacements.
- Use nonces for form actions.
- Check user capabilities before reading or changing protected data.
- Sanitize all input.
- Escape all output.
- Avoid generic global functions.
- Prefix hooks, options, metadata, handles and identifiers with `magazine73_` when namespaces do not apply.

## Internationalization

- All source strings must be written in English.
- All visible strings must use WordPress translation functions.
- Use the text domain `magazine73`.
- JavaScript translations must use `wp_set_script_translations()`.
- Spanish translations will be included.
- If no translation exists, WordPress must display the original English text.

## Assets

- Use Vite for CSS and JavaScript builds.
- Use modular ES JavaScript.
- Use modular CSS.
- Load frontend assets only when a Magazine73 viewer is rendered.
- Do not load assets globally.
- Use plugin-owned SVG icons.
- Do not rely on external CDNs.

## Third-party dependencies

- Only add dependencies with permissive licenses such as MIT, BSD or Apache-2.0 unless explicitly approved.
- Every third-party dependency must include:
  - project name;
  - version;
  - source;
  - license;
  - original license file;
  - notice inside `/third-party`.
- StPageFlip 2.0.7 is approved under the MIT license.
- Do not add a dependency before checking its license.

### Development-only dependency exception

Composer development tooling may include packages under LGPL-2.1-or-later when they are used only for local and CI validation and are excluded from production ZIP artifacts via `.distignore`. The current approved exception covers:

- `phpcompatibility/php-compatibility` (LGPL-2.1-or-later)
- `phpcompatibility/phpcompatibility-paragonie` (LGPL-2.1-or-later)
- `phpcompatibility/phpcompatibility-wp` (LGPL-2.1-or-later)

These packages support PHPCS PHPCompatibility rules. They are not bundled with the distributable plugin and do not affect the GPL-2.0-or-later runtime license of Magazine73.

## Scope control

- Work on only the phase explicitly requested.
- Do not implement future features without approval.
- Do not modify:
  - WordPress core;
  - themes;
  - unrelated plugins;
  - Docker infrastructure;
  - database configuration.
- Do not refactor unrelated working code.

## Quality

Before finishing a task:

1. List modified and created files.
2. Summarize the implementation.
3. Explain how to test it.
4. Run PHP syntax validation when possible.
5. Run PHPCS, PHPStan and tests when they are available.
6. Report failures honestly.
7. Do not continue to the next phase automatically.

## Git workflow

- `main`: stable releases.
- `develop`: active development.
- Branch prefixes:
  - `feature/`
  - `fix/`
  - `docs/`
  - `chore/`
- Use semantic versioning.
- Maintain `CHANGELOG.md`.
- Do not commit generated secrets, credentials or local environment files.

## MVP constraints

The initial MVP will support:

- Magazine administration.
- WebP pages only.
- Automatic ascending natural sort by filename.
- First page used as cover.
- Media upload and WordPress Media Library selection.
- Shortcode rendering.
- Public magazine URL.
- StPageFlip viewer.
- Two-page desktop view and one-page mobile view.
- Fullscreen.
- Optional PDF download.
- Configurable controls.
- Global settings with per-magazine overrides.
- Progressive page loading.
- Local reading progress without personal data.
- No analytics.
- No categories or tags.
- No automatic PDF-to-image conversion.

## Cursor Cloud specific instructions

Scope of this cloud environment (confirmed by the project owner):

- Use it only to edit code, run lightweight static checks, commit, and open pull requests.
- Do NOT install or run Docker, WordPress, MariaDB, or any system service here.
- Functional/runtime WordPress testing is done manually by the project owner in a local
  Docker environment (`docker-compose.yml` at the repo root serves WordPress at
  `http://localhost:8082`, with the plugin mounted from `plugin/magazine73`). That stack is
  not meant to run inside the cloud VM.

Lightweight static checks:

- PHP syntax validation is the primary check: `php -l <file>` on the PHP files you change
  (e.g. `plugin/magazine73/magazine73.php`).
- PHPCS and PHPStan are configured through Composer (`composer install`, then
  `vendor/bin/phpcs` and `vendor/bin/phpstan`).

Dependencies / build:

- There is currently no dependency manifest (no `composer.json`, no `package.json`), so
  there is nothing to install to work on the current code. Vite/npm assets are planned per
  `docs/ai/mvp-specification.md`; when a `package.json` is added, `npm install` will apply.
