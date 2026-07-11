/**
 * Magazine pages panel interactions.
 */

const LARGE_PAGE_BYTES = 307200;

/**
 * Initialize the magazine pages panel.
 */
export function initPagesPanel() {
	const panel = document.querySelector( '.magazine73-pages-panel[data-magazine73-admin]' );

	if ( ! panel || 'undefined' === typeof window.wp?.media ) {
		return;
	}

	const list = panel.querySelector( '#magazine73-pages-list' );
	const addButton = panel.querySelector( '#magazine73-add-pages' );

	if ( ! list || ! addButton ) {
		return;
	}

	const frame = window.wp.media( {
		title: 'Select WebP Pages',
		button: {
			text: 'Use selected pages',
		},
		library: {
			type: 'image/webp',
		},
		multiple: true,
	} );

	addButton.addEventListener( 'click', () => {
		frame.open();
	} );

	frame.on( 'select', () => {
		const selection = frame.state().get( 'selection' );
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

	item.innerHTML = `
		${ thumbUrl ? `<img class="magazine73-pages-panel__thumb" src="${ thumbUrl }" alt="" />` : '' }
		<span class="magazine73-pages-panel__filename"></span>
		${ isLarge ? '<span class="magazine73-pages-panel__warning">Larger than 300 KB</span>' : '' }
		<button type="button" class="button-link-delete magazine73-pages-panel__remove">Remove</button>
		<input type="hidden" name="magazine73_page_ids[]" value="${ id }" />
	`;

	const filenameNode = item.querySelector( '.magazine73-pages-panel__filename' );

	if ( filenameNode ) {
		filenameNode.textContent = filename;
	}

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

	summaries[0].textContent = `Pages: ${ count }`;
	summaries[1].textContent = `Total weight: ${ formatBytes( totalBytes ) }`;
	summaries[2].textContent = `Average weight per page: ${ formatBytes( count > 0 ? Math.round( totalBytes / count ) : 0 ) }`;
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
