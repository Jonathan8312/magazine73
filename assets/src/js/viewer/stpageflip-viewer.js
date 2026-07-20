/**
 * StPageFlip viewer integration.
 */

import { PageFlip } from '../../../../plugin/magazine73/third-party/stpageflip/dist/js/page-flip.module.js';
import '../../../../plugin/magazine73/third-party/stpageflip/src/Style/stPageFlip.css';
import { isDomElement } from '../shared/helpers.js';
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
 * MVP layout: one page on narrow viewports, two-page spread on desktop.
 *
 * @return {boolean} Whether portrait (single-page) mode should be used.
 */
function prefersSinglePageViewport() {
	return window.matchMedia( '(max-width: 781px)' ).matches;
}

/**
 * Available width for sizing the book (main column, not the gray frame).
 *
 * @param {HTMLElement} viewerElement Viewer root element.
 * @return {number}
 */
function getAvailableWidth( viewerElement ) {
	const main = viewerElement.querySelector( '.magazine73-viewer__main' );

	if ( isDomElement( main ) && main.clientWidth > 0 ) {
		return main.clientWidth;
	}

	return viewerElement.clientWidth || window.innerWidth;
}

/**
 * Compute display size so the gray frame hugs the real page aspect ratio.
 *
 * @param {number} availableWidth Parent content width.
 * @param {number} pageWidth Native page width.
 * @param {number} pageHeight Native page height.
 * @param {boolean} usePortrait Single-page mode.
 * @return {{ width: number, height: number }}
 */
function computeBookSize( availableWidth, pageWidth, pageHeight, usePortrait ) {
	const aspect = pageHeight / pageWidth;
	const maxPageWidth = Math.min( pageWidth, 1600 );
	const maxHeight = Math.min( Math.round( window.innerHeight * 0.8 ), 1800 );
	const widthBudget = Math.max( availableWidth, 200 );

	if ( usePortrait ) {
		let width = Math.min( widthBudget, maxPageWidth );
		let height = Math.round( width * aspect );

		if ( height > maxHeight ) {
			height = maxHeight;
			width = Math.round( height / aspect );
		}

		return {
			width: Math.max( Math.round( width ), 200 ),
			height: Math.max( height, 200 ),
		};
	}

	let bookWidth = Math.min( widthBudget, maxPageWidth * 2 );
	let displayPageWidth = bookWidth / 2;
	let bookHeight = Math.round( displayPageWidth * aspect );

	if ( bookHeight > maxHeight ) {
		bookHeight = maxHeight;
		displayPageWidth = bookHeight / aspect;
		bookWidth = displayPageWidth * 2;
	}

	return {
		width: Math.max( Math.round( bookWidth ), 400 ),
		height: Math.max( bookHeight, 200 ),
	};
}

/**
 * Apply explicit book box size (keeps StPageFlip from stretching the gray shell).
 *
 * @param {HTMLElement} bookElement Book host element.
 * @param {{ width: number, height: number }} size Display size.
 */
function applyBookSize( bookElement, size ) {
	bookElement.style.width = `${ size.width }px`;
	bookElement.style.height = `${ size.height }px`;

	const shell = bookElement.closest( '.magazine73-viewer__canvas' );

	if ( isDomElement( shell ) ) {
		shell.style.width = `${ size.width }px`;
	}
}

/**
 * Patch StPageFlip canvas resizing so bitmap pixels match devicePixelRatio.
 * Drawing stays in CSS pixels via setTransform; layout uses offsetWidth.
 *
 * @param {import('../../../../plugin/magazine73/third-party/stpageflip/dist/js/page-flip.module.js').PageFlip} pageFlip PageFlip instance.
 */
function enableCanvasHiDpi( pageFlip ) {
	const ui = pageFlip?.ui;

	if ( ! ui || 'function' !== typeof ui.resizeCanvas || 'function' !== typeof ui.getCanvas ) {
		return;
	}

	if ( ui.__magazine73HiDpi ) {
		return;
	}

	ui.__magazine73HiDpi = true;
	const originalResizeCanvas = ui.resizeCanvas.bind( ui );

	ui.resizeCanvas = () => {
		originalResizeCanvas();

		const canvas = ui.getCanvas();
		const dpr = Math.min( window.devicePixelRatio || 1, 2 );

		if ( ! canvas || dpr <= 1 ) {
			return;
		}

		const cssWidth = canvas.width;
		const cssHeight = canvas.height;

		canvas.width = Math.round( cssWidth * dpr );
		canvas.height = Math.round( cssHeight * dpr );
		canvas.style.width = `${ cssWidth }px`;
		canvas.style.height = `${ cssHeight }px`;
		canvas.getContext( '2d' ).setTransform( dpr, 0, 0, dpr, 0, 0 );
	};

	ui.resizeCanvas();
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

	// Duck-type: Elementor preview can break `instanceof HTMLElement` across DOM realms.
	if ( ! isDomElement( bookElement ) || ! Array.isArray( config.pages ) || 0 === config.pages.length ) {
		return null;
	}

	const { width, height } = getBaseDimensions( config.pages );
	const usePortrait = prefersSinglePageViewport();
	let updateTimeout = null;
	let resizeTimeout = null;

	const syncBookSize = () => {
		const size = computeBookSize( getAvailableWidth( viewerElement ), width, height, usePortrait );
		applyBookSize( bookElement, size );
		return size;
	};

	syncBookSize();

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
		minWidth: usePortrait ? 220 : 200,
		maxWidth: 1600,
		minHeight: 200,
		maxHeight: 1800,
		drawShadow: true,
		flippingTime: 700,
		usePortrait,
		// Explicit book box size controls the gray frame; avoid width:100% + padding-bottom.
		autoSize: false,
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

		window.requestAnimationFrame( () => {
			syncBookSize();
			if ( 'function' === typeof pageFlip.update ) {
				pageFlip.update();
			}
		} );
	} );

	const scheduleResizeUpdate = () => {
		if ( resizeTimeout ) {
			window.clearTimeout( resizeTimeout );
		}

		resizeTimeout = window.setTimeout( () => {
			syncBookSize();
			if ( 'function' === typeof pageFlip.update ) {
				pageFlip.update();
			}
		}, 150 );
	};

	window.addEventListener( 'resize', scheduleResizeUpdate );

	const measureTarget = viewerElement.querySelector( '.magazine73-viewer__main' ) || viewerElement;

	if ( 'function' === typeof ResizeObserver ) {
		const resizeObserver = new ResizeObserver( scheduleResizeUpdate );
		resizeObserver.observe( measureTarget );
	}

	pageFlip.loadFromImages( pageLoader.getResolvedUrls() );
	enableCanvasHiDpi( pageFlip );

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
