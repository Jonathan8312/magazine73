# Magazine73 MVP Specification

## Product

Magazine73 is a WordPress plugin for creating, managing and publishing digital magazines with a page-flip viewer.

## Compatibility

- WordPress 6.6 or newer.
- Tested against the latest stable WordPress release.
- PHP 8.0 or newer.
- Source and fallback language: English.
- Initial Spanish translation included.
- Text domain: `magazine73`.

## Magazine administration

Each magazine must support:

- Title.
- Optional edition field.
- Optional description.
- WebP pages only.
- Optional downloadable PDF.
- Draft and published states.
- Public URL.
- Generated shortcode.

New magazines must start as drafts.

A magazine cannot be published without at least one page.

## Pages

- Pages can be uploaded directly or selected from the WordPress Media Library.
- Only WebP images are accepted.
- Pages are sorted automatically by filename in ascending natural order.
- The first page is also used as the cover.
- Pages with a different aspect ratio are fitted without cropping.
- If needed, margins may be displayed.
- If the magazine has an odd number of pages, a visual blank page is added at the end.
- Files above 300 KB generate a warning but are not rejected.
- The editor displays:
  - page count;
  - total weight;
  - average weight per page.

## Administration list

The magazine list must display:

- Cover thumbnail.
- Title.
- Edition.
- Shortcode.
- Publication date.

If the edition is empty, display an em dash.

Magazines are ordered from newest to oldest.

## Roles and capabilities

- Administrators and editors can create and edit magazines.
- Editors can edit any magazine.
- Only administrators can delete magazines.
- Only authorized users can access plugin settings.

## Viewer

The viewer must use StPageFlip 2.0.7.

Default behavior:

- Two-page view on desktop.
- One-page view on mobile.
- Responsive width.
- Automatic height based on the first page ratio.
- Neutral and minimal visual design.
- CSS encapsulated from the active theme.
- Typography inherited from the active theme.

Controls:

- Previous page.
- Next page.
- Page counter.
- Fullscreen.
- Optional PDF download.
- Zoom in.
- Zoom out.
- Reset zoom.
- Collapsible thumbnail sidebar.

Navigation:

- Keyboard arrows.
- Home.
- End.
- Escape exits fullscreen.
- Swipe gestures on mobile and tablet.
- Accessible buttons and visible focus states.
- Translatable ARIA labels and status messages.

## Loading

Use progressive page loading:

- Load the cover and nearby initial pages first.
- Open the viewer when the initial set is ready.
- Preload remaining pages in the background.
- Prioritize pages near the current page.
- Display loading progress where possible.

## Reading progress

Use browser `localStorage`.

Store only:

- Magazine ID.
- Content hash.
- Last page read.

The content hash must be based on page IDs and order.

When the reader returns, ask whether to:

- continue from the last page;
- start from the cover.

If `localStorage` is unavailable, the viewer must continue working without saved progress.

## Settings

Provide:

`Magazines → Settings`

Global settings include:

- Viewer colors.
- Visible controls.
- Fullscreen.
- Download.
- Zoom.
- Thumbnails.
- Viewer behavior.

Each magazine includes:

- “Use global settings” enabled by default.
- Per-magazine overrides when disabled.

Default appearance must be neutral.

## Shortcode

Primary shortcode:

`[magazine73 id="123"]`

Supported optional attributes:

- `width`
- `height`
- `controls`
- `fullscreen`
- `download`
- `thumbnails`
- `theme`

Invalid values must be ignored and fall back to magazine or global settings.

## Public URL

Each published magazine has a public URL:

`/revistas/{magazine-slug}/`

The public page displays:

- Title.
- Edition when available.
- Optional description.
- Centered viewer.

## Templates

Reusable templates live in:

`/templates`

Themes may override templates through:

`wp-content/themes/{active-theme}/magazine73/`

CSS and JavaScript cannot be overridden from the theme.

## Assets

- Vite.
- Modular ES JavaScript.
- Modular CSS.
- SVG icons owned by the plugin.
- No external CDN.
- Frontend assets load only when a viewer is rendered.
- Browser target:

  `> 0.5%, last 2 versions, not dead`

## Internationalization

- English source strings.
- English fallback.
- Spanish translation included.
- PHP strings use WordPress translation functions.
- JavaScript translations use `wp_set_script_translations()`.

## Security

- Nonces.
- Capability checks.
- Input sanitization.
- Output escaping.
- No external telemetry.
- No external service dependency.

## Data removal

Provide an option:

“Delete plugin data on uninstall”

Disabled by default.

When enabled, uninstall removes:

- Magazines.
- Magazine metadata.
- Plugin settings.

It must not remove:

- WebP media files.
- PDF media files.
- Other Media Library items.

## Out of scope for MVP

- Categories.
- Tags.
- Analytics.
- Magazine duplication.
- Automatic PDF-to-image conversion.
- External APIs.
- Telemetry.
- Automatic cloud storage.
