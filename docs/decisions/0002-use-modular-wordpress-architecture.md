# ADR 0002: Use a modular WordPress plugin architecture

## Status

Accepted

## Context

Magazine73 will include administrative screens, magazine data, settings, shortcodes, public templates, assets and viewer integration.

Keeping all logic inside the main plugin file would make the project difficult to maintain, test and review.

The plugin must remain compatible with WordPress.org distribution and must work without requiring Composer at runtime.

## Decision

Magazine73 will use a modular architecture based on PHP classes separated by responsibility.

Classes will use the root namespace:

`Magazine73`

The main plugin file will remain minimal and will load required classes explicitly with `require_once`.

Reusable frontend templates will be stored in:

`/templates`

Themes will be allowed to override templates through:

`wp-content/themes/{active-theme}/magazine73/`

CSS and JavaScript will remain controlled by the plugin.

## Consequences

### Positive

- Easier maintenance and testing.
- Lower risk of naming collisions.
- Clear separation of responsibilities.
- Compatible with WordPress.org.
- No runtime dependency on Composer.
- Easier for contributors and AI assistants to understand.

### Negative

- Requires more files than a single-file plugin.
- Explicit class loading must be maintained.
- The project structure must remain consistent as features are added.
