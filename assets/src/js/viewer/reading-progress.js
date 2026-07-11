/**
 * Local reading progress storage without personal data.
 */

const STORAGE_KEY = 'magazine73_reading_progress';

/**
 * Determine whether localStorage is available.
 */
export function isStorageAvailable() {
	try {
		const testKey = '__magazine73_storage_test__';
		window.localStorage.setItem( testKey, '1' );
		window.localStorage.removeItem( testKey );
		return true;
	} catch {
		return false;
	}
}

/**
 * Read stored progress for a magazine.
 *
 * @param {number} magazineId Magazine ID.
 * @param {string} contentHash Content hash.
 */
export function getSavedPage( magazineId, contentHash ) {
	if ( ! isStorageAvailable() || ! contentHash ) {
		return null;
	}

	try {
		const raw = window.localStorage.getItem( STORAGE_KEY );

		if ( ! raw ) {
			return null;
		}

		const data = JSON.parse( raw );
		const entry = data?.[ String( magazineId ) ];

		if ( ! entry || entry.hash !== contentHash || 'number' !== typeof entry.page ) {
			return null;
		}

		return entry.page;
	} catch {
		return null;
	}
}

/**
 * Persist the current page for a magazine.
 *
 * @param {number} magazineId Magazine ID.
 * @param {string} contentHash Content hash.
 * @param {number} pageIndex Last page index.
 */
export function savePage( magazineId, contentHash, pageIndex ) {
	if ( ! isStorageAvailable() || ! contentHash ) {
		return;
	}

	try {
		const raw = window.localStorage.getItem( STORAGE_KEY );
		const data = raw ? JSON.parse( raw ) : {};

		if ( 'object' !== typeof data || null === data ) {
			return;
		}

		data[ String( magazineId ) ] = {
			hash: contentHash,
			page: pageIndex,
		};

		window.localStorage.setItem( STORAGE_KEY, JSON.stringify( data ) );
	} catch {
		// Continue without saved progress when storage is blocked.
	}
}

/**
 * Ask the reader whether to resume or restart.
 *
 * @param {HTMLElement} viewerElement Viewer root element.
 * @param {number} savedPage Saved page index.
 */
export function promptResumeChoice( viewerElement, savedPage ) {
	const dialog = viewerElement.querySelector( '[data-magazine73-resume]' );

	if ( ! ( dialog instanceof HTMLElement ) ) {
		return Promise.resolve( 0 );
	}

	const message = dialog.querySelector( '[data-magazine73-resume-message]' );

	if ( message instanceof HTMLElement ) {
		const template = message.getAttribute( 'data-template' ) || 'Continue from page %d or start from the cover?';
		message.textContent = template.replace( '%d', String( savedPage + 1 ) );
	}

	dialog.removeAttribute( 'hidden' );

	return new Promise( ( resolve ) => {
		const handleChoice = ( event ) => {
			const target = event.target;

			if ( ! ( target instanceof HTMLElement ) ) {
				return;
			}

			const action = target.getAttribute( 'data-magazine73-resume-action' );

			if ( ! action ) {
				return;
			}

			dialog.setAttribute( 'hidden', 'hidden' );
			dialog.removeEventListener( 'click', handleChoice );
			resolve( 'continue' === action ? savedPage : 0 );
		};

		dialog.addEventListener( 'click', handleChoice );
	} );
}

/**
 * Resolve the page index to open.
 *
 * @param {HTMLElement} viewerElement Viewer root element.
 * @param {object} config Viewer configuration.
 */
export async function resolveStartPage( viewerElement, config ) {
	const savedPage = getSavedPage( config.magazineId, config.contentHash );

	if ( null === savedPage || savedPage <= 0 ) {
		return 0;
	}

	if ( savedPage >= config.pages.length ) {
		return 0;
	}

	return promptResumeChoice( viewerElement, savedPage );
}
