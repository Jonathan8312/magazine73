<?php
/**
 * Viewer settings storage and resolution.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Manages global and per-magazine viewer settings.
 */
final class Viewer_Settings {

	/**
	 * Global settings option key.
	 */
	public const OPTION_KEY = 'magazine73_viewer_settings';

	/**
	 * Per-magazine global inheritance meta key.
	 */
	public const USE_GLOBAL_META_KEY = 'magazine73_use_global_settings';

	/**
	 * Per-magazine override settings meta key.
	 */
	public const OVERRIDES_META_KEY = 'magazine73_viewer_settings';

	/**
	 * Settings option group.
	 */
	public const SETTINGS_GROUP = 'magazine73_settings';

	/**
	 * Color setting keys.
	 *
	 * @var string[]
	 */
	private const COLOR_KEYS = array(
		'background',
		'controls',
		'text',
	);

	/**
	 * Control visibility keys.
	 *
	 * @var string[]
	 */
	private const CONTROL_KEYS = array(
		'previous',
		'next',
		'counter',
		'fullscreen',
		'download',
		'zoom',
		'thumbnails',
	);

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	/**
	 * Register per-magazine viewer settings meta.
	 */
	public function register_meta(): void {
		register_post_meta(
			Post_Type::POST_TYPE,
			self::USE_GLOBAL_META_KEY,
			array(
				'type'              => 'boolean',
				'description'       => __( 'Whether the magazine inherits global viewer settings.', 'magazine73' ),
				'single'            => true,
				'default'           => true,
				'sanitize_callback' => array( $this, 'sanitize_use_global_settings' ),
				'show_in_rest'      => false,
				'auth_callback'     => array( $this, 'can_edit_magazine_settings' ),
			)
		);

		register_post_meta(
			Post_Type::POST_TYPE,
			self::OVERRIDES_META_KEY,
			array(
				'type'              => 'array',
				'description'       => __( 'Per-magazine viewer setting overrides.', 'magazine73' ),
				'single'            => true,
				'default'           => array(),
				'sanitize_callback' => array( $this, 'sanitize_magazine_overrides' ),
				'show_in_rest'      => false,
				'auth_callback'     => array( $this, 'can_edit_magazine_settings' ),
			)
		);
	}

	/**
	 * Return neutral default viewer settings.
	 *
	 * @return array{colors: array{background: string, controls: string, text: string}, controls: array<string, bool>}
	 */
	public static function get_defaults(): array {
		return array(
			'colors'   => array(
				'background' => '#f5f5f5',
				'controls'   => '#ffffff',
				'text'       => '',
			),
			'controls' => array(
				'previous'   => true,
				'next'       => true,
				'counter'    => true,
				'fullscreen' => true,
				'download'   => true,
				'zoom'       => true,
				'thumbnails' => true,
			),
		);
	}

	/**
	 * Get stored global viewer settings.
	 *
	 * @return array{colors: array{background: string, controls: string, text: string}, controls: array<string, bool>}
	 */
	public static function get_global(): array {
		$stored = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return self::sanitize_settings( $stored, false );
	}

	/**
	 * Whether a magazine inherits global viewer settings.
	 *
	 * @param int $post_id Magazine post ID.
	 */
	public static function uses_global_settings( int $post_id ): bool {
		$stored = get_post_meta( $post_id, self::USE_GLOBAL_META_KEY, true );

		if ( '' === $stored ) {
			return true;
		}

		return rest_sanitize_boolean( $stored );
	}

	/**
	 * Get stored per-magazine override settings.
	 *
	 * @param int $post_id Magazine post ID.
	 * @return array{colors: array{background: string, controls: string, text: string}, controls: array<string, bool>}
	 */
	public static function get_magazine_overrides( int $post_id ): array {
		$stored = get_post_meta( $post_id, self::OVERRIDES_META_KEY, true );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return self::sanitize_settings( $stored, false );
	}

	/**
	 * Resolve effective viewer settings for a magazine.
	 *
	 * @param int $post_id Magazine post ID.
	 * @return array{colors: array{background: string, controls: string, text: string}, controls: array<string, bool>}
	 */
	public static function get_for_magazine( int $post_id ): array {
		if ( self::uses_global_settings( $post_id ) ) {
			return self::get_global();
		}

		return self::get_magazine_overrides( $post_id );
	}

