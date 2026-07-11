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
	}
}
