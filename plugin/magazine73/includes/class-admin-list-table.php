<?php
/**
 * Magazine admin list table.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Customizes the magazines list table.
 */
final class Admin_List_Table {

	/**
	 * Cover column key.
	 */
	public const COLUMN_COVER = 'magazine73_cover';

	/**
	 * Edition column key.
	 */
	public const COLUMN_EDITION = 'magazine73_edition';

	/**
	 * Shortcode column key.
	 */
	public const COLUMN_SHORTCODE = 'magazine73_shortcode';

	/**
	 * Register list table hooks.
	 */
	public function init(): void {
		add_filter( 'manage_' . Post_Type::POST_TYPE . '_posts_columns', array( $this, 'register_columns' ) );
		add_action( 'manage_' . Post_Type::POST_TYPE . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_filter( 'manage_edit-' . Post_Type::POST_TYPE . '_sortable_columns', array( $this, 'register_sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'set_default_sort_order' ) );
	}

	/**
	 * Register custom list table columns.
	 *
	 * @param array<string, string> $columns Existing columns.
	 * @return array<string, string>
	 */
	public function register_columns( array $columns ): array {
		$new_columns = array();

		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new_columns[ self::COLUMN_COVER ] = __( 'Cover', 'magazine73' );
			}

			$new_columns[ $key ] = $label;

			if ( 'title' === $key ) {
				$new_columns[ self::COLUMN_EDITION ]   = __( 'Edition', 'magazine73' );
				$new_columns[ self::COLUMN_SHORTCODE ] = __( 'Shortcode', 'magazine73' );
			}
		}

		return $new_columns;
	}

	/**
	 * Render a custom list table column.
	 *
	 * @param string $column_name Column key.
	 * @param int    $post_id     Post ID.
	 */
	public function render_column( string $column_name, int $post_id ): void {
		switch ( $column_name ) {
			case self::COLUMN_COVER:
				$cover_id = Magazine_Pages::get_cover_attachment_id( $post_id );

				if ( $cover_id > 0 ) {
					echo wp_get_attachment_image( $cover_id, array( 48, 48 ), true, array( 'class' => 'magazine73-list-cover' ) );
					break;
				}

				echo '<span aria-hidden="true">—</span><span class="screen-reader-text">' . esc_html__( 'No cover yet', 'magazine73' ) . '</span>';
				break;

			case self::COLUMN_EDITION:
				echo esc_html( Magazine_Meta::format_edition_display( Magazine_Meta::get_edition( $post_id ) ) );
				break;

			case self::COLUMN_SHORTCODE:
				echo '<code>' . esc_html( Magazine_Meta::get_shortcode( $post_id ) ) . '</code>';
				break;
		}
	}

	/**
	 * Register sortable columns.
	 *
	 * @param array<string, string> $columns Sortable columns.
	 * @return array<string, string>
	 */
	public function register_sortable_columns( array $columns ): array {
		$columns[ self::COLUMN_EDITION ] = self::COLUMN_EDITION;

		return $columns;
	}

	/**
	 * Show newest magazines first and support edition sorting.
	 *
	 * @param \WP_Query $query Current query.
	 */
	public function set_default_sort_order( \WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( Post_Type::POST_TYPE !== $query->get( 'post_type' ) ) {
			return;
		}

		if ( self::COLUMN_EDITION === $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', Magazine_Meta::EDITION_META_KEY );
			$query->set( 'orderby', 'meta_value' );
			return;
		}

		if ( '' === $query->get( 'orderby' ) ) {
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'DESC' );
		}
	}
}
