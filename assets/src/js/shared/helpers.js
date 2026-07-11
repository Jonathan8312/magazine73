/**
 * Shared frontend helpers.
 */

/**
 * Mark an element as ready for styling hooks.
 *
 * @param {Element} element   Target element.
 * @param {string}  className Class name to apply.
 */
export function markReady( element, className ) {
	element.classList.add( className );
}
