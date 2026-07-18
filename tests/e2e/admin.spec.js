import { test, expect } from '@playwright/test';

test.describe( 'Magazine73 admin fixture', () => {
	test( 'marks admin panels as ready', async ( { page } ) => {
		await page.goto( '/tests/fixtures/admin/index.html' );

		await expect( page.locator( '.magazine73-pages-panel' ) ).toHaveClass( /magazine73-admin--ready/ );
		await expect( page.locator( '.magazine73-pdf-field' ) ).toHaveClass( /magazine73-admin--ready/ );
	} );

	test( 'opens the media library when adding pages', async ( { page } ) => {
		await page.goto( '/tests/fixtures/admin/index.html' );

		await expect( page.locator( '.magazine73-pages-panel' ) ).toHaveClass( /magazine73-admin--ready/ );

		await page.evaluate( () => {
			window.__magazine73MediaOpened = false;
		} );

		await page.locator( '#magazine73-add-pages' ).click();
		await expect.poll( () => page.evaluate( () => window.__magazine73MediaOpened ) ).toBe( true );
	} );
} );
