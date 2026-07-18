<?php
/**
 * Prevent publishing magazines without pages.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Enforces the MVP rule that magazines need at least one page to publish.
 */
final class Publish_Guard {

	/**
	 * Transient key prefix for admin notices.
	 */
	private const NOTICE_TRANSIENT_PREFIX = 'magazine73_publish_blocked_';

	/**
	 * Register publish guard hooks.
	 */
	public function init(): void {
		add_filter( 'wp_insert_post_data', array( $this, 'maybe_block_publish' ), 20, 2 );
		add_action( 'admin_notices', array( $this, 'render_admin_notice' ) );
	}

	/**
	 * Force draft status when publishing without pages.
	 *
	 * @param array<string, mixed> $data    Sanitized post data.
	 * @param array<string, mixed> $postarr Raw post array.
	 * @return array<string, mixed>
	 */
	public function maybe_block_publish( array $data, array $postarr ): array {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $data;
		}

		if ( Post_Type::POST_TYPE !== ( $data['post_type'] ?? '' ) ) {
			return $data;
		}

		$requested_status = isset( $data['post_status'] ) ? (string) $data['post_status'] : '';
		$post_id          = isset( $postarr['ID'] ) ? absint( $postarr['ID'] ) : 0;
		$resolved_status  = self::resolve_status_for_save( $requested_status, $post_id );

		if ( $resolved_status === $requested_status ) {
			return $data;
		}

		$data['post_status'] = $resolved_status;

		if ( is_admin() && function_exists( 'get_current_user_id' ) ) {
			$user_id = get_current_user_id();

			if ( $user_id > 0 ) {
				set_transient( self::NOTICE_TRANSIENT_PREFIX . $user_id, 1, MINUTE_IN_SECONDS );
			}
		}

		return $data;
	}

	/**
	 * Resolve the post status for a magazine save attempt.
	 *
	 * @param string     $requested_status Requested post status.
	 * @param int        $post_id          Magazine post ID, or 0 for new posts.
	 * @param mixed|null $submitted_ids    Optional submitted page IDs for testing.
	 */
	public static function resolve_status_for_save( string $requested_status, int $post_id, $submitted_ids = null ): string {
		if ( 'publish' !== $requested_status ) {
			return $requested_status;
		}

		return self::has_publishable_pages( $post_id, $submitted_ids ) ? 'publish' : 'draft';
	}

	/**
	 * Whether a magazine has at least one valid WebP page for publishing.
	 *
	 * @param int        $post_id       Magazine post ID, or 0 for new posts.
	 * @param mixed|null $submitted_ids Optional submitted page IDs (bypasses $_POST).
	 */
	public static function has_publishable_pages( int $post_id, $submitted_ids = null ): bool {
		if ( null !== $submitted_ids ) {
			$pages = ( new Magazine_Pages() )->sanitize_page_ids( $submitted_ids );

			return ! empty( $pages );
		}

		if ( isset( $_POST['magazine73_page_ids'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$raw_ids = wp_unslash( $_POST['magazine73_page_ids'] );
			$pages   = ( new Magazine_Pages() )->sanitize_page_ids( $raw_ids );

			return ! empty( $pages );
		}

		if ( $post_id <= 0 ) {
			return false;
		}

		return ! empty( Magazine_Pages::get_page_ids( $post_id ) );
	}

	/**
	 * Show an admin notice when a publish attempt was blocked.
	 */
	public function render_admin_notice(): void {
		if ( ! is_admin() || ! function_exists( 'get_current_user_id' ) ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( $user_id <= 0 ) {
			return;
		}

		$transient_key = self::NOTICE_TRANSIENT_PREFIX . $user_id;

		if ( ! get_transient( $transient_key ) ) {
			return;
		}

		delete_transient( $transient_key );

		printf(
			'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
			esc_html__(
				'A magazine cannot be published without at least one page. The magazine was saved as a draft.',
				'magazine73'
			)
		);
	}
}
