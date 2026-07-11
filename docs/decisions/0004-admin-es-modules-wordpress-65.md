# ADR 0004: Load admin Vite entries as ES modules on WordPress 6.5

Status: Superseded by WordPress 6.6 minimum (PR #21).

## Context

Magazine73 originally declared WordPress 6.5 as its minimum supported version.

Vite builds ES module entry files that import hashed chunks through relative URLs. WordPress 6.5 introduced Script Modules on the frontend through `wp_enqueue_script_module()`, but admin Script Module support was added in WordPress 6.6.

The plugin therefore loaded admin assets on WordPress 6.5 without relying on unavailable admin Script Module hooks.

## Decision

Raise the minimum supported WordPress version to **6.6** and load both frontend and admin Vite entries through `wp_enqueue_script_module()`.

## Consequences

- Simplified asset loading with one script-module path for admin and frontend bundles.
- Removed the WordPress 6.5 admin compatibility branch.
- Plugin header, `readme.txt`, and MVP specification now declare WordPress 6.6 as the minimum supported version.

## Historical note

The original 6.5-specific admin loading approach described below is no longer used:

- Preserved WordPress 6.5 compatibility for admin screens.

That approach was replaced when Magazine73 standardized on WordPress 6.6.
