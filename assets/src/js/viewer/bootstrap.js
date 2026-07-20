/**
 * Viewer bootstrap module.
 */

import { markReady, whenDomReady, isDomElement } from '../shared/helpers.js';
import { bindViewerControls } from './controls.js';
import { createPageFlipViewer, createPageLoader } from './stpageflip-viewer.js';
import { resolveStartPage, savePage } from './reading-progress.js';
import { __, sprintf } from '@wordpress/i18n';

const INITIALIZED_ATTR = 'data-magazine73-initialized';

/**
 * Parse viewer configuration from a data attribute.
 *
 * @param {Element} viewerElement Viewer root element.
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
 * @param {Element} viewerElement Viewer root element.
 * @param {object} progress Progress stats.
 */
function updateLoadingProgress( viewerElement, progress ) {
	const progressElement = viewerElement.querySelector( '[data-magazine73-loading-progress]' );
	const labelElement = viewerElement.querySelector( '[data-magazine73-loading-label]' );
	const statusElement = viewerElement.querySelector( '[data-magazine73-loading-status]' );

	if ( isDomElement( progressElement ) && 'PROGRESS' === progressElement.tagName ) {
		const percent = progress.total > 0 ? Math.round( ( progress.loaded / progress.total ) * 100 ) : 0;
		/** @type {HTMLProgressElement} */ ( progressElement ).value = percent;
		/** @type {HTMLProgressElement} */ ( progressElement ).max = 100;
	}

	if ( isDomElement( labelElement ) ) {
		const template = labelElement.getAttribute( 'data-template' ) || __( 'Loading pages %1$d of %2$d', 'magazine73' );
		labelElement.textContent = sprintf( template, progress.loaded, progress.total );
	}

	if ( isDomElement( statusElement ) ) {
		const isComplete = progress.loaded + progress.failed >= progress.total;
		statusElement.toggleAttribute( 'hidden', isComplete );

		if ( ! isComplete ) {
			const template = statusElement.getAttribute( 'data-template' ) || __( 'Loading pages %1$d of %2$d…', 'magazine73' );
			statusElement.textContent = sprintf( template, progress.loaded, progress.total );
		}
	}
}

/**
 * Show or hide the blocking loading overlay.
 *
 * @param {Element} viewerElement Viewer root element.
 * @param {boolean} isVisible Whether the overlay is visible.
 */
function setLoadingVisible( viewerElement, isVisible ) {
	const loadingElement = viewerElement.querySelector( '[data-magazine73-loading]' );

	if ( isDomElement( loadingElement ) ) {
		loadingElement.toggleAttribute( 'hidden', ! isVisible );
		loadingElement.setAttribute( 'aria-busy', isVisible ? 'true' : 'false' );
	}
}

/**
 * Initialize a single viewer instance.
 *
 * @param {Element} viewerElement Viewer root element.
 */
async function initSingleViewer( viewerElement ) {
	const config = parseViewerConfig( viewerElement );

	if ( ! config || ! Array.isArray( config.pages ) || 0 === config.pages.length ) {
		viewerElement.removeAttribute( INITIALIZED_ATTR );
		return;
	}

	const pageLoader = createPageLoader( config );

	pageLoader.onProgress = ( progress ) => {
		updateLoadingProgress( viewerElement, progress );
	};

	// Ask resume before heavy work so the dialog stays responsive.
	const startPage = await resolveStartPage( /** @type {HTMLElement} */ ( viewerElement ), config );

	setLoadingVisible( viewerElement, true );
	updateLoadingProgress( viewerElement, pageLoader.getProgress() );

	await pageLoader.preloadIndices( pageLoader.getPriorityIndices( startPage ) );

	const pageFlip = createPageFlipViewer(
		/** @type {HTMLElement} */ ( viewerElement ),
		config,
		pageLoader,
		startPage
	);

	if ( ! pageFlip ) {
		setLoadingVisible( viewerElement, false );
		viewerElement.removeAttribute( INITIALIZED_ATTR );
		return;
	}

	bindViewerControls(
		/** @type {HTMLElement} */ ( viewerElement ),
		pageFlip,
		config,
		pageLoader,
		( pageIndex ) => {
			savePage( config.magazineId, config.contentHash, pageIndex );
		}
	);

	pageFlip.on( 'init', () => {
		setLoadingVisible( viewerElement, false );
		pageLoader.startBackgroundPreload( pageFlip.getCurrentPageIndex() );
		savePage( config.magazineId, config.contentHash, pageFlip.getCurrentPageIndex() );
		markReady( viewerElement, 'magazine73-viewer--ready' );
		updateLoadingProgress( viewerElement, pageLoader.getProgress() );
	} );

	pageFlip.on( 'flip', () => {
		const currentPage = pageFlip.getCurrentPageIndex();
		pageLoader.setCurrentIndex( currentPage );
		savePage( config.magazineId, config.contentHash, currentPage );
	} );
}

