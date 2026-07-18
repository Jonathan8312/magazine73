/**
 * Initialize WordPress Iris color pickers for Magazine73 admin fields.
 */

/**
 * Bind wpColorPicker to Magazine73 color fields.
 */
export function initColorFields() {
	const fields = document.querySelectorAll( '.magazine73-color-field' );

	if ( 0 === fields.length ) {
		return;
	}

	const jquery = window.jQuery;

	if ( ! jquery || 'function' !== typeof jquery.fn.wpColorPicker ) {
		return;
	}

	jquery( fields ).each( ( _, element ) => {
		if ( ! ( element instanceof HTMLElement ) ) {
			return;
		}

		if ( element.closest( '[hidden]' ) ) {
			return;
		}

		const $field = jquery( element );

		if ( $field.data( 'magazine73ColorPicker' ) ) {
			return;
		}

		$field.wpColorPicker( {
			change() {
				$field.trigger( 'input' );
			},
			clear() {
				$field.val( '' ).trigger( 'change' );
			},
		} );

		$field.data( 'magazine73ColorPicker', true );
	} );
}
