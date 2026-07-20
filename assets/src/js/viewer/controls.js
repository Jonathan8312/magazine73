/**
 * Viewer control bar, thumbnails, zoom, and fullscreen interactions.
 */

import prevIcon from '../../icons/prev.svg?raw';
import nextIcon from '../../icons/next.svg?raw';
import zoomInIcon from '../../icons/zoom-in.svg?raw';
import zoomOutIcon from '../../icons/zoom-out.svg?raw';
import zoomResetIcon from '../../icons/zoom-reset.svg?raw';
import fullscreenEnterIcon from '../../icons/fullscreen-enter.svg?raw';
import fullscreenExitIcon from '../../icons/fullscreen-exit.svg?raw';
import thumbnailsIcon from '../../icons/thumbnails.svg?raw';
import downloadIcon from '../../icons/download.svg?raw';
import { sanitizeImageUrl } from './page-loader.js';
import { isDomButton, isDomElement } from '../shared/helpers.js';
import { __ } from '@wordpress/i18n';

const ICONS = {
	prev: prevIcon,
	next: nextIcon,
	'zoom-in': zoomInIcon,
	'zoom-out': zoomOutIcon,
	'zoom-reset': zoomResetIcon,
	'fullscreen-enter': fullscreenEnterIcon,
	'fullscreen-exit': fullscreenExitIcon,
	thumbnails: thumbnailsIcon,
	download: downloadIcon,
};

const ZOOM_MIN = 1;
const ZOOM_MAX = 2;
const ZOOM_STEP = 0.25;

/**
 * Inject SVG icons into control buttons.
 *
 * @param {HTMLElement} viewerElement Viewer root element.
 */
function injectControlIcons( viewerElement ) {
	viewerElement.querySelectorAll( '[data-magazine73-icon]' ).forEach( ( iconElement ) => {
		if ( ! isDomElement( iconElement ) ) {
			return;
		}

		const iconName = iconElement.getAttribute( 'data-magazine73-icon' );

		if ( ! iconName || ! ICONS[ iconName ] ) {
			return;
		}

		iconElement.innerHTML = ICONS[ iconName ];
	} );
}

/**
 * Bind viewer controls to a page flip instance.
 *
 * @param {HTMLElement} viewerElement Viewer root element.
 * @param {import('../../../../plugin/magazine73/third-party/stpageflip/dist/js/page-flip.module.js').PageFlip} pageFlip Page flip instance.
 * @param {object} config Viewer configuration.
 * @param {import('./page-loader.js').PageLoader|null} pageLoader Progressive page loader.
 * @param {(pageIndex: number) => void|null} onPageChange Page change callback.
 */
