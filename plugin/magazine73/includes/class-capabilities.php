<?php
/**
 * Magazine role capabilities.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Manages role capabilities for magazines.
 */
final class Capabilities {

	/**
	 * Capability required to manage plugin settings.
	 */
	public const MANAGE_SETTINGS_CAP = 'manage_magazine73_settings';

	/**
	 * Grant magazine capabilities to supported roles.
	 */
	public static function activate(): void {
		self::ensure_role_capabilities();

		( new Post_Type() )->register();
		flush_rewrite_rules();

		Data_Lifecycle::maybe_run_migrations();
	}

	/**
	 * Ensure supported roles have the expected Magazine73 capabilities.
	 *
	 * Safe to call on activation and during data migrations.
	 */
	public static function ensure_role_capabilities(): void {
		if ( ! function_exists( 'get_role' ) ) {
			return;
		}

		$administrator = get_role( 'administrator' );
		$editor        = get_role( 'editor' );
		$capabilities  = array_values( Post_Type::get_capabilities() );

		if ( $administrator ) {
			foreach ( array_unique( $capabilities ) as $capability ) {
				$administrator->add_cap( $capability );
			}

			$administrator->add_cap( self::MANAGE_SETTINGS_CAP );
		}

		if ( $editor ) {
			foreach ( self::get_editor_capabilities() as $capability ) {
				$editor->add_cap( $capability );
			}
		}
	}

	/**
	 * Remove plugin capabilities from supported roles.
	 */
	public static function deactivate(): void {
		$roles        = array( get_role( 'administrator' ), get_role( 'editor' ) );
		$capabilities = array_unique( array_values( Post_Type::get_capabilities() ) );

		foreach ( $roles as $role ) {
			if ( ! $role ) {
				continue;
			}

			foreach ( $capabilities as $capability ) {
				$role->remove_cap( $capability );
			}
		}

		$administrator = get_role( 'administrator' );

		if ( $administrator ) {
			$administrator->remove_cap( self::MANAGE_SETTINGS_CAP );
		}

		flush_rewrite_rules();
	}

	/**
	 * Return editor capabilities excluding deletion permissions.
	 *
	 * @return string[]
	 */
	private static function get_editor_capabilities(): array {
		$capabilities = array_values( Post_Type::get_capabilities() );

		return array_values(
			array_unique(
				array_filter(
					$capabilities,
					static function ( string $capability ): bool {
						return 0 !== strpos( $capability, 'delete_' );
					}
				)
			)
		);
	}
}
