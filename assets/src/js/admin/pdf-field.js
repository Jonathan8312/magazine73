/**
 * Optional PDF attachment field for magazine administration.
 */

import { __ } from '@wordpress/i18n';

const INIT_FLAG = 'magazine73PdfInitialized';

/** @type {object | null} */
let mediaFrame = null;

/**
 * Initialize the PDF attachment field.
 *
 * @return {boolean} Whether initialization succeeded.
 */
export function initPdfField() {
	const field = document.querySelector( '.magazine73-pdf-field[data-magazine73-admin]' );

	if ( ! field || field.dataset[ INIT_FLAG ] === '1' ) {
		return Boolean( field && field.dataset[ INIT_FLAG ] === '1' );
	}

	if ( 'undefined' === typeof window.wp?.media ) {
		return false;
	}

	const selectButton = field.querySelector( '[data-magazine73-pdf-select]' );
	const removeButton = field.querySelector( '[data-magazine73-pdf-remove]' );
	const input = field.querySelector( '[data-magazine73-pdf-input]' );
	const filenameElement = field.querySelector( '[data-magazine73-pdf-filename]' );

	if ( ! ( selectButton instanceof HTMLButtonElement ) || ! ( input instanceof HTMLInputElement ) ) {
		return false;
	}

	mediaFrame = window.wp.media( {
		title: __( 'Select PDF', 'magazine73' ),
		button: {
			text: __( 'Use this PDF', 'magazine73' ),
		},
		library: {
			type: 'application/pdf',
		},
		multiple: false,
	} );

	const setFilename = ( filename ) => {
		if ( filenameElement instanceof HTMLElement ) {
			filenameElement.textContent = filename;
		}

		if ( removeButton instanceof HTMLButtonElement ) {
			removeButton.hidden = '' === filename;
		}
	};

	mediaFrame.on( 'select', () => {
		const attachment = mediaFrame.state().get( 'selection' ).first();

		if ( ! attachment ) {
			return;
		}

		const mime = attachment.get( 'mime' ) || attachment.get( 'subtype' );

		if ( 'application/pdf' !== mime && 'pdf' !== mime ) {
			return;
		}

		const id = String( attachment.get( 'id' ) || '' );
		const filename = attachment.get( 'filename' ) || attachment.get( 'title' ) || '';

		input.value = id;
		setFilename( filename );
	} );

	if ( removeButton instanceof HTMLButtonElement ) {
		removeButton.addEventListener( 'click', () => {
			input.value = '0';
			setFilename( '' );
		} );
	}

	field.dataset[ INIT_FLAG ] = '1';
	return true;
}

/**
 * Open the media library for the PDF field.
 */
export function openPdfMediaLibrary() {
	if ( ! initPdfField() || ! mediaFrame ) {
		return;
	}

	mediaFrame.open();
}
