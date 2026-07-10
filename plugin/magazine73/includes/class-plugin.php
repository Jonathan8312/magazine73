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
