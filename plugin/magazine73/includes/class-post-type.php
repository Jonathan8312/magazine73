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
		add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_block_editor' ), 10, 2 );
	}

	/**
	 * Register the magazine custom post type.
	 */
	public function register(): void {
		$labels = array(
			'name'                     => __( 'Magazines', 'magazine73' ),
			'singular_name'            => __( 'Magazine', 'magazine73' ),
			'menu_name'                => __( 'Magazines', 'magazine73' ),
			'name_admin_bar'           => __( 'Magazine', 'magazine73' ),
			'add_new'                  => __( 'Add New', 'magazine73' ),
			'add_new_item'             => __( 'Add New Magazine', 'magazine73' ),
			'new_item'                 => __( 'New Magazine', 'magazine73' ),
			'edit_item'                => __( 'Edit Magazine', 'magazine73' ),
			'view_item'                => __( 'View Magazine', 'magazine73' ),
			'all_items'                => __( 'All Magazines', 'magazine73' ),
			'search_items'             => __( 'Search Magazines', 'magazine73' ),
			'parent_item_colon'        => __( 'Parent Magazines:', 'magazine73' ),
			'not_found'                => __( 'No magazines found.', 'magazine73' ),
			'not_found_in_trash'       => __( 'No magazines found in Trash.', 'magazine73' ),
			'archives'                 => __( 'Magazine Archives', 'magazine73' ),
			'attributes'               => __( 'Magazine Attributes', 'magazine73' ),
			'insert_into_item'         => __( 'Insert into magazine', 'magazine73' ),
			'uploaded_to_this_item'    => __( 'Uploaded to this magazine', 'magazine73' ),
			'filter_items_list'        => __( 'Filter magazines list', 'magazine73' ),
			'items_list_navigation'    => __( 'Magazines list navigation', 'magazine73' ),
			'items_list'               => __( 'Magazines list', 'magazine73' ),
			'item_published'           => __( 'Magazine published.', 'magazine73' ),
			'item_published_privately' => __( 'Magazine published privately.', 'magazine73' ),
			'item_reverted_to_draft'   => __( 'Magazine reverted to draft.', 'magazine73' ),
			'item_scheduled'           => __( 'Magazine scheduled.', 'magazine73' ),
			'item_updated'             => __( 'Magazine updated.', 'magazine73' ),
		);

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => $labels,
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => true,
				'menu_position'       => 25,
				'menu_icon'           => 'dashicons-book-alt',
				'capability_type'     => array( 'magazine73_magazine', 'magazine73_magazines' ),
				'capabilities'        => self::get_capabilities(),
				'map_meta_cap'        => true,
				'hierarchical'        => false,
				'supports'            => array( 'title', 'revisions' ),
				'has_archive'         => false,
				'rewrite'             => array(
					'slug'       => 'revistas',
					'with_front' => false,
				),
				'query_var'           => true,
				'can_export'          => true,
				'delete_with_user'    => false,
				'exclude_from_search' => false,
			),
		);
	}

	/**
	 * Use the classic editor screen for magazines.
	 *
	 * Magazines are managed through custom metaboxes and the media library,
	 * not the block editor.
	 *
	 * @param bool   $use_block_editor Whether the block editor is enabled.
	 * @param string $post_type        Current post type.
	 */
	public function disable_block_editor( bool $use_block_editor, string $post_type ): bool {
		if ( self::POST_TYPE === $post_type ) {
			return false;
		}

		return $use_block_editor;
	}

	/**
	 * Return the custom capabilities used by magazines.
	 *
	 * @return array<string, string>
	 */
	public static function get_capabilities(): array {
		return array(
			'edit_post'              => 'edit_magazine73_magazine',
			'read_post'              => 'read_magazine73_magazine',
			'delete_post'            => 'delete_magazine73_magazine',
			'edit_posts'             => 'edit_magazine73_magazines',
			'edit_others_posts'      => 'edit_others_magazine73_magazines',
			'publish_posts'          => 'publish_magazine73_magazines',
			'read_private_posts'     => 'read_private_magazine73_magazines',
			'delete_posts'           => 'delete_magazine73_magazines',
			'delete_private_posts'   => 'delete_private_magazine73_magazines',
			'delete_published_posts' => 'delete_published_magazine73_magazines',
			'delete_others_posts'    => 'delete_others_magazine73_magazines',
			'edit_private_posts'     => 'edit_private_magazine73_magazines',
			'edit_published_posts'   => 'edit_published_magazine73_magazines',
			'create_posts'           => 'edit_magazine73_magazines',
		);
	}
}
