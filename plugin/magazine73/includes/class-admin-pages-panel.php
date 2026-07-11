<?php
/**
 * Magazine pages admin panel.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Renders and saves the magazine pages panel.
 */
final class Admin_Pages_Panel {

	/**
	 * Metabox identifier.
	 */
	private const METABOX_ID = 'magazine73_pages_panel';

	/**
	 * Register admin hooks.
	 */
	public function init(): void {
		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );
		add_action( 'save_post_' . Post_Type::POST_TYPE, array( $this, 'save_metabox' ), 10, 2 );
	}

	/**
	 * Register the pages panel metabox.
	 */
	public function register_metabox(): void {
		add_meta_box(
			self::METABOX_ID,
			__( 'Magazine Pages', 'magazine73' ),
			array( $this, 'render_metabox' ),
			Post_Type::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render the magazine pages panel.
	 *
	 * @param \WP_Post $post Current post object.
	 */
	public function render_metabox( \WP_Post $post ): void {
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		wp_nonce_field( 'magazine73_save_pages_panel', 'magazine73_pages_panel_nonce' );

		$page_items = Magazine_Pages::get_page_items( $post->ID );
		$stats      = Magazine_Pages::get_stats( $post->ID );
		?>
		<div class="magazine73-admin-panel magazine73-pages-panel" data-magazine73-admin>
			<p class="magazine73-pages-panel__intro">
				<?php esc_html_e( 'Add WebP images from the Media Library or upload new files. Pages are sorted automatically by filename.', 'magazine73' ); ?>
			</p>
			<p>
				<button type="button" class="button button-secondary" id="magazine73-add-pages">
					<?php esc_html_e( 'Add or Upload Pages', 'magazine73' ); ?>
				</button>
			</p>
			<ul class="magazine73-pages-panel__list" id="magazine73-pages-list">
				<?php foreach ( $page_items as $item ) : ?>
					<li class="magazine73-pages-panel__item<?php echo $item['is_large'] ? ' magazine73-pages-panel__item--large' : ''; ?>" data-attachment-id="<?php echo esc_attr( (string) $item['id'] ); ?>">
						<?php if ( '' !== $item['thumb_url'] ) : ?>
							<img class="magazine73-pages-panel__thumb" src="<?php echo esc_url( $item['thumb_url'] ); ?>" alt="" />
						<?php endif; ?>
						<span class="magazine73-pages-panel__filename"><?php echo esc_html( $item['filename'] ); ?></span>
						<?php if ( $item['is_large'] ) : ?>
							<span class="magazine73-pages-panel__warning"><?php esc_html_e( 'Larger than 300 KB', 'magazine73' ); ?></span>
						<?php endif; ?>
						<button type="button" class="button-link-delete magazine73-pages-panel__remove"><?php esc_html_e( 'Remove', 'magazine73' ); ?></button>
						<input type="hidden" name="magazine73_page_ids[]" value="<?php echo esc_attr( (string) $item['id'] ); ?>" />
					</li>
				<?php endforeach; ?>
			</ul>
			<div class="magazine73-pages-panel__summary">
				<p class="magazine73-admin-panel__summary">
					<?php
					printf(
						/* translators: %d: page count */
						esc_html__( 'Pages: %d', 'magazine73' ),
						(int) $stats['count']
					);
					?>
				</p>
				<p class="magazine73-admin-panel__summary">
					<?php
					printf(
						/* translators: %s: total file size */
						esc_html__( 'Total weight: %s', 'magazine73' ),
						esc_html( size_format( (int) $stats['total_bytes'] ) )
					);
					?>
				</p>
				<p class="magazine73-admin-panel__summary">
					<?php
					printf(
						/* translators: %s: average file size */
						esc_html__( 'Average weight per page: %s', 'magazine73' ),
						esc_html( size_format( (int) $stats['average_bytes'] ) )
					);
					?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Save magazine page attachment IDs.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public function save_metabox( int $post_id, \WP_Post $post ): void {
		unset( $post );

		if ( ! isset( $_POST['magazine73_pages_panel_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['magazine73_pages_panel_nonce'] ) );

		if ( ! wp_verify_nonce( $nonce, 'magazine73_save_pages_panel' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Values are sanitized in Magazine_Pages::sanitize_page_ids().
		$raw_ids = isset( $_POST['magazine73_page_ids'] ) ? wp_unslash( $_POST['magazine73_page_ids'] ) : array();

		if ( ! is_array( $raw_ids ) ) {
			$raw_ids = array();
		}

		$page_ids = ( new Magazine_Pages() )->sanitize_page_ids( $raw_ids );

		update_post_meta( $post_id, Magazine_Pages::PAGE_IDS_META_KEY, $page_ids );
	}
}
