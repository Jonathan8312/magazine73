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
	 * Viewer script module handle.
	 */
	public const VIEWER_HANDLE = 'magazine73-viewer';

	/**
	 * Admin script module handle.
	 */
	public const ADMIN_HANDLE = 'magazine73-admin';

	/**
	 * Manifest cache.
	 *
	 * @var array<string, mixed>|null
	 */
	private static ?array $manifest = null;

	/**
	 * Enqueued stylesheet paths.
	 *
	 * @var array<string, bool>
	 */
	private static array $enqueued_styles = array();

	/**
	 * Enqueued script module identifiers.
	 *
	 * @var array<string, bool>
	 */
	private static array $enqueued_modules = array();

	/**
	 * Enqueue the viewer entry assets.
	 */
	public static function enqueue_viewer(): void {
		wp_enqueue_script( 'wp-i18n' );
		self::enqueue_entry( self::VIEWER_HANDLE, self::VIEWER_ENTRY );
	}

	/**
	 * Enqueue the admin entry assets.
	 */
	public static function enqueue_admin(): void {
		self::enqueue_entry( self::ADMIN_HANDLE, self::ADMIN_ENTRY );
	}

	/**
	 * Enqueue a built entry script module and related stylesheets.
	 *
	 * @param string $handle Script module handle for the entry.
	 * @param string $entry  Manifest entry key.
	 */
	public static function enqueue_entry( string $handle, string $entry ): void {
		if ( ! function_exists( 'wp_enqueue_script_module' ) ) {
			return;
		}

		$resolved = self::resolve_entry_assets( $entry );

		if ( null === $resolved ) {
			return;
		}

		self::enqueue_stylesheets( $handle, $resolved['css'] );

		if ( '' === $resolved['src'] ) {
			return;
		}

		self::enqueue_script_module( $handle, $resolved['src'] );
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
	 * Resolve stylesheet dependencies and the entry script URL.
	 *
	 * Imported Vite chunks are not enqueued separately. The entry script loads
	 * generated chunks through native relative ES module imports.
	 *
	 * @param string $entry_key Manifest entry key.
	 * @return array{css: string[], src: string}|null
	 */
	public static function resolve_entry_assets( string $entry_key ): ?array {
		$manifest = self::get_manifest();

		if ( ! isset( $manifest[ $entry_key ] ) || ! is_array( $manifest[ $entry_key ] ) ) {
			return null;
		}

		$visited   = array();
		$css_paths = array();

		self::collect_stylesheets_for_entry(
			$entry_key,
			$manifest,
			$visited,
			$css_paths
		);

		$entry = $manifest[ $entry_key ];
		$src   = '';

		if ( isset( $entry['file'] ) && is_string( $entry['file'] ) && '' !== $entry['file'] ) {
			$src = self::build_asset_url( $entry['file'] );
		}

		if ( '' === $src && empty( $css_paths ) ) {
			return null;
		}

		return array(
			'css' => array_keys( $css_paths ),
			'src' => $src,
		);
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
	 * Recursively collect stylesheet paths for a manifest entry tree.
	 *
	 * @param string              $entry_key Manifest entry key.
	 * @param array<string,mixed> $manifest  Manifest data.
	 * @param array<string,bool>  $visited   Visited entry keys.
	 * @param array<string,bool>  $css_paths Stylesheet paths keyed by relative path.
	 */
	private static function collect_stylesheets_for_entry(
		string $entry_key,
		array $manifest,
		array &$visited,
		array &$css_paths
	): void {
		if ( isset( $visited[ $entry_key ] ) ) {
			return;
		}

		$visited[ $entry_key ] = true;

		if ( ! isset( $manifest[ $entry_key ] ) || ! is_array( $manifest[ $entry_key ] ) ) {
			return;
		}

		$entry = $manifest[ $entry_key ];

		if ( isset( $entry['imports'] ) && is_array( $entry['imports'] ) ) {
			foreach ( $entry['imports'] as $import_key ) {
				if ( ! is_string( $import_key ) || '' === $import_key ) {
					continue;
				}

				self::collect_stylesheets_for_entry(
					$import_key,
					$manifest,
					$visited,
					$css_paths
				);
			}
		}

		if ( ! isset( $entry['css'] ) || ! is_array( $entry['css'] ) ) {
			return;
		}

		foreach ( $entry['css'] as $stylesheet ) {
			if ( ! is_string( $stylesheet ) || '' === $stylesheet ) {
				continue;
			}

			$stylesheet_path = self::build_asset_path( $stylesheet );

			if ( '' !== $stylesheet_path ) {
				$css_paths[ $stylesheet_path ] = true;
			}
		}
	}

	/**
	 * Enqueue deduplicated stylesheets for an asset group.
	 *
	 * @param string   $handle_prefix Handle prefix.
	 * @param string[] $css_paths     Stylesheet paths relative to the plugin root.
	 */
	private static function enqueue_stylesheets( string $handle_prefix, array $css_paths ): void {
		foreach ( $css_paths as $index => $path ) {
			self::enqueue_stylesheet( $handle_prefix, $path, (int) $index );
		}
	}

	/**
	 * Enqueue a stylesheet once.
	 *
	 * @param string $handle_prefix Handle prefix.
	 * @param string $path          Relative asset path.
	 * @param int    $index         Stylesheet index for handle uniqueness.
	 */
	private static function enqueue_stylesheet( string $handle_prefix, string $path, int $index ): void {
		if ( isset( self::$enqueued_styles[ $path ] ) ) {
			return;
		}

		$style_handle = sanitize_key( $handle_prefix . '-css-' . $index );

		wp_enqueue_style(
			$style_handle,
			MAGAZINE73_URL . $path,
			array(),
			MAGAZINE73_VERSION
		);

		self::$enqueued_styles[ $path ] = true;
	}

	/**
	 * Enqueue a script module once.
	 *
	 * @param string $handle Script module handle.
	 * @param string $src    Module source URL.
	 */
	private static function enqueue_script_module( string $handle, string $src ): void {
		if ( isset( self::$enqueued_modules[ $handle ] ) ) {
			return;
		}

		wp_enqueue_script_module(
			$handle,
			$src,
			array(),
			MAGAZINE73_VERSION
		);

		if ( function_exists( 'load_script_module_textdomain' ) ) {
			load_script_module_textdomain( $handle, 'magazine73', MAGAZINE73_PATH . 'languages' );
		} elseif ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( $handle, 'magazine73', MAGAZINE73_PATH . 'languages' );
		}

		self::$enqueued_modules[ $handle ] = true;
	}

	/**
	 * Build a plugin-relative asset path.
	 *
	 * @param string $relative_path Manifest-relative path.
	 */
	private static function build_asset_path( string $relative_path ): string {
		$relative_path = ltrim( $relative_path, '/' );

		if ( '' === $relative_path ) {
			return '';
		}

		return 'assets/dist/' . $relative_path;
	}

	/**
	 * Build an absolute asset URL.
	 *
	 * @param string $relative_path Manifest-relative path.
	 */
	private static function build_asset_url( string $relative_path ): string {
		$asset_path = self::build_asset_path( $relative_path );

		if ( '' === $asset_path ) {
			return '';
		}

		return MAGAZINE73_URL . $asset_path;
	}
}
