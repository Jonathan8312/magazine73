<?php
/**
 * Magazine viewer rendering.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Builds viewer output for shortcodes and public templates.
 */
final class Magazine_Renderer {

	/**
	 * Supported shortcode theme presets.
	 *
	 * @var string[]
	 */
	private const VALID_THEMES = array(
		'light',
		'dark',
	);

	/**
	 * Render a viewer for a magazine.
	 *
	 * @param int                  $magazine_id     Magazine post ID.
	 * @param array<string, mixed> $shortcode_atts  Optional shortcode attributes.
	 */
	public static function render_viewer( int $magazine_id, array $shortcode_atts = array() ): string {
		$magazine = get_post( $magazine_id );

		if ( ! $magazine instanceof \WP_Post || Post_Type::POST_TYPE !== $magazine->post_type ) {
			return self::render_message( __( 'Magazine not found.', 'magazine73' ) );
		}

		if ( 'publish' !== $magazine->post_status ) {
			return self::render_message( __( 'This magazine is not available.', 'magazine73' ) );
		}

		$page_ids = Magazine_Pages::get_page_ids( $magazine_id );

		if ( empty( $page_ids ) ) {
			return self::render_message( __( 'This magazine has no pages yet.', 'magazine73' ) );
		}

		Assets::enqueue_viewer();

		$settings      = self::resolve_settings( $magazine_id, $shortcode_atts );
		$dimensions    = self::resolve_dimensions( $shortcode_atts );
		$viewer_config = self::build_viewer_config( $magazine_id, $settings, $dimensions );

		ob_start();

		Template_Loader::load_template(
			'viewer.php',
			array(
				'magazine'      => $magazine,
				'settings'      => $settings,
				'viewer_config' => $viewer_config,
				'width'         => $dimensions['width'],
				'height'        => $dimensions['height'],
			)
		);

		$output = ob_get_clean();

		return is_string( $output ) ? $output : '';
	}

	/**
	 * Resolve effective viewer settings with optional shortcode overrides.
	 *
	 * @param int                  $magazine_id    Magazine post ID.
	 * @param array<string, mixed> $shortcode_atts Shortcode attributes.
	 * @return array{colors: array{background: string, controls: string, text: string}, controls: array<string, bool>}
	 */
	public static function resolve_settings( int $magazine_id, array $shortcode_atts = array() ): array {
		$settings = Viewer_Settings::get_for_magazine( $magazine_id );
		$controls = $settings['controls'];

		if ( array_key_exists( 'controls', $shortcode_atts ) ) {
			$parsed = self::parse_boolean_attribute( $shortcode_atts['controls'] );

			if ( null !== $parsed ) {
				foreach ( Viewer_Settings::get_control_keys() as $control_key ) {
					$controls[ $control_key ] = $parsed;
				}
			}
		}

		foreach ( array( 'fullscreen', 'download', 'thumbnails' ) as $control_key ) {
			if ( ! array_key_exists( $control_key, $shortcode_atts ) ) {
				continue;
			}

			$parsed = self::parse_boolean_attribute( $shortcode_atts[ $control_key ] );

			if ( null !== $parsed ) {
				$controls[ $control_key ] = $parsed;
			}
		}

		$settings['controls'] = $controls;

		if ( isset( $shortcode_atts['theme'] ) ) {
			$settings = self::apply_theme( $settings, $shortcode_atts['theme'] );
		}

		return $settings;
	}

	/**
	 * Build viewer configuration for the frontend integration layer.
	 *
	 * @param int                                                                                                     $magazine_id Magazine post ID.
	 * @param array{colors: array{background: string, controls: string, text: string}, controls: array<string, bool>} $settings    Resolved viewer settings.
	 * @param array{width: string, height: string}                                                                    $dimensions  Optional viewer dimensions.
	 * @return array{magazineId: int, pages: array<int, array{url: string, width: int, height: int, blank?: bool}>, settings: array{colors: array{background: string, controls: string, text: string}, controls: array<string, bool>}, dimensions: array{width: string, height: string}}
	 */
	public static function build_viewer_config( int $magazine_id, array $settings, array $dimensions ): array {
		return array(
			'magazineId' => $magazine_id,
			'pages'      => Magazine_Pages::get_viewer_pages( $magazine_id ),
			'settings'   => $settings,
			'dimensions' => $dimensions,
		);
	}

	/**
	 * Render a user-facing message.
	 *
	 * @param string $message Message text.
	 */
	public static function render_message( string $message ): string {
		return sprintf(
			'<div class="magazine73-message">%s</div>',
			esc_html( $message )
		);
	}

	/**
	 * Resolve optional viewer dimensions.
	 *
	 * @param array<string, mixed> $shortcode_atts Shortcode attributes.
	 * @return array{width: string, height: string}
	 */
	private static function resolve_dimensions( array $shortcode_atts ): array {
		return array(
			'width'  => self::parse_dimension_attribute( $shortcode_atts['width'] ?? '' ),
			'height' => self::parse_dimension_attribute( $shortcode_atts['height'] ?? '' ),
		);
	}

	/**
	 * Parse a CSS dimension attribute.
	 *
	 * @param mixed $value Raw attribute value.
	 */
	private static function parse_dimension_attribute( $value ): string {
		if ( ! is_scalar( $value ) ) {
			return '';
		}

		$value = trim( (string) $value );

		if ( '' === $value ) {
			return '';
		}

		if ( preg_match( '/^\d+(\.\d+)?(px|%|rem|em|vw|vh)$/i', $value ) ) {
			return strtolower( $value );
		}

		return '';
	}

	/**
	 * Parse a boolean shortcode attribute.
	 *
	 * @param mixed $value Raw attribute value.
	 */
	private static function parse_boolean_attribute( $value ): ?bool {
		if ( ! is_scalar( $value ) ) {
			return null;
		}

		$normalized = strtolower( trim( (string) $value ) );

		if ( in_array( $normalized, array( '1', 'true', 'yes', 'on' ), true ) ) {
			return true;
		}

		if ( in_array( $normalized, array( '0', 'false', 'no', 'off' ), true ) ) {
			return false;
		}

		return null;
	}

	/**
	 * Apply a supported theme preset to viewer settings.
	 *
	 * @param array{colors: array{background: string, controls: string, text: string}, controls: array<string, bool>} $settings Viewer settings.
	 * @param mixed                                                                                                   $theme    Theme attribute value.
	 * @return array{colors: array{background: string, controls: string, text: string}, controls: array<string, bool>}
	 */
	private static function apply_theme( array $settings, $theme ): array {
		if ( ! is_scalar( $theme ) ) {
			return $settings;
		}

		$theme = strtolower( trim( (string) $theme ) );

		if ( ! in_array( $theme, self::VALID_THEMES, true ) ) {
			return $settings;
		}

		if ( 'dark' === $theme ) {
			$settings['colors']['background'] = '#1a1a1a';
			$settings['colors']['controls']   = '#2d2d2d';
			$settings['colors']['text']       = '#f5f5f5';
		}

		return $settings;
	}
}
