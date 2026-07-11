<?php
/**
 * Magazine metadata registration.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and resolves magazine metadata.
 */
final class Magazine_Meta {

	/**
	 * Edition metadata key.
	 */
	public const EDITION_META_KEY = 'magazine73_edition';

	/**
	 * Register metadata hooks.
	 */
	public function init(): void {
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	/**
	 * Register magazine metadata.
	 */
	public function register_meta(): void {
		register_post_meta(
			Post_Type::POST_TYPE,
			self::EDITION_META_KEY,
			array(
				'type'              => 'string',
				'description'       => __( 'Optional magazine edition label.', 'magazine73' ),
				'single'            => true,
				'sanitize_callback' => array( $this, 'sanitize_edition' ),
				'show_in_rest'      => true,
				'auth_callback'     => array( $this, 'can_edit_magazine_meta' ),
			)
		);
	}

	/**
	 * Sanitize the edition metadata value.
	 *
	 * @param mixed $value Raw metadata value.
	 */
	public function sanitize_edition( $value ): string {
		if ( ! is_scalar( $value ) ) {
			return '';
		}

		return sanitize_text_field( (string) $value );
	}

	/**
	 * Determine whether the current user can edit magazine metadata.
	 *
	 * @param bool   $allowed Whether the user can edit the value.
	 * @param string $meta_key Metadata key.
	 * @param int    $post_id Post ID.
	 */
	public function can_edit_magazine_meta( bool $allowed, string $meta_key, int $post_id ): bool {
		unset( $allowed, $meta_key );

		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * Get the edition for a magazine.
	 *
	 * @param int $post_id Magazine post ID.
	 */
	public static function get_edition( int $post_id ): string {
		$edition = get_post_meta( $post_id, self::EDITION_META_KEY, true );

		return is_string( $edition ) ? $edition : '';
	}

	/**
	 * Build the shortcode for a magazine.
	 *
	 * @param int $post_id Magazine post ID.
	 */
	public static function get_shortcode( int $post_id ): string {
		if ( $post_id <= 0 ) {
			return '';
		}

		return sprintf( '[magazine73 id="%d"]', $post_id );
	}

	/**
	 * Build the public URL for a published magazine.
	 *
	 * @param int $post_id Magazine post ID.
	 */
	public static function get_public_url( int $post_id ): string {
		if ( $post_id <= 0 ) {
			return '';
		}

		$permalink = get_permalink( $post_id );

		return is_string( $permalink ) ? $permalink : '';
	}

	/**
	 * Format an edition value for display.
	 *
	 * @param string $edition Edition value.
	 */
	public static function format_edition_display( string $edition ): string {
		return '' !== $edition ? $edition : '—';
	}
}
