/**
 * Viewer bootstrap module.
 */

import { markReady } from '../shared/helpers.js';
import { bindViewerControls } from './controls.js';
import { createPageFlipViewer, createPageLoader } from './stpageflip-viewer.js';
import { resolveStartPage, savePage } from './reading-progress.js';

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
 * Update the loading progress UI.
 *
 * @param {HTMLElement} viewerElement Viewer root element.
 * @param {object} progress Progress stats.
 */
function updateLoadingProgress( viewerElement, progress ) {
	const progressElement = viewerElement.querySelector( '[data-magazine73-loading-progress]' );
	const labelElement = viewerElement.querySelector( '[data-magazine73-loading-label]' );

	if ( progressElement instanceof HTMLProgressElement ) {
		const percent = progress.total > 0 ? Math.round( ( progress.loaded / progress.total ) * 100 ) : 0;
		progressElement.value = percent;
		progressElement.max = 100;
	}

	if ( labelElement instanceof HTMLElement ) {
		const template = labelElement.getAttribute( 'data-template' ) || 'Loading pages %1$d of %2$d';
		labelElement.textContent = template
			.replace( '%1$d', String( progress.loaded ) )
			.replace( '%2$d', String( progress.total ) );
	}
}

/**
 * Show or hide the loading overlay.
 *
 * @param {HTMLElement} viewerElement Viewer root element.
 * @param {boolean} isVisible Whether the overlay is visible.
 */
function setLoadingVisible( viewerElement, isVisible ) {
	const loadingElement = viewerElement.querySelector( '[data-magazine73-loading]' );

	if ( loadingElement instanceof HTMLElement ) {
		loadingElement.toggleAttribute( 'hidden', ! isVisible );
	}
}

/**
 * Initialize a single viewer instance.
 *
 * @param {HTMLElement} viewerElement Viewer root element.
 */
async function initSingleViewer( viewerElement ) {
	const config = parseViewerConfig( viewerElement );

	if ( ! config || ! Array.isArray( config.pages ) || 0 === config.pages.length ) {
		return;
	}

	const pageLoader = createPageLoader( config );

	setLoadingVisible( viewerElement, true );
	updateLoadingProgress( viewerElement, pageLoader.getProgress() );

	pageLoader.onProgress = ( progress ) => {
		updateLoadingProgress( viewerElement, progress );
	};

	await pageLoader.preloadIndices( pageLoader.getInitialIndices() );

	const startPage = await resolveStartPage( viewerElement, config );
	const pageFlip = createPageFlipViewer( viewerElement, config, pageLoader, startPage );

	if ( ! pageFlip ) {
		setLoadingVisible( viewerElement, false );
		return;
	}

	bindViewerControls( viewerElement, pageFlip, config, pageLoader, ( pageIndex ) => {
		savePage( config.magazineId, config.contentHash, pageIndex );
	} );

	pageFlip.on( 'init', () => {
		setLoadingVisible( viewerElement, false );
		pageLoader.startBackgroundPreload( pageFlip.getCurrentPageIndex() );
		savePage( config.magazineId, config.contentHash, pageFlip.getCurrentPageIndex() );
		markReady( viewerElement, 'magazine73-viewer--ready' );
	} );

	pageFlip.on( 'flip', () => {
		const currentPage = pageFlip.getCurrentPageIndex();
		pageLoader.setCurrentIndex( currentPage );
		savePage( config.magazineId, config.contentHash, currentPage );
	} );
}

/**
 * Initialize Magazine73 viewers on the current page.
 */
export function initViewer() {
	const viewers = document.querySelectorAll( '[data-magazine73-viewer]' );

	viewers.forEach( ( viewerElement ) => {
		if ( viewerElement instanceof HTMLElement ) {
			void initSingleViewer( viewerElement );
		}
	} );
}
