<?php
/**
 * Magazine admin asset loading.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues admin assets for magazine screens.
 */
final class Admin_Assets {

	/**
	 * Register admin asset hooks.
	 */
	public function init(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueue admin assets on magazine edit screens.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function enqueue( string $hook_suffix ): void {
		$settings_hook = Post_Type::POST_TYPE . '_page_' . Admin_Settings_Page::PAGE_SLUG;

		if ( $settings_hook === $hook_suffix ) {
			Assets::enqueue_admin();
			return;
		}

		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen || Post_Type::POST_TYPE !== $screen->post_type ) {
			return;
		}

		wp_enqueue_media();
		Assets::enqueue_admin();
	}
}
