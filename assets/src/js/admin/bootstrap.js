/**
 * Magazine admin bootstrap module.
 */

import { markReady, retryUntilReady, whenDomReady, whenWpMediaReady } from '../shared/helpers.js';
import { initColorFields } from './color-fields.js';
import { initPagesPanel, openPagesMediaLibrary } from './pages-panel.js';
import { initPdfField, openPdfMediaLibrary } from './pdf-field.js';
import { initViewerSettingsPanel } from './viewer-settings-panel.js';

/**
 * Mark visible admin panels as ready.
 */
function markAdminPanelsReady() {
	document.querySelectorAll( '[data-magazine73-admin]' ).forEach( ( element ) => {
		markReady( element, 'magazine73-admin--ready' );
	} );
}

/**
 * Initialize media-dependent admin controls.
 *
 * @return {boolean} Whether all required controls initialized.
 */
function initMediaControls() {
	const pagesReady = initPagesPanel();
	const pdfReady = initPdfField();

	return pagesReady && pdfReady;
}

/**
 * Register delegated click handlers for media buttons.
 */
function registerMediaClickHandlers() {
	document.addEventListener( 'click', ( event ) => {
		const target = event.target;

		if ( ! ( target instanceof HTMLElement ) ) {
			return;
		}

		if ( 'magazine73-add-pages' === target.id ) {
			event.preventDefault();
			openPagesMediaLibrary();
			return;
		}

		if ( target.matches( '[data-magazine73-pdf-select]' ) ) {
			event.preventDefault();
			openPdfMediaLibrary();
		}
	} );
}

/**
 * Initialize Magazine73 admin enhancements.
 */
export function initAdmin() {
	registerMediaClickHandlers();

	whenDomReady( () => {
		markAdminPanelsReady();
		initViewerSettingsPanel();
		initColorFields();

		whenWpMediaReady( () => {
			retryUntilReady( initMediaControls );
		} );
	} );
}
