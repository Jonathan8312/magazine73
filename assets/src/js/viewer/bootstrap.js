/**
 * Viewer bootstrap module.
 */

/**
 * Initialize the Magazine73 viewer shell.
 *
 * The full StPageFlip integration is added in a later issue.
 */
export function initViewer() {
	const viewers = document.querySelectorAll( '[data-magazine73-viewer]' );

	if ( viewers.length === 0 ) {
		return;
	}

	viewers.forEach( ( viewer ) => {
		viewer.classList.add( 'magazine73-viewer--ready' );
	} );
}
