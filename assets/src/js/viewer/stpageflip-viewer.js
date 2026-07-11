/**
 * StPageFlip viewer integration.
 */

import { PageFlip } from '../../../../plugin/magazine73/third-party/stpageflip/dist/js/page-flip.module.js';
import '../../../../plugin/magazine73/third-party/stpageflip/src/Style/stPageFlip.css';

/**
 * @typedef {object} ViewerPage
 * @property {string} url Page image URL.
 * @property {number} width Page width.
 * @property {number} height Page height.
 * @property {boolean} [blank] Whether the page is a generated blank page.
 */

/**
 * @typedef {object} ViewerConfig
 * @property {number} magazineId Magazine ID.
 * @property {ViewerPage[]} pages Viewer pages.
 */

/**
 * Create a blank page image for odd page counts.
 *
 * @param {number} width Page width.
 * @param {number} height Page height.
 */
function createBlankPageDataUrl( width, height ) {
	const canvas = document.createElement( 'canvas' );
	const pageWidth = width > 0 ? width : 3;
	const pageHeight = height > 0 ? height : 4;

	canvas.width = pageWidth;
	canvas.height = pageHeight;

	const context = canvas.getContext( '2d' );

	if ( context ) {
		context.fillStyle = '#f5f5f5';
		context.fillRect( 0, 0, pageWidth, pageHeight );
		context.strokeStyle = '#d9d9d9';
		context.lineWidth = 1;
		context.strokeRect( 0.5, 0.5, pageWidth - 1, pageHeight - 1 );
	}

	return canvas.toDataURL( 'image/png' );
}

/**
 * Prepare page image URLs for StPageFlip.
 *
 * @param {ViewerPage[]} pages Viewer pages.
 */
function preparePageUrls( pages ) {
	return pages.map( ( page ) => {
		if ( page.blank || '' === page.url ) {
			return createBlankPageDataUrl( page.width, page.height );
		}

		return page.url;
	} );
}

/**
 * Resolve base page dimensions from the cover page.
 *
 * @param {ViewerPage[]} pages Viewer pages.
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
 */
export function createPageFlipViewer( viewerElement, config ) {
	const bookElement = viewerElement.querySelector( '.magazine73-viewer__book' );

	if ( ! ( bookElement instanceof HTMLElement ) || ! Array.isArray( config.pages ) || 0 === config.pages.length ) {
		return null;
	}

	const { width, height } = getBaseDimensions( config.pages );
	const pageUrls = preparePageUrls( config.pages );

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

	pageFlip.on( 'init', () => {
		pageFlip.loadFromImages( pageUrls );
	} );

	return pageFlip;
}
