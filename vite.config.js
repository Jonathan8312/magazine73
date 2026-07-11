import { defineConfig } from 'vite';
import { resolve } from 'node:path';

const rootDir = resolve( import.meta.dirname );
const outDir = resolve( rootDir, 'plugin/magazine73/assets/dist' );

export default defineConfig( {
	build: {
		manifest: 'manifest.json',
		outDir,
		emptyOutDir: true,
		rollupOptions: {
			external: [ '@wordpress/i18n' ],
			input: {
				'magazine73-viewer': resolve( rootDir, 'assets/src/entries/viewer.js' ),
				'magazine73-editor': resolve( rootDir, 'assets/src/entries/admin.js' ),
			},
			output: {
				entryFileNames: 'js/[name].js',
				chunkFileNames: 'js/[name]-[hash].js',
				assetFileNames: 'css/[name]-[hash][extname]',
			},
		},
	},
} );
