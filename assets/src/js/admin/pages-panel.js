/**
 * Magazine pages panel interactions.
 */

import { __, sprintf } from '@wordpress/i18n';

const LARGE_PAGE_BYTES = 307200;
const INIT_FLAG = 'magazine73PagesInitialized';

/** @type {object | null} */
let mediaFrame = null;

/**
 * Initialize the magazine pages panel.
 *
 * @return {boolean} Whether initialization succeeded.
 */
export function initPagesPanel() {
	const panel = document.querySelector( '.magazine73-pages-panel[data-magazine73-admin]' );

	if ( ! panel || panel.dataset[ INIT_FLAG ] === '1' ) {
		return Boolean( panel && panel.dataset[ INIT_FLAG ] === '1' );
	}

	if ( 'undefined' === typeof window.wp?.media ) {
		return false;
	}

	const list = panel.querySelector( '#magazine73-pages-list' );
	const addButton = panel.querySelector( '#magazine73-add-pages' );

	if ( ! list || ! addButton ) {
		return false;
	}

	mediaFrame = window.wp.media( {
		title: __( 'Select WebP Pages', 'magazine73' ),
		button: {
			text: __( 'Use selected pages', 'magazine73' ),
		},
		library: {
			type: 'image',
		},
		multiple: true,
	} );

	mediaFrame.on( 'select', () => {
		const selection = mediaFrame.state().get( 'selection' );
		const existingIds = new Set(
			Array.from( list.querySelectorAll( '[data-attachment-id]' ) ).map( ( item ) =>
				item.getAttribute( 'data-attachment-id' )
			)
		);

		selection.each( ( attachment ) => {
			const mime = attachment.get( 'mime' ) || attachment.get( 'subtype' );

			if ( 'image/webp' !== mime && 'webp' !== mime ) {
				return;
			}

			const id = String( attachment.get( 'id' ) );

			if ( existingIds.has( id ) ) {
				return;
			}

			list.appendChild( createPageItem( attachment ) );
			existingIds.add( id );
		} );

		updateSummary( panel, list );
	} );

	list.addEventListener( 'click', ( event ) => {
		const target = event.target;

		if ( ! ( target instanceof HTMLElement ) || ! target.classList.contains( 'magazine73-pages-panel__remove' ) ) {
			return;
		}

		const item = target.closest( '.magazine73-pages-panel__item' );

		if ( item ) {
			item.remove();
			updateSummary( panel, list );
		}
	} );

	panel.dataset[ INIT_FLAG ] = '1';
	return true;
}

/**
 * Open the media library for magazine pages.
 */
export function openPagesMediaLibrary() {
	if ( ! initPagesPanel() || ! mediaFrame ) {
		return;
	}

	mediaFrame.open();
}

/**
 * Create a page list item element.
 *
 * @param {object} attachment Media attachment model.
 */
function createPageItem( attachment ) {
	const id = String( attachment.get( 'id' ) );
	const filename = attachment.get( 'filename' ) || attachment.get( 'title' ) || '';
	const thumbUrl = attachment.get( 'sizes' )?.thumbnail?.url || attachment.get( 'url' ) || '';
	const bytes = Number( attachment.get( 'filesizeInBytes' ) || attachment.get( 'filesize' ) || 0 );
	const isLarge = bytes > LARGE_PAGE_BYTES;
	const item = document.createElement( 'li' );

	item.className = `magazine73-pages-panel__item${ isLarge ? ' magazine73-pages-panel__item--large' : '' }`;
	item.dataset.attachmentId = id;

	if ( thumbUrl ) {
		const thumb = document.createElement( 'img' );
		thumb.className = 'magazine73-pages-panel__thumb';
		thumb.src = thumbUrl;
		thumb.alt = '';
		item.appendChild( thumb );
	}

	const filenameNode = document.createElement( 'span' );
	filenameNode.className = 'magazine73-pages-panel__filename';
	filenameNode.textContent = filename;
	item.appendChild( filenameNode );

	if ( isLarge ) {
		const warning = document.createElement( 'span' );
		warning.className = 'magazine73-pages-panel__warning';
		warning.textContent = __( 'Larger than 300 KB', 'magazine73' );
		item.appendChild( warning );
	}

	const removeButton = document.createElement( 'button' );
	removeButton.type = 'button';
	removeButton.className = 'button-link-delete magazine73-pages-panel__remove';
	removeButton.textContent = __( 'Remove', 'magazine73' );
	item.appendChild( removeButton );

	const hiddenInput = document.createElement( 'input' );
	hiddenInput.type = 'hidden';
	hiddenInput.name = 'magazine73_page_ids[]';
	hiddenInput.value = id;
	item.appendChild( hiddenInput );

	return item;
}

/**
 * Update summary counts in the panel.
 *
 * @param {Element} panel Panel element.
 * @param {Element} list  List element.
 */
function updateSummary( panel, list ) {
	const summaries = panel.querySelectorAll( '.magazine73-admin-panel__summary' );

	if ( summaries.length < 3 ) {
		return;
	}

	const items = Array.from( list.querySelectorAll( '.magazine73-pages-panel__item' ) );
	const totalBytes = items.reduce( ( total, item ) => {
		const warning = item.classList.contains( 'magazine73-pages-panel__item--large' );
		return total + ( warning ? LARGE_PAGE_BYTES + 1 : 0 );
	}, 0 );
	const count = items.length;

	summaries[0].textContent = sprintf( __( 'Pages: %d', 'magazine73' ), count );
	summaries[1].textContent = sprintf( __( 'Total weight: %s', 'magazine73' ), formatBytes( totalBytes ) );
	summaries[2].textContent = sprintf(
		__( 'Average weight per page: %s', 'magazine73' ),
		formatBytes( count > 0 ? Math.round( totalBytes / count ) : 0 )
	);
}

/**
 * Format bytes for display.
 *
 * @param {number} bytes Byte count.
 */
function formatBytes( bytes ) {
	if ( bytes < 1024 ) {
		return `${ bytes } B`;
	}

	if ( bytes < 1048576 ) {
		return `${ Math.round( bytes / 1024 ) } KB`;
	}

	return `${ ( bytes / 1048576 ).toFixed( 1 ) } MB`;
}
