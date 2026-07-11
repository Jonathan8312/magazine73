<?php
/**
 * Public magazine page rendering.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Handles public magazine URLs and single templates.
 */
final class Public_Magazine {

	/**
	 * Register public rendering hooks.
	 */
	public function init(): void {
		add_filter( 'single_template', array( $this, 'filter_single_template' ) );
		add_action( 'template_redirect', array( $this, 'block_unpublished_magazines' ) );
	}

	/**
	 * Use the plugin single magazine template when available.
	 *
	 * @param string $template Current template path.
	 */
	public function filter_single_template( string $template ): string {
		if ( ! is_singular( Post_Type::POST_TYPE ) ) {
			return $template;
		}

		$plugin_template = Template_Loader::locate_template( 'single-magazine.php' );

		return '' !== $plugin_template ? $plugin_template : $template;
	}

	/**
	 * Prevent unpublished magazines from being publicly rendered.
	 */
	public function block_unpublished_magazines(): void {
		if ( ! is_singular( Post_Type::POST_TYPE ) ) {
			return;
		}

		$post = get_queried_object();

		if ( ! $post instanceof \WP_Post || 'publish' !== $post->post_status ) {
			global $wp_query;

			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
		}
	}
}
