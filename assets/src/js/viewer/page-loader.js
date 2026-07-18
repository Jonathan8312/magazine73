/**
 * Progressive page image loading for the magazine viewer.
 */

const INITIAL_PAGE_COUNT = 4;
const NEARBY_PRIORITY_RADIUS = 4;
const MAX_CONCURRENT_LOADS = 3;
const PLACEHOLDER_WIDTH = 3;
const PLACEHOLDER_HEIGHT = 4;

/**
 * Validate an image source before DOM assignment.
 *
 * @param {string} url Candidate image URL.
 * @return {string|null} Sanitized URL or null when invalid.
 */
export function sanitizeImageUrl( url ) {
	if ( 'string' !== typeof url || '' === url ) {
		return null;
	}

	if ( url.startsWith( 'data:image/' ) ) {
		return url;
	}

	try {
		const parsed = new URL( url, window.location.origin );

		if ( 'http:' === parsed.protocol || 'https:' === parsed.protocol ) {
			return parsed.href;
		}
	} catch {
		return null;
	}

	return null;
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
	const pageWidth = width > 0 ? width : PLACEHOLDER_WIDTH;
	const pageHeight = height > 0 ? height : PLACEHOLDER_HEIGHT;

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
 * Shared tiny placeholder (avoid per-page full-resolution canvases).
 *
 * @return {string} Data URL.
 */
function getSharedLoadingPlaceholder() {
	if ( getSharedLoadingPlaceholder.cache ) {
		return getSharedLoadingPlaceholder.cache;
	}

	const canvas = document.createElement( 'canvas' );
	canvas.width = PLACEHOLDER_WIDTH;
	canvas.height = PLACEHOLDER_HEIGHT;

	const context = canvas.getContext( '2d' );

	if ( context ) {
		context.fillStyle = '#efefef';
		context.fillRect( 0, 0, PLACEHOLDER_WIDTH, PLACEHOLDER_HEIGHT );
		context.strokeStyle = '#d0d0d0';
		context.strokeRect( 0.5, 0.5, PLACEHOLDER_WIDTH - 1, PLACEHOLDER_HEIGHT - 1 );
	}

	getSharedLoadingPlaceholder.cache = canvas.toDataURL( 'image/png' );
	return getSharedLoadingPlaceholder.cache;
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
		this.queue = [];
		this.activeLoads = 0;
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
	 * Get indices for the initial visible page group around a start page.
	 *
	 * @param {number} [startIndex=0] Page index to center on.
	 * @return {number[]}
	 */
	getPriorityIndices( startIndex = 0 ) {
		const center = Math.max( 0, Math.min( startIndex, this.pages.length - 1 ) );
		const indices = new Set();

		for ( let offset = 0; offset <= NEARBY_PRIORITY_RADIUS; offset++ ) {
			const before = center - offset;
			const after = center + offset;

			if ( before >= 0 ) {
				indices.add( before );
			}

			if ( after < this.pages.length ) {
				indices.add( after );
			}

			if ( indices.size >= INITIAL_PAGE_COUNT && offset >= 1 ) {
				break;
			}
		}

		// Always warm the cover for restart / first paint.
		indices.add( 0 );
		if ( this.pages.length > 1 ) {
			indices.add( 1 );
		}

		return Array.from( indices ).sort( ( left, right ) => left - right );
	}

	/**
	 * Get indices for the initial visible page group.
	 */
	getInitialIndices() {
		return this.getPriorityIndices( 0 );
	}

	/**
	 * Preload a set of page indices (awaits completion).
	 *
	 * @param {number[]} indices Page indices.
	 */
	async preloadIndices( indices ) {
		await Promise.all( indices.map( ( index ) => this.preloadIndex( index ) ) );
	}

	/**
	 * Resolve URLs for StPageFlip, using a shared tiny placeholder when needed.
	 */
	getResolvedUrls() {
		const placeholder = getSharedLoadingPlaceholder();

		return this.pages.map( ( page, index ) => {
			if ( this.resolvedUrls[ index ] ) {
				return this.resolvedUrls[ index ];
			}

			return placeholder;
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
			pending: this.states.filter( ( state ) => 'pending' === state || 'loading' === state ).length,
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

		this.states[ index ] = 'loading';
		this.activeLoads += 1;

		const promise = new Promise( ( resolve ) => {
			const image = new Image();

			const finish = ( success ) => {
				this.inFlight.delete( index );
				this.activeLoads = Math.max( 0, this.activeLoads - 1 );

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
				this.pumpQueue();
			};

			image.decoding = 'async';

			image.addEventListener( 'load', () => {
				if ( 'function' === typeof image.decode ) {
					image.decode().then( () => finish( true ) ).catch( () => finish( true ) );
					return;
				}

				finish( true );
			} );
			image.addEventListener( 'error', () => finish( false ) );

			const safeUrl = sanitizeImageUrl( page.url );

			if ( ! safeUrl ) {
				finish( false );
				return;
			}

			image.setAttribute( 'src', safeUrl );

			if ( image.complete && image.naturalWidth > 0 ) {
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
			.filter( ( entry ) => {
				const state = this.states[ entry.index ];
				return ( 'pending' === state ) && ! entry.page.blank && '' !== entry.page.url && ! this.inFlight.has( entry.index );
			} )
			.sort( ( left, right ) => {
				const leftDistance = Math.abs( left.index - this.currentIndex );
				const rightDistance = Math.abs( right.index - this.currentIndex );

				if ( leftDistance === rightDistance ) {
					return left.index - right.index;
				}

				return leftDistance - rightDistance;
			} )
			.map( ( entry ) => entry.index );

		this.queue = pending;
		this.pumpQueue();
	}

	/**
	 * Start queued loads up to the concurrency limit.
	 */
	pumpQueue() {
		while ( this.activeLoads < MAX_CONCURRENT_LOADS && this.queue.length > 0 ) {
			const index = this.queue.shift();

			if ( 'undefined' === typeof index ) {
				break;
			}

			if ( 'pending' !== this.states[ index ] || this.inFlight.has( index ) ) {
				continue;
			}

			this.preloadIndex( index );
		}
	}
}