	/**
	 * Sanitize global settings submitted through the Settings API.
	 *
	 * @param mixed $input Raw submitted settings.
	 * @return array{colors: array{background: string, controls: string, text: string}, controls: array<string, bool>}
	 */
	public static function sanitize_global( $input ): array {
		if ( ! is_array( $input ) ) {
			return self::get_defaults();
		}

		return self::sanitize_settings( $input, true );
	}

	/**
	 * Sanitize the use-global-settings meta value.
	 *
	 * @param mixed $value Raw meta value.
	 */
	public function sanitize_use_global_settings( $value ): bool {
		return rest_sanitize_boolean( $value );
	}

	/**
	 * Sanitize per-magazine override settings.
	 *
	 * @param mixed $input Raw override settings.
	 * @return array{colors: array{background: string, controls: string, text: string}, controls: array<string, bool>}
	 */
	public function sanitize_magazine_overrides( $input ): array {
		if ( ! is_array( $input ) ) {
			return self::get_defaults();
		}

		return self::sanitize_settings( $input, true );
	}

	/**
	 * Determine whether the current user can edit magazine viewer settings.
	 *
	 * @param bool   $allowed   Whether the user can edit the meta value.
	 * @param string $meta_key  Meta key.
	 * @param int    $post_id   Post ID.
	 */
	public function can_edit_magazine_settings( bool $allowed, string $meta_key, int $post_id ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
		unset( $allowed, $meta_key );

		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * Return color setting keys.
	 *
	 * @return string[]
	 */
	public static function get_color_keys(): array {
		return self::COLOR_KEYS;
	}

	/**
	 * Return control visibility keys.
	 *
	 * @return string[]
	 */
	public static function get_control_keys(): array {
		return self::CONTROL_KEYS;
	}

	/**
	 * Sanitize a settings array.
	 *
	 * @param array<string, mixed> $input                           Raw settings.
	 * @param bool                 $treat_missing_controls_as_false Whether unchecked controls should be false.
	 * @return array{colors: array{background: string, controls: string, text: string}, controls: array<string, bool>}
	 */
	private static function sanitize_settings( array $input, bool $treat_missing_controls_as_false ): array {
		$defaults = self::get_defaults();
		$colors   = isset( $input['colors'] ) && is_array( $input['colors'] ) ? $input['colors'] : array();
		$controls = isset( $input['controls'] ) && is_array( $input['controls'] ) ? $input['controls'] : array();

		$sanitized_colors = array();

		foreach ( self::COLOR_KEYS as $color_key ) {
			$default_value = $defaults['colors'][ $color_key ];
			$raw_value     = $colors[ $color_key ] ?? $default_value;

			if ( 'text' === $color_key ) {
				$sanitized_colors[ $color_key ] = self::sanitize_optional_color( $raw_value );
				continue;
			}

			$sanitized_colors[ $color_key ] = self::sanitize_required_color( $raw_value, $default_value );
		}

		$sanitized_controls = array();

		foreach ( self::CONTROL_KEYS as $control_key ) {
			if ( $treat_missing_controls_as_false ) {
				$sanitized_controls[ $control_key ] = self::sanitize_boolean( $controls[ $control_key ] ?? false );
				continue;
			}

			$sanitized_controls[ $control_key ] = self::sanitize_boolean(
				$controls[ $control_key ] ?? $defaults['controls'][ $control_key ]
			);
		}

		return array(
			'colors'   => $sanitized_colors,
			'controls' => $sanitized_controls,
		);
	}

	/**
	 * Sanitize a required color value.
	 *
	 * @param mixed  $value         Raw color value.
	 * @param string $default_value Fallback color.
	 */
	private static function sanitize_required_color( $value, string $default_value ): string {
		if ( ! is_string( $value ) ) {
			return $default_value;
		}

		$sanitized = sanitize_hex_color( $value );

		return is_string( $sanitized ) && '' !== $sanitized ? $sanitized : $default_value;
	}

	/**
	 * Sanitize an optional color value.
	 *
	 * @param mixed $value Raw color value.
	 */
	private static function sanitize_optional_color( $value ): string {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$value = trim( $value );

		if ( '' === $value ) {
			return '';
		}

		$sanitized = sanitize_hex_color( $value );

		return is_string( $sanitized ) ? $sanitized : '';
	}

	/**
	 * Sanitize a boolean value.
	 *
	 * @param mixed $value Raw boolean value.
	 */
	private static function sanitize_boolean( $value ): bool {
		return rest_sanitize_boolean( $value );
	}
}
