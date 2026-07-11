/**
 * Magazine admin bootstrap module.
 */

import { markReady } from '../shared/helpers.js';

/**
 * Initialize Magazine73 admin enhancements.
 */
export function initAdmin() {
	const editor = document.querySelector( '[data-magazine73-admin]' );

	if ( ! editor ) {
		return;
	}

	markReady( editor, 'magazine73-admin--ready' );
}
