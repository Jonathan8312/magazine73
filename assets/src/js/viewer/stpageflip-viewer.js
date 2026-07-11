/**
 * StPageFlip viewer integration.
 */

import { PageFlip } from '../../../../plugin/magazine73/third-party/stpageflip/dist/js/page-flip.module.js';
import '../../../../plugin/magazine73/third-party/stpageflip/src/Style/stPageFlip.css';
import { PageLoader } from './page-loader.js';

/**
 * @typedef {object} ViewerConfig
 * @property {number} magazineId Magazine ID.
 * @property {string} contentHash Content hash.
 * @property {import('./page-loader.js').ViewerPage[]} pages Viewer pages.
 */

/**
 * Resolve base page dimensions from the cover page.
 *
 * @param {import('./page-loader.js').ViewerPage[]} pages Viewer pages.
 */
function getBaseDimensions( pages ) {
	const cover = pages[0];
	const width = cover?.width > 0 ? cover.width : 800;
	const height = cover?.height > 0 ? cover.height : Math.round( width * ( 4 / 3 ) );

	return { width, height };
}

/**
 * Initialize StPageFlip inside a viewer element.
 *
 * @param {HTMLElement} viewerElement Viewer root element.
 * @param {ViewerConfig} config Viewer configuration.
 * @param {PageLoader} pageLoader Progressive page loader.
 * @param {number} startPage Initial page index.
 */
export function createPageFlipViewer( viewerElement, config, pageLoader, startPage = 0 ) {
	const bookElement = viewerElement.querySelector( '.magazine73-viewer__book' );

	if ( ! ( bookElement instanceof HTMLElement ) || ! Array.isArray( config.pages ) || 0 === config.pages.length ) {
		return null;
	}

	const { width, height } = getBaseDimensions( config.pages );
	let updateTimeout = null;

	const scheduleImageUpdate = ( pageFlip ) => {
		if ( updateTimeout ) {
			window.clearTimeout( updateTimeout );
		}

		updateTimeout = window.setTimeout( () => {
			if ( 'function' === typeof pageFlip.updateFromImages ) {
				pageFlip.updateFromImages( pageLoader.getResolvedUrls() );
			}
		}, 120 );
	};

	const pageFlip = new PageFlip( bookElement, {
		width,
		height,
		size: 'stretch',
		minWidth: 280,
		maxWidth: 1400,
		minHeight: 200,
		maxHeight: 1600,
		drawShadow: true,
		flippingTime: 700,
		usePortrait: true,
		autoSize: true,
		maxShadowOpacity: 0.45,
		showCover: true,
		mobileScrollSupport: true,
		swipeDistance: 30,
		useMouseEvents: true,
	} );

	pageLoader.onPageLoaded = () => {
		scheduleImageUpdate( pageFlip );
	};

	pageFlip.on( 'init', () => {
		if ( startPage > 0 && startPage < pageFlip.getPageCount() ) {
			pageFlip.turnToPage( startPage );
		}
	} );

	pageFlip.loadFromImages( pageLoader.getResolvedUrls() );

	return pageFlip;
}

/**
 * Create a page loader for a viewer configuration.
 *
 * @param {ViewerConfig} config Viewer configuration.
 */
export function createPageLoader( config ) {
	return new PageLoader( config.pages );
}
