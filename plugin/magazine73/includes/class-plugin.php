<?php
/**
 * Main plugin bootstrap class.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Boots Magazine73 and registers core hooks.
 */
final class Plugin {

	/**
	 * Register plugin hooks.
	 */
	public function init(): void {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		( new Post_Type() )->init();
		( new Magazine_Meta() )->init();
		( new Magazine_Pages() )->init();
		( new Magazine_Pdf() )->init();
		( new Viewer_Settings() )->init();
		( new Data_Lifecycle() )->init();
		( new Shortcode() )->init();
		( new Public_Magazine() )->init();
		( new Admin_Metabox() )->init();
		( new Admin_Pages_Panel() )->init();
		( new Admin_Viewer_Settings_Metabox() )->init();
		( new Admin_Settings_Page() )->init();
		( new Admin_Assets() )->init();
		( new Admin_List_Table() )->init();
	}

	/**
	 * Load the plugin text domain for translations.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'magazine73',
			false,
			dirname( MAGAZINE73_BASENAME ) . '/languages'
		);
	}
}
