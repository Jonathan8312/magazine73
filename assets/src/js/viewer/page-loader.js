/**
 * Progressive page image loading for the magazine viewer.
 */

const INITIAL_PAGE_COUNT = 4;
const NEARBY_PRIORITY_RADIUS = 4;

/**
 * Validate an image source before DOM assignment.
 *
 * @param {string} url Candidate image URL.
 */
function isAllowedImageSource( url ) {
	if ( 'string' !== typeof url || '' === url ) {
		return false;
	}

	if ( url.startsWith( 'data:image/' ) ) {
		return true;
	}

	try {
		const parsed = new URL( url, window.location.origin );

		return 'http:' === parsed.protocol || 'https:' === parsed.protocol;
	} catch {
		return false;
	}
}

/**
 * @typedef {object} ViewerPage
 * @property {string} url Page image URL.
 * @property {number} width Page width.
 * @property {number} height Page height.
 * @property {boolean} [blank] Whether the page is a generated blank page.
 */

/**
 * Create a blank page image for odd page counts.
 *
 * @param {number} width Page width.
 * @param {number} height Page height.
 */
export function createBlankPageDataUrl( width, height ) {
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
 * Create a neutral placeholder while a page image is loading.
 *
 * @param {number} width Page width.
 * @param {number} height Page height.
 */
function createLoadingPlaceholderDataUrl( width, height ) {
	const canvas = document.createElement( 'canvas' );
	const pageWidth = width > 0 ? width : 3;
	const pageHeight = height > 0 ? height : 4;

	canvas.width = pageWidth;
	canvas.height = pageHeight;

	const context = canvas.getContext( '2d' );

	if ( context ) {
		context.fillStyle = '#efefef';
		context.fillRect( 0, 0, pageWidth, pageHeight );
		context.strokeStyle = '#d0d0d0';
		context.strokeRect( 0.5, 0.5, pageWidth - 1, pageHeight - 1 );
	}

	return canvas.toDataURL( 'image/png' );
}

/**
 * Manage progressive page image loading.
 */
export class PageLoader {
	/**
	 * @param {ViewerPage[]} pages Magazine pages.
	 */
	constructor( pages ) {
		this.pages = pages;
		this.resolvedUrls = new Array( pages.length ).fill( '' );
		this.states = new Array( pages.length ).fill( 'pending' );
		this.inFlight = new Map();
		this.currentIndex = 0;
		this.backgroundActive = false;
		this.onPageLoaded = null;
		this.onProgress = null;

		pages.forEach( ( page, index ) => {
			if ( page.blank || '' === page.url ) {
				this.resolvedUrls[ index ] = createBlankPageDataUrl( page.width, page.height );
				this.states[ index ] = 'loaded';
			}
		} );
	}

	/**
	 * Get indices for the initial visible page group.
	 */
	getInitialIndices() {
		const count = Math.min( INITIAL_PAGE_COUNT, this.pages.length );
		return Array.from( { length: count }, ( _, index ) => index );
	}

	/**
	 * Preload a set of page indices.
	 *
	 * @param {number[]} indices Page indices.
	 */
	async preloadIndices( indices ) {
		await Promise.all( indices.map( ( index ) => this.preloadIndex( index ) ) );
	}

	/**
	 * Resolve URLs for StPageFlip, using placeholders when needed.
	 */
	getResolvedUrls() {
		return this.pages.map( ( page, index ) => {
			if ( this.resolvedUrls[ index ] ) {
				return this.resolvedUrls[ index ];
			}

			return createLoadingPlaceholderDataUrl( page.width, page.height );
		} );
	}

	/**
	 * Get loading progress stats.
	 */
	getProgress() {
		const loaded = this.states.filter( ( state ) => 'loaded' === state ).length;
		const failed = this.states.filter( ( state ) => 'failed' === state ).length;

		return {
			loaded,
			failed,
			total: this.pages.length,
		};
	}

	/**
	 * Begin background preloading with priority near the current page.
	 *
	 * @param {number} currentIndex Current page index.
	 */
	startBackgroundPreload( currentIndex ) {
		this.currentIndex = currentIndex;
		this.backgroundActive = true;
		this.queueBackgroundLoads();
	}

	/**
	 * Reprioritize background loading after page changes.
	 *
	 * @param {number} currentIndex Current page index.
	 */
	setCurrentIndex( currentIndex ) {
		this.currentIndex = currentIndex;

		if ( this.backgroundActive ) {
			this.queueBackgroundLoads();
		}
	}

	/**
	 * Preload a single page index.
	 *
	 * @param {number} index Page index.
	 */
	preloadIndex( index ) {
		const page = this.pages[ index ];

		if ( ! page || page.blank || '' === page.url ) {
			return Promise.resolve();
		}

		if ( 'loaded' === this.states[ index ] || 'failed' === this.states[ index ] ) {
			return Promise.resolve();
		}

		if ( this.inFlight.has( index ) ) {
			return this.inFlight.get( index );
		}

		const promise = new Promise( ( resolve ) => {
			this.states[ index ] = 'loading';
			const image = new Image();

			const finish = ( success ) => {
				this.inFlight.delete( index );

				if ( success ) {
					this.states[ index ] = 'loaded';
					this.resolvedUrls[ index ] = page.url;

					if ( 'function' === typeof this.onPageLoaded ) {
						this.onPageLoaded( index );
					}
				} else {
					this.states[ index ] = 'failed';
				}

				if ( 'function' === typeof this.onProgress ) {
					this.onProgress( this.getProgress() );
				}

				resolve();
			};

			image.decoding = 'async';

			image.addEventListener( 'load', () => finish( true ) );
			image.addEventListener( 'error', () => finish( false ) );

			if ( ! isAllowedImageSource( page.url ) ) {
				finish( false );
				return;
			}

			image.setAttribute( 'src', page.url );

			if ( image.complete ) {
				finish( true );
			}
		} );

		this.inFlight.set( index, promise );
		return promise;
	}

	/**
	 * Queue pending pages ordered by distance from the current page.
	 */
	queueBackgroundLoads() {
		const pending = this.pages
			.map( ( page, index ) => ( { index, page } ) )
			.filter( ( entry ) => 'pending' === this.states[ entry.index ] && ! entry.page.blank && '' !== entry.page.url )
			.sort( ( left, right ) => {
				const leftDistance = Math.abs( left.index - this.currentIndex );
				const rightDistance = Math.abs( right.index - this.currentIndex );

				if ( leftDistance === rightDistance ) {
					return left.index - right.index;
				}

				return leftDistance - rightDistance;
			} );

		pending.slice( 0, NEARBY_PRIORITY_RADIUS * 2 + 1 ).forEach( ( entry ) => {
			this.preloadIndex( entry.index );
		} );

		pending.slice( NEARBY_PRIORITY_RADIUS * 2 + 1 ).forEach( ( entry ) => {
			window.setTimeout( () => {
				if ( 'pending' === this.states[ entry.index ] ) {
					this.preloadIndex( entry.index );
				}
			}, 0 );
		} );
	}
}
