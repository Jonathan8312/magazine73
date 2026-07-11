import { test, expect } from '@playwright/test';

test.describe( 'Magazine73 admin fixture', () => {
	test( 'marks admin panels as ready', async ( { page } ) => {
		await page.goto( '/tests/fixtures/admin/index.html' );

		await expect( page.locator( '.magazine73-pages-panel' ) ).toHaveClass( /magazine73-admin--ready/ );
		await expect( page.locator( '.magazine73-pdf-field' ) ).toHaveClass( /magazine73-admin--ready/ );
	} );

	test( 'exposes page and PDF admin controls', async ( { page } ) => {
		await page.goto( '/tests/fixtures/admin/index.html' );

		await expect( page.locator( '#magazine73-add-pages' ) ).toBeVisible();
		await expect( page.locator( '[data-magazine73-pdf-select]' ) ).toBeVisible();
	} );
} );
