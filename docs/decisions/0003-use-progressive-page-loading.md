# ADR 0003: Use progressive page loading

## Status

Accepted

## Context

A magazine may contain around 100 WebP pages and weigh approximately 10 MB in total.

Preloading every page before opening the viewer would delay the initial reading experience. Loading pages only when requested could also cause visible pauses while navigating.

Magazine73 needs a loading strategy that balances initial speed, smooth navigation and bandwidth usage.

## Decision

Magazine73 will use progressive page loading.

The viewer will:

- load the cover and the first nearby pages first;
- open as soon as the initial group is ready;
- continue preloading the remaining pages in the background;
- prioritize pages near the current reading position;
- display real loading progress when available;
- allow the browser cache to avoid unnecessary repeated downloads.

The viewer must remain functional if background preloading is interrupted.

## Consequences

### Positive

- Faster initial display.
- Smoother reading for large magazines.
- Better perceived performance.
- Avoids blocking the viewer until every page is downloaded.
- Works well with browser caching.

### Negative

- More JavaScript state and loading logic.
- Progress reporting may vary depending on browser cache behavior.
- Network errors must be handled without breaking the viewer.
