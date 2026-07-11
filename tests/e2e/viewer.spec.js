import { test, expect } from '@playwright/test';

const VIEWER_FIXTURE = '/tests/fixtures/viewer/index.html';
const PRODUCTION_VIEWER_BUNDLE = '/plugin/magazine73/assets/dist/js/magazine73-viewer.js';

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

		await page.goto( VIEWER_FIXTURE );
		await waitForViewerReady( page );

		expect( responses.some( ( url ) => url.endsWith( PRODUCTION_VIEWER_BUNDLE ) ) ).toBeTruthy();
	} );

	test( 'initializes the viewer shell and controls', async ( { page } ) => {
		await page.goto( VIEWER_FIXTURE );

		const viewer = page.locator( '[data-magazine73-viewer]' );
		await expect( viewer ).toBeVisible();
		await waitForViewerReady( page );

		await expect( page.locator( '[data-magazine73-loading]' ) ).toHaveAttribute( 'hidden', '' );
		await expect( page.locator( '[data-magazine73-page-status]' ) ).toContainText( '1 /' );
		await expect( page.locator( '[data-magazine73-action="next"]' ) ).toBeEnabled();
		await expect( page.locator( '[aria-label="Download PDF"]' ) ).toBeVisible();
	} );

	test( 'supports keyboard navigation between pages', async ( { page } ) => {
		await page.goto( VIEWER_FIXTURE );
		await waitForViewerReady( page );

		await page.locator( '[data-magazine73-viewer]' ).focus();
		await page.keyboard.press( 'ArrowRight' );

		await expect( page.locator( '[data-magazine73-page-status]' ) ).not.toContainText( '1 /' );
	} );

	test( 'toggles thumbnails and applies zoom controls', async ( { page } ) => {
		await page.goto( VIEWER_FIXTURE );
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
		await page.goto( VIEWER_FIXTURE );
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

		await page.goto( VIEWER_FIXTURE );

		const resumeDialog = page.locator( '[data-magazine73-resume]' );
		await expect( resumeDialog ).toBeVisible( { timeout: 15000 } );
		await expect( page.locator( '[data-magazine73-resume-message]' ) ).toContainText( 'Continue from page 3' );

		await page.locator( '[data-magazine73-resume-action="continue"]' ).click();
		await waitForViewerReady( page );
		await expect( resumeDialog ).toBeHidden();
	} );
} );
