/**
 * Viewer bootstrap module.
 */

import { markReady } from '../shared/helpers.js';

/**
 * Initialize the Magazine73 viewer shell.
 *
 * The full StPageFlip integration is added in a later issue.
 */
export function initViewer() {
	const viewers = document.querySelectorAll( '[data-magazine73-viewer]' );

	if ( viewers.length === 0 ) {
		return;
	}

	viewers.forEach( ( viewer ) => {
		markReady( viewer, 'magazine73-viewer--ready' );
	} );
}
