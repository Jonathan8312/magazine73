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
	 * Admin script handle.
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
	 * Whether the admin module script tag filter is registered.
	 *
	 * @var bool
	 */
	private static bool $admin_module_filter_registered = false;

	/**
	 * Enqueue the viewer entry assets.
	 */
	public static function enqueue_viewer(): void {
		if ( ! function_exists( 'wp_enqueue_script_module' ) ) {
			return;
		}

		$resolved = self::resolve_entry_assets( self::VIEWER_ENTRY );

		if ( null === $resolved ) {
			return;
		}

		self::enqueue_stylesheets( self::VIEWER_HANDLE, $resolved['css'] );

		if ( '' === $resolved['src'] ) {
			return;
		}

		wp_enqueue_script_module(
			self::VIEWER_HANDLE,
			$resolved['src'],
			array(),
			null
		);
	}

	/**
	 * Enqueue the admin entry assets.
	 */
	public static function enqueue_admin(): void {
		$resolved = self::resolve_entry_assets( self::ADMIN_ENTRY );

		if ( null === $resolved ) {
			return;
		}

		self::enqueue_stylesheets( self::ADMIN_HANDLE, $resolved['css'] );

		if ( '' === $resolved['src'] ) {
			return;
		}

		self::register_admin_module_loader();

		// phpcs:disable WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Hashed Vite filenames provide cache busting.
		wp_enqueue_script(
			self::ADMIN_HANDLE,
			$resolved['src'],
			array(),
			null,
			true
		);
		// phpcs:enable WordPress.WP.EnqueuedResourceParameters.MissingVersion
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
			'css' => array_values( $css_paths ),
			'src' => $src,
		);
	}

	/**
	 * Convert Magazine73 admin script tags to ES modules on WordPress 6.5.
	 *
	 * WordPress 6.5 exposes Script Modules on the frontend, but admin support
	 * arrived in WordPress 6.6. This filter is scoped to Magazine73 admin handles
	 * so only plugin admin entry scripts are affected.
	 *
	 * @param string $tag    Script tag HTML.
	 * @param string $handle Script handle.
	 * @param string $src    Script source URL.
	 */
	public static function set_admin_script_type_module( string $tag, string $handle, string $src ): string {
		unset( $src );

		if ( self::ADMIN_HANDLE !== $handle ) {
			return $tag;
		}

		if ( false !== strpos( $tag, ' type=' ) ) {
			return $tag;
		}

		return str_replace( '<script ', '<script type="module" ', $tag );
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
	 * Register the scoped admin script tag filter once.
	 */
	private static function register_admin_module_loader(): void {
		if ( self::$admin_module_filter_registered ) {
			return;
		}

		add_filter( 'script_loader_tag', array( self::class, 'set_admin_script_type_module' ), 10, 3 );
		self::$admin_module_filter_registered = true;
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
