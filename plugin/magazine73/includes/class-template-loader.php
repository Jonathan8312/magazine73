<?php
/**
 * Plugin template loader.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Locates and loads Magazine73 templates with theme override support.
 */
final class Template_Loader {

	/**
	 * Locate a template file in the theme or plugin.
	 *
	 * @param string $template_name Template file name.
	 */
	public static function locate_template( string $template_name ): string {
		$template_name = ltrim( $template_name, '/' );

		if ( '' === $template_name ) {
			return '';
		}

		$theme_template = trailingslashit( get_stylesheet_directory() ) . 'magazine73/' . $template_name;

		if ( is_readable( $theme_template ) ) {
			return $theme_template;
		}

		$plugin_template = MAGAZINE73_PATH . 'templates/' . $template_name;

		if ( is_readable( $plugin_template ) ) {
			return $plugin_template;
		}

		return '';
	}

	/**
	 * Load a template and pass variables into its scope.
	 *
	 * @param string               $template_name Template file name.
	 * @param array<string, mixed> $args          Template variables.
	 */
	public static function load_template( string $template_name, array $args = array() ): void {
		$template_path = self::locate_template( $template_name );

		if ( '' === $template_path ) {
			return;
		}

		if ( ! empty( $args ) ) {
			// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Template variables are intentionally extracted.
			extract( $args, EXTR_SKIP );
		}

		include $template_path;
	}
}