export function bindViewerControls( viewerElement, pageFlip, config, pageLoader = null, onPageChange = null ) {
	injectControlIcons( viewerElement );

	const previousButton = viewerElement.querySelector( '[data-magazine73-action="prev"]' );
	const nextButton = viewerElement.querySelector( '[data-magazine73-action="next"]' );
	const statusElement = viewerElement.querySelector( '[data-magazine73-page-status]' );
	const zoomInButton = viewerElement.querySelector( '[data-magazine73-action="zoom-in"]' );
	const zoomOutButton = viewerElement.querySelector( '[data-magazine73-action="zoom-out"]' );
	const zoomResetButton = viewerElement.querySelector( '[data-magazine73-action="zoom-reset"]' );
	const fullscreenButton = viewerElement.querySelector( '[data-magazine73-action="fullscreen"]' );
	const thumbnailsToggle = viewerElement.querySelector( '[data-magazine73-action="thumbnails"]' );
	const thumbnailsPanel = viewerElement.querySelector( '[data-magazine73-thumbnails]' );
	const zoomElement = viewerElement.querySelector( '[data-magazine73-zoom]' );
	const fullscreenIcon = isDomButton( fullscreenButton )
		? fullscreenButton.querySelector( '[data-magazine73-icon]' )
		: null;

	let zoomLevel = ZOOM_MIN;

	const applyZoom = () => {
		if ( ! isDomElement( zoomElement ) ) {
			return;
		}

		zoomElement.style.transform = `scale(${ zoomLevel })`;
	};

	const updateStatus = () => {
		if ( isDomElement( statusElement ) ) {
			const currentPage = pageFlip.getCurrentPageIndex() + 1;
			const totalPages = pageFlip.getPageCount();
			statusElement.textContent = `${ currentPage } / ${ totalPages }`;
		}

		if ( 'function' === typeof onPageChange ) {
			onPageChange( pageFlip.getCurrentPageIndex() );
		}

		if ( isDomButton( previousButton ) ) {
			previousButton.disabled = pageFlip.getCurrentPageIndex() <= 0;
		}

		if ( isDomButton( nextButton ) ) {
			nextButton.disabled = pageFlip.getCurrentPageIndex() >= pageFlip.getPageCount() - 1;
		}

		if ( isDomButton( zoomInButton ) ) {
			zoomInButton.disabled = zoomLevel >= ZOOM_MAX;
		}

		if ( isDomButton( zoomOutButton ) ) {
			zoomOutButton.disabled = zoomLevel <= ZOOM_MIN;
		}

		if ( isDomButton( zoomResetButton ) ) {
			zoomResetButton.disabled = zoomLevel <= ZOOM_MIN;
		}
	};

	const flipPrevious = () => {
		pageFlip.flipPrev( 'bottom' );
	};

	const flipNext = () => {
		pageFlip.flipNext( 'bottom' );
	};

	if ( isDomButton( previousButton ) ) {
		previousButton.addEventListener( 'click', flipPrevious );
	}

	if ( isDomButton( nextButton ) ) {
		nextButton.addEventListener( 'click', flipNext );
	}

	if ( isDomButton( zoomInButton ) ) {
		zoomInButton.addEventListener( 'click', () => {
			zoomLevel = Math.min( ZOOM_MAX, zoomLevel + ZOOM_STEP );
			applyZoom();
			updateStatus();
		} );
	}

	if ( isDomButton( zoomOutButton ) ) {
		zoomOutButton.addEventListener( 'click', () => {
			zoomLevel = Math.max( ZOOM_MIN, zoomLevel - ZOOM_STEP );
			applyZoom();
			updateStatus();
		} );
	}

	if ( isDomButton( zoomResetButton ) ) {
		zoomResetButton.addEventListener( 'click', () => {
			zoomLevel = ZOOM_MIN;
			applyZoom();
			updateStatus();
		} );
	}

	if ( isDomButton( fullscreenButton ) ) {
		fullscreenButton.addEventListener( 'click', async () => {
			if ( document.fullscreenElement === viewerElement ) {
				await document.exitFullscreen();
				return;
			}

			await viewerElement.requestFullscreen();
		} );
	}

	document.addEventListener( 'fullscreenchange', () => {
		if ( ! isDomElement( fullscreenIcon ) ) {
			return;
		}

		const isFullscreen = document.fullscreenElement === viewerElement;
		fullscreenIcon.setAttribute( 'data-magazine73-icon', isFullscreen ? 'fullscreen-exit' : 'fullscreen-enter' );
		fullscreenIcon.innerHTML = isFullscreen ? ICONS[ 'fullscreen-exit' ] : ICONS[ 'fullscreen-enter' ];

		if ( isDomButton( fullscreenButton ) ) {
			const enterLabel = fullscreenButton.getAttribute( 'data-enter-label' ) || __( 'Enter fullscreen', 'magazine73' );
			const exitLabel = fullscreenButton.getAttribute( 'data-exit-label' ) || __( 'Exit fullscreen', 'magazine73' );
			fullscreenButton.setAttribute( 'aria-label', isFullscreen ? exitLabel : enterLabel );
		}
	} );

	if ( isDomButton( thumbnailsToggle ) && isDomElement( thumbnailsPanel ) ) {
		thumbnailsToggle.addEventListener( 'click', () => {
			const isHidden = thumbnailsPanel.hasAttribute( 'hidden' );
			thumbnailsPanel.toggleAttribute( 'hidden', ! isHidden );
			thumbnailsToggle.setAttribute( 'aria-expanded', isHidden ? 'true' : 'false' );
		} );
	}

	if ( isDomElement( thumbnailsPanel ) && Array.isArray( config.pages ) ) {
		const list = thumbnailsPanel.querySelector( '[data-magazine73-thumbnails-list]' );

		if ( isDomElement( list ) ) {
			config.pages.forEach( ( page, index ) => {
				if ( page.blank || '' === page.url ) {
					return;
				}

				const item = document.createElement( 'button' );
				item.type = 'button';
				item.className = 'magazine73-thumbnails__item';
				item.dataset.pageIndex = String( index );

				const image = document.createElement( 'img' );
				const thumbnailUrl = sanitizeImageUrl( page.url );

				if ( ! thumbnailUrl ) {
					return;
				}

				image.setAttribute( 'src', thumbnailUrl );
				image.alt = '';
				image.loading = 'lazy';
				image.decoding = 'async';
				item.appendChild( image );

				item.addEventListener( 'click', () => {
					pageFlip.flip( index, 'bottom' );
				} );

				list.appendChild( item );
			} );
		}
	}

	pageFlip.on( 'flip', updateStatus );
	pageFlip.on( 'changeOrientation', updateStatus );
	pageFlip.on( 'init', updateStatus );
	updateStatus();
	applyZoom();

	viewerElement.addEventListener( 'keydown', ( event ) => {
		if ( ! ( event instanceof KeyboardEvent ) ) {
			return;
		}

		if ( 'Escape' === event.key && document.fullscreenElement === viewerElement ) {
			event.preventDefault();
			document.exitFullscreen();
			return;
		}

		if ( 'ArrowLeft' === event.key ) {
			event.preventDefault();
			flipPrevious();
		}

		if ( 'ArrowRight' === event.key ) {
			event.preventDefault();
			flipNext();
		}

		if ( 'Home' === event.key ) {
			event.preventDefault();
			pageFlip.turnToPage( 0 );
		}

		if ( 'End' === event.key ) {
			event.preventDefault();
			pageFlip.turnToPage( pageFlip.getPageCount() - 1 );
		}
	} );
}
