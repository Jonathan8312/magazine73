# ADR 0004: Load admin Vite entries as ES modules on WordPress 6.5

## Status

Superseded by WordPress 6.6 minimum support

## Context

Magazine73 originally declared WordPress 6.5 as its minimum supported version.

Vite builds ES module entry files that import hashed chunks through relative URLs. WordPress 6.5 introduced Script Modules on the frontend through `wp_enqueue_script_module()`, but admin Script Module support was added in WordPress 6.6.

The plugin therefore loaded admin assets on WordPress 6.5 without relying on unavailable admin Script Module hooks.

## Decision

- Load viewer entry scripts with `wp_enqueue_script_module()`.
- Load admin entry scripts with `wp_enqueue_script()` and convert only Magazine73 admin handles to `type="module"` through a scoped `script_loader_tag` filter.
- Enqueue only Vite entry JavaScript files.
- Traverse manifest imports recursively only to collect CSS dependencies.
- Do not register imported Vite chunks as separate WordPress module IDs.

## Consequences

### Positive

- Preserved WordPress 6.5 compatibility for admin screens.
- Avoided duplicate script URLs caused by registering Vite chunks separately.
- Kept frontend viewer integration aligned with the WordPress Script Modules API.

### Negative

- Admin and viewer used different loading mechanisms until the minimum WordPress version was raised to 6.6.
- The admin loader depended on a scoped script tag filter that had to remain limited to Magazine73 handles.

## Supersession

Magazine73 now requires WordPress 6.6 or newer. Viewer and admin entry scripts both use `wp_enqueue_script_module()` with the same manifest handling rules described in `docs/frontend-build.md`.
