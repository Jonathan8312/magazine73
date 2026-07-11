/**
 * Magazine admin bootstrap module.
 */

/**
 * Initialize Magazine73 admin enhancements.
 */
export function initAdmin() {
	const editor = document.querySelector( '[data-magazine73-admin]' );

	if ( ! editor ) {
		return;
	}

	editor.classList.add( 'magazine73-admin--ready' );
}
