<?php
/**
 * Plugin Name: Magazine73
 * Plugin URI: https://73software.com/magazine73
 * Description: Create, manage, and publish digital magazines with a page-flip viewer.
 * Version: 0.1.2
 * Requires at least: 6.6
 * Requires PHP: 8.0
 * Author: Jonathan Torres
 * Author URI: https://73software.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: magazine73
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'MAGAZINE73_VERSION' ) ) {
	define( 'MAGAZINE73_VERSION', '0.1.2' );
}

if ( ! defined( 'MAGAZINE73_FILE' ) ) {
	define( 'MAGAZINE73_FILE', __FILE__ );
}

if ( ! defined( 'MAGAZINE73_PATH' ) ) {
	define( 'MAGAZINE73_PATH', plugin_dir_path( MAGAZINE73_FILE ) );
}

if ( ! defined( 'MAGAZINE73_URL' ) ) {
	define( 'MAGAZINE73_URL', plugin_dir_url( MAGAZINE73_FILE ) );
}

if ( ! defined( 'MAGAZINE73_BASENAME' ) ) {
	define( 'MAGAZINE73_BASENAME', plugin_basename( MAGAZINE73_FILE ) );
}

require_once MAGAZINE73_PATH . 'includes/class-post-type.php';
require_once MAGAZINE73_PATH . 'includes/class-capabilities.php';
require_once MAGAZINE73_PATH . 'includes/class-magazine-meta.php';
require_once MAGAZINE73_PATH . 'includes/class-magazine-pages.php';
require_once MAGAZINE73_PATH . 'includes/class-magazine-pdf.php';
require_once MAGAZINE73_PATH . 'includes/class-viewer-settings.php';
require_once MAGAZINE73_PATH . 'includes/class-template-loader.php';
require_once MAGAZINE73_PATH . 'includes/class-magazine-renderer.php';
require_once MAGAZINE73_PATH . 'includes/class-shortcode.php';
require_once MAGAZINE73_PATH . 'includes/class-public-magazine.php';
require_once MAGAZINE73_PATH . 'includes/class-admin-metabox.php';
require_once MAGAZINE73_PATH . 'includes/class-admin-pages-panel.php';
require_once MAGAZINE73_PATH . 'includes/class-admin-viewer-settings-metabox.php';
require_once MAGAZINE73_PATH . 'includes/class-admin-settings-page.php';
require_once MAGAZINE73_PATH . 'includes/class-admin-assets.php';
require_once MAGAZINE73_PATH . 'includes/class-admin-list-table.php';
require_once MAGAZINE73_PATH . 'includes/class-data-lifecycle.php';
require_once MAGAZINE73_PATH . 'includes/class-uninstaller.php';
require_once MAGAZINE73_PATH . 'includes/class-plugin.php';
require_once MAGAZINE73_PATH . 'includes/class-assets.php';

register_activation_hook( MAGAZINE73_FILE, array( Magazine73\Capabilities::class, 'activate' ) );
register_deactivation_hook( MAGAZINE73_FILE, array( Magazine73\Capabilities::class, 'deactivate' ) );

( new Magazine73\Plugin() )->init();
