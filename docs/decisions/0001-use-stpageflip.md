# ADR 0001: Use StPageFlip as the magazine viewer library

## Status

Accepted

## Context

Magazine73 needs a page-flip viewer for digital magazines.

The library must:

- support image-based pages;
- work on desktop, tablet and mobile;
- support one-page and two-page layouts;
- allow commercial distribution;
- avoid mandatory external services;
- be suitable for inclusion in a WordPress plugin.

## Decision

Magazine73 will use StPageFlip version 2.0.7.

The library will be included locally inside the plugin and will not be loaded from a CDN.

## License

StPageFlip is distributed under the MIT License.

Magazine73 will include:

- the original license file;
- the project name;
- the version used;
- the original source;
- a third-party notice.

## Consequences

### Positive

- Compatible with commercial distribution.
- Lightweight and focused on page-flip interactions.
- Supports mobile and desktop layouts.
- No external runtime dependency.
- Already proven in the original magazine viewer.

### Negative

- Magazine73 must keep its own integration layer.
- Library updates must be reviewed manually.
- License and notices must remain included in every distributed version.
