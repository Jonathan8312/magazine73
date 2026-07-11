<?php
/**
 * Built asset manifest helpers.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Resolves and enqueues Vite-built plugin assets.
 */
final class Assets {

	/**
	 * Viewer manifest entry key.
	 */
	public const VIEWER_ENTRY = 'assets/src/entries/viewer.js';

	/**
	 * Admin manifest entry key.
	 */
	public const ADMIN_ENTRY = 'assets/src/entries/admin.js';

	/**
	 * Manifest cache.
	 *
	 * @var array<string, array<string, mixed>>|null
	 */
	private static ?array $manifest = null;

	/**
	 * Register a built entry and its stylesheet dependencies.
	 *
	 * @param string $handle Script handle.
	 * @param string $entry  Manifest entry key.
	 */
	public static function enqueue_entry( string $handle, string $entry ): void {
		$manifest_entry = self::get_manifest_entry( $entry );

		if ( null === $manifest_entry ) {
			return;
		}

		$script_path = self::get_entry_path( $manifest_entry, 'file' );

		if ( '' === $script_path ) {
			return;
		}

		$style_path = self::get_entry_path( $manifest_entry, 'css' );

		if ( '' !== $style_path ) {
			wp_enqueue_style(
				$handle,
				MAGAZINE73_URL . $style_path,
				array(),
				MAGAZINE73_VERSION
			);
		}

		wp_enqueue_script(
			$handle,
			MAGAZINE73_URL . $script_path,
			array(),
			MAGAZINE73_VERSION,
			true
		);
	}

	/**
	 * Get a manifest entry by key.
	 *
	 * @param string $entry Manifest entry key.
	 * @return array<string, mixed>|null
	 */
	public static function get_manifest_entry( string $entry ): ?array {
		$manifest = self::get_manifest();

		if ( ! isset( $manifest[ $entry ] ) ) {
			return null;
		}

		$entry_data = $manifest[ $entry ];

		return is_array( $entry_data ) ? $entry_data : null;
	}

	/**
	 * Load the Vite manifest from disk.
	 *
	 * @return array<string, mixed>
	 */
	private static function get_manifest(): array {
		if ( null !== self::$manifest ) {
			return self::$manifest;
		}

		$manifest_path = MAGAZINE73_PATH . 'assets/dist/manifest.json';

		if ( ! is_readable( $manifest_path ) ) {
			self::$manifest = array();
			return self::$manifest;
		}

		$decoded = function_exists( 'wp_json_file_decode' )
			? wp_json_file_decode(
				$manifest_path,
				array(
					'associative' => true,
				)
			)
			: null;

		if ( ! is_array( $decoded ) ) {
			self::$manifest = array();
			return self::$manifest;
		}

		self::$manifest = $decoded;

		return self::$manifest;
	}

	/**
	 * Resolve a relative asset path from a manifest entry.
	 *
	 * @param array<string, mixed> $entry Manifest entry.
	 * @param string               $type  Asset type key.
	 */
	private static function get_entry_path( array $entry, string $type ): string {
		if ( 'file' === $type && isset( $entry['file'] ) && is_string( $entry['file'] ) ) {
			return 'assets/dist/' . ltrim( $entry['file'], '/' );
		}

		if ( 'css' === $type && isset( $entry['css'] ) && is_array( $entry['css'] ) ) {
			$stylesheet = $entry['css'][0] ?? '';

			if ( is_string( $stylesheet ) && '' !== $stylesheet ) {
				return 'assets/dist/' . ltrim( $stylesheet, '/' );
			}
		}

		return '';
	}
}
