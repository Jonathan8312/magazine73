# Frontend build tooling

Magazine73 uses Vite for modular JavaScript and CSS builds. Node.js is required only during development and CI; installed WordPress sites do not need Node.js.

## Commands

```bash
npm install
npm run dev
npm run build
npm run validate
```

Production assets are written to `plugin/magazine73/assets/dist/` and are enqueued through `Magazine73\Assets`.

## WordPress loading strategy

Magazine73 supports WordPress 6.6 as its minimum version.

Viewer and admin entry scripts use the WordPress Script Modules API through `wp_enqueue_script_module()`.

`Magazine73\Assets` enqueues only Vite entry JavaScript modules. Generated chunks are loaded by native relative ES module imports inside the compiled entry files. Manifest `imports` are traversed recursively only to collect and deduplicate CSS dependencies.

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
