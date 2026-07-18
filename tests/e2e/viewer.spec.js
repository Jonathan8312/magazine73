import { test, expect } from '@playwright/test';
import fs from 'node:fs';
import path from 'node:path';

const VIEWER_FIXTURE = '/tests/fixtures/viewer/index.html';
const PRODUCTION_VIEWER_BUNDLE = '/plugin/magazine73/assets/dist/js/magazine73-viewer.js';
const ROOT = process.cwd();

/**
 * Resolve the built viewer stylesheet from the Vite manifest.
 *
 * @return {string} Absolute URL path for the viewer CSS.
 */
function getViewerStylesheetPath() {
	const manifest = JSON.parse(
		fs.readFileSync( path.join( ROOT, 'plugin/magazine73/assets/dist/manifest.json' ), 'utf8' )
	);
	const cssFile = manifest[ 'assets/src/entries/viewer.js' ]?.css?.[0];

	if ( ! cssFile ) {
		throw new Error( 'Viewer CSS missing from Vite manifest. Run npm run build.' );
	}

	return `/plugin/magazine73/assets/dist/${ cssFile }`;
}

/**
 * Open the viewer fixture with production JS + CSS.
 *
 * @param {import('@playwright/test').Page} page Playwright page.
 */
async function openViewerFixture( page ) {
	await page.goto( VIEWER_FIXTURE );
	await page.addStyleTag( { url: getViewerStylesheetPath() } );
}

async function waitForViewerReady( page ) {
	await expect( page.locator( '[data-magazine73-viewer]' ) ).toHaveClass( /magazine73-viewer--ready/, { timeout: 15000 } );
}

test.describe( 'Magazine73 viewer fixture', () => {
	test( 'loads the production Vite viewer bundle', async ( { page } ) => {
		const responses = [];
		page.on( 'response', ( response ) => {
			if ( response.url().includes( 'magazine73-viewer.js' ) ) {
				responses.push( response.url() );
			}
		} );

		await openViewerFixture( page );
		await waitForViewerReady( page );

		expect( responses.some( ( url ) => url.endsWith( PRODUCTION_VIEWER_BUNDLE ) ) ).toBeTruthy();
	} );

	test( 'initializes the viewer shell and controls', async ( { page } ) => {
		await openViewerFixture( page );

		const viewer = page.locator( '[data-magazine73-viewer]' );
		await expect( viewer ).toBeVisible();
		await waitForViewerReady( page );

		await expect( page.locator( '[data-magazine73-loading]' ) ).toHaveAttribute( 'hidden', '' );
		await expect( page.locator( '[data-magazine73-page-status]' ) ).toContainText( '1 /' );
		await expect( page.locator( '[data-magazine73-action="next"]' ) ).toBeEnabled();
		await expect( page.locator( '[aria-label="Download PDF"]' ) ).toBeVisible();
	} );

	test( 'supports keyboard navigation between pages', async ( { page } ) => {
		await openViewerFixture( page );
		await waitForViewerReady( page );

		await page.locator( '[data-magazine73-viewer]' ).focus();
		await page.keyboard.press( 'ArrowRight' );

		await expect( page.locator( '[data-magazine73-page-status]' ) ).not.toContainText( '1 /' );
	} );

	test( 'toggles thumbnails and applies zoom controls', async ( { page } ) => {
		await openViewerFixture( page );
		await waitForViewerReady( page );

		const thumbnails = page.locator( '[data-magazine73-thumbnails]' );
		await expect( thumbnails ).toHaveAttribute( 'hidden', '' );

		await page.locator( '[data-magazine73-action="thumbnails"]' ).click();
		await expect( thumbnails ).not.toHaveAttribute( 'hidden', '' );

		const zoomLayer = page.locator( '[data-magazine73-zoom]' );
		await page.locator( '[data-magazine73-action="zoom-in"]' ).click();

		const transform = await zoomLayer.evaluate( ( element ) => element.style.transform );
		expect( transform ).toContain( 'scale(1.25)' );
	} );

	test( 'exposes fullscreen control after initialization', async ( { page } ) => {
		await openViewerFixture( page );
		await waitForViewerReady( page );

		const fullscreenButton = page.locator( '[data-magazine73-action="fullscreen"]' );
		await expect( fullscreenButton ).toBeVisible();
		await expect( fullscreenButton ).toHaveAttribute( 'aria-label', 'Enter fullscreen' );
	} );

	test( 'prompts to resume when saved progress exists', async ( { page } ) => {
		await page.addInitScript( () => {
			window.localStorage.setItem(
				'magazine73_reading_progress',
				JSON.stringify( {
					1: {
						hash: 'fixture',
						page: 2,
					},
				} )
			);
		} );

		await openViewerFixture( page );

		const resumeDialog = page.locator( '[data-magazine73-resume]' );
		await expect( resumeDialog ).toBeVisible( { timeout: 15000 } );
		await expect( page.locator( '[data-magazine73-resume-message]' ) ).toContainText( 'Continue from page 3' );

		await page.locator( '[data-magazine73-resume-action="continue"]' ).click();
		await waitForViewerReady( page );
		await expect( resumeDialog ).toBeHidden();
	} );

	test( 'uses a landscape two-page book box on desktop', async ( { page } ) => {
		await page.setViewportSize( { width: 1280, height: 900 } );
		await openViewerFixture( page );
		await waitForViewerReady( page );

		const metrics = await page.evaluate( () => {
			const book = document.querySelector( '.magazine73-viewer__book' );
			const shell = document.querySelector( '.magazine73-viewer__canvas' );
			const main = document.querySelector( '.magazine73-viewer__main' );
			const bookRect = book.getBoundingClientRect();
			const shellRect = shell.getBoundingClientRect();
			const mainRect = main.getBoundingClientRect();

			return {
				bookWidth: bookRect.width,
				bookHeight: bookRect.height,
				shellWidth: shellRect.width,
				mainWidth: mainRect.width,
			};
		} );

		expect( metrics.bookWidth ).toBeGreaterThan( metrics.bookHeight );
		expect( Math.abs( metrics.shellWidth - metrics.bookWidth ) ).toBeLessThan( 4 );
		expect( metrics.shellWidth ).toBeLessThan( metrics.mainWidth - 4 );

		await page.locator( '[data-magazine73-action="next"]' ).click();
		await expect( page.locator( '[data-magazine73-page-status]' ) ).not.toContainText( '1 /' );

		const afterFlip = await page.evaluate( () => {
			const book = document.querySelector( '.magazine73-viewer__book' ).getBoundingClientRect();
			return { width: book.width, height: book.height };
		} );

		expect( afterFlip.width ).toBeGreaterThan( afterFlip.height );
	} );

	test( 'uses a portrait single-page book box on mobile', async ( { page } ) => {
		await page.setViewportSize( { width: 390, height: 844 } );
		await openViewerFixture( page );
		await waitForViewerReady( page );

		const metrics = await page.evaluate( () => {
			const book = document.querySelector( '.magazine73-viewer__book' ).getBoundingClientRect();
			return { width: book.width, height: book.height };
		} );

		expect( metrics.height ).toBeGreaterThan( metrics.width );
	} );
} );
