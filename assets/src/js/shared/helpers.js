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

/**
 * Run a callback once the DOM is ready.
 *
 * @param {() => void} callback Callback to run.
 */
export function whenDomReady( callback ) {
	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', callback );
		return;
	}

	callback();
}

/**
 * Run a callback once the WordPress media library is available.
 *
 * @param {() => void} callback Callback to run.
 */
export function whenWpMediaReady( callback ) {
	if ( 'undefined' !== typeof window.wp?.media ) {
		callback();
		return;
	}

	let attempts = 0;
	const timer = window.setInterval( () => {
		attempts += 1;

		if ( 'undefined' !== typeof window.wp?.media ) {
			window.clearInterval( timer );
			callback();
			return;
		}

		if ( attempts >= 100 ) {
			window.clearInterval( timer );
		}
	}, 50 );
}

/**
 * Retry a callback until it returns true or the attempt limit is reached.
 *
 * @param {() => boolean} callback    Callback that returns true when complete.
 * @param {object}        [options]    Retry options.
 * @param {number}        [options.maxAttempts=200] Maximum attempts.
 * @param {number}        [options.interval=100] Milliseconds between attempts.
 */
export function retryUntilReady( callback, options = {} ) {
	const maxAttempts = options.maxAttempts ?? 200;
	const interval = options.interval ?? 100;
	let attempts = 0;
	let timer = 0;
	let observer = null;

	const finish = () => {
		if ( timer ) {
			window.clearInterval( timer );
			timer = 0;
		}

		if ( observer ) {
			observer.disconnect();
			observer = null;
		}
	};

	const attempt = () => {
		if ( callback() ) {
			finish();
			return true;
		}

		attempts += 1;

		if ( attempts >= maxAttempts ) {
			finish();
			return true;
		}

		return false;
	};

	if ( attempt() ) {
		return;
	}

	timer = window.setInterval( attempt, interval );

	if ( document.body ) {
		observer = new MutationObserver( attempt );
		observer.observe( document.body, {
			childList: true,
			subtree: true,
		} );
	}
}
