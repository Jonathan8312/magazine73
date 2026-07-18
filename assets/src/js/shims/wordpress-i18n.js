/**
 * Bridge WordPress classic i18n globals for bundled admin/viewer modules.
 *
 * Falls back to identity helpers when `wp.i18n` is unavailable (e.g. Playwright fixtures).
 */

/**
 * @param {string} text Text to return unchanged.
 * @return {string} Original text.
 */
function identity( text ) {
	return text;
}

/**
 * Minimal sprintf used when WordPress i18n is not loaded.
 *
 * @param {string} format Format string with %s / %d placeholders.
 * @param {...*}   args   Replacement values.
 * @return {string} Formatted string.
 */
function sprintfFallback( format, ...args ) {
	let index = 0;

	return format.replace( /%(\d+\$)?[sd]/g, ( _match, position ) => {
		const argIndex = position ? Number.parseInt( position, 10 ) - 1 : index++;
		return String( args[ argIndex ] ?? '' );
	} );
}

const i18n = window.wp?.i18n;

export const __ = i18n?.__?.bind( i18n ) ?? identity;
export const _x = i18n?._x?.bind( i18n ) ?? identity;
export const _n = i18n?._n?.bind( i18n ) ?? identity;
export const _nx = i18n?._nx?.bind( i18n ) ?? identity;
export const sprintf = i18n?.sprintf?.bind( i18n ) ?? sprintfFallback;