/**
 * Initialize Magazine73 viewers under a root node.
 *
 * Safe to call repeatedly (Elementor editor re-renders widgets).
 *
 * @param {ParentNode} [root=document] Scope to search for viewer roots.
 */
export function initViewer( root = document ) {
	if ( ! root || 'function' !== typeof root.querySelectorAll ) {
		return;
	}

	const viewers = root.querySelectorAll( `[data-magazine73-viewer]:not([${ INITIALIZED_ATTR }])` );

	viewers.forEach( ( viewerElement ) => {
		if ( ! isDomElement( viewerElement ) ) {
			return;
		}

		viewerElement.setAttribute( INITIALIZED_ATTR, '1' );
		void initSingleViewer( viewerElement );
	} );
}

/**
 * Watch the DOM for viewers injected after the module first loads
 * (Elementor preview, shortcode widgets, AJAX).
 */
function watchForInjectedViewers() {
	if ( 'undefined' === typeof MutationObserver ) {
		return;
	}

	const root = document.documentElement || document.body;

	if ( ! root ) {
		return;
	}

	let scheduled = 0;

	const schedule = () => {
		if ( scheduled ) {
			return;
		}

		scheduled = window.requestAnimationFrame( () => {
			scheduled = 0;
			initViewer( document );
		} );
	};

	const observer = new MutationObserver( ( mutations ) => {
		for ( const mutation of mutations ) {
			if ( 'childList' !== mutation.type || 0 === mutation.addedNodes.length ) {
				continue;
			}

			for ( const node of mutation.addedNodes ) {
				if ( ! isDomElement( node ) ) {
					continue;
				}

				if (
					( 'function' === typeof node.matches && node.matches( '[data-magazine73-viewer]' ) ) ||
					( 'function' === typeof node.querySelector && node.querySelector( '[data-magazine73-viewer]' ) )
				) {
					schedule();
					return;
				}
			}
		}
	} );

	observer.observe( root, {
		childList: true,
		subtree: true,
	} );
}

/**
 * Re-init viewers when Elementor mounts or refreshes widgets.
 */
function bindElementorFrontend() {
	const register = () => {
		const hooks = window.elementorFrontend?.hooks;

		if ( ! hooks || 'function' !== typeof hooks.addAction ) {
			return false;
		}

		const onElementReady = ( $scope ) => {
			const element = $scope?.[ 0 ] ?? $scope;

			if ( isDomElement( element ) ) {
				initViewer( element );
				return;
			}

			initViewer( document );
		};

		hooks.addAction( 'frontend/element_ready/global', onElementReady );
		hooks.addAction( 'frontend/element_ready/magazine73_viewer.default', onElementReady );
		hooks.addAction( 'frontend/element_ready/shortcode.default', onElementReady );

		initViewer( document );
		return true;
	};

	if ( register() ) {
		return;
	}

	window.addEventListener( 'elementor/frontend/init', () => {
		register();
	} );
}

/**
 * Poll briefly for late Elementor/AJAX injections after early script evaluation.
 */
function pollForLateViewers() {
	let attempts = 0;
	const maxAttempts = 40;

	const timer = window.setInterval( () => {
		attempts += 1;
		initViewer( document );

		if ( attempts >= maxAttempts ) {
			window.clearInterval( timer );
		}
	}, 250 );
}

/**
 * Boot viewers for normal pages and Elementor editor/preview.
 */
export function bootViewer() {
	whenDomReady( () => {
		initViewer( document );
		watchForInjectedViewers();
		bindElementorFrontend();
		pollForLateViewers();
	} );
}
