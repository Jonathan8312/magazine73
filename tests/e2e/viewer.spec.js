import { test, expect } from '@playwright/test';

test.describe( 'Magazine73 viewer fixture', () => {
	test( 'initializes the viewer shell and controls', async ( { page } ) => {
		await page.goto( '/tests/fixtures/viewer/index.html' );

		const viewer = page.locator( '[data-magazine73-viewer]' );
		await expect( viewer ).toBeVisible();
		await expect( viewer ).toHaveClass( /magazine73-viewer--ready/, { timeout: 15000 } );

		await expect( page.locator( '[data-magazine73-page-status]' ) ).toContainText( '1 /' );
		await expect( page.locator( '[data-magazine73-action="next"]' ) ).toBeEnabled();
	} );

	test( 'supports keyboard navigation between pages', async ( { page } ) => {
		await page.goto( '/tests/fixtures/viewer/index.html' );
		await expect( page.locator( '[data-magazine73-viewer]' ) ).toHaveClass( /magazine73-viewer--ready/, { timeout: 15000 } );

		await page.locator( '[data-magazine73-viewer]' ).focus();
		await page.keyboard.press( 'ArrowRight' );

		await expect( page.locator( '[data-magazine73-page-status]' ) ).not.toContainText( '1 /' );
	} );
} );
