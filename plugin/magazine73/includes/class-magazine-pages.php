<?php
/**
 * Magazine page attachment management.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Stores and validates magazine WebP page attachments.
 */
final class Magazine_Pages {

	/**
	 * Page attachment IDs metadata key.
	 */
	public const PAGE_IDS_META_KEY = 'magazine73_page_ids';

	/**
	 * Large page warning threshold in bytes.
	 */
	public const LARGE_PAGE_THRESHOLD_BYTES = 307200;

	/**
	 * Register metadata hooks.
	 */
	public function init(): void {
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	/**
	 * Register magazine page metadata.
	 */
	public function register_meta(): void {
		register_post_meta(
			Post_Type::POST_TYPE,
			self::PAGE_IDS_META_KEY,
			array(
				'type'              => 'array',
				'description'       => __( 'Ordered WebP page attachment IDs for a magazine.', 'magazine73' ),
				'single'            => true,
				'sanitize_callback' => array( $this, 'sanitize_page_ids' ),
				'show_in_rest'      => false,
				'auth_callback'     => array( $this, 'can_edit_page_meta' ),
			)
		);
	}

	/**
	 * Sanitize and order magazine page attachment IDs.
	 *
	 * @param mixed $value Raw metadata value.
	 * @return int[]
	 */
	public function sanitize_page_ids( $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$attachment_ids = array();

		foreach ( $value as $attachment_id ) {
			if ( is_numeric( $attachment_id ) ) {
				$attachment_ids[] = (int) $attachment_id;
			}
		}

		return self::sort_attachment_ids_by_filename( self::filter_valid_webp_ids( $attachment_ids ) );
	}

	/**
	 * Determine whether the current user can edit page metadata.
	 *
	 * @param bool   $allowed  Whether the user can edit the value.
	 * @param string $meta_key Metadata key.
	 * @param int    $post_id  Post ID.
	 */
	public function can_edit_page_meta( bool $allowed, string $meta_key, int $post_id ): bool {
		unset( $allowed, $meta_key );

		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * Get ordered page attachment IDs for a magazine.
	 *
	 * @param int $post_id Magazine post ID.
	 * @return int[]
	 */
	public static function get_page_ids( int $post_id ): array {
		$page_ids = get_post_meta( $post_id, self::PAGE_IDS_META_KEY, true );

		if ( ! is_array( $page_ids ) ) {
			return array();
		}

		$sanitized = array();

		foreach ( $page_ids as $page_id ) {
			if ( is_numeric( $page_id ) ) {
				$sanitized[] = (int) $page_id;
			}
		}

		return self::sort_attachment_ids_by_filename( self::filter_valid_webp_ids( $sanitized ) );
	}

	/**
	 * Get the cover attachment ID for a magazine.
	 *
	 * @param int $post_id Magazine post ID.
	 */
	public static function get_cover_attachment_id( int $post_id ): int {
		$page_ids = self::get_page_ids( $post_id );

		return $page_ids[0] ?? 0;
	}

	/**
	 * Build page statistics for a magazine.
	 *
	 * @param int $post_id Magazine post ID.
	 * @return array{
	 *     count: int,
	 *     total_bytes: int,
	 *     average_bytes: int,
	 *     large_page_ids: int[]
	 * }
	 */
	public static function get_stats( int $post_id ): array {
		$page_ids    = self::get_page_ids( $post_id );
		$total_bytes = 0;
		$large_pages = array();

		foreach ( $page_ids as $attachment_id ) {
			$file_path = get_attached_file( $attachment_id );

			if ( ! is_string( $file_path ) || '' === $file_path || ! file_exists( $file_path ) ) {
				continue;
			}

			$filesize    = (int) filesize( $file_path );
			$total_bytes = $total_bytes + $filesize;

			if ( $filesize > self::LARGE_PAGE_THRESHOLD_BYTES ) {
				$large_pages[] = $attachment_id;
			}
		}

		$count = count( $page_ids );

		return array(
			'count'          => $count,
			'total_bytes'    => $total_bytes,
			'average_bytes'  => $count > 0 ? (int) round( $total_bytes / $count ) : 0,
			'large_page_ids' => $large_pages,
		);
	}

	/**
	 * Get display data for magazine pages.
	 *
	 * @param int $post_id Magazine post ID.
	 * @return array<int, array{id: int, filename: string, url: string, thumb_url: string, bytes: int, is_large: bool}>
	 */
	public static function get_page_items( int $post_id ): array {
		$items = array();

		foreach ( self::get_page_ids( $post_id ) as $attachment_id ) {
			$file_path = get_attached_file( $attachment_id );
			$filename  = basename( (string) $file_path );
			$bytes     = 0;

			if ( is_string( $file_path ) && '' !== $file_path && file_exists( $file_path ) ) {
				$bytes = (int) filesize( $file_path );
			}

			$thumb_url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
			$url       = wp_get_attachment_url( $attachment_id );

			$items[] = array(
				'id'        => $attachment_id,
				'filename'  => $filename,
				'url'       => is_string( $url ) ? $url : '',
				'thumb_url' => is_string( $thumb_url ) ? $thumb_url : '',
				'bytes'     => $bytes,
				'is_large'  => $bytes > self::LARGE_PAGE_THRESHOLD_BYTES,
			);
		}

		return $items;
	}

	/**
	 * Build viewer page data for the public flipbook.
	 *
	 * @param int $post_id Magazine post ID.
	 * @return array<int, array{url: string, width: int, height: int, blank?: bool}>
	 */
	public static function get_viewer_pages( int $post_id ): array {
		$pages = array();

		foreach ( self::get_page_items( $post_id ) as $item ) {
			if ( '' === $item['url'] ) {
				continue;
			}

			$dimensions = self::get_attachment_dimensions( $item['id'] );

			$pages[] = array(
				'url'    => $item['url'],
				'width'  => $dimensions['width'],
				'height' => $dimensions['height'],
			);
		}

		if ( 0 === count( $pages ) % 2 ) {
			return $pages;
		}

		$cover = $pages[0];

		$pages[] = array(
			'url'    => '',
			'width'  => $cover['width'],
			'height' => $cover['height'],
			'blank'  => true,
		);

		return $pages;
	}

	/**
	 * Build a content hash from ordered page attachment IDs.
	 *
	 * @param int $post_id Magazine post ID.
	 */
	public static function get_content_hash( int $post_id ): string {
		$page_ids = self::get_page_ids( $post_id );

		if ( empty( $page_ids ) ) {
			return '';
		}

		$hash_input = implode(
			',',
			array_map(
				static function ( int $page_id ): string {
					return (string) $page_id;
				},
				$page_ids
			)
		);

		return hash( 'sha256', $hash_input );
	}

	/**
	 * Get attachment dimensions with a neutral fallback ratio.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return array{width: int, height: int}
	 */
	private static function get_attachment_dimensions( int $attachment_id ): array {
		$metadata = wp_get_attachment_metadata( $attachment_id );

		if ( is_array( $metadata ) && ! empty( $metadata['width'] ) && ! empty( $metadata['height'] ) ) {
			return array(
				'width'  => (int) $metadata['width'],
				'height' => (int) $metadata['height'],
			);
		}

		return array(
			'width'  => 3,
			'height' => 4,
		);
	}

	/**
	 * Keep only valid WebP attachment IDs.
	 *
	 * @param int[] $attachment_ids Attachment IDs.
	 * @return int[]
	 */
	public static function filter_valid_webp_ids( array $attachment_ids ): array {
		$valid_ids = array();

		foreach ( array_unique( $attachment_ids ) as $attachment_id ) {
			if ( self::is_valid_webp_attachment( (int) $attachment_id ) ) {
				$valid_ids[] = (int) $attachment_id;
			}
		}

		return $valid_ids;
	}

	/**
	 * Determine whether an attachment is a WebP image.
	 *
	 * @param int $attachment_id Attachment ID.
	 */
	public static function is_valid_webp_attachment( int $attachment_id ): bool {
		if ( $attachment_id <= 0 || 'attachment' !== get_post_type( $attachment_id ) ) {
			return false;
		}

		$mime_type = get_post_mime_type( $attachment_id );

		return 'image/webp' === $mime_type;
	}

	/**
	 * Sort attachment IDs by filename using natural ascending order.
	 *
	 * @param int[] $attachment_ids Attachment IDs.
	 * @return int[]
	 */
	public static function sort_attachment_ids_by_filename( array $attachment_ids ): array {
		$filenames = array();

		foreach ( $attachment_ids as $attachment_id ) {
			$filenames[ $attachment_id ] = basename( (string) get_attached_file( $attachment_id ) );
		}

		natsort( $filenames );

		return array_map( 'intval', array_keys( $filenames ) );
	}
}
