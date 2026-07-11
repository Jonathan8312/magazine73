<?php
/**
 * Magazine admin editor screen.
 *
 * @package Magazine73
 */

namespace Magazine73;

defined( 'ABSPATH' ) || exit;

/**
 * Renders magazine metadata controls in the editor.
 */
final class Admin_Metabox {

	/**
	 * Metabox identifier.
	 */
	private const METABOX_ID = 'magazine73_magazine_details';

	/**
	 * Register admin hooks.
	 */
	public function init(): void {
		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );
		add_action( 'save_post_' . Post_Type::POST_TYPE, array( $this, 'save_metabox' ), 10, 2 );
	}

	/**
	 * Register the magazine details metabox.
	 */
	public function register_metabox(): void {
		add_meta_box(
			self::METABOX_ID,
			__( 'Magazine Details', 'magazine73' ),
			array( $this, 'render_metabox' ),
			Post_Type::POST_TYPE,
			'side',
			'high'
		);
	}

	/**
	 * Render the magazine details metabox.
	 *
	 * @param \WP_Post $post Current post object.
	 */
	public function render_metabox( \WP_Post $post ): void {
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		wp_nonce_field( 'magazine73_save_magazine_details', 'magazine73_magazine_details_nonce' );

		$edition    = Magazine_Meta::get_edition( $post->ID );
		$shortcode  = Magazine_Meta::get_shortcode( $post->ID );
		$public_url = 'publish' === $post->post_status ? Magazine_Meta::get_public_url( $post->ID ) : '';
		$pdf        = Magazine_Pdf::get_admin_display( $post->ID );
		$pdf_id     = $pdf['id'] ?? 0;
		$pdf_name   = $pdf['filename'] ?? '';
		?>
		<p>
			<label for="magazine73-edition"><strong><?php esc_html_e( 'Edition', 'magazine73' ); ?></strong></label>
			<input
				type="text"
				class="widefat"
				id="magazine73-edition"
				name="magazine73_edition"
				value="<?php echo esc_attr( $edition ); ?>"
			/>
		</p>
		<p>
			<label for="magazine73-shortcode"><strong><?php esc_html_e( 'Shortcode', 'magazine73' ); ?></strong></label>
			<input
				type="text"
				class="widefat"
				id="magazine73-shortcode"
				value="<?php echo esc_attr( $shortcode ); ?>"
				readonly
			/>
		</p>
		<?php if ( '' !== $public_url ) : ?>
			<p>
				<label for="magazine73-public-url"><strong><?php esc_html_e( 'Public URL', 'magazine73' ); ?></strong></label>
				<input
					type="url"
					class="widefat"
					id="magazine73-public-url"
					value="<?php echo esc_url( $public_url ); ?>"
					readonly
				/>
			</p>
		<?php else : ?>
			<p class="description"><?php esc_html_e( 'The public URL is available after the magazine is published.', 'magazine73' ); ?></p>
		<?php endif; ?>
		<div class="magazine73-pdf-field" data-magazine73-admin>
			<p>
				<strong><?php esc_html_e( 'PDF Download', 'magazine73' ); ?></strong>
			</p>
			<p class="description"><?php esc_html_e( 'Optional PDF file for the download control in the viewer.', 'magazine73' ); ?></p>
			<p>
				<span class="magazine73-pdf-field__filename" data-magazine73-pdf-filename"><?php echo esc_html( $pdf_name ); ?></span>
			</p>
			<p>
				<button type="button" class="button button-secondary" data-magazine73-pdf-select>
					<?php esc_html_e( 'Select PDF', 'magazine73' ); ?>
				</button>
				<button
					type="button"
					class="button button-link-delete"
					data-magazine73-pdf-remove
					<?php echo $pdf_id > 0 ? '' : 'hidden'; ?>
				>
					<?php esc_html_e( 'Remove PDF', 'magazine73' ); ?>
				</button>
			</p>
			<input type="hidden" name="magazine73_pdf_attachment_id" value="<?php echo esc_attr( (string) $pdf_id ); ?>" data-magazine73-pdf-input />
		</div>
		<?php
	}

	/**
	 * Save magazine metadata from the editor metabox.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public function save_metabox( int $post_id, \WP_Post $post ): void {
		unset( $post );

		if ( ! isset( $_POST['magazine73_magazine_details_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['magazine73_magazine_details_nonce'] ) );

		if ( ! wp_verify_nonce( $nonce, 'magazine73_save_magazine_details' ) ) {
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

		$edition = isset( $_POST['magazine73_edition'] )
			? sanitize_text_field( wp_unslash( $_POST['magazine73_edition'] ) )
			: '';

		update_post_meta( $post_id, Magazine_Meta::EDITION_META_KEY, $edition );

		$pdf_attachment_id = isset( $_POST['magazine73_pdf_attachment_id'] )
			? absint( wp_unslash( $_POST['magazine73_pdf_attachment_id'] ) )
			: 0;

		if ( $pdf_attachment_id > 0 && ! Magazine_Pdf::is_valid_pdf_attachment( $pdf_attachment_id ) ) {
			$pdf_attachment_id = 0;
		}

		update_post_meta( $post_id, Magazine_Pdf::PDF_ATTACHMENT_META_KEY, $pdf_attachment_id );
	}
}
