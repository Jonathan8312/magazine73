/**
 * Viewer bootstrap module.
 */

import { markReady } from '../shared/helpers.js';
import { bindViewerNavigation, createPageFlipViewer } from './stpageflip-viewer.js';

/**
 * Parse viewer configuration from a data attribute.
 *
 * @param {HTMLElement} viewerElement Viewer root element.
 */
function parseViewerConfig( viewerElement ) {
	const configValue = viewerElement.getAttribute( 'data-magazine73-config' );

	if ( ! configValue ) {
		return null;
	}

	try {
		return JSON.parse( configValue );
	} catch {
		return null;
	}
}

/**
 * Initialize Magazine73 viewers on the current page.
 */
export function initViewer() {
	const viewers = document.querySelectorAll( '[data-magazine73-viewer]' );

	if ( 0 === viewers.length ) {
		return;
	}

	viewers.forEach( ( viewerElement ) => {
		if ( ! ( viewerElement instanceof HTMLElement ) ) {
			return;
		}

		const config = parseViewerConfig( viewerElement );

		if ( ! config ) {
			return;
		}

		const pageFlip = createPageFlipViewer( viewerElement, config );

		if ( ! pageFlip ) {
			return;
		}

		bindViewerNavigation( viewerElement, pageFlip );
		markReady( viewerElement, 'magazine73-viewer--ready' );
	} );
}
