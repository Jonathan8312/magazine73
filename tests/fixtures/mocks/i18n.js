export function __( text ) {
	return text;
}

export function sprintf( format, ...args ) {
	let index = 0;
	return format.replace( /%(\d+\$)?[sd]/g, ( match, position ) => {
		const argIndex = position ? Number.parseInt( position, 10 ) - 1 : index++;
		return String( args[ argIndex ] ?? '' );
	} );
}
