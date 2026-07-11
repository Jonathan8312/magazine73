<?php
/**
 * Minimal WordPress stubs for unit tests.
 *
 * @package Magazine73
 */

declare(strict_types=1);

namespace Magazine73\Tests {

	/**
	 * Mutable WordPress state for tests.
	 */
	final class WordPressStub {

		/**
		 * Stored options.
		 *
		 * @var array<string, mixed>
		 */
		public static array $options = array();

		/**
		 * Stored post meta keyed by post ID and meta key.
		 *
		 * @var array<int, array<string, mixed>>
		 */
		public static array $post_meta = array();

		/**
		 * Post types keyed by post ID.
		 *
		 * @var array<int, string>
		 */
		public static array $post_types = array();

		/**
		 * MIME types keyed by attachment ID.
		 *
		 * @var array<int, string>
		 */
		public static array $mime_types = array();

		/**
		 * Attached file paths keyed by attachment ID.
		 *
		 * @var array<int, string>
		 */
		public static array $attached_files = array();

		/**
		 * Magazine post IDs returned by get_posts().
		 *
		 * @var array<int, int>
		 */
		public static array $magazine_post_ids = array();

		/**
		 * Post IDs passed to wp_delete_post().
		 *
		 * @var array<int, int>
		 */
		public static array $deleted_post_ids = array();

		/**
		 * Options removed through delete_option().
		 *
		 * @var array<int, string>
		 */
		public static array $deleted_options = array();

		/**
		 * Reset all stubbed state.
		 */
		public static function reset(): void {
			self::$options           = array();
			self::$post_meta         = array();
			self::$post_types        = array();
			self::$mime_types        = array();
			self::$attached_files    = array();
			self::$magazine_post_ids = array();
			self::$deleted_post_ids  = array();
			self::$deleted_options   = array();
		}
	}
}

namespace {

	use Magazine73\Tests\WordPressStub;

	defined( 'ABSPATH' ) || define( 'ABSPATH', '/tmp/wordpress/' );

	if ( ! defined( 'MAGAZINE73_VERSION' ) ) {
		define( 'MAGAZINE73_VERSION', '0.1.0' );
	}

	if ( ! defined( 'MAGAZINE73_FILE' ) ) {
		define( 'MAGAZINE73_FILE', dirname( __DIR__, 2 ) . '/plugin/magazine73/magazine73.php' );
	}

	if ( ! defined( 'MAGAZINE73_PATH' ) ) {
		define( 'MAGAZINE73_PATH', dirname( __DIR__, 2 ) . '/plugin/magazine73/' );
	}

	if ( ! defined( 'MAGAZINE73_URL' ) ) {
		define( 'MAGAZINE73_URL', 'https://example.test/wp-content/plugins/magazine73/' );
	}

	if ( ! defined( 'MAGAZINE73_BASENAME' ) ) {
		define( 'MAGAZINE73_BASENAME', 'magazine73/magazine73.php' );
	}

	if ( ! function_exists( '__' ) ) {
		/**
		 * @param string $text Text.
		 */
		function __( string $text, string $domain = 'default' ): string { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
			unset( $domain );
			return $text;
		}
	}

	if ( ! function_exists( 'get_option' ) ) {
		/**
		 * @param mixed $default_value Default value.
		 * @return mixed
		 */
		function get_option( string $option, $default_value = false ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
			return WordPressStub::$options[ $option ] ?? $default_value;
		}
	}

	if ( ! function_exists( 'update_option' ) ) {
		/**
		 * @param mixed $value Option value.
		 */
		function update_option( string $option, $value, $autoload = null ): bool { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
			unset( $autoload );
			WordPressStub::$options[ $option ] = $value;
			return true;
		}
	}

	if ( ! function_exists( 'delete_option' ) ) {
		function delete_option( string $option ): bool { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
			if ( array_key_exists( $option, WordPressStub::$options ) ) {
				unset( WordPressStub::$options[ $option ] );
				WordPressStub::$deleted_options[] = $option;
			}

			return true;
		}
	}

