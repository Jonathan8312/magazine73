/**
 * Magazine admin bootstrap module.
 */

import { markReady } from '../shared/helpers.js';
import { initPagesPanel } from './pages-panel.js';

/**
 * Initialize Magazine73 admin enhancements.
 */
export function initAdmin() {
	document.querySelectorAll( '[data-magazine73-admin]' ).forEach( ( element ) => {
		markReady( element, 'magazine73-admin--ready' );
	} );

	initPagesPanel();
}
