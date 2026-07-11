# Frontend build tooling

Magazine73 uses Vite for modular JavaScript and CSS builds. Node.js is required only during development and CI; installed WordPress sites do not need Node.js.

## Commands

```bash
npm install
npm run dev
npm run build
npm run validate
```

Production assets are written to `plugin/magazine73/assets/dist/` and can be enqueued through `Magazine73\Assets`.

## Browserslist

The build targets:

```text
> 0.5%, last 2 versions, not dead
```

## Development dependencies

| Package | Version | License | Source |
|---------|---------|---------|--------|
| Vite | ^6.3.5 | MIT | https://github.com/vitejs/vite |

Vite and its npm dependencies are excluded from production ZIP artifacts through `.distignore`. No npm package is required at runtime on WordPress sites.
