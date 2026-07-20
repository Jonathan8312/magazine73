# Magazine73

Magazine73 is a WordPress plugin for creating, managing, and publishing digital magazines with a responsive page-flip viewer.

## Status

Magazine73 is currently under active development and is not ready for production use.

## Main features planned for the MVP

- Magazine administration inside WordPress.
- WebP pages selected from the Media Library or uploaded directly.
- Automatic natural sorting by filename.
- First page used as the magazine cover.
- Responsive page-flip viewer powered by StPageFlip.
- Two-page desktop view and one-page mobile view.
- Fullscreen mode.
- Zoom controls.
- Collapsible page thumbnails.
- Optional PDF download.
- Progressive page loading.
- Reading progress stored locally without personal data.
- Shortcode support.
- Optional Elementor widget (soft dependency; Elementor not required).
- Public magazine URLs.
- Global viewer settings with per-magazine overrides.
- English source language and Spanish translation.

## Requirements

- WordPress 6.6 or newer.
- PHP 8.0 or newer.
- Modern browser with JavaScript enabled.

## Third-party components

Runtime third-party libraries are vendored locally inside the plugin. See
`plugin/magazine73/third-party/README.md` for project names, versions, sources,
authors, and licenses.

## Development environment

The local WordPress installation runs with Docker at:

```text
http://localhost:8082
```

## Agent and CI workflow

Repository automation, pull request validation, and cloud-agent workflow guidance are documented in:

- `docs/ai/agent-workflow.md`
- `docs/frontend-build.md`
- `.github/workflows/`
