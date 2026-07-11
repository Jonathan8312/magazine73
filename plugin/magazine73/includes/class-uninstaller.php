<?php
/**
 * Magazine73 uninstall cleanup.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Removes plugin-owned database data when configured to do so.
 */
final class Uninstaller {

	/**
	 * Run uninstall cleanup for the current site.
	 *
	 * Multisite note: WordPress runs uninstall.php per site when the plugin is
	 * deleted from that site. Network-wide cleanup is out of scope for the MVP.
	 */
	public static function run(): void {
		if ( ! Data_Lifecycle::should_delete_on_uninstall() ) {
			return;
		}

		self::delete_magazine_posts();
		self::delete_plugin_options();
	}

	/**
	 * Delete all magazine posts and their metadata without touching attachments.
	 */
	private static function delete_magazine_posts(): void {
		$post_ids = get_posts(
			array(
				'post_type'      => Post_Type::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		if ( ! is_array( $post_ids ) ) {
			return;
		}

		foreach ( $post_ids as $post_id ) {
			if ( is_numeric( $post_id ) ) {
				wp_delete_post( (int) $post_id, true );
			}
		}
	}

	/**
	 * Delete plugin options from the options table.
	 */
	private static function delete_plugin_options(): void {
		delete_option( Viewer_Settings::OPTION_KEY );
		delete_option( Data_Lifecycle::DATA_VERSION_OPTION );
		delete_option( Data_Lifecycle::DELETE_ON_UNINSTALL_OPTION );
	}
}