	if ( ! function_exists( 'get_posts' ) ) {
		/**
		 * @param array<string, mixed> $args Query arguments.
		 * @return array<int, int>
		 */
		function get_posts( array $args = array() ): array { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
			unset( $args );
			return WordPressStub::$magazine_post_ids;
		}
	}

	if ( ! function_exists( 'wp_delete_post' ) ) {
		function wp_delete_post( int $post_id, bool $force_delete = false ): bool { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
			unset( $force_delete );
			WordPressStub::$deleted_post_ids[] = $post_id;
			return true;
		}
	}

	if ( ! function_exists( 'get_post_meta' ) ) {
		/**
		 * @param mixed $default_value Default value.
		 * @return mixed
		 */
		function get_post_meta( int $post_id, string $key = '', bool $single = false ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
			unset( $single );
			return WordPressStub::$post_meta[ $post_id ][ $key ] ?? '';
		}
	}

	if ( ! function_exists( 'get_post_type' ) ) {
		function get_post_type( $post = null ): string|false { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
			if ( ! is_numeric( $post ) ) {
				return false;
			}

			return WordPressStub::$post_types[ (int) $post ] ?? false;
		}
	}

	if ( ! function_exists( 'get_post_mime_type' ) ) {
		function get_post_mime_type( $post = null ): string|false { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
			if ( ! is_numeric( $post ) ) {
				return false;
			}

			return WordPressStub::$mime_types[ (int) $post ] ?? false;
		}
	}

	if ( ! function_exists( 'get_attached_file' ) ) {
		function get_attached_file( int $attachment_id, bool $unfiltered = false ): string|false { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
			unset( $unfiltered );
			return WordPressStub::$attached_files[ $attachment_id ] ?? false;
		}
	}

	if ( ! function_exists( 'wp_get_attachment_url' ) ) {
		function wp_get_attachment_url( int $attachment_id = 0 ): string|false { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
			if ( $attachment_id <= 0 ) {
				return false;
			}

			return 'https://example.test/wp-content/uploads/' . $attachment_id . '.webp';
		}
	}

	if ( ! function_exists( 'wp_get_attachment_image_url' ) ) {
		function wp_get_attachment_image_url( int $attachment_id = 0, $size = 'thumbnail', bool $icon = false ): string|false { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
			unset( $size, $icon );
			return wp_get_attachment_url( $attachment_id );
		}
	}

	if ( ! function_exists( 'wp_get_attachment_metadata' ) ) {
		/**
		 * @return array<string, int>|false
		 */
		function wp_get_attachment_metadata( int $attachment_id = 0, bool $unfiltered = false ): array|false { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
			unset( $unfiltered );
			if ( $attachment_id <= 0 ) {
				return false;
			}

			return array(
				'width'  => 800,
				'height' => 1200,
			);
		}
	}

	if ( ! function_exists( 'rest_sanitize_boolean' ) ) {
		/**
		 * @param mixed $value Raw value.
		 */
		function rest_sanitize_boolean( $value ): bool { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
			return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
		}
	}

	if ( ! function_exists( 'sanitize_hex_color' ) ) {
		function sanitize_hex_color( string $color ): string|false { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
			if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
				return $color;
			}

			return false;
		}
	}

	if ( ! function_exists( 'wp_unslash' ) ) {
		/**
		 * @param mixed $value Value to unslash.
		 * @return mixed
		 */
		function wp_unslash( $value ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
			if ( is_array( $value ) ) {
				return array_map( 'wp_unslash', $value );
			}

			return is_string( $value ) ? stripslashes( $value ) : $value;
		}
	}

	if ( ! class_exists( 'WP_Post' ) ) {
		/**
		 * Minimal post object stub.
		 */
		class WP_Post { // phpcs:ignore Generic.Files.OneObjectStructurePerFile.MultipleFound, Squiz.Classes.ValidClassName.NotCamelCaps
			/**
			 * @param array<string, mixed> $data Post data.
			 */
			public function __construct( public array $data = array() ) {
				foreach ( $data as $key => $value ) {
					$this->{$key} = $value;
				}
			}
		}
	}
}
