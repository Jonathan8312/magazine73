/**
 * Viewer settings panel interactions.
 */

/**
 * Initialize the viewer settings metabox toggle.
 */
export function initViewerSettingsPanel() {
	const panel = document.querySelector( '[data-magazine73-viewer-settings]' );

	if ( ! panel ) {
		return;
	}

	const toggle = panel.querySelector( '#magazine73-use-global-settings' );
	const overrides = panel.querySelector( '#magazine73-viewer-settings-overrides' );

	if ( ! toggle || ! overrides ) {
		return;
	}

	const syncVisibility = () => {
		overrides.hidden = toggle.checked;
	};

	toggle.addEventListener( 'change', syncVisibility );
	syncVisibility();
}
