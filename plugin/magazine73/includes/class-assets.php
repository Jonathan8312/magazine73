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
		self::enqueue_entry( self::VIEWER_HANDLE, self::VIEWER_ENTRY );
	}

	/**
	 * Enqueue the admin entry assets.
	 */
	public static function enqueue_admin(): void {
		self::enqueue_entry( self::ADMIN_HANDLE, self::ADMIN_ENTRY );
	}

	/**
	 * Register a built entry, its imported modules, and stylesheet dependencies.
	 *
	 * @param string $handle Script module handle for the entry.
	 * @param string $entry  Manifest entry key.
	 */
	public static function enqueue_entry( string $handle, string $entry ): void {
		if ( ! function_exists( 'wp_enqueue_script_module' ) ) {
			return;
		}

		$resolved = self::resolve_entry( $entry );

		if ( null === $resolved ) {
			return;
		}

		foreach ( $resolved['css'] as $index => $stylesheet_path ) {
			self::enqueue_stylesheet( $handle, $stylesheet_path, (int) $index );
		}

		foreach ( $resolved['modules'] as $module_key => $module ) {
			if ( $module_key === $resolved['entry_key'] ) {
				continue;
			}

			self::enqueue_script_module(
				$module['id'],
				$module['src'],
				$module['deps']
			);
		}

		$entry_module = $resolved['modules'][ $resolved['entry_key'] ] ?? null;

		if ( null === $entry_module ) {
			return;
		}

		self::enqueue_script_module(
			$handle,
			$entry_module['src'],
			$entry_module['deps']
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
	 * Resolve an entry and its imported manifest dependencies.
	 *
	 * @param string $entry_key Manifest entry key.
	 * @return array{
	 *     entry_key: string,
	 *     css: string[],
	 *     modules: array<string, array{id: string, src: string, deps: array<int|string, array{id: string, import?: string}>}>
	 * }|null
	 */
	public static function resolve_entry( string $entry_key ): ?array {
		$manifest = self::get_manifest();

		if ( ! isset( $manifest[ $entry_key ] ) || ! is_array( $manifest[ $entry_key ] ) ) {
			return null;
		}

		$visited   = array();
		$css_paths = array();
		$modules   = array();

		self::collect_manifest_entry(
			$entry_key,
			$manifest,
			$visited,
			$css_paths,
			$modules
		);

		if ( ! isset( $modules[ $entry_key ] ) ) {
			return null;
		}

		foreach ( $modules as $module_key => $module ) {
			$modules[ $module_key ]['deps'] = self::get_module_dependencies(
				$module_key,
				$manifest,
				$modules
			);
		}

		return array(
			'entry_key' => $entry_key,
			'css'       => array_values( $css_paths ),
			'modules'   => $modules,
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
	 * Recursively collect CSS and module data for a manifest entry.
	 *
	 * @param string                                                                                                     $entry_key Manifest entry key.
	 * @param array<string, mixed>                                                                                       $manifest  Manifest data.
	 * @param array<string, bool>                                                                                        $visited   Visited entry keys.
	 * @param array<string, bool>                                                                                        $css_paths Stylesheet paths keyed by relative path.
	 * @param array<string, array{id: string, src: string, deps: array<int|string, array{id: string, import?: string}>}> $modules Module data keyed by manifest entry.
	 */
	private static function collect_manifest_entry(
		string $entry_key,
		array $manifest,
		array &$visited,
		array &$css_paths,
		array &$modules
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

				self::collect_manifest_entry(
					$import_key,
					$manifest,
					$visited,
					$css_paths,
					$modules
				);
			}
		}

		if ( isset( $entry['css'] ) && is_array( $entry['css'] ) ) {
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

		if ( ! isset( $entry['file'] ) || ! is_string( $entry['file'] ) || '' === $entry['file'] ) {
			return;
		}

		$module_src = self::build_asset_url( $entry['file'] );

		if ( '' === $module_src ) {
			return;
		}

		$modules[ $entry_key ] = array(
			'id'   => self::get_module_id( $entry_key ),
			'src'  => $module_src,
			'deps' => array(),
		);
	}

	/**
	 * Resolve imported module dependencies for a manifest entry.
	 *
	 * @param string                                                                                                     $entry_key Manifest entry key.
	 * @param array<string, mixed>                                                                                       $manifest  Manifest data.
	 * @param array<string, array{id: string, src: string, deps: array<int|string, array{id: string, import?: string}>}> $modules   Collected modules.
	 * @return array<int|string, array{id: string, import?: string}>
	 */
	private static function get_module_dependencies(
		string $entry_key,
		array $manifest,
		array $modules
	): array {
		if ( ! isset( $manifest[ $entry_key ] ) || ! is_array( $manifest[ $entry_key ] ) ) {
			return array();
		}

		$entry = $manifest[ $entry_key ];

		if ( ! isset( $entry['imports'] ) || ! is_array( $entry['imports'] ) ) {
			return array();
		}

		$dependencies = array();

		foreach ( $entry['imports'] as $import_key ) {
			if ( ! is_string( $import_key ) || '' === $import_key ) {
				continue;
			}

			if ( ! isset( $modules[ $import_key ] ) ) {
				continue;
			}

			$module_id = $modules[ $import_key ]['id'];

			$dependencies[ $module_id ] = array(
				'id' => $module_id,
			);
		}

		return array_values( $dependencies );
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
	 * @param string                                                $id   Script module identifier.
	 * @param string                                                $src  Module source URL.
	 * @param array<int|string, array{id: string, import?: string}> $deps Module dependencies.
	 */
	private static function enqueue_script_module( string $id, string $src, array $deps ): void {
		if ( isset( self::$enqueued_modules[ $id ] ) ) {
			return;
		}

		wp_enqueue_script_module(
			$id,
			$src,
			$deps,
			MAGAZINE73_VERSION
		);

		self::$enqueued_modules[ $id ] = true;
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

	/**
	 * Build a stable script module identifier for a manifest entry.
	 *
	 * @param string $entry_key Manifest entry key.
	 */
	private static function get_module_id( string $entry_key ): string {
		return 'magazine73-module-' . substr( md5( $entry_key ), 0, 12 );
	}
}
