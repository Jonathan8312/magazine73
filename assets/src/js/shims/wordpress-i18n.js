/**
 * Bridge WordPress classic i18n globals for bundled admin/viewer modules.
 */

const i18n = window.wp?.i18n;

if ( ! i18n ) {
	throw new Error( 'Magazine73 requires the WordPress wp-i18n script.' );
}

export const __ = i18n.__.bind( i18n );
export const _x = i18n._x.bind( i18n );
export const _n = i18n._n.bind( i18n );
export const _nx = i18n._nx.bind( i18n );
export const sprintf = i18n.sprintf.bind( i18n );
