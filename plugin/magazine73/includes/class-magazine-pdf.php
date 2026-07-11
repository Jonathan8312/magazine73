<?php
/**
 * Magazine PDF attachment management.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Stores and validates optional magazine PDF attachments.
 */
final class Magazine_Pdf {

	/**
	 * PDF attachment ID metadata key.
	 */
	public const PDF_ATTACHMENT_META_KEY = 'magazine73_pdf_attachment_id';

	/**
	 * Register metadata hooks.
	 */
	public function init(): void {
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	/**
	 * Register magazine PDF metadata.
	 */
	public function register_meta(): void {
		register_post_meta(
			Post_Type::POST_TYPE,
			self::PDF_ATTACHMENT_META_KEY,
			array(
				'type'              => 'integer',
				'description'       => __( 'Optional PDF attachment ID for magazine download.', 'magazine73' ),
				'single'            => true,
				'default'           => 0,
				'sanitize_callback' => array( $this, 'sanitize_attachment_id' ),
				'show_in_rest'      => false,
				'auth_callback'     => array( $this, 'can_edit_pdf_meta' ),
			)
		);
	}

	/**
	 * Sanitize and validate a PDF attachment ID.
	 *
	 * @param mixed $value Raw metadata value.
	 */
	public function sanitize_attachment_id( $value ): int {
		$attachment_id = is_numeric( $value ) ? (int) $value : 0;

		if ( $attachment_id <= 0 ) {
			return 0;
		}

		return self::is_valid_pdf_attachment( $attachment_id ) ? $attachment_id : 0;
	}

	/**
	 * Determine whether the current user can edit PDF metadata.
	 *
	 * @param bool   $allowed  Whether the user can edit the value.
	 * @param string $meta_key Metadata key.
	 * @param int    $post_id  Post ID.
	 */
	public function can_edit_pdf_meta( bool $allowed, string $meta_key, int $post_id ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
		unset( $allowed, $meta_key );

		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * Get the stored PDF attachment ID for a magazine.
	 *
	 * @param int $post_id Magazine post ID.
	 */
	public static function get_attachment_id( int $post_id ): int {
		$stored        = get_post_meta( $post_id, self::PDF_ATTACHMENT_META_KEY, true );
		$attachment_id = is_numeric( $stored ) ? (int) $stored : 0;

		if ( $attachment_id <= 0 || ! self::is_valid_pdf_attachment( $attachment_id ) ) {
			return 0;
		}

		return $attachment_id;
	}

	/**
	 * Build download data for the viewer when a valid PDF exists.
	 *
	 * @param int $post_id Magazine post ID.
	 * @return array{url: string, filename: string}|null
	 */
	public static function get_download_data( int $post_id ): ?array {
		$attachment_id = self::get_attachment_id( $post_id );

		if ( $attachment_id <= 0 ) {
			return null;
		}

		$url = wp_get_attachment_url( $attachment_id );

		if ( ! is_string( $url ) || '' === $url ) {
			return null;
		}

		return array(
			'url'      => $url,
			'filename' => self::get_safe_filename( $attachment_id ),
		);
	}

	/**
	 * Get admin display details for the selected PDF.
	 *
	 * @param int $post_id Magazine post ID.
	 * @return array{id: int, filename: string}|null
	 */
	public static function get_admin_display( int $post_id ): ?array {
		$attachment_id = self::get_attachment_id( $post_id );

		if ( $attachment_id <= 0 ) {
			return null;
		}

		$filename = self::get_attachment_filename( $attachment_id );

		if ( '' === $filename ) {
			return null;
		}

		return array(
			'id'       => $attachment_id,
			'filename' => $filename,
		);
	}

	/**
	 * Determine whether an attachment is a PDF file.
	 *
	 * @param int $attachment_id Attachment ID.
	 */
	public static function is_valid_pdf_attachment( int $attachment_id ): bool {
		if ( $attachment_id <= 0 || 'attachment' !== get_post_type( $attachment_id ) ) {
			return false;
		}

		return 'application/pdf' === get_post_mime_type( $attachment_id );
	}

	/**
	 * Build a safe download filename from an attachment.
	 *
	 * @param int $attachment_id Attachment ID.
	 */
	public static function get_safe_filename( int $attachment_id ): string {
		$filename = self::get_attachment_filename( $attachment_id );

		if ( '' === $filename ) {
			return 'magazine.pdf';
		}

		$sanitized = preg_replace( '/[^A-Za-z0-9._-]/', '_', $filename );

		if ( ! is_string( $sanitized ) ) {
			return 'magazine.pdf';
		}

		$sanitized = trim( $sanitized );

		if ( '' === $sanitized ) {
			return 'magazine.pdf';
		}

		if ( ! str_ends_with( strtolower( $sanitized ), '.pdf' ) ) {
			$sanitized .= '.pdf';
		}

		return $sanitized;
	}

	/**
	 * Get the original attachment filename.
	 *
	 * @param int $attachment_id Attachment ID.
	 */
	private static function get_attachment_filename( int $attachment_id ): string {
		$file = get_attached_file( $attachment_id );

		if ( ! is_string( $file ) || '' === $file ) {
			return '';
		}

		return basename( $file );
	}
}
