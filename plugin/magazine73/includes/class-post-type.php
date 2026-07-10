<?php
/**
 * Magazine custom post type.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the magazine content type.
 */
final class Post_Type {

	/**
	 * Custom post type key.
	 */
	public const POST_TYPE = 'magazine73_magazine';

	/**
	 * Register WordPress hooks.
	 */
	public function init(): void {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Register the magazine custom post type.
	 */