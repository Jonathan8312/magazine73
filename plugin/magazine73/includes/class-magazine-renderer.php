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
	 * Internal attribute flag: viewer colors come from an Elementor widget.
	 */
	public const ELEMENTOR_COLOR_SOURCE = '__magazine73_color_source';

	/**
	 * Attribute value paired with {@see Magazine_Renderer::ELEMENTOR_COLOR_SOURCE}.
	 */
	public const ELEMENTOR_COLOR_SOURCE_VALUE = 'elementor';

	/**
	 * Shortcode / widget color attribute map.
	 *
	 * @var array<string, string>
	 */
	private const COLOR_ATTRIBUTE_MAP = array(
		'color_background'     => 'background',
		'color_controls'       => 'controls',
		'color_controls_hover' => 'controls_hover',
		'color_icons'          => 'icons',
		'color_icons_hover'    => 'icons_hover',
		'color_counter'        => 'counter',
		'color_text'           => 'text',
	);

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
		$elementor_colors  = self::is_elementor_color_context( $shortcode_atts );
		$magazine_settings = Viewer_Settings::get_for_magazine( $magazine_id );
		$settings          = $magazine_settings;

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

		$theme = self::parse_theme_attribute( $shortcode_atts['theme'] ?? '' );

		if ( $elementor_colors ) {
			$settings['colors'] = Viewer_Settings::get_defaults()['colors'];
			$settings             = self::apply_color_overrides( $settings, $shortcode_atts );
			$settings['colors']   = self::merge_elementor_colors_with_magazine(
				$settings['colors'],
				self::get_elementor_color_fallback( $magazine_settings['colors'], $theme ),
				$shortcode_atts
			);
		} else {
			if ( '' !== $theme ) {
				$settings = self::apply_theme( $settings, $theme );
			}

			$settings = self::apply_color_overrides( $settings, $shortcode_atts );
		}

		$settings['colors'] = self::normalize_legacy_text_colors( $settings['colors'] );

		return $settings;
	}

	/**
	 * Build viewer configuration for the frontend integration layer.
	 *
	 * @param int                                                                                                     $magazine_id Magazine post ID.
	 * @param array{colors: array{background: string, controls: string, text: string}, controls: array<string, bool>} $settings    Resolved viewer settings.
	 * @param array{width: string, height: string}                                                                    $dimensions  Optional viewer dimensions.
	 * @return array{magazineId: int, contentHash: string, pages: array<int, array{url: string, width: int, height: int, blank?: bool}>, settings: array{colors: array{background: string, controls: string, text: string}, controls: array<string, bool>}, dimensions: array{width: string, height: string}, download?: array{url: string, filename: string}}
	 */
	public static function build_viewer_config( int $magazine_id, array $settings, array $dimensions ): array {
		$config = array(
			'magazineId'  => $magazine_id,
			'contentHash' => Magazine_Pages::get_content_hash( $magazine_id ),
			'pages'       => Magazine_Pages::get_viewer_pages( $magazine_id ),
			'settings'    => $settings,
			'dimensions'  => $dimensions,
		);

		$download = Magazine_Pdf::get_download_data( $magazine_id );

		if ( null !== $download ) {
			$config['download'] = $download;
		}

		return $config;
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
	 * Expand legacy text color values into icons and counter when unset.
	 *
	 * @param array<string, string> $colors Resolved color settings.
	 * @return array<string, string>
	 */
	private static function normalize_legacy_text_colors( array $colors ): array {
		$legacy_text = trim( $colors['text'] ?? '' );

		if ( '' === $legacy_text ) {
			return $colors;
		}

		foreach ( array( 'icons', 'counter' ) as $color_key ) {
			if ( '' === trim( $colors[ $color_key ] ?? '' ) ) {
				$colors[ $color_key ] = $legacy_text;
			}
		}

		return $colors;
	}

	/**
	 * Parse a theme preset attribute.
	 *
	 * @param mixed $theme Raw theme attribute.
	 */
	private static function parse_theme_attribute( $theme ): string {
		if ( ! is_scalar( $theme ) ) {
			return '';
		}

		return strtolower( trim( (string) $theme ) );
	}

	/**
	 * Resolve fallback colors for unset Elementor style fields.
	 *
	 * @param array<string, string> $magazine_colors Magazine or global colors.
	 * @param string                $theme           Theme preset slug.
	 * @return array<string, string>
	 */
	private static function get_elementor_color_fallback( array $magazine_colors, string $theme ): array {
		if ( '' === $theme ) {
			return $magazine_colors;
		}

		$preset_colors = self::get_theme_preset_colors( $theme );

		return is_array( $preset_colors ) ? $preset_colors : $magazine_colors;
	}

	/**
	 * Return the color palette for a supported theme preset.
	 *
	 * @param string $theme Theme preset slug.
	 * @return array<string, string>|null
	 */
	private static function get_theme_preset_colors( string $theme ): ?array {
		if ( ! in_array( $theme, self::VALID_THEMES, true ) ) {
			return null;
		}

		$settings = self::apply_theme(
			array(
				'colors'   => Viewer_Settings::get_defaults()['colors'],
				'controls' => Viewer_Settings::get_defaults()['controls'],
			),
			$theme
		);

		return $settings['colors'];
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
			$settings['colors']['icons']      = '#f5f5f5';
			$settings['colors']['counter']    = '#f5f5f5';
		}

		return $settings;
	}

	/**
	 * Apply explicit color overrides after theme presets.
	 *
	 * Empty values are ignored so magazine/global colors remain in effect.
	 *
	 * @param array{colors: array{background: string, controls: string, text: string}, controls: array<string, bool>} $settings Viewer settings.
	 * @param array<string, mixed>                                                                                    $atts     Shortcode or widget attributes.
	 * @return array{colors: array{background: string, controls: string, text: string}, controls: array<string, bool>}
	 */
	private static function apply_color_overrides( array $settings, array $atts ): array {
		foreach ( self::COLOR_ATTRIBUTE_MAP as $attribute => $color_key ) {
			if ( ! array_key_exists( $attribute, $atts ) ) {
				continue;
			}

			$raw = $atts[ $attribute ];

			if ( ! is_scalar( $raw ) ) {
				continue;
			}

			$raw = trim( (string) $raw );

			if ( '' === $raw ) {
				continue;
			}

			$sanitized = sanitize_hex_color( $raw );

			if ( is_string( $sanitized ) && '' !== $sanitized ) {
				$settings['colors'][ $color_key ] = $sanitized;
			}
		}

		return $settings;
	}

	/**
	 * Whether viewer colors are being supplied by an Elementor widget.
	 *
	 * @param array<string, mixed> $atts Shortcode or widget attributes.
	 */
	private static function is_elementor_color_context( array $atts ): bool {
		if ( ! array_key_exists( self::ELEMENTOR_COLOR_SOURCE, $atts ) ) {
			return false;
		}

		return self::ELEMENTOR_COLOR_SOURCE_VALUE === (string) $atts[ self::ELEMENTOR_COLOR_SOURCE ];
	}

	/**
	 * Merge Elementor color overrides with magazine colors for unset fields.
	 *
	 * @param array{background: string, controls: string, text: string} $resolved_colors Colors after Elementor overrides.
	 * @param array{background: string, controls: string, text: string} $magazine_colors Magazine or global colors.
	 * @param array<string, mixed>                                      $atts            Widget attributes.
	 * @return array{background: string, controls: string, text: string}
	 */
	private static function merge_elementor_colors_with_magazine( array $resolved_colors, array $magazine_colors, array $atts ): array {
		foreach ( self::COLOR_ATTRIBUTE_MAP as $attribute => $color_key ) {
			if ( self::attribute_has_resolved_color( $atts, $attribute ) ) {
				continue;
			}

			$resolved_colors[ $color_key ] = $magazine_colors[ $color_key ];
		}

		return $resolved_colors;
	}

	/**
	 * Whether a color attribute contains a non-empty resolved value.
	 *
	 * @param array<string, mixed> $atts      Shortcode or widget attributes.
	 * @param string               $attribute Color attribute key.
	 */
	private static function attribute_has_resolved_color( array $atts, string $attribute ): bool {
		if ( ! array_key_exists( $attribute, $atts ) ) {
			return false;
		}

		$raw = $atts[ $attribute ];

		if ( ! is_scalar( $raw ) ) {
			return false;
		}

		$raw = trim( (string) $raw );

		if ( '' === $raw ) {
			return false;
		}

		$sanitized = sanitize_hex_color( $raw );

		return is_string( $sanitized ) && '' !== $sanitized;
	}
}
