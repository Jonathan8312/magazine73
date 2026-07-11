# ADR 0004: Load admin Vite entries as ES modules on WordPress 6.5

## Status

Accepted

## Context

Magazine73 declares WordPress 6.5 as its minimum supported version.

Vite builds ES module entry files that import hashed chunks through relative URLs. WordPress 6.5 introduced Script Modules on the frontend through `wp_enqueue_script_module()`, but admin Script Module support was added in WordPress 6.6.

The plugin must therefore load admin assets on WordPress 6.5 without relying on unavailable admin Script Module hooks.

## Decision

- Load viewer entry scripts with `wp_enqueue_script_module()`.
- Load admin entry scripts with `wp_enqueue_script()` and convert only Magazine73 admin handles to `type="module"` through a scoped `script_loader_tag` filter.
- Enqueue only Vite entry JavaScript files.
- Traverse manifest imports recursively only to collect CSS dependencies.
- Do not register imported Vite chunks as separate WordPress module IDs.

## Consequences

### Positive

- Preserves WordPress 6.5 compatibility for admin screens.
- Avoids duplicate script URLs caused by registering Vite chunks separately.
- Keeps frontend viewer integration aligned with the WordPress Script Modules API.

### Negative

- Admin and viewer use different loading mechanisms until the minimum WordPress version can be raised to 6.6 or newer.
- The admin loader depends on a scoped script tag filter that must remain limited to Magazine73 handles.
